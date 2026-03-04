<?php
/**
 * Template pour l'affichage d'un médaillon public (CPT medaillon_public).
 * Les données sont récupérées depuis le post animal lié via _animal_id.
 *
 * @package Naturapets
 */

if (!defined('ABSPATH')) {
	exit;
}

$animal_id = get_post_meta(get_the_ID(), '_animal_id', true);
$animal    = $animal_id ? get_post($animal_id) : null;

if (!$animal || $animal->post_type !== 'animal') {
	global $wp_query;
	$wp_query->set_404();
	status_header(404);
	nocache_headers();
	include(get_query_template('404'));
	exit;
}

// Récupérer chaque donnée séparément
$nom                = get_field('nom', $animal->ID);
$photo              = get_field('photo_de_lanimal', $animal->ID);
$informations       = get_field('informations_importantes', $animal->ID);
$allergies          = get_field('allergies', $animal->ID);
$telephone          = get_field('telephone', $animal->ID);
$adresse            = get_field('adresse', $animal->ID);
$customer_id        = get_post_meta($animal->ID, '_customer_id', true);
$customer           = $customer_id ? get_user_by('id', $customer_id) : null;
$photo_url          = function_exists('naturapets_get_acf_image_url') ? naturapets_get_acf_image_url($photo, 'medium') : '';

get_header();
?>
<main class="medaillon-public naturapets-animal-page">
	<div class="medaillon-public__card">

		<?php if ($photo_url) : ?>
			<div class="medaillon-public__photo-wrap">
				<img src="<?php echo esc_url($photo_url); ?>" alt="<?php echo esc_attr($nom); ?>" class="medaillon-public__photo" />
			</div>
		<?php endif; ?>

		<h1 class="medaillon-public__title">
			<?php echo $nom ? esc_html($nom) : 'Médaillon'; ?>
		</h1>

		<table class="medaillon-public__table">
			<?php if ($informations) : ?>
				<tr>
					<th class="medaillon-public__th">Informations importantes</th>
					<td class="medaillon-public__td"><?php echo nl2br(esc_html($informations)); ?></td>
				</tr>
			<?php endif; ?>

			<?php if ($allergies) : ?>
				<tr>
					<th class="medaillon-public__th">Allergies</th>
					<td class="medaillon-public__td"><?php echo nl2br(esc_html($allergies)); ?></td>
				</tr>
			<?php endif; ?>

			<?php if ($telephone) : ?>
				<tr>
					<th class="medaillon-public__th">Téléphone</th>
					<td class="medaillon-public__td">
						<a href="tel:<?php echo esc_attr($telephone); ?>" class="medaillon-public__link">
							<?php echo esc_html($telephone); ?>
						</a>
					</td>
				</tr>
			<?php endif; ?>

			<?php if ($adresse && ($adresse['rue'] || $adresse['code_postal'] || $adresse['ville'])) : ?>
				<tr>
					<th class="medaillon-public__th">Adresse</th>
					<td class="medaillon-public__td">
						<?php
						if (!empty($adresse['rue'])) {
							echo esc_html($adresse['rue']) . '<br>';
						}
						if (!empty($adresse['code_postal']) || !empty($adresse['ville'])) {
							echo esc_html(trim(($adresse['code_postal'] ?? '') . ' ' . ($adresse['ville'] ?? '')));
						}
						?>
					</td>
				</tr>
			<?php endif; ?>

			<?php if ($customer) : ?>
				<tr>
					<th class="medaillon-public__th">Propriétaire</th>
					<td class="medaillon-public__td"><?php echo esc_html($customer->display_name); ?></td>
				</tr>
			<?php endif; ?>
		</table>

		<?php if (!$nom && !$informations && !$allergies) : ?>
			<p class="medaillon-public__empty">
				<em>Les informations de ce médaillon n'ont pas encore été renseignées.</em>
			</p>
		<?php endif; ?>

		<p class="medaillon-public__footer">
			Médaillon généré par <?php bloginfo('name'); ?>
		</p>
	</div>
</main>
<?php
// get_footer();
