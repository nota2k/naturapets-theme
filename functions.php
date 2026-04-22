<?php

/**
 * Naturapets - Thème enfant de Frost
 *
 * @package Naturapets
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Version du thème pour le cache busting.
 */
define('NATURAPETS_VERSION', wp_get_theme()->get('Version'));

/**
 * Extrait sur les pages : description utilisée par le modèle « Boutique » et le bloc « Description de la page ».
 */
function naturapets_add_page_excerpt_support()
{
	add_post_type_support('page', 'excerpt');
}
add_action('init', 'naturapets_add_page_excerpt_support');

/**
 * Canvas de l’éditeur (iframe) : même feuille que le front pour que le SCSS compilé s’applique aux blocs.
 * `enqueue_block_editor_assets` ne suffit pas : il charge le cadre admin, pas le contenu édité.
 */
function naturapets_setup_editor_styles()
{
	add_theme_support('editor-styles');
	$main_rel = 'assets/css/main.css';
	$main_abs = get_stylesheet_directory() . '/' . $main_rel;
	if (is_readable($main_abs)) {
		add_editor_style($main_rel);
	}
}
add_action('after_setup_theme', 'naturapets_setup_editor_styles', 20);

/**
 * AJAX : rendu HTML d’un shortcode pour l’aperçu dans l’éditeur (bloc Shortcode).
 */
function naturapets_ajax_shortcode_preview()
{
	if (!check_ajax_referer('naturapets_sc_preview', 'nonce', false) || !current_user_can('edit_posts')) {
		status_header(403);
		nocache_headers();
		header('Content-Type: text/plain; charset=' . get_option('blog_charset'));
		echo 'forbidden';
		wp_die('', '', array('response' => 403));
	}
	$raw = isset($_POST['shortcode']) ? wp_unslash($_POST['shortcode']) : '';
	if (!is_string($raw)) {
		status_header(400);
		wp_die('', '', array('response' => 400));
	}
	$raw = trim($raw);
	if ('' === $raw || strlen($raw) > 10000) {
		status_header(400);
		wp_die('', '', array('response' => 400));
	}

	// Charger les assets front pour que l'aperçu reflète les styles du shortcode.
	do_action('wp_enqueue_scripts');
	$html = do_shortcode($raw);
	ob_start();
	wp_print_styles();
	$styles = (string) ob_get_clean();
	nocache_headers();
	header('Content-Type: text/html; charset=' . get_option('blog_charset'));
	echo $styles . $html;
	wp_die('', '', array('response' => 200));
}
add_action('wp_ajax_naturapets_shortcode_preview', 'naturapets_ajax_shortcode_preview');

/**
 * Éditeur : script d’aperçu pour le bloc core/shortcode.
 */
function naturapets_enqueue_shortcode_editor_preview()
{
	$path = get_stylesheet_directory() . '/assets/js/shortcode-editor-preview.js';
	if (!file_exists($path)) {
		return;
	}
	wp_enqueue_script(
		'naturapets-shortcode-editor-preview',
		get_stylesheet_directory_uri() . '/assets/js/shortcode-editor-preview.js',
		array(
			'wp-hooks',
			'wp-compose',
			'wp-element',
			'wp-components',
			'wp-block-editor',
			'wp-i18n',
		),
		(string) filemtime($path),
		true
	);
	wp_localize_script(
		'naturapets-shortcode-editor-preview',
		'naturapetsShortcodePreview',
		array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('naturapets_sc_preview'),
		)
	);
}
add_action('enqueue_block_editor_assets', 'naturapets_enqueue_shortcode_editor_preview', 25);

/**
 * Éditeur : panneau « Description (boutique) » dans la barre latérale (Document) si le modèle Boutique est choisi.
 */
function naturapets_enqueue_shop_template_editor_script()
{
	$screen = function_exists('get_current_screen') ? get_current_screen() : null;
	if (!$screen || 'post' !== $screen->base || empty($screen->post_type) || 'page' !== $screen->post_type) {
		return;
	}
	if (method_exists($screen, 'is_block_editor') && !$screen->is_block_editor()) {
		return;
	}
	$path = get_stylesheet_directory() . '/assets/js/editor-shop-description.js';
	if (!file_exists($path)) {
		return;
	}
	wp_enqueue_script(
		'naturapets-editor-shop-description',
		get_stylesheet_directory_uri() . '/assets/js/editor-shop-description.js',
		array(
			'wp-plugins',
			'wp-edit-post',
			'wp-element',
			'wp-components',
			'wp-data',
			'wp-i18n',
		),
		(string) filemtime($path),
		true
	);
}
add_action('enqueue_block_editor_assets', 'naturapets_enqueue_shop_template_editor_script');

/**
 * Charger les classes du thème.
 */
require_once get_stylesheet_directory() . '/includes/class-qrcode.php';

/**
 * Autoriser l'upload des SVG dans le backoffice.
 */
function naturapets_allow_svg_upload($mimes)
{
	$mimes['svg'] = 'image/svg+xml';
	$mimes['svgz'] = 'image/svg+xml';
	return $mimes;
}
add_filter('upload_mimes', 'naturapets_allow_svg_upload');

/**
 * Activer l'inscription sur la page Mon compte (affichage du formulaire « Créer un compte »).
 */
function naturapets_enable_myaccount_registration($value)
{
	return 'yes';
}
add_filter('option_woocommerce_enable_myaccount_registration', 'naturapets_enable_myaccount_registration');

/**
 * Permettre à l'utilisateur de créer son mot de passe à l'inscription (au lieu d'en recevoir un par email).
 */
function naturapets_registration_generate_password($value)
{
	return 'no';
}
add_filter('option_woocommerce_registration_generate_password', 'naturapets_registration_generate_password');

/**
 * Valider la confirmation du mot de passe à l'inscription.
 */
function naturapets_validate_registration_password_confirmation($errors, $username, $password, $email)
{
	if (empty($_POST['password_confirm']) || (isset($_POST['password']) && $_POST['password'] !== $_POST['password_confirm'])) {
		$errors->add('password_mismatch', __('Les mots de passe ne correspondent pas.', 'naturapets'));
	}
	return $errors;
}
add_filter('woocommerce_process_registration_errors', 'naturapets_validate_registration_password_confirmation', 10, 4);

/**
 * Vérifier si l'adresse de livraison est identique à l'adresse de facturation.
 */
function naturapets_shipping_same_as_billing()
{
	$user_id = get_current_user_id();
	if (!$user_id || !function_exists('WC')) {
		return false;
	}
	$customer = new WC_Customer($user_id);
	$billing_keys = array('first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country');
	foreach ($billing_keys as $key) {
		$billing_val = $customer->{"get_billing_{$key}"}();
		$shipping_val = $customer->{"get_shipping_{$key}"}();
		if ($billing_val !== $shipping_val) {
			return false;
		}
	}
	return true;
}

/**
 * Copier l'adresse de facturation vers l'adresse de livraison quand la checkbox est cochée.
 */
function naturapets_copy_billing_to_shipping_on_save()
{
	if (empty($_POST['ship_to_billing_address']) || $_POST['ship_to_billing_address'] !== '1') {
		return;
	}
	if (empty($_POST['action']) || $_POST['action'] !== 'edit_address') {
		return;
	}
	global $wp;
	$address_type = isset($wp->query_vars['edit-address']) ? wc_edit_address_i18n(sanitize_title($wp->query_vars['edit-address']), true) : '';
	if ($address_type !== 'shipping') {
		return;
	}
	$user_id = get_current_user_id();
	if ($user_id <= 0 || !function_exists('WC')) {
		return;
	}
	$customer = new WC_Customer($user_id);
	$billing_keys = array('first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country');
	foreach ($billing_keys as $key) {
		$value = $customer->{"get_billing_{$key}"}();
		$_POST['shipping_' . $key] = $value;
	}
}
add_action('template_redirect', 'naturapets_copy_billing_to_shipping_on_save', 5);

/**
 * Traiter la checkbox "adresse livraison = facturation" sur la page Adresses.
 */
function naturapets_handle_ship_to_billing_from_addresses_page()
{
	if (empty($_POST['naturapets_apply_ship_to_billing']) || empty($_POST['naturapets_ship_to_billing_nonce'])) {
		return;
	}
	if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['naturapets_ship_to_billing_nonce'])), 'naturapets_ship_to_billing')) {
		return;
	}
	$user_id = get_current_user_id();
	if ($user_id <= 0 || !function_exists('WC')) {
		return;
	}
	if (empty($_POST['ship_to_billing_address']) || $_POST['ship_to_billing_address'] !== '1') {
		wp_safe_redirect(wc_get_endpoint_url('edit-address', '', wc_get_page_permalink('myaccount')));
		exit;
	}
	$customer = new WC_Customer($user_id);
	$billing_keys = array('first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country');
	foreach ($billing_keys as $key) {
		$value = $customer->{"get_billing_{$key}"}();
		$customer->{"set_shipping_{$key}"}($value);
	}
	$customer->save();
	wc_add_notice(__("L'adresse de livraison a été mise à jour avec l'adresse de facturation.", 'naturapets'), 'success');
	wp_safe_redirect(wc_get_endpoint_url('edit-address', '', wc_get_page_permalink('myaccount')));
	exit;
}
add_action('template_redirect', 'naturapets_handle_ship_to_billing_from_addresses_page', 5);

/**
 * Corriger la détection du type MIME des SVG.
 */
function naturapets_fix_svg_mime_type($data, $file, $filename, $mimes)
{
	if ($data['type'] === 'image/svg+xml' || $data['type'] === '') {
		$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		if ($ext === 'svg' || $ext === 'svgz') {
			$data['type'] = 'image/svg+xml';
			$data['ext'] = $ext;
			$data['proper_filename'] = $filename;
		}
	}
	return $data;
}
add_filter('wp_check_filetype_and_ext', 'naturapets_fix_svg_mime_type', 10, 4);

/**
 * Helper pour récupérer l'URL d'une image ACF (gère les différents formats de retour).
 */
function naturapets_get_acf_image_url($image, $size = 'thumbnail')
{
	if (empty($image)) {
		return false;
	}

	// Si c'est un tableau (format "Image Array")
	if (is_array($image)) {
		if (isset($image['sizes'][$size])) {
			return $image['sizes'][$size];
		} elseif (isset($image['url'])) {
			return $image['url'];
		}
	}

	// Si c'est un ID (format "Image ID")
	if (is_numeric($image)) {
		$url = wp_get_attachment_image_url($image, $size);
		return $url ? $url : false;
	}

	// Si c'est une URL (format "Image URL")
	if (is_string($image) && filter_var($image, FILTER_VALIDATE_URL)) {
		return $image;
	}

	return false;
}

/**
 * Charge les styles du thème parent et enfant.
 */
function naturapets_enqueue_styles()
{
	// Style du thème parent Frost.
	wp_enqueue_style(
		'frost-style',
		get_template_directory_uri() . '/style.css',
		array(),
		wp_get_theme('frost')->get('Version')
	);

	// Style principal du thème (style.css requis par WordPress).
	wp_enqueue_style(
		'naturapets-style',
		get_stylesheet_uri(),
		array('frost-style'),
		NATURAPETS_VERSION
	);

	// Police Nunito Sans (bloc Témoignage – Figma 3-56).
	wp_enqueue_style(
		'naturapets-font-nunito',
		'https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,wght@0,400;0,600;1,600&display=swap',
		array(),
		null
	);

	// Styles personnalisés compilés depuis SCSS.
	$css_file = get_stylesheet_directory() . '/assets/css/main.css';
	if (file_exists($css_file)) {
		wp_enqueue_style(
			'naturapets-main',
			get_stylesheet_directory_uri() . '/assets/css/main.css',
			array('naturapets-style', 'naturapets-font-nunito'),
			filemtime($css_file)
		);
	}
}
add_action('wp_enqueue_scripts', 'naturapets_enqueue_styles');

/**
 * Script d'animation du header au scroll.
 */
function naturapets_enqueue_header_scroll()
{
	if (is_admin()) {
		return;
	}

	$script_file = get_stylesheet_directory() . '/assets/js/header-scroll.js';
	if (file_exists($script_file)) {
		wp_enqueue_script(
			'naturapets-header-scroll',
			get_stylesheet_directory_uri() . '/assets/js/header-scroll.js',
			array(),
			filemtime($script_file),
			true
		);
	}

	$faq_js_file = get_stylesheet_directory() . '/assets/js/faq-accordeon.js';
	if (file_exists($faq_js_file)) {
		wp_enqueue_script(
			'naturapets-faq-accordeon',
			get_stylesheet_directory_uri() . '/assets/js/faq-accordeon.js',
			array(),
			filemtime($faq_js_file),
			true
		);
	}

	$points_js_file = get_stylesheet_directory() . '/assets/js/points-numerotes.js';
	if (file_exists($points_js_file)) {
		wp_enqueue_script(
			'naturapets-points-numerotes',
			get_stylesheet_directory_uri() . '/assets/js/points-numerotes.js',
			array(),
			filemtime($points_js_file),
			true
		);
	}

	$pres_js_file = get_stylesheet_directory() . '/assets/js/presentation-animee.js';
	if (file_exists($pres_js_file)) {
		wp_enqueue_script(
			'naturapets-presentation-animee',
			get_stylesheet_directory_uri() . '/assets/js/presentation-animee.js',
			array(),
			filemtime($pres_js_file),
			true
		);
	}

	$carousel_js_file = get_stylesheet_directory() . '/assets/js/carousel-texte.js';
	if (file_exists($carousel_js_file)) {
		wp_enqueue_script(
			'naturapets-carousel-texte',
			get_stylesheet_directory_uri() . '/assets/js/carousel-texte.js',
			array(),
			filemtime($carousel_js_file),
			true
		);
	}

	$mobile_menu_js_file = get_stylesheet_directory() . '/assets/js/mobile-menu.js';
	if (file_exists($mobile_menu_js_file)) {
		wp_enqueue_script(
			'naturapets-mobile-menu',
			get_stylesheet_directory_uri() . '/assets/js/mobile-menu.js',
			array(),
			filemtime($mobile_menu_js_file),
			true
		);
	}
}
add_action('wp_enqueue_scripts', 'naturapets_enqueue_header_scroll');


/**
 * ==========================================================================
 * BANDEAU DU HAUT – Options du thème (Customizer)
 * ==========================================================================
 */

/**
 * Enregistrer les options du bandeau dans le Customizer.
 */
function naturapets_customize_register_banner($wp_customize)
{
	$wp_customize->add_section(
		'np_top_banner',
		array(
			'title' => __('Bandeau du haut', 'naturapets'),
			'priority' => 30,
		)
	);

	$wp_customize->add_setting(
		'np_top_banner_enabled',
		array(
			'default' => false,
			'sanitize_callback' => 'wp_validate_boolean',
		)
	);

	$wp_customize->add_control(
		'np_top_banner_enabled',
		array(
			'label' => __('Afficher le bandeau', 'naturapets'),
			'section' => 'np_top_banner',
			'type' => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'np_top_banner_text',
		array(
			'default' => '',
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);

	$wp_customize->add_control(
		'np_top_banner_text',
		array(
			'label' => __('Texte du bandeau', 'naturapets'),
			'description' => __('Ce texte s\'affiche tout en haut de la page lorsque le bandeau est activé.', 'naturapets'),
			'section' => 'np_top_banner',
			'type' => 'textarea',
			'input_attrs' => array(
				'placeholder' => __('Ex. : Livraison offerte à partir de 50 € d\'achat', 'naturapets'),
			),
		)
	);
}
add_action('customize_register', 'naturapets_customize_register_banner');

/**
 * Retourne le HTML du bandeau du haut (utilisé par le shortcode et le filtre render_block).
 */
function naturapets_get_top_banner_html()
{
	if (!get_theme_mod('np_top_banner_enabled', false)) {
		return '';
	}

	$text = get_theme_mod('np_top_banner_text', '');
	if (empty(trim($text))) {
		return '';
	}

	return '<div class="np-top-banner" role="complementary"><p class="np-top-banner__text">'
		. wp_kses(nl2br(esc_html($text)), array('br' => array()))
		. '</p></div>';
}

/**
 * Shortcode [np_top_banner] — conservé pour les anciennes compositions (Customizer).
 */
function naturapets_top_banner_shortcode()
{
	return naturapets_get_top_banner_html();
}
add_shortcode('np_top_banner', 'naturapets_top_banner_shortcode');


/**
 * Script pour la modal QR Code sur la page Mes médaillons.
 */
function naturapets_myaccount_qr_modal_script()
{
	if (!function_exists('is_account_page') || !is_account_page()) {
		return;
	}
?>
	<script>
		(function() {
			document.addEventListener('DOMContentLoaded', function() {
				// Modal QR Code (page Mes médaillons)
				var modal = document.getElementById('animal-qr-modal');
				if (modal) {
					var triggers = document.querySelectorAll('.animal-card__qr-trigger');
					var img = modal.querySelector('.animal-qr-modal__img');
					var title = modal.querySelector('.animal-qr-modal__title');
					var backdrop = modal.querySelector('.animal-qr-modal__backdrop');
					var closeBtn = modal.querySelector('.animal-qr-modal__close');

					function openModal(url, name) {
						if (img) img.src = url;
						if (title) title.textContent = 'QR Code - ' + (name || '');
						modal.classList.add('is-open');
						modal.setAttribute('aria-hidden', 'false');
					}

					function closeModal() {
						modal.classList.remove('is-open');
						modal.setAttribute('aria-hidden', 'true');
					}
					triggers.forEach(function(btn) {
						btn.addEventListener('click', function() {
							openModal(btn.getAttribute('data-qr-url'), btn.getAttribute('data-animal-name'));
						});
					});
					if (backdrop) backdrop.addEventListener('click', closeModal);
					if (closeBtn) closeBtn.addEventListener('click', closeModal);
					document.addEventListener('keydown', function(e) {
						if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
					});
				}

				// Modal suppression de compte (page Profil)
				var deleteTrigger = document.getElementById('np-delete-account-trigger');
				var deleteModal = document.getElementById('np-delete-account-modal');
				if (deleteTrigger && deleteModal) {
					var deleteBackdrop = deleteModal.querySelector('.np-delete-account-modal__backdrop');
					var deleteClose = deleteModal.querySelector('.np-delete-account-modal__close');
					var deleteCancel = deleteModal.querySelector('.np-delete-account-modal__cancel');

					function openDeleteModal() {
						deleteModal.classList.add('is-open');
						deleteModal.setAttribute('aria-hidden', 'false');
					}

					function closeDeleteModal() {
						deleteModal.classList.remove('is-open');
						deleteModal.setAttribute('aria-hidden', 'true');
					}
					deleteTrigger.addEventListener('click', openDeleteModal);
					if (deleteBackdrop) deleteBackdrop.addEventListener('click', closeDeleteModal);
					if (deleteClose) deleteClose.addEventListener('click', closeDeleteModal);
					if (deleteCancel) deleteCancel.addEventListener('click', closeDeleteModal);
					document.addEventListener('keydown', function(e) {
						if (e.key === 'Escape' && deleteModal.classList.contains('is-open')) closeDeleteModal();
					});
				}

				// Checkbox "adresse livraison = adresse facturation"
				var shipToBilling = document.getElementById('ship_to_billing_address');
				var shippingFields = document.getElementById('shipping-address-fields');
				if (shipToBilling && shippingFields) {
					function toggleShippingFields() {
						shippingFields.style.display = shipToBilling.checked ? 'none' : '';
					}
					toggleShippingFields();
					shipToBilling.addEventListener('change', toggleShippingFields);
				}
			});
		})();
	</script>
<?php
}
add_action('wp_footer', 'naturapets_myaccount_qr_modal_script');

/**
 * Charge les styles du thème dans l'éditeur de blocs pour une preview identique au front.
 */
function naturapets_enqueue_editor_styles()
{
	wp_enqueue_style(
		'frost-style',
		get_template_directory_uri() . '/style.css',
		array(),
		wp_get_theme('frost')->get('Version')
	);
	wp_enqueue_style(
		'naturapets-style',
		get_stylesheet_uri(),
		array('frost-style'),
		NATURAPETS_VERSION
	);
	wp_enqueue_style(
		'naturapets-font-nunito',
		'https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,wght@0,400;0,600;1,600&display=swap',
		array(),
		null
	);
	// main.css est chargé dans l’iframe via add_editor_style() (voir naturapets_setup_editor_styles).
}
add_action('enqueue_block_editor_assets', 'naturapets_enqueue_editor_styles');

/**
 * Styles pour les pastilles de couleur du bloc Icône (palette du thème).
 */
function naturapets_icone_block_editor_styles()
{
	$palette = naturapets_get_theme_color_palette();
	if (empty($palette)) {
		return;
	}
	$rules = array();
	foreach (array_keys($palette) as $hex) {
		$esc_hex = esc_attr($hex);
		$rules[] = sprintf(
			'.acf-field[data-name="icone_background"] input[value="%1$s"], .acf-field-field_icone_background input[value="%1$s"] { background-color: %1$s !important; }',
			$esc_hex
		);
	}
	$css = '.acf-field[data-name="icone_background"] .acf-radio-list, .acf-field-field_icone_background .acf-radio-list { display: flex; flex-wrap: wrap; gap: 8px; } ';
	$css .= '.acf-field[data-name="icone_background"] .acf-radio-list label, .acf-field-field_icone_background .acf-radio-list label { display: flex; align-items: center; gap: 6px; } ';
	$css .= '.acf-field[data-name="icone_background"] .acf-radio-list input[type="radio"], .acf-field-field_icone_background .acf-radio-list input[type="radio"] { width: 28px; height: 28px; padding: 0; border: 2px solid rgba(0,0,0,0.2); border-radius: 4px; cursor: pointer; appearance: none; -webkit-appearance: none; } ';
	$css .= '.acf-field[data-name="icone_background"] .acf-radio-list input[type="radio"]:checked, .acf-field-field_icone_background .acf-radio-list input[type="radio"]:checked { border-color: #1e1e1e; box-shadow: 0 0 0 2px #fff, 0 0 0 4px #1e1e1e; } ';
	$css .= implode(' ', $rules);
	wp_add_inline_style('naturapets-style', $css);
}
add_action('enqueue_block_editor_assets', 'naturapets_icone_block_editor_styles', 20);

/**
 * Traiter la demande de suppression de compte utilisateur.
 */
function naturapets_handle_delete_account()
{
	if (empty($_POST['naturapets_delete_account']) || empty($_POST['naturapets_delete_account_nonce'])) {
		return;
	}
	if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['naturapets_delete_account_nonce'])), 'naturapets_delete_account')) {
		wp_die(esc_html__('Erreur de sécurité. Veuillez réessayer.', 'naturapets'), '', array('response' => 403));
	}
	$user_id = get_current_user_id();
	if (!$user_id) {
		wp_safe_redirect(wc_get_page_permalink('myaccount'));
		exit;
	}
	require_once ABSPATH . 'wp-admin/includes/user.php';
	wp_logout();
	$deleted = wp_delete_user($user_id, 0);
	if ($deleted) {
		wp_safe_redirect(home_url('/?account_deleted=1'));
	} else {
		wp_safe_redirect(wc_get_page_permalink('myaccount'));
	}
	exit;
}
add_action('init', 'naturapets_handle_delete_account', 5);

/**
 * Afficher un message de confirmation après suppression du compte.
 */
function naturapets_account_deleted_notice()
{
	if (!isset($_GET['account_deleted']) || $_GET['account_deleted'] !== '1') {
		return;
	}
?>
	<div class="np-account-deleted-notice" role="status">
		<p>Votre compte a été supprimé avec succès.</p>
	</div>
<?php
}
add_action('wp_body_open', 'naturapets_account_deleted_notice', 5);

/**
 * ==========================================================================
 * BLOC ACF – Section Hero (design Figma – grille 2x2)
 * ==========================================================================
 */

/**
 * Réactiver explicitement les patterns du cœur (au cas où le thème parent les désactiverait).
 */
function naturapets_enable_core_block_patterns() {
	add_theme_support( 'core-block-patterns' );
}
add_action( 'after_setup_theme', 'naturapets_enable_core_block_patterns', 11 );

/**
 * Indique si un pattern est fourni par WordPress (cœur).
 *
 * @param array $pattern Pattern enregistré.
 * @return bool
 */
function naturapets_is_core_block_pattern( $pattern ) {
	$pattern_name = isset( $pattern['name'] ) ? (string) $pattern['name'] : '';
	if ( $pattern_name !== '' && 0 === strpos( $pattern_name, 'core/' ) ) {
		return true;
	}
	if ( ! empty( $pattern['filePath'] ) && is_string( $pattern['filePath'] ) ) {
		$pattern_file = strtolower( wp_normalize_path( $pattern['filePath'] ) );
		if ( false !== strpos( $pattern_file, '/wp-includes/block-patterns' ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Autoriser le répertoire de patterns distants WordPress.org (comportement par défaut du cœur).
 */
add_filter( 'should_load_remote_block_patterns', '__return_true' );

/**
 * Déterminer si un pattern concerne WooCommerce (remote ou plugin).
 *
 * @param array $pattern Pattern enregistré.
 * @return bool
 */
function naturapets_is_woocommerce_pattern( $pattern ) {
	if ( ! empty( $pattern['filePath'] ) && is_string( $pattern['filePath'] ) ) {
		$pattern_file = strtolower( wp_normalize_path( $pattern['filePath'] ) );
		if ( false !== strpos( $pattern_file, '/plugins/woocommerce/' ) ) {
			return true;
		}
	}

	if ( ! empty( $pattern['source'] ) && is_string( $pattern['source'] ) ) {
		$source = strtolower( $pattern['source'] );
		if ( false !== strpos( $source, 'woocommerce' ) ) {
			return true;
		}
	}

	if ( ! empty( $pattern['categories'] ) && is_array( $pattern['categories'] ) ) {
		foreach ( $pattern['categories'] as $category ) {
			if ( ! is_string( $category ) ) {
				continue;
			}

			if ( false !== strpos( strtolower( $category ), 'woocommerce' ) ) {
				return true;
			}
		}
	}

	$fields_to_scan = array( 'name', 'title', 'description', 'content' );
	foreach ( $fields_to_scan as $field ) {
		if ( empty( $pattern[ $field ] ) || ! is_string( $pattern[ $field ] ) ) {
			continue;
		}

		if ( false !== strpos( strtolower( $pattern[ $field ] ), 'woocommerce' ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Limiter les patterns tiers : conserver le thème, WooCommerce, et les patterns natifs WordPress.
 *
 * Les patterns synchronisés/en BDD (wp_block) restent disponibles dans l'éditeur,
 * car ils ne passent pas par le registre des block patterns PHP.
 */
function naturapets_keep_only_local_theme_patterns() {
	if ( ! class_exists( 'WP_Block_Patterns_Registry' ) ) {
		return;
	}

	$registry               = WP_Block_Patterns_Registry::get_instance();
	$patterns               = $registry->get_all_registered();
	$stylesheet_patterns    = wp_normalize_path( trailingslashit( get_stylesheet_directory() . '/patterns' ) );
	$stylesheet_slug_prefix = get_stylesheet() . '/';

	foreach ( $patterns as $pattern ) {
		if ( empty( $pattern['name'] ) || ! is_string( $pattern['name'] ) ) {
			continue;
		}

		$pattern_name = $pattern['name'];
		$is_local     = false;

		if ( ! empty( $pattern['filePath'] ) && is_string( $pattern['filePath'] ) ) {
			$pattern_file = wp_normalize_path( $pattern['filePath'] );
			$is_local     = 0 === strpos( $pattern_file, $stylesheet_patterns );
		}

		if ( ! $is_local && 0 === strpos( $pattern_name, $stylesheet_slug_prefix ) ) {
			$is_local = true;
		}

		// Compat: certains patterns du thème sont enregistrés manuellement avec ce préfixe.
		if ( ! $is_local && 0 === strpos( $pattern_name, 'naturapets/' ) ) {
			$is_local = true;
		}

		$is_woocommerce_pattern = naturapets_is_woocommerce_pattern( $pattern );
		$is_core_pattern        = naturapets_is_core_block_pattern( $pattern );

		if ( ! $is_local && ! $is_woocommerce_pattern && ! $is_core_pattern ) {
			unregister_block_pattern( $pattern_name );
		}
	}
}
add_action( 'init', 'naturapets_keep_only_local_theme_patterns', 99 );

/**
 * Ajouter une catégorie dédiée aux blocs custom NaturaPets.
 */
function naturapets_register_blocks_category( $categories ) {
	$naturapets_category = array(
		'slug'  => 'naturapets',
		'title' => 'Naturapets',
		'icon'  => null,
	);

	foreach ( $categories as $category ) {
		if ( ! empty( $category['slug'] ) && 'naturapets' === $category['slug'] ) {
			return $categories;
		}
	}

	array_unshift( $categories, $naturapets_category );

	return $categories;
}
add_filter( 'block_categories_all', 'naturapets_register_blocks_category', 10, 1 );

/**
 * Forcer la catégorie "Naturapets" pour les blocs naturapets/*.
 */
function naturapets_set_custom_blocks_category( $metadata ) {
	if ( empty( $metadata['name'] ) || ! is_string( $metadata['name'] ) ) {
		return $metadata;
	}

	if ( 0 === strpos( $metadata['name'], 'naturapets/' ) ) {
		$metadata['category'] = 'naturapets';
	}

	return $metadata;
}
add_filter( 'block_type_metadata', 'naturapets_set_custom_blocks_category' );


/**
 * Enregistrer le bloc ACF Section Hero (nécessite ACF Pro).
 */
function naturapets_register_hero_block()
{
	if (!function_exists('acf_register_block_type') && !function_exists('register_block_type')) {
		return;
	}
	$banner_path = get_stylesheet_directory() . '/blocks/hero-banner';
	if (file_exists($banner_path . '/block.json')) {
		register_block_type($banner_path);
	}
	$moveblock_path = get_stylesheet_directory() . '/blocks/moveblock';
	if (file_exists($moveblock_path . '/block.json')) {
		register_block_type($moveblock_path);
	}
	$split_cta_path = get_stylesheet_directory() . '/blocks/split-cta';
	if (file_exists($split_cta_path . '/block.json')) {
		register_block_type($split_cta_path);
	}
	$found_animal_path = get_stylesheet_directory() . '/blocks/found-animal';
	if (file_exists($found_animal_path . '/block.json')) {
		register_block_type($found_animal_path);
	}
	$testimonial_path = get_stylesheet_directory() . '/blocks/testimonial';
	if (file_exists($testimonial_path . '/block.json')) {
		register_block_type($testimonial_path);
	}
	$product_gallery_path = get_stylesheet_directory() . '/blocks/product-gallery';
	if (file_exists($product_gallery_path . '/block.json')) {
		register_block_type($product_gallery_path);
	}
	$icone_path = get_stylesheet_directory() . '/blocks/icone';
	if (file_exists($icone_path . '/block.json')) {
		register_block_type($icone_path);
	}
	$promo_code_path = get_stylesheet_directory() . '/blocks/promo-code';
	if (file_exists($promo_code_path . '/block.json')) {
		register_block_type($promo_code_path);
	}
	$faq_accordeon_path = get_stylesheet_directory() . '/blocks/faq-accordeon';
	if (file_exists($faq_accordeon_path . '/block.json')) {
		register_block_type($faq_accordeon_path);
	}
	$points_numerotes_path = get_stylesheet_directory() . '/blocks/points-numerotes';
	if (file_exists($points_numerotes_path . '/block.json')) {
		register_block_type($points_numerotes_path);
	}
	$presentation_animee_path = get_stylesheet_directory() . '/blocks/presentation-animee';
	if (file_exists($presentation_animee_path . '/block.json')) {
		register_block_type($presentation_animee_path);
	}
	$mosaic_path = get_stylesheet_directory() . '/blocks/mosaic';
	if (file_exists($mosaic_path . '/block.json')) {
		register_block_type($mosaic_path);
	}
	$carousel_texte_path = get_stylesheet_directory() . '/blocks/carousel-texte';
	if (file_exists($carousel_texte_path . '/block.json')) {
		register_block_type($carousel_texte_path);
	}
	$top_banner_rotatif_path = get_stylesheet_directory() . '/blocks/top-banner-rotatif';
	if (file_exists($top_banner_rotatif_path . '/block.json')) {
		register_block_type($top_banner_rotatif_path);
	}
	$page_description_path = get_stylesheet_directory() . '/blocks/page-description';
	if (file_exists($page_description_path . '/block.json')) {
		register_block_type($page_description_path);
	}
}
add_action('init', 'naturapets_register_hero_block');

/**
 * Fallback: enregistrer explicitement le bloc naturapets/page-description.
 * Evite le cas "bloc non pris en charge" si l'enregistrement metadata échoue.
 */
function naturapets_register_page_description_block_fallback()
{
	if (!function_exists('register_block_type') || WP_Block_Type_Registry::get_instance()->is_registered('naturapets/page-description')) {
		return;
	}

	$render_file = get_stylesheet_directory() . '/blocks/page-description/render.php';
	if (!file_exists($render_file)) {
		return;
	}

	register_block_type('naturapets/page-description', array(
		'api_version' => 3,
		'title' => __('Description de la page', 'naturapets'),
		'category' => 'naturapets',
		'icon' => 'text-page',
		'description' => __('Affiche l’extrait (description) de la page courante.', 'naturapets'),
		'render_callback' => static function () use ($render_file) {
			ob_start();
			include $render_file;
			return (string) ob_get_clean();
		},
		'supports' => array(
			'html' => false,
			'inserter' => true,
			'anchor' => true,
			'align' => array('wide', 'full'),
			'spacing' => array(
				'margin' => true,
				'padding' => true,
			),
			'typography' => array(
				'fontSize' => true,
				'lineHeight' => true,
			),
			'color' => array(
				'text' => true,
				'background' => true,
			),
		),
	));
}
add_action('init', 'naturapets_register_page_description_block_fallback', 20);

/**
 * Résoudre l’ID de page source pour les shortcodes de contenu boutique.
 * En éditeur/AJAX preview, il n’y a pas de contexte d’archive : on force la page Boutique.
 *
 * @return int
 */
function naturapets_get_shop_page_context_id()
{
	$post_id = 0;

	if (function_exists('wc_get_page_id')) {
		$shop_id = (int) wc_get_page_id('shop');
		if ($shop_id > 0) {
			$post_id = $shop_id;
		}
	}

	// Sur le front archive produit, on conserve explicitement le contexte archive.
	if (function_exists('is_post_type_archive') && is_post_type_archive('product') && function_exists('wc_get_page_id')) {
		$post_id = (int) wc_get_page_id('shop');
	}

	if ($post_id < 1) {
		$post_id = (int) get_queried_object_id();
	}

	return $post_id > 0 ? $post_id : 0;
}

/**
 * Shortcode fallback pour afficher la description de page (extrait) sur la boutique.
 * Usage: [naturapets_page_description]
 *
 * @return string
 */
function naturapets_page_description_shortcode()
{
	$post_id = naturapets_get_shop_page_context_id();

	if ($post_id < 1) {
		return '';
	}

	$excerpt = get_the_excerpt($post_id);
	if ('' === trim(wp_strip_all_tags($excerpt))) {
		return '';
	}

	$inner = false !== stripos($excerpt, '<p') ? wp_kses_post($excerpt) : wp_kses_post(wpautop($excerpt));
	return '<div class="np-page-description-block"><div class="np-page-description">' . $inner . '</div></div>';
}
add_shortcode('naturapets_page_description', 'naturapets_page_description_shortcode');

/**
 * Shortcode pour afficher le contenu complet de la page Boutique.
 * Usage: [naturapets_page_content]
 *
 * @return string
 */
function naturapets_page_content_shortcode()
{
	$post_id = naturapets_get_shop_page_context_id();

	if ($post_id < 1) {
		return '';
	}

	$content = get_post_field('post_content', $post_id);
	if (!is_string($content) || '' === trim(wp_strip_all_tags($content))) {
		return '';
	}

	// Appliquer les filtres "the_content" (blocs, shortcodes, embeds, etc.).
	$rendered = apply_filters('the_content', $content);
	if (!is_string($rendered) || '' === trim($rendered)) {
		return '';
	}

	return '<div class="np-page-content-block">' . $rendered . '</div>';
}
add_shortcode('naturapets_page_content', 'naturapets_page_content_shortcode');

/**
 * Groupe de champs ACF pour le bloc Section Hero.
 */
function naturapets_hero_block_field_group()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}
	acf_add_local_field_group(
		array(
			'key' => 'group_naturapets_hero_section',
			'title' => 'Bloc Section Hero – Champs',
			'fields' => array(
				array(
					'key' => 'field_hero_texte_haut',
					'label' => 'Texte en haut à droite',
					'name' => 'texte_haut',
					'type' => 'textarea',
					'rows' => 3,
					'placeholder' => "Un système\nsimple et efficace",
				),
				array(
					'key' => 'field_hero_texte_bas',
					'label' => 'Texte en bas à gauche',
					'name' => 'texte_bas',
					'type' => 'textarea',
					'rows' => 3,
					'placeholder' => "Pour nos amis les\nbêtes",
				),
				array(
					'key' => 'field_hero_image_haut_gauche',
					'label' => 'Image en haut à gauche',
					'name' => 'image_haut_gauche',
					'type' => 'image',
					'return_format' => 'array',
					'preview_size' => 'medium',
				),
				array(
					'key' => 'field_hero_image_bas_droite',
					'label' => 'Image en bas à droite',
					'name' => 'image_bas_droite',
					'type' => 'image',
					'return_format' => 'array',
					'preview_size' => 'medium',
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'block',
						'operator' => '==',
						'value' => 'naturapets/hero-section',
					),
				),
			),
		)
	);
}
add_action('acf/init', 'naturapets_hero_block_field_group');

/**
 * Groupe de champs ACF pour le bloc Bannière Hero (design Figma 1-101).
 */
function naturapets_hero_banner_field_group()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}
	acf_add_local_field_group(
		array(
			'key' => 'group_naturapets_hero_banner',
			'title' => 'Bloc Bannière Hero – Champs',
			'fields' => array(
				array(
					'key' => 'field_hero_banner_image',
					'label' => 'Image de fond',
					'name' => 'hero_banner_image',
					'type' => 'image',
					'return_format' => 'array',
					'preview_size' => 'medium',
					'instructions' => 'Affichée si aucune vidéo n\'est renseignée.',
				),
				array(
					'key' => 'field_hero_banner_video',
					'label' => 'Vidéo de fond',
					'name' => 'hero_banner_video',
					'type' => 'file',
					'return_format' => 'array',
					'mime_types' => 'mp4,webm',
					'instructions' => 'Prioritaire sur l\'image. Formats : MP4, WebM.',
				),
				array(
					'key' => 'field_hero_banner_titre',
					'label' => 'Titre',
					'name' => 'hero_banner_titre',
					'type' => 'text',
					'placeholder' => 'Naturapets',
				),
				array(
					'key' => 'field_hero_banner_slogan',
					'label' => 'Slogan',
					'name' => 'hero_banner_slogan',
					'type' => 'text',
					'placeholder' => 'La tranquillité au bout du collier',
				),
				array(
					'key' => 'field_hero_banner_bouton_texte',
					'label' => 'Texte du bouton',
					'name' => 'hero_banner_bouton_texte',
					'type' => 'text',
					'placeholder' => 'Découvrir',
				),
				array(
					'key' => 'field_hero_banner_bouton_url',
					'label' => 'URL du bouton',
					'name' => 'hero_banner_bouton_url',
					'type' => 'url',
					'placeholder' => 'https://',
				),
				array(
					'key' => 'field_hero_banner_bouton_taille',
					'label' => 'Taille du bouton',
					'name' => 'hero_banner_bouton_taille',
					'type' => 'select',
					'choices' => array(
						'' => __('Défaut (thème)', 'naturapets'),
						'x-small' => __('Très petit', 'naturapets'),
						'small' => __('Petit', 'naturapets'),
						'medium' => __('Moyen', 'naturapets'),
						'large' => __('Grand', 'naturapets'),
						'x-large' => __('Très grand', 'naturapets'),
					),
					'default_value' => '',
					'instructions' => __('Utilise les presets de typographie du thème.', 'naturapets'),
				),
				array(
					'key' => 'field_hero_banner_titre_taille',
					'label' => 'Taille du titre',
					'name' => 'hero_banner_titre_taille',
					'type' => 'select',
					'choices' => array(
						'' => __('Défaut (ou panneau Typographie du bloc)', 'naturapets'),
						'x-small' => __('Très petit', 'naturapets'),
						'small' => __('Petit', 'naturapets'),
						'medium' => __('Moyen', 'naturapets'),
						'large' => __('Grand', 'naturapets'),
						'x-large' => __('Très grand', 'naturapets'),
						'max-36' => __('36px', 'naturapets'),
						'max-48' => __('48px', 'naturapets'),
						'max-60' => __('60px', 'naturapets'),
						'max-72' => __('72px', 'naturapets'),
					),
					'default_value' => '',
					'instructions' => __('Optionnel. Sinon, utilisez le panneau « Typographie » dans la barre latérale du bloc.', 'naturapets'),
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'block',
						'operator' => '==',
						'value' => 'naturapets/hero-banner',
					),
				),
			),
		)
	);
}
add_action('acf/init', 'naturapets_hero_banner_field_group');

/**
 * Groupe de champs ACF pour le bloc Section deux colonnes (design Figma 3-27).
 */
function naturapets_split_cta_field_group()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}
	acf_add_local_field_group(
		array(
			'key' => 'group_naturapets_split_cta',
			'title' => 'Bloc Section deux colonnes – Champs',
			'fields' => array(
				array(
					'key' => 'field_split_cta_image_gauche',
					'label' => 'Image colonne gauche',
					'name' => 'split_cta_image_gauche',
					'type' => 'image',
					'return_format' => 'array',
					'preview_size' => 'medium',
					'instructions' => 'Optionnel. Si vide, fond gris clair affiché.',
				),
				array(
					'key' => 'field_split_cta_titre',
					'label' => 'Titre',
					'name' => 'split_cta_titre',
					'type' => 'text',
					'placeholder' => 'GROS TITRE',
				),
				array(
					'key' => 'field_split_cta_texte',
					'label' => 'Texte',
					'name' => 'split_cta_texte',
					'type' => 'textarea',
					'rows' => 4,
					'placeholder' => 'Lorem ipsum dolor sit amet...',
				),
				array(
					'key' => 'field_split_cta_bouton_texte',
					'label' => 'Texte du bouton',
					'name' => 'split_cta_bouton_texte',
					'type' => 'text',
					'placeholder' => 'Découvrir',
				),
				array(
					'key' => 'field_split_cta_bouton_url',
					'label' => 'URL du bouton',
					'name' => 'split_cta_bouton_url',
					'type' => 'url',
					'placeholder' => 'https://',
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'block',
						'operator' => '==',
						'value' => 'naturapets/split-cta',
					),
				),
			),
		)
	);
}
add_action('acf/init', 'naturapets_split_cta_field_group');

/**
 * Groupe de champs ACF pour le bloc J'ai trouvé un animal (design Figma 3-29).
 */
function naturapets_found_animal_field_group()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}
	acf_add_local_field_group(
		array(
			'key' => 'group_naturapets_found_animal',
			'title' => "Bloc J'ai trouvé un animal – Champs",
			'fields' => array(
				array(
					'key' => 'field_found_animal_titre',
					'label' => 'Titre',
					'name' => 'found_animal_titre',
					'type' => 'text',
					'placeholder' => "J'ai trouvé un animal",
				),
				array(
					'key' => 'field_found_animal_placeholder',
					'label' => 'Placeholder du champ',
					'name' => 'found_animal_placeholder',
					'type' => 'text',
					'placeholder' => 'entrer le numéro du médaillon',
				),
				array(
					'key' => 'field_found_animal_bouton_texte',
					'label' => 'Texte du bouton',
					'name' => 'found_animal_bouton_texte',
					'type' => 'text',
					'placeholder' => 'Chercher',
				),
				array(
					'key' => 'field_found_animal_form_action_url',
					'label' => 'URL de la page de recherche',
					'name' => 'found_animal_form_action_url',
					'type' => 'url',
					'placeholder' => 'https://',
					'instructions' => 'Page vers laquelle le formulaire envoie (GET). Si vide, accueil.',
				),
				array(
					'key' => 'field_found_animal_param_name',
					'label' => 'Nom du paramètre (médaillon)',
					'name' => 'found_animal_param_name',
					'type' => 'text',
					'placeholder' => 'medaillon',
					'instructions' => 'Nom du paramètre GET pour le numéro de médaillon.',
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'block',
						'operator' => '==',
						'value' => 'naturapets/found-animal',
					),
				),
			),
		)
	);
}
add_action('acf/init', 'naturapets_found_animal_field_group');

/**
 * Choix du type d'animal (valeur stockée => libellé).
 *
 * @return array<string, string>
 */
function naturapets_get_type_animal_choices()
{
	return array(
		'chien' => 'Chien',
		'chat' => 'Chat',
		'cheval' => 'Cheval',
		'furet' => 'Furet',
	);
}

/**
 * Libellé affichable pour type_animal (liste déroulante ou ancienne saisie libre).
 *
 * @param mixed $value Valeur ACF type_animal.
 */
function naturapets_get_type_animal_label($value)
{
	if ($value === '' || $value === null) {
		return '';
	}
	$value = is_string($value) ? strtolower(trim($value)) : '';
	$choices = naturapets_get_type_animal_choices();
	if (isset($choices[$value])) {
		return $choices[$value];
	}
	foreach ($choices as $slug => $label) {
		if (strcasecmp((string) $value, $slug) === 0 || strcasecmp((string) $value, $label) === 0) {
			return $label;
		}
	}
	return is_string($value) ? $value : '';
}

/**
 * Champs ACF locaux pour le CPT animal (type, race, âge).
 * Si un groupe ACF existant définit déjà ces noms de champs, retirer le doublon dans l’admin ACF.
 */
function naturapets_animal_acf_field_group()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key' => 'group_naturapets_animal_medaillon',
			'title' => 'Animal – Médaillon',
			'fields' => array(
				array(
					'key' => 'field_naturapets_animal_type_animal',
					'label' => 'Type d\'animal',
					'name' => 'type_animal',
					'type' => 'select',
					'choices' => naturapets_get_type_animal_choices(),
					'allow_null' => 1,
					'return_format' => 'value',
				),
				array(
					'key' => 'field_naturapets_animal_race',
					'label' => 'Race',
					'name' => 'race',
					'type' => 'text',
				),
				array(
					'key' => 'field_naturapets_animal_age',
					'label' => 'Âge',
					'name' => 'age',
					'type' => 'text',
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'animal',
					),
				),
			),
			'position' => 'acf_after_title',
			'style' => 'default',
		)
	);
}
add_action('acf/init', 'naturapets_animal_acf_field_group');

/**
 * Groupe de champs ACF pour le bloc Témoignage (design Figma 3-56).
 */
function naturapets_testimonial_field_group()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}
	acf_add_local_field_group(
		array(
			'key' => 'group_naturapets_testimonial',
			'title' => 'Bloc Témoignage – Champs',
			'fields' => array(
				array(
					'key' => 'field_testimonial_photo',
					'label' => 'Photo (cercle)',
					'name' => 'testimonial_photo',
					'type' => 'image',
					'return_format' => 'array',
					'preview_size' => 'medium',
					'instructions' => __('Optionnel. Si vide, un cercle gris est affiché.', 'naturapets'),
				),
				array(
					'key' => 'field_testimonial_citation',
					'label' => 'Citation',
					'name' => 'testimonial_citation',
					'type' => 'textarea',
					'rows' => 3,
					'placeholder' => __("J'ai retrouvé facilement Rantanplan et j'en suis très rassurée!", 'naturapets'),
				),
				array(
					'key' => 'field_testimonial_auteur',
					'label' => 'Auteur',
					'name' => 'testimonial_auteur',
					'type' => 'text',
					'placeholder' => 'Liliane',
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'block',
						'operator' => '==',
						'value' => 'naturapets/testimonial',
					),
				),
			),
		)
	);
}
add_action('acf/init', 'naturapets_testimonial_field_group');

/**
 * Groupe de champs ACF pour le bloc FAQ Accordéon (design Pencil).
 */
function naturapets_faq_accordeon_field_group()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}
	acf_add_local_field_group(
		array(
			'key' => 'group_naturapets_faq_accordeon',
			'title' => 'Bloc FAQ Accordéon – Champs',
			'fields' => array(
				array(
					'key' => 'field_faq_label',
					'label' => 'Label',
					'name' => 'faq_label',
					'type' => 'text',
					'placeholder' => 'FAQ',
					'instructions' => __('Petite étiquette affichée au-dessus du titre (ex: "FAQ").', 'naturapets'),
				),
				array(
					'key' => 'field_faq_title',
					'label' => 'Titre',
					'name' => 'faq_title',
					'type' => 'text',
					'placeholder' => 'Questions fréquentes',
				),
				array(
					'key' => 'field_faq_subtitle',
					'label' => 'Sous-titre',
					'name' => 'faq_subtitle',
					'type' => 'text',
					'placeholder' => 'Tout ce que vous devez savoir sur nos médaillons connectés',
				),
				array(
					'key' => 'field_faq_items',
					'label' => 'Questions / Réponses',
					'name' => 'faq_items',
					'type' => 'repeater',
					'min' => 1,
					'button_label' => __('Ajouter une question', 'naturapets'),
					'layout' => 'block',
					'sub_fields' => array(
						array(
							'key' => 'field_faq_question',
							'label' => 'Question',
							'name' => 'faq_question',
							'type' => 'text',
							'required' => 1,
						),
						array(
							'key' => 'field_faq_answer',
							'label' => 'Réponse',
							'name' => 'faq_answer',
							'type' => 'textarea',
							'rows' => 4,
							'required' => 1,
						),
					),
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'block',
						'operator' => '==',
						'value' => 'naturapets/faq-accordeon',
					),
				),
			),
		)
	);
}
add_action('acf/init', 'naturapets_faq_accordeon_field_group');

/**
 * Groupe de champs ACF pour le bloc Points Numérotés (design Pencil).
 */
function naturapets_points_numerotes_field_group()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}
	acf_add_local_field_group(
		array(
			'key' => 'group_naturapets_points_numerotes',
			'title' => 'Bloc Points Numérotés – Champs',
			'fields' => array(
				array(
					'key' => 'field_points_label',
					'label' => 'Label',
					'name' => 'points_label',
					'type' => 'text',
					'placeholder' => 'Comment ça marche',
					'instructions' => __('Petit texte au-dessus du titre (ex: "Comment ça marche").', 'naturapets'),
				),
				array(
					'key' => 'field_points_title',
					'label' => 'Titre',
					'name' => 'points_title',
					'type' => 'text',
					'placeholder' => 'Comment ça marche',
					'required' => 1,
				),
				array(
					'key' => 'field_points_description',
					'label' => 'Description',
					'name' => 'points_description',
					'type' => 'text',
					'placeholder' => 'Quatre étapes simples pour offrir un médaillon unique à votre compagnon',
				),
				array(
					'key' => 'field_points_items',
					'label' => 'Étapes',
					'name' => 'points_items',
					'type' => 'repeater',
					'min' => 1,
					'max' => 9,
					'button_label' => __('Ajouter une étape', 'naturapets'),
					'layout' => 'block',
					'sub_fields' => array(
						array(
							'key' => 'field_points_item_title',
							'label' => 'Titre',
							'name' => 'points_item_title',
							'type' => 'text',
							'required' => 1,
						),
						array(
							'key' => 'field_points_item_description',
							'label' => 'Description',
							'name' => 'points_item_description',
							'type' => 'textarea',
							'rows' => 3,
						),
					),
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'block',
						'operator' => '==',
						'value' => 'naturapets/points-numerotes',
					),
				),
			),
		)
	);
}
add_action('acf/init', 'naturapets_points_numerotes_field_group');

/**
 * Groupe de champs ACF pour le bloc Présentation animée (design Pencil).
 */
function naturapets_presentation_animee_field_group()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}
	acf_add_local_field_group(
		array(
			'key' => 'group_naturapets_presentation_animee',
			'title' => 'Bloc Présentation animée – Champs',
			'fields' => array(
				array(
					'key' => 'field_pres_title',
					'label' => 'Titre',
					'name' => 'pres_title',
					'type' => 'text',
					'required' => 1,
					'placeholder' => 'Un moyen fiable et économique pour protéger votre animal',
				),
				array(
					'key' => 'field_pres_text',
					'label' => 'Texte descriptif',
					'name' => 'pres_text',
					'type' => 'textarea',
					'rows' => 4,
				),
				array(
					'key' => 'field_pres_image',
					'label' => 'Image (parallax)',
					'name' => 'pres_image',
					'type' => 'image',
					'return_format' => 'array',
					'preview_size' => 'medium',
					'instructions' => __('Image affichée en arrière-plan avec effet parallax. Fonctionne bien avec un PNG transparent.', 'naturapets'),
					'required' => 1,
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'block',
						'operator' => '==',
						'value' => 'naturapets/presentation-animee',
					),
				),
			),
		)
	);
}
add_action('acf/init', 'naturapets_presentation_animee_field_group');

/**
 * Groupe de champs ACF pour le bloc Mosaïque (design Pencil : k3CR0).
 */
function naturapets_mosaic_field_group()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}
	acf_add_local_field_group(array(
		'key'    => 'group_naturapets_mosaic',
		'title'  => 'Bloc Mosaïque – Champs',
		'fields' => array(
			array(
				'key'           => 'field_mosaic_image_main',
				'label'         => 'Image principale (grande)',
				'name'          => 'mosaic_image_main',
				'type'          => 'image',
				'return_format' => 'array',
				'preview_size'  => 'medium',
				'required'      => 1,
				'instructions'  => __('Image pleine hauteur, colonne de gauche par défaut.', 'naturapets'),
			),
			array(
				'key'           => 'field_mosaic_image_secondary',
				'label'         => 'Image secondaire (haut droite)',
				'name'          => 'mosaic_image_secondary',
				'type'          => 'image',
				'return_format' => 'array',
				'preview_size'  => 'medium',
				'required'      => 1,
				'instructions'  => __('Image en haut de la colonne secondaire.', 'naturapets'),
			),
			array(
				'key'         => 'field_mosaic_title',
				'label'       => 'Titre',
				'name'        => 'mosaic_title',
				'type'        => 'text',
				'required'    => 1,
				'placeholder' => 'Protégez ceux que vous aimez',
			),
			array(
				'key'         => 'field_mosaic_description',
				'label'       => 'Description',
				'name'        => 'mosaic_description',
				'type'        => 'textarea',
				'rows'        => 3,
				'placeholder' => 'Nos médaillons connectés offrent une tranquillité d\'esprit au quotidien.',
			),
			array(
				'key'         => 'field_mosaic_button_label',
				'label'       => 'Libellé du bouton',
				'name'        => 'mosaic_button_label',
				'type'        => 'text',
				'placeholder' => 'Découvrir',
			),
			array(
				'key'  => 'field_mosaic_button_url',
				'label' => 'Lien du bouton',
				'name'  => 'mosaic_button_url',
				'type'  => 'url',
			),
			array(
				'key'          => 'field_mosaic_inverted',
				'label'        => 'Inverser l\'ordre des colonnes',
				'name'         => 'mosaic_inverted',
				'type'         => 'true_false',
				'ui'           => 1,
				'message'      => __('Colonne texte à gauche, grande image à droite', 'naturapets'),
				'default_value' => 0,
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'naturapets/mosaic',
				),
			),
		),
	));
}
add_action('acf/init', 'naturapets_mosaic_field_group');

/**
 * Groupe de champs ACF pour le bloc Carousel de texte.
 */
function naturapets_carousel_texte_field_group()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}
	acf_add_local_field_group(array(
		'key'      => 'group_carousel_texte',
		'title'    => 'Carousel de texte',
		'fields'   => array(
			array(
				'key'           => 'field_carousel_title',
				'label'         => 'Titre',
				'name'          => 'carousel_title',
				'type'          => 'text',
				'instructions'  => 'Titre affiché au-dessus du carousel (optionnel).',
			),
			array(
				'key'           => 'field_carousel_subtitle',
				'label'         => 'Sous-titre',
				'name'          => 'carousel_subtitle',
				'type'          => 'text',
			),
			array(
				'key'           => 'field_carousel_items',
				'label'         => 'Cartes',
				'name'          => 'carousel_items',
				'type'          => 'repeater',
				'min'           => 1,
				'layout'        => 'block',
				'button_label'  => 'Ajouter une carte',
				'sub_fields'    => array(
					array(
						'key'   => 'field_carousel_card_label',
						'label' => 'Label (petit texte orange)',
						'name'  => 'card_label',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_carousel_card_title',
						'label' => 'Titre de la carte',
						'name'  => 'card_title',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_carousel_card_text',
						'label' => 'Texte',
						'name'  => 'card_text',
						'type'  => 'textarea',
						'rows'  => 3,
					),
				),
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'naturapets/carousel-texte',
				),
			),
		),
	));
}
add_action('acf/init', 'naturapets_carousel_texte_field_group');

/**
 * Bouton "Ajouter au panier" → "Ajouter" dans les boucles produits (Query Loop).
 * L'icône SVG panier est ajoutée via CSS (::before) pour correspondre au design Pencil.
 */
add_filter('woocommerce_product_add_to_cart_text', function ($text, $product) {
	// Ne change que dans les contextes loop/archive, pas sur la fiche produit
	if (!is_singular('product')) {
		return __('Ajouté au panié', 'naturapets');
	}
	return $text;
}, 10, 2);

/**
 * ID de pièce jointe de la première image de galerie produit (hors image à la une).
 *
 * @param int $product_id ID du produit WooCommerce.
 * @return int ID attachment ou 0.
 */
function naturapets_get_product_first_gallery_attachment_id($product_id)
{
	$product_id = (int) $product_id;
	if ($product_id < 1 || !function_exists('wc_get_product')) {
		return 0;
	}
	$product = wc_get_product($product_id);
	if (!$product) {
		return 0;
	}
	$featured = (int) $product->get_image_id();
	foreach ($product->get_gallery_image_ids() as $gid) {
		$gid = (int) $gid;
		if ($gid < 1 || $gid === $featured) {
			continue;
		}
		if (wp_get_attachment_image_url($gid, 'woocommerce_thumbnail')) {
			return $gid;
		}
	}
	return 0;
}

/**
 * Résout l’ID produit WooCommerce pour le bloc « Galerie produit » (contexte FSE, fiche produit, $post).
 *
 * @param WP_Block|null $block Instance du bloc lors du rendu dynamique.
 * @return int                  0 si aucun produit déterminé.
 */
function naturapets_product_gallery_get_product_id_for_block($block)
{
	if ($block instanceof WP_Block && !empty($block->context['postId'])) {
		$cid = (int) $block->context['postId'];
		if ($cid > 0 && 'product' === get_post_type($cid)) {
			return $cid;
		}
	}
	if (function_exists('is_product') && is_product()) {
		$qid = (int) get_queried_object_id();
		if ($qid > 0) {
			return $qid;
		}
	}
	global $post;
	if ($post && isset($post->post_type) && 'product' === $post->post_type) {
		return (int) $post->ID;
	}
	return 0;
}

/**
 * Indique si le survol galerie ne doit pas s’appliquer (fiche produit : image principale du produit courant).
 *
 * @param int $post_id ID du produit affiché par le bloc.
 * @return bool
 */
function naturapets_skip_product_gallery_hover_on_single_main($post_id)
{
	$post_id = (int) $post_id;
	if ($post_id < 1 || !function_exists('is_product') || !is_product()) {
		return false;
	}
	$main_id = (int) get_queried_object_id();
	return $main_id > 0 && $post_id === $main_id;
}

/**
 * Injecte la 1re image de galerie après la 1re balise &lt;img&gt; et enveloppe pour le survol CSS.
 *
 * @param string $block_content HTML du bloc.
 * @param int    $post_id       ID produit.
 * @param string $scale         Attribut scale (ex. cover, contain).
 * @return string
 */
function naturapets_product_gallery_hover_markup($block_content, $post_id, $scale = 'cover')
{
	$post_id = (int) $post_id;
	$gallery_id = naturapets_get_product_first_gallery_attachment_id($post_id);
	if ($gallery_id < 1 || '' === trim($block_content)) {
		return $block_content;
	}

	$feat = array(
		'class' => '',
		'style' => '',
	);
	if (class_exists('WP_HTML_Tag_Processor')) {
		$proc = new WP_HTML_Tag_Processor($block_content);
		if ($proc->next_tag(array('tag_name' => 'IMG'))) {
			foreach (array('class', 'style') as $attr_name) {
				$val = $proc->get_attribute($attr_name);
				if (is_string($val) && '' !== $val) {
					$feat[$attr_name] = $val;
				}
			}
		}
	}

	// Reprendre les classes « visuelles » de l’image principale ; wp_get_attachment_image réinjecte attachment-* / size-*.
	$merged_class = trim((string) $feat['class']);
	$merged_class = preg_replace('/\battachment-\S+/', '', $merged_class);
	$merged_class = preg_replace('/\bsize-\S+/', '', $merged_class);
	$merged_class = preg_replace('/\bwp-image-\d+\b/', 'wp-image-' . $gallery_id, $merged_class);
	$merged_class = trim(preg_replace('/\s+/', ' ', $merged_class) . ' np-product-thumb-hover__gallery');

	$img_attrs = array(
		'class' => $merged_class,
		'alt' => '',
		'loading' => 'lazy',
		'decoding' => 'async',
		'tabindex' => '-1',
		'aria-hidden' => 'true',
	);
	if ('' !== $feat['style']) {
		$img_attrs['style'] = $feat['style'];
	}

	$gallery_img = wp_get_attachment_image($gallery_id, 'woocommerce_thumbnail', false, $img_attrs);
	if ('' === $gallery_img) {
		return $block_content;
	}

	$inner = preg_replace('/<img\b[^>]*>/i', '$0' . $gallery_img, $block_content, 1);
	if (null === $inner || $inner === $block_content) {
		return $block_content;
	}

	$scale = is_string($scale) ? $scale : 'cover';
	$classes = 'np-product-thumb-hover';
	if ('contain' === $scale) {
		$classes .= ' np-product-thumb-hover--contain';
	}
	return '<div class="' . esc_attr($classes) . '">' . $inner . '</div>';
}

/**
 * Survol : afficher la première image de galerie sur l’image produit (bloc WooCommerce) ou l’image à la une.
 * Désactivé sur la fiche produit pour le produit principal (même ID que la page).
 *
 * @param string         $block_content HTML rendu du bloc.
 * @param array          $parsed_block  Bloc parsé.
 * @param WP_Block|null  $instance      Instance (WP 5.9+), pour le contexte postId / postType.
 * @return string
 */
function naturapets_wrap_product_featured_image_gallery_hover($block_content, $parsed_block, $instance = null)
{
	$block_name = isset($parsed_block['blockName']) ? (string) $parsed_block['blockName'] : '';
	$allowed = array('core/post-featured-image', 'woocommerce/product-image');
	if ('' === $block_name || !in_array($block_name, $allowed, true)) {
		return $block_content;
	}
	if (!$instance instanceof WP_Block) {
		return $block_content;
	}
	$post_id = isset($instance->context['postId']) ? (int) $instance->context['postId'] : 0;
	if ($post_id < 1) {
		return $block_content;
	}
	$post_type = isset($instance->context['postType']) ? (string) $instance->context['postType'] : '';
	if ('core/post-featured-image' === $block_name) {
		if ('product' !== $post_type) {
			$post_type = get_post_type($post_id);
		}
		if ('product' !== $post_type) {
			return $block_content;
		}
	} elseif ('woocommerce/product-image' === $block_name) {
		if ('product' !== get_post_type($post_id)) {
			return $block_content;
		}
	}
	if (naturapets_skip_product_gallery_hover_on_single_main($post_id)) {
		return $block_content;
	}
	$attrs = $instance->attributes;
	$scale = isset($attrs['scale']) && is_string($attrs['scale']) ? $attrs['scale'] : 'cover';
	return naturapets_product_gallery_hover_markup($block_content, $post_id, $scale);
}
add_filter('render_block', 'naturapets_wrap_product_featured_image_gallery_hover', 10, 3);

/**
 * Récupérer la palette de couleurs du thème (theme.json).
 * Retourne un tableau hex => nom pour les choix ACF (affichage + valeur stockée = hex).
 */
function naturapets_get_theme_color_palette()
{
	$palette = array();
	if (class_exists('WP_Theme_JSON_Resolver')) {
		$theme_json = WP_Theme_JSON_Resolver::get_merged_data();
		$settings = $theme_json->get_settings();
		$raw = $settings['color']['palette'] ?? array();
		foreach ($raw as $item) {
			if (!empty($item['color'])) {
				$hex = is_string($item['color']) ? $item['color'] : '';
				if ($hex && preg_match('/^#[0-9a-fA-F]{3,8}$/', $hex)) {
					$name = isset($item['name']) ? $item['name'] : $item['slug'] ?? $hex;
					$palette[$hex] = $name;
				}
			}
		}
	}
	return $palette;
}

/**
 * Groupe de champs ACF pour le bloc Icône.
 */
function naturapets_icone_field_group()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}
	$palette_choices = naturapets_get_theme_color_palette();
	$default_hex = array_key_first($palette_choices);
	acf_add_local_field_group(
		array(
			'key' => 'group_naturapets_icone',
			'title' => 'Bloc Icône – Champs',
			'fields' => array(
				array(
					'key' => 'field_icone_image',
					'label' => 'Image',
					'name' => 'icone_image',
					'type' => 'image',
					'return_format' => 'array',
					'preview_size' => 'thumbnail',
					'instructions' => __('Image affichée au centre du cercle. Taille max 60×60 px.', 'naturapets'),
				),
				array(
					'key' => 'field_icone_background',
					'label' => 'Couleur de fond',
					'name' => 'icone_background',
					'type' => 'radio',
					'choices' => $palette_choices,
					'default_value' => $default_hex ?: '',
					'layout' => 'horizontal',
					'return_format' => 'value',
					'instructions' => __('Couleur du cercle de fond (palette du thème).', 'naturapets'),
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'block',
						'operator' => '==',
						'value' => 'naturapets/icone',
					),
				),
			),
		)
	);
}
add_action('acf/init', 'naturapets_icone_field_group');

/**
 * ==========================================================================
 * BLOC MOVEBLOCK – Bibliothèque d’effets GSAP
 * ==========================================================================
 */

/**
 * Groupe de champs ACF pour le bloc Moveblock (effet, options, sélecteur cible).
 */
function naturapets_moveblock_field_group()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}
	$effect_choices = array(
		'fadeIn' => 'Fade In',
		'fadeOut' => 'Fade Out',
		'fadeFromTo' => 'Fade In/Out',
		'slideInLeft' => 'Slide from left',
		'slideInRight' => 'Slide from right',
		'slideInTop' => 'Slide from top',
		'slideInBottom' => 'Slide from bottom',
		'slideOutLeft' => 'Slide to left',
		'slideOutRight' => 'Slide to right',
		'moveX' => 'Move X',
		'moveY' => 'Move Y',
		'scaleUp' => 'Scale up',
		'scaleDown' => 'Scale down',
		'scaleFromTo' => 'Scale pulse',
		'scaleX' => 'Scale X',
		'scaleY' => 'Scale Y',
		'rotateIn' => 'Rotate in',
		'rotateOut' => 'Rotate out',
		'rotateY' => 'Rotate Y (3D)',
		'rotateX' => 'Rotate X (3D)',
		'skewIn' => 'Skew in',
		'skewOut' => 'Skew out',
		'popIn' => 'Pop in',
		'blurIn' => 'Blur in',
		'blurOut' => 'Blur out',
		'dropIn' => 'Drop in',
		'bounceIn' => 'Bounce in',
		'colorChange' => 'Color change',
		'backgroundColor' => 'Background color',
	);
	$ease_choices = array(
		'none' => 'none',
		'power1.in' => 'power1.in',
		'power1.out' => 'power1.out',
		'power1.inOut' => 'power1.inOut',
		'power2.in' => 'power2.in',
		'power2.out' => 'power2.out',
		'power2.inOut' => 'power2.inOut',
		'power3.in' => 'power3.in',
		'power3.out' => 'power3.out',
		'power3.inOut' => 'power3.inOut',
		'power4.in' => 'power4.in',
		'power4.out' => 'power4.out',
		'power4.inOut' => 'power4.inOut',
		'back.in' => 'back.in',
		'back.out' => 'back.out',
		'back.inOut' => 'back.inOut',
		'bounce.in' => 'bounce.in',
		'bounce.out' => 'bounce.out',
		'bounce.inOut' => 'bounce.inOut',
		'circ.in' => 'circ.in',
		'circ.out' => 'circ.out',
		'circ.inOut' => 'circ.inOut',
		'elastic.in' => 'elastic.in',
		'elastic.out' => 'elastic.out',
		'elastic.inOut' => 'elastic.inOut',
		'expo.in' => 'expo.in',
		'expo.out' => 'expo.out',
		'expo.inOut' => 'expo.inOut',
		'sine.in' => 'sine.in',
		'sine.out' => 'sine.out',
		'sine.inOut' => 'sine.inOut',
	);
	acf_add_local_field_group(
		array(
			'key' => 'group_naturapets_moveblock',
			'title' => 'Moveblock – Animation GSAP',
			'fields' => array(
				array(
					'key' => 'field_moveblock_effect',
					'label' => 'Effet GSAP',
					'name' => 'effect',
					'type' => 'select',
					'choices' => $effect_choices,
					'default_value' => 'fadeIn',
				),
				array(
					'key' => 'field_moveblock_target',
					'label' => 'Cibler l’élément (sélecteur CSS)',
					'name' => 'target_selector',
					'type' => 'text',
					'placeholder' => 'ex: .hero-title, #nav, .ma-classe',
					'instructions' => 'Sélecteur CSS de l’élément à animer sur la page (classe, id, etc.).',
				),
				array(
					'key' => 'field_moveblock_duration',
					'label' => 'Durée (secondes)',
					'name' => 'duration',
					'type' => 'number',
					'min' => 0,
					'step' => 0.1,
					'default_value' => 1,
				),
				array(
					'key' => 'field_moveblock_delay',
					'label' => 'Délai (secondes)',
					'name' => 'delay',
					'type' => 'number',
					'min' => 0,
					'step' => 0.1,
					'default_value' => 0,
				),
				array(
					'key' => 'field_moveblock_ease',
					'label' => 'Ease',
					'name' => 'ease',
					'type' => 'select',
					'choices' => $ease_choices,
					'default_value' => 'power2.out',
				),
				array(
					'key' => 'field_moveblock_stagger',
					'label' => 'Stagger (secondes)',
					'name' => 'stagger',
					'type' => 'number',
					'min' => 0,
					'step' => 0.05,
					'default_value' => 0,
					'instructions' => 'Décalage entre chaque élément si le sélecteur en cible plusieurs. 0 = pas de stagger.',
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'block',
						'operator' => '==',
						'value' => 'naturapets/moveblock',
					),
				),
			),
		)
	);
}
add_action('acf/init', 'naturapets_moveblock_field_group');

/**
 * Enqueue GSAP et le script Moveblock sur le front quand le bloc est utilisé.
 */
function naturapets_enqueue_moveblock_assets()
{
	if (!function_exists('has_blocks')) {
		return;
	}
	global $post;
	if (!$post || !has_blocks($post->post_content)) {
		return;
	}
	if (strpos($post->post_content, 'naturapets/moveblock') === false) {
		return;
	}
	$theme_dir = get_stylesheet_directory();
	$theme_uri = get_stylesheet_directory_uri();
	$gsap_path = $theme_dir . '/node_modules/gsap/dist/gsap.min.js';
	if (file_exists($gsap_path)) {
		wp_enqueue_script(
			'gsap',
			$theme_uri . '/node_modules/gsap/dist/gsap.min.js',
			array(),
			'3.12',
			true
		);
	} else {
		wp_enqueue_script(
			'gsap',
			'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js',
			array(),
			'3.12.5',
			true
		);
	}
	$moveblock_js = $theme_dir . '/assets/js/moveblock.js';
	if (file_exists($moveblock_js)) {
		wp_enqueue_script(
			'naturapets-moveblock',
			$theme_uri . '/assets/js/moveblock.js',
			array('gsap'),
			filemtime($moveblock_js),
			true
		);
	}
}
add_action('wp_enqueue_scripts', 'naturapets_enqueue_moveblock_assets');

/**
 * ==========================================================================
 * PRODUITS : ID unique pour chaque produit
 * ==========================================================================
 */

/**
 * Générer un ID unique pour un produit.
 */
function naturapets_generate_product_unique_id()
{
	$prefix = 'NP';
	$unique = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
	return $prefix . '-' . $unique;
}

/**
 * Récupérer ou créer l'ID unique d'un produit.
 */
function naturapets_get_product_unique_id($product_id)
{
	$unique_id = get_post_meta($product_id, '_naturapets_unique_id', true);

	if (!$unique_id) {
		$unique_id = naturapets_generate_product_unique_id();
		update_post_meta($product_id, '_naturapets_unique_id', $unique_id);
	}

	return $unique_id;
}

/**
 * Ajouter le champ ID unique dans l'onglet Inventaire du produit.
 */
function naturapets_add_product_unique_id_field()
{
	global $post;

	$unique_id = naturapets_get_product_unique_id($post->ID);

	echo '<div class="options_group">';

	woocommerce_wp_text_input(array(
		'id' => '_naturapets_unique_id',
		'label' => 'ID Unique Naturapets',
		'description' => 'Identifiant unique du produit (utilisé pour les QR codes)',
		'desc_tip' => true,
		'value' => $unique_id,
		'custom_attributes' => array('readonly' => 'readonly'),
	));

	echo '</div>';
}
add_action('woocommerce_product_options_inventory_product_data', 'naturapets_add_product_unique_id_field');

/**
 * Sauvegarder l'ID unique du produit (générer si vide).
 */
function naturapets_save_product_unique_id($post_id)
{
	$unique_id = get_post_meta($post_id, '_naturapets_unique_id', true);

	if (!$unique_id) {
		$unique_id = naturapets_generate_product_unique_id();
		update_post_meta($post_id, '_naturapets_unique_id', $unique_id);
	}
}
add_action('woocommerce_process_product_meta', 'naturapets_save_product_unique_id');

/**
 * Ajouter une colonne ID Unique dans la liste des produits.
 */
function naturapets_add_product_unique_id_column($columns)
{
	$new_columns = array();

	foreach ($columns as $key => $value) {
		$new_columns[$key] = $value;

		if ($key === 'sku') {
			$new_columns['unique_id'] = 'ID Unique';
		}
	}

	return $new_columns;
}
add_filter('manage_edit-product_columns', 'naturapets_add_product_unique_id_column');

/**
 * Afficher l'ID unique dans la colonne.
 */
function naturapets_product_unique_id_column_content($column, $post_id)
{
	if ($column === 'unique_id') {
		echo '<code>' . esc_html(naturapets_get_product_unique_id($post_id)) . '</code>';
	}
}
add_action('manage_product_posts_custom_column', 'naturapets_product_unique_id_column_content', 10, 2);

// Enregistrer l'endpoint
function naturapets_add_animals_endpoint()
{
	add_rewrite_endpoint('mes-animaux', EP_ROOT | EP_PAGES);
}
add_action('init', 'naturapets_add_animals_endpoint');

/**
 * Enregistrer le CPT Médaillon public (version publique, une URL propre par médaillon).
 */
function naturapets_register_medaillon_public_cpt()
{
	$labels = array(
		'name' => __('Médaillons publics', 'naturapets'),
		'singular_name' => __('Médaillon public', 'naturapets'),
		'menu_name' => __('Médaillons publics', 'naturapets'),
		'add_new' => __('Ajouter', 'naturapets'),
		'add_new_item' => __('Ajouter un médaillon public', 'naturapets'),
		'edit_item' => __('Modifier le médaillon public', 'naturapets'),
		'new_item' => __('Nouveau médaillon public', 'naturapets'),
		'view_item' => __('Voir le médaillon public', 'naturapets'),
		'search_items' => __('Rechercher', 'naturapets'),
		'not_found' => __('Aucun médaillon public trouvé', 'naturapets'),
		'not_found_in_trash' => __('Aucun médaillon public dans la corbeille', 'naturapets'),
	);

	register_post_type('medaillon_public', array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'query_var' => true,
		'rewrite' => array('slug' => 'medaillons'),
		'capability_type' => 'post',
		'has_archive' => false,
		'hierarchical' => false,
		'menu_position' => 25,
		'menu_icon' => 'dashicons-visibility',
		'supports' => array('title'),
		'show_in_rest' => false,
	));
}
add_action('init', 'naturapets_register_medaillon_public_cpt');

/**
 * Colonnes personnalisées pour la liste des médaillons publics.
 */
function naturapets_medaillon_public_admin_columns($columns)
{
	$new_columns = array();

	foreach ($columns as $key => $value) {
		if ($key === 'title') {
			$new_columns['medaillon_id'] = 'ID';
			$new_columns[$key] = $value;
			$new_columns['medaillon_qrcode'] = 'QR Code';
		} else {
			$new_columns[$key] = $value;
		}
	}

	return $new_columns;
}
add_filter('manage_medaillon_public_posts_columns', 'naturapets_medaillon_public_admin_columns');

/**
 * Réduire la largeur de la colonne ID dans la liste des médaillons publics.
 */
function naturapets_medaillon_public_admin_column_styles()
{
	$screen = get_current_screen();
	if (!$screen || $screen->id !== 'edit-medaillon_public') {
		return;
	}
	echo '<style>.column-medaillon_id { width: 5em; }</style>';
}
add_action('admin_head-edit.php', 'naturapets_medaillon_public_admin_column_styles');

/**
 * Contenu des colonnes médaillons publics.
 */
function naturapets_medaillon_public_admin_columns_content($column, $post_id)
{
	if ($column === 'medaillon_id') {
		echo '<code>' . (int) $post_id . '</code>';
		return;
	}

	if ($column === 'medaillon_qrcode') {
		$animal_id = get_post_meta($post_id, '_animal_id', true);
		if (!$animal_id) {
			echo '<em>—</em>';
			return;
		}

		$animal_url = naturapets_get_animal_url($animal_id);
		$qr_url = naturapets_get_qrcode_url($animal_url, 60);
		$download_url = admin_url('admin.php?naturapets_qr_download=1&medaillon_public_id=' . $post_id . '&nonce=' . wp_create_nonce('download_qr_mp_' . $post_id));

		echo '<div style="display: flex; align-items: center; gap: 8px;">';
		echo '<img src="' . esc_url($qr_url) . '" width="60" height="60" alt="QR Code" />';
		echo '<a href="' . esc_url($download_url) . '" class="button button-small" title="' . esc_attr__('Télécharger le QR code', 'naturapets') . '">';
		echo '↓ ' . __('Télécharger', 'naturapets');
		echo '</a>';
		echo '</div>';
	}
}
add_action('manage_medaillon_public_posts_custom_column', 'naturapets_medaillon_public_admin_columns_content', 10, 2);

/**
 * Metabox : toutes les infos du médaillon sur la fiche medaillon_public.
 */
function naturapets_add_medaillon_public_info_metabox()
{
	add_meta_box(
		'naturapets_medaillon_public_info',
		__('Informations du médaillon', 'naturapets'),
		'naturapets_medaillon_public_info_metabox_content',
		'medaillon_public',
		'normal',
		'high'
	);
}
add_action('add_meta_boxes', 'naturapets_add_medaillon_public_info_metabox');

/**
 * Contenu de la metabox infos médaillon public.
 */
function naturapets_medaillon_public_info_metabox_content($post)
{
	$animal_id = get_post_meta($post->ID, '_animal_id', true);

	if (!$animal_id) {
		echo '<p><em>' . __('Aucun animal lié à ce médaillon public.', 'naturapets') . '</em></p>';
		return;
	}

	$animal = get_post($animal_id);
	if (!$animal || $animal->post_type !== 'animal') {
		echo '<p><em>' . __('Animal introuvable.', 'naturapets') . '</em></p>';
		return;
	}

	$nom = get_field('nom', $animal_id);
	$type_animal = get_field('type_animal', $animal_id);
	$race = get_field('race', $animal_id);
	$age = get_field('age', $animal_id);
	$photo = get_field('photo_de_lanimal', $animal_id);
	$informations = get_field('informations_importantes', $animal_id);
	$allergies = get_field('allergies', $animal_id);
	$telephone = get_field('telephone', $animal_id);
	$adresse = get_field('adresse', $animal_id);
	$customer_id = get_post_meta($animal_id, '_customer_id', true);
	$product_id = get_post_meta($animal_id, '_product_id', true);
	$customer = $customer_id ? get_user_by('id', $customer_id) : null;
	$product = $product_id ? wc_get_product($product_id) : null;
	$photo_url = function_exists('naturapets_get_acf_image_url') ? naturapets_get_acf_image_url($photo, 'medium') : '';
	$display_id = function_exists('naturapets_get_animal_display_id') ? naturapets_get_animal_display_id($animal_id) : $animal_id;
	$animal_url = naturapets_get_animal_url($animal_id);
	$download_qr_url = admin_url('admin.php?naturapets_qr_download=1&medaillon_public_id=' . $post->ID . '&nonce=' . wp_create_nonce('download_qr_mp_' . $post->ID));
?>
	<div class="naturapets-medaillon-public-info" style="max-width: 600px;">
		<table class="form-table" role="presentation">
			<tr>
				<th>ID affichage</th>
				<td><code><?php echo esc_html($display_id); ?></code></td>
			</tr>
			<tr>
				<th>Nom</th>
				<td><?php echo $nom ? esc_html($nom) : '<em>—</em>'; ?></td>
			</tr>
			<tr>
				<th>Type</th>
				<td><?php echo $type_animal ? esc_html(naturapets_get_type_animal_label($type_animal)) : '<em>—</em>'; ?></td>
			</tr>
			<tr>
				<th>Race</th>
				<td><?php echo $race ? esc_html($race) : '<em>—</em>'; ?></td>
			</tr>
			<tr>
				<th>Âge</th>
				<td><?php echo $age ? esc_html($age) : '<em>—</em>'; ?></td>
			</tr>
			<?php if ($photo_url): ?>
				<tr>
					<th>Photo</th>
					<td><img src="<?php echo esc_url($photo_url); ?>" alt=""
							style="max-width: 150px; height: auto; border-radius: 50%; border: 2px solid #ddd;" /></td>
				</tr>
			<?php endif; ?>
			<tr>
				<th>Informations importantes</th>
				<td><?php echo $informations ? nl2br(esc_html($informations)) : '<em>—</em>'; ?></td>
			</tr>
			<tr>
				<th>Allergies</th>
				<td><?php echo $allergies ? nl2br(esc_html($allergies)) : '<em>—</em>'; ?></td>
			</tr>
			<tr>
				<th>Téléphone</th>
				<td><?php echo $telephone ? '<a href="tel:' . esc_attr($telephone) . '">' . esc_html($telephone) . '</a>' : '<em>—</em>'; ?>
				</td>
			</tr>
			<tr>
				<th>Adresse</th>
				<td>
					<?php
					if ($adresse && ($adresse['rue'] || $adresse['code_postal'] || $adresse['ville'])) {
						if (!empty($adresse['rue']))
							echo esc_html($adresse['rue']) . '<br>';
						if (!empty($adresse['code_postal']) || !empty($adresse['ville'])) {
							echo esc_html(trim(($adresse['code_postal'] ?? '') . ' ' . ($adresse['ville'] ?? '')));
						}
					} else {
						echo '<em>—</em>';
					}
					?>
				</td>
			</tr>
			<tr>
				<th>Propriétaire</th>
				<td>
					<?php
					if ($customer) {
						echo '<a href="' . admin_url('user-edit.php?user_id=' . $customer_id) . '">' . esc_html($customer->display_name) . '</a>';
						echo ' <small>(' . esc_html($customer->user_email) . ')</small>';
					} else {
						echo '<em>—</em>';
					}
					?>
				</td>
			</tr>
			<tr>
				<th>Produit lié</th>
				<td>
					<?php
					if ($product) {
						echo '<a href="' . get_edit_post_link($product_id) . '">' . esc_html($product->get_name()) . '</a>';
					} else {
						echo '<em>—</em>';
					}
					?>
				</td>
			</tr>
			<tr>
				<th>URL publique</th>
				<td><a href="<?php echo esc_url($animal_url); ?>" target="_blank"
						rel="noopener"><?php echo esc_html($animal_url); ?></a></td>
			</tr>
			<tr>
				<th>Médaillon (fiche animal)</th>
				<td><a href="<?php echo get_edit_post_link($animal_id); ?>" class="button button-small">Modifier le
						médaillon</a></td>
			</tr>
		</table>

		<div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 8px; text-align: center;">
			<h4 style="margin-top: 0;">QR Code</h4>
			<?php echo naturapets_generate_qrcode($animal_url, 150, false); ?>
			<p style="margin-top: 10px;">
				<a href="<?php echo esc_url($download_qr_url); ?>"
					class="button"><?php _e('Télécharger le QR code', 'naturapets'); ?></a>
			</p>
		</div>
	</div>
<?php
}

/**
 * Flush des règles de réécriture et création des posts medaillon_public pour les animaux existants.
 */
function naturapets_flush_rewrite_rules_on_activation()
{
	naturapets_register_medaillon_public_cpt();
	flush_rewrite_rules();

	// Créer les posts medaillon_public pour les animaux existants
	$animals = get_posts(array(
		'post_type' => 'animal',
		'posts_per_page' => -1,
		'post_status' => 'any',
	));

	foreach ($animals as $animal) {
		naturapets_get_or_create_medaillon_public_post($animal->ID);
	}
}
add_action('after_switch_theme', 'naturapets_flush_rewrite_rules_on_activation');

/**
 * Générer le slug pour un médaillon public (ID affichage : PSI-YYYY-NNNNNN).
 */
function naturapets_get_medaillon_public_slug($animal_id)
{
	$display_id = naturapets_get_animal_display_id($animal_id);

	return sanitize_title($display_id);
}

/**
 * Récupérer ou créer le post medaillon_public pour un animal.
 */
function naturapets_get_or_create_medaillon_public_post($animal_id)
{
	$animal = get_post($animal_id);
	if (!$animal || $animal->post_type !== 'animal') {
		return;
	}

	$existing = get_posts(array(
		'post_type' => 'medaillon_public',
		'meta_key' => '_animal_id',
		'meta_value' => $animal_id,
		'posts_per_page' => 1,
		'post_status' => 'any',
	));

	if (!empty($existing)) {
		$post_id = (int) $existing[0]->ID;
		$new_slug = naturapets_get_medaillon_public_slug($animal_id);

		// Mettre à jour le slug si différent (migration des anciens médaillons)
		if ($existing[0]->post_name !== $new_slug) {
			wp_update_post(array(
				'ID' => $post_id,
				'post_name' => $new_slug,
			));
		}

		return $post_id;
	}

	$slug = naturapets_get_medaillon_public_slug($animal_id);
	$nom = get_field('nom', $animal_id);
	$title = $nom ? sprintf('%s – Médaillon', $nom) : sprintf('Médaillon #%d', $animal_id);

	$post_id = wp_insert_post(array(
		'post_type' => 'medaillon_public',
		'post_title' => $title,
		'post_name' => $slug,
		'post_status' => 'publish',
		'post_author' => 1,
	));

	if ($post_id && !is_wp_error($post_id)) {
		update_post_meta($post_id, '_animal_id', $animal_id);
		return $post_id;
	}

	return null;
}

/**
 * Synchroniser le titre du post medaillon_public avec le nom de l'animal.
 */
function naturapets_sync_medaillon_public_title($animal_id)
{
	$posts = get_posts(array(
		'post_type' => 'medaillon_public',
		'meta_key' => '_animal_id',
		'meta_value' => $animal_id,
		'posts_per_page' => 1,
		'post_status' => 'any',
	));

	if (empty($posts)) {
		return;
	}

	$nom = get_field('nom', $animal_id);
	$title = $nom ? sprintf('%s – Médaillon', $nom) : sprintf('Médaillon #%d', $animal_id);

	wp_update_post(array(
		'ID' => $posts[0]->ID,
		'post_title' => $title,
	));
}

/**
 * Synchroniser le titre medaillon_public quand l'animal est modifié depuis l'admin.
 */
function naturapets_sync_medaillon_public_on_animal_save($post_id)
{
	if (get_post_type($post_id) !== 'animal') {
		return;
	}

	naturapets_sync_medaillon_public_title($post_id);
}
add_action('save_post', 'naturapets_sync_medaillon_public_on_animal_save');

// Ajouter au menu et personnaliser les libellés
function naturapets_add_animals_menu_item($items)
{
	$new_items = array();
	$labels = array(
		'dashboard' => 'Profil',
		'orders' => 'Commandes',
		'mes-animaux' => 'Mes médaillons',
		'edit-address' => 'Adresses',
		'edit-account' => 'Sécurité',
		'payment-methods' => 'Moyens de paiement',
		'customer-logout' => 'Déconnexion',
	);

	foreach ($items as $key => $value) {
		// Ne pas afficher "Téléchargements" et "Sécurité" dans le menu Mon compte
		if ($key === 'downloads' || $key === 'edit-account') {
			continue;
		}
		// Ne pas afficher "Déconnexion" dans les onglets (affiché dans le header)
		if ($key === 'customer-logout') {
			continue;
		}
		$new_items[$key] = isset($labels[$key]) ? $labels[$key] : $value;

		// Insérer "Mes médaillons" après "Commandes"
		if ($key === 'orders') {
			$new_items['mes-animaux'] = 'Mes médaillons';
		}
	}

	return $new_items;
}
add_filter('woocommerce_account_menu_items', 'naturapets_add_animals_menu_item');

/**
 * Ajoute la classe account-navigation au wrapper .woocommerce du shortcode Mon compte.
 */
function naturapets_myaccount_wrapper_class($content)
{
	if (!function_exists('is_account_page') || !is_account_page()) {
		return $content;
	}
	// Un seul remplacement : le div.woocommerce du shortcode [woocommerce_my_account]
	return preg_replace('/<div class="woocommerce">/', '<div class="woocommerce account-navigation">', $content, 1);
}
add_filter('the_content', 'naturapets_myaccount_wrapper_class', 20);

// Contenu de la page
function naturapets_animals_endpoint_content()
{
	$current_user_id = get_current_user_id();

	// Vérifie si on affiche un animal spécifique (pour édition)
	$animal_id = get_query_var('mes-animaux');

	if ($animal_id && is_numeric($animal_id)) {
		naturapets_display_animal_form($animal_id, $current_user_id);
	} else {
		naturapets_display_animals_list($current_user_id);
	}
}
add_action('woocommerce_account_mes-animaux_endpoint', 'naturapets_animals_endpoint_content');

/**
 * Générer l'ID d'affichage pour un animal (format PSI-YYYY-NNNNNN).
 */
function naturapets_get_animal_display_id($animal_id)
{
	$post = get_post($animal_id);
	if (!$post) {
		return 'PSI-0000-000000';
	}
	$year = get_the_date('Y', $animal_id);
	$padded_id = str_pad((string) $animal_id, 6, '0', STR_PAD_LEFT);
	return 'PSI-' . $year . '-' . $padded_id;
}

function naturapets_display_animals_list($customer_id)
{
	$animals = get_posts(array(
		'post_type' => 'animal',
		'meta_key' => '_customer_id',
		'meta_value' => $customer_id,
		'posts_per_page' => -1,
		'orderby' => 'date',
		'order' => 'DESC',
	));

	echo '<div class="myaccount-animals">';
	echo '<div class="myaccount-animals__header">';
	echo '<h2 class="myaccount-animals__title">Mes médaillons</h2>';
	echo '<p class="myaccount-animals__subtitle">Gérez vos médaillons</p>';
	echo '<a href="' . esc_url(wc_get_page_permalink('shop')) . '" class="myaccount-animals__add-btn">+ Ajouter un médaillon</a>';
	echo '</div>';

	if (empty($animals)) {
		echo '<p class="myaccount-animals__empty">Vous n\'avez pas encore de médaillons enregistrés. Ils apparaîtront ici après votre première commande.</p>';
		echo '</div>';
		return;
	}

	echo '<div class="myaccount-animals__grid">';

	foreach ($animals as $animal) {
		$nom = get_field('nom', $animal->ID);
		$type = get_field('type_animal', $animal->ID);
		$race = get_field('race', $animal->ID);
		$age = get_field('age', $animal->ID);
		$product_id = get_post_meta($animal->ID, '_product_id', true);
		$product = wc_get_product($product_id);

		$display_name = $nom ? esc_html($nom) : 'Non renseigné';
		$display_type = $type ? esc_html($type) : ($product ? esc_html($product->get_name()) : 'Non renseigné');
		$display_race = $race ? esc_html($race) : 'Non renseigné';
		$display_age = $age ? esc_html($age) : 'Non renseigné';
		$animal_url = naturapets_get_animal_url($animal->ID);
		$qr_url = naturapets_get_qrcode_url($animal_url, 200);
		$display_id = naturapets_get_animal_display_id($animal->ID);
		$edit_url = wc_get_account_endpoint_url('mes-animaux') . $animal->ID;

		echo '<article class="animal-card">';
		echo '<div class="animal-card__header">';
		echo '<h3 class="animal-card__name">' . $display_name . '</h3>';
		echo '<span class="animal-card__badge animal-card__badge--active">Actif</span>';
		echo '</div>';
		echo '<p class="animal-card__id">ID: ' . esc_html($display_id) . '</p>';
		echo '<dl class="animal-card__details">';
		echo '<div class="animal-card__row"><dt>Type:</dt><dd>' . $display_type . '</dd></div>';
		echo '<div class="animal-card__row"><dt>Race:</dt><dd>' . $display_race . '</dd></div>';
		echo '<div class="animal-card__row"><dt>Âge:</dt><dd>' . $display_age . '</dd></div>';
		echo '<div class="animal-card__row"><dt>Dernier scan:</dt><dd>Jamais scanné</dd></div>';
		echo '</dl>';
		echo '<div class="animal-card__actions">';
		echo '<a href="' . esc_url($edit_url) . '" class="animal-card__btn animal-card__btn--secondary">';
		echo '<svg class="animal-card__icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
		echo ' Modifier médaillon</a>';
		echo '<button type="button" class="animal-card__btn animal-card__btn--secondary animal-card__qr-trigger" data-qr-url="' . esc_attr($qr_url) . '" data-animal-name="' . esc_attr($display_name) . '">';
		echo '<svg class="animal-card__icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>';
		echo ' Voir le QR</button>';
		echo '</div>';
		echo '</article>';
	}

	echo '</div>';
	echo '</div>';

	// Modal QR Code (affiché uniquement s'il y a des médaillons)
	echo '<div id="animal-qr-modal" class="animal-qr-modal" aria-hidden="true" role="dialog" aria-labelledby="animal-qr-modal-title">';
	echo '<div class="animal-qr-modal__backdrop"></div>';
	echo '<div class="animal-qr-modal__content">';
	echo '<button type="button" class="animal-qr-modal__close" aria-label="Fermer">&times;</button>';
	echo '<h2 id="animal-qr-modal-title" class="animal-qr-modal__title">QR Code</h2>';
	echo '<div class="animal-qr-modal__body"><img src="" alt="QR Code" class="animal-qr-modal__img" /></div>';
	echo '</div>';
	echo '</div>';
}

function naturapets_display_animal_form($animal_id, $customer_id)
{
	$animal = get_post($animal_id);

	// Vérifier que l'animal appartient bien au client
	$animal_customer = get_post_meta($animal_id, '_customer_id', true);
	if (!$animal || $animal->post_type !== 'animal' || $animal_customer != $customer_id) {
		echo '<p>Médaillon non trouvé.</p>';
		echo '<a href="' . esc_url(wc_get_account_endpoint_url('mes-animaux')) . '">&larr; Retour à la liste</a>';
		return;
	}

	// Traitement du formulaire avec champs ACF
	if (isset($_POST['naturapets_save_animal']) && wp_verify_nonce($_POST['_wpnonce'], 'save_animal_' . $animal_id)) {
		// Champs ACF
		update_field('nom', sanitize_text_field($_POST['nom']), $animal_id);

		$allowed_types = array_keys(naturapets_get_type_animal_choices());
		$type_post = isset($_POST['type_animal']) ? sanitize_text_field(wp_unslash($_POST['type_animal'])) : '';
		$prev_type = get_field('type_animal', $animal_id);
		if ($type_post === '') {
			update_field('type_animal', '', $animal_id);
		} elseif (in_array($type_post, $allowed_types, true)) {
			update_field('type_animal', $type_post, $animal_id);
		} elseif ($prev_type !== '' && $type_post === $prev_type && !in_array((string) $prev_type, $allowed_types, true)) {
			update_field('type_animal', $type_post, $animal_id);
		}
		update_field('race', sanitize_text_field($_POST['race'] ?? ''), $animal_id);
		update_field('age', sanitize_text_field($_POST['age'] ?? ''), $animal_id);

		update_field('informations_importantes', sanitize_textarea_field($_POST['informations_importantes']), $animal_id);
		update_field('allergies', sanitize_textarea_field($_POST['allergies']), $animal_id);
		update_field('telephone', sanitize_text_field($_POST['telephone']), $animal_id);

		// Groupe Adresse
		$adresse = array(
			'rue' => sanitize_text_field($_POST['adresse_rue']),
			'code_postal' => sanitize_text_field($_POST['adresse_code_postal']),
			'ville' => sanitize_text_field($_POST['adresse_ville']),
		);
		update_field('adresse', $adresse, $animal_id);

		// Gestion de l'upload de photo
		if (!empty($_FILES['photo_de_lanimal']['name'])) {
			require_once(ABSPATH . 'wp-admin/includes/image.php');
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			require_once(ABSPATH . 'wp-admin/includes/media.php');

			$attachment_id = media_handle_upload('photo_de_lanimal', $animal_id);

			if (!is_wp_error($attachment_id)) {
				update_field('photo_de_lanimal', $attachment_id, $animal_id);
			}
		}

		// Mettre à jour le titre du post avec le nom de l'animal
		if (!empty($_POST['nom'])) {
			wp_update_post(array(
				'ID' => $animal_id,
				'post_title' => sanitize_text_field($_POST['nom']),
			));
		}

		// Synchroniser le titre du post medaillon_public lié
		naturapets_sync_medaillon_public_title($animal_id);

		wc_add_notice('Les informations de votre médaillon ont été enregistrées.', 'success');
	}

	// Récupérer les données ACF
	$nom = get_field('nom', $animal_id);
	$type_animal = get_field('type_animal', $animal_id);
	$race = get_field('race', $animal_id);
	$age = get_field('age', $animal_id);
	$photo = get_field('photo_de_lanimal', $animal_id);
	$informations = get_field('informations_importantes', $animal_id);
	$allergies = get_field('allergies', $animal_id);
	$telephone = get_field('telephone', $animal_id);
	$adresse = get_field('adresse', $animal_id);

	$product_id = get_post_meta($animal_id, '_product_id', true);
	$product = wc_get_product($product_id);

?>
	<p><a href="<?php echo esc_url(wc_get_account_endpoint_url('mes-animaux')); ?>">&larr; Retour à la liste</a></p>

	<h2>Informations du médaillon</h2>



	<form method="post" enctype="multipart/form-data"
		class="woocommerce-EditAccountForm edit-account edit-account-form-medaillon">
		<?php wp_nonce_field('save_animal_' . $animal_id); ?>
		<div class="row-animal-photo">
			<?php
			$photo_url_medium = naturapets_get_acf_image_url($photo, 'medium');
			if ($photo_url_medium): ?>
				<div style="margin-bottom: 10px;">
					<img src="<?php echo esc_url($photo_url_medium); ?>" alt="" />
				</div>
			<?php endif; ?>

			<input type="file" name="photo_de_lanimal" id="photo_de_lanimal" accept="image/*" />
			<small style="display: block; margin-top: 5px; color: #666;">Formats acceptés : JPG, PNG, GIF. Max 2 Mo.</small>
		</div>
		<div class="qrcode-container">
			<?php if ($product): ?>
				<p><strong>Produit associé :</strong> <?php echo esc_html($product->get_name()); ?></p>
			<?php endif; ?>

			<?php
			wc_print_notices();

			// Afficher le QR code
			naturapets_add_qrcode_to_animal_form($animal_id);
			?>
		</div>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="nom">Nom de l'animal <span class="required">*</span></label>
			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="nom" id="nom"
				value="<?php echo esc_attr($nom); ?>" required />
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="type_animal">Type</label>
			<select name="type_animal" id="type_animal" class="woocommerce-Input woocommerce-Input--select input-select">
				<option value=""><?php esc_html_e('— Sélectionner —', 'naturapets'); ?></option>
				<?php
				$type_choices = naturapets_get_type_animal_choices();
				if ($type_animal && !isset($type_choices[$type_animal])) :
				?>
					<option value="<?php echo esc_attr($type_animal); ?>" selected><?php echo esc_html($type_animal); ?></option>
				<?php endif; ?>
				<?php foreach ($type_choices as $val => $label) : ?>
					<option value="<?php echo esc_attr($val); ?>" <?php selected($type_animal, $val); ?>>
						<?php echo esc_html($label); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="race">Race</label>
			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="race" id="race"
				value="<?php echo esc_attr($race); ?>" />
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="age">Âge</label>
			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="age" id="age"
				value="<?php echo esc_attr($age); ?>" placeholder="<?php echo esc_attr__('ex. 3 ans', 'naturapets'); ?>" />
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="informations_importantes">Informations importantes</label>
			<textarea class="woocommerce-Input woocommerce-Input--textarea input-text" name="informations_importantes"
				id="informations_importantes" rows="4"><?php echo esc_textarea((string) ($informations ?? '')); ?></textarea>
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="allergies">Allergies</label>
			<textarea class="woocommerce-Input woocommerce-Input--textarea input-text" name="allergies" id="allergies"
				rows="3"><?php echo esc_textarea((string) ($allergies ?? '')); ?></textarea>
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="telephone">Téléphone</label>
			<input type="tel" class="woocommerce-Input woocommerce-Input--text input-text" name="telephone" id="telephone"
				value="<?php echo esc_attr($telephone); ?>" />
		</p>

		<fieldset style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
			<legend style="font-weight: bold; padding: 0 10px;">Adresse</legend>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="adresse_rue">Rue</label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="adresse_rue"
					id="adresse_rue" value="<?php echo esc_attr($adresse['rue'] ?? ''); ?>" />
			</p>

			<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
				<label for="adresse_code_postal">Code postal</label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="adresse_code_postal"
					id="adresse_code_postal" value="<?php echo esc_attr($adresse['code_postal'] ?? ''); ?>" />
			</p>

			<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
				<label for="adresse_ville">Ville</label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="adresse_ville"
					id="adresse_ville" value="<?php echo esc_attr($adresse['ville'] ?? ''); ?>" />
			</p>
		</fieldset>

		<div class="clear"></div>

		<p>
			<button type="submit" class="woocommerce-Button button" name="naturapets_save_animal" value="1">
				Enregistrer les modifications
			</button>
		</p>
	</form>
<?php
}

/**
 * Créer automatiquement un CPT Animal à chaque commande.
 */
function naturapets_create_animal_on_order($order_id)
{
	$order = wc_get_order($order_id);

	if (!$order) {
		return;
	}

	// Vérifier si des animaux ont déjà été créés pour cette commande
	$existing = get_posts(array(
		'post_type' => 'animal',
		'meta_key' => '_order_id',
		'meta_value' => $order_id,
		'posts_per_page' => 1,
	));

	if (!empty($existing)) {
		return;
	}

	$customer_id = $order->get_customer_id();

	if (!$customer_id) {
		return;
	}

	foreach ($order->get_items() as $item_id => $item) {
		$product_id = $item->get_product_id();
		$product = wc_get_product($product_id);
		$quantity = $item->get_quantity();

		for ($i = 0; $i < $quantity; $i++) {
			$animal_id = wp_insert_post(array(
				'post_type' => 'animal',
				'post_title' => sprintf('Animal - %s #%d', $product->get_name(), $i + 1),
				'post_status' => 'publish',
				'post_author' => $customer_id,
			));

			if ($animal_id && !is_wp_error($animal_id)) {
				update_post_meta($animal_id, '_customer_id', $customer_id);
				update_post_meta($animal_id, '_product_id', $product_id);
				update_post_meta($animal_id, '_order_id', $order_id);
				update_post_meta($animal_id, '_order_item_id', $item_id);
				naturapets_get_or_create_medaillon_public_post($animal_id);
			}
		}
	}
}
add_action('woocommerce_order_status_processing', 'naturapets_create_animal_on_order');
add_action('woocommerce_order_status_completed', 'naturapets_create_animal_on_order');

/**
 * ==========================================================================
 * ADMIN : Affichage des animaux dans le backoffice
 * ==========================================================================
 */

/**
 * Ajouter une metabox sur les commandes WooCommerce pour voir les animaux liés.
 */
function naturapets_add_order_animals_metabox()
{
	$screen = class_exists('\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController')
		&& wc_get_container()->get(\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled()
		? wc_get_page_screen_id('shop-order')
		: 'shop_order';

	add_meta_box(
		'naturapets_order_animals',
		'Médaillons liés à cette commande',
		'naturapets_order_animals_metabox_content',
		$screen,
		'normal',
		'default'
	);
}
add_action('add_meta_boxes', 'naturapets_add_order_animals_metabox');

/**
 * Télécharger le QR code d'un animal ou d'un médaillon public (proxy pour forcer le téléchargement).
 */
function naturapets_admin_download_qrcode()
{
	if (!isset($_GET['naturapets_qr_download']) || !isset($_GET['nonce'])) {
		return;
	}

	$animal_id = null;

	if (isset($_GET['medaillon_public_id'])) {
		$public_post_id = absint($_GET['medaillon_public_id']);
		if (!$public_post_id || !wp_verify_nonce(sanitize_text_field($_GET['nonce']), 'download_qr_mp_' . $public_post_id)) {
			wp_die(__('Lien invalide ou expiré.', 'naturapets'));
		}
		$animal_id = get_post_meta($public_post_id, '_animal_id', true);
	} elseif (isset($_GET['animal_id'])) {
		$animal_id = absint($_GET['animal_id']);
		if (!$animal_id || !wp_verify_nonce(sanitize_text_field($_GET['nonce']), 'download_qr_' . $animal_id)) {
			wp_die(__('Lien invalide ou expiré.', 'naturapets'));
		}
	}

	if (!$animal_id) {
		wp_die(__('Médaillon introuvable.', 'naturapets'));
	}

	if (!current_user_can('edit_others_posts')) {
		wp_die(__('Accès non autorisé.', 'naturapets'));
	}

	$animal = get_post($animal_id);
	if (!$animal || $animal->post_type !== 'animal') {
		wp_die(__('Médaillon introuvable.', 'naturapets'));
	}

	$animal_url = naturapets_get_animal_url($animal_id);
	$qr_url = naturapets_get_qrcode_download_url($animal_url, 300);

	$response = wp_remote_get($qr_url, array('timeout' => 15));
	if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
		wp_die(__('Impossible de générer le QR code.', 'naturapets'));
	}

	$body = wp_remote_retrieve_body($response);
	$nom = get_field('nom', $animal_id);
	$filename = 'qrcode-animal-' . $animal_id . '-' . ($nom ? sanitize_file_name($nom) : 'animal') . '.png';

	header('Content-Type: image/png');
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	header('Content-Length: ' . strlen($body));
	echo $body;
	exit;
}
add_action('admin_init', 'naturapets_admin_download_qrcode');

/**
 * Contenu de la metabox des animaux sur une commande.
 */
function naturapets_order_animals_metabox_content($post_or_order)
{
	$order = $post_or_order instanceof WC_Order ? $post_or_order : wc_get_order($post_or_order->ID);
	$order_id = $order ? $order->get_id() : ($post_or_order->ID ?? 0);

	$animals = get_posts(array(
		'post_type' => 'animal',
		'meta_key' => '_order_id',
		'meta_value' => $order_id,
		'posts_per_page' => -1,
		'post_status' => 'any',
	));

	if (empty($animals)) {
		echo '<p>Aucun médaillon associé à cette commande.</p>';
		echo '<p><em>Les médaillons sont créés automatiquement lorsque la commande passe en statut "En cours" ou "Terminée".</em></p>';
		return;
	}

	$order_validated = $order && in_array($order->get_status(), array('processing', 'completed'), true);
	$show_qrcode = $order_validated;

	echo '<table class="widefat striped">';
	echo '<thead><tr>';
	echo '<th>Photo</th>';
	echo '<th>Nom</th>';
	echo '<th>Téléphone</th>';
	echo '<th>Allergies</th>';
	echo '<th>Produit</th>';
	if ($show_qrcode) {
		echo '<th>QR Code</th>';
	}
	echo '<th>Actions</th>';
	echo '</tr></thead>';
	echo '<tbody>';

	foreach ($animals as $animal) {
		$nom = get_field('nom', $animal->ID);
		$photo = get_field('photo-animal', $animal->ID);
		$telephone = get_field('telephone', $animal->ID);
		$allergies = get_field('allergies', $animal->ID);
		$product_id = get_post_meta($animal->ID, '_product_id', true);
		$product = wc_get_product($product_id);

		$photo_url = naturapets_get_acf_image_url($photo, 'thumbnail');

		echo '<tr>';
		echo '<td style="width: 50px;">';
		if ($photo_url) {
			echo '<img src="' . esc_url($photo_url) . '" alt="" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;" />';
		} else {
			echo '<span style="display: inline-block; width: 40px; height: 40px; background: #ddd; border-radius: 50%;"></span>';
		}
		echo '</td>';
		echo '<td><strong>' . ($nom ? esc_html($nom) : '<em>Non renseigné</em>') . '</strong></td>';
		echo '<td>' . ($telephone ? esc_html($telephone) : '-') . '</td>';
		echo '<td>' . ($allergies ? esc_html(wp_trim_words($allergies, 5, '...')) : '-') . '</td>';
		echo '<td>' . ($product ? esc_html($product->get_name()) : '-') . '</td>';

		if ($show_qrcode) {
			$animal_url = naturapets_get_animal_url($animal->ID);
			$qr_url = naturapets_get_qrcode_url($animal_url, 80);
			$download_url = add_query_arg(array(
				'naturapets_qr_download' => '1',
				'animal_id' => $animal->ID,
				'nonce' => wp_create_nonce('download_qr_' . $animal->ID),
			), admin_url('index.php'));
			echo '<td style="vertical-align: middle;">';
			echo '<div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">';
			echo '<img src="' . esc_url($qr_url) . '" width="60" height="60" alt="QR Code" style="flex-shrink: 0;" />';
			echo '<a href="' . esc_url($download_url) . '" class="button button-small">Télécharger</a>';
			echo '</div>';
			echo '</td>';
		}

		echo '<td><a href="' . get_edit_post_link($animal->ID) . '" class="button button-small">Modifier médaillon</a></td>';
		echo '</tr>';
	}

	echo '</tbody></table>';
}

/**
 * Ajouter des colonnes personnalisées dans la liste des animaux.
 */
function naturapets_animal_admin_columns($columns)
{
	$new_columns = array();

	foreach ($columns as $key => $value) {
		$new_columns[$key] = $value;

		if ($key === 'title') {
			$new_columns['customer'] = 'Client';
			$new_columns['product'] = 'Produit';
			$new_columns['order'] = 'Commande';
			$new_columns['animal_info'] = 'Infos animal';
		}
	}

	return $new_columns;
}
add_filter('manage_animal_posts_columns', 'naturapets_animal_admin_columns');

/**
 * Remplir les colonnes personnalisées.
 */
function naturapets_animal_admin_columns_content($column, $post_id)
{
	switch ($column) {
		case 'customer':
			$customer_id = get_post_meta($post_id, '_customer_id', true);
			if ($customer_id) {
				$customer = get_user_by('id', $customer_id);
				if ($customer) {
					echo '<a href="' . admin_url('user-edit.php?user_id=' . $customer_id) . '">';
					echo esc_html($customer->display_name);
					echo '</a>';
					echo '<br><small>' . esc_html($customer->user_email) . '</small>';
				} else {
					echo '<em>Client supprimé</em>';
				}
			} else {
				echo '-';
			}
			break;

		case 'product':
			$product_id = get_post_meta($post_id, '_product_id', true);
			if ($product_id) {
				$product = wc_get_product($product_id);
				if ($product) {
					echo '<a href="' . get_edit_post_link($product_id) . '">';
					echo esc_html($product->get_name());
					echo '</a>';
				} else {
					echo '<em>Produit supprimé</em>';
				}
			} else {
				echo '-';
			}
			break;

		case 'order':
			$order_id = get_post_meta($post_id, '_order_id', true);
			if ($order_id) {
				$order = wc_get_order($order_id);
				if ($order) {
					echo '<a href="' . $order->get_edit_order_url() . '">';
					echo '#' . $order->get_order_number();
					echo '</a>';
				} else {
					echo '<em>Commande supprimée</em>';
				}
			} else {
				echo '-';
			}
			break;

		case 'animal_info':
			// Utiliser les champs ACF
			$nom = get_field('nom', $post_id);
			$telephone = get_field('telephone', $post_id);
			$allergies = get_field('allergies', $post_id);

			if ($nom || $telephone || $allergies) {
				$infos = array();
				if ($telephone)
					$infos[] = $telephone;
				if ($allergies)
					$infos[] = 'Allergies: ' . wp_trim_words($allergies, 3, '...');
				echo implode(' • ', $infos);

				if (!$nom) {
					echo '<br><span style="color: #d63638;"><em>Nom non renseigné</em></span>';
				}
			} else {
				echo '<span style="color: #d63638;"><em>Non renseigné</em></span>';
			}
			break;
	}
}
add_action('manage_animal_posts_custom_column', 'naturapets_animal_admin_columns_content', 10, 2);

/**
 * Rendre les colonnes triables.
 */
function naturapets_animal_sortable_columns($columns)
{
	$columns['customer'] = 'customer';
	$columns['order'] = 'order';
	return $columns;
}
add_filter('manage_edit-animal_sortable_columns', 'naturapets_animal_sortable_columns');

/**
 * Récupérer le terme de catégorie "Médaillon" (slug ou nom).
 */
function naturapets_get_medaillon_category()
{
	$term = get_term_by('slug', 'medaillon', 'product_cat');
	if (!$term) {
		$term = get_term_by('slug', 'médaillon', 'product_cat');
	}
	if (!$term) {
		$terms = get_terms(array('taxonomy' => 'product_cat', 'search' => 'Médaillon', 'hide_empty' => false));
		$term = !empty($terms) ? $terms[0] : null;
	}
	return $term;
}

/**
 * Vérifier si un produit appartient à la catégorie Médaillon.
 */
function naturapets_product_is_medaillon($product_id)
{
	$term = naturapets_get_medaillon_category();
	if (!$term) {
		return false;
	}
	return has_term($term->term_id, 'product_cat', $product_id);
}

/**
 * Vérifier si le médaillon est rempli (nom et type au minimum).
 */
function naturapets_medaillon_is_filled($animal_id)
{
	$nom = get_field('nom', $animal_id);
	$type = get_field('type_animal', $animal_id);
	return !empty(trim((string) $nom)) && !empty(trim((string) $type));
}

/**
 * Section produits Médaillon sur la page utilisateur (informations client).
 */
function naturapets_user_medaillon_products_section($user)
{
	if (!current_user_can('manage_woocommerce') && !current_user_can('edit_users')) {
		return;
	}

	$medaillon_term = naturapets_get_medaillon_category();
	if (!$medaillon_term) {
		echo '<h2>Produits Médaillon</h2>';
		echo '<p><em>La catégorie produit "Médaillon" n\'existe pas encore. Créez-la dans Produits → Catégories.</em></p>';
		return;
	}

	// Traitement de l'ajout manuel d'un produit
	if (isset($_POST['naturapets_add_medaillon_product']) && wp_verify_nonce($_POST['_wpnonce_medaillon'], 'add_medaillon_' . $user->ID)) {
		$product_id = isset($_POST['medaillon_product_id']) ? absint($_POST['medaillon_product_id']) : 0;
		if ($product_id && naturapets_product_is_medaillon($product_id)) {
			$product = wc_get_product($product_id);
			if ($product) {
				$animal_id = wp_insert_post(array(
					'post_type' => 'animal',
					'post_title' => sprintf('Animal - %s (ajout manuel)', $product->get_name()),
					'post_status' => 'publish',
					'post_author' => $user->ID,
				));
				if ($animal_id && !is_wp_error($animal_id)) {
					update_post_meta($animal_id, '_customer_id', $user->ID);
					update_post_meta($animal_id, '_product_id', $product_id);
					update_post_meta($animal_id, '_order_id', 0);
					update_post_meta($animal_id, '_order_item_id', 0);
					naturapets_get_or_create_medaillon_public_post($animal_id);
					echo '<div class="notice notice-success"><p>Produit ajouté avec succès. Un médaillon a été créé.</p></div>';
				}
			}
		}
	}

	// Récupérer les animaux du client dont le produit est dans Médaillon
	$animals = get_posts(array(
		'post_type' => 'animal',
		'meta_key' => '_customer_id',
		'meta_value' => $user->ID,
		'posts_per_page' => -1,
		'post_status' => 'any',
	));

	$medaillon_animals = array();
	foreach ($animals as $animal) {
		$product_id = get_post_meta($animal->ID, '_product_id', true);
		if ($product_id && naturapets_product_is_medaillon($product_id)) {
			$medaillon_animals[] = $animal;
		}
	}

	// Produits Médaillon disponibles pour l'ajout
	$medaillon_products = get_posts(array(
		'post_type' => 'product',
		'posts_per_page' => -1,
		'tax_query' => array(
			array(
				'taxonomy' => 'product_cat',
				'field' => 'term_id',
				'terms' => $medaillon_term->term_id,
			),
		),
		'post_status' => 'publish',
		'orderby' => 'title',
		'order' => 'ASC',
	));
?>
	<h2>Produits Médaillon</h2>

	<table class="form-table">
		<tr>
			<th>Produits Médaillon commandés</th>
			<td>
				<?php if (empty($medaillon_animals)): ?>
					<p>Aucun produit Médaillon commandé par ce client.</p>
				<?php else: ?>
					<table class="widefat striped" style="max-width: 800px;">
						<thead>
							<tr>
								<th>Produit</th>
								<th>ID unique</th>
								<th>Date commande</th>
								<th>Médaillon rempli</th>
								<th>Nom animal</th>
								<th>Type animal</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($medaillon_animals as $animal):
								$product_id = get_post_meta($animal->ID, '_product_id', true);
								$order_id = get_post_meta($animal->ID, '_order_id', true);
								$product = wc_get_product($product_id);
								$unique_id = $product_id ? naturapets_get_product_unique_id($product_id) : '—';
								$order = ($order_id && $order_id > 0) ? wc_get_order($order_id) : null;
								$order_date = $order ? $order->get_date_created()->format('d/m/Y') : 'Ajout manuel';
								$medaillon_filled = naturapets_medaillon_is_filled($animal->ID);
								$nom = get_field('nom', $animal->ID);
								$type = get_field('type_animal', $animal->ID);
							?>
								<tr>
									<td><?php echo $product ? esc_html($product->get_name()) : '—'; ?></td>
									<td><code><?php echo esc_html($unique_id); ?></code></td>
									<td><?php echo esc_html($order_date); ?></td>
									<td>
										<?php if ($medaillon_filled): ?>
											<span style="color: #00a32a;">✓ Oui</span>
										<?php else: ?>
											<span style="color: #d63638;">✗ Non</span>
										<?php endif; ?>
									</td>
									<td><?php echo $nom ? esc_html($nom) : '—'; ?></td>
									<td><?php echo $type ? esc_html($type) : '—'; ?></td>
									<td>
										<a href="<?php echo get_edit_post_link($animal->ID); ?>"
											class="button button-small">Modifier médaillon</a>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th>Ajouter un produit Médaillon</th>
			<td>
				<?php if (empty($medaillon_products)): ?>
					<p><em>Aucun produit dans la catégorie Médaillon.</em></p>
				<?php else: ?>
					<form method="post" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
						<?php wp_nonce_field('add_medaillon_' . $user->ID, '_wpnonce_medaillon'); ?>
						<input type="hidden" name="naturapets_add_medaillon_product" value="1" />
						<select name="medaillon_product_id" required>
							<option value="">— Choisir un produit —</option>
							<?php foreach ($medaillon_products as $p):
								$prod = wc_get_product($p->ID);
								if (!$prod)
									continue;
							?>
								<option value="<?php echo esc_attr($p->ID); ?>"><?php echo esc_html($prod->get_name()); ?></option>
							<?php endforeach; ?>
						</select>
						<button type="submit" class="button button-primary">Ajouter au client</button>
					</form>
					<p class="description">Crée un médaillon associé à ce produit pour le client.</p>
				<?php endif; ?>
			</td>
		</tr>
	</table>
<?php
}

add_action('show_user_profile', 'naturapets_user_medaillon_products_section');
add_action('edit_user_profile', 'naturapets_user_medaillon_products_section');

/**
 * Ajouter un lien vers les médaillons du client dans la page utilisateur.
 */
function naturapets_user_animals_section($user)
{
	$animals = get_posts(array(
		'post_type' => 'animal',
		'meta_key' => '_customer_id',
		'meta_value' => $user->ID,
		'posts_per_page' => -1,
		'post_status' => 'any',
	));

?>
	<h2>Médaillons du client</h2>
	<table class="form-table">
		<tr>
			<th>Médaillons enregistrés</th>
			<td>
				<?php if (empty($animals)): ?>
					<p>Aucun médaillon enregistré pour ce client.</p>
				<?php else: ?>
					<ul>
						<?php foreach ($animals as $animal):
							$nom = get_field('nom', $animal->ID);
							$product_id = get_post_meta($animal->ID, '_product_id', true);
							$product = wc_get_product($product_id);
						?>
							<li>
								<a href="<?php echo get_edit_post_link($animal->ID); ?>">
									<strong><?php echo $nom ? esc_html($nom) : 'Médaillon #' . $animal->ID; ?></strong>
								</a>
								<?php if ($product): ?>
									- <?php echo esc_html($product->get_name()); ?>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
					<p>
						<a href="<?php echo admin_url('edit.php?post_type=animal&meta_key=_customer_id&meta_value=' . $user->ID); ?>"
							class="button">
							Voir tous les médaillons de ce client
						</a>
					</p>
				<?php endif; ?>
			</td>
		</tr>
	</table>
<?php
}
add_action('show_user_profile', 'naturapets_user_animals_section');
add_action('edit_user_profile', 'naturapets_user_animals_section');


/**
 * ==========================================================================
 * QR CODE : Génération et affichage
 * ==========================================================================
 */

/**
 * Générer l'URL unique du médaillon (version publique avec URL propre).
 */
function naturapets_get_animal_url($animal_id)
{
	$public_post_id = naturapets_get_or_create_medaillon_public_post($animal_id);

	if ($public_post_id) {
		return get_permalink($public_post_id);
	}

	// Fallback : ancienne URL avec paramètres (si création du CPT a échoué)
	$product_id = get_post_meta($animal_id, '_product_id', true);
	$token = $product_id ? naturapets_get_product_unique_id($product_id) : 'NO-PRODUCT';

	return add_query_arg(array(
		'animal' => $animal_id,
		'token' => $token,
	), home_url('/medaillons/'));
}

/**
 * Ajouter une metabox QR Code sur les médaillons.
 */
function naturapets_add_qrcode_metabox()
{
	add_meta_box(
		'naturapets_animal_qrcode',
		'QR Code du médaillon',
		'naturapets_qrcode_metabox_content',
		'animal',
		'side',
		'default'
	);
}
add_action('add_meta_boxes', 'naturapets_add_qrcode_metabox');

/**
 * Contenu de la metabox QR Code.
 */
function naturapets_qrcode_metabox_content($post)
{
	$animal_url = naturapets_get_animal_url($post->ID);

	echo '<div style="text-align: center;">';
	echo naturapets_generate_qrcode($animal_url, 180);
	echo '<p style="margin-top: 10px; word-break: break-all;">';
	echo '<small><a href="' . esc_url($animal_url) . '" target="_blank">' . esc_html($animal_url) . '</a></small>';
	echo '</p>';
	echo '</div>';
}

/**
 * Ajouter la colonne QR Code dans la liste des animaux.
 */
function naturapets_add_qrcode_column($columns)
{
	$columns['qrcode'] = 'QR Code';
	return $columns;
}
add_filter('manage_animal_posts_columns', 'naturapets_add_qrcode_column');

/**
 * Afficher le QR Code dans la colonne.
 */
function naturapets_qrcode_column_content($column, $post_id)
{
	if ($column === 'qrcode') {
		$animal_url = naturapets_get_animal_url($post_id);
		$qr_url = naturapets_get_qrcode_url($animal_url, 60);
		echo '<img src="' . esc_url($qr_url) . '" width="60" height="60" alt="QR Code" />';
	}
}
add_action('manage_animal_posts_custom_column', 'naturapets_qrcode_column_content', 10, 2);

/**
 * Rediriger l'ancienne URL (?animal=&token=) vers la nouvelle URL propre du CPT.
 */
function naturapets_redirect_old_medaillon_url()
{
	if (!isset($_GET['animal']) || !isset($_GET['token'])) {
		return;
	}

	$animal_id = absint($_GET['animal']);
	$token = sanitize_text_field($_GET['token']);

	$animal = get_post($animal_id);

	if (!$animal || $animal->post_type !== 'animal') {
		return;
	}

	$product_id = get_post_meta($animal_id, '_product_id', true);

	if ($product_id) {
		$expected_token = naturapets_get_product_unique_id($product_id);
	} else {
		$expected_token = 'NO-PRODUCT';
	}

	if ($token !== $expected_token) {
		wp_die('Lien invalide ou expiré.', 'Erreur', array('response' => 403));
	}

	$public_post_id = naturapets_get_or_create_medaillon_public_post($animal_id);

	if ($public_post_id) {
		wp_safe_redirect(get_permalink($public_post_id), 301);
		exit;
	}

	// Fallback : afficher directement si le CPT n'a pas pu être créé
	naturapets_display_public_animal_page($animal);
	exit;
}
add_action('template_redirect', 'naturapets_redirect_old_medaillon_url', 5);

/**
 * Rediriger vers le médaillon public si l'ID est saisi dans le champ de recherche.
 * Gère les requêtes GET du bloc "J'ai trouvé un animal".
 */
function naturapets_redirect_medaillon_search()
{
	if (is_admin() || wp_doing_ajax()) {
		return;
	}

	$param_names = array('medaillon', 'medaillon_id', 'numero_medaillon');
	$search_value = null;

	foreach ($param_names as $name) {
		if (!empty($_GET[$name])) {
			$search_value = trim(sanitize_text_field(wp_unslash($_GET[$name])));
			break;
		}
	}

	if (!$search_value) {
		return;
	}

	// Normaliser pour la recherche (format ID affichage : PSI-YYYY-NNNNNN)
	$slug = sanitize_title($search_value);

	// Vérifier le format PSI-YYYY-NNNNNN
	if (!preg_match('/^psi-\d{4}-\d{6}$/', $slug)) {
		return;
	}

	$posts = get_posts(array(
		'post_type' => 'medaillon_public',
		'name' => $slug,
		'posts_per_page' => 1,
		'post_status' => 'publish',
	));

	if (!empty($posts)) {
		wp_safe_redirect(get_permalink($posts[0]->ID), 302);
		exit;
	}
}
add_action('template_redirect', 'naturapets_redirect_medaillon_search', 5);

/**
 * Afficher la page publique du médaillon (accessible via QR code).
 */
function naturapets_display_public_animal_page($animal)
{
	// Récupérer les champs ACF
	$nom = get_field('nom', $animal->ID);
	$type_animal = get_field('type_animal', $animal->ID);
	$race = get_field('race', $animal->ID);
	$age = get_field('age', $animal->ID);
	$photo = get_field('photo_de_lanimal', $animal->ID);
	$informations = get_field('informations_importantes', $animal->ID);
	$allergies = get_field('allergies', $animal->ID);
	$telephone = get_field('telephone', $animal->ID);
	$adresse = get_field('adresse', $animal->ID);

	$customer_id = get_post_meta($animal->ID, '_customer_id', true);
	$customer = get_user_by('id', $customer_id);

	get_header();
?>
	<main class="naturapets-animal-page" style="max-width: 600px; margin: 40px auto; padding: 20px;">
		<div style="background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); padding: 30px;">

			<?php
			$photo_url = naturapets_get_acf_image_url($photo, 'medium');
			if ($photo_url): ?>
				<div style="text-align: center; margin-bottom: 20px;">
					<img src="<?php echo esc_url($photo_url); ?>" alt="<?php echo esc_attr($nom); ?>"
						style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 4px solid #2d5a27;" />
				</div>
			<?php endif; ?>

			<h1 style="text-align: center; margin-bottom: 30px;">
				<?php echo $nom ? esc_html($nom) : 'Médaillon'; ?>
			</h1>

			<table style="width: 100%; border-collapse: collapse;">
				<?php if ($type_animal): ?>
					<tr>
						<th style="text-align: left; padding: 12px; border-bottom: 1px solid #eee; width: 40%; vertical-align: top;">
							Type</th>
						<td style="padding: 12px; border-bottom: 1px solid #eee;"><?php echo esc_html(naturapets_get_type_animal_label($type_animal)); ?>
						</td>
					</tr>
				<?php endif; ?>

				<?php if ($race): ?>
					<tr>
						<th style="text-align: left; padding: 12px; border-bottom: 1px solid #eee; vertical-align: top;">
							Race</th>
						<td style="padding: 12px; border-bottom: 1px solid #eee;"><?php echo nl2br(esc_html($race)); ?>
						</td>
					</tr>
				<?php endif; ?>

				<?php if ($age): ?>
					<tr>
						<th style="text-align: left; padding: 12px; border-bottom: 1px solid #eee;">Âge</th>
						<td style="padding: 12px; border-bottom: 1px solid #eee;"><?php echo esc_html($age); ?>
						</td>
					</tr>
				<?php endif; ?>

				<?php if ($informations): ?>
					<tr>
						<th
							style="text-align: left; padding: 12px; border-bottom: 1px solid #eee; width: 40%; vertical-align: top;">
							Informations importantes</th>
						<td style="padding: 12px; border-bottom: 1px solid #eee;"><?php echo nl2br(esc_html($informations)); ?>
						</td>
					</tr>
				<?php endif; ?>

				<?php if ($allergies): ?>
					<tr>
						<th style="text-align: left; padding: 12px; border-bottom: 1px solid #eee; vertical-align: top;">
							Allergies</th>
						<td style="padding: 12px; border-bottom: 1px solid #eee;"><?php echo nl2br(esc_html($allergies)); ?>
						</td>
					</tr>
				<?php endif; ?>

				<?php if ($telephone): ?>
					<tr>
						<th style="text-align: left; padding: 12px; border-bottom: 1px solid #eee;">Téléphone</th>
						<td style="padding: 12px; border-bottom: 1px solid #eee;">
							<a href="tel:<?php echo esc_attr($telephone); ?>" style="color: #2d5a27; text-decoration: none;">
								<?php echo esc_html($telephone); ?>
							</a>
						</td>
					</tr>
				<?php endif; ?>

				<?php if ($adresse && ($adresse['rue'] || $adresse['code_postal'] || $adresse['ville'])): ?>
					<tr>
						<th style="text-align: left; padding: 12px; border-bottom: 1px solid #eee; vertical-align: top;">Adresse
						</th>
						<td style="padding: 12px; border-bottom: 1px solid #eee;">
							<?php
							if ($adresse['rue'])
								echo esc_html($adresse['rue']) . '<br>';
							if ($adresse['code_postal'] || $adresse['ville']) {
								echo esc_html(trim($adresse['code_postal'] . ' ' . $adresse['ville']));
							}
							?>
						</td>
					</tr>
				<?php endif; ?>

				<?php if ($customer): ?>
					<tr>
						<th style="text-align: left; padding: 12px; border-bottom: 1px solid #eee;">Propriétaire</th>
						<td style="padding: 12px; border-bottom: 1px solid #eee;">
							<?php echo esc_html($customer->display_name); ?></td>
					</tr>
				<?php endif; ?>
			</table>

			<?php if (!$nom && !$type_animal && !$race && !$age && !$informations && !$allergies): ?>
				<p style="text-align: center; color: #666; margin-top: 20px;">
					<em>Les informations de ce médaillon n'ont pas encore été renseignées.</em>
				</p>
			<?php endif; ?>

			<p style="text-align: center; margin-top: 30px; color: #999; font-size: 14px;">
				Médaillon généré par <?php bloginfo('name'); ?>
			</p>
		</div>
	</main>
<?php
	get_footer();
}

/**
 * Ajouter le QR code dans l'espace client (page de modification médaillon).
 */
function naturapets_add_qrcode_to_animal_form($animal_id)
{
	$animal_url = naturapets_get_animal_url($animal_id);
?>
	<div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
		<h3 style="margin-top: 0;">QR Code de votre médaillon</h3>
		<p style="color: #666; font-size: 14px;">Scannez ce QR code pour accéder à votre médaillon.</p>
		<?php echo naturapets_generate_qrcode($animal_url, 150); ?>
	</div>
<?php
}

function naturapets_product_grid_shortcode($atts)
{
	$atts = shortcode_atts(array(
		'limit' => 8,
		'columns' => 4,
		'orderby' => 'date',
		'order' => 'DESC',
		'category' => '',
	), $atts, 'naturapets_products');
	$args = array(
		'limit' => intval($atts['limit']),
		'orderby' => $atts['orderby'],
		'order' => $atts['order'],
		'status' => 'publish',
	);
	if (!empty($atts['category'])) {
		$args['category'] = array($atts['category']);
	}
	$products = wc_get_products($args);
	if (empty($products)) {
		return '<p>Aucun produit trouvé.</p>';
	}
	ob_start();
	echo '<div class="np-product-grid">';
	foreach ($products as $product) {
		$GLOBALS['product'] = $product;
		get_template_part('parts/product-card');
	}
	echo '</div>';
	wp_reset_postdata();
	return ob_get_clean();
}
add_shortcode('naturapets_products', 'naturapets_product_grid_shortcode');

/**
 * ==========================================================================
 * WOOCOMMERCE : Comportement du bouton "Ajouter au panier"
 * ==========================================================================
 *
 * Retire l'API d'interactivité des blocs pour forcer le comportement classique "AJAX"
 * et affiche "Ajouté" à la place.
 */

// 1. Ré-activer le script classique d'ajout au panier d'AJAX
add_action('wp_enqueue_scripts', function () {
	wp_enqueue_script('wc-add-to-cart');
}, 99);

// 2. Nettoyer le HTML des boutons générés par le bloc WooCommerce "Product Button"
add_filter('woocommerce_loop_add_to_cart_link', function ($html, $product, $args) {
	if (strpos($html, 'data-wp-interactive') !== false) {
		// Supprimer les attributs data-wp-* de l'API d'interactivité
		$html = preg_replace('/data-wp-[a-zA-Z0-9\-]+="[^"]*"/', '', $html);
		$html = preg_replace('/data-wp-[a-zA-Z0-9\-]+=\'(?:\\\\.|[^\'])*\'/', '', $html);

		// Supprimer le span "View cart" généré par le bloc
		$html = preg_replace('/<span\s+hidden[^>]*>.*?<\/span>/s', '', $html);
		// Supprimer les spans de quantité (ex: "2 dans le panier")
		$html = preg_replace('/<span[^>]*product-button__quantity[^>]*>.*?<\/span>/is', '', $html);

		// S'assurer que les classes d'AJAX sont présentes
		$html = str_replace('wc-interactive', 'ajax_add_to_cart', $html);

		// Forcer un libellé stable pour éviter les textes dynamiques de quantité.
		$button_text = esc_html__('Ajouté au panié', 'naturapets');
		$html = preg_replace('/<span\s*>(\s*)<\/span>/', '<span>' . $button_text . '</span>', $html);
	}

	// Ajouter un label de référence utilisé par le script d'animation.
	if (strpos($html, 'data-default-label=') === false) {
		$html = preg_replace('/<a\s/i', '<a data-default-label="' . esc_attr__('Ajouté au panié', 'naturapets') . '" ', $html, 1);
	}

	return $html;
}, 10, 3);

// 3. Modifier le bouton pour afficher "Ajouté au panié" via script JS
add_action('wp_footer', function () {
?>
	<script>
		jQuery(function($) {
			function setButtonLabel($button, label) {
				if (!$button || !$button.length) {
					return;
				}
				$button.find('.wc-block-components-product-button__quantity').remove();
				if ($button.find('span').length) {
					$button.find('span').first().text(label);
				} else {
					$button.text(label);
				}
			}

			function getDefaultLabel($button) {
				return ($button && $button.data('default-label')) ? $button.data('default-label') : 'Ajouté au panié';
			}

			$(document).on('added_to_cart', function(event, fragments, cart_hash, $button) {
				if (!$button || !$button.length) {
					return;
				}

				setButtonLabel($button, 'Ajouté au panié');
			});

			$(document).on('wc_fragments_loaded wc_fragments_refreshed', function() {
				$('.add_to_cart_button.ajax_add_to_cart').each(function() {
					var $button = $(this);
					setButtonLabel($button, getDefaultLabel($button));
				});
			});
		});
	</script>
<?php
}, 99);

// 4. Cacher définitivement le lien "Voir le panier" généré par WooCommerce
add_action('wp_head', function () {
	echo '<style> .added_to_cart.wc_forward { display: none !important; } </style>';
});

/**
 * ==========================================================================
 * PATTERNS : Import automatique depuis /patterns (HTML + JSON)
 * ==========================================================================
 */
function naturapets_register_patterns_from_directory()
{
	$patterns_dir = get_stylesheet_directory() . '/patterns';

	if ( ! is_dir( $patterns_dir ) ) {
		return;
	}

	$patterns_dir_normalized = trailingslashit( wp_normalize_path( $patterns_dir ) );
	$iterator                = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $patterns_dir, FilesystemIterator::SKIP_DOTS )
	);

	foreach ( $iterator as $file_info ) {
		if ( ! $file_info instanceof SplFileInfo ) {
			continue;
		}

		$extension = strtolower( $file_info->getExtension() );
		if ( ! in_array( $extension, array( 'html', 'json' ), true ) ) {
			continue;
		}

		$file_path = wp_normalize_path( $file_info->getPathname() );
		$file_raw = file_get_contents( $file_path );
		if ( false === $file_raw ) {
			continue;
		}

		$relative_path = ltrim( str_replace( $patterns_dir_normalized, '', $file_path ), '/' );
		$filename      = basename( $relative_path, '.html' );

		$relative_dir = dirname( $relative_path );
		if ( '.' === $relative_dir ) {
			$relative_dir = '';
		}

		$folder_slug     = $relative_dir ? sanitize_title( str_replace( '/', '-', $relative_dir ) ) : 'naturapets';
		$folder_label    = $relative_dir ? ucwords( str_replace( array( '-', '_' ), ' ', basename( $relative_dir ) ) ) : 'Naturapets';
		$category_slug   = 'naturapets-' . $folder_slug;
		$pattern_slug    = sanitize_title( str_replace( '/', '-', $relative_path ) );
		$pattern_name    = 'naturapets/' . $pattern_slug;
		$content         = '';
		$title           = str_replace( '-', ' ', ucfirst( $filename ) );
		$categories      = array( $category_slug );

		register_block_pattern_category(
			$category_slug,
			array( 'label' => $folder_label )
		);

		if ( 'json' === $extension ) {
			$data = json_decode( $file_raw, true );
			if ( ! is_array( $data ) || empty( $data['content'] ) || ! is_string( $data['content'] ) ) {
				continue;
			}

			$content = $data['content'];

			if ( ! empty( $data['title'] ) && is_string( $data['title'] ) ) {
				$title = $data['title'];
			}

			if ( ! empty( $data['categories'] ) && is_array( $data['categories'] ) ) {
				$json_categories = array_filter(
					array_map( 'sanitize_title', array_filter( $data['categories'], 'is_string' ) )
				);
				if ( ! empty( $json_categories ) ) {
					$categories = array_values( array_unique( array_merge( array( $category_slug ), $json_categories ) ) );
				}
			}
		} else {
			$content = $file_raw;
		}

		// Fallback: lire le nom déclaré dans le contenu du pattern.
		if ( preg_match( '/"metadata"\s*:\s*\{\s*"name"\s*:\s*"([^"]+)"/', $content, $matches ) ) {
			$title = stripslashes( $matches[1] );
		}

		register_block_pattern(
			$pattern_name,
			array(
				'title'      => $title,
				'content'    => $content,
				'categories' => $categories,
			)
		);
	}
}
add_action('init', 'naturapets_register_patterns_from_directory');

/**
 * ==========================================================================
 * BLOCS ACF : Enregistrement des groupes de champs (Promo Code)
 * ==========================================================================
 */
add_action('acf/init', 'naturapets_register_promo_code_block_fields');
function naturapets_register_promo_code_block_fields()
{
	if (function_exists('acf_add_local_field_group')):
		acf_add_local_field_group(array(
			'key' => 'group_promo_code_block',
			'title' => 'Bloc Code Promo',
			'fields' => array(
				array(
					'key' => 'field_promo_image',
					'label' => 'Image Illustrative',
					'name' => 'promo_image',
					'type' => 'image',
					'instructions' => 'Image affichée sur la partie gauche.',
					'required' => 1,
					'return_format' => 'id',
					'preview_size' => 'medium',
					'library' => 'all',
				),
				array(
					'key' => 'field_promo_percent',
					'label' => 'Pourcentage / Valeur',
					'name' => 'promo_percent',
					'type' => 'text',
					'instructions' => 'Ex : 15 (sans le %)',
					'required' => 1,
					'default_value' => '15',
				),
				array(
					'key' => 'field_promo_title',
					'label' => 'Titre',
					'name' => 'promo_title',
					'type' => 'text',
					'default_value' => 'Votre première commande',
				),
				array(
					'key' => 'field_promo_subtitle',
					'label' => 'Sous-titre',
					'name' => 'promo_subtitle',
					'type' => 'textarea',
					'default_value' => 'Profitez de -15% sur l\'ensemble de notre collection de médaillons personnalisés',
					'rows' => 3,
				),
				array(
					'key' => 'field_promo_woo_code',
					'label' => 'Sélectionnez un code promo WooCommerce',
					'name' => 'promo_woo_code',
					'type' => 'post_object',
					'instructions' => 'Choisissez le code de réduction qui sera affiché (doit être créé dans Marketing > Codes promo).',
					'required' => 0,
					'post_type' => array(
						0 => 'shop_coupon',
					),
					'allow_null' => 1,
					'multiple' => 0,
					'return_format' => 'object',
					'ui' => 1,
				),
				array(
					'key' => 'field_promo_mention',
					'label' => 'Mention Légale',
					'name' => 'promo_mention',
					'type' => 'text',
					'default_value' => '* Offre valable pour toute première commande. Non cumulable.',
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'block',
						'operator' => '==',
						'value' => 'naturapets/promo-code',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => true,
			'description' => '',
		));
	endif;
}

/**
 * ==========================================================================
 * BLOCS ACF : Bandeau du haut (messages rotatifs)
 * ==========================================================================
 */
add_action('acf/init', 'naturapets_register_top_banner_rotatif_block_fields');
function naturapets_register_top_banner_rotatif_block_fields()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}
	acf_add_local_field_group(array(
		'key' => 'group_np_top_banner_rotatif',
		'title' => 'Bloc Bandeau du haut (rotatif)',
		'fields' => array(
			array(
				'key' => 'field_np_tb_phrase_1',
				'label' => 'Phrase 1',
				'name' => 'phrase_1',
				'type' => 'text',
				'instructions' => __('Premier message affiché dans le bandeau.', 'naturapets'),
				'required' => 0,
				'maxlength' => 160,
			),
			array(
				'key' => 'field_np_tb_phrase_2',
				'label' => 'Phrase 2',
				'name' => 'phrase_2',
				'type' => 'text',
				'instructions' => __('Deuxième message (optionnel). Si renseigné, les messages défilent automatiquement.', 'naturapets'),
				'required' => 0,
				'maxlength' => 160,
			),
			array(
				'key' => 'field_np_tb_phrase_3',
				'label' => 'Phrase 3',
				'name' => 'phrase_3',
				'type' => 'text',
				'instructions' => __('Troisième message (optionnel).', 'naturapets'),
				'required' => 0,
				'maxlength' => 160,
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'block',
					'operator' => '==',
					'value' => 'naturapets/top-banner-rotatif',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'active' => true,
	));
}
