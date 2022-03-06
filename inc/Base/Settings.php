<?php

/*
*
* @package Yariko
*
*/

namespace Wrech\Inc\Base;

class Settings{

    public function register(){

	    //add_action( 'woocommerce_cart_calculate_fees', array($this, 'elex_discount_price') );

	    add_action( 'customize_preview_init', array($this, 'customizer_load') );

	    add_filter( 'kirki/config', array($this, 'kirki_configuration') );

	    add_filter( 'woocommerce_is_checkout', array($this,'is_checkout'),100);

	    add_filter( 'wc_stripe_params', array($this, 'stripe_scripts_params'),1,1 );

	    add_filter( 'woocommerce_paypal_payments_single_product_renderer_hook', array($this, 'wrech_render_paypal_btn'),1,100);

	    add_filter( 'woocommerce_locate_template', array($this, 'locate_template'), 10, 3 );

	    add_filter( 'woocommerce_checkout_update_order_review_expired', array($this, 'wrech_update_order_review_expired'));

	    add_filter('woocommerce_loop_add_to_cart_link', array($this,'add_link_event_class_to_loop_add_to_cart'),3,100);

    }

	/**
	 * Enqueue js to the customize in order to avoid the modal natural interations
	 */
    function customizer_load(){
	   ?>
	    <script>
		    setTimeout(()=>{
                jQuery('.wrech-float-btn').css('display','block');
                jQuery('.wrech-modal').css('display','block');
                jQuery('.wrech-close-modal,.wrech-float-btn').on('click',function(e){
                    e.preventDefault();
                    e.stopPropagation();
                    jQuery('.wrech-float-btn').css('display','block');
                    jQuery('.wrech-modal').css('display','block');
                    return true;
                });

		    },3000)
	    </script>
		<?php
    }

	/**
	 * @return string[]
	 * Load Kirki config
	 */
	function kirki_configuration() {
		return array( 'url_path'     => WRECH_PLUGIN_PATH . '/inc/kirki/' );
	}

	/**
	 * @param $params
	 *
	 * @return mixed
	 * Forcing Stripe to change the is_checkout parameter passed to the stripe lib enqueue to 'yes'
	 */
	function stripe_scripts_params($params){

		if(!Checkout::is_checkout()){
			$params['is_checkout'] = 'yes';
		}
		return $params;
	}

	/**
	 * @return bool
	 * Change is_checkout flag to use the woocommerce checkout features on any page
	 */
	function is_checkout(){
		if(!is_cart())
			return true;
	}

	/**
	 * @param $template
	 * @param $template_name
	 * @param $template_path
	 *
	 * @return string
	 * Overriding the woocommerce templates
	 */
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

	/**
	 * @param $flag
	 *
	 * @return bool
	 * Don't allow the expire woocommerce validation, this woocommerce validation show an error and dont allow to load the checkout from,
	 * since we have the empty cart validation this is not an issue. Without this hook the order info screen will be empty
	 */
	function wrech_update_order_review_expired ($flag){
		if(!Checkout::is_checkout()) {
			$flag = false;
		}

		return $flag;
	}

	/**
	 * @param $hook
	 *
	 * @return string
	 * Change where the paypal btns will be render, we are using woocommerce_checkout_order_review hook to render those btns in this area.
	 */
	function wrech_render_paypal_btn($hook){

		if(!Checkout::is_checkout()){
			$hook = 'woocommerce_checkout_order_review';
		}
		return $hook;
	}

	/**
	 * @param $link
	 * @param $product
	 * @param $args
	 *
	 * @return string
	 * Inserting class to add to cart button in order to plug ajax event
	 */
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
			'<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
			esc_url( $product->add_to_cart_url() ),
			esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
			esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
			isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
			esc_html( $product->add_to_cart_text() )
		);

	}

	//To use later on
	function elex_discount_price()

	{

		global $woocommerce; //Set the price for user role.

		$discount_price = 5;

		$woocommerce->cart->add_fee( 'Discounted Price', -$discount_price, true, 'standard' );

	}

}