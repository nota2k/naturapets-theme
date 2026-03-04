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

$bg_style = ! empty( $bg_color ) ? 'background-color: ' . esc_attr( $bg_color ) . ';' : '';
?>
<div class="np-icone" style="<?php echo $bg_style; ?>" aria-hidden="true">
	<?php if ( $image_url ) : ?>
		<img class="np-icone__img" src="<?php echo esc_url( $image_url ); ?>" alt="" loading="lazy" />
	<?php endif; ?>
</div>
