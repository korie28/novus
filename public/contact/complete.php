<?php
require_once __DIR__ . "/../../app/ContactAct.php";

use Novus\ContactAct;

$act = new ContactAct(1);

// 送信処理を実行し、結果を受け取る
$result = $act->complete();

$act->printHeader();
?>

<div class="offset-sm-2">
    <div class="d-flex align-items-center justify-content-center mt-5 fs-3 font-weight-bold">
        <?php if ($result) : ?>
        <p>
            送信しました。<br>
            お問い合わせありがとうございました。<br>
            ご入力いただいた内容を確認後、３営業日以内に返信致します。<br>
            <a href="index.php">
            トップへ戻る
            </a><br>
        </p>
        <?php else : ?>
        <p>
            送信に失敗しました。<br>
            しばらくしてもう一度お試しください。<br>
            ご迷惑をおかけして誠に申し訳ございません。<br>
            <a href="index.php">
            お問い合わせトップに戻る
            </a><br>
        </p>
        <?php endif; ?>
    </div>
</div>
<?php $act->printFooter(); ?>
