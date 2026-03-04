<?php
/**
 * Template de rendu du bloc Icône
 * Bloc rond (max 60×60) avec image au centre et fond personnalisable.
 *
 * @package Naturapets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$image    = get_field( 'icone_image' );
$bg_color = get_field( 'icone_background' );

$image_url = function_exists( 'naturapets_get_acf_image_url' ) ? naturapets_get_acf_image_url( $image, 'thumbnail' ) : ( is_array( $image ) && isset( $image['url'] ) ? $image['url'] : ( is_numeric( $image ) ? wp_get_attachment_image_url( $image, 'thumbnail' ) : false ) );

// Valeur stockée = hex (palette) ou slug (legacy) ou hex ancien
$bg_hex = '';
if ( ! empty( $bg_color ) ) {
	if ( preg_match( '/^#[0-9a-fA-F]{3,8}$/', $bg_color ) ) {
		$bg_hex = $bg_color;
	} elseif ( class_exists( 'WP_Theme_JSON_Resolver' ) ) {
		$theme_json = WP_Theme_JSON_Resolver::get_merged_data();
		$settings   = $theme_json->get_settings();
		$raw        = $settings['color']['palette'] ?? array();
		foreach ( $raw as $item ) {
			if ( isset( $item['slug'] ) && $item['slug'] === $bg_color && ! empty( $item['color'] ) ) {
				$bg_hex = $item['color'];
				break;
			}
		}
	}
}
$bg_style = $bg_hex ? 'background-color: ' . esc_attr( $bg_hex ) . ';' : '';
?>
<div class="np-icone" style="<?php echo $bg_style; ?>" aria-hidden="true">
	<?php if ( $image_url ) : ?>
		<img class="np-icone__img" src="<?php echo esc_url( $image_url ); ?>" alt="" loading="lazy" />
	<?php endif; ?>
</div>
