<?php
    session_start();

    //ファイルの読み込み
    require_once '../../classes/QuestionLogic.php';
    require_once '../../classes/UserLogic.php';

    //error
    $err = [];

    // データ受け渡しチェック
    if (isset($_SESSION['q_data']['user_id']) &&
        isset($_SESSION['q_data']['title']) &&
        isset($_SESSION['q_data']['category']) &&
        isset($_SESSION['q_data']['message'])
      ){
        //質問を登録する処理
        $hasCreated = QuestionLogic::createQuestion();
          if(!$hasCreated){
            $err[] = '登録に失敗しました';
          }elseif($hasCreated){
            // 経験値を加算する処理
            $plusEXP = UserLogic::plusEXP($_SESSION['login_user']['user_id'], 10);
          }
          if(!$plusEXP){
            $err['plusEXP'] = '経験値加算処理に失敗しました';
          }
      }
      //最新の質問を取得する処理
      $hasTaken = QuestionLogic::newQuestion();
        if(!$hasTaken){
          $err[] = '質問の取り込みに失敗しました';
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
                <p class="h4">投稿完了</p>
                <p>以下の内容で投稿が完了しました</p>
                <!--題名-->
                <div class="fw-bold pb-1">題名</div>
                <div><?php echo $hasTaken[0]['title'] ?></div>
                <!--カテゴリー-->
                <div class="fw-bold pt-3 pb-1">カテゴリ</div>
                <div><?php echo $hasTaken[0]['category_name'] ?></div>
                <!--本文-->
                <div class="fw-bold pt-3 pb-1">本文</div>
                <div><?php echo $hasTaken[0]['message'] ?></div>
                <!--ファイルを投稿していたら表示-->
                <?php if (isset($hasTaken[0]['question_image'])): ?>
                  <img src="../../top/img/<?php echo $hasTaken[0]['question_image']; ?>">
                <?php endif; ?> 
                <form method="GET" name="form1" action="question_disp.php">
                    <input type="hidden" name="question_id" value="<?php echo $hasTaken[0]['question_id']; ?>">
                    <a href="javascript:form1.submit()" class="btn btn-warning mt-2">詳細画面へ</a>
                </form>
                <a href="../login_top.php" class="btn btn-outline-dark fw-bold">TOP</a>
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