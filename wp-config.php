<?php
define( 'WP_CACHE', true );
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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u603487159_tMuxA' );

/** Database username */
define( 'DB_USER', 'u603487159_UU3i5' );

/** Database password */
define( 'DB_PASSWORD', '9x3ld41xSV' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define( 'AUTH_KEY',          'hVQ91U{A,v42jcnXq}z_$%M52`#A>1NV3Jz6obs4E07`*xkQGD?B)H];eP[qy@l#' );
define( 'SECURE_AUTH_KEY',   'S~[!PO?[Gj^i1}0(KF24-!Q-/T$ExR>6p1 D({XO}{EX n1Q2>]SU$|7YtNyD!K&' );
define( 'LOGGED_IN_KEY',     'qhy@2O: lozZS>s7h2$xoduq+)`BX-SGlT]uTTi9e6{S_+oWx2Ni!l9BN,;=#hZd' );
define( 'NONCE_KEY',         '(Wsh=M8o#[VaXzZ.:0]zW/f,exM<;P0c|QyZQudnJFYCd/A2t9CJ+!stu$8|7YpS' );
define( 'AUTH_SALT',         'QnA{P)W}BQM#>]N9?]xu7tZ.#&p4YQHi)g-O8<Dqsth5p1_Q%Ef?|$192r*Y?6^}' );
define( 'SECURE_AUTH_SALT',  'zp^y3D4-SdoONvK;j`ci=LP{%)85@ sGW{@[~*&Fn-_W6`R0ZS?mPO!M$l$4u7D>' );
define( 'LOGGED_IN_SALT',    'm~gF6F*_pj4U+!Z2Y3K~;6p2L eIA(2PFFelSTU?vs~]MMQ3F1V4n?9.`fiLPvN)' );
define( 'NONCE_SALT',        'rz#Mdjf$#c-CFcOD3%1LT&TE2+l.J%iL%x_=k9DmL`l9RnZM]-1 q.|lurUtQjuw' );
define( 'WP_CACHE_KEY_SALT', 'ApM,?)S5:-c&)>H8eFCH)&JdCeCohN[hu;SKM(|(@_1N9x/`Ai$[$WOWGDj7bZTr' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'FS_METHOD', 'direct' );
define( 'COOKIEHASH', '80ad269916e6c0e609b12dcddac3e757' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
