<?php
//ファイル読み込み
require_once '../../app/Dbconnect.php';

class LevelLogic
{
    /**
    * ページネーション
    * @param void
    * @return bool $result
    */
    public static function levelRanking()
    {
    $result = false;
    $user = self::getLevel();

    if (isset($user)) {
        try {
            // postsテーブルから10件のデータを取得する
            $sql = 'SELECT COUNT(*) user_id FROM users';
            $stmt = connect()->prepare($sql);
            // SQL実行
            $result = $stmt-> execute();
            $paging = $stmt->fetchColumn();
            // ページネーションの数を取得する
            $pagination = ceil($paging / 10);
            return $pagination;
        } catch(\Exception $e) {
            // エラーの出力
            echo $e;
            // ログの出力
            error_log($e, 3, '../error.log');
            return $result;
        }
    }
    }
    
    /**
    * 全レベルを表示
    * @param void
    * @return bool $result
    */
    public static function getLevel()
    {
    $result = false;
    // GETで現在のページ数を取得する（未入力の場合は1を挿入）
    if (isset($_GET['page'])) {
    	  $page = (int)$_GET['page'];
    } else {
    	  $page = 1;
    }
    // スタートのポジションを計算する
    if ($page > 1) {
    	  // ２ページ目の場合
      	$start = ($page * 10) - 10;
    } else {
      	$start = 0;
    }

    try {
        // postsテーブルから10件のデータを取得する
        $sql = 'SELECT user_id, name, level, icon, comment FROM users ORDER BY level DESC LIMIT '.$start.' ,10';
        
        $stmt = connect()->prepare($sql);
        // SQL実行
        $result = $stmt-> execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    } catch(\Exception $e) {
        // エラーの出力
        echo $e;
        // ログの出力
        error_log($e, 3, '../error.log');
        return $result;
    }
    }
    
    /**
    * 上位３位のレベルを表示
    * @param void
    * @return bool $result
    */
    public static function levelTop3()
    {
    $result = false;
    
    $sql = 'SELECT user_id, name, level, icon, comment FROM users ORDER BY level DESC LIMIT 3';

    try {
        $stmt = connect()->prepare($sql);
        // SQL実行
        $result = $stmt-> execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    } catch(\Exception $e) {
        // エラーの出力
        echo $e;
        // ログの出力
        error_log($e, 3, '../error.log');
        return $result;
    }
    }

    /**
     * ユーザーの情報を表示する
     * @param array $levelData
     * @return bool $result
     */
    public static function displayUsers($levelData)
    {
    $result = false;

    $sql = 'SELECT user_id, name, level, icon, comment FROM users WHERE user_id = ?';

    // user_idを配列に入れる
    $arr = [];
    $arr[] = $levelData['user_id']; // user_id
    
    try {
        $stmt = connect()->prepare($sql);
        // SQL実行
        $result = $stmt-> execute($arr);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data;
    } catch(\Exception $e) {
        // エラーの出力
        echo $e;
        // ログの出力
        error_log($e, 3, '../error.log');
        return $result;
    }
    }
}