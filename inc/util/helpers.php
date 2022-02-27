<?php

function wrech_woocommerce_is_active(){
	return class_exists( 'woocommerce' );
}

function wrech_settings($field = null){

	$default_settings = array(
		'float_btn_position' => 'bottom_left',
		'cart_icon_url' => WRECH_PLUGIN_URL . '/assets/images/cart.png',
		'cart_icon_id' => -1,
		'excluded_pages' => '',
	);

	$wrech_settings = get_option('wrech_settings', array());

	$settings = array_replace($default_settings, $wrech_settings);

	if($field !== null){
		return $settings[$field];
	}

	return $settings;
}

function wrech_save_settings($settings){

	$wrech_settings = get_option('wrech_settings', array());

	$general_settings = array_replace($wrech_settings, $settings);

	return update_option('wrech_settings', $general_settings);
}

function wrech_save_field_settings($field,$value){

	$settings = get_option('wrech_settings', array());

	if(isset($settings[$field])){
		$settings[$field] = $value;
	}

}

function wrech_delete_field_settings($field){

	$settings = get_option('wrech_settings', array());

	if(isset($settings[$field])){
		unset($settings[$field]);
		update_option('wrech_settings', $settings);
	}

}

function wrech_upload_file( $file, $post_id = 0, $desc = null ) {
	if( empty( $file['name'] ) ) {
		return new \WP_Error( 'error', 'File is empty' );
	}

	// Get filename and store it into $file_array
	preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file['name'], $matches );

	// If error storing temporarily, return the error.
	if ( is_wp_error( $file['tmp_name'] ) ) {
		return new \WP_Error( 'error', 'Error while storing file temporarily' );
	}

	// Store and validate
	$id = media_handle_sideload( $file, $post_id, $desc );

	// Unlink if couldn't store permanently
	if ( is_wp_error( $id ) ) {
		unlink( $file['tmp_name'] );
		return new \WP_Error( 'error', "Couldn't store upload permanently" );
	}

	if ( empty( $id ) ) {
		return new \WP_Error( 'error', "Upload ID is empty" );
	}

	return $id;
}