/* WordPress bridge for the Blue Mattress interactions. */
(() => {
  'use strict';
  const cfg = window.BlueTheme || {};
  const AR = () => Boolean(cfg.isArabic);
  const esc = (value) => String(value ?? '').replace(/[&<>'"]/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[char]));
  const fallbackStrings = AR() ? {
    from: 'ابتداءً من', details: 'التفاصيل', quickAdd: 'أضف إلى السلة', saleBadge: 'تخفيض',
    plush: 'ناعمة', firm: 'صلبة', standard: 'قياسي', arr: '←', added: 'جاري فتح السلة…',
    searchHint: 'اكتب للبحث في المنتجات.', searchNone: 'لا توجد نتائج.',
  } : {
    from: 'From', details: 'Details', quickAdd: 'Add to cart', saleBadge: 'Sale',
    plush: 'Plush', firm: 'Firm', standard: 'Standard', arr: '→', added: 'Opening cart…',
    searchHint: 'Start typing to search products.', searchNone: 'No products found.',
  };
  const strings = Object.assign({}, fallbackStrings, cfg.strings || {});

  const products = Array.isArray(cfg.products) ? cfg.products : [];
  const categories = Array.isArray(cfg.categories) ? cfg.categories : [];
  const reviews = Array.isArray(cfg.reviews) ? cfg.reviews : [];
  const t = (key) => strings[key] || key;
  const loc = (object, field) => object ? (object[field] ?? '') : '';
  const priceFrom = (product) => Math.min(...Object.values(product.prices || { default: 0 }).map(Number));
  const fmt = (number) => AR()
    ? `${new Intl.NumberFormat('ar-SA').format(number)} ${cfg.currency || 'ر.س'}`
    : `${cfg.currency || 'SAR'} ${new Intl.NumberFormat('en-US').format(number)}`;
  const byId = (id) => products.find((product) => String(product.id) === String(id));
  const search = (query) => {
    const words = String(query || '').toLowerCase().trim().split(/\s+/).filter(Boolean);
    if (!words.length) return [];
    return products.filter((product) => {
      const haystack = [product.name, product.kind, product.tag, product.cat].join(' ').toLowerCase();
      return words.every((word) => haystack.includes(word));
    });
  };
  const href = (page) => {
    if (page.includes('shop')) return cfg.shopUrl;
    if (page.includes('contact')) return cfg.contactUrl;
    if (page.includes('selector')) return cfg.finderUrl;
    if (page.includes('stark')) return cfg.starkUrl;
    if (page.includes('checkout')) return cfg.checkoutUrl;
    return cfg.homeUrl;
  };

  window.BLUE = {
    PRODUCTS: products,
    CATEGORIES: categories,
    REVIEWS: reviews,
    ANATOMY: cfg.anatomy || { layers: [] },
    HOME: cfg.home || {},
    SOCIAL: cfg.socials || [],
    SIZES: [],
    AR, t, loc, fmt, priceFrom, byId, search, href,
    priceRange: (product) => fmt(priceFrom(product)),
  };

  const starSvg = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.6 7.1.7-5.4 4.8 1.6 7-6.2-3.7-6.2 3.7 1.6-7L2 9.3l7.1-.7z"/></svg>';
  const stars = (rating) => {
    let html = `<span class="stars" role="img" aria-label="${esc(rating)} / 5">`;
    for (let index = 1; index <= 5; index += 1) html += `<span class="${index <= Math.round(rating) ? '' : 'muted'}">${starSvg}</span>`;
    return `${html}</span>`;
  };
  const feelMeter = (feel) => feel == null ? '' : `<div class="feel" style="--feel:${Number(feel)}"><div class="feel-track"></div><div class="feel-labels"><span>${t('plush')}</span><span>${t('firm')}</span></div></div>`;
  const productCard = (product, options = {}) => {
    const reveal = options.reveal ? 'data-reveal' : '';
    const delay = options.delay ? `style="--rd:${esc(options.delay)}"` : '';
    return `<article class="pcard" ${reveal} ${delay}>
      <a class="pcard-media" href="${esc(product.url)}">
        ${product.sale ? `<span class="pcard-badge"><span class="chip chip-sale">${t('saleBadge')}</span></span>` : ''}
        <img src="${esc(product.img)}" alt="${esc(product.name)}" loading="lazy">
        <span class="pcard-quick" data-quick-add="${esc(product.id)}">${t('quickAdd')}</span>
      </a>
      <div class="pcard-body">
        <span class="pcard-kind">${esc(product.kind)}</span>
        <span class="pcard-name"><a class="pcard-link" href="${esc(product.url)}">${esc(product.name)}</a></span>
        <span class="pcard-tag">${esc(product.tag)}</span>
        ${product.rating ? `<span class="rating-line">${stars(product.rating)} <span>${esc(product.rating)} (${esc(product.reviews)})</span></span>` : ''}
        <div class="pcard-foot"><span class="pcard-price"><span class="from">${t('from')}</span>${fmt(priceFrom(product))}</span><span class="pcard-cta">${t('details')} <span class="arr">${t('arr')}</span></span></div>
      </div>
    </article>`;
  };
  const socialRow = () => `<div class="social-row">${(cfg.socials || []).map((item) => `<a class="social-ic" href="${esc(item.url)}" target="_blank" rel="noopener noreferrer">${esc(item.label)}</a>`).join('')}</div>`;
  let toastTimer;
  const toast = (message) => {
    const element = document.getElementById('toast');
    const content = document.getElementById('toastMsg');
    if (!element || !content) return;
    content.textContent = message;
    element.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => element.classList.remove('show'), 2200);
  };
  window.BlueUI = { stars, feelMeter, productCard, socialRow, toast, cart: { add: (id) => {
    const product = byId(id);
    if (!product) return;
    toast(t('added'));
    window.location.assign(product.addUrl || product.url);
  } } };

  const boot = () => {
    if (window.gsap && window.ScrollTrigger) window.gsap.registerPlugin(window.ScrollTrigger);
    const header = document.getElementById('siteHeader');
    const onScroll = () => header?.classList.toggle('scrolled', window.scrollY > 24);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    const themeButton = document.getElementById('themeBtn');
    themeButton?.addEventListener('click', () => {
      const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
      document.documentElement.dataset.theme = next;
      try { localStorage.setItem('blue-theme', next); } catch (error) {}
    });

    const menu = document.getElementById('mobileMenu');
    const burger = document.getElementById('burgerBtn');
    const setMenu = (open) => {
      menu?.classList.toggle('open', open);
      menu?.setAttribute('aria-hidden', String(!open));
      burger?.setAttribute('aria-expanded', String(open));
    };
    burger?.addEventListener('click', () => setMenu(true));
    document.getElementById('mmClose')?.addEventListener('click', () => setMenu(false));

    const overlay = document.getElementById('searchOverlay');
    const input = document.getElementById('searchInput');
    const body = document.getElementById('searchBody');
    const setSearch = (open) => {
      overlay?.classList.toggle('open', open);
      overlay?.setAttribute('aria-hidden', String(!open));
      if (open) { body.innerHTML = `<p class="search-hint">${t('searchHint')}</p>`; setTimeout(() => input?.focus(), 10); }
    };
    document.getElementById('searchBtn')?.addEventListener('click', () => setSearch(true));
    document.getElementById('searchClose')?.addEventListener('click', () => setSearch(false));
    overlay?.addEventListener('click', (event) => { if (event.target === overlay) setSearch(false); });
    input?.addEventListener('input', () => {
      const results = search(input.value).slice(0, 8);
      body.innerHTML = results.length ? `<div class="search-list">${results.map((product) => `<a class="sr-item" href="${esc(product.url)}"><img src="${esc(product.img)}" alt=""><span class="sr-info"><span class="sr-name">${esc(product.name)}</span><span class="sr-kind">${esc(product.kind)}</span></span><span class="sr-price">${fmt(priceFrom(product))}</span></a>`).join('')}</div>` : `<p class="search-hint">${t('searchNone')}</p>`;
    });

    document.addEventListener('click', (event) => {
      const target = event.target.closest('[data-quick-add]');
      if (!target) return;
      event.preventDefault();
      event.stopPropagation();
      window.BlueUI.cart.add(target.dataset.quickAdd);
    });

	const wishlistKey = 'blue-mattress-wishlist';
	let wished = [];
	try {
	  const saved = JSON.parse(window.localStorage.getItem(wishlistKey) || '[]');
	  wished = Array.isArray(saved) ? saved.map(String) : [];
	} catch (error) {}
	const updateWish = (button) => {
	  const active = wished.includes(button.dataset.wishProduct);
	  button.classList.toggle('is-wished', active);
	  button.setAttribute('aria-pressed', active ? 'true' : 'false');
	};
	document.querySelectorAll('[data-wish-product]').forEach(updateWish);
	document.addEventListener('click', (event) => {
	  const button = event.target.closest('[data-wish-product]');
	  if (!button) return;
	  event.preventDefault();
	  event.stopPropagation();
	  const id = button.dataset.wishProduct;
	  wished = wished.includes(id) ? wished.filter((item) => item !== id) : [...wished, id];
	  updateWish(button);
	  try { window.localStorage.setItem(wishlistKey, JSON.stringify(wished)); } catch (error) {}
	});
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape') { setSearch(false); setMenu(false); } });

    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
        if (entry.isIntersecting) { entry.target.classList.add('in'); observer.unobserve(entry.target); }
      }), { threshold: 0.1 });
      const observe = (root = document) => root.querySelectorAll('[data-reveal]:not(.in)').forEach((element) => observer.observe(element));
      window.BlueReveal = observe;
      observe();
    } else {
      window.BlueReveal = (root = document) => root.querySelectorAll('[data-reveal]').forEach((element) => element.classList.add('in'));
      window.BlueReveal();
    }
  };
  document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', boot) : boot();
})();
