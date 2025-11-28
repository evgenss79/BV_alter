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
        limited_edition: 39.90
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
                imgEl.src = 'assets/img/fragrances/' + fragranceData.image;
                imgEl.alt = fragranceData.name || '';
            }

            fragranceInfo.style.display = 'block';
        }
    }

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
        
        // Reload cart page if on cart page
        if (window.location.pathname.includes('cart.php')) {
            window.location.reload();
        }
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
            }
        }
    };

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
    // INITIALIZATION
    // ========================================
    
    document.addEventListener('DOMContentLoaded', () => {
        initProductSelectors();
        initGiftSet();
        initCheckoutForm();
        initBackInStock();
        initNewsletter();
        updateCartCount();
    });

})();
