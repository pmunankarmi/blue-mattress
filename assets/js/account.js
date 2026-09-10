/**
 * Blue Mattress account tabs.
 */
(() => {
	'use strict';

	const tabs = Array.from(document.querySelectorAll('[data-account-tab]'));
	if (!tabs.length) return;

	const panels = {
		login: document.getElementById('blue-login-panel'),
		register: document.getElementById('blue-register-panel'),
	};

	const activate = (name, focusPanel = false) => {
		tabs.forEach((tab) => {
			const selected = tab.dataset.accountTab === name;
			tab.classList.toggle('is-active', selected);
			tab.setAttribute('aria-selected', String(selected));
			tab.tabIndex = selected ? 0 : -1;
		});

		Object.entries(panels).forEach(([key, panel]) => {
			if (panel) panel.hidden = key !== name;
		});

		if (focusPanel && panels[name]) {
			panels[name].querySelector('input:not([type="hidden"])')?.focus();
		}
	};

	tabs.forEach((tab, index) => {
		tab.addEventListener('click', () => activate(tab.dataset.accountTab, true));
		tab.addEventListener('keydown', (event) => {
			if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
			event.preventDefault();
			const direction = event.key === 'ArrowRight' ? 1 : -1;
			const next = tabs[(index + direction + tabs.length) % tabs.length];
			activate(next.dataset.accountTab, true);
			next.focus();
		});
	});

	const selected = tabs.find((tab) => tab.getAttribute('aria-selected') === 'true') || tabs[0];
	activate(selected.dataset.accountTab);
})();
