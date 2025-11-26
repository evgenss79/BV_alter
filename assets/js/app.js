(function(){
  document.querySelectorAll('[data-variant-select]').forEach(function(select){
    select.addEventListener('change', function(){
      var price = this.options[this.selectedIndex].dataset.price;
      var target = this.dataset.target;
      if(target){
        var el = document.getElementById(target);
        if(el){ el.textContent = price + ' CHF'; }
      }
    });
  });

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
        productSelect.innerHTML = '<option value="">Select</option>';
        variantSelect.innerHTML = '<option value="">Select</option>';
        products.filter(p => p.category === cat).forEach(p => {
          const opt = document.createElement('option');
          opt.value = p.id;
          opt.textContent = p.name;
          productSelect.appendChild(opt);
        });
      });

      productSelect.addEventListener('change', function(){
        const prod = products.find(p => p.id === this.value);
        variantSelect.innerHTML = '<option value="">Select</option>';
        if (prod) {
          prod.variants.forEach(v => {
            const opt = document.createElement('option');
            opt.value = v.sku;
            opt.textContent = (v.volume ? v.volume + ' / ' : '') + (v.fragrance || '');
            opt.dataset.price = v.priceCHF;
            variantSelect.appendChild(opt);
          });
        }
      });
    });
  }
})();
