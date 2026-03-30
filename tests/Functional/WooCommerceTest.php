<?php
/**
 * Tests fonctionnels pour les intégrations WooCommerce de NaturaPets.
 *
 * Vérifie :
 * - Validation de l'inscription (mot de passe)
 * - Création d'animaux à la commande
 * - Synchronisation adresses facturation/livraison
 * - Menu Mon compte personnalisé
 * - Unique ID produit
 * - Catégorie médaillon
 */

class WooCommerceTest extends NaturaPets_TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once dirname(__DIR__, 2) . '/includes/class-qrcode.php';
        require_once dirname(__DIR__, 2) . '/functions.php';
    }

    // =========================================================================
    // Inscription : Validation mot de passe
    // =========================================================================

    public function test_registration_password_mismatch_adds_error(): void
    {
        $_POST = [
            'password' => 'SecureP@ss123',
            'password_confirm' => 'DifferentP@ss456',
        ];

        $errors = new class {
            public array $errors = [];
            public function add(string $code, string $message): void
            {
                $this->errors[$code] = $message;
            }
        };

        $result = naturapets_validate_registration_password_confirmation($errors, 'user', 'SecureP@ss123', 'user@test.com');

        $this->assertArrayHasKey('password_mismatch', $result->errors);

        $_POST = [];
    }

    public function test_registration_password_match_passes(): void
    {
        $_POST = [
            'password' => 'SecureP@ss123',
            'password_confirm' => 'SecureP@ss123',
        ];

        $errors = new class {
            public array $errors = [];
            public function add(string $code, string $message): void
            {
                $this->errors[$code] = $message;
            }
        };

        $result = naturapets_validate_registration_password_confirmation($errors, 'user', 'SecureP@ss123', 'user@test.com');

        $this->assertEmpty($result->errors);

        $_POST = [];
    }

    public function test_registration_empty_password_confirm_adds_error(): void
    {
        $_POST = [
            'password' => 'SecureP@ss123',
            // password_confirm manquant
        ];

        $errors = new class {
            public array $errors = [];
            public function add(string $code, string $message): void
            {
                $this->errors[$code] = $message;
            }
        };

        $result = naturapets_validate_registration_password_confirmation($errors, 'user', 'SecureP@ss123', 'user@test.com');

        $this->assertArrayHasKey('password_mismatch', $result->errors);

        $_POST = [];
    }

    // =========================================================================
    // Menu Mon Compte : Personnalisation
    // =========================================================================

    public function test_account_menu_contains_mes_medaillons(): void
    {
        $items = [
            'dashboard' => 'Tableau de bord',
            'orders' => 'Commandes',
            'downloads' => 'Téléchargements',
            'edit-address' => 'Adresses',
            'edit-account' => 'Détails du compte',
            'payment-methods' => 'Moyens de paiement',
            'customer-logout' => 'Déconnexion',
        ];

        $result = naturapets_add_animals_menu_item($items);

        $this->assertArrayHasKey('mes-animaux', $result);
        $this->assertEquals('Mes médaillons', $result['mes-animaux']);
    }

    public function test_account_menu_removes_downloads(): void
    {
        $items = [
            'dashboard' => 'Tableau de bord',
            'orders' => 'Commandes',
            'downloads' => 'Téléchargements',
            'edit-address' => 'Adresses',
            'edit-account' => 'Détails du compte',
            'customer-logout' => 'Déconnexion',
        ];

        $result = naturapets_add_animals_menu_item($items);

        $this->assertArrayNotHasKey('downloads', $result);
    }

    public function test_account_menu_removes_edit_account(): void
    {
        $items = [
            'dashboard' => 'Tableau de bord',
            'orders' => 'Commandes',
            'edit-account' => 'Détails du compte',
            'customer-logout' => 'Déconnexion',
        ];

        $result = naturapets_add_animals_menu_item($items);

        $this->assertArrayNotHasKey('edit-account', $result);
    }

    public function test_account_menu_removes_logout(): void
    {
        $items = [
            'dashboard' => 'Tableau de bord',
            'orders' => 'Commandes',
            'customer-logout' => 'Déconnexion',
        ];

        $result = naturapets_add_animals_menu_item($items);

        $this->assertArrayNotHasKey('customer-logout', $result);
    }

    public function test_account_menu_renames_dashboard_to_profil(): void
    {
        $items = [
            'dashboard' => 'Tableau de bord',
            'orders' => 'Commandes',
        ];

        $result = naturapets_add_animals_menu_item($items);

        $this->assertEquals('Profil', $result['dashboard']);
    }

    public function test_account_menu_mes_animaux_after_orders(): void
    {
        $items = [
            'dashboard' => 'Tableau de bord',
            'orders' => 'Commandes',
            'edit-address' => 'Adresses',
        ];

        $result = naturapets_add_animals_menu_item($items);
        $keys = array_keys($result);

        $orders_pos = array_search('orders', $keys);
        $animaux_pos = array_search('mes-animaux', $keys);

        $this->assertEquals($orders_pos + 1, $animaux_pos, 'mes-animaux doit être juste après orders');
    }

    // =========================================================================
    // Création d'animaux à la commande
    // =========================================================================

    public function test_create_animal_on_order_skips_without_order(): void
    {
        \Brain\Monkey\Functions\expect('wc_get_order')
            ->once()
            ->with(999)
            ->andReturn(false);

        \Brain\Monkey\Functions\expect('wp_insert_post')->never();

        naturapets_create_animal_on_order(999);
        $this->assertTrue(true);
    }

    public function test_create_animal_on_order_skips_duplicate(): void
    {
        $order = \Mockery::mock('WC_Order');
        $order->shouldReceive('get_customer_id')->never();

        \Brain\Monkey\Functions\expect('wc_get_order')
            ->once()
            ->with(100)
            ->andReturn($order);

        \Brain\Monkey\Functions\expect('get_posts')
            ->once()
            ->andReturn([(object) ['ID' => 42]]);

        \Brain\Monkey\Functions\expect('wp_insert_post')->never();

        naturapets_create_animal_on_order(100);
        $this->assertTrue(true);
    }

    public function test_create_animal_on_order_skips_guest_orders(): void
    {
        $order = \Mockery::mock('WC_Order');
        $order->shouldReceive('get_customer_id')->once()->andReturn(0);

        \Brain\Monkey\Functions\expect('wc_get_order')
            ->once()
            ->with(100)
            ->andReturn($order);

        \Brain\Monkey\Functions\expect('get_posts')
            ->once()
            ->andReturn([]);

        \Brain\Monkey\Functions\expect('wp_insert_post')->never();

        naturapets_create_animal_on_order(100);
        $this->assertTrue(true);
    }

    public function test_create_animal_on_order_creates_animal_per_quantity(): void
    {
        $product = \Mockery::mock('WC_Product');
        $product->shouldReceive('get_name')->andReturn('Médaillon Chat');

        $item = \Mockery::mock('WC_Order_Item_Product');
        $item->shouldReceive('get_product_id')->andReturn(50);
        $item->shouldReceive('get_quantity')->andReturn(2);

        $order = \Mockery::mock('WC_Order');
        $order->shouldReceive('get_customer_id')->once()->andReturn(10);
        $order->shouldReceive('get_items')->once()->andReturn([1 => $item]);

        \Brain\Monkey\Functions\expect('wc_get_order')
            ->once()
            ->with(100)
            ->andReturn($order);

        \Brain\Monkey\Functions\expect('get_posts')
            ->once()
            ->andReturn([]);

        // wc_get_product est appelé 1 fois par item (pas par quantité)
        \Brain\Monkey\Functions\expect('wc_get_product')
            ->once()
            ->with(50)
            ->andReturn($product);

        // 2 animaux doivent être créés (quantity = 2)
        \Brain\Monkey\Functions\expect('wp_insert_post')
            ->times(2)
            ->andReturn(200, 201);

        \Brain\Monkey\Functions\expect('is_wp_error')
            ->times(2)
            ->andReturn(false);

        \Brain\Monkey\Functions\expect('update_post_meta')
            ->times(8); // 4 meta × 2 animaux

        \Brain\Monkey\Functions\expect('naturapets_get_or_create_medaillon_public_post')
            ->times(2);

        naturapets_create_animal_on_order(100);
        $this->assertTrue(true);
    }

    // =========================================================================
    // Unique ID produit
    // =========================================================================

    public function test_get_product_unique_id_creates_if_missing(): void
    {
        \Brain\Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(50, '_naturapets_unique_id', true)
            ->andReturn('');

        \Brain\Monkey\Functions\expect('update_post_meta')
            ->once()
            ->with(50, '_naturapets_unique_id', \Mockery::pattern('/^NP-[A-F0-9]{8}$/'));

        $id = naturapets_get_product_unique_id(50);

        $this->assertMatchesRegularExpression('/^NP-[A-F0-9]{8}$/', $id);
    }

    public function test_get_product_unique_id_returns_existing(): void
    {
        \Brain\Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(50, '_naturapets_unique_id', true)
            ->andReturn('NP-ABCD1234');

        \Brain\Monkey\Functions\expect('update_post_meta')->never();

        $id = naturapets_get_product_unique_id(50);

        $this->assertEquals('NP-ABCD1234', $id);
    }

    // =========================================================================
    // Adresse facturation/livraison
    // =========================================================================

    public function test_shipping_same_as_billing_requires_authenticated_user(): void
    {
        \Brain\Monkey\Functions\expect('get_current_user_id')
            ->once()
            ->andReturn(0);

        $result = naturapets_shipping_same_as_billing();

        $this->assertFalse($result);
    }

    public function test_shipping_same_as_billing_function_exists(): void
    {
        // La comparaison adresse billing/shipping requiert WC_Customer (new),
        // ce qui ne peut pas être mocké sans une installation WP complète.
        // On vérifie que la fonction existe et retourne false pour un guest.
        $this->assertTrue(function_exists('naturapets_shipping_same_as_billing'));
    }

    // =========================================================================
    // Registration settings
    // =========================================================================

    public function test_enable_myaccount_registration_returns_yes(): void
    {
        $result = naturapets_enable_myaccount_registration('no');

        $this->assertEquals('yes', $result);
    }

    public function test_registration_generate_password_returns_no(): void
    {
        $result = naturapets_registration_generate_password('yes');

        $this->assertEquals('no', $result);
    }

    // =========================================================================
    // Colonnes admin produits
    // =========================================================================

    public function test_product_unique_id_column_is_added_after_sku(): void
    {
        $columns = [
            'cb' => '<input type="checkbox" />',
            'thumb' => 'Image',
            'name' => 'Nom',
            'sku' => 'UGS',
            'price' => 'Prix',
        ];

        $result = naturapets_add_product_unique_id_column($columns);

        $keys = array_keys($result);
        $sku_pos = array_search('sku', $keys);
        $id_pos = array_search('unique_id', $keys);

        $this->assertNotFalse($id_pos);
        $this->assertEquals($sku_pos + 1, $id_pos, 'unique_id doit être juste après sku');
    }

    // =========================================================================
    // Médaillon public CPT
    // =========================================================================

    public function test_get_medaillon_public_slug_format(): void
    {
        \Brain\Monkey\Functions\expect('naturapets_get_animal_display_id')
            ->once()
            ->with(42)
            ->andReturn('PSI-2026-000042');

        $slug = naturapets_get_medaillon_public_slug(42);

        // sanitize_title convertit en lowercase avec tirets
        $this->assertStringContainsString('psi', $slug);
        $this->assertStringContainsString('2026', $slug);
        $this->assertStringContainsString('000042', $slug);
    }

    // =========================================================================
    // Product grid shortcode
    // =========================================================================

    public function test_product_grid_shortcode_returns_message_when_no_products(): void
    {
        \Brain\Monkey\Functions\expect('shortcode_atts')
            ->once()
            ->andReturn([
                'limit' => 8,
                'columns' => 4,
                'orderby' => 'date',
                'order' => 'DESC',
                'category' => '',
            ]);

        \Brain\Monkey\Functions\expect('wc_get_products')
            ->once()
            ->andReturn([]);

        $output = naturapets_product_grid_shortcode([]);

        $this->assertStringContainsString('Aucun produit', $output);
    }
}
