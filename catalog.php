<?php
/**
 * Catalog - Category Overview
 */

require_once __DIR__ . '/init.php';
include __DIR__ . '/includes/header.php';

$categories = loadJSON('categories.json');
$currentLang = I18N::getLanguage();

// Sort categories by sort_order
uasort($categories, function($a, $b) {
    return ($a['sort_order'] ?? 99) - ($b['sort_order'] ?? 99);
});
?>

<section class="page-hero">
    <div class="page-hero__content">
        <h1 class="page-title"><?php echo I18N::t('page.catalog.title', 'Catalog'); ?></h1>
    </div>
</section>

<section class="catalog-section">
    <div class="catalog-grid">
        <?php foreach ($categories as $slug => $category): ?>
            <?php
            $name = I18N::t('category.' . $slug . '.name', ucfirst(str_replace('_', ' ', $slug)));
            $short = I18N::t('category.' . $slug . '.short', '');
            $image = getCategoryImage($slug);
            
            // Determine link
            if (isset($category['redirect'])) {
                $link = $category['redirect'] . '?lang=' . $currentLang;
            } else {
                $link = 'category.php?slug=' . urlencode($slug) . '&lang=' . $currentLang;
            }
            ?>
            <a href="<?php echo htmlspecialchars($link); ?>" class="category-card">
                <div class="category-card__image">
                    <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($name); ?>" class="category-card__image-el" onerror="this.src='/img/placeholder.svg'">
                </div>
                <div class="category-card__content">
                    <h3 class="category-card__title"><?php echo htmlspecialchars($name); ?></h3>
                    <p class="category-card__desc"><?php echo htmlspecialchars($short); ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>