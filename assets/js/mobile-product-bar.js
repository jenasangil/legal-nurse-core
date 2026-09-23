/* Legal Nurse Core — Mobile Product Bar
 * Sticky bottom bar that slides in once the user scrolls past the
 * configured trigger element (data-offset, e.g. .hero-section). */
( function () {
	'use strict';

	function setup( bar ) {
		var selector = bar.getAttribute( 'data-offset' );
		var trigger = null;
		if ( selector ) {
			try {
				trigger = document.querySelector( selector );
			} catch ( e ) {
				trigger = null;
			}
		}

		function update() {
			var show;
			if ( trigger ) {
				// Show once the trigger element has scrolled above the viewport.
				show = trigger.getBoundingClientRect().bottom <= 0;
			} else {
				// Fallback: show after a bit of scrolling.
				show = ( window.pageYOffset || document.documentElement.scrollTop || 0 ) > 300;
			}
			bar.classList.toggle( 'is-visible', show );
		}

		var ticking = false;
		function onScroll() {
			if ( ! ticking ) {
				window.requestAnimationFrame( function () {
					update();
					ticking = false;
				} );
				ticking = true;
			}
		}

		window.addEventListener( 'scroll', onScroll, { passive: true } );
		window.addEventListener( 'resize', update );
		update();
	}

	function init() {
		var bars = document.querySelectorAll( '.lnc-mpbar' );
		for ( var i = 0; i < bars.length; i++ ) {
			setup( bars[ i ] );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
