---
name: naturapets-theme-workflow
description: Développe et maintient le thème WordPress NaturaPets avec WooCommerce et ACF Pro, en priorisant les compositions FSE, les réglages de theme.json et les variables CSS WordPress avant le SCSS. Utiliser quand la demande concerne templates, patterns, styles, blocs, pages produit, checkout, ou intégration ACF dans ce thème.
---

# NaturaPets Theme Workflow

## Objectif

Appliquer une méthode cohérente pour développer ce thème WordPress en respectant les priorités techniques de l'équipe.

## Pile technique de référence

- WordPress (FSE / block theme)
- WooCommerce
- ACF Pro
- SCSS (compilation vers les assets CSS du thème)
- `theme.json` comme source primaire des tokens/design settings

## Ordre de décision (obligatoire)

Pour tout besoin UI, layout ou style, suivre cet ordre:

1. **Compositions FSE**: privilégier les patterns, template parts, templates et variations de styles.
2. **`theme.json`**: utiliser d'abord les presets (couleurs, typo, spacing, layout, blocks, styles).
3. **Variables CSS WordPress**: s'appuyer sur `var(--wp--preset--*)` et variables exposées par le système.
4. **SCSS du thème**: ajouter du SCSS uniquement si le besoin ne peut pas être couvert proprement par les étapes 1-3.

Ne pas introduire de valeurs hardcodées si une valeur de preset existe déjà.

## Règles FSE (compositions d'abord)

- Préférer les modifications via `templates/`, `parts/` et `patterns/` avant de toucher aux templates PHP.
- Réutiliser les compositions existantes avant d'en créer de nouvelles.
- Garder des blocs sémantiques et réutilisables (éviter les structures trop spécifiques à une seule page).
- Si une mise en page est spécifique WooCommerce, créer une composition dédiée réutilisable plutôt qu'un markup dupliqué.

## Règles `theme.json` et design tokens

- Ajouter les nouveaux tokens dans `theme.json` (couleur, typo, spacing, rayons, ombres) avant tout style SCSS.
- En SCSS, utiliser les variables WP:
  - `var(--wp--preset--color--*)`
  - `var(--wp--preset--font-size--*)`
  - `var(--wp--preset--spacing--*)`
  - `var(--wp--custom--*)` si des tokens custom sont exposés
- Éviter les hex/rgb fixes et les tailles arbitraires si un preset couvre déjà le besoin.

## Règles SCSS

- Écrire du SCSS modulaire dans `src/` selon l'architecture existante.
- Limiter la spécificité CSS; ne pas lutter contre le style engine de WordPress.
- Éviter `!important` sauf contrainte avérée (à documenter en commentaire court).
- Pour les blocs, cibler des classes de bloc stables (`.wp-block-*`) et des wrappers du thème, pas des sélecteurs fragiles.

## Convention de nommage CSS/SCSS

- Utiliser des classes de composant préfixées thème, ex: `.np-card`, `.np-product-grid`.
- Utiliser des modificateurs explicites, ex: `.np-card--featured`, `.np-card--compact`.
- Garder les classes utilitaires locales rares; préférer les presets/controls WordPress.
- Ne pas nommer selon la position visuelle (`left`, `small-red`), nommer selon le rôle (`meta`, `highlight`, `badge`).

## Organisation SCSS recommandée

Adapter à l'arborescence existante, mais garder cette logique:

- `src/scss/base/`: reset léger, typo globale, helpers de base.
- `src/scss/components/`: composants réutilisables du thème.
- `src/scss/blocks/`: styles ciblés par bloc WordPress.
- `src/scss/woocommerce/`: surcharges WooCommerce strictement nécessaires.
- `src/scss/templates/`: styles de structures de templates si non exprimables en `theme.json`.

## WooCommerce

- Vérifier d'abord les templates/compositions WooCommerce existants dans le thème.
- Conserver la compatibilité avec les hooks WooCommerce; éviter de casser les templates natifs sans nécessité.
- Prioriser les adaptations de style via tokens `theme.json` et variables WP, puis SCSS ciblé.
- Tester les écrans clés: listing, fiche produit, panier, checkout, compte.

## ACF Pro

- Utiliser ACF Pro pour les besoins éditoriaux structurés non couverts nativement par les blocs/compositions.
- Préférer l'affichage via blocs/compositions connectés aux données ACF plutôt que du markup PHP couplé.
- Toujours prévoir:
  - validation/sanitation des valeurs en entrée,
  - échappement (`esc_html`, `esc_attr`, `wp_kses_post`, etc.) en sortie,
  - comportement de fallback si le champ est vide.

### Snippet ACF de référence (sortie sécurisée)

```php
$title = get_field('section_title');
if ( $title ) {
	echo '<h2 class="np-section-title">' . esc_html( $title ) . '</h2>';
}
```

## Workflow d'implémentation

1. Identifier si le besoin est réalisable en composition FSE.
2. Vérifier si un preset `theme.json` existe déjà; sinon l'ajouter.
3. Implémenter la structure (templates/parts/patterns ou blocs).
4. Ajouter le style minimal nécessaire via variables WP.
5. Compléter en SCSS uniquement pour les cas non couverts.
6. Vérifier le rendu desktop/mobile et les parcours WooCommerce critiques.

## Workflow WooCommerce (rapide)

À chaque changement lié au commerce:

1. Vérifier d'abord si la vue peut être ajustée via composition FSE/template part.
2. Confirmer qu'aucun hook WooCommerce existant n'est cassé.
3. Vérifier styles et états des boutons, prix, variations, messages d'erreur/succès.
4. Tester flux minimum: ajout panier -> panier -> checkout -> confirmation.

## Done Definition (DoD)

Une tâche est terminée si:

- la structure passe d'abord par FSE/compositions,
- les tokens sont centralisés dans `theme.json`,
- le SCSS n'introduit pas de dette évidente (spécificité excessive, hardcode inutile),
- le rendu est correct mobile/desktop,
- les points critiques WooCommerce fonctionnent.

## Checklist de revue avant livraison

- [ ] Pas de valeur visuelle hardcodée alors qu'un preset existe.
- [ ] `theme.json` mis à jour si nouveau token nécessaire.
- [ ] Utilisation prioritaire de compositions FSE.
- [ ] SCSS limité au strict nécessaire.
- [ ] Compatibilité WooCommerce vérifiée sur les pages clés.
- [ ] Sorties ACF échappées et fallback présent.

## À éviter

- Créer un template PHP complet pour un besoin gérable en FSE.
- Ajouter des couleurs/espacements "one-off" hors `theme.json`.
- Dupliquer des sections WooCommerce au lieu de créer une composition réutilisable.
- Ajouter du JS/DOM hack pour corriger un problème résolvable via structure FSE + tokens.
