/**
 * NICHEHOME.CH - Main JavaScript
 * Handles: Navigation, Cart, Selectors, Price Updates
 */

(function() {
    'use strict';

    // ========================================
    // NAVIGATION
    // ========================================
    
    const burger = document.querySelector('.site-header__burger');
    const header = document.querySelector('.site-header');
    const nav = document.querySelector('.primary-nav');
    
    if (burger) {
        burger.addEventListener('click', () => {
            header.classList.toggle('nav-open');
        });
    }

    // Close nav when clicking outside
    document.addEventListener('click', (e) => {
        if (header && !header.contains(e.target)) {
            header.classList.remove('nav-open');
        }
    });

    // Mega menu handling
    const megaItems = document.querySelectorAll('.primary-nav__item--mega');
    megaItems.forEach(item => {
        const toggle = item.querySelector('[data-mega-toggle]');
        if (toggle) {
            toggle.addEventListener('click', (e) => {
                if (window.innerWidth <= 1100) {
                    e.preventDefault();
                    item.classList.toggle('is-open');
                }
            });
        }
    });

    // ========================================
    // LANGUAGE SWITCHER
    // ========================================
    
    const langDropdown = document.querySelector('[data-lang-dropdown]');
    const langToggle = document.querySelector('[data-lang-toggle]');
    
    if (langToggle && langDropdown) {
        langToggle.addEventListener('click', () => {
            langDropdown.classList.toggle('is-open');
        });

        // Language option click
        const langOptions = langDropdown.querySelectorAll('[data-lang]');
        langOptions.forEach(option => {
            option.addEventListener('click', () => {
                const lang = option.dataset.lang;
                const url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
                window.location.href = url.toString();
            });
        });

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!langDropdown.contains(e.target)) {
                langDropdown.classList.remove('is-open');
            }
        });
    }

    // ========================================
    // PRICE CONFIGURATION
    // ========================================
    
    const PRICES = {
        aroma_diffusers: {
            '125ml': 20.90,
            '250ml': 29.90,
            '500ml': 50.90
        },
        scented_candles: {
            '160ml': 24.90,
            '500ml': 59.90
        },
        home_perfume: {
            '10ml': 9.90,
            '50ml': 19.90
        },
        car_perfume: 14.90,
        textile_perfume: 19.90,
        limited_edition: 39.90,
        accessories: {
            'standard': 11.90  // Default price for accessories (can be overridden per product)
        }
    };

    // ========================================
    // PRODUCT SELECTORS
    // ========================================
    
    function initProductSelectors() {
        const productCards = document.querySelectorAll('[data-product-card]');
        
        productCards.forEach(card => {
            const volumeSelect = card.querySelector('[data-volume-select]');
            const fragranceSelect = card.querySelector('[data-fragrance-select]');
            const priceDisplay = card.querySelector('[data-price-display]');
            const fragranceInfo = card.querySelector('[data-fragrance-info]');
            const addToCartBtn = card.querySelector('[data-add-to-cart]');
            const category = card.dataset.category;

            // Volume change handler
            if (volumeSelect) {
                volumeSelect.addEventListener('change', () => {
                    updatePrice(card, category);
                });
            }

            // Fragrance change handler
            if (fragranceSelect) {
                fragranceSelect.addEventListener('change', () => {
                    updateFragranceInfo(card, fragranceSelect.value);
                });
            }

            // Add to cart handler
            if (addToCartBtn) {
                addToCartBtn.addEventListener('click', () => {
                    addToCart(card);
                });
            }
        });
    }

    function updatePrice(card, category) {
        const volumeSelect = card.querySelector('[data-volume-select]');
        const priceDisplay = card.querySelector('[data-price-display]');
        
        if (!priceDisplay) return;

        let price = 0;
        
        if (volumeSelect && PRICES[category]) {
            const volume = volumeSelect.value;
            if (typeof PRICES[category] === 'object') {
                price = PRICES[category][volume] || 0;
            } else {
                price = PRICES[category];
            }
        } else if (typeof PRICES[category] === 'number') {
            price = PRICES[category];
        }

        priceDisplay.textContent = 'CHF ' + price.toFixed(2);
    }

    function updateFragranceInfo(card, fragranceCode) {
        // Update the main product image when fragrance changes
        const productImage = card.querySelector('[data-product-image]');
        const fragranceSelect = card.querySelector('[data-fragrance-select]');
        
        if (productImage && fragranceSelect && fragranceCode && fragranceCode !== 'none') {
            // Get the image path from the data attribute on the selected option
            const selectedOption = fragranceSelect.querySelector('option:checked');
            if (selectedOption && selectedOption.dataset.image) {
                // Use the data-image directly - it already contains the full /img/ path
                productImage.src = selectedOption.dataset.image;
            } else if (window.FRAGRANCES && window.FRAGRANCES[fragranceCode] && window.FRAGRANCES[fragranceCode].image) {
                // Fallback to FRAGRANCES data - image already contains the full /img/ path
                productImage.src = window.FRAGRANCES[fragranceCode].image;
            }
        }
        
        // Update fragrance description from FRAGRANCE_DESCRIPTIONS
        updateFragranceDescription(card, fragranceCode);
        
        // Legacy fragrance info display (if the element exists)
        const fragranceInfo = card.querySelector('[data-fragrance-info]');
        if (!fragranceInfo || !fragranceCode || fragranceCode === 'none') {
            if (fragranceInfo) {
                fragranceInfo.style.display = 'none';
            }
            return;
        }

        // Get fragrance data (this would ideally come from an API)
        const fragranceData = window.FRAGRANCES ? window.FRAGRANCES[fragranceCode] : null;
        
        if (fragranceData) {
            const nameEl = fragranceInfo.querySelector('[data-fragrance-name]');
            const descEl = fragranceInfo.querySelector('[data-fragrance-desc]');
            const imgEl = fragranceInfo.querySelector('[data-fragrance-image]');

            if (nameEl) nameEl.textContent = fragranceData.name || '';
            if (descEl) descEl.textContent = fragranceData.short || '';
            if (imgEl && fragranceData.image) {
                // Use the image path directly - it already contains the full /img/ path
                imgEl.src = fragranceData.image;
                imgEl.alt = fragranceData.name || '';
            }

            fragranceInfo.style.display = 'block';
        }
    }

    // ========================================
    // FRAGRANCE & CATEGORY DESCRIPTION TOGGLE
    // ========================================

    /**
     * Get first N lines from text
     */
    function getShortText(fullText, maxLines) {
        const lines = fullText.split(/\r?\n/).filter(l => l.trim() !== '');
        const shortLines = lines.slice(0, maxLines);
        return shortLines.join('\n');
    }

    /**
     * Update fragrance description in product card
     * Uses 'short' directly from FRAGRANCE_DESCRIPTIONS (from i18n)
     */
    function updateFragranceDescription(card, fragranceCode) {
        const descData = window.FRAGRANCE_DESCRIPTIONS || {};
        const info = descData[fragranceCode];
        const descBlock = card.querySelector('.product-card__fragrance-description');
        
        if (!descBlock) return;
        
        const shortEl = descBlock.querySelector('.product-card__fragrance-text--short');
        const fullEl = descBlock.querySelector('.product-card__fragrance-text--full');
        const toggleBtn = descBlock.querySelector('.product-card__fragrance-toggle');

        if (!shortEl || !fullEl || !toggleBtn) return;

        if (!info || (!info.short && !info.full)) {
            shortEl.textContent = '';
            fullEl.textContent = '';
            toggleBtn.style.display = 'none';
            descBlock.style.display = 'none';
            return;
        }

        // Use 'short' from i18n directly (already translated)
        const short = info.short || '';
        const full = info.full || '';

        shortEl.textContent = short;
        fullEl.textContent = full;
        fullEl.style.display = 'none';
        shortEl.style.display = 'block';
        descBlock.style.display = 'block';

        toggleBtn.style.display = 'inline-block';
        toggleBtn.textContent = getI18NLabel('fragrance_read_more');
        toggleBtn.dataset.expanded = 'false';
    }

    /**
     * Handle fragrance description toggle click
     */
    function onFragranceToggleClick(event) {
        const btn = event.target.closest('.product-card__fragrance-toggle');
        if (!btn) return;

        const card = btn.closest('.product-card');
        if (!card) return;

        const descBlock = card.querySelector('.product-card__fragrance-description');
        if (!descBlock) return;

        const shortEl = descBlock.querySelector('.product-card__fragrance-text--short');
        const fullEl = descBlock.querySelector('.product-card__fragrance-text--full');
        if (!shortEl || !fullEl) return;

        const expanded = btn.dataset.expanded === 'true';

        if (expanded) {
            fullEl.style.display = 'none';
            shortEl.style.display = 'block';
            btn.textContent = getI18NLabel('fragrance_read_more');
            btn.dataset.expanded = 'false';
            descBlock.classList.remove('expanded');
            descBlock.classList.remove('product-card__fragrance-description--expanded');
        } else {
            fullEl.style.display = 'block';
            shortEl.style.display = 'none';
            btn.textContent = getI18NLabel('fragrance_collapse');
            btn.dataset.expanded = 'true';
            descBlock.classList.add('expanded');
            descBlock.classList.add('product-card__fragrance-description--expanded');
        }
    }

    /**
     * Initialize category descriptions with toggle
     */
    function initCategoryDescriptions() {
        document.querySelectorAll('.category-hero__description-block').forEach(block => {
            const full = block.dataset.fullDescription || '';
            const shortEl = block.querySelector('.category-hero__description-short');
            const fullEl = block.querySelector('.category-hero__description-full');
            const toggleBtn = block.querySelector('.category-hero__description-toggle');
            
            if (!shortEl || !fullEl || !toggleBtn || !full) {
                if (toggleBtn) toggleBtn.style.display = 'none';
                return;
            }

            const short = getShortText(full, 3); // first 3 lines for category

            shortEl.textContent = short;
            fullEl.textContent = full;
            fullEl.style.display = 'none';
            shortEl.style.display = 'block';

            toggleBtn.dataset.expanded = 'false';
            toggleBtn.textContent = getI18NLabel('category_read_more');
        });
    }

    /**
     * Handle category description toggle click
     */
    function onCategoryDescriptionToggleClick(event) {
        const btn = event.target.closest('.category-hero__description-toggle');
        if (!btn) return;

        const block = btn.closest('.category-hero__description-block');
        if (!block) return;

        const shortEl = block.querySelector('.category-hero__description-short');
        const fullEl = block.querySelector('.category-hero__description-full');
        if (!shortEl || !fullEl) return;

        const expanded = btn.dataset.expanded === 'true';

        if (expanded) {
            fullEl.style.display = 'none';
            shortEl.style.display = 'block';
            btn.textContent = getI18NLabel('category_read_more');
            btn.dataset.expanded = 'false';
        } else {
            fullEl.style.display = 'block';
            shortEl.style.display = 'none';
            btn.textContent = getI18NLabel('category_collapse');
            btn.dataset.expanded = 'true';
        }
    }

    /**
     * Get I18N label with fallback
     */
    function getI18NLabel(key) {
        const labels = window.I18N_LABELS || {};
        const defaults = {
            fragrance_read_more: 'Read more',
            fragrance_collapse: 'Collapse',
            category_read_more: 'Read more',
            category_collapse: 'Collapse'
        };
        return labels[key] || defaults[key] || key;
    }

    /**
     * Initialize fragrance descriptions on page load
     */
    function initFragranceDescriptions() {
        const productCards = document.querySelectorAll('[data-product-card]');
        productCards.forEach(card => {
            const fragranceSelect = card.querySelector('[data-fragrance-select]');
            if (fragranceSelect && fragranceSelect.value) {
                updateFragranceDescription(card, fragranceSelect.value);
            }
        });
    }

    // Add event listeners for toggle clicks
    document.addEventListener('click', onFragranceToggleClick);
    document.addEventListener('click', onCategoryDescriptionToggleClick);
    
    // Delegated event listener for fragrance select changes
    document.addEventListener('change', function(event) {
        if (!event.target.classList.contains('product-card__select--fragrance')) return;
        
        const select = event.target;
        const productId = select.dataset.productId;
        const option = select.options[select.selectedIndex];
        const imagePath = option.dataset.image;
        const fragranceCode = option.value;
        
        const card = select.closest('.product-card');
        if (!card) return;
        
        // 1) Update image
        const img = card.querySelector('.product-card__image-el[data-product-id="' + productId + '"]');
        if (img && imagePath) {
            img.src = imagePath;
        }
        
        // 2) Update fragrance description (short/full)
        if (typeof updateFragranceDescription === 'function') {
            updateFragranceDescription(card, fragranceCode);
        }
    });

    // ========================================
    // CART FUNCTIONALITY
    // ========================================
    
    function getCart() {
        const cartData = localStorage.getItem('nichehome_cart');
        return cartData ? JSON.parse(cartData) : [];
    }

    function saveCart(cart) {
        localStorage.setItem('nichehome_cart', JSON.stringify(cart));
        updateCartCount();
        // Sync to PHP session
        syncCartToServer(cart);
    }
    
    /**
     * Sync cart to PHP session
     */
    function syncCartToServer(cart) {
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'sync',
                cart: cart
            })
        }).catch(function(error) {
            console.error('Cart sync error:', error);
        });
    }
    
    /**
     * Add item to server cart
     */
    function addItemToServer(item) {
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                item: item
            })
        }).catch(function(error) {
            console.error('Add to cart error:', error);
        });
    }

    function updateCartCount() {
        const cartCountElements = document.querySelectorAll('[data-cart-count]');
        const cart = getCart();
        let count = 0;
        cart.forEach(item => {
            count += item.quantity || 1;
        });
        
        cartCountElements.forEach(el => {
            el.textContent = '(' + count + ')';
        });
    }

    function addToCart(card) {
        const productId = card.dataset.productId;
        const productName = card.dataset.productName;
        const category = card.dataset.category;
        
        const volumeSelect = card.querySelector('[data-volume-select]');
        const fragranceSelect = card.querySelector('[data-fragrance-select]');
        
        const volume = volumeSelect ? volumeSelect.value : 'standard';
        const fragrance = fragranceSelect ? fragranceSelect.value : 'none';

        // Calculate price
        let price = 0;
        if (volumeSelect && PRICES[category] && typeof PRICES[category] === 'object') {
            price = PRICES[category][volume] || 0;
        } else if (typeof PRICES[category] === 'number') {
            price = PRICES[category];
        }

        // Generate SKU
        const sku = generateSKU(productId, volume, fragrance);

        const item = {
            sku: sku,
            productId: productId,
            name: productName,
            category: category,
            volume: volume,
            fragrance: fragrance,
            price: price,
            quantity: 1
        };

        const cart = getCart();
        
        // Check if item already exists
        const existingIndex = cart.findIndex(i => i.sku === sku);
        if (existingIndex > -1) {
            cart[existingIndex].quantity += 1;
        } else {
            cart.push(item);
        }

        saveCart(cart);
        showAddToCartFeedback(card);
    }

    function generateSKU(productId, volume, fragrance) {
        const prefix = productId.substring(0, 3).toUpperCase();
        const vol = volume.replace('ml', '');
        const frag = fragrance.substring(0, 3).toUpperCase();
        return prefix + '-' + vol + '-' + frag;
    }

    function showAddToCartFeedback(card) {
        const btn = card.querySelector('[data-add-to-cart]');
        if (btn) {
            const originalText = btn.textContent;
            btn.textContent = '✓ Added!';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.textContent = originalText;
                btn.disabled = false;
            }, 1500);
        }
    }

    // Remove from cart
    window.removeFromCart = function(sku) {
        let cart = getCart();
        cart = cart.filter(item => item.sku !== sku);
        saveCart(cart);
        
        // Also send remove to server
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'remove',
                sku: sku
            })
        }).then(function() {
            // Reload cart page if on cart page
            if (window.location.pathname.includes('cart.php')) {
                window.location.reload();
            }
        }).catch(function(error) {
            console.error('Remove from cart error:', error);
            // Still reload even on error
            if (window.location.pathname.includes('cart.php')) {
                window.location.reload();
            }
        });
    };

    // Update cart quantity
    window.updateCartQuantity = function(sku, quantity) {
        const cart = getCart();
        const item = cart.find(i => i.sku === sku);
        
        if (item) {
            if (quantity <= 0) {
                removeFromCart(sku);
            } else {
                item.quantity = parseInt(quantity);
                saveCart(cart);
                
                // Also update on server
                fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'update',
                        sku: sku,
                        quantity: parseInt(quantity)
                    })
                }).catch(function(error) {
                    console.error('Update quantity error:', error);
                });
            }
        }
    };
    
    /**
     * Sync localStorage cart to server on page load
     */
    function syncCartOnLoad() {
        const cart = getCart();
        if (cart.length > 0) {
            syncCartToServer(cart);
        }
    }

    // ========================================
    // GIFT SET FUNCTIONALITY
    // ========================================
    
    function initGiftSet() {
        const giftSetForm = document.querySelector('[data-gift-set-form]');
        if (!giftSetForm) return;

        const slots = giftSetForm.querySelectorAll('[data-gift-slot]');
        const totalDisplay = giftSetForm.querySelector('[data-gift-total]');
        const discountDisplay = giftSetForm.querySelector('[data-gift-discount]');

        slots.forEach(slot => {
            const categorySelect = slot.querySelector('[data-gift-category]');
            const productSelect = slot.querySelector('[data-gift-product]');
            const volumeSelect = slot.querySelector('[data-gift-volume]');
            const fragranceSelect = slot.querySelector('[data-gift-fragrance]');

            if (categorySelect) {
                categorySelect.addEventListener('change', () => {
                    updateGiftSlot(slot);
                    updateGiftTotal(giftSetForm);
                });
            }

            if (volumeSelect) {
                volumeSelect.addEventListener('change', () => {
                    updateGiftTotal(giftSetForm);
                });
            }

            if (fragranceSelect) {
                fragranceSelect.addEventListener('change', () => {
                    updateGiftTotal(giftSetForm);
                });
            }
        });

        // Add gift set to cart
        const addGiftSetBtn = giftSetForm.querySelector('[data-add-gift-set]');
        if (addGiftSetBtn) {
            addGiftSetBtn.addEventListener('click', () => {
                addGiftSetToCart(giftSetForm);
            });
        }
    }

    function updateGiftSlot(slot) {
        const categorySelect = slot.querySelector('[data-gift-category]');
        const volumeSelect = slot.querySelector('[data-gift-volume]');
        const fragranceSelect = slot.querySelector('[data-gift-fragrance]');
        
        const category = categorySelect ? categorySelect.value : '';
        
        // Update volume options based on category
        if (volumeSelect) {
            volumeSelect.innerHTML = '<option value="">Select volume</option>';
            
            const volumes = getVolumesForCategory(category);
            volumes.forEach(vol => {
                const option = document.createElement('option');
                option.value = vol;
                option.textContent = vol;
                volumeSelect.appendChild(option);
            });
            
            volumeSelect.style.display = volumes.length > 0 ? 'block' : 'none';
        }
    }

    function getVolumesForCategory(category) {
        const volumeMap = {
            'aroma_diffusers': ['125ml', '250ml', '500ml'],
            'scented_candles': ['160ml', '500ml'],
            'home_perfume': ['10ml', '50ml'],
            'car_perfume': [],
            'textile_perfume': [],
            'limited_edition': []
        };
        return volumeMap[category] || [];
    }

    function updateGiftTotal(form) {
        const slots = form.querySelectorAll('[data-gift-slot]');
        let total = 0;

        slots.forEach(slot => {
            const categorySelect = slot.querySelector('[data-gift-category]');
            const volumeSelect = slot.querySelector('[data-gift-volume]');
            
            const category = categorySelect ? categorySelect.value : '';
            const volume = volumeSelect ? volumeSelect.value : '';

            if (category && PRICES[category]) {
                if (typeof PRICES[category] === 'object' && volume) {
                    total += PRICES[category][volume] || 0;
                } else if (typeof PRICES[category] === 'number') {
                    total += PRICES[category];
                }
            }
        });

        const discount = total * 0.05;
        const finalTotal = total - discount;

        const totalDisplay = form.querySelector('[data-gift-total]');
        const discountDisplay = form.querySelector('[data-gift-discount]');

        if (totalDisplay) {
            totalDisplay.textContent = 'CHF ' + finalTotal.toFixed(2);
        }
        if (discountDisplay) {
            discountDisplay.textContent = '-CHF ' + discount.toFixed(2);
        }
    }

    function addGiftSetToCart(form) {
        const slots = form.querySelectorAll('[data-gift-slot]');
        const items = [];
        let total = 0;

        slots.forEach((slot, index) => {
            const categorySelect = slot.querySelector('[data-gift-category]');
            const volumeSelect = slot.querySelector('[data-gift-volume]');
            const fragranceSelect = slot.querySelector('[data-gift-fragrance]');
            
            const category = categorySelect ? categorySelect.value : '';
            const volume = volumeSelect ? volumeSelect.value : 'standard';
            const fragrance = fragranceSelect ? fragranceSelect.value : 'none';

            if (category) {
                let price = 0;
                if (typeof PRICES[category] === 'object' && volume) {
                    price = PRICES[category][volume] || 0;
                } else if (typeof PRICES[category] === 'number') {
                    price = PRICES[category];
                }

                items.push({
                    slot: index + 1,
                    category: category,
                    volume: volume,
                    fragrance: fragrance,
                    price: price
                });
                total += price;
            }
        });

        if (items.length === 0) {
            alert('Please select at least one product for your gift set.');
            return;
        }

        const discount = total * 0.05;
        const finalTotal = total - discount;

        const giftSetItem = {
            sku: 'GIFTSET-' + Date.now(),
            productId: 'gift_set',
            name: 'Custom Gift Set',
            category: 'gift_sets',
            items: items,
            price: finalTotal,
            quantity: 1,
            isGiftSet: true
        };

        const cart = getCart();
        cart.push(giftSetItem);
        saveCart(cart);

        alert('Gift set added to cart!');
        window.location.href = 'cart.php';
    }

    // ========================================
    // CHECKOUT FORM
    // ========================================
    
    function initCheckoutForm() {
        const checkoutForm = document.querySelector('[data-checkout-form]');
        if (!checkoutForm) return;

        const sameAsShippingCheckbox = checkoutForm.querySelector('[data-same-as-shipping]');
        const billingSection = checkoutForm.querySelector('[data-billing-section]');

        if (sameAsShippingCheckbox && billingSection) {
            sameAsShippingCheckbox.addEventListener('change', () => {
                billingSection.style.display = sameAsShippingCheckbox.checked ? 'none' : 'block';
            });
        }

        // Form submission
        checkoutForm.addEventListener('submit', (e) => {
            // Form validation handled by PHP
        });
    }

    // ========================================
    // BACK IN STOCK NOTIFICATION
    // ========================================
    
    function initBackInStock() {
        const notifyForms = document.querySelectorAll('[data-notify-form]');
        
        notifyForms.forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const emailInput = form.querySelector('input[type="email"]');
                const skuInput = form.querySelector('input[name="sku"]');
                
                if (!emailInput || !skuInput) return;

                const email = emailInput.value;
                const sku = skuInput.value;

                try {
                    const response = await fetch('ajax/notify.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ email, sku })
                    });

                    if (response.ok) {
                        form.innerHTML = '<p class="text-success">Thank you! We\'ll notify you when this item is back in stock.</p>';
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            });
        });
    }

    // ========================================
    // NEWSLETTER FORM
    // ========================================
    
    function initNewsletter() {
        const newsletterForm = document.querySelector('#newsletterForm');
        if (!newsletterForm) return;

        newsletterForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const emailInput = newsletterForm.querySelector('input[type="email"]');
            if (!emailInput) return;

            const email = emailInput.value;

            // Simple validation
            if (!email || !email.includes('@')) {
                alert('Please enter a valid email address.');
                return;
            }

            // In production, this would send to a server
            alert('Thank you for subscribing!');
            emailInput.value = '';
        });
    }

    // ========================================
    // PRODUCT IMAGE GALLERY/SLIDER
    // ========================================
    
    function initProductGallery() {
        const gallery = document.querySelector('[data-product-gallery]');
        if (!gallery) return;
        
        const images = gallery.querySelectorAll('[data-gallery-image]');
        const thumbs = gallery.querySelectorAll('[data-gallery-thumb]');
        const prevBtn = gallery.querySelector('[data-gallery-prev]');
        const nextBtn = gallery.querySelector('[data-gallery-next]');
        
        if (images.length <= 1) return; // No need for gallery with single image
        
        let currentIndex = 0;
        
        function showImage(index) {
            // Ensure index is within bounds
            if (index < 0) index = images.length - 1;
            if (index >= images.length) index = 0;
            
            currentIndex = index;
            
            // Update active image
            images.forEach((img, i) => {
                if (i === index) {
                    img.classList.add('is-active');
                } else {
                    img.classList.remove('is-active');
                }
            });
            
            // Update active thumbnail
            thumbs.forEach((thumb, i) => {
                if (i === index) {
                    thumb.classList.add('is-active');
                } else {
                    thumb.classList.remove('is-active');
                }
            });
        }
        
        // Previous button
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                showImage(currentIndex - 1);
            });
        }
        
        // Next button
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                showImage(currentIndex + 1);
            });
        }
        
        // Thumbnail clicks
        thumbs.forEach((thumb, index) => {
            thumb.addEventListener('click', () => {
                showImage(index);
            });
        });
        
        // Keyboard navigation - only when not in input fields
        document.addEventListener('keydown', (e) => {
            // Don't trigger if user is typing in an input, textarea, or select
            const activeElement = document.activeElement;
            if (activeElement && (
                activeElement.tagName === 'INPUT' ||
                activeElement.tagName === 'TEXTAREA' ||
                activeElement.tagName === 'SELECT'
            )) {
                return;
            }
            
            if (e.key === 'ArrowLeft') {
                showImage(currentIndex - 1);
            } else if (e.key === 'ArrowRight') {
                showImage(currentIndex + 1);
            }
        });
    }

    // ========================================
    // INITIALIZATION
    // ========================================
    
    document.addEventListener('DOMContentLoaded', () => {
        initProductSelectors();
        initGiftSet();
        initCheckoutForm();
        initBackInStock();
        initNewsletter();
        initProductGallery(); // Initialize image gallery/slider
        updateCartCount();
        // Initialize category and fragrance descriptions
        initCategoryDescriptions();
        initFragranceDescriptions();
        // Sync cart from localStorage to server session
        syncCartOnLoad();
    });

})();
