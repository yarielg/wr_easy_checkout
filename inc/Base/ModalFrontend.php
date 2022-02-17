<?php

/*
*
* @package Yariko
*
*/

namespace Wrech\Inc\Base;

use Wrech\Inc\Base\Checkout;

class ModalFrontend{

    public function register(){



        //add_action( 'woocommerce_before_cart', array($this, 'adding_before_cart') );

	    add_filter( 'woocommerce_paypal_payments_single_product_renderer_hook', array($this, 'wrech_render_paypal_btn'),1,100);

	    add_filter( 'woocommerce_locate_template', array($this, 'locate_template'), 10, 3 );

	    add_filter( 'woocommerce_is_checkout', function(){
	        return true;
        },100);

	    add_filter( 'woocommerce_checkout_update_order_review_expired', array($this, 'wrech_update_order_review_expired'));

        //Forcing Stripe to change the is_checkout parameter passed to the stripe lib enqueue to 'yes'
	    add_filter( 'wc_stripe_params', function($params){

		    if(!Checkout::is_checkout() && !is_cart()){
		        $params['is_checkout'] = 'yes';
            }
		    return $params;
	    },1,1 );

	    /*add_filter( 'woocommerce_is_checkout', function(){
		    $page_id = wc_get_page_id( 'checkout' );

		    return ( $page_id && is_page( $page_id ) ) || wc_post_content_has_shortcode( 'woocommerce_checkout' );
	    },100);*/

	    //Inserting class to add to cart button in order to plug ajax event
        add_filter('woocommerce_loop_add_to_cart_link', array($this,'add_link_event_class_to_loop_add_to_cart'),3,100);

	    add_action('wp_footer', array($this, 'add_modal'),100);
    }

    function wrech_update_order_review_expired ($flag){
	    if(!Checkout::is_checkout() && !is_cart()) {
		    $flag = false;
	    }

	    return $flag;
    }

    function wrech_render_paypal_btn($hook){

	    if(!Checkout::is_checkout() && !is_cart()){
	        $hook = 'woocommerce_checkout_order_review';
	    }
        return $hook;
    }

    function add_link_event_class_to_loop_add_to_cart($link,$product,$args){

	    //Adding a custom in case the add_to_cart class does not exist
        if($product->get_type() == 'simple'){
	        $args['class'] = $args['class'] . ' wrech_add_to_cart_button';
        }

	    /**
	     * Make sure the simple product on loop have the data-product_id attribute
         * This attr is used in add_to_cart ajax call.
	     */
        if(!array_key_exists('data-product_id', $args['attributes'])){
	        $args['attributes']['data_product_id'] = $product->get_id();
        }

	    return sprintf(
		    '<a href="%s" data-quantity="%s" class="%s tete" %s>%s</a>',
		    esc_url( $product->add_to_cart_url() ),
		    esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
		    esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
		    isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
		    esc_html( $product->add_to_cart_text() )
	    );

    }

    function add_modal(){

        if(!Checkout::is_checkout() && !is_cart()){
        ?>
        <div class="woocommerce wrech-modal" style="display: none">
            <div class="wrech-mask-container">
                <div class="wrech-mask-loading"></div>
                <img class="mask-icon" src="<?php echo WRECH_PLUGIN_URL . '/assets/images/loading.svg' ?>" alt="">
            </div>
            <div class="wrech-header-modal">
                <?php echo Checkout::coupon() ?>
                <img class="wrech-close-modal" src="<?php echo WRECH_PLUGIN_URL . '/assets/images/close.png' ?>" alt="">

            </div>
            <div class="wrech-main">
                <div class="wrech-step  wrech-step-cart">
                    <?php
                    echo Checkout::cart();
                    ?>
                </div>

                <div class="wrech-checkout-form">
	                <?php echo  Checkout::checkout_info_form(); ?>
                </div>
            </div>
            <div class="wrech-footer-modal">
                <!--todo add the translation here -->
                <div class="wrech-actions">
                    <button class="wrech-cart-btn wrech-btn">Cart</button>
                    <button class="wrech-order-info-btn wrech-btn">Order Info</button>
                    <button class="wrech-payment-btn wrech-btn">Payment</button>
                </div>
            </div>
        </div>

        <div class="wrech-float-btn">
            <?php Checkout::cart_float_btn(); ?>
        </div>

        <?php
        }
    }

	function locate_template( $template, $template_name, $template_path ) {
		$basename = basename( $template );
        //todo: Replace with a function that return true to the page that easy checkout is allowed
        switch ($basename){
            case 'error.php':
                $template = WRECH_PLUGIN_PATH . 'templates/error.php';
                break;
            case 'success.php':
                $template = WRECH_PLUGIN_PATH . 'templates/success.php';
                break;
            case 'notice.php':
                $template = WRECH_PLUGIN_PATH . 'templates/notice.php';
                break;
	       /* case 'mini-cart.php':
		        $template = WRECH_PLUGIN_PATH . 'templates/mini-cart.php';
		        break;*/
        }

		return $template;
	}

}
