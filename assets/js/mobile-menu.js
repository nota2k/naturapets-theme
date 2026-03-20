document.addEventListener('DOMContentLoaded', () => {
    const header   = document.querySelector('.np-header-inner');
    const navBlock = document.querySelector('.np-header-nav');

    if (!header || !navBlock) return;

    // Créer notre propre bouton hamburger
    const toggleBtn = document.createElement('button');
    toggleBtn.className = 'np-hamburger-toggle';
    toggleBtn.setAttribute('aria-label', 'Menu');
    toggleBtn.setAttribute('aria-expanded', 'false');

    toggleBtn.innerHTML = `
        <span class="np-hamburger-line top"></span>
        <span class="np-hamburger-line middle"></span>
        <span class="np-hamburger-line bottom"></span>
    `;

    header.appendChild(toggleBtn);

    function openMenu() {
        // Mesure la position courante du bouton avant qu'il soit masqué par la nav
        const rect  = toggleBtn.getBoundingClientRect();
        const right = window.innerWidth - rect.right;

        // Bascule en position: fixed pour échapper au contexte d'empilement du header
        // (la nav fixed a z-index: 100000 en contexte racine ; le bouton doit être au-dessus)
        toggleBtn.style.cssText = `
            position: fixed !important;
            top: ${rect.top}px;
            right: ${right}px;
            left: auto;
            z-index: 100002 !important;
        `;

        toggleBtn.setAttribute('aria-expanded', 'true');
        toggleBtn.classList.add('is-active');
        document.body.classList.add('np-mobile-menu-open');
    }

    function closeMenu() {
        // Remet le bouton dans le flux normal du header
        toggleBtn.style.cssText = '';

        toggleBtn.setAttribute('aria-expanded', 'false');
        toggleBtn.classList.remove('is-active');
        document.body.classList.remove('np-mobile-menu-open');
    }

    toggleBtn.addEventListener('click', () => {
        if (toggleBtn.getAttribute('aria-expanded') === 'true') {
            closeMenu();
        } else {
            openMenu();
        }
    });

    // Fermer au clic sur un lien du menu
    navBlock.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', closeMenu);
    });

    // Fermer avec Echap
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && document.body.classList.contains('np-mobile-menu-open')) {
            closeMenu();
        }
    });
});
