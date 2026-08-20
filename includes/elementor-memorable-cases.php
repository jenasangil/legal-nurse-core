<?php
/**
 * Elementor Memorable Cases Widget
 *
 * Lists the child pages of a chosen parent page (e.g. "Memorable Cases"),
 * showing the linked page title and the ACF field "right_box_text". The round
 * image lives inside right_box_text: its first <img> is placed on the left,
 * with the title and the remaining byline text on the right.
 *
 * @package LegalNurseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LNC_Memorable_Cases_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'lnc_memorable_cases';
	}

	public function get_title() {
		return esc_html__( 'LN - Memorable Cases', 'legal-nurse-core' );
	}

	public function get_icon() {
		return 'eicon-testimonial';
	}

	public function get_categories() {
		return [ 'legal-nurse' ];
	}

	public function get_keywords() {
		return [ 'memorable', 'cases', 'child', 'pages', 'testimonial', 'author' ];
	}

	public function get_style_depends() {
		return [ 'lnc-memorable-cases' ];
	}

	public function get_script_depends() {
		return [ 'lnc-memorable-cases' ];
	}

	/**
	 * All pages as id => indented title, for the parent selector.
	 *
	 * @return array<int,string>
	 */
	private function get_page_options() {
		$options = [ 0 => esc_html__( '— Select a parent page —', 'legal-nurse-core' ) ];
		$pages   = get_pages( [ 'sort_column' => 'menu_order,post_title', 'number' => 500 ] );

		if ( ! is_array( $pages ) ) {
			return $options;
		}

		$by_parent = [];
		foreach ( $pages as $p ) {
			$by_parent[ $p->post_parent ][] = $p;
		}
		$build = function ( $parent, $depth ) use ( &$build, &$options, $by_parent ) {
			if ( empty( $by_parent[ $parent ] ) ) {
				return;
			}
			foreach ( $by_parent[ $parent ] as $p ) {
				$options[ $p->ID ] = str_repeat( '— ', $depth ) . $p->post_title;
				$build( $p->ID, $depth + 1 );
			}
		};
		$build( 0, 0 );

		return $options;
	}

	protected function register_controls() {

		$this->start_controls_section( 'section_content', [ 'label' => esc_html__( 'Content', 'legal-nurse-core' ) ] );

		$this->add_control(
			'parent_id',
			[
				'label'       => esc_html__( 'Parent Page', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'options'     => $this->get_page_options(),
				'default'     => 0,
				'description' => esc_html__( 'Pick the parent (e.g. "Memorable Cases") whose child pages you want to list.', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'meta_field',
			[
				'label'       => esc_html__( 'ACF Field', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'right_box_text',
				'description' => esc_html__( 'ACF field holding the image + byline (its first image goes on the left).', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'number',
			[
				'label'   => esc_html__( 'Max Items', 'legal-nurse-core' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 12,
				'min'     => 1,
				'max'     => 100,
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

		$this->add_control(
			'show_title',
			[
				'label'        => esc_html__( 'Show Title', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'enable_view_more',
			[
				'label'        => esc_html__( 'Enable "View More"', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'separator'    => 'before',
				'description'  => esc_html__( 'Show only the first N items with a button to reveal the rest.', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'initial_count',
			[
				'label'     => esc_html__( 'Initially Visible', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 4,
				'min'       => 1,
				'condition' => [ 'enable_view_more' => 'yes' ],
			]
		);

		$this->add_control(
			'view_more_label',
			[
				'label'     => esc_html__( '"View More" Label', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => esc_html__( 'View More', 'legal-nurse-core' ),
				'condition' => [ 'enable_view_more' => 'yes' ],
			]
		);

		$this->add_control(
			'view_less_label',
			[
				'label'     => esc_html__( '"View Less" Label', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => esc_html__( 'View Less', 'legal-nurse-core' ),
				'condition' => [ 'enable_view_more' => 'yes' ],
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
				'default'        => '2',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => [ '1' => '1', '2' => '2', '3' => '3' ],
				'selectors'      => [ '{{WRAPPER}} .lnc-cases' => 'grid-template-columns:repeat({{VALUE}},1fr);' ],
			]
		);

		$this->add_responsive_control(
			'gap',
			[
				'label'      => esc_html__( 'Gap', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
				'default'    => [ 'size' => 24, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-cases' => 'gap:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_responsive_control(
			'image_dimension',
			[
				'label'      => esc_html__( 'Image Size', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 60, 'max' => 240 ] ],
				'default'    => [ 'size' => 120, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-case__media img' => 'width:{{SIZE}}{{UNIT}};height:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_control(
			'image_border_color',
			[
				'label'     => esc_html__( 'Image Border Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#D4F3F0',
				'selectors' => [ '{{WRAPPER}} .lnc-case__media img' => 'border-color:{{VALUE}};' ],
			]
		);

		$this->add_control(
			'image_border_width',
			[
				'label'      => esc_html__( 'Image Border Width', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 12 ] ],
				'default'    => [ 'size' => 4, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-case__media img' => 'border-width:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_responsive_control(
			'gap_image_text',
			[
				'label'      => esc_html__( 'Image ↔ Text Gap', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
				'default'    => [ 'size' => 18, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-case' => 'gap:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( 'Title Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .lnc-case__title a' => 'color:{{VALUE}};' ],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[ 'name' => 'title_typography', 'label' => esc_html__( 'Title', 'legal-nurse-core' ), 'selector' => '{{WRAPPER}} .lnc-case__title' ]
		);

		$this->add_control(
			'byline_color',
			[
				'label'     => esc_html__( 'Byline Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .lnc-case__byline' => 'color:{{VALUE}};' ],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[ 'name' => 'byline_typography', 'label' => esc_html__( 'Byline', 'legal-nurse-core' ), 'selector' => '{{WRAPPER}} .lnc-case__byline' ]
		);

		$this->end_controls_section();

		// View More button style.
		$this->start_controls_section(
			'section_button_style',
			[
				'label'     => esc_html__( 'View More Button', 'legal-nurse-core' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [ 'enable_view_more' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'button_align',
			[
				'label'     => esc_html__( 'Alignment', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => [
					'flex-start' => [ 'title' => esc_html__( 'Left', 'legal-nurse-core' ), 'icon' => 'eicon-text-align-left' ],
					'center'     => [ 'title' => esc_html__( 'Center', 'legal-nurse-core' ), 'icon' => 'eicon-text-align-center' ],
					'flex-end'   => [ 'title' => esc_html__( 'Right', 'legal-nurse-core' ), 'icon' => 'eicon-text-align-right' ],
				],
				'default'   => 'center',
				'selectors' => [ '{{WRAPPER}} .lnc-cases__more' => 'justify-content:{{VALUE}};' ],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[ 'name' => 'button_typography', 'selector' => '{{WRAPPER}} .lnc-cases__more-btn' ]
		);

		$this->start_controls_tabs( 'button_tabs' );

		$this->start_controls_tab( 'button_normal', [ 'label' => esc_html__( 'Normal', 'legal-nurse-core' ) ] );
		$this->add_control( 'button_color', [ 'label' => esc_html__( 'Text', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#ffffff', 'selectors' => [ '{{WRAPPER}} .lnc-cases__more-btn' => 'color:{{VALUE}};' ] ] );
		$this->add_control( 'button_bg', [ 'label' => esc_html__( 'Background', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#25797c', 'selectors' => [ '{{WRAPPER}} .lnc-cases__more-btn' => 'background:{{VALUE}};' ] ] );
		$this->end_controls_tab();

		$this->start_controls_tab( 'button_hover', [ 'label' => esc_html__( 'Hover', 'legal-nurse-core' ) ] );
		$this->add_control( 'button_color_h', [ 'label' => esc_html__( 'Text', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .lnc-cases__more-btn:hover' => 'color:{{VALUE}};' ] ] );
		$this->add_control( 'button_bg_h', [ 'label' => esc_html__( 'Background', 'legal-nurse-core' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .lnc-cases__more-btn:hover' => 'background:{{VALUE}};' ] ] );
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'button_padding',
			[
				'label'      => esc_html__( 'Padding', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'default'    => [ 'top' => 12, 'right' => 28, 'bottom' => 12, 'left' => 28, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-cases__more-btn' => 'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			]
		);

		$this->add_control(
			'button_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 50 ] ],
				'default'    => [ 'size' => 999, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-cases__more-btn' => 'border-radius:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_responsive_control(
			'button_top_spacing',
			[
				'label'      => esc_html__( 'Top Spacing', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
				'default'    => [ 'size' => 28, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-cases__more' => 'margin-top:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Byline HTML allowed tags (right_box_text content, minus its image).
	 *
	 * @return array
	 */
	private function byline_allowed_tags() {
		return [
			'em'     => [],
			'i'      => [],
			'strong' => [],
			'b'      => [],
			'span'   => [ 'class' => [], 'style' => [] ],
			'br'     => [],
			'a'      => [ 'href' => [], 'target' => [], 'rel' => [] ],
			'p'      => [],
		];
	}

	protected function render() {
		wp_enqueue_style( 'lnc-memorable-cases' );

		$settings = $this->get_settings_for_display();
		$parent   = (int) ( $settings['parent_id'] ?? 0 );

		if ( ! $parent ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="elementor-alert elementor-alert-info">'
					. esc_html__( 'Memorable Cases: pick a parent page in the widget settings.', 'legal-nurse-core' )
					. '</div>';
			}
			return;
		}

		$number     = (int) ( $settings['number'] ?? 12 );
		$orderby    = $settings['orderby'] ?? 'menu_order';
		$order      = ( 'DESC' === ( $settings['order'] ?? 'ASC' ) ) ? 'DESC' : 'ASC';
		$field      = $settings['meta_field'] ? $settings['meta_field'] : 'right_box_text';
		$show_title = 'yes' === ( $settings['show_title'] ?? 'yes' );
		$view_more  = 'yes' === ( $settings['enable_view_more'] ?? '' );
		$initial    = max( 1, (int) ( $settings['initial_count'] ?? 4 ) );

		$q = new WP_Query(
			[
				'post_type'      => 'page',
				'post_parent'    => $parent,
				'posts_per_page' => $number,
				'orderby'        => $orderby,
				'order'          => $order,
				'post_status'    => 'publish',
				'no_found_rows'  => true,
			]
		);

		if ( ! $q->have_posts() ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="elementor-alert elementor-alert-warning">'
					. esc_html__( 'Memorable Cases: this page has no published children.', 'legal-nurse-core' )
					. '</div>';
			}
			return;
		}

		$total    = (int) $q->post_count;
		$has_more = $view_more && $total > $initial;

		echo '<div class="lnc-cases">';

		$index = 0;
		while ( $q->have_posts() ) {
			$q->the_post();
			$id    = get_the_ID();
			$url   = get_permalink( $id );
			$title = get_the_title( $id );

			$rbt = function_exists( 'get_field' ) ? get_field( $field, $id ) : '';
			$rbt = is_string( $rbt ) ? $rbt : '';

			// Pull the first image out of the field for the left column.
			$img_html = '';
			$byline   = $rbt;
			if ( preg_match( '/<img\b[^>]*>/i', $rbt, $m ) ) {
				$img_html = $m[0];
				$byline   = preg_replace( '/<img\b[^>]*>/i', '', $rbt, 1 );
			}

			// Keep only the first paragraph of the byline.
			if ( preg_match( '/<p\b[^>]*>.*?<\/p>/is', $byline, $pm ) ) {
				$byline = $pm[0];
			}

			$hidden      = $has_more && $index >= $initial;
			$item_class  = 'lnc-case' . ( $hidden ? ' lnc-case--hidden' : '' );
			$item_hidden = $hidden ? ' hidden' : '';

			echo '<article class="' . esc_attr( $item_class ) . '"' . $item_hidden . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			echo '<a class="lnc-case__media" href="' . esc_url( $url ) . '">';
			if ( '' !== $img_html ) {
				echo wp_kses( $img_html, [ 'img' => [ 'src' => [], 'srcset' => [], 'sizes' => [], 'alt' => [], 'width' => [], 'height' => [], 'class' => [], 'loading' => [] ] ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				echo '<span class="lnc-case__media-placeholder"></span>';
			}
			echo '</a>';

			echo '<div class="lnc-case__body">';
			if ( $show_title ) {
				printf(
					'<h3 class="lnc-case__title"><a href="%s">%s</a></h3>',
					esc_url( $url ),
					esc_html( $title )
				);
			}

			$byline = trim( (string) $byline );
			if ( '' !== $byline ) {
				echo '<div class="lnc-case__byline">' . wp_kses( $byline, $this->byline_allowed_tags() ) . '</div>';
			}
			echo '</div>';

			echo '</article>';
			$index++;
		}
		wp_reset_postdata();

		echo '</div>';

		if ( $has_more ) {
			wp_enqueue_script( 'lnc-memorable-cases' );
			$more_label = $settings['view_more_label'] ? $settings['view_more_label'] : esc_html__( 'View More', 'legal-nurse-core' );
			$less_label = $settings['view_less_label'] ? $settings['view_less_label'] : esc_html__( 'View Less', 'legal-nurse-core' );
			printf(
				'<div class="lnc-cases__more"><button type="button" class="lnc-cases__more-btn" data-more="%s" data-less="%s">%s</button></div>',
				esc_attr( $more_label ),
				esc_attr( $less_label ),
				esc_html( $more_label )
			);
		}
	}
}
