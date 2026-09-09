<?php
/**
 * Elementor Featured Carousel Widget
 *
 * @package LegalNurseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LNC_Featured_Carousel_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'lnc_featured_carousel';
	}

	public function get_title() {
		return esc_html__( 'LN - Featured Carousel', 'legal-nurse-core' );
	}

	public function get_icon() {
		return 'eicon-post-slider';
	}

	public function get_categories() {
		return [ 'legal-nurse' ];
	}

	public function get_keywords() {
		return [ 'carousel', 'slider', 'featured', 'pages', 'resources' ];
	}

	public function get_script_depends() {
		return [ 'swiper-js', 'clnc-carousel' ];
	}

	public function get_style_depends() {
		return [ 'swiper', 'clnc-carousel' ];
	}

	/**
	 * Get all published pages for the Select2 control.
	 */
	private function get_page_options() {
		$options = [];

		// Only needed to populate the editor dropdown — skip on the front end,
		// where render() uses saved IDs, not this list. Loading every page and
		// post (and priming their term cache) was killing front-end requests.
		$is_editor = is_admin()
			|| \Elementor\Plugin::$instance->editor->is_edit_mode()
			|| ( isset( $_GET['action'] ) && 'elementor' === $_GET['action'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! $is_editor ) {
			return $options;
		}

		$pages = get_posts( [
			'post_type'              => [ 'page', 'post' ],
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		] );

		foreach ( $pages as $p ) {
			$options[ $p->ID ] = $p->post_title;
		}

		return $options;
	}

	protected function register_controls() {
		// ==========================================
		// CONTENT TAB
		// ==========================================
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Categories', 'legal-nurse-core' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'category_name',
			[
				'label'       => esc_html__( 'Category Name', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'New Category', 'legal-nurse-core' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'selected_pages',
			[
				'label'       => esc_html__( 'Selected Pages', 'legal-nurse-core' ),
				'type'        => 'sortable_pages',
				'options'     => $this->get_page_options(),
			]
		);

		$this->add_control(
			'categories',
			[
				'label'       => esc_html__( 'Categories List', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[ 'category_name' => esc_html__( 'Getting Started', 'legal-nurse-core' ) ],
				],
				'title_field' => '{{{ category_name }}}',
			]
		);

		$this->end_controls_section();

		// ==========================================
		// CONTENT TAB: Link Options
		// ==========================================
		$this->start_controls_section(
			'section_content_link',
			[
				'label' => esc_html__( 'Learn More Link', 'legal-nurse-core' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'link_icon',
			[
				'label'       => esc_html__( 'Icon', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::ICONS,
				'default'     => [
					'value'   => 'fas fa-arrow-right',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'link_icon_position',
			[
				'label'   => esc_html__( 'Icon Position', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left'  => [
						'title' => esc_html__( 'Left', 'legal-nurse-core' ),
						'icon'  => 'eicon-h-align-left',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'legal-nurse-core' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'default' => 'right',
				'toggle'  => false,
			]
		);

		$this->end_controls_section();

		// ==========================================
		// CONTENT TAB: Navigation
		// ==========================================
		$this->start_controls_section(
			'section_content_navigation',
			[
				'label' => esc_html__( 'Navigation Icons', 'legal-nurse-core' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'nav_prev_icon',
			[
				'label'       => esc_html__( 'Previous Icon', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::ICONS,
				'skin'        => 'inline',
				'label_block' => false,
			]
		);

		$this->add_control(
			'nav_next_icon',
			[
				'label'       => esc_html__( 'Next Icon', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::ICONS,
				'skin'        => 'inline',
				'label_block' => false,
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB: Filter Buttons
		// ==========================================
		$this->start_controls_section(
			'section_style_filters',
			[
				'label' => esc_html__( 'Filter Buttons', 'legal-nurse-core' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'filters_padding',
			[
				'label'      => esc_html__( 'Padding', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .filter-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'filters_margin',
			[
				'label'      => esc_html__( 'Margin', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .filter-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'filters_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .filter-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_responsive_control(
			'filters_border_width',
			[
				'label'      => esc_html__( 'Border Width', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .filter-btn' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; border-style: solid;',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_filters_style' );

		// Normal
		$this->start_controls_tab(
			'tab_filters_normal',
			[ 'label' => esc_html__( 'Normal', 'legal-nurse-core' ) ]
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'filters_typography',
				'selector' => '{{WRAPPER}} .filter-btn',
			]
		);
		$this->add_control(
			'filters_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .filter-btn' => 'color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'filters_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .filter-btn' => 'background-color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'filters_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .filter-btn' => 'border-color: {{VALUE}};' ],
			]
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'filters_box_shadow',
				'selector' => '{{WRAPPER}} .filter-btn',
			]
		);
		$this->end_controls_tab();

		// Hover
		$this->start_controls_tab(
			'tab_filters_hover',
			[ 'label' => esc_html__( 'Hover', 'legal-nurse-core' ) ]
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'filters_typography_hover',
				'selector' => '{{WRAPPER}} .filter-btn:hover',
			]
		);
		$this->add_control(
			'filters_text_color_hover',
			[
				'label'     => esc_html__( 'Text Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .filter-btn:hover' => 'color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'filters_bg_color_hover',
			[
				'label'     => esc_html__( 'Background Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .filter-btn:hover' => 'background-color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'filters_border_color_hover',
			[
				'label'     => esc_html__( 'Border Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .filter-btn:hover' => 'border-color: {{VALUE}};' ],
			]
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'filters_box_shadow_hover',
				'selector' => '{{WRAPPER}} .filter-btn:hover',
			]
		);
		$this->end_controls_tab();

		// Active
		$this->start_controls_tab(
			'tab_filters_active',
			[ 'label' => esc_html__( 'Active', 'legal-nurse-core' ) ]
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'filters_typography_active',
				'selector' => '{{WRAPPER}} .filter-btn.active',
			]
		);
		$this->add_control(
			'filters_text_color_active',
			[
				'label'     => esc_html__( 'Text Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .filter-btn.active' => 'color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'filters_bg_color_active',
			[
				'label'     => esc_html__( 'Background Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .filter-btn.active' => 'background-color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'filters_border_color_active',
			[
				'label'     => esc_html__( 'Border Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .filter-btn.active' => 'border-color: {{VALUE}};' ],
			]
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'filters_box_shadow_active',
				'selector' => '{{WRAPPER}} .filter-btn.active',
			]
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB: Cards
		// ==========================================
		$this->start_controls_section(
			'section_style_cards',
			[
				'label' => esc_html__( 'Cards', 'legal-nurse-core' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'cards_padding',
			[
				'label'      => esc_html__( 'Padding', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .resource-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'cards_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .resource-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_control(
			'cards_border_width',
			[
				'label'      => esc_html__( 'Border Width', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .resource-card' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_cards_style' );
		// Normal
		$this->start_controls_tab(
			'tab_cards_normal',
			[ 'label' => esc_html__( 'Normal', 'legal-nurse-core' ) ]
		);
		$this->add_control(
			'cards_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .resource-card' => 'background-color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'cards_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .resource-card' => 'border-color: {{VALUE}};' ],
			]
		);
		$this->end_controls_tab();
		// Hover
		$this->start_controls_tab(
			'tab_cards_hover',
			[ 'label' => esc_html__( 'Hover', 'legal-nurse-core' ) ]
		);
		$this->add_control(
			'cards_bg_color_hover',
			[
				'label'     => esc_html__( 'Background Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .resource-card:hover' => 'background-color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'cards_border_color_hover',
			[
				'label'     => esc_html__( 'Border Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .resource-card:hover' => 'border-color: {{VALUE}};' ],
			]
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();

		// Card Title
		$this->add_control(
			'heading_card_title',
			[
				'label'     => esc_html__( 'Title', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'card_title_typography',
				'selector' => '{{WRAPPER}} .card-title',
			]
		);
		$this->add_control(
			'card_title_color',
			[
				'label'     => esc_html__( 'Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .card-title' => 'color: {{VALUE}};' ],
			]
		);
		$this->add_responsive_control(
			'card_title_spacing',
			[
				'label'      => esc_html__( 'Bottom Spacing', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 100 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .card-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		// Card Description
		$this->add_control(
			'heading_card_desc',
			[
				'label'     => esc_html__( 'Description', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'card_desc_typography',
				'selector' => '{{WRAPPER}} .card-desc',
			]
		);
		$this->add_control(
			'card_desc_color',
			[
				'label'     => esc_html__( 'Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .card-desc' => 'color: {{VALUE}};' ],
			]
		);

		// Card Link
		$this->add_control(
			'heading_card_link',
			[
				'label'     => esc_html__( 'Learn More Link', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'card_link_typography',
				'selector' => '{{WRAPPER}} .card-link',
			]
		);
		
		$this->add_responsive_control(
			'card_link_padding',
			[
				'label'      => esc_html__( 'Padding', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .card-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'card_link_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .card-link' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_control(
			'card_link_border_width',
			[
				'label'      => esc_html__( 'Border Width', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .card-link' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; border-style: solid;',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_card_link_style' );
		// Normal Link
		$this->start_controls_tab(
			'tab_card_link_normal',
			[ 'label' => esc_html__( 'Normal', 'legal-nurse-core' ) ]
		);
		$this->add_control(
			'card_link_color',
			[
				'label'     => esc_html__( 'Text Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .card-link' => 'color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'card_link_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .card-link' => 'background-color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'card_link_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .card-link' => 'border-color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'card_link_icon_stroke_color',
			[
				'label'     => esc_html__( 'Icon Stroke Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .card-link svg' => 'stroke: {{VALUE}};',
					'{{WRAPPER}} .card-link svg path' => 'stroke: {{VALUE}};' 
				],
			]
		);
		$this->add_control(
			'card_link_icon_fill_color',
			[
				'label'     => esc_html__( 'Icon Fill Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .card-link i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .card-link svg' => 'fill: {{VALUE}};',
					'{{WRAPPER}} .card-link svg path' => 'fill: {{VALUE}};' 
				],
			]
		);
		$this->end_controls_tab();

		// Hover Link
		$this->start_controls_tab(
			'tab_card_link_hover',
			[ 'label' => esc_html__( 'Hover', 'legal-nurse-core' ) ]
		);
		$this->add_control(
			'card_link_color_hover',
			[
				'label'     => esc_html__( 'Text Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .resource-card:hover .card-link, {{WRAPPER}} .card-link:hover' => 'color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'card_link_bg_color_hover',
			[
				'label'     => esc_html__( 'Background Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .resource-card:hover .card-link, {{WRAPPER}} .card-link:hover' => 'background-color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'card_link_border_color_hover',
			[
				'label'     => esc_html__( 'Border Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .resource-card:hover .card-link, {{WRAPPER}} .card-link:hover' => 'border-color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'card_link_icon_stroke_color_hover',
			[
				'label'     => esc_html__( 'Icon Stroke Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .resource-card:hover .card-link svg, {{WRAPPER}} .card-link:hover svg' => 'stroke: {{VALUE}};',
					'{{WRAPPER}} .resource-card:hover .card-link svg path, {{WRAPPER}} .card-link:hover svg path' => 'stroke: {{VALUE}};' 
				],
			]
		);
		$this->add_control(
			'card_link_icon_fill_color_hover',
			[
				'label'     => esc_html__( 'Icon Fill Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .resource-card:hover .card-link i, {{WRAPPER}} .card-link:hover i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .resource-card:hover .card-link svg, {{WRAPPER}} .card-link:hover svg' => 'fill: {{VALUE}};',
					'{{WRAPPER}} .resource-card:hover .card-link svg path, {{WRAPPER}} .card-link:hover svg path' => 'fill: {{VALUE}};' 
				],
			]
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'card_link_icon_size',
			[
				'label' => esc_html__( 'Icon Size', 'legal-nurse-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [ 'min' => 6, 'max' => 100 ],
				],
				'selectors' => [
					'{{WRAPPER}} .card-link i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .card-link svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'card_link_icon_spacing',
			[
				'label' => esc_html__( 'Icon Spacing', 'legal-nurse-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [ 'min' => 0, 'max' => 100 ],
				],
				'selectors' => [
					'{{WRAPPER}} .card-link' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		// Badge
		$this->add_control(
			'heading_card_badge',
			[
				'label'     => esc_html__( 'New Badge', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'card_badge_typography',
				'selector' => '{{WRAPPER}} .badge-new',
			]
		);
		$this->add_control(
			'card_badge_color',
			[
				'label'     => esc_html__( 'Text Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .badge-new' => 'color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'card_badge_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .badge-new' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB: Pagination & Nav
		// ==========================================
		$this->start_controls_section(
			'section_style_nav',
			[
				'label' => esc_html__( 'Pagination & Navigation', 'legal-nurse-core' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'pagination_typography',
				'selector' => '{{WRAPPER}} .resource-count, {{WRAPPER}} .swiper-pagination',
				'label'    => esc_html__( 'Pagination Typography', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'pagination_color',
			[
				'label'     => esc_html__( 'Pagination Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .resource-count, {{WRAPPER}} .swiper-pagination' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'heading_nav_arrows',
			[
				'label'     => esc_html__( 'Buttons & Arrows', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'nav_buttons_gap',
			[
				'label'      => esc_html__( 'Buttons Gap', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 100 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .nav-buttons' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'nav_btn_size',
			[
				'label'      => esc_html__( 'Button Size', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range'      => [
					'px' => [ 'min' => 20, 'max' => 150 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .nav-btn' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'nav_btn_padding',
			[
				'label'      => esc_html__( 'Padding', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .nav-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'nav_btn_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .nav-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'nav_btn_border_width',
			[
				'label'      => esc_html__( 'Border Width', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .nav-btn' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; border-style: solid;',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_nav_arrows' );
		
		// Normal
		$this->start_controls_tab(
			'tab_nav_normal',
			[ 'label' => esc_html__( 'Normal', 'legal-nurse-core' ) ]
		);
		$this->add_control(
			'nav_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .nav-btn' => 'background-color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'nav_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .nav-btn' => 'border-color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'nav_icon_stroke_color',
			[
				'label'     => esc_html__( 'Icon Stroke Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .nav-btn svg' => 'stroke: {{VALUE}};',
					'{{WRAPPER}} .nav-btn svg path' => 'stroke: {{VALUE}};' 
				],
			]
		);
		$this->add_control(
			'nav_icon_fill_color',
			[
				'label'     => esc_html__( 'Icon Fill Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .nav-btn i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nav-btn svg' => 'fill: {{VALUE}};',
					'{{WRAPPER}} .nav-btn svg path' => 'fill: {{VALUE}};' 
				],
			]
		);
		$this->end_controls_tab();

		// Hover
		$this->start_controls_tab(
			'tab_nav_hover',
			[ 'label' => esc_html__( 'Hover', 'legal-nurse-core' ) ]
		);
		$this->add_control(
			'nav_bg_color_hover',
			[
				'label'     => esc_html__( 'Background Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .nav-btn:hover' => 'background-color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'nav_border_color_hover',
			[
				'label'     => esc_html__( 'Border Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .nav-btn:hover' => 'border-color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'nav_icon_stroke_color_hover',
			[
				'label'     => esc_html__( 'Icon Stroke Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .nav-btn:hover svg' => 'stroke: {{VALUE}};',
					'{{WRAPPER}} .nav-btn:hover svg path' => 'stroke: {{VALUE}};' 
				],
			]
		);
		$this->add_control(
			'nav_icon_fill_color_hover',
			[
				'label'     => esc_html__( 'Icon Fill Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .nav-btn:hover i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nav-btn:hover svg' => 'fill: {{VALUE}};',
					'{{WRAPPER}} .nav-btn:hover svg path' => 'fill: {{VALUE}};' 
				],
			]
		);
		$this->end_controls_tab();

		// Disabled
		$this->start_controls_tab(
			'tab_nav_disabled',
			[ 'label' => esc_html__( 'Disabled', 'legal-nurse-core' ) ]
		);
		$this->add_control(
			'nav_bg_color_disabled',
			[
				'label'     => esc_html__( 'Background Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .nav-btn.swiper-button-disabled' => 'background-color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'nav_border_color_disabled',
			[
				'label'     => esc_html__( 'Border Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .nav-btn.swiper-button-disabled' => 'border-color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'nav_icon_stroke_color_disabled',
			[
				'label'     => esc_html__( 'Icon Stroke Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .nav-btn.swiper-button-disabled svg' => 'stroke: {{VALUE}};',
					'{{WRAPPER}} .nav-btn.swiper-button-disabled svg path' => 'stroke: {{VALUE}};' 
				],
			]
		);
		$this->add_control(
			'nav_icon_fill_color_disabled',
			[
				'label'     => esc_html__( 'Icon Fill Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ 
					'{{WRAPPER}} .nav-btn.swiper-button-disabled i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nav-btn.swiper-button-disabled svg' => 'fill: {{VALUE}};',
					'{{WRAPPER}} .nav-btn.swiper-button-disabled svg path' => 'fill: {{VALUE}};' 
				],
			]
		);
		$this->add_control(
			'nav_opacity_disabled',
			[
				'label'     => esc_html__( 'Opacity', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 0,
						'max'  => 1,
						'step' => 0.05,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nav-btn.swiper-button-disabled' => 'opacity: {{SIZE}};',
				],
			]
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'nav_icon_size',
			[
				'label'      => esc_html__( 'Icon Size', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range'      => [
					'px' => [ 'min' => 6, 'max' => 50 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .nav-btn i'   => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nav-btn svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
				'separator'  => 'before',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		// Build filters and resources
		$categories = $settings['categories'] ?? [];
		$resources  = [];
		$filters    = [];

		foreach ( $categories as $index => $cat ) {
			if ( empty( $cat['category_name'] ) ) {
				continue;
			}
			$slug = sanitize_title( $cat['category_name'] );
			$filters[ $slug ] = $cat['category_name'];

			$page_data = $cat['selected_pages'] ?? [];
			if ( ! is_array( $page_data ) ) {
				continue;
			}

			foreach ( $page_data as $page ) {
				$page_id = $page['id'] ?? null;
				if ( ! $page_id ) {
					continue;
				}

				$post = get_post( $page_id );
				if ( ! $post ) {
					continue;
				}

				// Check if published in last 14 days
				$publish_time = get_post_time( 'U', false, $post );
				$fourteen_days_ago = time() - ( 14 * 24 * 60 * 60 );
				$is_new = $publish_time > $fourteen_days_ago;

				// Description: use excerpt, fallback to trimmed content
				$desc = get_the_excerpt( $post );
				if ( empty( $desc ) ) {
					$desc = wp_trim_words( $post->post_content, 30, '&hellip;' );
				}

				$resources[] = [
					'id'          => $post->ID,
					'title'       => get_the_title( $post ),
					'description' => $desc,
					'category'    => $slug,
					'isNew'       => $is_new,
					'link'        => get_permalink( $post ),
				];
			}
		}

		$resources_json = htmlspecialchars( wp_json_encode( $resources ), ENT_QUOTES, 'UTF-8' );

		$icon_html = '';
		if ( ! empty( $settings['link_icon']['value'] ) ) {
			ob_start();
			\Elementor\Icons_Manager::render_icon( $settings['link_icon'], [ 'aria-hidden' => 'true' ] );
			$icon_html = ob_get_clean();
		}
		$icon_pos = $settings['link_icon_position'] ?? 'right';

		$nav_prev_icon_html = '';
		if ( ! empty( $settings['nav_prev_icon']['value'] ) ) {
			ob_start();
			\Elementor\Icons_Manager::render_icon( $settings['nav_prev_icon'], [ 'aria-hidden' => 'true' ] );
			$nav_prev_icon_html = ob_get_clean();
		}

		$nav_next_icon_html = '';
		if ( ! empty( $settings['nav_next_icon']['value'] ) ) {
			ob_start();
			\Elementor\Icons_Manager::render_icon( $settings['nav_next_icon'], [ 'aria-hidden' => 'true' ] );
			$nav_next_icon_html = ob_get_clean();
		}
		?>
		<div class="clnc-carousel-wrap" data-resources="<?php echo $resources_json; ?>" data-icon-pos="<?php echo esc_attr( $icon_pos ); ?>">
			<template class="clnc-icon-template"><?php echo $icon_html; ?></template>
			<div class="section-wrapper">

				<?php if ( ! empty( $filters ) ) : ?>
					<div class="filter-bar">
						<span class="filter-label">Filter:</span>
						<button class="filter-btn active" data-filter="all">All</button>
						<?php foreach ( $filters as $slug => $name ) : ?>
							<button class="filter-btn" data-filter="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?></button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="count-nav-row">
					<span class="resource-count"></span>
					<div class="nav-buttons">
						<button class="nav-btn btn-prev" aria-label="Previous">
							<?php if ( ! empty( $nav_prev_icon_html ) ) : ?>
								<?php echo $nav_prev_icon_html; ?>
							<?php else : ?>
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
									<path d="M11.0606 4L3 12L11.0606 20M3 12L22 12" stroke="#B0B5BA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
								</svg>
							<?php endif; ?>
						</button>
						<button class="nav-btn btn-next" aria-label="Next">
							<?php if ( ! empty( $nav_next_icon_html ) ) : ?>
								<?php echo $nav_next_icon_html; ?>
							<?php else : ?>
								<svg xmlns="http://www.w3.org/2000/svg" width="21" height="18" viewBox="0 0 21 18" fill="none">
									<path d="M11.9394 1L20 9L11.9394 17M20 9L1 9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
								</svg>
							<?php endif; ?>
						</button>
					</div>
				</div>

				<div class="swiper">
					<div class="swiper-wrapper"></div>
				</div>

				<div class="nav-row-mobile">
					<div class="nav-buttons">
						<button class="nav-btn btn-prev" aria-label="Previous">
							<?php if ( ! empty( $nav_prev_icon_html ) ) : ?>
								<?php echo $nav_prev_icon_html; ?>
							<?php else : ?>
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
									<path d="M11.0606 4L3 12L11.0606 20M3 12L22 12" stroke="#B0B5BA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
								</svg>
							<?php endif; ?>
						</button>
						<div class="swiper-pagination"></div>
						<button class="nav-btn btn-next" aria-label="Next">
							<?php if ( ! empty( $nav_next_icon_html ) ) : ?>
								<?php echo $nav_next_icon_html; ?>
							<?php else : ?>
								<svg xmlns="http://www.w3.org/2000/svg" width="21" height="18" viewBox="0 0 21 18" fill="none">
									<path d="M11.9394 1L20 9L11.9394 17M20 9L1 9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
								</svg>
							<?php endif; ?>
						</button>
					</div>
				</div>

			</div>
		</div>
		<?php
	}
}
