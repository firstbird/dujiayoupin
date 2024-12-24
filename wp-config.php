<?php
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
define( 'DB_NAME', 'dujiayoupin' );

/** Database username */
define( 'DB_USER', 'dujiayoupin' );

/** Database password */
define( 'DB_PASSWORD', 'ni0821HAO' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         'ui*@S BGz[RIMLc<4VNf2D)(|axz#KMY ji[ki:o0z7~olh0Xcpf(JRnT66,=j$/' );
define( 'SECURE_AUTH_KEY',  'a6=Ab$_uTtb?WwZ+MBa]|ux_XtCCxhv{ul}rEnW_uPOQhCd6}L(/Y__-lw5Fmf()' );
define( 'LOGGED_IN_KEY',    '~d_{|{KG62wEg^B(8sW->sg_A8_*FmhA><E4MS9aN])<Tb}%6(yP`6lx.ZdcX^d_' );
define( 'NONCE_KEY',        'lbW]!~do(]OUl4_K=LYy2q0xQmeNSOOnnY,UvpWw7MoZdRjue#4VPgoV(8|2spG*' );
define( 'AUTH_SALT',        ':FAf5+i_1npE<nH~Rnc})Yz]HSuK/+^{e^2=l&+_]R7-jV(^V>`D|d|/{WR,MA9Y' );
define( 'SECURE_AUTH_SALT', 'W]FQxyO8^N[JsIx1@=Bv!Ay{:01 rTn<zfC,H>ZUb:p1qP3YJbUOWO`r1dO*pP4{' );
define( 'LOGGED_IN_SALT',   '(ACUapm5FttXCGnCAVNZZ}2<uD{M[`Eb:+*Y?Qf;bb(Sj`g60`h9C)lZP]pqB kr' );
define( 'NONCE_SALT',       'xUJrEgX7Rv%M@_Y(Vnbj)l+XcaSnLn=~IVp93Bg3(G=t1)k!c>cFjlf+o>COdJkM' );

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
/* SSL Settings */
define('FORCE_SSL_ADMIN', true);
define('FORCE_SSL_LOGIN', true);
/* Turn HTTPS 'on' if HTTP_X_FORWARDED_PROTO matches 'https' */

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
