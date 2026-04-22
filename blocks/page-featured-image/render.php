<?php
/**
 * Bloc dynamique : image mise en avant de la page de contexte.
 *
 * @package Naturapets
 */

if (!defined('ABSPATH')) {
	exit;
}

$post_id = function_exists('naturapets_get_page_hero_context_id')
	? (int) naturapets_get_page_hero_context_id()
	: 0;

if ($post_id < 1) {
	return;
}

$thumb_id = (int) get_post_thumbnail_id($post_id);
if ($thumb_id < 1) {
	return;
}

$dim_ratio = isset($attributes['dimRatio']) ? (int) $attributes['dimRatio'] : 60;
$dim_ratio = max(0, min(100, $dim_ratio));
$overlay_opacity = (float) $dim_ratio / 100;

$wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'np-page-featured-image',
	)
);

$image_html = wp_get_attachment_image(
	$thumb_id,
	'full',
	false,
	array(
		'class' => 'np-page-featured-image__img',
		'loading' => 'eager',
		'fetchpriority' => 'high',
		'alt' => '',
	)
);

if (!is_string($image_html) || '' === trim($image_html)) {
	return;
}
?>
<figure <?php echo $wrapper; ?>>
	<?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<span class="np-page-featured-image__overlay" style="opacity:<?php echo esc_attr((string) $overlay_opacity); ?>;"></span>
</figure>
