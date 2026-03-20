<?php
/**
 * Carousel de texte — render.php
 * Défilement horizontal piloté par le scroll vertical de la page.
 */

$title    = get_field('carousel_title');
$subtitle = get_field('carousel_subtitle');
$items    = get_field('carousel_items');

if (empty($items)) {
	return;
}

$count     = count($items);
$block_id  = 'np-carousel-' . ($block['id'] ?? uniqid());
$anchor    = !empty($block['anchor']) ? 'id="' . esc_attr($block['anchor']) . '"' : '';
?>
<section
	class="np-carousel-texte"
	data-carousel-scroll
	style="--card-count: <?php echo esc_attr($count); ?>"
	<?php echo $anchor; ?>
>
	<div class="np-carousel-texte__sticky">

		<?php if ($title || $subtitle) : ?>
		<div class="np-carousel-texte__header">
			<?php if ($title) : ?>
				<h2 class="np-carousel-texte__title"><?php echo esc_html($title); ?></h2>
			<?php endif; ?>
			<?php if ($subtitle) : ?>
				<p class="np-carousel-texte__subtitle"><?php echo esc_html($subtitle); ?></p>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<div class="np-carousel-texte__track-wrap">
			<div class="np-carousel-texte__track">
				<?php foreach ($items as $index => $item) : ?>
				<article class="np-carousel-texte__card" aria-label="<?php echo esc_attr($item['card_label'] ?? ''); ?>">
					<?php if (!empty($item['card_label'])) : ?>
						<span class="np-carousel-texte__card-label"><?php echo esc_html($item['card_label']); ?></span>
					<?php endif; ?>
					<?php if (!empty($item['card_title'])) : ?>
						<h3 class="np-carousel-texte__card-title"><?php echo esc_html($item['card_title']); ?></h3>
					<?php endif; ?>
					<?php if (!empty($item['card_text'])) : ?>
						<p class="np-carousel-texte__card-text"><?php echo esc_html($item['card_text']); ?></p>
					<?php endif; ?>
				</article>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="np-carousel-texte__progress" aria-hidden="true">
			<div class="np-carousel-texte__progress-bar"></div>
		</div>

	</div>
</section>
