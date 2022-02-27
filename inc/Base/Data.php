<?php

/*
*
* @package Yariko
*
*/

namespace Wrech\Inc\Base;

class Data{

	public function __construct() {
		global $wrech_settings;
		if ( ! $wrech_settings ) {
			$wrech_settings = get_option( 'wrech_params', array() );
		}
	}
}