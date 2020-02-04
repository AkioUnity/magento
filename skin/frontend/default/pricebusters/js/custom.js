/*
 *  jQuery custom js
 *  
 */
(function ($, window, document) {
    /*var e = $("#co-shipping-method-form").length;
    var p = $("#co-shipping-method-form").find('.s_method_flatrate_flatrate span').html()
    console.log(e + p);*/

}(jQuery, window, document));
window.onload = function () {

    //sessionStorage.setItem('lastloc', window.location.href);
    //console.log(sessionStorage.getItem("lastloc"));
    /*var x = getCookie('lastfilter');
    console.log(x);
    if (x) {
        console.log(x);
        setCookie('filterkey', x ,7);
        eraseCookie(x);
    }*/
    //console.log('Product Detaiils Page!!');
    /*if (sessionStorage.getItem("lastfilter") !== null) {
        console.log(sessionStorage.getItem("lastfilter"));
        sessionStorage.setItem('filterkey', sessionStorage.getItem("lastfilter"));
        sessionStorage.removeItem('lastfilter');
    }*/


    /*function setCookie(name,value,days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    }
    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    }
    function eraseCookie(name) {   
        document.cookie = name+'=; Max-Age=-99999999;';  
    }*/

}

jQuery(document).ready(function () {
    jQuery('#pimgBlock').click(function (e) {
        jQuery('#show-full-product-img').attr('src', '');
        jQuery('#pimgBlock').fadeOut();
    });

    jQuery('.pimgthumb').click(function (e) {
        jQuery('#mpimg').attr('href', jQuery(this).data('img'));
        jQuery('#show-full-product-img').attr('src', jQuery(this).data('img'));
        jQuery('#pimgBlock').fadeIn();
        jQuery('.product-image.product-image-zoom').find('#image').attr('src', jQuery(this).data('img'));
        jQuery('#image').magnify();
    });
    //jQuery("a.p_image").fancybox();
    if (jQuery(window).width() <= 767) {
        jQuery("footer .fc .foot h1").click(function () {
            jQuery(this).toggleClass('shweta');
            jQuery(this).parents('div').children('.lineHite1').slideToggle('slow');
        });
    }
});
