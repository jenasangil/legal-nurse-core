<?php
/**
 * Consultant Stories — shared query/render helpers + AJAX handler.
 *
 * Loaded unconditionally (not tied to Elementor widget registration) so the
 * AJAX action is available on admin-ajax requests. Used by the
 * "LN - Consultant Stories" widget (elementor-pages-by-category.php).
 *
 * @package LegalNurseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Byline HTML allowed tags. */
function lnc_cs_byline_tags() {
	return [ 'em' => [], 'i' => [], 'strong' => [], 'b' => [], 'span' => [ 'class' => [] ], 'br' => [], 'a' => [ 'href' => [], 'target' => [] ] ];
}

/**
 * Keep only the author line — cut at the earliest natural break after it.
 *
 * @param string $html Byline HTML (image already removed).
 * @return string
 */
function lnc_cs_author_line( $html ) {
	if ( preg_match( '/^\s*(?:by\b)?[^<]{0,60}<a\b[^>]*>.*?<\/a>/is', $html, $m ) ) {
		return trim( $m[0] );
	}
	if ( preg_match( '/<br\s*\/?>|<\/p>|\r\n|\n|\r/i', $html, $mb, PREG_OFFSET_CAPTURE ) ) {
		return trim( substr( $html, 0, $mb[0][1] ) );
	}
	return trim( $html );
}

/**
 * Render the story <li> items for a filter/page and report page count.
 *
 * @param array $o taxonomy, term_ids, active_term, orderby, order, per_page,
 *                 page, field, show_more, more_label, arrow_html.
 * @return array{ html:string, max:int, found:int }
 */
function lnc_cs_render_items( $o ) {
	$term_ids = array_map( 'intval', (array) ( $o['term_ids'] ?? [] ) );
	$active   = $o['active_term'] ?? 'all';
	$terms    = ( 'all' === $active ) ? $term_ids : array_values( array_intersect( $term_ids, [ (int) $active ] ) );
	if ( empty( $terms ) ) {
		$terms = $term_ids;
	}

	$per_page = (int) ( $o['per_page'] ?? -1 );
	$q        = new WP_Query(
		[
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'paged'          => max( 1, (int) ( $o['page'] ?? 1 ) ),
			'orderby'        => $o['orderby'] ?? 'menu_order',
			'order'          => ( 'DESC' === ( $o['order'] ?? 'ASC' ) ) ? 'DESC' : 'ASC',
			'no_found_rows'  => $per_page < 1,
			'tax_query'      => [
				[ 'taxonomy' => $o['taxonomy'], 'field' => 'term_id', 'terms' => $terms ],
			],
		]
	);

	$field      = $o['field'] ?? 'right_box_text';
	$show_more  = ! empty( $o['show_more'] );
	$more_label = $o['more_label'] ?? '';
	$arrow_html = $o['arrow_html'] ?? '&rarr;';

	$html = '';
	if ( $q->have_posts() ) {
		while ( $q->have_posts() ) {
			$q->the_post();
			$id = get_the_ID();

			$page_terms = wp_get_post_terms( $id, $o['taxonomy'], [ 'fields' => 'ids' ] );
			$page_terms = is_array( $page_terms ) ? array_intersect( $page_terms, $term_ids ) : [];
			$data_terms = implode( ' ', array_map( 'intval', $page_terms ) );

			$byline = function_exists( 'get_field' ) ? get_field( $field, $id ) : '';
			$byline = is_string( $byline ) ? trim( preg_replace( '/<img\b[^>]*>/i', '', $byline ) ) : '';
			if ( '' !== $byline ) {
				$byline = lnc_cs_author_line( $byline );
			}

			$url   = get_permalink();
			$title = wp_strip_all_tags( html_entity_decode( get_the_title(), ENT_QUOTES ) );

			$html .= '<li class="lnc-pbc__item" data-terms="' . esc_attr( $data_terms ) . '">';
			$html .= '<span class="lnc-pbc__content">';
			$html .= sprintf( '<span class="lnc-pbc__title"><a href="%s">%s</a></span>', esc_url( $url ), esc_html( $title ) );
			if ( '' !== $byline ) {
				$html .= '<span class="lnc-pbc__byline">' . wp_kses( $byline, lnc_cs_byline_tags() ) . '</span>';
			}
			if ( $show_more ) {
				$html .= sprintf(
					'<a class="lnc-pbc__more" href="%s"><span class="lnc-pbc__more-label">%s</span> <span class="lnc-pbc__more-arrow" aria-hidden="true">%s</span></a>',
					esc_url( $url ),
					esc_html( $more_label ),
					$arrow_html
				);
			}
			$html .= '</span></li>';
		}
	}
	$max = (int) $q->max_num_pages;
	wp_reset_postdata();

	return [ 'html' => $html, 'max' => $max, 'found' => (int) $q->found_posts ];
}

/** Numbered pagination markup (AJAX buttons with data-page). */
function lnc_cs_build_pagination( $current, $max ) {
	if ( $max < 2 ) {
		return '';
	}
	$current = max( 1, (int) $current );
	$items   = '';
	$dots    = false;
	for ( $i = 1; $i <= $max; $i++ ) {
		if ( $i <= 2 || $i > $max - 2 || abs( $i - $current ) <= 1 ) {
			$cls    = 'page-numbers' . ( $i === $current ? ' current' : '' );
			$items .= sprintf( '<button type="button" class="%s" data-page="%d">%d</button>', esc_attr( $cls ), $i, $i );
			$dots   = false;
		} elseif ( ! $dots ) {
			$items .= '<span class="page-numbers dots">&hellip;</span>';
			$dots   = true;
		}
	}
	return $items;
}

/**
 * AJAX: filtered + paginated Consultant Stories items.
 */
add_action( 'wp_ajax_lnc_pbc', 'lnc_pbc_ajax' );
add_action( 'wp_ajax_nopriv_lnc_pbc', 'lnc_pbc_ajax' );
function lnc_pbc_ajax() {
	check_ajax_referer( 'lnc_pbc', 'nonce' );

	$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_key( wp_unslash( $_POST['taxonomy'] ) ) : 'page_category';
	$term     = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : 'all';
	$page     = isset( $_POST['page'] ) ? max( 1, absint( $_POST['page'] ) ) : 1;
	$per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 9;
	$per_page = $per_page > 0 ? min( $per_page, 48 ) : 9;
	$orderby  = isset( $_POST['orderby'] ) ? sanitize_key( wp_unslash( $_POST['orderby'] ) ) : 'menu_order';
	$order    = ( isset( $_POST['order'] ) && 'DESC' === $_POST['order'] ) ? 'DESC' : 'ASC';
	$field    = isset( $_POST['field'] ) ? sanitize_key( wp_unslash( $_POST['field'] ) ) : 'right_box_text';
	$show     = ! empty( $_POST['show_more'] );
	$label    = isset( $_POST['more_label'] ) ? sanitize_text_field( wp_unslash( $_POST['more_label'] ) ) : '';

	$term_ids = [];
	if ( isset( $_POST['term_ids'] ) ) {
		$term_ids = array_filter( array_map( 'absint', (array) wp_unslash( $_POST['term_ids'] ) ) );
	}

	// Read-more arrow icon (rebuild from value/library).
	$arrow = '&rarr;';
	$iv    = isset( $_POST['icon_value'] ) ? sanitize_text_field( wp_unslash( $_POST['icon_value'] ) ) : '';
	$il    = isset( $_POST['icon_library'] ) ? sanitize_text_field( wp_unslash( $_POST['icon_library'] ) ) : '';
	if ( '' !== $iv && class_exists( '\Elementor\Icons_Manager' ) ) {
		ob_start();
		\Elementor\Icons_Manager::render_icon( [ 'value' => $iv, 'library' => $il ], [ 'aria-hidden' => 'true' ] );
		$arrow = ob_get_clean();
	}

	$res = lnc_cs_render_items(
		[
			'taxonomy'    => $taxonomy,
			'term_ids'    => $term_ids,
			'active_term' => $term,
			'orderby'     => $orderby,
			'order'       => $order,
			'per_page'    => $per_page,
			'page'        => $page,
			'field'       => $field,
			'show_more'   => $show,
			'more_label'  => $label,
			'arrow_html'  => $arrow,
		]
	);

	wp_send_json_success(
		[
			'html'       => $res['html'],
			'pagination' => lnc_cs_build_pagination( $page, $res['max'] ),
			'page'       => $page,
			'maxPages'   => $res['max'],
			'empty'      => '' === $res['html'],
		]
	);
}
