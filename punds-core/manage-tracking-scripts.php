<?php
/**
 * Plugin Name: Tracking Script Manager
 * Description: Admin page for managing tracking scripts in head and body tags
 * Version: 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class PundS_Tracking_Script_Manager {
    
    private $option_name = 'punds_tracking_scripts';
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_head', array($this, 'output_head_scripts'), 1);
        add_action('wp_footer', array($this, 'output_footer_scripts'), 999);
    }
    
    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Tracking Scripts', 'punds-core'),
            __('Tracking Scripts', 'punds-core'),
            'manage_options',
            'punds-tracking-scripts',
            array($this, 'render_admin_page'),
            'dashicons-chart-line',
            30
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'punds_tracking_scripts_group',
            $this->option_name,
            array($this, 'sanitize_scripts')
        );
    }
    
    /**
     * Sanitize input
     */
    public function sanitize_scripts($input) {
        $sanitized = array();
        
        // Allow script tags and common tracking attributes
        $allowed_tags = array(
            'script' => array(
                'src' => true,
                'type' => true,
                'async' => true,
                'defer' => true,
                'crossorigin' => true,
                'integrity' => true,
            ),
            'noscript' => array(),
            'iframe' => array(
                'src' => true,
                'height' => true,
                'width' => true,
                'style' => true,
            ),
        );
        
        if (isset($input['head_scripts'])) {
            $sanitized['head_scripts'] = wp_kses($input['head_scripts'], $allowed_tags);
        }
        
        if (isset($input['footer_scripts'])) {
            $sanitized['footer_scripts'] = wp_kses($input['footer_scripts'], $allowed_tags);
        }
        
        return $sanitized;
    }
    
    /**
     * Output scripts in head
     */
    public function output_head_scripts() {
        $options = get_option($this->option_name);
        if (!empty($options['head_scripts'])) {
            echo "\n<!-- PundS Tracking Scripts (Head) -->\n";
            echo $options['head_scripts'];
            echo "\n<!-- /PundS Tracking Scripts (Head) -->\n";
        }
    }
    
    /**
     * Output scripts in footer
     */
    public function output_footer_scripts() {
        $options = get_option($this->option_name);
        if (!empty($options['footer_scripts'])) {
            echo "\n<!-- PundS Tracking Scripts (Footer) -->\n";
            echo $options['footer_scripts'];
            echo "\n<!-- /PundS Tracking Scripts (Footer) -->\n";
        }
    }

    /**
     * Check if tracking scripts exist in functions.php
     */
    public function check_functions_php_for_scripts() {
        $theme_dir = get_stylesheet_directory();
        $functions_file = $theme_dir . '/functions.php';
        
        if (!file_exists($functions_file)) {
            return false;
        }
        
        $functions_content = file_get_contents($functions_file);
        
        // Check for common tracking script indicators
        $indicators = array(
            'wp_head',
            'wp_footer',
            'gtag',
            'google-analytics',
            'googletagmanager',
            'fbq', // Facebook Pixel
            'tracking',
            '_gaq', // Old Google Analytics
        );
        
        foreach ($indicators as $indicator) {
            if (stripos($functions_content, $indicator) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $options = get_option($this->option_name, array(
            'head_scripts' => '',
            'footer_scripts' => ''
        ));
        
        // Show success message
        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                'punds_tracking_messages',
                'punds_tracking_message',
                __('Tracking Scripts erfolgreich aktualisiert.', 'punds-core'),
                'success'
            );
        }
        
        settings_errors('punds_tracking_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php if ($this->check_functions_php_for_scripts()): ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php _e('⚠️ Warnung:', 'punds-core'); ?></strong><br>
                    <?php _e('Es wurden möglicherweise bereits Tracking Scripts in der functions.php gefunden.', 'punds-core'); ?><br>
                    <?php _e('Bitte prüfe, ob diese entfernt werden sollten, um Duplikate zu vermeiden.', 'punds-core'); ?>
                </p>
            </div>
            <?php endif; ?>

            <div class="notice notice-info">
                <p>
                    <strong><?php _e('Hinweise:', 'punds-core'); ?></strong><br>
                    <?php _e('Füge die Tracking Scripts unten ein.', 'punds-core'); ?> <br>
                    <strong><?php _e('Wichtig:', 'punds-core'); ?></strong> <?php _e('Es wird kein php-Code in Form von wp_head oder wp_footer benötigt!', 'punds-core'); ?>
                </p>
            </div>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('punds_tracking_scripts_group');
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="head_scripts"><?php _e('Head Scripts', 'punds-core'); ?></label>
                        </th>
                        <td>
                            <textarea 
                                name="<?php echo esc_attr($this->option_name); ?>[head_scripts]" 
                                id="head_scripts" 
                                rows="10" 
                                cols="80" 
                                class="large-text code"
                                placeholder="<!-- Google Tag Manager -->"
                            ><?php echo esc_textarea($options['head_scripts']); ?></textarea>
                            <p class="description">
                                <?php _e('Diese Scripts werden im &lt;head&gt;der Webseite platziert.', 'punds-core'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="footer_scripts"><?php _e('Footer Scripts', 'punds-core'); ?></label>
                        </th>
                        <td>
                            <textarea 
                                name="<?php echo esc_attr($this->option_name); ?>[footer_scripts]" 
                                id="footer_scripts" 
                                rows="10" 
                                cols="80" 
                                class="large-text code"
                                placeholder="<!-- Google Tag Manager (noscript) -->"
                            ><?php echo esc_textarea($options['footer_scripts']); ?></textarea>
                            <p class="description">
                                <?php _e('Diese Scripts werden vor dem schließenden &lt;/body&gt;-Tag der Webseite platziert.', 'punds-core'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Tracking Scripts speichern', 'punds-core')); ?>
            </form>
            
            <hr>
            
        </div>
        <?php
    }
}

// Initialize the plugin
new PundS_Tracking_Script_Manager();