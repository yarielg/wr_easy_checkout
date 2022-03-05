<?php
/*
*
* @package yariko


Plugin Name:  Easy Checkout
Plugin URI:   https://webreadynow.com
Description:  This plugin implement an easy checkout flow.
Version:      1.0.0
Author:       Yariel Gordillo from webready
Author URI:   https://webreadynow.com
Tested up to: 5.3.2
Text Domain:  wrech_domain
Domain Path:  /languages
*/

defined('ABSPATH') or die('You do not have access, sally human!!!');

define ( 'WRECH_PLUGIN_VERSION', '1.0.0');

if( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php') ){
    require_once  dirname( __FILE__ ) . '/vendor/autoload.php';
}
//Change WRPL for plugin's initials
define('WRECH_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
//define('WRECH_PLUGIN_PATH_CLASS', plugin_dir_path( __FILE__ ) ) . '/inc/class/';
define('WRECH_PLUGIN_URL' , plugin_dir_url(  __FILE__  ) );
define('WRECH_ADMIN_URL' , get_admin_url() );
define('WRECH_PLUGIN_DIR_BASENAME' , dirname(plugin_basename(__FILE__)) );

//include the helpers
include 'inc/util/helpers.php';

//include kirki framework
include_once( 'inc/kirki/kirki.php' );

if ( wrech_woocommerce_is_active() ){
	if( class_exists( 'Wrech\\Inc\\Init' ) ){

		register_activation_hook( __FILE__ , array('Wrech\\Inc\\Base\\Activate','activate') );
		Wrech\Inc\Init::register_services();

	}
}else{

	add_action('admin_notices', function(){
		?>
		<div class="notice notice-error is-dismissible">
			<p>WR Price List Manager required WooCommerce, please activate it to use <b>WR Price List Manager</b> Plugin</p>
		</div>
		<?php
	});
}




