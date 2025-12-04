<?php
/**
 * Product - Single product page
 */

require_once __DIR__ . '/init.php';

$productId = $_GET['id'] ?? '';
$currentLang = I18N::getLanguage();

// Load data
$products = loadJSON('products.json');
$categories = loadJSON('categories.json');
$fragrances = loadJSON('fragrances.json');
$accessoriesData = loadJSON('accessories.json');

if (!isset($products[$productId])) {
    header('Location: catalog.php?lang=' . $currentLang);
    exit;
}

$product = $products[$productId];
$categorySlug = $product['category'] ?? '';
$category = $categories[$categorySlug] ?? [];

$productName = I18N::t('product.' . $productId . '.name', $product['name_key'] ?? $productId);
$productDesc = I18N::t('product.' . $productId . '.desc', $product['desc_key'] ?? '');
$categoryName = I18N::t('category.' . $categorySlug . '.name', ucfirst(str_replace('_', ' ', $categorySlug)));
$productImage = $product['image'] ?? '';
$productVariants = $product['variants'] ?? [];

// Check if this is an accessory with multiple images
$productImages = [];
$isAccessory = false;
if ($categorySlug === 'accessories' && isset($accessoriesData[$productId])) {
    $isAccessory = true;
    $accessoryData = $accessoriesData[$productId];
    if (isset($accessoryData['images']) && is_array($accessoryData['images'])) {
        $productImages = $accessoryData['images'];
    }
    // Override price from accessories.json if available
    if (isset($accessoryData['priceCHF'])) {
        $defaultPrice = $accessoryData['priceCHF'];
    }
}

// Fallback to single image if no images in accessories
if (empty($productImages) && $productImage) {
    $productImages = [$productImage];
}

// Ensure we have at least a placeholder if no images at all
if (empty($productImages)) {
    $productImages = ['placeholder.jpg'];
}

// Determine image paths based on category
$imgPrefix = ($isAccessory) ? 'img/' : 'assets/img/';
$errorPlaceholder = ($isAccessory) ? 'img/placeholder.svg' : 'assets/img/placeholder.jpg';

// Get allowed fragrances and volumes
// For accessories, use data from accessories.json if available
if ($isAccessory && isset($accessoryData['allowed_fragrances'])) {
    $allowedFrags = $accessoryData['allowed_fragrances'];
} elseif ($categorySlug === 'accessories' && isset($product['allowed_fragrances'])) {
    $allowedFrags = $product['allowed_fragrances'];
} else {
    $allowedFrags = allowedFragrances($categorySlug);
}
$volumes = getVolumesForCategory($categorySlug);

// Check if limited edition with fixed fragrance
$isLimitedWithFixed = ($categorySlug === 'limited_edition' && isset($product['fragrance']));

// Get default price
if (!isset($defaultPrice)) {
    $defaultPrice = 0;
    if (!empty($productVariants)) {
        $defaultPrice = $productVariants[0]['priceCHF'] ?? 0;
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="category-hero <?php echo ($categorySlug === 'accessories' ? 'product-page--accessories' : ''); ?>">
    <div class="category-hero__content">
        <p class="section-heading__label">
            <a href="category.php?slug=<?php echo htmlspecialchars($categorySlug); ?>&lang=<?php echo $currentLang; ?>">
                <?php echo htmlspecialchars($categoryName); ?>
            </a>
        </p>
        <h1><?php echo htmlspecialchars($productName); ?></h1>
        <p class="category-hero__desc"><?php echo nl2br(htmlspecialchars($productDesc)); ?></p>
    </div>
    
    <?php if (count($productImages) > 1): ?>
        <!-- Image Gallery/Slider for multiple images -->
        <div class="product-gallery" data-product-gallery>
            <div class="product-gallery__main">
                <?php foreach ($productImages as $index => $imgFile): ?>
                    <img 
                        src="<?php echo $imgPrefix . htmlspecialchars($imgFile); ?>"
                        alt="<?php echo htmlspecialchars($productName); ?>"
                        class="product-gallery__image <?php echo $index === 0 ? 'is-active' : ''; ?>"
                        data-gallery-image="<?php echo $index; ?>"
                        onerror="this.src='<?php echo $errorPlaceholder; ?>'">
                <?php endforeach; ?>
            </div>
            <div class="product-gallery__nav">
                <button type="button" class="product-gallery__prev" data-gallery-prev aria-label="Previous image">&lt;</button>
                <button type="button" class="product-gallery__next" data-gallery-next aria-label="Next image">&gt;</button>
            </div>
            <div class="product-gallery__thumbs">
                <?php foreach ($productImages as $index => $imgFile): ?>
                    <img
                        src="<?php echo $imgPrefix . htmlspecialchars($imgFile); ?>"
                        alt="<?php echo htmlspecialchars($productName); ?>"
                        class="product-gallery__thumb <?php echo $index === 0 ? 'is-active' : ''; ?>"
                        data-gallery-thumb="<?php echo $index; ?>"
                        onerror="this.src='<?php echo $errorPlaceholder; ?>'">
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Single image display -->
        <?php $singleImagePath = !empty($productImages) ? $productImages[0] : 'placeholder.jpg'; ?>
        <img src="<?php echo $imgPrefix . htmlspecialchars($singleImagePath); ?>" 
             alt="<?php echo htmlspecialchars($productName); ?>" 
             class="category-hero__image"
             onerror="this.src='<?php echo $errorPlaceholder; ?>'">
    <?php endif; ?>
</section>

<section class="catalog-section">
    <div class="container">
        <div style="max-width: 600px; margin: 0 auto;">
            <article class="product-card" 
                     data-product-card 
                     data-product-id="<?php echo htmlspecialchars($productId); ?>"
                     data-product-name="<?php echo htmlspecialchars($productName); ?>"
                     data-category="<?php echo htmlspecialchars($categorySlug); ?>">
                
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
                        <p><strong><?php echo I18N::t('common.fragrance', 'Fragrance'); ?>:</strong> 
                           <?php echo htmlspecialchars(I18N::t('fragrance.' . $product['fragrance'] . '.name', $product['fragrance'])); ?>
                        </p>
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
                
                <button class="btn btn--gold" data-add-to-cart style="width: 100%;">
                    <?php echo I18N::t('common.addToCart', 'Add to cart'); ?>
                </button>
            </article>
        </div>
    </div>
</section>

<!-- Recommended Products -->
<section class="category-products">
    <div class="container">
        <h2 class="text-center mb-4"><?php echo I18N::t('product.recommended', 'You might also like'); ?></h2>
        <div class="products-grid">
            <?php
            // Get other products from same category
            $recommendedProducts = array_filter($products, function($p, $id) use ($categorySlug, $productId) {
                return ($p['category'] ?? '') === $categorySlug && $id !== $productId;
            }, ARRAY_FILTER_USE_BOTH);
            
            $recommendedProducts = array_slice($recommendedProducts, 0, 3, true);
            
            foreach ($recommendedProducts as $recId => $recProduct):
                $recName = I18N::t('product.' . $recId . '.name', $recProduct['name_key'] ?? $recId);
                $recImage = $recProduct['image'] ?? '';
                $recVariants = $recProduct['variants'] ?? [];
                $recPrice = !empty($recVariants) ? ($recVariants[0]['priceCHF'] ?? 0) : 0;
            ?>
                <a href="product.php?id=<?php echo htmlspecialchars($recId); ?>&lang=<?php echo $currentLang; ?>" class="product-card" style="text-decoration: none;">
                    <img src="assets/img/<?php echo htmlspecialchars($recImage); ?>" 
                         alt="<?php echo htmlspecialchars($recName); ?>" 
                         class="product-card__image"
                         onerror="this.src='assets/img/placeholder.jpg'">
                    <h3 class="product-card__title"><?php echo htmlspecialchars($recName); ?></h3>
                    <div class="product-card__meta">
                        <span class="product-card__price">CHF <?php echo number_format($recPrice, 2); ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
window.FRAGRANCES = <?php echo json_encode(array_map(function($code) {
    return [
        'name' => I18N::t('fragrance.' . $code . '.name', ucfirst(str_replace('_', ' ', $code))),
        'short' => I18N::t('fragrance.' . $code . '.short', ''),
        'image' => getFragranceImage($code)
    ];
}, array_combine($allowedFrags, $allowedFrags))); ?>;
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
