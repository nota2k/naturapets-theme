/**
 * Panneau Document : description (extrait) lorsque le modèle de page « Boutique » est actif.
 */
(function (wp) {
	const { registerPlugin } = wp.plugins;
	const { PluginDocumentSettingPanel } = wp.editPost;
	const { useSelect, useDispatch } = wp.data;
	const { __ } = wp.i18n;
	const { TextareaControl } = wp.components;
	const el = wp.element.createElement;

	function isBoutiqueTemplate(template) {
		if (!template || typeof template !== 'string') {
			return false;
		}
		return (
			template === 'page-boutique' ||
			template.indexOf('page-boutique') !== -1
		);
	}

	function ShopPageDescriptionPanel() {
		const template = useSelect(function (select) {
			return select('core/editor').getEditedPostAttribute('template') || '';
		}, []);

		const excerpt = useSelect(function (select) {
			return select('core/editor').getEditedPostAttribute('excerpt') || '';
		}, []);

		const { editPost } = useDispatch('core/editor');

		if (!isBoutiqueTemplate(template)) {
			return null;
		}

		return el(
			PluginDocumentSettingPanel,
			{
				name: 'naturapets-shop-page-description',
				title: __('Description (boutique)', 'naturapets'),
				className: 'naturapets-shop-description-panel',
			},
			el(TextareaControl, {
				label: __(
					'Texte utilisé par le bloc « Description de la page »',
					'naturapets'
				),
				help: __(
					'Correspond à l’extrait WordPress. Ce texte peut être affiché sur le site avec le bloc « Description de la page ».',
					'naturapets'
				),
				value: excerpt,
				onChange: function (value) {
					editPost({ excerpt: value });
				},
				rows: 6,
			})
		);
	}

	registerPlugin('naturapets-shop-page-description', {
		render: ShopPageDescriptionPanel,
	});
})(window.wp);
