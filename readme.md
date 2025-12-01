# Export Database

Quickly and easily export your WordPress database with a single click for the purposes of migration, testing, or backup (in either SQL or ZIP format).

## Changelog

### 1.2.0
- updated core classes for PHP 7.0–8.3 compatibility
- replaced deprecated PHP functions including `each()`
- fixed undefined bit-field handling in SQL export logic
- improved sanitization and nonce handling for admin actions
- removed deprecated `&$this` usage for PHP 8 compatibility
- ensured all AJAX actions load safely without inline JavaScript
- maintained original UI but cleaned up `admin.php` callbacks
- added safe use of `wp_json_encode()` for migration state storage
- improved stability of file writes during export and compression phases

### 1.1.0
- tested with WP 5.0
- updated plugin meta

### 1.0.9
- updated plugin meta

### 1.0.8
- added warning for Multisite installations
- updated recommended plugins

### 1.0.7
- tested with WP 4.9
- added support for `DISABLE_NAG_NOTICES`

### 1.0.6
- optimized plugin code
- updated recommended plugins
- added rating request notice

### 1.0.5
- optimized plugin code

### 1.0.4
- updated recommended plugins

### 1.0.3
- added recommended plugins notice

### 1.0.2
- tested with WP 4.8

### 1.0.1
- updated plugin meta

### 1.0.0
- initial release
