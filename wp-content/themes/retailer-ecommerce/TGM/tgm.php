<?php

require get_template_directory() . '/TGM/class-tgm-plugin-activation.php';
/**
 * Recommended plugins.
 */
function retailer_ecommerce_register_recommended_plugins() {
	$plugins = array(		
		array(
			'name'             => __( 'FOX - Currency Switcher Professional for WooCommerce', 'retailer-ecommerce' ),
			'slug'             => 'woocommerce-currency-switcher',
			'source'           => '',
			'required'         => false,
			'force_activation' => false,
		),
		array(
			'name'             => __( 'GTranslate', 'retailer-ecommerce' ),
			'slug'             => 'gtranslate',
			'source'           => '',
			'required'         => false,
			'force_activation' => false,
		)
	);
	$config = array();
	tgmpa( $plugins, $config );
}
add_action( 'tgmpa_register', 'retailer_ecommerce_register_recommended_plugins' );