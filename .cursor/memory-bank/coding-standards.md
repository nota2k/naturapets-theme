# Standards de Code

## FSE et structure

- Prioriser `templates/`, `parts/`, `patterns/` avant templates PHP custom.
- Reutiliser les compositions existantes avant d'en creer de nouvelles.
- Garder des structures semantiques et orientees composant.

## theme.json et design tokens

- Tout nouveau token visuel doit etre defini dans `theme.json` d'abord.
- Utiliser en priorite les variables:
  - `var(--wp--preset--color--*)`
  - `var(--wp--preset--font-size--*)`
  - `var(--wp--preset--spacing--*)`
  - `var(--wp--custom--*)`
- Eviter hex/rgb fixes sauf exception documentee.

## SCSS

- SCSS modulaire dans `src/` selon l'organisation du theme.
- Limiter la specificite et eviter `!important` (sauf justification claire).
- Nommage de classes coherent (prefixe theme recommande: `np-`).

## WooCommerce

- Ne pas casser les hooks/comportements natifs sans besoin explicite.
- Tester listing produit, fiche produit, panier, checkout, compte.
- Preferer des adaptations via tokens et structure avant surcharge lourde.

## ACF Pro

- Utiliser ACF pour le contenu structure non couvert nativement.
- En sortie:
  - echapper les donnees (`esc_html`, `esc_attr`, `wp_kses_post`, etc.),
  - gerer les champs vides avec fallback,
  - garder un markup simple et robuste.
