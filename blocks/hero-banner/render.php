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
$use_page_featured_image = (bool) get_field( 'hero_banner_use_page_featured_image' );

if ( empty( $titre ) ) {
	$titre = 'Naturapets';
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

$image_url = '';

// Priorité image : featured image de la page de contexte (si activé), sinon image ACF du bloc.
if ( $use_page_featured_image && function_exists( 'naturapets_get_page_hero_context_id' ) ) {
	$context_post_id = (int) naturapets_get_page_hero_context_id();
	if ( $context_post_id > 0 ) {
		$featured_id = (int) get_post_thumbnail_id( $context_post_id );
		if ( $featured_id > 0 ) {
			$image_url = (string) wp_get_attachment_image_url( $featured_id, 'large' );
		}
	}
}

if ( '' === $image_url ) {
	$image_url = (string) naturapets_get_acf_image_url( $image, 'large' );
}

// Description de page prioritaire dans le hero (ex: page Boutique).
$context_post_id = function_exists( 'naturapets_get_page_hero_context_id' )
	? (int) naturapets_get_page_hero_context_id()
	: 0;
if ( $context_post_id > 0 ) {
	$page_excerpt = (string) get_the_excerpt( $context_post_id );
	if ( '' !== trim( wp_strip_all_tags( $page_excerpt ) ) ) {
		$slogan = $page_excerpt;
	}
}

if ( empty( $slogan ) ) {
	$slogan = 'La tranquillité au bout du collier';
}

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
		</div>
	</div>
</section>
