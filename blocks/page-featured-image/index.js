/**
 * Éditeur : aperçu serveur du bloc naturapets/page-featured-image.
 */
(function (blocks, blockEditor, serverSideRender, element) {
	const { registerBlockType, unregisterBlockType, getBlockType } = blocks;
	const { useBlockProps, InspectorControls } = blockEditor;
	const ServerSideRenderComponent = serverSideRender;
	const { createElement: el } = element;
	const { PanelBody, RangeControl } = window.wp.components;
	const { __ } = window.wp.i18n;

	const blockName = 'naturapets/page-featured-image';

	function edit(props) {
		const blockProps = useBlockProps({
			className: 'np-page-featured-image-block-editor',
		});

		return el(
			'div',
			blockProps,
			el(
				InspectorControls,
				null,
				el(
					PanelBody,
					{ title: __('Overlay', 'naturapets'), initialOpen: true },
					el(RangeControl, {
						label: __('Opacité du voile (%)', 'naturapets'),
						value: props.attributes.dimRatio ?? 60,
						onChange: function (next) {
							props.setAttributes({ dimRatio: Number(next) || 0 });
						},
						min: 0,
						max: 100,
					})
				)
			),
			el(ServerSideRenderComponent, {
				block: blockName,
				attributes: props.attributes,
			})
		);
	}

	function save() {
		return null;
	}

	const existing = getBlockType(blockName);
	if (existing) {
		const { edit: _e, save: _s, ...rest } = existing;
		unregisterBlockType(blockName);
		registerBlockType(blockName, {
			...rest,
			edit: edit,
			save: save,
		});
	} else {
		registerBlockType(blockName, {
			apiVersion: 3,
			title: 'Image mise en avant de la page',
			category: 'naturapets',
			icon: 'format-image',
			description:
				'Affiche l’image mise en avant de la page de contexte, sans dépendre de la boucle produits.',
			attributes: {
				dimRatio: { type: 'number', default: 60 },
			},
			supports: {
				html: false,
				anchor: true,
				align: ['wide', 'full'],
				spacing: { margin: true, padding: true },
			},
			edit: edit,
			save: save,
		});
	}
})(
	window.wp.blocks,
	window.wp.blockEditor,
	window.wp.serverSideRender,
	window.wp.element
);
