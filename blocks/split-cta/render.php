<?php
/**
 * Template de rendu du bloc Section deux colonnes (design Figma node 3-27)
 * Colonne gauche : fond gris ou image. Colonne droite : titre, texte, bouton.
 *
 * @package Naturapets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titre        = get_field( 'split_cta_titre' );
$texte        = get_field( 'split_cta_texte' );
$bouton_texte = get_field( 'split_cta_bouton_texte' );
$bouton_url   = get_field( 'split_cta_bouton_url' );
$image_gauche = get_field( 'split_cta_image_gauche' );

if ( empty( $titre ) ) {
	$titre = 'GROS TITRE';
}
if ( empty( $texte ) ) {
	$texte = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
}
if ( empty( $bouton_texte ) ) {
	$bouton_texte = 'Découvrir';
}
if ( empty( $bouton_url ) ) {
	$bouton_url = '#';
}

$url_image_gauche = naturapets_get_acf_image_url( $image_gauche, 'large' );
?>
<section class="np-split-cta" aria-label="<?php esc_attr_e( 'Section présentation', 'naturapets' ); ?>">
	<div class="np-split-cta__image-wrap<?php echo $url_image_gauche ? ' np-split-cta__image-wrap--has-image' : ''; ?>">
		<?php if ( $url_image_gauche ) : ?>
			<img src="<?php echo esc_url( $url_image_gauche ); ?>" alt="" loading="lazy" />
		<?php endif; ?>
	</div>
	<div class="np-split-cta__content-wrap">
		<div class="np-split-cta__content">
			<div class="np-split-cta__title-wrap">
				<h2 class="np-split-cta__title behind"><?php echo esc_html( $titre ); ?></h2>
				<h2 class="np-split-cta__title"><?php echo esc_html( $titre ); ?></h2>
			</div>
			<p class="np-split-cta__text"><?php echo wp_kses_post( nl2br( $texte ) ); ?></p>
			<a href="<?php echo esc_url( $bouton_url ); ?>" class="np-split-cta__cta">
				<?php echo esc_html( $bouton_texte ); ?>
			</a>
		</div>
	</div>
</section>
