<?php
/**
 * Template de rendu du bloc Moveblock – Animation GSAP
 * Champs ACF : effet, durée, délai, ease, stagger, sélecteur cible
 *
 * @package Naturapets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$effect_id = get_field( 'effect' ) ?: 'fadeIn';
$duration  = get_field( 'duration' ) !== '' && get_field( 'duration' ) !== null ? floatval( get_field( 'duration' ) ) : 1;
$delay     = get_field( 'delay' ) !== '' && get_field( 'delay' ) !== null ? floatval( get_field( 'delay' ) ) : 0;
$ease      = get_field( 'ease' ) ?: 'power2.out';
$stagger   = get_field( 'stagger' ) !== '' && get_field( 'stagger' ) !== null ? floatval( get_field( 'stagger' ) ) : 0;
$selector  = get_field( 'target_selector' ) ? trim( (string) get_field( 'target_selector' ) ) : '';

if ( empty( $selector ) ) {
	// En éditeur, afficher un message si pas de cible.
	if ( is_admin() ) {
		echo '<div class="moveblock-placeholder" style="padding:12px;background:#f0f0f0;border:1px dashed #999;">';
		echo esc_html__( 'Moveblock : saisir un sélecteur CSS (ex. .titre, #hero) dans les réglages du bloc.', 'naturapets' );
		echo '</div>';
	}
	return;
}

$block_id = 'moveblock-' . ( isset( $block['id'] ) ? $block['id'] : uniqid() );
?>
<div class="moveblock-config"
	id="<?php echo esc_attr( $block_id ); ?>"
	data-moveblock-effect="<?php echo esc_attr( $effect_id ); ?>"
	data-moveblock-duration="<?php echo esc_attr( (string) $duration ); ?>"
	data-moveblock-delay="<?php echo esc_attr( (string) $delay ); ?>"
	data-moveblock-ease="<?php echo esc_attr( $ease ); ?>"
	data-moveblock-stagger="<?php echo esc_attr( (string) $stagger ); ?>"
	data-moveblock-selector="<?php echo esc_attr( $selector ); ?>"
	aria-hidden="true"
	style="display:none;"></div>
