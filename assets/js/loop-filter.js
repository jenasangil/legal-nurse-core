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

		function resolveContainer() {
			var target = document.querySelector( config.target );
			if ( ! target ) {
				return null;
			}
			// Elementor Loop Grid inner container; fall back to the target itself.
			return target.querySelector( '.elementor-loop-container' ) || target;
		}

		function setActiveButton( term ) {
			buttons.forEach( function ( btn ) {
				btn.classList.toggle( 'is-active', btn.getAttribute( 'data-term' ) === String( term ) );
			} );
		}

		function fetchPosts() {
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
						// Let Elementor re-scan lazy images / widgets if present.
						if ( window.elementorFrontend && window.elementorFrontend.elementsHandler ) {
							try {
								window.dispatchEvent( new Event( 'resize' ) );
							} catch ( e ) {}
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
