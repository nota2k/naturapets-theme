<?php
/**
 * Tests unitaires pour les fonctions helpers du thème NaturaPets.
 *
 * Teste les fonctions pures ou quasi-pures de functions.php :
 * - naturapets_get_type_animal_choices()
 * - naturapets_get_type_animal_label()
 * - naturapets_get_animal_display_id()
 * - naturapets_generate_product_unique_id()
 * - naturapets_get_acf_image_url()
 * - naturapets_get_top_banner_html()
 * - naturapets_allow_svg_upload()
 * - naturapets_fix_svg_mime_type()
 */

class HelpersTest extends NaturaPets_TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once dirname(__DIR__, 2) . '/includes/class-qrcode.php';
        require_once dirname(__DIR__, 2) . '/functions.php';
    }

    // =========================================================================
    // naturapets_get_type_animal_choices()
    // =========================================================================

    public function test_get_type_animal_choices_returns_expected_types(): void
    {
        $choices = naturapets_get_type_animal_choices();

        $this->assertIsArray($choices);
        $this->assertArrayHasKey('chien', $choices);
        $this->assertArrayHasKey('chat', $choices);
        $this->assertArrayHasKey('cheval', $choices);
        $this->assertArrayHasKey('furet', $choices);
        $this->assertCount(4, $choices);
    }

    public function test_get_type_animal_choices_values_are_strings(): void
    {
        $choices = naturapets_get_type_animal_choices();

        foreach ($choices as $key => $label) {
            $this->assertIsString($key);
            $this->assertIsString($label);
            $this->assertNotEmpty($label);
        }
    }

    // =========================================================================
    // naturapets_get_type_animal_label()
    // =========================================================================

    public function test_get_type_animal_label_returns_correct_label(): void
    {
        $this->assertEquals('Chien', naturapets_get_type_animal_label('chien'));
        $this->assertEquals('Chat', naturapets_get_type_animal_label('chat'));
        $this->assertEquals('Cheval', naturapets_get_type_animal_label('cheval'));
        $this->assertEquals('Furet', naturapets_get_type_animal_label('furet'));
    }

    public function test_get_type_animal_label_is_case_insensitive(): void
    {
        $this->assertEquals('Chien', naturapets_get_type_animal_label('CHIEN'));
        $this->assertEquals('Chat', naturapets_get_type_animal_label('Chat'));
        $this->assertEquals('Cheval', naturapets_get_type_animal_label('CHEVAL'));
    }

    public function test_get_type_animal_label_handles_empty_values(): void
    {
        $this->assertEquals('', naturapets_get_type_animal_label(''));
        $this->assertEquals('', naturapets_get_type_animal_label(null));
    }

    public function test_get_type_animal_label_returns_unknown_value_as_is(): void
    {
        // Valeurs non reconnues retournées telles quelles
        $this->assertEquals('hamster', naturapets_get_type_animal_label('hamster'));
    }

    public function test_get_type_animal_label_trims_whitespace(): void
    {
        $this->assertEquals('Chien', naturapets_get_type_animal_label('  chien  '));
    }

    // =========================================================================
    // naturapets_generate_product_unique_id()
    // =========================================================================

    public function test_generate_product_unique_id_format(): void
    {
        $id = naturapets_generate_product_unique_id();

        $this->assertMatchesRegularExpression('/^NP-[A-F0-9]{8}$/', $id);
    }

    public function test_generate_product_unique_id_is_unique(): void
    {
        $ids = [];
        for ($i = 0; $i < 100; $i++) {
            $ids[] = naturapets_generate_product_unique_id();
        }

        // Tous les IDs doivent être uniques
        $this->assertCount(100, array_unique($ids));
    }

    public function test_generate_product_unique_id_starts_with_prefix(): void
    {
        $id = naturapets_generate_product_unique_id();

        $this->assertStringStartsWith('NP-', $id);
    }

    // =========================================================================
    // naturapets_get_animal_display_id()
    // =========================================================================

    public function test_get_animal_display_id_format(): void
    {
        // Mock get_post pour retourner un post valide
        \Brain\Monkey\Functions\expect('get_post')
            ->once()
            ->with(42)
            ->andReturn((object) ['ID' => 42, 'post_date' => '2026-03-15']);

        \Brain\Monkey\Functions\expect('get_the_date')
            ->once()
            ->with('Y', 42)
            ->andReturn('2026');

        $display_id = naturapets_get_animal_display_id(42);

        $this->assertEquals('PSI-2026-000042', $display_id);
    }

    public function test_get_animal_display_id_pads_small_ids(): void
    {
        \Brain\Monkey\Functions\expect('get_post')
            ->once()
            ->with(1)
            ->andReturn((object) ['ID' => 1]);

        \Brain\Monkey\Functions\expect('get_the_date')
            ->once()
            ->with('Y', 1)
            ->andReturn('2025');

        $display_id = naturapets_get_animal_display_id(1);

        $this->assertEquals('PSI-2025-000001', $display_id);
    }

    public function test_get_animal_display_id_handles_null_post(): void
    {
        \Brain\Monkey\Functions\expect('get_post')
            ->once()
            ->with(999999)
            ->andReturn(null);

        $display_id = naturapets_get_animal_display_id(999999);

        $this->assertEquals('PSI-0000-000000', $display_id);
    }

    public function test_get_animal_display_id_handles_large_ids(): void
    {
        \Brain\Monkey\Functions\expect('get_post')
            ->once()
            ->with(1234567)
            ->andReturn((object) ['ID' => 1234567]);

        \Brain\Monkey\Functions\expect('get_the_date')
            ->once()
            ->with('Y', 1234567)
            ->andReturn('2026');

        $display_id = naturapets_get_animal_display_id(1234567);

        $this->assertEquals('PSI-2026-1234567', $display_id);
    }

    // =========================================================================
    // naturapets_get_acf_image_url()
    // =========================================================================

    public function test_get_acf_image_url_with_array_sizes(): void
    {
        $image = [
            'sizes' => ['thumbnail' => 'https://example.com/img-150x150.jpg'],
            'url' => 'https://example.com/img.jpg',
        ];

        $url = naturapets_get_acf_image_url($image, 'thumbnail');

        $this->assertEquals('https://example.com/img-150x150.jpg', $url);
    }

    public function test_get_acf_image_url_with_array_fallback_to_url(): void
    {
        $image = [
            'sizes' => [],
            'url' => 'https://example.com/img.jpg',
        ];

        $url = naturapets_get_acf_image_url($image, 'large');

        $this->assertEquals('https://example.com/img.jpg', $url);
    }

    public function test_get_acf_image_url_with_numeric_id(): void
    {
        \Brain\Monkey\Functions\expect('wp_get_attachment_image_url')
            ->once()
            ->with(123, 'medium')
            ->andReturn('https://example.com/uploads/img-300x200.jpg');

        $url = naturapets_get_acf_image_url(123, 'medium');

        $this->assertEquals('https://example.com/uploads/img-300x200.jpg', $url);
    }

    public function test_get_acf_image_url_with_string_url(): void
    {
        $url = naturapets_get_acf_image_url('https://example.com/image.jpg');

        $this->assertEquals('https://example.com/image.jpg', $url);
    }

    public function test_get_acf_image_url_with_empty_value(): void
    {
        $this->assertFalse(naturapets_get_acf_image_url(''));
        $this->assertFalse(naturapets_get_acf_image_url(null));
        $this->assertFalse(naturapets_get_acf_image_url(0));
    }

    public function test_get_acf_image_url_with_invalid_string(): void
    {
        $url = naturapets_get_acf_image_url('not-a-url');

        $this->assertFalse($url);
    }

    // =========================================================================
    // naturapets_allow_svg_upload()
    // =========================================================================

    public function test_allow_svg_upload_adds_svg_mime_types(): void
    {
        $mimes = ['jpg|jpeg|jpe' => 'image/jpeg', 'png' => 'image/png'];
        $result = naturapets_allow_svg_upload($mimes);

        $this->assertArrayHasKey('svg', $result);
        $this->assertEquals('image/svg+xml', $result['svg']);
        $this->assertArrayHasKey('svgz', $result);
        $this->assertEquals('image/svg+xml', $result['svgz']);
    }

    public function test_allow_svg_upload_preserves_existing_mimes(): void
    {
        $mimes = ['jpg|jpeg|jpe' => 'image/jpeg'];
        $result = naturapets_allow_svg_upload($mimes);

        $this->assertArrayHasKey('jpg|jpeg|jpe', $result);
        $this->assertEquals('image/jpeg', $result['jpg|jpeg|jpe']);
    }

    // =========================================================================
    // naturapets_fix_svg_mime_type()
    // =========================================================================

    public function test_fix_svg_mime_type_corrects_svg_detection(): void
    {
        $data = ['type' => '', 'ext' => '', 'proper_filename' => ''];
        $result = naturapets_fix_svg_mime_type($data, '/tmp/test.svg', 'test.svg', []);

        $this->assertEquals('image/svg+xml', $result['type']);
        $this->assertEquals('svg', $result['ext']);
    }

    public function test_fix_svg_mime_type_handles_svgz(): void
    {
        $data = ['type' => 'image/svg+xml', 'ext' => 'svgz', 'proper_filename' => ''];
        $result = naturapets_fix_svg_mime_type($data, '/tmp/test.svgz', 'test.svgz', []);

        $this->assertEquals('image/svg+xml', $result['type']);
        $this->assertEquals('svgz', $result['ext']);
    }

    public function test_fix_svg_mime_type_does_not_affect_other_types(): void
    {
        $data = ['type' => 'image/jpeg', 'ext' => 'jpg', 'proper_filename' => 'photo.jpg'];
        $result = naturapets_fix_svg_mime_type($data, '/tmp/photo.jpg', 'photo.jpg', []);

        $this->assertEquals('image/jpeg', $result['type']);
        $this->assertEquals('jpg', $result['ext']);
    }

    // =========================================================================
    // naturapets_get_top_banner_html()
    // =========================================================================

    public function test_get_top_banner_html_returns_empty_when_disabled(): void
    {
        \Brain\Monkey\Functions\expect('get_theme_mod')
            ->once()
            ->with('np_top_banner_enabled', false)
            ->andReturn(false);

        $html = naturapets_get_top_banner_html();

        $this->assertEquals('', $html);
    }

    public function test_get_top_banner_html_returns_empty_when_text_is_empty(): void
    {
        \Brain\Monkey\Functions\expect('get_theme_mod')
            ->once()
            ->with('np_top_banner_enabled', false)
            ->andReturn(true);

        \Brain\Monkey\Functions\expect('get_theme_mod')
            ->once()
            ->with('np_top_banner_text', '')
            ->andReturn('   ');

        $html = naturapets_get_top_banner_html();

        $this->assertEquals('', $html);
    }

    public function test_get_top_banner_html_returns_banner_with_text(): void
    {
        \Brain\Monkey\Functions\expect('get_theme_mod')
            ->once()
            ->with('np_top_banner_enabled', false)
            ->andReturn(true);

        \Brain\Monkey\Functions\expect('get_theme_mod')
            ->once()
            ->with('np_top_banner_text', '')
            ->andReturn('Livraison offerte dès 50€');

        $html = naturapets_get_top_banner_html();

        $this->assertStringContainsString('np-top-banner', $html);
        $this->assertStringContainsString('Livraison offerte', $html);
        $this->assertStringContainsString('role="complementary"', $html);
    }

    public function test_get_top_banner_html_escapes_xss(): void
    {
        \Brain\Monkey\Functions\expect('get_theme_mod')
            ->once()
            ->with('np_top_banner_enabled', false)
            ->andReturn(true);

        \Brain\Monkey\Functions\expect('get_theme_mod')
            ->once()
            ->with('np_top_banner_text', '')
            ->andReturn('<script>alert("xss")</script>');

        $html = naturapets_get_top_banner_html();

        $this->assertStringNotContainsString('<script>', $html);
    }

    // =========================================================================
    // naturapets_medaillon_is_filled()
    // =========================================================================

    public function test_medaillon_is_filled_with_complete_data(): void
    {
        \Brain\Monkey\Functions\expect('get_field')
            ->once()
            ->with('nom', 42)
            ->andReturn('Rex');

        \Brain\Monkey\Functions\expect('get_field')
            ->once()
            ->with('type_animal', 42)
            ->andReturn('chien');

        $this->assertTrue(naturapets_medaillon_is_filled(42));
    }

    public function test_medaillon_is_filled_returns_false_without_name(): void
    {
        \Brain\Monkey\Functions\expect('get_field')
            ->once()
            ->with('nom', 42)
            ->andReturn('');

        \Brain\Monkey\Functions\expect('get_field')
            ->once()
            ->with('type_animal', 42)
            ->andReturn('chien');

        $this->assertFalse(naturapets_medaillon_is_filled(42));
    }

    public function test_medaillon_is_filled_returns_false_without_type(): void
    {
        \Brain\Monkey\Functions\expect('get_field')
            ->once()
            ->with('nom', 42)
            ->andReturn('Rex');

        \Brain\Monkey\Functions\expect('get_field')
            ->once()
            ->with('type_animal', 42)
            ->andReturn('');

        $this->assertFalse(naturapets_medaillon_is_filled(42));
    }
}
