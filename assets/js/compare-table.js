/* Legal Nurse Core — Comparison Table sticky header.
 *
 * The table is a CSS grid of divs (not a <table>), so we clone the header row
 * into a position:fixed element that appears once the real header scrolls past
 * the offset. Grid `fr` columns re-align automatically when the clone matches
 * the table's width, so per-column measuring isn't needed. */
( function () {
	'use strict';

	function initSticky( root ) {
		if ( root.dataset.lncStickyInit === '1' ) {
			return;
		}
		root.dataset.lncStickyInit = '1';

		var head = root.querySelector( '.lnc-ct__head' );
		if ( ! head ) {
			return;
		}

		var offsetDesktop = parseInt( root.getAttribute( 'data-sticky-top' ) || '0', 10 );
		var offsetMobile  = parseInt( root.getAttribute( 'data-sticky-top-mobile' ) || String( offsetDesktop ), 10 );

		// Build the fixed clone: a mini .lnc-ct that holds only the header row.
		var clone = document.createElement( 'div' );
		clone.className = 'lnc-ct lnc-ct--stickyclone';
		clone.setAttribute( 'aria-hidden', 'true' );
		clone.style.position = 'fixed';
		clone.style.zIndex = '500';
		clone.style.display = 'none';
		clone.style.overflow = 'hidden';
		clone.style.margin = '0';
		clone.style.pointerEvents = 'none';
		clone.style.setProperty( '--lnc-ct-cols', getComputedStyle( root ).getPropertyValue( '--lnc-ct-cols' ) );
		clone.appendChild( head.cloneNode( true ) );
		document.body.appendChild( clone );

		function currentOffset() {
			return window.innerWidth < 768 ? offsetMobile : offsetDesktop;
		}

		function sync() {
			var rect = root.getBoundingClientRect();
			clone.style.width = rect.width + 'px';
			clone.style.left = rect.left + 'px';
			clone.style.top = currentOffset() + 'px';
		}

		function onScroll() {
			var rect    = root.getBoundingClientRect();
			var headH   = head.offsetHeight || 60;
			var off     = currentOffset();
			// Stick once the real header passes the offset line; release when the
			// table's bottom reaches it (so it never floats past the table).
			var stick = rect.top < off && rect.bottom > ( off + headH );

			if ( stick ) {
				sync();
				clone.style.display = 'block';
			} else {
				clone.style.display = 'none';
			}
		}

		sync();
		onScroll();

		window.addEventListener( 'scroll', onScroll, { passive: true } );
		window.addEventListener( 'resize', function () { sync(); onScroll(); } );

		// Column widths depend on rendered fonts/images; re-sync shortly after load.
		window.addEventListener( 'load', function () { sync(); onScroll(); } );
		setTimeout( function () { sync(); onScroll(); }, 400 );
	}

	function initAll( ctx ) {
		( ctx || document ).querySelectorAll( '.lnc-ct--sticky' ).forEach( initSticky );
	}

	if ( document.readyState !== 'loading' ) {
		initAll();
	} else {
		document.addEventListener( 'DOMContentLoaded', function () { initAll(); } );
	}

	if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/lnc_compare_table.default', function ( $scope ) {
			initAll( $scope[ 0 ] );
		} );
	}
} )();
