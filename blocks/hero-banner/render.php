<?php
/**
 * Template de rendu du bloc Bannière Hero (design Figma node 1-101)
 * Fond : image ou vidéo (éditable en ACF). Titre, slogan, bouton à droite.
 *
 * @package Naturapets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titre        = get_field( 'hero_banner_titre' );
$slogan       = get_field( 'hero_banner_slogan' );
$bouton_texte = get_field( 'hero_banner_bouton_texte' );
$bouton_url   = get_field( 'hero_banner_bouton_url' );
$bouton_taille = get_field( 'hero_banner_bouton_taille' );
$titre_taille  = get_field( 'hero_banner_titre_taille' );
$image        = get_field( 'hero_banner_image' );
$video        = get_field( 'hero_banner_video' );

if ( empty( $titre ) ) {
	$titre = 'Naturapets';
}
if ( empty( $slogan ) ) {
	$slogan = 'La tranquillité au bout du collier';
}
if ( empty( $bouton_texte ) ) {
	$bouton_texte = 'Découvrir';
}
if ( empty( $bouton_url ) ) {
	$bouton_url = '#';
}

$video_url  = null;
$video_mime = 'video/mp4';
if ( ! empty( $video ) && is_array( $video ) && ! empty( $video['url'] ) ) {
	$video_url  = $video['url'];
	$video_mime = ! empty( $video['mime_type'] ) ? $video['mime_type'] : 'video/mp4';
} elseif ( ! empty( $video ) && is_numeric( $video ) ) {
	$video_url  = wp_get_attachment_url( (int) $video );
	$video_mime = get_post_mime_type( (int) $video ) ?: 'video/mp4';
}

$image_url = naturapets_get_acf_image_url( $image, 'large' );
$has_media = ! empty( $video_url ) || ! empty( $image_url );

$section_class = 'np-hero-banner';
if ( $has_media ) {
	$section_class .= ' np-hero-banner--has-media';
}
?>
<section class="<?php echo esc_attr( $section_class ); ?>" aria-label="<?php esc_attr_e( 'Bannière d\'accueil', 'naturapets' ); ?>">
	<?php if ( $has_media ) : ?>
		<div class="np-hero-banner__media" aria-hidden="false">
			<?php if ( ! empty( $video_url ) ) : ?>
				<video class="np-hero-banner__video" autoplay muted loop playsinline>
					<source src="<?php echo esc_url( $video_url ); ?>" type="<?php echo esc_attr( $video_mime ); ?>">
				</video>
			<?php elseif ( ! empty( $image_url ) ) : ?>
				<img class="np-hero-banner__image" src="<?php echo esc_url( $image_url ); ?>" alt="" loading="eager" fetchpriority="high">
			<?php endif; ?>
			<span class="np-hero-banner__overlay"></span>
		</div>
	<?php endif; ?>
	<div class="np-hero-banner__inner">
		<div class="np-hero-banner__content">
			<?php
			$title_class = 'np-hero-banner__title';
			if ( ! empty( $titre_taille ) ) {
				$title_class .= ' has-' . esc_attr( $titre_taille ) . '-font-size';
			}
			?>
			<h1 class="<?php echo esc_attr( $title_class ); ?>"><?php echo esc_html( $titre ); ?></h1>
			<p class="np-hero-banner__slogan"><?php echo esc_html( $slogan ); ?></p>
			<?php
			$cta_class = 'np-hero-banner__cta';
			if ( ! empty( $bouton_taille ) ) {
				$cta_class .= ' has-' . esc_attr( $bouton_taille ) . '-font-size';
			}
			?>
			<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"left"}} -->
			<div class="wp-block-buttons">
				<!-- wp:button {"url":"<?php echo esc_url( $bouton_url ); ?>","textAlign":"center","className":"<?php echo esc_attr( $cta_class ); ?>"} -->
				<a class="wp-block-button__link <?php echo esc_attr( $cta_class ); ?>" href="<?php echo esc_url( $bouton_url ); ?>">
					<?php echo esc_html( $bouton_texte ); ?>
				</a>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
	</div>
</section>
