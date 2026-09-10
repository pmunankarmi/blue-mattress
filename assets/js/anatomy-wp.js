(() => {
  'use strict';

  const roleOf = (title) => {
    const text = String(title || '');
    if (/spring|pocket|bonnel|coil|نوابض|أساس/i.test(text)) return 'spring';
    if (/pillow ?top|euro-?top|وثيرة|علوية/i.test(text)) return 'quilt';
    if (/foam|tpe|latex|إسفنج|رغوي|لاتكس|ميموري/i.test(text)) return 'foam';
    return 'fabric';
  };
  const width = 240;
  const rhombusHeight = 120;
  const thickness = { fabric: 8, quilt: 15, foam: 19, spring: 32 };
  const gap = 26;
  const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[character]);

  const slab = (layer, y, index) => {
    const depth = thickness[layer.role];
    const top = `polygon(${width / 2}px 0,${width}px ${rhombusHeight / 2}px,${width / 2}px ${rhombusHeight}px,0 ${rhombusHeight / 2}px)`;
    const left = `polygon(0 ${rhombusHeight / 2}px,${width / 2}px ${rhombusHeight}px,${width / 2}px ${rhombusHeight + depth}px,0 ${rhombusHeight / 2 + depth}px)`;
    const right = `polygon(${width / 2}px ${rhombusHeight}px,${width}px ${rhombusHeight / 2}px,${width}px ${rhombusHeight / 2 + depth}px,${width / 2}px ${rhombusHeight + depth}px)`;
    return `<div class="slab s-${layer.role}" style="--y:${y}px;--fly:${index * gap}px;--d:${index * 60}ms;height:${rhombusHeight + depth}px"><span class="f-top" style="clip-path:${top}"></span><span class="f-left" style="clip-path:${left}"></span><span class="f-right" style="clip-path:${right}"></span><span class="slab-lbl">${escapeHtml(layer.title)}</span></div>`;
  };

  const buildStack = (titles) => {
    if (titles.length < 2) return null;
    const ranks = { fabric: 0, quilt: 1, foam: 2, spring: 3 };
    const layers = titles.map((title, index) => ({ title, index, role: roleOf(title) }))
      .sort((a, b) => ranks[a.role] - ranks[b.role] || a.index - b.index);
    let y = 0;
    const markup = layers.map((layer, index) => {
      const html = slab(layer, y, index);
      y += thickness[layer.role];
      return html;
    });
    const wrap = document.createElement('div');
    wrap.className = 'anat';
    wrap.setAttribute('aria-hidden', 'true');
    wrap.innerHTML = `<div class="iso" style="--w:${width}px;--sh:${rhombusHeight + y + (layers.length - 1) * gap}px">${markup.join('')}</div><span class="anat-cap">${document.documentElement.lang.startsWith('ar') ? 'التركيب الداخلي' : 'Inside this mattress'}</span>`;
    return wrap;
  };

  const fit = (media) => {
    const iso = media.querySelector('.iso');
    if (!iso) return;
    const rect = media.getBoundingClientRect();
    if (!rect.width) return;
    const stackHeight = Number.parseFloat(iso.style.getPropertyValue('--sh')) || 300;
    const roomy = rect.width >= 330;
    media.classList.toggle('anat-labels', roomy);
    const budget = roomy ? rect.width - 176 : rect.width * .82;
    iso.style.setProperty('--s', Math.min(1.3, budget / width, (rect.height * .72) / stackHeight).toFixed(3));
  };

  const enhance = (root = document) => {
    root.querySelectorAll('[data-anat]:not([data-anat-ready])').forEach((media) => {
      let reveal = null;
      const videoUrl = media.dataset.anatVideo;
      if (videoUrl) {
        reveal = document.createElement('div');
        reveal.className = 'anat anat-vid';
        reveal.setAttribute('aria-hidden', 'true');
        const video = document.createElement('video');
        video.className = 'anat-video';
        video.src = videoUrl;
        if (media.dataset.anatPoster) video.poster = media.dataset.anatPoster;
        video.muted = true;
        video.loop = true;
        video.playsInline = true;
        video.preload = 'none';
        reveal.append(video);
      } else {
        try { reveal = buildStack(JSON.parse(media.dataset.anatLayers || '[]')); } catch (_) { reveal = null; }
      }
      media.dataset.anatReady = 'true';
      if (!reveal) return;
      media.append(reveal);
      const hotspot = media.closest('.pcard') || media;
      const video = reveal.querySelector('video');
      const setHot = (active) => {
        if (active === media.classList.contains('anat-hot')) return;
        media.classList.toggle('anat-hot', active);
        if (!video) return;
        if (active) {
          video.preload = 'auto';
          try { video.currentTime = 0; } catch (_) {}
          video.play().catch(() => {});
        } else video.pause();
      };
      hotspot.addEventListener('mousemove', (event) => {
        const rect = media.getBoundingClientRect();
        setHot(event.clientX >= rect.left && event.clientX <= rect.right && event.clientY >= rect.top && event.clientY <= rect.bottom);
      });
      hotspot.addEventListener('mouseleave', () => setHot(false));
      media._blueAnatomyOpen = () => {
        media.classList.add('anat-on');
        if (video) { video.preload = 'auto'; video.play().catch(() => {}); }
      };
      fit(media);
    });
  };

  const watchTouch = () => {
    if (!window.matchMedia('(hover: none)').matches || !('IntersectionObserver' in window)) return;
    const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      entry.target._blueAnatomyOpen?.();
      observer.unobserve(entry.target);
    }), { threshold: .55 });
    document.querySelectorAll('[data-anat-ready]:not([data-anat-seen])').forEach((media) => {
      media.dataset.anatSeen = 'true';
      observer.observe(media);
    });
  };

  const boot = () => { enhance(); watchTouch(); };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
  let timer = null;
  new MutationObserver(() => {
    window.clearTimeout(timer);
    timer = window.setTimeout(boot, 120);
  }).observe(document.documentElement, { childList: true, subtree: true });
  window.addEventListener('resize', () => document.querySelectorAll('[data-anat-ready]').forEach(fit));
})();
