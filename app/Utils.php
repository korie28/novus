<?php
namespace Novus;

require_once __DIR__ . '/../config/def.php';

class Utils
{
    // htmlで行表示対応の文字列作成
    public static function compatiStr($str)
    {
        return str_replace("\r\n", "<br>", $str);
    }

    // 日時の文字列作成
    public static function compatiDate($date, $format = "Y-m-d H:i")
    {
        if (!$date) {
            return '';
        }
        return date($format, strtotime($date));
    }

    // 現在の時刻から $addDay日後の日時の文字列を作成
    public static function addDay($addDay, $format = 0)
    {
        $targetDt = strtotime($addDay . 'day');
        if ($format == 0) {
            return date('Y-m-d 00:00', $targetDt);
        } else if ($format == 1) {
            return date('Y-m-d 17:00', $targetDt);
        } else {
            return date('Y-m-d 23:59', $targetDt);
        }
    }

    // 日時整形
    public static function dayFormat($dateTime)
    {
        return date("Y-m-d H:i", strtotime($dateTime));
    }

    // 特殊文字指定変換
    public static function convertSQL($str) {
        $str = mb_ereg_replace('(["_%#])', '#\1', $str); // % や _ を #% #_ にする
        $str = mb_ereg_replace("(['\\\\])", '#\\\1', $str); // ' や \ を #\' #\\ にする
        return $str;
    }
    // 特殊文字除去(htmlのタグとして動作しないように変換)
    public static function h($str)
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    // 特殊タグ除去(一部タグとして許容する。article/datail.php用)
    public static $stable = array (
        '&lt;h1&gt;' => '<h1>',
        '&lt;/h1&gt;' =>'</h1>',
        '&lt;h2&gt;' => '<h2>',
        '&lt;/h2&gt;' =>'</h2>',
        '&lt;h3&gt;' => '<h3>',
        '&lt;/h3&gt;' =>'</h3>',
        '&lt;h4&gt;' => '<h4>',
        '&lt;/h4&gt;' =>'</h4>',
        '&lt;h5&gt;' => '<h5>',
        '&lt;/h5&gt;' =>'</h5>',
        '&lt;b&gt;' => '<b>',
        '&lt;/b&gt;' =>'</b>',
        '&lt;u&gt;' =>'<u>',
        '&lt;/u&gt;' =>'</u>',
        '&lt;p&gt;' =>'<p>',
        '&lt;/p&gt;' =>'</p>',
        '&lt;br&gt;' =>'<br>',
        '&lt;br/&gt;' =>'<br/>',
        '&lt;strong&gt;' => '<strong>',
        '&lt;/strong&gt;'=> '</strong>',
        '&lt;ul&gt;' => '<ul>',
        '&lt;/ul&gt;'=> '</ul>',
        '&lt;li&gt;' => '<li>',
        '&lt;/li&gt;'=> '</li>',
        '&lt;'=> '<',
        '&gt;'=> '>'
    );

    public static $btable = array (
        '/&lt;img(.*)&gt;/' => '<img$1>'
    );

    public static function trimHtmlTag($str)
    {
        $str = htmlspecialchars($str, ENT_NOQUOTES, 'UTF-8');
        // 単純なタグ
        $search = array_keys(Utils::$stable);
        $replace = array_values(Utils::$stable);
        $str = str_replace($search, $replace, $str); // $searchを$replaceに変換
        // パラメータ付きタグ
        foreach (Utils::$btable as $key => $val) {
            $str = preg_replace($key, $val, $str);
        }
        return $str;
    }

    // 日付文字列チェック
    public static function checkDatetimeFormat($dateTime)
    {
        $dateTime = str_replace('/', '-', $dateTime); // 『/』を『-』に変換
        if ($dateTime === date("Y-m-d H:i", strtotime($dateTime)) || $dateTime === date("Y-m-d H:i:s", strtotime($dateTime))) {
            return true;
        }
        return false;
    }

    // 両端をトリミング
    public static function mbTrim($str)
    {
        return preg_replace("/(^\s+)|(\s+$)/u", "", $str);
    }

    // 文字列チェック
    public static function isStrLen($str, $len)
    {
        if ($str && mb_strlen($str) <= $len) { // $len以下ならtrue
            return true;
        } else {
            return false;
        }
    }

    //入力値に不正なデータがないかなどをチェックする関数
    public static function checkInput($var)
    {
        if (is_array($var)) {
            return array_map(function ($v) {
                return static::checkInput($v);
            }, $var);
        } else {
            //NULLバイト攻撃対策
            if (preg_match('/\0/', $var)) {
                die('不正な入力です。');
            }
            //文字エンコードのチェック
            if (!mb_check_encoding($var, 'UTF-8')) {
                die('不正な入力です。');
            }
            //改行、タブ以外の制御文字のチェック
            if (preg_match('/\A[\r\n\t[:^cntrl:]]*\z/u', $var) === 0) {
                die('不正な入力です。制御文字は使用できません。');
            }
            return $var;
        }
    }
}
