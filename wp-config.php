<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'jit' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '4PTCE8~-LT{`+m9rpR(V?3=>SF{N:CZw^U-0/W.KqM|->rT1f=-N)h=XxH_sjEJ+' );
define( 'SECURE_AUTH_KEY',  'e6oWfzd>1{0SecF^.`%4.FD87ERRZ_=3:[c??(*^^5M2B2~zD]>:RWN-I mFC?@[' );
define( 'LOGGED_IN_KEY',    '/FYAwOwR/^>W+atty?{gm{Zw0gMOG[$UU>sx9[z$B3j,x}`$3A?:M4K[^]<=T^EX' );
define( 'NONCE_KEY',        'c8:oGNn(&h8ieuuA|J@Q<Iihr`#0!9b*k6Ooj#6mPA`qW#uY(B%Ak:xUy}adB4+e' );
define( 'AUTH_SALT',        'zPU|.!uQA6(C?zQor1A6VBh^u{qsp>{8x(tndN ZKczh.h[W(QcrRaGoe5/I!ze)' );
define( 'SECURE_AUTH_SALT', 'Z[.t|KFUVWTOfs5)RXZx4RT3csjofP&dEFPf@AK>ax^47l=p7+uA4<lLswC}=zB`' );
define( 'LOGGED_IN_SALT',   'B,OjiuJ$PL<+rrGx!(6cdzfa{0)x,`8C)28?R _*eyfi+kS09pn$;3&~sR6zf^+c' );
define( 'NONCE_SALT',       'WQ/a<[]MvM~=LBc%v:!Dj_|j{NFpK[ie6t7Yo.~?U;9 /$<G%nR}v6nVe:?`7Mdz' );

/**#@-*/

/**
 * WordPress Database Table prefix.
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

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
