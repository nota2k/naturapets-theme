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
		<div class="np-hero-banner__media" aria-hidden="true">
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
			<h1 class="np-hero-banner__title"><?php echo esc_html( $titre ); ?></h1>
			<p class="np-hero-banner__slogan"><?php echo esc_html( $slogan ); ?></p>
			<a href="<?php echo esc_url( $bouton_url ); ?>" class="np-hero-banner__cta">
				<?php echo esc_html( $bouton_texte ); ?>
			</a>
		</div>
	</div>
</section>
