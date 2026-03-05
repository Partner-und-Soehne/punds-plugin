# Partner & Söhne MU-Plugins

Must-Use Plugins für WordPress-Websites der Partner & Söhne Kund:innen.

## 📋 Übersicht

Diese Sammlung von Must-Use Plugins stellt zentrale Funktionalitäten für alle WordPress-Projekte der Agentur Partner & Söhne bereit. Die Module werden automatisch geladen und benötigen keine manuelle Aktivierung.

## 🗂️ Struktur

```
MU-Plugins/
├── punds-core-loader.php    # Haupt-Loader für alle Module
├── punds-core/              # Core-Funktionalität
│   ├── admin-footer-branding.php
│   ├── custom-login-logo.php
│   ├── disable-comments.php
│   ├── duplicate-posts.php
│   ├── e-recht24-fix.php
│   ├── enable-svg-upload.php
│   ├── ps-utm-tracking.php
│   └── assets/
│       └── punds_logo.svg
```

## 🔌 Enthaltene Module

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

### ps-utm-tracking.php

**UTM-Parameter Persistenz & Contact Form 7 Integration**

- Speichert UTM-Parameter in Cookies (30 Tage)
- Tracking von utm_source, utm_medium, utm_campaign, utm_term, utm_content
- Unterstützung für Click-IDs (gclid, fbclid, msclkid, ttclid)
- Automatisches Befüllen von Contact Form 7 Hidden Fields
- Referrer-Tracking und Landing Page Detection
- Cookie-basierte Session-Persistenz

## 🚀 Installation

1. Kopiere den gesamten Ordner nach `/wp-content/mu-plugins/`
2. Die Plugins werden automatisch geladen
3. Keine weitere Konfiguration erforderlich

## ⚙️ Voraussetzungen

- WordPress 5.0+
- PHP 7.4+
- Contact Form 7 (optional, für UTM-Tracking Integration)

## 📝 Hinweise

- **Must-Use Plugins** werden automatisch aktiviert und können nicht über das WordPress-Backend deaktiviert werden
- Änderungen an den Dateien werden sofort wirksam
- Für kundspezifische Anpassungen sollten separate MU-Plugins erstellt werden

## 🔒 Sicherheit

Alle Module enthalten:

- Schutz vor direktem Dateizugriff (`ABSPATH`-Check)
- Nonce-Verifizierung bei relevanten Aktionen
- Sanitization von Eingaben
- Sichere SVG-Upload-Handhabung

## 👥 Entwicklung

**Agentur:** Partner & Söhne  
**Version:** 1.0.0  
**Lizenz:** Proprietär

---

© 2026 Partner & Söhne - Alle Rechte vorbehalten
