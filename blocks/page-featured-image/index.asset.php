<?php
/**
 * Dependances du script editeur du bloc (convention WordPress).
 *
 * @package naturapets
 */

return array(
	'dependencies' => array(
		'wp-blocks',
		'wp-element',
		'wp-hooks',
		'wp-block-editor',
		'wp-server-side-render',
	),
	'version'      => file_exists(__DIR__ . '/index.js') ? (string) filemtime(__DIR__ . '/index.js') : '1.0.0',
);
