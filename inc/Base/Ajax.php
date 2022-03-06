<?php

/*
*
* @package Yariko
*
*/

namespace Wrech\Inc\Base;

use Wrech\Inc\Base\Checkout;
use WC_AJAX;

class Ajax {

    public function register(){

        /**
         * Ajax actions
         */
        add_action( 'wp_ajax_nopriv_wrech_remove_item_cart', array($this,'remove_item_cart'));
        add_action( 'wp_ajax_wrech_remove_item_cart', array($this,'remove_item_cart'));

        add_action( 'wp_ajax_nopriv_wrech_add_to_cart', array($this,'add_to_cart'));
        add_action( 'wp_ajax_wrech_add_to_cart', array($this,'add_to_cart'));

	    add_action( 'wp_ajax_nopriv_wrech_update_quantity_item', array($this,'update_quantity_item'));
	    add_action( 'wp_ajax_wrech_update_quantity_item', array($this,'update_quantity_item'));

	    //Get the html content checkout
	    add_action( 'wp_ajax_nopriv_wrech_get_checkout_html', array($this,'get_checkout_html'));
	    add_action( 'wp_ajax_wrech_get_checkout_html', array($this,'get_checkout_html'));

	    add_action( 'wp_ajax_nopriv_wrech_update_order_review', array($this,'update_order_review'));
	    add_action( 'wp_ajax_wrech_update_order_review', array($this,'update_order_review'));

	    //ADMIN AJAX CALLS
	    add_action( 'wp_ajax_wrech_save_customizations', array($this,'save_customizations'));
	    add_action( 'wp_ajax_wrech_add_cart_icon', array($this,'add_cart_icon'));
	    add_action( 'wp_ajax_wrech_default_icon', array($this,'default_icon'));
	    add_action( 'wp_ajax_wrech_get_pages', array($this,'get_pages'));

    }

	/**
	 * Get all the site pages
	 */
	function get_pages(){

		$cart_page_id = get_option( 'woocommerce_cart_page_id' );
		$checkout_page_id = get_option( 'woocommerce_checkout_page_id' );

		$args = array(
			'sort_order' => 'asc',
			'sort_column' => 'ID',
			'hierarchical' => 1,
			'child_of' => 0,
			'parent' => -1,
			'post_type' => 'page',
			'post_status' => 'publish',
			'exclude' => array($cart_page_id,$checkout_page_id),
		);
		$pages = get_pages($args); // get all pages based on supplied args

		array_push($pages,array('ID' => 1, 'post_title' => 'Home'));

		$excluded_page_id = wrech_settings('excluded_pages');

		$excluded_page_id = $excluded_page_id !== '' ? explode(',', wrech_settings('excluded_pages')) : [];

		//Converting array value into int, this is needed for the multiple select component to work
		$excluded_pages = array();
		foreach ($excluded_page_id as $page_id){
			$excluded_pages[] = intval($page_id);
		}

		echo  json_encode(array('success' => true, 'pages' => $pages, 'excluded_pages' => $excluded_pages));
		wp_die();

	}

	/**
	 * Putting back the default icon
	 */
	function default_icon(){
		wrech_delete_field_settings('cart_icon_url');
		wrech_delete_field_settings('cart_icon_id');

		echo  json_encode(array('success' => true));
		wp_die();
	}

	/**
	 * Upload the cart Icon
	 */
	function add_cart_icon(){

		$image_id = wrech_upload_file($_FILES['file']);

		if($image_id > 0){

			$url = wp_get_attachment_url( $image_id );

			wrech_save_settings(array(
				'cart_icon_id' => $image_id,
				'cart_icon_url' => 	$url
				)
			);

			echo  json_encode(array('success' => true, 'cart_icon' => array('id' => $image_id, 'url'=> $url)));
			wp_die();
		}

		echo  json_encode(array('success' => false, 'msg' => 'The image was not uploaded'));
		wp_die();
	}

	/**
	 * Save the params on the customization tab
	 */
	function save_customizations(){

		$settings = array();
		$settings['excluded_pages'] = $_POST['excluded_pages'];

		wrech_save_settings($settings);
		echo  json_encode(array('success' => true, 'post' => $_POST));
		wp_die();
		/*echo  json_encode(array('success' => false, 'msg' => 'The settings were not saved'));
		wp_die();*/
	}

	/**
	 * AJAX update order review on checkout.
	 * check changes on update_order_review function inside of class-wc-ajax.php
	 */
	public static function update_order_review() {
		check_ajax_referer( 'update-order-review', 'security' );

		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		if ( WC()->cart->is_empty() && ! is_customize_preview() && apply_filters( 'woocommerce_checkout_update_order_review_expired', true ) ) {
			echo  json_encode(array('success' => false, 'msg' => __( 'Sorry, your session has expired.', 'woocommerce' )) );
			wp_die();
		}

		do_action( 'woocommerce_checkout_update_order_review', isset( $_POST['post_data'] ) ? wp_unslash( $_POST['post_data'] ) : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
		$posted_shipping_methods = isset( $_POST['shipping_method'] ) ? wc_clean( wp_unslash( $_POST['shipping_method'] ) ) : array();

		if ( is_array( $posted_shipping_methods ) ) {
			foreach ( $posted_shipping_methods as $i => $value ) {
				$chosen_shipping_methods[ $i ] = $value;
			}
		}

		WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
		WC()->session->set( 'chosen_payment_method', empty( $_POST['payment_method'] ) ? '' : wc_clean( wp_unslash( $_POST['payment_method'] ) ) );
		WC()->customer->set_props(
			array(
				'billing_country'   => isset( $_POST['country'] ) ? wc_clean( wp_unslash( $_POST['country'] ) ) : null,
				'billing_state'     => isset( $_POST['state'] ) ? wc_clean( wp_unslash( $_POST['state'] ) ) : null,
				'billing_postcode'  => isset( $_POST['postcode'] ) ? wc_clean( wp_unslash( $_POST['postcode'] ) ) : null,
				'billing_city'      => isset( $_POST['city'] ) ? wc_clean( wp_unslash( $_POST['city'] ) ) : null,
				'billing_address_1' => isset( $_POST['address'] ) ? wc_clean( wp_unslash( $_POST['address'] ) ) : null,
				'billing_address_2' => isset( $_POST['address_2'] ) ? wc_clean( wp_unslash( $_POST['address_2'] ) ) : null,
			)
		);

		if ( wc_ship_to_billing_address_only() ) {
			WC()->customer->set_props(
				array(
					'shipping_country'   => isset( $_POST['country'] ) ? wc_clean( wp_unslash( $_POST['country'] ) ) : null,
					'shipping_state'     => isset( $_POST['state'] ) ? wc_clean( wp_unslash( $_POST['state'] ) ) : null,
					'shipping_postcode'  => isset( $_POST['postcode'] ) ? wc_clean( wp_unslash( $_POST['postcode'] ) ) : null,
					'shipping_city'      => isset( $_POST['city'] ) ? wc_clean( wp_unslash( $_POST['city'] ) ) : null,
					'shipping_address_1' => isset( $_POST['address'] ) ? wc_clean( wp_unslash( $_POST['address'] ) ) : null,
					'shipping_address_2' => isset( $_POST['address_2'] ) ? wc_clean( wp_unslash( $_POST['address_2'] ) ) : null,
				)
			);
		} else {
			WC()->customer->set_props(
				array(
					'shipping_country'   => isset( $_POST['s_country'] ) ? wc_clean( wp_unslash( $_POST['s_country'] ) ) : null,
					'shipping_state'     => isset( $_POST['s_state'] ) ? wc_clean( wp_unslash( $_POST['s_state'] ) ) : null,
					'shipping_postcode'  => isset( $_POST['s_postcode'] ) ? wc_clean( wp_unslash( $_POST['s_postcode'] ) ) : null,
					'shipping_city'      => isset( $_POST['s_city'] ) ? wc_clean( wp_unslash( $_POST['s_city'] ) ) : null,
					'shipping_address_1' => isset( $_POST['s_address'] ) ? wc_clean( wp_unslash( $_POST['s_address'] ) ) : null,
					'shipping_address_2' => isset( $_POST['s_address_2'] ) ? wc_clean( wp_unslash( $_POST['s_address_2'] ) ) : null,
				)
			);
		}

		if ( isset( $_POST['has_full_address'] ) && wc_string_to_bool( wc_clean( wp_unslash( $_POST['has_full_address'] ) ) ) ) {
			WC()->customer->set_calculated_shipping( true );
		} else {
			WC()->customer->set_calculated_shipping( false );
		}

		WC()->customer->save();

		// Calculate shipping before totals. This will ensure any shipping methods that affect things like taxes are chosen prior to final totals being calculated. Ref: #22708.
		WC()->cart->calculate_shipping();
		WC()->cart->calculate_totals();

		// Get order review fragment.
		ob_start();
		woocommerce_order_review();
		$woocommerce_order_review = ob_get_clean();

		// Get checkout payment fragment.
		ob_start();
		woocommerce_checkout_payment();
		$woocommerce_checkout_payment = ob_get_clean();

		// Get messages if reload checkout is not true.
		$reload_checkout = isset( WC()->session->reload_checkout );
		if ( ! $reload_checkout ) {
			$messages = wc_print_notices( true );
		} else {
			$messages = '';
		}

		unset( WC()->session->refresh_totals, WC()->session->reload_checkout );

		wp_send_json(
			array(
				'result'    => empty( $messages ) ? 'success' : 'failure',
				'messages'  => $messages,
				'reload'    => $reload_checkout,
				'fragments' => apply_filters(
					'woocommerce_update_order_review_fragments',
					array(
						'.woocommerce-checkout-review-order-table' => $woocommerce_order_review,
						'.woocommerce-checkout-payment' => $woocommerce_checkout_payment,
					)
				),
			)
		);
	}

    function get_checkout_html(){
	    echo  json_encode(array('html' => Checkout::checkout_info_form()));
	    wp_die();
    }

    /**
     * Remove item cart
     */
    function remove_item_cart(){
	    ob_start();

	    $cart_item_key = wc_clean( isset( $_POST['cart_item_key'] ) ? wp_unslash( $_POST['cart_item_key'] ) : '' );

	    if ( $cart_item_key && false !== WC()->cart->remove_cart_item( $cart_item_key ) ) {
		    woocommerce_mini_cart();

		    $mini_cart = ob_get_clean();

		    $data = array(
			    'fragments' => apply_filters(
				    'woocommerce_add_to_cart_fragments',
				    array(
					    'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
				    )
			    ),
			    'cart_hash' => WC()->cart->get_cart_hash(),
			    'count' => WC()->cart->get_cart_contents_count()
		    );
	    } else {
		    wp_send_json_error();
	    }

	    echo  json_encode(array('html' => Checkout::cart(), 'data' => $data));
        wp_die();
    }

	/**
	 * Add cart item
	 * @throws \Exception
	 */
	function add_to_cart(){
		ob_start();

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['product_id'] ) ) {
			return;
		}

		$product_id        = apply_filters( 'wrech_add_to_cart_product_id', absint( $_POST['product_id'] ) );
		$product           = wc_get_product( $product_id );
		$quantity          = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_POST['quantity'] ) );
		$passed_validation = apply_filters( 'wrech_add_to_cart_validation', true, $product_id, $quantity );
		$product_status    = get_post_status( $product_id );
		$variation_id      = 0;
		$variation         = array();

		if ( $product && 'variation' === $product->get_type() ) {
			$variation_id = $product_id;
			$product_id   = $product->get_parent_id();
			$variation    = $product->get_variation_attributes();
		}

		if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation ) && 'publish' === $product_status ) {

			do_action( 'woocommerce_ajax_added_to_cart', $product_id );

			if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
				wc_add_to_cart_message( array( $product_id => $quantity ), true );
			}
			woocommerce_mini_cart();

			$mini_cart = ob_get_clean();

			$data = array(
				'fragments' => apply_filters(
					'woocommerce_add_to_cart_fragments',
					array(
						'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
					)
				),
				'cart_hash' => WC()->cart->get_cart_hash(),
				'count' => WC()->cart->get_cart_contents_count()
			);

		} else {
			wp_send_json_error();
		}
		echo  json_encode(array('html' => Checkout::cart(), 'data' => $data));
		//echo $removed ? json_encode(array('success' => true, 'msg' => __( 'Product Removed' ))) : json_encode(array('success' => false, 'msg' => __( 'We could not remove the product' )));
		wp_die();
	}

	/**
	 * Update cart item quanity
	 */

	function update_quantity_item(){
		ob_start();

		$cart_item_key = wc_clean( isset( $_POST['cart_item_key'] ) ? wp_unslash( $_POST['cart_item_key'] ) : '' );
		$cart_quantity = wc_clean( isset( $_POST['cart_quantity'] ) ? wp_unslash( $_POST['cart_quantity'] ) : 1 );

		if ( $cart_item_key && false !== WC()->cart->set_quantity( $cart_item_key,  $cart_quantity) ) {
			woocommerce_mini_cart();

			$mini_cart = ob_get_clean();

			$data = array(
				'fragments' => apply_filters(
					'woocommerce_add_to_cart_fragments',
					array(
						'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
					)
				),
				'cart_hash' => WC()->cart->get_cart_hash(),
				'count' => WC()->cart->get_cart_contents_count(),
				'quantity' => $cart_quantity
			);
		} else {
			wp_send_json_error();
		}

		echo  json_encode(array('html' => Checkout::cart(), 'data' => $data));
		wp_die();

	}

}