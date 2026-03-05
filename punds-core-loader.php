<?php
/**
 * Plugin Name: Partner & Söhne Core Loader
 * Description: Loads all Partner & Söhne core functionality modules
 * Version: 1.0.0
 * Author: Partner & Söhne
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('PUNDS_CORE_PATH', plugin_dir_path(__FILE__) . 'punds-core/');
define('PUNDS_CORE_URL', plugin_dir_url(__FILE__) . 'punds-core/');
define('PUNDS_CORE_VERSION', '1.0.0');

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