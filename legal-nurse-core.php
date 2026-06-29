<?php
/**
 * Plugin Name: Legal Nurse Core
 * Plugin URI:  https://growenrollments.com
 * Description: Site-specific custom functionality for the Legal Nurse website, including custom features, integrations, shortcodes, and utility functions.
 * Version:     1.0.0
 * Author:      Growenrollments
 * Author URI:  https://growenrollments.com
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: legal-nurse-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LNC_VERSION', '1.0.0' );
define( 'LNC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LNC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once LNC_PLUGIN_DIR . 'includes/svg-support.php';
