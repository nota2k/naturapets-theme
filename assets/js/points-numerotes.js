/**
 * Points Numérotés — animation au scroll
 * Chaque item apparaît individuellement quand il entre dans le viewport.
 * Le délai CSS `--delay` crée un effet de cascade subtil entre les items proches.
 */
(function () {
	'use strict';

	function init() {
		var items = document.querySelectorAll('.np-points__item[data-scroll-reveal]');

		if (!items.length) return;

		// Fallback : si IntersectionObserver non supporté, tout afficher
		if (!('IntersectionObserver' in window)) {
			items.forEach(function (el) {
				el.classList.add('is-visible');
			});
			return;
		}

		var observer = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						entry.target.classList.add('is-visible');
						// On arrête d'observer une fois visible (animation one-shot)
						observer.unobserve(entry.target);
					}
				});
			},
			{
				threshold: 0.35, // se déclenche quand 15% de l'item est visible
				rootMargin: '0px 0px -40px 0px', // légèrement avant le bas du viewport
			}
		);

		items.forEach(function (el) {
			observer.observe(el);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
