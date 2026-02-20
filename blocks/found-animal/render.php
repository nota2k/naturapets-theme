<?php
/**
 * Template de rendu du bloc J'ai trouvé un animal (design Figma node 3-29)
 * Fond vert menthe, titre + champ médaillon + bouton Chercher.
 *
 * @package Naturapets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titre           = get_field( 'found_animal_titre' );
$placeholder     = get_field( 'found_animal_placeholder' );
$bouton_texte   = get_field( 'found_animal_bouton_texte' );
$form_action_url = get_field( 'found_animal_form_action_url' );
$name_medaillon  = get_field( 'found_animal_param_name' );

if ( empty( $titre ) ) {
	$titre = __( "J'ai trouvé un animal", 'naturapets' );
}
if ( empty( $placeholder ) ) {
	$placeholder = __( 'entrer le numéro du médaillon', 'naturapets' );
}
if ( empty( $bouton_texte ) ) {
	$bouton_texte = __( 'Chercher', 'naturapets' );
}
if ( empty( $form_action_url ) ) {
	$form_action_url = home_url( '/' );
}
if ( empty( $name_medaillon ) ) {
	$name_medaillon = 'medaillon';
}
?>
<section class="np-found-animal" aria-label="<?php esc_attr_e( "Recherche par numéro de médaillon", 'naturapets' ); ?>">
	<div class="np-found-animal__inner">
		<form class="np-found-animal__form" action="<?php echo esc_url( $form_action_url ); ?>" method="get" role="search">
			<label for="np-found-animal-input" class="np-found-animal__label">
				<?php echo esc_html( $titre ); ?>
			</label>
			<input
				id="np-found-animal-input"
				class="np-found-animal__input"
				type="text"
				name="<?php echo esc_attr( $name_medaillon ); ?>"
				placeholder="<?php echo esc_attr( $placeholder ); ?>"
				autocomplete="off"
				aria-label="<?php echo esc_attr( $placeholder ); ?>"
			/>
			<button type="submit" class="np-found-animal__btn">
				<?php echo esc_html( $bouton_texte ); ?>
			</button>
		</form>
	</div>
</section>
