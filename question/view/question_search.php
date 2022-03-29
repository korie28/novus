<?php 

session_start();

//ファイルの読み込み
  require_once '../../classes/CategoryLogic.php';
  require_once '../../classes/QuestionLogic.php';

//error
$err = [];

$categories = CategoryLogic::getCategory();

//検索ボタン押下時、条件に合った質問を表示
if(isset($_GET['search'])){
  $searchQuestion = QuestionLogic::searchQuestion($_GET);
  if(!$searchQuestion){
    $err['question'] = '質問の読み込みに失敗しました';
	}
}else{// 通常時は、新着の質問を表示する
	$newQuestion = QuestionLogic::newQuestion();
}

?>
<!DOCTYPE HTML>
<html lang="ja">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>質問</title>
<link rel="stylesheet" href="style.css">
<script src="https://kit.fontawesome.com/7bf203e5c7.js" crossorigin="anonymous"></script>
<link rel="stylesheet" type="text/css" href="2.css" />
<link rel="stylesheet" type="text/css" href="../../css/mypage.css" />
<link rel="stylesheet" type="text/css" href="../../css/top.css" />
<link rel="stylesheet" type="text/css" href="../../css/question.css" />
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
                <h2 class="col-xs-6 col-xs-offset-3 pb-3 mt-4">質問サイトへようこそ</h2>
                <a href="question_create.php" class="text-dark">質問を投稿する</a>
                <div class="col-xs-6 col-xs-offset-3 well">
	                <!-- ②検索フォーム  -->
	                <form method="get">
	                	<div class="form-group">
	                		<input name="keyword" class="form-control mb-3" value="<?php echo isset($_GET['name']) ? htmlspecialchars($_GET['name']) : '' ?>">
	                	</div>
	                	<div class="form-group">
                            <select name="category">
                                <option></option>
                                <?php foreach($categories as $category){ ?>
                                <option 
                                value="<?php echo $category['cate_id'] ?>"
                                <?php if(isset($_GET['category']) && $category['cate_id'] == $_GET['category']) : ?>
                                  selected
                                <?php endif; ?>> 
                                <?php echo $category['category_name'] ?>
                                </option>
                                <?php } ?>
                            </select>
	                	</div>
	                	<button type="submit" class="btn btn-warning mt-4" name="search">検索</button>
	                </form>
                </div>

	            <div class="col-xs-6 col-xs-offset-3">
        		    <!-- 検索ボタン押下時、取得データを表示する -->
        		    <?php if(isset($searchQuestion) && count($searchQuestion)): ?>
        			<p class="alert alert-success"><?php echo count($searchQuestion) ?>件見つかりました。</p>
        			<div class="fw-bold mt-2 mb-2 h5">検索結果</div>
					<?php foreach($searchQuestion as $value): ?>
        			<div><a href="question_disp.php? question_id=<?php echo $value['question_id']?>">題名：<?php echo htmlspecialchars($value['title']) ?></a></div>
					<div><img src="../../top/img/<?php echo $value['icon']; ?>"></div>
					<div><?php echo htmlspecialchars($value['name']) ?>さん</div>
					<div>カテゴリ：<?php echo htmlspecialchars($value['category_name']) ?></div>
        			<div>本文：<?php echo htmlspecialchars($value['message']) ?></div>
        			<!-- 更新されていた場合、その日付を優先表示 -->
				    <div>
					    <?php if (!isset($value['upd_date'])): ?>
					    	投稿：<?php echo htmlspecialchars($value['answer_date'])  ?>
					    <?php else: ?>
					    	更新：<?php echo htmlspecialchars($value['upd_date']) ?>
					    <?php endif; ?>
                    </div>
			        <?php endforeach; ?>

		            <?php elseif (isset($searchQuestion) && count($searchQuestion) == 0): ?>
			        <p class="alert alert-danger">検索対象は見つかりませんでした。</p>
		
			        <!-- 通常時、新着の質問を表示 -->
		            <?php elseif(isset($newQuestion)): ?>
		            	<div class="fw-bold mt-2 mb-2 h5">新着の質問</div>
		            	<?php foreach($newQuestion as $value): ?>
		            		<div><a href="question_disp.php? question_id=<?php echo $value['question_id']?>">題名「<?php echo htmlspecialchars($value['title']) ?>」</a></div>
		            		<div><img src="../../top/img/<?php echo $value['icon']; ?>"></div>
							<div><?php echo htmlspecialchars($value['name']) ?>さん</div>
							<div>カテゴリ：<?php echo htmlspecialchars($value['category_name']) ?></div>
		            		<div>本文：<?php echo htmlspecialchars($value['message']) ?></div>
		            	    <div class="small pb-4">日時：<?php echo htmlspecialchars($value['post_date']) ?></div>
		            	<?php endforeach; ?>
		            <?php endif; ?>
	            </div>
	            <button type="button" class="mb-4 mt-5 btn btn-outline-dark" onclick="location.href='../../top/userLogin/login_top.php'">TOP</button>
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
