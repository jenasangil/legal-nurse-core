/* Legal Nurse Core — Consultant Stories (chip filter; AJAX when paginated) */
( function () {
	'use strict';

	function initWidget( root ) {
		if ( ! root || root.dataset.lncPbcInit === '1' ) {
			return;
		}
		root.dataset.lncPbcInit = '1';

		var chips = root.querySelectorAll( '.lnc-pbc__chip' );
		var list  = root.querySelector( '.lnc-pbc__list' );
		var pager = root.querySelector( '.lnc-pbc__pagination' );
		var ajaxEl = root.querySelector( '.lnc-pbc__ajax' );

		var config = null;
		if ( ajaxEl ) {
			try { config = JSON.parse( ajaxEl.getAttribute( 'data-config' ) || 'null' ); } catch ( e ) { config = null; }
		}

		function setActiveChip( chip ) {
			for ( var j = 0; j < chips.length; j++ ) {
				chips[ j ].classList.remove( 'is-active' );
			}
			chip.classList.add( 'is-active' );
		}

		/* -------- Client-side filter (no pagination) -------- */
		function applyFilter( term ) {
			var items = list.querySelectorAll( '.lnc-pbc__item' );
			for ( var i = 0; i < items.length; i++ ) {
				var terms = ( items[ i ].getAttribute( 'data-terms' ) || '' ).split( /\s+/ );
				var show = ( term === 'all' ) || terms.indexOf( term ) !== -1;
				items[ i ].classList.toggle( 'is-hidden', ! show );
			}
		}

		/* -------- AJAX filter + pagination -------- */
		var state = { term: 'all', page: 1 };

		function fetchAjax() {
			if ( ! config || ! window.lncPbc || ! window.lncPbc.ajaxUrl ) {
				return;
			}
			root.classList.add( 'is-loading' );

			var body = new URLSearchParams();
			body.append( 'action', 'lnc_pbc' );
			body.append( 'nonce', config.nonce );
			body.append( 'taxonomy', config.taxonomy );
			body.append( 'term', state.term );
			body.append( 'page', state.page );
			body.append( 'per_page', config.perPage );
			body.append( 'orderby', config.orderby );
			body.append( 'order', config.order );
			body.append( 'field', config.field );
			body.append( 'show_more', config.showMore );
			body.append( 'more_label', config.moreLabel );
			body.append( 'icon_value', config.moreIcon ? config.moreIcon.value : '' );
			body.append( 'icon_library', config.moreIcon ? config.moreIcon.library : '' );
			( config.termIds || [] ).forEach( function ( id ) {
				body.append( 'term_ids[]', id );
			} );

			fetch( window.lncPbc.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: body.toString()
			} )
				.then( function ( r ) { return r.json(); } )
				.then( function ( res ) {
					if ( res && res.success ) {
						list.innerHTML = res.data.html || '';
						if ( pager ) {
							pager.innerHTML = res.data.pagination || '';
						}
					}
				} )
				.catch( function () {} )
				.finally( function () { root.classList.remove( 'is-loading' ); } );
		}

		// Chip clicks.
		for ( var c = 0; c < chips.length; c++ ) {
			chips[ c ].addEventListener( 'click', function () {
				setActiveChip( this );
				var term = this.getAttribute( 'data-term' ) || 'all';
				if ( config ) {
					state.term = term;
					state.page = 1; // new filter resets to page 1
					fetchAjax();
				} else {
					applyFilter( term );
				}
			} );
		}

		// Pagination clicks (AJAX mode) — delegated.
		if ( config && pager ) {
			pager.addEventListener( 'click', function ( e ) {
				var btn = e.target.closest( '.page-numbers[data-page]' );
				if ( ! btn ) {
					return;
				}
				var p = parseInt( btn.getAttribute( 'data-page' ), 10 );
				if ( p > 0 && p !== state.page ) {
					state.page = p;
					fetchAjax();
					root.scrollIntoView( { behavior: 'smooth', block: 'start' } );
				}
			} );
		}
	}

	function initAll( context ) {
		var scope = context || document;
		var roots = scope.querySelectorAll( '.lnc-pbc' );
		for ( var i = 0; i < roots.length; i++ ) {
			initWidget( roots[ i ] );
		}
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initAll( document );
	} );

	if ( window.jQuery ) {
		window.jQuery( window ).on( 'elementor/frontend/init', function () {
			if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
				window.elementorFrontend.hooks.addAction(
					'frontend/element_ready/lnc_pages_by_category.default',
					function ( $scope ) {
						initAll( $scope && $scope[ 0 ] ? $scope[ 0 ] : document );
					}
				);
			}
		} );
	}
} )();
