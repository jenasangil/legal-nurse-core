<?php
/**
 * Elementor Search Box Widget
 *
 * A standalone search field (icon + input) that redirects to a results page
 * (e.g. /blog-search-results/?s=keyword) on submit. Pairs with the LN -
 * Search Results widget.
 *
 * @package LegalNurseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LNC_Search_Box_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'lnc_search_box';
	}

	public function get_title() {
		return esc_html__( 'LN - Search Box', 'legal-nurse-core' );
	}

	public function get_icon() {
		return 'eicon-search';
	}

	public function get_categories() {
		return [ 'legal-nurse' ];
	}

	public function get_keywords() {
		return [ 'search', 'box', 'posts', 'blog', 'form', 'redirect' ];
	}

	public function get_style_depends() {
		return [ 'lnc-search-box' ];
	}

	protected function register_controls() {

		$this->start_controls_section( 'section_content', [ 'label' => esc_html__( 'Search', 'legal-nurse-core' ) ] );

		$this->add_control(
			'placeholder',
			[
				'label'   => esc_html__( 'Placeholder', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Search posts', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'results_url',
			[
				'label'       => esc_html__( 'Results Page URL', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '/blog-search-results/',
				'placeholder' => '/blog-search-results/',
				'description' => esc_html__( 'Where the search submits to.', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'search_param',
			[
				'label'       => esc_html__( 'Query Parameter', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 's',
				'description' => esc_html__( 'URL parameter for the term (e.g. "s" -> ?s=keyword).', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'search_icon',
			[
				'label'   => esc_html__( 'Search Icon', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::ICONS,
				'default' => [ 'value' => 'fas fa-search', 'library' => 'fa-solid' ],
			]
		);

		$this->add_control(
			'icon_position',
			[
				'label'   => esc_html__( 'Icon Position', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'left',
				'options' => [ 'left' => esc_html__( 'Left', 'legal-nurse-core' ), 'right' => esc_html__( 'Right', 'legal-nurse-core' ) ],
			]
		);

		$this->end_controls_section();

		$this->register_style_controls();
	}

	private function register_style_controls() {
		$this->start_controls_section( 'section_style', [ 'label' => esc_html__( 'Style', 'legal-nurse-core' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );

		$this->add_control( 'field_bg', [ 'label' => esc_html__( 'Background', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#ffffff', 'selectors' => [ '{{WRAPPER}} .lnc-searchbox__input' => 'background:{{VALUE}};' ] ] );
		$this->add_control( 'text_color', [ 'label' => esc_html__( 'Text Color', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#2a2926', 'selectors' => [ '{{WRAPPER}} .lnc-searchbox__input' => 'color:{{VALUE}};' ] ] );
		$this->add_control( 'placeholder_color', [ 'label' => esc_html__( 'Placeholder Color', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#9a9a92', 'selectors' => [ '{{WRAPPER}} .lnc-searchbox__input::placeholder' => 'color:{{VALUE}};opacity:1;' ] ] );
		$this->add_control( 'icon_color', [ 'label' => esc_html__( 'Icon Color', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#9a9a92', 'selectors' => [ '{{WRAPPER}} .lnc-searchbox__icon' => 'color:{{VALUE}};' ] ] );

		$this->add_responsive_control( 'icon_size', [ 'label' => esc_html__( 'Icon Size', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => [ 'px' ], 'range' => [ 'px' => [ 'min' => 10, 'max' => 40 ] ], 'default' => [ 'size' => 18, 'unit' => 'px' ], 'selectors' => [ '{{WRAPPER}} .lnc-searchbox__icon svg' => 'width:{{SIZE}}{{UNIT}};height:{{SIZE}}{{UNIT}};', '{{WRAPPER}} .lnc-searchbox__icon i' => 'font-size:{{SIZE}}{{UNIT}};' ] ] );

		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'text_typography', 'selector' => '{{WRAPPER}} .lnc-searchbox__input' ] );

		$this->add_group_control( \Elementor\Group_Control_Border::get_type(), [ 'name' => 'field_border', 'label' => esc_html__( 'Border', 'legal-nurse-core' ), 'selector' => '{{WRAPPER}} .lnc-searchbox__input' ] );

		$this->add_control( 'field_border_color', [ 'label' => esc_html__( 'Border Color', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#e6e2d8', 'selectors' => [ '{{WRAPPER}} .lnc-searchbox__input' => 'border-color:{{VALUE}};' ] ] );

		$this->add_control( 'field_radius', [ 'label' => esc_html__( 'Border Radius', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => [ 'px' ], 'range' => [ 'px' => [ 'min' => 0, 'max' => 50 ] ], 'default' => [ 'size' => 10, 'unit' => 'px' ], 'selectors' => [ '{{WRAPPER}} .lnc-searchbox__input' => 'border-radius:{{SIZE}}{{UNIT}};' ] ] );

		$this->add_responsive_control( 'field_padding', [ 'label' => esc_html__( 'Padding', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'size_units' => [ 'px', 'em' ], 'default' => [ 'top' => 14, 'right' => 18, 'bottom' => 14, 'left' => 18, 'unit' => 'px' ], 'selectors' => [ '{{WRAPPER}} .lnc-searchbox--iconleft .lnc-searchbox__input' => 'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} 46px;', '{{WRAPPER}} .lnc-searchbox--iconright .lnc-searchbox__input' => 'padding:{{TOP}}{{UNIT}} 46px {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ] );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$url    = $settings['results_url'] ? $settings['results_url'] : '/blog-search-results/';
		$param  = $settings['search_param'] ? sanitize_key( $settings['search_param'] ) : 's';
		$ph     = $settings['placeholder'] ? $settings['placeholder'] : esc_html__( 'Search posts', 'legal-nurse-core' );
		$rightp = 'right' === ( $settings['icon_position'] ?? 'left' );

		ob_start();
		$icon = $settings['search_icon'] ?? [];
		if ( ! empty( $icon['value'] ) ) {
			\Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
		} else {
			echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>';
		}
		$icon_html = '<button type="submit" class="lnc-searchbox__icon" aria-label="' . esc_attr__( 'Search', 'legal-nurse-core' ) . '">' . ob_get_clean() . '</button>';

		$modifier = $rightp ? 'lnc-searchbox--iconright' : 'lnc-searchbox--iconleft';

		echo '<form class="lnc-searchbox ' . esc_attr( $modifier ) . '" action="' . esc_url( $url ) . '" method="get" role="search">';
		printf(
			'<input type="search" class="lnc-searchbox__input" name="%s" placeholder="%s" aria-label="%s" autocomplete="off">',
			esc_attr( $param ),
			esc_attr( $ph ),
			esc_attr( $ph )
		);
		echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</form>';
	}
}
