<?php include __DIR__ . '/templates/header.php'; ?>
<section class="section">
    <h1><?php echo I18N::t('ui.nav.catalog'); ?></h1>
    <div class="grid">
        <?php $categories = DataStore::readJson(__DIR__ . '/data/i18n/categories_en.json'); ?>
        <?php foreach ($categories as $slug => $data): ?>
            <div class="card">
                <div class="title"><?php echo I18N::tCategory($slug, 'name'); ?></div>
                <p><?php echo I18N::tCategory($slug, 'description'); ?></p>
                <a class="button" href="/category.php?slug=<?php echo $slug; ?>"><?php echo I18N::t('ui.actions.explore'); ?></a>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>
