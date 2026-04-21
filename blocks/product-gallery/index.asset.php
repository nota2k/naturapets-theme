<?php
/**
 * Dépendances du script éditeur — bloc Galerie produit.
 *
 * @package naturapets
 */

return array(
	'dependencies' => array(
		'wp-blocks',
		'wp-element',
		'wp-block-editor',
		'wp-server-side-render',
		'wp-data',
	),
	'version'      => file_exists( __DIR__ . '/index.js' ) ? (string) filemtime( __DIR__ . '/index.js' ) : '1.0.0',
);
