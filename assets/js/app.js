/** Global, dependency-free Fahar initialization. */
(() => {
	'use strict';

	const init = () => {
		document.documentElement.classList.add('fahar-js');
	};

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init, { once: true });
	} else {
		init();
	}
})();
