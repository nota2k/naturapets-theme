<?php
/**
 * Template de rendu du bloc Présentation animée (design Pencil : Bloc Presentation animée)
 * Image en parallax (haut-droit → bas-gauche au scroll), titre + texte sur fond blanc.
 *
 * Champs ACF :
 * - pres_title  : titre principal (Nunito Sans 900)
 * - pres_text   : texte descriptif
 * - pres_image  : image en arrière-plan (parallax)
 *
 * @package Naturapets
 */

if (!defined('ABSPATH')) {
	exit;
}

$title = get_field('pres_title');
$text  = get_field('pres_text');
$image = get_field('pres_image');

if (empty($title)) {
	$title = 'Un moyen fiable et économique pour protéger votre animal';
}
if (empty($text)) {
	$text = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc dictum ultricies cursus. Praesent ut porttitor leo. Nullam ultrices purus eget imperdiet vehicula.';
}

$image_url = '';
if (!empty($image)) {
	$image_url = is_array($image)
		? ($image['sizes']['large'] ?? $image['url'] ?? '')
		: wp_get_attachment_image_url($image, 'large');
}

$block_id = !empty($block['anchor']) ? $block['anchor'] : 'presentation-' . $block['id'];
?>
<section
	id="<?php echo esc_attr($block_id); ?>"
	class="np-pres"
	aria-label="<?php echo esc_attr($title); ?>"
>
	<div class="np-pres__inner">

		<?php if ($image_url): ?>
			<div class="np-pres__bg" aria-hidden="true">
				<img
					class="np-pres__img js-pres-parallax"
					src="<?php echo esc_url($image_url); ?>"
					alt=""
					loading="lazy"
				/>
			</div>
		<?php endif; ?>

		<div class="np-pres__content">
			<h2 class="np-pres__title"><?php echo esc_html($title); ?></h2>
			<?php if ($text): ?>
				<p class="np-pres__text"><?php echo nl2br(esc_html($text)); ?></p>
			<?php endif; ?>
		</div>

	</div>
</section>
