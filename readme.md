# Export Database

Quickly and easily export your WordPress database with a single click for the purposes of migration, testing, or backup (in either SQL or ZIP format).

## Changelog

### 1.3.1
- added strict filesystem safety checks around all read/write/delete operations to prevent silent failures
- hardened folder validation using new `ensure_folder()` to guarantee migrations directory is readable and writable
- improved handling of corrupted or legacy migration timestamps via centralized sanitize logic
- replaced low-level file operations with safe wrappers (`safe_write`, `safe_read`, `safe_unlink`) for consistent error behavior
- added explicit readability guard to download handler to prevent 0-byte or inaccessible file downloads
- improved admin pre-flight validation to detect permission issues before export begins
- updated AJAX workflow to gracefully surface `wp_die()` errors triggered by permission or filesystem problems
- cleaned up internal logic to minimize edge-case failures on restrictive hosting environments

### 1.3.0
- fixed long-standing issue with BIT field export handling to prevent malformed SQL in certain table structures
- cleaned up legacy AJAX routing logic and simplified initialization for improved stability
- hardened cleanup routine to prevent warnings when removing expired migration files
- added PHP 7.0–8.3 compatibility adjustments across core classes
- removed redundant or outdated internal code and improved coding-standards compliance
- minor internal refactoring to support potential future backup or migration add-ons

### 1.2.0
- updated core classes for PHP 7.0–8.3 compatibility
- replaced deprecated PHP functions including `each()`
- improved sanitization and nonce handling for admin actions
- removed deprecated `&$this` usage for PHP 8 compatibility
- maintained original UI but cleaned up `admin.php` callbacks
- improved stability of file writes during export and compression phases
- implemented safe use of `wp_json_encode()` for migration state storage
- note: fix to undefined BIT field handling was NOT implemented in this version despite earlier notes

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
