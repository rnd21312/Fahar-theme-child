/** Navigation lifecycle scaffold; behavior will be added with the app shell. */
(() => {
	'use strict';

	const init = () => {
		document.querySelectorAll('[data-fahar-navigation]').forEach((navigation) => {
			navigation.dataset.faharReady = 'true';
		});
	};

	document.addEventListener('DOMContentLoaded', init, { once: true });
})();
