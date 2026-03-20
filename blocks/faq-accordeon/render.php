<?php
/**
 * Template de rendu du bloc FAQ Accordéon (design Pencil : Bloc/FAQ Accordeon)
 * Section FAQ avec titre centré et items accordéon dépliables.
 *
 * Champs ACF :
 * - faq_label      : texte du label (ex: "WP Block")
 * - faq_title      : titre principal (Fraunces 40px)
 * - faq_subtitle   : sous-titre descriptif
 * - faq_items      : repeater
 *     - faq_question : question
 *     - faq_answer   : réponse
 *
 * @package Naturapets
 */

if (!defined('ABSPATH')) {
	exit;
}

$label = get_field('faq_label');
$title = get_field('faq_title');
$subtitle = get_field('faq_subtitle');
$items = get_field('faq_items');

if (empty($label)) {
	$label = 'FAQ';
}
if (empty($title)) {
	$title = 'Questions fréquentes';
}
if (empty($subtitle)) {
	$subtitle = 'Tout ce que vous devez savoir sur nos médaillons connectés';
}
if (empty($items)) {
	$items = array(
		array(
			'faq_question' => 'Comment fonctionne le QR code du médaillon ?',
			'faq_answer' => 'Le QR code est gravé de manière permanente sur le médaillon. Lorsqu\'une personne le scanne avec son smartphone, elle accède directement à la fiche de votre animal avec vos coordonnées. Aucune application n\'est nécessaire.',
		),
		array(
			'faq_question' => 'Le médaillon est-il résistant à l\'eau ?',
			'faq_answer' => 'Oui, nos médaillons sont en acier inoxydable et résistants aux intempéries. Ils peuvent supporter l\'eau, la pluie et les bains de votre animal sans aucun problème.',
		),
		array(
			'faq_question' => 'Comment mettre à jour les informations de mon animal ?',
			'faq_answer' => 'Connectez-vous à votre espace personnel sur notre site et modifiez la fiche de votre animal à tout moment. Les modifications sont instantanément visibles lors du scan du QR code.',
		),
		array(
			'faq_question' => 'Puis-je commander un médaillon pour plusieurs animaux ?',
			'faq_answer' => 'Absolument ! Vous pouvez créer autant de fiches animaux que vous le souhaitez et commander un médaillon pour chacun d\'eux.',
		),
	);
}

$block_id = !empty($block['anchor']) ? $block['anchor'] : 'faq-' . $block['id'];
?>
<section id="<?php echo esc_attr($block_id); ?>" class="np-faq"
	aria-label="<?php esc_attr_e('Questions fréquentes', 'naturapets'); ?>">
	<div class="np-faq__inner">

		<?php if ($label): ?>
			<div class="np-faq__label" aria-hidden="true">
				<span><?php echo esc_html($label); ?></span>
			</div>
		<?php endif; ?>

		<div class="np-faq__header">
			<h2 class="np-faq__title"><?php echo esc_html($title); ?></h2>
			<?php if ($subtitle): ?>
				<p class="np-faq__subtitle"><?php echo esc_html($subtitle); ?></p>
			<?php endif; ?>
		</div>

		<?php if (!empty($items)): ?>
			<div class="np-faq__list" role="list">
				<?php foreach ($items as $index => $item):
					$question = isset($item['faq_question']) ? $item['faq_question'] : '';
					$answer = isset($item['faq_answer']) ? $item['faq_answer'] : '';
					if (empty($question)) {
						continue;
					}
					$item_id = $block_id . '-item-' . $index;
					$is_first = (0 === $index);
					?>
					<div class="np-faq__item<?php echo $is_first ? ' np-faq__item--open' : ''; ?>" role="listitem">
						<button class="np-faq__question" aria-expanded="<?php echo $is_first ? 'true' : 'false'; ?>"
							aria-controls="<?php echo esc_attr($item_id); ?>" type="button">
							<span class="np-faq__question-text"><?php echo esc_html($question); ?></span>
							<span class="np-faq__icon" aria-hidden="true">
								<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
									stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<polyline points="6 9 12 15 18 9"></polyline>
								</svg>
							</span>
						</button>
						<div id="<?php echo esc_attr($item_id); ?>" class="np-faq__answer" role="region"
							aria-labelledby="<?php echo esc_attr($item_id . '-btn'); ?>">
							<div class="np-faq__answer-inner">
								<p><?php echo nl2br(esc_html($answer)); ?></p>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>
</section>