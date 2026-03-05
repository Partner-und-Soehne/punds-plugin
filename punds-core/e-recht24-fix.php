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

add_action('admin_init', function() {
    global $wp_filter;
    
    // Liste der zu bereinigenden Hooks
    $hooks_to_clean = array(
        'admin_notices',           
        'admin_enqueue_scripts',   
        'admin_init',
        'admin_menu',
        'admin_head'
    );
    
    foreach ($hooks_to_clean as $hook_name) {
        if (isset($wp_filter[$hook_name])) {
            foreach ($wp_filter[$hook_name]->callbacks as $priority => $callbacks) {
                foreach ($callbacks as $key => $callback) {
                    // Prüfe ob es eine eRecht24-Funktion ist
                    if (is_array($callback['function']) && 
                        is_object($callback['function'][0]) && 
                        strpos(get_class($callback['function'][0]), 'eRecht24') !== false) {
                        unset($wp_filter[$hook_name]->callbacks[$priority][$key]);
                    }
                }
            }
        }
    }
}, 999);