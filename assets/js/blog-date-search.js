/* Legal Nurse Core — Blog Date Search (filters a Loop Grid by date range) */
( function () {
	'use strict';

	function init( root ) {
		if ( root.dataset.lncDsInit === '1' ) {
			return;
		}
		root.dataset.lncDsInit = '1';

		var config;
		try {
			config = JSON.parse( root.getAttribute( 'data-config' ) || '{}' );
		} catch ( e ) {
			config = {};
		}

		var startEl = root.querySelector( '.lnc-datesearch__start' );
		var endEl   = root.querySelector( '.lnc-datesearch__end' );
		var submit  = root.querySelector( '.lnc-datesearch__submit' );

		// Calendar icon buttons open the native picker for their field.
		root.querySelectorAll( '.lnc-datesearch__field' ).forEach( function ( field ) {
			var input = field.querySelector( 'input[type="date"]' );
			var icon  = field.querySelector( '.lnc-datesearch__icon' );
			if ( icon && input ) {
				icon.addEventListener( 'click', function () {
					if ( typeof input.showPicker === 'function' ) {
						try { input.showPicker(); return; } catch ( e ) {}
					}
					input.focus();
				} );
			}
		} );

		function resolveContainer() {
			if ( config.target ) {
				var t = document.querySelector( config.target );
				if ( t ) {
					return t.querySelector( '.elementor-loop-container' ) || t;
				}
			}
			var node = root;
			while ( node && node !== document.body ) {
				var grid = node.querySelector( '.elementor-widget-loop-grid' );
				if ( grid ) {
					return grid.querySelector( '.elementor-loop-container' ) || grid;
				}
				node = node.parentElement;
			}
			return document.querySelector( '.elementor-widget-loop-grid .elementor-loop-container' )
				|| document.querySelector( '.elementor-loop-container' );
		}

		function renderPagination( container, html ) {
			var grid = config.target ? document.querySelector( config.target ) : null;
			var host = grid ? grid.closest( '.elementor-widget-loop-grid' ) : container.closest( '.elementor-widget-loop-grid' );
			host = host ? ( host.querySelector( '.elementor-widget-container' ) || host ) : container.parentNode;
			var nav = host.querySelector( '.elementor-pagination' );
			if ( ! html ) {
				if ( nav ) { nav.innerHTML = ''; }
				return;
			}
			if ( ! nav ) {
				nav = document.createElement( 'nav' );
				nav.className = 'elementor-pagination';
				host.appendChild( nav );
			}
			nav.innerHTML = html;
		}

		function fetchPosts( page ) {
			if ( ! window.lncLoopFilter || ! window.lncLoopFilter.ajaxUrl ) {
				return;
			}
			var container = resolveContainer();
			if ( ! container ) {
				return;
			}
			root.classList.add( 'is-loading' );
			container.classList.add( 'lnc-loading' );

			var body = new URLSearchParams();
			body.append( 'action', 'lnc_loop_filter' );
			body.append( 'nonce', config.nonce );
			body.append( 'term', 'all' );
			body.append( 'sort', 'recent' );
			body.append( 'page', page || 1 );
			body.append( 'post_type', config.post_type || 'post' );
			body.append( 'taxonomy', config.taxonomy || 'category' );
			body.append( 'template', config.template || 0 );
			body.append( 'ppp', config.ppp || 12 );
			body.append( 'date_start', startEl && startEl.value ? startEl.value : '' );
			body.append( 'date_end', endEl && endEl.value ? endEl.value : '' );

			fetch( window.lncLoopFilter.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: body.toString()
			} )
				.then( function ( r ) { return r.json(); } )
				.then( function ( res ) {
					if ( res && res.success ) {
						container.innerHTML = res.data.empty
							? '<div class="lnc-loop-empty">' + ( config.emptyText || 'No posts found.' ) + '</div>'
							: res.data.html;
						renderPagination( container, res.data.pagination || '' );
						bindPagination( container );
					}
				} )
				.catch( function () {} )
				.finally( function () {
					root.classList.remove( 'is-loading' );
					container.classList.remove( 'lnc-loading' );
				} );
		}

		function bindPagination( container ) {
			var grid = config.target ? document.querySelector( config.target ) : container.closest( '.elementor-widget-loop-grid' );
			var host = grid ? ( grid.closest( '.elementor-widget-loop-grid' ) || grid ) : container.parentNode;
			var nav  = host ? host.querySelector( '.elementor-pagination' ) : null;
			if ( ! nav ) {
				return;
			}
			nav.querySelectorAll( 'a.page-numbers' ).forEach( function ( link ) {
				link.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					var text = link.textContent.replace( /[^0-9]/g, '' );
					var page = parseInt( text, 10 );
					if ( link.classList.contains( 'next' ) || link.classList.contains( 'prev' ) ) {
						var cur = nav.querySelector( '.page-numbers.current' );
						var curPage = cur ? parseInt( cur.textContent.replace( /[^0-9]/g, '' ), 10 ) : 1;
						page = link.classList.contains( 'next' ) ? curPage + 1 : curPage - 1;
					}
					if ( page > 0 ) {
						fetchPosts( page );
					}
				} );
			} );
		}

		if ( submit ) {
			submit.addEventListener( 'click', function () {
				fetchPosts( 1 );
			} );
		}
	}

	function initAll( ctx ) {
		( ctx || document ).querySelectorAll( '.lnc-datesearch' ).forEach( init );
	}

	if ( document.readyState !== 'loading' ) {
		initAll();
	} else {
		document.addEventListener( 'DOMContentLoaded', function () { initAll(); } );
	}

	if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/lnc_blog_date_search.default', function ( $scope ) {
			initAll( $scope[ 0 ] );
		} );
	}
} )();
