<?php
/**
 * Block Template: Promo Code
 */

$image_id   = get_field('promo_image');
$percent    = get_field('promo_percent') ?: '15';
$title      = get_field('promo_title') ?: 'Votre première commande';
$subtitle   = get_field('promo_subtitle') ?: "Profitez de -{$percent}% sur l'ensemble de notre collection de médaillons personnalisés";
$coupon_obj = get_field('promo_woo_code');
$mention    = get_field('promo_mention') ?: '* Offre valable pour toute première commande. Non cumulable.';

$coupon_code = 'BIENVENUE' . $percent;
if ( $coupon_obj ) {
    // Si c'est un objet (plusieurs fois ACF renvoie l'objet WP_Post)
    if ( is_object($coupon_obj) ) {
        $coupon_code = strtoupper( $coupon_obj->post_title );
    } elseif ( is_numeric($coupon_obj) ) {
        // Au cas où c'est juste l'ID du post
        $post_coupon = get_post($coupon_obj);
        if ( $post_coupon ) {
            $coupon_code = strtoupper( $post_coupon->post_title );
        }
    }
}

$wrapper_attributes = get_block_wrapper_attributes( array(
    'class' => 'np-promo-code-block'
) );

$unique_id = uniqid();
?>
<div <?php echo $wrapper_attributes; ?>>
    <div class="np-promo-code__inner">
        <div class="np-promo-code__image" <?php if ( $image_id ) echo 'style="background-image:url(' . esc_url( wp_get_attachment_image_url( $image_id, 'full' ) ) . ');"'; ?>>
            <?php if ( ! $image_id ) : ?>
                <div class="np-promo-code__placeholder-image">Image manquante</div>
            <?php endif; ?>
        </div>
        
        <div class="np-promo-code__content">
            <div class="np-promo-code__badge">
                <span>-<?php echo esc_html( $percent ); ?>%</span>
            </div>
            
            <h2 class="np-promo-code__title"><?php echo esc_html( $title ); ?></h2>
            <p class="np-promo-code__subtitle"><?php echo nl2br( esc_html( $subtitle ) ); ?></p>
            
            <hr class="np-promo-code__separator">
            
            <div class="np-promo-code__box">
                <span class="np-promo-code__box-label">Code :</span>
                <span class="np-promo-code__box-value" id="promo-code-val-<?php echo esc_attr($unique_id); ?>"><?php echo esc_html( $coupon_code ); ?></span>
            </div>
            
            <button type="button" class="np-promo-code__cta" data-target="promo-code-val-<?php echo esc_attr($unique_id); ?>" id="btn-promo-<?php echo esc_attr($unique_id); ?>">
                <span class="np-promo-code__cta-text">J'en profite</span> 
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </button>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                var btn = document.getElementById('btn-promo-<?php echo esc_attr($unique_id); ?>');
                if (!btn) return;
                
                btn.addEventListener('click', function() {
                    var targetId = this.getAttribute('data-target');
                    var valElem = document.getElementById(targetId);
                    if (!valElem) return;
                    
                    var codeText = valElem.innerText;
                    var originalHtml = this.innerHTML;
                    var that = this;
                    
                    navigator.clipboard.writeText(codeText).then(function() {
                        that.innerHTML = '<span class="np-promo-code__cta-text">Code copié !</span> <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>';
                        setTimeout(function() { 
                            that.innerHTML = originalHtml; 
                        }, 2000);
                    }).catch(function(err) {
                        console.error('Erreur :', err);
                    });
                });
            });
            </script>
            
            <p class="np-promo-code__mention"><?php echo esc_html( $mention ); ?></p>
        </div>
    </div>
</div>
