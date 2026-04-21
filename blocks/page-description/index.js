/**
 * Éditeur : aperçu serveur du bloc naturapets/page-description.
 */
(function (blocks, blockEditor, serverSideRender, element) {
	const { registerBlockType, unregisterBlockType, getBlockType } = blocks;
	const { useBlockProps } = blockEditor;
	const ServerSideRenderComponent = serverSideRender;
	const { createElement: el } = element;

	const blockName = 'naturapets/page-description';

	function edit() {
		const blockProps = useBlockProps({
			className: 'np-page-description-block-editor',
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

	/*
	 * WordPress pré-enregistre le bloc depuis block.json avant ce script.
	 * registerBlockType ignore alors l’appel (« already registered ») et l’éditeur reste vide.
	 */
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
			title: 'Description de la page',
			category: 'naturapets',
			icon: 'text-page',
			description:
				'Affiche l’extrait (description) de la page courante (ex. page Boutique).',
			supports: {
				html: false,
				anchor: true,
				align: ['wide', 'full'],
				spacing: { margin: true, padding: true },
				typography: { fontSize: true, lineHeight: true },
				color: { text: true, background: true },
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
