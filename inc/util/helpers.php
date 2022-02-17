<?php

function wrech_woocommerce_is_active(){
	return class_exists( 'woocommerce' );
}