<?php
/**
 * KI-Kennzeichnung: Frontend-Ausgabe
 *
 * Umschließt als "KI-generiert" markierte Bilder automatisch mit einem
 * Hinweis-Badge, egal ob sie über wp_get_attachment_image() (z.B. von
 * Cornerstone-Bild-Elementen) oder als eingebettetes <img> im Content
 * ausgegeben werden. Für Bilder, die nur als CSS-Hintergrund gesetzt
 * werden (kein <img>-Tag, daher nicht automatisch erkennbar), steht ein
 * manueller Shortcode/Template-Tag als Fallback zur Verfügung.
 *
 * Notfall-Kill-Switch: define('PUNDS_AI_LABEL_DISABLED', true) in
 * wp-config.php deaktiviert nur die Frontend-Ausgabe. Die Checkbox in der
 * Mediathek bleibt unverändert nutzbar.
 *
 * @package PundsCore
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Zentraler Schalter für den Notfall-Kill-Switch
 */
function punds_ai_label_frontend_disabled() {
    return defined('PUNDS_AI_LABEL_DISABLED') && PUNDS_AI_LABEL_DISABLED;
}

/**
 * Pro-Instanz-Gate, damit einzelne Kundenseiten per Filter zusätzliche
 * Bedingungen ergänzen können (z.B. eine Mindestgröße)
 */
function punds_ai_label_should_render($attachment_id, $context = '') {
    return (bool) apply_filters('punds_ai_label_should_render', true, $attachment_id, $context);
}

/**
 * Baut das Badge-Wrapper-Markup um ein bereits gerendertes <img>-Tag
 */
function punds_ai_label_wrap_image_html($image_html, $attachment_id) {
    // Idempotenz-Schutz: falls das Bild bereits (z.B. durch einen anderen
    // Hook-Durchlauf) umschlossen wurde, nicht erneut wrappen.
    if (strpos($image_html, 'punds-ai-label-wrap') !== false) {
        return $image_html;
    }

    $label = apply_filters('punds_ai_label_text', __('KI-generiert', 'punds-core'), $attachment_id);

    $wrapped = sprintf(
        '<span class="punds-ai-label-wrap">%1$s<span class="punds-ai-badge">%2$s</span></span>',
        $image_html,
        esc_html($label)
    );

    return apply_filters('punds_ai_label_wrapper_html', $wrapped, $image_html, $label, $attachment_id);
}

/**
 * Injektionspunkt 1: alle Ausgaben über wp_get_attachment_image()
 */
add_filter('wp_get_attachment_image_html', function($html, $attachment_id, $size, $icon, $attr) {
    if ($icon || punds_ai_label_frontend_disabled()) {
        return $html;
    }

    if (!punds_is_ai_generated_image($attachment_id) || !punds_ai_label_should_render($attachment_id, 'wp_get_attachment_image')) {
        return $html;
    }

    return punds_ai_label_wrap_image_html($html, $attachment_id);
}, 10, 5);

/**
 * Injektionspunkt 2: <img class="wp-image-{ID}">-Tags, die direkt im
 * Content stehen (z.B. über den Block-/Classic-Editor eingefügte Bilder).
 *
 * WP_HTML_Tag_Processor kann Attribute lesen/setzen, aber keine neuen
 * Geschwister-Elemente einfügen - das Umschließen mit einem Badge braucht
 * aber genau das. Ein preg_replace_callback()-Pass gegen die von
 * WordPress selbst vergebene Klasse "wp-image-{ID}" ist deshalb der
 * primäre (und versionsunabhängige) Mechanismus - ein seit Jahren in
 * vielen WP-Plugins (Lazy-Load, Lightbox u.ä.) etabliertes Verfahren.
 */
add_filter('the_content', function($content) {
    if (punds_ai_label_frontend_disabled() || strpos($content, 'wp-image-') === false) {
        return $content;
    }

    return preg_replace_callback(
        '/<img\b[^>]*\bclass="[^"]*\bwp-image-(\d+)[^"]*"[^>]*>/i',
        function($matches) {
            $attachment_id = (int) $matches[1];

            if (!punds_is_ai_generated_image($attachment_id) || !punds_ai_label_should_render($attachment_id, 'the_content')) {
                return $matches[0];
            }

            return punds_ai_label_wrap_image_html($matches[0], $attachment_id);
        },
        $content
    );
}, 20);

/**
 * Default-Styling. Bewusst niedrige Selektor-Spezifität (keine IDs, kein
 * !important) und frühe Hook-Priorität, damit das CSS jeder Kundenseite
 * (Theme/Cornerstone-Custom-CSS, meist Standard-Priorität 10) den Badge
 * bei Bedarf ohne Aufwand überschreiben kann.
 */
add_action('wp_head', function() {
    if (punds_ai_label_frontend_disabled()) {
        return;
    }

    $css = apply_filters('punds_ai_label_css', '
.punds-ai-label-wrap { position: relative; display: inline-block; max-width: 100%; }
.punds-ai-label-wrap img { display: block; max-width: 100%; height: auto; }
.punds-ai-badge {
	position: absolute; right: .4em; bottom: .4em;
	padding: .15em .5em; font-size: .7rem; line-height: 1.4;
	background: rgba(0,0,0,.65); color: #fff; border-radius: .25em;
	font-family: sans-serif; pointer-events: none;
}
');

    echo "<style id=\"punds-ai-label-css\">" . $css . "</style>\n";
}, 5);

/**
 * Fallback für Fälle ohne <img>-Tag (z.B. Cornerstone-Elemente, die ein
 * Bild als CSS-Hintergrund setzen). Manuell im Custom-Code-Element
 * platzieren oder per Shortcode nutzen.
 */
function punds_get_ai_label_badge($args = array()) {
    if (punds_ai_label_frontend_disabled()) {
        return '';
    }

    $args = wp_parse_args($args, array(
        'id'    => 0,
        'force' => false,
        'label' => '',
    ));

    $attachment_id = (int) $args['id'];

    if (!$args['force'] && (!$attachment_id || !punds_is_ai_generated_image($attachment_id) || !punds_ai_label_should_render($attachment_id, 'manual'))) {
        return '';
    }

    $label = $args['label'] !== '' ? $args['label'] : apply_filters('punds_ai_label_text', __('KI-generiert', 'punds-core'), $attachment_id);

    return sprintf('<span class="punds-ai-badge punds-ai-badge--standalone">%s</span>', esc_html($label));
}

function punds_the_ai_label_badge($args = array()) {
    echo punds_get_ai_label_badge($args);
}

add_shortcode('punds_ai_label', function($atts) {
    $atts = shortcode_atts(array(
        'id'    => 0,
        'force' => '0',
        'label' => '',
    ), $atts, 'punds_ai_label');

    return punds_get_ai_label_badge(array(
        'id'    => $atts['id'],
        'force' => (bool) $atts['force'],
        'label' => $atts['label'],
    ));
});
