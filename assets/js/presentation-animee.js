/**
 * Présentation animée — parallax scroll
 *
 * L'image en arrière-plan se déplace du haut-droit vers le bas-gauche
 * proportionnellement à la progression du bloc dans le viewport.
 *
 * Technique : CSS custom properties --px / --py mises à jour via rAF.
 * Un lerp (interpolation linéaire) adoucit les changements pour éviter
 * tout saut brutal entre frames.
 */
(function () {
	'use strict';

	// Amplitude maximale du déplacement — augmentée pour un bloc pleine largeur
	var RANGE_X = 200; // haut-droit (positif) → bas-gauche (négatif)
	var RANGE_Y = 120; // haut (négatif) → bas (positif)

	// Facteur de lissage (0 = immobile, 1 = instantané)
	var LERP = 0.07;

	function ParallaxBlock(section) {
		this.section = section;
		this.img     = section.querySelector('.js-pres-parallax');
		if (!this.img) return;

		// Valeurs courantes (interpolées)
		this.currentX = RANGE_X;
		this.currentY = -RANGE_Y;

		// Valeurs cibles (calculées depuis le scroll)
		this.targetX  = RANGE_X;
		this.targetY  = -RANGE_Y;

		this.raf = null;
		this.tick = this.tick.bind(this);
		this.onScroll = this.onScroll.bind(this);

		window.addEventListener('scroll', this.onScroll, { passive: true });
		this.onScroll();
		this.startRaf();
	}

	ParallaxBlock.prototype.getProgress = function () {
		var rect     = this.section.getBoundingClientRect();
		var vh       = window.innerHeight;
		// progress = 0 quand le bloc entre dans le bas du viewport
		//           = 1 quand le bas du bloc atteint le haut du viewport
		var progress = (vh - rect.top) / (vh + rect.height);
		return Math.min(1, Math.max(0, progress));
	};

	ParallaxBlock.prototype.onScroll = function () {
		var p       = this.getProgress();
		// Début (p=0) : image en haut-droit  (+RANGE_X, -RANGE_Y)
		// Fin   (p=1) : image en bas-gauche  (-RANGE_X, +RANGE_Y)
		this.targetX = RANGE_X - p * RANGE_X * 2;
		this.targetY = -RANGE_Y + p * RANGE_Y * 2;
	};

	ParallaxBlock.prototype.tick = function () {
		// Interpolation linéaire vers la cible
		this.currentX += (this.targetX - this.currentX) * LERP;
		this.currentY += (this.targetY - this.currentY) * LERP;

		this.img.style.setProperty('--px', this.currentX.toFixed(2) + 'px');
		this.img.style.setProperty('--py', this.currentY.toFixed(2) + 'px');

		this.raf = requestAnimationFrame(this.tick);
	};

	ParallaxBlock.prototype.startRaf = function () {
		if (!this.raf) {
			this.raf = requestAnimationFrame(this.tick);
		}
	};

	function init() {
		var sections = document.querySelectorAll('.np-pres');

		// Respect des préférences de réduction de mouvement (accessibilité)
		if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
			return;
		}

		sections.forEach(function (section) {
			new ParallaxBlock(section);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
