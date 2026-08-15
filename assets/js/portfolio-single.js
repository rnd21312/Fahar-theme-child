/** Progressive enhancement for Single Portfolio components. */
(() => {
	'use strict';

	const storageKey = 'faharExploreReturnState:v1';
	const stateMaxAge = 30 * 60 * 1000;

	const removeStoredExploreState = () => {
		try {
			window.sessionStorage.removeItem(storageKey);
		} catch (error) {
			// Storage can be unavailable without affecting the fallback link.
		}
	};

	const readStoredExploreState = () => {
		try {
			const stored = window.sessionStorage.getItem(storageKey);
			return stored ? JSON.parse(stored) : null;
		} catch (error) {
			removeStoredExploreState();
			return null;
		}
	};

	const writeStoredExploreState = (state) => {
		try {
			window.sessionStorage.setItem(storageKey, JSON.stringify(state));
			return true;
		} catch (error) {
			return false;
		}
	};

	const normalizePathname = (pathname) => pathname.replace(/\/+$/, '') || '/';

	const validateExploreState = (state, fallbackUrl) => {
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
			|| state.timestamp > Date.now()
			|| Date.now() - state.timestamp > stateMaxAge
		) {
			return null;
		}

		try {
			const returnUrl = new URL(state.returnUrl, window.location.origin);
			const currentUrl = new URL(window.location.href);

			if (
				fallbackUrl.origin !== window.location.origin
				|| returnUrl.origin !== window.location.origin
				|| normalizePathname(returnUrl.pathname) !== normalizePathname(fallbackUrl.pathname)
				|| (
					normalizePathname(returnUrl.pathname) === normalizePathname(currentUrl.pathname)
					&& returnUrl.search === currentUrl.search
				)
			) {
				return null;
			}

			return { state, returnUrl };
		} catch (error) {
			return null;
		}
	};

	const initializeBackLink = (backLink) => {
		let fallbackUrl;

		try {
			fallbackUrl = new URL(backLink.href, window.location.href);
		} catch (error) {
			return;
		}

		const storedState = readStoredExploreState();
		const parsed = validateExploreState(storedState, fallbackUrl);

		if (!parsed) {
			if (storedState) {
				removeStoredExploreState();
			}

			return;
		}

		backLink.href = `${parsed.returnUrl.pathname}${parsed.returnUrl.search}${parsed.returnUrl.hash}`;
		backLink.addEventListener('click', (event) => {
			const latest = validateExploreState(readStoredExploreState(), fallbackUrl);

			if (!latest) {
				backLink.href = fallbackUrl.href;
				removeStoredExploreState();
				return;
			}

			backLink.href = `${latest.returnUrl.pathname}${latest.returnUrl.search}${latest.returnUrl.hash}`;

			if (
				event.button !== 0
				|| event.metaKey
				|| event.ctrlKey
				|| event.shiftKey
				|| event.altKey
			) {
				return;
			}

			latest.state.restoreRequested = true;
			writeStoredExploreState(latest.state);
		});
	};

	const initializeSlider = (slider) => {
		const viewport = slider.querySelector('.fahar-portfolio-slider__viewport');
		const slides = Array.from(slider.querySelectorAll('.fahar-portfolio-slider__slide'));
		const controls = slider.querySelector('.fahar-portfolio-slider__controls');
		const previousButton = slider.querySelector('.fahar-portfolio-slider__prev');
		const nextButton = slider.querySelector('.fahar-portfolio-slider__next');
		const counter = slider.querySelector('.fahar-portfolio-slider__counter');

		if (!viewport || slides.length < 2 || !controls || !previousButton || !nextButton || !counter) {
			return;
		}

		const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
		const direction = window.getComputedStyle(slider).direction;
		let numberFormatter = null;

		if (typeof Intl === 'object' && typeof Intl.NumberFormat === 'function') {
			try {
				numberFormatter = new Intl.NumberFormat(document.documentElement.lang || undefined, { useGrouping: false });
			} catch (error) {
				// A malformed document language must not disable slider controls.
			}
		}

		const formatNumber = (value) => numberFormatter ? numberFormatter.format(value) : value;
		const counterTemplate = counter.dataset.counterTemplate;
		const counterAriaTemplate = counter.dataset.counterAriaTemplate;

		if (
			!counterTemplate
			|| !counterTemplate.includes('%1$s')
			|| !counterTemplate.includes('%2$s')
			|| !counterAriaTemplate
			|| !counterAriaTemplate.includes('%1$s')
			|| !counterAriaTemplate.includes('%2$s')
		) {
			return;
		}

		const formatCounter = (template, index) => template
			.replace('%1$s', formatNumber(index + 1))
			.replace('%2$s', formatNumber(slides.length));
		const ratios = new Map(slides.map((slide) => [slide, 0]));
		let currentIndex = 0;
		let measurementFrame = 0;

		const pauseNativeVideo = (slide) => {
			slide.querySelectorAll('video').forEach((video) => {
				if (!video.paused) {
					video.pause();
				}
			});
		};

		const updateState = (nextIndex) => {
			const boundedIndex = Math.max(0, Math.min(nextIndex, slides.length - 1));

			if (boundedIndex !== currentIndex) {
				pauseNativeVideo(slides[currentIndex]);
				slides[currentIndex].removeAttribute('aria-current');
				currentIndex = boundedIndex;
			}

			slides[currentIndex].setAttribute('aria-current', 'true');
			previousButton.disabled = currentIndex === 0;
			nextButton.disabled = currentIndex === slides.length - 1;
			counter.textContent = formatCounter(counterTemplate, currentIndex);
			counter.setAttribute('aria-label', formatCounter(counterAriaTemplate, currentIndex));
		};

		const navigateTo = (nextIndex) => {
			const boundedIndex = Math.max(0, Math.min(nextIndex, slides.length - 1));

			updateState(boundedIndex);
			slides[boundedIndex].scrollIntoView({
				behavior: reduceMotion.matches ? 'auto' : 'smooth',
				block: 'nearest',
				inline: 'start',
			});
		};

		const measureCurrentSlide = () => {
			measurementFrame = 0;
			const viewportRect = viewport.getBoundingClientRect();
			let strongestIndex = currentIndex;
			let strongestOverlap = -1;

			slides.forEach((slide, index) => {
				const slideRect = slide.getBoundingClientRect();
				const overlap = Math.max(
					0,
					Math.min(viewportRect.right, slideRect.right) - Math.max(viewportRect.left, slideRect.left),
				);

				if (overlap > strongestOverlap) {
					strongestOverlap = overlap;
					strongestIndex = index;
				}
			});

			updateState(strongestIndex);
		};

		const queueMeasurement = () => {
			if (!measurementFrame) {
				measurementFrame = window.requestAnimationFrame(measureCurrentSlide);
			}
		};

		previousButton.addEventListener('click', () => navigateTo(currentIndex - 1));
		nextButton.addEventListener('click', () => navigateTo(currentIndex + 1));

		slider.addEventListener('keydown', (event) => {
			if (!(event.target instanceof Element) || event.target.closest('video, iframe, input, select, textarea, a')) {
				return;
			}

			let nextIndex = null;

			if (event.key === 'Home') {
				nextIndex = 0;
			} else if (event.key === 'End') {
				nextIndex = slides.length - 1;
			} else if (event.key === 'ArrowRight') {
				nextIndex = currentIndex + (direction === 'rtl' ? -1 : 1);
			} else if (event.key === 'ArrowLeft') {
				nextIndex = currentIndex + (direction === 'rtl' ? 1 : -1);
			}

			if (nextIndex === null) {
				return;
			}

			event.preventDefault();
			navigateTo(nextIndex);
		});

		if ('IntersectionObserver' in window) {
			const observer = new IntersectionObserver((entries) => {
				entries.forEach((entry) => ratios.set(entry.target, entry.intersectionRatio));

				let strongestIndex = currentIndex;
				let strongestRatio = -1;

				slides.forEach((slide, index) => {
					const ratio = ratios.get(slide) || 0;

					if (ratio > strongestRatio) {
						strongestRatio = ratio;
						strongestIndex = index;
					}
				});

				updateState(strongestIndex);
			}, {
				root: viewport,
				threshold: [0, 0.25, 0.5, 0.75, 1],
			});

			slides.forEach((slide) => observer.observe(slide));
		} else {
			viewport.addEventListener('scroll', queueMeasurement, { passive: true });
		}

		if ('ResizeObserver' in window) {
			const resizeObserver = new ResizeObserver(queueMeasurement);
			resizeObserver.observe(viewport);
		} else {
			window.addEventListener('resize', queueMeasurement, { passive: true });
		}

		controls.hidden = false;
		updateState(0);
	};

	const initializeDescription = (description) => {
		const content = description.querySelector('[data-fahar-description-content]');
		const toggle = description.querySelector('[data-fahar-description-toggle]');

		if (!content || !toggle) {
			return;
		}

		const focusableSelector = [
			'a[href]',
			'button:not([disabled])',
			'input:not([type="hidden"]):not([disabled])',
			'select:not([disabled])',
			'textarea:not([disabled])',
			'details > summary:first-of-type',
			'[tabindex]:not([tabindex="-1"])',
			'[contenteditable]:not([contenteditable="false"])',
			'audio[controls]',
			'video[controls]',
			'iframe',
			'object',
			'embed',
		].join(',');
		const collapsedLabel = toggle.dataset.labelCollapsed;
		const expandedLabel = toggle.dataset.labelExpanded;

		if (!collapsedLabel || !expandedLabel) {
			return;
		}

		const focusableContent = Array.from(content.querySelectorAll(focusableSelector));
		let userExpanded = false;
		let measurementFrame = 0;
		let lastInlineSize = null;

		const setFocusableState = (expanded) => {
			const contentBottom = content.getBoundingClientRect().bottom;

			focusableContent.forEach((element) => {
				const isVisible = expanded || element.getBoundingClientRect().top < contentBottom;

				if (isVisible) {
					if (element.hasAttribute('data-fahar-original-tabindex')) {
						const originalTabindex = element.getAttribute('data-fahar-original-tabindex');
						element.removeAttribute('data-fahar-original-tabindex');

						if (originalTabindex === '') {
							element.removeAttribute('tabindex');
						} else {
							element.setAttribute('tabindex', originalTabindex);
						}
					}
				} else if (!element.hasAttribute('data-fahar-original-tabindex')) {
					element.setAttribute('data-fahar-original-tabindex', element.getAttribute('tabindex') || '');
					element.setAttribute('tabindex', '-1');
				}
			});
		};

		const setExpanded = (expanded) => {
			description.classList.toggle('is-collapsed', !expanded);
			description.classList.toggle('is-expanded', expanded);
			toggle.setAttribute('aria-expanded', String(expanded));
			toggle.textContent = expanded ? expandedLabel : collapsedLabel;
			setFocusableState(expanded);
		};

		const getCollapsedSize = () => {
			const previousMaxBlockSize = content.style.maxBlockSize;

			content.style.maxBlockSize = 'var(--fahar-description-collapsed-size)';
			const collapsedSize = Number.parseFloat(window.getComputedStyle(content).maxBlockSize);
			content.style.maxBlockSize = previousMaxBlockSize;

			return collapsedSize;
		};

		const evaluate = () => {
			measurementFrame = 0;

			const collapsedSize = getCollapsedSize();
			const needsCollapse = Number.isFinite(collapsedSize) && content.scrollHeight > collapsedSize + 8;

			if (!needsCollapse) {
				description.classList.remove('is-collapsible', 'is-collapsed', 'is-expanded');
				toggle.hidden = true;
				toggle.setAttribute('aria-expanded', 'true');
				setFocusableState(true);
				return;
			}

			description.classList.add('is-collapsible');
			toggle.hidden = false;
			setExpanded(userExpanded);
		};

		const queueEvaluation = () => {
			if (!measurementFrame) {
				measurementFrame = window.requestAnimationFrame(evaluate);
			}
		};

		toggle.addEventListener('click', () => {
			userExpanded = toggle.getAttribute('aria-expanded') !== 'true';
			setExpanded(userExpanded);
		});

		if ('ResizeObserver' in window) {
			const resizeObserver = new ResizeObserver((entries) => {
				const inlineSize = entries[0] ? entries[0].contentRect.width : null;

				if (inlineSize !== lastInlineSize) {
					lastInlineSize = inlineSize;
					queueEvaluation();
				}
			});

			resizeObserver.observe(description);
		} else {
			window.addEventListener('resize', queueEvaluation, { passive: true });
		}

		evaluate();
	};

	const initialize = () => {
		document.querySelectorAll('[data-fahar-slider]').forEach(initializeSlider);
		document.querySelectorAll('[data-fahar-description]').forEach(initializeDescription);
		document.querySelectorAll('[data-fahar-back-to-explore]').forEach(initializeBackLink);
	};

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initialize, { once: true });
	} else {
		initialize();
	}
})();
