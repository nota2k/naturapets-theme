<?php
/**
 * Template pour l'affichage d'un médaillon public (CPT medaillon_public).
 * Les données sont récupérées depuis le post animal lié via _animal_id.
 *
 * @package Naturapets
 */

if (!defined('ABSPATH')) {
	exit;
}

$public_post_id = get_the_ID();
$animal_id = get_post_meta($public_post_id, '_animal_id', true);
$animal = $animal_id ? get_post($animal_id) : null;
$is_claimable = !$animal || $animal->post_type !== 'animal';
$activation_error = '';

if ($is_claimable && isset($_POST['naturapets_claim_medaillon'])) {
	if (!is_user_logged_in()) {
		$activation_error = __('Vous devez être connecté pour activer ce médaillon.', 'naturapets');
	} elseif (
		!isset($_POST['naturapets_claim_nonce'])
		|| !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['naturapets_claim_nonce'])), 'naturapets_claim_medaillon_' . $public_post_id)
	) {
		$activation_error = __('La demande est invalide. Merci de réessayer.', 'naturapets');
	} else {
		$result = naturapets_claim_medaillon_public_for_user($public_post_id, get_current_user_id());
		if (is_wp_error($result)) {
			$activation_error = $result->get_error_message();
		} else {
			$redirect = wc_get_endpoint_url('mes-animaux', (string) $result, wc_get_page_permalink('myaccount'));
			wp_safe_redirect($redirect);
			exit;
		}
	}
}

$nom = $animal ? get_field('nom', $animal->ID) : '';
$type_animal = $animal ? get_field('type_animal', $animal->ID) : '';
$race = $animal ? get_field('race', $animal->ID) : '';
$age = $animal ? get_field('age', $animal->ID) : '';
$photo = $animal ? get_field('photo_de_lanimal', $animal->ID) : '';
$informations = $animal ? get_field('informations_importantes', $animal->ID) : '';
$allergies = $animal ? get_field('allergies', $animal->ID) : '';
$telephone = $animal ? get_field('telephone', $animal->ID) : '';
$adresse = $animal ? get_field('adresse', $animal->ID) : array();
$customer_id = $animal ? (int) get_post_meta($animal->ID, '_customer_id', true) : 0;
$customer = $customer_id ? get_user_by('id', $customer_id) : null;
$photo_url = $animal && function_exists('naturapets_get_acf_image_url') ? naturapets_get_acf_image_url($photo, 'medium') : '';
$claimable_count = is_user_logged_in() ? (int) get_user_meta(get_current_user_id(), '_naturapets_claimable_medaillons', true) : 0;
$medaillon_code = (string) get_post_meta($public_post_id, '_medaillon_code', true);
$medaillon_status = (string) get_post_meta($public_post_id, '_medaillon_status', true);

$view_model = array(
	'public_post_id' => $public_post_id,
	'medaillon_code' => $medaillon_code,
	'medaillon_status' => $medaillon_status ?: ($is_claimable ? 'available' : 'assigned'),
	'activation_error' => $activation_error,
	'is_claimable' => $is_claimable,
	'is_user_logged_in' => is_user_logged_in(),
	'claimable_count' => $claimable_count,
	'claim_nonce_action' => 'naturapets_claim_medaillon_' . $public_post_id,
	'claim_nonce_name' => 'naturapets_claim_nonce',
	'login_url' => wp_login_url(get_permalink($public_post_id)),
	'title' => $nom ? $nom : __('Médaillon', 'naturapets'),
	'data' => array(
		'animal_id' => $animal ? (int) $animal->ID : 0,
		'nom' => (string) $nom,
		'type_animal' => (string) $type_animal,
		'type_animal_label' => $type_animal ? naturapets_get_type_animal_label($type_animal) : '',
		'race' => (string) $race,
		'age' => (string) $age,
		'informations' => (string) $informations,
		'allergies' => (string) $allergies,
		'telephone' => (string) $telephone,
		'adresse' => array(
			'rue' => isset($adresse['rue']) ? (string) $adresse['rue'] : '',
			'code_postal' => isset($adresse['code_postal']) ? (string) $adresse['code_postal'] : '',
			'ville' => isset($adresse['ville']) ? (string) $adresse['ville'] : '',
		),
		'photo_url' => (string) $photo_url,
		'owner_name' => $customer ? (string) $customer->display_name : '',
	),
);

set_query_var('naturapets_medaillon_view_model', $view_model);

get_header();
get_template_part('parts/medaillon-public-empty');
