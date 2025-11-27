(function(){
  const selectLabel = (document.body && document.body.dataset.selectLabel) || 'Select';
  const productDataEl = document.getElementById('product-data');
  if (productDataEl) {
    const data = JSON.parse(productDataEl.textContent);
    const volumeSelect = document.querySelector('[data-volume-select]');
    const fragranceSelect = document.querySelector('[data-fragrance-select]');
    const singleFragranceSelect = document.querySelector('[data-single-fragrance]');
    const priceEl = document.querySelector('[data-price]');
    const skuInput = document.querySelector('[data-sku-input]');
    const fragranceNameEl = document.querySelector('[data-fragrance-name]');
    const fragranceShortEl = document.querySelector('[data-fragrance-short]');
    const fragranceRecommendedEl = document.querySelector('[data-fragrance-recommended]');
    const pyramidTop = document.querySelector('[data-pyramid-top]');
    const pyramidHeart = document.querySelector('[data-pyramid-heart]');
    const pyramidBase = document.querySelector('[data-pyramid-base]');
    const addBtn = document.querySelector('[data-add-to-cart]');
    const stockLabel = document.querySelector('[data-stock-label]');

    const renderFragrance = (code) => {
      const frag = data.fragrances[code];
      if (!frag) return;
      fragranceNameEl && (fragranceNameEl.textContent = frag.name);
      fragranceShortEl && (fragranceShortEl.textContent = frag.shortDescription);
      fragranceRecommendedEl && (fragranceRecommendedEl.textContent = frag.recommendedSpaces);
      const renderList = (target, items) => {
        if (!target) return;
        target.innerHTML = '';
        items.forEach((item) => {
          const li = document.createElement('li');
          li.textContent = item;
          target.appendChild(li);
        });
      };
      renderList(pyramidTop, frag.olfactoryPyramid.top || []);
      renderList(pyramidHeart, frag.olfactoryPyramid.heart || []);
      renderList(pyramidBase, frag.olfactoryPyramid.base || []);
    };

    const updateVariant = () => {
      let selectedVariant = null;
      if (volumeSelect && fragranceSelect) {
        const vol = volumeSelect.value;
        const frag = fragranceSelect.value;
        selectedVariant = data.variants.find(v => v.volume === vol && v.fragranceCode === frag);
      } else if (singleFragranceSelect) {
        const frag = singleFragranceSelect.value;
        selectedVariant = data.variants.find(v => v.fragranceCode === frag);
      }
      if (!selectedVariant) { return; }
      if (priceEl) { priceEl.textContent = selectedVariant.priceLabel; }
      if (skuInput) { skuInput.value = selectedVariant.sku; }
      renderFragrance(selectedVariant.fragranceCode);
      if (addBtn) {
        const inStock = selectedVariant.stock > 0;
        addBtn.disabled = !inStock;
        if (stockLabel) {
          stockLabel.textContent = inStock ? data.labels.inStock : data.labels.outOfStock;
        }
        addBtn.textContent = inStock ? data.labels.addToCart : data.labels.notify;
      }
    };

    [volumeSelect, fragranceSelect, singleFragranceSelect].forEach((el) => {
      if (el) el.addEventListener('change', updateVariant);
    });

    updateVariant();
  }

  document.querySelectorAll('select[data-variant-select]').forEach((select) => {
    const priceTarget = document.getElementById(select.dataset.target);
    const updatePrice = () => {
      const option = select.selectedOptions[0];
      if (priceTarget && option && option.dataset.price) {
        priceTarget.textContent = option.dataset.price;
      }
    };
    select.addEventListener('change', updatePrice);
    updatePrice();
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
