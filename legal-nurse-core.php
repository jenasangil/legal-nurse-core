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
require_once LNC_PLUGIN_DIR . 'includes/shortcodes.php';
require_once LNC_PLUGIN_DIR . 'includes/loop-filter-ajax.php';
require_once LNC_PLUGIN_DIR . 'includes/page-categories.php';

// Load Elementor extensions only after Elementor is ready.
add_action( 'elementor/init', function () {
	require_once LNC_PLUGIN_DIR . 'includes/elementor-lcp-hero.php';
	new LNC_Elementor_LCP_Hero();
} );

// Register the "Legal Nurse" widget category.
add_action( 'elementor/elements/categories_registered', function ( $elements_manager ) {
	$elements_manager->add_category(
		'legal-nurse',
		[
			'title' => esc_html__( 'Legal Nurse', 'legal-nurse-core' ),
			'icon'  => 'fa fa-plug',
		]
	);
} );

// Register widgets.
add_action( 'elementor/widgets/register', function ( $widgets_manager ) {
	require_once LNC_PLUGIN_DIR . 'includes/elementor-pricing-cards.php';
	$widgets_manager->register( new LNC_Pricing_Cards_Widget() );

	require_once LNC_PLUGIN_DIR . 'includes/elementor-loop-filter.php';
	$widgets_manager->register( new LNC_Loop_Filter_Widget() );

	require_once LNC_PLUGIN_DIR . 'includes/elementor-social-share.php';
	$widgets_manager->register( new LNC_Social_Share_Widget() );

	require_once LNC_PLUGIN_DIR . 'includes/elementor-compare-table.php';
	$widgets_manager->register( new LNC_Compare_Table_Widget() );

	require_once LNC_PLUGIN_DIR . 'includes/elementor-acf-content.php';
	$widgets_manager->register( new LNC_ACF_Content_Widget() );

	require_once LNC_PLUGIN_DIR . 'includes/elementor-featured-carousel.php';
	$widgets_manager->register( new LNC_Featured_Carousel_Widget() );

	require_once LNC_PLUGIN_DIR . 'includes/elementor-child-pages.php';
	$widgets_manager->register( new LNC_Child_Pages_Widget() );

	require_once LNC_PLUGIN_DIR . 'includes/elementor-web-lead-form.php';
	$widgets_manager->register( new LNC_Web_Lead_Form_Widget() );

	require_once LNC_PLUGIN_DIR . 'includes/elementor-memorable-cases.php';
	$widgets_manager->register( new LNC_Memorable_Cases_Widget() );
} );

// Register Memorable Cases stylesheet.
add_action( 'wp_enqueue_scripts', 'lnc_register_memorable_cases_assets' );
add_action( 'elementor/preview/enqueue_styles', 'lnc_register_memorable_cases_assets' );
function lnc_register_memorable_cases_assets() {
	wp_register_style( 'lnc-memorable-cases', LNC_PLUGIN_URL . 'assets/css/memorable-cases.css', [], LNC_VERSION );
}

// Register Web Lead Form assets (style + Creatio tracking scripts).
add_action( 'wp_enqueue_scripts', 'lnc_register_web_lead_form_assets' );
add_action( 'elementor/preview/enqueue_styles', 'lnc_register_web_lead_form_assets' );
function lnc_register_web_lead_form_assets() {
	wp_register_style( 'lnc-web-lead-form', LNC_PLUGIN_URL . 'assets/css/web-lead-form.css', [], LNC_VERSION );

	wp_register_script( 'creatio-track-cookies', 'https://webtracking-v01.creatio.com/JS/track-cookies.js', [], null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	wp_register_script( 'creatio-create-object', 'https://webtracking-v01.creatio.com/JS/create-object.js', [], null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	wp_register_script(
		'lnc-web-lead-form',
		LNC_PLUGIN_URL . 'assets/js/web-lead-form.js',
		[ 'creatio-track-cookies', 'creatio-create-object' ],
		LNC_VERSION,
		true
	);
}

// Register ACF Content + Child Pages stylesheets.
add_action( 'wp_enqueue_scripts', 'lnc_register_acf_content_assets' );
add_action( 'elementor/preview/enqueue_styles', 'lnc_register_acf_content_assets' );
function lnc_register_acf_content_assets() {
	wp_register_style( 'lnc-acf-content', LNC_PLUGIN_URL . 'assets/css/acf-content.css', [], LNC_VERSION );
	wp_register_style( 'lnc-child-pages', LNC_PLUGIN_URL . 'assets/css/child-pages.css', [], LNC_VERSION );
}

// Register Comparison Table stylesheet.
add_action( 'wp_enqueue_scripts', 'lnc_register_compare_table_assets' );
add_action( 'elementor/preview/enqueue_styles', 'lnc_register_compare_table_assets' );
function lnc_register_compare_table_assets() {
	wp_register_style( 'lnc-compare-table', LNC_PLUGIN_URL . 'assets/css/compare-table.css', [], LNC_VERSION );
	wp_register_script( 'lnc-compare-table', LNC_PLUGIN_URL . 'assets/js/compare-table.js', [], LNC_VERSION, true );
}

// Load the default plugin font (Wix Madefor Display).
add_action( 'wp_enqueue_scripts', 'lnc_enqueue_fonts' );
add_action( 'elementor/preview/enqueue_styles', 'lnc_enqueue_fonts' );
function lnc_enqueue_fonts() {
	wp_enqueue_style(
		'lnc-fonts',
		'https://fonts.googleapis.com/css2?family=Wix+Madefor+Display:wght@400;500;600;700&family=Wix+Madefor+Text:wght@400;500;600;700&display=swap',
		[],
		null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	);
}

// Register Social Share assets.
add_action( 'wp_enqueue_scripts', 'lnc_register_social_share_assets' );
add_action( 'elementor/preview/enqueue_styles', 'lnc_register_social_share_assets' );
function lnc_register_social_share_assets() {
	wp_register_style( 'lnc-social-share', LNC_PLUGIN_URL . 'assets/css/social-share.css', [], LNC_VERSION );
	wp_register_script( 'lnc-social-share', LNC_PLUGIN_URL . 'assets/js/social-share.js', [], LNC_VERSION, true );
}

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

// Register Featured Carousel assets.
add_action( 'wp_enqueue_scripts', 'lnc_register_featured_carousel_assets' );
add_action( 'elementor/preview/enqueue_styles', 'lnc_register_featured_carousel_assets' );
function lnc_register_featured_carousel_assets() {
	wp_register_style( 'swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], null );
	wp_register_script( 'swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], null, true );
	
	wp_register_style( 'clnc-carousel', LNC_PLUGIN_URL . 'assets/css/clnc-carousel.css', [ 'swiper' ], filemtime( LNC_PLUGIN_DIR . 'assets/css/clnc-carousel.css' ) );
	wp_register_script( 'clnc-carousel', LNC_PLUGIN_URL . 'assets/js/clnc-carousel.js', [ 'swiper-js' ], filemtime( LNC_PLUGIN_DIR . 'assets/js/clnc-carousel.js' ), true );
}
