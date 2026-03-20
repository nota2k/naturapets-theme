/**
 * FAQ Accordéon – naturapets
 * Gère l'ouverture/fermeture des items FAQ.
 */
(function () {
	'use strict';

	function initFaq(container) {
		var buttons = container.querySelectorAll('.np-faq__question');

		buttons.forEach(function (btn) {
			btn.addEventListener('click', function () {
				var item   = btn.closest('.np-faq__item');
				var isOpen = item.classList.contains('np-faq__item--open');

				// Fermer tous les items du même bloc
				container.querySelectorAll('.np-faq__item').forEach(function (el) {
					el.classList.remove('np-faq__item--open');
					el.querySelector('.np-faq__question').setAttribute('aria-expanded', 'false');
				});

				// Ouvrir l'item cliqué (si ce n'était pas déjà ouvert)
				if (!isOpen) {
					item.classList.add('np-faq__item--open');
					btn.setAttribute('aria-expanded', 'true');
				}
			});
		});
	}

	function init() {
		document.querySelectorAll('.np-faq').forEach(initFaq);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
