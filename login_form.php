<?php
    session_start();
    require_once 'classes/UserLogic.php';

    $result = UserLogic::checkLogin();
    if ($result){
        header('location: login_top.html');
        return;
    }

    $err = $_SESSION;

    $_SESSION = array();
    session_destroy();
?>

<!--ログインフォーム-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="2.css" />
    <title>ログインフォーム</title>
</head>

<body class="h-100 bg-secondary p-4 p-md-5">
    <form class="row g-3 bg-white p-2 p-md-5 shadow-sm" action="login.php" method="POST">
        <?php if (isset($err['msg'])) : ?>
            <p><?php echo $err['msg']; ?></p>
        <?php endif; ?>
        <h1 class="my-3">ログインフォーム</h1>
        <p class="my-2">下記項目を記入して下さい。</p>
        <!--名前を記入-->
        <div class="row my-4">
            <label for="name" class="form-label">Name</label>
            <div class="md-3">
                <input type="text" class="form-control col-10" name="name" required>
                <?php if (isset($err['name'])) : ?>
                    <p><?php echo $err['name']; ?></p>
                <?php endif; ?>
            </div>
        </div>
        <!--電話番号を記入-->
        <div class="row my-4">
            <label for="tel" class="form-label">Phone</label>
            <div class="md-3">
                <input type="text" class="form-control col-10" name="tel" required>
                <?php if (isset($err['tel'])) : ?>
                    <p><?php echo $err['tel']; ?></p>
                <?php endif; ?>
            </div>
        </div>
        <!--メアドを記入-->
        <div class="row my-4">
            <label for="email" class="form-label">Email</label>
            <div class="md-3">
                <input type="email" class="form-control col-10" name="email">
                <?php if (isset($err['email'])) : ?>
                    <p><?php echo $err['email']; ?></p>
                <?php endif; ?>
            </div>
        </div>
        <!--パスワードを記入-->
        <div class="row my-4">
            <label for="password" class="form-label">*Password</label>
            <div class="md-3">
               <input type="password" class="form-control col-6" id="inputPassword8" name="password" required>
               <?php if (isset($err['password'])) : ?>
                    <p><?php echo $err['password']; ?></p>
                <?php endif; ?>
            </div>
        </div>
        <!--送信ボタン-->
        <div class="col-12 my-4 text-center">
            <p><input type="submit" class="btn btn-primary" value="Log in"></p>
            <!--エントリーへのリンク-->
            <a href = "entry_form.php">新規登録はこちら</a>
        </div>
    </form>
</body>
</html>