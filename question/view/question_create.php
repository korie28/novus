<?php
    session_start();
    
    //ファイルの読み込み
    require_once '../../classes/UserLogic.php';
    require_once '../../classes/QuestionLogic.php';
    require_once '../../classes/CategoryLogic.php';

    // ログインチェック
    $result = UserLogic::checkLogin();
    if(!$result) {
        $_SESSION['login_err'] = 'ユーザーを登録してログインして下さい';
        header('Location: ../../top/userLogin/login_top.php');
        return;
    }

    //error
    $err = [];
    $categories = CategoryLogic::getCategory();

    // ボタン押下時の処理（成功でページ移動）
    if(isset($_POST['create_question'])){
        $_SESSION['q_data']['user_id'] = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_SPECIAL_CHARS);
        $_SESSION['q_data']['title'] = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
        $_SESSION['q_data']['category'] = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_SPECIAL_CHARS);
        $_SESSION['q_data']['message'] = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS);
        
        if(isset($_POST['question_image'])){
            $_SESSION['q_data']['question_image'] = filter_input(INPUT_POST, 'question_image', FILTER_SANITIZE_SPECIAL_CHARS);
        }else{
            $_SESSION['q_data']['question_image'] = null;
        }
        // 必須部分チェック
        if(!$_SESSION['q_data']['title']) {
            $err['title'] = '質問タイトルを入力してください';
        }
        if(!$_SESSION['q_data']['category']) {
            $err['category'] = 'カテゴリを選択してください';
        }
        if(!$_SESSION['q_data']['message']) {
            $err['message'] = '本文を入力してください';
        }
        
        if (count($err) === 0){
            header('Location: question_comp.php');
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="2.css" />
    <link rel="stylesheet" type="text/css" href="../../css/mypage.css" />
    <link rel="stylesheet" type="text/css" href="../../css/top.css" />
    <title>質問投稿ページ</title>
</head>

<body>
    <!--メニュー-->
    <header>
        <div class="navtext-container">
            <div class="navtext">Q&A SITE</div>
        </div>
        <input type="checkbox" class="menu-btn" id="menu-btn">
        <label for="menu-btn" class="menu-icon"><span class="navicon"></span></label>
        <ul class="menu">
            <li class="top"><a href="login_top.php">TOP Page</a></li>
            <li><a href="../userEdit/edit_user.php">My Page</a></li>
            <li><a href="#">TO DO LIST</a></li>
            <li><a href="../../question/view/qhistory.php">質問 履歴</a></li>
            <li><a href="../../">記事 履歴</a></li>
            <li>
                <form type="hidden" action="logout.php" method="POST">
				    <input type="submit" name="logout" value="ログアウト" id="logout" style="text-align:left;">
                </form>
            </li>
        </ul>
    </header>

    <section class="wrapper">
        <div class="container">
            <div class="content">
                <p class="h4">質問投稿</p>
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
                        <?php if(isset($err['title'])): ?>
                            <p class="text-danger pt-2"><?php echo $err['title'] ?></p>
                        <?php endif; ?>
                    </div>
                    <!--カテゴリー-->
                    <div class="fw-bold pt-4 pb-1">カテゴリ</div>
                    <select name="category">
                        <option></option>
                        <?php foreach($categories as $value){ ?>
                            <option value="<?php echo $value['cate_id'] ?>"> 
                                <?php echo $value['category_name'] ?>
                            </option>";
                        <?php } ?>
                    </select>
                    <!--エラー表示-->
                    <div>
                        <?php if(isset($err['category'])): ?>
                            <p class="text-danger pt-2"><?php echo $err['category'] ?></p>
                        <?php endif; ?>
                    </div>
                    <!--本文-->
                    <div class="fw-bold pt-4 pb-1">本文</div>
                    <textarea name="message" rows="5" class="w-100"></textarea>
                    <!--エラー表示-->
                    <div>
                        <?php if(isset($err['message'])): ?>
                            <p class="text-danger pt-2"><?php echo $err['message'] ?></p>
                        <?php endif; ?>
                    </div>
                    <!--添付ファイル-->
                    <div class="fw-bold pt-4 pb-1">添付</div>
                    <div class="small pb-3">※jpgまたはpng形式にてお願いいたします</div>
                        <input type="file" name="question_image" accept="image/png, image/jpeg">
                    <input type="submit" name="create_question" class="btn btn-warning mt-5 mb-5" value="投稿する">
                </form>
            </div>
        </div>
    </section>

    <!-- フッタ -->
    <footer class="h-10"><hr>
	    <div class="footer-item text-center">
			<h4>Q&A SITE</h4>
			<ul class="nav nav-pills nav-fill">
                <li class="nav-item">
					<a class="nav-link small" href="#">記事</a>
				</li>
				<li class="nav-item">
					<a class="nav-link small" href="#">質問</a>
				</li>
				<li class="nav-item">
					<a class="nav-link small" href="#">本検索</a>
				</li>
				<li class="nav-item">
					<a class="nav-link small" href="#">お問い合わせ</a>
				</li>
			</ul>
		</div>
		<p class="text-center small mt-2">Copyright (c) HTMQ All Rights Reserved.</p>
	</footer>
</body>
</html>