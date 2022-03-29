<?php
  session_start();

  //ファイルの読み込み
  require_once '../../classes/QuestionLogic.php';

  //error
  $err = [];

  $a_message = filter_input(INPUT_POST, 'a_message', FILTER_SANITIZE_SPECIAL_CHARS);
  $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_SPECIAL_CHARS);
  $question_id = filter_input(INPUT_POST, 'question_id', FILTER_SANITIZE_SPECIAL_CHARS);

  if(!$a_message) {
    $err[] = '本文を入力してください';
  }
  if(!$user_id) {
    $err[] = 'ユーザーを選択し直してください';
  }
  if(!$question_id) {
    $err['question_id'] = '質問を選択し直してください';
  }

  // 投稿ボタン押下時の内部処理（成功でページ移動）
  if(isset($_POST['a_comp'])){
    $_SESSION['a_data']['message'] = filter_input(INPUT_POST, 'a_message', FILTER_SANITIZE_SPECIAL_CHARS);
    $_SESSION['a_data']['user_id'] = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_SPECIAL_CHARS);
    $_SESSION['a_data']['question_id'] = filter_input(INPUT_POST, 'question_id', FILTER_SANITIZE_SPECIAL_CHARS);

    if(empty($_SESSION['a_data']['message'])) {
      $err['q_id'] = '本文が入力されていません';
    }
    if(empty($_SESSION['a_data']['user_id'])) {
      $err['q_id'] = 'ユーザーが選択されていません';
    }
    if(empty($_SESSION['a_data']['question_id'])) {
      $err['q_id'] = '質問IDが選択されていません';
    }

      if (count($err) === 0){
        header('Location: answer_create_comp.php');
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
  <title>質問投稿完了</title>
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

    <!--コンテンツ-->
    <section class="wrapper">
        <div class="container">
            <div class="content">
                <p class="h4">投稿内容の確認</p>
                <p>以下の内容でよろしいですか？</p>
                <!--回答内容の確認-->
                <div class="fw-bold pb-1">内容</div>
                <div><?php echo $a_message ?></div>
                <form method="POST" action="">
                    <input type="hidden" name="a_message" value="<?php echo $a_message; ?>">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    <input type="hidden" name="question_id" value="<?php echo $question_id ?>">
                    <input type="submit" name="a_comp"value="投稿">
                </form>
                <button type="button" class="btn btn-outline-dark fw-bold mb-5" onclick="location.href='../../top/userLogin/login_top.php'">TOP</button>
                <button type="button" class="btn btn-outline-dark fw-bold mb-5" onclick="history.back()">戻る</button>
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
