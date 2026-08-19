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

	public function get_script_depends() {
		return [ 'lnc-compare-table' ];
	}

	/** Values that render as a checkmark. */
	private static function is_check( $value ) {
		return in_array( strtolower( trim( $value ) ), [ 'check', 'yes', 'true', '1', 'x', '✓' ], true );
	}

	/** Values that render as a lock icon. */
	private static function is_lock( $value ) {
		return in_array( strtolower( trim( $value ) ), [ 'lock', 'locked', 'padlock' ], true );
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
			]
		);

		$plan = new \Elementor\Repeater();
		$plan->add_control( 'name', [ 'label' => esc_html__( 'Plan Name', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::TEXT ] );
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
				'description' => esc_html__( 'Category title (band) or feature name. Basic HTML like <em> is allowed.', 'legal-nurse-core' ),
			]
		);
		$row->add_control(
			'cells',
			[
				'label'       => esc_html__( 'Cells', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'description' => esc_html__( 'One value per plan, separated by "|". Use check for a tick, lock for a lock icon, leave empty for blank, or type text (e.g. Once a month).', 'legal-nurse-core' ),
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
			]
		);

		$this->add_control(
			'check_icon',
			[
				'label'       => esc_html__( 'Check Icon', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::ICONS,
				'description' => esc_html__( 'Leave empty to use the default check. Or choose an icon / upload an SVG.', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'lock_icon',
			[
				'label'       => esc_html__( 'Lock Icon', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::ICONS,
				'description' => esc_html__( 'Leave empty to use the default lock. Or choose an icon / upload an SVG. Type "lock" in a cell to use it.', 'legal-nurse-core' ),
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
				'label'       => esc_html__( 'Sticky Top Offset (Desktop)', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::SLIDER,
				'size_units'  => [ 'px' ],
				'range'       => [ 'px' => [ 'min' => 0, 'max' => 300 ] ],
				'default'     => [ 'size' => 120, 'unit' => 'px' ],
				'description' => esc_html__( 'Distance from the top on desktop (e.g. your fixed header height).', 'legal-nurse-core' ),
				'condition'   => [ 'sticky_header' => 'yes' ],
			]
		);

		$this->add_control(
			'sticky_offset_mobile',
			[
				'label'       => esc_html__( 'Sticky Top Offset (Mobile)', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::SLIDER,
				'size_units'  => [ 'px' ],
				'range'       => [ 'px' => [ 'min' => 0, 'max' => 300 ] ],
				'default'     => [ 'size' => 80, 'unit' => 'px' ],
				'description' => esc_html__( 'Distance from the top on mobile (< 768px).', 'legal-nurse-core' ),
				'condition'   => [ 'sticky_header' => 'yes' ],
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
		$this->add_responsive_control( 'plan_padding', [
			'label'      => esc_html__( 'Plan Header Padding', 'legal-nurse-core' ),
			'type'       => \Elementor\Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em' ],
			'selectors'  => [ '{{WRAPPER}} .lnc-ct__plan' => 'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );
		$this->add_responsive_control( 'plan_align', [
			'label'     => esc_html__( 'Plan Header Alignment', 'legal-nurse-core' ),
			'type'      => \Elementor\Controls_Manager::CHOOSE,
			'options'   => [
				'flex-start' => [ 'title' => esc_html__( 'Left', 'legal-nurse-core' ), 'icon' => 'eicon-text-align-left' ],
				'center'     => [ 'title' => esc_html__( 'Center', 'legal-nurse-core' ), 'icon' => 'eicon-text-align-center' ],
				'flex-end'   => [ 'title' => esc_html__( 'Right', 'legal-nurse-core' ), 'icon' => 'eicon-text-align-right' ],
			],
			'selectors' => [ '{{WRAPPER}} .lnc-ct__plan' => 'justify-content:{{VALUE}};text-align:{{VALUE}};' ],
		] );
		$this->add_control( 'plan_border_color', [
			'label'     => esc_html__( 'Plan Header Border', 'legal-nurse-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .lnc-ct__plan + .lnc-ct__plan' => 'border-left-color:{{VALUE}};' ],
		] );
		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
			'name' => 'value_typography', 'label' => esc_html__( 'Cell Value Text', 'legal-nurse-core' ), 'selector' => '{{WRAPPER}} .lnc-ct__value',
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
		wp_enqueue_style( 'lnc-compare-table' );

		$settings = $this->get_settings_for_display();
		$plans    = $settings['plans'] ?? [];
		$rows     = $settings['rows'] ?? [];

		if ( empty( $plans ) ) {
			return;
		}

		$plan_count = count( $plans );
		$grid_cols  = '2.4fr repeat(' . (int) $plan_count . ', 1fr)';

		// Prebuild the check icon HTML. Default to an inline SVG so it renders
		// even when Font Awesome is not enqueued on the page.
		$icon = $settings['check_icon'] ?? [];
		if ( ! empty( $icon['value'] ) ) {
			ob_start();
			\Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
			$check_html = ob_get_clean();
		} else {
			$check_html = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 10.5l4 4 8-9"/></svg>';
		}

		// Lock icon: use the chosen icon, else a default inline SVG.
		$lock = $settings['lock_icon'] ?? [];
		if ( ! empty( $lock['value'] ) ) {
			ob_start();
			\Elementor\Icons_Manager::render_icon( $lock, [ 'aria-hidden' => 'true' ] );
			$lock_html = ob_get_clean();
		} else {
			// Uses currentColor so it follows the cell's check color.
			$lock_html = '<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M5.494 18c-.413 0-.765-.147-1.057-.441A1.44 1.44 0 0 1 4 16.5v-8c0-.413.147-.766.441-1.059A1.44 1.44 0 0 1 5.5 7H6V5c0-1.107.39-2.05 1.171-2.83A3.86 3.86 0 0 1 10.005 1c1.108 0 2.05.39 2.828 1.17C13.611 2.95 14 3.893 14 5v2h.5c.413 0 .766.147 1.059.441.294.293.441.646.441 1.059v8c0 .413-.147.766-.441 1.059A1.44 1.44 0 0 1 14.499 18H5.494ZM5.5 16.5h9v-8h-9v8Zm2-9.5h5V5c0-.694-.243-1.285-.729-1.771A2.41 2.41 0 0 0 10 2.5a2.41 2.41 0 0 0-1.771.729A2.41 2.41 0 0 0 7.5 5v2Z"/></svg>';
		}

		$allowed = [ 'em' => [], 'strong' => [], 'span' => [ 'class' => [] ], 'sup' => [], 'sub' => [], 'br' => [] ];

		$sticky        = 'yes' === ( $settings['sticky_header'] ?? '' );
		$sticky_top    = isset( $settings['sticky_offset']['size'] ) ? (int) $settings['sticky_offset']['size'] : 120;
		$sticky_top_mb = isset( $settings['sticky_offset_mobile']['size'] ) ? (int) $settings['sticky_offset_mobile']['size'] : 80;
		$root_class    = 'lnc-ct' . ( $sticky ? ' lnc-ct--sticky' : '' );
		$root_style    = '--lnc-ct-cols:' . $grid_cols . ';';

		$sticky_attrs = $sticky
			? ' data-sticky-top="' . esc_attr( $sticky_top ) . '" data-sticky-top-mobile="' . esc_attr( $sticky_top_mb ) . '"'
			: '';

		echo '<div class="lnc-ct-scroll">';
		echo '<div class="' . esc_attr( $root_class ) . '" style="' . esc_attr( $root_style ) . '"' . $sticky_attrs . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

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
				} elseif ( self::is_lock( $value ) ) {
					echo '<span class="lnc-ct__check lnc-ct__lock" style="color:' . esc_attr( $p['check_color'] ?? '' ) . '">' . $lock_html . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
