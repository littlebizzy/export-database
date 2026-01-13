# Export Database

Backup your WordPress website

## Changelog

### 2.1.0
- replaced direct filename-based download URLs with short-lived token downloads
- removed `.sql` and `file=` parameters from AJAX requests to avoid firewall false positives
- added transient-based authorization for download requests
- switched from memory-based file loading to streamed downloads
- improved compatibility with BBQ Firewall and hardened WordPress stacks
- improved reliability for large database exports
- `Tested up to:` bumped to 6.9

### 2.0.2
- added Settings link on the plugins screen pointing to Tools > Export Database

### 2.0.1
- added support for Git Updater

### 2.0.0
- migrated plugin to a classic procedural architecture with minimal files
- removed all class-based structures and legacy constants for a cleaner codebase
- added dedicated `inc/` folder with separate export and ajax handlers
- introduced new export naming format using database name, timestamp, and random string
- replaced automatic file deletion with a persistent export history table in the admin screen
- added download and delete buttons for each generated file
- improved overall security using per-request nonce validation for all ajax actions
- redesigned admin page layout to show exports, file sizes, timestamps, and actions
- strengthened zip and gzip handling with automatic fallbacks

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
