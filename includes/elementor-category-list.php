<?php
/**
 * Elementor Category List Widget
 *
 * A vertical list of post categories (with an "All Posts" item first) that
 * filters a Loop Grid via AJAX — reusing the Loop Filter widget's handler and
 * front-end script. Point it at a Loop Grid + Loop Item template like the
 * Loop Filter widget.
 *
 * @package LegalNurseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LNC_Category_List_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'lnc_category_list';
	}

	public function get_title() {
		return esc_html__( 'LN - Category List', 'legal-nurse-core' );
	}

	public function get_icon() {
		return 'eicon-editor-list-ul';
	}

	public function get_categories() {
		return [ 'legal-nurse' ];
	}

	public function get_keywords() {
		return [ 'category', 'categories', 'list', 'filter', 'loop', 'sidebar', 'posts' ];
	}

	public function get_style_depends() {
		return [ 'lnc-loop-filter', 'lnc-category-list' ];
	}

	public function get_script_depends() {
		return [ 'lnc-loop-filter' ];
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
				'label'     => esc_html__( 'Heading', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => esc_html__( 'Categories', 'legal-nurse-core' ),
				'condition' => [ 'show_heading' => 'yes' ],
			]
		);

		$this->add_control(
			'target_selector',
			[
				'label'       => esc_html__( 'Target Loop Grid Selector (optional)', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => '.blog-lists',
				'description' => esc_html__( 'Leave empty to auto-detect the nearest Loop Grid in the same Container/Section. Set a CSS ID (#your-id) or Class (.your-class) to target a specific grid.', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'loop_template_id',
			[
				'label'       => esc_html__( 'Loop Item Template', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'options'     => $this->get_loop_template_options(),
				'default'     => 0,
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
				'label'       => esc_html__( 'Taxonomy', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'category',
				'description' => esc_html__( 'Taxonomy used for the list (e.g. category, post_tag, or a custom taxonomy).', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label'   => esc_html__( 'Posts Per Page', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 12,
				'min'     => 1,
				'max'     => 48,
			]
		);

		$this->add_control(
			'all_label',
			[
				'label'   => esc_html__( '"All Posts" Label', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'All Posts', 'legal-nurse-core' ),
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
			'orderby',
			[
				'label'   => esc_html__( 'Order Categories By', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'name',
				'options' => [
					'name'  => esc_html__( 'Name', 'legal-nurse-core' ),
					'count' => esc_html__( 'Post Count', 'legal-nurse-core' ),
				],
			]
		);

		$this->end_controls_section();

		$this->register_style_controls();
	}

	private function register_style_controls() {
		$this->start_controls_section( 'section_style', [ 'label' => esc_html__( 'Style', 'legal-nurse-core' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );

		// Heading.
		$this->add_control( 'heading_color', [ 'label' => esc_html__( 'Heading Color', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .lnc-catlist__heading' => 'color:{{VALUE}};' ], 'condition' => [ 'show_heading' => 'yes' ] ] );
		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'heading_typography', 'label' => esc_html__( 'Heading', 'legal-nurse-core' ), 'selector' => '{{WRAPPER}} .lnc-catlist__heading', 'condition' => [ 'show_heading' => 'yes' ] ] );
		$this->add_responsive_control( 'heading_spacing', [ 'label' => esc_html__( 'Heading Spacing', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => [ 'px' ], 'range' => [ 'px' => [ 'min' => 0, 'max' => 60 ] ], 'default' => [ 'size' => 16, 'unit' => 'px' ], 'selectors' => [ '{{WRAPPER}} .lnc-catlist__heading' => 'margin-bottom:{{SIZE}}{{UNIT}};' ], 'condition' => [ 'show_heading' => 'yes' ] ] );

		// Items.
		$this->add_control( 'items_heading', [ 'label' => esc_html__( 'Items', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before' ] );
		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'item_typography', 'selector' => '{{WRAPPER}} .lnc-catlist .lnc-loop-filter__btn' ] );
		$this->add_responsive_control( 'item_gap', [ 'label' => esc_html__( 'Item Spacing', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => [ 'px' ], 'range' => [ 'px' => [ 'min' => 0, 'max' => 40 ] ], 'default' => [ 'size' => 14, 'unit' => 'px' ], 'selectors' => [ '{{WRAPPER}} .lnc-catlist .lnc-loop-filter__bar' => 'gap:{{SIZE}}{{UNIT}};' ] ] );

		$this->start_controls_tabs( 'item_tabs' );

		$this->start_controls_tab( 'item_normal', [ 'label' => esc_html__( 'Normal', 'legal-nurse-core' ) ] );
		$this->add_control( 'item_color', [ 'label' => esc_html__( 'Text', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#2a2926', 'selectors' => [ '{{WRAPPER}} .lnc-catlist .lnc-loop-filter__btn' => 'color:{{VALUE}};' ] ] );
		$this->end_controls_tab();

		$this->start_controls_tab( 'item_active', [ 'label' => esc_html__( 'Active', 'legal-nurse-core' ) ] );
		$this->add_control( 'item_color_a', [ 'label' => esc_html__( 'Text', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#1BA39C', 'selectors' => [ '{{WRAPPER}} .lnc-catlist .lnc-loop-filter__btn.is-active' => 'color:{{VALUE}};' ] ] );
		$this->add_control( 'item_border_a', [ 'label' => esc_html__( 'Underline', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#1BA39C', 'selectors' => [ '{{WRAPPER}} .lnc-catlist .lnc-loop-filter__btn.is-active' => 'box-shadow:inset 0 -2px 0 0 {{VALUE}};' ] ] );
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$taxonomy  = $settings['taxonomy'] ? $settings['taxonomy'] : 'category';
		$post_type = $settings['post_type'] ? $settings['post_type'] : 'post';
		$all_label = $settings['all_label'] ? $settings['all_label'] : esc_html__( 'All Posts', 'legal-nurse-core' );
		$template  = (int) ( $settings['loop_template_id'] ?? 0 );
		$target    = trim( (string) ( $settings['target_selector'] ?? '' ) );
		$ppp       = (int) ( $settings['posts_per_page'] ?? 12 );
		$orderby   = 'count' === ( $settings['orderby'] ?? 'name' ) ? 'count' : 'name';
		$order     = 'count' === $orderby ? 'DESC' : 'ASC';

		$terms = get_terms(
			[
				'taxonomy'   => $taxonomy,
				'hide_empty' => 'yes' === ( $settings['hide_empty'] ?? 'yes' ),
				'orderby'    => $orderby,
				'order'      => $order,
			]
		);
		if ( is_wp_error( $terms ) ) {
			$terms = [];
		}

		$config = [
			'target'    => $target,
			'template'  => $template,
			'ppp'       => $ppp,
			'post_type' => $post_type,
			'taxonomy'  => $taxonomy,
			'views_key' => 'post_views_count',
			'allowed'   => [],
			'nonce'     => wp_create_nonce( 'lnc_loop_filter' ),
		];

		if ( ! $template && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			echo '<div class="elementor-alert elementor-alert-warning">'
				. esc_html__( 'Category List: choose the Loop Item Template so filtered results render. The target Loop Grid is auto-detected if left empty.', 'legal-nurse-core' )
				. '</div>';
		}
		?>
		<div class="lnc-loop-filter lnc-catlist" data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">
			<div class="lnc-loop-filter__inner">
				<?php if ( 'yes' === ( $settings['show_heading'] ?? 'yes' ) && '' !== trim( (string) $settings['heading_text'] ) ) : ?>
					<div class="lnc-catlist__heading"><?php echo esc_html( $settings['heading_text'] ); ?></div>
				<?php endif; ?>

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
			</div>
		</div>
		<?php
	}
}
