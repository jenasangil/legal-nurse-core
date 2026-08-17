<?php
/**
 * Page Categories
 *
 * Enables the standard Category taxonomy on Pages, so pages can be assigned
 * categories (Category box appears on the page editor, and pages become
 * filterable by category — e.g. in the LN - Loop Filter / Child Pages widgets).
 *
 * @package LegalNurseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'lnc_add_category_to_pages' );
function lnc_add_category_to_pages() {
	register_taxonomy_for_object_type( 'category', 'page' );
}

/**
 * Make sure page category archives include pages (core category queries only
 * pull posts by default).
 */
add_action( 'pre_get_posts', 'lnc_include_pages_in_category_archives' );
function lnc_include_pages_in_category_archives( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_category() ) {
		$types = $query->get( 'post_type' );
		if ( empty( $types ) ) {
			$types = [ 'post' ];
		} elseif ( ! is_array( $types ) ) {
			$types = [ $types ];
		}
		if ( ! in_array( 'page', $types, true ) ) {
			$types[] = 'page';
		}
		$query->set( 'post_type', $types );
	}
}
