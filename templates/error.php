<?php
/**
 * Show error messages
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/notices/error.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! $notices ) {
	return;
}


if(wp_doing_ajax() && isset($_POST['easy_checkout'])){
	$errors = [];
	foreach ( $notices as $notice ) :
		$errors[] =  wc_kses_notice( $notice['notice']);
	endforeach;
	echo wp_json_encode( array('type' => 'error','errors' => $errors));
}else{
	?>
	<ul class="woocommerce-error" role="alert">
		<?php foreach ( $notices as $notice ) : ?>
			<li<?php echo wc_get_notice_data_attr( $notice ); ?>>
				<?php echo wc_kses_notice( $notice['notice'] ); ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

