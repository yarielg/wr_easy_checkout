<?php

/*
*
* @package Yariko
*
*/

namespace Wrech\Inc\Base;

use WC_AJAX;
use Wrech\Inc\Base\Checkout;

class Enqueue{

    public function register(){

        add_action( 'wp_enqueue_scripts',  array($this,'wrech_enqueue_frontend'));
        add_action( 'wp_head',  array($this,'dynamic_settings_styles'));

    }

    /**
     * Enqueueing the main scripts with all the javascript logic that this plugin offer
     */
    function wrech_enqueue_frontend(){

        if(!Checkout::is_checkout()){
	        wp_enqueue_style('main-css', WRECH_PLUGIN_URL . '/assets/css/main.css');
	       // wp_add_inline_style( 'main-css', array($this, 'dynamic_settings_styles') );

            // Third Party js lib
            //todo: we need to have them locally
	        wp_enqueue_style('toastr-css', 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css');
	        wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
	        wp_enqueue_style('swiper-css', 'https://unpkg.com/swiper@8/swiper-bundle.min.css');

	        wp_enqueue_script('toastr-js', 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js' ,array(),'1.0', true);
	        wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js' ,array(),'1.0', true);
	        wp_enqueue_script('swiper-js', 'https://unpkg.com/swiper@8/swiper-bundle.min.js' ,array(),'1.0', true);

	        wp_enqueue_script('main-js', WRECH_PLUGIN_URL  . '/assets/js/main.js' ,array('jquery','swiper-js'),'1.0', true);

	        $checkout_js_dependencies = ['jquery','toastr-js','select2-js'];

            //Enqueue stripe script only of the stripe plugin is activated
	        if(wrech_woocommerce_stripe_is_activate()){
	        	$checkout_js_dependencies[] = 'woocommerce_stripe';
	        }

	        wp_enqueue_script('checkout-js', WRECH_PLUGIN_URL  . '/assets/js/checkout.js' ,$checkout_js_dependencies,'1.0', true);

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

	function dynamic_settings_styles(){
		$mod_settings = wrech_mod_settings();

		?>
		<style wrech_styles>
            /* Cart float button position */
			.wrech-float-btn{
				<?php if($mod_settings['wrech_float_btn_position'] === 'bottom_left'){ ?>
					bottom: 30px;
					left: 30px;
				<?php }
				else if($mod_settings['wrech_float_btn_position'] === 'bottom_right'){ ?>
					bottom: 30px;
					right: 30px;
				<?php }
				else if($mod_settings['wrech_float_btn_position'] === 'up_right'){ ?>
				top: 30px;
				right: 30px;
			<?php }
			else if($mod_settings['wrech_float_btn_position'] === 'up_left'){ ?>
				top: 30px;
				left: 30px;
			<?php } ?>
			}

            /* Modal Position */

            <?php if($mod_settings['wrech_cart_modal_position'] == 'modal_right'){ ?>
            .wrech-modal{
                right: 0;
            }
            @-webkit-keyframes slideIn {
                0%   {
                    opacity:0;
                    transform: translateX(420px);
                }

                100% {
                    opacity: 1;
                    transform: translateX(0);
                }
            }

            @keyframes slideOut {
                0%   {
                    opacity:1;
                    transform: translateX(0);
                }

                100% {
                    opacity: 0;
                    transform: translateX(420px);
                }
            }
            <?php }
            else {  ?>
            .wrech-modal{
                left: 0;
            }
            @-webkit-keyframes slideIn {
                0%   {
                    opacity:0;
                    transform: translateX(-420px);
                }

                100% {
                    opacity: 1;
                    transform: translateX(0);
                }
            }

            @keyframes slideOut {
                0%   {
                    opacity:1;
                    transform: translateX(0);
                }

                100% {
                    opacity: 0;
                    transform: translateX(-420px);
                }
            }
            <?php } ?>

            /* Float Btn Background */
            .wrech-float-btn{
                background: <?php echo $mod_settings['wrech_float_btn_bg'] ?>;
            }
            .wrech-cart-count{
                background: <?php echo $mod_settings['wrech_float_bubble_bg'] ?>;
            }

		</style>
		<?php
	}

}