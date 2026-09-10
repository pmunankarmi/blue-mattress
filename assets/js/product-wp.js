(() => {
  'use strict';
	const strings = window.BlueTheme?.strings || {};

  const syncVariationCards = () => {
    const touchedForms = new Set();
    document.querySelectorAll('.blue-variation-cards').forEach((group) => {
      const selectName = group.dataset.attributeName;
      const form = group.closest('.variations_form');
      const select = form && form.querySelector(`select[name="${CSS.escape(selectName)}"]`);
      const radios = Array.from(group.querySelectorAll('input[type="radio"]'));
      if (!select || !radios.length) return;
      touchedForms.add(form);

      const notifyWooCommerce = () => {
        if (window.jQuery) window.jQuery(select).trigger('change');
        else select.dispatchEvent(new Event('change', { bubbles: true }));
      };

      const setActive = (value) => {
        radios.forEach((radio) => {
          radio.checked = radio.value === value;
          radio.closest('.pd-size')?.classList.toggle('active', radio.checked);
        });
      };

      radios.forEach((radio) => radio.addEventListener('change', () => {
        if (!radio.checked) return;
        select.value = radio.value;
        notifyWooCommerce();
        setActive(radio.value);
      }));
      select.addEventListener('change', () => setActive(select.value));

      const initial = select.value || radios.find((radio) => radio.checked)?.value || radios[0].value;
      if (!select.value) {
        select.value = initial;
        notifyWooCommerce();
      }
      setActive(initial);
    });

    touchedForms.forEach((form) => {
      const synchronize = () => {
        form.querySelectorAll('.blue-variation-cards').forEach((group) => {
          const radio = group.querySelector('input[type="radio"]:checked');
          const select = form.querySelector(`select[name="${CSS.escape(group.dataset.attributeName)}"]`);
          if (radio && select && select.value !== radio.value) {
            select.value = radio.value;
            if (window.jQuery) window.jQuery(select).trigger('change');
            else select.dispatchEvent(new Event('change', { bubbles: true }));
          }
        });
        if (window.jQuery) window.jQuery(form).trigger('check_variations');
      };
      form.querySelector('.single_add_to_cart_button')?.addEventListener('pointerdown', synchronize);
      form.querySelector('.single_add_to_cart_button')?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') synchronize();
      });
      window.setTimeout(synchronize, 50);
    });
  };

  const enhanceQuantity = () => {
    document.querySelectorAll('.blue-product-detail form.cart .quantity').forEach((quantity) => {
      const input = quantity.querySelector('input.qty');
      if (!input || quantity.querySelector('.blue-qty-minus')) return;
      const minus = document.createElement('button');
      const plus = document.createElement('button');
      minus.type = plus.type = 'button';
      minus.className = 'blue-qty-minus';
      plus.className = 'blue-qty-plus';
		minus.setAttribute('aria-label', strings.decreaseQty || 'Decrease quantity');
		plus.setAttribute('aria-label', strings.increaseQty || 'Increase quantity');
      minus.textContent = '−';
      plus.textContent = '+';
      quantity.prepend(minus);
      quantity.append(plus);
      const step = Number(input.step) || 1;
      minus.addEventListener('click', () => {
        input.value = Math.max(Number(input.min) || 1, Number(input.value || 1) - step);
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });
      plus.addEventListener('click', () => {
        const next = Number(input.value || 1) + step;
        input.value = input.max ? Math.min(Number(input.max), next) : next;
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });
    });
  };

  const placeTechnologyTile = () => {
    const tile = document.querySelector('.pd-tech-tile');
    const thumbnails = document.querySelector('.blue-product-detail .flex-control-thumbs');
    if (!tile || !thumbnails || tile.closest('.pd-tech-slot')) return;
    [...thumbnails.children].slice(3).forEach((thumbnail) => {
      thumbnail.hidden = true;
    });
    const slot = document.createElement('li');
    slot.className = 'pd-tech-slot';
    slot.append(tile);
    thumbnails.append(slot);
  };

	const enableSingleOpenAccordion = () => {
		document.querySelectorAll('.blue-product-detail .pd-acc').forEach((accordion) => {
			const items = Array.from(accordion.querySelectorAll(':scope > details.pd-acc-item'));
			items.forEach((item) => item.addEventListener('toggle', () => {
				if (!item.open) return;
				items.forEach((other) => {
					if (other !== item) other.open = false;
				});
			}));
		});
	};

	const enableCutawayAnimation = () => {
		const gallery = document.querySelector('.blue-product-detail .pd-gallery[data-product-cut-video]');
		const hero = gallery?.querySelector('.woocommerce-product-gallery__image:first-child');
		if (gallery && hero && !hero.querySelector('.pd-hero-anim')) {
			const video = document.createElement('video');
			video.className = 'pd-hero-anim';
			video.src = gallery.dataset.productCutVideo;
			if (gallery.dataset.productCutPoster) video.poster = gallery.dataset.productCutPoster;
			video.muted = true;
			video.loop = true;
			video.playsInline = true;
			video.preload = 'none';
			hero.append(video);
			const open = () => {
				hero.classList.add('is-opening');
				video.preload = 'auto';
				try { video.currentTime = 0; } catch (_) {}
				video.play().catch(() => {});
			};
			const close = () => { hero.classList.remove('is-opening'); video.pause(); };
			hero.addEventListener('mouseenter', open);
			hero.addEventListener('mouseleave', close);
			hero.addEventListener('focusin', open);
			hero.addEventListener('focusout', close);
		}

		document.querySelectorAll('.blue-product-detail .pd-cut-vid').forEach((video) => {
			if (video.dataset.cutawayReady) return;
			video.dataset.cutawayReady = 'true';
			const play = () => video.play().catch(() => {});
			video.closest('.pd-cut')?.addEventListener('mouseenter', play);
			video.closest('.pd-cut')?.addEventListener('mouseleave', () => video.pause());
		});
	};

  const syncVariationPrice = () => {
    if (!window.jQuery) return;
    window.jQuery('.blue-product-detail .variations_form').each(function () {
      const form = window.jQuery(this);
      const summary = this.closest('.summary');
      const price = summary && summary.querySelector(':scope > p.price');
      const button = this.querySelector('.single_add_to_cart_button');
      if (price && !price.dataset.originalPrice) price.dataset.originalPrice = price.innerHTML;
      if (button && !button.dataset.baseLabel) button.dataset.baseLabel = button.textContent.trim();

      form.on('found_variation', (_event, variation) => {
        if (price && variation.price_html) price.innerHTML = variation.price_html;
        if (button && variation.price_html) button.innerHTML = `${button.dataset.baseLabel} — ${variation.price_html}`;
      });
      form.on('reset_data hide_variation', () => {
        if (price && price.dataset.originalPrice) price.innerHTML = price.dataset.originalPrice;
        if (button && button.dataset.baseLabel) button.textContent = button.dataset.baseLabel;
      });
    });

    document.querySelectorAll('.blue-product-detail form.cart:not(.variations_form)').forEach((form) => {
      const button = form.querySelector('.single_add_to_cart_button');
      const price = form.closest('.summary')?.querySelector(':scope > p.price');
      if (button && price && !button.dataset.priceAdded) {
        button.textContent = `${button.textContent.trim()} — ${price.textContent.trim()}`;
        button.dataset.priceAdded = 'true';
      }
    });
  };

  const boot = () => {
    syncVariationPrice();
    syncVariationCards();
    enhanceQuantity();
    placeTechnologyTile();
		enableSingleOpenAccordion();
		enableCutawayAnimation();
    window.setTimeout(placeTechnologyTile, 350);
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
