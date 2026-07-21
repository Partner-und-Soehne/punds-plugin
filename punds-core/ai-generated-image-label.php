<?php
/**
 * KI-Kennzeichnung: Datenmodell & Mediathek-UI
 *
 * Fügt Bild-Anhängen eine KI-Kennzeichnung hinzu (KI-Kennzeichnungspflicht):
 * "Mit KI bearbeitet" oder "KI-generiert", gegenseitig ausschließend.
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

if (!defined('PUNDS_AI_LABEL_TYPE_META_KEY')) {
    define('PUNDS_AI_LABEL_TYPE_META_KEY', '_punds_ai_label_type');
}

/**
 * Meta-Felder auf dem attachment-Post-Type registrieren
 *
 * PUNDS_AI_LABEL_META_KEY bleibt als Boolean-Legacy-Feld registriert (Drittanbieter/
 * REST-Konsumenten könnten es bereits lesen), ist aber nicht mehr die Quelle der
 * Wahrheit - siehe punds_get_ai_label_type().
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

    register_post_meta('attachment', PUNDS_AI_LABEL_TYPE_META_KEY, array(
        'type'              => 'string',
        'single'            => true,
        'default'           => '',
        'show_in_rest'      => array(
            'schema' => array(
                'type' => 'string',
                'enum' => array('', 'edited', 'generated'),
            ),
        ),
        'sanitize_callback' => 'punds_sanitize_ai_label_type',
        'auth_callback'     => function($allowed, $meta_key, $post_id) {
            return current_user_can('edit_post', $post_id);
        },
    ));
});

/**
 * Beschränkt einen rohen Wert auf die erlaubten Kennzeichnungs-Typen
 */
function punds_sanitize_ai_label_type($value) {
    $value = sanitize_key((string) $value);
    return in_array($value, array('edited', 'generated'), true) ? $value : '';
}

/**
 * Zentraler Helfer: löst den KI-Kennzeichnungs-Typ eines Attachments auf
 * ('', 'edited' oder 'generated')
 */
function punds_get_ai_label_type($attachment_id) {
    $attachment_id = (int) $attachment_id;
    $type = punds_sanitize_ai_label_type(get_post_meta($attachment_id, PUNDS_AI_LABEL_TYPE_META_KEY, true));

    if ($type === '' && get_post_meta($attachment_id, PUNDS_AI_LABEL_META_KEY, true)) {
        // Vor Einführung der Radio-Buttons gab es nur die Option "KI-generiert"
        $type = 'generated';
    }

    return $type;
}

/**
 * Zentraler Helfer: prüft, ob ein Attachment als KI-generiert markiert ist
 */
function punds_is_ai_generated_image($attachment_id) {
    return punds_get_ai_label_type($attachment_id) === 'generated';
}

/**
 * Zentraler Helfer: prüft, ob ein Attachment als "mit KI bearbeitet" markiert ist
 */
function punds_is_ai_edited_image($attachment_id) {
    return punds_get_ai_label_type($attachment_id) === 'edited';
}

/**
 * Radio-Buttons im Mediathek-Grid-Modal und im klassischen "Datei bearbeiten"-Screen
 */
add_filter('attachment_fields_to_edit', function($form_fields, $post) {
    if (strpos((string) $post->post_mime_type, 'image/') !== 0) {
        return $form_fields;
    }

    $current = punds_get_ai_label_type($post->ID);
    $name    = sprintf('attachments[%d][punds_ai_label_type]', $post->ID);

    $options = array(
        ''          => __('Keine Kennzeichnung', 'punds-core'),
        'edited'    => __('Mit KI bearbeitet', 'punds-core'),
        'generated' => __('KI-generiert', 'punds-core'),
    );

    $html = '';
    foreach ($options as $value => $label) {
        $html .= sprintf(
            '<label style="display:block;"><input type="radio" name="%1$s" value="%2$s" %3$s> %4$s</label>',
            esc_attr($name),
            esc_attr($value),
            checked($current, $value, false),
            esc_html($label)
        );
    }

    $form_fields['punds_ai_label_type'] = array(
        'label' => __('KI-Kennzeichnung', 'punds-core'),
        'input' => 'html',
        'html'  => $html,
        'helps' => __('Kennzeichnet dieses Bild entsprechend. Im Frontend wird automatisch ein passender Hinweis angezeigt.', 'punds-core'),
    );

    return $form_fields;
}, 10, 2);

/**
 * Speichern der Radio-Auswahl.
 *
 * Schreibt zusätzlich das Legacy-Boolean-Feld mit, damit ein zuvor gesetztes
 * Legacy-Flag nicht erneut über den Fallback in punds_get_ai_label_type()
 * "auflebt", sobald hier explizit gespeichert wurde (z.B. bei Auswahl von
 * "Keine Kennzeichnung" oder "Mit KI bearbeitet").
 */
add_filter('attachment_fields_to_save', function($post, $attachment) {
    if (current_user_can('edit_post', $post['ID'])) {
        $value = isset($attachment['punds_ai_label_type'])
            ? punds_sanitize_ai_label_type($attachment['punds_ai_label_type'])
            : '';

        if ($value === '') {
            delete_post_meta($post['ID'], PUNDS_AI_LABEL_TYPE_META_KEY);
        } else {
            update_post_meta($post['ID'], PUNDS_AI_LABEL_TYPE_META_KEY, $value);
        }

        update_post_meta($post['ID'], PUNDS_AI_LABEL_META_KEY, $value === 'generated');
    }

    return $post;
}, 10, 2);

/**
 * Spalte in der Mediathek-Listenansicht
 */
add_filter('manage_media_columns', function($columns) {
    $columns['punds_ai_generated'] = __('KI-Kennzeichnung', 'punds-core');
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

    $type = punds_get_ai_label_type($post_id);

    if ($type === 'generated') {
        echo '<span class="dashicons dashicons-yes" style="color:#2271b1;" title="' . esc_attr__('KI-generiert', 'punds-core') . '"></span>';
    } elseif ($type === 'edited') {
        echo '<span class="dashicons dashicons-edit" style="color:#996800;" title="' . esc_attr__('Mit KI bearbeitet', 'punds-core') . '"></span>';
    } else {
        echo '<span class="dashicons dashicons-minus" style="color:#c3c4c7;"></span>';
    }
}, 10, 2);

/**
 * Audit-Filter in der Mediathek-Listenansicht: nach KI-Kennzeichnung filtern
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
        <option value="generated" <?php selected($current, 'generated'); ?>><?php esc_html_e('Nur KI-generierte Bilder', 'punds-core'); ?></option>
        <option value="edited" <?php selected($current, 'edited'); ?>><?php esc_html_e('Nur mit KI bearbeitete Bilder', 'punds-core'); ?></option>
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

    if ($filter === 'generated') {
        // OR-Fallback: erfasst auch Bilder, die vor Einführung der Radio-Buttons
        // nur über das Legacy-Boolean-Feld als "KI-generiert" markiert wurden.
        $query->set('meta_query', array(
            'relation' => 'OR',
            array(
                'key'   => PUNDS_AI_LABEL_TYPE_META_KEY,
                'value' => 'generated',
            ),
            array(
                'relation' => 'AND',
                array(
                    'key'     => PUNDS_AI_LABEL_TYPE_META_KEY,
                    'compare' => 'NOT EXISTS',
                ),
                array(
                    'key'   => PUNDS_AI_LABEL_META_KEY,
                    'value' => '1',
                ),
            ),
        ));
    } elseif ($filter === 'edited') {
        $query->set('meta_key', PUNDS_AI_LABEL_TYPE_META_KEY);
        $query->set('meta_value', 'edited');
    } elseif ($filter === 'unflagged') {
        $query->set('meta_query', array(
            'relation' => 'AND',
            array(
                'relation' => 'OR',
                array(
                    'key'     => PUNDS_AI_LABEL_TYPE_META_KEY,
                    'compare' => 'NOT EXISTS',
                ),
                array(
                    'key'   => PUNDS_AI_LABEL_TYPE_META_KEY,
                    'value' => '',
                ),
            ),
            array(
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
            ),
        ));
    }
});
