<?php
/**
 * Générateur de QR Code en SVG.
 * Basé sur une implémentation simplifiée sans dépendances.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Naturapets_QRCode {
    
    private $data;
    private $size;
    private $margin;
    
    public function __construct($data, $size = 200, $margin = 2) {
        $this->data = $data;
        $this->size = $size;
        $this->margin = $margin;
    }
    
    /**
     * Génère le QR code via l'API Google Charts (solution simple et fiable).
     */
    public function get_image_url() {
        return 'https://chart.googleapis.com/chart?' . http_build_query(array(
            'cht' => 'qr',
            'chs' => $this->size . 'x' . $this->size,
            'chl' => $this->data,
            'choe' => 'UTF-8',
            'chld' => 'M|' . $this->margin,
        ));
    }
    
    /**
     * Génère le QR code via QR Server API (alternative gratuite).
     */
    public function get_qrserver_url() {
        return 'https://api.qrserver.com/v1/create-qr-code/?' . http_build_query(array(
            'size' => $this->size . 'x' . $this->size,
            'data' => $this->data,
            'margin' => $this->margin * 5,
            'format' => 'svg',
        ));
    }
    
    /**
     * Retourne une balise img avec le QR code.
     */
    public function get_image_tag($alt = 'QR Code') {
        return sprintf(
            '<img src="%s" alt="%s" width="%d" height="%d" style="max-width: 100%%; height: auto;" />',
            esc_url($this->get_qrserver_url()),
            esc_attr($alt),
            $this->size,
            $this->size
        );
    }
    
    /**
     * Retourne le HTML complet avec le QR code et un lien de téléchargement.
     */
    public function get_html($alt = 'QR Code', $show_download = true) {
        $html = '<div class="naturapets-qrcode">';
        $html .= $this->get_image_tag($alt);
        
        if ($show_download) {
            $png_url = 'https://api.qrserver.com/v1/create-qr-code/?' . http_build_query(array(
                'size' => '300x300',
                'data' => $this->data,
                'margin' => 10,
                'format' => 'png',
            ));
            $html .= '<br><a href="' . esc_url($png_url) . '" download="qrcode.png" class="button button-small" style="margin-top: 10px;">Télécharger PNG</a>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}

/**
 * Fonction helper pour générer un QR code facilement.
 */
function naturapets_generate_qrcode($url, $size = 200) {
    $qr = new Naturapets_QRCode($url, $size);
    return $qr->get_html('QR Code de l\'animal');
}

/**
 * Fonction pour obtenir l'URL du QR code.
 */
function naturapets_get_qrcode_url($url, $size = 200) {
    $qr = new Naturapets_QRCode($url, $size);
    return $qr->get_qrserver_url();
}
