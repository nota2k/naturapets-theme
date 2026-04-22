<?php
/**
 * Bloc dynamique : image mise en avant du contexte de page (hors Query Loop).
 *
 * @package Naturapets
 */

if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('naturapets_get_page_featured_image_banner_markup')) {
	return;
}

$markup = naturapets_get_page_featured_image_banner_markup();
if ('' === trim($markup)) {
	if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST)) {
		echo '<div class="np-featured-image-banner__placeholder" style="width:100%;min-height:50vh;border:1px dashed #c7c7c7;background-color:#f3f3f3;background-image:repeating-linear-gradient(45deg, rgba(167,167,167,.18) 0 12px, rgba(255,255,255,.18) 12px 24px);"></div>';
	}
	return;
}

echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
