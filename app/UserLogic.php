<?php
// ファイル読み込み
require_once '../../app/Dbconnect.php';

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

class UserLogic
{
    /**
     * ユーザーを登録する
     * @param array $userData
     * @return bool $result
     */
    public static function createUser()
    {
    $result = false;
        $sql = 'INSERT INTO users (name, tel, email, password) VALUES (?, ?, ?, ?)';
        // ユーザーデータを配列に入れる
        $arr = [];
        $arr[] = $_SESSION['signUp']['0'];                                      // name
        $arr[] = $_SESSION['signUp']['1'];                                      // tel
        $arr[] = $_SESSION['signUp']['2'];                                      // email
        $arr[] = password_hash($_SESSION['signUp']['3'], PASSWORD_DEFAULT);     // password
        try {
            $stmt = connect()->prepare($sql);
            // SQL実行
            $result = $stmt-> execute($arr);
            $user = $stmt->fetch();
            // 実行後、$_SESSIONの内容を消去
            $_SESSION['signUp']['0'] = null;                                    
            $_SESSION['signUp']['1'] = null;                                     
            $_SESSION['signUp']['2'] = null;                                      
            $_SESSION['signUp']['3'] = null;    
            return $result;
        } catch(\Exception $e) {
            // エラーの出力
            echo $e;
            // ログの出力
            error_log($e, 3, '../error.log');
            return $result;
        }
    }

    /**
     * 電話番号の重複チェック
     * @param array $tel
     * @return array|bool $user|false
     */
    public static function checkDuplicateByTel($tel)
    {
    $sql = 'SELECT COUNT(*) as cnt FROM users WHERE tel = ?';
    // telを配列に入れる
    $arr = [];
    $arr[] = $tel;

    try {
        $stmt = connect()->prepare($sql);
        // SQL実行
        $stmt->execute($arr);
        // SQLの結果を返す
        $user = $stmt->fetch();
        return $user;
    } catch(\Exception $e) {
        return false;
    }
    }

    /**
    * ログイン処理
    * @param string $tel
    * @param string $password
    * @return bool $result
    */
    public static function login($tel, $password)
    {
    $result = false;
    // ユーザをtelから検索して取得
    $user = self::getUserByTel($tel);
    // バリデーション
    if (!$user) {
        $_SESSION['msg'] = '電話番号が一致しません。';
        return $result;
    }
    // パスワードの照会
    if (password_verify($password, $user['password'])) {
        // ログイン成功
        // ハイジャック対策
        session_regenerate_id(true);
        $_SESSION['login_user'] = $user;
        $result = true;
        return $result;
    }
    $_SESSION['msg'] = 'パスワードが一致しません。';
    return $result;
    }
    
    /**
     * telからユーザを取得
     * @param string $tel
     * @return array|bool $user|false
    */
    public static function getUserBytel($tel)
    {
    // SQLの準備
    // SQLの実行
    // SQLの結果を返す
    $sql = 'SELECT * FROM users WHERE tel = ?';
    // telを配列に入れる
    $arr = [];
    $arr[] = $tel;

    try {
        $stmt = connect()->prepare($sql);
        // SQL実行
        $stmt->execute($arr);
        // SQLの結果を返す
        $user = $stmt->fetch();
        return $user;
    } catch(\Exception $e) {
        return false;
    }
    }
    
    /**
     * ログインチェック
     * @param void
     * @return bool $result
     */
    public static function checkLogin()
    {
    $result = false;
    // セッションにログインユーザが入っていなかったらfalse
    if (isset($_SESSION['login_user']) && $_SESSION['login_user']['user_id'] > 0) {
        return $result = true;
    }
    return $result;
    } 

    /**
    * ログアウト処理
    */
    public static function logout()
    {
    $_SESSION = array();
    session_destroy();
    } 

    /**
     * ユーザー情報[name]編集
     * @param string $name
     * @return bool $result
     */
    public static function editUserName()
    {
    $result = false;
    // SQLの準備
    // SQLの実行
    // SQLの結果を返す
    $sql = 'UPDATE users SET name=? WHERE user_id=?';
    // nameを配列に入れる
    $arr = [];
    $arr[] = $_SESSION['nameEdit']; 
    $arr[] = $_SESSION['login_user']['user_id']; 

    try {
        $stmt = connect()->prepare($sql);
        // SQL実行
        $result = $stmt-> execute($arr);
        // セッション値を最新に更新
        $_SESSION['login_user']['name'] = $_SESSION['nameEdit'];
        $user = $stmt->fetch();
        return $result;
    } catch(\Exception $e) {
        // エラーの出力
        echo $e;
        // ログの出力
        error_log($e, 3, '../error.log');
        return $result;
    }
    }
    
    /**
     * ユーザー情報[tel]編集
     * @param string $tel
     * @return bool $result
     */
    public static function editUserTel()
    {
    $result = false;
    // SQLの準備
    // SQLの実行
    // SQLの結果を返す
    $sql = 'UPDATE users SET tel=? WHERE user_id=?';
    // telを配列に入れる
    $arr = [];
    $arr[] = $_SESSION['telEdit']; 
    $arr[] = $_SESSION['login_user']['user_id']; 

    try {
        $stmt = connect()->prepare($sql);
        // SQL実行
        $result = $stmt-> execute($arr);
        // セッション値を最新に更新
        $_SESSION['login_user']['tel'] = $_SESSION['telEdit'];
        $user = $stmt->fetch();
        return $result;
    } catch(\Exception $e) {
        // エラーの出力
        echo $e;
        // ログの出力
        error_log($e, 3, '../error.log');
        return $result;
    }
    }

     
    /**
     * ユーザー情報[email]編集
     * @param string $email
     * @return bool $result
     */
    public static function editUserEmail()
    {
    $result = false;
    // SQLの準備
    // SQLの実行
    // SQLの結果を返す
    $sql = 'UPDATE users SET email=? WHERE user_id=?';
    // emailを配列に入れる
    $arr = [];
    $arr[] = $_SESSION['emailEdit']; 
    $arr[] = $_SESSION['login_user']['user_id']; 

    try {
        $stmt = connect()->prepare($sql);
        // SQL実行
        $result = $stmt-> execute($arr);
        // セッション値を最新に更新
        $_SESSION['login_user']['email'] = $_SESSION['emailEdit']; 
        $user = $stmt->fetch();
        return $result;
    } catch(\Exception $e) {
        // エラーの出力
        echo $e;
        // ログの出力
        error_log($e, 3, '../error.log');
        return $result;
    }
    }

    /**
     * ユーザー情報[password]編集
     * @param string $password
     * @return bool $result
     */
    public static function editUserPassword()
    {
    $result = false;
    // SQLの準備
    // SQLの実行
    // SQLの結果を返す
    $sql = 'UPDATE users SET password=? WHERE user_id=?';
    // passwordを配列に入れる
    $arr = [];
    $arr[] = password_hash($_SESSION['passwordEdit'], PASSWORD_DEFAULT);
    $arr[] = $_SESSION['login_user']['user_id']; 
    
    try {
        $stmt = connect()->prepare($sql);
        // SQL実行
        $result = $stmt-> execute($arr);
        // セッション値を最新に更新
        $_SESSION['login_user']['password'] = $_SESSION['passwordEdit'];
        $user = $stmt->fetch();
        // セッション情報を消去し、セキュリティ対策
        $_SESSION['login_user']['password'] = null;
        return $result;
    } catch(\Exception $e) {
        // エラーの出力
        echo $e;
        // ログの出力
        error_log($e, 3, '../error.log');
        return $result;
    }
    }

    /**
     * ユーザー情報[icon]編集
     * @param string $icon
     * @return bool $result
     */
    public static function editUserIcon()
    {
    // SQLの準備
    // SQLの実行
    // SQLの結果を返す
    $sql = 'UPDATE users SET icon=? WHERE user_id=?';
    // iconを配列に入れる
    $arr = [];
    $arr[] = $_SESSION['iconEdit']['name']; 
    $arr[] = $_SESSION['login_user']['user_id']; 

    try {
        $stmt = connect()->prepare($sql);
        // SQL実行
        $result = $stmt-> execute($arr);
        // セッション値を最新に更新
        $_SESSION['login_user']['icon'] = $_SESSION['iconEdit']['name'];
        $user = $stmt->fetch();
        return $result;
    } catch(\Exception $e) {
        // エラーの出力
        echo $e;
        // ログの出力
        error_log($e, 3, '../error.log');
        return $result;
    }
    }
    
    /**
     * ユーザー情報[comment]編集
     * @param string $comment
     * @return bool $result
     */
    public static function editUserComment()
    {
    $result = false;
    // SQLの準備
    // SQLの実行
    // SQLの結果を返す
    $sql = 'UPDATE users SET comment=? WHERE user_id=?';
    // commentを配列に入れる
    $arr = [];
    $arr[] = $_SESSION['commentEdit']; 
    $arr[] = $_SESSION['login_user']['user_id']; 
    
    try {
        $stmt = connect()->prepare($sql);
        // SQL実行
        $result = $stmt-> execute($arr);
        // セッション値を最新に更新
        $_SESSION['login_user']['comment'] = $_SESSION['commentEdit']; 
        $user = $stmt->fetch();
        return $result;
    } catch(\Exception $e) {
        // エラーの出力
        echo $e;
        // ログの出力
        error_log($e, 3, '../error.log');
        return $result;
    }
    }

    /**
     * アイコン表示
     * @param string $icon
     * @return bool $result
     */
    public static function showIcon()
    {
    $result = false;
    // SQLの準備
    // SQLの実行
    // SQLの結果を返す
    $sql = 'SELECT icon FROM users WHERE user_id=?';
    // 配列に入れる
    $arr = [];
    $arr[] = $_SESSION['login_user']['user_id']; 

    try {
        $stmt = connect()->prepare($sql);
        // SQL実行
        $result = $stmt-> execute($arr);
        return $result;
    } catch(\Exception $e) {
        // エラーの出力
        echo $e;
        // ログの出力
        error_log($e, 3, '../error.log');
        return $result;
    }
    }

    /**
     * ユーザー情報の削除
     * UPDATEで「退会済みユーザー」という表示にして投稿・質問は残す
     * @param string $user_id
     * @return bool $result
     */
    public static function deleteUser()
    {
    $result = false;
    // SQLの準備
    // SQLの実行
    // SQLの結果を返す
    $sql = 'UPDATE users SET name="退会済みユーザー", tel="", email="", icon="", password="", comment="", level="0", 
            exp="0", pre_level="0", pre_exp="0" WHERE user_id=?';
    // 配列に入れる
    $arr = [];
    $arr[] = $_SESSION['login_user']['user_id']; 

    try {
        $stmt = connect()->prepare($sql);
        // SQL実行
        $result = $stmt-> execute($arr);
        $_SESSION['login_user']['user_id'] = null;
        return $result;
    } catch(\Exception $e) {
        // エラーの出力
        echo $e;
        // ログの出力
        error_log($e, 3, '../error.log');
        return $result;
    }
    }

    
    /**
     * pre_level,pre_exp更新処理
     * @param string $level
     * @return bool $result
     */
    public static function levelUpdate()
    {
    $result = false; 
    // SQLの準備・実行・結果を返す
    $sql = 'UPDATE users SET pre_level=?, pre_exp=? WHERE user_id = ?';
    // 配列に入れる
    $arr = [];
    $arr[] = $_SESSION['login_user']['level']; 
    $arr[] = $_SESSION['login_user']['exp']; 
    $arr[] = $_SESSION['login_user']['user_id']; 
    
    try {
        $stmt = connect()->prepare($sql);
        // SQL実行
        $result = $stmt-> execute($arr);
        // セッション変数を更新し、リロード時に再度のレベルモーダル発動を防ぐ
        $_SESSION['login_user']['pre_level'] = $_SESSION['login_user']['level']; 
        $_SESSION['login_user']['pre_exp'] = $_SESSION['login_user']['exp']; 
        return $result;
    } catch(\Exception $e) {
        // エラーの出力
        echo $e;
        // ログの出力
        error_log($e, 3, '../error.log');
        return $result;
    }
    }

    /**
     * 経験値取得処理
     * @param int $user_id
     * @param int $plus_exp
     * @return bool $result
     */
    public static function plusEXP($user_id, $plus_exp)
    {
    $result = false;
    // SQLの準備
    // SQLの実行
    // SQLの結果を返す
    $sql_sel = 'SELECT level, exp FROM users WHERE user_id=?';
    // 配列に入れる
    $arr = [];
    $arr[] = $user_id;

    try {
        $stmt = connect()->prepare($sql_sel);
        // SQL実行
        $data = $stmt-> execute($arr);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(\Exception $e) {
        // エラーの出力
        echo $e;
    }

    // SELECT文で取得した経験値とレベルを定義
    $exp = $data['exp'];
    $level = $data['level'];
    // 経験値を付与
    $new_exp = $exp + $plus_exp;
    // 経験値を付与した上でのレベルの計算
    // 経験値を100で割り（小数点切捨）、レベルの初期値である１を足す
    $new_level = floor($new_exp / 100) + 1;

    // 取得したレベルと新しいレベルの比較
    // 新しいレベルが取得レベルより高い場合
    if ($level < $new_level) { 
        // 経験値とレベルを更新するSQLの定義
        $sql_upd = 'UPDATE users SET exp=?, level=? WHERE user_id=?';   
        $arr = [];
        $arr[] = $new_exp;
        $arr[] = $new_level;
        $arr[] = $user_id;
        
    // 新しいレベルが取得レベルと同じ場合
    } else {
        // 経験値だけを更新するSQLの定義
        $sql_upd = 'UPDATE users SET exp=? WHERE user_id=?';   
        $arr = [];
        $arr[] = $new_exp;
        $arr[] = $user_id;
    }

        try {
            $stmt = connect()->prepare($sql_upd);
            // SQL実行
            $data = $stmt-> execute($arr);
            $_SESSION['login_user']['level'] = $new_level;
            $_SESSION['login_user']['exp'] = $new_exp;
            return $data;
        } catch(\Exception $e) {
            // エラーの出力
            echo $e;
        }

    }
}