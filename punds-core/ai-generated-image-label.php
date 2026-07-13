<?php
/**
 * KI-Kennzeichnung: Datenmodell & Mediathek-UI
 *
 * Fügt Bild-Anhängen ein "KI-generiert"-Flag hinzu (KI-Kennzeichnungspflicht).
 * Nutzt bewusst native WordPress-Felder statt ACF, damit die Kennzeichnung
 * nicht von einem Drittanbieter-Plugin abhängt.
 *
 * @package PundsCore
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

if (!defined('PUNDS_AI_LABEL_META_KEY')) {
    define('PUNDS_AI_LABEL_META_KEY', '_punds_ai_generated');
}

/**
 * Meta-Feld auf dem attachment-Post-Type registrieren
 */
add_action('init', function() {
    register_post_meta('attachment', PUNDS_AI_LABEL_META_KEY, array(
        'type'              => 'boolean',
        'single'            => true,
        'default'           => false,
        'show_in_rest'      => true,
        'sanitize_callback' => 'rest_sanitize_boolean',
        'auth_callback'     => function($allowed, $meta_key, $post_id) {
            return current_user_can('edit_post', $post_id);
        },
    ));
});

/**
 * Zentraler Helfer: prüft, ob ein Attachment als KI-generiert markiert ist
 */
function punds_is_ai_generated_image($attachment_id) {
    return (bool) get_post_meta((int) $attachment_id, PUNDS_AI_LABEL_META_KEY, true);
}

/**
 * Checkbox im Mediathek-Grid-Modal und im klassischen "Datei bearbeiten"-Screen
 */
add_filter('attachment_fields_to_edit', function($form_fields, $post) {
    if (strpos((string) $post->post_mime_type, 'image/') !== 0) {
        return $form_fields;
    }

    $form_fields['punds_ai_generated'] = array(
        'label' => __('KI-generiert', 'punds-core'),
        'input' => 'html',
        'html'  => sprintf(
            '<label><input type="checkbox" name="attachments[%1$d][punds_ai_generated]" value="1" %2$s></label>',
            $post->ID,
            checked(punds_is_ai_generated_image($post->ID), true, false)
        ),
        'helps' => __('Kennzeichnet dieses Bild als KI-generiert. Im Frontend wird automatisch ein Hinweis angezeigt.', 'punds-core'),
    );

    return $form_fields;
}, 10, 2);

/**
 * Speichern der Checkbox (unchecked Checkboxen fehlen im POST, daher explizit behandeln)
 */
add_filter('attachment_fields_to_save', function($post, $attachment) {
    if (current_user_can('edit_post', $post['ID'])) {
        update_post_meta($post['ID'], PUNDS_AI_LABEL_META_KEY, !empty($attachment['punds_ai_generated']));
    }

    return $post;
}, 10, 2);

/**
 * Spalte in der Mediathek-Listenansicht
 */
add_filter('manage_media_columns', function($columns) {
    $columns['punds_ai_generated'] = __('KI-generiert', 'punds-core');
    return $columns;
});

add_action('manage_media_custom_column', function($column_name, $post_id) {
    if ($column_name !== 'punds_ai_generated') {
        return;
    }

    if (strpos((string) get_post_mime_type($post_id), 'image/') !== 0) {
        echo '&#8212;';
        return;
    }

    if (punds_is_ai_generated_image($post_id)) {
        echo '<span class="dashicons dashicons-yes" style="color:#2271b1;" title="' . esc_attr__('KI-generiert', 'punds-core') . '"></span>';
    } else {
        echo '<span class="dashicons dashicons-minus" style="color:#c3c4c7;"></span>';
    }
}, 10, 2);

/**
 * Audit-Filter in der Mediathek-Listenansicht: nach KI-Status filtern
 */
add_action('restrict_manage_posts', function() {
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'upload') {
        return;
    }

    $current = isset($_GET['punds_ai_filter']) ? sanitize_key($_GET['punds_ai_filter']) : '';
    ?>
    <select name="punds_ai_filter">
        <option value=""><?php esc_html_e('Alle Medien', 'punds-core'); ?></option>
        <option value="flagged" <?php selected($current, 'flagged'); ?>><?php esc_html_e('Nur KI-generierte Bilder', 'punds-core'); ?></option>
        <option value="unflagged" <?php selected($current, 'unflagged'); ?>><?php esc_html_e('Nur nicht gekennzeichnete Bilder', 'punds-core'); ?></option>
    </select>
    <?php
});

add_action('pre_get_posts', function($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'upload') {
        return;
    }

    $filter = isset($_GET['punds_ai_filter']) ? sanitize_key($_GET['punds_ai_filter']) : '';

    if ($filter === 'flagged') {
        $query->set('meta_key', PUNDS_AI_LABEL_META_KEY);
        $query->set('meta_value', '1');
    } elseif ($filter === 'unflagged') {
        $query->set('meta_query', array(
            'relation' => 'OR',
            array(
                'key'     => PUNDS_AI_LABEL_META_KEY,
                'compare' => 'NOT EXISTS',
            ),
            array(
                'key'     => PUNDS_AI_LABEL_META_KEY,
                'value'   => '1',
                'compare' => '!=',
            ),
        ));
    }
});
