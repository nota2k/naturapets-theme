/**
 * Éditeur : aperçu serveur + contexte produit (postId) quand disponible.
 */
(function (blocks, blockEditor, serverSideRender, element, data) {
	const { registerBlockType, unregisterBlockType, getBlockType } = blocks;
	const { useBlockProps } = blockEditor;
	const ServerSideRenderComponent = serverSideRender;
	const { createElement: el } = element;
	const { useSelect } = data;

	const blockName = 'naturapets/product-gallery';
	const emptyContext = {};

	function edit() {
		const blockProps = useBlockProps({
			className: 'np-product-gallery-block-editor',
		});

		const previewContext = useSelect(function (select) {
			try {
				const editor = select('core/editor');
				if (editor && editor.getCurrentPostId && editor.getCurrentPostType) {
					const postId = editor.getCurrentPostId();
					const postType = editor.getCurrentPostType();
					if (postId && postType) {
						return { postId: postId, postType: postType };
					}
				}
			} catch (e) {
				// Éditeur de site : pas de store core/editor.
			}
			return emptyContext;
		}, []);

		return el(
			'div',
			blockProps,
			el(ServerSideRenderComponent, {
				block: blockName,
				context: previewContext,
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
			title: 'Galerie produit',
			category: 'naturapets',
			icon: 'format-gallery',
			description:
				'Galerie Figma : image principale, bandeau, grande vue. Images du produit sur une fiche WooCommerce.',
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
	window.wp.element,
	window.wp.data
);
