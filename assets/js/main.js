(function(window, $) {
    "use strict";

    function MapHandler(){
        var self = this;

        self.init();
    }

    MapHandler.prototype = {

        init: function () {
            var self = this;
        },
        initMap(){

        },
        loadMarkersAjax(map) {

        },
        getLocation(){

        },
        showPosition(position) {

        },
        calcCrow(lat1, lon1, lat2, lon2)
        {

        },
        toRad(value)
        {
            return value * Math.PI / 180;
        },
        getLocationZipCode(){

        },
        generateClick(id) {

        },
        preventingPlaceOrder(){

        },
        addressComponent(address_component){

        },
        cleanFields(){

        },
        initAddressAuto(){

        },
        toggleMap(){
        },
        checkDealerFields(){

        },
        processAddDealer(){

        }

    }

    window.Kgm = window.Kgm || {};
    window.Kgm.MapHandler = new MapHandler();

    /*$(document).ready(function(){
        $('body').on('change', '.woocommerce-checkout input', function(){
            console.log($(this).val())
            $('body').trigger("update_checkout");
        })
    });*/

})(window, window.jQuery);


