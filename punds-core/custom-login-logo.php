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
        body {
            background-color: rgba(0,0,0,.05);
        }
        .message.notice {
            border-left: 4px solid black;
            box-shadow: unset;
            border-radius: 4px;
        }
        #loginform{
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
            padding: 1rem;
            margin-top: 5%;
            border: 1px solid black;
            border-radius: 4px;
        }
        p.forgetmenot {
            float: unset;
        }
        .button-primary{
            float: unset;
            margin-top: 1rem;
        }
        #wp-submit{
            padding: .75rem 1.5rem;
            border-radius: 2rem;
            border: 1px solid black;
            background-color: transparent;
            color: black;
            transition: background 300ms ease, color 300ms ease;
        }
        #wp-submit:hover{
            background-color: black;
            color: white;
        }
        .dashicons-visibility::before, .dashicons-hidden::before {
            color: black;
        }
        .login .button.wp-hide-pw:focus {
            border-color: black;
            box-shadow: 0 0 0 1px black;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border-color: black;
            box-shadow: 0 0 0 1px #000;
        }
        .wp-core-ui .button {
            color: black;
            border-color: black;
        }
        .wp-core-ui .button:hover {
            color: black;
            border-color: black;
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