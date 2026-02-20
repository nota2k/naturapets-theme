<?php
/**
 * Template de rendu du bloc Témoignage (design Figma node 3-56)
 * Cercle photo à gauche, citation + auteur à droite, guillemets verts.
 *
 * @package Naturapets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$photo    = get_field( 'testimonial_photo' );
$citation = get_field( 'testimonial_citation' );
$auteur   = get_field( 'testimonial_auteur' );

if ( empty( $citation ) ) {
	$citation = __( "J'ai retrouvé facilement Rantanplan et j'en suis très rassurée!", 'naturapets' );
}
if ( empty( $auteur ) ) {
	$auteur = 'Liliane';
}

$photo_url = naturapets_get_acf_image_url( $photo, 'large' );
?>
<section class="np-testimonial" aria-label="<?php esc_attr_e( 'Témoignage', 'naturapets' ); ?>">
	<div class="np-testimonial__inner">
		<div class="np-testimonial__photo-wrap">
			<?php if ( $photo_url ) : ?>
				<img class="np-testimonial__photo" src="<?php echo esc_url( $photo_url ); ?>" alt="" loading="lazy" />
			<?php endif; ?>
		</div>
		<div class="np-testimonial__content">
			<span class="np-testimonial__quote np-testimonial__quote--open" aria-hidden="true">&#8223;</span>
			<blockquote class="np-testimonial__blockquote">
				<p class="np-testimonial__citation"><?php echo nl2br( esc_html( $citation ) ); ?></p>
				<cite class="np-testimonial__author"><?php echo esc_html( $auteur ); ?></cite>
			</blockquote>
			<span class="np-testimonial__quote np-testimonial__quote--close" aria-hidden="true">&#8223;</span>
		</div>
	</div>
</section>
