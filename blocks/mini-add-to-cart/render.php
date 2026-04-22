<?php
/**
 * Block: naturapets/mini-add-to-cart
 * Bouton "+" circulaire — ajout AJAX au panier pour les boucles produit.
 */

if (! defined('ABSPATH') || ! function_exists('wc_get_product')) {
	return;
}

$post_id = $block->context['postId'] ?? get_the_ID();
$product = wc_get_product($post_id);

if (! $product || ! $product->is_purchasable() || ! $product->is_in_stock()) {
	return;
}

$wrapper = get_block_wrapper_attributes(['class' => 'np-mini-cart']);
?>
<div <?php echo $wrapper; ?>>
	<a href="<?php echo esc_url($product->add_to_cart_url()); ?>"
	   data-quantity="1"
	   data-product_id="<?php echo $product->get_id(); ?>"
	   data-product_sku="<?php echo esc_attr($product->get_sku()); ?>"
	   class="np-mini-cart__btn add_to_cart_button ajax_add_to_cart"
	   aria-label="<?php echo esc_attr(sprintf(__('Ajouter « %s » au panier', 'naturapets'), $product->get_name())); ?>"
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
