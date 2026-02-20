/**
 * Moveblock – Applique les animations GSAP configurées via le bloc.
 * Cherche tous les .moveblock-config et exécute l’effet sur l’élément ciblé.
 */
(function () {
	'use strict';

	var GSAP_EFFECTS = {
		fadeIn:          { method: 'from', vars: { opacity: 0 } },
		fadeOut:         { method: 'to', vars: { opacity: 0 } },
		fadeFromTo:      { method: 'fromTo', vars: { opacity: 1 }, fromVars: { opacity: 0 } },
		slideInLeft:     { method: 'from', vars: { x: -100, opacity: 0 } },
		slideInRight:    { method: 'from', vars: { x: 100, opacity: 0 } },
		slideInTop:      { method: 'from', vars: { y: -100, opacity: 0 } },
		slideInBottom:   { method: 'from', vars: { y: 100, opacity: 0 } },
		slideOutLeft:    { method: 'to', vars: { x: -100, opacity: 0 } },
		slideOutRight:   { method: 'to', vars: { x: 100, opacity: 0 } },
		moveX:           { method: 'to', vars: { x: 100 } },
		moveY:           { method: 'to', vars: { y: 100 } },
		scaleUp:        { method: 'from', vars: { scale: 0, opacity: 0 } },
		scaleDown:      { method: 'to', vars: { scale: 0, opacity: 0 } },
		scaleFromTo:    { method: 'fromTo', vars: { scale: 1 }, fromVars: { scale: 0 } },
		scaleX:         { method: 'to', vars: { scaleX: 1.2 } },
		scaleY:         { method: 'to', vars: { scaleY: 1.2 } },
		rotateIn:       { method: 'from', vars: { rotation: -180, opacity: 0 } },
		rotateOut:      { method: 'to', vars: { rotation: 180, opacity: 0 } },
		rotateY:        { method: 'from', vars: { rotationY: -90, opacity: 0 } },
		rotateX:        { method: 'from', vars: { rotationX: -90, opacity: 0 } },
		skewIn:         { method: 'from', vars: { skewX: 20, skewY: 20, opacity: 0 } },
		skewOut:        { method: 'to', vars: { skewX: 10, skewY: 10 } },
		popIn:          { method: 'from', vars: { scale: 0, opacity: 0 } },
		blurIn:         { method: 'from', vars: { filter: 'blur(10px)', opacity: 0 } },
		blurOut:        { method: 'to', vars: { filter: 'blur(10px)', opacity: 0 } },
		dropIn:         { method: 'from', vars: { y: -200, opacity: 0 } },
		bounceIn:       { method: 'from', vars: { y: 80, opacity: 0 } },
		colorChange:    { method: 'to', vars: { color: '#ff6b6b' } },
		backgroundColor:{ method: 'to', vars: { backgroundColor: '#4ecdc4' } }
	};

	function getData(el, key) {
		var attr = el.getAttribute('data-moveblock-' + key);
		return attr !== null ? attr : '';
	}

	function applyMoveblock(configEl) {
		if (typeof gsap === 'undefined') return;
		var effectId = getData(configEl, 'effect');
		var effect = GSAP_EFFECTS[effectId];
		if (!effect) return;
		var selector = getData(configEl, 'selector');
		if (!selector) return;
		var duration = parseFloat(getData(configEl, 'duration')) || 1;
		var delay = parseFloat(getData(configEl, 'delay')) || 0;
		var ease = getData(configEl, 'ease') || 'power2.out';
		var stagger = parseFloat(getData(configEl, 'stagger')) || 0;
		var targets = document.querySelectorAll(selector);
		if (!targets.length) return;
		var vars = {};
		var k;
		for (k in effect.vars) if (effect.vars.hasOwnProperty(k)) vars[k] = effect.vars[k];
		vars.duration = duration;
		vars.delay = delay;
		vars.ease = ease;
		if (stagger > 0) vars.stagger = stagger;
		if (effect.method === 'fromTo' && effect.fromVars) {
			gsap.fromTo(targets, effect.fromVars, vars);
		} else if (effect.method === 'from') {
			gsap.from(targets, vars);
		} else {
			gsap.to(targets, vars);
		}
	}

	function run() {
		var list = document.querySelectorAll('.moveblock-config');
		for (var i = 0; i < list.length; i++) {
			applyMoveblock(list[i]);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', run);
	} else {
		run();
	}
})();
