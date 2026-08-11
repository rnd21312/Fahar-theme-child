/** Search UI scaffold. No AJAX or live-query behavior is implemented. */
(() => {
	'use strict';

	document.addEventListener('DOMContentLoaded', () => {
		document.querySelectorAll('[data-fahar-search]').forEach((region) => {
			region.dataset.faharReady = 'true';
		});
	}, { once: true });
})();
