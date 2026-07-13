# Partner & Söhne – Core

WordPress-Plugin für die Websites der Partner & Söhne Kund:innen.

## Übersicht

Dieses Plugin stellt zentrale Funktionalitäten für alle WordPress-Projekte der Agentur Partner & Söhne bereit. Es wird als reguläres Plugin über `wp-content/plugins/` installiert und über WP Umbrella verwaltet und aktualisiert (Updates zusätzlich über den GitHub Plugin Update Checker).

## Struktur

```
punds-plugin/
├── punds-core-loader.php               # Haupt-Loader für alle Module
├── punds-core/                         # Core-Funktionalität
│   ├── admin-footer-branding.php
│   ├── ai-generated-image-label.php
│   ├── ai-generated-image-label-frontend.php
│   ├── custom-login-logo.php
│   ├── disable-comments.php
│   ├── duplicate-posts.php
│   ├── e-recht24-fix.php
│   ├── enable-svg-upload.php
│   ├── google-sso.php
│   ├── manage-tracking-scripts.php
│   ├── ps-utm-tracking.php
│   └── assets/
│       ├── maintenance-icon.svg
│       ├── punds_favicon.png
│       └── punds_logo.svg
```

## Enthaltene Module

### punds-core-loader.php

**Hauptloader für alle Core-Module**

- Lädt automatisch alle PHP-Dateien aus dem `punds-core/` Verzeichnis
- Definiert zentrale Konstanten (Pfade, URLs, Version)
- Verhindert direkten Dateizugriff

### admin-footer-branding.php

**Corporate Design im Admin-Bereich**

- Schwarzes Adminmenü mit weißer Schrift im Partner & Söhne Look
- Angepasste Hover-States und aktive Menüpunkte
- Custom Branding im Footer-Bereich
- Verbesserte visuelle Identität im Backend

### ai-generated-image-label.php

**KI-Kennzeichnung: Datenmodell & Mediathek-UI**

- Checkbox "KI-generiert" für Bild-Anhänge, direkt im Mediathek-Grid-Modal und im "Datei bearbeiten"-Screen
- Native WordPress-Felder statt ACF – funktioniert unabhängig von Drittanbieter-Plugins
- Eigene Spalte in der Mediathek-Listenansicht
- Filter-Dropdown zum Auditieren aller KI-markierten bzw. nicht markierten Bilder

### ai-generated-image-label-frontend.php

**KI-Kennzeichnung: Frontend-Ausgabe**

- Automatischer Hinweis-Badge bei jedem als KI-generiert markierten Bild im Frontend
- Erkennt Bilder sowohl über `wp_get_attachment_image()` als auch als eingebettetes `<img>` im Content (inkl. Page-Builder wie Cornerstone)
- Styling und Wortlaut pro Website per Filter anpassbar (`punds_ai_label_text`, `punds_ai_label_css`, `punds_ai_label_wrapper_html`)
- Shortcode `[punds_ai_label]` als manueller Fallback für als CSS-Hintergrund gesetzte Bilder
- Notfall-Kill-Switch über `PUNDS_AI_LABEL_DISABLED` in `wp-config.php`

### custom-login-logo.php

**Angepasste Login-Seite**

- Zeigt Partner & Söhne Logo auf der WordPress-Login-Seite
- Logo-Link führt zu https://partnerundsoehne.de
- Angepasster Logo-Titel-Text
- Verwendet SVG-Logo aus dem Assets-Ordner

### disable-comments.php

**Vollständige Deaktivierung der Kommentar-Funktionalität**

- Entfernt Kommentar-Funktion für alle Post-Types
- Blockiert Zugriff auf Kommentar-Admin-Seite
- Entfernt Kommentar-Menüpunkt aus der Admin-Navigation
- Entfernt Kommentar-Widget aus der Admin-Bar
- Deaktiviert Pingbacks und Trackbacks

### duplicate-posts.php

**Beiträge und Seiten duplizieren**

- Fügt "Duplizieren"-Link zu Posts und Pages hinzu
- Kopiert alle Post-Daten, Metadaten und Taxonomien
- Nonce-Verifizierung für Sicherheit
- Erstellt Duplikat als Entwurf mit aktuellem Benutzer als Autor

### e-recht24-fix.php

**eRecht24 Plugin Optimierung**

- Entfernt störende Admin-Notices des eRecht24-Plugins
- Bereinigt unnötige Hook-Callbacks
- Verbessert die Admin-UI-Performance
- Läuft mit niedriger Priorität (999) für maximale Kompatibilität

### enable-svg-upload.php

**SVG-Datei-Upload Unterstützung**

- Erlaubt Upload von SVG und SVGZ Dateien
- Sicherheitsgeprüfte SVG-Validierung
- Korrekte Thumbnail-Anzeige in der Mediathek
- Fallback für SVG-Dimensionen (200x200px)

### google-sso.php

**Login mit Google (SSO) für Agentur-Mitarbeitende**

- "Login mit Google"-Button auf der WordPress-Login-Seite
- Zugriff nur für eine konfigurierte Domain (`PUNDS_GOOGLE_CLIENT_ID`, `PUNDS_SSO_ALLOWED_DOMAIN`)
- Erstellt bei Erstanmeldung automatisch einen Administrator-Account
- Wird nur aktiv, wenn die nötigen Konstanten in `wp-config.php` gesetzt sind – sonst funktioniert der normale Login unverändert weiter

### manage-tracking-scripts.php

**Tracking-Scripts über den Admin-Bereich verwalten**

- Eigene Admin-Seite zum Einfügen von Head-/Footer-Scripts (z.B. Google Tag Manager)
- Kein Code in der `functions.php` nötig
- Warnt, falls bereits Tracking-Code in der `functions.php` gefunden wird

### ps-utm-tracking.php

**UTM-Parameter Persistenz & Contact Form 7 Integration**

- Speichert UTM-Parameter in Cookies (30 Tage)
- Tracking von utm_source, utm_medium, utm_campaign, utm_term, utm_content
- Unterstützung für Click-IDs (gclid, fbclid, msclkid, ttclid)
- Automatisches Befüllen von Contact Form 7 Hidden Fields
- Referrer-Tracking und Landing Page Detection
- Cookie-basierte Session-Persistenz

## Installation

1. Ordner nach `/wp-content/plugins/punds-plugin/` kopieren
2. Plugin über das WordPress-Backend (Plugins-Übersicht) aktivieren
3. Keine weitere Konfiguration erforderlich

## Updates

Updates werden über WP Umbrella ausgerollt. Der im Plugin gebündelte GitHub Plugin Update Checker sorgt zusätzlich dafür, dass neue Releases auch direkt in der WordPress-Plugins-Übersicht als Update angezeigt werden.

## Voraussetzungen

- WordPress 5.9+
- PHP 8.0+
- Contact Form 7 (optional, für UTM-Tracking Integration)

## Hinweise

- Änderungen an den Dateien werden erst nach einem erneuten Plugin-Update wirksam
- Für kundenspezifische Anpassungen sollten separate Plugins erstellt werden

## Sicherheit

Alle Module enthalten:

- Schutz vor direktem Dateizugriff (`ABSPATH`-Check)
- Nonce-Verifizierung bei relevanten Aktionen
- Sanitization von Eingaben
- Sichere SVG-Upload-Handhabung

## Entwicklung

**Agentur:** Partner & Söhne
**Version:** 1.1.0
**Lizenz:** Proprietär

---

© 2026 Partner & Söhne - Alle Rechte vorbehalten
