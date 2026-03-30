<?php
/**
 * Tests unitaires pour la classe Naturapets_QRCode et les helpers associés.
 */

class QRCodeTest extends NaturaPets_TestCase
{
    private Naturapets_QRCode $qrcode;

    protected function setUp(): void
    {
        parent::setUp();

        // Charger la classe QRCode (ABSPATH est défini, le guard passe)
        require_once dirname(__DIR__, 2) . '/includes/class-qrcode.php';

        $this->qrcode = new Naturapets_QRCode('https://naturapets.test/medaillons/psi-2026-000001', 200, 2);
    }

    public function test_get_image_url_returns_google_charts_url(): void
    {
        $url = $this->qrcode->get_image_url();

        $this->assertStringContainsString('chart.googleapis.com', $url);
        $this->assertStringContainsString('cht=qr', $url);
        $this->assertStringContainsString('200x200', $url);
        $this->assertStringContainsString(urlencode('https://naturapets.test/medaillons/psi-2026-000001'), $url);
    }

    public function test_get_qrserver_url_returns_qrserver_api_url(): void
    {
        $url = $this->qrcode->get_qrserver_url();

        $this->assertStringContainsString('api.qrserver.com', $url);
        $this->assertStringContainsString('200x200', $url);
        $this->assertStringContainsString('format=svg', $url);
    }

    public function test_get_image_tag_returns_valid_img_html(): void
    {
        $tag = $this->qrcode->get_image_tag('Test QR');

        $this->assertStringContainsString('<img', $tag);
        $this->assertStringContainsString('alt="Test QR"', $tag);
        $this->assertStringContainsString('width="200"', $tag);
        $this->assertStringContainsString('height="200"', $tag);
    }

    public function test_get_html_contains_download_link(): void
    {
        $html = $this->qrcode->get_html('QR Code', true);

        $this->assertStringContainsString('naturapets-qrcode', $html);
        $this->assertStringContainsString('download="qrcode.png"', $html);
        $this->assertStringContainsString('300x300', $html);
        $this->assertStringContainsString('format=png', $html);
    }

    public function test_get_html_without_download_hides_link(): void
    {
        $html = $this->qrcode->get_html('QR Code', false);

        $this->assertStringNotContainsString('download=', $html);
        $this->assertStringContainsString('<img', $html);
    }

    public function test_custom_size_is_applied(): void
    {
        $qr = new Naturapets_QRCode('https://example.com', 300, 5);
        $url = $qr->get_qrserver_url();

        $this->assertStringContainsString('300x300', $url);
    }

    public function test_special_characters_in_data_are_encoded(): void
    {
        $qr = new Naturapets_QRCode('https://example.com/path?foo=bar&baz=qux');
        $url = $qr->get_image_url();

        // L'URL doit être valide et contenir les données encodées
        $this->assertStringContainsString('chart.googleapis.com', $url);
        $this->assertNotFalse(filter_var(
            'https://' . parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH),
            FILTER_VALIDATE_URL
        ));
    }

    public function test_empty_data_generates_valid_url(): void
    {
        $qr = new Naturapets_QRCode('');
        $url = $qr->get_qrserver_url();

        $this->assertStringContainsString('api.qrserver.com', $url);
    }
}
