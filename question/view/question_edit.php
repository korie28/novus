<?php

  session_start();

  //ファイルの読み込み
  require_once '../../classes/QuestionLogic.php';
  require_once '../../classes/CategoryLogic.php';
  require_once '../../classes/UserLogic.php';

  // ログインチェック
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

  // ボタン押下時の処理（成功でページ移動）
  if(isset($_POST['q_edit_conf'])){
    $_SESSION['q_data']['title'] = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
    $_SESSION['q_data']['category'] = filter_input(INPUT_POST, 'category');
    $_SESSION['q_data']['message'] = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS);
    $_SESSION['q_data']['question_id'] = filter_input(INPUT_POST, 'question_id');
    if(isset($_POST['question_image'])){
        $_SESSION['q_data']['question_image'] = filter_input(INPUT_POST, 'question_image', FILTER_SANITIZE_SPECIAL_CHARS);
    }else{
        $_SESSION['q_data']['question_image'] = null;
    }

    if(empty($_SESSION['q_data']['title'])) {
        $err['title'] = '質問タイトルを入力してください';
    }
    if(empty($_SESSION['q_data']['category'])) {
        $err['category'] = 'カテゴリを選択してください';
    }
    if(empty($_SESSION['q_data']['message'])) {
        $err['message'] = '本文を入力してください';
    }
    if(empty($_SESSION['q_data']['question_id'])) {
        $err['q_id'] = '質問IDが選択されていません';
    }
    if (count($err) === 0){
        header('Location: question_edit_comp.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>質問編集</title>
</head>

<body>
  <div>質問内容</div>
  <form method="POST" action="">
    <div>
      <?php if(isset($err['q_id'])): ?>
      <?php echo $err['q_id'] ?>
      <?php endif; ?>
    </div>
    <div>
      <?php if(isset($err['title'])): ?>
      <?php echo $err['title'] ?>
      <?php endif; ?>
    </div>
    <div>題名：
      <input type="text"
              name="title"
              value="<?php echo $question['title'] ?>"
              required>
    </div>
    <div>
      <?php if(isset($err['category'])): ?>
      <?php echo $err['category'] ?>
      <?php endif; ?>
    </div>
    <div>カテゴリ： 
      <select name="category"  required>
        <option></option>
        <option value="1">項目1</option>
        <?php foreach($categories as $value){ ?>
          <option 
            value="<?php echo $value['cate_id'] ?>"
            <?php if($value['cate_id'] == $question['cate_id']) : ?>
              selected
            <?php endif; ?>
          > 
            <?php echo $value['category_name'] ?>
          </option>
        <?php } ?>
      </select>
    </div>
    <div><?php if(isset($err['message'])): ?>
      <?php echo $err['message'] ?>
      <?php endif; ?>
    </div>
    <div>本文：
      <textarea name="message"><?php echo $question['message'] ?></textarea>
    </div>
    <div>添付</div>
    <div>※jpgもしくはpng形式にてお願いいたします。</div>
    <input type="file" name="question_image" accept="image/png, image/jpeg">
    <input type="hidden" name="question_id" value="<?php echo $question['question_id'] ?>">
    <input type="submit" name="q_edit_conf">
  </form>
  <button type="button" onclick="history.back()">戻る</button>
</body>
