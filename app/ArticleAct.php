<?php
namespace Novus;

require_once "Action.php";
require_once "Log.php";
require_once "Utils.php";

// 記事関係
class ArticleAct extends Action
{
    // 取得系(表示中の記事リスト, 個々の記事, DBにある記事投稿の総数)
    const QUERY_ARTICLE_LIST = "SELECT article_id, user_id, title, message, post_date, upd_date, cate_id FROM article_posts %s ORDER BY upd_date DESC LIMIT :limit offset :offset";
    const QUERY_ARTICLE = "SELECT article_id, user_id, title, message, post_date, upd_date, cate_id FROM article_posts WHERE article_id=:article_id"; 
    const QUERY_ARTICLE_COUNT = "SELECT COUNT(article_id) AS cnt FROM article_posts %s";
    // 更新系
    const INSERT_ARTICLE = "INSERT INTO article_posts (user_id, title, message, post_date, upd_date, cate_id) VALUES (:user_id, :title, :message, now(), now(), :cate_id)";
    const UPDATE_ARTICLE = "UPDATE article_posts SET title=:title, message=:message, cate_id=:cate_id, upd_date=now() WHERE article_id=:article_id AND user_id=:user_id";
    const DELETE_ARTICLE = "DELETE FROM article_posts WHERE article_id=:article_id AND user_id=:user_id";
    // カテゴリ一覧
    const QUERY_CATEGORY_LIST = "SELECT cate_id, category_name, color FROM categories";
    // いいね取得
    const QUERY_POSTLIKE = "SELECT a_like_id, user_id, article_id, like_flg FROM article_likes WHERE article_id=:article_id AND user_id=:user_id";
    const QUERY_POSTLIKE_COUNTLIST = "SELECT article_id, COUNT(a_like_id) AS like_cnt FROM article_likes WHERE article_id IN (%s) AND like_flg=1 GROUP BY article_id";
    // いいね更新
    const INSERT_POSTLIKE = "INSERT INTO article_likes (user_id, article_id, like_flg) VALUES (:user_id, :article_id, 1)";
    const UPDATE_POSTLIKE = "UPDATE article_likes SET like_flg=:like_flg WHERE a_like_id=:a_like_id";
    const DELETE_POSTLIKE = "DELETE FROM article_likes WHERE article_id=:article_id";
    // レベル, 経験値取得
    const QUERY_LEVEL = "SELECT level, exp FROM users WHERE user_id=:user_id";
    // レベル, 経験値更新
    const UPDATE_LEVEL = "UPDATE users SET exp=:exp, level=:level WHERE user_id=:user_id";
    //経験値更新
    const UPDATE_EXP = "UPDATE users SET exp=:exp WHERE user_id=:user_id";

    // $modeが0以上の場合、明示的にbeginを呼び出す
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

    // article/index.phpへリダイレクト
    public function redirectTop()
    {
        header("Location: " . DOMAIN . "/public/article/index.php");
        exit;
    }

    // 表示中の全情報を返す
    public function articleList($page, $searchText = "", $searchCategory = "")
    {
        $inWhere = "";
        
        if ($searchText !== "" || $searchCategory !== "") {
            $inWhere .= "WHERE "; // 代入演算子 ⇒ $inWhere = $inWhere ."WHERE";(WHERE句)
        }
        
        if ($searchText !== "") {
            // 検索文字指定不可の文字をエスケープ
            $searchText = Utils::convertSQL($searchText);
            $inWhere .= "title LIKE :title ESCAPE '#' ";
        }

        if ($searchCategory !== "") {
            if ($searchText !== "") {
                $inWhere .= "AND "; 
            }
            $inWhere .= "cate_id = :category"; 
        }

        // retrieve:取得
        $retInfo = [
            'category' => $this->categoryMap(),
            'page' => $page,
            'total' => 0,
            'maxPage' => 0,
            'articleList' => [],
            'userMap' => [],
            'postLikeMap' => [],
        ];
        $retInfo['category'] = $this->categoryMap();

        // 記事全体の数
        $retInfo["total"] = $this->countArticleListTotal($inWhere, $searchText, $searchCategory);
        if ($retInfo["total"] <= 0) {
            return $retInfo;
        }
        $retInfo["maxPage"] = ceil(($retInfo["total"]) / LISTCOUNT);

        if ($page > $retInfo["maxPage"]) {
            $retInfo["page"] = $retInfo["maxPage"];
        } else {
            $retInfo["page"] = $page;
        }

        // 表示中の記事リスト情報
        $retInfo["articleList"] = $this->searchArticleList($inWhere, $searchText, $searchCategory, $retInfo["page"]);

        // 表示中記事のユーザー情報
        $retInfo["userMap"] = $this->memberMap($retInfo["articleList"], "user_id");
        
        // article_idといいね数の連想配列
        $retInfo["postLikeMap"] = $this->likeCountMap($retInfo["articleList"]);

        return $retInfo;
    }

    // 表示中の記事リスト情報を返す(検索結果考慮)
    private function searchArticleList($inWhere, $searchText, $searchCategory, $page)
    {
        try {
            // 記事リストを取得
            $stmt = $this->conn->prepare(sprintf(self::QUERY_ARTICLE_LIST, $inWhere));

            if ($searchText != "") {
                $stmt->bindValue(":title", "%$searchText%", \PDO::PARAM_STR);
            }

            if ($searchCategory != "") {
                $stmt->bindValue(":category", (int)$searchCategory, \PDO::PARAM_INT);
            }

            $offset = ($page - 1) * LISTCOUNT;
            $stmt->bindValue(":offset", (int)$offset, \PDO::PARAM_INT);
            $stmt->bindValue(":limit", (int)LISTCOUNT, \PDO::PARAM_INT);
            $result = $stmt->execute();
        } catch (\Exception $e) {
            Log::error($e);
            echo $e;
        }

        if ($result) {
            $retList = [];
            while ($rec =  $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $retList[] = $rec;
            }
            return $retList; //表示中の記事情報
        }
        return [];
    }

    // 検索ヒット数を返す
    private function countArticleListTotal($inWhere, $searchText, $searchCategory)
    {
        try {
            // 記事全体の数
            $stmt = $this->conn->prepare(sprintf(self::QUERY_ARTICLE_COUNT, $inWhere));

            if ($searchText !== "") {
                $stmt->bindValue(":title", "%$searchText%", \PDO::PARAM_STR); //あいまい検索
            }

            if ($searchCategory !== "") {
                $stmt->bindValue(":category", (int)$searchCategory, \PDO::PARAM_INT); //cate_idが入る
            }

            $result = $stmt->execute();
        } catch (\Exception $e) {
            Log::error($e);
            echo $e;
        }
        $cntrec = $result ? $stmt->fetch(\PDO::FETCH_ASSOC) : null; // 検索ヒット数(配列)
        return $cntrec == null ? 0 : $cntrec["cnt"]; // 検索ヒット数
    }

    // 記事単独の全情報を返す
    public function article($article_id)
    {
        $retInfo = [];

        {
            try {
                $stmt = $this->conn->prepare(self::QUERY_ARTICLE);
                $stmt->bindValue(':article_id', $article_id);
                $result = $stmt->execute();
            } catch (\Exception $e) {
                Log::error($e);
                echo $e;
            }
            $article = $result ? $stmt->fetch(\PDO::FETCH_ASSOC) : null; //三項演算子
            $retInfo['article'] = $article;
            $retInfo['user'] = $this->memberRef($article['user_id']); // ユーザ情報
        }

        // その記事にいいねしたことがあるか?
        $retInfo['postLike'] = $this->postLike($article_id);

        // その記事のいいね数を取得
        {
            try {
                $stmt = $this->conn->query(sprintf(self::QUERY_POSTLIKE_COUNTLIST, $article_id));
                $postLikeCnt = $stmt->fetch(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                Log::error($e);
                echo $e;
            }
            $retInfo['postLikeCnt'] = $postLikeCnt == null ? 0 : $postLikeCnt['like_cnt'];
        }

        // カテゴリ
        $retInfo['category'] = $this->categoryMap();

        return $retInfo;
    }

    // 記事投稿処理
    public function create($title, $message, $cate_id)
    {
        try {
            $stmt = $this->conn->prepare(self::INSERT_ARTICLE);
            $stmt->bindValue(':user_id', $this->member['user_id']);
            $stmt->bindValue(':title', $title);
            $stmt->bindValue(':message', $message);
            $stmt->bindValue(':cate_id', $cate_id);
            $stmt->execute();
        } catch (\Exception $e) {
            Log::error($e);
            echo $e;
        }
    }

    // 記事更新処理
    public function update($article_id, $title, $message, $cate_id)
    {
        try {
            $stmt = $this->conn->prepare(self::UPDATE_ARTICLE);
            $stmt->bindValue(':article_id', $article_id);
            $stmt->bindValue(':user_id', $this->member['user_id']);
            $stmt->bindValue(':title', $title);
            $stmt->bindValue(':message', $message);
            $stmt->bindValue(':cate_id', $cate_id);
            $stmt->execute();
        } catch (\Exception $e) {
            Log::error($e);
            echo $e;
        }
    }

    // 記事削除処理
    public function delete($article_id)
    {
        $this->conn->beginTransaction();
        {
            try {
                $stmt = $this->conn->prepare(self::DELETE_ARTICLE);
                $stmt->bindValue(':user_id', $this->member['user_id']);
                $stmt->bindValue(':article_id', $article_id);
                $stmt->execute();
            } catch (\Exception $e) {
                $this->conn->rollback();
                echo $e;
            }
        }

        {
            try {
                $stmt = $this->conn->prepare(self::DELETE_POSTLIKE);
                $stmt->bindValue(':article_id', $article_id);
                $stmt->execute();
            } catch (\Exception $e) {
                $this->conn->rollback();
                echo $e;
            }
        }

        $this->conn->commit();
    }

    // いいね押下/解除時の処理
    public function postLikeArticle($article_id)
    {
        $retcode = 'error';
        try {
            $this->conn->beginTransaction();
            // 登録済みかどうか?
            $postLike = $this->postLike($article_id);
            if ($postLike == null) {
                try {
                    $stmt = $this->conn->prepare(self::INSERT_POSTLIKE);
                    $stmt->bindValue(':user_id', $this->member['user_id']);
                    $stmt->bindValue(':article_id', $article_id);
                    $stmt->execute();
                } catch (\Exception $e) {
                    Log::error($e);
                    echo $e;
                }
                $retcode = 'likeset';
            } else {
                // 現在の状態を反転させて更新
                $likeflg = $postLike['like_flg'] == 1 ? 0 : 1;
                try {
                    $stmt = $this->conn->prepare(self::UPDATE_POSTLIKE);
                    $stmt->bindValue(':like_flg', $likeflg);
                    $stmt->bindValue(':a_like_id', $postLike['a_like_id']);
                    $stmt->execute();
                } catch (\Exception $e) {
                    Log::error($e);
                    echo $e;
                }
                $retcode = $likeflg == 1 ? 'likeset' : 'reset';
            }

            $this->conn->commit();
        } catch (\Exception $e) {
            $this->conn->rollback();
        }
        return $retcode;
    }

    //経験値付与処理
    public function addEXP($user_id, $plus_exp)
    {
        try {
            $stmt = $this->conn->prepare(self::QUERY_LEVEL);
            $stmt->bindValue(':user_id', $user_id);
            $data = $stmt->execute();
            if (! $data) {
                return null;
            }
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            Log::error($e);
            echo $e;
        }
        // QUERY_LEVELで取得した経験値とレベルを定義
        $exp = $data['exp'];
        $level = $data['level'];

        $new_exp = $exp + $plus_exp; // 合算経験値
        $new_level = floor($new_exp / 100) + 1; //合算レベル

        // 取得レベルと合算レベルの比較
        if ($level < $new_level) { // レベルに変化あり
            try {
                $stmt = $this->conn->prepare(self::UPDATE_LEVEL);// 経験値とレベルを更新
                $stmt->bindValue(':user_id', $user_id);
                $stmt->bindValue(':exp', $new_exp);
                $stmt->bindValue(':level', $new_level);
                $data = $stmt-> execute();
                $_SESSION['login_user']['level'] = $new_level;
                $_SESSION['login_user']['exp'] = $new_exp;
                return $data;
            } catch (\Exception $e) {
                echo $e;
            }
        } else { // レベルに変化なし
            try {
                $stmt = $this->conn->prepare(self::UPDATE_EXP);// 経験値のみ付与
                $stmt->bindValue(':user_id', $user_id);
                $stmt->bindValue(':exp', $new_exp);
                $data = $stmt-> execute();
                $_SESSION['login_user']['level'] = $new_level;
                $_SESSION['login_user']['exp'] = $new_exp;
                return $data;
            } catch (\Exception $e) {
                echo $e;
            }
        }
    }


    // カテゴリマップ。戻り値は cate_id とカテゴリ名の連想配列を返す。
    public function categoryMap()
    {
        $keymap = [];
        $result = $this->conn->query(self::QUERY_CATEGORY_LIST);
        if ($result) {
            while ($rec =  $result->fetch(\PDO::FETCH_ASSOC)) {
                $keymap[$rec['cate_id']] = $rec['category_name'];
            }
        }
        return $keymap;
    }

    // カテゴリカラーマップ。cate_idとcolorの連想配列を返す。
    public function categoryColorMap()
    {
        $keymap = [];
        $result = $this->conn->query(self::QUERY_CATEGORY_LIST);
        if ($result) {
            while ($rec =  $result->fetch(\PDO::FETCH_ASSOC)) {
                $keymap[$rec['cate_id']] = $rec['color'];
            }
        }
        return $keymap;
    }

    // カテゴリIdチェック
    public function isCategory($cate_id)
    {
        $catmap = $this->categoryMap();
        return isset($catmap[$cate_id]);
    }

    // その記事のいいね情報取得
    public function postLike($article_id)
    {
        if (isset($_SESSION['login_user'])) {
            try {
                $stmt = $this->conn->prepare(self::QUERY_POSTLIKE);
                $stmt->bindValue(':article_id', $article_id);
                $stmt->bindValue(':user_id', $this->member['user_id']);
                $result = $stmt->execute();
            } catch (\Exception $e) {
                Log::error($e);
                echo $e;
            }
            $postLike = $result ? $stmt->fetch(\PDO::FETCH_ASSOC) : null;
            return $postLike;
        }
    }

    // article_id といいね数の連想配列。
    private function likeCountMap($articles)
    {
        $countmap = [];
        if (count($articles) == 0) {
            return $countmap;
        }

        // where句の作成
        $ids = [];

        foreach ($articles as $art) {
            $ids[] = $art['article_id'];
        }
        $inClause = substr(str_repeat(',?', count($ids)), 1);

        try {
            // いいね数取得
            $stmt = $this->conn->prepare(sprintf(self::QUERY_POSTLIKE_COUNTLIST, $inClause));
            $result = $stmt->execute($ids);
        } catch (\Exception $e) {
            Log::error($e);
            echo $e;
        }

        if ($result) {
            while ($mem = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $countmap[$mem['article_id']] = $mem['like_cnt'];
            }
        }
        return $countmap;
    }
}
