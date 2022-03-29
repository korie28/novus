<?php
// 記事一覧表示
require_once "../../model/ArticleAct.php";
require_once '../../model/Token.php';
require_once '../../model/Utils.php';

use Qanda\ArticleAct;
use Qanda\Token;
use Qanda\Utils;

$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT);
if (!$page) {
  $page = 0;
}

// 初期は、全体一覧
$title = SYSTITLE;
$headertitle = '';
$searchtextrow = '';
$searchtext = filter_input(INPUT_GET, 'searchtext');

if ($searchtext != '') {
  // 検索指定時
  $searchtext =  rawurldecode($searchtext); // urldecodeをしておく
  $title = Utils::h($searchtext) . 'の検索結果';
  $headertitle = ' (' . $title . ')';
  $searchtextrow = rawurlencode(Utils::h($searchtext)); // 特殊文字をjavascriptにそのまま渡すとエラーになるので encodeしておく
}

// 記事一覧
$act = new ArticleAct(0);
$retinfo = $act->articlelist($page, $searchtext);

// Token生成
Token::create();
?>

<div class="row m-2 pt-4 pb-2">
  <div class="col-sm-2"><?php echo $act->getMemberName(); ?>さんようこそ</div>
  <div class="col-sm-7">
    <input type="search" style="width:100%;" id="searcharticle" placeholder="キーワードを入力" value="<?php echo Utils::h($searchtext); ?>" />
  </div>
  <div class="col-sm-3">
  </div>
</div>

<h5 class="artListTitle mt-3 font-weight-bold">記事一覧 <?php echo $headertitle; ?></h5>

<?php
foreach ($retinfo['articlelist'] as $art) {
  // ユーザ情報
  $user = $retinfo['usermap'][$art['USER_ID']];
  $username = ($user == NULL) ? '非ログインユーザー' : $user['NAME'];
  // 投稿日時
  $postdt = Utils::compatiDate($art['UPD_DATE'], 'Y/m/d H:i');
  // いいね数
  $postlikecnt = isset($retinfo['postlikemap'][$art['ARTICLE_ID']]) ? $retinfo['postlikemap'][$art['ARTICLE_ID']] : 0;

  echo '<div class="artfrm" articleid="' . $art['ARTICLE_ID'] . '">';
  echo '<div class="arthead">' . $username . ' さんの投稿</div>';
  echo '<div class="arttitle">' . Utils::h($art['TITLE']) . '</div>';
  echo '<div class="artFootLeft">' . $postdt . '</div>';
  echo '<div class="artfoot">' . "&hearts; " . $postlikecnt . '</div>';
  echo '</div>';
}

if (count($retinfo['articlelist']) == 0) {
  echo '<div class="row m-2"><div class="col-sm-12">1件も記事はありません</div></div>';
}
?>

<!-- ページネーション -->
<div class="row m-2 ml-5 pl-5 col-sm-12">
  <?php
  // 一番最初に戻る(page=0)
  if ($page <= 0) {
    echo '<div class="col-sm-1 text-right"><span class="btn btn-link disabled ">&lt;&lt;</span></div>';
  } else {
    $urlstr = sprintf("%sarticle/home.php?page=%d", DOMAIN."/public/", 0);
    printf('<div class="col-sm-1 text-right"><a class="btn btn-link" href="%s">&lt;&lt;</a></div>', $urlstr);
  }
  // ひとつ前に戻る(page)
  if ($page <= 0) {
    echo '<div class="col-sm-1 text-right"><span class="btn btn-link disabled ">&lt;</span></div>';
  } else {
    $urlstr = sprintf("%sarticle/home.php?page=%d", DOMAIN."/public/", $page - 1);
    printf('<div class="col-sm-1 text-right"><a class="btn btn-link" href="%s">&lt;</a></div>', $urlstr);
  }
  // ページボタン
  $start = $page - 3;
  if ($start < 0)  {
    $start = 0;
  }
  $end = $page + 3;
  if ($end > $retinfo['MAXPAGE']) {
    $end = $retinfo['MAXPAGE'];
  }
  for ($i = $start; $i <= $end; $i++) {
    if ($i == $page) {
      printf('<div class="col-sm-1 cur"><a class="btn btn-primary  disabled">%d</a></span></div>', $i + 1); //表示
    }
    else {
      $urlstr = sprintf("%sarticle/home.php?page=%d", DOMAIN."/public/", $i);
      printf('<div class="col-sm-1"><a class="btn btn-light" href="%s">%d</a></span></div>', $urlstr, $i + 1); //表示
    }
  }
  // 次送り 一つ先
  if ($page >= $retinfo['MAXPAGE']) {
    echo '<div class="col-sm-1 text-left"><span class="btn btn-link disabled">&gt;</span></div>';
  } else {
    $nextpage = ($page + 1) >= $retinfo['MAXPAGE'] ? $retinfo['MAXPAGE']: $page + 1;
    $urlstr = sprintf("%sarticle/home.php?page=%d", DOMAIN."/public/", $nextpage);
    printf('<div class="col-sm-1 text-left"><a class="btn btn-link" href="%s">&gt;</a></div>', $urlstr);
  }
  // 次送り page=MAXPAGE
  if ($page >= $retinfo['MAXPAGE']) {
    echo '<div class="col-sm-1 text-left"><span class="btn btn-link disabled">&gt;&gt;</span></div>';
  } else {
    $urlstr = sprintf("%sarticle/home.php?page=%d", DOMAIN."/public/", $retinfo['MAXPAGE']);
    printf('<div class="col-sm-1 text-left"><a class="btn btn-link" href="%s">&gt;&gt;</a></div>', $urlstr);
  }
  ?>
</div>


<script type="text/javascript">
  // 初期化処理
  $(function() {
<?php
    if ($searchtextrow != '') {
    // ブラウザのタイトルを変更 (javascriptないで urldecodeする)
        echo "$('title').html(decodeURIComponent('" . $searchtextrow . "') + 'の検索結果');";
    }
?>
  });
  $('.artfrm').click(function() {
    // 記事をクリックした
    $articleid = $(this).attr('articleid');
    // 記事詳細へ
    jumpapi('article/detail.php?articleid=' + $articleid);
  });
  $('#searcharticle').change(function() {
    // 検索フィールド利用

    // 検索キーワード指定で、本ページ再読み込み
    var txtdata = $(this).val();
    jumpapi('article/home.php?page=' + <?php echo $page; ?> + '&searchtext=' + encodeURIComponent(txtdata));
  });
</script>

<hr />
<div class="row m-2">
  <div class="col-sm-8">
  <a class="btn btn-warning m-2" href="<?php echo DOMAIN; ?>/public/todo/index.php">todoへ</a>
    <a class="btn btn-success m-2" href="../../top/userLogin/login_top.php">ホーム画面へ</a>
  </div>
  <div class="col-sm-4">
    <a class="btn btn-primary" href="<?php echo DOMAIN; ?>/public/article/postedit.php">投稿する</a>
  </div>
</div>

<?php
$act->end(1);
?>