<?php
//Begin Really Simple SSL session cookie settings
@ini_set('session.cookie_httponly', true);
@ini_set('session.cookie_secure', true);
@ini_set('session.use_only_cookies', true);
//END Really Simple SSL

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'Restaurant-Food-Ordering' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

if ( !defined('WP_CLI') ) {
    define( 'WP_SITEURL', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
    define( 'WP_HOME',    $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
}



/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '67KxrBL5XnvzrTv2afW5VMfMcbZFAF0IAhdHxxO4g2PQDi1RhJHO3IhZOw998Ssa' );
define( 'SECURE_AUTH_KEY',  '7pj41PDtdoUXkUlPGla44yg9fpSPCAiWSukMsTSfJCPwD0eSjPd27lv2Vx6RUGEN' );
define( 'LOGGED_IN_KEY',    'thI5btNslD6Q4lhwKULh7YVvMKmQ0gqWuva35wST0IUdTW4mE58g1lfBTt2YiiJe' );
define( 'NONCE_KEY',        'G1pKnSLodAhKVF48yqBFq2EAtpXXpUdj36YHHrVWy3m35Tvfh5L8mxyqDfR0fjRw' );
define( 'AUTH_SALT',        '5H0QXaMAptQdpjer4oLgCjTPbTdynM235A2sI28HYuXiKrcRyajPbUZnQCq5jFkA' );
define( 'SECURE_AUTH_SALT', 'DyBiwH1QSEmFK4zSOnHHyZ6kI4o3CF0oav5OHhi6pJ0pvPh5wv7Uz6S6h05VOCIN' );
define( 'LOGGED_IN_SALT',   'deoPvS5inthIR20X2MbwDwRHqml7kKXtFVkF6tEzQkR7GXfppHxtRDr0Y8mAZjvD' );
define( 'NONCE_SALT',       'TpdyFZGo4gnyhDqTApjr0GpDC5DUnsKIpNc5EvAqh19u7oP78rVsTg6honuBearl' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
