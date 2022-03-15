<?php

    //DBファイルを読み込む
    require("./dbconnect.php");

    //変数の初期化
    $error_message = array();
    $pdo = null;
    $option = null;

    session_start();
     
    if ($_SERVER["REQUEST_METHOD"] === "POST") {//フォームが送信された時

        if (!empty($_POST)) {
    
            /* 入力情報の不備を検知 */
            if ($_POST['name'] === "") {
                $error['name'] = "blank";
            }
    
            if ($_POST['tel'] === "") {
                $error['tel'] = "blank";
            }
    
            if ($_POST['password'] === "") {
                $error['password'] = "blank";
            }
    
    
            /* 電話番号の重複を検知 
            if (!isset($error)) {
                $member = $pdo->prepare('SELECT COUNT(*) as cnt FROM users WHERE tel=?');
                $member->execute(array(
                    $_POST['tel']
                ));
                $record = $member->fetch();
                if ($record['cnt'] > 0) {
                    $error['tel'] = 'duplicate';
                }
            }*/
         
            /* エラーがなければ次のページへ遷移 */
            if (!isset($error)) {
                $_SESSION['join'] = $_POST;   // フォームの内容をセッションで保存
                header('location: http://localhost/qandasite/core/check.php');   // check.phpへ移動
                exit();
            }
        }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="2.css" />
    <title>新規会員登録</title>
</head>

<body class="bg-secondary p-4 p-md-5">
    <form class="row g-3 bg-white p-2  p-md-5 shadow-sm" action="" method="post">
        <h1 class="my-3">アカウント作成</h1>
            <p class="my-2">当サービスを利用するために、次のフォームに必要事項をご記入ください。</p>

            <div class="row my-4">
                <label for="InputName" class="form-label">*Name</label>
                <div class="md-3">
                    <input type="text" class="form-control col-6" name="name">
                    <?php if (!empty($error["name"]) && $error['name'] === 'blank'): ?>
                        <p class="error text-danger">＊名前を入力してください</p>
                    <?php endif ?>
                </div>
            </div>

            <div class="row my-4">
                <label for="InputPhone" class="form-label">*Phone</label>
                <div class="md-3">
                    <input type="text" class="form-control col-6" name="tel">
                    <?php if (!empty($error["tel"]) && $error['tel'] === 'blank'): ?>
                        <p class="error text-danger">＊電話番号を入力してください</p>
                    <?php elseif (!empty($error["tel"]) && $error['tel'] === 'duplicate'): ?>
                        <p class="error">＊この電話番号はすでに登録済みです</p>
                    <?php endif ?>
                </div>
            </div>
            
            <div class="row my-4">
                <label for="InputEmail" class="form-label">Email</label>
                <div class="md-3">
                    <input type="email" class="form-control col-6" name="email">
                </div>
            </div>
            
            <div class="row my-4">
                <div class="md-3">
                   <p>*Password</p>
                   <input type="password" class="form-control col-4" id="inputPassword8" name="password">
                </div>
        </div>

        <div class="col-12 my-4">
<<<<<<< Updated upstream:entry.php
            <button type="submit" class="btn btn-primary">Sign up</button>
=======
            <input type="submit" class="btn btn-primary" value="Sign up">
>>>>>>> Stashed changes:entry_form.php
        </div>
 
    </form>
</body>
</html>