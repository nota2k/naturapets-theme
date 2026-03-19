document.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('.np-header-inner');
    const navBlock = document.querySelector('.np-header-nav');

    if (!header || !navBlock) return;

    // Créer notre propre bouton hamburger
    const toggleBtn = document.createElement('button');
    toggleBtn.className = 'np-hamburger-toggle';
    toggleBtn.setAttribute('aria-label', 'Menu');
    toggleBtn.setAttribute('aria-expanded', 'false');
    
    // Ajout des 3 traits pour le burger SVG ou CSS (on utilise du CSS pour l'animation)
    toggleBtn.innerHTML = `
        <span class="np-hamburger-line top"></span>
        <span class="np-hamburger-line middle"></span>
        <span class="np-hamburger-line bottom"></span>
    `;

    // Ajouter le bouton dans le header
    header.appendChild(toggleBtn);

    // Gérer l'état d'ouverture / fermeture 
    toggleBtn.addEventListener('click', () => {
        const isExpanded = toggleBtn.getAttribute('aria-expanded') === 'true';
        toggleBtn.setAttribute('aria-expanded', !isExpanded);
        
        if (!isExpanded) {
            // Ouverture
            toggleBtn.classList.add('is-active');
            document.body.classList.add('np-mobile-menu-open');
        } else {
            // Fermeture
            toggleBtn.classList.remove('is-active');
            document.body.classList.remove('np-mobile-menu-open');
        }
    });

    // Optionnel : fermer le menu lors du clic sur un lien du menu
    const menuLinks = navBlock.querySelectorAll('a');
    menuLinks.forEach((link) => {
        link.addEventListener('click', () => {
            toggleBtn.setAttribute('aria-expanded', 'false');
            toggleBtn.classList.remove('is-active');
            document.body.classList.remove('np-mobile-menu-open');
        });
    });
});
