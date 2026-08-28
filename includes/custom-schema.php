<?php
/**
 * Custom Schema Meta Box
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Register Meta Box
add_action( 'add_meta_boxes', 'lnc_register_custom_schema_meta_box' );
function lnc_register_custom_schema_meta_box() {
	$screens = [ 'page', 'post' ];
	foreach ( $screens as $screen ) {
		add_meta_box(
			'lnc_custom_schema_meta_box',
			__( 'Custom Schema Code', 'legal-nurse-core' ),
			'lnc_custom_schema_meta_box_html',
			$screen,
			'normal',
			'high'
		);
	}
}

// 2. Output Meta Box HTML
function lnc_custom_schema_meta_box_html( $post ) {
	wp_nonce_field( 'lnc_custom_schema_meta_box_nonce', 'lnc_custom_schema_nonce' );

	$disable_yoast = get_post_meta( $post->ID, '_lnc_disable_yoast_schema', true );
	$main_schema = get_post_meta( $post->ID, '_lnc_main_schema', true );
	$faq_schema = get_post_meta( $post->ID, '_lnc_faq_schema', true );
	$breadcrumb_schema = get_post_meta( $post->ID, '_lnc_breadcrumb_schema', true );

	?>
	<p>
		<label>
			<input type="checkbox" name="lnc_disable_yoast_schema" value="yes" <?php checked( $disable_yoast, 'yes' ); ?> />
			<?php esc_html_e( 'Disable Yoast Schema markup for this page', 'legal-nurse-core' ); ?>
		</label>
	</p>
	
	<p>
		<label for="lnc_main_schema"><strong><?php esc_html_e( 'Main Schema (JSON-LD)', 'legal-nurse-core' ); ?></strong></label><br/>
		<textarea id="lnc_main_schema" name="lnc_main_schema" rows="6" style="width:100%; font-family:monospace;"><?php echo esc_textarea( $main_schema ); ?></textarea>
		<small><?php esc_html_e( 'Paste the JSON-LD content here. If you do not include <script> tags, they will be added automatically.', 'legal-nurse-core' ); ?></small>
	</p>

	<p>
		<label for="lnc_faq_schema"><strong><?php esc_html_e( 'FAQPage Schema (JSON-LD)', 'legal-nurse-core' ); ?></strong></label><br/>
		<textarea id="lnc_faq_schema" name="lnc_faq_schema" rows="6" style="width:100%; font-family:monospace;"><?php echo esc_textarea( $faq_schema ); ?></textarea>
		<small><?php esc_html_e( 'Paste the JSON-LD content here. If you do not include <script> tags, they will be added automatically.', 'legal-nurse-core' ); ?></small>
	</p>

	<p>
		<label for="lnc_breadcrumb_schema"><strong><?php esc_html_e( 'BreadcrumbList Schema (JSON-LD)', 'legal-nurse-core' ); ?></strong></label><br/>
		<textarea id="lnc_breadcrumb_schema" name="lnc_breadcrumb_schema" rows="6" style="width:100%; font-family:monospace;"><?php echo esc_textarea( $breadcrumb_schema ); ?></textarea>
		<small><?php esc_html_e( 'Paste the JSON-LD content here. If you do not include <script> tags, they will be added automatically.', 'legal-nurse-core' ); ?></small>
	</p>
	<?php
}

// 3. Save Meta Box Data
add_action( 'save_post', 'lnc_save_custom_schema_meta_box_data' );
function lnc_save_custom_schema_meta_box_data( $post_id ) {
	if ( ! isset( $_POST['lnc_custom_schema_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( $_POST['lnc_custom_schema_nonce'], 'lnc_custom_schema_meta_box_nonce' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( isset( $_POST['post_type'] ) && 'page' === $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}
	} else {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	$disable_yoast = isset( $_POST['lnc_disable_yoast_schema'] ) ? 'yes' : 'no';
	update_post_meta( $post_id, '_lnc_disable_yoast_schema', $disable_yoast );

	if ( isset( $_POST['lnc_main_schema'] ) ) {
		update_post_meta( $post_id, '_lnc_main_schema', wp_unslash( $_POST['lnc_main_schema'] ) );
	}
	if ( isset( $_POST['lnc_faq_schema'] ) ) {
		update_post_meta( $post_id, '_lnc_faq_schema', wp_unslash( $_POST['lnc_faq_schema'] ) );
	}
	if ( isset( $_POST['lnc_breadcrumb_schema'] ) ) {
		update_post_meta( $post_id, '_lnc_breadcrumb_schema', wp_unslash( $_POST['lnc_breadcrumb_schema'] ) );
	}
}

// 4. Output the Schema in the footer (before end of body tag)
add_action( 'wp_footer', 'lnc_output_custom_schema', 100 );
function lnc_output_custom_schema() {
	if ( is_singular() ) {
		$post_id = get_the_ID();
		
		$schemas = [
			get_post_meta( $post_id, '_lnc_main_schema', true ),
			get_post_meta( $post_id, '_lnc_faq_schema', true ),
			get_post_meta( $post_id, '_lnc_breadcrumb_schema', true ),
		];
		
		foreach ( $schemas as $schema ) {
			$schema = trim( (string) $schema );
			if ( ! empty( $schema ) ) {
				// Check if user already included the script tags
				if ( false === stripos( $schema, '<script' ) ) {
					echo "\n<!-- LNC Custom Schema -->\n";
					echo '<script type="application/ld+json">' . "\n";
					echo $schema . "\n";
					echo '</script>' . "\n";
				} else {
					echo "\n<!-- LNC Custom Schema -->\n";
					echo $schema . "\n";
				}
			}
		}
	}
}

// 5. Disable Yoast Schema conditionally
add_filter( 'wpseo_json_ld_output', 'lnc_disable_yoast_schema_conditionally', 99 );
function lnc_disable_yoast_schema_conditionally( $data ) {
	if ( is_singular() ) {
		$disable_yoast = get_post_meta( get_the_ID(), '_lnc_disable_yoast_schema', true );
		if ( 'yes' === $disable_yoast ) {
			return [];
		}
	}
	return $data;
}
