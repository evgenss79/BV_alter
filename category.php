<?php include __DIR__ . '/templates/header.php'; ?>
<?php
$slug = $_GET['slug'] ?? '';
$categoryName = I18N::tCategory($slug, 'name');
$categoryDesc = I18N::tCategory($slug, 'short') ?: I18N::tCategory($slug, 'description');
$products = Products::byCategory($slug);
$volumeFragranceCategories = ['aroma_diffusers', 'home_perfume', 'scented_candles'];
$fragranceOnlyCategories = ['car_perfume', 'textile_perfume', 'limited_edition'];
?>
<section class="hero">
    <div>
        <div class="headline"><?php echo $categoryName; ?></div>
        <p><?php echo $categoryDesc; ?></p>
    </div>
    <div class="card">
        <p><?php echo I18N::t('ui.categories.enjoy'); ?></p>
    </div>
</section>
<section class="section">
    <div class="grid">
        <?php foreach ($products as $product): ?>
            <?php
                $categorySlug = $product['category'];
                $allowedFragrances = Fragrances::allowedFragrances($categorySlug);
                $variants = array_values(array_filter($product['variants'], function ($variant) use ($allowedFragrances) {
                    return empty($variant['fragranceCode']) || in_array($variant['fragranceCode'], $allowedFragrances, true);
                }));
                $volumes = array_values(array_unique(array_filter(array_map(fn($v) => $v['volume'] ?? null, $variants))));
                $variantFragrances = array_values(array_unique(array_filter(array_map(fn($v) => $v['fragranceCode'] ?? null, $variants))));
                $fragrances = array_values(array_intersect($allowedFragrances, $variantFragrances));
                $volumePrices = [];
                foreach ($volumes as $volume) {
                    $volumePrices[$volume] = Products::getPrice($categorySlug, $volume, $variants[0]['priceCHF'] ?? 0);
                }
                $initialVariant = $variants[0] ?? null;
                $initialPriceValue = $initialVariant ? Products::getPrice($categorySlug, $initialVariant['volume'] ?? null, $initialVariant['priceCHF'] ?? 0) : 0;
                $initialPrice = $initialVariant ? ($initialPriceValue . ' ' . $currency) : '';
                $fallbackDescription = I18N::tCategory($categorySlug, 'short') ?: I18N::tCategory($categorySlug, 'description');
                $fragranceData = [];
                foreach ($fragrances as $code) {
                    $fragranceData[$code] = [
                        'name' => I18N::t("fragrance.$code.name"),
                        'shortDescription' => I18N::t("fragrance.$code.shortDescription"),
                        'fullDescription' => I18N::t("fragrance.$code.fullDescription"),
                        'image' => Fragrances::getImagePath($code),
                    ];
                }
                $variantPayload = array_map(function($v) use ($currency, $categorySlug) {
                    $v['priceCHF'] = Products::getPrice($categorySlug, $v['volume'] ?? null, $v['priceCHF'] ?? 0);
                    $v['priceLabel'] = $v['priceCHF'] . ' ' . $currency;
                    $v['stock'] = Products::getStock($v['sku']);
                    $v['fragranceCode'] = $v['fragranceCode'] ?? null;
                    return $v;
                }, $variants);
            ?>
            <div class="card product-card">
                <img src="/assets/images/product-placeholder.jpg" alt="">
                <div class="title"><?php echo I18N::t($product['nameKey']); ?></div>
                <p><?php echo I18N::t($product['descriptionKey']); ?></p>
                <form method="post" action="/cart.php">
                    <?php if (in_array($categorySlug, $volumeFragranceCategories)): ?>
                        <label for="volume-<?php echo $product['id']; ?>"><?php echo I18N::t('ui.product.volume'); ?></label>
                        <select id="volume-<?php echo $product['id']; ?>" data-volume-select>
                            <?php foreach ($volumes as $volume): ?>
                                <option value="<?php echo htmlspecialchars($volume); ?>"><?php echo htmlspecialchars($volume); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label for="fragrance-<?php echo $product['id']; ?>"><?php echo I18N::t('ui.product.fragrance'); ?></label>
                        <select id="fragrance-<?php echo $product['id']; ?>" data-fragrance-select>
                            <option value=""><?php echo I18N::t('ui.actions.select'); ?></option>
                            <?php foreach ($fragrances as $code): ?>
                                <option value="<?php echo htmlspecialchars($code); ?>"><?php echo I18N::t("fragrance.$code.name"); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif (in_array($categorySlug, $fragranceOnlyCategories)): ?>
                        <label for="fragrance-<?php echo $product['id']; ?>"><?php echo I18N::t('ui.product.fragrance'); ?></label>
                        <select id="fragrance-<?php echo $product['id']; ?>" data-single-fragrance>
                            <option value=""><?php echo I18N::t('ui.actions.select'); ?></option>
                            <?php foreach ($fragrances as $code): ?>
                                <option value="<?php echo htmlspecialchars($code); ?>"><?php echo I18N::t("fragrance.$code.name"); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                    <div class="price" id="price-<?php echo $product['id']; ?>" data-price><?php echo htmlspecialchars($initialPrice); ?></div>
                    <input type="hidden" name="sku" data-sku-input value="<?php echo htmlspecialchars($initialVariant['sku'] ?? ''); ?>">
                    <button class="button" type="submit" name="add_to_cart" value="1" data-add-to-cart><?php echo I18N::t('ui.actions.add_to_cart'); ?></button>
                    <div class="fragrance-name" data-fragrance-name><?php echo I18N::tCategory($categorySlug, 'name'); ?></div>
                    <div id="fragrance-info-<?php echo $product['id']; ?>" class="fragrance-info" data-fragrance-short><?php echo htmlspecialchars($fallbackDescription); ?></div>
                    <img id="fragrance-image-<?php echo $product['id']; ?>" class="fragrance-image" data-fragrance-image alt="" src="/assets/images/product-placeholder.jpg">
                    <script type="application/json" class="product-data"><?php echo json_encode([
                        'category' => $categorySlug,
                        'currency' => $currency,
                        'variants' => $variantPayload,
                        'priceByVolume' => $volumePrices,
                        'fragrances' => $fragranceData,
                        'fallback' => [
                            'title' => I18N::tCategory($categorySlug, 'name'),
                            'description' => $fallbackDescription,
                            'image' => '/assets/images/product-placeholder.jpg',
                        ],
                        'initialPrice' => $initialPrice,
                        'labels' => [
                            'addToCart' => I18N::t('ui.actions.add_to_cart'),
                            'notify' => I18N::t('ui.actions.notify'),
                            'inStock' => I18N::t('ui.product.in_stock'),
                            'outOfStock' => I18N::t('ui.product.out_of_stock'),
                        ]
                    ], JSON_UNESCAPED_SLASHES); ?></script>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>
