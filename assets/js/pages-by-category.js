/* Legal Nurse Core — Pages by Category chip filtering */
( function () {
	'use strict';

	function initWidget( root ) {
		if ( ! root || root.dataset.lncPbcInit === '1' ) {
			return;
		}
		root.dataset.lncPbcInit = '1';

		var chips = root.querySelectorAll( '.lnc-pbc__chip' );
		var items = root.querySelectorAll( '.lnc-pbc__item' );

		function applyFilter( term ) {
			for ( var i = 0; i < items.length; i++ ) {
				var item = items[ i ];
				var terms = ( item.getAttribute( 'data-terms' ) || '' ).split( /\s+/ );
				var show = ( term === 'all' ) || terms.indexOf( term ) !== -1;
				item.classList.toggle( 'is-hidden', ! show );
			}
		}

		for ( var c = 0; c < chips.length; c++ ) {
			chips[ c ].addEventListener( 'click', function () {
				for ( var j = 0; j < chips.length; j++ ) {
					chips[ j ].classList.remove( 'is-active' );
				}
				this.classList.add( 'is-active' );
				applyFilter( this.getAttribute( 'data-term' ) || 'all' );
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

	// Elementor frontend (editor preview + optimized front end).
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
