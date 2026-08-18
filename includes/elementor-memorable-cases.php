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
				'default'        => '1',
				'tablet_default' => '1',
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

		$number  = (int) ( $settings['number'] ?? 12 );
		$orderby = $settings['orderby'] ?? 'menu_order';
		$order   = ( 'DESC' === ( $settings['order'] ?? 'ASC' ) ) ? 'DESC' : 'ASC';
		$field   = $settings['meta_field'] ? $settings['meta_field'] : 'right_box_text';

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

		echo '<div class="lnc-cases">';

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

			echo '<article class="lnc-case">';

			echo '<a class="lnc-case__media" href="' . esc_url( $url ) . '">';
			if ( '' !== $img_html ) {
				echo wp_kses( $img_html, [ 'img' => [ 'src' => [], 'srcset' => [], 'sizes' => [], 'alt' => [], 'width' => [], 'height' => [], 'class' => [], 'loading' => [] ] ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				echo '<span class="lnc-case__media-placeholder"></span>';
			}
			echo '</a>';

			echo '<div class="lnc-case__body">';
			printf(
				'<h3 class="lnc-case__title"><a href="%s">%s</a></h3>',
				esc_url( $url ),
				esc_html( $title )
			);

			$byline = trim( (string) $byline );
			if ( '' !== $byline ) {
				echo '<div class="lnc-case__byline">' . wp_kses( $byline, $this->byline_allowed_tags() ) . '</div>';
			}
			echo '</div>';

			echo '</article>';
		}
		wp_reset_postdata();

		echo '</div>';
	}
}
