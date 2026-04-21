/**
 * Aperçu des shortcodes dans le bloc « Shortcode » (éditeur).
 * Masque le champ shortcode lorsque l’aperçu HTML est disponible (lien pour réafficher).
 */
(function (wp) {
	const { addFilter } = wp.hooks;
	const { createHigherOrderComponent } = wp.compose;
	const { Fragment, useState, useEffect, createElement: el } = wp.element;
	const { Spinner, Button } = wp.components;
	const { __ } = wp.i18n;

	const MAX_LEN = 10000;

	function ShortcodeWithPreview(props) {
		const BlockEdit = props.BlockEdit;
		const text = (props.attributes && props.attributes.text) || '';
		const trimmed = text.trim();

		const [sourceVisible, setSourceVisible] = useState(!trimmed);
		const [preview, setPreview] = useState('');
		const [loading, setLoading] = useState(false);
		const [error, setError] = useState('');

		useEffect(
			function () {
				if (!trimmed || trimmed.length > MAX_LEN) {
					setPreview('');
					setError('');
					setSourceVisible(true);
					return;
				}
				setLoading(true);
				setError('');
				const data = new window.FormData();
				data.append('action', 'naturapets_shortcode_preview');
				data.append('nonce', window.naturapetsShortcodePreview.nonce);
				data.append('shortcode', trimmed);

				window
					.fetch(window.naturapetsShortcodePreview.ajaxUrl, {
						method: 'POST',
						body: data,
						credentials: 'same-origin',
					})
					.then(function (res) {
						if (!res.ok) {
							throw new Error(String(res.status));
						}
						return res.text();
					})
					.then(function (html) {
						setPreview(html || '');
						if (html && String(html).trim() !== '') {
							setSourceVisible(false);
						} else {
							setSourceVisible(true);
						}
					})
					.catch(function () {
						setPreview('');
						setError(__('Impossible de charger l’aperçu.', 'naturapets'));
						setSourceVisible(true);
					})
					.finally(function () {
						setLoading(false);
					});
			},
			[text]
		);

		return el(
			Fragment,
			null,
			el(
				'div',
				{
					className: 'naturapets-shortcode-editor__source',
					style: {
						display: sourceVisible ? 'block' : 'none',
						overflow: sourceVisible ? 'visible' : 'hidden',
						height: sourceVisible ? 'auto' : 0,
						margin: 0,
						padding: 0,
					},
					'aria-hidden': sourceVisible ? 'false' : 'true',
				},
				el(BlockEdit, props)
			),
			trimmed
				? el(
						'div',
						{
							className: 'naturapets-shortcode-preview',
							style: {
								marginTop: sourceVisible ? '12px' : 0,
								padding: '12px',
								border: '1px dashed #c3c4c7',
								background: '#f6f7f7',
								borderRadius: '2px',
							},
						},
						!sourceVisible &&
							el(
								'div',
								{ style: { marginBottom: '10px' } },
								el(Button, {
									variant: 'link',
									onClick: function () {
										setSourceVisible(true);
									},
								}, __('Modifier le shortcode', 'naturapets'))
							),
						el(
							'div',
							{
								style: {
									fontSize: '11px',
									fontWeight: 600,
									textTransform: 'uppercase',
									color: '#757575',
									marginBottom: '8px',
									letterSpacing: '0.04em',
								},
							},
							__('Aperçu', 'naturapets')
						),
						error
							? el('p', { style: { margin: 0, color: '#b32d2e' } }, error)
							: loading
								? el(Spinner, null)
								: el('div', {
										className: 'naturapets-shortcode-preview__html',
										dangerouslySetInnerHTML: { __html: preview },
									})
					)
				: null
		);
	}

	const withShortcodePreview = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (props.name !== 'core/shortcode') {
				return el(BlockEdit, props);
			}
			return el(ShortcodeWithPreview, Object.assign({}, props, { BlockEdit: BlockEdit }));
		};
	}, 'naturapetsWithShortcodePreview');

	addFilter('editor.BlockEdit', 'naturapets/with-shortcode-preview', withShortcodePreview);
})(window.wp);
