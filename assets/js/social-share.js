/* Legal Nurse Core — Social Share */
( function () {
	'use strict';

	function initShare( root ) {
		if ( root.dataset.lncInit === '1' ) {
			return;
		}
		root.dataset.lncInit = '1';

		var url   = encodeURIComponent( window.location.href );
		var title = encodeURIComponent( document.title );

		var links = {
			facebook:  'https://www.facebook.com/sharer/sharer.php?u=' + url,
			x:         'https://twitter.com/intent/tweet?text=' + title + '&url=' + url,
			linkedin:  'https://www.linkedin.com/sharing/share-offsite/?url=' + url,
			pinterest: 'https://pinterest.com/pin/create/button/?url=' + url + '&description=' + title
		};

		Object.keys( links ).forEach( function ( key ) {
			var el = root.querySelector( '.lnc-social-btn--' + key );
			if ( el ) {
				el.setAttribute( 'href', links[ key ] );
			}
		} );

		var copyBtn = root.querySelector( '.lnc-social-btn--copy' );
		if ( copyBtn ) {
			copyBtn.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				var msg = root.getAttribute( 'data-copied' ) || 'Link copied';
				var done = function () { showToast( copyBtn, msg ); };
				var fail = function () { showToast( copyBtn, 'Failed to copy' ); };

				if ( navigator.clipboard && navigator.clipboard.writeText ) {
					navigator.clipboard.writeText( window.location.href ).then( done ).catch( fail );
				} else {
					try {
						var ta = document.createElement( 'textarea' );
						ta.value = window.location.href;
						ta.style.position = 'fixed';
						ta.style.opacity = '0';
						document.body.appendChild( ta );
						ta.select();
						document.execCommand( 'copy' );
						document.body.removeChild( ta );
						done();
					} catch ( err ) {
						fail();
					}
				}
			} );
		}
	}

	function showToast( target, message ) {
		var existing = target.querySelector( '.lnc-copy-toast' );
		if ( existing ) {
			existing.remove();
		}

		var toast = document.createElement( 'div' );
		toast.className = 'lnc-copy-toast';
		toast.textContent = message;
		target.appendChild( toast );

		requestAnimationFrame( function () {
			toast.classList.add( 'is-visible' );
		} );

		setTimeout( function () {
			toast.classList.remove( 'is-visible' );
			setTimeout( function () { toast.remove(); }, 200 );
		}, 1800 );
	}

	function initAll( ctx ) {
		( ctx || document ).querySelectorAll( '.lnc-social-share' ).forEach( initShare );
	}

	if ( document.readyState !== 'loading' ) {
		initAll();
	} else {
		document.addEventListener( 'DOMContentLoaded', function () { initAll(); } );
	}

	if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/lnc_social_share.default', function ( $scope ) {
			initAll( $scope[ 0 ] );
		} );
	}
} )();
