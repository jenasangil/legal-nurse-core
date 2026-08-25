<?php
/**
 * Elementor Child Pages Widget
 *
 * Pick a parent page and list its child pages (title, SEO meta description,
 * excerpt, read-more) in a responsive grid.
 *
 * @package LegalNurseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LNC_Child_Pages_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'lnc_child_pages';
	}

	public function get_title() {
		return esc_html__( 'LN - Child Pages', 'legal-nurse-core' );
	}

	public function get_icon() {
		return 'eicon-post-list';
	}

	public function get_categories() {
		return [ 'legal-nurse' ];
	}

	public function get_keywords() {
		return [ 'child', 'pages', 'subpages', 'parent', 'list', 'menu', 'resources' ];
	}

	public function get_style_depends() {
		return [ 'lnc-child-pages' ];
	}

	/**
	 * All pages as id => indented title, for the parent selector.
	 *
	 * @return array<int,string>
	 */
	private function get_page_options() {
		$options = [ 0 => esc_html__( '— Select a parent page —', 'legal-nurse-core' ) ];

		// Only needed for the editor dropdown — skip on the front end, where
		// render() uses the saved parent-page ID, not this list.
		$is_editor = is_admin()
			|| \Elementor\Plugin::$instance->editor->is_edit_mode()
			|| ( isset( $_GET['action'] ) && 'elementor' === $_GET['action'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! $is_editor ) {
			return $options;
		}

		$pages = get_pages( [ 'sort_column' => 'menu_order,post_title', 'number' => 500 ] );

		if ( ! is_array( $pages ) ) {
			return $options;
		}

		// Depth for indentation.
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
				'description' => esc_html__( 'Search and pick the page whose children you want to list.', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'include_descendants',
			[
				'label'        => esc_html__( 'Include All Descendants', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'description'  => esc_html__( 'Off = direct children only. On = children and grandchildren.', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'number',
			[
				'label'       => esc_html__( 'Max Items', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 12,
				'min'         => 1,
				'max'         => 100,
				'description' => esc_html__( 'Number of child pages to show.', 'legal-nurse-core' ),
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
			'show_image',
			[
				'label'        => esc_html__( 'Show Featured Image', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'image_size',
			[
				'label'     => esc_html__( 'Image Size', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'medium_large',
				'options'   => [
					'thumbnail'    => esc_html__( 'Thumbnail', 'legal-nurse-core' ),
					'medium'       => esc_html__( 'Medium', 'legal-nurse-core' ),
					'medium_large' => esc_html__( 'Medium Large', 'legal-nurse-core' ),
					'large'        => esc_html__( 'Large', 'legal-nurse-core' ),
					'full'         => esc_html__( 'Full', 'legal-nurse-core' ),
				],
				'condition' => [ 'show_image' => 'yes' ],
			]
		);

		$this->add_control(
			'show_excerpt',
			[
				'label'        => esc_html__( 'Show Excerpt', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => esc_html__( 'Outputs the page\'s hand-written excerpt.', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'excerpt_words',
			[
				'label'       => esc_html__( 'Excerpt Word Limit', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'max'         => 100,
				'description' => esc_html__( '0 = show the full excerpt (keeps formatting like italics).', 'legal-nurse-core' ),
				'condition'   => [ 'show_excerpt' => 'yes' ],
			]
		);

		$this->add_control(
			'show_readmore',
			[
				'label'        => esc_html__( 'Show Read More', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'readmore_text',
			[
				'label'     => esc_html__( 'Read More Text', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => esc_html__( 'Read More', 'legal-nurse-core' ),
				'condition' => [ 'show_readmore' => 'yes' ],
			]
		);

		$this->add_control(
			'readmore_icon',
			[
				'label'       => esc_html__( 'Read More Icon', 'legal-nurse-core' ),
				'type'        => \Elementor\Controls_Manager::ICONS,
				'description' => esc_html__( 'Optional icon shown after the Read More text.', 'legal-nurse-core' ),
				'condition'   => [ 'show_readmore' => 'yes' ],
			]
		);

		$this->end_controls_section();

		$this->register_style_controls();
	}

	private function register_style_controls() {
		$this->start_controls_section( 'section_style', [ 'label' => esc_html__( 'Layout', 'legal-nurse-core' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );

		$this->add_control(
			'center_content',
			[
				'label'        => esc_html__( 'Center Content', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'description'  => esc_html__( 'Center the image, title, excerpt, and Read More inside each card.', 'legal-nurse-core' ),
			]
		);

		$this->add_responsive_control(
			'image_width',
			[
				'label'      => esc_html__( 'Image Width', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [ 'px' => [ 'min' => 100, 'max' => 500 ], '%' => [ 'min' => 15, 'max' => 60 ] ],
				'default'    => [ 'size' => 220, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-childpages--list .lnc-childpage__image' => 'flex:0 0 {{SIZE}}{{UNIT}};max-width:{{SIZE}}{{UNIT}};' ],
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
				'selectors'  => [ '{{WRAPPER}} .lnc-childpages' => 'gap:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_responsive_control(
			'card_padding',
			[
				'label'      => esc_html__( 'Card Padding', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'default'    => [ 'top' => 24, 'right' => 24, 'bottom' => 24, 'left' => 24, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-childpage' => 'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			]
		);

		$this->add_control(
			'card_bg',
			[
				'label'     => esc_html__( 'Card Background', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [ '{{WRAPPER}} .lnc-childpage' => 'background:{{VALUE}};' ],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[ 'name' => 'card_border', 'selector' => '{{WRAPPER}} .lnc-childpage' ]
		);

		$this->add_control(
			'card_radius',
			[
				'label'      => esc_html__( 'Card Radius', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
				'default'    => [ 'size' => 12, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-childpage' => 'border-radius:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_control(
			'image_radius',
			[
				'label'      => esc_html__( 'Image Radius', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
				'default'    => [ 'size' => 24, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-childpage__image img' => 'border-radius:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_responsive_control(
			'image_spacing',
			[
				'label'      => esc_html__( 'Image Spacing', 'legal-nurse-core' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
				'default'    => [ 'size' => 16, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}} .lnc-childpage__image' => 'margin-bottom:{{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( 'Title Color', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .lnc-childpage__title a' => 'color:{{VALUE}};' ],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[ 'name' => 'title_typography', 'label' => esc_html__( 'Title', 'legal-nurse-core' ), 'selector' => '{{WRAPPER}} .lnc-childpage__title' ]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[ 'name' => 'text_typography', 'label' => esc_html__( 'Text', 'legal-nurse-core' ), 'selector' => '{{WRAPPER}} .lnc-childpage__text' ]
		);

		$this->end_controls_section();
	}

	protected function render() {
		wp_enqueue_style( 'lnc-child-pages' );

		$settings = $this->get_settings_for_display();
		$parent   = (int) ( $settings['parent_id'] ?? 0 );

		if ( ! $parent ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="elementor-alert elementor-alert-info">'
					. esc_html__( 'Child Pages: pick a parent page in the widget settings.', 'legal-nurse-core' )
					. '</div>';
			}
			return;
		}

		$number  = (int) ( $settings['number'] ?? 12 );
		$orderby = $settings['orderby'] ?? 'menu_order';
		$order   = ( 'DESC' === ( $settings['order'] ?? 'ASC' ) ) ? 'DESC' : 'ASC';

		if ( 'yes' === ( $settings['include_descendants'] ?? '' ) ) {
			// All descendants.
			$pages = get_pages(
				[
					'child_of'    => $parent,
					'sort_column' => 'menu_order' === $orderby ? 'menu_order' : ( 'title' === $orderby ? 'post_title' : 'post_date' ),
					'sort_order'  => $order,
					'number'      => $number,
				]
			);
			$children = is_array( $pages ) ? $pages : [];
		} else {
			// Direct children only.
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
			$children = $q->posts;
			wp_reset_postdata();
		}

		if ( empty( $children ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="elementor-alert elementor-alert-warning">'
					. esc_html__( 'Child Pages: this page has no published children.', 'legal-nurse-core' )
					. '</div>';
			}
			return;
		}

		$show_image    = 'yes' === ( $settings['show_image'] ?? 'yes' );
		$image_size    = $settings['image_size'] ? $settings['image_size'] : 'medium_large';
		$show_excerpt  = 'yes' === ( $settings['show_excerpt'] ?? 'yes' );
		$show_readmore = 'yes' === ( $settings['show_readmore'] ?? 'yes' );
		$words         = (int) ( $settings['excerpt_words'] ?? 0 );
		$readmore      = $settings['readmore_text'] ? $settings['readmore_text'] : esc_html__( 'Read More', 'legal-nurse-core' );
		$excerpt_tags  = [ 'em' => [], 'strong' => [], 'i' => [], 'b' => [], 'br' => [] ];
		$center        = 'yes' === ( $settings['center_content'] ?? '' ) ? ' lnc-childpages--center' : '';

		// Read-more icon (optional), rendered after the text.
		$rm_icon      = $settings['readmore_icon'] ?? [];
		$rm_icon_html = '';
		if ( ! empty( $rm_icon['value'] ) ) {
			ob_start();
			\Elementor\Icons_Manager::render_icon( $rm_icon, [ 'aria-hidden' => 'true' ] );
			$rm_icon_html = ob_get_clean();
		}

		echo '<div class="lnc-childpages lnc-childpages--list' . esc_attr( $center ) . '">';

		foreach ( $children as $page ) {
			$id    = $page->ID;
			$url   = get_permalink( $id );
			$title = get_the_title( $id );

			echo '<article class="lnc-childpage">';

			// Featured image before the title.
			if ( $show_image && has_post_thumbnail( $id ) ) {
				printf(
					'<a class="lnc-childpage__image" href="%s">%s</a>',
					esc_url( $url ),
					get_the_post_thumbnail( $id, $image_size, [ 'alt' => esc_attr( $title ) ] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);
			}

			echo '<div class="lnc-childpage__body">';

			printf(
				'<h3 class="lnc-childpage__title"><a href="%s">%s</a></h3>',
				esc_url( $url ),
				esc_html( $title )
			);

			if ( $show_excerpt ) {
				// Use the page's hand-written excerpt, preserving light formatting.
				$excerpt = get_post_field( 'post_excerpt', $id );
				if ( '' === $excerpt ) {
					$excerpt = get_the_excerpt( $id );
				}
				if ( '' !== $excerpt ) {
					$out = ( $words > 0 ) ? esc_html( wp_trim_words( $excerpt, $words, '…' ) ) : wp_kses( $excerpt, $excerpt_tags );
					printf( '<p class="lnc-childpage__text lnc-childpage__excerpt">%s</p>', $out ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}

			if ( $show_readmore ) {
				printf(
					'<a class="lnc-childpage__more" href="%s"><span class="lnc-childpage__more-label">%s</span>%s</a>',
					esc_url( $url ),
					esc_html( $readmore ),
					'' !== $rm_icon_html ? '<span class="lnc-childpage__more-icon" aria-hidden="true">' . $rm_icon_html . '</span>' : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);
			}

			echo '</div>'; // .lnc-childpage__body
			echo '</article>';
		}

		echo '</div>';
	}
}
