<?php
/**
 * Naturapets - Thème enfant de Frost
 *
 * @package Naturapets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Version du thème pour le cache busting.
 */
define( 'NATURAPETS_VERSION', wp_get_theme()->get( 'Version' ) );

/**
 * Charger les classes du thème.
 */
require_once get_stylesheet_directory() . '/includes/class-qrcode.php';

/**
 * Helper pour récupérer l'URL d'une image ACF (gère les différents formats de retour).
 */
function naturapets_get_acf_image_url($image, $size = 'thumbnail') {
    if (empty($image)) {
        return false;
    }
    
    // Si c'est un tableau (format "Image Array")
    if (is_array($image)) {
        if (isset($image['sizes'][$size])) {
            return $image['sizes'][$size];
        } elseif (isset($image['url'])) {
            return $image['url'];
        }
    }
    
    // Si c'est un ID (format "Image ID")
    if (is_numeric($image)) {
        $url = wp_get_attachment_image_url($image, $size);
        return $url ? $url : false;
    }
    
    // Si c'est une URL (format "Image URL")
    if (is_string($image) && filter_var($image, FILTER_VALIDATE_URL)) {
        return $image;
    }
    
    return false;
}

/**
 * Charge les styles du thème parent et enfant.
 */
function naturapets_enqueue_styles() {
	// Style du thème parent Frost.
	wp_enqueue_style(
		'frost-style',
		get_template_directory_uri() . '/style.css',
		array(),
		wp_get_theme( 'frost' )->get( 'Version' )
	);

	// Style principal du thème (style.css requis par WordPress).
	wp_enqueue_style(
		'naturapets-style',
		get_stylesheet_uri(),
		array( 'frost-style' ),
		NATURAPETS_VERSION
	);

	// Police Nunito Sans (bloc Témoignage – Figma 3-56).
	wp_enqueue_style(
		'naturapets-font-nunito',
		'https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,wght@0,400;0,600;1,600&display=swap',
		array(),
		null
	);

	// Styles personnalisés compilés depuis SCSS.
	$css_file = get_stylesheet_directory() . '/assets/css/main.css';
	if ( file_exists( $css_file ) ) {
		wp_enqueue_style(
			'naturapets-main',
			get_stylesheet_directory_uri() . '/assets/css/main.css',
			array( 'naturapets-style', 'naturapets-font-nunito' ),
			filemtime( $css_file )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'naturapets_enqueue_styles' );

/**
 * Charge les styles du thème dans l'éditeur de blocs pour une preview identique au front.
 */
function naturapets_enqueue_editor_styles() {
	wp_enqueue_style(
		'frost-style',
		get_template_directory_uri() . '/style.css',
		array(),
		wp_get_theme( 'frost' )->get( 'Version' )
	);
	wp_enqueue_style(
		'naturapets-style',
		get_stylesheet_uri(),
		array( 'frost-style' ),
		NATURAPETS_VERSION
	);
	wp_enqueue_style(
		'naturapets-font-nunito',
		'https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,wght@0,400;0,600;1,600&display=swap',
		array(),
		null
	);
	$css_file = get_stylesheet_directory() . '/assets/css/main.css';
	if ( file_exists( $css_file ) ) {
		wp_enqueue_style(
			'naturapets-main',
			get_stylesheet_directory_uri() . '/assets/css/main.css',
			array( 'naturapets-style', 'naturapets-font-nunito' ),
			filemtime( $css_file )
		);
	}
}
add_action( 'enqueue_block_editor_assets', 'naturapets_enqueue_editor_styles' );

/**
 * ==========================================================================
 * BLOC ACF – Section Hero (design Figma – grille 2x2)
 * ==========================================================================
 */

/**
 * Enregistrer le bloc ACF Section Hero (nécessite ACF Pro).
 */
function naturapets_register_hero_block() {
	if ( ! function_exists( 'acf_register_block_type' ) && ! function_exists( 'register_block_type' ) ) {
		return;
	}
	$block_path = get_stylesheet_directory() . '/blocks/hero-section';
	if ( file_exists( $block_path . '/block.json' ) ) {
		register_block_type( $block_path );
	}
	$banner_path = get_stylesheet_directory() . '/blocks/hero-banner';
	if ( file_exists( $banner_path . '/block.json' ) ) {
		register_block_type( $banner_path );
	}
	$moveblock_path = get_stylesheet_directory() . '/blocks/moveblock';
	if ( file_exists( $moveblock_path . '/block.json' ) ) {
		register_block_type( $moveblock_path );
	}
	$split_cta_path = get_stylesheet_directory() . '/blocks/split-cta';
	if ( file_exists( $split_cta_path . '/block.json' ) ) {
		register_block_type( $split_cta_path );
	}
	$found_animal_path = get_stylesheet_directory() . '/blocks/found-animal';
	if ( file_exists( $found_animal_path . '/block.json' ) ) {
		register_block_type( $found_animal_path );
	}
	$testimonial_path = get_stylesheet_directory() . '/blocks/testimonial';
	if ( file_exists( $testimonial_path . '/block.json' ) ) {
		register_block_type( $testimonial_path );
	}
}
add_action( 'init', 'naturapets_register_hero_block' );

/**
 * Groupe de champs ACF pour le bloc Section Hero.
 */
function naturapets_hero_block_field_group() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group(
		array(
			'key'                   => 'group_naturapets_hero_section',
			'title'                 => 'Bloc Section Hero – Champs',
			'fields'                => array(
				array(
					'key'   => 'field_hero_texte_haut',
					'label' => 'Texte en haut à droite',
					'name'  => 'texte_haut',
					'type'  => 'textarea',
					'rows'  => 3,
					'placeholder' => "Un système\nsimple et efficace",
				),
				array(
					'key'   => 'field_hero_texte_bas',
					'label' => 'Texte en bas à gauche',
					'name'  => 'texte_bas',
					'type'  => 'textarea',
					'rows'  => 3,
					'placeholder' => "Pour nos amis les\nbêtes",
				),
				array(
					'key'           => 'field_hero_image_haut_gauche',
					'label'         => 'Image en haut à gauche',
					'name'          => 'image_haut_gauche',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
				),
				array(
					'key'           => 'field_hero_image_bas_droite',
					'label'         => 'Image en bas à droite',
					'name'          => 'image_bas_droite',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'block',
						'operator' => '==',
						'value'    => 'naturapets/hero-section',
					),
				),
			),
		)
	);
}
add_action( 'acf/init', 'naturapets_hero_block_field_group' );

/**
 * Groupe de champs ACF pour le bloc Bannière Hero (design Figma 1-101).
 */
function naturapets_hero_banner_field_group() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group(
		array(
			'key'                   => 'group_naturapets_hero_banner',
			'title'                 => 'Bloc Bannière Hero – Champs',
			'fields'                => array(
				array(
					'key'           => 'field_hero_banner_image',
					'label'         => 'Image de fond',
					'name'          => 'hero_banner_image',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
					'instructions'  => 'Affichée si aucune vidéo n\'est renseignée.',
				),
				array(
					'key'           => 'field_hero_banner_video',
					'label'         => 'Vidéo de fond',
					'name'          => 'hero_banner_video',
					'type'          => 'file',
					'return_format' => 'array',
					'mime_types'    => 'mp4,webm',
					'instructions'  => 'Prioritaire sur l\'image. Formats : MP4, WebM.',
				),
				array(
					'key'         => 'field_hero_banner_titre',
					'label'       => 'Titre',
					'name'        => 'hero_banner_titre',
					'type'        => 'text',
					'placeholder' => 'Naturapets',
				),
				array(
					'key'         => 'field_hero_banner_slogan',
					'label'       => 'Slogan',
					'name'        => 'hero_banner_slogan',
					'type'        => 'text',
					'placeholder' => 'La tranquillité au bout du collier',
				),
				array(
					'key'         => 'field_hero_banner_bouton_texte',
					'label'       => 'Texte du bouton',
					'name'        => 'hero_banner_bouton_texte',
					'type'        => 'text',
					'placeholder' => 'Découvrir',
				),
				array(
					'key'         => 'field_hero_banner_bouton_url',
					'label'       => 'URL du bouton',
					'name'        => 'hero_banner_bouton_url',
					'type'        => 'url',
					'placeholder' => 'https://',
				),
				array(
					'key'           => 'field_hero_banner_bouton_taille',
					'label'         => 'Taille du bouton',
					'name'          => 'hero_banner_bouton_taille',
					'type'          => 'select',
					'choices'       => array(
						''          => __( 'Défaut (thème)', 'naturapets' ),
						'x-small'   => __( 'Très petit', 'naturapets' ),
						'small'     => __( 'Petit', 'naturapets' ),
						'medium'    => __( 'Moyen', 'naturapets' ),
						'large'     => __( 'Grand', 'naturapets' ),
						'x-large'   => __( 'Très grand', 'naturapets' ),
					),
					'default_value' => '',
					'instructions'  => __( 'Utilise les presets de typographie du thème.', 'naturapets' ),
				),
				array(
					'key'           => 'field_hero_banner_titre_taille',
					'label'         => 'Taille du titre',
					'name'          => 'hero_banner_titre_taille',
					'type'          => 'select',
					'choices'       => array(
						''          => __( 'Défaut (ou panneau Typographie du bloc)', 'naturapets' ),
						'x-small'   => __( 'Très petit', 'naturapets' ),
						'small'     => __( 'Petit', 'naturapets' ),
						'medium'    => __( 'Moyen', 'naturapets' ),
						'large'     => __( 'Grand', 'naturapets' ),
						'x-large'   => __( 'Très grand', 'naturapets' ),
						'max-36'    => __( '36px', 'naturapets' ),
						'max-48'    => __( '48px', 'naturapets' ),
						'max-60'    => __( '60px', 'naturapets' ),
						'max-72'    => __( '72px', 'naturapets' ),
					),
					'default_value' => '',
					'instructions'  => __( 'Optionnel. Sinon, utilisez le panneau « Typographie » dans la barre latérale du bloc.', 'naturapets' ),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'block',
						'operator' => '==',
						'value'    => 'naturapets/hero-banner',
					),
				),
			),
		)
	);
}
add_action( 'acf/init', 'naturapets_hero_banner_field_group' );

/**
 * Groupe de champs ACF pour le bloc Section deux colonnes (design Figma 3-27).
 */
function naturapets_split_cta_field_group() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group(
		array(
			'key'                   => 'group_naturapets_split_cta',
			'title'                 => 'Bloc Section deux colonnes – Champs',
			'fields'                => array(
				array(
					'key'           => 'field_split_cta_image_gauche',
					'label'         => 'Image colonne gauche',
					'name'          => 'split_cta_image_gauche',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
					'instructions'  => 'Optionnel. Si vide, fond gris clair affiché.',
				),
				array(
					'key'         => 'field_split_cta_titre',
					'label'       => 'Titre',
					'name'        => 'split_cta_titre',
					'type'        => 'text',
					'placeholder' => 'GROS TITRE',
				),
				array(
					'key'         => 'field_split_cta_texte',
					'label'       => 'Texte',
					'name'        => 'split_cta_texte',
					'type'        => 'textarea',
					'rows'        => 4,
					'placeholder' => 'Lorem ipsum dolor sit amet...',
				),
				array(
					'key'         => 'field_split_cta_bouton_texte',
					'label'       => 'Texte du bouton',
					'name'        => 'split_cta_bouton_texte',
					'type'        => 'text',
					'placeholder' => 'Découvrir',
				),
				array(
					'key'         => 'field_split_cta_bouton_url',
					'label'       => 'URL du bouton',
					'name'        => 'split_cta_bouton_url',
					'type'        => 'url',
					'placeholder' => 'https://',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'block',
						'operator' => '==',
						'value'    => 'naturapets/split-cta',
					),
				),
			),
		)
	);
}
add_action( 'acf/init', 'naturapets_split_cta_field_group' );

/**
 * Groupe de champs ACF pour le bloc J'ai trouvé un animal (design Figma 3-29).
 */
function naturapets_found_animal_field_group() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group(
		array(
			'key'                   => 'group_naturapets_found_animal',
			'title'                 => "Bloc J'ai trouvé un animal – Champs",
			'fields'                => array(
				array(
					'key'         => 'field_found_animal_titre',
					'label'       => 'Titre',
					'name'        => 'found_animal_titre',
					'type'        => 'text',
					'placeholder' => "J'ai trouvé un animal",
				),
				array(
					'key'         => 'field_found_animal_placeholder',
					'label'       => 'Placeholder du champ',
					'name'        => 'found_animal_placeholder',
					'type'        => 'text',
					'placeholder' => 'entrer le numéro du médaillon',
				),
				array(
					'key'         => 'field_found_animal_bouton_texte',
					'label'       => 'Texte du bouton',
					'name'        => 'found_animal_bouton_texte',
					'type'        => 'text',
					'placeholder' => 'Chercher',
				),
				array(
					'key'         => 'field_found_animal_form_action_url',
					'label'       => 'URL de la page de recherche',
					'name'        => 'found_animal_form_action_url',
					'type'        => 'url',
					'placeholder' => 'https://',
					'instructions' => 'Page vers laquelle le formulaire envoie (GET). Si vide, accueil.',
				),
				array(
					'key'         => 'field_found_animal_param_name',
					'label'       => 'Nom du paramètre (médaillon)',
					'name'        => 'found_animal_param_name',
					'type'        => 'text',
					'placeholder' => 'medaillon',
					'instructions' => 'Nom du paramètre GET pour le numéro de médaillon.',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'block',
						'operator' => '==',
						'value'    => 'naturapets/found-animal',
					),
				),
			),
		)
	);
}
add_action( 'acf/init', 'naturapets_found_animal_field_group' );

/**
 * Groupe de champs ACF pour le bloc Témoignage (design Figma 3-56).
 */
function naturapets_testimonial_field_group() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group(
		array(
			'key'                   => 'group_naturapets_testimonial',
			'title'                 => 'Bloc Témoignage – Champs',
			'fields'                => array(
				array(
					'key'           => 'field_testimonial_photo',
					'label'         => 'Photo (cercle)',
					'name'          => 'testimonial_photo',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
					'instructions'  => __( 'Optionnel. Si vide, un cercle gris est affiché.', 'naturapets' ),
				),
				array(
					'key'         => 'field_testimonial_citation',
					'label'       => 'Citation',
					'name'        => 'testimonial_citation',
					'type'        => 'textarea',
					'rows'        => 3,
					'placeholder' => __( "J'ai retrouvé facilement Rantanplan et j'en suis très rassurée!", 'naturapets' ),
				),
				array(
					'key'         => 'field_testimonial_auteur',
					'label'       => 'Auteur',
					'name'        => 'testimonial_auteur',
					'type'        => 'text',
					'placeholder' => 'Liliane',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'block',
						'operator' => '==',
						'value'    => 'naturapets/testimonial',
					),
				),
			),
		)
	);
}
add_action( 'acf/init', 'naturapets_testimonial_field_group' );

/**
 * ==========================================================================
 * BLOC MOVEBLOCK – Bibliothèque d’effets GSAP
 * ==========================================================================
 */

/**
 * Groupe de champs ACF pour le bloc Moveblock (effet, options, sélecteur cible).
 */
function naturapets_moveblock_field_group() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	$effect_choices = array(
		'fadeIn'         => 'Fade In',
		'fadeOut'        => 'Fade Out',
		'fadeFromTo'     => 'Fade In/Out',
		'slideInLeft'    => 'Slide from left',
		'slideInRight'   => 'Slide from right',
		'slideInTop'     => 'Slide from top',
		'slideInBottom'  => 'Slide from bottom',
		'slideOutLeft'   => 'Slide to left',
		'slideOutRight'  => 'Slide to right',
		'moveX'          => 'Move X',
		'moveY'          => 'Move Y',
		'scaleUp'        => 'Scale up',
		'scaleDown'      => 'Scale down',
		'scaleFromTo'    => 'Scale pulse',
		'scaleX'         => 'Scale X',
		'scaleY'         => 'Scale Y',
		'rotateIn'       => 'Rotate in',
		'rotateOut'      => 'Rotate out',
		'rotateY'        => 'Rotate Y (3D)',
		'rotateX'        => 'Rotate X (3D)',
		'skewIn'         => 'Skew in',
		'skewOut'        => 'Skew out',
		'popIn'          => 'Pop in',
		'blurIn'         => 'Blur in',
		'blurOut'        => 'Blur out',
		'dropIn'         => 'Drop in',
		'bounceIn'       => 'Bounce in',
		'colorChange'    => 'Color change',
		'backgroundColor'=> 'Background color',
	);
	$ease_choices = array(
		'none'           => 'none',
		'power1.in'      => 'power1.in',
		'power1.out'     => 'power1.out',
		'power1.inOut'   => 'power1.inOut',
		'power2.in'      => 'power2.in',
		'power2.out'     => 'power2.out',
		'power2.inOut'   => 'power2.inOut',
		'power3.in'      => 'power3.in',
		'power3.out'     => 'power3.out',
		'power3.inOut'   => 'power3.inOut',
		'power4.in'      => 'power4.in',
		'power4.out'     => 'power4.out',
		'power4.inOut'   => 'power4.inOut',
		'back.in'        => 'back.in',
		'back.out'       => 'back.out',
		'back.inOut'     => 'back.inOut',
		'bounce.in'      => 'bounce.in',
		'bounce.out'     => 'bounce.out',
		'bounce.inOut'   => 'bounce.inOut',
		'circ.in'        => 'circ.in',
		'circ.out'       => 'circ.out',
		'circ.inOut'     => 'circ.inOut',
		'elastic.in'     => 'elastic.in',
		'elastic.out'    => 'elastic.out',
		'elastic.inOut'  => 'elastic.inOut',
		'expo.in'        => 'expo.in',
		'expo.out'       => 'expo.out',
		'expo.inOut'     => 'expo.inOut',
		'sine.in'        => 'sine.in',
		'sine.out'       => 'sine.out',
		'sine.inOut'     => 'sine.inOut',
	);
	acf_add_local_field_group(
		array(
			'key'                   => 'group_naturapets_moveblock',
			'title'                 => 'Moveblock – Animation GSAP',
			'fields'                => array(
				array(
					'key'          => 'field_moveblock_effect',
					'label'        => 'Effet GSAP',
					'name'         => 'effect',
					'type'         => 'select',
					'choices'      => $effect_choices,
					'default_value'=> 'fadeIn',
				),
				array(
					'key'          => 'field_moveblock_target',
					'label'        => 'Cibler l’élément (sélecteur CSS)',
					'name'         => 'target_selector',
					'type'         => 'text',
					'placeholder'  => 'ex: .hero-title, #nav, .ma-classe',
					'instructions' => 'Sélecteur CSS de l’élément à animer sur la page (classe, id, etc.).',
				),
				array(
					'key'          => 'field_moveblock_duration',
					'label'        => 'Durée (secondes)',
					'name'         => 'duration',
					'type'         => 'number',
					'min'          => 0,
					'step'         => 0.1,
					'default_value'=> 1,
				),
				array(
					'key'          => 'field_moveblock_delay',
					'label'        => 'Délai (secondes)',
					'name'         => 'delay',
					'type'         => 'number',
					'min'          => 0,
					'step'         => 0.1,
					'default_value'=> 0,
				),
				array(
					'key'          => 'field_moveblock_ease',
					'label'        => 'Ease',
					'name'         => 'ease',
					'type'         => 'select',
					'choices'      => $ease_choices,
					'default_value'=> 'power2.out',
				),
				array(
					'key'          => 'field_moveblock_stagger',
					'label'        => 'Stagger (secondes)',
					'name'         => 'stagger',
					'type'         => 'number',
					'min'          => 0,
					'step'         => 0.05,
					'default_value'=> 0,
					'instructions' => 'Décalage entre chaque élément si le sélecteur en cible plusieurs. 0 = pas de stagger.',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'block',
						'operator' => '==',
						'value'    => 'naturapets/moveblock',
					),
				),
			),
		)
	);
}
add_action( 'acf/init', 'naturapets_moveblock_field_group' );

/**
 * Enqueue GSAP et le script Moveblock sur le front quand le bloc est utilisé.
 */
function naturapets_enqueue_moveblock_assets() {
	if ( ! function_exists( 'has_blocks' ) ) {
		return;
	}
	global $post;
	if ( ! $post || ! has_blocks( $post->post_content ) ) {
		return;
	}
	if ( strpos( $post->post_content, 'naturapets/moveblock' ) === false ) {
		return;
	}
	$theme_dir = get_stylesheet_directory();
	$theme_uri = get_stylesheet_directory_uri();
	$gsap_path = $theme_dir . '/node_modules/gsap/dist/gsap.min.js';
	if ( file_exists( $gsap_path ) ) {
		wp_enqueue_script(
			'gsap',
			$theme_uri . '/node_modules/gsap/dist/gsap.min.js',
			array(),
			'3.12',
			true
		);
	} else {
		wp_enqueue_script(
			'gsap',
			'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js',
			array(),
			'3.12.5',
			true
		);
	}
	$moveblock_js = $theme_dir . '/assets/js/moveblock.js';
	if ( file_exists( $moveblock_js ) ) {
		wp_enqueue_script(
			'naturapets-moveblock',
			$theme_uri . '/assets/js/moveblock.js',
			array( 'gsap' ),
			filemtime( $moveblock_js ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'naturapets_enqueue_moveblock_assets' );

/**
 * ==========================================================================
 * PRODUITS : ID unique pour chaque produit
 * ==========================================================================
 */

/**
 * Générer un ID unique pour un produit.
 */
function naturapets_generate_product_unique_id() {
    $prefix = 'NP';
    $unique = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
    return $prefix . '-' . $unique;
}

/**
 * Récupérer ou créer l'ID unique d'un produit.
 */
function naturapets_get_product_unique_id($product_id) {
    $unique_id = get_post_meta($product_id, '_naturapets_unique_id', true);
    
    if (!$unique_id) {
        $unique_id = naturapets_generate_product_unique_id();
        update_post_meta($product_id, '_naturapets_unique_id', $unique_id);
    }
    
    return $unique_id;
}

/**
 * Ajouter le champ ID unique dans l'onglet Inventaire du produit.
 */
function naturapets_add_product_unique_id_field() {
    global $post;
    
    $unique_id = naturapets_get_product_unique_id($post->ID);
    
    echo '<div class="options_group">';
    
    woocommerce_wp_text_input(array(
        'id' => '_naturapets_unique_id',
        'label' => 'ID Unique Naturapets',
        'description' => 'Identifiant unique du produit (utilisé pour les QR codes)',
        'desc_tip' => true,
        'value' => $unique_id,
        'custom_attributes' => array('readonly' => 'readonly'),
    ));
    
    echo '</div>';
}
add_action('woocommerce_product_options_inventory_product_data', 'naturapets_add_product_unique_id_field');

/**
 * Sauvegarder l'ID unique du produit (générer si vide).
 */
function naturapets_save_product_unique_id($post_id) {
    $unique_id = get_post_meta($post_id, '_naturapets_unique_id', true);
    
    if (!$unique_id) {
        $unique_id = naturapets_generate_product_unique_id();
        update_post_meta($post_id, '_naturapets_unique_id', $unique_id);
    }
}
add_action('woocommerce_process_product_meta', 'naturapets_save_product_unique_id');

/**
 * Ajouter une colonne ID Unique dans la liste des produits.
 */
function naturapets_add_product_unique_id_column($columns) {
    $new_columns = array();
    
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        
        if ($key === 'sku') {
            $new_columns['unique_id'] = 'ID Unique';
        }
    }
    
    return $new_columns;
}
add_filter('manage_edit-product_columns', 'naturapets_add_product_unique_id_column');

/**
 * Afficher l'ID unique dans la colonne.
 */
function naturapets_product_unique_id_column_content($column, $post_id) {
    if ($column === 'unique_id') {
        echo '<code>' . esc_html(naturapets_get_product_unique_id($post_id)) . '</code>';
    }
}
add_action('manage_product_posts_custom_column', 'naturapets_product_unique_id_column_content', 10, 2);

// Enregistrer l'endpoint
function naturapets_add_animals_endpoint() {
    add_rewrite_endpoint('mes-animaux', EP_ROOT | EP_PAGES);
}
add_action('init', 'naturapets_add_animals_endpoint');

/**
 * Ajouter la règle de réécriture pour les fiches animaux publiques.
 */
function naturapets_add_animal_rewrite_rules() {
    add_rewrite_rule(
        '^fiche-animal/?$',
        'index.php?naturapets_animal_page=1',
        'top'
    );
}
add_action('init', 'naturapets_add_animal_rewrite_rules');

/**
 * Ajouter la query var pour la page animal.
 */
function naturapets_add_query_vars($vars) {
    $vars[] = 'naturapets_animal_page';
    return $vars;
}
add_filter('query_vars', 'naturapets_add_query_vars');

// Ajouter au menu
function naturapets_add_animals_menu_item($items) {
    $new_items = array();
    
    foreach ($items as $key => $value) {
        $new_items[$key] = $value;
        
        // Insérer après "Commandes"
        if ($key === 'orders') {
            $new_items['mes-animaux'] = 'Gérer mes animaux';
        }
    }
    
    return $new_items;
}
add_filter('woocommerce_account_menu_items', 'naturapets_add_animals_menu_item');

// Contenu de la page
function naturapets_animals_endpoint_content() {
    $current_user_id = get_current_user_id();
    
    // Vérifie si on affiche un animal spécifique (pour édition)
    $animal_id = get_query_var('mes-animaux');
    
    if ($animal_id && is_numeric($animal_id)) {
        naturapets_display_animal_form($animal_id, $current_user_id);
    } else {
        naturapets_display_animals_list($current_user_id);
    }
}
add_action('woocommerce_account_mes-animaux_endpoint', 'naturapets_animals_endpoint_content');

function naturapets_display_animals_list($customer_id) {
    $animals = get_posts(array(
        'post_type' => 'animal',
        'meta_key' => '_customer_id',
        'meta_value' => $customer_id,
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
    ));
    
    echo '<h2>Mes Animaux</h2>';
    
    if (empty($animals)) {
        echo '<p>Vous n\'avez pas encore d\'animaux enregistrés. Ils apparaîtront ici après votre première commande.</p>';
        return;
    }
    
    echo '<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table">';
    echo '<thead><tr>';
    echo '<th>Photo</th>';
    echo '<th>Nom</th>';
    echo '<th>Produit associé</th>';
    echo '<th>Commande</th>';
    echo '<th>Actions</th>';
    echo '</tr></thead>';
    echo '<tbody>';
    
    foreach ($animals as $animal) {
        // Utiliser les champs ACF
        $nom = get_field('nom', $animal->ID);
        $photo = get_field('photo_de_lanimal', $animal->ID);
        $product_id = get_post_meta($animal->ID, '_product_id', true);
        $order_id = get_post_meta($animal->ID, '_order_id', true);
        
        $product = wc_get_product($product_id);
        $order = wc_get_order($order_id);
        
        $display_name = $nom ? esc_html($nom) : '<em>Non renseigné</em>';
        
        $photo_url = naturapets_get_acf_image_url($photo, 'thumbnail');
        
        echo '<tr>';
        echo '<td style="width: 60px;">';
        if ($photo_url) {
            echo '<img src="' . esc_url($photo_url) . '" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;" />';
        } else {
            echo '<span style="display: inline-block; width: 50px; height: 50px; background: #eee; border-radius: 50%;"></span>';
        }
        echo '</td>';
        echo '<td>' . $display_name . '</td>';
        echo '<td>' . ($product ? esc_html($product->get_name()) : 'N/A') . '</td>';
        echo '<td>' . ($order ? '#' . $order->get_order_number() : 'N/A') . '</td>';
        echo '<td>';
        echo '<a href="' . esc_url(wc_get_account_endpoint_url('mes-animaux') . $animal->ID) . '" class="woocommerce-button button">Modifier</a>';
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
}

function naturapets_display_animal_form($animal_id, $customer_id) {
    $animal = get_post($animal_id);
    
    // Vérifier que l'animal appartient bien au client
    $animal_customer = get_post_meta($animal_id, '_customer_id', true);
    if (!$animal || $animal->post_type !== 'animal' || $animal_customer != $customer_id) {
        echo '<p>Animal non trouvé.</p>';
        echo '<a href="' . esc_url(wc_get_account_endpoint_url('mes-animaux')) . '">&larr; Retour à la liste</a>';
        return;
    }
    
    // Traitement du formulaire avec champs ACF
    if (isset($_POST['naturapets_save_animal']) && wp_verify_nonce($_POST['_wpnonce'], 'save_animal_' . $animal_id)) {
        // Champs ACF
        update_field('nom', sanitize_text_field($_POST['nom']), $animal_id);
        update_field('informations_importantes', sanitize_textarea_field($_POST['informations_importantes']), $animal_id);
        update_field('allergies', sanitize_textarea_field($_POST['allergies']), $animal_id);
        update_field('telephone', sanitize_text_field($_POST['telephone']), $animal_id);
        
        // Groupe Adresse
        $adresse = array(
            'rue' => sanitize_text_field($_POST['adresse_rue']),
            'code_postal' => sanitize_text_field($_POST['adresse_code_postal']),
            'ville' => sanitize_text_field($_POST['adresse_ville']),
        );
        update_field('adresse', $adresse, $animal_id);
        
        // Gestion de l'upload de photo
        if (!empty($_FILES['photo_de_lanimal']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            
            $attachment_id = media_handle_upload('photo_de_lanimal', $animal_id);
            
            if (!is_wp_error($attachment_id)) {
                update_field('photo_de_lanimal', $attachment_id, $animal_id);
            }
        }
        
        // Mettre à jour le titre du post avec le nom de l'animal
        if (!empty($_POST['nom'])) {
            wp_update_post(array(
                'ID' => $animal_id,
                'post_title' => sanitize_text_field($_POST['nom']),
            ));
        }
        
        wc_add_notice('Les informations de votre animal ont été enregistrées.', 'success');
    }
    
    // Récupérer les données ACF
    $nom = get_field('nom', $animal_id);
    $photo = get_field('photo_de_lanimal', $animal_id);
    $informations = get_field('informations_importantes', $animal_id);
    $allergies = get_field('allergies', $animal_id);
    $telephone = get_field('telephone', $animal_id);
    $adresse = get_field('adresse', $animal_id);
    
    $product_id = get_post_meta($animal_id, '_product_id', true);
    $product = wc_get_product($product_id);
    
    ?>
    <p><a href="<?php echo esc_url(wc_get_account_endpoint_url('mes-animaux')); ?>">&larr; Retour à la liste</a></p>
    
    <h2>Informations de l'animal</h2>
    
    <?php if ($product): ?>
    <p><strong>Produit associé :</strong> <?php echo esc_html($product->get_name()); ?></p>
    <?php endif; ?>
    
    <?php 
    wc_print_notices(); 
    
    // Afficher le QR code
    naturapets_add_qrcode_to_animal_form($animal_id);
    ?>
    
    <form method="post" enctype="multipart/form-data" class="woocommerce-EditAccountForm edit-account">
        <?php wp_nonce_field('save_animal_' . $animal_id); ?>
        
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="nom">Nom de l'animal <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" 
                   name="nom" id="nom" value="<?php echo esc_attr($nom); ?>" required />
        </p>
        
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="photo_de_lanimal">Photo de l'animal</label>
            <?php 
            $photo_url_medium = naturapets_get_acf_image_url($photo, 'medium');
            if ($photo_url_medium): ?>
            <div style="margin-bottom: 10px;">
                <img src="<?php echo esc_url($photo_url_medium); ?>" alt="" style="max-width: 200px; height: auto; border-radius: 8px;" />
            </div>
            <?php endif; ?>
            <input type="file" name="photo_de_lanimal" id="photo_de_lanimal" accept="image/*" />
            <small style="display: block; margin-top: 5px; color: #666;">Formats acceptés : JPG, PNG, GIF. Max 2 Mo.</small>
        </p>
        
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="informations_importantes">Informations importantes</label>
            <textarea class="woocommerce-Input woocommerce-Input--textarea input-text" 
                      name="informations_importantes" id="informations_importantes" rows="4"><?php echo esc_textarea($informations); ?></textarea>
        </p>
        
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="allergies">Allergies</label>
            <textarea class="woocommerce-Input woocommerce-Input--textarea input-text" 
                      name="allergies" id="allergies" rows="3"><?php echo esc_textarea($allergies); ?></textarea>
        </p>
        
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="telephone">Téléphone</label>
            <input type="tel" class="woocommerce-Input woocommerce-Input--text input-text" 
                   name="telephone" id="telephone" value="<?php echo esc_attr($telephone); ?>" />
        </p>
        
        <fieldset style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <legend style="font-weight: bold; padding: 0 10px;">Adresse</legend>
            
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="adresse_rue">Rue</label>
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" 
                       name="adresse_rue" id="adresse_rue" value="<?php echo esc_attr($adresse['rue'] ?? ''); ?>" />
            </p>
            
            <p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
                <label for="adresse_code_postal">Code postal</label>
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" 
                       name="adresse_code_postal" id="adresse_code_postal" value="<?php echo esc_attr($adresse['code_postal'] ?? ''); ?>" />
            </p>
            
            <p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
                <label for="adresse_ville">Ville</label>
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" 
                       name="adresse_ville" id="adresse_ville" value="<?php echo esc_attr($adresse['ville'] ?? ''); ?>" />
            </p>
        </fieldset>
        
        <div class="clear"></div>
        
        <p>
            <button type="submit" class="woocommerce-Button button" name="naturapets_save_animal" value="1">
                Enregistrer les modifications
            </button>
        </p>
    </form>
    <?php
}

/**
 * Créer automatiquement un CPT Animal à chaque commande.
 */
function naturapets_create_animal_on_order($order_id) {
    $order = wc_get_order($order_id);
    
    if (!$order) {
        return;
    }
    
    // Vérifier si des animaux ont déjà été créés pour cette commande
    $existing = get_posts(array(
        'post_type' => 'animal',
        'meta_key' => '_order_id',
        'meta_value' => $order_id,
        'posts_per_page' => 1,
    ));
    
    if (!empty($existing)) {
        return;
    }
    
    $customer_id = $order->get_customer_id();
    
    if (!$customer_id) {
        return;
    }
    
    foreach ($order->get_items() as $item_id => $item) {
        $product_id = $item->get_product_id();
        $product = wc_get_product($product_id);
        $quantity = $item->get_quantity();
        
        for ($i = 0; $i < $quantity; $i++) {
            $animal_id = wp_insert_post(array(
                'post_type' => 'animal',
                'post_title' => sprintf('Animal - %s #%d', $product->get_name(), $i + 1),
                'post_status' => 'publish',
                'post_author' => $customer_id,
            ));
            
            if ($animal_id && !is_wp_error($animal_id)) {
                update_post_meta($animal_id, '_customer_id', $customer_id);
                update_post_meta($animal_id, '_product_id', $product_id);
                update_post_meta($animal_id, '_order_id', $order_id);
                update_post_meta($animal_id, '_order_item_id', $item_id);
            }
        }
    }
}
add_action('woocommerce_order_status_processing', 'naturapets_create_animal_on_order');
add_action('woocommerce_order_status_completed', 'naturapets_create_animal_on_order');

/**
 * ==========================================================================
 * ADMIN : Affichage des animaux dans le backoffice
 * ==========================================================================
 */

/**
 * Ajouter une metabox sur les commandes WooCommerce pour voir les animaux liés.
 */
function naturapets_add_order_animals_metabox() {
    $screen = class_exists('\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController') 
        && wc_get_container()->get(\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled()
        ? wc_get_page_screen_id('shop-order')
        : 'shop_order';

    add_meta_box(
        'naturapets_order_animals',
        'Animaux liés à cette commande',
        'naturapets_order_animals_metabox_content',
        $screen,
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'naturapets_add_order_animals_metabox');

/**
 * Contenu de la metabox des animaux sur une commande.
 */
function naturapets_order_animals_metabox_content($post_or_order) {
    $order_id = $post_or_order instanceof WC_Order ? $post_or_order->get_id() : $post_or_order->ID;
    
    $animals = get_posts(array(
        'post_type' => 'animal',
        'meta_key' => '_order_id',
        'meta_value' => $order_id,
        'posts_per_page' => -1,
        'post_status' => 'any',
    ));
    
    if (empty($animals)) {
        echo '<p>Aucun animal associé à cette commande.</p>';
        echo '<p><em>Les animaux sont créés automatiquement lorsque la commande passe en statut "En cours" ou "Terminée".</em></p>';
        return;
    }
    
    echo '<table class="widefat striped">';
    echo '<thead><tr>';
    echo '<th>Photo</th>';
    echo '<th>Nom</th>';
    echo '<th>Téléphone</th>';
    echo '<th>Allergies</th>';
    echo '<th>Produit</th>';
    echo '<th>Actions</th>';
    echo '</tr></thead>';
    echo '<tbody>';
    
    foreach ($animals as $animal) {
        // Utiliser les champs ACF
        $nom = get_field('nom', $animal->ID);
        $photo = get_field('photo-animal', $animal->ID);
        $telephone = get_field('telephone', $animal->ID);
        $allergies = get_field('allergies', $animal->ID);
        $product_id = get_post_meta($animal->ID, '_product_id', true);
        $product = wc_get_product($product_id);
        
        $photo_url = naturapets_get_acf_image_url($photo, 'thumbnail');
        
        echo '<tr>';
        echo '<td style="width: 50px;">';
        if ($photo_url) {
            echo '<img src="' . esc_url($photo_url) . '" alt="" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;" />';
        } else {
            echo '<span style="display: inline-block; width: 40px; height: 40px; background: #ddd; border-radius: 50%;"></span>';
        }
        echo '</td>';
        echo '<td><strong>' . ($nom ? esc_html($nom) : '<em>Non renseigné</em>') . '</strong></td>';
        echo '<td>' . ($telephone ? esc_html($telephone) : '-') . '</td>';
        echo '<td>' . ($allergies ? esc_html(wp_trim_words($allergies, 5, '...')) : '-') . '</td>';
        echo '<td>' . ($product ? esc_html($product->get_name()) : '-') . '</td>';
        echo '<td><a href="' . get_edit_post_link($animal->ID) . '" class="button button-small">Modifier</a></td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
}

/**
 * Ajouter des colonnes personnalisées dans la liste des animaux.
 */
function naturapets_animal_admin_columns($columns) {
    $new_columns = array();
    
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        
        if ($key === 'title') {
            $new_columns['customer'] = 'Client';
            $new_columns['product'] = 'Produit';
            $new_columns['order'] = 'Commande';
            $new_columns['animal_info'] = 'Infos animal';
        }
    }
    
    return $new_columns;
}
add_filter('manage_animal_posts_columns', 'naturapets_animal_admin_columns');

/**
 * Remplir les colonnes personnalisées.
 */
function naturapets_animal_admin_columns_content($column, $post_id) {
    switch ($column) {
        case 'customer':
            $customer_id = get_post_meta($post_id, '_customer_id', true);
            if ($customer_id) {
                $customer = get_user_by('id', $customer_id);
                if ($customer) {
                    echo '<a href="' . admin_url('user-edit.php?user_id=' . $customer_id) . '">';
                    echo esc_html($customer->display_name);
                    echo '</a>';
                    echo '<br><small>' . esc_html($customer->user_email) . '</small>';
                } else {
                    echo '<em>Client supprimé</em>';
                }
            } else {
                echo '-';
            }
            break;
            
        case 'product':
            $product_id = get_post_meta($post_id, '_product_id', true);
            if ($product_id) {
                $product = wc_get_product($product_id);
                if ($product) {
                    echo '<a href="' . get_edit_post_link($product_id) . '">';
                    echo esc_html($product->get_name());
                    echo '</a>';
                } else {
                    echo '<em>Produit supprimé</em>';
                }
            } else {
                echo '-';
            }
            break;
            
        case 'order':
            $order_id = get_post_meta($post_id, '_order_id', true);
            if ($order_id) {
                $order = wc_get_order($order_id);
                if ($order) {
                    echo '<a href="' . $order->get_edit_order_url() . '">';
                    echo '#' . $order->get_order_number();
                    echo '</a>';
                } else {
                    echo '<em>Commande supprimée</em>';
                }
            } else {
                echo '-';
            }
            break;
            
        case 'animal_info':
            // Utiliser les champs ACF
            $nom = get_field('nom', $post_id);
            $telephone = get_field('telephone', $post_id);
            $allergies = get_field('allergies', $post_id);
            
            if ($nom || $telephone || $allergies) {
                $infos = array();
                if ($telephone) $infos[] = $telephone;
                if ($allergies) $infos[] = 'Allergies: ' . wp_trim_words($allergies, 3, '...');
                echo implode(' • ', $infos);
                
                if (!$nom) {
                    echo '<br><span style="color: #d63638;"><em>Nom non renseigné</em></span>';
                }
            } else {
                echo '<span style="color: #d63638;"><em>Non renseigné</em></span>';
            }
            break;
    }
}
add_action('manage_animal_posts_custom_column', 'naturapets_animal_admin_columns_content', 10, 2);

/**
 * Rendre les colonnes triables.
 */
function naturapets_animal_sortable_columns($columns) {
    $columns['customer'] = 'customer';
    $columns['order'] = 'order';
    return $columns;
}
add_filter('manage_edit-animal_sortable_columns', 'naturapets_animal_sortable_columns');

/**
 * Ajouter un lien vers les animaux du client dans la page utilisateur.
 */
function naturapets_user_animals_section($user) {
    $animals = get_posts(array(
        'post_type' => 'animal',
        'meta_key' => '_customer_id',
        'meta_value' => $user->ID,
        'posts_per_page' => -1,
        'post_status' => 'any',
    ));
    
    ?>
    <h2>Animaux du client</h2>
    <table class="form-table">
        <tr>
            <th>Animaux enregistrés</th>
            <td>
                <?php if (empty($animals)): ?>
                    <p>Aucun animal enregistré pour ce client.</p>
                <?php else: ?>
                    <ul>
                    <?php foreach ($animals as $animal): 
                        $nom = get_post_meta($animal->ID, '_animal_nom', true);
                        $product_id = get_post_meta($animal->ID, '_product_id', true);
                        $product = wc_get_product($product_id);
                    ?>
                        <li>
                            <a href="<?php echo get_edit_post_link($animal->ID); ?>">
                                <strong><?php echo $nom ? esc_html($nom) : 'Animal #' . $animal->ID; ?></strong>
                            </a>
                            <?php if ($product): ?>
                                - <?php echo esc_html($product->get_name()); ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                    <p>
                        <a href="<?php echo admin_url('edit.php?post_type=animal&meta_key=_customer_id&meta_value=' . $user->ID); ?>" class="button">
                            Voir tous les animaux de ce client
                        </a>
                    </p>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'naturapets_user_animals_section');
add_action('edit_user_profile', 'naturapets_user_animals_section');

/**
 * ==========================================================================
 * QR CODE : Génération et affichage
 * ==========================================================================
 */

/**
 * Générer l'URL unique de la fiche animal.
 * Le token est basé sur l'ID unique du produit associé.
 */
function naturapets_get_animal_url($animal_id) {
    $product_id = get_post_meta($animal_id, '_product_id', true);
    
    if (!$product_id) {
        $token = 'NO-PRODUCT';
    } else {
        $token = naturapets_get_product_unique_id($product_id);
    }
    
    return add_query_arg(array(
        'animal' => $animal_id,
        'token' => $token,
    ), home_url('/fiche-animal/'));
}

/**
 * Ajouter une metabox QR Code sur les fiches animaux.
 */
function naturapets_add_qrcode_metabox() {
    add_meta_box(
        'naturapets_animal_qrcode',
        'QR Code de l\'animal',
        'naturapets_qrcode_metabox_content',
        'animal',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'naturapets_add_qrcode_metabox');

/**
 * Contenu de la metabox QR Code.
 */
function naturapets_qrcode_metabox_content($post) {
    $animal_url = naturapets_get_animal_url($post->ID);
    
    echo '<div style="text-align: center;">';
    echo naturapets_generate_qrcode($animal_url, 180);
    echo '<p style="margin-top: 10px; word-break: break-all;">';
    echo '<small><a href="' . esc_url($animal_url) . '" target="_blank">' . esc_html($animal_url) . '</a></small>';
    echo '</p>';
    echo '</div>';
}

/**
 * Ajouter la colonne QR Code dans la liste des animaux.
 */
function naturapets_add_qrcode_column($columns) {
    $columns['qrcode'] = 'QR Code';
    return $columns;
}
add_filter('manage_animal_posts_columns', 'naturapets_add_qrcode_column');

/**
 * Afficher le QR Code dans la colonne.
 */
function naturapets_qrcode_column_content($column, $post_id) {
    if ($column === 'qrcode') {
        $animal_url = naturapets_get_animal_url($post_id);
        $qr_url = naturapets_get_qrcode_url($animal_url, 60);
        echo '<img src="' . esc_url($qr_url) . '" width="60" height="60" alt="QR Code" />';
    }
}
add_action('manage_animal_posts_custom_column', 'naturapets_qrcode_column_content', 10, 2);

/**
 * Créer la page virtuelle pour afficher la fiche animal publique.
 */
function naturapets_animal_public_page() {
    if (!isset($_GET['animal']) || !isset($_GET['token'])) {
        return;
    }
    
    $animal_id = absint($_GET['animal']);
    $token = sanitize_text_field($_GET['token']);
    
    $animal = get_post($animal_id);
    
    if (!$animal || $animal->post_type !== 'animal') {
        return;
    }
    
    // Vérifier le token basé sur l'ID unique du produit
    $product_id = get_post_meta($animal_id, '_product_id', true);
    
    if ($product_id) {
        $expected_token = naturapets_get_product_unique_id($product_id);
    } else {
        $expected_token = 'NO-PRODUCT';
    }
    
    if ($token !== $expected_token) {
        wp_die('Lien invalide ou expiré.', 'Erreur', array('response' => 403));
    }
    
    // Afficher la fiche animal
    naturapets_display_public_animal_page($animal);
    exit;
}
add_action('template_redirect', 'naturapets_animal_public_page');

/**
 * Afficher la page publique de l'animal (accessible via QR code).
 */
function naturapets_display_public_animal_page($animal) {
    // Récupérer les champs ACF
    $nom = get_field('nom', $animal->ID);
    $photo = get_field('photo_de_lanimal', $animal->ID);
    $informations = get_field('informations_importantes', $animal->ID);
    $allergies = get_field('allergies', $animal->ID);
    $telephone = get_field('telephone', $animal->ID);
    $adresse = get_field('adresse', $animal->ID);
    
    $customer_id = get_post_meta($animal->ID, '_customer_id', true);
    $customer = get_user_by('id', $customer_id);
    
    get_header();
    ?>
    <main class="naturapets-animal-page" style="max-width: 600px; margin: 40px auto; padding: 20px;">
        <div style="background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); padding: 30px;">
            
            <?php 
            $photo_url = naturapets_get_acf_image_url($photo, 'medium');
            if ($photo_url): ?>
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="<?php echo esc_url($photo_url); ?>" alt="<?php echo esc_attr($nom); ?>" 
                     style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 4px solid #2d5a27;" />
            </div>
            <?php endif; ?>
            
            <h1 style="text-align: center; margin-bottom: 30px;">
                <?php echo $nom ? esc_html($nom) : 'Fiche Animal'; ?>
            </h1>
            
            <table style="width: 100%; border-collapse: collapse;">
                <?php if ($informations): ?>
                <tr>
                    <th style="text-align: left; padding: 12px; border-bottom: 1px solid #eee; width: 40%; vertical-align: top;">Informations importantes</th>
                    <td style="padding: 12px; border-bottom: 1px solid #eee;"><?php echo nl2br(esc_html($informations)); ?></td>
                </tr>
                <?php endif; ?>
                
                <?php if ($allergies): ?>
                <tr>
                    <th style="text-align: left; padding: 12px; border-bottom: 1px solid #eee; vertical-align: top;">Allergies</th>
                    <td style="padding: 12px; border-bottom: 1px solid #eee;"><?php echo nl2br(esc_html($allergies)); ?></td>
                </tr>
                <?php endif; ?>
                
                <?php if ($telephone): ?>
                <tr>
                    <th style="text-align: left; padding: 12px; border-bottom: 1px solid #eee;">Téléphone</th>
                    <td style="padding: 12px; border-bottom: 1px solid #eee;">
                        <a href="tel:<?php echo esc_attr($telephone); ?>" style="color: #2d5a27; text-decoration: none;">
                            <?php echo esc_html($telephone); ?>
                        </a>
                    </td>
                </tr>
                <?php endif; ?>
                
                <?php if ($adresse && ($adresse['rue'] || $adresse['code_postal'] || $adresse['ville'])): ?>
                <tr>
                    <th style="text-align: left; padding: 12px; border-bottom: 1px solid #eee; vertical-align: top;">Adresse</th>
                    <td style="padding: 12px; border-bottom: 1px solid #eee;">
                        <?php 
                        if ($adresse['rue']) echo esc_html($adresse['rue']) . '<br>';
                        if ($adresse['code_postal'] || $adresse['ville']) {
                            echo esc_html(trim($adresse['code_postal'] . ' ' . $adresse['ville']));
                        }
                        ?>
                    </td>
                </tr>
                <?php endif; ?>
                
                <?php if ($customer): ?>
                <tr>
                    <th style="text-align: left; padding: 12px; border-bottom: 1px solid #eee;">Propriétaire</th>
                    <td style="padding: 12px; border-bottom: 1px solid #eee;"><?php echo esc_html($customer->display_name); ?></td>
                </tr>
                <?php endif; ?>
            </table>
            
            <?php if (!$nom && !$informations && !$allergies): ?>
            <p style="text-align: center; color: #666; margin-top: 20px;">
                <em>Les informations de cet animal n'ont pas encore été renseignées.</em>
            </p>
            <?php endif; ?>
            
            <p style="text-align: center; margin-top: 30px; color: #999; font-size: 14px;">
                Fiche générée par <?php bloginfo('name'); ?>
            </p>
        </div>
    </main>
    <?php
    get_footer();
}

/**
 * Ajouter le QR code dans l'espace client (page de modification animal).
 */
function naturapets_add_qrcode_to_animal_form($animal_id) {
    $animal_url = naturapets_get_animal_url($animal_id);
    ?>
    <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
        <h3 style="margin-top: 0;">QR Code de votre animal</h3>
        <p style="color: #666; font-size: 14px;">Scannez ce QR code pour accéder à la fiche de votre animal.</p>
        <?php echo naturapets_generate_qrcode($animal_url, 150); ?>
    </div>
    <?php
}