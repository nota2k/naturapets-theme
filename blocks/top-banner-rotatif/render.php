<?php
/**
 * Bloc : bandeau du haut avec messages rotatifs (ACF).
 */

$raw = array(
	get_field( 'phrase_1' ),
	get_field( 'phrase_2' ),
	get_field( 'phrase_3' ),
);

$phrases = array();
foreach ( $raw as $line ) {
	if ( ! is_string( $line ) ) {
		continue;
	}
	$trimmed = trim( $line );
	if ( '' !== $trimmed ) {
		$phrases[] = $trimmed;
	}
}

$count = count( $phrases );

if ( 0 === $count ) {
	return;
}

$count_class = 'np-top-banner--count-' . min( 3, max( 1, $count ) );
$modifier    = ( $count > 1 ) ? ' np-top-banner--rotating' : '';

$wrapper_args = array(
	'class' => 'np-top-banner' . $modifier . ' ' . $count_class,
	'role'  => 'complementary',
);
if ( $count > 1 ) {
	$wrapper_args['aria-label'] = implode( ' · ', $phrases );
}

$wrapper_attributes = get_block_wrapper_attributes( $wrapper_args );
?>
<div <?php echo $wrapper_attributes; ?>>
<?php if ( 1 === $count ) : ?>
	<p class="np-top-banner__text"><?php echo esc_html( $phrases[0] ); ?></p>
<?php else : ?>
	<div class="np-top-banner__viewport" aria-hidden="true">
		<div class="np-top-banner__track">
			<?php foreach ( $phrases as $phrase ) : ?>
				<p class="np-top-banner__line"><?php echo esc_html( $phrase ); ?></p>
			<?php endforeach; ?>
			<?php foreach ( $phrases as $phrase ) : ?>
				<p class="np-top-banner__line"><?php echo esc_html( $phrase ); ?></p>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif; ?>
</div>
