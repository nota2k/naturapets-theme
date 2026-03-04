<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs, the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$user = wp_get_current_user();
$billing_address = function_exists( 'wc_get_account_formatted_address' ) ? wc_get_account_formatted_address( 'billing' ) : '';
$shipping_address = ( function_exists( 'wc_shipping_enabled' ) && wc_shipping_enabled() && ! wc_ship_to_billing_address_only() && function_exists( 'wc_get_account_formatted_address' ) ) ? wc_get_account_formatted_address( 'shipping' ) : '';
$edit_account_url = wc_get_endpoint_url( 'edit-account', '', wc_get_page_permalink( 'myaccount' ) );
?>

<div class="profile-summary">
	<div class="profile-summary__header">
		<h2 class="profile-summary__title">Profil</h2>
		<p class="profile-summary__subtitle">Vos informations personnelles</p>
		<a href="<?php echo esc_url( $edit_account_url ); ?>" class="profile-summary__edit-btn">
			<svg class="profile-summary__icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
			Modifier mon profil
		</a>
	</div>

	<div class="profile-summary__card">
		<dl class="profile-summary__list">
			<div class="profile-summary__row">
				<dt>Nom</dt>
				<dd><?php echo esc_html( trim( $user->first_name . ' ' . $user->last_name ) ?: $user->display_name ); ?></dd>
			</div>
			<div class="profile-summary__row">
				<dt>Email</dt>
				<dd><?php echo esc_html( $user->user_email ); ?></dd>
			</div>
			<?php if ( $billing_address ) : ?>
			<div class="profile-summary__row">
				<dt>Adresse de facturation</dt>
				<dd><?php echo wp_kses_post( $billing_address ); ?></dd>
			</div>
			<?php endif; ?>
			<?php if ( $shipping_address ) : ?>
			<div class="profile-summary__row">
				<dt>Adresse de livraison</dt>
				<dd><?php echo wp_kses_post( $shipping_address ); ?></dd>
			</div>
			<?php endif; ?>
		</dl>
	</div>
</div>

<?php
	/**
	 * My Account dashboard.
	 *
	 * @since 2.6.0
	 */
	do_action( 'woocommerce_account_dashboard' );

	/**
	 * Deprecated woocommerce_before_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_before_my_account' );

	/**
	 * Deprecated woocommerce_after_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_after_my_account' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
