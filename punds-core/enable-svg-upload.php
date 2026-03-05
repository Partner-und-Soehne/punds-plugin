<?php
/**
 * Enable SVG Upload Support
 * 
 * @package PundsCore
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add SVG to allowed upload mime types
 */
add_filter('upload_mimes', function($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    return $mimes;
});

/**
 * Check and sanitize SVG files for security
 */
add_filter('wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {
    $filetype = wp_check_filetype($filename, $mimes);
    
    return [
        'ext'             => $filetype['ext'],
        'type'            => $filetype['type'],
        'proper_filename' => $data['proper_filename']
    ];
}, 10, 4);

/**
 * Fix SVG thumbnail display in media library
 */
add_filter('wp_prepare_attachment_for_js', function($response, $attachment, $meta) {
    if ($response['mime'] === 'image/svg+xml' && empty($response['sizes'])) {
        $svg_path = get_attached_file($attachment->ID);
        
        if (file_exists($svg_path)) {
            $response['sizes'] = [
                'full' => [
                    'url' => $response['url'],
                    'width' => 200,
                    'height' => 200,
                    'orientation' => 'portrait'
                ]
            ];
        }
    }
    
    return $response;
}, 10, 3);

/**
 * Display SVG thumbnails in media library
 */
add_action('admin_head', function() {
    ?>
    <style type="text/css">
        table.media .column-title .media-icon img[src$=".svg"],
        .attachment-info .thumbnail img[src$=".svg"],
        .media-modal-content .attachment-preview img[src$=".svg"] {
            width: 100% !important;
            height: auto !important;
        }
    </style>
    <?php
});

/**
 * Basic SVG sanitization for security
 * Removes potentially dangerous elements and attributes
 */
add_filter('wp_handle_upload_prefilter', function($file) {
    if ($file['type'] === 'image/svg+xml') {
        $svg_content = file_get_contents($file['tmp_name']);
        
        // Remove potentially dangerous tags
        $dangerous_tags = [
            'script',
            'iframe',
            'object',
            'embed',
            'link',
            'style'
        ];
        
        foreach ($dangerous_tags as $tag) {
            $svg_content = preg_replace('/<' . $tag . '[^>]*>.*?<\/' . $tag . '>/is', '', $svg_content);
            $svg_content = preg_replace('/<' . $tag . '[^>]*\/>/is', '', $svg_content);
        }
        
        // Remove event handlers
        $svg_content = preg_replace('/on\w+="[^"]*"/i', '', $svg_content);
        $svg_content = preg_replace('/on\w+=\'[^\']*\'/i', '', $svg_content);
        
        // Save sanitized content
        file_put_contents($file['tmp_name'], $svg_content);
    }
    
    return $file;
});