<?php
/**
 * Admin Footer Branding
 * 
 * @package PundsCore
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add custom admin footer
 */
add_action('admin_footer', function() {
    ?>
    <style type="text/css">
		#adminmenu,
        #adminmenu .wp-submenu,
        #adminmenuback,
        #adminmenuwrap {
            background-color: #000 !important;
			padding-bottom: 30px !important;
        }
        
        /* Menu text colors */
        #adminmenu a,
        #adminmenu div.wp-menu-name {
            color: #fff !important;
        }
        
        /* Menu icons */
        #adminmenu .wp-menu-image:before,
        #adminmenu .wp-menu-image img {
            color: #fff !important;
            opacity: 0.7;
        }
        
        /* Hover state */
        #adminmenu li.menu-top:hover,
        #adminmenu li.opensub > a.menu-top,
        #adminmenu li > a.menu-top:focus {
            background-color: #1a1a1a !important;
            color: #fff !important;
        }
        
        #adminmenu li.menu-top:hover .wp-menu-image:before,
        #adminmenu li.menu-top:hover .wp-menu-image img {
            opacity: 1;
        }
        
        /* Current/Active menu item */
        #adminmenu .wp-has-current-submenu .wp-submenu,
        #adminmenu .wp-has-current-submenu .wp-submenu.wp-submenu-wrap,
        #adminmenu .wp-has-current-submenu.opensub .wp-submenu,
        #adminmenu a.wp-has-current-submenu:focus + .wp-submenu,
        .folded #adminmenu .wp-has-current-submenu .wp-submenu {
            background-color: #1a1a1a !important;
        }
        
        #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu,
        #adminmenu li.current a.menu-top,
        #adminmenu .wp-submenu a:focus,
        #adminmenu .wp-submenu a:hover,
        #adminmenu a:hover,
        #adminmenu li.menu-top > a:focus {
            color: #fff !important;
        }
        
        #adminmenu .wp-has-current-submenu .wp-menu-image:before,
        #adminmenu .wp-has-current-submenu .wp-menu-image img,
        #adminmenu .current .wp-menu-image:before,
        #adminmenu .current .wp-menu-image img {
            opacity: 1;
        }
        
        /* Submenu styling */
        #adminmenu .wp-submenu {
            background-color: #0a0a0a !important;
        }
        
        #adminmenu .wp-submenu a {
            color: #ccc !important;
        }
        
        #adminmenu .wp-submenu a:hover,
        #adminmenu .wp-submenu a:focus {
            color: #fff !important;
            background-color: #1a1a1a !important;
        }
        
        #adminmenu .wp-submenu li.current a,
        #adminmenu .wp-submenu li.current a:hover,
        #adminmenu .wp-submenu li.current a:focus {
            color: #fff !important;
            font-weight: 600;
        }
        
        /* Collapse menu button */
        #collapse-button,
        #collapse-button:hover {
            color: #fff !important;
        }
        
        /* Admin bar (top bar) - optional, for full black theme */
        #wpadminbar {
            background-color: #000 !important;
        }
        
        #wpadminbar .ab-item,
        #wpadminbar a.ab-item,
        #wpadminbar > #wp-toolbar span.ab-label,
        #wpadminbar > #wp-toolbar span.noticon {
            color: #fff !important;
        }
        
        #wpadminbar .ab-top-menu > li:hover > .ab-item,
        #wpadminbar .ab-top-menu > li.hover > .ab-item,
        #wpadminbar .ab-top-menu > li > .ab-item:focus,
        #wpadminbar.nojq .quicklinks .ab-top-menu > li > .ab-item:focus,
        #wpadminbar.nojs .ab-top-menu > li.menupop:hover > .ab-item,
        #wpadminbar .ab-top-menu > li.menupop.hover > .ab-item {
            background-color: #1a1a1a !important;
            color: #fff !important;
        }
        
        #wpadminbar .ab-submenu {
            background-color: #1a1a1a !important;
        }
        
        #wpadminbar .ab-submenu .ab-item {
            color: #ccc !important;
        }
        
        #wpadminbar .ab-submenu .ab-item:hover {
            color: #fff !important;
            background-color: #2a2a2a !important;
        }
		
        #punds-admin-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: black;
            color: #fff;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            font-size: 14px;
            z-index: 99999;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        }
        
        #punds-admin-footer img {
            height: 24px;
            width: auto;
            vertical-align: middle;
			-webkit-filter: invert(100%); /* Safari/Chrome */
            filter: invert(100%);
        }
        
        #punds-admin-footer a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            transition: opacity 0.2s;
        }
        
        #punds-admin-footer a:hover {
            opacity: 0.8;
        }
        
        /* Add padding to body to prevent content being hidden behind footer */
        body.wp-admin {
            padding-bottom: 50px;
        }
        
        /* Adjust for mobile */
        @media screen and (max-width: 782px) {
            #punds-admin-footer {
                padding: 10px 15px;
                font-size: 12px;
            }
            
            #punds-admin-footer img {
                height: 20px;
            }
        }
    </style>
    
    <div id="punds-admin-footer">
        <img src="<?php echo PUNDS_CORE_URL; ?>assets/punds_favicon.png" alt="Partner & Söhne Logo">
        <span>
            Die Webseite wird durch 
            <a href="https://partnerundsoehne.de" target="_blank" rel="noopener noreferrer">
                Partner &amp; Söhne
            </a> 
            betreut
        </span>
    </div>
    <?php
});
