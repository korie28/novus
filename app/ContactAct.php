<?php
namespace Novus;

require_once "Action.php";
require_once "Log.php";
require_once "Token.php";
require_once "Utils.php";

class ContactAct extends Action
{
    const QUERY_CONTACT = "INSERT INTO contacts(name, email, title, contents) VALUES(:name, :email, :title, :contents)";

    public function __construct($mode = -1)
    {
        try {
            if ($mode >= 0) {
                $this->begin($mode);
            }
        } catch (\Exception $e) {
            Log::error($e);
            echo $e;
        }
    }

    // 入力値チェック & 入力データを返す
    private function validateInput()
    {
        // 入力値をチェック
        $_POST = Utils::checkInput($_POST);

        // 入力値を取得
        $name = trim(filter_input(INPUT_POST, 'name'));
        $email = trim(filter_input(INPUT_POST, 'email'));
        $email_check = trim(filter_input(INPUT_POST, 'email_check'));
        $title = trim(filter_input(INPUT_POST, 'title'));
        $contents = trim(filter_input(INPUT_POST, 'contents'));

        $error = [];

        //値の検証（入力内容が条件を満たさない場合はエラーメッセージを配列 $error に設定）
        if ($name == trim('')) {
            $error['name'] = '*お名前は必須項目です。';
        //制御文字でないことと文字数をチェック
        } elseif (preg_match('/\A[[:^cntrl:]]{1,30}\z/u', $name) == 0) {
            $error['name'] = '*お名前は30文字以内でお願いします。';
        }

        if ($email == '') {
            $error['email'] = '*メールアドレスは必須です。';
        } elseif (preg_match('/\A[[:^cntrl:]]{1,200}\z/u', $email) == 0) {
            $error['email'] = '*メールアドレスは200文字以内でお願いします。';
        }

        //メールアドレスを正規表現でチェック
        $pattern = '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/uiD';
        if (!preg_match($pattern, $email)) {
            $error['email'] = '*メールアドレスの形式が正しくありません。';
        }

        if ($email_check == '') {
            $error['email_check'] = '*確認用メールアドレスは必須です。';
        } else { //メールアドレスを正規表現でチェック
            if ($email_check !== $email) {
                $error['email_check'] = '*メールアドレスが一致しません。';
            }
        }

        if ($title == '') {
            $error['title'] = '*タイトルは必須項目です。';
        //制御文字でないことと文字数をチェック
        } elseif (preg_match('/\A[[:^cntrl:]]{1,150}\z/u', $title) == 0) {
            $error['title'] = '*タイトルは150文字以内でお願いします。';
        }

        if ($contents == '') {
            $error['contents'] = '*内容は必須項目です。';
        //制御文字（タブ、復帰、改行を除く）でないことと文字数をチェック
        } elseif (preg_match('/\A[\r\n\t[:^cntrl:]]{1,1500}\z/u', $contents) == 0) {
            $error['contents'] = '*内容は1500文字以内でお願いします。';
        }
        // 分割代入用
        return [
            [
                'name' => $name,
                'email' => $email,
                'email_check' => $email_check,
                'title' => $title,
                'contents' => $contents,
            ],
            $error
        ];
    }

    // 問い合わせフォームトップページ画面処理
    public function index()
    {
        try {
            // トークン生成
            Token::regenerate();
            // 書き直しデータ
            [$oldInputs, $inputErrors] = $this->getOldInputWithError(); //分割代入
            // ログイン時のデフォルト入力値設定
            if (!isset($oldInputs['name']) && isset($_SESSION["login_user"])) {
                $oldInputs['name'] = $this->getMemberName();
            }
            if (!isset($oldInputs['email']) && isset($_SESSION["login_user"])) {
                $oldInputs['email'] = $this->getMemberEmail();
            }
            // 一度画面へ表示したら不要のためクリア
            $this->clearOldInputWithError();
        } catch (\Exception $e) {
            Log::error($e);
            echo $e;
        }
        return [
            $oldInputs,
            $inputErrors
        ];
    }

    // 問い合わせフォーム確認ページ処理
    public function confirm()
    {
        try {
            // トークン生成
            Token::validate();
            // 入力データ取得
            [$inputs, $errors] = $this->validateInput();
            // 入力内容にエラーがある場合、トップへリダイレクト
            if (!empty($errors)) {
                $this->storeOldInputWithError($inputs, $errors);
                $this->redirectTop();
            }
        } catch (\Exception $e) {
            Log::error($e);
            echo $e;
        }
        return $inputs;
    }

    // 問い合わせフォーム完了ページ処理
    public function complete()
    {
        try {
            // トークン生成
            Token::validate();
            
            [$inputs, $errors] = $this->validateInput();
            // 入力内容にエラーがある場合、トップへリダイレクト
            if (!empty($errors)) {
                $this->storeOldInputWithError($inputs, $errors);
                $this->redirectTop();
            }
            // 戻るボタン処理の場合、トップへリダイレクト
            $action = filter_input(INPUT_POST, 'action');
            if ($action !== "send") {
                $this->storeOldInput($inputs);
                $this->redirectTop();
            }
            //DB登録
            $this->postarticle($inputs['name'], $inputs['email'], $inputs['title'], $inputs['contents']);
            // 二重送信防止
            Token::regenerate();
        } catch (\Exception $e) {
            Log::error($e);
            return false;
        }
        return true;
    }

    // セッショントークンの取得
    public function getToken()
    {
        return $_SESSION["token"];
    }

    // 入力内容送信
    private function postarticle($name, $email, $title, $contents)
    {
        try {
            $stmt = $this->conn->prepare(self::QUERY_CONTACT);
            $stmt->bindValue("name", $name, \PDO::PARAM_STR);
            $stmt->bindValue("email", $email, \PDO::PARAM_STR);
            $stmt->bindValue("title", $title, \PDO::PARAM_STR);
            $stmt->bindValue("contents", $contents, \PDO::PARAM_STR);
            $stmt->execute();
        } catch (\Exception $e) {
            Log::error($e);
            echo $e;
        }
    }

    // リダイレクト処理
    private function redirectTop()
    {
        header("Location: " . DOMAIN . "/public/contact/index.php");
        exit;
    }

    // 問い合わせフォームで入力された値をセッションへの保存
    private function storeOldInput($inputs)
    {
        // 問い合わせフォームで入力された値を格納
        $_SESSION['old_input'] = [
            'name' => $inputs['name'],
            'email' => $inputs['email'],
            'email_check' => $inputs['email_check'],
            'title' => $inputs['title'],
            'contents' => $inputs['contents'],
        ];
    }

    // 問い合わせフォームで入力された値とエラーメッセージをセッションへの保存
    private function storeOldInputWithError($inputs, $errors)
    {
        $this->storeOldInput($inputs);
        $_SESSION['input_error'] = $errors;
    }

    // セッションより問い合わせフォームで入力された値とエラーメッセージを取得
    private function getOldInputWithError()
    {
        return [
            $_SESSION['old_input'] ?? [],
            $_SESSION['input_error'] ?? [],
        ];
    }

    // セッションへ格納されている問い合わせフォームで入力された値とエラーメッセージを削除
    private function clearOldInputWithError()
    {
        unset($_SESSION['old_input']);
        unset($_SESSION['input_error']);
    }

    public function printHeader()
    {
        echo '<!DOCTYPE html>';
        echo '<html lang="ja">';
        echo '<head>';
        echo '<meta charset=UTF-8" http-equiv="Content-Type">';
        echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<meta name="format-detection" content="telephone=no">';
        echo '<link rel="stylesheet" href="' . DOMAIN . '/public/css/bootstrap-4.4.1.css">';
        echo '<link href="' . DOMAIN . '/public/css/contact.css" rel="stylesheet">';
        echo '<script src="' . DOMAIN . '/public/js/jquery-3.1.1.js" defer></script>';
        echo '<script src="' . DOMAIN . '/public/contact/js/script.js" defer></script>';
        echo '<script src="' . DOMAIN . '/public/js/bootstrap-4.4.1.js" defer></script>';
        echo '<title>' . SYSTITLE . '</title>';
        echo '</head>';
        echo '<body>';
        echo '<header>';
        if (isset($_SESSION["login_user"])) {
            echo '<div class="navbar bg-dark text-white">';
            echo '<a href="' . DOMAIN . '/public/userLogin/home.php" class="navtext h2 text-white text-decoration-none">novus</a>';
            echo '<ul class="nav justify-content-center">';
            echo '<li class="nav-item">';
            echo '<form type="hidden" action="mypage.php" method="POST" name="mypage">';
            echo '<a class="nav-link small text-white" href="' . DOMAIN . '/public/myPage/index.php">マイページ</a>';
            echo '<input type="hidden">';
            echo '</form>';
            echo '</li>';
            echo '<li>';
            echo '<li id="li"><a class="nav-link active small text-white" href="' . DOMAIN . '/public/userLogin/home.php">TOPページ</a></li>';
            echo '<li id="li"><a class="nav-link small text-white" href="' . DOMAIN . '/public/todo/index.php">TO DO LIST</a></li>';
            echo '<li id="li"><a class="nav-link small text-white" href="' . DOMAIN . '/public/myPage/qHistory.php">【履歴】質問</a></li>';
            echo '<li id="li"><a class="nav-link small text-white" href="' . DOMAIN . '/public/myPage/aHistory.php">【履歴】記事</a></li>';
            echo '<li id="li"><a class="nav-link small text-white" href="' . DOMAIN . '/public/userLogin/logout.php?user_id=' . $_SESSION["login_user"]["user_id"] . '">ログアウト</a></li>';
            echo '</ul>';
            echo '</div>';
        } else {
            echo '<div class="navbar bg-dark text-white">';
            echo '<a href="' . DOMAIN . '/public/top/index.php" class="navtext h2 text-white text-decoration-none">novus</a>';
            echo '<label for="menu-btn" class="menu-icon"><span class="navicon"></span></label>';
            echo '<ul class="nav justify-content-center">';
            echo '<li id="li"><a class="nav-link active small text-white" href="' . DOMAIN . '/public/top/index.php">TOPページ</a></li>';
            echo '<li id="li"><a class="nav-link active small text-white" href="' . DOMAIN . '/public/question/index.php">質問ページ</a></li>';
            echo '<li id="li"><a class="nav-link active small text-white" href="' . DOMAIN . '/public/article/index.php">記事ページ</a></li>';
            echo '</ul>';
            echo '</div>';
        }
        echo '</header>';
        echo '<main><div class="container">';
    }

    // ページ表示がないファイルは、mode=1で呼ぶ
    public function printFooter($mode = 0)
    {
        if ($mode == 0) {
            // echo '<hr/>';
            echo '<div class="row m-2">';
            if (isset($_SESSION['login_user'])) {
                echo '<div class="col-sm-9"></div>';
                echo '<div class="col-sm-3">';
                echo '<a class="btn btn-success m-2" href="' . DOMAIN . '/public/userLogin/home.php">ホーム画面へ</a>';
                echo '</div>';
            } else {
                echo '<div class="col-sm-9"></div>';
                echo '<div class="col-sm-3">';
                echo '<a class="btn btn-success m-2" href="' . DOMAIN . '/public/top/index.php">ホーム画面へ</a>';
                echo '</div>';
            }
            echo '</div>';
        }
        echo '</div></main>';
        echo '<hr><footer class="h-10">';
        echo '<div class="footer-item text-center">';
        echo '<h4>novus</h4>';
        echo '<ul class="nav nav-pills nav-fill">';
        echo '<li class="nav-item">';
        echo '<a class="nav-link small" href="' . DOMAIN . '/public/article/index.php">記事</a>';
        echo '</li>';
        echo '<li class="nav-item">';
        echo '<a class="nav-link small" href="' . DOMAIN . '/public/question/index.php">質問</a>';
        echo '</li>';
        echo '<li class="nav-item">';
        echo '<a class="nav-link small" href="' . DOMAIN . '/public/bookApi/index.php">本検索</a>';
        echo '</li>';
        echo '<li class="nav-item">';
        echo '<a class="nav-link small" href="' . DOMAIN . '/public/contact/index.php">お問い合わせ</a>';
        echo '</li>';
        echo '</ul>';
        echo '</div>';
        echo '<p class="text-center small mt-2">Copyright (c) HTMQ All Rights Reserved.</p>';
        echo '</footer>';
        echo '</body>';
        echo '</html>';
    }
}
