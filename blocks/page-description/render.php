<?php
/**
 * Bloc dynamique : description / extrait de la page courante.
 *
 * @package Naturapets
 */

if (!defined('ABSPATH')) {
	exit;
}

$post_id = 0;

if (function_exists('is_post_type_archive') && is_post_type_archive('product') && function_exists('wc_get_page_id')) {
	$post_id = (int) wc_get_page_id('shop');
}

if ($post_id < 1) {
	$post_id = (int) get_queried_object_id();
}

if ($post_id < 1) {
	$post_id = (int) get_the_ID();
}

if ($post_id < 1) {
	return;
}

$excerpt = get_the_excerpt($post_id);
if ('' === trim(wp_strip_all_tags($excerpt))) {
	return;
}

// get_the_excerpt applique the_excerpt ; éviter un double wpautop si du HTML est déjà présent.
if (false !== stripos($excerpt, '<p')) {
	$inner = wp_kses_post($excerpt);
} else {
	$inner = wp_kses_post(wpautop($excerpt));
}
$wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'np-page-description-block',
	)
);
printf(
	'<div %s><div class="np-page-description">%s</div></div>',
	$wrapper,
	$inner
);
