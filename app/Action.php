<?php
namespace Novus;

require_once __DIR__ . "/../config/def.php";
require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/Log.php";

// ベースクラス
class Action
{
    // ユーザー情報取得系
    const QUERY_MEMBER_REF = "SELECT user_id,name,tel,name,password,email,icon,q_disp_flg,level,exp,comment,pre_level,pre_exp FROM users WHERE user_id=:user_id";
    const QUERY_MEMBERLIST_IDS = "SELECT user_id,name,tel,name,password,email,icon,q_disp_flg,level,exp,comment,pre_level,pre_exp FROM users WHERE user_id IN (%s)";

    protected $member;
    protected $conn;

    public function __construct($mode = 1)
    {
        try {
            $this->begin($mode);
        } catch (\Exception $e) {
            Log::error($e);
            echo $e;
        }
    }

    // ページ読み出し処理(各ページの最初に呼び出す)
    // ページ表示不要のリクエストは,mode=1にして呼ぶ。
    public function begin($mode = 0)
    {
        session_start();

        // DB接続
        $this->conn = Database::getInstance();

        // ユーザー情報をmemberに入れる
        if (isset($_SESSION['login_user'])) {
            $this->member = $_SESSION['login_user'];
        }

        // 外部サイトからフレームでのページの読み込みを制限
        header("X-FRAME-OPTIONS: DENY");

        if ($mode == 0) {
            $this->printHeader();
        }
    }

    // ログイン中か判定
    public function checkLogin() {
        if (isset($_SESSION["USER_ID"]) === false && isset($_SESSION["login_user"]) === false) {
            header('Location: '. DOMAIN .'/public/userLogin/form.php');
            // クッキーの破棄
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params["path"],$params["domain"], $params["secure"], $params["httponly"]);
            }
            exit;
        }
    }

    // メンバーのIDを返す
    public function getMemberId() {
        return $this->member['user_id'];
    }
    // メンバーの名前を返す
    public function getMemberIcon() {
        return $this->member['icon'];
    }
    // メンバーの名前を返す
    public function getMemberName() {
        return $this->member['name'];
    }
    // メンバーのメールアドレスを返す
    public function getMemberEmail() {
        return $this->member['email'];
    }

    // userIdからユーザ情報を取得
    protected function memberRef($userid)
    {
        try {
            $stmt = $this->conn->prepare(self::QUERY_MEMBER_REF);
            $stmt->bindValue(':user_id', $userid);
            $result = $stmt->execute();
        } catch (\Exception $e) {
            Log::error($e);
            echo $e;
        }

        if (!$result) {
            return NULL;
        }

        $member = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $member;
    }

    // 特定の連想配列$userからIDを取り出してユーザ情報のマップを作成
    protected function memberMap($users, $idkey)
    {
        $members = [];
        $ids = [];
        $dupMap = []; // duplicate

        if (count($users) === 0) {
            return $members; 
        }

        foreach ($users as $user) { 
            if (isset($dupMap[$user[$idkey]])) {
                // user_idが重複している時はスキップ
                continue;
            }
            $dupMap[$user[$idkey]] = 1; //重複分をまとめる
            $ids[] = $user[$idkey]; //各記事のuser_idを取得
        }
        $inClause = substr(str_repeat(',?', count($ids)), 1); // $idsの数だけ『,?』を取得

        try {
            // 非重複分だけメンバー情報取得
            $stmt = $this->conn->prepare(sprintf(self::QUERY_MEMBERLIST_IDS, $inClause));
            $result = $stmt->execute($ids);
        } catch (\Exception $e) {
            Log::error($e);
            echo $e;
        }

        if ($result) {
            while ($mem = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $members[$mem['user_id']] = $mem; //表示中のユーザー情報
            }
        }
        return $members; // user_idとユーザ情報の連想配列
    }

    // 開始タグ
    public function printHeader() {
        echo '<!DOCTYPE html>';
        echo '<html lang="ja">';
        echo '<head>';
        echo '<meta charset=UTF-8" http-equiv="Content-Type">';
        echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<link rel="stylesheet" href="' . DOMAIN . '/public/css/novus.css?ver=' . VERSION . '">';
        echo '<link rel="stylesheet" href="' . DOMAIN . '/public/css/jquery.datetimepicker.css">';
        echo '<link rel="stylesheet" href="' . DOMAIN . '/public/css/bootstrap-4.4.1.css">';
        echo '<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.3/css/all.css" integrity="sha384-SZXxX4whJ79/gErwcOYf+zWLeJdY/qpuqC4cAa9rOGUstPomtqpuNWT9wdPEn2fk" crossorigin="anonymous">';
        echo '<script src="https://kit.fontawesome.com/3f20c0ff36.js" crossorigin="anonymous"></script>';
        echo '<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>';
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>';
        echo '<script src="' . DOMAIN . '/public/js/jquery-3.1.1.js"></script>';
        echo '<script src="' . DOMAIN . '/public/js/jquery.datetimepicker.full.js"></script>';
        echo '<script src="' . DOMAIN . '/public/js/qapi.js"></script>';
        echo '<script src="' . DOMAIN . '/public/js/bootstrap-4.4.1.js" defer></script>';
        echo '<script src="' . DOMAIN . '/public/js/marked.min.v1.js"></script>';
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
    public function printFooter($mode = 0) {
        if ($mode === 0) {
            echo '<hr/>';
            echo '<div class="row m-2">';
            if (isset($_SESSION['login_user'])) {
                echo '<div class="col-sm-10">';
                echo '<a class="btn btn-success m-2" href="' . DOMAIN . '/public/userLogin/home.php">ホーム画面へ</a>';
                echo '</div>';
            } else {
                echo '<div class="col-sm-10">';
                echo '<a class="btn btn-success m-2" href="' . DOMAIN . '/public/top/index.php">ホーム画面へ</a>';
                echo '</div>';
            }
            if (isset($_SESSION['login_user'])) {
                echo '<div class="col-sm-2">';
                echo '<a class="btn btn-primary m-2" href="' .DOMAIN. '/public/article/postEdit.php">投稿する</a>';
                echo '</div>';
            } else {
                echo '<div class="col-sm-2">';
                echo '<a class="btn btn-primary m-2" href="' .DOMAIN. '/public/userLogin/form.php">投稿する</a>';
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
