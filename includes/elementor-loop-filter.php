<?php
/**
 * Elementor Loop Filter Widget
 *
 * A category filter + sort bar that updates an existing Elementor Loop Grid via
 * AJAX (no page reload). Categories load automatically; desktop shows a
 * horizontal bar, mobile shows a select. Multiple instances per page are
 * supported, each targeting its own Loop Grid.
 *
 * @package LegalNurseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LNC_Loop_Filter_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'lnc_loop_filter';
	}

	public function get_title() {
		return esc_html__( 'LN - Loop Filter', 'legal-nurse-core' );
	}

	public function get_icon() {
		return 'eicon-filter';
	}

	public function get_categories() {
		return [ 'legal-nurse' ];
	}

	public function get_keywords() {
		return [ 'filter', 'loop', 'grid', 'category', 'sort', 'ajax', 'posts' ];
	}

	public function get_script_depends() {
		return [ 'lnc-loop-filter' ];
	}

	public function get_style_depends() {
		return [ 'lnc-loop-filter' ];
	}

	/**
	 * Options for the Loop Item template select (Elementor Loop templates).
	 *
	 * @return array<int,string>
	 */
	private function get_loop_template_options() {
		$options = [ 0 => esc_html__( '— Select a Loop template —', 'legal-nurse-core' ) ];

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

	/**
	 * Category term options for the multi-select (category taxonomy).
	 *
	 * @return array<int,string>
	 */
	private function get_category_options() {
		$options = [];
		$terms   = get_terms( [ 'taxonomy' => 'category', 'hide_empty' => false ] );

		if ( is_wp_error( $terms ) ) {
			return $options;
		}

		foreach ( $terms as $term ) {
			$options[ $term->term_id ] = $term->name;
		}

		return $options;
	}

	protected function register_controls() {

		// ---------------------------------------------------------------
		// CONTENT: Settings
		// ---------------------------------------------------------------
		$this->start_controls_section(
			'section_settings',
			[ 'label' => esc_html__( 'Filter Settings', 'legal-nurse-core' ) ]
		);

		$this->add_control(
			'target_selector',
			[
				'label'       => esc_html__( 'Target Loop Grid Selector', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => '.elementor-element-abc123',
				'description' => esc_html__( 'CSS selector of the Loop Grid to filter. Select the Loop Grid, open Advanced, and copy its CSS ID (#your-id) or add a CSS Class and use .your-class.', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'loop_template_id',
			[
				'label'   => esc_html__( 'Loop Item Template', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT2,
				'options' => $this->get_loop_template_options(),
				'default' => 0,
				'description' => esc_html__( 'Must match the Loop template used by the target Loop Grid so filtered items render identically.', 'legal-nurse-core' ),
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
			'taxonomy',
			[
				'label'   => esc_html__( 'Taxonomy', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'category',
				'description' => esc_html__( 'Taxonomy used for the filter buttons (e.g. category, post_tag, or a custom taxonomy).', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label'   => esc_html__( 'Posts Per Page', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 6,
				'min'     => 1,
				'max'     => 48,
			]
		);

		$this->add_control(
			'categories_source',
			[
				'label'   => esc_html__( 'Categories to Show', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'all',
				'options' => [
					'all'    => esc_html__( 'All categories with posts', 'legal-nurse-core' ),
					'manual' => esc_html__( 'Selected categories only', 'legal-nurse-core' ),
				],
			]
		);

		$this->add_control(
			'selected_categories',
			[
				'label'       => esc_html__( 'Choose Categories', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->get_category_options(),
				'description' => esc_html__( 'Only these categories appear as filters, and "All" shows posts from just these. (Loads the "category" taxonomy terms.)', 'legal-nurse-core' ),
				'condition'   => [ 'categories_source' => 'manual' ],
			]
		);

		$this->add_control(
			'all_label',
			[
				'label'   => esc_html__( '"All" Label', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'All Articles', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'hide_empty',
			[
				'label'        => esc_html__( 'Hide Empty Categories', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'enable_sort',
			[
				'label'        => esc_html__( 'Enable Sort By', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'sort_recent_label',
			[
				'label'     => esc_html__( 'Most Recent Label', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => esc_html__( 'Most Recent', 'legal-nurse-core' ),
				'condition' => [ 'enable_sort' => 'yes' ],
			]
		);

		$this->add_control(
			'sort_viewed_label',
			[
				'label'     => esc_html__( 'Most Viewed Label', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => esc_html__( 'Most Viewed', 'legal-nurse-core' ),
				'condition' => [ 'enable_sort' => 'yes' ],
			]
		);

		$this->add_control(
			'views_meta_key',
			[
				'label'       => esc_html__( 'Views Meta Key', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'post_views_count',
				'description' => esc_html__( 'Post meta key holding the view count, used by "Most Viewed".', 'legal-nurse-core' ),
				'condition'   => [ 'enable_sort' => 'yes' ],
			]
		);

		$this->end_controls_section();

		$this->register_style_controls();
	}

	private function register_style_controls() {

		// ---------------------------------------------------------------
		// STYLE: Layout / alignment
		// ---------------------------------------------------------------
		$this->start_controls_section(
			'section_style_layout',
			[
				'label' => esc_html__( 'Layout', 'legal-nurse-core' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'bar_align',
			[
				'label'     => esc_html__( 'Alignment', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => [
					'flex-start'    => [ 'title' => esc_html__( 'Left', 'legal-nurse-core' ), 'icon' => 'eicon-text-align-left' ],
					'center'        => [ 'title' => esc_html__( 'Center', 'legal-nurse-core' ), 'icon' => 'eicon-text-align-center' ],
					'flex-end'      => [ 'title' => esc_html__( 'Right', 'legal-nurse-core' ), 'icon' => 'eicon-text-align-right' ],
					'space-between' => [ 'title' => esc_html__( 'Justify', 'legal-nurse-core' ), 'icon' => 'eicon-text-align-justify' ],
				],
				'default'   => 'space-between',
				'selectors' => [ '{{WRAPPER}} .lnc-loop-filter__inner' => 'justify-content:{{VALUE}};' ],
			]
		);

		$this->add_responsive_control(
			'item_gap',
			[
				'label'      => esc_html__( 'Category Gap', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
				'default'    => [ 'size' => 24, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-loop-filter__bar' => 'gap:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------
		// STYLE: Categories (normal + active)
		// ---------------------------------------------------------------
		$this->start_controls_section(
			'section_style_categories',
			[
				'label' => esc_html__( 'Categories', 'legal-nurse-core' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'category_typography',
				'selector' => '{{WRAPPER}} .lnc-loop-filter__btn',
			]
		);

		$this->add_responsive_control(
			'category_padding',
			[
				'label'      => esc_html__( 'Padding', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-loop-filter__btn' => 'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			]
		);

		$this->add_control(
			'category_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
				'selectors'  => [ '{{WRAPPER}} .lnc-loop-filter__btn' => 'border-radius:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->start_controls_tabs( 'category_state_tabs' );

		// Normal.
		$this->start_controls_tab( 'category_normal', [ 'label' => esc_html__( 'Normal', 'legal-nurse-core' ) ] );
		$this->add_control(
			'category_color',
			[
				'label'     => esc_html__( 'Text Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#4A4A4A',
				'selectors' => [ '{{WRAPPER}} .lnc-loop-filter__btn' => 'color:{{VALUE}};' ],
			]
		);
		$this->add_control(
			'category_bg',
			[
				'label'     => esc_html__( 'Background', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'transparent',
				'selectors' => [ '{{WRAPPER}} .lnc-loop-filter__btn' => 'background:{{VALUE}};' ],
			]
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[ 'name' => 'category_border', 'selector' => '{{WRAPPER}} .lnc-loop-filter__btn' ]
		);
		$this->end_controls_tab();

		// Hover.
		$this->start_controls_tab( 'category_hover', [ 'label' => esc_html__( 'Hover', 'legal-nurse-core' ) ] );
		$this->add_control(
			'category_color_hover',
			[
				'label'     => esc_html__( 'Text Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .lnc-loop-filter__btn:hover' => 'color:{{VALUE}};' ],
			]
		);
		$this->add_control(
			'category_bg_hover',
			[
				'label'     => esc_html__( 'Background', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .lnc-loop-filter__btn:hover' => 'background:{{VALUE}};' ],
			]
		);
		$this->add_control(
			'category_border_color_hover',
			[
				'label'     => esc_html__( 'Border Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .lnc-loop-filter__btn:hover' => 'border-color:{{VALUE}};' ],
			]
		);
		$this->end_controls_tab();

		// Active.
		$this->start_controls_tab( 'category_active', [ 'label' => esc_html__( 'Active', 'legal-nurse-core' ) ] );
		$this->add_control(
			'category_color_active',
			[
				'label'     => esc_html__( 'Text Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#1BA39C',
				'selectors' => [ '{{WRAPPER}} .lnc-loop-filter__btn.is-active' => 'color:{{VALUE}};' ],
			]
		);
		$this->add_control(
			'category_bg_active',
			[
				'label'     => esc_html__( 'Background', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .lnc-loop-filter__btn.is-active' => 'background:{{VALUE}};' ],
			]
		);
		$this->add_control(
			'category_active_border_color',
			[
				'label'     => esc_html__( 'Underline / Border Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#1BA39C',
				'selectors' => [ '{{WRAPPER}} .lnc-loop-filter__btn.is-active' => 'box-shadow:inset 0 -2px 0 0 {{VALUE}};' ],
			]
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// ---------------------------------------------------------------
		// STYLE: Dropdowns (mobile category select + sort select)
		// ---------------------------------------------------------------
		$this->start_controls_section(
			'section_style_dropdowns',
			[
				'label' => esc_html__( 'Dropdowns', 'legal-nurse-core' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'dropdown_typography',
				'selector' => '{{WRAPPER}} .lnc-loop-filter__select',
			]
		);

		$this->add_control(
			'dropdown_color',
			[
				'label'     => esc_html__( 'Text Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .lnc-loop-filter__select' => 'color:{{VALUE}};' ],
			]
		);

		$this->add_control(
			'dropdown_bg',
			[
				'label'     => esc_html__( 'Background', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .lnc-loop-filter__select' => 'background:{{VALUE}};' ],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[ 'name' => 'dropdown_border', 'selector' => '{{WRAPPER}} .lnc-loop-filter__select' ]
		);

		$this->add_responsive_control(
			'dropdown_padding',
			[
				'label'      => esc_html__( 'Padding', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-loop-filter__select' => 'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$taxonomy   = $settings['taxonomy'] ? $settings['taxonomy'] : 'category';
		$post_type  = $settings['post_type'] ? $settings['post_type'] : 'post';
		$all_label  = $settings['all_label'] ? $settings['all_label'] : esc_html__( 'All Articles', 'legal-nurse-core' );
		$template   = (int) ( $settings['loop_template_id'] ?? 0 );
		$target     = trim( (string) ( $settings['target_selector'] ?? '' ) );
		$ppp        = (int) ( $settings['posts_per_page'] ?? 6 );
		$views_key  = $settings['views_meta_key'] ? $settings['views_meta_key'] : 'post_views_count';
		$enable_sort = 'yes' === ( $settings['enable_sort'] ?? 'yes' );

		$term_args = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => 'yes' === ( $settings['hide_empty'] ?? 'yes' ),
		];

		// Manual selection: limit to chosen categories, preserving their order.
		$allowed = [];
		if ( 'manual' === ( $settings['categories_source'] ?? 'all' ) && ! empty( $settings['selected_categories'] ) ) {
			$allowed             = array_map( 'intval', (array) $settings['selected_categories'] );
			$term_args['include'] = $allowed;
			$term_args['orderby'] = 'include';
			unset( $term_args['hide_empty'] );
		}

		$terms = get_terms( $term_args );

		if ( is_wp_error( $terms ) ) {
			$terms = [];
		}

		$config = [
			'target'    => $target,
			'template'  => $template,
			'ppp'       => $ppp,
			'post_type' => $post_type,
			'taxonomy'  => $taxonomy,
			'views_key' => $views_key,
			'allowed'   => $allowed,
			'nonce'     => wp_create_nonce( 'lnc_loop_filter' ),
		];

		// Editor helper notice.
		if ( ( '' === $target || ! $template ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			echo '<div class="elementor-alert elementor-alert-warning">'
				. esc_html__( 'Loop Filter: set the Target Loop Grid Selector and Loop Item Template in the widget settings.', 'legal-nurse-core' )
				. '</div>';
		}
		?>
		<div class="lnc-loop-filter" data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">
			<div class="lnc-loop-filter__inner">

				<div class="lnc-loop-filter__bar" role="tablist">
					<button type="button" class="lnc-loop-filter__btn is-active" data-term="all">
						<?php echo esc_html( $all_label ); ?>
					</button>
					<?php foreach ( $terms as $term ) : ?>
						<button type="button" class="lnc-loop-filter__btn" data-term="<?php echo esc_attr( $term->term_id ); ?>">
							<?php echo esc_html( $term->name ); ?>
						</button>
					<?php endforeach; ?>
				</div>

				<select class="lnc-loop-filter__select lnc-loop-filter__categories" aria-label="<?php esc_attr_e( 'Filter by category', 'legal-nurse-core' ); ?>">
					<option value="all"><?php echo esc_html( $all_label ); ?></option>
					<?php foreach ( $terms as $term ) : ?>
						<option value="<?php echo esc_attr( $term->term_id ); ?>"><?php echo esc_html( $term->name ); ?></option>
					<?php endforeach; ?>
				</select>

				<?php if ( $enable_sort ) : ?>
					<select class="lnc-loop-filter__select lnc-loop-filter__sort" aria-label="<?php esc_attr_e( 'Sort by', 'legal-nurse-core' ); ?>">
						<option value="recent"><?php echo esc_html( $settings['sort_recent_label'] ? $settings['sort_recent_label'] : esc_html__( 'Most Recent', 'legal-nurse-core' ) ); ?></option>
						<option value="viewed"><?php echo esc_html( $settings['sort_viewed_label'] ? $settings['sort_viewed_label'] : esc_html__( 'Most Viewed', 'legal-nurse-core' ) ); ?></option>
					</select>
				<?php endif; ?>

			</div>
		</div>
		<?php
	}
}
