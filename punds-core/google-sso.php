<?php
/**
 * Google SSO Login
 *
 * Adds "Login with Google" functionality for agency staff.
 * Requires the following constants to be defined in wp-config.php:
 *
 *   define('PUNDS_GOOGLE_CLIENT_ID',  'your-client-id');
 *   define('PUNDS_SSO_SHARED_SECRET', 'your-shared-secret');
 *
 * Optional override (defaults to production relay):
 *   define('PUNDS_SSO_RELAY_URL', 'https://sso.partnerundsoehne.de/oauth/callback');
 *
 * @package PundsCore
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Only activate SSO if the required constants are defined in wp-config.php.
 * If they are missing, the plugin silently skips — the normal login still works.
 */
if (!defined('PUNDS_GOOGLE_CLIENT_ID') || !defined('PUNDS_SSO_SHARED_SECRET')) {
    return;
}

// --- Constants (safe to be public — no secrets here) ---
define('PUNDS_SSO_RELAY_URL', defined('PUNDS_SSO_RELAY_URL')
    ? PUNDS_SSO_RELAY_URL
    : 'https://sso.partnerundsoehne.de/oauth/callback'
);
define('PUNDS_SSO_ALLOWED_DOMAIN', 'partnerundsoehne.de');
define('PUNDS_SSO_TOKEN_TTL', 60); // seconds the return token stays valid


// -------------------------------------------------------
// PART A: Add "Login with Google" button to login page
// -------------------------------------------------------

add_action('login_form', function() {
    $state = base64_encode(json_encode([
        'return_url' => wp_login_url(),
        'nonce'      => wp_create_nonce('punds-google-sso'),
        'site'       => get_site_url(),
    ]));

    $oauth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
        'client_id'     => PUNDS_GOOGLE_CLIENT_ID,
        'redirect_uri'  => PUNDS_SSO_RELAY_URL,
        'response_type' => 'code',
        'scope'         => 'openid email profile',
        'state'         => $state,
        'hd'            => PUNDS_SSO_ALLOWED_DOMAIN, // Hints Google to show only agency accounts
        'prompt'        => 'select_account',
    ]);
    ?>
    <div class="punds-sso-divider">
        <span>oder</span>
    </div>
    <a href="<?php echo esc_url($oauth_url); ?>" class="punds-sso-button">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="18" height="18">
            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.36-8.16 2.36-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
            <path fill="none" d="M0 0h48v48H0z"/>
        </svg>
        Mit Google anmelden
    </a>
    <?php
});


// -------------------------------------------------------
// PART B: Handle the return token from sso.partnerundsoehne.de
// -------------------------------------------------------

add_action('init', function() {
    // Only act if this looks like an SSO return
    if (!isset($_GET['sso_token'], $_GET['sso_hmac'])) {
        return;
    }

    $token = sanitize_text_field($_GET['sso_token']);
    $hmac  = sanitize_text_field($_GET['sso_hmac']);

    // 1. Verify the HMAC signature — ensures the token came from our relay
    $expected_hmac = hash_hmac('sha256', $token, PUNDS_SSO_SHARED_SECRET);
    if (!hash_equals($expected_hmac, $hmac)) {
        wp_die(
            'SSO-Fehler: Ungültige Signatur. Bitte versuche es erneut.',
            'SSO Fehler',
            ['response' => 403]
        );
    }

    // 2. Decode the payload
    $payload = json_decode(base64_decode($token), true);
    if (!$payload || !isset($payload['email'], $payload['expires'])) {
        wp_die('SSO-Fehler: Ungültiges Token-Format.', 'SSO Fehler', ['response' => 400]);
    }

    // 3. Check token expiry
    if ($payload['expires'] < time()) {
        wp_die(
            'SSO-Token abgelaufen. <a href="' . esc_url(wp_login_url()) . '">Bitte erneut anmelden.</a>',
            'SSO Fehler',
            ['response' => 401]
        );
    }

    // 4. Enforce domain restriction — belt and braces check
    $email = sanitize_email($payload['email']);
    if (!str_ends_with($email, '@' . PUNDS_SSO_ALLOWED_DOMAIN)) {
        wp_die('SSO-Fehler: Dieses Konto ist nicht autorisiert.', 'SSO Fehler', ['response' => 403]);
    }

    // 5. Find or create the WordPress user
    $user = get_user_by('email', $email);

    if (!$user) {
        $username = sanitize_user(explode('@', $email)[0]);

        // Ensure username is unique
        if (username_exists($username)) {
            $username = $username . '_punds';
        }

        $user_id = wp_create_user($username, wp_generate_password(24), $email);

        if (is_wp_error($user_id)) {
            wp_die('SSO-Fehler: Benutzer konnte nicht erstellt werden.', 'SSO Fehler', ['response' => 500]);
        }

        $user = get_user_by('id', $user_id);
        $user->set_role('administrator');

        wp_update_user([
            'ID'           => $user_id,
            'display_name' => sanitize_text_field($payload['name'] ?? $username),
            'first_name'   => sanitize_text_field($payload['given_name'] ?? ''),
            'last_name'    => sanitize_text_field($payload['family_name'] ?? ''),
        ]);
    }

    // 6. Log the user in and redirect to wp-admin
    wp_set_auth_cookie($user->ID, false); // false = don't remember (session cookie only)
    wp_redirect(admin_url());
    exit;
});


// -------------------------------------------------------
// PART C: Styling for the Google button
// (hooks into the same login_enqueue_scripts as custom-login-logo.php)
// -------------------------------------------------------

add_action('login_enqueue_scripts', function() {
    ?>
    <style>
        .punds-sso-divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.25rem 0 1rem;
            color: #aaa;
            font-size: 0.8rem;
        }
        .punds-sso-divider::before,
        .punds-sso-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #ddd;
        }
        .punds-sso-divider span {
            padding: 0 0.75rem;
        }
        .punds-sso-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            width: 100%;
            padding: 0.4rem 1rem !important;
            border: 1px solid black;
            border-radius: 2rem;
            color: black !important;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background 300ms ease, color 300ms ease;
            margin-top: 1rem !important;
            box-sizing: border-box;
        }
        .punds-sso-button:hover {
            background: black;
            color: white;
        }
        .punds-sso-button:hover svg path[fill="#EA4335"],
        .punds-sso-button:hover svg path[fill="#4285F4"],
        .punds-sso-button:hover svg path[fill="#FBBC05"],
        .punds-sso-button:hover svg path[fill="#34A853"] {
            fill: white;
        }
    </style>
    <?php
});