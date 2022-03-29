<?php
  session_start();

  //ファイルの読み込み
  require_once '../../classes/QuestionLogic.php';
  require_once '../../classes/CategoryLogic.php';
  require_once '../../classes/UserLogic.php';

  $result = UserLogic::checkLogin();
  if(!$result) {
    $_SESSION['login_err'] = 'ユーザーを登録してログインして下さい';
    header('Location: ../../top/userLogin/login_top.php');
    return;
  }
  $categories = CategoryLogic::getCategory();

  //error
  $err = [];

  $question_id = filter_input(INPUT_POST, 'question_id');
  if(!$question_id == filter_input(INPUT_POST, 'question_id')) {
    $err[] = '質問を選択し直してください';
  }

  if (count($err) === 0){
    //質問を引っ張る処理
    $question = QuestionLogic::displayQuestion($_POST);
    if(!$question){
        $err[] = '質問の読み込みに失敗しました';
    }
  }
  
  if(isset($_POST['q_dlt'])){
    $_SESSION['q_data']['question_id'] = filter_input(INPUT_POST, 'question_id', FILTER_SANITIZE_SPECIAL_CHARS);
    if(!$_SESSION['q_data']['question_id']) {
      $err['q_id'] = '質問IDが選択されていません';
    }
    
    $dlt = QuestionLogic::deleteQuestion($_SESSION['q_data']['question_id']);
    if(!$dlt){
        $err[] = '質問の削除に失敗しました';
    }

    if (count($err) === 0){
        header('Location: question_delete_comp.php');
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <script src="https://kit.fontawesome.com/7bf203e5c7.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" type="text/css" href="2.css" />
  <link rel="stylesheet" type="text/css" href="../../css/mypage.css" />
  <link rel="stylesheet" type="text/css" href="../../css/top.css" />
  <link rel="stylesheet" type="text/css" href="../../css/question.css" />
  <title>質問削除</title>
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
            <li class="top"><a href="../../top/userLogin/login_top.php">TOP Page</a></li>
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
                <p class="h4 pb-3 mt-3">質問内容</p>
                <form method="POST" action="">
                    <!--題名-->
                    <div class="fw-bold pb-1">題名</div>
                    <div><?php echo $question['title'] ?></div>
                    <!--カテゴリー-->
                    <div class="fw-bold pt-3 pb-1">カテゴリ</div>
                    <div><?php echo $question['category_name'] ?></div>
                    <!--本文-->
                    <div class="fw-bold pt-3 pb-1">本文</div>
                    <div><?php echo $question['message'] ?></div>
                    <div>添付</div>
                    <input type="hidden" name="question_id" value="<?php echo $question['question_id'] ?>">
                    <i class="fa-solid fa-trash-can mt-3"><input class="fw-bold " type="submit" name="q_dlt" id="delete" value="削除"></i>
                    
                </form>
                <button type="button" class="mb-4 mt-5 btn btn-outline-dark" onclick="history.back()">戻る</button>
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