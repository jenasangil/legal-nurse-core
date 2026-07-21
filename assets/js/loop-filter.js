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

		function ensurePagination( container ) {
			if ( paginationEl && paginationEl.isConnected ) {
				return paginationEl;
			}
			paginationEl = document.createElement( 'nav' );
			paginationEl.className = 'lnc-loop-pagination';
			paginationEl.setAttribute( 'aria-label', 'Pagination' );
			container.parentNode.insertBefore( paginationEl, container.nextSibling );
			return paginationEl;
		}

		function pageNumbers( current, max ) {
			// Windowed list: 1 … c-1 c c+1 … max
			var pages = [];
			var add = function ( p ) { if ( pages.indexOf( p ) === -1 ) { pages.push( p ); } };
			add( 1 );
			for ( var p = current - 1; p <= current + 1; p++ ) {
				if ( p > 1 && p < max ) { add( p ); }
			}
			add( max );
			pages.sort( function ( a, b ) { return a - b; } );
			// Insert ellipsis markers.
			var out = [];
			for ( var i = 0; i < pages.length; i++ ) {
				if ( i > 0 && pages[ i ] - pages[ i - 1 ] > 1 ) {
					out.push( '…' );
				}
				out.push( pages[ i ] );
			}
			return out;
		}

		function renderPagination( container, maxPages ) {
			if ( ! config.pagination ) {
				return;
			}
			var nav = ensurePagination( container );
			nav.innerHTML = '';

			if ( maxPages <= 1 ) {
				return;
			}

			var frag = document.createDocumentFragment();

			var prev = document.createElement( 'button' );
			prev.type = 'button';
			prev.className = 'lnc-loop-pagination__item lnc-loop-pagination__prev';
			prev.textContent = config.prevLabel || 'Prev';
			prev.disabled = state.page <= 1;
			prev.addEventListener( 'click', function () { goToPage( state.page - 1 ); } );
			frag.appendChild( prev );

			pageNumbers( state.page, maxPages ).forEach( function ( p ) {
				if ( p === '…' ) {
					var gap = document.createElement( 'span' );
					gap.className = 'lnc-loop-pagination__ellipsis';
					gap.textContent = '…';
					frag.appendChild( gap );
					return;
				}
				var btn = document.createElement( 'button' );
				btn.type = 'button';
				btn.className = 'lnc-loop-pagination__item' + ( p === state.page ? ' is-active' : '' );
				btn.textContent = p;
				btn.addEventListener( 'click', function () { goToPage( p ); } );
				frag.appendChild( btn );
			} );

			var next = document.createElement( 'button' );
			next.type = 'button';
			next.className = 'lnc-loop-pagination__item lnc-loop-pagination__next';
			next.textContent = config.nextLabel || 'Next';
			next.disabled = state.page >= maxPages;
			next.addEventListener( 'click', function () { goToPage( state.page + 1 ); } );
			frag.appendChild( next );

			nav.appendChild( frag );
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

						renderPagination( container, res.data.maxPages || 1 );

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
		if ( config.pagination ) {
			fetchPosts();
		}
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
