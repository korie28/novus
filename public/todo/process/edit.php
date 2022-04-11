
<?php
require_once "../../../app/TodoAct.php";
require_once '../../../app/Token.php';

use Novus\TodoAct;
use Novus\Token;
use Novus\Utils;

$act = new ToDoAct();
$act->begin(1);

// ログインチェック
$act->checkLogin();

// トークンチェック
Token::validate();

$editTodoId = filter_input(INPUT_POST, 'editTodoId');
$editTodoTitle = filter_input(INPUT_POST, 'editTodoTitle');
$editTodoDt = filter_input(INPUT_POST, 'editTodoDt');

if (Utils::mbTrim($editTodoTitle) === "") {
    // 何も入力されていない時(スペース入力も)
    header('Location: ' . DOMAIN . '/public/todo/index.php?errSignal=noTitle');
    exit;
} elseif (!Utils::isStrLen($editTodoTitle, 100)) {
    // 範囲外
    header('Location: ' . DOMAIN . '/public/todo/index.php?errSignal=invalidTitle');
    exit;
}

if (!Utils::checkDatetimeFormat($editTodoDt)) {
    // 日付フォーマットが違う
    header('Location: ' . DOMAIN . '/public/todo/index.php?errSignal=invalidformatdt');
    exit;
}

$act->edit($editTodoId, $editTodoTitle, $editTodoDt);

// 二重送信防止
Token::regenerate();

// todo一覧へリダイレクト
header('Location: ' . DOMAIN . '/public/todo/index.php');
