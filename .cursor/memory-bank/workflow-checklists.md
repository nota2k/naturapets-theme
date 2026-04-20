# Checklists Workflow

## Checklist implementation

- [ ] Le besoin est evalue d'abord en composition FSE.
- [ ] Les presets `theme.json` existants sont reutilises.
- [ ] Si un token manque, il est ajoute dans `theme.json`.
- [ ] Le SCSS ajoute est minimal et sans hardcode inutile.
- [ ] Les impacts WooCommerce sont identifies.
- [ ] Les sorties ACF sont echappees et resilientes.

## Checklist QA rapide

- [ ] Rendu valide desktop.
- [ ] Rendu valide mobile.
- [ ] Pages WooCommerce critiques valides.
- [ ] Pas de regression visuelle evidente.
- [ ] Pas de warning PHP introduit sur le flux teste.

## Definition of Done

Une tache est finie quand:

- la priorite FSE -> `theme.json` -> variables WP -> SCSS est respectee,
- les flux WooCommerce critiques passent,
- les champs ACF utilises sont traites de facon securisee,
- la solution est reutilisable et maintenable.
