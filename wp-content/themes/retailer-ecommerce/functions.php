<?php
/**
 * Retailer Ecommerce functions
 */

if ( ! function_exists( 'retailer_ecommerce_styles' ) ) :
	function retailer_ecommerce_styles() {
		// Register theme stylesheet.
		wp_register_style('retailer-ecommerce-style',
			get_template_directory_uri() . '/style.css',array(),
			wp_get_theme()->get( 'Version' )
		);
		// Register theme custom code js.
		wp_enqueue_script( 'customcode', 
			get_template_directory_uri() . '/assets/js/customcode.js', array( 'jquery' ) );

		// Enqueue theme stylesheet.
		wp_enqueue_style( 'retailer-ecommerce-style' );
	}
endif;
add_action( 'wp_enqueue_scripts', 'retailer_ecommerce_styles' );

/**
 * About Theme Function
 */
require get_theme_file_path( '/about-theme/about-theme.php' );

/**
 * TGM FILE
 */
require get_theme_file_path( '/TGM/tgm.php' );