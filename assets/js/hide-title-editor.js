(function () {
	'use strict';

	if (typeof wp === 'undefined' || !wp.plugins || !wp.editPost) {
		return;
	}

	var registerPlugin = wp.plugins.registerPlugin;
	var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
	var createElement = wp.element.createElement;
	var CheckboxControl = wp.components.CheckboxControl;
	var useSelect = wp.data.useSelect;
	var useDispatch = wp.data.useDispatch;
	var __ = wp.i18n.__;

	function HideTitlePanel() {
		var postType = useSelect(function (select) {
			return select('core/editor').getCurrentPostType();
		}, []);

		if (postType !== 'page') {
			return null;
		}

		var meta = useSelect(function (select) {
			return select('core/editor').getEditedPostAttribute('meta') || {};
		}, []);

		var editPost = useDispatch('core/editor').editPost;

		var hideTitle = meta.naturapets_hide_page_title === true || meta.naturapets_hide_page_title === '1' || meta.naturapets_hide_page_title === undefined;

		function onChange(value) {
			editPost({
				meta: Object.assign({}, meta, {
					naturapets_hide_page_title: value
				})
			});
		}

		return createElement(
			PluginDocumentSettingPanel,
			{
				name: 'naturapets-hide-title',
				title: __('Page', 'naturapets'),
				className: 'naturapets-hide-title-panel'
			},
			createElement(
				CheckboxControl,
				{
					label: __('Afficher le titre de la page', 'naturapets'),
					checked: !hideTitle,
					onChange: function (checked) {
						onChange(!checked);
					}
				}
			)
		);
	}

	registerPlugin('naturapets-hide-title', {
		render: HideTitlePanel,
		icon: 'visibility'
	});
})();
