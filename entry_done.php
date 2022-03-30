<<<<<<< Updated upstream
=======
<?php
    session_start();

    //ファイルの読み込み
    require_once 'classes/UserLogic.php';
    

    $name = filter_input(INPUT_POST, 'name');
    $tel = filter_input(INPUT_POST, 'tel');
    $email = filter_input(INPUT_POST, 'email');
    $password = filter_input(INPUT_POST, 'password');
    $password_conf = filter_input(INPUT_POST, 'password_conf');

    //error
    $err = [];

    if(!$name = filter_input(INPUT_POST, 'name')) {
        $err[] = '名前を入力してください';
    }
    if(!$tel = filter_input(INPUT_POST, 'tel')) {
        $err[] = '電話番号を入力してください';
    }
    if(!$email = filter_input(INPUT_POST, 'email')) {
        $err[] = 'メールアドレスを入力してください';
    }
    $password = filter_input(INPUT_POST, 'password');
    //正規表現
    if (!preg_match("/\A[a-z\d]{4,20}+\z/i", $password)) {
        $err[] = 'パスワードは英数字4文字以上20文字以下にしてください';
    }
    $password_conf = filter_input(INPUT_POST, 'password_conf');
    if ($password !== $password_conf) {
        $err[] = '確認用パスワードと異なっています';
    }

    if (count($err) === 0){
        //ユーザーを登録する処理
        $hasCreated = UserLogic::createUser($_POST);

        if(!$hasCreated){
            $err[] = '登録に失敗しました';
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
    <title>会員登録完了画面</title>
</head>

<body class="h-100 bg-secondary p-4 p-md-5">
    <div class = "container bg-white p-5">
        <?php if (count($err) > 0) :?>
            <?php foreach($err as $e) :?>
                <p><?php echo $e ?></p>
                <p><a href="entry_form.php" class="btn btn-primary">フォームに戻る</p> 
            <?php endforeach ?>
        <?php else :?>
            <div class="row align-items-start">
                <h1 class="my-3 h1">会員登録が完了しました</h1>
    <?php endif ?>
                <br><br>
                <a href="login_top.html"><button class="">トップページへ移動</button></a>
            </div>
        </div>

</body>
</html>
>>>>>>> Stashed changes
