<?php
/**
 * Modèle quasi vierge pour la lecture d'un médaillon public.
 * Les données sont bindées via l'ID du post medaillon_public.
 *
 * @package Naturapets
 */

if (!defined('ABSPATH')) {
	exit;
}

$vm = get_query_var('naturapets_medaillon_view_model', array());

$public_post_id = isset($vm['public_post_id']) ? (int) $vm['public_post_id'] : 0;
$title = isset($vm['title']) ? (string) $vm['title'] : __('Médaillon', 'naturapets');
$is_claimable = !empty($vm['is_claimable']);
$is_user_logged_in = !empty($vm['is_user_logged_in']);
$claimable_count = isset($vm['claimable_count']) ? (int) $vm['claimable_count'] : 0;
$activation_error = isset($vm['activation_error']) ? (string) $vm['activation_error'] : '';
$medaillon_code = isset($vm['medaillon_code']) ? (string) $vm['medaillon_code'] : '';
$medaillon_status = isset($vm['medaillon_status']) ? (string) $vm['medaillon_status'] : 'available';
$data = isset($vm['data']) && is_array($vm['data']) ? $vm['data'] : array();
$adresse = isset($data['adresse']) && is_array($data['adresse']) ? $data['adresse'] : array();
$adresse_ligne_1 = isset($adresse['rue']) ? (string) $adresse['rue'] : '';
$adresse_ligne_2 = trim(
	(isset($adresse['code_postal']) ? (string) $adresse['code_postal'] : '') . ' ' .
	(isset($adresse['ville']) ? (string) $adresse['ville'] : '')
);

?>
<main class="np-medaillon-read np-medaillon-read--blank"
	data-medaillon-id="<?php echo esc_attr($public_post_id); ?>"
	data-medaillon-code="<?php echo esc_attr($medaillon_code); ?>"
	data-medaillon-status="<?php echo esc_attr($medaillon_status); ?>">

	<section class="np-medaillon-read__container"
		data-animal-id="<?php echo esc_attr(isset($data['animal_id']) ? (int) $data['animal_id'] : 0); ?>">
		<div class="np-medaillon-read__head">
			<?php if ($medaillon_code) : ?>
				<p class="np-medaillon-read__code"><?php echo esc_html($medaillon_code); ?></p>
			<?php endif; ?>
			<p class="np-medaillon-read__badge">
				<?php echo esc_html('assigned' === $medaillon_status ? __('Activé', 'naturapets') : __('Disponible', 'naturapets')); ?>
			</p>
		</div>
		<h1 class="np-medaillon-read__title"><?php echo esc_html($title); ?></h1>

		<?php if ($is_claimable) : ?>
			<div class="np-claim-form">
				<p class="np-claim-form__intro"><?php esc_html_e('Ce médaillon n’est pas encore relié à un compte.', 'naturapets'); ?></p>

				<?php if ($activation_error) : ?>
					<p class="np-claim-form__alert"><strong><?php echo esc_html($activation_error); ?></strong></p>
				<?php endif; ?>

				<?php if (!$is_user_logged_in) : ?>
					<a class="np-claim-form__button np-claim-form__button--secondary" href="<?php echo esc_url(isset($vm['login_url']) ? (string) $vm['login_url'] : wp_login_url(get_permalink($public_post_id))); ?>">
						<?php esc_html_e('Se connecter pour activer', 'naturapets'); ?>
					</a>
				<?php elseif ($claimable_count <= 0) : ?>
					<p class="np-claim-form__alert"><?php esc_html_e('Aucun médaillon à activer sur votre compte.', 'naturapets'); ?></p>
				<?php else : ?>
					<p class="np-claim-form__count"><?php echo esc_html(sprintf(_n('%d médaillon activable', '%d médaillons activables', $claimable_count, 'naturapets'), $claimable_count)); ?></p>
					<form method="post" class="np-claim-form__form">
						<?php
						wp_nonce_field(
							isset($vm['claim_nonce_action']) ? (string) $vm['claim_nonce_action'] : '',
							isset($vm['claim_nonce_name']) ? (string) $vm['claim_nonce_name'] : 'naturapets_claim_nonce'
						);
						?>
						<label class="np-claim-form__field">
							<span><?php esc_html_e('Code médaillon', 'naturapets'); ?></span>
							<input type="text" value="<?php echo esc_attr($medaillon_code ?: (string) $public_post_id); ?>" readonly />
						</label>
						<button type="submit" class="np-claim-form__button np-claim-form__button--primary" name="naturapets_claim_medaillon" value="1">
							<?php esc_html_e('Connecter ce médaillon à mon compte', 'naturapets'); ?>
						</button>
					</form>
				<?php endif; ?>
			</div>
		<?php else : ?>
			<?php if (!empty($data['photo_url'])) : ?>
				<p class="np-medaillon-read__media"><img src="<?php echo esc_url((string) $data['photo_url']); ?>" alt="<?php echo esc_attr($title); ?>" /></p>
			<?php endif; ?>
			<div class="np-medaillon-read__details">
				<?php if (!empty($data['nom'])) : ?>
					<p><strong><?php esc_html_e('Nom de l’animal', 'naturapets'); ?> :</strong> <?php echo esc_html((string) $data['nom']); ?></p>
				<?php endif; ?>

				<?php if (!empty($data['race'])) : ?>
					<p><strong><?php esc_html_e('Race', 'naturapets'); ?> :</strong> <?php echo esc_html((string) $data['race']); ?></p>
				<?php endif; ?>

				<?php if (!empty($data['type_animal_label'])) : ?>
					<p><strong><?php esc_html_e('Type', 'naturapets'); ?> :</strong> <?php echo esc_html((string) $data['type_animal_label']); ?></p>
				<?php endif; ?>

				<?php if (!empty($data['age'])) : ?>
					<p><strong><?php esc_html_e('Âge', 'naturapets'); ?> :</strong> <?php echo esc_html((string) $data['age']); ?></p>
				<?php endif; ?>

				<?php if (!empty($data['allergies'])) : ?>
					<p><strong><?php esc_html_e('Allergies', 'naturapets'); ?> :</strong> <?php echo nl2br(esc_html((string) $data['allergies'])); ?></p>
				<?php endif; ?>

				<?php if (!empty($data['telephone'])) : ?>
					<p>
						<strong><?php esc_html_e('Téléphone', 'naturapets'); ?> :</strong>
						<a href="tel:<?php echo esc_attr((string) $data['telephone']); ?>"><?php echo esc_html((string) $data['telephone']); ?></a>
					</p>
				<?php endif; ?>

				<?php if ($adresse_ligne_1 || $adresse_ligne_2) : ?>
					<p>
						<strong><?php esc_html_e('Adresse', 'naturapets'); ?> :</strong><br>
						<?php if ($adresse_ligne_1) : ?>
							<?php echo esc_html($adresse_ligne_1); ?><br>
						<?php endif; ?>
						<?php if ($adresse_ligne_2) : ?>
							<?php echo esc_html($adresse_ligne_2); ?>
						<?php endif; ?>
					</p>
				<?php endif; ?>

				<?php if (!empty($data['owner_name'])) : ?>
					<p><strong><?php esc_html_e('Propriétaire', 'naturapets'); ?> :</strong> <?php echo esc_html((string) $data['owner_name']); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</section>
</main>
