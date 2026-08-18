<?php
/**
 * Page Categories
 *
 * Registers a dedicated, hierarchical "Page Category" taxonomy for Pages —
 * separate from the blog post categories — so pages can be organized and
 * filtered without mixing with post categories.
 *
 * @package LegalNurseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const LNC_PAGE_TAXONOMY = 'page_category';

add_action( 'init', 'lnc_register_page_category_taxonomy' );
function lnc_register_page_category_taxonomy() {
	$labels = [
		'name'              => esc_html__( 'Page Categories', 'legal-nurse-core' ),
		'singular_name'     => esc_html__( 'Page Category', 'legal-nurse-core' ),
		'search_items'      => esc_html__( 'Search Page Categories', 'legal-nurse-core' ),
		'all_items'         => esc_html__( 'All Page Categories', 'legal-nurse-core' ),
		'parent_item'       => esc_html__( 'Parent Page Category', 'legal-nurse-core' ),
		'parent_item_colon' => esc_html__( 'Parent Page Category:', 'legal-nurse-core' ),
		'edit_item'         => esc_html__( 'Edit Page Category', 'legal-nurse-core' ),
		'update_item'       => esc_html__( 'Update Page Category', 'legal-nurse-core' ),
		'add_new_item'      => esc_html__( 'Add New Page Category', 'legal-nurse-core' ),
		'new_item_name'     => esc_html__( 'New Page Category Name', 'legal-nurse-core' ),
		'menu_name'         => esc_html__( 'Page Categories', 'legal-nurse-core' ),
	];

	register_taxonomy(
		LNC_PAGE_TAXONOMY,
		[ 'page' ],
		[
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'show_in_menu'      => true,
			'query_var'         => true,
			'rewrite'           => [ 'slug' => 'page-category', 'with_front' => false ],
		]
	);
}

/**
 * Include pages in this taxonomy's archive pages.
 */
add_action( 'pre_get_posts', 'lnc_include_pages_in_page_category_archives' );
function lnc_include_pages_in_page_category_archives( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_tax( LNC_PAGE_TAXONOMY ) ) {
		$query->set( 'post_type', [ 'page' ] );
	}
}
