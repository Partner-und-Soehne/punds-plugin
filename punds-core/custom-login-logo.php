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
        .message.notice {
            border-left: 4px solid black !important;
            box-shadow: unset !important;
            border-radius: 4px;
        }
        #loginform {
            background: transparent;
            border: none;
        }
        #login h1 a, .login h1 a {
            background-image: url(<?php echo PUNDS_CORE_URL; ?>assets/punds_logo.svg);
            background-size: contain;
            background-position: center;
            width: 100%;
            height: 80px;
        }
        #login {
            padding: 1rem !important;
            margin-top: 5% !important;
            border: 1px solid black;
            border-radius: 4px;
        }
        p.forgetmenot {
            float: unset !important;
        }
        .button-primary{
            float: unset !important;
            margin-top: 1rem !important;
        }
        #wp-submit{
            padding: .25rem 1.5rem;
            border-radius: 2rem;
            border: 1px solid black;
            background-color: transparent;
            color: black;
            transition: background 300ms ease, color 300ms ease;
        }
        #wp-submit:hover{
            background-color: black !important;
            color: white !important;
        }
        .dashicons-visibility::before, .dashicons-hidden::before {
            color: black !important;
        }
        .login .button.wp-hide-pw:focus {
            border-color: black !important;
            box-shadow: 0 0 0 1px black !important;
        }
        input[type="checkbox"]:checked::before {
            filter: brightness(0) grayscale(1) !important;
        }
        input[type="text"]:focus, input[type="password"]:focus, input[type="checkbox"]:focus {
            border-color: black !important;
            box-shadow: 0 0 0 1px #000 !important;
        }
        .wp-core-ui .button {
            color: black !important;
            border-color: black !important;
        }
        .wp-core-ui .button:hover {
            color: black !important;
            border-color: black !important;
        }
        .language-switcher {
            margin-top: 2rem !important;
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