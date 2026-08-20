/* Legal Nurse Core — Memorable Cases "View More" toggle */
( function () {
	'use strict';

	function initWidget( scope ) {
		var btn = scope.querySelector( '.lnc-cases__more-btn' );
		var grid = scope.querySelector( '.lnc-cases' );
		if ( ! btn || ! grid || btn.dataset.lncInit === '1' ) {
			return;
		}
		btn.dataset.lncInit = '1';

		btn.addEventListener( 'click', function () {
			var expanded = grid.classList.toggle( 'is-expanded' );
			var hidden = grid.querySelectorAll( '.lnc-case--hidden' );
			for ( var i = 0; i < hidden.length; i++ ) {
				if ( expanded ) {
					hidden[ i ].removeAttribute( 'hidden' );
				} else {
					hidden[ i ].setAttribute( 'hidden', '' );
				}
			}
			btn.textContent = expanded
				? ( btn.getAttribute( 'data-less' ) || 'View Less' )
				: ( btn.getAttribute( 'data-more' ) || 'View More' );
		} );
	}

	function initAll( ctx ) {
		var scope = ctx || document;
		var btns = scope.querySelectorAll( '.lnc-cases__more' );
		for ( var i = 0; i < btns.length; i++ ) {
			initWidget( btns[ i ].parentNode || document );
		}
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initAll( document );
	} );

	if ( window.jQuery ) {
		window.jQuery( window ).on( 'elementor/frontend/init', function () {
			if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
				window.elementorFrontend.hooks.addAction(
					'frontend/element_ready/lnc_memorable_cases.default',
					function ( $scope ) {
						initWidget( $scope && $scope[ 0 ] ? $scope[ 0 ] : document );
					}
				);
			}
		} );
	}
} )();
