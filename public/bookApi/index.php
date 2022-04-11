<?php
require_once "../../app/BookApiAct.php";
require_once '../../app/Token.php';

use Novus\BookApiAct;
use Novus\Token;

$act = new BookApiAct(0);
Token::create();
?>

<div class="header col-sm-8 offset-sm-2">
    <p class="text-center mt-5 p-2" id="title">GoogleBooks Api</p>
</div>

<div class="search col-sm-8 offset-sm-2">
    <div class="search__text">
        <input type="text" id="search-word" class="search__text__input text-center search-text" placeholder="検索する">
    </div>
    <button id="search-button" class="search__btn">検索する</button>
</div>

<div class="row flex">
    <div class="offset-sm-6">
    </div>

    <div class="col-sm-2 mb-3 text-right">
        表示件数：
        <select id="displayed-num" class="search-text">
            <option value=10>10件</option>
            <option value=20>20件</option>
            <option value=30>30件</option>
            <option value=40>40件</option>
        </select>
    </div>

    <div class="col-sm-2 mb-3 text-right">
        表示順：
        <select id="displayed-orderBy" class="search-text">
            <option value=relevance>関連度順</option>
            <option value=newest>新着順</option>
        </select>
    </div>
</div>

<div class="lists"></div>

<?php
$act->printFooter(0);
?>