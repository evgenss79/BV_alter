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

<section class="category-hero">
    <div class="category-hero-text">
        <div class="category-hero__content">
            <p class="section-heading__label">
                <a href="category.php?slug=<?php echo htmlspecialchars($categorySlug); ?>&lang=<?php echo $currentLang; ?>">
                    <?php echo htmlspecialchars($categoryName); ?>
                </a>
            </p>
            <h1><?php echo htmlspecialchars($productName); ?></h1>
            <p class="category-hero__desc"><?php echo nl2br(htmlspecialchars($productDesc)); ?></p>
        </div>
    </div>
    <div class="category-hero-image">
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
            <div class="category-hero__image" data-category="<?php echo htmlspecialchars($categorySlug); ?>">
                <img src="<?php echo $imgPrefix . htmlspecialchars($singleImagePath); ?>" 
                     alt="<?php echo htmlspecialchars($productName); ?>" 
                     class="category-hero__image-el"
                     onerror="this.src='<?php echo $errorPlaceholder; ?>'">
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="catalog-section">
    <div class="container">
        <div class="product-card-wrapper">
            <article class="product-card" 
                     data-product-card 
                     data-product-id="<?php echo htmlspecialchars($productId); ?>"
                     data-product-name="<?php echo htmlspecialchars($productName); ?>"
                     data-category="<?php echo htmlspecialchars($categorySlug); ?>">
                <div class="product-card__inner">
                    <?php 
                    // Get first fragrance for initial image display
                    $firstFragCode = !empty($allowedFrags) ? $allowedFrags[0] : null;
                    if ($isLimitedWithFixed) {
                        $firstFragCode = $product['fragrance'];
                    }
                    
                    // Determine the image to show - use fragrance image from /img/ folder
                    $displayImage = '/img/placeholder.svg';
                    if ($firstFragCode) {
                        $displayImage = getFragranceImage($firstFragCode);
                    } elseif ($productImage) {
                        $displayImage = '/img/' . rawurlencode($productImage);
                    }
                    ?>
                    <div class="product-card__image">
                        <img src="<?php echo htmlspecialchars($displayImage); ?>" 
                             alt="<?php echo htmlspecialchars($productName); ?>" 
                             class="product-card__image-el"
                             data-product-image
                             data-product-id="<?php echo htmlspecialchars($productId); ?>"
                             data-default-image="<?php echo htmlspecialchars($displayImage); ?>"
                             onerror="this.src='/img/placeholder.svg'">
                    </div>
                    
                    <div class="product-card__content">
                        <header class="product-card__header">
                            <h2 class="product-card__title"><?php echo htmlspecialchars($productName); ?></h2>
                        </header>
                        
                        <div class="product-card__selectors">
                            <?php if (!empty($volumes)): ?>
                                <div class="product-card__field">
                                    <label><?php echo I18N::t('common.volume', 'Volume'); ?></label>
                                    <select class="product-card__select product-card__select--volume" data-volume-select>
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
                                    <select class="product-card__select product-card__select--fragrance" 
                                            data-fragrance-select
                                            data-product-id="<?php echo htmlspecialchars($productId); ?>">
                                        <?php foreach ($allowedFrags as $fragCode): ?>
                                            <?php
                                            $fragName = I18N::t('fragrance.' . $fragCode . '.name', ucfirst(str_replace('_', ' ', $fragCode)));
                                            ?>
                                            <option value="<?php echo htmlspecialchars($fragCode); ?>"
                                                    data-image="<?php echo htmlspecialchars(getFragranceImage($fragCode)); ?>">
                                                <?php echo htmlspecialchars($fragName); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php elseif ($isLimitedWithFixed): ?>
                                <input type="hidden" data-fragrance-select value="<?php echo htmlspecialchars($product['fragrance']); ?>">
                            <?php endif; ?>
                        </div>
                        
                        <!-- Fragrance Description Block -->
                        <div class="product-card__fragrance-description"
                             data-product-id="<?php echo htmlspecialchars($productId); ?>">
                            <p class="product-card__fragrance-text product-card__fragrance-text--short"></p>
                            <p class="product-card__fragrance-text product-card__fragrance-text--full"></p>
                            <button type="button" class="product-card__fragrance-toggle">
                                <?php echo I18N::t('ui.fragrance.read_more', 'Read more'); ?>
                            </button>
                        </div>
                        
                        <div class="product-card__price-row">
                            <span class="product-card__price-label"><?php echo I18N::t('common.price', 'Price'); ?></span>
                            <span class="product-card__price-value" data-price-display>
                                CHF <?php echo number_format($defaultPrice, 2); ?>
                            </span>
                        </div>
                        
                        <button type="button" class="btn btn--gold product-card__add-to-cart" data-add-to-cart>
                            <?php echo I18N::t('common.addToCart', 'Add to cart'); ?>
                        </button>
                    </div>
                </div>
            </article>
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

// Pass multilingual fragrance descriptions from i18n
window.FRAGRANCE_DESCRIPTIONS = <?php 
$fragranceDescriptions = [];
foreach ($allowedFrags as $fragCode) {
    $fragranceDescriptions[$fragCode] = [
        'name' => I18N::t('fragrance.' . $fragCode . '.name', ucfirst(str_replace('_', ' ', $fragCode))),
        'short' => I18N::t('fragrance.' . $fragCode . '.short', ''),
        'full' => I18N::t('fragrance.' . $fragCode . '.full', '')
    ];
}
echo json_encode($fragranceDescriptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP); 
?>;

// Pass I18N labels for JS
window.I18N_LABELS = {
    fragrance_read_more: <?php echo json_encode(I18N::t('ui.fragrance.read_more', 'Read more')); ?>,
    fragrance_collapse: <?php echo json_encode(I18N::t('ui.fragrance.collapse', 'Collapse')); ?>
};
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
