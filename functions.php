<?php
/**
 * Blue Mattress theme bootstrap.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

define( 'BLUE_THEME_VERSION', '2.9.19' );
define( 'BLUE_THEME_DIR', get_template_directory() );
define( 'BLUE_THEME_URI', get_template_directory_uri() );

// Language and URL handling.
require_once BLUE_THEME_DIR . '/inc/bilingual.php';
require_once BLUE_THEME_DIR . '/inc/polylang-slugs.php';

// Theme setup, content helpers and editable options.
require_once BLUE_THEME_DIR . '/inc/helpers.php';
require_once BLUE_THEME_DIR . '/inc/media-assets.php';
require_once BLUE_THEME_DIR . '/inc/setup.php';
require_once BLUE_THEME_DIR . '/inc/acf.php';
require_once BLUE_THEME_DIR . '/inc/smtp.php';

// Storefront integrations.
require_once BLUE_THEME_DIR . '/inc/woocommerce.php';
require_once BLUE_THEME_DIR . '/inc/email-styles.php';
require_once BLUE_THEME_DIR . '/inc/checkout-messages.php';
require_once BLUE_THEME_DIR . '/inc/invoice-product-qr.php';
require_once BLUE_THEME_DIR . '/inc/invoice-payment.php';
require_once BLUE_THEME_DIR . '/inc/shipping.php';
require_once BLUE_THEME_DIR . '/inc/paymob.php';

// Maintenance and WordPress admin features.
require_once BLUE_THEME_DIR . '/inc/theme-updater.php';
require_once BLUE_THEME_DIR . '/inc/contact-submissions.php';
