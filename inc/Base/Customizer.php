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

		$wp_customize->add_section( 'wrech_cart_modal', array(
			'title'       => __( 'Modal General', 'wrech_domain' ),
			'priority'    => 10,
			'panel'       => 'wrech_mod_main',
		) );

		$wp_customize->add_section( 'wrech_cart_modal_header', array(
			'title'       => __( 'Modal Header', 'wrech_domain' ),
			'priority'    => 10,
			'panel'       => 'wrech_mod_main',
		) );


		$wp_customize->add_section( 'wrech_cart_item_modal', array(
			'title'       => __( 'Cart Items', 'wrech_domain' ),
			'priority'    => 10,
			'panel'       => 'wrech_mod_main',
		) );

		$wp_customize->add_section( 'wrech_cart_float', array(
			'title'       => __( 'Float Button', 'wrech_domain' ),
			'priority'    => 10,
			'panel'       => 'wrech_mod_main',
		) );

	}

	function kirki_fields($fields ){

		/* CART MODAL HEADER */
		$fields[] = array(
			'type' => 'text',
			'settings' => 'wrech_header_heading',
			'label'    => esc_html__( 'Text Control', 'wrech_domain' ),
			'section'  => 'wrech_cart_modal_header',
			'default'  => esc_html__( 'YOUR CART', 'wrech_domain' ),
		);

		$fields[] = array(
			'type' => 'color',
			'settings' => 'wrech_header_heading_color',
			'label'    => esc_html__( 'Cart Heading', 'wrech_domain' ),
			'description' => esc_html__( 'Set the header cart heading', 'wrech_domain' ),
			'section'  => 'wrech_cart_modal_header',
			'default'     => '#000000',
		);

		$fields[] = array(
			'type' => 'color',
			'settings'    => 'wrech_header_bg',
			'label'       => __( 'Cart Header Background', 'wrech_domain' ),
			'description' => esc_html__( 'Set the cart header background', 'wrech_domain' ),
			'section'     => 'wrech_cart_modal_header',
			'default'     => '#FFFFFF',
		);

		$fields[] = array(
			'type' => 'slider',
			'settings'    => 'wrech_coupon_input_radius',
			'label'       => esc_html__( 'Coupon Text Border Radius', 'wrech_domain' ),
			'section'     => 'wrech_cart_modal_header',
			'default'     => 0,
			'choices'     => [
				'min'  => 0,
				'max'  => 20,
				'step' => 1,
			],
		);

		$fields[] = array(
			'type' => 'slider',
			'settings'    => 'wrech_coupon_button_radius',
			'label'       => esc_html__( 'Coupon Button Border Radius', 'wrech_domain' ),
			'section'     => 'wrech_cart_modal_header',
			'default'     => 0,
			'choices'     => [
				'min'  => 0,
				'max'  => 20,
				'step' => 1,
			],
		);

		$fields[] = array(
			'type' => 'radio_image',
			'settings'    => 'wrech_cart_header_close_icon',
			'label'       => esc_html__( 'Cart header close icon', 'wrech_domain' ),
			'section'     => 'wrech_cart_modal_header',
			'default'     => 'close_icon_1',
			'choices'     => [
				'close_icon_1'   => WRECH_PLUGIN_URL . '/assets/images/close_icon_1.png',
				'close_icon_2' => WRECH_PLUGIN_URL . '/assets/images/close_icon_2.png',
				'close_icon_3'  => WRECH_PLUGIN_URL . '/assets/images/close_icon_3.png',
				'close_icon_4'  => WRECH_PLUGIN_URL . '/assets/images/close_icon_4.png',
				'close_icon_5'  => WRECH_PLUGIN_URL . '/assets/images/close_icon_5.png',
				'close_icon_6'  => WRECH_PLUGIN_URL . '/assets/images/close_icon_6.png',
			]
		);

		$fields[] = array(
			'type' => 'color',
			'settings'    => 'wrech_header_close_icon_color',
			'label'       => __( 'Cart Header Color Close Icon', 'wrech_domain' ),
			'description' => esc_html__( 'Set the cart header icon close color', 'wrech_domain' ),
			'section'     => 'wrech_cart_modal_header',
			'default'     => '#000000',
		);

		/* CART FLOAT BTN  */
		$fields[] = array(
			'type' => 'color',
			'settings'    => 'wrech_float_btn_bg',
			'label'       => __( 'Cart Float Background', 'wrech_domain' ),
			'description' => esc_html__( 'Regular color control, no alpha channel.', 'wrech_domain' ),
			'section'     => 'wrech_cart_float',
			'default'     => '#FFFFFF',
		);

		$fields[] = array(
			'type' => 'color',
			'settings'    => 'wrech_float_bubble_bg',
			'label'       => __( 'Cart Float Bubble Background', 'wrech_domain' ),
			'description' => esc_html__( 'Regular color control, no alpha channel.', 'wrech_domain' ),
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

		//CART ITEMS
		$fields[] = array(
			'type' => 'radio_image',
			'settings'    => 'wrech_cart_item_close_icon',
			'label'       => esc_html__( 'Cart header close icon', 'wrech_domain' ),
			'section'     => 'wrech_cart_item_modal',
			'default'     => 'close_icon_1',
			'choices'     => [
				'close_icon_1'   => WRECH_PLUGIN_URL . '/assets/images/close_icon_1.png',
				'close_icon_2' => WRECH_PLUGIN_URL . '/assets/images/close_icon_2.png',
				'close_icon_3'  => WRECH_PLUGIN_URL . '/assets/images/close_icon_3.png',
				'close_icon_4'  => WRECH_PLUGIN_URL . '/assets/images/close_icon_4.png',
				'close_icon_5'  => WRECH_PLUGIN_URL . '/assets/images/close_icon_5.png',
				'close_icon_6'  => WRECH_PLUGIN_URL . '/assets/images/close_icon_6.png',
			]
		);

		$fields[] = array(
			'type' => 'color',
			'settings'    => 'wrech_cart_item_close_icon_color',
			'label'       => __( 'Cart Header Color Close Icon', 'wrech_domain' ),
			'description' => esc_html__( 'Set the cart header icon close color', 'wrech_domain' ),
			'section'     => 'wrech_cart_item_modal',
			'default'     => '#000000',
		);

		return $fields;
	}
}
