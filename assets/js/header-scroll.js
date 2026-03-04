/**
 * Animation du header au scroll : slide vers le haut après 100px,
 * réapparition quand on scroll vers le haut ou en bas de page.
 */
(function () {
	'use strict';

	const SCROLL_THRESHOLD = 100;
	const BOTTOM_OFFSET = 50;

	function init() {
		const siteBlocks = document.querySelector('.wp-site-blocks');
		const header = siteBlocks ? siteBlocks.querySelector('header') : null;

		if (!header || document.body.classList.contains('medaillon_public-template-default')) {
			return;
		}

		let lastScrollY = window.scrollY || window.pageYOffset;
		let ticking = false;

		function updateHeader() {
			const scrollY = window.scrollY || window.pageYOffset;
			const windowHeight = window.innerHeight;
			const docHeight = document.documentElement.scrollHeight;
			const isAtBottom = scrollY + windowHeight >= docHeight - BOTTOM_OFFSET;
			const isScrollingUp = scrollY < lastScrollY;
			const isAboveThreshold = scrollY <= SCROLL_THRESHOLD;

			const shouldShow = isAboveThreshold || isScrollingUp || isAtBottom;

			if (shouldShow) {
				header.classList.remove('np-header--hidden');
			} else {
				header.classList.add('np-header--hidden');
			}

			lastScrollY = scrollY;
			ticking = false;
		}

		function onScroll() {
			if (!ticking) {
				window.requestAnimationFrame(updateHeader);
				ticking = true;
			}
		}

		window.addEventListener('scroll', onScroll, { passive: true });
		updateHeader();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
