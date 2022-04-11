const $domainurl = '/novus/public/';

// $urlへリダイレクト
function jumpApi($url) {
	location.href = $domainurl + $url;
}

// ajaxで post送信
function formApiCallback($url, $data, $callback) {
console.log('url=' + $url);
console.log($data);
	console.log($callback);
	$.ajax({
		type: 'POST',
		url: $domainurl + $url,
		data: $data,
		success: function(strtext) {
console.log(strtext);
			$callback(strtext);
		}
	});
}

// swalライブラリ
function onShow($text) {
	swal($text);
}

// 文字の長さチェック
function isStrLen(value, minval, maxval) {
	var $len = value.length;
	if ($len < minval) {
		return false;
	}
	if ($len > maxval) {
		return false;
	}
	return true;
}

// javascriptで特殊タグ除去(一部をタグとして許容する)
function trimHtmlTag($str) {
	// 全体タグを無効化
	$str = $str.replace(/</g,"&lt;") ;
	$str = $str.replace(/>/g,"&gt;") ;

	// 許容するタグを戻す
	$str = $str.replace(/&lt;h1&gt;/g, '<h1>');
	$str = $str.replace(/&lt;\/h1&gt;/g, '</h1>');
	$str = $str.replace(/&lt;h2&gt;/g, '<h2>');
	$str = $str.replace(/&lt;\/h2&gt;/g, '</h2>');
	$str = $str.replace(/&lt;h3&gt;/g, '<h3>');
	$str = $str.replace(/&lt;\/h3&gt;/g, '</h3>');
	$str = $str.replace(/&lt;h4&gt;/g, '<h4>');
	$str = $str.replace(/&lt;\/h4&gt;/g, '</h4>');
	$str = $str.replace(/&lt;h5&gt;/g, '<h5>');
	$str = $str.replace(/&lt;\/h5&gt;/g, '</h5>');
	$str = $str.replace(/&lt;b&gt;/g, '<b>');
	$str = $str.replace(/&lt;\/b&gt;/g, '</b>');
	$str = $str.replace(/&lt;p&gt;/g, '<p>');
	$str = $str.replace(/&lt;\/p&gt;/g, '</p>');
	$str = $str.replace(/&lt;u&gt;/g, '<u>');
	$str = $str.replace(/&lt;\/u&gt;/g, '</u>');
	$str = $str.replace(/&lt;br&gt;/g, '<br>');
	$str = $str.replace(/&lt;br\/&gt;/g, '<br/>');
	$str = $str.replace(/&lt;ul&gt;/g, '<ul>');
	$str = $str.replace(/&lt;\/ul&gt;/g, '</ul>');
	$str = $str.replace(/&lt;ul&gt;/g, '<li>');
	$str = $str.replace(/&lt;\/ul&gt;/g, '</li>');
	$str = $str.replace(/&lt;img(.*)&gt;/g, '<img$1>');
	return $str;
}
