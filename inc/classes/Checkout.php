<?php

/*
*
* @package Yariko
* Class to dealing with the html element of checkout
*/

namespace Wrech\Inc\Classes;

class Checkout{

	public static function checkout() {
		return \WC_Checkout::instance();
	}

	public static function cart(){
        ob_start();
        if ( ! WC()->cart->is_empty() ){
            ?>
            <ul class="wrech-cart-wrapper">
                <?php
                foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                    $_product   = apply_filters( 'wrech_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                    $product_id = apply_filters( 'wrech_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                    if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                        $product_name      = apply_filters( 'wrech_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
                        $thumbnail         = apply_filters( 'wrech_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                        $product_price     = apply_filters( 'wrech_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
                        $product_permalink = apply_filters( 'wrech_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                        ?>
                        <li class=" <?php echo esc_attr( apply_filters( 'wrech_mini_cart_item_class', 'wrech_mini_cart_item', $cart_item, $cart_item_key ) ); ?>">
                            <div class="wrech-col wrech-col-2">
	                            <?php if ( empty( $product_permalink ) ) : ?>
		                            <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	                            <?php else : ?>
                                    <a href="<?php echo esc_url( $product_permalink ); ?>">
			                            <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                    </a>
	                            <?php endif; ?>
                            </div>
                            <?php /*wc_get_formatted_cart_item_data( $cart_item );  */?>
                            <?php echo "<div class='wrech-col wrech-col-7'>
                                            <span class='wrech-cart-item-name'>" . $product_name . "</span>
                                            <span class='wrech-change-quantity wrech-quantity-decrease'>-</span>
                                            <input min='0' data-cart_item_key='".$cart_item_key."' type='number' class='wrech-quantity' value='" .$cart_item['quantity']. "' >
                                            <span class='wrech-change-quantity wrech-quantity-increase'>+</span>
                                        </div>"; ?>

	                        <?php
	                        echo "<div class='wrech-col wrech-col-3'> "
                                 . "<span style='float: right' class='wrech-cart-price'>".$product_price."</span><br>"
                                 .  apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		                        'woocommerce_cart_item_remove_link',
		                        sprintf(
			                        '<a href="'.esc_url( wc_get_cart_remove_url( $cart_item_key ) ).'" class="wrech_cart_item_remove" aria-label="'.esc_attr__( 'Remove this item', 'woocommerce' ).'" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s"><img src="'.WRECH_PLUGIN_URL . '/assets/images/close.png'.'" class="wrech_remove_item_img" alt=""></a>',
			                        esc_attr( $product_id ),
			                        esc_attr( $cart_item_key ),
			                        esc_attr( $_product->get_sku() )
		                        ),
		                        $cart_item_key
	                        ) . "</div>";
	                        ?>
                        </li>
                        <?php
                    }
                }
                ?>
            </ul>
            <?php
        }else{
            //todo translation
            ?> <div class="wrech-empty-cart"><img src="<?php echo WRECH_PLUGIN_URL . '/assets/images/empty-cart.png' ?>" alt=""></div><?php
        }

        return ob_get_clean();

    }

	/**
	 * Is_checkout - Returns true when viewing the checkout page.
	 *
	 * @return bool
	 */
	public static function is_checkout() {
		$page_id = wc_get_page_id( 'checkout' );

		return ( $page_id && is_page( $page_id ) ) || wc_post_content_has_shortcode( 'woocommerce_checkout' );
	}

	public static function checkout_form($checkout){

		ob_start();

		?>

		<form name="checkout" method="post" class="wrech-checkout checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">
            <!-- Diferenciate the ajax call-->
            <input type="hidden" name="easy_checkout" value="true">
			<?php if ( $checkout->get_checkout_fields() ) : ?>

				<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

				<div class="wrech-step wrech-step-info" id="customer_details">
					<div class="customer-details-section">
						<?php do_action( 'woocommerce_checkout_billing' ); ?>
					</div>

					<div class="customer-details-section">
						<?php wc_get_template( 'checkout/form-shipping.php', array( 'checkout' => $checkout ) ); ?>
					</div>
				</div>

				<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

			<?php endif; ?>

			<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

			<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

			<div id="" class="woocommerce-checkout-review-order wrech-step wrech-step-payment">
				<?php do_action( 'woocommerce_checkout_order_review' ); ?>
			</div>

			<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

		</form>

		<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

		<?php

		return ob_get_clean();
	}

	public static function coupon(){

        ob_start();

		if ( ! wc_coupons_enabled() ) { // @codingStandardsIgnoreLine.
			return '';
		}
        ?>
        <form id="wrech-coupon-form" class="wrech-coupon-form wrech-checkout_coupon" method="post">

                <input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" id="wrech-coupon_code" value="" />
                <button id="wrech-apply-coupon" type="submit" class="button wrech-btn" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>

            <div class="clear"></div>

        </form>

        <?php

		return ob_get_clean();
    }

	public static function checkout_info_form(){

		$checkout = self::checkout();
        ob_start();
		echo self::login_form();

		echo self::checkout_form($checkout);
		return ob_get_clean();
    }

    public static function login_form(){
	    ob_start();
	    if ( is_user_logged_in() || 'no' === get_option( 'woocommerce_enable_checkout_login_reminder' ) ) {
		    return;
	    }

	    ?>
        <div class="woocommerce-form-login-toggle">
		    <?php wc_print_notice( apply_filters( 'woocommerce_checkout_login_message', esc_html__( 'Returning customer?', 'woocommerce' ) ) . ' <a href="#" class="showlogin">' . esc_html__( 'Click here to login', 'woocommerce' ) . '</a>', 'notice' ); ?>
        </div>
	    <?php

	    woocommerce_login_form(
		    array(
			    'message'  => esc_html__( 'If you have shopped with us before, please enter your details below. If you are a new customer, please proceed to the Billing section.', 'woocommerce' ),
			    'redirect' => wc_get_checkout_url(),
			    'hidden'   => true,
		    )
	    );
    }

    public static function cart_float_btn(){
	    ob_start();
	    $cart_count = WC()->cart->get_cart_contents_count();
	    ?>
        <span class="wrech-cart-count"><?php echo $cart_count ?></span>
        <img src="<?php echo WRECH_PLUGIN_URL . '/assets/images/cart.png' ?>" alt="">
        <?php

        echo ob_get_clean();
    }


}
