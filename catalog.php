<?php include __DIR__ . '/templates/header.php'; ?>
<?php
$categories = [
    'aroma_diffusers',
    'scented_candles',
    'home_perfume',
    'car_perfume',
    'textile_perfume',
    'limited_edition',
    'accessories',
    'aroma_marketing'
];
?>
<section class="section categories-overview">
    <div class="section-heading section-heading--center">
        <h1><?php echo I18N::t('ui.nav.catalog'); ?></h1>
        <p class="catalog-section__description"><?php echo I18N::t('ui.categories.enjoy'); ?></p>
    </div>
    <div class="category-grid">
        <?php foreach ($categories as $slug): ?>
            <?php
            $title = I18N::tCategory($slug, 'name');
            $short = I18N::tCategory($slug, 'short') ?: I18N::tCategory($slug, 'description');
            ?>
            <a href="/category.php?slug=<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>" class="category-card category-card--grid">
                <div class="category-card__body">
                    <div class="category-card__title"><?php echo $title; ?></div>
                    <p class="category-card__description"><?php echo $short; ?></p>
                </div>
                <span class="category-card__cta">
                    <?php echo I18N::t('ui.actions.explore'); ?>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>
