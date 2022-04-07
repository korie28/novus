<?php
// ToDo編集
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

$editTodoId = filter_input(INPUT_POST, 'editTodoId', FILTER_SANITIZE_NUMBER_INT);
$editTodoTitle = filter_input(INPUT_POST, 'editTodoTitle', FILTER_SANITIZE_SPECIAL_CHARS);
$editTodoDt = filter_input(INPUT_POST, 'editTodoDt', FILTER_SANITIZE_SPECIAL_CHARS);

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

// ToDo編集
$act->edit($editTodoId, $editTodoTitle, $editTodoDt);

// ToDo一覧へリダイレクト
header('Location: ' . DOMAIN . '/public/todo/index.php');
