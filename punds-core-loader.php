<?php
/**
 * Plugin Name:       Partner & Söhne – Core
 * Plugin URI:        https://github.com/Partner-und-Soehne/punds-plugin
 * Description:       Loads all Partner & Söhne core functionality modules.
 * Version:           1.0.2
 * Author:            Partner & Söhne
 * Author URI:        https://www.partnerundsoehne.de
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 5.9
 * Requires PHP:      8.0
 * GitHub Plugin URI: https://github.com/Partner-und-Soehne/punds-plugin
 * Primary Branch:    main
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('PUNDS_CORE_PATH', plugin_dir_path(__FILE__) . 'punds-core/');
define('PUNDS_CORE_URL', plugin_dir_url(__FILE__) . 'punds-core/');
define('PUNDS_CORE_VERSION', '1.0.1');

/**
 * Load all PHP files from the punds-core directory
 */
function punds_core_load_modules() {
    $modules_dir = PUNDS_CORE_PATH;
    
    if (!is_dir($modules_dir)) {
        return;
    }
    
    // Get all PHP files from the directory
    $modules = glob($modules_dir . '*.php');
    
    if (!empty($modules)) {
        foreach ($modules as $module) {
            require_once $module;
        }
    }
}

// Load all modules
punds_core_load_modules();