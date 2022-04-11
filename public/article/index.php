<?php
require_once "../../app/ArticleAct.php";
require_once '../../app/Token.php';
require_once '../../app/Utils.php';

use Novus\ArticleAct;
use Novus\Token;
use Novus\Utils;

// 表示するページを取得
$currentPage = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT);
if ($currentPage !== '') {
    $currentPage = (int)$currentPage;
    if ($currentPage <= 0) {
        $currentPage = 1;
    }
} else {
    $currentPage = 1;
}

// 初期状態は全体一覧を表示
$searchTextrow = '';
$searchCategoryrow = '';
$searchText = filter_input(INPUT_GET, 'searchText') ?? ''; // string(入力文字)
$searchCategory = filter_input(INPUT_GET, 'searchCategory') ?? ''; // int(入力カテゴリ)

// 検索指定時
if ($searchText !== '') {
    $searchText =  rawurldecode($searchText); // 入力値をデコードしておく
    $searchTextrow = rawurlencode(Utils::h($searchText)); // 特殊文字を変換しておく
}

// カテゴリー指定時
if ($searchCategory !== '') {
    $searchCategoryrow = rawurlencode(Utils::h($searchCategory)); // 特殊文字を変換しておく
}

// 記事一覧
$act = new ArticleAct(0);
// 表示中の全情報
$retInfo = $act->articleList($currentPage, $searchText, $searchCategory);

$currentPage = $retInfo['page'];

$category = $act->categoryMap(); //cate_idとカテゴリ名の連想配列
$categoryColor = $act->categoryColorMap(); //cate_idとcolorの連想配列

// ログインユーザーのアイコンと名前
if (isset($_SESSION['login_user'])) {
    $icon = $act->getMemberIcon();
    $name = $act->getMemberName();
}

// Token生成
Token::create();

// URL引数作成
$params = [];
if ($searchTextrow !== '') {
    $params[] = "searchText=$searchTextrow";
}
if ($searchCategoryrow !== '') {
    $params[] = "searchCategory=$searchCategoryrow";
}
$query = !empty($params) ? '&' . implode('&', $params) : ''; // &で区切って表示

// タイトルに表示する、カテゴリー名称を取得
$selectedCategoryName = '';
if ($searchCategory !== '') {
    foreach ($category as $key => $val) {
        if ($key == (int)$searchCategory) {
            $selectedCategoryName = $val; //example:国語
            break;
        }
    }
}

// ページタイトル及びヘッダータイトルに表示する文字列を作成
$pageTitle = SYSTITLE; // ブラウザタブに記載
$headerTitle = '';

// 検索内容を出力
if ($searchText !== '' || $selectedCategoryName !== '') {
    $pageTitle = sprintf(
        '%s%sの検索結果',
        $searchText !== '' ? $searchText : '',
        $selectedCategoryName !== '' ? '（' . $selectedCategoryName . '）' : ''
    );
    $headerTitle = '（' . $pageTitle . '）';
}
?>

<div class="row m-2 pt-4 pb-2 align-items-center">
    <?php if (isset($_SESSION['login_user'])) : ?>
        <a href="<?php echo DOMAIN ?>/public/myPage/index.php" class="d-flex align-items-center col-sm-2 text-dark">
            <?php echo (isset($icon) && !empty($icon) ? '<img src="' . DOMAIN . '/public/top/img/' . $icon . '" class="mr-1">' : '<img src="' . DOMAIN . '/public/top/img/sample_icon.png" class="mr-1">') ?>
            <span style="overflow: hidden; overflow-wrap: break-word;"><?php echo $name ?> さん</span>
        </a>
    <?php else : ?>
        <div class="col-sm-2"></div>
    <?php endif; ?>
    <div class="col-sm-7 text-center">
        <div class="input-group">
            <input type="search" style="width:100%;" class="form-control search-text" placeholder="キーワードを入力" value="<?php echo Utils::h($searchText); ?>">
            <div class="input-group-append mr-2">
                <select class="form-control search-category" name="category" placeholder="カテゴリ">
                    <?php
                    echo "<option value=''></option>";
                    foreach ($category as $key => $val) {
                        printf('<option value="%s"%s>%s</option>', $key, $key == $searchCategory ? ' selected' : '', $val);
                    }
                    ?>
                </select>
            </div>
            <button class="btn btn-secondary btn-search" type="button">検索</button>
        </div>
    </div>
</div>

<h5 class="artListTitle mt-3 font-weight-bold artTitle">記事一覧 <?php echo Utils::h($headerTitle); ?></h5>

<?php
//全データを各投稿ごとに展開
foreach ($retInfo['articleList'] as $art) {
    // 投稿ユーザ情報
    $user = $retInfo['userMap'][$art['user_id']];
    // 投稿ユーザのアイコン
    $postIcon = $retInfo['userMap'][$art['user_id']]['icon'];
    // 投稿者の名前
    $userName = $user["name"];
    // 投稿タイトル
    $title = $art['title'];
    // 投稿日時
    $postDt = Utils::compatiDate($art['post_date'], 'Y/m/d H:i');
    $updDt = Utils::compatiDate($art['upd_date'], 'Y/m/d H:i');
    // カテゴリ名
    $cateName = Utils::h($retInfo["category"][$art["cate_id"]]);
    // カテゴリの背景色
    $cateColor = Utils::h($categoryColor[$art["cate_id"]]);
    // いいね数
    $postLikeCnt = $retInfo['postLikeMap'][$art['article_id']] ??  0; //合体演算子

    echo '<div class="artfrm" article_id="' . $art['article_id'] . '">';
    echo '<div href="#" class="d-flex align-items-end">';
    echo (isset($postIcon) && !empty($postIcon) ? '<img src="' . DOMAIN . '/public/top/img/' . $postIcon . '" class="mr-1">' : '<img src="' . DOMAIN . '/public/top/img/sample_icon.png" class="mr-1">');
    echo '<span class="arthead ml-1">' . $userName . 'さんの投稿</span>';
    echo '</div>';
    echo '<div class="arttitle">' . Utils::h($title) . '</div>';
    echo '<div class="artFootLeft">' . (isset($updDt) && $updDt === "" ? $postDt : $updDt) . '</div>';
    echo '<div class="artFootLeft badge rounded-pill  ml-3 p-1 pl-2 pr-2 " style="background:' . $cateColor . '; color:#fff;">' . $cateName . '</div>';
    echo '<div class="artfoot">' . "&hearts; " . $postLikeCnt . '</div>';
    echo '</div>';
}

// count($retInfo['articleList']): 表示中の記事の数
if (count($retInfo['articleList']) === 0) {
    echo '<div class="row m-2"><div class="col-sm-12">1件も記事がありません</div></div>';
}
?>
<!-- ページネーション -->
<?php if (count($retInfo['articleList']) > 0) : ?>
    <div>
        <ul class="pagination justify-content-center">
            <?php
            // 一番最初に戻る(page=0)
            if ($currentPage <= 1) {
                echo '<li class="page-item page-item-nav"><span class="btn btn-link disabled">&lt;&lt;</span></li>';
            } else {
                $urlstr = sprintf("%sarticle/index.php?page=%d%s", DOMAIN . "/public/", 1, $query);
                printf('<li class="page-item page-item-nav"><a class="btn btn-link" href="%s">&lt;&lt;</a></li>', $urlstr); // &lt;⇒『<』
            }
            // ひとつ前に戻る(page)
            if ($currentPage <= 1) {
                echo '<li class="page-item page-item-nav" style="width: 36px;"><span class="btn btn-link disabled ">&lt;</span></li>';
            } else {
                $urlstr = sprintf("%sarticle/index.php?page=%d%s", DOMAIN . "/public/", $currentPage - 1, $query);
                printf('<li class="page-item page-item-nav"><a class="btn btn-link" href="%s">&lt;</a></li>', $urlstr);
            }

            // 一番左にくるボタンの表示
            if ($currentPage <= 3) {
                $start = 1;
            } elseif ($currentPage > $retInfo['maxPage'] - 2) {
                $start = $retInfo['maxPage'] - 4;
            } else {
                $start = $currentPage - 2;
            }

            // 一番右にくるボタンの表示
            if ($start + 4 <= $retInfo['maxPage']) {
                $end = $start + 4;
            } else {
                $end = $retInfo['maxPage'];
            }
            // リンクボタンを5個表示
            for ($i = $start; $i <= $end; $i++) {
                if ($i == $currentPage) {
                    printf('<li class="page-item page-item-page"><a class="btn btn-primary disabled">%d</a></span></li>', $i);
                } else {
                    $urlstr = sprintf("%sarticle/index.php?page=%d%s", DOMAIN . "/public/", $i, $query);
                    printf('<li class="page-item page-item-page"><a class="btn btn-light" href="%s">%d</a></span></li>', $urlstr, $i);
                }
            }

            // 次送り 一つ先
            if ($currentPage >= $retInfo['maxPage']) {
                echo '<li class="page-item page-item-nav"><span class="btn btn-link disabled">&gt;</span></li>';
            } else {
                $nextpage = ($currentPage + 1) >= $retInfo['maxPage'] ? $retInfo['maxPage'] : $currentPage + 1;
                $urlstr = sprintf("%sarticle/index.php?page=%d%s", DOMAIN . "/public/", $nextpage, $query);
                printf('<li class="page-item page-item-nav"><a class="btn btn-link" href="%s">&gt;</a></li>', $urlstr);
            }
            // 次送り page=maxPage
            if ($currentPage >= $retInfo['maxPage']) {
                echo '<li class="page-item page-item-nav"><span class="btn btn-link disabled">&gt;&gt;</span></li>';
            } else {
                $urlstr = sprintf("%sarticle/index.php?page=%d%s", DOMAIN . "/public/", $retInfo['maxPage'], $query);
                printf('<li class="page-item page-item-nav"><a class="btn btn-link" href="%s">&gt;&gt;</a></li>', $urlstr);
            }
            ?>
        </ul>
    </div>
<?php endif ?>


<script type="text/javascript">
    // 初期化処理
    $(function() {
        <?php
        // ブラウザのタイトルを変更(javascript内でurldecodeする)
        echo "$('title').html(decodeURIComponent('" . Utils::h($pageTitle) . "'));";
        ?>

        $('.artfrm').click(function() {
            // 記事をクリックした
            $article_id = $(this).attr('article_id');
            // 記事詳細へ
            jumpApi('article/detail.php?article_id=' + $article_id + '&page=<?php echo $currentPage; ?>' + '<?php echo $query; ?>');
        });

        function search() {
            // 検索キーワード指定で、本ページ再読み込み
            var searchText = $('.search-text').val();
            var searchCategory = $('.search-category').val();

            var params = [];
            if (searchText !== '') params.push('searchText=' + encodeURIComponent(searchText));
            if (searchCategory !== '') params.push('searchCategory=' + encodeURIComponent(searchCategory));
            var url = params.length ? '&' + params.join('&') : '';
            jumpApi('article/index.php?page=1' + url);
        }

        $('.search-text').change(function() {
            // 検索実行
            search();
        });

        $('.search-category').on("keydown", function(e) {
            // カテゴリーがEnterされた場合に検索実行(キーコード)
            if (e.which == 13) {
                search();
            }
        });

        $('.btn-search').click(function() {
            // 検索実行
            search();
        });
    });
</script>

<?php
$act->printFooter(0);
?>