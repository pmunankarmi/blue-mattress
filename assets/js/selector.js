/* ============================================================
   BLUE — Mattress Selector (EN + AR from one file)
   ONE page, FOUR fully-working ways to choose a mattress over
   the same six beds. The client picks a favourite paradigm;
   end-users pick a bed.

   · Guided   (#guided)  — 5-step quiz, deterministic scoring,
                           tailored why-bullets, runner-ups.
   · Filters  (#filters) — live reactive control panel; all six
                           beds re-rank instantly, no submit.
   · Swipe    (#swipe)   — this-or-that deck → the guided result.
   · Compare  (#compare) — six beds side by side + a "highlight
                           what matters" priority overlay.

   Deep links: #guided | #filters | #swipe | #compare, kept in
   the URL so the chrome language switcher (twinUrl → hash)
   carries the mode across locales.
   Requires data.js (BLUE) + main.js (BlueUI). Every visible
   string resolves through BLUE.t / BLUE.loc or the S dict.
   ============================================================ */

(() => {
  const { fmt, priceFrom, byId, t, loc, href } = BLUE;
  const AR = BLUE.AR();
  const REDUCED = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const KEY = 'blue_selector_v1';
  const tpl = (s, v) => String(s).replace(/\{(\w+)\}/g, (_, k) => (v[k] != null ? v[k] : ''));

  /* ============================================================
     STRINGS — one local dictionary covering every injected
     string across all four modes. Product fields still resolve
     via BLUE.loc; prices via BLUE.fmt; links via BLUE.href.
     ============================================================ */
  const S = AR ? {
    /* switch + per-mode one-liners */
    modeNote: {
      guided: 'أجب عن خمسة أسئلة سريعة، ونرشّح لك الأنسب.',
      filters: 'اضبط تفضيلاتك، وشاهد الترتيب يتحدّث أمامك فورًا.',
      swipe: 'هذه أم تلك؟ اختر بلمسة، ونصل بك إلى مرتبتك.',
      compare: 'الأسرّة الستة، جنبًا إلى جنب.',
    },

    /* ---- guided wizard ---- */
    stepOf: 'السؤال {a} من {b}',
    back: '→ رجوع',
    q1: 'لمن هذا السرير؟',
    q1o: { me: 'لي وحدي', two: 'لنا نحن الاثنين', child: 'لطفل', guest: 'غرفة الضيوف' },
    q2: 'كيف تنام عادةً؟',
    q2o: { side: 'على جنبي', back: 'على ظهري', stomach: 'على بطني', varies: 'أتقلّب كثيرًا' },
    q3: 'أي إحساس تفضّل؟',
    q3o: { soft: 'ناعمة كالغيمة', balanced: 'متوازنة', firm: 'صلبة', unsure: 'لست متأكدًا' },
    q4: 'هل تشعر بالحرارة أثناء النوم؟',
    q4o: { very: 'كثيرًا', sometimes: 'أحيانًا', rarely: 'نادرًا' },
    q5: 'كم ميزانيتك لهذا السرير؟',
    under: 'أقل من {x}', between: 'من {a} إلى {b}', above: 'أكثر من {x}', anyBudget: 'أرني كل الخيارات',

    /* ---- shared result ---- */
    yourMatch: 'اخترناها لك',
    lastNote: 'هذه نتيجتك من زيارتك هذه، أو ابدأ من جديد في الأسفل.',
    startOver: 'ابدأ من جديد',
    runnersTitle: 'خيارات أخرى تستحقّ نظرة',
    match: 'تطابق {n}%',
    sideBySide: 'قارنها جنبًا إلى جنب',
    view: 'اكتشف {name}',

    /* ---- compare matrix ---- */
    rowFeel: 'الإحساس', rowHeight: 'الارتفاع', rowBest: 'الأنسب لـ', rowCool: 'التبريد',
    rowMotion: 'عزل الحركة', rowTrial: 'التجربة والضمان', rowPrice: 'السعر يبدأ من',
    trialVal: 'تجربة 100 ليلة · ضمان 10 سنوات',
    cm: '{n} سم',
    outOf5: '{n} من 5',
    best: {
      horizon: 'الأزواج ومعظم النائمين',
      royal: 'فخامة فنادق الخمس نجوم',
      reef: 'من يشعرون بالحرارة ليلًا',
      summit: 'النوم على الظهر والبطن، ولأصحاب الحساسية',
      cloud: 'النوم على الجنب وعشّاق النعومة',
      haven: 'الأطفال والناشئة',
    },
    hlTitle: 'أبرِز ما يهمّك',
    hl: { cooling: 'التبريد', motion: 'عزل الحركة', firm: 'الدعم الصلب', value: 'أفضل قيمة', soft: 'النعومة' },
    hlCallout: 'الأنسب لأولوياتك: {bed}',

    /* ---- guided why-bullets (composed from the actual answers) ---- */
    bl: {
      side: 'تخفيف لطيف للضغط حيث يحتاجه النائم على جنبه — عند الكتفين والوركين.',
      back: 'دعم مستوٍ متوازن يحافظ على استقامة عمودك الفقري عند النوم على الظهر.',
      stomach: 'دعم صلب ومستوٍ يمنع وركيك من الغوص عند النوم على البطن.',
      varies: 'إحساس متجاوب ومتوازن يتحرك معك أيًّا كانت وضعية نومك.',
      heatReef: 'إسفنج الجرافيت والجل وقلب نوابض مهوّى، مصمّمة خصيصًا لمن يشعرون بحرّ شديد أثناء النوم.',
      heatVery: 'أقمشة تتنفّس وطبقات مفتوحة تُبقي الهواء متحركًا في الليالي الدافئة.',
      heatSome: 'طبقات تهوية تمنع احتباس الحرارة أثناء نومك.',
      couple: 'نوابض مستقلّة تمتص الحركة — تقلُّب أحدكما لن يوقظ الآخر.',
      child: 'دعم صلب بمواصفات مناسبة للأطفال، بمقاس وسعر يليقان بالنائمين الصغار.',
      childSafe: 'إسفنج مضادّ للحساسية ومنخفض الانبعاثات، مع غطاء يُنزع بسحّاب ويُغسل في الغسّالة.',
      feelMatch: 'درجة راحة «{feel}» — كما طلبت تمامًا.',
      budgetIn: 'تبدأ من {price} — ضمن ميزانيتك بأريحية.',
      budgetAll: 'تبدأ من {price}، مع تجربة 100 ليلة في بيتك لتطمئن.',
      budgetOut: 'تبدأ من {price} — أعلى قليلًا من ميزانيتك، لكنها الأقرب لطريقة نومك.',
      trial: 'تجربة 100 ليلة وضمان 10 سنوات على كل سرير من بلو.',
    },

    /* ---- smart filters ---- */
    fFirm: 'مستوى الصلابة',
    fZone: { plush: 'ناعمة', balanced: 'متوازنة', firm: 'صلبة' },
    fBudget: 'الميزانية',
    fBudgetUpTo: 'حتى {x}',
    fPos: 'وضعية النوم',
    fPosO: { side: 'على الجنب', back: 'على الظهر', stomach: 'على البطن', any: 'أي وضعية' },
    fPrio: 'ما الأهمّ بالنسبة لك؟',
    fPrioO: { cooling: 'التبريد', motion: 'عزل الحركة', back: 'دعم الظهر', kids: 'آمنة للأطفال' },
    fReset: 'إعادة الضبط',
    fCount: '{n} أسرّة — الأنسب أولًا',
    why: {
      cooling: 'الأبرد بين الستة — صُمّمت لمن يشعرون بالحرارة ليلًا.',
      motion: 'تمتص الحركة — لن تشعر بتقلّب شريكك.',
      firm: 'صلابة داعمة تمامًا عند الدرجة التي اخترتها.',
      soft: 'نعومة تذيب الضغط عند الإحساس الذي اخترته.',
      balanced: 'إحساس متوازن يطابق درجة الصلابة التي ضبطتها.',
      budget: 'ضمن ميزانيتك بأريحية، وتبدأ من {price}.',
      posSide: 'تريح الكتف والورك للنائم على جنبه.',
      posBack: 'دعم مستوٍ يحافظ على استقامة النائم على ظهره.',
      posStomach: 'صلبة ومستوية فلا يغوص الورك عند النوم على البطن.',
      kids: 'آمنة للأطفال، مضادة للحساسية وبالمقاس المناسب.',
      allround: 'خيار متوازن يناسب كل ما اخترته.',
    },

    /* ---- swipe / this-or-that ---- */
    swipeHint: 'اختر جهة — أو استخدم السهمين → ←',
    swipeOf: 'بطاقة {a} من {b}',
    swipeReset: 'أعد التوزيع',
    swipeGo: 'شاهد نتيجتك',
    cards: [
      { q: 'كيف تريد الإحساس؟', a: { ic: 'soft', label: 'أغوص في نعومة' }, b: { ic: 'firm', label: 'صلابة تسند ظهري' } },
      { q: 'كيف تحب أن تستيقظ؟', a: { ic: 'side', label: 'مُحتضَنًا' }, b: { ic: 'back', label: 'مرفوعًا فوقها' } },
      { q: 'حرارتك أثناء النوم؟', a: { ic: 'very', label: 'أشعر بالحرّ ليلًا' }, b: { ic: 'cool', label: 'أنام باردًا' } },
      { q: 'من سينام هنا؟', a: { ic: 'me', label: 'لي وحدي' }, b: { ic: 'two', label: 'لنا نحن الاثنين' } },
      { q: 'مزاجك في الإنفاق؟', a: { ic: 'b1', label: 'خيار اقتصادي' }, b: { ic: 'b3', label: 'أُدلّل نفسي' } },
      { q: 'لو كان عليك اختيار واحدة؟', a: { ic: 'cool', label: 'التبريد أولًا' }, b: { ic: 'soft', label: 'النعومة أولًا' } },
    ],
  } : {
    modeNote: {
      guided: "Answer five quick questions and we'll match you.",
      filters: 'Set your preferences and watch the matches reorder live.',
      swipe: 'This or that — tap your way to a match.',
      compare: 'All six beds, side by side.',
    },

    stepOf: 'Question {a} of {b}',
    back: '← Back',
    q1: 'Who is this bed for?',
    q1o: { me: 'Just me', two: 'Two of us', child: 'A child', guest: 'Guest room' },
    q2: 'How do you sleep?',
    q2o: { side: 'On my side', back: 'On my back', stomach: 'On my stomach', varies: 'It varies' },
    q3: 'What feel do you like?',
    q3o: { soft: 'Cloud-soft', balanced: 'Balanced', firm: 'Firm', unsure: 'Not sure' },
    q4: 'Do you sleep hot?',
    q4o: { very: 'Very', sometimes: 'Sometimes', rarely: 'Rarely' },
    q5: 'Budget for this bed?',
    under: 'Under {x}', between: '{a} – {b}', above: 'Above {x}', anyBudget: 'Show me everything',

    yourMatch: 'Your match',
    lastNote: 'Your result, saved from this visit — or start over below.',
    startOver: 'Start over',
    runnersTitle: 'Also worth a look',
    match: '{n}% match',
    sideBySide: 'See them side by side',
    view: 'View {name}',

    rowFeel: 'Feel', rowHeight: 'Height', rowBest: 'Best for', rowCool: 'Cooling',
    rowMotion: 'Motion isolation', rowTrial: 'Trial & warranty', rowPrice: 'Price from',
    trialVal: '100 nights · 10 years',
    cm: '{n} cm',
    outOf5: '{n} out of 5',
    best: {
      horizon: 'Couples & most sleepers',
      royal: 'Five-star luxury',
      reef: 'Hot sleepers',
      summit: 'Back & stomach sleepers, allergies',
      cloud: 'Side sleepers & softness lovers',
      haven: 'Kids & teens',
    },
    hlTitle: 'Highlight what matters to me',
    hl: { cooling: 'Cooling', motion: 'Motion', firm: 'Firm support', value: 'Value', soft: 'Softness' },
    hlCallout: 'Best for your priorities: {bed}',

    bl: {
      side: 'Gentle pressure relief right where side sleepers need it — shoulders and hips.',
      back: 'Even, level support that keeps your spine aligned when you sleep on your back.',
      stomach: 'Firm, flat support that stops your hips sinking when you sleep face-down.',
      varies: 'A responsive, balanced feel that moves with you — whatever position you land in.',
      heatReef: 'Graphite-gel foam and a ventilated spring core, engineered for genuinely hot sleepers.',
      heatVery: 'Breathable covers and open layers that keep air moving on warm nights.',
      heatSome: 'Airflow layers that stop heat building up while you sleep.',
      couple: 'Pocketed support that absorbs movement — one of you turning never wakes the other.',
      child: 'Paediatric-grade firm support, sized and priced for growing sleepers.',
      childSafe: 'Hypoallergenic, low-emission foams with a zip-off, machine-washable cover.',
      feelMatch: 'A {feel} feel — right in line with what you asked for.',
      budgetIn: 'Starts at {price} — comfortably inside your budget.',
      budgetAll: 'Starts at {price}, with a 100-night home trial to be sure.',
      budgetOut: 'Starts at {price} — a step above your budget, but the truest match to how you sleep.',
      trial: 'A 100-night trial and a 10-year warranty on every Blue bed.',
    },

    fFirm: 'Firmness',
    fZone: { plush: 'Plush', balanced: 'Balanced', firm: 'Firm' },
    fBudget: 'Budget',
    fBudgetUpTo: 'Up to {x}',
    fPos: 'Sleep position',
    fPosO: { side: 'Side', back: 'Back', stomach: 'Stomach', any: 'Any' },
    fPrio: 'What matters most?',
    fPrioO: { cooling: 'Cooling', motion: 'Motion isolation', back: 'Back support', kids: 'Kids-safe' },
    fReset: 'Reset filters',
    fCount: '{n} beds — best first',
    why: {
      cooling: 'Runs coolest of the six — built for hot sleepers.',
      motion: "Absorbs movement — you won't feel a restless partner.",
      firm: 'Firm, supportive lift right where you set the dial.',
      soft: 'Plush, pressure-melting softness at your chosen feel.',
      balanced: 'A balanced feel, matched to where you set firmness.',
      budget: 'Comfortably inside your budget, from {price}.',
      posSide: 'Cushions the shoulder and hip for side sleepers.',
      posBack: 'Even support that keeps a back sleeper aligned.',
      posStomach: "Firm and flat so hips don't sink face-down.",
      kids: 'Kid-safe, hypoallergenic and rightly sized.',
      allround: 'A well-rounded match across everything you chose.',
    },

    swipeHint: 'Pick a side — or use the ← → keys',
    swipeOf: 'Card {a} of {b}',
    swipeReset: 'Deal again',
    swipeGo: 'See my match',
    cards: [
      { q: 'How should it feel?', a: { ic: 'soft', label: 'Sink in softly' }, b: { ic: 'firm', label: 'Firm & supportive' } },
      { q: 'How do you want to wake up?', a: { ic: 'side', label: 'Cradled' }, b: { ic: 'back', label: 'Lifted on top' } },
      { q: 'Your night temperature?', a: { ic: 'very', label: 'I sleep hot' }, b: { ic: 'cool', label: 'I sleep cool' } },
      { q: "Who's sleeping here?", a: { ic: 'me', label: 'Just me' }, b: { ic: 'two', label: 'Two of us' } },
      { q: 'Your spending mood?', a: { ic: 'b1', label: 'Budget-friendly' }, b: { ic: 'b3', label: 'Treat myself' } },
      { q: 'If you had to pick one?', a: { ic: 'cool', label: 'Cooling above all' }, b: { ic: 'soft', label: 'Softness above all' } },
    ],
  };

  /* ============================================================
     CATALOG SLICES & FACTS (shared by every mode)
     ============================================================ */
  const ORDER = ['horizon', 'royal', 'reef', 'summit', 'cloud', 'haven'];
  const MATTS = ORDER.map(byId).filter(Boolean);
  const ADULTS = MATTS.filter(p => p.id !== 'haven');
  const COOL   = { reef: 5, horizon: 4, summit: 3, royal: 3, cloud: 2, haven: 3 };
  const MOTION = { royal: 5, cloud: 5, horizon: 4, reef: 4, summit: 3, haven: 3 };
  const TARGET = { soft: 3, balanced: 5.5, firm: 8 };
  const FROMS  = MATTS.map(priceFrom);
  const MIN_FROM = Math.min(...FROMS), MAX_FROM = Math.max(...FROMS);
  const url = p => `${href('product.html')}?id=${p.id}`;

  /* ---- inline line icons (guided options + swipe choices) ---- */
  const ico = d => `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${d}</svg>`;
  const I = {
    me: ico('<circle cx="12" cy="7.6" r="3.1"/><path d="M5.4 19.5c.8-3.6 3.5-5.4 6.6-5.4s5.8 1.8 6.6 5.4"/>'),
    two: ico('<circle cx="9" cy="8.2" r="2.9"/><path d="M3.2 19.5c.7-3.2 3-4.9 5.8-4.9s5.1 1.7 5.8 4.9"/><circle cx="16.6" cy="7.6" r="2.4"/><path d="M16.2 14.7c2.4.3 4.1 1.9 4.6 4.5"/>'),
    child: ico('<circle cx="12" cy="10.2" r="3"/><circle cx="8.6" cy="6.8" r="1.3"/><circle cx="15.4" cy="6.8" r="1.3"/><path d="M6.8 19.5c.7-2.9 2.7-4.4 5.2-4.4s4.5 1.5 5.2 4.4"/>'),
    guest: ico('<path d="M3 18.5v-8m0 5h18v3m-18-6h16a2 2 0 0 1 2 2v1"/><path d="M5 12.5v-2a1.5 1.5 0 0 1 1.5-1.5H8a1.5 1.5 0 0 1 1.5 1.5v2"/>'),
    side: ico('<circle cx="6.3" cy="11.6" r="2"/><path d="M8.8 12.8c2.2-1.6 3.8-1.7 5.8-.4s3.9 1.1 5.4-.4"/><path d="M3.5 17.5h17"/>'),
    back: ico('<circle cx="6.3" cy="12.4" r="2"/><path d="M8.9 13.4h11.6"/><path d="M3.5 17.5h17"/>'),
    stomach: ico('<circle cx="6.3" cy="13" r="2"/><path d="M8.8 13.2c1.8-1.4 3.2-2 5-1s4.2 1.1 6.7.3"/><path d="M3.5 17.5h17"/>'),
    varies: ico('<path d="M4.5 8h12.2m0 0-2.4-2.4M16.7 8l-2.4 2.4M19.5 16H7.3m0 0 2.4-2.4M7.3 16l2.4 2.4"/>'),
    soft: ico('<path d="M7.3 16.5a3.6 3.6 0 1 1 .5-7.2 4.6 4.6 0 0 1 8.9.9 3 3 0 0 1-.5 6.3z"/>'),
    balanced: ico('<path d="M4 12h5m6 0h5"/><circle cx="12" cy="12" r="3"/>'),
    firm: ico('<rect x="4" y="9.8" width="16" height="4.4" rx="1.2"/>'),
    unsure: ico('<path d="M9.3 9.2a2.8 2.8 0 1 1 3.9 2.6c-.9.4-1.2 1-1.2 2"/><path d="M12 17.3v.2"/>'),
    very: ico('<path d="M10.4 4.9a1.7 1.7 0 0 1 3.4 0v7.2a3.7 3.7 0 1 1-3.4 0z"/><path d="M12.1 9v5.4"/>'),
    sometimes: ico('<circle cx="12" cy="13.4" r="3.1"/><path d="M12 6.2v2M5.2 13.4h-2m17.6 0h-2M7 8.4l1.4 1.4m8.6-1.4-1.4 1.4"/>'),
    rarely: ico('<path d="M12 4.8v14.4M6.2 8.4l11.6 7.2M17.8 8.4 6.2 15.6"/>'),
    cool: ico('<path d="M12 3.4v17.2M4.6 7.7l14.8 8.6M19.4 7.7 4.6 16.3M12 3.4 9.8 5.6M12 3.4l2.2 2.2M12 20.6l-2.2-2.2M12 20.6l2.2-2.2"/>'),
    b1: ico('<circle cx="12" cy="12" r="7"/><circle cx="12" cy="12" r="2.6"/>'),
    b2: ico('<ellipse cx="12" cy="7.4" rx="6.2" ry="2.5"/><path d="M5.8 7.4v9.2c0 1.4 2.8 2.5 6.2 2.5s6.2-1.1 6.2-2.5V7.4"/><path d="M5.8 12c0 1.4 2.8 2.5 6.2 2.5s6.2-1.1 6.2-2.5"/>'),
    b3: ico('<path d="M7.2 4.8h9.6l3 4.8-7.8 9.6-7.8-9.6z"/><path d="M4.2 9.6h15.6M12 19.2 9.2 9.6l2.8-4.8 2.8 4.8z"/>'),
    all: ico('<rect x="4.2" y="4.2" width="6.4" height="6.4" rx="1.4"/><rect x="13.4" y="4.2" width="6.4" height="6.4" rx="1.4"/><rect x="4.2" y="13.4" width="6.4" height="6.4" rx="1.4"/><rect x="13.4" y="13.4" width="6.4" height="6.4" rx="1.4"/>'),
  };
  const CHECK = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>';

  /* ============================================================
     SCORING ENGINE (deterministic) — shared by guided + swipe
     ============================================================ */
  const budgetCap = b => b === 'b1' ? 3000 : b === 'b2' ? 5000 : Infinity;

  function scored(a) {
    const target = TARGET[a.feel]; /* 'unsure'/undefined → neutral on feel */
    return ADULTS.map(p => {
      let s = 0;
      if (target != null) s += Math.max(0, 3 - Math.abs(p.feel - target)); /* feel distance, wt 3 */
      if (a.pos === 'side' && (p.id === 'cloud' || p.id === 'royal')) s += 2;
      if ((a.pos === 'back' || a.pos === 'stomach') && p.id === 'summit') s += 2;
      if (a.pos === 'varies' && p.id === 'horizon') s += 2;
      if (a.heat === 'very' && p.id === 'reef') s += 4;
      if (a.heat === 'sometimes' && p.id === 'reef') s += 2;
      const from = priceFrom(p);
      if (from > budgetCap(a.budget)) s -= 3;                 /* soft penalty, never hidden */
      if (a.budget === 'b3' && from >= 4500) s += 1;          /* premium intent */
      if (a.who === 'two' && (p.id === 'horizon' || p.id === 'royal')) s += 1;
      return { p, s };
    }).sort((x, y) => (y.s - x.s) || (y.p.rating - x.p.rating) || (y.p.reviews - x.p.reviews));
  }

  /* guided: a child answer is a hard pick for the Haven kids bed */
  function guidedResult(a) {
    const list = scored(a);
    if (a.who === 'child') return { top: byId('haven'), runners: list.slice(0, 2), list };
    return { top: list[0].p, runners: list.slice(1, 3), list };
  }

  const pctFn = list => {
    const ss = list.map(x => x.s);
    const min = Math.min(...ss), max = Math.max(...ss);
    return s => Math.round(62 + 36 * ((s - min) / ((max - min) || 1)));
  };

  /* why-bullets composed from the user's actual answers */
  function buildBullets(a, p) {
    const from = priceFrom(p);
    const budget = a.budget === 'all' ? tpl(S.bl.budgetAll, { price: fmt(from) })
      : from > budgetCap(a.budget) ? tpl(S.bl.budgetOut, { price: fmt(from) })
        : tpl(S.bl.budgetIn, { price: fmt(from) });
    if (a.who === 'child') return [S.bl.child, S.bl.childSafe, budget];
    const out = [S.bl[a.pos] || S.bl.varies];
    if (a.heat === 'very') out.push(p.id === 'reef' ? S.bl.heatReef : S.bl.heatVery);
    else if (a.heat === 'sometimes') out.push(S.bl.heatSome);
    if (a.who === 'two') out.push(S.bl.couple);
    const target = TARGET[a.feel];
    if (target != null && Math.abs(p.feel - target) <= 1.5) {
      const label = loc(p, 'feelLabel');
      out.push(tpl(S.bl.feelMatch, { feel: AR ? label : String(label).toLowerCase() }));
    }
    out.push(budget, S.bl.trial);
    return out.slice(0, 3);
  }

  /* ============================================================
     PERSISTENCE — sessionStorage 'blue_selector_v1'
     { v:1, a:{who,pos,feel,heat,budget}, topId }
     ============================================================ */
  const VALID = {
    who: ['me', 'two', 'child', 'guest'],
    pos: ['side', 'back', 'stomach', 'varies'],
    feel: ['soft', 'balanced', 'firm', 'unsure'],
    heat: ['very', 'sometimes', 'rarely'],
    budget: ['b1', 'b2', 'b3', 'all'],
  };
  function readSaved() {
    try {
      const d = JSON.parse(sessionStorage.getItem(KEY));
      if (!d || d.v !== 1 || !d.a) return null;
      for (const k in VALID) if (!VALID[k].includes(d.a[k])) return null;
      return d;
    } catch { return null; }
  }
  const save = (a, topId) => { try { sessionStorage.setItem(KEY, JSON.stringify({ v: 1, a, topId })); } catch { /* private mode */ } };
  const clearSaved = () => { try { sessionStorage.removeItem(KEY); } catch { /* noop */ } };

  /* ============================================================
     SHARED RESULT RENDERER (guided + swipe)
     ============================================================ */
  function resultMarkup(res, answers, restored) {
    const p = res.top;
    const pct = pctFn(res.list);
    const bullets = buildBullets(answers, p);
    return `
      ${restored ? `<p class="res-note">${S.lastNote}</p>` : ''}
      <div class="res-hero">
        <div class="res-media"><img src="${p.img}" alt="${loc(p, 'name')} — ${loc(p, 'kind')}"></div>
        <div class="res-body">
          <span class="eyebrow">${S.yourMatch}</span>
          <h2 class="display h2 res-name" tabindex="-1">${loc(p, 'name')}</h2>
          <p class="res-kind">${loc(p, 'kind')} · ${loc(p, 'feelLabel')} · ${loc(p, 'tag')}</p>
          <ul class="res-why">
            ${bullets.map(b => `<li>${CHECK}<span>${b}</span></li>`).join('')}
          </ul>
          <p class="res-price"><span class="from-lab">${t('from')}</span>${fmt(priceFrom(p))}</p>
          <div class="res-ctas">
            <a class="btn" href="${url(p)}">${tpl(S.view, { name: loc(p, 'name') })} <span class="arr">${t('arr')}</span></a>
            <button class="btn btn-ghost" type="button" data-quick-add="${p.id}">${t('quickAdd')}</button>
          </div>
        </div>
      </div>
      <h3 class="res-sub">${S.runnersTitle}</h3>
      <div class="res-rgrid">
        ${res.runners.map(x => `
          <a class="res-rcard" href="${url(x.p)}">
            <img src="${x.p.img}" alt="">
            <span class="res-rbody">
              <b>${loc(x.p, 'name')}</b>
              <span class="res-rmeta">${loc(x.p, 'feelLabel')} · ${t('from')} ${fmt(priceFrom(x.p))}</span>
            </span>
            <span class="res-rpct">${tpl(S.match, { n: pct(x.s) })}</span>
          </a>`).join('')}
      </div>
      <div class="res-foot">
        <button class="btn btn-ghost btn-sm res-startover" type="button">${S.startOver}</button>
        <button class="res-cmplink" type="button">${S.sideBySide} <span class="arr">${t('arr')}</span></button>
      </div>`;
  }

  function renderResult(mount, res, answers, opts = {}) {
    mount.hidden = false;
    mount.innerHTML = resultMarkup(res, answers, opts.restored);
    if (!REDUCED) { mount.classList.remove('enter'); void mount.offsetWidth; mount.classList.add('enter'); }
    mount.querySelector('.res-startover').addEventListener('click', opts.onStartOver);
    mount.querySelector('.res-cmplink').addEventListener('click', () => { setMode('compare'); scrollToSwitch(); });
    if (!opts.restored) mount.querySelector('.res-name')?.focus({ preventScroll: true });
  }

  /* ============================================================
     MODE 1 — GUIDED JOURNEY (#guided)
     ============================================================ */
  const QUESTIONS = [
    { id: 'who', title: S.q1, opts: [['me', S.q1o.me], ['two', S.q1o.two], ['child', S.q1o.child], ['guest', S.q1o.guest]] },
    { id: 'pos', title: S.q2, opts: [['side', S.q2o.side], ['back', S.q2o.back], ['stomach', S.q2o.stomach], ['varies', S.q2o.varies]] },
    { id: 'feel', title: S.q3, opts: [['soft', S.q3o.soft], ['balanced', S.q3o.balanced], ['firm', S.q3o.firm], ['unsure', S.q3o.unsure]] },
    { id: 'heat', title: S.q4, opts: [['very', S.q4o.very], ['sometimes', S.q4o.sometimes], ['rarely', S.q4o.rarely]] },
    {
      id: 'budget', title: S.q5, opts: [
        ['b1', tpl(S.under, { x: fmt(3000) })],
        ['b2', tpl(S.between, { a: fmt(3000), b: fmt(5000) })],
        ['b3', tpl(S.above, { x: fmt(5000) })],
        ['all', S.anyBudget],
      ],
    },
  ];
  const BUDGET_ICON = { b1: I.b1, b2: I.b2, b3: I.b3, all: I.all };

  let gAnswers = {}, gStep = 0;
  let wizard, wizStep, wizBack, progFill, gResult;

  function gRenderStep(focusQ) {
    const q = QUESTIONS[gStep];
    progFill.style.width = (gStep / QUESTIONS.length * 100) + '%';
    wizStep.innerHTML = `
      <p class="wiz-progtext">${tpl(S.stepOf, { a: gStep + 1, b: QUESTIONS.length })}</p>
      <h2 class="wiz-q display" tabindex="-1">${q.title}</h2>
      <div class="wiz-opts${q.opts.length === 3 ? ' cols-3' : ''}">
        ${q.opts.map(([v, label]) => `
          <button class="wiz-opt${gAnswers[q.id] === v ? ' is-on' : ''}" type="button" data-val="${v}">
            <span class="wiz-ico">${q.id === 'budget' ? BUDGET_ICON[v] : I[v]}</span>
            <span class="wiz-lab">${label}</span>
          </button>`).join('')}
      </div>`;
    wizBack.hidden = gStep === 0;
    if (!REDUCED) { wizStep.classList.remove('enter'); void wizStep.offsetWidth; wizStep.classList.add('enter'); }
    if (focusQ) wizStep.querySelector('.wiz-q')?.focus({ preventScroll: true });
  }

  function gPick(val) {
    gAnswers[QUESTIONS[gStep].id] = val;
    if (gStep < QUESTIONS.length - 1) { gStep++; gRenderStep(true); }
    else gShowResult(false);
  }

  function gShowResult(restored) {
    const res = guidedResult(gAnswers);
    save(gAnswers, res.top.id);
    progFill.style.width = '100%';
    wizard.hidden = true;
    renderResult(gResult, res, gAnswers, { restored, onStartOver: gStartOver });
  }

  function gStartOver() {
    clearSaved();
    gAnswers = {}; gStep = 0;
    gResult.hidden = true; gResult.innerHTML = '';
    wizard.hidden = false;
    gRenderStep(true);
  }

  function initGuided() {
    wizard = document.getElementById('wizard');
    wizStep = document.getElementById('wizStep');
    wizBack = document.getElementById('wizBack');
    progFill = document.getElementById('progFill');
    gResult = document.getElementById('guidedResult');
    wizBack.textContent = S.back;
    wizBack.addEventListener('click', () => { if (gStep > 0) { gStep--; gRenderStep(true); } });
    wizStep.addEventListener('click', e => {
      const opt = e.target.closest('[data-val]');
      if (opt) gPick(opt.dataset.val);
    });

    const saved = readSaved();
    if (saved) { gAnswers = saved.a; gStep = QUESTIONS.length - 1; gShowResult(true); }
    else gRenderStep(false);
  }

  /* ============================================================
     MODE 2 — SMART FILTERS (#filters) — live, no submit
     ============================================================ */
  const fSt = { firm: 6, budget: 5000, pos: 'any', prio: new Set() };
  let fltBuilt = false, fltResults, firmRange, firmVal, budgetRange, budgetVal, fltCount, fDebounce;

  const zoneOf = v => v <= 4 ? 'plush' : v <= 6 ? 'balanced' : 'firm';

  function filterScore(p) {
    const contrib = {};
    let s = 0;
    const fc = Math.max(0, 5 - Math.abs(p.feel - fSt.firm)); /* firmness closeness, 0–5 */
    s += fc; contrib.feel = fc;
    const from = priceFrom(p);
    if (from <= fSt.budget) { s += 3; contrib.budget = 3; }
    else { const pen = Math.min(4, (from - fSt.budget) / 700 + 1); s -= pen; }
    let pb = 0, posWhy = null;
    if (fSt.pos === 'side' && (p.id === 'cloud' || p.id === 'royal')) { pb = 2.6; posWhy = 'posSide'; }
    else if (fSt.pos === 'back' && (p.id === 'summit' || p.id === 'horizon')) { pb = p.id === 'summit' ? 2.6 : 1.3; posWhy = 'posBack'; }
    else if (fSt.pos === 'stomach' && (p.id === 'summit' || p.id === 'haven')) { pb = p.id === 'summit' ? 2.6 : 1.3; posWhy = 'posStomach'; }
    if (pb) { s += pb; contrib[posWhy] = pb; }
    if (fSt.prio.has('cooling')) { const v = COOL[p.id] / 5 * 3.4; s += v; contrib.cooling = v; }
    if (fSt.prio.has('motion')) { const v = MOTION[p.id] / 5 * 3.4; s += v; contrib.motion = v; }
    if (fSt.prio.has('back')) { const v = Math.max(0, p.feel - 4) / 6 * 3; s += v; if (v) contrib.back = v; }
    if (fSt.prio.has('kids')) { const v = p.id === 'haven' ? 3.6 : p.id === 'summit' ? 1.4 : 0; if (v) { s += v; contrib.kids = v; } }
    return { p, s, from, contrib };
  }

  function whyLine(row) {
    /* pick the single strongest positive contribution and voice it */
    const keys = Object.keys(row.contrib).filter(k => row.contrib[k] > 0);
    if (!keys.length) return S.why.allround;
    keys.sort((a, b) => row.contrib[b] - row.contrib[a]);
    const top = keys[0];
    if (top === 'feel') {
      const z = zoneOf(fSt.firm);
      return z === 'firm' ? S.why.firm : z === 'plush' ? S.why.soft : S.why.balanced;
    }
    if (top === 'budget') return tpl(S.why.budget, { price: fmt(row.from) });
    if (top === 'cooling') return S.why.cooling;
    if (top === 'motion') return S.why.motion;
    if (top === 'back') return S.why.firm;
    if (top === 'kids') return S.why.kids;
    if (top === 'posSide') return S.why.posSide;
    if (top === 'posBack') return S.why.posBack;
    if (top === 'posStomach') return S.why.posStomach;
    return S.why.allround;
  }

  const ring = pct => `
    <span class="ring" role="img" aria-label="${tpl(S.match, { n: pct })}">
      <svg viewBox="0 0 36 36" aria-hidden="true">
        <circle class="ring-bg" cx="18" cy="18" r="15.915" fill="none" stroke-width="3.2"></circle>
        <circle class="ring-fg" cx="18" cy="18" r="15.915" fill="none" stroke-width="3.2"
          pathLength="100" stroke-dasharray="${pct} 100" stroke-linecap="round"></circle>
      </svg>
      <span class="ring-num">${pct}<i>%</i></span>
    </span>`;

  function fltRender() {
    const rows = MATTS.map(filterScore).sort((a, b) => (b.s - a.s) || (b.p.rating - a.p.rating));
    const ss = rows.map(r => r.s);
    const min = Math.min(...ss), max = Math.max(...ss);
    const pctOf = s => Math.round(48 + 50 * ((s - min) / ((max - min) || 1)));
    fltCount.textContent = tpl(S.fCount, { n: rows.length });
    fltResults.innerHTML = rows.map(r => {
      const p = r.p, pct = pctOf(r.s);
      const badge = p.sale ? `<span class="chip chip-sale">${t('saleBadge')}</span>`
        : p.badge ? `<span class="chip">${loc(p, 'badge')}</span>` : '';
      return `
      <article class="fcard">
        <div class="fcard-media">
          ${badge ? `<span class="fcard-badge">${badge}</span>` : ''}
          <img src="${p.img}" alt="${loc(p, 'name')} — ${loc(p, 'kind')}" loading="lazy">
        </div>
        <div class="fcard-body">
          <div class="fcard-head">
            <span class="fcard-titles">
              <span class="fcard-kind">${loc(p, 'kind')}</span>
              <span class="fcard-name">${loc(p, 'name')}</span>
            </span>
            ${ring(pct)}
          </div>
          ${BlueUI.feelMeter(p.feel)}
          <p class="fcard-why">${whyLine(r)}</p>
          <div class="fcard-foot">
            <span class="fcard-price"><span class="from">${t('from')}</span>${fmt(r.from)}</span>
            <span class="fcard-ctas">
              <a class="btn btn-sm" href="${url(p)}">${t('details')} <span class="arr">${t('arr')}</span></a>
              <button class="btn btn-ghost btn-sm" type="button" data-quick-add="${p.id}">${t('quickAdd')}</button>
            </span>
          </div>
        </div>
      </article>`;
    }).join('');
  }
  const fltRenderDebounced = () => { clearTimeout(fDebounce); fDebounce = setTimeout(fltRender, 110); };

  function syncFilterUI() {
    firmRange.value = fSt.firm;
    firmVal.textContent = S.fZone[zoneOf(fSt.firm)];
    budgetRange.value = fSt.budget;
    budgetVal.textContent = tpl(S.fBudgetUpTo, { x: fmt(fSt.budget) });
    document.querySelectorAll('#posChips .chip-btn').forEach(b =>
      b.setAttribute('aria-pressed', String(b.dataset.pos === fSt.pos)));
    document.querySelectorAll('#prioChips .chip-btn').forEach(b =>
      b.setAttribute('aria-pressed', String(fSt.prio.has(b.dataset.prio))));
  }

  function ensureFilters() {
    if (fltBuilt) return;
    fltBuilt = true;
    const posOrder = ['side', 'back', 'stomach', 'any'];
    const prioOrder = ['cooling', 'motion', 'back', 'kids'];
    document.getElementById('pane-filters').querySelector('.flt-mount').innerHTML = `
      <div class="flt-panel">
        <div class="flt-ctrl">
          <div class="flt-ctrl-top"><span class="flt-lab">${S.fFirm}</span><span class="flt-read" id="firmVal"></span></div>
          <input type="range" id="firmRange" min="1" max="10" step="1" value="${fSt.firm}" aria-label="${S.fFirm}">
          <div class="flt-scale"><span>${S.fZone.plush}</span><span>${S.fZone.balanced}</span><span>${S.fZone.firm}</span></div>
        </div>
        <div class="flt-ctrl">
          <div class="flt-ctrl-top"><span class="flt-lab">${S.fBudget}</span><span class="flt-read" id="budgetVal"></span></div>
          <input type="range" id="budgetRange" min="1500" max="5000" step="100" value="${fSt.budget}" aria-label="${S.fBudget}">
        </div>
        <div class="flt-ctrl">
          <span class="flt-lab">${S.fPos}</span>
          <div class="chip-row" id="posChips" role="group" aria-label="${S.fPos}">
            ${posOrder.map(v => `<button class="chip-btn" type="button" data-pos="${v}" aria-pressed="false">${S.fPosO[v]}</button>`).join('')}
          </div>
        </div>
        <div class="flt-ctrl">
          <span class="flt-lab">${S.fPrio}</span>
          <div class="chip-row" id="prioChips" role="group" aria-label="${S.fPrio}">
            ${prioOrder.map(v => `<button class="chip-btn" type="button" data-prio="${v}" aria-pressed="false">${S.fPrioO[v]}</button>`).join('')}
          </div>
        </div>
        <button class="flt-reset" type="button" id="fltReset">${S.fReset}</button>
      </div>
      <div class="flt-results-wrap">
        <p class="flt-count" id="fltCount" aria-live="polite"></p>
        <div class="flt-results" id="fltResults"></div>
      </div>`;

    firmRange = document.getElementById('firmRange');
    firmVal = document.getElementById('firmVal');
    budgetRange = document.getElementById('budgetRange');
    budgetVal = document.getElementById('budgetVal');
    fltResults = document.getElementById('fltResults');
    fltCount = document.getElementById('fltCount');

    firmRange.addEventListener('input', () => { fSt.firm = +firmRange.value; firmVal.textContent = S.fZone[zoneOf(fSt.firm)]; fltRenderDebounced(); });
    budgetRange.addEventListener('input', () => { fSt.budget = +budgetRange.value; budgetVal.textContent = tpl(S.fBudgetUpTo, { x: fmt(fSt.budget) }); fltRenderDebounced(); });
    document.getElementById('posChips').addEventListener('click', e => {
      const b = e.target.closest('[data-pos]'); if (!b) return;
      fSt.pos = b.dataset.pos; syncFilterUI(); fltRender();
    });
    document.getElementById('prioChips').addEventListener('click', e => {
      const b = e.target.closest('[data-prio]'); if (!b) return;
      const k = b.dataset.prio; fSt.prio.has(k) ? fSt.prio.delete(k) : fSt.prio.add(k);
      syncFilterUI(); fltRender();
    });
    document.getElementById('fltReset').addEventListener('click', () => {
      fSt.firm = 6; fSt.budget = 5000; fSt.pos = 'any'; fSt.prio.clear();
      syncFilterUI(); fltRender();
    });

    syncFilterUI();
    fltRender();
  }

  /* ============================================================
     MODE 3 — SWIPE / THIS-OR-THAT (#swipe)
     ============================================================ */
  const SWIPE = S.cards;
  /* how each choice steers the shared scoring engine */
  const SWIPE_APPLY = [
    { a: () => (sAns.feel = 'soft'), b: () => (sAns.feel = 'firm') },
    { a: () => (sAns.pos = 'side'), b: () => (sAns.pos = 'back') },
    { a: () => (sAns.heat = 'very'), b: () => (sAns.heat = 'rarely') },
    { a: () => (sAns.who = 'me'), b: () => (sAns.who = 'two') },
    { a: () => (sAns.budget = 'b1'), b: () => (sAns.budget = 'b3') },
    { a: () => (sBump = 'reef'), b: () => (sBump = 'cloud') },  /* signature priority */
  ];
  let sAns, sBump, sIdx = 0, swipeBuilt = false, swipeDeck, swipeProg, swipeResultEl, swipeSec, dragState = null;

  function swipeReset() {
    sAns = { who: 'me', pos: 'varies', feel: 'balanced', heat: 'sometimes', budget: 'all' };
    sBump = null; sIdx = 0;
    swipeResultEl.hidden = true; swipeResultEl.innerHTML = '';
    swipeDeck.hidden = false; swipeProg.hidden = false;
    renderDeck();
  }

  /* option A sits inline-start, B inline-end; arrows/drag follow the
     visual side (flipped under RTL) so both stay intuitive */
  const leftKey = AR ? 'b' : 'a';
  const rightKey = AR ? 'a' : 'b';

  function renderDeck() {
    const total = SWIPE.length;
    swipeProg.innerHTML = `
      <span class="swipe-count">${tpl(S.swipeOf, { a: sIdx + 1, b: total })}</span>
      <span class="swipe-dots" aria-hidden="true">${SWIPE.map((_, i) => `<i class="${i < sIdx ? 'done' : i === sIdx ? 'now' : ''}"></i>`).join('')}</span>
      <span class="swipe-hint">${S.swipeHint}</span>`;
    /* render current + up to two peeking cards behind it */
    const stack = [];
    for (let d = Math.min(2, total - 1 - sIdx); d >= 0; d--) {
      const i = sIdx + d;
      const c = SWIPE[i];
      stack.push(`
        <div class="swipe-card${d === 0 ? ' is-top' : ''}" data-depth="${d}" ${d === 0 ? 'tabindex="0" role="group" aria-roledescription="choice card"' : 'aria-hidden="true"'} aria-label="${c.q}">
          <span class="swipe-q">${c.q}</span>
          <div class="swipe-choices">
            <button class="swipe-choice" type="button" data-side="a" ${d === 0 ? '' : 'tabindex="-1"'}>
              <span class="swipe-ic">${I[c.a.ic]}</span><span class="swipe-clab">${c.a.label}</span>
            </button>
            <span class="swipe-or">${AR ? 'أم' : 'or'}</span>
            <button class="swipe-choice" type="button" data-side="b" ${d === 0 ? '' : 'tabindex="-1"'}>
              <span class="swipe-ic">${I[c.b.ic]}</span><span class="swipe-clab">${c.b.label}</span>
            </button>
          </div>
        </div>`);
    }
    swipeDeck.innerHTML = stack.join('');
    const top = swipeDeck.querySelector('.swipe-card.is-top');
    if (top && !REDUCED) { top.classList.remove('enter'); void top.offsetWidth; top.classList.add('enter'); }
    if (top) bindDrag(top);
  }

  function choose(side, dir) {
    const top = swipeDeck.querySelector('.swipe-card.is-top');
    SWIPE_APPLY[sIdx][side]();
    const advance = () => {
      sIdx++;
      if (sIdx >= SWIPE.length) swipeFinish();
      else renderDeck();
    };
    if (top && !REDUCED) {
      const sign = (dir || (side === leftKey ? -1 : 1)) > 0 ? 1 : -1;
      top.dataset.done = '1';
      top.classList.add('flying');
      top.style.transform = `translateX(${sign * 130}%) rotate(${sign * 12}deg)`;
      top.addEventListener('transitionend', advance, { once: true });
      setTimeout(advance, 480); /* safety if transitionend is missed */
    } else advance();
  }

  function swipeFinish() {
    const list = scored(sAns);
    if (sBump) list.forEach(x => { if (x.p.id === sBump) x.s += 3; });
    list.sort((x, y) => (y.s - x.s) || (y.p.rating - x.p.rating) || (y.p.reviews - x.p.reviews));
    const res = { top: list[0].p, runners: list.slice(1, 3), list };
    save(sAns, res.top.id);
    swipeDeck.hidden = true; swipeProg.hidden = true;
    renderResult(swipeResultEl, res, sAns, { restored: false, onStartOver: swipeReset });
  }

  /* pointer drag as an enhancement; buttons + arrows are the a11y path */
  function bindDrag(card) {
    if (REDUCED) return;
    let sx = 0, dx = 0, active = false;
    const onDown = e => {
      if (e.target.closest('.swipe-choice')) return; /* let buttons click */
      active = true; sx = e.clientX; dx = 0; card.setPointerCapture?.(e.pointerId);
      card.classList.add('dragging');
    };
    const onMove = e => {
      if (!active) return;
      dx = e.clientX - sx;
      card.style.transform = `translateX(${dx}px) rotate(${dx / 26}deg)`;
    };
    const onUp = () => {
      if (!active) return;
      active = false; card.classList.remove('dragging');
      const T = 90;
      if (Math.abs(dx) > T) {
        const visualDir = dx > 0 ? 1 : -1;      /* +1 = dragged right */
        const side = visualDir > 0 ? rightKey : leftKey;
        choose(side, visualDir);
      } else {
        card.style.transform = '';
      }
    };
    card.addEventListener('pointerdown', onDown);
    card.addEventListener('pointermove', onMove);
    card.addEventListener('pointerup', onUp);
    card.addEventListener('pointercancel', onUp);
  }

  function onSwipeKey(e) {
    if (mode !== 'swipe' || swipeDeck.hidden) return;
    if (e.key === 'ArrowLeft') { e.preventDefault(); choose(leftKey, -1); }
    else if (e.key === 'ArrowRight') { e.preventDefault(); choose(rightKey, 1); }
  }

  function ensureSwipe() {
    if (swipeBuilt) { if (swipeResultEl.hidden && swipeDeck.hidden) swipeReset(); return; }
    swipeBuilt = true;
    swipeSec = document.getElementById('pane-swipe');
    swipeDeck = document.getElementById('swipeDeck');
    swipeProg = document.getElementById('swipeProg');
    swipeResultEl = document.getElementById('swipeResult');
    swipeDeck.addEventListener('click', e => {
      const b = e.target.closest('[data-side]');
      const card = e.target.closest('.swipe-card.is-top');
      if (b && card && !card.dataset.done) choose(b.dataset.side, b.dataset.side === leftKey ? -1 : 1);
    });
    document.addEventListener('keydown', onSwipeKey);
    swipeReset();
  }

  /* ============================================================
     MODE 4 — COMPARE GRID (#compare)
     ============================================================ */
  const dots = n => `<span class="dots" role="img" aria-label="${tpl(S.outOf5, { n })}">${
    [1, 2, 3, 4, 5].map(i => `<i${i <= n ? ' class="on"' : ''}></i>`).join('')}</span>`;

  const ROWS = [
    { key: 'feel', k: S.rowFeel, v: p => BlueUI.feelMeter(p.feel) + `<span class="cmp-feellab">${loc(p, 'feelLabel')}</span>` },
    { key: 'height', k: S.rowHeight, v: p => tpl(S.cm, { n: p.height }) },
    { key: 'best', k: S.rowBest, v: p => S.best[p.id] },
    { key: 'cool', k: S.rowCool, v: p => dots(COOL[p.id]) },
    { key: 'motion', k: S.rowMotion, v: p => dots(MOTION[p.id]) },
    { key: 'trial', k: S.rowTrial, v: () => S.trialVal },
    { key: 'price', k: S.rowPrice, v: p => `<span class="cmp-price">${fmt(priceFrom(p))}</span>` },
  ];

  /* highlight chips → which row(s) they light + a per-bed dimension score */
  const HL = [
    { key: 'cooling', row: 'cool', dim: p => COOL[p.id] },
    { key: 'motion', row: 'motion', dim: p => MOTION[p.id] },
    { key: 'firm', row: 'feel', dim: p => p.feel / 2 },
    { key: 'value', row: 'price', dim: p => (MAX_FROM - priceFrom(p)) / ((MAX_FROM - MIN_FROM) || 1) * 5 },
    { key: 'soft', row: 'feel', dim: p => (10 - p.feel) / 2 },
  ];
  const hlSel = new Set();
  let cmpBuilt = false, cmpGrid, cmpCards, hlChipsEl, hlCallout;

  function buildCompare() {
    const saved = readSaved();
    const matchId = saved ? (saved.topId || guidedResult(saved.a).top.id) : null;

    /* desktop grid */
    let cells = `<div class="cmp-cell cmp-head" data-col="0" aria-hidden="true"></div>`;
    cells += MATTS.map((p, i) => `
      <div class="cmp-cell cmp-head${p.id === matchId ? ' is-match' : ''}" data-col="${i + 1}">
        ${p.id === matchId ? `<span class="cmp-chip">${S.yourMatch}</span>` : ''}
        <img src="${p.img}" alt="">
        <span class="cmp-name"><a href="${url(p)}">${loc(p, 'name')}</a></span>
        <span class="cmp-from">${t('from')} ${fmt(priceFrom(p))}</span>
        <a class="cmp-cta" href="${url(p)}">${t('details')} <span class="arr">${t('arr')}</span></a>
      </div>`).join('');
    ROWS.forEach(row => {
      cells += `<div class="cmp-cell cmp-lab" data-col="0" data-row="${row.key}">${row.k}</div>`;
      cells += MATTS.map((p, i) =>
        `<div class="cmp-cell${p.id === matchId ? ' is-match' : ''}" data-col="${i + 1}" data-row="${row.key}">${row.v(p)}</div>`
      ).join('');
    });
    cmpGrid.innerHTML = cells;

    /* mobile scroll-snap cards */
    cmpCards.innerHTML = MATTS.map(p => `
      <article class="cmp-card${p.id === matchId ? ' is-match' : ''}" data-bed="${p.id}">
        <img src="${p.img}" alt="">
        <div class="cmp-card-head">
          ${p.id === matchId ? `<span class="cmp-chip">${S.yourMatch}</span>` : ''}
          <span class="cmp-name">${loc(p, 'name')}</span>
          <span class="cmp-from">${t('from')} ${fmt(priceFrom(p))}</span>
        </div>
        ${ROWS.map(row => `<div class="cmp-row" data-row="${row.key}"><span class="k">${row.k}</span><span class="v">${row.v(p)}</span></div>`).join('')}
        <div class="cmp-card-cta"><a class="btn btn-sm" href="${url(p)}">${t('details')} <span class="arr">${t('arr')}</span></a></div>
      </article>`).join('');

    applyHighlight();
  }

  function applyHighlight() {
    const rowsOn = new Set(), dims = [];
    hlSel.forEach(k => { const h = HL.find(x => x.key === k); if (h) { rowsOn.add(h.row); dims.push(h.dim); } });
    /* light the relevant rows (grid + cards) */
    document.querySelectorAll('#cmpGrid [data-row], #cmpCards [data-row]').forEach(n =>
      n.classList.toggle('row-hl', rowsOn.has(n.dataset.row)));
    /* compute the best bed for the chosen priorities */
    let bestId = null;
    if (dims.length) {
      let best = -Infinity;
      MATTS.forEach(p => { const v = dims.reduce((a, d) => a + d(p), 0); if (v > best) { best = v; bestId = p.id; } });
    }
    /* column highlight (desktop) */
    const bestCol = bestId ? MATTS.findIndex(p => p.id === bestId) + 1 : null;
    cmpGrid.querySelectorAll('[data-col]').forEach(n =>
      n.classList.toggle('col-best', bestCol != null && n.dataset.col === String(bestCol)));
    cmpCards.querySelectorAll('.cmp-card').forEach(n =>
      n.classList.toggle('col-best', bestId != null && n.dataset.bed === bestId));
    /* callout */
    if (bestId) {
      hlCallout.hidden = false;
      hlCallout.innerHTML = `${CHECK}<span>${tpl(S.hlCallout, { bed: loc(byId(bestId), 'name') })}</span>`;
    } else hlCallout.hidden = true;
  }

  function bindCompareOnce() {
    /* soft column hover (desktop) */
    const hl = c => cmpGrid.querySelectorAll('[data-col]').forEach(n =>
      n.classList.toggle('hl', c != null && c !== '0' && n.dataset.col === c));
    cmpGrid.addEventListener('mouseover', e => { const cell = e.target.closest('[data-col]'); if (cell) hl(cell.dataset.col); });
    cmpGrid.addEventListener('mouseleave', () => hl(null));
    /* highlight chips */
    hlChipsEl.addEventListener('click', e => {
      const b = e.target.closest('[data-hl]'); if (!b) return;
      const k = b.dataset.hl;
      hlSel.has(k) ? hlSel.delete(k) : hlSel.add(k);
      b.setAttribute('aria-pressed', String(hlSel.has(k)));
      applyHighlight();
    });
  }

  function initCompare() {
    cmpGrid = document.getElementById('cmpGrid');
    cmpCards = document.getElementById('cmpCards');
    hlChipsEl = document.getElementById('hlChips');
    hlCallout = document.getElementById('hlCallout');
    hlChipsEl.innerHTML = HL.map(h =>
      `<button class="chip-btn" type="button" data-hl="${h.key}" aria-pressed="false">${S.hl[h.key]}</button>`).join('');
    bindCompareOnce();
  }

  /* ============================================================
     MODE SWITCH + DEEP LINK
     ============================================================ */
  const MODES = ['guided', 'compare'];
  let mode = null, noteEl;

  function setMode(m, push = true) {
    if (!MODES.includes(m)) m = 'guided';
    mode = m;
    MODES.forEach(x => {
      const pane = document.getElementById('pane-' + x);
      const seg = document.getElementById('seg-' + x);
      const on = x === m;
      if (pane) pane.hidden = !on;
      if (seg) { seg.classList.toggle('is-on', on); seg.setAttribute('aria-pressed', String(on)); }
    });
    if (noteEl) noteEl.textContent = S.modeNote[m];
    if (m === 'filters') ensureFilters();
    else if (m === 'swipe') ensureSwipe();
    else if (m === 'compare') buildCompare();
    if (push) { try { history.replaceState(null, '', location.pathname + location.search + '#' + m); } catch { /* file:// */ } }
  }

  function scrollToSwitch() {
    const sw = document.getElementById('selSwitch');
    if (!sw) return;
    const y = Math.max(0, sw.getBoundingClientRect().top + window.scrollY - 90);
    if (window.lenis?.scrollTo) window.lenis.scrollTo(y);
    else window.scrollTo({ top: y, behavior: REDUCED ? 'auto' : 'smooth' });
  }

  /* ============================================================
     BOOT
     ============================================================ */
  function init() {
    noteEl = document.getElementById('selNote');
    initGuided();
    initCompare();

    document.getElementById('selSwitch').addEventListener('click', e => {
      const b = e.target.closest('[data-mode]');
      if (b) setMode(b.dataset.mode);
    });

    const fromHash = (location.hash || '').replace('#', '');
    setMode(MODES.includes(fromHash) ? fromHash : 'guided', false);
    /* normalise the URL so a bare / or #x always carries the active mode */
    try { history.replaceState(null, '', location.pathname + location.search + '#' + mode); } catch { /* noop */ }

    window.addEventListener('hashchange', () => {
      const want = (location.hash || '').replace('#', '');
      if (MODES.includes(want) && want !== mode) setMode(want, false);
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
