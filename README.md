# 📖 Documentation du Thème NaturaPets

> **Thème enfant de Frost** — Version 1.0.10  
> Auteur : Nelly Babillon  
> Requis : WordPress ≥ 6.7 · PHP ≥ 7.2 · **ACF Pro** (Advanced Custom Fields Pro)

---

## Table des matières

1. [Architecture du thème](#1-architecture-du-thème)
2. [Palette de couleurs](#2-palette-de-couleurs)
3. [Typographie](#3-typographie)
4. [Blocs ACF personnalisés](#4-blocs-acf-personnalisés)
   - [Bannière Hero](#41--bannière-hero)
   - [Section Hero (Grille 2×2)](#42--section-hero-grille-2×2)
   - [Section deux colonnes (Split CTA)](#43--section-deux-colonnes-split-cta)
   - [J'ai trouvé un animal](#44--jai-trouvé-un-animal)
   - [Témoignage](#45--témoignage)
   - [Galerie produit](#46--galerie-produit)
   - [Icône](#47--icône)
   - [Moveblock (Animation GSAP)](#48--moveblock-animation-gsap)
5. [Templates et Parts](#5-templates-et-parts)
6. [Bandeau du haut (Customizer)](#6-bandeau-du-haut-customizer)
7. [Styles et SCSS](#7-styles-et-scss)
8. [Fonctionnalités WooCommerce](#8-fonctionnalités-woocommerce)
9. [Système QR Code et Médaillons](#9-système-qr-code-et-médaillons)

---

## 1. Architecture du thème

```
naturapets/
├── assets/
│   ├── css/main.css            ← CSS compilé (ne pas modifier directement)
│   ├── fonts/                  ← Polices (Outfit Variable)
│   ├── images/
│   └── js/
│       ├── header-scroll.js    ← Animation header au scroll
│       └── moveblock.js        ← Moteur d'animation GSAP
├── blocks/                     ← Blocs ACF (8 blocs personnalisés)
│   ├── found-animal/
│   ├── hero-banner/
│   ├── hero-section/
│   ├── icone/
│   ├── moveblock/
│   ├── product-gallery/
│   ├── split-cta/
│   └── testimonial/
├── includes/
│   └── class-qrcode.php        ← Génération de QR codes
├── parts/
│   └── header.html              ← Header (logo + navigation)
├── src/scss/                    ← Sources SCSS (à compiler)
├── templates/                   ← Templates de page WordPress
├── woocommerce/                 ← Surcharges WooCommerce
├── functions.php                ← Fonctions du thème
├── style.css                    ← Métadonnées du thème
└── theme.json                   ← Configuration globale (couleurs, typo, espaces)
```

> **⚠️ Important** : Le plugin **ACF Pro** doit être installé et activé pour que les blocs personnalisés fonctionnent.

---

## 2. Palette de couleurs

Les couleurs sont définies dans [`theme.json`](theme.json) et sont accessibles dans l'éditeur Gutenberg :

| Nom            | Slug          | Code Hex    |
|----------------|---------------|-------------|
| Base           | `base`        | `#ffffff`   |
| Brun           | `brun`        | `#774A0A`   |
| Marron         | `marron`      | `#774A0A`   |
| Olive          | `olive`       | `#848A71`   |
| Beige          | `beige`       | `#F4F3EA`   |
| Vert flash     | `vert-flash`  | `#6CFA90`   |
| Bleu noir      | `bleu-noir`   | `#232E40`   |
| Vert d'eau     | `vert-eau`    | `#C5E3C6`   |
| Gris moyen     | `gris-moyen`  | `#4A4A4A`   |
| Rouge moyen    | `rouge-moyen` | `#FF5E5E`   |

### Comment modifier les couleurs

1. Ouvrir le fichier **`theme.json`**
2. Chercher la section `settings > color > palette`
3. Modifier le `color` (valeur hex) du slug souhaité
4. Sauvegarder → les changements sont appliqués globalement

---

## 3. Typographie

### Polices

| Nom          | Slug        | Famille                  | Usage          |
|--------------|-------------|--------------------------|----------------|
| **Outfit**   | `primary`   | Outfit, sans-serif       | Police principale (variable, 100-900) |
| System Font  | `system-font` | -apple-system, etc.    | Fallback système |
| Nunito Sans  | —           | Nunito Sans (Google)     | Bloc Témoignage |

### Tailles de police

| Nom     | Slug       | Taille    | Fluid (min → max)  |
|---------|------------|-----------|---------------------|
| xSmall  | `x-small`  | 16px      | Non                 |
| Small   | `small`    | 18px      | Non                 |
| Medium  | `medium`   | 20px      | 18px → 20px         |
| Large   | `large`    | 24px      | 20px → 24px         |
| xLarge  | `x-large`  | 30px      | 24px → 30px         |
| 36px    | `max-36`   | 36px      | 30px → 36px         |
| 48px    | `max-48`   | 48px      | 36px → 48px         |
| 60px    | `max-60`   | 60px      | 42px → 60px         |
| 72px    | `max-72`   | 72px      | 48px → 72px         |

### Comment modifier la typographie

- **Fichier** : `theme.json` → section `settings > typography`
- Pour les tailles : modifier `fontSizes[]`, les valeurs `fluid.min` et `fluid.max`
- Pour la police : remplacer le fichier dans `assets/fonts/` et adapter `fontFace`

---

## 4. Blocs ACF personnalisés

Tous les blocs sont situés dans le dossier `blocks/`. Chacun contient :
- **`block.json`** : Déclaration du bloc (nom, titre, catégorie, icône…)
- **`render.php`** : Template de rendu HTML/PHP

> 💡 **Comment insérer un bloc** : Dans l'éditeur de page, cliquer sur le **+** (ajouter un bloc) et chercher le nom du bloc ou le mot-clé. Les blocs NaturaPets sont dans la catégorie **« theme »**.

---

### 4.1 — Bannière Hero

> **Nom technique** : `naturapets/hero-banner`  
> **Fichiers** : [`blocks/hero-banner/block.json`](blocks/hero-banner/block.json) + [`blocks/hero-banner/render.php`](blocks/hero-banner/render.php)  
> **Catégorie** : Theme · **Icône** : `cover-image`

#### Description
Bannière plein écran avec fond (image **ou** vidéo), titre, slogan et bouton d'appel à l'action. Le contenu est positionné à droite. Idéal pour la page d'accueil.

#### Champs ACF

| Champ                   | Nom ACF                      | Type      | Description                                           |
|-------------------------|------------------------------|-----------|-------------------------------------------------------|
| **Image de fond**       | `hero_banner_image`          | Image     | Image de fond (affichée si pas de vidéo)              |
| **Vidéo de fond**       | `hero_banner_video`          | Fichier   | Vidéo MP4/WebM (prioritaire sur l'image)              |
| **Titre**               | `hero_banner_titre`          | Texte     | Titre principal (défaut : « Naturapets »)             |
| **Slogan**              | `hero_banner_slogan`         | Texte     | Sous-titre (défaut : « La tranquillité au bout du collier ») |
| **Texte du bouton**     | `hero_banner_bouton_texte`   | Texte     | Libellé du CTA (défaut : « Découvrir »)              |
| **URL du bouton**       | `hero_banner_bouton_url`     | URL       | Lien du bouton (défaut : `#`)                        |
| **Taille du bouton**    | `hero_banner_bouton_taille`  | Sélection | Taille du texte du bouton (presets du thème)          |
| **Taille du titre**     | `hero_banner_titre_taille`   | Sélection | Taille du titre (x-small → 72px)                     |

#### Comment modifier ce bloc
1. **Dans l'éditeur** : Insérer le bloc → remplir les champs dans le panneau latéral ACF
2. **Taille du titre** : Choisir dans la liste déroulante OU utiliser le panneau *Typographie* du bloc
3. **Fond vidéo vs image** : Si une vidéo est renseignée, elle sera prioritaire. Supprimer la vidéo pour afficher l'image
4. **Alignement** : Supporte `wide` et `full` (pleine largeur)

#### Classes CSS principales
- `.np-hero-banner` — Conteneur principal
- `.np-hero-banner__media` — Zone fond (image/vidéo)
- `.np-hero-banner__overlay` — Overlay semi-transparent
- `.np-hero-banner__title` — Titre H1
- `.np-hero-banner__slogan` — Sous-titre
- `.np-hero-banner__cta` — Bouton CTA
- **SCSS** : `src/scss/components/_hero-banner.scss`

---

### 4.2 — Section Hero (Grille 2×2)

> **Nom technique** : `naturapets/hero-section`  
> **Fichiers** : [`blocks/hero-section/block.json`](blocks/hero-section/block.json) + [`blocks/hero-section/render.php`](blocks/hero-section/render.php)  
> **Catégorie** : Theme · **Icône** : `layout`

#### Description
Grille 2×2 design Figma avec alternance image/texte :
- **Haut-gauche** : Image
- **Haut-droite** : Texte
- **Bas-gauche** : Texte
- **Bas-droite** : Image

#### Champs ACF

| Champ                        | Nom ACF              | Type     | Description                                       |
|------------------------------|----------------------|----------|---------------------------------------------------|
| **Texte en haut à droite**   | `texte_haut`         | Textarea | Texte zone haute (défaut : « Un système simple et efficace ») |
| **Texte en bas à gauche**    | `texte_bas`          | Textarea | Texte zone basse (défaut : « Pour nos amis les bêtes ») |
| **Image en haut à gauche**   | `image_haut_gauche`  | Image    | Visuel haut-gauche                                |
| **Image en bas à droite**    | `image_bas_droite`   | Image    | Visuel bas-droite                                 |

#### Comment modifier ce bloc
1. **Dans l'éditeur** : Les sauts de ligne dans les champs textarea sont conservés (retours à la ligne avec `nl2br`)
2. **Images** : Format recommandé carré ou paysage, taille « large » WordPress
3. **Alignement** : Supporte `wide` et `full`

#### Classes CSS principales
- `.naturapets-hero-section` — Conteneur principal
- `.naturapets-hero-section__grid` — Grille CSS 2×2
- `.naturapets-hero-section__text--top-right` — Texte haut-droite
- `.naturapets-hero-section__text--bottom-left` — Texte bas-gauche
- `.naturapets-hero-section__block--top-left` — Image haut-gauche
- `.naturapets-hero-section__block--bottom-right` — Image bas-droite
- **SCSS** : `src/scss/components/_hero-section.scss`

---

### 4.3 — Section deux colonnes (Split CTA)

> **Nom technique** : `naturapets/split-cta`  
> **Fichiers** : [`blocks/split-cta/block.json`](blocks/split-cta/block.json) + [`blocks/split-cta/render.php`](blocks/split-cta/render.php)  
> **Catégorie** : Theme · **Icône** : `columns`

#### Description
Section en deux colonnes :
- **Colonne gauche** : Image ou fond gris clair (si pas d'image)
- **Colonne droite** : Titre avec effet « ombre », paragraphe et bouton

#### Champs ACF

| Champ                     | Nom ACF                 | Type     | Description                                         |
|---------------------------|-------------------------|----------|-----------------------------------------------------|
| **Image colonne gauche**  | `split_cta_image_gauche` | Image   | Optionnel. Si vide → fond gris affiché              |
| **Titre**                 | `split_cta_titre`        | Texte   | Titre principal (défaut : « GROS TITRE »)            |
| **Texte**                 | `split_cta_texte`        | Textarea | Contenu descriptif                                  |
| **Texte du bouton**       | `split_cta_bouton_texte` | Texte   | Libellé du CTA (défaut : « Découvrir »)             |
| **URL du bouton**         | `split_cta_bouton_url`   | URL     | Lien de destination                                  |

#### Comment modifier ce bloc
1. **Image gauche** : Optionnelle. Sans image, un fond gris clair sera affiché automatiquement
2. **Effet titre** : Le titre est doublé dans le HTML (classe `.behind`) pour créer un effet de profondeur via CSS. Pas besoin de le dupliquer manuellement
3. **Alignement** : Supporte `wide` et `full`

#### Classes CSS principales
- `.np-split-cta` — Conteneur
- `.np-split-cta__image-wrap` — Zone image gauche
- `.np-split-cta__content-wrap` — Zone texte droite
- `.np-split-cta__title` — Titre (+ `.behind` pour l'ombre)
- `.np-split-cta__text` — Paragraphe
- `.np-split-cta__cta` — Bouton CTA
- **SCSS** : `src/scss/components/_split-cta.scss`

---

### 4.4 — J'ai trouvé un animal

> **Nom technique** : `naturapets/found-animal`  
> **Fichiers** : [`blocks/found-animal/block.json`](blocks/found-animal/block.json) + [`blocks/found-animal/render.php`](blocks/found-animal/render.php)  
> **Catégorie** : Theme · **Icône** : `search`

#### Description
Section avec fond vert menthe contenant un formulaire de recherche de médaillon. Le visiteur saisit un numéro de médaillon et clique sur « Chercher » pour retrouver les informations de l'animal.

#### Champs ACF

| Champ                      | Nom ACF                     | Type  | Description                                              |
|----------------------------|-----------------------------|-------|----------------------------------------------------------|
| **Titre**                  | `found_animal_titre`        | Texte | Titre de la section (défaut : « J'ai trouvé un animal ») |
| **Placeholder du champ**   | `found_animal_placeholder`  | Texte | Texte indicatif dans le champ (défaut : « entrer le numéro du médaillon ») |
| **Texte du bouton**        | `found_animal_bouton_texte` | Texte | Libellé du bouton (défaut : « Chercher »)                |
| **URL page de recherche**  | `found_animal_form_action_url` | URL | Page de destination du formulaire (défaut : accueil)     |
| **Nom du paramètre GET**   | `found_animal_param_name`   | Texte | Paramètre URL (défaut : `medaillon`)                     |

#### Comment modifier ce bloc
1. **URL de recherche** : Indiquer la page qui traite la recherche de médaillon. Le formulaire envoie une requête GET avec le paramètre configuré
2. **Nom du paramètre** : Par défaut `medaillon`, l'URL résultante sera : `https://…/page-recherche/?medaillon=12345`
3. **Alignement** : Supporte `wide` et `full`

#### Classes CSS principales
- `.np-found-animal` — Conteneur (fond vert menthe)
- `.np-found-animal__form` — Formulaire
- `.np-found-animal__label` — Titre / label
- `.np-found-animal__input` — Champ de saisie
- `.np-found-animal__btn` — Bouton « Chercher »
- **SCSS** : `src/scss/components/_found-animal.scss`

---

### 4.5 — Témoignage

> **Nom technique** : `naturapets/testimonial`  
> **Fichiers** : [`blocks/testimonial/block.json`](blocks/testimonial/block.json) + [`blocks/testimonial/render.php`](blocks/testimonial/render.php)  
> **Catégorie** : Theme · **Icône** : `format-quote`

#### Description
Bloc de témoignage client : photo circulaire à gauche, citation avec guillemets décoratifs verts à droite, et nom de l'auteur. Utilise la police **Nunito Sans** pour la citation.

#### Champs ACF

| Champ        | Nom ACF                | Type     | Description                                                   |
|--------------|------------------------|----------|---------------------------------------------------------------|
| **Photo**    | `testimonial_photo`    | Image    | Photo circulaire (opt. — cercle gris si vide)                 |
| **Citation** | `testimonial_citation` | Textarea | Texte du témoignage                                           |
| **Auteur**   | `testimonial_auteur`   | Texte    | Nom de l'auteur (défaut : « Liliane »)                        |

#### Comment modifier ce bloc
1. **Photo** : Optionnelle. Utiliser une image carrée pour un meilleur rendu dans le cercle
2. **Guillemets** : Les guillemets verts décoratifs (`‟`) sont générés automatiquement par le template
3. **Alignement** : Supporte `wide` et `full`

#### Classes CSS principales
- `.np-testimonial` — Conteneur
- `.np-testimonial__photo-wrap` — Zone photo circulaire
- `.np-testimonial__blockquote` — Citation
- `.np-testimonial__citation` — Texte de la citation
- `.np-testimonial__author` — Nom de l'auteur
- `.np-testimonial__quote` — Guillemets décoratifs (vert)
- **SCSS** : `src/scss/components/_testimonial.scss`

---

### 4.6 — Galerie produit

> **Nom technique** : `naturapets/product-gallery`  
> **Fichiers** : [`blocks/product-gallery/block.json`](blocks/product-gallery/block.json) + [`blocks/product-gallery/render.php`](blocks/product-gallery/render.php)  
> **Catégorie** : Theme · **Icône** : `format-gallery`

#### Description
Trois zones d'images empilées verticalement :
1. **Image principale** (en haut)
2. **Image bandeau** (au centre)
3. **Image grande** (en bas)

> 📌 **Comportement intelligent** : Sur une page produit WooCommerce, le bloc récupère automatiquement l'image à la une puis les images de la galerie du produit. Les champs ACF ne servent que de fallback (pages non-produit).

#### Champs ACF

| Champ                        | Nom ACF                    | Type  | Description                            |
|------------------------------|----------------------------|-------|----------------------------------------|
| **Image principale (haut)**  | `product_gallery_image_1`  | Image | Première image                         |
| **Image bandeau (centre)**   | `product_gallery_image_2`  | Image | Deuxième image                         |
| **Image grande (bas)**       | `product_gallery_image_3`  | Image | Troisième image                        |

#### Comment modifier ce bloc
1. **Page produit WooCommerce** : Les images sont tirées automatiquement de l'image à la une et de la galerie produit — pas besoin de remplir les champs ACF
2. **Page standard** : Remplir les 3 champs image manuellement
3. **Alignement** : Supporte `wide` et `full`

#### Classes CSS principales
- `.np-product-gallery` — Conteneur
- `.np-product-gallery__item--featured` — Image principale
- `.np-product-gallery__item--strip` — Image bandeau
- `.np-product-gallery__item--large` — Image grande
- **SCSS** : `src/scss/components/_product-gallery.scss`

---

### 4.7 — Icône

> **Nom technique** : `naturapets/icone`  
> **Fichiers** : [`blocks/icone/block.json`](blocks/icone/block.json) + [`blocks/icone/render.php`](blocks/icone/render.php)  
> **Catégorie** : Theme · **Icône** : `admin-customizer`

#### Description
Petit cercle (60×60 px max) avec une image centrée et un fond coloré personnalisable. Idéal pour les pictogrammes et les icônes de fonctionnalités.

#### Champs ACF

| Champ               | Nom ACF            | Type  | Description                                      |
|---------------------|--------------------|-------|--------------------------------------------------|
| **Image**           | `icone_image`      | Image | Icône/pictogramme au centre (60×60 px max)       |
| **Couleur de fond** | `icone_background` | Radio | Couleur du cercle (palette du thème)             |

#### Comment modifier ce bloc
1. **Image** : Utiliser une icône PNG/SVG de petite taille (idéalement 60×60 px)
2. **Couleur de fond** : Les choix affichent les pastilles de couleur de la palette du thème. Sélectionner une couleur met à jour le fond du cercle
3. **Alignement** : Supporte `left`, `center`, `right`

#### Classes CSS principales
- `.np-icone` — Cercle avec fond coloré (inline style)
- `.np-icone__img` — Image centrée
- **SCSS** : `src/scss/components/_icone.scss`

---

### 4.8 — Moveblock (Animation GSAP)

> **Nom technique** : `naturapets/moveblock`  
> **Fichiers** : [`blocks/moveblock/block.json`](blocks/moveblock/block.json) + [`blocks/moveblock/render.php`](blocks/moveblock/render.php)  
> **Catégorie** : Theme · **Icône** : `visibility`

#### Description
Bloc **invisible** qui permet d'appliquer des animations GSAP à n'importe quel élément de la page via un sélecteur CSS. Il ne produit aucun rendu visuel — seulement un `<div>` caché contenant les données de l'animation.

> ⚠️ Ce bloc nécessite que **GSAP** soit chargé (automatique quand le bloc est utilisé). Le fichier `assets/js/moveblock.js` gère l'exécution des effets.

#### Champs ACF

| Champ                        | Nom ACF           | Type      | Description                                           |
|------------------------------|--------------------|-----------|-------------------------------------------------------|
| **Effet GSAP**               | `effect`           | Sélection | Type d'animation (voir liste ci-dessous)              |
| **Sélecteur CSS cible**      | `target_selector`  | Texte     | Ex : `.hero-title`, `#nav`, `.ma-classe`              |
| **Durée (secondes)**         | `duration`         | Nombre    | Durée de l'animation (défaut : 1)                     |
| **Délai (secondes)**         | `delay`            | Nombre    | Délai avant le démarrage (défaut : 0)                 |
| **Ease**                     | `ease`             | Sélection | Courbe d'accélération (défaut : `power2.out`)         |
| **Stagger (secondes)**       | `stagger`          | Nombre    | Décalage entre éléments multiples (défaut : 0)         |

#### Effets disponibles

| Clé               | Effet               | Clé               | Effet               |
|-------------------|----------------------|-------------------|----------------------|
| `fadeIn`          | Fade In              | `fadeOut`          | Fade Out             |
| `fadeFromTo`      | Fade In/Out          | `slideInLeft`      | Slide depuis gauche  |
| `slideInRight`    | Slide depuis droite  | `slideInTop`       | Slide depuis haut    |
| `slideInBottom`   | Slide depuis bas     | `slideOutLeft`     | Slide vers gauche    |
| `slideOutRight`   | Slide vers droite    | `moveX`            | Déplacement X        |
| `moveY`           | Déplacement Y        | `scaleUp`          | Agrandissement       |
| `scaleDown`       | Réduction            | `scaleFromTo`      | Pulse (scale)        |
| `scaleX`          | Scale horizontal     | `scaleY`           | Scale vertical       |
| `rotateIn`        | Rotation entrée      | `rotateOut`        | Rotation sortie      |
| `rotateY`         | Rotation Y (3D)      | `rotateX`          | Rotation X (3D)      |
| `skewIn`          | Skew entrée          | `skewOut`          | Skew sortie          |
| `popIn`           | Pop in               | `blurIn`           | Blur entrée          |
| `blurOut`         | Blur sortie          | `dropIn`           | Drop in              |
| `bounceIn`        | Bounce entrée        | `colorChange`      | Changement couleur   |
| `backgroundColor` | Changement de fond   |                    |                      |

#### Courbes d'ease disponibles

`none`, `power1.in/out/inOut`, `power2.in/out/inOut`, `power3.in/out/inOut`, `power4.in/out/inOut`, `back.in/out/inOut`, `bounce.in/out/inOut`, `circ.in/out/inOut`, `elastic.in/out/inOut`, `expo.in/out/inOut`, `sine.in/out/inOut`

#### Comment modifier ce bloc
1. **Ajouter une animation** : Insérer le bloc Moveblock dans la page → choisir l'effet et saisir le sélecteur CSS de l'élément à animer
2. **Cibler plusieurs éléments** : Si le sélecteur CSS correspond à plusieurs éléments (ex : `.card`), utiliser le **stagger** pour décaler les animations
3. **Combinaisons** : Ajouter plusieurs blocs Moveblock pour animer différents éléments de la même page
4. **Éditeur** : Dans l'éditeur, un message placeholder s'affiche si aucun sélecteur n'est renseigné. Le bloc est invisible côté front

---

## 5. Templates et Parts

### Templates de page

| Fichier                  | Usage                                |
|--------------------------|--------------------------------------|
| `templates/home.html`    | Page d'accueil                       |
| `templates/page.html`    | Pages standards                      |
| `templates/single.html`  | Articles / Posts                     |
| `templates/archive.html` | Pages d'archives                     |
| `templates/search.html`  | Page de résultats de recherche       |
| `templates/404.html`     | Page d'erreur 404                    |
| `templates/blank.html`   | Template vide (sans header/footer)   |
| `templates/no-title.html`| Template sans titre                  |
| `templates/page-myaccount.html` | Page Mon Compte WooCommerce  |

### Parts (Éléments réutilisables)

| Fichier              | Description                              |
|----------------------|------------------------------------------|
| `parts/header.html`  | En-tête : logo NaturaPets + navigation   |

#### Comment modifier le header
Le header est défini dans [`parts/header.html`](parts/header.html) en syntaxe de blocs WordPress. Il contient :
- Le **logo du site** (bloc `core/site-logo`)
- Le **nom de la marque** (`Natura` + `Pets` en spans séparés pour le style)
- La **navigation** (bloc `core/navigation`)

Pour modifier graphiquement : **Apparence → Éditeur → Parties du template → Header** dans l'administration WordPress.

---

## 6. Bandeau du haut (Customizer)

Le thème intègre un bandeau promotionnel affichable en haut de toutes les pages.

### Configuration

**Emplacement** : Administration → **Apparence → Personnaliser → Bandeau du haut**

| Option                | Description                                                |
|-----------------------|------------------------------------------------------------|
| **Afficher le bandeau** | Checkbox pour activer/désactiver                          |
| **Texte du bandeau**    | Texte affiché (ex : « Livraison offerte à partir de 50 € d'achat ») |

### Classes CSS
- `.np-top-banner` — Conteneur du bandeau
- `.np-top-banner__text` — Texte
- **SCSS** : `src/scss/components/_top-banner.scss`

---

## 7. Styles et SCSS

### Structure SCSS

```
src/scss/
├── abstracts/
│   ├── _variables.scss    ← Variables SCSS (couleurs, breakpoints…)
│   ├── _mixins.scss       ← Mixins réutilisables
│   └── _functions.scss    ← Fonctions SCSS
├── base/
│   ├── _reset.scss        ← Reset / normalisation
│   └── _typography.scss   ← Styles de base typographiques
├── components/
│   ├── _buttons.scss      ← Styles des boutons
│   ├── _cards.scss        ← Cartes
│   ├── _forms.scss        ← Formulaires
│   ├── _hero-section.scss ← Bloc Section Hero
│   ├── _hero-banner.scss  ← Bloc Bannière Hero
│   ├── _split-cta.scss    ← Bloc Split CTA
│   ├── _found-animal.scss ← Bloc J'ai trouvé un animal
│   ├── _testimonial.scss  ← Bloc Témoignage
│   ├── _product-gallery.scss ← Bloc Galerie produit
│   ├── _icone.scss        ← Bloc Icône
│   └── _top-banner.scss   ← Bandeau promotionnel
├── layout/
│   ├── _header.scss       ← En-tête
│   ├── _footer.scss       ← Pied de page
│   └── _navigation.scss   ← Navigation
├── pages/
│   ├── _home.scss         ← Accueil
│   ├── _single.scss       ← Article unique
│   ├── _archive.scss      ← Archives
│   ├── _contact.scss      ← Contact
│   └── _medaillon-public.scss ← Page publique médaillon
├── woocommerce/
│   ├── _products.scss     ← Listing produits
│   ├── _cart.scss         ← Panier
│   ├── _checkout.scss     ← Commande
│   └── _myaccount.scss    ← Mon compte
└── main.scss              ← Point d'entrée (imports)
```

### Comment compiler les styles

Le thème utilise un fichier `package.json` pour la compilation SCSS :

```bash
# Se placer dans le dossier du thème
cd app/public/wp-content/themes/naturapets

# Installer les dépendances
npm install

# Compiler le SCSS → CSS
npm run build

# Mode watch (compilation automatique)
npm run watch
```

Le fichier compilé est généré dans `assets/css/main.css`.

> 🚨 **Ne modifiez jamais `assets/css/main.css` directement** — vos changements seront écrasés à la prochaine compilation.

### Comment modifier le style d'un bloc

1. Identifier le fichier SCSS correspondant dans `src/scss/components/`
2. Modifier les styles
3. Recompiler avec `npm run build` ou utiliser `npm run watch`
4. Vérifier le résultat dans le navigateur

---

## 8. Fonctionnalités WooCommerce

Le thème intègre plusieurs fonctionnalités WooCommerce spécifiques :

### Surcharges de templates
Le dossier `woocommerce/` contient les templates WooCommerce surchargés.

### Fonctionnalités intégrées

| Fonctionnalité                       | Description                                                  |
|--------------------------------------|--------------------------------------------------------------|
| **Inscription Mon compte**           | Le formulaire d'inscription est activé sur la page Mon compte |
| **Création du mot de passe**         | L'utilisateur crée son propre mot de passe (pas d'envoi par email) |
| **Validation mot de passe**          | Confirmation du mot de passe à l'inscription                 |
| **Adresse livraison = facturation**  | Checkbox pour copier l'adresse de facturation vers livraison |
| **ID unique produit**                | Chaque produit reçoit un identifiant unique (préfixe `NP-`) |
| **Suppression de compte**            | Les utilisateurs peuvent supprimer leur compte               |
| **QR Code médaillon**                | Génération de QR codes pour les médaillons d'animaux         |
| **Upload SVG**                       | Les fichiers SVG sont autorisés dans la médiathèque          |

### Page Mon Compte
La page Mon Compte intègre :
- Une **modal QR Code** pour visualiser les QR codes des médaillons
- Une **modal de suppression de compte** avec confirmation
- Un système de gestion d'**adresses** avec synchronisation facturation/livraison

---

## 9. Système QR Code et Médaillons

Le thème intègre un système complet de **médaillons numériques** pour animaux de compagnie, basé sur des QR codes. Ce système relie un produit WooCommerce (médaillon physique) à une fiche animal accessible publiquement via un QR code.

### Vue d'ensemble du flux

```
Commande WooCommerce → Création CPT "animal" → Création CPT "medaillon_public"
                                                     ↓
                                              URL publique propre
                                          /medaillons/psi-2026-000123
                                                     ↓
                                              QR Code généré
                                        (encode l'URL publique)
                                                     ↓
                                     Scanné → Affiche la fiche animal
```

### Custom Post Types (CPT)

Le système utilise deux CPT :

| CPT              | Slug URL      | Description                                              |
|------------------|---------------|----------------------------------------------------------|
| `animal`         | —             | Fiche interne de l'animal (données ACF, propriétaire…)   |
| `medaillon_public` | `/medaillons/` | Version publique avec URL propre, liée au CPT animal   |

#### Relation entre les CPT
- Chaque `medaillon_public` est lié à un `animal` via la métadonnée `_animal_id`
- Chaque `animal` est lié à un client via `_customer_id` et à un produit via `_product_id`
- Le slug de l'URL publique utilise l'ID d'affichage : `psi-YYYY-NNNNNN` (ex : `psi-2026-000042`)

#### Champs ACF de la fiche animal

| Champ                        | Nom ACF                   | Type     |
|------------------------------|---------------------------|----------|
| **Nom**                      | `nom`                     | Texte    |
| **Type d'animal**            | `type_animal`             | Texte    |
| **Race**                     | `race`                    | Texte    |
| **Âge**                      | `age`                     | Texte    |
| **Photo**                    | `photo_de_lanimal`        | Image    |
| **Informations importantes** | `informations_importantes`| Textarea |
| **Allergies**                | `allergies`               | Textarea |
| **Téléphone**                | `telephone`               | Texte    |
| **Adresse**                  | `adresse`                 | Groupe (rue, code_postal, ville) |

### Classe `Naturapets_QRCode`

> **Fichier** : [`includes/class-qrcode.php`](includes/class-qrcode.php)

Classe PHP qui génère des QR codes **sans dépendance locale** en utilisant l'API externe [QR Server](https://goqr.me/api/).

#### Constructeur

```php
$qr = new Naturapets_QRCode($data, $size = 200, $margin = 2);
```

| Paramètre | Type   | Description                    | Défaut |
|-----------|--------|--------------------------------|--------|
| `$data`   | string | Donnée à encoder (URL)         | —      |
| `$size`   | int    | Taille en pixels               | 200    |
| `$margin` | int    | Marge autour du QR code        | 2      |

#### Méthodes

| Méthode               | Retour | Description                                      |
|-----------------------|--------|--------------------------------------------------|
| `get_image_url()`     | string | URL via Google Charts API (alternative)          |
| `get_qrserver_url()`  | string | URL via QR Server API (SVG, utilisée par défaut) |
| `get_image_tag()`     | string | Balise `<img>` complète                          |
| `get_html()`          | string | HTML complet avec bouton de téléchargement PNG    |

### Fonctions PHP helper

Trois fonctions globales sont disponibles pour générer des QR codes facilement :

```php
// Générer le HTML complet (image + bouton téléchargement)
echo naturapets_generate_qrcode($url, $size = 200, $show_download = true);

// Obtenir uniquement l'URL de l'image SVG du QR code
$svg_url = naturapets_get_qrcode_url($url, $size = 200);

// Obtenir l'URL de téléchargement du QR code en PNG
$png_url = naturapets_get_qrcode_download_url($url, $size = 300);
```

### ID d'affichage

Chaque animal reçoit un **ID d'affichage unique** au format :

```
PSI-YYYY-NNNNNN
```

- **PSI** : Préfixe fixe
- **YYYY** : Année de création de la fiche
- **NNNNNN** : ID WordPress de l'animal, paddé sur 6 chiffres

Exemple : `PSI-2026-000042` pour l'animal ID 42 créé en 2026.

Fonction : `naturapets_get_animal_display_id($animal_id)`

### URL publique de l'animal

L'URL publique d'un animal est générée par :

```php
$url = naturapets_get_animal_url($animal_id);
// → https://naturapets.fr/medaillons/psi-2026-000042
```

Cette URL est encodée dans le QR code. Quand un visiteur scanne le QR code, il est redirigé vers cette page.

### Page publique du médaillon

> **Fichier** : [`single-medaillon_public.php`](single-medaillon_public.php)

Template spécifique qui affiche les informations de l'animal au public. Cette page est ce que voit la personne qui scanne le QR code.

#### Informations affichées

| Information              | Condition d'affichage      |
|--------------------------|----------------------------|
| Photo de l'animal        | Si renseignée              |
| Nom                      | Toujours (ou « Médaillon »)|
| Informations importantes | Si renseignées             |
| Allergies                | Si renseignées             |
| Téléphone (cliquable)    | Si renseigné               |
| Adresse                  | Si renseignée              |
| Nom du propriétaire      | Si un client est lié       |

Si aucune information n'est renseignée, un message indique : *« Les informations de ce médaillon n'ont pas encore été renseignées. »*

#### Classes CSS
- `.medaillon-public` — Conteneur principal
- `.medaillon-public__card` — Carte d'informations
- `.medaillon-public__photo-wrap` — Zone photo
- `.medaillon-public__table` — Tableau des informations
- **SCSS** : `src/scss/pages/_medaillon-public.scss`

### Intégration dans l'administration

#### Liste des médaillons publics
Dans **Administration → Médaillons publics**, la liste affiche des colonnes personnalisées :
- **ID** : Identifiant WordPress
- **Titre** : Nom de l'animal + « Médaillon »
- **QR Code** : Miniature 60×60 + bouton « Télécharger »

#### Metabox sur la fiche médaillon public
Chaque fiche `medaillon_public` affiche une metabox complète avec :
- Toutes les informations de l'animal (nom, type, race, âge, photo…)
- Les infos du propriétaire (lien vers le profil utilisateur)
- Le produit WooCommerce lié
- L'URL publique
- Le **QR Code** avec bouton de téléchargement

#### Metabox sur la fiche animal
Chaque fiche `animal` dispose d'une metabox laterale « QR Code du médaillon » :
- Affiche le QR Code (180px)
- Lien de téléchargement en PNG
- Affiche l'URL publique du médaillon

#### Colonne QR Code dans la liste des animaux
La liste des CPT `animal` affiche une colonne avec une miniature du QR Code (60×60 px).

### Intégration Mon Compte (front-end)

#### Onglet « Mes médaillons »
Un endpoint WooCommerce `mes-animaux` est ajouté à la page Mon Compte. Il affiche :
- La **liste des médaillons** du client sous forme de cartes
- Pour chaque médaillon : nom, type, race, âge, ID d'affichage, statut
- Un bouton **« Modifier médaillon »** → formulaire d'édition
- Un bouton **« Voir le QR »** → ouvre une modal avec le QR Code
- Un bouton **« + Ajouter un médaillon »** → redirige vers la boutique

#### Modal QR Code
La modal affiche le QR Code en grand format (200px) et est contrôlée par JavaScript (`functions.php`, hook `wp_footer`). Fonctionnalités :
- Ouverture au clic sur « Voir le QR »
- Fermeture par clic sur le backdrop, le bouton ✕ ou la touche Échap
- Attributs d'accessibilité (`aria-hidden`, `role="dialog"`)

### Téléchargement des QR Codes

Le téléchargement passe par un **proxy admin** (`naturapets_admin_download_qrcode`) qui :
1. Vérifie les permissions (`edit_others_posts`)
2. Vérifie le nonce de sécurité
3. Récupère l'image PNG depuis l'API QR Server (300×300 px)
4. Force le téléchargement avec un nom de fichier explicite : `qrcode-animal-{id}-{nom}.png`

> 📌 Le téléchargement est réservé aux **administrateurs** (utilisateurs avec la capacité `edit_others_posts`).

### Redirection d'anciennes URLs

Le thème gère la rétrocompatibilité : les anciennes URLs au format `?animal=42&token=NP-XXXX` sont automatiquement redirigées vers la nouvelle URL propre `/medaillons/psi-2026-000042` (redirection 301).

### Synchronisation automatique

| Événement                      | Action automatique                              |
|--------------------------------|-------------------------------------------------|
| Nouvelle commande WooCommerce  | Création du CPT `animal` + `medaillon_public`   |
| Modification d'un animal       | Mise à jour du titre du `medaillon_public`      |
| Activation du thème            | Création des `medaillon_public` pour les animaux existants + flush des réécritures |

---

## Aide-mémoire rapide

### Ajouter un bloc NaturaPets à une page

1. Ouvrir la page dans l'éditeur WordPress (Gutenberg)
2. Cliquer sur **+** pour ajouter un bloc
3. Chercher « **naturapets** » ou le nom du bloc
4. Remplir les champs ACF dans le panneau latéral
5. Publier ou mettre à jour la page

### Modifier les couleurs globales

1. Modifier `theme.json` → `settings.color.palette`
2. Sauvegarder le fichier
3. Les changements sont appliqués instantanément

### Modifier le style d'un bloc

1. Modifier le fichier SCSS correspondant dans `src/scss/components/`
2. Compiler : `npm run build` (depuis le dossier du thème)
3. Uploader `assets/css/main.css` si nécessaire

### Modifier un template de bloc

1. Ouvrir `blocks/<nom-du-bloc>/render.php`
2. Modifier le HTML/PHP
3. Sauvegarder

---

*Documentation générée le 19 mars 2026 — Thème NaturaPets v1.0.10*
