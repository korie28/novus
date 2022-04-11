$(function () {

    // ボタンクリックにより発火
    $("#search-button").click(function () {
        searchBooks(); // 検索実行
    });
    // 変更イベントにより発火
    $('.search-text').change(function () {
        searchBooks(); // 検索実行
    });

    //関数searchBooks
    function searchBooks() {
        const searchText = $(".search__text__input").val(); //検索ワード
        const num = $("#displayed-num option:selected").val(); //id="displayed-num"のoptionの選択したvalueを代入
        const orderBy = $("#displayed-orderBy option:selected").val(); //並べ替え機能
        const displayedNum = "&maxResults=" + num;
        const displayedOrderBy = "&orderBy=" + orderBy;

        //lists__itemのclassがついた要素を消去
        $(".lists__item").remove(); //画面の初期化

        $.ajax({
            url: 'https://www.googleapis.com/books/v1/volumes?q=' + encodeURIComponent(searchText) + displayedNum + displayedOrderBy,
            type: 'GET',// HTTP通信の種類
            datatype: 'json',//サーバから返されるデータの型
        })
        
        //通信成功の場合の記述
        .done(function (data) {
            displayBooks(data); //引数にデータを持たせておく
        })

        //通信失敗の場合の記述(エラー発生)
        .fail(function (err) {
            displayError(err);
        });
    }

    // 通信成功した場合の画面に表示するための処理
    function displayBooks(data) {
        var hitsData = data.totalItems; 
        var itemsData = data.items; //変数itemsDataにdata{}内のitems{}を代入
        var template = '';

        //検索結果がo件の時
        if (hitsData === 0) {
            swal({
                text: '検索結果が見つかりませんでした'
            }).then(function () {
                jumpApi('bookApi/index.php');
            });
        }

        $.each(itemsData, function (index, el) {
            var bookData = el;
            var titleData = bookData.volumeInfo.title; //本のタイトル
            
            // 作者名が見つからない時
            if (bookData.volumeInfo.authors == undefined) {
                var authorData = "不明"; //"不明"と表示する
            } else {
                var authorData = bookData.volumeInfo.authors; //作者
            }
        
            // 出版社名が見つからない時
            if (bookData.volumeInfo.publisher == undefined) {
                var publisherNameData = "不明"; //"不明"と表示する
            } else {
                var publisherNameData = bookData.volumeInfo.publisher; //出版社
            }
        
            var itemUrl = bookData.volumeInfo.infoLink; //本のURL
        
            if (bookData.volumeInfo.imageLinks) {
                var imageData = bookData.volumeInfo.imageLinks.thumbnail; //画像URL
            } else {
              //画像データがない時
                var imageData = src = "https://dummyimage.com/600x600/cbcbcb/ffffff&text=NO+IMAGE";
            }
        
        
            //↓画面に表示するためのHTMLタグを変数templateへ代入
            template += 
                "<li class='lists__item'>" +
                "<div class='lists__item__inner'>" +
                "<a href='" + itemUrl + "' class='lists__item__link' target='_blank'>" +
                "<img src='" + imageData + "' class='lists__item__img' alt=''>" +
                "<p class='lists__item__detail'>作品名：" + titleData + "</p>" +
                "<p class='lists__item__detail'>作者　：" + authorData + "</p>" +
                "<p class='lists__item__detail'>出版社：" + publisherNameData + "</p>" +
                "</a>" +
                "</div>" +
                "</li>";
        });
        //↓変数templateを挿入
        $("div.lists").prepend(template);
    }

    // 通信失敗した場合のエラーメッセージを表示するための処理
    function displayError(err) {
        var error_code = err.status;
        // 400: リクエストエラー
        if (error_code === 400) {
            swal({
                text: '何も入力されていません'
            }).then(function () {
                jumpApi('bookApi/index.php');
            });
            // $(".search").after("<p class='message' style='color:red; font-weight:bold;'>何も入力されていません</p>");
        }
    }
    
});
    
    