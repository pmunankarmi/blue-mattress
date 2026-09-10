/* ============================================================
   BLUE — home page behaviors (serves index.html AND index-ar.html)
   Hero parallax · lineup carousel · anatomy scrollytelling ·
   feel finder · categories · stat counters · reviews carousel ·
   lifestyle parallax. Every effect degrades gracefully when
   GSAP / ScrollTrigger / Lenis are absent.
   All injected copy resolves per-locale via BLUE.t / BLUE.loc
   or the local S dictionary below.
   ============================================================ */

(() => {
  'use strict';

  const $  = (s, r) => (r || document).querySelector(s);
  const $$ = (s, r) => Array.prototype.slice.call((r || document).querySelectorAll(s));
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const hasGsap = () => !!(window.gsap && window.ScrollTrigger);
  const reveal = (root) => { if (window.BlueReveal) window.BlueReveal(root); };
  /* data.js / main.js declare BLUE and BlueUI with `const` (lexical globals,
     not window properties) — bind them safely here. */
  const B  = typeof BLUE   !== 'undefined' ? BLUE   : window.BLUE;
  const UI = typeof BlueUI !== 'undefined' ? BlueUI : window.BlueUI;
  const isRTL = document.documentElement.dir === 'rtl';
	const H = (B && B.HOME) || {};
	const escapeCopy = (value) => String(value || '').replace(/[&<>"']/g, (char) => ({
		'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
	}[char]));

  /* ------------------------------------------------ page strings */
	const arabic = B && B.AR();
	const recommendationTemplate = String(H.feelRecommendation || '');
	const S = {
		zones: H.zones || {},
		valuetext: (v, z) => arabic ? v + ' من 10 — ' + z : v + ' out of 10 — ' + z,
		rec: (v, name, clause) => escapeCopy(recommendationTemplate)
			.replace('{value}', '<b>' + escapeCopy(v) + '</b>')
			.replace('{name}', '<em>' + name + '</em>')
			.replace('{description}', clause),
		goReview: (n) => String(H.reviewDotLabel || '').replace('{number}', n),
		anatomyAlt: H.anatomyAlt || '',
	};

  /* ------------------------------------------------ drag-to-scroll (mouse) */
  function dragScroll(el) {
    let down = false, moved = false, sx = 0, sl = 0;
    el.addEventListener('pointerdown', (e) => {
      if (e.pointerType !== 'mouse' || e.button !== 0) return;
      down = true; moved = false; sx = e.clientX; sl = el.scrollLeft;
    });
    window.addEventListener('pointermove', (e) => {
      if (!down) return;
      const dx = e.clientX - sx;
      if (!moved && Math.abs(dx) > 5) { moved = true; el.classList.add('dragging'); }
      if (moved) el.scrollLeft = sl - dx;
    });
    const up = () => {
      if (!down) return;
      down = false;
      requestAnimationFrame(() => el.classList.remove('dragging'));
    };
    window.addEventListener('pointerup', up);
    window.addEventListener('pointercancel', up);
    el.addEventListener('click', (e) => {
      if (moved) { e.preventDefault(); e.stopPropagation(); moved = false; }
    }, true);
    el.addEventListener('dragstart', (e) => e.preventDefault());
  }

  /* ------------------------------------------------ 0 · hero cinemagraph
     The attribute `autoplay` alone is unreliable for the muted background
     video in some environments (it stalls at readyState 0 and never plays).
     Nudge it: force a load() + play() once the browser can render frames,
     retry on the first user gesture, and pause it when scrolled off-screen
     to save power. If it can never play, the poster/CSS still frame remains. */
  function heroVideo() {
    const v = $('.hero-video');
    if (!v) return;
    if (reduced) { v.pause(); return; }

    const tryPlay = () => {
      const p = v.play();
      if (p && p.catch) p.catch(() => {});
    };
    if (v.readyState >= 2) tryPlay();
    v.addEventListener('loadeddata', tryPlay, { once: true });
    v.addEventListener('canplay', tryPlay, { once: true });
    try { v.load(); } catch (e) {}

    /* browsers that block autoplay until interaction */
    const onGesture = () => { tryPlay(); window.removeEventListener('pointerdown', onGesture); };
    window.addEventListener('pointerdown', onGesture, { once: true });

    /* only run the loop while the hero is in view */
    if ('IntersectionObserver' in window) {
      new IntersectionObserver((entries) => {
        entries.forEach((e) => (e.isIntersecting ? tryPlay() : v.pause()));
      }, { threshold: 0.05 }).observe(v);
    }
  }

  /* ------------------------------------------------ 1 · hero parallax */
  function heroFx() {
    if (!hasGsap() || reduced) return;
    const bg = $('.hero-bg');
    if (!bg) return;
    gsap.to(bg, {
      yPercent: 12, ease: 'none',
      scrollTrigger: { trigger: '.hero', start: 'top top', end: 'bottom top', scrub: true },
    });
  }

  /* ------------------------------------------------ 4 · lineup carousel */
  function lineup() {
    const track = $('#luTrack');
    if (!track) return;

    /* Product markup is rendered by PHP through WooCommerce's standard
       content-product template. JavaScript only enhances the carousel. */
    reveal(track);

    /* progress bar — scrollLeft runs 0 → -max on RTL pages, so use the
       magnitude and mirror the translate direction */
    const bar = $('#luBar');
    const setBar = () => {
      if (!bar) return;
      const sw = track.scrollWidth, cw = track.clientWidth, max = sw - cw;
      const w = sw > 0 ? cw / sw : 1;
      const p = max > 0 ? Math.abs(track.scrollLeft) / max : 0;
      bar.style.width = (w * 100) + '%';
      bar.style.transform = 'translateX(' + ((isRTL ? -1 : 1) * (w > 0 ? (p * (1 - w) / w) * 100 : 0)) + '%)';
    };
    track.addEventListener('scroll', setBar, { passive: true });
    window.addEventListener('resize', setBar);
    setBar();

    /* arrows — "next" scrolls toward negative x on RTL pages */
    const step = () => {
      const card = track.querySelector('li.product, .pcard');
      return card ? card.getBoundingClientRect().width + 24 : 360;
    };
    const go = (dir) => track.scrollBy({ left: dir * step() * (isRTL ? -1 : 1), behavior: reduced ? 'auto' : 'smooth' });
    const prev = $('#luPrev'), next = $('#luNext');
    if (prev) prev.addEventListener('click', () => go(-1));
    if (next) next.addEventListener('click', () => go(1));

    dragScroll(track);
  }

  /* ------------------------------------------------ 5 · anatomy scrollytelling */
  function anatomy() {
    const sec = $('#anatomy');
    const stage = $('.an-stage');
    if (!sec || !stage || !B) return;

    const p = B.PRODUCTS.find((item) => item.cat === 'mattresses' && item.layers && item.layers.length);
    if (!p) return;
    const layers = (B.loc(p, 'layers') || []).slice(0, 6); /* ACF product layers */
    if (!layers) return;

    /* geometry: stacked slabs inside a 560 × 520 viewBox */
    const HEIGHTS = [22, 46, 52, 34, 92, 56];
    const W = 560, VH = 520, X = 42, SW = 250, GAP = 26;
    const total = HEIGHTS.reduce((a, b) => a + b, 0);
    const off = HEIGHTS.map((_, i) => (i - (HEIGHTS.length - 1) / 2) * GAP);
    const FILLS = ['url(#anKnit)', 'url(#anQuilt)', '#9FC2EC', '#BDE7EF', '#12294A', '#1B3A66'];

    const defs = `<defs>
      <pattern id="anKnit" width="7" height="7" patternUnits="userSpaceOnUse">
        <rect width="7" height="7" fill="#F3EDDF"/>
        <path d="M1.5 0v7M5 0v7" stroke="rgba(14,31,56,.08)" stroke-width="1.4"/>
      </pattern>
      <pattern id="anQuilt" width="28" height="28" patternUnits="userSpaceOnUse">
        <rect width="28" height="28" fill="#DCE9F8"/>
        <path d="M0 14L14 0l14 14L14 28z" fill="none" stroke="rgba(14,31,56,.12)"/>
      </pattern>
    </defs>`;

    let y = (VH - total) / 2;
    let gs = '';
    layers.forEach((L, i) => {
      const h = HEIGHTS[i], my = y + h / 2;
      let extra = '';
      if (i === 3) { /* gel band — dashed airflow lines */
        extra = `<path d="M${X + 18} ${my - 3}h${SW - 36}M${X + 32} ${my + 7}h${SW - 64}"
          stroke="rgba(255,255,255,.55)" stroke-width="1.5" stroke-dasharray="7 9" fill="none" stroke-linecap="round"/>`;
      }
      if (i === 4) { /* coil row — springs drawn as circles */
        let c = '';
        for (let k = 0; k < 7; k++) {
          const cx = X + 24 + k * ((SW - 48) / 6);
          c += `<circle cx="${cx}" cy="${my}" r="15" fill="none" stroke="rgba(159,194,236,.6)" stroke-width="1.5"/>
                <circle cx="${cx}" cy="${my}" r="6.5" fill="none" stroke="rgba(159,194,236,.35)" stroke-width="1.2"/>`;
        }
        extra = c;
      }
      gs += `<g class="an-slab" data-i="${i}">
        <rect x="${X}" y="${y}" width="${SW}" height="${h}" rx="9" fill="${FILLS[i]}" stroke="rgba(233,239,248,.14)"/>
        ${extra}
        <line class="an-lab" x1="${X + SW + 6}" y1="${my}" x2="${X + SW + 24}" y2="${my}" stroke="rgba(159,194,236,.5)" stroke-width="1"/>
        <text class="an-lab an-idx" x="${X + SW + 32}" y="${my - 4}">0${i + 1}</text>
        <text class="an-lab an-ttl" x="${X + SW + 32}" y="${my + 15}">${L[0]}</text>
      </g>`;
      y += h;
    });

    stage.innerHTML = `<svg class="an-svg" viewBox="0 0 ${W} ${VH}" xmlns="http://www.w3.org/2000/svg"
      role="img" aria-label="${S.anatomyAlt}">${defs}${gs}</svg>`;

    const groups = $$('.an-slab', stage);

    /* active-layer sync for the sticky copy */
    let cur = -1;
    const setActive = (i) => {
      if (i === cur || !layers[i]) return;
      cur = i;
      const n = $('#anNum'), nm = $('#anName'), d = $('#anDesc'), box = $('#anActive');
      if (n) n.textContent = '0' + (i + 1);
      if (nm) nm.textContent = layers[i][0];
      if (d) d.textContent = layers[i][1];
      if (box) { box.classList.remove('swap'); void box.offsetWidth; box.classList.add('swap'); }
      groups.forEach((g, k) => g.classList.toggle('dim', k !== i));
    };

    setActive(0);
    const pinned = hasGsap() && !reduced && window.matchMedia('(min-width: 961px)').matches;
    sec.classList.toggle('an-pinned', pinned);

    if (pinned) {
      /* pinned scrollytelling: slabs separate, labels fade in, copy swaps */
      const labels = groups.map((g) => $$('.an-lab', g));
      labels.forEach((l) => gsap.set(l, { opacity: 0 }));
      setActive(0);

      const tl = gsap.timeline({
        scrollTrigger: {
          trigger: sec,
          start: 'top top',
          end: '+=250%',
          scrub: 0.6,
          pin: true,
          anticipatePin: 1,
          onUpdate: (st) => setActive(Math.min(layers.length - 1, Math.floor(st.progress * layers.length))),
        },
      });
      groups.forEach((g, i) => {
        tl.to(g, { y: off[i], duration: 1, ease: 'power2.inOut' }, i * 0.5);
        tl.to(labels[i], { opacity: 1, duration: 0.5 }, i * 0.5 + 0.35);
      });
    } else {
      /* static exploded view + revealed list */
      groups.forEach((g, i) => g.setAttribute('transform', `translate(0 ${off[i]})`));
      const list = $('#anList');
      if (list) {
        list.innerHTML = layers
          .map((L, i) => `<li data-reveal style="--rd:${(i * 0.07).toFixed(2)}s">
            <span class="an-li-num">0${i + 1}</span>
            <div><b>${L[0]}</b><p>${L[1]}</p></div>
          </li>`)
          .join('');
        reveal(list);
      }
    }
  }

  /* WordPress-safe Anatomy renderer. It consumes the dedicated homepage ACF
     data exposed by BlueTheme and never depends on a particular product slug,
     layer count, or GSAP pinning support. */
  function anatomyWp() {
    const sec = $('#anatomy');
    const stage = $('.an-stage');
    const list = $('#anList');
    if (!sec || !stage || !list || !B) return;

    const safe = (value) => String(value || '').replace(/[&<>"']/g, (char) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[char]));
    const source = B.ANATOMY && Array.isArray(B.ANATOMY.layers) ? B.ANATOMY.layers : [];
    const layers = source
      .map((layer) => {
        if (Array.isArray(layer)) {
          return { title: layer[0], description: layer[1] };
        }
        return {
          title: layer.title,
          description: layer.description,
        };
      })
      .filter((layer) => layer.title)
      .slice(0, 6);
    if (!layers.length) return;

    /* Keep the WordPress SVG in lockstep with the current static demo. The
       schematic is driven by each editable ACF title, so translated or
       reordered technologies retain the correct visual treatment. */
    const roleOf = (title) => {
      const value = String(title);
      if (/spring|pocket|bonnel|coil|نوابض|أساس/i.test(value)) return 'spring';
      if (/pillow ?top|euro-?top|وثيرة|علوية/i.test(value)) return 'quilt';
      if (/foam|tpe|latex|إسفنج|رغوي|لاتكس|ميموري/i.test(value)) return 'foam';
      return 'fabric';
    };
    const specs = {
      fabric: { height: 26, fill: 'url(#anKnitWp)' },
      quilt: { height: 48, fill: 'url(#anQuiltWp)' },
      foam: { height: 62, fill: '#9FC2EC' },
      spring: { height: 104, fill: '#12294A' },
    };
    const roles = layers.map((layer) => roleOf(layer.title));
    const heights = roles.map((role) => specs[role].height);
    const fills = roles.map((role) => specs[role].fill);
    const width = 560;
    const viewHeight = 520;
    const slabX = 42;
    const slabWidth = 250;
    const gap = 26;
    const totalHeight = heights.reduce((sum, height) => sum + height, 0);
    const offsets = heights.map((_, index) => (index - (heights.length - 1) / 2) * gap);

    const defs = `<defs>
      <pattern id="anKnitWp" width="7" height="7" patternUnits="userSpaceOnUse">
        <rect width="7" height="7" fill="#F3EDDF"/>
        <path d="M1.5 0v7M5 0v7" stroke="rgba(14,31,56,.08)" stroke-width="1.4"/>
      </pattern>
      <pattern id="anQuiltWp" width="28" height="28" patternUnits="userSpaceOnUse">
        <rect width="28" height="28" fill="#DCE9F8"/>
        <path d="M0 14L14 0l14 14L14 28z" fill="none" stroke="rgba(14,31,56,.12)"/>
      </pattern>
    </defs>`;

    let y = (viewHeight - totalHeight) / 2;
    const slabs = layers.map((layer, index) => {
      const slabHeight = heights[index];
      const middle = y + slabHeight / 2;
      const role = roles[index];
      let detail = '';
      if (role === 'foam') {
        detail = `<path d="M${slabX + 18} ${middle - 3}h${slabWidth - 36}M${slabX + 32} ${middle + 7}h${slabWidth - 64}" stroke="rgba(255,255,255,.55)" stroke-width="1.5" stroke-dasharray="7 9" fill="none" stroke-linecap="round"/>`;
      }
      if (role === 'spring') {
        const coils = Array.from({ length: 7 }, (_, coilIndex) => {
          const cx = slabX + 24 + coilIndex * ((slabWidth - 48) / 6);
          return `<rect x="${cx - 15}" y="${middle - slabHeight / 2 + 10}" width="30" height="${slabHeight - 20}" rx="7" fill="none" stroke="rgba(159,194,236,.28)" stroke-width="1.1"/>
            <circle cx="${cx}" cy="${middle}" r="15" fill="none" stroke="rgba(159,194,236,.6)" stroke-width="1.5"/>
            <circle cx="${cx}" cy="${middle}" r="6.5" fill="none" stroke="rgba(159,194,236,.35)" stroke-width="1.2"/>`;
        }).join('');
        detail = coils;
      }
      const markup = `<g class="an-slab" data-i="${index}">
        <rect x="${slabX}" y="${y}" width="${slabWidth}" height="${slabHeight}" rx="9" fill="${fills[index]}" stroke="rgba(233,239,248,.14)"/>
        ${detail}
        <line class="an-lab" x1="${slabX + slabWidth + 6}" y1="${middle}" x2="${slabX + slabWidth + 24}" y2="${middle}" stroke="rgba(159,194,236,.5)" stroke-width="1"/>
        <text class="an-lab an-idx" x="${slabX + slabWidth + 32}" y="${middle - 4}">${String(index + 1).padStart(2, '0')}</text>
        <text class="an-lab an-ttl" x="${slabX + slabWidth + 32}" y="${middle + 15}">${safe(layer.title)}</text>
      </g>`;
      y += slabHeight;
      return markup;
    }).join('');

    stage.innerHTML = `<svg class="an-svg" viewBox="0 0 ${width} ${viewHeight}" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="${safe(S.anatomyAlt)}">${defs}${slabs}</svg>`;
    list.innerHTML = layers.map((layer, index) => `<li data-layer="${index}" tabindex="0">
      <span class="an-li-num">${String(index + 1).padStart(2, '0')}</span>
      <div><b>${safe(layer.title)}</b><p>${safe(layer.description)}</p></div>
    </li>`).join('');

    const groups = $$('.an-slab', stage);
    let current = -1;
    const setActive = (index) => {
      if (!layers[index] || index === current) return;
      current = index;
      const number = $('#anNum');
      const name = $('#anName');
      const description = $('#anDesc');
      const active = $('#anActive');
      if (number) number.textContent = String(index + 1).padStart(2, '0');
      if (name) name.textContent = layers[index].title;
      if (description) description.textContent = layers[index].description || '';
      if (active) { active.classList.remove('swap'); void active.offsetWidth; active.classList.add('swap'); }
      groups.forEach((group, groupIndex) => {
        group.classList.toggle('dim', groupIndex !== index);
      });
    };

    const pinned = hasGsap() && !reduced && window.matchMedia('(min-width: 961px)').matches;
    sec.classList.toggle('an-pinned', pinned);

    if (pinned) {
      const labels = groups.map((group) => $$('.an-lab', group));
      labels.forEach((label) => gsap.set(label, { opacity: 0 }));
      setActive(0);
      const timeline = gsap.timeline({
        scrollTrigger: {
          trigger: sec,
          start: 'top top',
          end: '+=250%',
          scrub: .6,
          pin: true,
          anticipatePin: 1,
          onUpdate: (state) => setActive(Math.min(layers.length - 1, Math.floor(state.progress * layers.length))),
        },
      });
      groups.forEach((group, index) => {
        timeline.to(group, { y: offsets[index], duration: 1, ease: 'power2.inOut' }, index * .5);
        timeline.to(labels[index], { opacity: 1, duration: .5 }, index * .5 + .35);
      });
    } else {
      groups.forEach((group, index) => group.setAttribute('transform', `translate(0 ${offsets[index]})`));
      list.innerHTML = layers.map((layer, index) => `<li data-reveal style="--rd:${(index * .07).toFixed(2)}s">
        <span class="an-li-num">${String(index + 1).padStart(2, '0')}</span>
        <div><b>${safe(layer.title)}</b><p>${safe(layer.description)}</p></div>
      </li>`).join('');
      reveal(list);
    }
  }

  /* ------------------------------------------------ 6 · find your feel
     PHP renders the WooCommerce products; this only filters/highlights them. */
  function feelFinderWp() {
    const range = $('#fzRange');
    const tilesWrap = $('#fzTiles');
    if (!range || !tilesWrap) return;

    const safe = (value) => String(value || '').replace(/[&<>"']/g, (char) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[char]));

    const readout = $('#fzReadout');
    const number = $('#fzNum');
    const zone = $('#fzZone');
    const recommendation = $('#fzRec');
    const tiles = $$('.fz-tile', tilesWrap);
    const beds = tiles.map((tile, index) => ({
      id: tile.dataset.productId || String(index),
      name: tile.dataset.productName || $('.fz-tname', tile)?.textContent || '',
      tag: tile.dataset.productTag || '',
      feel: Math.max(1, Math.min(10, Number(tile.dataset.feel))),
    })).filter((product) => Number.isFinite(product.feel));
    if (!beds.length) return;

    const zoneOf = (value) => value <= 4 ? S.zones.plush : (value <= 7 ? S.zones.balanced : S.zones.firm);

    const update = () => {
      const value = Number(range.value);
      const percent = (value - 1) / 9;
      const displayedPercent = percent; // The range is explicitly LTR in both locales.
      if (number) number.textContent = value;
      if (zone) zone.textContent = zoneOf(value);
      if (readout) readout.style.left = `calc(${(displayedPercent * 100).toFixed(2)}% + ${((.5 - displayedPercent) * 28).toFixed(1)}px)`;
      range.style.setProperty('--fill', `${(percent * 100).toFixed(2)}%`);
      range.setAttribute('aria-valuetext', S.valuetext(value, zoneOf(value)));

      const ranked = beds.map((product, index) => ({ product, index, distance: Math.abs(product.feel - value) }))
        .sort((a, b) => a.distance - b.distance || a.index - b.index);
      const closest = new Set(ranked.slice(0, Math.min(3, ranked.length)).map((entry) => entry.product.id));
      tiles.forEach((tile, index) => tile.classList.toggle('on', closest.has(beds[index].id)));

      const best = ranked[0].product;
			const clause = best.tag || H.feelRecommendationFallback || '';
      if (recommendation) recommendation.innerHTML = S.rec(value, safe(best.name), safe(clause));
    };

    range.addEventListener('input', update);
    range.addEventListener('change', update);
    update();
  }

  /* ------------------------------------------------ 7 · categories */
  function categories() {
    const grid = $('#catsGrid');
    if (!grid) return;
    /* Categories and thumbnails come from the PHP/WooCommerce term loop. */
    reveal(grid);
  }

  /* ------------------------------------------------ 8 · stat counters */
  function counters() {
    const nums = $$('.cs-num');
    if (!nums.length) return;
    const run = (el) => {
      const target = +el.dataset.count || 0;
      if (reduced) { el.textContent = target; return; }
      el.textContent = '0';
      const t0 = performance.now(), dur = 1600;
      const tick = (now) => {
        const p = Math.min(1, (now - t0) / dur);
        const e = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.round(target * e);
        if (p < 1) requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
    };
    const io = new IntersectionObserver((entries) => {
      entries.forEach((e) => { if (e.isIntersecting) { run(e.target); io.unobserve(e.target); } });
    }, { threshold: 0.6 });
    nums.forEach((n) => io.observe(n));
  }

  /* ------------------------------------------------ 8+10 · image parallax */
  function parallaxFx() {
    if (!hasGsap() || reduced) return;
    $$('.craft-frame img').forEach((craftImg) => {
      const craftSection = craftImg.closest('.craft');
      gsap.fromTo(craftImg, { yPercent: -5 }, {
        yPercent: 5, ease: 'none',
        scrollTrigger: { trigger: craftSection || craftImg, start: 'top bottom', end: 'bottom top', scrub: true },
      });
    });
    const lf = $('.lf-media');
    if (lf) {
      gsap.fromTo(lf, { yPercent: -7 }, {
        yPercent: 7, ease: 'none',
        scrollTrigger: { trigger: '.lifest', start: 'top bottom', end: 'bottom top', scrub: true },
      });
    }
  }

  /* ------------------------------------------------ 9 · reviews carousel */
  function reviews() {
    const track = $('#rvTrack');
    if (!track || !B || !UI) return;

    track.innerHTML = B.REVIEWS
      .map((r, i) => {
        /* REVIEWS.product stores the EN model name — map to the product
           for a localized chip */
        const pr = B.PRODUCTS.find((p) => p.name === r.product);
        return `<article class="rv-card" data-reveal style="--rd:${((i % 4) * 0.07).toFixed(2)}s">
        <div class="rv-stars">${UI.stars(r.stars)}</div>
        <h3 class="rv-title display">${B.loc(r, 'title')}</h3>
        <p class="rv-body">${B.loc(r, 'body')}</p>
        <footer class="rv-foot">
          <span class="rv-name">${B.loc(r, 'name')} — ${B.loc(r, 'city')}</span>
          <span class="chip chip-soft">${pr ? B.loc(pr, 'name') : r.product}</span>
        </footer>
      </article>`;
      })
      .join('');
    reveal(track);

    const cards = $$('.rv-card', track);
    const dotsWrap = $('#rvDots');
    let dots = [];
    if (dotsWrap) {
      dotsWrap.innerHTML = cards.map((_, i) =>
        `<button type="button" data-dot="${i}" aria-label="${S.goReview(i + 1)}"></button>`).join('');
      dots = $$('button', dotsWrap);
    }

    const cardStep = () => (cards.length > 1 ? cards[1].offsetLeft - cards[0].offsetLeft : 380);
    const activeIdx = () => Math.max(0, Math.min(cards.length - 1, Math.round(track.scrollLeft / cardStep())));
    const paint = () => { const a = activeIdx(); dots.forEach((d, i) => d.classList.toggle('on', i === a)); };
    track.addEventListener('scroll', paint, { passive: true });
    paint();

    if (dotsWrap) {
      dotsWrap.addEventListener('click', (e) => {
        const b = e.target.closest('[data-dot]');
        if (b) track.scrollTo({ left: +b.dataset.dot * cardStep(), behavior: reduced ? 'auto' : 'smooth' });
      });
    }

    /* auto-advance — pauses on hover, touch, drag, hidden tab, off-screen */
    let timer = null;
    const play = () => {
      if (reduced || timer) return;
      timer = setInterval(() => {
        /* |scrollLeft| handles RTL (0 → -max); cardStep() is already signed */
        const max = track.scrollWidth - track.clientWidth;
        const atEnd = Math.abs(track.scrollLeft) >= max - 8;
        const next = atEnd ? 0 : (activeIdx() + 1) * cardStep();
        track.scrollTo({ left: next, behavior: 'smooth' });
      }, 4600);
    };
    const stop = () => { clearInterval(timer); timer = null; };

    track.addEventListener('mouseenter', stop);
    track.addEventListener('mouseleave', play);
    track.addEventListener('pointerdown', stop);
    track.addEventListener('touchstart', stop, { passive: true });
    track.addEventListener('focusin', stop);
    track.addEventListener('focusout', (e) => {
      if (!track.contains(e.relatedTarget)) play();
    });
    document.addEventListener('visibilitychange', () => (document.hidden ? stop() : play()));
    const vio = new IntersectionObserver((entries) => {
      entries.forEach((e) => (e.isIntersecting ? play() : stop()));
    }, { threshold: 0.3 });
    vio.observe(track);

    dragScroll(track);
  }

  /* ------------------------------------------------ boot */
  const boot = () => {
    heroVideo();
    heroFx();
    lineup();
    anatomyWp();
    feelFinderWp();
    categories();
    counters();
    parallaxFx();
    reviews();
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
