<?php
/**
 * Template de rendu du bloc Galerie produit (design Figma node 4-129)
 * Trois zones empilées : image principale (haute), bandeau, grande image.
 * Sur une page produit WooCommerce : récupère l'image à la une puis la galerie produit.
 * Sinon : utilise les images ACF du bloc.
 *
 * @package Naturapets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$url_1 = null;
$url_2 = null;
$url_3 = null;

// En contexte page produit WooCommerce : image à la une en premier, puis galerie produit
if ( function_exists( 'is_product' ) && function_exists( 'wc_get_product' ) && is_product() ) {
	$product_id = get_the_ID();
	$product   = wc_get_product( $product_id );
	if ( $product ) {
		$image_ids   = array();
		$featured_id = $product->get_image_id();
		// 1. Image à la une en premier
		if ( $featured_id ) {
			$image_ids[] = (int) $featured_id;
		}
		// 2. Images de la galerie pour les suivantes (sans ré-afficher l'image à la une)
		$gallery_ids = $product->get_gallery_image_ids();
		if ( ! empty( $gallery_ids ) ) {
			foreach ( $gallery_ids as $gid ) {
				$gid = (int) $gid;
				if ( $gid && $gid !== $featured_id ) {
					$image_ids[] = $gid;
				}
			}
		}
		$image_ids = array_slice( $image_ids, 0, 3 );
		if ( isset( $image_ids[0] ) ) {
			$url_1 = wp_get_attachment_image_url( (int) $image_ids[0], 'large' );
		}
		if ( isset( $image_ids[1] ) ) {
			$url_2 = wp_get_attachment_image_url( (int) $image_ids[1], 'large' );
		}
		if ( isset( $image_ids[2] ) ) {
			$url_3 = wp_get_attachment_image_url( (int) $image_ids[2], 'large' );
		}
	}
}

// Fallback : images ACF du bloc (pages non-produit ou produit sans images)
if ( ! $url_1 && ! $url_2 && ! $url_3 ) {
	$image_1 = get_field( 'product_gallery_image_1' );
	$image_2 = get_field( 'product_gallery_image_2' );
	$image_3 = get_field( 'product_gallery_image_3' );
	$url_1   = naturapets_get_acf_image_url( $image_1, 'large' );
	$url_2   = naturapets_get_acf_image_url( $image_2, 'large' );
	$url_3   = naturapets_get_acf_image_url( $image_3, 'large' );
}

$product_name = ( function_exists( 'is_product' ) && is_product() && function_exists( 'wc_get_product' ) ) ? get_the_title() : '';
$alt_1 = $product_name ? sprintf( /* translators: %s: product name */ __( 'Image principale de %s', 'naturapets' ), $product_name ) : '';
$alt_2 = $product_name ? sprintf( /* translators: %s: product name */ __( 'Galerie de %s (2)', 'naturapets' ), $product_name ) : '';
$alt_3 = $product_name ? sprintf( /* translators: %s: product name */ __( 'Galerie de %s (3)', 'naturapets' ), $product_name ) : '';
?>
<section class="np-product-gallery" aria-label="<?php esc_attr_e( 'Galerie produit', 'naturapets' ); ?>">
	<div class="np-product-gallery__inner">
		<div class="np-product-gallery__item np-product-gallery__item--featured">
			<?php if ( $url_1 ) : ?>
				<img src="<?php echo esc_url( $url_1 ); ?>" alt="<?php echo esc_attr( $alt_1 ); ?>" loading="lazy" />
			<?php endif; ?>
		</div>
		<div class="np-product-gallery__item np-product-gallery__item--strip">
			<?php if ( $url_2 ) : ?>
				<img src="<?php echo esc_url( $url_2 ); ?>" alt="<?php echo esc_attr( $alt_2 ); ?>" loading="lazy" />
			<?php endif; ?>
		</div>
		<div class="np-product-gallery__item np-product-gallery__item--large">
			<?php if ( $url_3 ) : ?>
				<img src="<?php echo esc_url( $url_3 ); ?>" alt="<?php echo esc_attr( $alt_3 ); ?>" loading="lazy" />
			<?php endif; ?>
		</div>
	</div>
</section>
