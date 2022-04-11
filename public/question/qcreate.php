<?php
session_start();

// ファイルの読み込み
require_once '../../app/UserLogic.php';
require_once '../../app/QuestionLogic.php';
require_once '../../app/CategoryLogic.php';

// ログインチェック
$result = UserLogic::checkLogin();
if (!$result) {
    $_SESSION['login_err'] = '再度ログインして下さい';
    header('Location: ../userLogin/home.php');
    return;
}

// エラーメッセージ
$err = [];

//カテゴリ処理
$categories = CategoryLogic::getCategory();

// ボタン押下時の処理（成功でページ移動）
if (isset($_POST['create_question'])) {
    $_SESSION['q_data']['user_id'] = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_SPECIAL_CHARS);
    $_SESSION['q_data']['title'] = filter_input(INPUT_POST, 'title');
    $_SESSION['q_data']['category'] = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_SPECIAL_CHARS);
    $_SESSION['q_data']['message'] = filter_input(INPUT_POST, 'message');
    // 必須部分チェック
    if (!$_SESSION['q_data']['title']) {
        $err['title'] = '質問タイトルを入力してください';
    }
    if (!$_SESSION['q_data']['category']) {
        $err['category'] = 'カテゴリを選択してください';
    }
    if (!$_SESSION['q_data']['message']) {
        $err['message'] = '本文を入力してください';
    }

    if (!empty($_SESSION['q_data']['title'])) {
        $limitTitle = 150;
        // 文字数チェック
        if (mb_strlen($_SESSION['q_data']['title']) > $limitTitle) {
        $err['title'] = '150文字以内で入力してください';
        }
    }
    if (!empty($_SESSION['q_data']['message'])) {
        $limitMessage = 1500;
        // 文字数チェック
        if (mb_strlen($_SESSION['q_data']['message']) > $limitMessage) {
        $err['message'] = '1500文字以内で入力してください';
        }
    }

    if (count($err) === 0) {
        header('Location: qComp.php');
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../css/mypage.css">
    <link rel="stylesheet" type="text/css" href="../css/top.css">
    <title>novus</title>
</head>

<body>
    <!--メニュー-->
    <header>
    <div class="navbar bg-dark text-white">
        <div class="navtext h2" id="headerlogo"><a href="<?php echo (($result) ? '../userLogin/home.php' : '../top/index.php'); ?>" style="color: white;">novus</a></div>
			<ul class="nav justify-content-center">
                <li class="nav-item"><form type="hidden" action="mypage.php" method="POST" name="mypage">
			    	    <a class="nav-link small text-white" href="../myPage/index.php">マイページ</a>
			    	    <input type="hidden">
                    </form>
                </li>
			    <li id="li"><a class="nav-link active small text-white" href="../userLogin/home.php">TOPページ</a></li>
                <li id="li"><a class="nav-link small text-white" href="../todo/index.php">TO DO LIST</a></li>
                <li id="li"><a class="nav-link small text-white" href="<?php echo "../userLogin/logout.php?=user_id=".$_SESSION['login_user']['user_id']; ?>">ログアウト</a></li>
            </ul>
		</div>
    </header>

    <div class="wrapper">
        <div class="container">
            <div class="content">
                <p class="h4 mt-5">質問投稿</p>
                <p>質問したい内容を入力して下さい</P>
                <!-- 質問投稿フォーム -->
                <form method="POST" action="" name="q_data">
                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['login_user']['user_id']; ?>">
                    <div class=""style="text-align: center">
                    <!--題名-->
                    <div class="fw-bold pb-1">題名</div>
                    <input type="text" name="title"><br>
                    <!--エラー表示-->
                    <div>
                        <?php if (isset($err['title'])): ?>
                            <p class="text-danger pt-2"><?php echo $err['title']; ?></p>
                        <?php endif; ?>
                    </div>
                    <!--カテゴリー-->
                    <div class="fw-bold pt-4 pb-1">カテゴリ</div>
                    <select id="category" name="category">
                        <?php foreach ($categories as $value): ?>
                            <option value="<?php echo $value['cate_id']; ?>"> 
                                <?php echo $value['category_name']; ?>
                            </option>";
                        <?php endforeach; ?>
                    </select>
                    <!--エラー表示-->
                    <div>
                        <?php if (isset($err['category'])): ?>
                            <p class="text-danger pt-2"><?php echo $err['category']; ?></p>
                        <?php endif; ?>
                    </div>
                    <!--本文-->
                    <div class="fw-bold pt-4 pb-1">本文</div>
                    <textarea name="message" rows="5" class="w-100"></textarea>
                    <!--エラー表示-->
                    <div>
                        <?php if (isset($err['message'])): ?>
                            <p class="text-danger pt-2"><?php echo $err['message']; ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="row">
                        <div class="col-sm-3"></div>
                        <button style="display: inline-block;" type="button" class="col-sm-2 mb-4 mt-5 btn btn-outline-dark" onclick="history.back()">戻る</button>
                        <div class="col-sm-2"></div>
                        <input style="display: inline-block;" type="submit" name="create_question" class="col-sm-2 btn btn-warning mt-5 mb-5" value="投稿する">
                        <div class="col-sm-3"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- フッタ -->
    <footer class="h-10"><hr>
	    <div class="footer-item text-center">
			<h4>novus</h4>
			<ul class="nav nav-pills nav-fill">
                <li class="nav-item">
					<a class="nav-link small" href="../article/index.php">記事</a>
				</li>
				<li class="nav-item">
					<a class="nav-link small" href="../question/index.php">質問</a>
				</li>
				<li class="nav-item">
					<a class="nav-link small" href="../bookApi/index.php">本検索</a>
				</li>
				<li class="nav-item">
					<a class="nav-link small" href="../contact/index.php">お問い合わせ</a>
				</li>
			</ul>
		</div>
		<p class="text-center small mt-2">Copyright (c) HTMQ All Rights Reserved.</p>
	</footer>
</body>
</html>

<script type="text/javascript">
$(function() {
    $('#category').val(0);
    setupPreview();
});
</script>