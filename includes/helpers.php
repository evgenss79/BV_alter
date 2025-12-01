<?php
/**
 * Helper Functions for NICHEHOME.CH
 */

/**
 * Get allowed fragrances for a category
 */
function allowedFragrances(string $category): array {
    $all = [
        'cherry_blossom', 'bellini', 'eden', 'rosso',
        'salted_caramel', 'santal', 'lime_basil', 'bamboo',
        'tobacco_vanilla', 'salty_water', 'christmas_tree',
        'fleur', 'blanc', 'green_mango', 'carolina',
        'sugar', 'dubai', 'africa', 'dune',
        'valencia', 'etna', 'new_york', 'abu_dhabi', 'palermo'
    ];

    $exclude = ['new_york', 'abu_dhabi', 'palermo'];  // never used except Limited Edition

    if ($category === 'scented_candles') {
        $exclude = array_merge($exclude, ['etna', 'valencia']);
    }
    if ($category === 'textile_perfume') {
        $exclude = array_merge($exclude, [
            'salted_caramel', 'cherry_blossom', 'dubai',
            'salty_water', 'rosso', 'christmas_tree'
        ]);
    }
    if ($category === 'limited_edition') {
        // Limited edition ONLY uses new_york, abu_dhabi, palermo
        return ['new_york', 'abu_dhabi', 'palermo'];
    }

    return array_values(array_diff($all, $exclude));
}

/**
 * Get price for diffuser by volume
 */
function diffuserPriceByVolume(string $volume): float {
    switch ($volume) {
        case '125ml':
        case '125':
            return 20.90;
        case '250ml':
        case '250':
            return 29.90;
        case '500ml':
        case '500':
            return 50.90;
        default:
            return 0.0;
    }
}

/**
 * Get price for candle by volume
 */
function candlePriceByVolume(string $volume): float {
    switch ($volume) {
        case '160ml':
        case '160':
            return 24.90;
        case '500ml':
        case '500':
            return 59.90;
        default:
            return 0.0;
    }
}

/**
 * Get price for home perfume by volume
 */
function homePerfumePriceByVolume(string $volume): float {
    switch ($volume) {
        case '10ml':
        case '10':
            return 9.90;
        case '50ml':
        case '50':
            return 19.90;
        default:
            return 0.0;
    }
}

/**
 * Get price for car perfume (fixed)
 */
function carPerfumePrice(): float {
    return 14.90;
}

/**
 * Get price for textile perfume (fixed)
 */
function textilePerfumePrice(): float {
    return 19.90;
}

/**
 * Get price for limited edition candles (fixed)
 */
function limitedEditionPrice(): float {
    return 39.90;
}

/**
 * Get price by category and volume
 */
function getPriceByCategory(string $category, string $volume = ''): float {
    switch ($category) {
        case 'aroma_diffusers':
            return diffuserPriceByVolume($volume);
        case 'scented_candles':
            return candlePriceByVolume($volume);
        case 'home_perfume':
            return homePerfumePriceByVolume($volume);
        case 'car_perfume':
            return carPerfumePrice();
        case 'textile_perfume':
            return textilePerfumePrice();
        case 'limited_edition':
            return limitedEditionPrice();
        default:
            return 0.0;
    }
}

/**
 * Get volumes for a category
 */
function getVolumesForCategory(string $category): array {
    switch ($category) {
        case 'aroma_diffusers':
            return ['125ml', '250ml', '500ml'];
        case 'scented_candles':
            return ['160ml', '500ml'];
        case 'home_perfume':
            return ['10ml', '50ml'];
        default:
            return [];
    }
}

/**
 * Format price with currency
 */
function formatPrice(float $price, string $currency = 'CHF'): string {
    return $currency . ' ' . number_format($price, 2);
}

/**
 * Generate SKU
 */
function generateSKU(string $productId, string $volume, string $fragrance): string {
    $prefix = strtoupper(substr($productId, 0, 3));
    $vol = str_replace('ml', '', $volume);
    $frag = strtoupper(substr($fragrance, 0, 3));
    return $prefix . '-' . $vol . '-' . $frag;
}

/**
 * Load JSON file
 */
function loadJSON(string $filename): array {
    $path = __DIR__ . '/../data/' . $filename;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        return json_decode($content, true) ?? [];
    }
    return [];
}

/**
 * Save JSON file with locking
 */
function saveJSON(string $filename, array $data): bool {
    $path = __DIR__ . '/../data/' . $filename;
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    $fp = fopen($path, 'w');
    if (flock($fp, LOCK_EX)) {
        fwrite($fp, $json);
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }
    fclose($fp);
    return false;
}

/**
 * Get stock for SKU
 */
function getStock(string $sku): int {
    $stock = loadJSON('stock.json');
    return $stock[$sku]['quantity'] ?? 0;
}

/**
 * Update stock
 */
function updateStock(string $sku, int $quantity): bool {
    $stock = loadJSON('stock.json');
    if (isset($stock[$sku])) {
        $stock[$sku]['quantity'] = $quantity;
        return saveJSON('stock.json', $stock);
    }
    return false;
}

/**
 * Decrease stock
 */
function decreaseStock(string $sku, int $amount = 1): bool {
    $stock = loadJSON('stock.json');
    if (isset($stock[$sku]) && $stock[$sku]['quantity'] >= $amount) {
        $stock[$sku]['quantity'] -= $amount;
        return saveJSON('stock.json', $stock);
    }
    return false;
}

/**
 * Get cart from session
 */
function getCart(): array {
    return $_SESSION['cart'] ?? [];
}

/**
 * Add to cart
 */
function addToCart(array $item): void {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $sku = $item['sku'] ?? '';
    $found = false;
    
    foreach ($_SESSION['cart'] as &$cartItem) {
        if ($cartItem['sku'] === $sku) {
            $cartItem['quantity'] += $item['quantity'] ?? 1;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $item['quantity'] = $item['quantity'] ?? 1;
        $_SESSION['cart'][] = $item;
    }
}

/**
 * Update cart item quantity
 */
function updateCartQuantity(string $sku, int $quantity): void {
    if (!isset($_SESSION['cart'])) return;
    
    foreach ($_SESSION['cart'] as $key => &$item) {
        if ($item['sku'] === $sku) {
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$key]);
                $_SESSION['cart'] = array_values($_SESSION['cart']);
            } else {
                $item['quantity'] = $quantity;
            }
            break;
        }
    }
}

/**
 * Remove from cart
 */
function removeFromCart(string $sku): void {
    if (!isset($_SESSION['cart'])) return;
    
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['sku'] === $sku) {
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            break;
        }
    }
}

/**
 * Clear cart
 */
function clearCart(): void {
    $_SESSION['cart'] = [];
}

/**
 * Get cart total
 */
function getCartTotal(): float {
    $total = 0;
    foreach (getCart() as $item) {
        $total += ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
    }
    return $total;
}

/**
 * Get cart item count
 */
function getCartCount(): int {
    $count = 0;
    foreach (getCart() as $item) {
        $count += $item['quantity'] ?? 1;
    }
    return $count;
}

/**
 * Sanitize input
 */
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate order ID
 */
function generateOrderId(): string {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
}

/**
 * Get fragrance image path - uses /img/ folder
 */
function getFragranceImage(string $fragranceCode): string {
    $imageMap = [
        'cherry_blossom' => 'Cherry-Blossom.png',
        'bellini' => 'Bellini.jpg',
        'eden' => 'Eden.jpg',
        'rosso' => 'Rosso.jpg',
        'salted_caramel' => 'Salted caramel.jpg',
        'santal' => 'Santal 2.jpg',
        'lime_basil' => 'Lime Basil.jpg',
        'bamboo' => 'Bamboo.jpg',
        'tobacco_vanilla' => 'Tob Van.jpg',
        'salty_water' => 'Salty Water.jpg',
        'christmas_tree' => 'Christmas Tree.jpg',
        'fleur' => 'Fleur.png',
        'blanc' => 'Blanc.jpg',
        'green_mango' => 'Green Mango 2.jpg',
        'carolina' => 'Carolina-2.png',
        'sugar' => 'Sugar.jpg',
        'dubai' => 'Dubai.jpg',
        'africa' => 'Africa.jpg',
        'dune' => 'Dune.png',
        'valencia' => 'Valencia.jpg',
        'etna' => 'Etna.jpg',
        'new_york' => 'New-York.jpg',
        'abu_dhabi' => 'abu-dhabi.jpg',
        'palermo' => 'Palermo.jpg'
    ];
    
    $filename = $imageMap[$fragranceCode] ?? '';
    if ($filename) {
        return '/img/' . rawurlencode($filename);
    }
    return '/img/placeholder.svg';
}

/**
 * Get fragrance image path with file existence check
 * Used for data-image attributes in select options
 */
function getFragranceImagePath(string $fragranceCode): string {
    return getFragranceImage($fragranceCode);
}

/**
 * Get product image path - uses /img/ folder
 */
function getProductImagePath(string $productId): string {
    // Products don't have specific images, use placeholder
    // or fall back to fragrance images based on product context
    return '/img/placeholder.svg';
}

/**
 * Get category image path - uses /img/ folder
 */
function getCategoryImage(string $category): string {
    $imageMap = [
        'aroma_diffusers' => 'Aroma diffusers_category.jpg',
        'scented_candles' => 'Candels category.jpg',
        'home_perfume' => 'home pefume.jpg',
        'car_perfume' => 'AutoParf.jpg',
        'textile_perfume' => 'Textil-spray.jpg',
        'limited_edition' => '3 velas.jpg',
        'gift_sets' => 'ETSY-foto.jpg',
        'accessories' => 'ETSY-foto.jpg',
        'aroma_marketing' => 'ETSY-foto.jpg'
    ];
    
    $filename = $imageMap[$category] ?? '';
    if ($filename) {
        return '/img/' . rawurlencode($filename);
    }
    return '/img/placeholder.svg';
}

/**
 * Get category image path helper (alias for getCategoryImage)
 */
function getCategoryImagePath(string $categorySlug): string {
    return getCategoryImage($categorySlug);
}

/**
 * Check if user is logged in as admin
 */
function isAdminLoggedIn(): bool {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Check if user has admin role
 */
function isAdmin(): bool {
    return isAdminLoggedIn() && ($_SESSION['admin_role'] ?? '') === 'admin';
}

/**
 * Check if user has manager role
 */
function isManager(): bool {
    return isAdminLoggedIn() && in_array($_SESSION['admin_role'] ?? '', ['admin', 'manager']);
}

/**
 * Redirect with language parameter
 */
function redirectWithLang(string $url): void {
    $lang = I18N::getLanguage();
    $separator = strpos($url, '?') !== false ? '&' : '?';
    header('Location: ' . $url . $separator . 'lang=' . $lang);
    exit;
}
