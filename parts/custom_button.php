add_filter('woocommerce_loop_add_to_cart_link', function($html, $product, $args) {
    // Return to classical WooCommerce button logic
    $html = preg_replace('/data-wp-[a-zA-Z0-9\-]+="[^"]*"/', '', $html);
    $html = preg_replace('/data-wp-context=\'(?:\\\\.|[^\'])*\'/', '', $html);
    $html = preg_replace('/<span\s+hidden[^>]*>.*?<\/span>/s', '', $html);
    return $html;
}, 10, 3);
