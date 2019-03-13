<?php
define('WP_CACHE', true);
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
define('DB_NAME', 'atrung1');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'M!ts?&]o4;{G4$^O>8E a}`+`dwV]u0614w&o{gV`;~6Wf&X=@L{iL?xF#VKzIVh');
define('SECURE_AUTH_KEY',  'wjHs+VnhQJ8co*?Z9I+WK_^T921s,j3GdQtcFscqAu._*L{?#{} FDSItZufG$=G');
define('LOGGED_IN_KEY',    'G`KbJZs8o;=J-].N/5(esI`AYs894-/iE18i<NL%@si&Kb]<r<[+!iEOXk4s1HnS');
define('NONCE_KEY',        'N,wVjQSG2#D86]|_;{sL@zE^EWaV<+,/.?]&j$|6?8ds6[lDsJ?kPsuF$=/$PZx#');
define('AUTH_SALT',        '(z2O5nw;WaCeS,G6ZFBIGNloN99)#y?17cuEnF=D-4_O{lV&h+xtbSS029g}oKBA');
define('SECURE_AUTH_SALT', '!i|`eYsPs6r`:ZK)*1mG(V}zH8-sk%Rfy[zd?gVJs6@d4L-RM7?`s,;0,8:hb!<c');
define('LOGGED_IN_SALT',   'UZv$9=0K.S1|LgDSzNFyQy-(Y=.V;(}=5(,)#!m[$=486763-<_l{i__fcrxC2SZ');
define('NONCE_SALT',       '^Q;y<eh-rJ:rk1796k%zKL_H2DBxq!EUT30!v_yov>Fr%|rk1/JH<`H^hMO3#Un=');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
