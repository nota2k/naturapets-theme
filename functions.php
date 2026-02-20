<?php
/**
 * Naturapets - Thème enfant de Frost
 *
 * @package Naturapets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Version du thème pour le cache busting.
 */
define( 'NATURAPETS_VERSION', wp_get_theme()->get( 'Version' ) );

/**
 * Charge les styles du thème parent et enfant.
 */
function naturapets_enqueue_styles() {
	// Style du thème parent Frost.
	wp_enqueue_style(
		'frost-style',
		get_template_directory_uri() . '/style.css',
		array(),
		wp_get_theme( 'frost' )->get( 'Version' )
	);

	// Style principal du thème (style.css requis par WordPress).
	wp_enqueue_style(
		'naturapets-style',
		get_stylesheet_uri(),
		array( 'frost-style' ),
		NATURAPETS_VERSION
	);

	// Styles personnalisés compilés depuis SCSS.
	$css_file = get_stylesheet_directory() . '/assets/css/main.css';
	if ( file_exists( $css_file ) ) {
		wp_enqueue_style(
			'naturapets-main',
			get_stylesheet_directory_uri() . '/assets/css/main.css',
			array( 'naturapets-style' ),
			filemtime( $css_file )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'naturapets_enqueue_styles' );
