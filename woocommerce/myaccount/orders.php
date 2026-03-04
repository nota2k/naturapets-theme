<?php
/**
 * Orders
 *
 * Shows orders on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/orders.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs, the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.5.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders ); ?>

<?php if ( $has_orders ) : ?>

	<div class="orders-history">
		<div class="orders-history__header">
			<h2 class="orders-history__title">Historique des commandes</h2>
			<p class="orders-history__subtitle">Consultez toutes vos commandes passées</p>
		</div>

		<div class="orders-history__list">
			<?php
			foreach ( $customer_orders->orders as $customer_order ) {
				$order = wc_get_order( $customer_order );
				if ( ! $order ) {
					continue;
				}
				$order_date = $order->get_date_created();
				$order_year = $order_date ? $order_date->format( 'Y' ) : date( 'Y' );
				$order_id_display = 'CMD-' . $order_year . '-' . str_pad( $order->get_order_number(), 3, '0', STR_PAD_LEFT );
				$status_slug = $order->get_status();
				$status_label = wc_get_order_status_name( $status_slug );
				$view_url = $order->get_view_order_url();

				// Construire la description des articles
				$items_summary = array();
				foreach ( $order->get_items() as $item ) {
					$qty = $item->get_quantity();
					$name = $item->get_name();
					$items_summary[] = $qty . '× ' . $name;
				}
				$items_text = implode( ', ', $items_summary );
				?>
				<article class="order-card">
					<div class="order-card__header">
						<div class="order-card__id-date">
							<h3 class="order-card__id"><?php echo esc_html( $order_id_display ); ?></h3>
							<time class="order-card__date" datetime="<?php echo esc_attr( $order_date ? $order_date->date( 'c' ) : '' ); ?>">
								<?php echo esc_html( $order_date ? date_i18n( 'j F Y', $order_date->getTimestamp() ) : '' ); ?>
							</time>
						</div>
						<span class="order-card__badge order-card__badge--<?php echo esc_attr( $status_slug ); ?>">
							<?php echo esc_html( $status_label ); ?>
						</span>
					</div>

					<p class="order-card__items"><?php echo esc_html( $items_text ); ?></p>

					<div class="order-card__footer">
						<span class="order-card__total"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
						<a href="<?php echo esc_url( $view_url ); ?>" class="order-card__btn">
							Voir les détails
						</a>
					</div>
				</article>
				<?php
			}
			?>
		</div>

		<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

		<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
			<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination orders-history__pagination">
				<?php if ( 1 !== $current_page ) : ?>
					<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button<?php echo esc_attr( $wp_button_class ); ?>" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>"><?php esc_html_e( 'Précédent', 'woocommerce' ); ?></a>
				<?php endif; ?>

				<?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?>
					<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button<?php echo esc_attr( $wp_button_class ); ?>" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>"><?php esc_html_e( 'Suivant', 'woocommerce' ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>

<?php else : ?>

	<?php wc_print_notice( esc_html__( 'Vous n\'avez pas encore passé de commande.', 'woocommerce' ) . ' <a class="woocommerce-Button wc-forward button' . esc_attr( $wp_button_class ) . '" href="' . esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ) . '">' . esc_html__( 'Découvrir les produits', 'woocommerce' ) . '</a>', 'notice' ); ?>

<?php endif; ?>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
