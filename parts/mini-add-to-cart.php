<?php
/**
 * Mini "Add to cart" button — circular "+" for product loops / query blocks.
 *
 * Usage: <?php get_template_part('parts/mini-add-to-cart'); ?>
 * Or via shortcode: [naturapets_mini_cart]
 */

if (! defined('ABSPATH')) {
	exit;
}

global $product;

if (! $product instanceof WC_Product) {
	$product = wc_get_product(get_the_ID());
}

if (! $product || ! $product->is_purchasable() || ! $product->is_in_stock()) {
	return;
}

$url        = $product->add_to_cart_url();
$product_id = $product->get_id();
$sku        = $product->get_sku();
$label      = sprintf(esc_attr__('Ajouter « %s » au panier', 'naturapets'), $product->get_name());
?>
<div class="np-mini-cart">
	<a href="<?php echo esc_url($url); ?>"
	   data-quantity="1"
	   data-product_id="<?php echo $product_id; ?>"
	   data-product_sku="<?php echo esc_attr($sku); ?>"
	   class="np-mini-cart__btn add_to_cart_button ajax_add_to_cart"
	   aria-label="<?php echo $label; ?>"
	   rel="nofollow">
		<svg class="np-mini-cart__icon np-mini-cart__icon--plus" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
			<line x1="10" y1="3" x2="10" y2="17" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
			<line x1="3" y1="10" x2="17" y2="10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
		</svg>
		<svg class="np-mini-cart__icon np-mini-cart__icon--check" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
			<polyline points="4,10 8,14 16,6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
	</a>
</div>
