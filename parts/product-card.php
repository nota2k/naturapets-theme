<?php
/**
 * Product card used by [naturapets_products] shortcode.
 *
 * Relies on WooCommerce native template part to keep markup consistent.
 */

if (! defined('ABSPATH')) {
	exit;
}

if (function_exists('wc_get_template_part')) {
	wc_get_template_part('content', 'product');
}
