<?php
session_start();

//ファイル読み込み
require_once '../../classes/UserLogic.php';
require_once '../../classes/QuestionLogic.php';
require_once '../../classes/LevelLogic.php';
require_once '../../functions.php';
//エラーメッセージ
$err = [];

//ログインしているか判定して、していなかったら新規登録画面へ移す
$result = UserLogic::checkLogin();
if (!$result) {
    $_SESSION['login_err'] = 'ユーザーを登録してログインして下さい';
    header('Location: ../userCreate/signup_form.php');
    return;
}
$login_user = $_SESSION['login_user'];

//上位３位のレベルを表示
$level = LevelLogic::levelTop3();
$paging = LevelLogic::levelRanking();
if (!$level) {
	$err[] = '表示するレベルがありません';
}

//最新の質問を表示
$newQuestion = QuestionLogic::newQuestion();
if(!$newQuestion){
	$err['question'] = '質問の読み込みに失敗しました';
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../../css/top.css" />
    <link rel="stylesheet" type="text/css" href="../../css/mypage.css" />
    <script src="https://kit.fontawesome.com/7bf203e5c7.js" crossorigin="anonymous"></script>
    <title>トップ画面</title>
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
            <li class="top"><form type="hidden" action="mypage.php" method="POST" name="mypage">
				    <a class="nav-link small text-white" href="mypage.php">マイページ</a>
				    <input type="hidden">
                </form>
            </li>
			<li><a class="nav-link active small text-white" href="../../question/view/question_search.php">質問Page</a></li>
            <li><a class="nav-link small text-white" href="#">記事ページ</a>
            <li><a class="nav-link small text-white" href="#">ライブラリ</a>
            <li><a class="nav-link small text-white" href="#contact">お問い合わせ</a></li>
            <li>
                <form type="hidden" action="logout.php" method="POST">
				    <input type="submit" name="logout" value="Log Out" id="logout">
                </form>
            </li>
        </ul>
    </header>

	<section class="wrapper">
	    <div class="container">
	        <div class="text-center">
				<!--レベル上位３人を出す-->
                <div class="form-row text-center">
                    <div id="title">
		    		    <h2 class="heading" id="rankingtitle">レベルランキング TOP3</h2>
		    			<?php foreach($level as $value): ?>
                        <!--ユーザーが登録した画像を表示-->
                        <div class="level-icon"><br>
                            <?php if (isset($value['icon'])): ?> 
		    					<!--画像をクリックすると、自分のアイコンならmypage,他人ならuserpageに遷移-->
		    					<a name="icon" href="<?php if ($value['user_id'] === $_SESSION['login_user']['user_id']) {
		    						echo 'mypage.php'; } else {
                                    echo "userpage.php?user_id=".$value['user_id'] ;} ?>">
                                <img src="../img/<?php echo $value['icon']; ?>"></a>
                            <?php else: ?>
		    					<!--上記と同じ処理-->
		    					<!-- <form type="hidden" name="userpage" action="-->
		    					<a name="icon" href="<?php if ($value['user_id'] === $_SESSION['login_user']['user_id']) { 
		    						echo 'mypage.php'; } else {
									//user_idをユーザーページに引き継ぐ
		    						echo "userpage.php?user_id=".$value['user_id'] ;} ?>">
		    						<?php echo "<img src="."../img/sample_icon.png".">"; ?></a>
		    					    <!-- <input id="imginput" type="submit" value=""></form> -->
                            <?php endif; ?>
                        </div>
                        <div class="text">
                            <!--名前とレベル-->
		        			<?php echo $value['name']; ?>
                            Lv.<?php echo $value['level']; ?>
                        </div>
                        <?php endforeach ?>
		        		<a class="small" href="../level/levelDisp.php">ランキング詳細<i class="fa-solid fa-arrow-right"></i></a><hr>
		        	</div>
                </div>

			    <!-- 通常時、新着の質問を表示 -->
		        <?php if(isset($newQuestion)): ?>
		        	<div>新着の質問</div>
		        	<?php foreach($newQuestion as $value): ?>
		        		<div><a href="question_disp.php? question_id=<?php echo $value['question_id']?>">題名：<?php echo htmlspecialchars($value['title']) ?></a></div>
		        		<div>カテゴリ：<?php echo htmlspecialchars($value['category_name']) ?></div>
		        		<div>本文：<?php echo htmlspecialchars($value['message']) ?></div>
		        		<div>名前：<?php echo htmlspecialchars($value['name']) ?></div>
		        		<div><?php echo htmlspecialchars($value['icon']) ?></div>
		        	<div>日時：<?php echo htmlspecialchars($value['post_date']) ?></div>
		        	<?php endforeach; ?>
		        <?php endif; ?>
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
