<?php
/**
 * Template de rendu du bloc Points Numérotés (design Pencil : Bloc/Points Numérotés)
 * Numéros en Fraunces vert, étapes avec animation au scroll via IntersectionObserver.
 *
 * Champs ACF :
 * - points_label       : label discret (ex: "Comment ça marche")
 * - points_title       : titre de la section
 * - points_description : sous-titre descriptif
 * - points_items       : repeater
 *     - points_item_title       : titre de l'étape
 *     - points_item_description : description de l'étape
 *
 * @package Naturapets
 */

if (!defined('ABSPATH')) {
	exit;
}

$label       = get_field('points_label');
$title       = get_field('points_title');
$description = get_field('points_description');
$items       = get_field('points_items');

if (empty($label)) {
	$label = 'Comment ça marche';
}
if (empty($title)) {
	$title = 'Comment ça marche';
}
if (empty($description)) {
	$description = 'Quatre étapes simples pour offrir un médaillon unique à votre compagnon';
}
if (empty($items)) {
	$items = [
		[
			'points_item_title'       => 'Choisissez votre modèle',
			'points_item_description' => 'Parcourez notre collection de médaillons et sélectionnez la forme, la taille et le matériau qui correspond le mieux à votre animal.',
		],
		[
			'points_item_title'       => 'Personnalisez la gravure',
			'points_item_description' => 'Ajoutez le nom de votre compagnon, votre numéro de téléphone ou un message personnel. Choisissez parmi nos typographies élégantes.',
		],
		[
			'points_item_title'       => 'Validez votre commande',
			'points_item_description' => 'Paiement sécurisé en quelques clics. Recevez une confirmation instantanée avec le suivi de fabrication de votre médaillon.',
		],
		[
			'points_item_title'       => 'Recevez et activez',
			'points_item_description' => 'Votre médaillon est livré sous 5 à 7 jours. Activez-le depuis votre espace personnel pour commencer à protéger votre animal.',
		],
	];
}

$block_id = !empty($block['anchor']) ? $block['anchor'] : 'points-' . $block['id'];
?>
<section
	id="<?php echo esc_attr($block_id); ?>"
	class="np-points"
	aria-label="<?php echo esc_attr($title); ?>"
>
	<div class="np-points__inner">

		<div class="np-points__header">
			<?php if ($label): ?>
				<span class="np-points__label"><?php echo esc_html($label); ?></span>
			<?php endif; ?>
			<h2 class="np-points__title"><?php echo esc_html($title); ?></h2>
			<?php if ($description): ?>
				<p class="np-points__desc"><?php echo esc_html($description); ?></p>
			<?php endif; ?>
		</div>

		<div class="np-points__separator" aria-hidden="true"></div>

		<?php if (!empty($items)): ?>
			<ol class="np-points__list">
				<?php foreach ($items as $index => $item):
					$item_title = $item['points_item_title'] ?? '';
					$item_desc  = $item['points_item_description'] ?? '';
					if (empty($item_title)) continue;
					$num = str_pad($index + 1, 2, '0', STR_PAD_LEFT);
					$is_last = ($index === count($items) - 1);
				?>
					<li
						class="np-points__item"
						data-scroll-reveal
						style="--delay: <?php echo $index * 120; ?>ms"
					>
						<span class="np-points__num" aria-hidden="true"><?php echo esc_html($num); ?></span>
						<div class="np-points__content">
							<h3 class="np-points__item-title"><?php echo esc_html($item_title); ?></h3>
							<?php if ($item_desc): ?>
								<p class="np-points__item-desc"><?php echo nl2br(esc_html($item_desc)); ?></p>
							<?php endif; ?>
						</div>
					</li>
					<?php if (!$is_last): ?>
						<li class="np-points__sep" aria-hidden="true" role="presentation"></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>

	</div>
</section>
