<?php

/*
*
* @package Yariko
*
*/

namespace Wrech\Inc\Base;

use Kirki;

class Customizer{

	public function register(){
		add_action( 'customize_register', array($this, 'kirki_sections'));

		add_filter( 'kirki/fields', array($this, 'kirki_fields'));
	}

	function kirki_sections($wp_customize ){
		/**
		 * Add panels
		 */
		$wp_customize->add_panel( 'wrech_mod_main', array(
			'priority'    => 10,
			'title'       => __( 'Easy Checkout', 'wrech_domain' ),
		) );

		/**
		 * Add sections
		 */
		$wp_customize->add_section( 'wrech_cart_float', array(
			'title'       => __( 'Cart Float Button', 'wrech_domain' ),
			'priority'    => 10,
			'panel'       => 'wrech_mod_main',
		) );

		$wp_customize->add_section( 'wrech_cart_modal', array(
			'title'       => __( 'Cart Modal', 'wrech_domain' ),
			'priority'    => 10,
			'panel'       => 'wrech_mod_main',
		) );

	}

	function kirki_fields($fields ){

		/* CART FLOAT BTN FIELD */
		$fields[] = array(
			'type' => 'color',
			'settings'    => 'wrech_float_btn_bg',
			'label'       => __( 'Cart Float Background', 'wrech_domain' ),
			'description' => esc_html__( 'Regular color control, no alpha channel.', 'kirki' ),
			'section'     => 'wrech_cart_float',
			'default'     => '#FFFFFF',
		);

		$fields[] = array(
			'type' => 'color',
			'settings'    => 'wrech_float_bubble_bg',
			'label'       => __( 'Cart Float Bubble Background', 'wrech_domain' ),
			'description' => esc_html__( 'Regular color control, no alpha channel.', 'kirki' ),
			'section'     => 'wrech_cart_float',
			'default'     => '#000000',
		);

		$fields[] = array(
			'type' => 'radio_image',
			'settings'    => 'wrech_float_btn_position',
			'label'       => esc_html__( 'Cart Float Button Position', 'wrech_domain' ),
			'section'     => 'wrech_cart_float',
			'default'     => 'bottom_left',
			'priority'    => 10,
			'choices'     => [
				'bottom_left'   => WRECH_PLUGIN_URL . '/assets/images/bottom_left.png',
				'bottom_right' => WRECH_PLUGIN_URL . '/assets/images/bottom_right.png',
				'up_right'  => WRECH_PLUGIN_URL . '/assets/images/up_right.png',
				'up_left'  => WRECH_PLUGIN_URL . '/assets/images/up_left.png'
			]
		);

		/* CART MODAL */
		$fields[] = array(
			'type' => 'radio_image',
			'settings'    => 'wrech_cart_modal_position',
			'label'       => esc_html__( 'Cart Modal Position', 'wrech_domain' ),
			'section'     => 'wrech_cart_modal',
			'default'     => 'modal_right',
			'priority'    => 10,
			'choices'     => [
				'modal_left' => WRECH_PLUGIN_URL . '/assets/images/modal_left.png',
				'modal_right'   => WRECH_PLUGIN_URL . '/assets/images/modal_right.png',
			]
		);

		return $fields;
	}
}
