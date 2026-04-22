/**
 * Editeur : apercu serveur du bloc naturapets/page-featured-image.
 */
(function (blocks, hooks, blockEditor, serverSideRender, element) {
	const { registerBlockType, getBlockType } = blocks;
	const { useBlockProps } = blockEditor;
	const ServerSideRenderComponent = serverSideRender;
	const { createElement: el } = element;

	const blockName = 'naturapets/page-featured-image';

	function edit() {
		const blockProps = useBlockProps({
			className: 'np-page-featured-image-block-editor',
		});

		return el(
			'div',
			blockProps,
			el(ServerSideRenderComponent, {
				block: blockName,
			})
		);
	}

	function save() {
		return null;
	}

	const existing = getBlockType(blockName);

	if (!existing) {
		registerBlockType(blockName, {
			apiVersion: 3,
			title: 'Image mise en avant de la page',
			category: 'naturapets',
			icon: 'format-image',
			description:
				"Affiche l'image mise en avant de la page courante (ou la page Boutique sur l'archive produit).",
			supports: {
				html: false,
				inserter: true,
				anchor: true,
				align: ['wide', 'full'],
				spacing: { margin: true, padding: true },
			},
			edit: edit,
			save: save,
		});
		return;
	}

	hooks.addFilter(
		'blocks.registerBlockType',
		'naturapets/page-featured-image/edit-override',
		function (settings, name) {
			if (name !== blockName) {
				return settings;
			}

			return {
				...settings,
				edit: edit,
				save: save,
			};
		}
	);
})(
	window.wp.blocks,
	window.wp.hooks,
	window.wp.blockEditor,
	window.wp.serverSideRender,
	window.wp.element
);
