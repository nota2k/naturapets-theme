<?php
/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();
$allowed_html = array( 'a' => array( 'href' => array() ) );
?>
<div class="header-dashboard">
	<img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/naturapet-account.jpg' ); ?>" alt="NaturaPets">
	<h2><?php echo esc_html( $current_user->display_name ); ?></h2>
</div>

<p class="profile-summary__welcome">
	<?php
	printf(
		/* translators: 1: user display name 2: logout url */
		wp_kses( __( 'Bonjour %1$s (pas %1$s ? <a href="%2$s">Se déconnecter</a>)', 'woocommerce' ), $allowed_html ),
		'<strong>' . esc_html( $current_user->display_name ) . '</strong>',
		esc_url( wc_logout_url() )
	);
	?>
</p>

<div class="myaccount-header">
	<div class="myaccount-header__top">
		<h1 class="myaccount-header__title">Mon compte</h1>
		<a href="<?php echo esc_url( wc_logout_url() ); ?>" class="myaccount-header__logout wp-block-button__link">
			<svg class="myaccount-header__logout-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M15.2199 20.3999L18.9258 20.3999C19.4874 20.3999 20.0261 20.1787 20.4232 19.7848C20.8203 19.391 21.0435 18.8569 21.0435 18.2999L21.0435 5.6999C21.0435 5.14295 20.8203 4.60881 20.4232 4.21498C20.0261 3.82115 19.4874 3.5999 18.9258 3.5999L15.2199 3.5999M14.9567 11.9999L2.95674 11.9999M2.95674 11.9999L7.54189 16.7999M2.95674 11.9999L7.54189 7.1999" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
			Déconnexion
		</a>
	</div>
	<p class="myaccount-header__subtitle">Gérez vos informations et vos médaillons</p>
</div>
<?php
/**
 * My Account navigation.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_account_navigation' ); ?>

<div class="woocommerce-MyAccount-content">
	<?php
		/**
		 * My Account content.
		 *
		 * @since 2.6.0
		 */
		do_action( 'woocommerce_account_content' );
	?>
</div>
