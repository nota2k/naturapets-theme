<?php
/**
 * Bloc dynamique : description / extrait de la page courante.
 *
 * @package Naturapets
 */

if (!defined('ABSPATH')) {
	exit;
}

$excerpt = '';

if (function_exists('is_post_type_archive') && is_post_type_archive('product') && function_exists('wc_get_page_id')) {
	$shop_id = (int) wc_get_page_id('shop');
	if ($shop_id > 0) {
		$excerpt = (string) get_the_excerpt($shop_id);
	}
} elseif (function_exists('is_home') && is_home() && 'page' === get_option('show_on_front')) {
	$page_for_posts = (int) get_option('page_for_posts');
	if ($page_for_posts > 0) {
		$excerpt = (string) get_the_excerpt($page_for_posts);
	}
} elseif (function_exists('is_singular') && is_singular()) {
	$post_id = (int) get_queried_object_id();
	if ($post_id > 0) {
		$excerpt = (string) get_the_excerpt($post_id);
	}
} elseif (function_exists('is_archive') && is_archive()) {
	$excerpt = (string) get_the_archive_description();
}

if ('' === trim(wp_strip_all_tags($excerpt))) {
	// Fallback éditeur: tenter via le contexte bannière (page/template en cours).
	if (function_exists('naturapets_get_banner_context_post_id')) {
		$editor_context_id = (int) naturapets_get_banner_context_post_id();
		if ($editor_context_id > 0) {
			$excerpt = (string) get_the_excerpt($editor_context_id);
		}
	}
}

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
