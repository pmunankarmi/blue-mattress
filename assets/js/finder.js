(() => {
  'use strict';

  const root = document.querySelector('.blue-mattress-finder');
  if (!root) return;

  const isArabic = Boolean(window.BlueTheme?.isArabic);
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const quiz = document.getElementById('blueFinderQuiz');
  const resultsSection = document.getElementById('finder-results');
  const results = document.getElementById('blueFinderResults');
  const progress = document.getElementById('blueFinderProgress');
  const back = document.getElementById('blueFinderBack');
  const reset = document.getElementById('blueFinderReset');
  const summary = document.getElementById('blueFinderSummary');
  const steps = quiz ? Array.from(quiz.querySelectorAll('[data-finder-step]')) : [];
  const cards = results ? Array.from(results.querySelectorAll('[data-finder-card]')) : [];
  const answers = {};
  let stepIndex = 0;
  let matchedProductId = '';

  const toNumber = (value, fallback = 0) => {
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : fallback;
  };
  const profile = (element) => ({
    element,
    id: String(element.dataset.productId || ''),
    key: String(element.dataset.productKey || ''),
    name: String(element.dataset.productName || ''),
    feel: toNumber(element.dataset.feel, 6),
    positions: String(element.dataset.positions || '').split(',').filter(Boolean),
    cooling: toNumber(element.dataset.cooling, 6),
    motion: toNumber(element.dataset.motion, 6),
    price: toNumber(element.dataset.price, 0),
  });

  const whoBonus = {
    two: { horizon: 2, royal: 1, comfy: 2.3, loft: 1.2 },
    child: { haven: 5, retro: 2 },
    guest: { retro: 2.5, haven: 1.8 },
  };
  const positionBonus = {
    side: { haven: 2.5, cloud: 1.8, royal: 1 },
    back: { loft: 2.5, horizon: 1.5, summit: 1.2 },
    stomach: { retro: 2.5, royal: 1.4, summit: 1.2 },
    mixed: { comfy: 2.5, horizon: 1.5 },
  };
  const heatBonus = {
    very: { horizon: 2.5, comfy: 1.2, loft: 1.2, haven: 1.2 },
    sometimes: { horizon: 1.2, loft: 0.6 },
  };

  const scoreCard = (card) => {
    const item = profile(card);
    const feelTarget = { soft: 4, balanced: 5.5, firm: 7.4 }[answers.feel];
    let score = 0;
    if (feelTarget != null) {
      const gap = Math.abs(item.feel - feelTarget);
      score += Math.max(0, 3 - gap);
      score -= Math.max(0, gap - 1.5) * 1.4;
    }
    if (answers.position) {
      score += item.positions.includes(answers.position) ? 2.2 : 0;
      score += positionBonus[answers.position]?.[item.key] || 0;
    }
    score += whoBonus[answers.who]?.[item.key] || 0;
    score += heatBonus[answers.temperature]?.[item.key] || 0;
    if (answers.temperature === 'very') score += Math.max(0, item.cooling - 5) * 0.45;
    if (answers.who === 'two') score += Math.max(0, item.motion - 5) * 0.4;

    const bands = { b1: [0, 3000], b2: [3000, 5000], b3: [5000, Infinity] };
    const band = bands[answers.budget];
    if (band) {
      if (item.price >= band[0] && item.price <= band[1]) score += 2;
      else if (item.price < band[0]) score += 0.8;
      else score -= 2.5 + Math.min(4, (item.price - band[1]) / 1000);
    }
    return { ...item, score };
  };

  const updateComparisonMatch = () => {
    root.querySelectorAll('[data-compare-product]').forEach((item) => {
      const matches = matchedProductId && item.dataset.productId === matchedProductId;
      item.classList.toggle('is-match', Boolean(matches));
      item.querySelector('.finder-match-chip')?.toggleAttribute('hidden', !matches);
    });
  };

  const showStep = (next, focus = true) => {
    if (!steps.length) return;
    stepIndex = Math.max(0, Math.min(steps.length - 1, next));
    steps.forEach((step, index) => { step.hidden = index !== stepIndex; });
    if (progress) progress.style.width = `${(stepIndex / steps.length) * 100}%`;
    if (back) back.hidden = stepIndex === 0;
    if (focus) steps[stepIndex].querySelector('.wiz-q')?.focus({ preventScroll: true });
  };

  const showResults = () => {
    if (!cards.length || !resultsSection || !quiz) return;
    const ranked = cards.map(scoreCard).sort((a, b) => b.score - a.score || a.price - b.price);
    const scores = ranked.map((item) => item.score);
    const minimum = Math.min(...scores);
    const maximum = Math.max(...scores);
    ranked.forEach((item, rank) => {
      const percent = Math.min(99, Math.round(72 + 27 * ((item.score - minimum) / ((maximum - minimum) || 1))));
      item.element.style.order = String(rank);
      item.element.hidden = rank > 2;
      item.element.classList.toggle('is-best-match', rank === 0);
      item.element.classList.toggle('is-runner-match', rank > 0 && rank < 3);
      const label = item.element.querySelector('.finder-match');
      if (label) label.textContent = rank === 0
        ? `${quiz.dataset.bestLabel} · ${percent}%`
        : `${percent}% ${quiz.dataset.matchLabel}`;
    });

    const best = ranked[0];
    matchedProductId = best?.id || '';
    if (summary && best) summary.textContent = isArabic
      ? `${best.name} هي الأقرب إلى وضعية نومك، والإحساس الذي تفضله، ودرجة الحرارة والميزانية.`
      : `${best.name} is the closest fit for your sleep position, preferred feel, temperature and budget.`;
    if (progress) progress.style.width = '100%';
    quiz.hidden = true;
    resultsSection.hidden = false;
    updateComparisonMatch();
    try { window.localStorage.setItem('blue_finder_match', matchedProductId); } catch (error) {}
    resultsSection.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'start' });
  };

  quiz?.addEventListener('click', (event) => {
    const button = event.target.closest('[data-finder-answer]');
    if (!button) return;
    answers[button.dataset.finderAnswer] = button.dataset.value;
    button.closest('.wiz-opts')?.querySelectorAll('.wiz-opt').forEach((option) => {
      option.classList.toggle('is-on', option === button);
      option.setAttribute('aria-pressed', option === button ? 'true' : 'false');
    });
    if (stepIndex === steps.length - 1) window.setTimeout(showResults, 140);
    else window.setTimeout(() => showStep(stepIndex + 1), 120);
  });
  back?.addEventListener('click', () => showStep(stepIndex - 1));
  reset?.addEventListener('click', () => {
    Object.keys(answers).forEach((key) => delete answers[key]);
    quiz?.querySelectorAll('.wiz-opt').forEach((option) => {
      option.classList.remove('is-on');
      option.setAttribute('aria-pressed', 'false');
    });
    cards.forEach((card) => {
      card.hidden = false;
      card.style.order = '';
      card.classList.remove('is-best-match', 'is-runner-match');
    });
    matchedProductId = '';
    updateComparisonMatch();
    try { window.localStorage.removeItem('blue_finder_match'); } catch (error) {}
    resultsSection.hidden = true;
    quiz.hidden = false;
    showStep(0, false);
    quiz.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'center' });
  });

  /* Guided / comparison switch with V12 deep links. */
  const switcher = document.getElementById('blueFinderSwitch');
  const modeNote = document.getElementById('blueFinderModeNote');
  const modeNotes = isArabic ? {
    guided: 'أجب عن خمسة أسئلة سريعة، ونرشّح لك الأنسب.',
    compare: 'قارن كل المراتب جنبًا إلى جنب.',
  } : {
    guided: 'Answer five quick questions and we will match you.',
    compare: 'Compare every mattress side by side.',
  };
  const setMode = (mode, updateUrl = true) => {
    const selected = mode === 'compare' ? 'compare' : 'guided';
    root.querySelectorAll('[data-finder-pane]').forEach((pane) => { pane.hidden = pane.dataset.finderPane !== selected; });
    switcher?.querySelectorAll('[data-finder-mode]').forEach((button) => {
      const active = button.dataset.finderMode === selected;
      button.classList.toggle('is-on', active);
      button.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
    if (modeNote) modeNote.textContent = modeNotes[selected];
    if (updateUrl) {
      try { window.history.replaceState(null, '', `${window.location.pathname}${window.location.search}#${selected}`); } catch (error) {}
    }
  };
  switcher?.addEventListener('click', (event) => {
    const button = event.target.closest('[data-finder-mode]');
    if (button) setMode(button.dataset.finderMode);
  });
  window.addEventListener('hashchange', () => setMode(window.location.hash.slice(1), false));

  /* V12 comparison priority highlighting. Product cells remain PHP-rendered. */
  const highlights = new Set();
  const highlightWrap = document.getElementById('blueFinderHighlights');
  const callout = document.getElementById('blueFinderHighlightCallout');
  const compareGrid = document.getElementById('blueFinderCompareGrid');
  const compareProducts = compareGrid ? Array.from(compareGrid.querySelectorAll('[data-compare-product]')) : [];
  const minimumPrice = Math.min(...compareProducts.map((item) => toNumber(item.dataset.price, 0)));
  const maximumPrice = Math.max(...compareProducts.map((item) => toNumber(item.dataset.price, 0)));
  const rowFor = { cooling: 'cool', motion: 'motion', firm: 'feel', soft: 'feel', value: 'price' };

  const dimension = (item, key) => {
    if (key === 'cooling') return toNumber(item.dataset.cooling, 0);
    if (key === 'motion') return toNumber(item.dataset.motion, 0);
    if (key === 'firm') return toNumber(item.dataset.feel, 0);
    if (key === 'soft') return 10 - toNumber(item.dataset.feel, 0);
    if (key === 'value') return 10 * (maximumPrice - toNumber(item.dataset.price, 0)) / ((maximumPrice - minimumPrice) || 1);
    return 0;
  };

  const applyHighlights = () => {
    const activeRows = new Set(Array.from(highlights).map((key) => rowFor[key]));
    root.querySelectorAll('#pane-compare [data-row]').forEach((row) => row.classList.toggle('row-hl', activeRows.has(row.dataset.row)));
    let best = null;
    if (highlights.size) {
      compareProducts.forEach((item) => {
        const score = Array.from(highlights).reduce((total, key) => total + dimension(item, key), 0);
        if (!best || score > best.score) best = { score, id: item.dataset.productId, name: item.dataset.productName, col: item.dataset.col };
      });
    }
    root.querySelectorAll('#pane-compare [data-col]').forEach((cell) => cell.classList.toggle('col-best', Boolean(best && cell.dataset.col === best.col)));
    root.querySelectorAll('#blueFinderCompareCards [data-compare-product]').forEach((card) => card.classList.toggle('col-best', Boolean(best && card.dataset.productId === best.id)));
    if (callout) {
      callout.hidden = !best;
      callout.textContent = best ? `✓ ${callout.dataset.prefix} ${best.name}` : '';
    }
  };

  highlightWrap?.addEventListener('click', (event) => {
    const button = event.target.closest('[data-finder-highlight]');
    if (!button) return;
    const key = button.dataset.finderHighlight;
    if (highlights.has(key)) highlights.delete(key); else highlights.add(key);
    button.setAttribute('aria-pressed', highlights.has(key) ? 'true' : 'false');
    applyHighlights();
  });

  compareGrid?.addEventListener('mouseover', (event) => {
    const cell = event.target.closest('[data-col]');
    const column = cell?.dataset.col;
    compareGrid.querySelectorAll('[data-col]').forEach((item) => item.classList.toggle('hl', Boolean(column && column !== '0' && item.dataset.col === column)));
  });
  compareGrid?.addEventListener('mouseleave', () => compareGrid.querySelectorAll('[data-col]').forEach((item) => item.classList.remove('hl')));

  try { matchedProductId = window.localStorage.getItem('blue_finder_match') || ''; } catch (error) {}
  updateComparisonMatch();
  showStep(0, false);
  setMode(window.location.hash.slice(1), false);
})();
