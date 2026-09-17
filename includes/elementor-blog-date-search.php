<?php
/**
 * Elementor Blog Date Search Widget
 *
 * Filters a Loop Grid by a start/end publish date range, using the Loop
 * Filter AJAX handler. Point it at a Loop Grid + Loop Item template like the
 * Category List / Loop Filter widgets.
 *
 * @package LegalNurseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LNC_Blog_Date_Search_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'lnc_blog_date_search';
	}

	public function get_title() {
		return esc_html__( 'LN - Blog Date Search', 'legal-nurse-core' );
	}

	public function get_icon() {
		return 'eicon-calendar';
	}

	public function get_categories() {
		return [ 'legal-nurse' ];
	}

	public function get_keywords() {
		return [ 'blog', 'date', 'search', 'filter', 'range', 'loop', 'posts' ];
	}

	public function get_style_depends() {
		return [ 'lnc-loop-filter', 'lnc-blog-date-search' ];
	}

	public function get_script_depends() {
		return [ 'lnc-loop-filter', 'lnc-blog-date-search' ];
	}

	/** Loop Item templates for the selector (editor-only query). */
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

		$this->start_controls_section( 'section_content', [ 'label' => esc_html__( 'Filter Settings', 'legal-nurse-core' ) ] );

		$this->add_control( 'show_heading', [ 'label' => esc_html__( 'Show Heading', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ] );
		$this->add_control( 'heading_text', [ 'label' => esc_html__( 'Heading', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => esc_html__( 'Search Blog By Date', 'legal-nurse-core' ), 'condition' => [ 'show_heading' => 'yes' ] ] );

		$this->add_control( 'start_label', [ 'label' => esc_html__( 'Start Date Label', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => esc_html__( 'Start Date:', 'legal-nurse-core' ) ] );
		$this->add_control( 'end_label', [ 'label' => esc_html__( 'End Date Label', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => esc_html__( 'End Date:', 'legal-nurse-core' ) ] );
		$this->add_control( 'button_label', [ 'label' => esc_html__( 'Button Label', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => esc_html__( 'Search', 'legal-nurse-core' ) ] );

		$this->add_control(
			'calendar_icon',
			[
				'label'   => esc_html__( 'Calendar Icon', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::ICONS,
				'default' => [ 'value' => 'far fa-calendar', 'library' => 'fa-regular' ],
			]
		);

		$this->add_control(
			'target_selector',
			[
				'label'       => esc_html__( 'Target Loop Grid Selector (optional)', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => '.blog-lists',
				'separator'   => 'before',
				'description' => esc_html__( 'Leave empty to auto-detect the nearest Loop Grid. Set a CSS ID/Class to target a specific grid.', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'loop_template_id',
			[
				'label'       => esc_html__( 'Loop Item Template', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'options'     => $this->get_loop_template_options(),
				'default'     => 0,
				'description' => esc_html__( 'Must match the Loop template used by the target Loop Grid.', 'legal-nurse-core' ),
			]
		);

		$this->add_control( 'post_type', [ 'label' => esc_html__( 'Post Type', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'post' ] );
		$this->add_control( 'taxonomy', [ 'label' => esc_html__( 'Taxonomy', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'category' ] );
		$this->add_control( 'posts_per_page', [ 'label' => esc_html__( 'Posts Per Page', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 12, 'min' => 1, 'max' => 48 ] );

		$this->end_controls_section();

		$this->register_style_controls();
	}

	private function register_style_controls() {
		$this->start_controls_section( 'section_style', [ 'label' => esc_html__( 'Style', 'legal-nurse-core' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );

		$this->add_control( 'heading_color', [ 'label' => esc_html__( 'Heading Color', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .lnc-datesearch__heading' => 'color:{{VALUE}};' ], 'condition' => [ 'show_heading' => 'yes' ] ] );
		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'heading_typography', 'label' => esc_html__( 'Heading', 'legal-nurse-core' ), 'selector' => '{{WRAPPER}} .lnc-datesearch__heading', 'condition' => [ 'show_heading' => 'yes' ] ] );

		$this->add_control( 'label_color', [ 'label' => esc_html__( 'Label Color', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .lnc-datesearch__label' => 'color:{{VALUE}};' ], 'separator' => 'before' ] );
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'field_border_group',
				'label'    => esc_html__( 'Field Border', 'legal-nurse-core' ),
				'selector' => '{{WRAPPER}} .lnc-datesearch__field',
			]
		);
		$this->add_control( 'field_border', [ 'label' => esc_html__( 'Field Border Color', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#e6e2d8', 'selectors' => [ '{{WRAPPER}} .lnc-datesearch__field' => 'border-color:{{VALUE}};' ] ] );
		$this->add_responsive_control( 'field_padding', [ 'label' => esc_html__( 'Field Padding', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'size_units' => [ 'px', 'em' ], 'selectors' => [ '{{WRAPPER}} .lnc-datesearch__input' => 'padding:{{TOP}}{{UNIT}} 44px {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ] );
		$this->add_control( 'field_radius', [ 'label' => esc_html__( 'Field Radius', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => [ 'px' ], 'range' => [ 'px' => [ 'min' => 0, 'max' => 40 ] ], 'default' => [ 'size' => 10, 'unit' => 'px' ], 'selectors' => [ '{{WRAPPER}} .lnc-datesearch__field' => 'border-radius:{{SIZE}}{{UNIT}};' ] ] );

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'input_border_group',
				'label'    => esc_html__( 'Input Border', 'legal-nurse-core' ),
				'selector' => '{{WRAPPER}} .lnc-datesearch__field input',
			]
		);
		$this->add_control( 'input_radius', [ 'label' => esc_html__( 'Input Radius', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => [ 'px' ], 'range' => [ 'px' => [ 'min' => 0, 'max' => 40 ] ], 'selectors' => [ '{{WRAPPER}} .lnc-datesearch__field input' => 'border-radius:{{SIZE}}{{UNIT}};' ] ] );
		$this->add_control( 'icon_color', [ 'label' => esc_html__( 'Calendar Icon Color', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#8a8a8a', 'selectors' => [ '{{WRAPPER}} .lnc-datesearch__icon' => 'color:{{VALUE}};' ] ] );

		$this->add_control( 'btn_heading', [ 'label' => esc_html__( 'Button', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before' ] );
		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'btn_typography', 'selector' => '{{WRAPPER}} .lnc-datesearch__submit' ] );
		$this->add_control( 'btn_color', [ 'label' => esc_html__( 'Text', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#ffffff', 'selectors' => [ '{{WRAPPER}} .lnc-datesearch__submit' => 'color:{{VALUE}};' ] ] );
		$this->add_control( 'btn_bg', [ 'label' => esc_html__( 'Background', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#1f6f72', 'selectors' => [ '{{WRAPPER}} .lnc-datesearch__submit' => 'background:{{VALUE}};' ] ] );
		$this->add_control( 'btn_radius', [ 'label' => esc_html__( 'Border Radius', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => [ 'px' ], 'range' => [ 'px' => [ 'min' => 0, 'max' => 50 ] ], 'default' => [ 'size' => 999, 'unit' => 'px' ], 'selectors' => [ '{{WRAPPER}} .lnc-datesearch__submit' => 'border-radius:{{SIZE}}{{UNIT}};' ] ] );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$config = [
			'target'    => trim( (string) ( $settings['target_selector'] ?? '' ) ),
			'template'  => (int) ( $settings['loop_template_id'] ?? 0 ),
			'ppp'       => (int) ( $settings['posts_per_page'] ?? 12 ),
			'post_type' => $settings['post_type'] ? $settings['post_type'] : 'post',
			'taxonomy'  => $settings['taxonomy'] ? $settings['taxonomy'] : 'category',
			'views_key' => 'post_views_count',
			'allowed'   => [],
			'nonce'     => wp_create_nonce( 'lnc_loop_filter' ),
		];

		ob_start();
		$icon = $settings['calendar_icon'] ?? [];
		if ( ! empty( $icon['value'] ) ) {
			\Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
		} else {
			echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';
		}
		$icon_html = ob_get_clean();

		if ( ! $config['template'] && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			echo '<div class="elementor-alert elementor-alert-warning">'
				. esc_html__( 'Blog Date Search: choose the Loop Item Template so filtered results render.', 'legal-nurse-core' )
				. '</div>';
		}
		?>
		<div class="lnc-loop-filter lnc-datesearch" data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">
			<?php if ( 'yes' === ( $settings['show_heading'] ?? 'yes' ) && '' !== trim( (string) $settings['heading_text'] ) ) : ?>
				<div class="lnc-datesearch__heading"><?php echo esc_html( $settings['heading_text'] ); ?></div>
			<?php endif; ?>

			<div class="lnc-datesearch__group">
				<label class="lnc-datesearch__label"><?php echo esc_html( $settings['start_label'] ); ?></label>
				<div class="lnc-datesearch__field">
					<input type="date" class="lnc-datesearch__input lnc-datesearch__start" aria-label="<?php echo esc_attr( $settings['start_label'] ); ?>">
					<button type="button" class="lnc-datesearch__icon" tabindex="-1" aria-hidden="true"><?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
				</div>
			</div>

			<div class="lnc-datesearch__group">
				<label class="lnc-datesearch__label"><?php echo esc_html( $settings['end_label'] ); ?></label>
				<div class="lnc-datesearch__field">
					<input type="date" class="lnc-datesearch__input lnc-datesearch__end" aria-label="<?php echo esc_attr( $settings['end_label'] ); ?>">
					<button type="button" class="lnc-datesearch__icon" tabindex="-1" aria-hidden="true"><?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
				</div>
			</div>

			<button type="button" class="lnc-datesearch__submit"><?php echo esc_html( $settings['button_label'] ); ?></button>
		</div>
		<?php
	}
}
