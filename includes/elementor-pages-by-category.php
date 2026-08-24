<?php
/**
 * Elementor Pages by Category Widget
 *
 * A category filter bar (with "View All" first) over a list of pages. Pick
 * Page Categories (page_category taxonomy) in a chosen order; the list shows
 * each page's title and its ACF byline (right_box_text). Clicking a chip
 * filters the list client-side.
 *
 * @package LegalNurseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LNC_Pages_By_Category_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'lnc_pages_by_category';
	}

	public function get_title() {
		return esc_html__( 'LN - Pages by Category', 'legal-nurse-core' );
	}

	public function get_icon() {
		return 'eicon-filter';
	}

	public function get_categories() {
		return [ 'legal-nurse' ];
	}

	public function get_keywords() {
		return [ 'pages', 'category', 'page category', 'filter', 'list', 'taxonomy' ];
	}

	public function get_style_depends() {
		return [ 'lnc-pages-by-category' ];
	}

	public function get_script_depends() {
		return [ 'lnc-pages-by-category' ];
	}

	private function get_term_options( $taxonomy ) {
		$options = [];

		// Only needed for the editor dropdown — skip on the front end.
		$is_editor = is_admin()
			|| \Elementor\Plugin::$instance->editor->is_edit_mode()
			|| ( isset( $_GET['action'] ) && 'elementor' === $_GET['action'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! $is_editor ) {
			return $options;
		}

		$terms = get_terms( [ 'taxonomy' => $taxonomy, 'hide_empty' => false ] );
		if ( is_wp_error( $terms ) ) {
			return $options;
		}
		foreach ( $terms as $term ) {
			$options[ $term->term_id ] = $term->name;
		}
		return $options;
	}

	protected function register_controls() {

		$this->start_controls_section( 'section_content', [ 'label' => esc_html__( 'Content', 'legal-nurse-core' ) ] );

		$this->add_control(
			'taxonomy',
			[
				'label'   => esc_html__( 'Taxonomy', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'page_category',
			]
		);

		$this->add_control(
			'terms',
			[
				'label'       => esc_html__( 'Page Categories', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->get_term_options( 'page_category' ),
				'description' => esc_html__( 'Order of selection = chip order. Leave empty to use all categories.', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'all_label',
			[
				'label'   => esc_html__( '"View All" Label', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'View All', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'byline_field',
			[
				'label'       => esc_html__( 'Byline ACF Field', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'right_box_text',
				'description' => esc_html__( 'ACF field shown under each title (e.g. the byline).', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'check_icon',
			[
				'label'   => esc_html__( 'Bullet Icon', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::ICONS,
				'default' => [ 'value' => 'fas fa-check', 'library' => 'fa-solid' ],
			]
		);

		$this->add_control(
			'number',
			[
				'label'       => esc_html__( 'Max Pages', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'description' => esc_html__( '0 = all pages in the selected categories.', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'orderby',
			[
				'label'   => esc_html__( 'Order By', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'menu_order',
				'options' => [
					'menu_order' => esc_html__( 'Menu Order', 'legal-nurse-core' ),
					'title'      => esc_html__( 'Title', 'legal-nurse-core' ),
					'date'       => esc_html__( 'Date', 'legal-nurse-core' ),
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label'   => esc_html__( 'Order', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'ASC',
				'options' => [ 'ASC' => esc_html__( 'Ascending', 'legal-nurse-core' ), 'DESC' => esc_html__( 'Descending', 'legal-nurse-core' ) ],
			]
		);

		$this->end_controls_section();

		$this->register_style_controls();
	}

	private function register_style_controls() {

		// Chips.
		$this->start_controls_section( 'section_chips', [ 'label' => esc_html__( 'Filter Chips', 'legal-nurse-core' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );

		$this->add_responsive_control(
			'chip_gap',
			[
				'label'      => esc_html__( 'Gap', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 30 ] ],
				'default'    => [ 'size' => 10, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-pbc__chips' => 'gap:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[ 'name' => 'chip_typography', 'selector' => '{{WRAPPER}} .lnc-pbc__chip' ]
		);

		$this->add_responsive_control(
			'chip_padding',
			[
				'label'      => esc_html__( 'Padding', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'default'    => [ 'top' => 8, 'right' => 18, 'bottom' => 8, 'left' => 18, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-pbc__chip' => 'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			]
		);

		$this->start_controls_tabs( 'chip_tabs' );

		$this->start_controls_tab( 'chip_normal', [ 'label' => esc_html__( 'Normal', 'legal-nurse-core' ) ] );
		$this->add_control( 'chip_color', [ 'label' => esc_html__( 'Text', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#93003a', 'selectors' => [ '{{WRAPPER}} .lnc-pbc__chip' => 'color:{{VALUE}};' ] ] );
		$this->add_control( 'chip_bg', [ 'label' => esc_html__( 'Background', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#ffffff', 'selectors' => [ '{{WRAPPER}} .lnc-pbc__chip' => 'background:{{VALUE}};' ] ] );
		$this->add_control( 'chip_border', [ 'label' => esc_html__( 'Border', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#e6b7cd', 'selectors' => [ '{{WRAPPER}} .lnc-pbc__chip' => 'border-color:{{VALUE}};' ] ] );
		$this->end_controls_tab();

		$this->start_controls_tab( 'chip_active', [ 'label' => esc_html__( 'Active', 'legal-nurse-core' ) ] );
		$this->add_control( 'chip_color_a', [ 'label' => esc_html__( 'Text', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#93003a', 'selectors' => [ '{{WRAPPER}} .lnc-pbc__chip.is-active' => 'color:{{VALUE}};' ] ] );
		$this->add_control( 'chip_bg_a', [ 'label' => esc_html__( 'Background', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#f6e7ee', 'selectors' => [ '{{WRAPPER}} .lnc-pbc__chip.is-active' => 'background:{{VALUE}};' ] ] );
		$this->add_control( 'chip_border_a', [ 'label' => esc_html__( 'Border', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#93003a', 'selectors' => [ '{{WRAPPER}} .lnc-pbc__chip.is-active' => 'border-color:{{VALUE}};' ] ] );
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'chip_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
				'default'    => [ 'size' => 999, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-pbc__chip' => 'border-radius:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->end_controls_section();

		// List.
		$this->start_controls_section( 'section_list', [ 'label' => esc_html__( 'Page List', 'legal-nurse-core' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );

		$this->add_responsive_control(
			'list_top',
			[
				'label'      => esc_html__( 'List Top Spacing', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
				'default'    => [ 'size' => 28, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-pbc__list' => 'margin-top:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_responsive_control(
			'item_spacing',
			[
				'label'      => esc_html__( 'Item Spacing', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
				'default'    => [ 'size' => 18, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-pbc__item' => 'margin-bottom:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_control( 'icon_color', [ 'label' => esc_html__( 'Bullet Color', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#d6006e', 'selectors' => [ '{{WRAPPER}} .lnc-pbc__bullet' => 'color:{{VALUE}};' ] ] );
		$this->add_control( 'title_color', [ 'label' => esc_html__( 'Title Color', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .lnc-pbc__title a' => 'color:{{VALUE}};' ] ] );
		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'title_typography', 'label' => esc_html__( 'Title', 'legal-nurse-core' ), 'selector' => '{{WRAPPER}} .lnc-pbc__title' ] );
		$this->add_control( 'byline_color', [ 'label' => esc_html__( 'Byline Color', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#3a3a3a', 'selectors' => [ '{{WRAPPER}} .lnc-pbc__byline' => 'color:{{VALUE}};' ] ] );
		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'byline_typography', 'label' => esc_html__( 'Byline', 'legal-nurse-core' ), 'selector' => '{{WRAPPER}} .lnc-pbc__byline' ] );

		$this->end_controls_section();
	}

	/** Byline HTML allowed tags. */
	private function byline_tags() {
		return [ 'em' => [], 'i' => [], 'strong' => [], 'b' => [], 'span' => [ 'class' => [] ], 'br' => [], 'a' => [ 'href' => [], 'target' => [] ] ];
	}

	protected function render() {
		wp_enqueue_style( 'lnc-pages-by-category' );

		$settings = $this->get_settings_for_display();
		$taxonomy = $settings['taxonomy'] ? $settings['taxonomy'] : 'page_category';

		if ( ! taxonomy_exists( $taxonomy ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="elementor-alert elementor-alert-warning">' . esc_html__( 'Pages by Category: taxonomy not found.', 'legal-nurse-core' ) . '</div>';
			}
			return;
		}

		$selected = array_map( 'intval', (array) ( $settings['terms'] ?? [] ) );
		$all_lbl  = $settings['all_label'] ? $settings['all_label'] : esc_html__( 'View All', 'legal-nurse-core' );
		$field    = $settings['byline_field'] ? $settings['byline_field'] : 'right_box_text';
		$number   = (int) ( $settings['number'] ?? 0 );
		$orderby  = $settings['orderby'] ?? 'menu_order';
		$order    = ( 'DESC' === ( $settings['order'] ?? 'ASC' ) ) ? 'DESC' : 'ASC';

		// Chip terms in chosen order (or all).
		$term_args = [ 'taxonomy' => $taxonomy, 'hide_empty' => false ];
		if ( ! empty( $selected ) ) {
			$term_args['include'] = $selected;
			$term_args['orderby'] = 'include';
		} else {
			$term_args['orderby'] = 'name';
		}
		$terms = get_terms( $term_args );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="elementor-alert elementor-alert-info">' . esc_html__( 'Pages by Category: select one or more Page Categories.', 'legal-nurse-core' ) . '</div>';
			}
			return;
		}
		$term_ids = wp_list_pluck( $terms, 'term_id' );

		// Pages in those categories.
		$pages = new WP_Query(
			[
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => $number > 0 ? $number : -1,
				'orderby'        => $orderby,
				'order'          => $order,
				'no_found_rows'  => true,
				'tax_query'      => [
					[
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $term_ids,
					],
				],
			]
		);

		// Prebuild bullet icon.
		$icon = $settings['check_icon'] ?? [];
		ob_start();
		if ( ! empty( $icon['value'] ) ) {
			\Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
		} else {
			echo '&#10003;';
		}
		$bullet = ob_get_clean();

		echo '<div class="lnc-pbc">';

		// Chips.
		echo '<div class="lnc-pbc__chips">';
		printf( '<button type="button" class="lnc-pbc__chip is-active" data-term="all">%s</button>', esc_html( $all_lbl ) );
		foreach ( $terms as $term ) {
			printf( '<button type="button" class="lnc-pbc__chip" data-term="%d">%s</button>', (int) $term->term_id, esc_html( $term->name ) );
		}
		echo '</div>';

		// List.
		echo '<ul class="lnc-pbc__list">';
		if ( $pages->have_posts() ) {
			while ( $pages->have_posts() ) {
				$pages->the_post();
				$id = get_the_ID();

				$page_terms = wp_get_post_terms( $id, $taxonomy, [ 'fields' => 'ids' ] );
				$page_terms = is_array( $page_terms ) ? array_intersect( $page_terms, $term_ids ) : [];
				$data_terms = implode( ' ', array_map( 'intval', $page_terms ) );

				$byline = function_exists( 'get_field' ) ? get_field( $field, $id ) : '';
				$byline = is_string( $byline ) ? trim( preg_replace( '/<img\b[^>]*>/i', '', $byline ) ) : '';

				echo '<li class="lnc-pbc__item" data-terms="' . esc_attr( $data_terms ) . '">';
				echo '<span class="lnc-pbc__bullet" aria-hidden="true">' . $bullet . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<span class="lnc-pbc__content">';
				printf( '<span class="lnc-pbc__title"><a href="%s">%s</a></span>', esc_url( get_permalink() ), esc_html( get_the_title() ) );
				if ( '' !== $byline ) {
					echo '<span class="lnc-pbc__byline">' . wp_kses( $byline, $this->byline_tags() ) . '</span>';
				}
				echo '</span>';
				echo '</li>';
			}
		}
		wp_reset_postdata();
		echo '</ul>';

		echo '</div>';
	}
}
