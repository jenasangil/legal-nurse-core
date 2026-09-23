/* Legal Nurse Core — Mobile Product Bar
 * Moves each bar to appear right after its configured offset element. */
( function () {
	'use strict';

	function relocate( bar ) {
		var selector = bar.getAttribute( 'data-offset' );
		if ( ! selector ) {
			return;
		}
		var target;
		try {
			target = document.querySelector( selector );
		} catch ( e ) {
			return;
		}
		if ( target && target.parentNode ) {
			target.insertAdjacentElement( 'afterend', bar );
		}
	}

	function init() {
		var bars = document.querySelectorAll( '.lnc-mpbar[data-offset]' );
		for ( var i = 0; i < bars.length; i++ ) {
			relocate( bars[ i ] );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
