(function(window, $) {
    "use strict";

    function ModalHandler(){
        var self = this;

    }

    ModalHandler.prototype = {

        init: function () {
            var self = this;
            this.initSlider();
            this.footerProductsInterations();
        },
        initSlider(){
            const swiper = new Swiper('.swiper', {
                // Optional parameters
                loop: false,
                // Navigation arrows
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },

            });
        },
        footerProductsInterations(){

            $('.wrech-products-slider').on('mouseover', function(e){
                $('.wrech-swipper-prev, .wrech-swipper-next').attr('style','display: flex !important');
            });

            $('.wrech-products-slider').on('mouseout', function(e){
                $('.wrech-swipper-prev, .wrech-swipper-next').attr('style','display: none !important');
            });
        }

    }

    window.Wrech = window.Wrech || {};
    window.Wrech.ModalHandler = new ModalHandler();

    $(document).ready(function(){
        if($('body').hasClass('wp-customizer')){
            alert(' Customizer');
        }
        window.Wrech.ModalHandler.init();
    });

})(window, window.jQuery);


