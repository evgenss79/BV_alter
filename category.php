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

// Determine if this is Home Perfume category for hero image scaling
$heroImageClass = $slug === 'home_perfume' ? 'hero-home-perfume' : '';

// Handle accessories category specially
if ($slug === 'accessories') {
    // Get Aroma Sashe product
    $aromaSashe = $products['aroma_sashe'] ?? null;
    $aromaSasheFragrances = [];
    if ($aromaSashe && isset($aromaSashe['allowed_fragrances'])) {
        $aromaSasheFragrances = $aromaSashe['allowed_fragrances'];
    }
    
    // Build multilingual fragrance descriptions for Aroma Sashe
    $fragranceDescriptions = [];
    foreach ($aromaSasheFragrances as $fragCode) {
        $fragranceDescriptions[$fragCode] = [
            'name' => I18N::t('fragrance.' . $fragCode . '.name', ucfirst(str_replace('_', ' ', $fragCode))),
            'short' => I18N::t('fragrance.' . $fragCode . '.short', ''),
            'full' => I18N::t('fragrance.' . $fragCode . '.full', '')
        ];
    }
    
    include __DIR__ . '/includes/header.php';
    ?>
    <main class="category-page">
    <section class="category-hero">
        <div class="category-hero-text">
            <div class="category-hero__content">
                <h1><?php echo htmlspecialchars($categoryName); ?></h1>
                <div class="category-hero__description-block"
                     data-full-description="<?php echo htmlspecialchars($categoryLong ?: $categoryShort, ENT_QUOTES); ?>">
                    <p class="category-hero__description-short"></p>
                    <p class="category-hero__description-full"></p>
                    <button type="button" class="category-hero__description-toggle">
                        <?php echo I18N::t('ui.category.read_more', 'Read more'); ?>
                    </button>
                </div>
            </div>
        </div>
        <div class="category-hero-image">
            <div class="category-hero__image <?php echo $heroImageClass; ?>" data-category="<?php echo htmlspecialchars($slug); ?>">
                <img src="<?php echo htmlspecialchars($categoryImage); ?>" 
                     alt="<?php echo htmlspecialchars($categoryName); ?>" 
                     class="category-hero__image-el" 
                     onerror="this.src='/img/placeholder.svg'">
            </div>
        </div>
    </section>

    <section class="category-products">
        <div class="accessories-grid">
            <?php if ($aromaSashe): ?>
                <?php
                $productId = 'aroma_sashe';
                $productName = I18N::t('product.aroma_sashe.name', 'Aroma Sashé');
                $productDesc = I18N::t('product.aroma_sashe.desc', '');
                $productImage = $aromaSashe['image'] ?? '';
                $hoverImage = $aromaSashe['hover_image'] ?? '';
                $productVariants = $aromaSashe['variants'] ?? [];
                $defaultPrice = !empty($productVariants) ? ($productVariants[0]['priceCHF'] ?? 0) : 0;
                $firstFragCode = !empty($aromaSasheFragrances) ? $aromaSasheFragrances[0] : null;
                $displayImage = $productImage ? '/img/' . rawurlencode($productImage) : '/img/placeholder.svg';
                $hoverImagePath = $hoverImage ? '/img/' . rawurlencode($hoverImage) : '';
                ?>
                <article class="catalog-card product-card" 
                         data-product-card 
                         data-product-id="<?php echo htmlspecialchars($productId); ?>"
                         data-product-name="<?php echo htmlspecialchars($productName); ?>"
                         data-category="<?php echo htmlspecialchars($slug); ?>">
                    <div class="catalog-card__title-bar product-card__inner">
                        <?php echo htmlspecialchars($productName); ?>
                    </div>
                    <div class="catalog-card__image-wrapper product-card__image">
                        <img src="<?php echo htmlspecialchars($displayImage); ?>" 
                             alt="<?php echo htmlspecialchars($productName); ?>" 
                             class="catalog-card__image product-card__image-el"
                             data-product-image
                             data-product-id="<?php echo htmlspecialchars($productId); ?>"
                             data-default-image="<?php echo htmlspecialchars($displayImage); ?>"
                             <?php if ($hoverImagePath): ?>
                             data-hover-image="<?php echo htmlspecialchars($hoverImagePath); ?>"
                             <?php endif; ?>
                             onerror="this.src='/img/placeholder.svg'">
                    </div>
                    <div class="product-card__content" style="padding: 1rem;">
                        <div class="product-card__selectors">
                            <?php if (!empty($aromaSasheFragrances)): ?>
                                <div class="product-card__field">
                                    <label><?php echo I18N::t('common.fragrance', 'Fragrance'); ?></label>
                                    <select class="product-card__select product-card__select--fragrance" 
                                            data-fragrance-select
                                            data-product-id="<?php echo htmlspecialchars($productId); ?>">
                                        <?php foreach ($aromaSasheFragrances as $fragCode): ?>
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
                            <?php endif; ?>
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
                </article>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= 7; $i++): ?>
                <article class="catalog-card">
                    <div class="catalog-card__title-bar">
                        <?php echo I18N::t('common.accessory', 'Accessory') . ' ' . $i; ?>
                    </div>
                    <div class="catalog-card__image-wrapper">
                        <img src="/img/placeholder.svg" 
                             alt="<?php echo I18N::t('common.accessory', 'Accessory') . ' ' . $i; ?>" 
                             class="catalog-card__image">
                    </div>
                </article>
            <?php endfor; ?>
        </div>
    </section>
    </main>

    <script>
    // Pass fragrance data to JavaScript for Aroma Sashe
    window.FRAGRANCES = <?php echo json_encode(array_map(function($code) {
        return [
            'name' => I18N::t('fragrance.' . $code . '.name', ucfirst(str_replace('_', ' ', $code))),
            'short' => I18N::t('fragrance.' . $code . '.short', ''),
            'image' => getFragranceImage($code)
        ];
    }, array_combine($aromaSasheFragrances, $aromaSasheFragrances))); ?>;
    
    // Pass multilingual fragrance descriptions from i18n
    window.FRAGRANCE_DESCRIPTIONS = <?php echo json_encode($fragranceDescriptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP); ?>;
    
    // Pass I18N labels for JS
    window.I18N_LABELS = {
        fragrance_read_more: <?php echo json_encode(I18N::t('ui.fragrance.read_more', 'Read more')); ?>,
        fragrance_collapse: <?php echo json_encode(I18N::t('ui.fragrance.collapse', 'Collapse')); ?>,
        category_read_more: <?php echo json_encode(I18N::t('ui.category.read_more', 'Read more')); ?>,
        category_collapse: <?php echo json_encode(I18N::t('ui.category.collapse', 'Collapse')); ?>
    };
    
    // Add hover effect for products with hover images
    document.addEventListener('DOMContentLoaded', function() {
        const productsWithHover = document.querySelectorAll('[data-hover-image]');
        productsWithHover.forEach(function(img) {
            const defaultImg = img.src;
            const hoverImg = img.dataset.hoverImage;
            
            img.addEventListener('mouseenter', function() {
                this.src = hoverImg;
            });
            
            img.addEventListener('mouseleave', function() {
                this.src = defaultImg;
            });
        });
    });
    </script>

    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Get products for this category
$categoryProducts = array_filter($products, function($p) use ($slug) {
    return ($p['category'] ?? '') === $slug;
});

// Get allowed fragrances for this category
$allowedFrags = allowedFragrances($slug);

// Get volumes for this category
$volumes = getVolumesForCategory($slug);

// Build multilingual fragrance descriptions from i18n
$fragranceDescriptions = [];
foreach ($allowedFrags as $fragCode) {
    $fragranceDescriptions[$fragCode] = [
        'name' => I18N::t('fragrance.' . $fragCode . '.name', ucfirst(str_replace('_', ' ', $fragCode))),
        'short' => I18N::t('fragrance.' . $fragCode . '.short', ''),
        'full' => I18N::t('fragrance.' . $fragCode . '.full', '')
    ];
}

include __DIR__ . '/includes/header.php';
?>

<?php
// Get category full description for toggle
$fullCategoryDescription = $categoryLong ?: $categoryShort;
?>

<main class="category-page">
<section class="category-hero">
    <div class="category-hero-text">
        <div class="category-hero__content">
            <h1><?php echo htmlspecialchars($categoryName); ?></h1>
            <div class="category-hero__description-block"
                 data-full-description="<?php echo htmlspecialchars($fullCategoryDescription, ENT_QUOTES); ?>">
                <p class="category-hero__description-short"></p>
                <p class="category-hero__description-full"></p>
                <button type="button" class="category-hero__description-toggle">
                    <?php echo I18N::t('ui.category.read_more', 'Read more'); ?>
                </button>
            </div>
        </div>
    </div>
    <div class="category-hero-image">
        <div class="category-hero__image <?php echo $heroImageClass; ?>" data-category="<?php echo htmlspecialchars($slug); ?>">
            <img src="<?php echo htmlspecialchars($categoryImage); ?>" 
                 alt="<?php echo htmlspecialchars($categoryName); ?>" 
                 class="category-hero__image-el" 
                 onerror="this.src='/img/placeholder.svg'">
        </div>
    </div>
</section>

<section class="category-products">
    <div class="products-list">
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
            
            // Get the first fragrance for initial image display (if fragrance select exists)
            $firstFragCode = !empty($allowedFrags) ? $allowedFrags[0] : null;
            // For limited edition, use the fixed fragrance
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
            <article class="product-card" 
                     data-product-card 
                     data-product-id="<?php echo htmlspecialchars($productId); ?>"
                     data-product-name="<?php echo htmlspecialchars($productName); ?>"
                     data-category="<?php echo htmlspecialchars($slug); ?>">
                <div class="product-card__inner">
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
                            <?php if ($slug !== 'limited_edition' && $slug !== 'car_perfume'): ?>
                                <p class="product-card__description"><?php echo htmlspecialchars($productDesc); ?></p>
                            <?php endif; ?>
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
        <?php endforeach; ?>
        
        <?php if (empty($categoryProducts)): ?>
            <?php
            // Set variables for generic card
            $genericProductId = $slug . '_product';
            $genericFirstFrag = !empty($allowedFrags) ? $allowedFrags[0] : null;
            $genericDisplayImage = $genericFirstFrag 
                ? getFragranceImage($genericFirstFrag) 
                : $categoryImage;
            ?>
            <!-- Show generic product card for categories without specific products -->
            <article class="product-card" 
                     data-product-card 
                     data-product-id="<?php echo htmlspecialchars($genericProductId); ?>"
                     data-product-name="<?php echo htmlspecialchars($categoryName); ?>"
                     data-category="<?php echo htmlspecialchars($slug); ?>">
                <div class="product-card__inner">
                    <div class="product-card__image">
                        <img src="<?php echo htmlspecialchars($genericDisplayImage); ?>" 
                             alt="<?php echo htmlspecialchars($categoryName); ?>" 
                             class="product-card__image-el"
                             data-product-image
                             data-product-id="<?php echo htmlspecialchars($genericProductId); ?>"
                             data-default-image="<?php echo htmlspecialchars($genericDisplayImage); ?>"
                             onerror="this.src='/img/placeholder.svg'">
                    </div>
                    
                    <div class="product-card__content">
                        <header class="product-card__header">
                            <h2 class="product-card__title"><?php echo htmlspecialchars($categoryName); ?></h2>
                            <p class="product-card__description"><?php echo htmlspecialchars($categoryShort); ?></p>
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
                            
                            <?php if (!empty($allowedFrags)): ?>
                                <div class="product-card__field">
                                    <label><?php echo I18N::t('common.fragrance', 'Fragrance'); ?></label>
                                    <select class="product-card__select product-card__select--fragrance" 
                                            data-fragrance-select
                                            data-product-id="<?php echo htmlspecialchars($genericProductId); ?>">
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
                            <?php endif; ?>
                        </div>
                        
                        <!-- Fragrance Description Block -->
                        <div class="product-card__fragrance-description"
                             data-product-id="<?php echo htmlspecialchars($genericProductId); ?>">
                            <p class="product-card__fragrance-text product-card__fragrance-text--short"></p>
                            <p class="product-card__fragrance-text product-card__fragrance-text--full"></p>
                            <button type="button" class="product-card__fragrance-toggle">
                                <?php echo I18N::t('ui.fragrance.read_more', 'Read more'); ?>
                            </button>
                        </div>
                        
                        <div class="product-card__price-row">
                            <span class="product-card__price-label"><?php echo I18N::t('common.price', 'Price'); ?></span>
                            <span class="product-card__price-value" data-price-display>
                                CHF <?php echo number_format(getPriceByCategory($slug, $volumes[0] ?? ''), 2); ?>
                            </span>
                        </div>
                        
                        <button type="button" class="btn btn--gold product-card__add-to-cart" data-add-to-cart>
                            <?php echo I18N::t('common.addToCart', 'Add to cart'); ?>
                        </button>
                    </div>
                </div>
            </article>
        <?php endif; ?>
    </div>
</section>
</main>

<script>
// Pass fragrance data to JavaScript with correct /img/ paths
window.FRAGRANCES = <?php echo json_encode(array_map(function($code) {
    return [
        'name' => I18N::t('fragrance.' . $code . '.name', ucfirst(str_replace('_', ' ', $code))),
        'short' => I18N::t('fragrance.' . $code . '.short', ''),
        'image' => getFragranceImage($code)
    ];
}, array_combine($allowedFrags, $allowedFrags))); ?>;

// Pass multilingual fragrance descriptions from i18n
window.FRAGRANCE_DESCRIPTIONS = <?php echo json_encode($fragranceDescriptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP); ?>;

// Pass I18N labels for JS
window.I18N_LABELS = {
    fragrance_read_more: <?php echo json_encode(I18N::t('ui.fragrance.read_more', 'Read more')); ?>,
    fragrance_collapse: <?php echo json_encode(I18N::t('ui.fragrance.collapse', 'Collapse')); ?>,
    category_read_more: <?php echo json_encode(I18N::t('ui.category.read_more', 'Read more')); ?>,
    category_collapse: <?php echo json_encode(I18N::t('ui.category.collapse', 'Collapse')); ?>
};
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
