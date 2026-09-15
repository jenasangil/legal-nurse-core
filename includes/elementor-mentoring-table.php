<?php
/**
 * Elementor Mentoring Table Widget
 *
 * A small two-column plan table (plan name + description). The row whose
 * "Highlight Slug" matches the current page slug gets the ".green" highlight,
 * so the same widget can be dropped on each plan page (basic-system,
 * executive-system, vip-system, vip-pro) and auto-highlights the right row.
 *
 * @package LegalNurseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LNC_Mentoring_Table_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'lnc_mentoring_table';
	}

	public function get_title() {
		return esc_html__( 'LN - Mentoring Table', 'legal-nurse-core' );
	}

	public function get_icon() {
		return 'eicon-table';
	}

	public function get_categories() {
		return [ 'legal-nurse' ];
	}

	public function get_keywords() {
		return [ 'mentoring', 'table', 'plans', 'clnc', 'compare', 'tiers' ];
	}

	public function get_style_depends() {
		return [ 'lnc-mentoring-table' ];
	}

	protected function register_controls() {

		$this->start_controls_section( 'section_content', [ 'label' => esc_html__( 'Rows', 'legal-nurse-core' ) ] );

		$this->add_control(
			'table_label',
			[
				'label'   => esc_html__( 'Accessible Label', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'CLNC mentoring plans', 'legal-nurse-core' ),
			]
		);

		$row = new \Elementor\Repeater();
		$row->add_control( 'plan_name', [ 'label' => esc_html__( 'Plan Name', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => esc_html__( 'Plan', 'legal-nurse-core' ) ] );
		$row->add_control( 'plan_desc', [ 'label' => esc_html__( 'Description', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'rows' => 2 ] );
		$row->add_control(
			'match_slug',
			[
				'label'       => esc_html__( 'Highlight Slug', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'description' => esc_html__( 'Highlight this row when the page slug matches (e.g. vip-pro). Comma-separate for multiple.', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'rows',
			[
				'label'       => esc_html__( 'Rows', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $row->get_controls(),
				'title_field' => '{{{ plan_name }}}',
				'default'     => [
					[ 'plan_name' => 'VIP Pro', 'plan_desc' => 'Unlimited Priority CLNC Mentoring', 'match_slug' => 'vip-pro' ],
					[ 'plan_name' => 'VIP', 'plan_desc' => 'Unlimited CLNC Mentoring', 'match_slug' => 'vip-system' ],
					[ 'plan_name' => 'Executive', 'plan_desc' => 'FREE CLNC Mentoring twice a month', 'match_slug' => 'executive-system' ],
					[ 'plan_name' => 'Basic', 'plan_desc' => 'FREE CLNC Mentoring once a month', 'match_slug' => 'basic-system' ],
				],
			]
		);

		$this->add_control(
			'active_slug',
			[
				'label'       => esc_html__( 'Force Active Slug', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'separator'   => 'before',
				'description' => esc_html__( 'Leave empty to auto-detect from the current page slug. Set a slug here to force which row highlights.', 'legal-nurse-core' ),
			]
		);

		$this->end_controls_section();

		$this->register_style_controls();
	}

	private function register_style_controls() {
		$this->start_controls_section( 'section_style', [ 'label' => esc_html__( 'Table', 'legal-nurse-core' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[ 'name' => 'name_typography', 'label' => esc_html__( 'Plan Name', 'legal-nurse-core' ), 'selector' => '{{WRAPPER}} .mentoring-table th' ]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[ 'name' => 'desc_typography', 'label' => esc_html__( 'Description', 'legal-nurse-core' ), 'selector' => '{{WRAPPER}} .mentoring-table td' ]
		);

		$this->add_responsive_control(
			'cell_padding',
			[
				'label'      => esc_html__( 'Cell Padding', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'default'    => [ 'top' => 16, 'right' => 20, 'bottom' => 16, 'left' => 20, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .mentoring-table th, {{WRAPPER}} .mentoring-table td' => 'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			]
		);

		$this->add_control( 'border_color', [
			'label'     => esc_html__( 'Border Color', 'legal-nurse-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#e6e2d8',
			'selectors' => [
				'{{WRAPPER}} .mentoring-table' => 'border-color:{{VALUE}};',
				'{{WRAPPER}} .mentoring-table tr + tr' => 'border-top-color:{{VALUE}};',
				'{{WRAPPER}} .mentoring-table td' => 'border-left-color:{{VALUE}};',
			],
		] );

		$this->add_control(
			'radius',
			[
				'label'      => esc_html__( 'Border Radius', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
				'default'    => [ 'size' => 12, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .mentoring-table' => 'border-radius:{{SIZE}}{{UNIT}};' ],
			]
		);

		// Normal rows.
		$this->add_control( 'row_heading', [ 'label' => esc_html__( 'Rows', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before' ] );
		$this->add_control( 'row_bg', [ 'label' => esc_html__( 'Background', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#ffffff', 'selectors' => [ '{{WRAPPER}} .mentoring-table tr' => 'background:{{VALUE}};' ] ] );
		$this->add_control( 'row_color', [ 'label' => esc_html__( 'Text Color', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#2a2926', 'selectors' => [ '{{WRAPPER}} .mentoring-table tr' => 'color:{{VALUE}};' ] ] );

		// Highlighted (.green) row.
		$this->add_control( 'green_heading', [ 'label' => esc_html__( 'Highlighted Row', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before' ] );
		$this->add_control( 'green_bg', [ 'label' => esc_html__( 'Background', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#25797c', 'selectors' => [ '{{WRAPPER}} .mentoring-table tr.green' => 'background:{{VALUE}};' ] ] );
		$this->add_control( 'green_color', [ 'label' => esc_html__( 'Text Color', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#ffffff', 'selectors' => [ '{{WRAPPER}} .mentoring-table tr.green' => 'color:{{VALUE}};' ] ] );

		$this->end_controls_section();
	}

	/** The current page slug (last URL segment / post_name). */
	private function current_slug() {
		$id = get_queried_object_id();
		if ( $id ) {
			$name = get_post_field( 'post_name', $id );
			if ( $name ) {
				return sanitize_title( $name );
			}
		}
		// Fallback: last non-empty segment of the request path.
		$path = trim( wp_parse_url( ( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '' ), PHP_URL_PATH ) ?? '', '/' );
		$segs = array_filter( explode( '/', $path ) );
		return $segs ? sanitize_title( end( $segs ) ) : '';
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$rows     = $settings['rows'] ?? [];

		if ( empty( $rows ) ) {
			return;
		}

		$active = $settings['active_slug'] ? sanitize_title( $settings['active_slug'] ) : $this->current_slug();

		printf( '<table class="mentoring-table" aria-label="%s"><tbody>', esc_attr( $settings['table_label'] ?? '' ) );

		foreach ( $rows as $r ) {
			$slugs    = array_filter( array_map( 'sanitize_title', array_map( 'trim', explode( ',', (string) ( $r['match_slug'] ?? '' ) ) ) ) );
			$is_green = $active && in_array( $active, $slugs, true );

			printf(
				'<tr class="%s"><th scope="row">%s</th><td>%s</td></tr>',
				$is_green ? 'green' : '',
				esc_html( $r['plan_name'] ?? '' ),
				esc_html( $r['plan_desc'] ?? '' )
			);
		}

		echo '</tbody></table>';
	}
}
