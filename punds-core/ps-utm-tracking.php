<?php
/**
 * Plugin Name: UTM Persistence & CF7 Integration
 * Description: Speichert UTM/Click-IDs über Session und befüllt CF7 Felder
 * Version: 1.0
 * Author: Partner & Söhne
 */

if (!defined('ABSPATH')) exit;

add_action('wp_head', function() {
    ?>
    <script>
    (function() {
        'use strict';
        
        // ============================================
        // HELPER FUNCTIONS
        // ============================================
        
        function getURLParam(name) {
            const params = new URLSearchParams(window.location.search);
            return params.get(name) || '';
        }
        
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return '';
        }
        
        function setCookie(name, value, days) {
            const expires = days ? `; expires=${new Date(Date.now() + days * 864e5).toUTCString()}` : '';
            document.cookie = `${name}=${value}${expires}; path=/; SameSite=Lax`;
        }
        
        // ============================================
        // TRACKING DATA COLLECTION
        // ============================================
        
        // Get or set UTM Source
        function getUTMSource() {
            let source = getURLParam('utm_source');
            if (source) {
                setCookie('_ps_utm_source', source, 30);
                return source;
            }
            
            source = getCookie('_ps_utm_source');
            if (source) return source;
            
            // Fallback: Detect from referrer
            const referrer = document.referrer;
            if (!referrer) return 'direct';
            
            try {
                const refURL = new URL(referrer);
                const refDomain = refURL.hostname;
                
                // Search engines
                if (refDomain.includes('google.')) return 'google';
                if (refDomain.includes('bing.')) return 'bing';
                if (refDomain.includes('yahoo.')) return 'yahoo';
                if (refDomain.includes('duckduckgo.')) return 'duckduckgo';
                
                // Referral
                return refDomain.replace('www.', '');
            } catch(e) {
                return 'direct';
            }
        }
        
        // Get or set UTM Medium
        function getUTMMedium() {
            let medium = getURLParam('utm_medium');
            if (medium) {
                setCookie('_ps_utm_medium', medium, 30);
                return medium;
            }
            
            medium = getCookie('_ps_utm_medium');
            if (medium) return medium;
            
            // Fallback: Detect from referrer
            const referrer = document.referrer;
            if (!referrer) return 'direct';
            
            try {
                const refURL = new URL(referrer);
                const refDomain = refURL.hostname;
                
                // Search engines = organic
                const searchEngines = ['google.', 'bing.', 'yahoo.', 'duckduckgo.', 'baidu.', 'yandex.'];
                for (let engine of searchEngines) {
                    if (refDomain.includes(engine)) return 'organic';
                }
                
                // Everything else = referral
                return 'referral';
            } catch(e) {
                return 'direct';
            }
        }
        
        // Get or set UTM Campaign
        function getUTMCampaign() {
            let campaign = getURLParam('utm_campaign');
            if (campaign) {
                setCookie('_ps_utm_campaign', campaign, 30);
                return campaign;
            }
            return getCookie('_ps_utm_campaign') || '(not set)';
        }
        
        // Get gclid (with trk fallback)
        function getGCLID() {
            let gclid = getURLParam('gclid') || getURLParam('trk');
            if (gclid) {
                setCookie('_ps_gclid', gclid, 90);
                return gclid;
            }
            return getCookie('_ps_gclid') || '';
        }
        
        // Get fbclid
        function getFBCLID() {
            let fbclid = getURLParam('fbclid');
            if (fbclid) {
                setCookie('_ps_fbclid', fbclid, 90);
                return fbclid;
            }
            return getCookie('_ps_fbclid') || '';
        }
        
        // Get GA4 Client ID
        function getGA4ClientID() {
            const gaCookie = getCookie('_ga');
            if (gaCookie) {
                const parts = gaCookie.split('.');
                if (parts.length >= 4) {
                    return parts.slice(2).join('.');
                }
            }
            return '';
        }
        
        // Get or generate Session ID
        function getSessionID() {
            let sessionID = sessionStorage.getItem('_ps_session_id');
            if (!sessionID) {
                sessionID = Date.now() + '.' + Math.random().toString(36).substr(2, 9);
                sessionStorage.setItem('_ps_session_id', sessionID);
            }
            return sessionID;
        }
        
        // ============================================
        // COLLECT ALL DATA
        // ============================================
        
        const trackingData = {
            utm_source: getUTMSource(),
            utm_medium: getUTMMedium(),
            utm_campaign: getUTMCampaign(),
            gclid: getGCLID(),
            fbclid: getFBCLID(),
            client_id: getGA4ClientID(),
            session_id: getSessionID()
        };
        
        // Make globally available
        window.psTrackingData = trackingData;
        
        // ============================================
        // POPULATE CF7 HIDDEN FIELDS
        // ============================================
        
        function populateHiddenFields() {
            const fieldMapping = {
                'utm_source': trackingData.utm_source,
                'utm_medium': trackingData.utm_medium,
                'utm_campaign': trackingData.utm_campaign,
                'gclid': trackingData.gclid,
                'fbclid': trackingData.fbclid,
                'client_id': trackingData.client_id,
                'session_id': trackingData.session_id
            };
            
            for (let fieldId in fieldMapping) {
                const field = document.getElementById(fieldId);
                if (field && fieldMapping[fieldId]) {
                    field.value = fieldMapping[fieldId];
                }
            }
        }
        
        // Wait for DOM and populate
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', populateHiddenFields);
        } else {
            populateHiddenFields();
        }
        
        // Re-populate after CF7 events
        document.addEventListener('wpcf7mailsent', populateHiddenFields);
        document.addEventListener('wpcf7invalid', populateHiddenFields);
        document.addEventListener('wpcf7spam', populateHiddenFields);
        document.addEventListener('wpcf7mailfailed', populateHiddenFields);
        
        // ============================================
        // PUSH TO DATALAYER (falls benötigt)
        // ============================================
        
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            'event': 'ps_tracking_ready',
            ...trackingData
        });
        
        // Debug logging (remove in production)
        console.log('Partner & Söhne Tracking:', trackingData);
        
    })();
    </script>
    <?php
}, 999);