/**
 * Carousel de texte — scroll-driven horizontal
 *
 * Technique : la section est plus haute que le viewport (hauteur réservée).
 * L'inner div est sticky. On mesure la progression du scroll dans la section
 * et on traduit en translateX sur le track des cartes.
 */
(function () {
  'use strict';

  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  function initCarousel(section) {
    const sticky  = section.querySelector('.np-carousel-texte__sticky');
    const track   = section.querySelector('.np-carousel-texte__track');
    const bar     = section.querySelector('.np-carousel-texte__progress-bar');

    if (!sticky || !track) return;

    let currentX = 0;
    let targetX  = 0;
    let rafId    = null;

    function getMaxScroll() {
      // S'arrête quand le centre de la dernière carte est au centre du sticky.
      // Cela laisse le scroll général reprendre dès que la card est centrée.
      const cards    = track.querySelectorAll('.np-carousel-texte__card');
      const wrapW    = sticky.clientWidth;
      if (!cards.length) return 0;
      const last     = cards[cards.length - 1];
      const center   = last.offsetLeft + last.offsetWidth / 2;
      return Math.max(0, center - wrapW / 2);
    }

    function getProgress() {
      const rect       = section.getBoundingClientRect();
      const sectionH   = section.offsetHeight;
      const viewportH  = window.innerHeight;
      const scrolled   = -rect.top;
      const scrollable = sectionH - viewportH;
      return Math.min(1, Math.max(0, scrolled / scrollable));
    }

    function tick() {
      const maxScroll = getMaxScroll();
      const progress  = getProgress();
      targetX = -(Math.min(progress, 1) * maxScroll);

      // Lerp pour fluidité
      currentX += (targetX - currentX) * 0.1;

      // Snap au pixel près pour éviter le sub-pixel flicker
      const tx = Math.round(currentX * 100) / 100;
      track.style.transform = `translateX(${tx}px)`;

      if (bar) {
        // Barre : basée sur l'avancement de l'animation (pas du scroll global)
        const animProgress = maxScroll > 0 ? Math.min(1, Math.abs(currentX) / maxScroll) : 1;
        bar.style.width = `${animProgress * 100}%`;
      }

      rafId = requestAnimationFrame(tick);
    }

    // IntersectionObserver : lance rAF uniquement quand la section est visible
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            if (!rafId) rafId = requestAnimationFrame(tick);
          } else {
            if (rafId) {
              cancelAnimationFrame(rafId);
              rafId = null;
            }
          }
        });
      },
      { threshold: 0 }
    );

    observer.observe(section);
  }

  function init() {
    document.querySelectorAll('[data-carousel-scroll]').forEach(initCarousel);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
