<?php

/*
*
* @package Yariko
*
*/

namespace Wrech\Inc\Base;

use WC_AJAX;
use Wrech\Inc\Classes\Checkout;

class Enqueue{

    public function register(){

        add_action( 'wp_enqueue_scripts',  array($this,'wrech_enqueue_frontend'));

    }

    /**
     * Enqueueing the main scripts with all the javascript logic that this plugin offer
     */
    function wrech_enqueue_frontend(){
        if(true){
	        wp_enqueue_style('main-css', WRECH_PLUGIN_URL . '/assets/css/main.css');
	        wp_enqueue_style('toastr-css', 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css');
	        wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');

	        wp_enqueue_script('toastr-js', 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js' ,array(),'1.0', true);
	        wp_enqueue_script('main-js', WRECH_PLUGIN_URL  . '/assets/js/main.js' ,array('jquery'),'1.0', true);
	        wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js' ,array(),'1.0', true);
	        wp_enqueue_script('checkout-js', WRECH_PLUGIN_URL  . '/assets/js/checkout.js' ,array('jquery','toastr-js','select2-js','woocommerce_stripe'),'1.0', true);
	        //wp_enqueue_script('modal-js', WRECH_PLUGIN_URL  . '/assets/js/modal.js' ,array('jquery','toastr-js','checkout-js'),'1.0', true);
	        //wp_enqueue_script('checkout-js', plugins_url()  . '/woocommerce/assets/js/frontend/checkout.js' ,array('jquery','checkout-js'),'1.0', false);

	        //Remove checkout.js script to have the total control of checkout ajax call
	        wp_dequeue_script( 'wc-checkout' );

	        //Enqueueing the javascript logic for form checkout, process order, update order, etc, It also requires wc_checkout_params to work
	        //todo pass the translable script to js
	        $args = array(
		        'wc_ajax_url'  => WC_AJAX::get_endpoint( "%%endpoint%%" ),
		        'is_checkout' => 1,
		        'option_guest_checkout' => 'yes',
		        'update_order_review_nonce' => wp_create_nonce( 'update-order-review' ),
		        'apply_coupon_nonce' => wp_create_nonce( 'apply-coupon' ),
		        'checkout_url' => WC_AJAX::get_endpoint( "checkout" ),
		        'remove_coupon_nonce' => wp_create_nonce( 'remove-coupon' ),
		        'ajax_url'=> admin_url('admin-ajax.php'),
		        'cart_redirect_after_add' => get_option( 'woocommerce_cart_redirect_after_add' ),
		        'cart_ajax_archive' => get_option('woocommerce_enable_ajax_add_to_cart')
	        );
	        wp_localize_script( 'checkout-js', 'wc_checkout_params', $args );
        }
    }

}