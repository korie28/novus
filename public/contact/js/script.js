$(function () {
    var $requiredElems = $(".required");
    var $patternElems = $(".pattern");
    var $equalToElems = $(".equal-to");
    var $minlengthElems = $(".minlength");
    var $maxlengthElems = $(".maxlength");
    var $showCountElems = $(".showCount");

    // 初回送信前にはエラーを非表示
    var validateAfterFirstSubmit = true;
    var errorClassName = "error-js";

    // エラーメッセージを表示するspan要素を生成(対象の要素, エラーメッセージに付与するクラス名, デフォルトのエラーメッセージ)
    var addError = function ($elem, className, defaultMessage) {
        var errorMessage = defaultMessage;
        var dataError = $elem.data("error-" + className);

        if (dataError) {
            errorMessage = dataError;
        }
        // 送信時にエラーを表示
        if (!validateAfterFirstSubmit) {
            var classVal = errorClassName + " " + className;

            var html =
                '<span class="' +
                classVal +
                '" aria-live="polite">' +
                errorMessage +
                "</span>";
            $elem.parent().append(html);
        }
    };

    // 値が空かどうか検証
    var isValueMissing = function ($elem) {
        var className = "required";
        var $errorSpan = $elem
            .parent()
            .find("." + errorClassName + "." + className);

        if ($elem.val().trim().length === 0) {
            if ($errorSpan.length === 0) {
                addError($elem, className, "入力は必須です");
            }
            return true;
        } else {
            if ($errorSpan.length !== 0) {
                $errorSpan.remove();
            }
            return false;
        }
    };

    // 指定されたパターンにマッチしているかを検証
    var isPatternMismatch = function ($elem) {
        var className = "pattern";
        var pattern = new RegExp("^" + $elem.data(className) + "$");

        if ($elem.data(className) === "email") {
            pattern = /^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/i;
        }
        // エラーを表示するspan要素がすでに存在すれば取得
        var $errorSpan = $elem
            .parent()
            .find("." + errorClassName + "." + className);

        var val = $elem.val().trim();

        // パターンにマッチするか
        if (val !== "") {
            if (!pattern.test($elem.val())) {
                if ($errorSpan.length === 0) {
                    addError($elem, className, "入力された値が正しくありません");
                }
                return true;
            } else {
                if ($errorSpan.length !== 0) {
                    $errorSpan.remove();
                }
                return false;
            }
        } else if (val === "" && $errorSpan.length !== 0) {
            $errorSpan.remove();
        }
    };

    // 指定要素と値が一致するか検証
    var isNotEqualTo = function ($elem) {
        var className = "equal-to";
        var equalTo = $elem.data(className);
        var $equalToElem = $("#" + equalTo);
        // エラーを表示するspan要素を取得（存在しなければnullを返す）
        var $errorSpan = $elem
            .parent()
            .find("." + errorClassName + "." + className);
        // 値が一致するかチェック
        if ($elem.val().trim() !== "" && $equalToElem.val().trim() !== "") {
            if ($equalToElem.val() !== $elem.val()) {
                if ($errorSpan.length === 0) {
                    addError($elem, className, "入力された値が一致しません");
                }
                return true;
            } else {
                if ($errorSpan.length !== 0) {
                    $errorSpan.remove();
                }
                return false;
            }
        }
    };

    // 文字数を返す
    var getValueLength = function (value) {
        return (value.match(/[\uD800-\uDBFF][\uDC00-\uDFFF]|[\s\S]/g) || []).length;
    };

    // 指定された最大文字数を満たしているか検証
    var isTooLong = function ($elem) {
        var className = "maxlength";
        var maxlength = Number($elem.data(className));
        // エラーを表示するspan要素を取得（存在しなければnullを返す）
        var $errorSpan = $elem
            .parent()
            .find("." + errorClassName + "." + className);

        var val = $elem.val().trim();
        if (val !== "") {
            // 文字数を取得
            var valueLength = getValueLength($elem.val());

            if (valueLength > maxlength) {
                if ($errorSpan.length === 0) {
                    addError($elem, className, maxlength + "文字以内で入力ください");
                }
                return true;
            } else {
                if ($errorSpan.length !== 0) {
                    $errorSpan.remove();
                }
                return false;
            }
        } else if (val === "" && $errorSpan.length !== 0) {
            $errorSpan.remove();
        }
    };

    $requiredElems.on("input", function () {
        isValueMissing($(this));
    });

    $patternElems.on("input", function () {
        isPatternMismatch($(this));
    });

    $equalToElems.on("input", function () {
        isNotEqualTo($(this));
    });

    $minlengthElems.on("input", function () {
        isTooShort($(this));
    });

    $maxlengthElems.on("input", function () {
        isTooLong($(this));
    });

    // 入力文字数を表示
    $showCountElems.each(function () {
        const dataMaxlength = $(this).data("maxlength");

        if (dataMaxlength && !isNaN(dataMaxlength)) {
            var countSpanHtml =
                '<span class="countSpan">0</span>/' + parseInt(dataMaxlength);
            
            var countHtml = '<p class="countSpanWrapper">' + countSpanHtml + "</p>";
            
            // 入力文字数を表示するp要素追加
            $(this).after(countHtml);
        }
    });

    $showCountElems.on("input", function () {
        // カウントを出力するspan要素取得
        var $countSpan = $(this).parent().find(".countSpan");

        if ($countSpan.length !== 0) {
            // 入力されている文字数を取得
            var count = getValueLength($(this).val());
            // span 要素に文字数を出力
            $countSpan.text(count);
            var dataMaxlength = $(this).data("maxlength");
            
            if (count > dataMaxlength) {
                $countSpan.css("color", "red");
                $countSpan.addClass("overMaxCount");
            } else {
                $countSpan.css("color", "");
                $countSpan.removeClass("overMaxCount");
            }
        }
    });

    // 送信時の処理
    $(".validationForm").submit(function () {
        validateAfterFirstSubmit = false;

        let isError = false;

        try {
            // 記入漏れがないか
            $requiredElems.each(function () {
                if (isValueMissing($(this))) {
                    isError = true;
                }
            });

            // パターン検証
            $patternElems.each(function () {
                if (isPatternMismatch($(this))) {
                    isError = true;
                }
            });

            // メールアドレスが一致するか
            $equalToElems.each(function () {
                if (isNotEqualTo($(this))) {
                    isError = true;
                }
            });

            // .maxlengthを指定した要素の検証
            $maxlengthElems.each(function () {
                if (isTooLong($(this))) {
                    isError = true;
                }
            });

            // .errorの要素を取得
            var $errorElem = $("." + errorClassName);
            if ($errorElem.length !== 0) {
                var errorElemOffsetTop = $errorElem.offset().top;
                // エラーの要素の位置へスクロール
                window.scrollTo({
                    top: errorElemOffsetTop - 40,
                    // スムーススクロール
                    behavior: "smooth",
                });
            }
        } catch (e) {
            console.log(e);
            return false;
        }

        return !isError;
    });
});
