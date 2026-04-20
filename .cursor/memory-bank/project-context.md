# Contexte Projet

## Identite du projet

- Projet: theme WordPress NaturaPets
- Scope principal: site vitrine + e-commerce WooCommerce
- Type de theme: FSE (Full Site Editing)

## Stack technique

- WordPress (block theme / Site Editor)
- WooCommerce
- ACF Pro
- SCSS compile vers les assets du theme
- `theme.json` comme source de tokens/design settings

## Priorites d'implementation

1. Compositions FSE (templates, parts, patterns)
2. Reglages et presets dans `theme.json`
3. Variables CSS WordPress (`--wp--preset--*`, `--wp--custom--*`)
4. SCSS uniquement en complement

## Contraintes UX/techniques

- Favoriser la reutilisabilite des sections et compositions.
- Eviter les valeurs hardcodees quand un token existe.
- Maintenir la compatibilite des parcours WooCommerce.
- Garder des sorties ACF securisees et robustes (fallback).
