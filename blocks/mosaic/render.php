<?php
/**
 * Template de rendu du bloc Mosaïque (design Pencil : Bloc Mosaïque k3CR0)
 *
 * Structure : deux colonnes
 *   – Gauche  : grande image pleine hauteur
 *   – Droite  : image en haut + zone texte verte en bas (titre, desc, bouton)
 * L'option "inverser" permute les deux colonnes.
 *
 * Champs ACF :
 * - mosaic_image_main        : image colonne principale (gauche par défaut)
 * - mosaic_image_secondary   : image colonne secondaire (haut droite)
 * - mosaic_title             : titre (Fraunces blanc)
 * - mosaic_description       : texte descriptif
 * - mosaic_button_label      : libellé du bouton
 * - mosaic_button_url        : URL du bouton
 * - mosaic_inverted          : true/false — inverser l'ordre des colonnes
 *
 * @package Naturapets
 */

if (!defined('ABSPATH')) {
	exit;
}

$image_main      = get_field('mosaic_image_main');
$image_secondary = get_field('mosaic_image_secondary');
$title           = get_field('mosaic_title');
$description     = get_field('mosaic_description');
$button_label    = get_field('mosaic_button_label');
$button_url      = get_field('mosaic_button_url');
$inverted        = get_field('mosaic_inverted');

// Valeurs par défaut
if (empty($title))        $title        = 'Protégez ceux que vous aimez';
if (empty($description))  $description  = 'Nos médaillons connectés offrent une tranquillité d\'esprit au quotidien. Personnalisez, scannez et retrouvez votre compagnon en un instant.';
if (empty($button_label)) $button_label = 'Découvrir';
if (empty($button_url))   $button_url   = '#';

// URLs des images
$url_main      = '';
$url_secondary = '';
$alt_main      = '';
$alt_secondary = '';

if (!empty($image_main)) {
	if (is_array($image_main)) {
		$url_main = $image_main['sizes']['large'] ?? $image_main['url'] ?? '';
		$alt_main = $image_main['alt'] ?? '';
	} else {
		$url_main = wp_get_attachment_image_url($image_main, 'large');
		$alt_main = get_post_meta($image_main, '_wp_attachment_image_alt', true);
	}
}

if (!empty($image_secondary)) {
	if (is_array($image_secondary)) {
		$url_secondary = $image_secondary['sizes']['large'] ?? $image_secondary['url'] ?? '';
		$alt_secondary = $image_secondary['alt'] ?? '';
	} else {
		$url_secondary = wp_get_attachment_image_url($image_secondary, 'large');
		$alt_secondary = get_post_meta($image_secondary, '_wp_attachment_image_alt', true);
	}
}

$block_id   = !empty($block['anchor']) ? $block['anchor'] : 'mosaic-' . $block['id'];
$css_class  = 'np-mosaic';
if ($inverted) $css_class .= ' np-mosaic--inverted';
?>
<section
	id="<?php echo esc_attr($block_id); ?>"
	class="<?php echo esc_attr($css_class); ?>"
	aria-label="<?php echo esc_attr($title); ?>"
>
	<!-- Colonne principale : image pleine hauteur -->
	<div class="np-mosaic__col np-mosaic__col--main">
		<?php if ($url_main): ?>
			<img
				class="np-mosaic__img-main"
				src="<?php echo esc_url($url_main); ?>"
				alt="<?php echo esc_attr($alt_main); ?>"
				loading="lazy"
			/>
		<?php else: ?>
			<div class="np-mosaic__img-main np-mosaic__img-main--placeholder"></div>
		<?php endif; ?>
	</div>

	<!-- Colonne secondaire : image haut + texte bas -->
	<div class="np-mosaic__col np-mosaic__col--secondary">

		<!-- Image haute -->
		<div class="np-mosaic__img-secondary-wrap">
			<?php if ($url_secondary): ?>
				<img
					class="np-mosaic__img-secondary"
					src="<?php echo esc_url($url_secondary); ?>"
					alt="<?php echo esc_attr($alt_secondary); ?>"
					loading="lazy"
				/>
			<?php else: ?>
				<div class="np-mosaic__img-secondary np-mosaic__img-secondary--placeholder"></div>
			<?php endif; ?>
		</div>

		<!-- Zone texte verte -->
		<div class="np-mosaic__text-zone">
			<h2 class="np-mosaic__title"><?php echo esc_html($title); ?></h2>

			<?php if ($description): ?>
				<p class="np-mosaic__desc"><?php echo nl2br(esc_html($description)); ?></p>
			<?php endif; ?>

			<?php if ($button_url && $button_label): ?>
				<a
					href="<?php echo esc_url($button_url); ?>"
					class="np-mosaic__btn"
				>
					<span><?php echo esc_html($button_label); ?></span>
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<line x1="5" y1="12" x2="19" y2="12"></line>
						<polyline points="12 5 19 12 12 19"></polyline>
					</svg>
				</a>
			<?php endif; ?>
		</div>

	</div>
</section>
