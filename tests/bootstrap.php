<?php
/**
 * PHPUnit bootstrap pour les tests du thème NaturaPets.
 *
 * Utilise Brain Monkey pour mocker les fonctions WordPress
 * sans nécessiter une installation WordPress complète.
 */

$theme_dir = dirname(__DIR__);

require_once $theme_dir . '/vendor/autoload.php';

// Définir ABSPATH pour que les fichiers du thème se chargent correctement
if (!defined('ABSPATH')) {
    define('ABSPATH', $theme_dir . '/../../..');
}

// Stub wp_get_theme() car functions.php l'appelle au chargement (niveau define)
if (!function_exists('wp_get_theme')) {
    function wp_get_theme($stylesheet = '') {
        return new class {
            public function get($key) {
                if ($key === 'Version') return '1.0.0-test';
                return '';
            }
        };
    }
}

// Stubs WordPress de base nécessaires au chargement de functions.php
// Stubs qui retournent null par défaut
$wp_null_stubs = [
    'add_filter', 'add_action', 'add_shortcode',
    'get_stylesheet_directory_uri', 'get_template_directory_uri',
];
foreach ($wp_null_stubs as $fn) {
    if (!function_exists($fn)) {
        eval("function {$fn}(...\$args) { return null; }");
    }
}

// get_stylesheet_directory doit retourner le vrai chemin pour les require_once
if (!function_exists('get_stylesheet_directory')) {
    function get_stylesheet_directory() {
        return dirname(__DIR__);
    }
}

/**
 * Classe de base pour tous les tests NaturaPets.
 * Configure Brain Monkey avant chaque test et le nettoie après.
 */
abstract class NaturaPets_TestCase extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Brain\Monkey\setUp();

        // Fonctions WordPress courantes utilisées dans functions.php
        \Brain\Monkey\Functions\stubs([
            'esc_html'       => function ($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); },
            'esc_attr'       => function ($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); },
            'esc_url'        => function ($url) { return filter_var($url, FILTER_SANITIZE_URL); },
            'esc_textarea'   => function ($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); },
            'wp_kses'        => function ($string, $allowed_html) { return strip_tags($string, '<br>'); },
            'sanitize_text_field' => function ($str) { return trim(strip_tags($str)); },
            'sanitize_textarea_field' => function ($str) { return trim(strip_tags($str)); },
            'sanitize_title' => function ($title) { return strtolower(preg_replace('/[^a-z0-9-]/', '-', strtolower($title))); },
            'sanitize_file_name' => function ($name) { return preg_replace('/[^a-z0-9._-]/', '', strtolower($name)); },
            'absint'         => function ($val) { return abs(intval($val)); },
            'wp_unslash'     => function ($val) { return is_string($val) ? stripslashes($val) : $val; },
            '__'             => function ($text) { return $text; },
            'esc_html__'     => function ($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); },
            'esc_attr__'     => function ($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); },
            'is_admin'       => function () { return false; },
            'wp_doing_ajax'  => function () { return false; },
            'home_url'       => function ($path = '') { return 'https://naturapets.test' . $path; },
            'admin_url'      => function ($path = '') { return 'https://naturapets.test/wp-admin/' . $path; },
            'add_query_arg'  => function ($args, $url = '') {
                return $url . '?' . http_build_query($args);
            },
            'wp_validate_boolean' => function ($val) { return (bool) $val; },
        ]);
    }

    protected function tearDown(): void
    {
        \Brain\Monkey\tearDown();
        parent::tearDown();
    }
}
