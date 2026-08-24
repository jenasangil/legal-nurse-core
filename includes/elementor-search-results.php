<?php
/**
 * Elementor Search Results Widget
 *
 * Renders search results for the current ?s= query on a results page
 * (e.g. /blog-search-results/?s=Vickie). Each result is rendered with a chosen
 * Elementor Loop Item template so results look identical to the blog grid.
 * Pairs with the Loop Filter widget's expandable search.
 *
 * @package LegalNurseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LNC_Search_Results_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'lnc_search_results';
	}

	public function get_title() {
		return esc_html__( 'LN - Search Results', 'legal-nurse-core' );
	}

	public function get_icon() {
		return 'eicon-search-results';
	}

	public function get_categories() {
		return [ 'legal-nurse' ];
	}

	public function get_keywords() {
		return [ 'search', 'results', 'query', 's', 'blog', 'loop' ];
	}

	public function get_style_depends() {
		return [ 'lnc-search-results', 'lnc-loop-filter' ];
	}

	/**
	 * Loop Item templates for the selector (editor-only query).
	 *
	 * @return array<int,string>
	 */
	private function get_loop_template_options() {
		$options = [ 0 => esc_html__( '— Select a Loop template —', 'legal-nurse-core' ) ];

		$is_editor = is_admin()
			|| \Elementor\Plugin::$instance->editor->is_edit_mode()
			|| ( isset( $_GET['action'] ) && 'elementor' === $_GET['action'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! $is_editor ) {
			return $options;
		}

		$templates = get_posts(
			[
				'post_type'      => 'elementor_library',
				'posts_per_page' => 100,
				'post_status'    => 'publish',
				'orderby'        => 'title',
				'order'          => 'ASC',
				'meta_query'     => [
					[
						'key'     => '_elementor_template_type',
						'value'   => 'loop-item',
						'compare' => '=',
					],
				],
			]
		);

		foreach ( $templates as $template ) {
			$options[ $template->ID ] = $template->post_title ? $template->post_title : ( '#' . $template->ID );
		}

		return $options;
	}

	protected function register_controls() {

		$this->start_controls_section( 'section_content', [ 'label' => esc_html__( 'Search Results', 'legal-nurse-core' ) ] );

		$this->add_control(
			'search_param',
			[
				'label'       => esc_html__( 'Query Parameter', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'sq',
				'description' => esc_html__( 'URL parameter holding the search term (e.g. "sq" for ?sq=keyword). Avoid the reserved "s" — WordPress hijacks it for its built-in search.', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'loop_template_id',
			[
				'label'       => esc_html__( 'Loop Item Template', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'options'     => $this->get_loop_template_options(),
				'default'     => 0,
				'description' => esc_html__( 'Template used to render each result (match your blog Loop Grid).', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'post_type',
			[
				'label'   => esc_html__( 'Post Type', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'post',
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label'   => esc_html__( 'Results Per Page', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 9,
				'min'     => 1,
				'max'     => 48,
			]
		);

		$this->add_control(
			'show_heading',
			[
				'label'        => esc_html__( 'Show Heading', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'heading_text',
			[
				'label'       => esc_html__( 'Heading', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Search results for “{term}”', 'legal-nurse-core' ),
				'description' => esc_html__( 'Use {term} for the search term and {count} for the number of results.', 'legal-nurse-core' ),
				'condition'   => [ 'show_heading' => 'yes' ],
			]
		);

		$this->add_control(
			'no_results_text',
			[
				'label'   => esc_html__( 'No Results Text', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'No results found for “{term}”. Try a different keyword.', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'empty_query_text',
			[
				'label'   => esc_html__( 'Empty Query Text', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Enter a keyword to search.', 'legal-nurse-core' ),
			]
		);

		$this->end_controls_section();

		$this->register_style_controls();
	}

	private function register_style_controls() {
		$this->start_controls_section( 'section_style', [ 'label' => esc_html__( 'Layout', 'legal-nurse-core' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );

		$this->add_responsive_control(
			'columns',
			[
				'label'          => esc_html__( 'Columns', 'legal-nurse-core' ),
				'type'           => \Elementor\Controls_Manager::SELECT,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => [ '1' => '1', '2' => '2', '3' => '3', '4' => '4' ],
				'selectors'      => [ '{{WRAPPER}} .lnc-search-grid' => 'grid-template-columns:repeat({{VALUE}},1fr);' ],
			]
		);

		$this->add_responsive_control(
			'gap',
			[
				'label'      => esc_html__( 'Gap', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
				'default'    => [ 'size' => 32, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-search-grid' => 'gap:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_control(
			'heading_color',
			[
				'label'     => esc_html__( 'Heading Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .lnc-search-results__heading' => 'color:{{VALUE}};' ],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[ 'name' => 'heading_typography', 'selector' => '{{WRAPPER}} .lnc-search-results__heading' ]
		);

		$this->add_responsive_control(
			'heading_spacing',
			[
				'label'      => esc_html__( 'Heading Spacing', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
				'default'    => [ 'size' => 28, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-search-results__heading' => 'margin-bottom:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->end_controls_section();
	}

	/** Replace {term} and {count} tokens. */
	private function tokens( $text, $term, $count ) {
		return str_replace(
			[ '{term}', '{count}' ],
			[ esc_html( $term ), number_format_i18n( $count ) ],
			$text
		);
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$param     = $settings['search_param'] ? sanitize_key( $settings['search_param'] ) : 's';
		$post_type = $settings['post_type'] ? $settings['post_type'] : 'post';
		$template  = (int) ( $settings['loop_template_id'] ?? 0 );
		$ppp       = (int) ( $settings['posts_per_page'] ?? 9 );
		$ppp       = $ppp > 0 ? min( $ppp, 48 ) : 9;

		// The search term from the URL.
		$term = isset( $_GET[ $param ] ) ? sanitize_text_field( wp_unslash( $_GET[ $param ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		echo '<div class="lnc-search-results">';

		$paged = max(
			1,
			(int) ( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : ( get_query_var( 'page' ) ? get_query_var( 'page' ) : ( isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1 ) ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		);

		$query_args = [
			'post_type'           => $post_type,
			'post_status'         => 'publish',
			'posts_per_page'      => $ppp,
			'paged'               => $paged,
			'ignore_sticky_posts' => true,
		];

		// No term: show all posts (unfiltered). With a term: filter by search.
		$has_term = ( '' !== $term );
		if ( $has_term ) {
			$query_args['s'] = $term;
		}

		$query = new WP_Query( $query_args );

		// If a search plugin (Relevanssi) is active, run the search through it —
		// its index replaces WordPress's default search, which otherwise returns
		// nothing for secondary queries like this one.
		if ( $has_term && function_exists( 'relevanssi_do_query' ) ) {
			relevanssi_do_query( $query );
		}

		// Heading only when there's a search term.
		if ( $has_term && 'yes' === ( $settings['show_heading'] ?? '' ) && ! empty( $settings['heading_text'] ) ) {
			echo '<h2 class="lnc-search-results__heading">'
				. wp_kses_post( $this->tokens( $settings['heading_text'], $term, (int) $query->found_posts ) )
				. '</h2>';
		}

		if ( ! $query->have_posts() ) {
			echo '<div class="lnc-search-results__message">' . esc_html( $this->tokens( $settings['no_results_text'] ?? '', $term, 0 ) ) . '</div>';
			echo '</div>';
			wp_reset_postdata();
			return;
		}

		echo '<div class="lnc-search-grid e-loop-grid">';
		while ( $query->have_posts() ) {
			$query->the_post();
			echo function_exists( 'lnc_loop_filter_render_item' )
				? lnc_loop_filter_render_item( $template, get_the_ID() ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				: '';
		}
		echo '</div>';

		$this->render_pagination( $paged, (int) $query->max_num_pages, $param, $term );

		wp_reset_postdata();

		echo '</div>';
	}

	/** Real (non-AJAX) pagination links that preserve the search query. */
	private function render_pagination( $current, $max, $param, $term ) {
		if ( $max < 2 ) {
			return;
		}

		$big   = 999999999;
		$links = paginate_links(
			[
				'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format'    => '?paged=%#%',
				'current'   => max( 1, $current ),
				'total'     => $max,
				'add_args'  => [ $param => rawurlencode( $term ) ],
				'mid_size'  => 1,
				'end_size'  => 1,
				'prev_text' => '&laquo; ' . esc_html__( 'Previous', 'legal-nurse-core' ),
				'next_text' => esc_html__( 'Next', 'legal-nurse-core' ) . ' &raquo;',
			]
		);

		if ( $links ) {
			echo '<nav class="elementor-pagination lnc-search-results__pagination" role="navigation" aria-label="' . esc_attr__( 'Pagination', 'legal-nurse-core' ) . '">'
				. $links // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				. '</nav>';
		}
	}
}
