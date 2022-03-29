<script src=" https://code.jquery.com/jquery-3.4.1.min.js "></script>
  <script src="/qandasite/question/view/js/like.js" defer></script>

<?php
  session_start();

  //ファイルの読み込み
  require_once '../../classes/QuestionLogic.php';

  //error
  $err = [];

  $question_id = filter_input(INPUT_GET, 'question_id');
  if(!$question_id = filter_input(INPUT_GET, 'question_id', FILTER_SANITIZE_SPECIAL_CHARS)) {
    $err[] = '質問を選択し直してください';
  }
  if (count($err) === 0){
    //質問を引っ張る処理
    $question = QuestionLogic::displayQuestion($_GET);
      if(!$question){
        $err['question'] = '質問の読み込みに失敗しました';
      }
    // 質問への返答を引っ張る処理
    $answer = QuestionLogic::displayAnswer($_GET);
      if(!$answer){
        $err['answer'] = '返信の読み込みに失敗しました';
      }
  }

  
  if(isset($_POST['like_id'])){
    if(isset($_POST['like_delete'])){
      $like_btn = QuestionLogic::switchLike(0, $_POST['like_id']);
      if(!$like_btn){
        $err['like'] = 'いいねの切り替えに失敗しました';
      }
    }
    if(isset($_POST['like_reregist'])){
      $like_btn = QuestionLogic::switchLike(1, $_POST['like_id']);
      if(!$like_btn){
        $err['like'] = 'いいねの切り替えに失敗しました';
      }
    }
  }
    if(isset($_POST['like_regist'])){
      // いいね登録処理
      $like_btn = QuestionLogic::createLike($_POST);
      if(!$like_btn){
        $err['like'] = 'いいねの登録に失敗しました';
      }
      // 経験値を加算する処理
      $plusEXP = UserLogic::plusEXP($_SESSION['login_user']['user_id'], 5);
      if(!$plusEXP){
        $err['plusEXP'] = '経験値加算処理に失敗しました';
      }
    }
    var_dump($_POST);
?>      
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <script src=" https://code.jquery.com/jquery-3.4.1.min.js "></script>
  <script src="/qandasite/question/view/js/like.js" defer></script>
  <!-- <script src="js/like.js"></script> -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>質問表示</title>
</head>
<body>
  <?php if(isset($err['question'])):  ?>
    <?php echo $err['question'] ?>
  <?php endif; ?>
  <!-- 質問表示 -->
  <div>質問内容</div>
    <div>題名：<?php echo $question['title'] ?></div>
    <div>カテゴリ：<?php echo $question['category_name'] ?></div>
    <div>本文：<?php echo htmlspecialchars($question['message'], FILTER_SANITIZE_SPECIAL_CHARS, 'UTF-8') ?></div>
    <!-- 更新されていた場合、その日付を優先表示 -->
    <div>
    <?php if (!isset($question['upd_date'])): ?>
        投稿：<?php echo $question['post_date']  ?>
    <?php else: ?>
        更新：<?php echo $question['upd_date'] ?>
    <?php endif; ?>
    </div>
    <div>名前：<?php echo $question['name'] ?></div>
    <div>アイコン：
      <!-- アイコン設定時、アイコン表示 -->
      <div class="list-item">
        <?php if (isset($login_user['icon'])): ?> 
            <img src="../img/<?php echo $login_user['icon']; ?>">
        <?php else: ?>
        <?php echo "<img src="."../../top/img/sample_icon.png".">"; ?>
        <?php endif; ?>
    </div>

    <!-- 質問者本人の時、編集・削除ボタン表示 -->
    <?php if($_SESSION['login_user']['user_id'] == $question['user_id']): ?>
      <form method="POST" name="question" action="question_edit.php">
        <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
        <input type="submit" value="編集">
      </form>
      <form method="POST" name="question" action="question_delete.php">
        <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
        <input type="submit" value="削除">
      </form>
    <?php endif; ?>

    <!-- 返答表示部分 -->
    <?php if(!empty($answer)): ?>
      <?php if(isset($err['answer'])):  ?>
        <?php echo $err['answer'] ?>
      <?php endif; ?>
      <?php foreach($answer as $value){ ?>

        <div>名前：<?php echo $value['name'] ?></div>
        <div>アイコン：
        <div class="list-item">
        <?php if (isset($login_user['icon'])): ?> 
            <img src="../img/<?php echo $login_user['icon']; ?>">
        <?php else: ?>
        <?php echo "<img src="."../../top/img/sample_icon.png".">"; ?>
        <?php endif; ?>
        </div>
        <div>本文：<?php echo htmlspecialchars($value['message'], FILTER_SANITIZE_SPECIAL_CHARS, 'UTF-8') ?></div>
        <div>
          <!-- 更新されていた場合、その日付を優先表示 -->
          <?php if (!isset($value['upd_date'])): ?>
            投稿：<?php echo $value['answer_date']  ?>
          <?php else: ?>
            更新：<?php echo $value['upd_date'] ?>
          <?php endif; ?>
        </div>

        <!-- フラグがONになっているいいねの数を表示 -->
        <?php $likes = QuestionLogic::displayLike($value['answer_id']); ?>
        <div>いいね数：<?php echo count($likes) ?></div>
        
        <!-- ベストアンサー選択された返答の目印 -->
        <?php if($value['best_flg']): ?>
          <div>ベストアンサー選択されてます！！！！！</div>
        <?php endif; ?>

        <div class="col-sm-2">
    <?php
    if ($retinfo["article"]["USER_ID"] != $act->getMemberId()) {
      if ($retinfo["postlike"] == NULL || $retinfo["postlike"]["LIKE_FLG"] == 0) {
        // いいねボタン押下で、いいねにする
        print('<a class="btn btn-primary" id="btnlike">いいね</a>');
      } else {
        // いいね済み。ボタン押下で、いいねを解除
        print('<a class="btn btn-primary active" id="btnlike">いいね[済]</a>');
      }
    }
    ?>
  </div>

        <!-- いいねボタンの表示部分 -->
        <?php $checkLike = QuestionLogic::checkLike($_SESSION['login_user']['user_id'],$value['answer_id']); ?>
        <form class="favorite_count" action="#" method="post">
          <input type="hidden" id="user_id" name="user_id" value="<?php echo $_SESSION['login_user']['user_id'] ?>">
          <input type="hidden" id="answer_id" name="answer_id" value="<?php echo $value['answer_id'] ?>">
          <!-- いいねの有無チェック -->
          <?php if(!empty($checkLike)): ?>
          <input type="hidden" name="like_id" value="<?php echo $checkLike['q_like_id'] ?>">
            <!-- いいねがある場合 -->
            <!-- いいねフラグが1の場合、いいね解除のボタンに -->
            <?php if($checkLike['like_flg'] == 1): ?>
                <input type="submit" name="like_delete" value="いいね仮">
              <button type="button" name="like_delete" class="like_btn">
                いいね解除
              <!-- いいねフラグが0の場合、いいね再登録のボタンに -->
              <?php else: ?>
                <input type="submit" name="like_reregist" value="いいね仮">
                <button type="button" name="like_reregist" data-answer_id="<?php echo $value['answer_id'] ?>" class="like_btn">

                いいね再登録 <!-- どっちのいいねが表示されてるかの仮置き -->
              <?php endif; ?>
          <!-- いいねがない場合、いいね登録のボタンに -->
          <?php else: ?>
            <input type="submit" name="like_regist" value="いいね仮">
            <button type="button" name="like_regist" class="like_btn">
            いいね登録 <!-- どっちのいいねが表示されてるかの仮置き -->
          <?php endif; ?>
          </button>
        </form>

        <!-- 質問者本人 ＆ 返答が質問者以外の場合、ベストアンサーボタンの表示 -->
        <?php if($_SESSION['login_user']['user_id'] == $question['user_id'] && $question['best_select_flg'] == 0 && $_SESSION['login_user']['user_id'] != $value['user_id']): ?>
          <form method="POST" action="best_answer.php">
            <input type="hidden" name="question_id" value="<?php echo $question_id ?>">
            <input type="hidden" name="answer_id" value="<?php echo $value['answer_id'] ?>">
            <input type="submit" value="ベストアンサー">
          </form>
        <?php endif; ?>

        <!-- 本人の返答に対して、返答の編集・削除ボタン表示 -->
        <?php if($_SESSION['login_user']['user_id'] == $value['user_id']): ?>
          <form method="POST" action="answer_edit.php">
            <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
            <input type="hidden" name="answer_id" value="<?php echo $value['answer_id'] ?>">
            <input type="submit" name="a_edit" value="編集">
          </form>
          <form method="POST" action="answer_delete.php">
            <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
            <input type="hidden" name="answer_id" value="<?php echo $value['answer_id'] ?>">
            <input type="submit" name="a_edit" value="削除">
          </form>
        <?php endif; ?>
        <div>----------------</div>
      <?php }; ?>
    <?php endif; ?>

  <!-- ベストアンサーが選択されていると新規投稿できなくなる処理 -->
  <?php if($question['best_select_flg'] == 0): ?>
    <form method="POST" action="answer_create_conf.php">
      <input type="hidden" name="user_id" value="<?php echo $_SESSION['login_user']['user_id']; ?>">
      <input type="hidden" name="question_id" value="<?php echo $question['question_id'] ?>">
      <textarea placeholder="ここに返信を入力してください" name="a_message"></textarea>
      <input type="submit">
    </form>
  <?php endif; ?>

  <button type="button" onclick="location.href='question_search.php'">戻る</button>
  <!-- <script>
    //URLから引数に入っている値を渡す処理
function get_param(name, url) {
  if (!url) url = window.location.href;
  name = name.replace(/[\[\]]/g, "\\$&");
  var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
      results = regex.exec(url);
  if (!results) return null;
  if (!results[2]) return false;
  return decodeURIComponent(results[2].replace(/\+/g, " "));
}


$(document).on('click','.like_btn',function(e){
  e.stopPropagation();
  var $this = $(this),
  user_id = get_param('user_id'),
  like_id = get_param('like_id'),
  answer_id = get_param('answer_id');
  console.log(like_id);
  $.ajax({
    type: 'POST',
    url: '../view/question_disp.php',
    dataType: 'json',
    data: { user_id: user_id,
      like_id: like_id,
      answer_id: answer_id}
    }).done(function(data){
      $('.result').json(data);
      location.reload();
    }).fail(function() {
      location.reload();
    });
});
  </script> -->
</body>

