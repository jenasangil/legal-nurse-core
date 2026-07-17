<?php
/**
 * WooCommerce Product Meta
 *
 * Adds a "Product Meta" box to WooCommerce products with:
 *   - Pricing Note   (single line, e.g. "Free mentoring: once a month")
 *   - Features       (repeatable list, e.g. "CLNC® Certification Program + exam")
 *
 * These feed the Pricing Cards Elementor widget when its source is WooCommerce.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const LNC_META_PRICING_NOTE = '_lnc_pricing_note';
const LNC_META_FEATURES     = '_lnc_features';

/**
 * Register the meta box on the product edit screen.
 */
add_action( 'add_meta_boxes', 'lnc_add_product_meta_box' );
function lnc_add_product_meta_box() {
	add_meta_box(
		'lnc_product_meta',
		esc_html__( 'Product Meta', 'legal-nurse-core' ),
		'lnc_render_product_meta_box',
		'product',
		'normal',
		'high'
	);
}

/**
 * Render the meta box UI.
 *
 * @param WP_Post $post
 */
function lnc_render_product_meta_box( $post ) {
	wp_nonce_field( 'lnc_product_meta_save', 'lnc_product_meta_nonce' );

	$note     = get_post_meta( $post->ID, LNC_META_PRICING_NOTE, true );
	$features = get_post_meta( $post->ID, LNC_META_FEATURES, true );
	$features = is_array( $features ) ? $features : [];
	if ( empty( $features ) ) {
		$features = [ '' ]; // Start with one empty row.
	}
	?>
	<p>
		<label for="lnc_pricing_note"><strong><?php esc_html_e( 'Pricing Note', 'legal-nurse-core' ); ?></strong></label><br>
		<input type="text" id="lnc_pricing_note" name="lnc_pricing_note"
			value="<?php echo esc_attr( $note ); ?>" class="widefat"
			placeholder="<?php esc_attr_e( 'Free mentoring: once a month', 'legal-nurse-core' ); ?>">
	</p>

	<p><strong><?php esc_html_e( 'Features', 'legal-nurse-core' ); ?></strong></p>
	<table class="widefat" id="lnc-features-table" style="max-width:800px;">
		<thead>
			<tr>
				<th style="width:30px;">#</th>
				<th><?php esc_html_e( 'Feature', 'legal-nurse-core' ); ?></th>
				<th style="width:40px;"></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $features as $i => $feature ) : ?>
				<tr class="lnc-feature-row">
					<td class="lnc-feature-index"><?php echo (int) ( $i + 1 ); ?></td>
					<td>
						<input type="text" name="lnc_features[]" class="widefat"
							value="<?php echo esc_attr( $feature ); ?>">
					</td>
					<td>
						<button type="button" class="button lnc-remove-feature" title="<?php esc_attr_e( 'Remove', 'legal-nurse-core' ); ?>">&minus;</button>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p>
		<button type="button" class="button button-primary" id="lnc-add-feature">
			<?php esc_html_e( 'Add Row', 'legal-nurse-core' ); ?>
		</button>
	</p>

	<script>
	( function () {
		var table = document.getElementById( 'lnc-features-table' );
		if ( ! table ) { return; }
		var tbody = table.querySelector( 'tbody' );

		function reindex() {
			tbody.querySelectorAll( '.lnc-feature-index' ).forEach( function ( cell, idx ) {
				cell.textContent = idx + 1;
			} );
		}

		document.getElementById( 'lnc-add-feature' ).addEventListener( 'click', function () {
			var row = document.createElement( 'tr' );
			row.className = 'lnc-feature-row';
			row.innerHTML = '<td class="lnc-feature-index"></td>' +
				'<td><input type="text" name="lnc_features[]" class="widefat" value=""></td>' +
				'<td><button type="button" class="button lnc-remove-feature">&minus;</button></td>';
			tbody.appendChild( row );
			reindex();
		} );

		tbody.addEventListener( 'click', function ( e ) {
			if ( e.target.classList.contains( 'lnc-remove-feature' ) ) {
				if ( tbody.querySelectorAll( '.lnc-feature-row' ).length > 1 ) {
					e.target.closest( '.lnc-feature-row' ).remove();
				} else {
					e.target.closest( '.lnc-feature-row' ).querySelector( 'input' ).value = '';
				}
				reindex();
			}
		} );
	} )();
	</script>
	<?php
}

/**
 * Save the meta box data.
 *
 * @param int $post_id
 */
add_action( 'save_post_product', 'lnc_save_product_meta' );
function lnc_save_product_meta( $post_id ) {
	if ( ! isset( $_POST['lnc_product_meta_nonce'] )
		|| ! wp_verify_nonce( sanitize_key( $_POST['lnc_product_meta_nonce'] ), 'lnc_product_meta_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Pricing note.
	$note = isset( $_POST['lnc_pricing_note'] ) ? sanitize_text_field( wp_unslash( $_POST['lnc_pricing_note'] ) ) : '';
	update_post_meta( $post_id, LNC_META_PRICING_NOTE, $note );

	// Features — drop empty rows.
	$features = [];
	if ( isset( $_POST['lnc_features'] ) && is_array( $_POST['lnc_features'] ) ) {
		foreach ( wp_unslash( $_POST['lnc_features'] ) as $feature ) {
			$feature = sanitize_text_field( $feature );
			if ( '' !== $feature ) {
				$features[] = $feature;
			}
		}
	}
	update_post_meta( $post_id, LNC_META_FEATURES, $features );
}
