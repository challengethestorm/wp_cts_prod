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
//define('WP_CACHE', true); //Added by WP-Cache Manager
//define( 'WPCACHEHOME', '/home1/challex0/public_html/wp-content/plugins/wp-super-cache/' ); //Added by WP-Cache Manager
define('DB_NAME', 'wp_cts_prod');
/** MySQL database username */
define('DB_USER', 'root');
/** MySQL database password */
define('DB_PASSWORD', '');
/** MySQL hostname */
define('DB_HOST', 'localhost');
/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');
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
define('AUTH_KEY', '<itC+pFkJaUp(C%)WNA|(CmvE^oTvLsOaau|WZ$bX!&CBwo?Tj<h&(i/@gC^mOQaSB=Wh^cIyaKA&*^Df^lU[InFolB;^MKC]YkHxy=gBm%kW|*_u$j^^hqtOMnR?R(Y');
define('SECURE_AUTH_KEY', 'G|/g+MKpqpNSpP}fH|n@KdGwSjVLiuc%g%$a_uZ$VfCyz<hsAqXSI@%$HlDkx=WM>}$tY&spADRbZ{q;yr]C!gUFyDke+b^p[C=ym=%|rQ(QejnEVhOk=OOzto;JJ=yB');
define('LOGGED_IN_KEY', '=yy_Bov$vOw^F([hoXr/}fc&MAD&=j=}JVNCnWOP{Agh{pMPQ%]jD/+S]dem@jUzQSzoYucwMlFc$RsCj+(%b/+Z-W;^QoNzE&-cZR*ew?O>VaLusOrv-M&pnN?ok)at');
define('NONCE_KEY', 'Cw!$Wuy<I*vhXj)!B/xSyH_mbJh?+Ug$NL[}g[Pr;v&e;iMDNJC;g_cr*@=LsVXGVZ/+{_=!|u{?luTe]}sKtB+Xlp&xYC/zEAu>iBXiR_HlWFcT!AeU%BcWUW_tqS/O');
define('AUTH_SALT', 'WLg@OguOlikh?J*iLvH/ZPoYMQuF<C]Gc/Wo_f;)PtB_FDxFtjr&+]f/V%gmcNSfzY{}z/Gtw=LmoFej[TnpXEt|Samzl%FKawnb)GNK;|VkTOe$m(|P]NXH[Ng]jEAF');
define('SECURE_AUTH_SALT', '(K^cHmVsi_N-r=|mXaMLKCvPRteIWz/^>V<;_p^>V=wcQ>UOyp>d(zt%j?/bXT<Qo(]Plfltsxb/+zW<MhN<WrJ/sjS=poWQA%!s[nS*KteBi>*KX&/olZ_>fXv(fMmR');
define('LOGGED_IN_SALT', 'TnI;;knhm>$hfu;>[ginmZxoC^A]?KwW-+bN(s*VyqyYk&>c@D*g/}WA$MZGr^kEXQ}l^|)quuW&BSarFr@Cg;&m%zYUWezvMCK{%*Pf$SSRpE}ULmXcS&vUFUivP+yG');
define('NONCE_SALT', 'DymqXSorEuop=r_mmm(@@&X)LU!QTWA-UzYOgloMZ;){-HW]V^rO(%v/WZYDTjIZ_$)C!DlA%d?O]%!A>srZqb|Ea@jnPIQE!*vZ%kuTVQ_k*IQ?(%&!-*HjsCIcNnEU');
/**#@-*/
/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_mfab_';
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
/**
 * Include tweaks requested by hosting providers.  You can safely
 * remove either the file or comment out the lines below to get
 * to a vanilla state.
 
if (file_exists(ABSPATH . 'hosting_provider_filters.php')) {
	include('hosting_provider_filters.php');
} */
