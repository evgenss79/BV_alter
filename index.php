<?php include __DIR__ . '/templates/header.php'; ?>
<section class="hero">
    <div>
        <div class="headline"><?php echo I18N::t('ui.home.headline'); ?></div>
        <p><?php echo I18N::t('ui.home.subtext'); ?></p>
        <a class="button" href="/catalog.php"><?php echo I18N::t('ui.nav.catalog'); ?></a>
    </div>
    <div class="card">
        <p><?php echo I18N::t('ui.home.highlight'); ?></p>
        <div class="tag"><?php echo I18N::t('ui.home.swiss'); ?></div>
    </div>
</section>
<section class="section">
    <h2><?php echo I18N::t('ui.home.categories'); ?></h2>
    <div class="grid">
        <?php foreach (DataStore::readJson(__DIR__ . '/data/products.json') as $product): ?>
            <div class="card product-card">
                <img src="/assets/images/product-placeholder.jpg" alt="">
                <div class="title"><?php echo I18N::t($product['nameKey']); ?></div>
                <p><?php echo I18N::t($product['descriptionKey']); ?></p>
                <a class="button" href="/product.php?id=<?php echo $product['id']; ?>"><?php echo I18N::t('ui.actions.view'); ?></a>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>
