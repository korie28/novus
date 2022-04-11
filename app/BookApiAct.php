<?php
namespace Novus;

require_once "Action.php";
require_once "Log.php";
require_once "Utils.php";

// 記事/いいね関連クラス
class BookApiAct extends Action
{
    // $mode>=0の場合、明示的にbeginを呼び出す
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

    // head,body開始タグ
    public function printHeader() {
        echo '<!DOCTYPE html>';
        echo '<html lang="ja">';
        echo '<head>';
        echo '<meta charset=UTF-8" http-equiv="Content-Type">';
        echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<meta name="format-detection" content="telephone=no">';
        echo '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">';
        echo '<link rel="stylesheet" href="' . DOMAIN . '/public/css/bookApi.css">';
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>';
        echo '<script src= "https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>';
        echo '<script src="' . DOMAIN . '/public/bookApi/js/script.js" defer></script>';
        echo '<script src="' . DOMAIN . '/public/JS/qapi.js" defer></script>';
        echo '<title>' . SYSTITLE . '</title>';
        echo '</head>';
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
            echo '<li id="li"><a class="nav-link small text-white" href="' . DOMAIN . '/public/userLogin/logout.php?user_id='.$_SESSION["login_user"]["user_id"].'">ログアウト</a></li>';
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
        echo '<body><div class="container">';
    }

    // ページ表示がないファイルは、mode=1で呼ぶ
    public function printFooter($mode = 0) {
        if ($mode == 0) {
            echo '<div class="row m-2">';
                if (isset($_SESSION['login_user'])):
                    echo '<div class="col-sm-1"></div>';
                    echo '<div class="col-sm-11">';
                    echo '<a class="btn btn-success m-2" href="' . DOMAIN . '/public/userLogin/home.php">ホーム画面へ</a>';
                    echo '</div>';
                else:
                    echo '<div class="col-sm-8">';
                    echo '<a class="btn btn-success m-2" href="' . DOMAIN . '/public/top/index.php">ホーム画面へ</a>';
                    echo '</div>';
                endif;
            echo '</div>';
        }
        echo '</div>';
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
