/* Copyright (C) 2026 NSIS
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       htdocs/custom/modernui/js/modernui.js
 * \ingroup    modernui
 * \brief      Small progressive enhancements for the ModernUI skin.
 *             Only adds classes / decorative elements, never changes Dolibarr behaviour.
 *             No external library required (jQuery is used only when Dolibarr already loaded it).
 */
(function () {
	'use strict';

	/* ---------------------------------------------------------------------
	 * Configuration — edit texts shown in the brand block and login page
	 * --------------------------------------------------------------------- */
	var CONFIG = window.MODERNUI_CONFIG || {
		brandName: 'Dolibarr ERP',
		brandInitial: 'D',
		loginTitle: 'Chào mừng trở lại',
		loginSubtitle: 'Đăng nhập để tiếp tục làm việc với hệ thống.',
		heroTitle: 'Quản trị doanh nghiệp tập trung trên một nền tảng',
		heroText: 'Bán hàng, mua hàng, kho, kế toán và nhân sự — dữ liệu liền mạch, báo cáo theo thời gian thực.',
		heroPoints: [
			['360°', 'Khách hàng & nhà cung cấp'],
			['Realtime', 'Tồn kho & công nợ'],
			['1', 'Nền tảng cho mọi phòng ban']
		]
	};

	var doc = document;
	var root = doc.documentElement;

	function ready(fn) {
		if (doc.readyState !== 'loading') {
			fn();
		} else {
			doc.addEventListener('DOMContentLoaded', fn);
		}
	}
	function el(tag, cls, html) {
		var e = doc.createElement(tag);
		if (cls) e.className = cls;
		if (html !== undefined) e.innerHTML = html;
		return e;
	}
	function text(node) {
		return (node && (node.textContent || '')).replace(/\s+/g, ' ').trim();
	}
	function escapeHtml(s) {
		return String(s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}
	var isMac = /Mac|iPhone|iPad/.test(navigator.platform || navigator.userAgent);

	/* Runs as soon as the script is parsed (in <head>) to avoid flashes */
	root.classList.add('mui');

	/* ---------------------------------------------------------------------
	 * 1. Brand block (logo + name + version) on top of the sidebar
	 * --------------------------------------------------------------------- */
	function buildBrand() {
		if (doc.querySelector('.mui-brand') || !doc.getElementById('id-top') || !doc.querySelector('.side-nav, #id-left')) {
			return;
		}
		var brand = el('a', 'mui-brand');
		var home = doc.querySelector('#mainmenua_home, li#mainmenutd_home a');
		brand.href = home ? home.getAttribute('href') : (window.location.pathname.replace(/\/[^/]*\/?[^/]*$/, '/') || '/');

		var logo = doc.querySelector('li#mainmenutd_companylogo img, .menulogocontainer img');
		if (logo && logo.getAttribute('src')) {
			var img = el('img', 'mui-brand-logo');
			img.src = logo.getAttribute('src');
			img.alt = '';
			brand.appendChild(img);
		} else {
			brand.appendChild(el('span', 'mui-brand-mark', escapeHtml(CONFIG.brandInitial)));
			brand.appendChild(el('span', 'mui-brand-name', escapeHtml(CONFIG.brandName)));
		}
		var version = doc.querySelector('span.aversion');
		if (version && text(version)) {
			brand.appendChild(el('span', 'mui-brand-version', 'v' + escapeHtml(text(version))));
		}
		doc.body.appendChild(brand);

		// Burger for tablet / mobile (off-canvas sidebar)
		var burger = el('button', 'mui-burger', '<span class="fas fa-bars"></span>');
		burger.type = 'button';
		burger.setAttribute('aria-label', 'Menu');
		burger.addEventListener('click', function (e) {
			e.stopPropagation();
			root.classList.toggle('mui-nav-open');
		});
		doc.body.appendChild(burger);
		doc.addEventListener('click', function (e) {
			if (!root.classList.contains('mui-nav-open')) return;
			var nav = doc.querySelector('.side-nav');
			if (nav && !nav.contains(e.target)) root.classList.remove('mui-nav-open');
		});
	}

	/* ---------------------------------------------------------------------
	 * 2. Top menu: full label as tooltip (labels may be shortened)
	 * --------------------------------------------------------------------- */
	function topMenuTooltips() {
		var labels = doc.querySelectorAll('ul.tmenu .mainmenuaspan');
		for (var i = 0; i < labels.length; i++) {
			var li = labels[i].closest('li');
			if (li && !li.getAttribute('title')) li.setAttribute('title', text(labels[i]));
		}
	}

	/* ---------------------------------------------------------------------
	 * 3. Left menu: mark current entry and indentation depth
	 * --------------------------------------------------------------------- */
	function normalizeUrl(href) {
		try {
			var u = new URL(href, window.location.href);
			var ignore = ['mainmenu', 'leftmenu', 'idmenu', 'token', 'restore_lastsearch_values', 'contextpage', 'optioncss'];
			var params = [];
			u.searchParams.forEach(function (v, k) {
				if (ignore.indexOf(k) < 0 && v !== '') params.push(k + '=' + v);
			});
			params.sort();
			return { path: u.pathname.replace(/\/+/g, '/'), query: params.join('&') };
		} catch (e) {
			return null;
		}
	}
	function markLeftMenu() {
		var items = doc.querySelectorAll('div.menu_contenu, div.menu_titre');
		if (!items.length) return;
		var current = normalizeUrl(window.location.href);
		if (!current) return;
		var best = null, bestScore = 0;
		for (var i = 0; i < items.length; i++) {
			var item = items[i];
			// depth from the &nbsp; indentation Dolibarr prints before the link
			if (item.classList.contains('menu_contenu')) {
				var first = item.firstChild;
				if (first && first.nodeType === 3) {
					var n = (first.nodeValue.match(/ /g) || []).length;
					if (n >= 6) item.classList.add('mui-level3');
					else if (n >= 3) item.classList.add('mui-level2');
				}
			}
			var a = item.querySelector('a[href]');
			if (!a) continue;
			var target = normalizeUrl(a.getAttribute('href'));
			if (!target || target.path !== current.path) continue;
			var score = 1;
			if (target.query === current.query) {
				score = 3;
			} else if (target.query && current.query.indexOf(target.query) === 0) {
				score = 2;
			} else if (target.query) {
				score = 0.5;
			}
			if (item.classList.contains('menu_contenu')) score += 0.1;
			if (score > bestScore) {
				best = item;
				bestScore = score;
			}
		}
		if (best && bestScore >= 1) best.classList.add('mui-active');
	}

	/* ---------------------------------------------------------------------
	 * 4. Page title: first title of the page gets the big style
	 * --------------------------------------------------------------------- */
	function markPageTitle() {
		var fiche = doc.querySelector('div.fiche');
		if (!fiche) return;
		var t = fiche.querySelector('table.table-fiche-title');
		if (!t || t.classList.contains('modulefamilygroup')) return;
		// only if nothing meaningful (tabs, cards) is printed before it
		var tabs = fiche.querySelector('div.tabs');
		if (tabs && (tabs.compareDocumentPosition(t) & Node.DOCUMENT_POSITION_FOLLOWING)) return;
		t.classList.add('mui-page-title');
	}

	/* ---------------------------------------------------------------------
	 * 5. Title buttons → segmented control (first / last radius)
	 * --------------------------------------------------------------------- */
	function segmentedButtons() {
		var groups = doc.querySelectorAll('td.col-right, .titre_right, div.tabsElem');
		for (var g = 0; g < groups.length; g++) {
			var btns = groups[g].querySelectorAll('.btnTitle:not(.btnTitlePlus)');
			var runs = [], run = [];
			for (var i = 0; i < btns.length; i++) {
				var prev = run[run.length - 1];
				if (prev && !isAdjacent(prev, btns[i])) {
					runs.push(run);
					run = [];
				}
				run.push(btns[i]);
			}
			if (run.length) runs.push(run);
			runs.forEach(function (r) {
				if (r.length === 1) {
					r[0].classList.add('mui-seg-single');
				} else {
					r[0].classList.add('mui-seg-first');
					r[r.length - 1].classList.add('mui-seg-last');
				}
			});
		}
	}
	function isAdjacent(a, b) {
		// buttons separated by a "button-title-separator" belong to different groups
		return a.nextElementSibling === b;
	}

	/* ---------------------------------------------------------------------
	 * 6. Setup > Modules: family colors, counters, collapse chevron
	 * --------------------------------------------------------------------- */
	var FAMILY_RULES = [
		['hr', /nh[aâ]n s[ựu]|\bhr\b|human|ressources humaines|personnel/i],
		['crm', /kh[aá]ch h[aà]ng|\bcrm\b|nh[aà] cung c[aấ]p|\bvrm\b|\bsrm\b|customer|vendor|supplier|client/i],
		['fin', /t[aà]i ch[ií]nh|k[eế] to[aá]n|ng[aâ]n qu[yỹ]|financ|accounting|compta/i],
		['prod', /s[aả]n ph[aẩ]m|\bkho\b|product|stock|warehouse/i],
		['proj', /d[ựu] [aá]n|c[oộ]ng t[aá]c|c[oô]ng c[uụ]|project|collabor|tool|ecm|n[oộ]i dung/i]
	];
	function familyKey(label) {
		for (var i = 0; i < FAMILY_RULES.length; i++) {
			if (FAMILY_RULES[i][1].test(label)) return FAMILY_RULES[i][0];
		}
		return 'other';
	}
	function nextContainer(table) {
		var n = table.nextElementSibling;
		while (n) {
			if (n.classList && (n.classList.contains('box-flex-container') || n.classList.contains('div-table-responsive'))) return n;
			if (n.tagName === 'TABLE' && n.classList.contains('modulefamilygroup')) return null;
			n = n.nextElementSibling;
		}
		return null;
	}
	function moduleFamilies() {
		var groups = doc.querySelectorAll('table.modulefamilygroup');
		if (groups.length) groups[0].classList.add('mui-first');
		for (var i = 0; i < groups.length; i++) {
			var g = groups[i];
			var titre = g.querySelector('.titre');
			var label = text(titre);
			var box = nextContainer(g);
			if (!box) continue;
			box.setAttribute('data-mui-family', familyKey(label));
			if (titre && !titre.querySelector('.mui-count')) {
				var count = box.querySelectorAll('.box-flex-item.info-box-module, tr.oddeven').length;
				var chevron = el('span', 'fas fa-chevron-down mui-chevron');
				titre.insertBefore(chevron, titre.firstChild);
				if (count) titre.appendChild(el('span', 'mui-count', String(count)));
			}
			(function (table, container) {
				table.addEventListener('click', function () {
					// Dolibarr toggles the container itself; we only reflect the state after the animation
					setTimeout(function () {
						table.classList.toggle('mui-collapsed', container.offsetParent === null);
					}, 200);
				});
			})(g, box);
		}
	}

	/* ---------------------------------------------------------------------
	 * 7. Login page: heading + gradient hero panel
	 * --------------------------------------------------------------------- */
	function loginPage() {
		var body = doc.body;
		if (!body.classList.contains('bodylogin') || doc.querySelector('.mui-login-hero')) return;
		var right = doc.getElementById('login_right');
		if (right && !doc.querySelector('.mui-login-heading')) {
			var h = el('div', 'mui-login-heading', '<h1>' + escapeHtml(CONFIG.loginTitle) + '</h1><p>' + escapeHtml(CONFIG.loginSubtitle) + '</p>');
			right.insertBefore(h, right.firstChild);
		}
		var points = '';
		(CONFIG.heroPoints || []).forEach(function (p) {
			points += '<div class="mui-hero-point"><strong>' + escapeHtml(p[0]) + '</strong>' + escapeHtml(p[1]) + '</div>';
		});
		var hero = el('aside', 'mui-login-hero',
			'<div class="mui-hero-top"><span class="mui-brand-mark">' + escapeHtml(CONFIG.brandInitial) + '</span>' + escapeHtml(CONFIG.brandName) + '</div>' +
			'<div class="mui-hero-body"><h2>' + escapeHtml(CONFIG.heroTitle) + '</h2><p>' + escapeHtml(CONFIG.heroText) + '</p></div>' +
			'<div class="mui-hero-points">' + points + '</div>');
		hero.setAttribute('aria-hidden', 'true');
		body.appendChild(hero);
	}

	/* ---------------------------------------------------------------------
	 * 8. Command bar: Ctrl/⌘ + K opens the existing global search
	 * --------------------------------------------------------------------- */
	function commandBar() {
		var box = doc.getElementById('blockvmenusearch');
		if (box && !box.querySelector('.mui-kbd')) {
			box.appendChild(el('span', 'mui-kbd', isMac ? '⌘K' : 'Ctrl K'));
		}
		doc.addEventListener('keydown', function (e) {
			if ((e.ctrlKey || e.metaKey) && !e.shiftKey && !e.altKey && (e.key === 'k' || e.key === 'K')) {
				var opened = false;
				if (window.jQuery) {
					var combo = window.jQuery('#searchselectcombo, .blockvmenusearch select').first();
					if (combo.length && combo.data('select2')) {
						root.classList.add('mui-nav-open');
						combo.select2('open');
						opened = true;
					}
				}
				if (!opened) {
					var input = doc.querySelector('#blockvmenusearch input[type="text"], #blockvmenusearch input.select2-search__field, #topmenu-global-search-dropdown input');
					if (input) {
						input.focus();
						opened = true;
					}
				}
				if (opened) e.preventDefault();
			}
			if (e.key === 'Escape') root.classList.remove('mui-nav-open');
		});
	}

	/* --------------------------------------------------------------------- */
	ready(function () {
		var steps = [loginPage, buildBrand, topMenuTooltips, markLeftMenu, markPageTitle, segmentedButtons, moduleFamilies, commandBar];
		for (var i = 0; i < steps.length; i++) {
			try {
				steps[i]();
			} catch (err) {
				if (window.console) console.warn('modernui:', err);
			}
		}
	});
})();
