<?php
require_once __DIR__ . "/../../app/ContactAct.php";
require_once __DIR__ . '/../../app/Utils.php';

use Novus\ContactAct;
use Novus\Utils;

$act = new ContactAct(1);

// 入力欄の初期値、及びエラーメッセージ取得
[$inputs, $errors] = $act->index();

// ヘッダー出力
$act->printHeader();
?>

<div class="m-2">
    <h1 class="mt-5 text-center font-weight-bold fs-2">お問い合わせフォーム</h1>
    <form id="form" class="validationForm mt-4" method="post" action="confirm.php" novalidate>
        <div class="offset-sm-3">
            <div class="form-group">
                <label for="name">*お名前
                    <span class="error-php"><?php echo Utils::h($errors['name'] ?? ''); ?></span>
                </label>
                <input type="text" class="required maxlength form-control" data-maxlength="30" id="name" name="name" placeholder="お名前" data-error-required="入力は必須です" value="<?php echo Utils::h($inputs['name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="email">*Email
                    <span class="error-php"><?php echo Utils::h($errors['email'] ?? ''); ?></span>
                </label>
                <input type="email" class="required maxlength pattern form-control" data-maxlength="200" data-pattern="email" id="email" name="email" placeholder="Email" data-error-required="入力は必須です" data-error-pattern="形式が正しくありません" value="<?php echo Utils::h($inputs['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="email_check">*Email（確認用）
                    <span class="error-php"><?php echo Utils::h($errors['email_check'] ?? ''); ?></span>
                </label>
                <input type="email" class="form-control maxlength equal-to required" data-maxlength="200" data-equal-to="email" data-error-equal-to="メールアドレスが異なります<br>" data-error-required="入力は必須です" id="email_check" name="email_check" placeholder="Email（確認用）" value="<?php echo Utils::h($inputs['email_check'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="title">*タイトル
                    <span class="error-php"><?php echo Utils::h($errors['title'] ?? ''); ?></span>
                </label>
                <input type="text" class="required maxlength form-control" data-maxlength="150" id="subject" name="title" placeholder="タイトル" data-error-required="入力は必須です" value="<?php echo Utils::h($inputs['title'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="contents">*お問い合わせ内容
                    <span class="error-php"><?php echo Utils::h($errors['contents'] ?? ''); ?></span>
                </label>
                <textarea class="required maxlength showCount form-control" data-maxlength="1500" id="contents" name="contents" placeholder="お問い合わせ内容" rows="3"><?php echo Utils::h($inputs['contents'] ?? ''); ?></textarea>
            </div>
        </div>
        <div class="text-center">
            <input type="hidden" name="token" value="<?php echo $act->getToken(); ?>">
            <button name="submitted" type="submit" class="btn btn-primary text-center">確認する</button>
        </div>
    </form>
</div>
<?php $act->printFooter(); ?>
