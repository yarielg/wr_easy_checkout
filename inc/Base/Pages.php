<?php

/*
*
* @package Yariko
*
*/

namespace Wrech\Inc\Base;

class Pages{

	public function register(){
		add_action('admin_menu', function(){
			$page_product =	add_menu_page('Easy Checkout', 'Easy Checkout', 'manage_options', 'wrech-test', array($this,'settings') );
			add_action( 'load-' . $page_product, function(){
				add_action( 'admin_enqueue_scripts',function (){

					wp_enqueue_style('wrech-bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css'  );

					wp_enqueue_style('wrech-app-css', WRECH_PLUGIN_URL . '/dist/app.css'  );
					wp_enqueue_style('wrech-vendors-css', WRECH_PLUGIN_URL . '/dist/vendors.css'  );
					wp_enqueue_script( 'wrech-bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js');

					wp_enqueue_script( 'wrech-runtime-js', WRECH_PLUGIN_URL . '/dist/runtime.wec.bundle.js', '1.00', true);
					wp_enqueue_script( 'wrech-vendors-js', WRECH_PLUGIN_URL . '/dist/vendors.wec.bundle.js', array('wrech-runtime-js'),'1.00', true);

					wp_enqueue_script( 'wrech-app-js', WRECH_PLUGIN_URL . '/dist/app.wec.bundle.js', array('wrech-runtime-js', 'wrech-vendors-js'),'1.00', true);

					$args = array(
						'ajax_url'=> admin_url('admin-ajax.php'),
                        'settings' => wrech_settings(),
                        'plugin_url' => WRECH_PLUGIN_URL,
                        'plugin_path' => WRECH_PLUGIN_PATH,
					);
					wp_localize_script( 'wrech-app-js', 'wrech_settings_params', $args );

				});
			});
		});

	}

	function settings(){
		?>
		<style>
			#wpcontent {
				padding-left: 0 !important;
			}
			#wrech-app{
			}
		</style>
		<div id="wrech-app"></div>
		<?php
	}
}
