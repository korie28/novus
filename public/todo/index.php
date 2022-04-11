<?php
require_once "../../app/TodoAct.php";
require_once '../../app/Token.php';
require_once '../../app/Utils.php';

use Novus\TodoAct;
use Novus\Token;
use Novus\Utils;

// ToDo一覧取得
$act = new ToDoAct();
$retInfo = $act->begin(); //ユーザー情報呼び出し
$retInfo = $act->get(); //todoの一覧取得

//ログインチェック
$act->checkLogin();

// Token生成
Token::create();

// 7日後（リマインドの日付の初期値に使用）
$remindDt = Utils::addDay(7, 1);

// エラー信号を代入
$errSignal = filter_input(INPUT_GET, 'errSignal');

// アイコンを取得
$icon = $act->getMemberIcon();
?>

<div class="row m-2">
    <div class="col-sm-8"></div>
    <?php if (isset($_SESSION['login_user'])) : ?>
        <a href="<?php echo DOMAIN ?>/public/mypage/index.php" class="d-flex align-items-center col-sm-4 text-dark">
            <?php echo (isset($icon) ? '<img src="' . DOMAIN . '/public/top/img/' . $icon . '" class="mr-1">' : '<img src="' . DOMAIN . '/public/top/img/sample_icon.png" class="mr-1">') ?>
            <?php echo $act->getMemberName(); ?> さん
        </a>
    <?php endif; ?>
</div>

<h5>Todo</h5>
<form method="POST" class="form-horizontal" name="addForm" action="<?php echo DOMAIN . '/public/todo/process/add.php'; ?>">
    <div class="row m-2">
        <div class="col-sm-6">
            <input type="text" class="form-control" id="newTodoTitle" name="newTodoTitle" value="" maxlength="101">
        </div>
        <div class="col-sm-3">
            <input type="text" class="form-control dateTimePickerForm" id="newTodoDt" name="newTodoDt" maxlength="16" value="<?php echo $remindDt; ?>">
        </div>
        <div class="col-sm-3">
            <input type="hidden" name="token" value="<?php echo $_SESSION["token"]; ?>">
            <div class="btn btn-primary" onClick="onAddToDo();">追加</div>
        </div>
    </div>
</form>

<h5>やること:</h5>
<table class="table table-striped" style="overflow: hidden; overflow-wrap: break-word;">
    <tr>
        <th style="width: 5%">#</th>
        <th style="width: 45%">Task</th>
        <th style="width: 20%">DeadLine</th>
        <th style="width: 30%">Actions</th>
    </tr>
    <?php
    // activeステータス分を表示
    $idx = 1;
    foreach ($retInfo['activeList'] as $active) {
        $escapeTitle = Utils::h($active['title']); //タイトルだけ取得
        echo '<tr><td>' . $idx . '</td><td style="word-break: break-all;">' . $escapeTitle . '</td><td>' . Utils::dayFormat($active['remind_date']) . '</td><td>';
        echo '<span class="btn btn-link done" todoId="' . $active['todo_id'] . '">Done</span>';
        echo '<span class="btn btn-link edit" todoId="' . $active['todo_id'] . '" todoTitle="' . $escapeTitle . '" tododt="' . Utils::dayFormat($active['remind_date']) . '">Edit</span>';
        echo '<span class="btn btn-link delete" todoId="' . $active['todo_id'] . '">Delete</span>';
        echo '</td></tr>';
        $idx++;
    }
    if (count($retInfo['activeList']) === 0) {
        echo '<tr><td colspan="4" class="text-center">1件もありません</td></tr>';
    }
    ?>
</table>

<h5>終わったこと:</h5>
<table class="table table-striped" style="overflow: hidden; overflow-wrap: break-word;">
    <tr>
        <th style="width: 5%">#</th>
        <th style="width: 45%">Task</th>
        <th style="width: 20%">DeadLine</th>
        <th style="width: 30%">Actions</th>
    </tr>
    <?php
    // finishステータス分を表示
    $idx = 1;
    foreach ($retInfo['finList'] as $fin) {
        $escapeTitle = Utils::h($fin['title']); //タイトルだけ取得
        echo '<tr><td>' . $idx . '</td><td style="word-break: break-all;">' . $escapeTitle
            . '</td><td>' . Utils::dayFormat($fin['remind_date']) . '</td><td>';
        echo '<span class="btn btn-link return" todoId="' . $fin['todo_id'] . '">Return</span>';
        echo '<span class="btn btn-link edit" todoId="' . $fin['todo_id'] . '" todoTitle="' . $escapeTitle
            . '" tododt="' . Utils::dayFormat($fin['remind_date']) . '">Edit</span>';
        echo '<span class="btn btn-link delete" todoId="' . $fin['todo_id'] . '">Delete</span>';
        echo '</td></tr>';
        $idx++;
    }
    if (count($retInfo['finList']) == 0) {
      echo '<tr><td colspan="4" class="text-center">1件もありません</td></tr>';
    }
    ?>
</table>

<!-- 編集ダイアログ(ブートストラップで表示)  -->
<div class="modal fade" id="demoNormalModal" tabindex="-1" role="dialog" aria-labelledby="modal" aria-hidden="true">
    <form method="POST" class="form-horizontal" name="editForm" action="<?php echo DOMAIN . '/public/todo/process/edit.php'; ?>">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="demoModalTitle">ToDo編集</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row m-2">
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="editTodoTitle" name="editTodoTitle" value="" maxlength="200">
                        </div>
                        <div class="col-sm-4">
                            <input type="text" class="form-control dateTimePickerForm" id="editTodoDt" name="editTodoDt" maxlength="16" value="">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
                    <button type="button" class="btn btn-primary" id="updateToDo">更新</button>
                    <input type="hidden" id="editTodoId" name="editTodoId" value="">
                    <input type="hidden" name="token" value="<?php echo $_SESSION["token"]; ?>">
                </div>
            </div>
        </div>
    </form>
</div>
<!-- /編集ダイアログ  -->

<script type="text/javascript">
    // 追加ボタンを押した
    function onAddToDo() {
        const newTodoTitle = document.getElementById('newTodoTitle').value;
        if ($.trim(newTodoTitle) === "") {
            onShow('何も入力されていません');
            return;
        }
        if (!isStrLen(newTodoTitle, 1, 100)) {
            onShow('100文字以内で入力してください');
            return;
        }
        // 送信
        document.addForm.submit();
    }

    // Editボタンを押したとき
    function onEdit() {
        // 編集前の値を取得
        var todoId = $(this).attr('todoId');
        var title = $(this).attr('todoTitle');
        var tododt = $(this).attr('tododt');

        // エディットに設定
        $('#editTodoTitle').val(title);
        $('#editTodoDt').val(tododt);
        $('#editTodoId').val(todoId);

        // 編集モーダル表示
        $('#demoNormalModal').modal('show');

        // 編集が確定したとき
        $('#updateToDo').off().click(function() {
            const editTodoTitle = document.getElementById('editTodoTitle').value;
            if ($.trim(editTodoTitle) === "") {
                onShow('何も入力されていません');
                return;
            }
            if (!isStrLen(editTodoTitle, 1, 100)) {
                onShow('100文字以内で入力してください');
                return;
            }
            // 送信
            document.editForm.submit();
        });
    }

    // 削除ボタンを押した
    function onDelete() {
        var todoId = $(this).attr('todoId');
        swal({
            text: '削除してもよろしいですか？',
            icon: 'warning',
            buttons: true,
            dangerMode: true
        }).then(function(isConfirm) {
            if (isConfirm) {
                var $data = 'todoId=' + todoId + '&token=<?php echo $_SESSION["token"]; ?>';
                // ajax呼び出し
                formApiCallback('todo/process/delete.php', $data, function(result) {
                    // 一覧を再読み込み
                    jumpApi('todo/index.php');
                });
            }
        });
    }

    // ステータス更新(done)
    function toggleFinish() {
        var todoId = $(this).attr('todoId');
        var $data = 'state=finish&todoId=' + todoId + '&token=<?php echo $_SESSION["token"]; ?>';
        // ajax呼び出し
        formApiCallback('todo/process/toggle.php', $data, function(result) {
            // 一覧を再読み込み
            jumpApi('todo/index.php');
        });
    }
    // ステータス更新(return)
    function toggleActive() {
        var todoId = $(this).attr('todoId');
        var $data = 'state=active&todoId=' + todoId + '&token=<?php echo $_SESSION["token"]; ?>';
        // ajax呼び出し
        formApiCallback('todo/process/toggle.php', $data, function(result) {
            // 一覧を再読み込み
            jumpApi('todo/index.php');
        });
    }

    // 初期化処理(カレンダー)
    $(function() {
        // 日付フィールドの初期化
        $('.dateTimePickerForm').datetimepicker({
            formatTime: 'H:i',
            formatDate: 'd.m.Y',
            language: 'ja'
        });

        // ボタン/リンク
        $('.btn.btn-link.done').click(toggleFinish);
        $('.btn.btn-link.return').click(toggleActive);
        $('.btn.btn-link.edit').click(onEdit);
        $('.btn.btn-link.delete').click(onDelete);

        <?php
        if ($errSignal) {
            // 更新に失敗したとき
            if ($errSignal == 'noTitle') {
                echo 'onShow("何も入力されていません");';
            } elseif ($errSignal == 'invalidTitle') {
                echo 'onShow("100文字以内で入力してください");';
            } elseif ($errSignal == 'invalidformatdt') {
                echo 'onShow("リマインドの日付に誤りがあります");';
            }
        }
        ?>
    });
</script>

<?php
$act->printFooter();
?>