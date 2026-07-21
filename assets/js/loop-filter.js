/* Legal Nurse Core — Loop Filter (AJAX) */
( function () {
	'use strict';

	function initFilter( root ) {
		if ( root.dataset.lncInit === '1' ) {
			return;
		}
		root.dataset.lncInit = '1';

		var config;
		try {
			config = JSON.parse( root.getAttribute( 'data-config' ) || '{}' );
		} catch ( e ) {
			config = {};
		}

		if ( ! config.target ) {
			return;
		}

		var state = { term: 'all', sort: 'recent', page: 1 };

		var buttons  = root.querySelectorAll( '.lnc-loop-filter__btn' );
		var catSelect = root.querySelector( '.lnc-loop-filter__categories' );
		var sortSelect = root.querySelector( '.lnc-loop-filter__sort' );

		var paginationEl = null;

		function resolveContainer() {
			// 1) Explicit selector, if provided and present.
			if ( config.target ) {
				var target = document.querySelector( config.target );
				if ( target ) {
					return target.querySelector( '.elementor-loop-container' ) || target;
				}
			}

			// 2) Auto-detect the nearest Loop Grid relative to this filter widget.
			var node = root;
			while ( node && node !== document.body ) {
				var grid = node.querySelector( '.elementor-widget-loop-grid' );
				if ( grid ) {
					return grid.querySelector( '.elementor-loop-container' ) || grid;
				}
				node = node.parentElement;
			}

			// 3) Last resort: first Loop Grid on the page.
			return document.querySelector( '.elementor-widget-loop-grid .elementor-loop-container' )
				|| document.querySelector( '.elementor-loop-container' );
		}

		// The Loop Grid widget element (for placing/reusing its pagination).
		function resolveGrid() {
			if ( config.target ) {
				var t = document.querySelector( config.target );
				if ( t ) {
					return t.closest( '.elementor-widget-loop-grid' ) || t;
				}
			}
			var node = root;
			while ( node && node !== document.body ) {
				var grid = node.querySelector( '.elementor-widget-loop-grid' );
				if ( grid ) {
					return grid;
				}
				node = node.parentElement;
			}
			return document.querySelector( '.elementor-widget-loop-grid' );
		}

		// Reuse the grid's own .elementor-pagination node (so its styling applies),
		// or create one inside the grid's widget container if none exists yet.
		function ensurePagination( container ) {
			var grid = resolveGrid();
			var host = grid ? ( grid.querySelector( '.elementor-widget-container' ) || grid ) : ( container.parentNode );

			paginationEl = host.querySelector( '.elementor-pagination' );
			if ( ! paginationEl ) {
				paginationEl = document.createElement( 'nav' );
				paginationEl.className = 'elementor-pagination';
				host.appendChild( paginationEl );
			}
			return paginationEl;
		}

		function bindPaginationLinks( nav ) {
			nav.querySelectorAll( 'a.page-numbers' ).forEach( function ( link ) {
				link.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					var target;
					if ( link.classList.contains( 'next' ) ) {
						target = state.page + 1;
					} else if ( link.classList.contains( 'prev' ) ) {
						target = state.page - 1;
					} else {
						target = parseInt( link.textContent.replace( /\D/g, '' ), 10 );
					}
					if ( target && ! isNaN( target ) ) {
						goToPage( target );
					}
				} );
			} );
		}

		function renderPagination( container, paginationHtml ) {
			var nav = ensurePagination( container );
			nav.innerHTML = paginationHtml || '';
			if ( paginationHtml ) {
				bindPaginationLinks( nav );
			}
		}

		function goToPage( p ) {
			state.page = Math.max( 1, p );
			fetchPosts( true );
		}

		function setActiveButton( term ) {
			buttons.forEach( function ( btn ) {
				btn.classList.toggle( 'is-active', btn.getAttribute( 'data-term' ) === String( term ) );
			} );
		}

		function fetchPosts( scrollToGrid ) {
			var container = resolveContainer();
			if ( ! container ) {
				return;
			}

			root.classList.add( 'is-loading' );
			container.classList.add( 'lnc-loading' );

			var body = new URLSearchParams();
			body.append( 'action', 'lnc_loop_filter' );
			body.append( 'nonce', config.nonce );
			body.append( 'term', state.term );
			body.append( 'sort', state.sort );
			body.append( 'page', state.page );
			body.append( 'post_type', config.post_type || 'post' );
			body.append( 'taxonomy', config.taxonomy || 'category' );
			body.append( 'template', config.template || 0 );
			body.append( 'ppp', config.ppp || 6 );
			body.append( 'views_key', config.views_key || 'post_views_count' );
			( config.allowed || [] ).forEach( function ( id ) {
				body.append( 'allowed[]', id );
			} );

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

						// Let Elementor re-scan lazy images / widgets if present.
						if ( window.elementorFrontend && window.elementorFrontend.elementsHandler ) {
							try {
								window.dispatchEvent( new Event( 'resize' ) );
							} catch ( e ) {}
						}

						if ( scrollToGrid ) {
							var top = container.getBoundingClientRect().top + window.pageYOffset - 100;
							window.scrollTo( { top: top, behavior: 'smooth' } );
						}
					}
				} )
				.catch( function () {} )
				.finally( function () {
					root.classList.remove( 'is-loading' );
					container.classList.remove( 'lnc-loading' );
				} );
		}

		buttons.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				state.term = btn.getAttribute( 'data-term' );
				state.page = 1;
				setActiveButton( state.term );
				if ( catSelect ) {
					catSelect.value = state.term;
				}
				fetchPosts();
			} );
		} );

		if ( catSelect ) {
			catSelect.addEventListener( 'change', function () {
				state.term = catSelect.value;
				state.page = 1;
				setActiveButton( state.term );
				fetchPosts();
			} );
		}

		if ( sortSelect ) {
			sortSelect.addEventListener( 'change', function () {
				state.sort = sortSelect.value;
				state.page = 1;
				fetchPosts();
			} );
		}

		// Initial load so the grid is AJAX-driven and pagination renders from the start.
		fetchPosts();
	}

	function initAll( ctx ) {
		( ctx || document ).querySelectorAll( '.lnc-loop-filter' ).forEach( initFilter );
	}

	if ( document.readyState !== 'loading' ) {
		initAll();
	} else {
		document.addEventListener( 'DOMContentLoaded', function () { initAll(); } );
	}

	// Elementor editor preview support.
	if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/lnc_loop_filter.default', function ( $scope ) {
			initAll( $scope[ 0 ] );
		} );
	}
} )();
