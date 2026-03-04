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

$animal_id = get_post_meta(get_the_ID(), '_animal_id', true);
$animal    = $animal_id ? get_post($animal_id) : null;

if (!$animal || $animal->post_type !== 'animal') {
	global $wp_query;
	$wp_query->set_404();
	status_header(404);
	nocache_headers();
	include(get_query_template('404'));
	exit;
}

naturapets_display_public_animal_page($animal);
