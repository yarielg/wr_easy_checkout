<?php

function wrech_stripe_is_active(){
	return is_plugin_active('woocommerce-gateway-stripe/woocommerce-gateway-stripe.php');
}

function wrech_woocommerce_is_active(){
	return is_plugin_active('woocommerce/woocommerce.php');
}