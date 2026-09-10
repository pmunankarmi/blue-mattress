<?php
/**
 * Blue Mattress theme bootstrap.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

define( 'BLUE_THEME_VERSION', '2.7.26' );
define( 'BLUE_THEME_DIR', get_template_directory() );
define( 'BLUE_THEME_URI', get_template_directory_uri() );

require_once BLUE_THEME_DIR . '/inc/bilingual.php';
require_once BLUE_THEME_DIR . '/inc/helpers.php';
require_once BLUE_THEME_DIR . '/inc/setup.php';
require_once BLUE_THEME_DIR . '/inc/acf.php';
require_once BLUE_THEME_DIR . '/inc/woocommerce.php';
require_once BLUE_THEME_DIR . '/inc/shipping.php';
require_once BLUE_THEME_DIR . '/inc/paymob.php';
require_once BLUE_THEME_DIR . '/inc/theme-updater.php';
require_once BLUE_THEME_DIR . '/inc/contact-submissions.php';
