/*********************************
 * Themes, rules, and i18n support
 * Locale: Uzbek; O'zbek
 *********************************/
(function(factory) {
    typeof module === "object" && module.exports ? module.exports = factory( require( "jquery" ) ) :
    typeof define === 'function' && define.amd ? define(['jquery'], factory) :
    factory(jQuery);
}(function($) {

    /* Global configuration
     */
    $.validator.config({
        //stopOnError: true,
        //focusCleanup: true,
        //theme: 'yellow_right',
        //timely: 2,

        // Custom rules
        rules: {
            digits: [/^\d+$/, "Raqam kiriting"]
            ,letters: [/^[a-z]+$/i, "Harflar kiriting"]
            ,date: [/^\d{4}-\d{2}-\d{2}$/, "To'g'ri sana kiriting, format:yyyy-mm-dd"]
            ,time: [/^([01]\d|2[0-3])(:[0-5]\d){1,2}$/, "To'g'ri vaqt kiriting, 00:00 dan 23:59 gacha"]
            ,email: [/^[\w\+\-]+(\.[\w\+\-]+)*@[a-z\d\-]+(\.[a-z\d\-]+)*\.([a-z]{2,4})$/i, "To'g'ri elektron pochta kiriting"]
            ,url: [/^(https?|s?ftp):\/\/\S+$/i, "To'g'ri havola kiriting"]
            ,qq: [/^[1-9]\d{4,}$/, "To'g'ri QQ raqamini kiriting"]
            ,IDcard: [/^\d{6}(19|2\d)?\d{2}(0[1-9]|1[012])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)?$/, "To'g'ri shaxs guvohnomasi raqamini kiriting"]
            ,tel: [/^(?:(?:0\d{2,3}[\- ]?[1-9]\d{6,7})|(?:[48]00[\- ]?[1-9]\d{6}))$/, "To'g'ri telefon raqamini kiriting"]
            ,mobile: [/^1[3-9]\d{9}$/, "To'g'ri mobil raqam kiriting"]
            ,zipcode: [/^\d{6}$/, "Pochta indeksini tekshiring"]
            ,chinese: [/^[\u0391-\uFFE5]+$/, "Xitoycha belgilar kiriting"]
            ,username: [/^\w{3,12}$/, "3-12 ta raqam, harf yoki pastki chiziq kiriting"]
            ,password: [/^[\S]{6,16}$/, "6-16 ta belgi kiriting, bo'sh joy bo'lmasin"]
            ,accept: function (element, params){
                if (!params) return true;
                var ext = params[0],
                    value = $(element).val();
                return (ext === '*') ||
                       (new RegExp(".(?:" + ext + ")$", "i")).test(value) ||
                       this.renderMsg("Faqat {1} kengaytmali fayllar qabul qilinadi", ext.replace(/\|/g, ','));
            }

        },

        // Default error messages
        messages: {
            0: "Bu joy",
            fallback: "{0} formati noto'g'ri",
            loading: "Tekshirilmoqda...",
            error: "Tarmoq xatosi",
            timeout: "So'rov vaqti tugadi",
            required: "{0} bo'sh bo'lmasligi kerak",
            remote: "{0} allaqachon ishlatilgan",
            integer: {
                '*': "Butun son kiriting",
                '+': "Musbat butun son kiriting",
                '+0': "Musbat butun son yoki 0 kiriting",
                '-': "Manfiy butun son kiriting",
                '-0': "Manfiy butun son yoki 0 kiriting"
            },
            match: {
                eq: "{0} va {1} mos emas",
                neq: "{0} va {1} bir xil bo'lmasligi kerak",
                lt: "{0} {1} dan kichik bo'lishi kerak",
                gt: "{0} {1} dan katta bo'lishi kerak",
                lte: "{0} {1} dan katta bo'lmasligi kerak",
                gte: "{0} {1} dan kichik bo'lmasligi kerak"
            },
            range: {
                rg: "{1} dan {2} gacha son kiriting",
                gte: "{1} dan kichik bo'lmagan son kiriting",
                lte: "Eng ko'p {1} son kiriting",
                gtlt: "{1} dan {2} gacha son kiriting",
                gt: "{1} dan katta son kiriting",
                lt: "{1} dan kichik son kiriting"
            },
            checked: {
                eq: "{1} ta tanlang",
                rg: "{1} dan {2} ta tanlang",
                gte: "Kamida {1} ta tanlang",
                lte: "Eng ko'p {1} ta tanlang"
            },
            length: {
                eq: "{1} ta belgi kiriting",
                rg: "{1} dan {2} ta belgi kiriting",
                gte: "Kamida {1} ta belgi kiriting",
                lte: "Eng ko'p {1} ta belgi kiriting",
                eq_2: "",
                rg_2: "",
                gte_2: "",
                lte_2: ""
            }
        }
    });

    /* Themes
     */
    var TPL_ARROW = '<span class="n-arrow"><b>◆</b><i>◆</i></span>';
    $.validator.setTheme({
        'simple_right': {
            formClass: 'n-simple',
            msgClass: 'n-right'
        },
        'simple_bottom': {
            formClass: 'n-simple',
            msgClass: 'n-bottom'
        },
        'yellow_top': {
            formClass: 'n-yellow',
            msgClass: 'n-top',
            msgArrow: TPL_ARROW
        },
        'yellow_right': {
            formClass: 'n-yellow',
            msgClass: 'n-right',
            msgArrow: TPL_ARROW
        },
        'yellow_right_effect': {
            formClass: 'n-yellow',
            msgClass: 'n-right',
            msgArrow: TPL_ARROW,
            msgShow: function($msgbox, type){
                var $el = $msgbox.children();
                if ($el.is(':animated')) return;
                if (type === 'error') {
                    $el.css({left: '20px', opacity: 0})
                        .delay(100).show().stop()
                        .animate({left: '-4px', opacity: 1}, 150)
                        .animate({left: '3px'}, 80)
                        .animate({left: 0}, 80);
                } else {
                    $el.css({left: 0, opacity: 1}).fadeIn(200);
                }
            },
            msgHide: function($msgbox, type){
                var $el = $msgbox.children();
                $el.stop().delay(100).show()
                    .animate({left: '20px', opacity: 0}, 300, function(){
                        $msgbox.hide();
                    });
            }
        }
    });
}));
