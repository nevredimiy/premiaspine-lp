(function($){
    var $document = $(document);
    $document.ready(function(){
        $(document).find('input[type="radio"][value="Medicare"]').on('change', function (){
            dataLayer.push({'form_medicare_click': 'form_medicare_click'});
        });
        $(document).find('input[type="radio"][value="Medicare Advantage"]').on('change', function (){
            dataLayer.push({'form_medicare_adv_click': 'form_medicare_adv_click'});
        });

        // var geoipOnSuccess = function(geoipResponse) {
        //     if(!getCookie('city')){
        //         document.cookie = "city="+geoipResponse.city.names.en;
        //     }
        //     if(!getCookie('country')){
        //         document.cookie = "country="+geoipResponse.country.names.en;
        //     }
        // };
        //
        // var geoipOnError = function(error) {
        //     console.log(error,'error');
        // };
        //
        // function getCookie(name) {
        //     let matches = document.cookie.match(new RegExp(
        //         "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
        //     ));
        //     return matches ? decodeURIComponent(matches[1]) : undefined;
        // }
        //
        // if (typeof geoip2 !== 'undefined') {
        //     geoip2.city(geoipOnSuccess, geoipOnError);
        // }
    });
})(jQuery);