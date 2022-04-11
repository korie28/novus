<?php
// ToDo追加処理
require_once "../../../app/TodoAct.php";
require_once '../../../app/Token.php';
require_once '../../../app/Utils.php';

use Novus\TodoAct;
use Novus\Token;
use Novus\Utils;

$act = new ToDoAct();
$act->begin(1);

// ログインチェック
$act->checkLogin();

// トークンチェック
Token::validate();

$newTodoTitle = filter_input(INPUT_POST, 'newTodoTitle');
$newTodoDt = filter_input(INPUT_POST, 'newTodoDt');

if (Utils::mbTrim($newTodoTitle) === "") {
    // 何も入力されていない時(スペース入力も)
    header('Location: ' . DOMAIN . '/public/todo/index.php?errSignal=noTitle');
    exit;
} elseif (!Utils::isStrLen($newTodoTitle, 100)) {
    // 101文字以上を入力されたとき
    header('Location: ' . DOMAIN . '/public/todo/index.php?errSignal=invalidTitle');
    exit;
}

if (!Utils::checkDatetimeFormat($newTodoDt)) {
    // 日付フォーマットが違うとき
    header('Location: ' . DOMAIN . '/public/todo/index.php?errSignal=invalidformatdt');
    exit;
}

// ToDo追加
$act->add($newTodoTitle, $newTodoDt);

// 二重送信防止
Token::regenerate();

// ToDo一覧へリダイレクト
header('Location: ' . DOMAIN . '/public/todo/index.php');