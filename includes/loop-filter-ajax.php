<?php
/**
 * Loop Filter AJAX handler + asset registration.
 *
 * @package LegalNurseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register (not enqueue) the front-end assets so the widget can depend on them.
 */
add_action( 'wp_enqueue_scripts', 'lnc_loop_filter_register_assets' );
add_action( 'elementor/preview/enqueue_styles', 'lnc_loop_filter_register_assets' );
function lnc_loop_filter_register_assets() {
	wp_register_style(
		'lnc-loop-filter',
		LNC_PLUGIN_URL . 'assets/css/loop-filter.css',
		[],
		LNC_VERSION
	);

	wp_register_script(
		'lnc-loop-filter',
		LNC_PLUGIN_URL . 'assets/js/loop-filter.js',
		[],
		LNC_VERSION,
		true
	);

	wp_localize_script(
		'lnc-loop-filter',
		'lncLoopFilter',
		[
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		]
	);
}

/**
 * AJAX: return filtered/sorted loop items HTML.
 */
add_action( 'wp_ajax_lnc_loop_filter', 'lnc_loop_filter_ajax' );
add_action( 'wp_ajax_nopriv_lnc_loop_filter', 'lnc_loop_filter_ajax' );
function lnc_loop_filter_ajax() {
	check_ajax_referer( 'lnc_loop_filter', 'nonce' );

	$term      = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : 'all';
	$sort      = isset( $_POST['sort'] ) ? sanitize_key( wp_unslash( $_POST['sort'] ) ) : 'recent';
	$page      = isset( $_POST['page'] ) ? max( 1, absint( $_POST['page'] ) ) : 1;
	$post_type = isset( $_POST['post_type'] ) ? sanitize_key( wp_unslash( $_POST['post_type'] ) ) : 'post';
	$taxonomy  = isset( $_POST['taxonomy'] ) ? sanitize_key( wp_unslash( $_POST['taxonomy'] ) ) : 'category';
	$template  = isset( $_POST['template'] ) ? absint( $_POST['template'] ) : 0;
	$ppp       = isset( $_POST['ppp'] ) ? absint( $_POST['ppp'] ) : 6;
	$views_key = isset( $_POST['views_key'] ) ? sanitize_key( wp_unslash( $_POST['views_key'] ) ) : 'post_views_count';

	$ppp = $ppp > 0 ? min( $ppp, 48 ) : 6;

	// Allowed term IDs (when the widget is limited to selected categories).
	$allowed = [];
	if ( isset( $_POST['allowed'] ) ) {
		$allowed = array_filter( array_map( 'absint', (array) wp_unslash( $_POST['allowed'] ) ) );
	}

	$args = [
		'post_type'           => $post_type,
		'post_status'         => 'publish',
		'posts_per_page'      => $ppp,
		'paged'               => $page,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => false,
	];

	if ( 'all' !== $term && is_numeric( $term ) ) {
		// A specific category was chosen.
		$args['tax_query'] = [
			[
				'taxonomy' => $taxonomy,
				'field'    => 'term_id',
				'terms'    => (int) $term,
			],
		];
	} elseif ( ! empty( $allowed ) ) {
		// "All" but the widget is limited to selected categories.
		$args['tax_query'] = [
			[
				'taxonomy' => $taxonomy,
				'field'    => 'term_id',
				'terms'    => $allowed,
			],
		];
	}

	if ( 'viewed' === $sort ) {
		// Sort by view count; posts without the meta are treated as 0 and still included.
		$args['meta_query'] = [
			'relation'     => 'OR',
			'lnc_views'    => [ 'key' => $views_key, 'compare' => 'EXISTS', 'type' => 'NUMERIC' ],
			'lnc_no_views' => [ 'key' => $views_key, 'compare' => 'NOT EXISTS' ],
		];
		$args['orderby'] = [ 'lnc_views' => 'DESC', 'date' => 'DESC' ];
	} else {
		$args['orderby'] = 'date';
		$args['order']   = 'DESC';
	}

	$query = new WP_Query( $args );

	$html = '';
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$html .= lnc_loop_filter_render_item( $template, get_the_ID() );
		}
	}
	wp_reset_postdata();

	wp_send_json_success(
		[
			'html'       => $html,
			'pagination' => lnc_loop_filter_pagination( $page, (int) $query->max_num_pages ),
			'found'      => (int) $query->found_posts,
			'maxPages'   => (int) $query->max_num_pages,
			'page'       => $page,
			'empty'      => '' === $html,
		]
	);
}

/**
 * Build pagination markup using Elementor's pagination classes so the target
 * Loop Grid's own pagination styling applies to it.
 *
 * @param int $current Current page.
 * @param int $max     Total pages.
 * @return string
 */
function lnc_loop_filter_pagination( $current, $max ) {
	if ( $max < 2 ) {
		return '';
	}

	$links = paginate_links(
		[
			'base'      => '#%#%',
			'format'    => '%#%',
			'current'   => max( 1, $current ),
			'total'     => $max,
			'type'      => 'array',
			'mid_size'  => 1,
			'end_size'  => 1,
			'prev_text' => '&laquo; ' . esc_html__( 'Previous', 'legal-nurse-core' ),
			'next_text' => esc_html__( 'Next', 'legal-nurse-core' ) . ' &raquo;',
		]
	);

	if ( empty( $links ) ) {
		return '';
	}

	// Elementor wraps pagination in .elementor-pagination; its links use .page-numbers.
	return '<nav class="elementor-pagination" role="navigation" aria-label="' . esc_attr__( 'Pagination', 'legal-nurse-core' ) . '">'
		. implode( '', $links )
		. '</nav>';
}

/**
 * Render a single post using the Elementor Loop Item template, wrapped so it
 * drops into the Loop Grid container as a grid item.
 *
 * @param int $template_id Elementor Loop template ID.
 * @param int $post_id     Current post ID.
 * @return string
 */
function lnc_loop_filter_render_item( $template_id, $post_id ) {
	$inner = '';

	if ( $template_id && class_exists( '\Elementor\Plugin' ) ) {
		$inner = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template_id, true );
	}

	if ( '' === $inner ) {
		// Minimal fallback so filtering still shows something if no template is set.
		$inner = sprintf(
			'<a href="%s" class="lnc-loop-fallback"><span>%s</span></a>',
			esc_url( get_permalink( $post_id ) ),
			esc_html( get_the_title( $post_id ) )
		);
	}

	return sprintf(
		'<div class="e-loop-item lnc-loop-item post-%d">%s</div>',
		(int) $post_id,
		$inner
	);
}
