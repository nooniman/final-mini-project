<?php
/**
 * Application Configuration
 */

// JWT Secret Key - CHANGE THIS IN PRODUCTION!
define('JWT_SECRET', 'wmsu-grading-system-secret-key-change-me-2024');

// Token expiration times (in seconds)
define('ACCESS_TOKEN_EXPIRY', 3600);      // 1 hour
define('REFRESH_TOKEN_EXPIRY', 604800);   // 7 days

// App settings
define('APP_NAME', 'WMSU Grading System');
define('APP_VERSION', '1.0.0');
define('APP_DEBUG', true); // Set to false in production

// Pagination defaults
define('DEFAULT_PAGE_SIZE', 20);
define('MAX_PAGE_SIZE', 100);
