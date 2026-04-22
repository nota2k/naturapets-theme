(function (blocks, blockEditor, element) {
	const { registerBlockType, unregisterBlockType, getBlockType } = blocks;
	const { useBlockProps } = blockEditor;
	const { createElement: el } = element;

	const blockName = 'naturapets/mini-add-to-cart';

	function edit() {
		const blockProps = useBlockProps({
			className: 'np-mini-cart-editor',
			style: {
				display: 'inline-flex',
				alignItems: 'center',
				justifyContent: 'center',
				width: '42px',
				height: '42px',
				borderRadius: '50%',
				backgroundColor: 'var(--wp--preset--color--secondary, #3D6B4F)',
				color: '#fff',
				cursor: 'default',
			},
		});

		return el(
			'div',
			blockProps,
			el('svg', {
				width: 20,
				height: 20,
				viewBox: '0 0 20 20',
				fill: 'none',
				xmlns: 'http://www.w3.org/2000/svg',
			},
				el('line', { x1: 10, y1: 3, x2: 10, y2: 17, stroke: 'currentColor', strokeWidth: 2.2, strokeLinecap: 'round' }),
				el('line', { x1: 3, y1: 10, x2: 17, y2: 10, stroke: 'currentColor', strokeWidth: 2.2, strokeLinecap: 'round' })
			)
		);
	}

	function save() {
		return null;
	}

	const existing = getBlockType(blockName);
	if (existing) {
		const { edit: _e, save: _s, ...rest } = existing;
		unregisterBlockType(blockName);
		registerBlockType(blockName, { ...rest, edit: edit, save: save });
	} else {
		registerBlockType(blockName, {
			apiVersion: 3,
			title: 'Mini Ajout Panier',
			category: 'woocommerce',
			icon: 'cart',
			description: 'Bouton circulaire "+" d\'ajout au panier pour les boucles produit.',
			usesContext: ['postId', 'postType'],
			supports: { html: false, align: ['left', 'center', 'right'], multiple: false },
			edit: edit,
			save: save,
		});
	}
})(
	window.wp.blocks,
	window.wp.blockEditor,
	window.wp.element
);
