(function(){
  const selectLabel = (document.body && document.body.dataset.selectLabel) || 'Select';

  const initVariantContext = (root, data) => {
    const volumeSelect = root.querySelector('[data-volume-select]');
    const fragranceSelect = root.querySelector('[data-fragrance-select]');
    const singleFragranceSelect = root.querySelector('[data-single-fragrance]');
    const priceEl = root.querySelector('[data-price]');
    const skuInput = root.querySelector('[data-sku-input]');
    const fragranceNameEl = root.querySelector('[data-fragrance-name]');
    const fragranceShortEl = root.querySelector('[data-fragrance-short]');
    const fragranceFullEl = root.querySelector('[data-fragrance-full]');
    const fragranceRecommendedEl = root.querySelector('[data-fragrance-recommended]');
    const fragranceImageEl = root.querySelector('[data-fragrance-image]');
    const pyramidTop = root.querySelector('[data-pyramid-top]');
    const pyramidHeart = root.querySelector('[data-pyramid-heart]');
    const pyramidBase = root.querySelector('[data-pyramid-base]');
    const addBtn = root.querySelector('[data-add-to-cart]');
    const stockLabel = root.querySelector('[data-stock-label]');
    const fallback = data.fallback || {};

    const setImage = (el, src, altText) => {
      if (!el) return;
      if (el.tagName && el.tagName.toLowerCase() === 'img') {
        el.src = src;
        el.alt = altText || '';
      } else {
        el.style.backgroundImage = src ? `url(${src})` : '';
      }
    };

    const renderFallback = () => {
      fragranceNameEl && (fragranceNameEl.textContent = fallback.title || '');
      fragranceShortEl && (fragranceShortEl.textContent = fallback.description || '');
      if (fragranceFullEl) fragranceFullEl.textContent = '';
      if (fragranceRecommendedEl) fragranceRecommendedEl.textContent = '';
      setImage(fragranceImageEl, fallback.image, fallback.title || '');
      [pyramidTop, pyramidHeart, pyramidBase].forEach((target) => { if (target) target.innerHTML = ''; });
      if (priceEl) {
        const vol = volumeSelect ? volumeSelect.value : null;
        if (vol && data.priceByVolume && data.priceByVolume[vol]) {
          priceEl.textContent = `${data.priceByVolume[vol]} ${data.currency || ''}`.trim();
        } else if (data.initialPrice) {
          priceEl.textContent = data.initialPrice;
        }
      }
      if (skuInput) skuInput.value = '';
      if (addBtn) {
        addBtn.disabled = true;
        addBtn.textContent = data.labels ? data.labels.addToCart : '';
      }
      if (stockLabel) stockLabel.textContent = '';
    };

    const renderFragrance = (code) => {
      const frag = data.fragrances && data.fragrances[code];
      if (!frag) return;
      fragranceNameEl && (fragranceNameEl.textContent = frag.name);
      fragranceShortEl && (fragranceShortEl.textContent = frag.shortDescription);
      if (fragranceFullEl) fragranceFullEl.textContent = frag.fullDescription || '';
      if (fragranceRecommendedEl) fragranceRecommendedEl.textContent = frag.recommendedSpaces || '';
      if (frag.image) setImage(fragranceImageEl, frag.image, frag.name);
      const renderList = (target, items) => {
        if (!target || !items) return;
        target.innerHTML = '';
        items.forEach((item) => {
          const li = document.createElement('li');
          li.textContent = item;
          target.appendChild(li);
        });
      };
      renderList(pyramidTop, frag.olfactoryPyramid ? frag.olfactoryPyramid.top : []);
      renderList(pyramidHeart, frag.olfactoryPyramid ? frag.olfactoryPyramid.heart : []);
      renderList(pyramidBase, frag.olfactoryPyramid ? frag.olfactoryPyramid.base : []);
    };

    const updateVariant = () => {
      let selectedVariant = null;
      if (volumeSelect && fragranceSelect) {
        const vol = volumeSelect.value;
        const frag = fragranceSelect.value;
        if (!frag) {
          if (priceEl && data.priceByVolume && data.priceByVolume[vol]) {
            priceEl.textContent = `${data.priceByVolume[vol]} ${data.currency || ''}`.trim();
          }
          if (addBtn) {
            addBtn.disabled = true;
            if (data.labels) addBtn.textContent = data.labels.addToCart;
          }
          if (stockLabel) stockLabel.textContent = '';
          renderFallback();
          return;
        }
        selectedVariant = data.variants.find(v => v.volume === vol && v.fragranceCode === frag);
        if (!selectedVariant && frag) {
          const variantWithFragrance = data.variants.find(v => v.fragranceCode === frag);
          if (variantWithFragrance && volumeSelect.value !== variantWithFragrance.volume) {
            volumeSelect.value = variantWithFragrance.volume;
            selectedVariant = variantWithFragrance;
          }
        }
      } else if (singleFragranceSelect) {
        const frag = singleFragranceSelect.value;
        if (!frag) {
          renderFallback();
          return;
        }
        selectedVariant = data.variants.find(v => v.fragranceCode === frag);
      } else {
        selectedVariant = data.variants[0] || null;
      }
      if (!selectedVariant) {
        renderFallback();
        return;
      }
      if (priceEl) priceEl.textContent = selectedVariant.priceLabel;
      if (skuInput) skuInput.value = selectedVariant.sku;
      renderFragrance(selectedVariant.fragranceCode);
      if (addBtn) {
        const inStock = (selectedVariant.stock || 0) > 0;
        addBtn.disabled = !inStock;
        if (stockLabel && data.labels) {
          stockLabel.textContent = inStock ? data.labels.inStock : data.labels.outOfStock;
        }
        if (data.labels) {
          addBtn.textContent = inStock ? data.labels.addToCart : data.labels.notify;
        }
      }
    };

    [volumeSelect, fragranceSelect, singleFragranceSelect].forEach((el) => {
      if (el) el.addEventListener('change', updateVariant);
    });

    updateVariant();
  };

  const productDataEl = document.getElementById('product-data');
  if (productDataEl) {
    const data = JSON.parse(productDataEl.textContent);
    initVariantContext(document, data);
  }

  document.querySelectorAll('.product-card').forEach((card) => {
    const dataEl = card.querySelector('script.product-data');
    if (!dataEl) return;
    const data = JSON.parse(dataEl.textContent);
    initVariantContext(card, data);
  });

  // Gift set configurator
  const giftRows = document.querySelectorAll('.gift-row');
  const productsDataEl = document.getElementById('products-data');
  if (giftRows.length && productsDataEl) {
    const products = JSON.parse(productsDataEl.textContent);
    giftRows.forEach(row => {
      const categorySelect = row.querySelector('.gift-category');
      const productSelect = row.querySelector('.gift-product');
      const variantSelect = row.querySelector('.gift-variant');

      categorySelect.addEventListener('change', function(){
        const cat = this.value;
        productSelect.innerHTML = `<option value="">${selectLabel}</option>`;
        variantSelect.innerHTML = `<option value="">${selectLabel}</option>`;
        products.filter(p => p.category === cat).forEach(p => {
          const opt = document.createElement('option');
          opt.value = p.id;
          opt.textContent = p.name;
          productSelect.appendChild(opt);
        });
      });

      productSelect.addEventListener('change', function(){
        const prod = products.find(p => p.id === this.value);
        variantSelect.innerHTML = `<option value="">${selectLabel}</option>`;
        if (prod) {
          prod.variants.forEach(v => {
            const opt = document.createElement('option');
            const labelParts = [];
            if (v.volume) labelParts.push(v.volume);
            if (v.fragrance) labelParts.push(v.fragrance);
            opt.value = v.sku;
            opt.textContent = labelParts.join(' / ') || v.sku;
            opt.dataset.price = v.priceCHF;
            variantSelect.appendChild(opt);
          });
        }
      });
    });
  }
})();
