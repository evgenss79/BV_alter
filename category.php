<?php
/**
 * Category - Products listing for a single category
 */

require_once __DIR__ . '/init.php';

$slug = $_GET['slug'] ?? '';
$currentLang = I18N::getLanguage();

// Redirect special categories
if ($slug === 'gift_sets') {
    header('Location: gift-sets.php?lang=' . $currentLang);
    exit;
}
if ($slug === 'aroma_marketing') {
    header('Location: aroma-marketing.php?lang=' . $currentLang);
    exit;
}

// Load category data
$categories = loadJSON('categories.json');
$products = loadJSON('products.json');
$fragrances = loadJSON('fragrances.json');
$stock = loadJSON('stock.json');

if (!isset($categories[$slug])) {
    header('Location: catalog.php?lang=' . $currentLang);
    exit;
}

$category = $categories[$slug];
$categoryName = I18N::t('category.' . $slug . '.name', ucfirst(str_replace('_', ' ', $slug)));
$categoryShort = I18N::t('category.' . $slug . '.short', '');
$categoryLong = I18N::t('category.' . $slug . '.long', '');
$categoryImage = getCategoryImage($slug);

// Get products for this category
$categoryProducts = array_filter($products, function($p) use ($slug) {
    return ($p['category'] ?? '') === $slug;
});

// Get allowed fragrances for this category
$allowedFrags = allowedFragrances($slug);

// Get volumes for this category
$volumes = getVolumesForCategory($slug);

include __DIR__ . '/includes/header.php';
?>

<section class="category-hero">
    <div class="category-hero__content">
        <h1><?php echo htmlspecialchars($categoryName); ?></h1>
        <p class="category-hero__desc"><?php echo nl2br(htmlspecialchars($categoryLong ?: $categoryShort)); ?></p>
    </div>
    <img src="<?php echo htmlspecialchars($categoryImage); ?>" alt="<?php echo htmlspecialchars($categoryName); ?>" class="category-hero__image" onerror="this.src='assets/img/placeholder.jpg'">
</section>

<section class="category-products">
    <div class="products-grid">
        <?php foreach ($categoryProducts as $productId => $product): ?>
            <?php
            $productName = I18N::t('product.' . $productId . '.name', $product['name_key'] ?? $productId);
            $productDesc = I18N::t('product.' . $productId . '.desc', $product['desc_key'] ?? '');
            $productImage = $product['image'] ?? '';
            $productVariants = $product['variants'] ?? [];
            
            // Check if this is a limited edition product with fixed fragrance
            $isLimitedWithFixed = ($slug === 'limited_edition' && isset($product['fragrance']));
            
            // Get first variant price as default
            $defaultPrice = 0;
            if (!empty($productVariants)) {
                $defaultPrice = $productVariants[0]['priceCHF'] ?? 0;
            }
            ?>
            <article class="product-card" 
                     data-product-card 
                     data-product-id="<?php echo htmlspecialchars($productId); ?>"
                     data-product-name="<?php echo htmlspecialchars($productName); ?>"
                     data-category="<?php echo htmlspecialchars($slug); ?>">
                
                <img src="assets/img/<?php echo htmlspecialchars($productImage); ?>" 
                     alt="<?php echo htmlspecialchars($productName); ?>" 
                     class="product-card__image"
                     onerror="this.src='assets/img/placeholder.jpg'">
                
                <p class="product-card__eyebrow"><?php echo htmlspecialchars($categoryName); ?></p>
                <h3 class="product-card__title"><?php echo htmlspecialchars($productName); ?></h3>
                <p class="product-card__desc"><?php echo htmlspecialchars($productDesc); ?></p>
                
                <div class="product-card__options">
                    <?php if (!empty($volumes)): ?>
                        <div class="product-card__field">
                            <label><?php echo I18N::t('common.volume', 'Volume'); ?></label>
                            <select data-volume-select>
                                <?php foreach ($volumes as $vol): ?>
                                    <?php 
                                    $volPrice = getPriceByCategory($slug, $vol);
                                    ?>
                                    <option value="<?php echo htmlspecialchars($vol); ?>">
                                        <?php echo htmlspecialchars($vol); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!$isLimitedWithFixed && !empty($allowedFrags)): ?>
                        <div class="product-card__field">
                            <label><?php echo I18N::t('common.fragrance', 'Fragrance'); ?></label>
                            <select data-fragrance-select>
                                <option value="none"><?php echo I18N::t('common.selectFragrance', 'Select fragrance'); ?></option>
                                <?php foreach ($allowedFrags as $fragCode): ?>
                                    <?php
                                    $fragName = I18N::t('fragrance.' . $fragCode . '.name', ucfirst(str_replace('_', ' ', $fragCode)));
                                    ?>
                                    <option value="<?php echo htmlspecialchars($fragCode); ?>">
                                        <?php echo htmlspecialchars($fragName); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php elseif ($isLimitedWithFixed): ?>
                        <input type="hidden" data-fragrance-select value="<?php echo htmlspecialchars($product['fragrance']); ?>">
                    <?php endif; ?>
                </div>
                
                <div class="fragrance-info" data-fragrance-info style="display: none;">
                    <img src="" alt="" class="fragrance-info__image" data-fragrance-image>
                    <p class="fragrance-info__name" data-fragrance-name></p>
                    <p class="fragrance-info__desc" data-fragrance-desc></p>
                </div>
                
                <div class="product-card__meta">
                    <span><?php echo I18N::t('common.price', 'Price'); ?></span>
                    <span class="product-card__price" data-price-display>
                        CHF <?php echo number_format($defaultPrice, 2); ?>
                    </span>
                </div>
                
                <button class="btn btn--gold" data-add-to-cart>
                    <?php echo I18N::t('common.addToCart', 'Add to cart'); ?>
                </button>
            </article>
        <?php endforeach; ?>
        
        <?php if (empty($categoryProducts)): ?>
            <!-- Show generic product card for categories without specific products -->
            <article class="product-card" 
                     data-product-card 
                     data-product-id="<?php echo htmlspecialchars($slug); ?>_product"
                     data-product-name="<?php echo htmlspecialchars($categoryName); ?>"
                     data-category="<?php echo htmlspecialchars($slug); ?>">
                
                <img src="<?php echo htmlspecialchars($categoryImage); ?>" 
                     alt="<?php echo htmlspecialchars($categoryName); ?>" 
                     class="product-card__image"
                     onerror="this.src='assets/img/placeholder.jpg'">
                
                <p class="product-card__eyebrow"><?php echo htmlspecialchars($categoryName); ?></p>
                <h3 class="product-card__title"><?php echo htmlspecialchars($categoryName); ?></h3>
                <p class="product-card__desc"><?php echo htmlspecialchars($categoryShort); ?></p>
                
                <div class="product-card__options">
                    <?php if (!empty($volumes)): ?>
                        <div class="product-card__field">
                            <label><?php echo I18N::t('common.volume', 'Volume'); ?></label>
                            <select data-volume-select>
                                <?php foreach ($volumes as $vol): ?>
                                    <option value="<?php echo htmlspecialchars($vol); ?>">
                                        <?php echo htmlspecialchars($vol); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($allowedFrags)): ?>
                        <div class="product-card__field">
                            <label><?php echo I18N::t('common.fragrance', 'Fragrance'); ?></label>
                            <select data-fragrance-select>
                                <option value="none"><?php echo I18N::t('common.selectFragrance', 'Select fragrance'); ?></option>
                                <?php foreach ($allowedFrags as $fragCode): ?>
                                    <?php
                                    $fragName = I18N::t('fragrance.' . $fragCode . '.name', ucfirst(str_replace('_', ' ', $fragCode)));
                                    ?>
                                    <option value="<?php echo htmlspecialchars($fragCode); ?>">
                                        <?php echo htmlspecialchars($fragName); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="fragrance-info" data-fragrance-info style="display: none;">
                    <img src="" alt="" class="fragrance-info__image" data-fragrance-image>
                    <p class="fragrance-info__name" data-fragrance-name></p>
                    <p class="fragrance-info__desc" data-fragrance-desc></p>
                </div>
                
                <div class="product-card__meta">
                    <span><?php echo I18N::t('common.price', 'Price'); ?></span>
                    <span class="product-card__price" data-price-display>
                        CHF <?php echo number_format(getPriceByCategory($slug, $volumes[0] ?? ''), 2); ?>
                    </span>
                </div>
                
                <button class="btn btn--gold" data-add-to-cart>
                    <?php echo I18N::t('common.addToCart', 'Add to cart'); ?>
                </button>
            </article>
        <?php endif; ?>
    </div>
</section>

<script>
// Pass fragrance data to JavaScript
window.FRAGRANCES = <?php echo json_encode(array_map(function($code) {
    $fragrances = loadJSON('fragrances.json');
    return [
        'name' => I18N::t('fragrance.' . $code . '.name', ucfirst(str_replace('_', ' ', $code))),
        'short' => I18N::t('fragrance.' . $code . '.short', ''),
        'image' => $fragrances[$code]['image'] ?? ''
    ];
}, array_combine($allowedFrags, $allowedFrags))); ?>;
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
