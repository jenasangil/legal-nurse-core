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

// WooCommerce product meta (Pricing Note + Features) — only when WooCommerce is active.
add_action( 'plugins_loaded', function () {
	if ( class_exists( 'WooCommerce' ) ) {
		require_once LNC_PLUGIN_DIR . 'includes/woocommerce-product-meta.php';
	}
} );

// Load Elementor extensions only after Elementor is ready.
add_action( 'elementor/init', function () {
	require_once LNC_PLUGIN_DIR . 'includes/elementor-lcp-hero.php';
	new LNC_Elementor_LCP_Hero();
} );

// Register the Pricing Cards widget.
add_action( 'elementor/widgets/register', function ( $widgets_manager ) {
	require_once LNC_PLUGIN_DIR . 'includes/elementor-pricing-cards.php';
	$widgets_manager->register( new LNC_Pricing_Cards_Widget() );
} );

// Enqueue the Pricing Cards stylesheet (frontend + Elementor editor preview).
add_action( 'wp_enqueue_scripts', 'lnc_enqueue_pricing_cards_css' );
add_action( 'elementor/preview/enqueue_styles', 'lnc_enqueue_pricing_cards_css' );
function lnc_enqueue_pricing_cards_css() {
	wp_enqueue_style(
		'lnc-pricing-cards',
		LNC_PLUGIN_URL . 'assets/css/pricing-cards.css',
		[],
		LNC_VERSION
	);
}
