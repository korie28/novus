<?php
namespace Qanda;

require_once __DIR__ . '/../config/def.php';
require_once __DIR__ . '/database.php';

// ユーザー情報取得系
define("QUERY_MEMBER", "SELECT USER_ID,NAME,TEL,NAME,PASSWORD,EMAIL,ICON,TW_USER,Q_DISP_FLG,LEVEL,EXP,COMMENT,PRE_LEVEL,PRE_EXP FROM users WHERE user_id=:user_id");
define("QUERY_MEMBER_REF", "SELECT USER_ID,NAME,TEL,NAME,PASSWORD,EMAIL,ICON,TW_USER,Q_DISP_FLG,LEVEL,EXP,COMMENT,PRE_LEVEL,PRE_EXP FROM users WHERE USER_ID=:user_id");
define("QUERY_MEMBER_TEL", "SELECT USER_ID,NAME,TEL,NAME,PASSWORD,EMAIL,ICON,TW_USER,Q_DISP_FLG,LEVEL,EXP,COMMENT,PRE_LEVEL,PRE_EXP FROM users WHERE TEL=:tel");
define("QUERY_MEMBERLIST_IDS", "SELECT USER_ID,NAME,TEL,NAME,PASSWORD,EMAIL,ICON,TW_USER,Q_DISP_FLG,LEVEL,EXP,COMMENT,PRE_LEVEL,PRE_EXP FROM users WHERE USER_ID IN (%s)");

// ユーザー情報更新系
define("INSERT_MEMBER", "INSERT INTO users (NAME,PASSWORD,EMAIL) VALUES (:name, :password, :email)");

// ベースクラス
class Action
{
  protected $member;
  protected $conn;

  public function __construct($mode = -1) {
    if ($mode >= 0) {
      $this->begin($mode);
    }
  }

  // ページ読み出し処理(各ページの最初に呼び出す)
  // セッションがない場合は、トップページへ遷移させる
  // ページ表示不要のリクエストは,mode=1にして呼ぶ。
  function begin($mode = 0) {
    session_start();

    // Cookie
    if (isset($_SESSION["USER_ID"]) == false && isset($_SESSION["login_user"]) == false) {
      // LOGIN PAGEへ
      header('Location: '. DOMAIN .'/top/login_form.php');
      exit;
    }

    // DB接続
    $this->conn = Database::getInstance();

    // ユーザー情報
    if (isset($_SESSION['login_user'])) {
    // セッションに全部ある場合
      $this->member = $_SESSION['login_user'];
    }
    else {
    // USER_IDのみの場合,DBから取得
      $userid = $_SESSION["USER_ID"];
      $stmt = $this->conn->prepare(QUERY_MEMBER);
      $stmt->bindValue(':user_id', $userid);
      $result = $stmt->execute();
      $this->member = $result ? $stmt->fetch(\PDO::FETCH_ASSOC): NULL;
    }

    // 外部サイトからフレームでのページの読み込みを制限
    header("X-FRAME-OPTIONS: DENY");

    if ($mode == 0) {
    // header,include,bodyまで出力
        $this->printHeader();
    }
  }

  // ページ読み出し処理(各ページの初期時に呼び出す)
  // セッション不要ページ用
  // ページ表示不要のリクエストは,mode=1にして呼ぶ。
  function begin_free($mode = 0) {
    // DB接続
    $this->conn = Database::getInstance();

    // 外部サイトからフレームでのページの読み込みを制限
    header("X-FRAME-OPTIONS: DENY");

    if ($mode == 0) {
    // header,include,bodyまで出力
      $this->printHeader();
    }
  }

  // ログイン中か判定
  function isLogin() {
    return isset($_SESSION["user_id"]);
  }

  // ログイン処理★
  function login($tel, $password) {
    session_start();

    // DB接続
    $this->conn = Database::getInstance();

    // telから user情報取得
    $stmt = $this->conn->prepare(QUERY_MEMBER_TEL);
    $stmt->bindValue(':tel', $tel);
    $result = $stmt->execute();
    $this->member = $result ? $stmt->fetch(\PDO::FETCH_ASSOC): NULL;
    if (! $this->member) {
      return 'AUTHERROR';
    }

    if (md5($password) == $this->member['PASSWORD']) {
      // 認証成功
      // セッションIDを新規に発行する
      session_regenerate_id(TRUE);
      $_SESSION['USER_ID'] = $this->member['USER_ID'];
      $_SESSION['login_user'] = $this->member;  // ユーザ情報をセッションにも保持
      return 'SUCCESS';
    }else{
      return 'AUTHERROR';
    }
  }

  // ログアウト処理★
  function logout() {
    session_start();

    if (isset($_SESSION['USER_ID']) == TRUE) {
      $errorMessage = "ログアウトしました。";
    } else {
      $errorMessage = "セッションがタイムアウトしました。";
    }

    // セッション変数のクリア
    $_SESSION = array();

    // クッキーの破棄
    if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000, $params["path"],$params["domain"], $params["secure"], $params["httponly"]);
    }

    // セッションクリア(制御演算子@ を付ける。エラー出力を非表示)
    @session_destroy();

    return $errorMessage;
  }

  // メンバー情報を返す
  function getMember() {
    return $this->member;
  }
  // メンバーの名前を返す
  function getMemberName() {
    return $this->member['name'];
  }
  // メンバーのIDを返す
  function getMemberId() {
    return $this->member['user_id'];
  }

  // userIdからユーザ情報を取得
  function memberref($userid) {
    $stmt = $this->conn->prepare(QUERY_MEMBER_REF);
    $stmt->bindValue(':user_id', $userid);
    $result = $stmt->execute();
    if (! $result) {
      return NULL;
    }
    $member = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $member;
  }
  // telからユーザ情報を取得
  function memberrefemail($email) {
    $stmt = $this->conn->prepare(QUERY_MEMBER_TEL);
    $stmt->bindValue(':email', $email);
    $result = $stmt->execute();
    if (! $result) {
      return NULL;
    }
    $member = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $member;
  }
  // 特定の連想配列$userから、IDを取り出して、ユーザ情報のマップを作成
  // 戻り値は user-id とユーザ情報の連想配列。
  function membermap($users, $idkey) {
    $members = array();
    if (count($users) == 0) {
    // リストが 0件
      return $members;
    }

    // where句の作成
    $ids = array();
    $dupmap = array();
    foreach ($users as $user) {
      if (isset($dupmap[$user[$idkey]])) {
        // user_idが重複しているので、スキップ
        continue;
      }
      $dupmap[$user[$idkey]] = 1;
      $ids[] = $user[$idkey];
    }
    $inClause = substr(str_repeat(',?', count($ids)), 1);

    // メンバー情報取得
    $stmt = $this->conn->prepare(sprintf(QUERY_MEMBERLIST_IDS, $inClause));
    $result = $stmt->execute($ids);
    if ($result) {
      while ($mem = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $members[$mem['USER_ID']] = $mem;
      }
    }

    return $members;
  }

    
    // footerを出力
    // ページ表示不要のリクエストは,mode=1にして呼ぶ。
    function end($mode = 0) {
      $domain = DOMAIN;

      if ($mode == 0) {
      // フッダー出力
        echo '<hr/>';
        echo '<div class="row m-2">';
        echo '<a class="btn btn-warning m-2" href="' . DOMAIN . '/public/article/home.php">記事一覧へ</a>';
        echo '<a class="btn btn-success m-2" href="../../top/userLogin/login_top.php">ホーム画面へ</a>';
        echo '</div>';
      }
      echo '</div></body>';
      echo '</html>';
    }
    // header,include,bodyまで出力する
    // SEO対策、javascript/cssを追加するときは、ここに追加。
    function printHeader() {
      echo '<!DOCTYPE html>';
      echo '<html lang="ja">';
      echo '<head>';
      echo '<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">';
      echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
      echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
      echo '<link rel="stylesheet" type="text/css" href="' . DOMAIN . '/public/CSS/qanda.css?ver=' . VERSION . '" />';
      echo '<link rel="stylesheet" type="text/css" href="' . DOMAIN . '/public/CSS/jquery.datetimepicker.css" media="screen" />';
      echo '<link rel="stylesheet" type="text/css" href="' . DOMAIN . '/public/CSS/bootstrap-4.4.1.css">';
      echo '<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.3/css/all.css" integrity="sha384-SZXxX4whJ79/gErwcOYf+zWLeJdY/qpuqC4cAa9rOGUstPomtqpuNWT9wdPEn2fk" crossorigin="anonymous">';
      echo '<link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">';
      echo '<link rel="stylesheet" href="https://unpkg.com/mavon-editor@2.7.4/dist/css/index.css">';//vue(マークダウン記法)
      echo '<script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>';
      echo '<script src="https://kit.fontawesome.com/3f20c0ff36.js" crossorigin="anonymous"></script>';
      echo '<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>';//vue
      echo '<script src="https://unpkg.com/mavon-editor@2.7.4/dist/mavon-editor.js"></script>';//vue(マークダウン記法)
      echo '<script src= "https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>';
      echo '<script src="' . DOMAIN . '/public/JS/jquery-3.1.1.js"></script>';
      echo '<script src="' . DOMAIN . '/public/JS/jquery.datetimepicker.full.js"></script>';
      echo '<script src="' . DOMAIN . '/public/JS/qapi.js?ver={$version}"></script>';
      echo '<script src="' . DOMAIN . '/public/JS/bootstrap-4.4.1.js"></script>';
      echo '<script src= "https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>';
      echo '<script src="' . DOMAIN . '/public/JS/marked.min.v1.js"></script>';
      echo '<title>' . SYSTITLE . '</title>';
      echo '</head>';
      echo '<body><div class="container">';
    }
}




