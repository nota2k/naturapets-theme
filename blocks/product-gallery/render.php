<?php
/**
 * Bloc dynamique : galerie produit (design Figma 4-129).
 * Image à la une + images de galerie WooCommerce (fiche produit ou contexte postId).
 *
 * @package Naturapets
 */

if (!defined('ABSPATH')) {
	exit;
}

$image_ids = array();

$product_id = 0;
if (function_exists('naturapets_product_gallery_get_product_id_for_block')) {
	$product_id = naturapets_product_gallery_get_product_id_for_block($block ?? null);
}

if ($product_id > 0 && function_exists('wc_get_product')) {
	$product = wc_get_product($product_id);
	if ($product) {
		$featured_id = (int) $product->get_image_id();
		if ($featured_id) {
			$image_ids[] = $featured_id;
		}
		foreach ($product->get_gallery_image_ids() as $gid) {
			$gid = (int) $gid;
			if ($gid > 0 && $gid !== $featured_id) {
				$image_ids[] = $gid;
			}
		}
		$image_ids = array_slice(array_values(array_unique($image_ids)), 0, 3);
	}
}

$product_name = ($product_id > 0) ? get_the_title($product_id) : '';

$size_main  = (function_exists('wc_get_product') && class_exists('WooCommerce')) ? 'woocommerce_single' : 'large';
$size_strip = 'large';
$size_large = 'large';

$slots = array(
	'featured' => isset($image_ids[0]) ? (int) $image_ids[0] : 0,
	'strip'    => isset($image_ids[1]) ? (int) $image_ids[1] : 0,
	'large'    => isset($image_ids[2]) ? (int) $image_ids[2] : 0,
);

$has_any = $slots['featured'] || $slots['strip'] || $slots['large'];

$wrapper_classes = 'np-product-gallery';
if (!$has_any) {
	$wrapper_classes .= ' np-product-gallery--empty';
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => $wrapper_classes,
		'aria-label' => __('Galerie produit', 'naturapets'),
	)
);

/**
 * Affiche une image de la galerie ou rien.
 *
 * @param int    $attachment_id ID pièce jointe.
 * @param string $size          Taille d’image WordPress.
 * @param string $slot_modifier Modificateur : featured, strip, large.
 * @param string $alt           Texte alternatif.
 * @param string $loading       lazy|eager.
 */
$render_slot = static function ($attachment_id, $size, $slot_modifier, $alt, $loading) {
	if ($attachment_id < 1) {
		return;
	}
	$img = wp_get_attachment_image(
		$attachment_id,
		$size,
		false,
		array(
			'class' => 'np-product-gallery__img',
			'alt' => $alt,
			'loading' => $loading,
			'decoding' => 'async',
		)
	);
	if ('' === $img) {
		return;
	}
	$item_class = 'np-product-gallery__item np-product-gallery__item--' . preg_replace('/[^a-z0-9_-]/i', '', $slot_modifier);
	echo '<div class="' . esc_attr($item_class) . '">';
	echo $img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image()
	echo '</div>';
};
?>
<section <?php echo $wrapper_attributes; ?>>
	<div class="np-product-gallery__inner">
		<?php
		if (!$has_any) :
			?>
			<p class="np-product-gallery__placeholder">
				<?php
				if (!function_exists('wc_get_product')) {
					esc_html_e('WooCommerce doit être actif pour afficher la galerie produit.', 'naturapets');
				} elseif ($product_id < 1) {
					esc_html_e('Ajoutez ce bloc sur un modèle ou une page produit, ou prévisualisez une fiche produit pour voir les images.', 'naturapets');
				} else {
					esc_html_e('Ce produit n’a pas encore d’image à la une ni de galerie.', 'naturapets');
				}
				?>
			</p>
			<?php
		else :
			$alt_base = $product_name ? sprintf(
				/* translators: %s: product title */
				__('Galerie — %s', 'naturapets'),
				$product_name
			) : __('Galerie produit', 'naturapets');

			$render_slot(
				$slots['featured'],
				$size_main,
				'featured',
				$alt_base . ' — ' . __('Image principale', 'naturapets'),
				'eager'
			);
			$render_slot(
				$slots['strip'],
				$size_strip,
				'strip',
				$alt_base . ' — ' . __('Bandeau', 'naturapets'),
				'lazy'
			);
			$render_slot(
				$slots['large'],
				$size_large,
				'large',
				$alt_base . ' — ' . __('Vue large', 'naturapets'),
				'lazy'
			);
		endif;
		?>
	</div>
</section>
