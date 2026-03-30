<?php
/**
 * Tests de sécurité pour le thème NaturaPets.
 *
 * Vérifie :
 * - Protection CSRF (nonces) sur toutes les actions sensibles
 * - Contrôle d'accès (IDOR) sur les formulaires animaux
 * - Vérification des permissions admin
 * - Sanitisation des entrées utilisateur
 * - Échappement des sorties (XSS)
 * - Sécurité de la suppression de compte
 *
 * NOTE : Les fonctions qui appellent exit() (naturapets_handle_delete_account,
 * naturapets_handle_ship_to_billing_from_addresses_page avec redirect, etc.)
 * sont testées uniquement sur leurs conditions de garde (early returns).
 * Les chemins qui arrivent à exit() sont vérifiés via les mocks "never()".
 */

class SecurityTest extends NaturaPets_TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once dirname(__DIR__, 2) . '/includes/class-qrcode.php';
        require_once dirname(__DIR__, 2) . '/functions.php';
    }

    // =========================================================================
    // CSRF : Suppression de compte — conditions de garde
    // =========================================================================

    public function test_delete_account_ignored_without_post_field(): void
    {
        $_POST = [];

        \Brain\Monkey\Functions\expect('wp_verify_nonce')->never();
        \Brain\Monkey\Functions\expect('wp_delete_user')->never();

        naturapets_handle_delete_account();

        $this->assertTrue(true);
    }

    public function test_delete_account_requires_nonce_field(): void
    {
        $_POST = [
            'naturapets_delete_account' => '1',
            // naturapets_delete_account_nonce manquant
        ];

        \Brain\Monkey\Functions\expect('wp_verify_nonce')->never();
        \Brain\Monkey\Functions\expect('wp_delete_user')->never();

        naturapets_handle_delete_account();
        $this->assertTrue(true);

        $_POST = [];
    }

    public function test_delete_account_rejects_invalid_nonce(): void
    {
        $_POST = [
            'naturapets_delete_account' => '1',
            'naturapets_delete_account_nonce' => 'invalid_nonce_value',
        ];

        \Brain\Monkey\Functions\expect('wp_verify_nonce')
            ->once()
            ->with('invalid_nonce_value', 'naturapets_delete_account')
            ->andReturn(false);

        // wp_die est appelé et doit interrompre l'exécution — on simule via exception
        \Brain\Monkey\Functions\expect('wp_die')
            ->once()
            ->with(
                \Mockery::type('string'),
                '',
                \Mockery::hasKey('response')
            )
            ->andThrow(new \RuntimeException('wp_die called'));

        \Brain\Monkey\Functions\expect('wp_delete_user')->never();

        $this->expectException(\RuntimeException::class);
        naturapets_handle_delete_account();

        $_POST = [];
    }

    // =========================================================================
    // CSRF : Copie adresse facturation → livraison — conditions de garde
    // =========================================================================

    public function test_ship_to_billing_ignored_without_post_fields(): void
    {
        $_POST = [];

        \Brain\Monkey\Functions\expect('wp_verify_nonce')->never();

        naturapets_handle_ship_to_billing_from_addresses_page();
        $this->assertTrue(true);
    }

    public function test_ship_to_billing_requires_nonce(): void
    {
        $_POST = [
            'naturapets_apply_ship_to_billing' => '1',
            // nonce manquant
        ];

        \Brain\Monkey\Functions\expect('wp_verify_nonce')->never();
        \Brain\Monkey\Functions\expect('get_current_user_id')->never();

        naturapets_handle_ship_to_billing_from_addresses_page();
        $this->assertTrue(true);

        $_POST = [];
    }

    public function test_ship_to_billing_rejects_invalid_nonce(): void
    {
        $_POST = [
            'naturapets_apply_ship_to_billing' => '1',
            'naturapets_ship_to_billing_nonce' => 'bad_nonce',
        ];

        \Brain\Monkey\Functions\expect('wp_verify_nonce')
            ->once()
            ->with('bad_nonce', 'naturapets_ship_to_billing')
            ->andReturn(false);

        \Brain\Monkey\Functions\expect('get_current_user_id')->never();

        naturapets_handle_ship_to_billing_from_addresses_page();
        $this->assertTrue(true);

        $_POST = [];
    }

    // =========================================================================
    // IDOR : Formulaire animal (un utilisateur ne peut modifier que SES animaux)
    // =========================================================================

    public function test_animal_form_rejects_other_users_animals(): void
    {
        $animal_id = 42;
        $attacker_id = 99;

        \Brain\Monkey\Functions\expect('get_post')
            ->once()
            ->with($animal_id)
            ->andReturn((object) ['ID' => $animal_id, 'post_type' => 'animal']);

        \Brain\Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with($animal_id, '_customer_id', true)
            ->andReturn('10'); // Propriétaire = 10, pas 99

        \Brain\Monkey\Functions\expect('wc_get_account_endpoint_url')
            ->once()
            ->andReturn('/mes-animaux/');

        ob_start();
        naturapets_display_animal_form($animal_id, $attacker_id);
        $output = ob_get_clean();

        $this->assertStringContainsString('non trouvé', $output);
        $this->assertStringNotContainsString('<form', $output);
    }

    public function test_animal_form_rejects_nonexistent_animal(): void
    {
        \Brain\Monkey\Functions\expect('get_post')
            ->once()
            ->with(99999)
            ->andReturn(null);

        // get_post_meta est appelé même si get_post retourne null,
        // car la vérification se fait sur post_type après get_post_meta
        \Brain\Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(99999, '_customer_id', true)
            ->andReturn('');

        \Brain\Monkey\Functions\expect('wc_get_account_endpoint_url')
            ->once()
            ->andReturn('/mes-animaux/');

        ob_start();
        naturapets_display_animal_form(99999, 1);
        $output = ob_get_clean();

        $this->assertStringContainsString('non trouvé', $output);
    }

    public function test_animal_form_rejects_wrong_post_type(): void
    {
        \Brain\Monkey\Functions\expect('get_post')
            ->once()
            ->with(42)
            ->andReturn((object) ['ID' => 42, 'post_type' => 'post']);

        \Brain\Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(42, '_customer_id', true)
            ->andReturn('1');

        \Brain\Monkey\Functions\expect('wc_get_account_endpoint_url')
            ->once()
            ->andReturn('/mes-animaux/');

        ob_start();
        naturapets_display_animal_form(42, 1);
        $output = ob_get_clean();

        $this->assertStringContainsString('non trouvé', $output);
    }

    // =========================================================================
    // QR Code Download : Permissions admin
    // =========================================================================

    public function test_qr_download_ignored_without_flag(): void
    {
        $_GET = [];

        \Brain\Monkey\Functions\expect('wp_verify_nonce')->never();

        naturapets_admin_download_qrcode();
        $this->assertTrue(true);
    }

    public function test_qr_download_requires_nonce_param(): void
    {
        $_GET = ['naturapets_qr_download' => '1'];
        // nonce manquant

        \Brain\Monkey\Functions\expect('wp_verify_nonce')->never();

        naturapets_admin_download_qrcode();
        $this->assertTrue(true);

        $_GET = [];
    }

    public function test_qr_download_rejects_invalid_animal_nonce(): void
    {
        $_GET = [
            'naturapets_qr_download' => '1',
            'animal_id' => '42',
            'nonce' => 'bad_nonce',
        ];

        \Brain\Monkey\Functions\expect('wp_verify_nonce')
            ->once()
            ->with('bad_nonce', 'download_qr_42')
            ->andReturn(false);

        \Brain\Monkey\Functions\expect('wp_die')
            ->once()
            ->with(\Mockery::type('string'))
            ->andThrow(new \RuntimeException('wp_die'));

        $this->expectException(\RuntimeException::class);
        naturapets_admin_download_qrcode();

        $_GET = [];
    }

    public function test_qr_download_requires_edit_others_posts_capability(): void
    {
        $_GET = [
            'naturapets_qr_download' => '1',
            'animal_id' => '42',
            'nonce' => 'valid_nonce',
        ];

        \Brain\Monkey\Functions\expect('wp_verify_nonce')
            ->once()
            ->andReturn(true);

        \Brain\Monkey\Functions\expect('current_user_can')
            ->once()
            ->with('edit_others_posts')
            ->andReturn(false);

        \Brain\Monkey\Functions\expect('wp_die')
            ->once()
            ->with(\Mockery::type('string'))
            ->andThrow(new \RuntimeException('wp_die'));

        $this->expectException(\RuntimeException::class);
        naturapets_admin_download_qrcode();

        $_GET = [];
    }

    // =========================================================================
    // XSS : Échappement des sorties
    // =========================================================================

    public function test_qrcode_metabox_escapes_url(): void
    {
        $post = (object) ['ID' => 42];

        \Brain\Monkey\Functions\expect('naturapets_get_animal_url')
            ->once()
            ->with(42)
            ->andReturn('https://naturapets.test/medaillons/psi-2026-000042');

        \Brain\Monkey\Functions\expect('naturapets_generate_qrcode')
            ->once()
            ->andReturn('<div class="qr">QR</div>');

        ob_start();
        naturapets_qrcode_metabox_content($post);
        $output = ob_get_clean();

        $this->assertStringContainsString('target="_blank"', $output);
        $this->assertStringNotContainsString('javascript:', $output);
    }

    public function test_account_deleted_notice_rejects_xss_parameter(): void
    {
        $_GET = ['account_deleted' => '<script>alert(1)</script>'];

        ob_start();
        naturapets_account_deleted_notice();
        $output = ob_get_clean();

        $this->assertEmpty($output);

        $_GET = [];
    }

    public function test_account_deleted_notice_shows_only_for_value_1(): void
    {
        $_GET = ['account_deleted' => '1'];

        ob_start();
        naturapets_account_deleted_notice();
        $output = ob_get_clean();

        $this->assertStringContainsString('supprimé avec succès', $output);
        $this->assertStringContainsString('role="status"', $output);

        $_GET = [];
    }

    // =========================================================================
    // Recherche médaillon : Validation du format d'entrée
    // =========================================================================

    public function test_medaillon_search_rejects_invalid_format(): void
    {
        $_GET = ['medaillon' => 'INVALID-FORMAT'];

        \Brain\Monkey\Functions\expect('get_posts')->never();

        naturapets_redirect_medaillon_search();
        $this->assertTrue(true);

        $_GET = [];
    }

    public function test_medaillon_search_rejects_sql_injection(): void
    {
        $_GET = ['medaillon' => "' OR 1=1 --"];

        \Brain\Monkey\Functions\expect('get_posts')->never();

        naturapets_redirect_medaillon_search();
        $this->assertTrue(true);

        $_GET = [];
    }

    public function test_medaillon_search_accepts_valid_format(): void
    {
        $_GET = ['medaillon' => 'PSI-2026-000042'];

        \Brain\Monkey\Functions\expect('get_posts')
            ->once()
            ->andReturn([]);

        naturapets_redirect_medaillon_search();
        $this->assertTrue(true);

        $_GET = [];
    }

    // =========================================================================
    // SVG Upload : Sécurité des MIME types
    // =========================================================================

    public function test_svg_upload_only_adds_svg_types(): void
    {
        $mimes = naturapets_allow_svg_upload([]);

        $this->assertCount(2, $mimes);
        $this->assertArrayHasKey('svg', $mimes);
        $this->assertArrayHasKey('svgz', $mimes);
        $this->assertArrayNotHasKey('php', $mimes);
        $this->assertArrayNotHasKey('exe', $mimes);
        $this->assertArrayNotHasKey('js', $mimes);
    }

    // =========================================================================
    // Whitelist type animal
    // =========================================================================

    public function test_animal_type_uses_whitelist(): void
    {
        $allowed = naturapets_get_type_animal_choices();

        $this->assertArrayHasKey('chien', $allowed);
        $this->assertArrayHasKey('chat', $allowed);
        $this->assertArrayHasKey('cheval', $allowed);
        $this->assertArrayHasKey('furet', $allowed);
        $this->assertArrayNotHasKey('', $allowed);
        $this->assertArrayNotHasKey('script', $allowed);
    }

    // =========================================================================
    // Ancienne URL médaillon : Validation token
    // =========================================================================

    public function test_old_medaillon_url_requires_both_params(): void
    {
        $_GET = ['animal' => '42']; // token manquant

        \Brain\Monkey\Functions\expect('get_post')->never();

        naturapets_redirect_old_medaillon_url();
        $this->assertTrue(true);

        $_GET = [];
    }

    public function test_old_medaillon_url_validates_post_type(): void
    {
        $_GET = ['animal' => '42', 'token' => 'some-token'];

        \Brain\Monkey\Functions\expect('get_post')
            ->once()
            ->with(42)
            ->andReturn((object) ['ID' => 42, 'post_type' => 'post']); // Pas un animal

        \Brain\Monkey\Functions\expect('wp_safe_redirect')->never();
        \Brain\Monkey\Functions\expect('wp_die')->never();

        naturapets_redirect_old_medaillon_url();
        $this->assertTrue(true);

        $_GET = [];
    }
}
