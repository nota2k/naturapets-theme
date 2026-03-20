/**
 * Animation du header au scroll — slide vers le haut à la descente,
 * réapparition à la remontée ou en bas de page.
 *
 * Implémentation : on change la propriété CSS `--header-offset` (translateY)
 * directement sur la variable, sans ajouter de classe portant un transform,
 * pour ne pas créer de nouveau contexte de positionnement pour les enfants
 * `position: fixed` (ex. nav overlay du menu mobile).
 */
(function () {
  'use strict';

  const HIDE_AFTER   = 80;   // px scrollés avant de masquer
  const BOTTOM_DELTA = 60;   // px avant le bas pour faire réapparaître

  function init() {
    const siteBlocks = document.querySelector('.wp-site-blocks');
    const header     = siteBlocks ? siteBlocks.querySelector(':scope > header') : null;

    if (!header || document.body.classList.contains('medaillon_public-template-default')) {
      return;
    }

    let lastY   = window.scrollY;
    let ticking = false;
    let hidden  = false;

    function setHidden(shouldHide) {
      if (shouldHide === hidden) return;
      hidden = shouldHide;
      header.classList.toggle('np-header--hidden', shouldHide);
    }

    function update() {
      // Ne jamais masquer le header lorsque le menu mobile est ouvert
      if (document.body.classList.contains('np-mobile-menu-open')) {
        setHidden(false);
        lastY   = window.scrollY;
        ticking = false;
        return;
      }

      const y          = window.scrollY;
      const atTop      = y <= HIDE_AFTER;
      const atBottom   = y + window.innerHeight >= document.documentElement.scrollHeight - BOTTOM_DELTA;
      const scrollingUp = y < lastY;

      if (atTop || atBottom || scrollingUp) {
        setHidden(false);
      } else {
        setHidden(true);
      }

      lastY   = y;
      ticking = false;
    }

    window.addEventListener('scroll', () => {
      if (!ticking) {
        requestAnimationFrame(update);
        ticking = true;
      }
    }, { passive: true });

    update();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
