(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var alertBox = document.querySelector('.wp-supportdesk-alert[data-autofocus="true"]');
		if (alertBox && typeof alertBox.focus === 'function') {
			alertBox.focus({ preventScroll: false });
		}

		var forms = document.querySelectorAll('.wp-supportdesk-form');

		forms.forEach(function (form) {
			var submitBtn = form.querySelector('.wp-supportdesk-submit');
			var fields = form.querySelectorAll('.wp-supportdesk-input, .wp-supportdesk-select, .wp-supportdesk-textarea');

			// Remove error highlight and aria-invalid as soon as the user updates the field.
			fields.forEach(function (input) {
				var clearFieldError = function () {
					var wrapper = input.closest('.wp-supportdesk-field');
					if (wrapper) {
						wrapper.classList.remove('wp-supportdesk-field--error');
					}
					input.removeAttribute('aria-invalid');
				};

				input.addEventListener('input', clearFieldError);
				input.addEventListener('change', clearFieldError);
			});

			// Prevent duplicate clicks while the form POST is in flight.
			form.addEventListener('submit', function () {
				if (submitBtn && !submitBtn.disabled) {
					submitBtn.disabled = true;
					var submittingText = submitBtn.getAttribute('data-submitting-text');
					if (submittingText) {
						submitBtn.textContent = submittingText;
					}
				}
			});
		});
	});
})();
