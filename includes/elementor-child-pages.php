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
		$pages   = get_pages( [ 'sort_column' => 'menu_order,post_title', 'number' => 500 ] );

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

	/**
	 * SEO meta description for a page (Yoast / Rank Math), else the excerpt.
	 *
	 * @param int $id
	 * @return string
	 */
	private function get_meta_description( $id ) {
		$meta = get_post_meta( $id, '_yoast_wpseo_metadesc', true );
		if ( '' === $meta ) {
			$meta = get_post_meta( $id, 'rank_math_description', true );
		}
		if ( '' === $meta ) {
			$meta = get_the_excerpt( $id );
		}
		return (string) $meta;
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
			'show_meta',
			[
				'label'        => esc_html__( 'Show Meta Description', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'show_excerpt',
			[
				'label'        => esc_html__( 'Show Excerpt', 'legal-nurse-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'description'  => esc_html__( 'Shows the page excerpt in addition to (or instead of) the meta description.', 'legal-nurse-core' ),
			]
		);

		$this->add_control(
			'excerpt_words',
			[
				'label'     => esc_html__( 'Excerpt/Meta Word Limit', 'legal-nurse-core' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 24,
				'min'       => 0,
				'max'       => 100,
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
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => [ '1' => '1', '2' => '2', '3' => '3', '4' => '4' ],
				'selectors'      => [ '{{WRAPPER}} .lnc-childpages' => 'grid-template-columns:repeat({{VALUE}},1fr);' ],
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

		$show_meta     = 'yes' === ( $settings['show_meta'] ?? 'yes' );
		$show_excerpt  = 'yes' === ( $settings['show_excerpt'] ?? '' );
		$show_readmore = 'yes' === ( $settings['show_readmore'] ?? 'yes' );
		$words         = (int) ( $settings['excerpt_words'] ?? 24 );
		$readmore      = $settings['readmore_text'] ? $settings['readmore_text'] : esc_html__( 'Read More', 'legal-nurse-core' );

		echo '<div class="lnc-childpages">';

		foreach ( $children as $page ) {
			$id    = $page->ID;
			$url   = get_permalink( $id );
			$title = get_the_title( $id );

			echo '<article class="lnc-childpage">';
			printf(
				'<h3 class="lnc-childpage__title"><a href="%s">%s</a></h3>',
				esc_url( $url ),
				esc_html( $title )
			);

			if ( $show_meta ) {
				$meta = $this->get_meta_description( $id );
				if ( '' !== $meta ) {
					printf( '<p class="lnc-childpage__text lnc-childpage__meta">%s</p>', esc_html( wp_trim_words( $meta, $words, '…' ) ) );
				}
			}

			if ( $show_excerpt ) {
				$excerpt = get_the_excerpt( $id );
				if ( '' !== $excerpt ) {
					printf( '<p class="lnc-childpage__text lnc-childpage__excerpt">%s</p>', esc_html( wp_trim_words( $excerpt, $words, '…' ) ) );
				}
			}

			if ( $show_readmore ) {
				printf(
					'<a class="lnc-childpage__more" href="%s">%s</a>',
					esc_url( $url ),
					esc_html( $readmore )
				);
			}

			echo '</article>';
		}

		echo '</div>';
	}
}
