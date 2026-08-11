/** Progressive masonry, filtering, loading, and return-state behavior for Fahar Explore. */
(() => {
	'use strict';

	const gridSelector = '[data-fahar-masonry]';
	const cardSelector = '.fahar-portfolio-card';
	const exploreSelector = '[data-fahar-explore]';
	const detailLinkSelector = '[data-fahar-portfolio-detail]';
	const filterRootSelector = '[data-fahar-filter-root]';
	const searchRootSelector = '[data-fahar-search-suggest]';
	const infiniteFeedSelector = '[data-fahar-infinite-feed]';
	const focusableSelector = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), summary, [tabindex]:not([tabindex="-1"])';
	const storageKey = 'faharExploreReturnState:v1';
	const stateMaxAge = 30 * 60 * 1000;
	const initializedGrids = new WeakSet();
	const resizeObservers = new WeakMap();
	const masonryControllers = new WeakMap();

	const getCards = (grid) => Array.from(grid.children).filter((child) => child.matches(cardSelector));

	const removeStoredState = () => {
		try {
			window.sessionStorage.removeItem(storageKey);
		} catch (error) {
			// Storage can be unavailable without affecting normal navigation.
		}
	};

	const readStoredState = () => {
		try {
			const stored = window.sessionStorage.getItem(storageKey);
			return stored ? JSON.parse(stored) : null;
		} catch (error) {
			removeStoredState();
			return null;
		}
	};

	const writeStoredState = (state) => {
		try {
			window.sessionStorage.setItem(storageKey, JSON.stringify(state));
			return true;
		} catch (error) {
			return false;
		}
	};

	const parseStoredState = (state) => {
		if (
			!state
			|| typeof state !== 'object'
			|| typeof state.returnUrl !== 'string'
			|| !state.returnUrl.startsWith('/')
			|| state.returnUrl.startsWith('//')
			|| !Number.isFinite(state.scrollY)
			|| state.scrollY < 0
			|| !Number.isInteger(state.portfolioId)
			|| state.portfolioId < 1
			|| !Number.isFinite(state.cardViewportOffset)
			|| !Number.isFinite(state.timestamp)
			|| typeof state.restoreRequested !== 'boolean'
			|| (undefined !== state.loadedPage && (!Number.isInteger(state.loadedPage) || state.loadedPage < 1))
			|| state.timestamp > Date.now()
			|| Date.now() - state.timestamp > stateMaxAge
		) {
			return null;
		}

		try {
			const returnUrl = new URL(state.returnUrl, window.location.origin);
			if (returnUrl.origin !== window.location.origin) {
				return null;
			}

			state.loadedPage = Number.isInteger(state.loadedPage) ? state.loadedPage : 1;
			return { state, returnUrl };
		} catch (error) {
			return null;
		}
	};

	const captureDeparture = (explore, event) => {
		if (
			event.defaultPrevented
			|| event.button !== 0
			|| event.metaKey
			|| event.ctrlKey
			|| event.shiftKey
			|| event.altKey
			|| !(event.target instanceof Element)
		) {
			return;
		}

		const link = event.target.closest(detailLinkSelector);

		if (!link || !explore.contains(link)) {
			return;
		}

		const card = link.closest('[data-fahar-portfolio-id]');
		const rawPortfolioId = card ? card.dataset.faharPortfolioId : '';

		if (!card || !/^[1-9]\d*$/.test(rawPortfolioId)) {
			return;
		}

		let destination;

		try {
			destination = new URL(link.href, window.location.href);
		} catch (error) {
			return;
		}

		if (destination.origin !== window.location.origin) {
			return;
		}

		writeStoredState({
			returnUrl: `${window.location.pathname}${window.location.search}${window.location.hash}`,
			scrollY: Math.max(0, window.scrollY),
			portfolioId: Number.parseInt(rawPortfolioId, 10),
			cardViewportOffset: card.getBoundingClientRect().top,
			timestamp: Date.now(),
			restoreRequested: false,
			loadedPage: Number.parseInt(card.dataset.faharPortfolioPage || '1', 10),
		});
	};

	const restoreExplorePosition = async (explore, infiniteFeed) => {
		const parsed = parseStoredState(readStoredState());

		if (!parsed) {
			removeStoredState();
			return;
		}

		const { state, returnUrl } = parsed;

		if (!state.restoreRequested) {
			return;
		}

		if (returnUrl.pathname !== window.location.pathname || returnUrl.search !== window.location.search) {
			removeStoredState();
			return;
		}

		state.restoreRequested = false;

		if (!writeStoredState(state)) {
			removeStoredState();
		}

		let cancelled = false;
		let finished = false;
		const cancelEvents = ['wheel', 'touchstart', 'pointerdown', 'keydown'];
		const cancelRestoration = () => {
			cancelled = true;
			cancelEvents.forEach((eventName) => window.removeEventListener(eventName, cancelRestoration));
		};
		const finishRestoration = () => {
			finished = true;
			cancelEvents.forEach((eventName) => window.removeEventListener(eventName, cancelRestoration));
		};

		cancelEvents.forEach((eventName) => window.addEventListener(eventName, cancelRestoration, { passive: true }));

		const restore = (isFinalAttempt) => {
			if (cancelled || finished) {
				return;
			}

			const card = explore.querySelector(`[data-fahar-portfolio-id="${state.portfolioId}"]`);
			const documentHeight = Math.max(
				document.documentElement.scrollHeight,
				document.body ? document.body.scrollHeight : 0,
			);
			const maxScroll = Math.max(0, documentHeight - window.innerHeight);
			const desiredScroll = card
				? window.scrollY + card.getBoundingClientRect().top - state.cardViewportOffset
				: state.scrollY;
			const clampedScroll = Math.max(0, Math.min(desiredScroll, maxScroll));

			if (Number.isFinite(clampedScroll)) {
				window.scrollTo({ top: clampedScroll, behavior: 'auto' });
			}

			if (isFinalAttempt) {
				finishRestoration();
			}
		};

		const scheduleRestore = (isFinalAttempt) => {
			window.requestAnimationFrame(() => {
				window.requestAnimationFrame(() => restore(isFinalAttempt));
			});
		};

		if (infiniteFeed && state.loadedPage > infiniteFeed.getCurrentPage()) {
			await infiniteFeed.loadThroughPage(state.loadedPage, () => !cancelled);
		}

		if (cancelled) {
			return;
		}

		if (document.readyState === 'complete') {
			scheduleRestore(true);
		} else {
			scheduleRestore(false);
			window.addEventListener('load', () => scheduleRestore(true), { once: true });
		}
	};

	const initializeGrid = (grid) => {
		if (initializedGrids.has(grid)) {
			return masonryControllers.get(grid) || null;
		}

		const cards = getCards(grid);

		if (!cards.length) {
			return;
		}

		grid.classList.add('is-masonry');

		const initialStyles = window.getComputedStyle(grid);
		const initialRowSize = Number.parseFloat(initialStyles.gridAutoRows);

		if (!Number.isFinite(initialRowSize) || initialRowSize <= 0) {
			grid.classList.remove('is-masonry');
			return;
		}

		initializedGrids.add(grid);

		let frameId = 0;
		let layoutAll = false;
		const pendingCards = new Set();

		const layout = () => {
			frameId = 0;

			const styles = window.getComputedStyle(grid);
			const rowSize = Number.parseFloat(styles.gridAutoRows);
			const rowGap = Number.parseFloat(styles.rowGap) || 0;

			if (!Number.isFinite(rowSize) || rowSize <= 0) {
				return;
			}

			const cardsToLayout = layoutAll ? getCards(grid) : Array.from(pendingCards);
			layoutAll = false;
			pendingCards.clear();
			const measurements = cardsToLayout.map((card) => ({
				card,
				height: card.getBoundingClientRect().height,
			}));

			measurements.forEach(({ card, height }) => {
				if (!Number.isFinite(height) || height <= 0) {
					card.style.removeProperty('grid-row-end');
					return;
				}

				const span = Math.max(1, Math.ceil((height + rowGap) / (rowSize + rowGap)));
				card.style.gridRowEnd = `span ${span}`;
			});
		};

		const scheduleLayout = (cardsToLayout = null) => {
			if (cardsToLayout) {
				cardsToLayout.forEach((card) => pendingCards.add(card));
			} else {
				layoutAll = true;
			}

			if (!frameId) {
				frameId = window.requestAnimationFrame(layout);
			}
		};

		let resizeObserver = null;
		let gridInlineSize = grid.getBoundingClientRect().width;

		if ('ResizeObserver' in window) {
			resizeObserver = new window.ResizeObserver((entries) => {
				const gridEntry = entries.find((entry) => entry.target === grid);

				if (gridEntry) {
					const nextInlineSize = grid.getBoundingClientRect().width;

					if (Math.abs(nextInlineSize - gridInlineSize) >= 0.5) {
						gridInlineSize = nextInlineSize;
						scheduleLayout();
						return;
					}
				}

				const resizedCards = entries
					.map((entry) => entry.target)
					.filter((target) => target !== grid);

				if (resizedCards.length) {
					scheduleLayout(resizedCards);
				}
			});
			resizeObserver.observe(grid);
			resizeObservers.set(grid, resizeObserver);
		} else {
			let resizeTimer = 0;

			window.addEventListener('resize', () => {
				window.clearTimeout(resizeTimer);
				resizeTimer = window.setTimeout(scheduleLayout, 120);
			}, { passive: true });
		}

		const registerCards = (newCards) => {
			const validCards = Array.from(newCards).filter((card) => card.matches(cardSelector));

			validCards.forEach((card) => {
				card.querySelectorAll('img').forEach((image) => {
					if (!image.complete) {
						image.addEventListener('load', () => scheduleLayout([card]), { once: true });
						image.addEventListener('error', () => scheduleLayout([card]), { once: true });
					}
				});

				if (resizeObserver) {
					resizeObserver.observe(card);
				}
			});

			scheduleLayout(validCards);
		};

		const controller = { registerCards, scheduleLayout };
		masonryControllers.set(grid, controller);
		registerCards(cards);
		return controller;
	};

	const initializeInfiniteFeed = (explore) => {
		const feed = explore.querySelector(infiniteFeedSelector);
		const grid = feed ? feed.querySelector(gridSelector) : null;
		const controls = feed ? feed.querySelector('[data-fahar-load-controls]') : null;
		const link = controls ? controls.querySelector('[data-fahar-load-more]') : null;

		if (!feed || !grid || !controls) {
			return null;
		}

		const masonry = initializeGrid(grid);
		const status = controls.querySelector('[data-fahar-load-status]');
		const initialLocation = window.location.href;
		const loadMoreLabel = link ? link.textContent : '';
		const loadedIds = new Set();
		let currentPage = Number.parseInt(feed.dataset.currentPage || '1', 10);
		let nextUrl = link ? link.href : '';
		let inFlight = null;
		let controller = null;
		let observer = null;
		let hasMore = Boolean(nextUrl);

		const registerPageCards = (cards, page) => {
			cards.forEach((card) => {
				card.dataset.faharPortfolioPage = String(page);
				const portfolioId = card.dataset.faharPortfolioId;

				if (portfolioId) {
					loadedIds.add(portfolioId);
				}
			});
		};

		registerPageCards(getCards(grid), currentPage);

		const setStatus = (message) => {
			if (status) {
				status.textContent = message;
			}
		};

		const stop = () => {
			hasMore = false;
			nextUrl = '';
			feed.classList.remove('is-loading', 'has-error');
			feed.classList.add('is-complete');
			if (link) {
				link.hidden = true;
			}
			if (observer) {
				observer.disconnect();
			}
			setStatus(feed.dataset.endMessage || '');
		};

		const requestNextPage = async () => {
			if (!hasMore || !nextUrl || window.location.href !== initialLocation) {
				return false;
			}

			const requestUrl = new URL(nextUrl, window.location.href);
			requestUrl.searchParams.set('fahar_explore_partial', '1');
			controller = new window.AbortController();
			feed.classList.remove('has-error');
			feed.classList.add('is-loading');
			setStatus(feed.dataset.loadingMessage || '');
			if (observer) {
				observer.unobserve(controls);
			}
			if (link) {
				link.setAttribute('aria-disabled', 'true');
			}

			try {
				const response = await window.fetch(requestUrl.href, {
					credentials: 'same-origin',
					headers: { Accept: 'application/json' },
					signal: controller.signal,
				});

				if (!response.ok) {
					throw new Error(`Explore request failed with ${response.status}`);
				}

				const payload = await response.json();
				const page = Number.parseInt(payload.page, 10);

				if (
					window.location.href !== initialLocation
					|| !Number.isInteger(page)
					|| page !== currentPage + 1
					|| typeof payload.html !== 'string'
				) {
					throw new Error('Invalid Explore response');
				}

				const template = document.createElement('template');
				template.innerHTML = payload.html.trim();
				const cards = Array.from(template.content.children).filter((card) => {
					const portfolioId = card.dataset.faharPortfolioId;
					return card.matches(cardSelector) && portfolioId && !loadedIds.has(portfolioId);
				});

				currentPage = page;
				feed.dataset.currentPage = String(page);
				nextUrl = 'string' === typeof payload.next_url ? payload.next_url : '';
				hasMore = Boolean(payload.has_more && nextUrl);

				if (hasMore) {
					const parsedNextUrl = new URL(nextUrl, window.location.href);

					if (parsedNextUrl.origin !== window.location.origin) {
						throw new Error('Invalid Explore next URL');
					}

					nextUrl = parsedNextUrl.href;
				}

				if (cards.length) {
					registerPageCards(cards, page);
					const fragment = document.createDocumentFragment();
					cards.forEach((card) => fragment.append(card));
					grid.append(fragment);
					if (masonry) {
						masonry.registerCards(cards);
					}
				}

				setStatus((feed.dataset.loadedMessage || '%d').replace('%d', String(cards.length)));

				if (!hasMore) {
					stop();
				} else if (link) {
					link.href = nextUrl;
					link.textContent = loadMoreLabel;
				}

				return true;
			} catch (error) {
				if ('AbortError' === error.name) {
					return false;
				}

				feed.classList.add('has-error');
				setStatus(feed.dataset.errorMessage || '');
				if (link) {
					link.hidden = false;
					link.textContent = feed.dataset.retryLabel || link.textContent;
				}
				return false;
			} finally {
				feed.classList.remove('is-loading');
				if (link) {
					link.removeAttribute('aria-disabled');
				}
				controller = null;
			}
		};

		const loadNextPage = () => {
			if (inFlight) {
				return inFlight;
			}

			inFlight = requestNextPage().finally(() => {
				inFlight = null;
				if (observer && hasMore && !feed.classList.contains('has-error')) {
					observer.observe(controls);
				}
			});
			return inFlight;
		};

		const loadThroughPage = async (targetPage, shouldContinue) => {
			while (currentPage < targetPage && hasMore && shouldContinue()) {
				const loaded = await loadNextPage();
				if (!loaded) {
					break;
				}
			}
		};

		const abortPendingRequest = () => {
			if (controller) {
				controller.abort();
			}
		};

		explore.addEventListener('click', (event) => {
			if (event.defaultPrevented || !(event.target instanceof Element)) {
				return;
			}

			const navigationLink = event.target.closest('a[href]');

			if (navigationLink && navigationLink !== link) {
				abortPendingRequest();
			}
		});
		explore.addEventListener('submit', abortPendingRequest);
		window.addEventListener('pagehide', abortPendingRequest, { once: true });

		if (link) {
			feed.classList.add('is-enhanced');
			link.addEventListener('click', (event) => {
				event.preventDefault();
				feed.classList.remove('has-error');
				loadNextPage();
			});

			if ('IntersectionObserver' in window) {
				feed.classList.add('is-automatic');
				observer = new window.IntersectionObserver((entries) => {
					if (entries.some((entry) => entry.isIntersecting)) {
						loadNextPage();
					}
				}, { rootMargin: '800px 0px' });
				observer.observe(controls);
			}
		}

		return {
			getCurrentPage: () => currentPage,
			loadThroughPage,
		};
	};

	const initializeRealtimeSearch = (root) => {
		const input = root.querySelector('[data-fahar-search-input]');
		const listbox = root.querySelector('[data-fahar-search-listbox]');
		const status = root.querySelector('[data-fahar-search-status]');
		const endpoint = root.dataset.endpoint;

		if (!input || !listbox || !status || !endpoint) {
			return;
		}

		let debounceTimer = 0;
		let controller = null;
		let requestSequence = 0;
		let activeIndex = -1;
		let options = [];

		const closeList = () => {
			activeIndex = -1;
			options = [];
			listbox.replaceChildren();
			listbox.hidden = true;
			input.setAttribute('aria-expanded', 'false');
			input.removeAttribute('aria-activedescendant');
		};

		const setStatus = (message = '') => {
			status.textContent = message;
		};

		const setActiveOption = (nextIndex) => {
			if (!options.length) {
				return;
			}

			activeIndex = (nextIndex + options.length) % options.length;

			options.forEach((option, index) => {
				option.setAttribute('aria-selected', String(index === activeIndex));
			});

			input.setAttribute('aria-activedescendant', options[activeIndex].id);
			options[activeIndex].scrollIntoView({ block: 'nearest' });
		};

		const renderResults = (items) => {
			closeList();

			items.forEach((item, index) => {
				if (!item || typeof item.title !== 'string' || typeof item.url !== 'string') {
					return;
				}

				let destination;

				try {
					destination = new URL(item.url, window.location.href);
				} catch (error) {
					return;
				}

				if (!['http:', 'https:'].includes(destination.protocol)) {
					return;
				}

				const option = document.createElement('li');
				const link = document.createElement('a');
				const visual = document.createElement('span');
				const text = document.createElement('span');
				const title = document.createElement('span');

				option.className = 'fahar-search-suggestion';
				option.setAttribute('role', 'none');
				link.className = 'fahar-search-suggestion__link';
				link.href = destination.href;
				link.tabIndex = -1;
				link.id = `${listbox.id}-option-${index}`;
				link.setAttribute('role', 'option');
				link.setAttribute('aria-selected', 'false');
				text.className = 'fahar-search-suggestion__text';
				title.className = 'fahar-search-suggestion__title';
				title.textContent = item.title;
				text.append(title);

				if (item.thumbnail && typeof item.thumbnail.url === 'string') {
					const image = document.createElement('img');
					image.className = 'fahar-search-suggestion__thumbnail';
					image.src = item.thumbnail.url;
					image.alt = typeof item.thumbnail.alt === 'string' ? item.thumbnail.alt : '';
					image.loading = 'lazy';
					image.decoding = 'async';
					if (Number.isInteger(item.thumbnail.width)) {
						image.width = item.thumbnail.width;
					}
					if (Number.isInteger(item.thumbnail.height)) {
						image.height = item.thumbnail.height;
					}
					visual.append(image);
				} else {
					const placeholder = document.createElement('span');
					placeholder.className = 'fahar-search-suggestion__placeholder';
					placeholder.setAttribute('aria-hidden', 'true');
					visual.append(placeholder);
				}

				if (typeof item.type_label === 'string' && item.type_label) {
					const type = document.createElement('span');
					type.className = 'fahar-search-suggestion__type';
					type.textContent = item.type_label;
					text.append(type);
				}

				link.append(visual, text);
				option.append(link);
				listbox.append(option);
			});

			options = Array.from(listbox.querySelectorAll('[role="option"]'));

			if (!options.length) {
				setStatus(root.dataset.emptyMessage || '');
				return;
			}

			setStatus();
			listbox.hidden = false;
			input.setAttribute('aria-expanded', 'true');
		};

		const requestSuggestions = async (query) => {
			const sequence = ++requestSequence;

			if (controller) {
				controller.abort();
			}

			controller = 'AbortController' in window ? new AbortController() : null;
			closeList();
			setStatus(root.dataset.loadingMessage || '');

			try {
				const url = new URL(endpoint, window.location.href);
				url.searchParams.set('search', query);
				url.searchParams.set('limit', '6');

				const response = await window.fetch(url.href, {
					headers: { Accept: 'application/json' },
					signal: controller ? controller.signal : undefined,
				});

				if (!response.ok) {
					throw new Error(`Suggestion request failed with ${response.status}`);
				}

				const items = await response.json();

				if (sequence !== requestSequence || input.value.trim() !== query) {
					return;
				}

				renderResults(Array.isArray(items) ? items : []);
			} catch (error) {
				if (error && error.name === 'AbortError') {
					return;
				}

				if (sequence === requestSequence) {
					closeList();
					setStatus(root.dataset.emptyMessage || '');
				}
			}
		};

		input.addEventListener('input', () => {
			window.clearTimeout(debounceTimer);
			requestSequence += 1;
			if (controller) {
				controller.abort();
			}
			closeList();
			setStatus();

			const query = input.value.trim();
			if (query.length < 2) {
				return;
			}

			debounceTimer = window.setTimeout(() => requestSuggestions(query), 250);
		});

		input.addEventListener('keydown', (event) => {
			if ('ArrowDown' === event.key && options.length) {
				event.preventDefault();
				setActiveOption(activeIndex + 1);
			} else if ('ArrowUp' === event.key && options.length) {
				event.preventDefault();
				setActiveOption(activeIndex - 1);
			} else if ('Enter' === event.key && 0 <= activeIndex && options[activeIndex]) {
				event.preventDefault();
				window.location.assign(options[activeIndex].href);
			} else if ('Escape' === event.key) {
				event.preventDefault();
				requestSequence += 1;
				if (controller) {
					controller.abort();
				}
				closeList();
				setStatus();
			}
		});

		root.addEventListener('focusout', () => {
			window.setTimeout(() => {
				if (!root.contains(document.activeElement)) {
					requestSequence += 1;
					if (controller) {
						controller.abort();
					}
					closeList();
				}
			}, 0);
		});

		root.addEventListener('submit', () => {
			window.clearTimeout(debounceTimer);
			if (controller) {
				controller.abort();
			}
		});
	};

	const initializeFilterSurface = (root) => {
		const trigger = root.querySelector('[data-fahar-filter-trigger]');
		const disclosure = root.querySelector('[data-fahar-filter-disclosure]');
		const panel = root.querySelector('[data-fahar-filter-panel]');
		const closeButton = root.querySelector('[data-fahar-filter-close]');

		if (!trigger || !disclosure || !panel || !closeButton) {
			return;
		}

		const mobileQuery = window.matchMedia('(max-width: 63.999rem)');
		root.classList.add('is-enhanced');

		const getFocusableElements = () => Array.from(panel.querySelectorAll(focusableSelector))
			.filter((element) => !element.hidden && element.getClientRects().length > 0);

		const syncOpenState = () => {
			const isOpen = mobileQuery.matches && disclosure.open;

			trigger.setAttribute('aria-expanded', String(isOpen));
			root.classList.toggle('is-open', isOpen);
			document.body.classList.toggle('fahar-filter-surface-open', isOpen);
		};

		const closePanel = (restoreFocus = true) => {
			if (!mobileQuery.matches || !disclosure.open) {
				return;
			}

			disclosure.open = false;
			syncOpenState();

			if (restoreFocus) {
				trigger.focus({ preventScroll: true });
			}
		};

		const openPanel = () => {
			if (!mobileQuery.matches || disclosure.open) {
				return;
			}

			disclosure.open = true;
			syncOpenState();
			window.requestAnimationFrame(() => closeButton.focus({ preventScroll: true }));
		};

		trigger.addEventListener('click', () => {
			if (!mobileQuery.matches) {
				return;
			}
			if (disclosure.open) {
				closePanel();
			} else {
				openPanel();
			}
		});

		closeButton.addEventListener('click', () => closePanel());
		const backdrop = root.querySelector('.fahar-filter-backdrop');

		if (backdrop) {
			backdrop.addEventListener('click', () => closePanel());
		}
		disclosure.addEventListener('toggle', syncOpenState);

		panel.addEventListener('keydown', (event) => {
			if (mobileQuery.matches && 'Escape' === event.key) {
				event.preventDefault();
				closePanel();
				return;
			}

			if (!mobileQuery.matches || 'Tab' !== event.key) {
				return;
			}

			const focusableElements = getFocusableElements();

			if (!focusableElements.length) {
				event.preventDefault();
				panel.focus();
				return;
			}

			const firstElement = focusableElements[0];
			const lastElement = focusableElements[focusableElements.length - 1];

			if (event.shiftKey && document.activeElement === firstElement) {
				event.preventDefault();
				lastElement.focus();
			} else if (!event.shiftKey && document.activeElement === lastElement) {
				event.preventDefault();
				firstElement.focus();
			}
		});

		const configureViewport = () => {
			if (mobileQuery.matches) {
				disclosure.open = false;
				trigger.hidden = false;
				closeButton.hidden = false;
				panel.setAttribute('role', 'dialog');
				panel.setAttribute('aria-modal', 'true');
				panel.setAttribute('tabindex', '-1');
			} else {
				disclosure.open = true;
				trigger.hidden = true;
				closeButton.hidden = true;
				panel.removeAttribute('role');
				panel.removeAttribute('aria-modal');
				panel.removeAttribute('tabindex');
			}

			syncOpenState();
		};

		if (typeof mobileQuery.addEventListener === 'function') {
			mobileQuery.addEventListener('change', configureViewport);
		} else if (typeof mobileQuery.addListener === 'function') {
			mobileQuery.addListener(configureViewport);
		}

		configureViewport();
	};

	const initialize = () => {
		document.querySelectorAll(gridSelector).forEach(initializeGrid);
		document.querySelectorAll(filterRootSelector).forEach(initializeFilterSurface);
		document.querySelectorAll(searchRootSelector).forEach(initializeRealtimeSearch);

		const explore = document.querySelector(exploreSelector);

		if (explore) {
			const infiniteFeed = initializeInfiniteFeed(explore);
			explore.addEventListener('click', (event) => captureDeparture(explore, event));
			restoreExplorePosition(explore, infiniteFeed);
		}
	};

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', initialize, { once: true });
	} else {
		initialize();
	}
})();
