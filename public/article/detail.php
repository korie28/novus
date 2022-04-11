<?php
require_once "../../app/ArticleAct.php";
require_once '../../app/Token.php';
require_once '../../app/Utils.php';
require_once "../../app/VendorUtils.php";

use Novus\ArticleAct;
use Novus\Token;
use Novus\Utils;
use Novus\VendorUtils;

$act = new ArticleAct(1);
Token::create(); //Token生成

$retInfo = null;
$article_id = filter_input(INPUT_GET, "article_id", FILTER_SANITIZE_NUMBER_INT);
// 記事一覧のクエリ情報
$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT);
$searchText = filter_input(INPUT_GET, 'searchText') ?? '';
$searchCategory = filter_input(INPUT_GET, 'searchCategory') ?? '';

if ($article_id) {
    // 記事単独の全情報を取得
    $retInfo = $act->article($article_id);
}

if ($retInfo === null || $retInfo['user'] === false || $retInfo['article'] === false) {
    // 記事がない場合は、記事一覧へリダイレクト
    $act->redirectTop();
    exit;
}

$category = $act->categoryMap();
$categoryColor = $act->categoryColorMap();

// カテゴリ名
$cateName = Utils::h($retInfo["category"][$retInfo["article"]["cate_id"]]);
$cateColor = Utils::h($categoryColor[$retInfo["article"]["cate_id"]]);

// 投稿日時
$postDt = Utils::compatiDate($retInfo["article"]["post_date"], 'Y/m/d H:i');
$updDt = Utils::compatiDate($retInfo["article"]["upd_date"], "Y/m/d H:i");

// 投稿内容
$message = $retInfo['article']['message'];
$message = Utils::h($message);
$message = VendorUtils::markDown($message); // マークダウンの変換
$message = Utils::trimHtmlTag($message); // 一部タグを許容、他は変換
$message = html_entity_decode($message); // エンティティデコード
$message = Utils::compatiStr($message); // 改行を <br/>

$icon = $retInfo["user"]["icon"];

// 記事一覧へ戻る際のURL引数作成
$params = [];
if ($searchText !== '') {
    $params[] = 'searchText='.rawurldecode($searchText);
}
if ($searchCategory !== '') {
    $params[] = 'searchCategory='.rawurldecode($searchCategory);
}
$query = 'page=' .rawurldecode($page);
$query .= !empty($params) ? '&' . implode('&', $params) : '';

$act->printHeader();
?>

<!-- ここから本文 -->
<h5 class="artListTitle mt-3 font-weight-bold artTitle">記事詳細</h5>
<!-- 投稿者名といいね数 -->
<div class="row m-2">
    <?php if (isset($_SESSION['login_user']['user_id']) && ($_SESSION['login_user']['user_id'] === $retInfo["article"]["user_id"])) : ?>
        <a href="<?php echo DOMAIN ?>/public/myPage/index.php" class="d-flex align-items-center col-sm-9 text-dark">
            <?php echo((isset($icon) && !empty($icon)) ? '<img src="' . DOMAIN . '/public/top/img/' . $icon . '" class="mr-1">' : '<img src="' . DOMAIN . '/public/top/img/sample_icon.png" class="mr-1">') ?>
            <?php echo $act->getMemberName(); ?> さん
        </a>
    <?php elseif (isset($_SESSION['login_user']['user_id'])) : ?>
        <a href="<?php echo DOMAIN ?>/public/myPage/userPage.php?user_id=<?php echo $retInfo["article"]["user_id"] ?>" class="d-flex align-items-center col-sm-9 text-dark">
            <?php echo(isset($icon) && !empty($icon) ? '<img src="' . DOMAIN . '/public/top/img/' . $icon . '" class="mr-1">' : '<img src="' . DOMAIN . '/public/top/img/sample_icon.png" class="mr-1">'); ?>
            <?php echo $retInfo["user"]["name"]; ?>さんの投稿
        </a>
    <?php else : ?>
        <a href="<?php echo DOMAIN ?>/public/top/userPage.php?user_id=<?php echo $retInfo["article"]["user_id"] ?>" class="d-flex align-items-center col-sm-9 text-dark">
            <?php echo(isset($icon) && !empty($icon) ? '<img src="' . DOMAIN . '/public/top/img/' . $icon . '" class="mr-1">' : '<img src="' . DOMAIN . '/public/top/img/sample_icon.png" class="mr-1">'); ?>
            <?php echo $retInfo["user"]["name"]; ?>さんの投稿
        </a>
    <?php endif ?>
    <div class="d-flex align-items-center col-sm-2 mt-2">
        <?php
        if (isset($_SESSION['login_user']) && $retInfo["article"]["user_id"] != $act->getMemberId()) {
            if ($retInfo["postLike"] == null || $retInfo["postLike"]["like_flg"] == 0) {
                // いいねボタン押下で、いいねにする
                print('<a class="btn btn-warning like" id="btnlike">いいね</a>');
            } else {
                // いいね済み。ボタン押下で、いいねを解除
                print('<a class="btn btn-warning active liked" id="btnlike">いいね</a>');
            }
        }
        ?>
        <span class="ml-3">&hearts; <span id="postLikeCnt"><?php echo $retInfo["postLikeCnt"]; ?></span></span>
    </div>
</div>

<!-- タイトルと本文 -->
<div class="container-fluid">
    <!-- タイトル -->
    <div class="form-group m-2">
        <div class="col-sm-12" style="word-break:break-all;">
            <h2 class="artDetailTitle"><?php echo Utils::h($retInfo['article']['title']); ?></h2>
            <div class="m-2" style="overflow:scroll; overflow-x: hidden; height:430px;">
                <span class="artDetailContents" id="preview"><?php echo $message; ?></span>
            </div>
        </div>
        <!-- カテゴリバッジ -->
        <div class="row m-2 form-group">
            <div class="catename col-sm-1">
                <div class="badge rounded-pill p-2" style="background:<?php echo $cateColor; ?>; color:#fff;">
                    <?php echo $cateName; ?>
                </div>
            </div>
            <div class="col-sm1-11"></div>
        </div>
        <!-- 日付 -->
        <div class="row m-2 form-group">
            <div class="artFootLeft col-sm-3"><?php echo(isset($updDt) && $updDt === "" ? $postDt : $updDt); ?></div>
            <div class="artFootLeft col-sm-9"></div>
        </div>
    </div>

    <hr>
    <div class="row m-2">
        <div class="col-sm-6">
            <?php if (isset($_SESSION['login_user'])) : ?>
                <a class="btn btn-success mt-2" href="<?php echo DOMAIN; ?>/public/userLogin/home.php">ホーム画面へ</a>
            <?php else : ?>
                <a class="btn btn-success mt-2" href="<?php echo DOMAIN; ?>/public/top/index.php">ホーム画面へ</a>
            <?php endif ?>
        </div>
        <div class="col-sm-6">
            <?php
            if (isset($_SESSION['login_user']) && $retInfo["article"]["user_id"] == $act->getMemberId()) {
                // 自分が投稿した記事
                print('<div class="btn btn-warning mt-2 ml-sm-5" id="btndelete">削除する</div>');
                printf(
                    '<a class="btn btn-primary mt-2 ml-1" href="%sarticle/postEdit.php?article_id=%d&%s">編集する</a>',
                    DOMAIN . "/public/",
                    $article_id,
                    $query
                );
            }
            ?>
            <a class="btn btn-primary mt-2" href="<?php echo DOMAIN; ?>/public/article/index.php?<?php echo $query; ?>">一覧に戻る</a>
        </div>
    </div>
</div>

<script type="text/javascript">
    // いいねボタンを押したとき
    function onPostLike() {
        // ボタンを一時的に無効にする
        const $btnlike = $('#btnlike').off().removeClass('active');

        // 送信(ajax)
        var $data = 'article_id=' + <?php echo $_GET['article_id']; ?> + '&token=<?php echo $_SESSION["token"]; ?>';
        formApiCallback('article/process/postLike.php', $data, function($result) {

            const $postLikeCnt = $('#postLikeCnt');
            const cnt = parseInt($postLikeCnt.html());

            // ボタンを戻す
            $btnlike.click(onPostLike);

            // 画面に反映
            if ($result == 'likeset') {
                // いいねにした
                $btnlike.addClass('active');
                $btnlike.html('いいね');
                $postLikeCnt.html(cnt + 1);
            } else {
                // いいね解除。ボタンを再いいねできるようにする。
                $btnlike.html('いいね');
                $postLikeCnt.html(cnt > 0 ? cnt - 1 : 0); //三項演算子 → 条件式 ? true式1 : false式2
            }
        });
    }
    // 削除ボタンを押した
    function onDelete() {
        swal({
            text: '削除してもよろしいですか？',
            icon: 'warning',
            buttons: true,
            dangerMode: true
        }).then(function(isConfirm) {
            if (isConfirm) {
                var $data = 'article_id=' + <?php echo $article_id; ?> + '&token=<?php echo $_SESSION["token"]; ?>';
                formApiCallback('article/process/delete.php', $data, function($retcode) {
                    // 投稿一覧画面へ
                    jumpApi('article/index.php?<?php echo $query; ?>');
                });
            }
        });
    }
    // 初期化
    $(function() {
        $('#btnlike').click(onPostLike);
        $('#btndelete').click(onDelete);
    });
</script>

<?php
$act->printFooter(1);
?>
