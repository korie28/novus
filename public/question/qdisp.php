<script src=" https://code.jquery.com/jquery-3.4.1.min.js "></script>

<?php
session_start();

// ファイルの読み込み
require_once '../../app/QuestionLogic.php';
require_once '../../app/UserLogic.php';

// ログインしているか判定
$result = UserLogic::checkLogin();

// エラーメッセージ
$err = [];

// 詳細表示する質問の選択処理
$question_id = filter_input(INPUT_GET, 'question_id', FILTER_SANITIZE_NUMBER_INT);
if (!$question_id) {
    $question_id = filter_input(INPUT_POST, 'question_id', FILTER_SANITIZE_NUMBER_INT);
}
if (!$question_id) {
    $err['q_id'] = '質問を選択し直してください';
}

// 質問表示処理
if (count($err) === 0) {
    // 質問の取得
    $question = QuestionLogic::displayQuestion($_GET);
    if (!$question) {
        $err['q_id'] = '質問を選択し直してください';
    }
    // 質問返答の取得
    $answer = QuestionLogic::displayAnswer($_GET['question_id']);
        if (!$answer) {
            $err['answer'] = '返信の読み込みに失敗しました';
        }
}

// いいね処理
if (isset($_POST['like_id'])) {
    if (isset($_POST['like_delete'])) {
        $like_btn = QuestionLogic::switchLike(0, $_POST['like_id']);
        if (!$like_btn) {
            $err['like'] = 'いいねの切り替えに失敗しました';
        }
    }
    if (isset($_POST['like_reregist'])) {
        $like_btn = QuestionLogic::switchLike(1, $_POST['like_id']);
        if (!$like_btn) {
            $err['like'] = 'いいねの切り替えに失敗しました';
        }
    }
}

// いいね登録処理
if (isset($_POST['like_regist'])) {
    $a_user_id = filter_input(INPUT_POST, 'a_user_id', FILTER_SANITIZE_NUMBER_INT);
    // 登録処理
    $like_btn = QuestionLogic::createLike($_POST);
    if (!$like_btn) {
        $err['like'] = 'いいねの登録に失敗しました';
    }
    // 経験値を加算する処理
    $plusEXP = UserLogic::plusEXP($a_user_id, 5);
    if (!$plusEXP) {
        $err['plusEXP'] = '経験値加算処理に失敗しました';
    }
    header("Location: qDisp.php?question_id=$question_id");
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <script src=" https://code.jquery.com/jquery-3.4.1.min.js "></script>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/7bf203e5c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="../css/mypage.css">
    <link rel="stylesheet" type="text/css" href="../css/top.css">
    <link rel="stylesheet" type="text/css" href="../css/question.css">
    <title>novus</title>
</head>

<body>
	<!--メニュー-->
    <header>
	    <?php if ($result): // ログインしていれば下記の表示 ?>
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
                <li id="li"><a class="nav-link small text-white" href="../../public/myPage/qHistory.php">【履歴】質問</a></li>
                <li id="li"><a class="nav-link small text-white" href="../../public/myPage/aHistory.php">【履歴】記事</a></li>
                <li id="li"><a class="nav-link small text-white" href="<?php echo "../userLogin/logout.php?=user_id=".$_SESSION['login_user']['user_id']; ?>">ログアウト</a></li>
            </ul>
		</div>
		<?php else: // 未ログインであれば下記の表示 ?>
        <div class="navbar bg-dark text-white">
            <div class="navtext h2" id="headerlogo"><a href="<?php echo (($result) ? '../userLogin/home.php' : '../top/index.php'); ?>" style="color: white;">novus</a></div>
            <ul class="nav justify-content-center">
			    <li id="li"><a class="nav-link active small text-white" href="../top/index.php">TOPページ</a></li>
			    <li id="li"><a class="nav-link active small text-white" href="../question/index.php">質問ページ</a></li>
			    <li id="li"><a class="nav-link active small text-white" href="../article/index.php">記事ページ</a></li>      
            </ul>
		</div>
		<?php endif; ?>
    </header>

    <!--コンテンツ-->
    <div class="wrapper">
        <div class="container">
            <div class="content">
                <?php if (!$question_id || !$question): ?>
                    <div class="alert alert-danger"><?php echo $err['q_id']; ?></div>
                <?php else: ?>
                <!--質問の詳細表示-->
                <?php if (isset($err['question'])):  ?>
                    <?php echo $err['question']; ?>
                <?php endif; ?>
                <p class="h4 pb-3 pt-4">詳細表示</p>
                <?php if ($question['best_select_flg'] == 1): ?>
                    <div class="text-danger">解決済み</div>
                <?php endif; ?>
                <div class="pb-1 small">
                    <!--アイコン-->
                    <?php if ($question['icon'] !== null && !empty($question['icon'])): ?>
                        <img src="../top/img/<?php echo $question['icon']; ?>">
                    <?php else: ?>
                        <?php echo "<img src="."../top/img/sample_icon.png".">"; ?>
                    <?php endif; ?>
                    <!--投稿者-->
                    <div class="pb-4 pt-2 small">
                        <?php echo $question['name']; ?>
                        さん
                    </div>
                    <!--題名-->
                    <div class="fw-bold pb-1">題名</div>
                        <div style="overflow: hidden; overflow-wrap: break-word;"><?php echo nl2br(htmlspecialchars($question['title'])); ?></div>
                    <!--本文-->
                    <div class="fw-bold pt-3 pb-1">本文</div>
                        <div style="overflow: hidden; overflow-wrap: break-word;"><?php echo nl2br(htmlspecialchars($question['message'], \ENT_QUOTES, 'UTF-8')); ?></div>
                    <!-- カテゴリと投稿日時を横並びにする処理 -->
                    <div class="block">
                        <!--カテゴリ-->
                        <div style="background: <?php echo $question['color']; ?>; display: inline-block;" class="artFootLeft badge rounded-pill border border-secondary ml-3"><?php echo htmlspecialchars($question['category_name']); ?></div>
                        <!--投稿日時-->
                        <div style="display: inline-block;" class="small pb-4">
                            <!-- 更新されていた場合、その日付を優先表示 -->
                            <?php if (!isset($value['upd_date'])): ?>
                                投稿：<?php echo date('Y/m/d H:i', strtotime($question['post_date'])); ?>
                            <?php else: ?>
                                更新：<?php echo date('Y/m/d H:i', strtotime($question['upd_date'])); ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 質問者本人の時、編集・削除ボタン表示 -->
                    <?php if ($result): ?>
                        <?php if ($_SESSION['login_user']['user_id'] == $question['user_id']): ?>
                            <form method="POST" name="question" action="qEdit.php" id="qedit">
                                <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
                                <i class="fa-solid fa-pen"><input type="submit" id="edit" value="編集"></i>
                            </form>
                            <form method="POST" name="question" action="qDelete.php" id="qDelete">
                                <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
                                <i class="fa-solid fa-trash-can"><input type="submit" id="delete" value="削除"></i>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                    <br>
                    <hr size="4">
                    <br>
                    <!-- 返答表示部分 -->
                    <?php if (!empty($answer)): ?>
                        <h4>返信一覧</h4>
                        <?php if (isset($err['answer'])):  ?>
                            <?php echo $err['answer']; ?>
                        <?php endif; ?>
                        <?php foreach ($answer as $value): ?>
                            <hr id="dot">
                            <!--アイコン-->
                            <div class="pb-1 small">
                                <?php if ($value['icon'] !== null && !empty($value['icon'])): ?>
                                    <img src="../top/img/<?php echo $value['icon']; ?>">
                                <?php else: ?>
                                    <?php echo "<img src="."../top/img/sample_icon.png".">"; ?>
                                <?php endif; ?>
                            </div>
                            <!--ユーザー名-->
                            <div><?php echo $value['name']; ?>さん</div>
                            <!--本文-->
                            <div class="fw-bold pt-3 pb-1">本文</div>
                            <div style="overflow: hidden; overflow-wrap: break-word;"><?php echo nl2br(htmlspecialchars($value['message'], FILTER_SANITIZE_SPECIAL_CHARS, 'UTF-8')); ?></div>
                            <!-- いいね表示と投稿日時を横並びにする処理 -->
                            <div class="block">
                                <!-- フラグがONになっているいいねの数を表示 -->
                                <?php $likes = QuestionLogic::displayLike($value['answer_id']); ?>
                                <div class="mb-3" style="color: red; display: inline-block;">&hearts;<?php echo count($likes); ?>　</div>
                                <!--投稿日時-->
                                <div style="display: inline-block;">
                                    <!-- 更新されていた場合、その日付を優先表示 -->
                                    <?php if (!isset($value['upd_date'])): ?>
                                        投稿：<?php echo date('Y/m/d H:i', strtotime($value['answer_date']));  ?>
                                    <?php else: ?>
                                        更新：<?php echo date('Y/m/d H:i', strtotime($value['upd_date'])); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <!-- ベストアンサー選択された返答の目印 -->
                            <?php if ($value['best_flg']): ?>
                                <div class="alert alert-danger">ベストアンサー</div>
                            <?php endif; ?>

                            <!-- いいねボタンの表示部分 -->
                            <?php if ($result): ?>
                                <!-- 返信投稿ユーザーと違うユーザーなら、いいねボタン表示 -->
                                <?php if ($value['user_id'] != $_SESSION['login_user']['user_id']): ?>
                                    <form class="favorite_count" action="#" method="post">
                                        <input type="hidden" id="user_id" name="user_id" value="<?php echo $_SESSION['login_user']['user_id']; ?>">
                                        <input type="hidden" id="answer_id" name="answer_id" value="<?php echo $value['answer_id']; ?>">
                                        <!-- いいねの有無チェック -->
                                        <?php $checkLike = QuestionLogic::checkLike($_SESSION['login_user']['user_id'], $value['answer_id']); ?>
                                        <!-- いいねがある場合 -->
                                        <?php if (!empty($checkLike)): ?>
                                            <input type="hidden" name="like_id" value="<?php echo $checkLike['q_like_id']; ?>">
                                            <!-- いいねフラグが1の場合、いいね解除のボタンに -->
                                            <?php if ($checkLike['like_flg'] == 1): ?>
                                                <input type="submit" name="like_delete" value="いいね解除">
                                            <!-- いいねフラグが0の場合、いいね再登録のボタンに -->
                                            <?php else: ?>
                                                <input type="submit" name="like_reregist" value="いいね">
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <!-- いいねがない場合、いいね登録のボタンに -->
                                            <input type="hidden" name="a_user_id" value="<?php echo $value['user_id']; ?>">
                                            <input type="hidden" name="q_id" value="<?php echo $question_id; ?>">
                                            <input type="submit" name="like_regist" value="いいね">
                                        <?php endif; ?>
                                    </form>
                                    <!-- 返信投稿ユーザー＝ログインユーザーなら、返答の編集・削除ボタン表示 -->
                                <?php else: ?>
                                    <?php if ($_SESSION['login_user']['user_id'] == $value['user_id']): ?>
                                        <form method="POST" action="../questionAnswer/aEdit.php" name="question" id="qedit">
                                            <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
                                            <input type="hidden" name="answer_id" value="<?php echo $value['answer_id']; ?>">
                                            <i class="fa-solid fa-pen"><input type="submit" name="a_edit"  id="edit" value="編集"></i>
                                        </form>                                     
                                        <form method="POST" action="../questionAnswer/aDelete.php" name="question" id="qDelete">
                                            <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
                                            <input type="hidden" name="answer_id" value="<?php echo $value['answer_id']; ?>">
                                            <i class="fa-solid fa-trash-can"><input type="submit" name="a_edit" value="削除" id="delete"></i>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php if ($_SESSION['login_user']['user_id'] == $question['user_id'] && $question['best_select_flg'] == 0 && $_SESSION['login_user']['user_id'] != $value['user_id']): ?>
                                    <!-- 質問者本人 ＆ 返答が質問者以外の場合、ベストアンサーボタンの表示 -->
                                    <form method="POST" action="bestAnswer.php">
                                        <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
                                        <input type="hidden" name="answer_id" value="<?php echo $value['answer_id']; ?>">
                                        <input type="hidden" name="answer_user_id" value="<?php echo $value['user_id']; ?>">
                                        <input type="submit" value="ベストアンサー">
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                            <br>
                            <hr size="4">
                            <br>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- ベストアンサーが選択されていると新規投稿できなくなる処理 -->
                    <?php if ($result): ?>
                        <?php if ($question['best_select_flg'] == 0): ?>
                            <form method="POST" action="../questionAnswer/aCreateConf.php">
                                <input type="hidden" name="a_user_id" value="<?php echo $_SESSION['login_user']['user_id']; ?>">
                                <input type="hidden" name="q_user_id" value="<?php echo $question['user_id']; ?>">
                                <input type="hidden" name="question_id" value="<?php echo $question['question_id']; ?>">
                                <textarea placeholder="ここに返信を入力してください" name="a_message" class="w-75" rows="3"></textarea>
                                <br><input type="submit" class="btn btn-warning mt-2" value="返信">
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                    <button type="button" class="mb-4 mt-3 btn btn-outline-dark" onclick="location.href='index.php'">戻る</button>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
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

