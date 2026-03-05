<?php
/**
 * Custom Login Logo
 * 
 * @package PundsCore
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Customize login logo
 */
add_action('login_enqueue_scripts', function() {
    ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url(<?php echo PUNDS_CORE_URL; ?>assets/punds_logo.svg);
            background-size: contain;
            background-position: center;
            width: 100%;
            height: 80px;
        }
    </style>
    <?php
});


add_filter('login_headerurl', function() {
    return 'https://partnerundsoehne.de';
});

add_filter('login_headertext', function() {
    return 'Partner & Söhne';
});