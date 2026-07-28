<?php
/**
 * Elementor Comparison Table Widget
 *
 * A plan comparison table: configurable plan columns and grouped feature rows.
 * Each feature row defines its per-plan cells with a pipe-separated value, where
 * each segment maps to a plan column in order:
 *   - empty segment                     -> empty cell
 *   - check | yes | true | 1 | x | ✓   -> checkmark
 *   - anything else                     -> that text (e.g. "Once a month")
 *
 * Example (3 plans): "check|check|check"  or  "|check|check"  or
 *                    "Once a month|Twice a month|Priority, unlimited"
 *
 * @package LegalNurseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LNC_Compare_Table_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'lnc_compare_table';
	}

	public function get_title() {
		return esc_html__( 'LN - Comparison Table', 'legal-nurse-core' );
	}

	public function get_icon() {
		return 'eicon-table';
	}

	public function get_categories() {
		return [ 'legal-nurse' ];
	}

	public function get_keywords() {
		return [ 'comparison', 'table', 'pricing', 'plans', 'features', 'compare' ];
	}

	public function get_style_depends() {
		return [ 'lnc-compare-table' ];
	}

	/** Values that render as a checkmark. */
	private static function is_check( $value ) {
		return in_array( strtolower( trim( $value ) ), [ 'check', 'yes', 'true', '1', 'x', '✓' ], true );
	}

	protected function register_controls() {

		// ---------------------------------------------------------------
		// CONTENT: Plans
		// ---------------------------------------------------------------
		$this->start_controls_section( 'section_plans', [ 'label' => esc_html__( 'Plans', 'legal-nurse-core' ) ] );

		$this->add_control(
			'first_col_label',
			[
				'label'   => esc_html__( 'First Column Label', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Included', 'legal-nurse-core' ),
			]
		);

		$plan = new \Elementor\Repeater();
		$plan->add_control( 'name', [ 'label' => esc_html__( 'Plan Name', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => esc_html__( 'Plan', 'legal-nurse-core' ) ] );
		$plan->add_control( 'header_bg', [ 'label' => esc_html__( 'Header Background', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#F4EFE1' ] );
		$plan->add_control( 'header_color', [ 'label' => esc_html__( 'Header Text', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#2a2926' ] );
		$plan->add_control( 'column_bg', [ 'label' => esc_html__( 'Column Tint', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#FBF8F1' ] );
		$plan->add_control( 'check_color', [ 'label' => esc_html__( 'Check Color', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#25797c' ] );
		$plan->add_control( 'cell_text_color', [ 'label' => esc_html__( 'Cell Text Color', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#2a2926' ] );

		$this->add_control(
			'plans',
			[
				'label'       => esc_html__( 'Plans', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $plan->get_controls(),
				'title_field' => '{{{ name }}}',
				'default'     => [
					[ 'name' => 'Basic', 'header_bg' => '#F4EFE1', 'header_color' => '#2a2926', 'column_bg' => '#FBF8F1', 'check_color' => '#25797c', 'cell_text_color' => '#2a2926' ],
					[ 'name' => 'Executive', 'header_bg' => '#A9E4D6', 'header_color' => '#216164', 'column_bg' => '#E3F5F0', 'check_color' => '#25797c', 'cell_text_color' => '#2a2926' ],
					[ 'name' => 'VIP', 'header_bg' => '#6E64C6', 'header_color' => '#FFFFFF', 'column_bg' => '#CBC7E9', 'check_color' => '#25797c', 'cell_text_color' => '#2a2926' ],
				],
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------
		// CONTENT: Rows
		// ---------------------------------------------------------------
		$this->start_controls_section( 'section_rows', [ 'label' => esc_html__( 'Rows', 'legal-nurse-core' ) ] );

		$row = new \Elementor\Repeater();
		$row->add_control(
			'row_type',
			[
				'label'   => esc_html__( 'Row Type', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'feature',
				'options' => [
					'feature' => esc_html__( 'Feature row', 'legal-nurse-core' ),
					'band'    => esc_html__( 'Category band', 'legal-nurse-core' ),
				],
			]
		);
		$row->add_control(
			'label',
			[
				'label'       => esc_html__( 'Label', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => esc_html__( 'Feature name', 'legal-nurse-core' ),
				'description' => esc_html__( 'Category title (band) or feature name. Basic HTML like <em> is allowed.', 'legal-nurse-core' ),
			]
		);
		$row->add_control(
			'cells',
			[
				'label'       => esc_html__( 'Cells', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'check|check|check',
				'description' => esc_html__( 'One value per plan, separated by "|". Use check for a tick, leave empty for blank, or type text (e.g. Once a month).', 'legal-nurse-core' ),
				'condition'   => [ 'row_type' => 'feature' ],
			]
		);

		$this->add_control(
			'rows',
			[
				'label'       => esc_html__( 'Rows', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $row->get_controls(),
				'title_field' => '{{{ label }}}',
				'default'     => [
					[ 'row_type' => 'band', 'label' => 'Certification Resources' ],
					[ 'row_type' => 'feature', 'label' => 'CLNC® Certification Program (Online Video)', 'cells' => 'check|check|check' ],
					[ 'row_type' => 'feature', 'label' => 'CLNC® Certification Exam', 'cells' => 'check|check|check' ],
					[ 'row_type' => 'band', 'label' => 'Mentoring' ],
					[ 'row_type' => 'feature', 'label' => 'Free Mentoring from CLNC® Mentors', 'cells' => 'Once a month|Twice a month|Priority, unlimited' ],
					[ 'row_type' => 'band', 'label' => 'Next-Level Resources' ],
					[ 'row_type' => 'feature', 'label' => '79 Medical-Related Case Reports', 'cells' => '||check' ],
				],
			]
		);

		$this->add_control(
			'check_icon',
			[
				'label'   => esc_html__( 'Check Icon', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::ICONS,
				'default' => [ 'value' => 'fas fa-check', 'library' => 'fa-solid' ],
			]
		);

		$this->add_control(
			'sticky_header',
			[
				'label'        => esc_html__( 'Sticky Header', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'separator'    => 'before',
				'description'  => esc_html__( 'Keep the plan header row visible while scrolling the page.', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'sticky_offset',
			[
				'label'      => esc_html__( 'Sticky Top Offset', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 200 ] ],
				'default'    => [ 'size' => 0, 'unit' => 'px' ],
				'description' => esc_html__( 'Space from the top (e.g. for a fixed header/nav).', 'legal-nurse-core' ),
				'condition'  => [ 'sticky_header' => 'yes' ],
			]
		);

		$this->end_controls_section();

		$this->register_style_controls();
	}

	private function register_style_controls() {
		$this->start_controls_section( 'section_style', [ 'label' => esc_html__( 'Table', 'legal-nurse-core' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );

		$this->add_control( 'band_bg', [
			'label'     => esc_html__( 'Category Band Background', 'legal-nurse-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#25797c',
			'selectors' => [ '{{WRAPPER}} .lnc-ct__band' => 'background:{{VALUE}};' ],
		] );
		$this->add_control( 'band_color', [
			'label'     => esc_html__( 'Category Band Text', 'legal-nurse-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#FFFFFF',
			'selectors' => [ '{{WRAPPER}} .lnc-ct__band' => 'color:{{VALUE}};' ],
		] );
		$this->add_control( 'border_color', [
			'label'     => esc_html__( 'Row Border Color', 'legal-nurse-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#e6e2d8',
			'selectors' => [
				'{{WRAPPER}} .lnc-ct__row + .lnc-ct__row' => 'border-top-color:{{VALUE}};',
				'{{WRAPPER}} .lnc-ct'                     => 'border-color:{{VALUE}};',
				'{{WRAPPER}} .lnc-ct__plan'               => 'border-left-color:{{VALUE}};',
				'{{WRAPPER}} .lnc-ct__cell'               => 'border-left-color:{{VALUE}};',
			],
		] );
		$this->add_control( 'radius', [
			'label'      => esc_html__( 'Border Radius', 'legal-nurse-core' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
			'default'    => [ 'size' => 14, 'unit' => 'px' ],
			'selectors'  => [ '{{WRAPPER}} .lnc-ct' => 'border-radius:{{SIZE}}{{UNIT}};' ],
		] );
		$this->add_responsive_control( 'cell_padding', [
			'label'      => esc_html__( 'Cell Padding', 'legal-nurse-core' ),
			'type'       => \Elementor\Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em' ],
			'default'    => [ 'top' => 14, 'right' => 18, 'bottom' => 14, 'left' => 18, 'unit' => 'px' ],
			'selectors'  => [ '{{WRAPPER}} .lnc-ct__feat, {{WRAPPER}} .lnc-ct__cell' => 'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );
		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
			'name' => 'feature_typography', 'label' => esc_html__( 'Feature Text', 'legal-nurse-core' ), 'selector' => '{{WRAPPER}} .lnc-ct__feat',
		] );
		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
			'name' => 'header_typography', 'label' => esc_html__( 'Plan Header', 'legal-nurse-core' ), 'selector' => '{{WRAPPER}} .lnc-ct__plan',
		] );
		$this->add_control( 'icon_size', [
			'label'      => esc_html__( 'Check Icon Size', 'legal-nurse-core' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 8, 'max' => 32 ] ],
			'default'    => [ 'size' => 16, 'unit' => 'px' ],
			'selectors'  => [
				'{{WRAPPER}} .lnc-ct__check svg' => 'width:{{SIZE}}{{UNIT}};height:{{SIZE}}{{UNIT}};',
				'{{WRAPPER}} .lnc-ct__check i'   => 'font-size:{{SIZE}}{{UNIT}};',
			],
		] );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$plans    = $settings['plans'] ?? [];
		$rows     = $settings['rows'] ?? [];

		if ( empty( $plans ) ) {
			return;
		}

		$plan_count = count( $plans );
		$grid_cols  = '2.4fr repeat(' . (int) $plan_count . ', 1fr)';

		// Prebuild the check icon HTML.
		$icon = $settings['check_icon'] ?? [];
		ob_start();
		if ( ! empty( $icon['value'] ) ) {
			\Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
		} else {
			echo '&#10003;';
		}
		$check_html = ob_get_clean();

		$allowed = [ 'em' => [], 'strong' => [], 'span' => [ 'class' => [] ], 'sup' => [], 'sub' => [], 'br' => [] ];

		$sticky      = 'yes' === ( $settings['sticky_header'] ?? '' );
		$sticky_top  = isset( $settings['sticky_offset']['size'] ) ? (int) $settings['sticky_offset']['size'] : 0;
		$root_class  = 'lnc-ct' . ( $sticky ? ' lnc-ct--sticky' : '' );
		$root_style  = '--lnc-ct-cols:' . $grid_cols . ';--lnc-ct-sticky-top:' . $sticky_top . 'px;';

		echo '<div class="lnc-ct-scroll">';
		echo '<div class="' . esc_attr( $root_class ) . '" style="' . esc_attr( $root_style ) . '">';

		// Header row.
		echo '<div class="lnc-ct__row lnc-ct__head">';
		echo '<div class="lnc-ct__feat lnc-ct__firstlabel">' . esc_html( $settings['first_col_label'] ) . '</div>';
		foreach ( $plans as $p ) {
			printf(
				'<div class="lnc-ct__plan" style="background:%s;color:%s">%s</div>',
				esc_attr( $p['header_bg'] ?? '' ),
				esc_attr( $p['header_color'] ?? '' ),
				esc_html( $p['name'] ?? '' )
			);
		}
		echo '</div>';

		// Data rows.
		foreach ( $rows as $r ) {
			if ( 'band' === ( $r['row_type'] ?? 'feature' ) ) {
				echo '<div class="lnc-ct__row"><div class="lnc-ct__band">' . wp_kses( $r['label'] ?? '', $allowed ) . '</div></div>';
				continue;
			}

			$cells = explode( '|', (string) ( $r['cells'] ?? '' ) );

			echo '<div class="lnc-ct__row">';
			echo '<div class="lnc-ct__feat">' . wp_kses( $r['label'] ?? '', $allowed ) . '</div>';

			foreach ( $plans as $i => $p ) {
				$value = isset( $cells[ $i ] ) ? trim( $cells[ $i ] ) : '';
				$tint  = $p['column_bg'] ?? '';

				echo '<div class="lnc-ct__cell" style="background:' . esc_attr( $tint ) . '">';
				if ( '' === $value ) {
					echo '';
				} elseif ( self::is_check( $value ) ) {
					echo '<span class="lnc-ct__check" style="color:' . esc_attr( $p['check_color'] ?? '' ) . '">' . $check_html . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				} else {
					echo '<span class="lnc-ct__value" style="color:' . esc_attr( $p['cell_text_color'] ?? '' ) . '">' . esc_html( $value ) . '</span>';
				}
				echo '</div>';
			}

			echo '</div>';
		}

		echo '</div>'; // .lnc-ct
		echo '</div>'; // .lnc-ct-scroll
	}
}
