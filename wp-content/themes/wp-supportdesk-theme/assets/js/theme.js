(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var toggle = document.querySelector('.sd-nav-toggle');
		var nav = document.getElementById('site-navigation');

		if (!toggle || !nav) {
			return;
		}

		function setMenuOpen(isOpen) {
			nav.classList.toggle('is-open', isOpen);
			toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		}

		toggle.addEventListener('click', function () {
			var currentlyOpen = toggle.getAttribute('aria-expanded') === 'true';
			setMenuOpen(!currentlyOpen);
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && nav.classList.contains('is-open')) {
				setMenuOpen(false);
				toggle.focus();
			}
		});

		document.addEventListener('click', function (event) {
			if (nav.classList.contains('is-open') && !nav.contains(event.target) && !toggle.contains(event.target)) {
				setMenuOpen(false);
			}
		});
	});
})();
