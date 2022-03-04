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

	    add_action('wp_footer', array($this, 'modal'),100);
    }

    function modal(){

        if(!Checkout::is_checkout()){
        ?>

        <!-- Modal -->
        <div class="woocommerce wrech-modal" style="display: none">

            <!-- Modal Mask -->
            <div class="wrech-mask-container">
                <div class="wrech-mask-loading"></div>
                <img class="mask-icon" src="<?php echo WRECH_PLUGIN_URL . '/assets/images/loading.svg' ?>" alt="">
            </div>

            <!-- Modal Header -->
            <div class="wrech-header-modal">
                <?php echo Checkout::coupon() ?>
                <img class="wrech-close-modal" src="<?php echo WRECH_PLUGIN_URL . '/assets/images/close.png' ?>" alt="">
            </div>

            <!-- Modal Content -->
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

            <!-- Modal Footer -->
            <div class="wrech-footer-modal">
                <!--todo add the translation here -->
                <div class="wrech-actions">
                    <button class="wrech-cart-btn wrech-btn">Cart</button>
                    <button class="wrech-order-info-btn wrech-btn">Order Info</button>
                    <button class="wrech-payment-btn wrech-btn">Payment</button>
                </div>

                <?php $this->bestseller_products() ?>

            </div>
        </div> <!-- End Modal -->

        <!-- Modal Float Btn -->
        <div class="wrech-float-btn">
            <?php Checkout::cart_float_btn(); ?>
        </div>

        <?php
        }
    }

    function bestseller_products(){
	    $args = array(
		    'post_type' => 'product',
		    'meta_key' => 'total_sales',
		    'orderby' => 'meta_value_num',
		    'posts_per_page' => 5,
	    );
	    $loop = new \WP_Query( $args );

	    ob_start();
	    ?>
        <p class="wrech-header-label">BEST SELLER</p>
	    <div class="wrech-products-slider">
            <div class="swiper">
                <!-- Additional required wrapper -->
                <div class="swiper-wrapper">
                    <!-- Slides -->
                    <?php
                    while($loop->have_posts()){
                        $loop->the_post();
                        $product_id = get_the_ID();
                        $product_permalink = get_permalink( $product_id );
                        $product = wc_get_product($product_id);
                        $type = $product->get_type();
                        $product_class_btn = $type === 'simple' ? 'wrech_add_to_cart_button' : 'wrech_add_to_cart_button wrech_product_no_simple';
                    ?>
                        <div class="swiper-slide wrech-product">
                            <div class="wrech-product-wrapper">
                                <div class="wrech-product-description">
                                    <div class="wrech-product-img">
                                        <a href="<?php echo $product_id  ?>"><?php echo woocommerce_get_product_thumbnail('woocommerce_thumbnail'); ?></a>
                                    </div>
                                    <div class="wrech-product-info">
                                        <div  class="wrech-product-name"><a href="<?php echo $product_permalink; ?>"><?php echo $product->get_title() ?></a></div>
                                        <div class="wrech-product-price">
                                            <del><span><?php echo $product->get_regular_price() ? wc_price($product->get_regular_price()) : '' ?></span></del>
                                            <span><?php echo wc_price($product->get_price()); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wrech-product-action">
                                    <div class="wrech-btn-icon-action wrech-footer-add <?php echo $product_class_btn ?>"
                                         data-product_id="<?php echo $product_id ?>"
                                         data-quantity="1"
                                         data-url="<?php echo $product_permalink ?>">
                                        <img class="wrech-footer-add" src="<?php echo WRECH_PLUGIN_URL . '/assets/images/plus.png' ?>" alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <!-- If we need navigation buttons -->
                <div class="swiper-button-prev wrech-swipper-prev"></div>
                <div class="swiper-button-next wrech-swipper-next"></div>

            </div>
        </div>
        <?php

        echo ob_get_clean();
    }


}
