<?php
require_once __DIR__ . "/../../app/ContactAct.php";
require_once __DIR__ . "/../../app/Utils.php";

use Novus\ContactAct;
use Novus\Utils;

$act = new ContactAct(1);

// 入力値の検証⇒確認画面に表示する値を受け取る
$inputs = $act->confirm();

// ヘッダー出力
$act->printHeader();
?>

<div>
    <div class="mt-5">
        <h2 class="text-center font-weight-bold fs-2">入力内容の確認</h2>
    </div>
    <div class="offset-sm-2">
        <div>
            <p class="mt-4">
                以下の内容でよろしければ「送信する」を選択してください。<br>
                内容を変更する場合は「戻る」を選択して入力画面にお戻りください。
            </p>
        </div>
        <div class="table-responsive confirm_table">
            <table class="table table-bordered">
                <caption>ご入力内容</caption>
                <tr>
                    <th>お名前</th>
                    <td style="overflow: hidden; word-break: break-all;"><?php echo Utils::h($inputs['name']); ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td style="overflow: hidden; word-break: break-all;"><?php echo Utils::h($inputs['email']); ?></td>
                </tr>
                <tr>
                    <th>タイトル</th>
                    <td style="overflow: hidden; word-break: break-all;"><?php echo Utils::h($inputs['title']); ?></td>
                </tr>
                <tr>
                    <th>お問い合わせ内容</th>
                    <td class="text-break" style="overflow: hidden; word-break: break-all;"><?php echo nl2br(Utils::h($inputs['contents'])); ?></td>
                </tr>
            </table>
        </div>
    </div>
    <div>
        <form action="complete.php" method="post">
            <input type="hidden" name="name" value="<?php echo Utils::h($inputs['name']); ?>">
            <input type="hidden" name="email" value="<?php echo Utils::h($inputs['email']); ?>">
            <input type="hidden" name="email_check" value="<?php echo Utils::h($inputs['email_check']); ?>">
            <input type="hidden" name="title" value="<?php echo Utils::h($inputs['title']); ?>">
            <input type="hidden" name="contents" value="<?php echo Utils::h($inputs['contents']); ?>">
            <input type="hidden" name="token" value="<?php echo $act->getToken(); ?>">
            <div class="text-center">
                <button type="submit" class="btn btn-secondary text-center" name="action" value="back">戻る</button>
                <button type="submit" class="btn btn-primary" name="action" value="send">送信する</button>
            </div>
        </form>
    </div>
</div>
<?php $act->printFooter(); ?>