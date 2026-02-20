<?php
/**
 * Template de rendu du bloc ACF Section Hero Naturapets
 * Champs ACF : texte_haut, texte_bas, image_haut_gauche, image_bas_droite
 *
 * @package Naturapets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$texte_haut         = get_field( 'texte_haut' );
$texte_bas          = get_field( 'texte_bas' );
$image_haut_gauche  = get_field( 'image_haut_gauche' );
$image_bas_droite   = get_field( 'image_bas_droite' );

if ( empty( $texte_haut ) ) {
	$texte_haut = "Un système\nsimple et efficace";
}
if ( empty( $texte_bas ) ) {
	$texte_bas = "Pour nos amis les\nbêtes";
}

$url_image_haut  = naturapets_get_acf_image_url( $image_haut_gauche, 'large' );
$url_image_bas   = naturapets_get_acf_image_url( $image_bas_droite, 'large' );
?>
<section class="naturapets-hero-section" aria-label="<?php esc_attr_e( 'Présentation Naturapets', 'naturapets' ); ?>">
	<div class="naturapets-hero-section__grid">
		<div class="naturapets-hero-section__block naturapets-hero-section__block--top-left" aria-hidden="true">
			<?php if ( $url_image_haut ) : ?>
				<img src="<?php echo esc_url( $url_image_haut ); ?>" alt="" loading="lazy" />
			<?php endif; ?>
		</div>
		<p class="naturapets-hero-section__text naturapets-hero-section__text--top-right">
			<?php echo wp_kses_post( nl2br( $texte_haut ) ); ?>
		</p>
		<p class="naturapets-hero-section__text naturapets-hero-section__text--bottom-left">
			<?php echo wp_kses_post( nl2br( $texte_bas ) ); ?>
		</p>
		<div class="naturapets-hero-section__block naturapets-hero-section__block--bottom-right" aria-hidden="true">
			<?php if ( $url_image_bas ) : ?>
				<img src="<?php echo esc_url( $url_image_bas ); ?>" alt="" loading="lazy" />
			<?php endif; ?>
		</div>
	</div>
</section>
