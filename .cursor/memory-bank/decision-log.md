# Journal des Decisions

Ce fichier trace les decisions durables du projet.

## Format d'entree

Date: YYYY-MM-DD
Sujet: court titre
Contexte: pourquoi la decision est necessaire
Decision: choix retenu
Impact: consequences techniques et produit

---

Date: 2026-04-20
Sujet: Priorite des couches de style/theme
Contexte: besoin d'une approche cohérente pour eviter la dette CSS et la duplication.
Decision: appliquer l'ordre FSE/compositions -> `theme.json` -> variables CSS WordPress -> SCSS.
Impact: meilleure coherence, maintenance simplifiee, moins de styles ad hoc.

Date: 2026-04-20
Sujet: Stack de reference du theme
Contexte: necessite d'aligner les decisions agent sur les outils reellement utilises.
Decision: stack officielle = WordPress FSE + WooCommerce + ACF Pro + SCSS.
Impact: les recommandations agent sont mieux ciblees et executables.
