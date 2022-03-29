<?php
  session_start();

  //ファイルの読み込み
  require_once '../../classes/QuestionLogic.php';
  require_once '../../classes/UserLogic.php';

  //error
  $err = [];


  if(isset($_SESSION['a_data']['message']) &&
    isset($_SESSION['a_data']['user_id']) &&
    isset($_SESSION['a_data']['question_id'])
  ){
    //返答を登録する処理
    $hasCreated = QuestionLogic::createAnswer();
    
    if(!$hasCreated){
      $err['answer'] = '返信の読み込みに失敗しました';
    }elseif($hasCreated){
      // 経験値を加算する処理
      $plusEXP = UserLogic::plusEXP($_SESSION['login_user']['user_id'], 10);
    }
    if(!$plusEXP){
      $err['plusEXP'] = '経験値加算処理に失敗しました';
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
                <p class="h4">回答完了</p>
                <p>返答の投稿が完了しました</p>
                <form method="GET" action="question_disp.php">
                    <input type="hidden" name="question_id" value="<?php echo $_SESSION['a_data']['question_id'] ?>">
                    <input type="submit" class="btn btn-warning mb-3" name="q_disp"value="質問表示へ戻る">
                </form>
                <button type="button" class="btn btn-outline-dark fw-bold mb-5" onclick="location.href='../../top/userLogin/login_top.php'">TOP</button>
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
