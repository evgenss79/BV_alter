<?php
/**
 * Catalog - Category Overview
 */

require_once __DIR__ . '/init.php';
include __DIR__ . '/includes/header.php';

$categories = loadJSON('categories.json');
$currentLang = I18N::getLanguage();

// Define which categories to display on catalog page
// We show accessories instead of gift_sets on catalog cards
// gift_sets is still accessible via navigation
$catalogCategories = [
    'aroma_diffusers',
    'scented_candles',
    'home_perfume',
    'car_perfume',
    'textile_perfume',
    'limited_edition',
    'accessories',       // instead of gift_sets
    'aroma_marketing',
];

// Filter and keep only catalog categories in the correct order
$displayCategories = [];
foreach ($catalogCategories as $slug) {
    if (isset($categories[$slug])) {
        $displayCategories[$slug] = $categories[$slug];
    }
}
?>
</main>
<script>document.body.classList.add('is-catalog-page');</script>

<main class="catalog-page">
<section class="page-hero">
    <div class="page-hero__content">
        <h1 class="page-title"><?php echo I18N::t('page.catalog.title', 'Catalog'); ?></h1>
    </div>
</section>

<section class="catalog-section">
    <div class="catalog-grid">
        <?php foreach ($displayCategories as $slug => $category): ?>
            <?php
            $name = I18N::t('category.' . $slug . '.name', ucfirst(str_replace('_', ' ', $slug)));
            $image = getCategoryImage($slug);
            
            // Determine link
            if (isset($category['redirect'])) {
                $link = $category['redirect'] . '?lang=' . $currentLang;
            } else {
                $link = 'category.php?slug=' . urlencode($slug) . '&lang=' . $currentLang;
            }
            ?>
            <a href="<?php echo htmlspecialchars($link); ?>" class="catalog-card">
                <div class="catalog-card__title-bar">
                    <?php echo htmlspecialchars($name); ?>
                </div>
                <div class="catalog-card__image-wrapper">
                    <img src="<?php echo htmlspecialchars($image); ?>" 
                         alt="<?php echo htmlspecialchars($name); ?>" 
                         class="catalog-card__image"
                         onerror="this.src='/img/placeholder.svg'">
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>