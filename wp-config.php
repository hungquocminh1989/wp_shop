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
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wp_shop' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '123456' );

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
define( 'AUTH_KEY',         'RRA=3|oDLn*HH!NT(5nL8SU/# wgsnDQ]?=[8{ZHQ=^/xQ@eI:U5O9Mrv?;Y`-7U' );
define( 'SECURE_AUTH_KEY',  'H$lh4Pv@;E3wMJFA09M)HOn7OdH&e{T;DEu2}P@xDrk( _Lxqxir_uo%U~ t(f4e' );
define( 'LOGGED_IN_KEY',    'wD{g)L3pmu0eC)CJ,K~^+Gz4mL_R+Jv);f}qU,Jx)x*JVLD3>IOA!9tH:(]y=Gw!' );
define( 'NONCE_KEY',        'E/!VKp`Mv]9%<.g?sE-KMOWr#CVJSt#^:JHF}+~Hi?q-mn/J$[0QN],K{dKX^[?x' );
define( 'AUTH_SALT',        '&_$>F6b[s|hwm5-I<q9v2E*-ot4)}o=GQ]?8<{CkN%+GrzP*BD>eBb+fIPfS,s.[' );
define( 'SECURE_AUTH_SALT', 'Ab,e8 g#>*f6R&qle@9{bfbf*0(fVM8&k#|d-D@)0BDqifE ^lx-l)0Tp+HKPIIj' );
define( 'LOGGED_IN_SALT',   'o.)]s2F*E V;#?Uo{l!R^V.hl^IW0FKK^;GR35C5+2InLLWKc)K zBGQS`@$Z@5v' );
define( 'NONCE_SALT',       '|1STlMkpBhq:K{W|n*LXq2Oh/,PuR>JP;mgQ mT3=N$7_+EF:oV_=bNk  u>qVS#' );

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
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
