<?php include __DIR__ . '/templates/header.php'; ?>
<?php
$slug = $_GET['slug'] ?? '';
$categoryName = I18N::tCategory($slug, 'name');
$categoryDesc = I18N::tCategory($slug, 'short') ?: I18N::tCategory($slug, 'description');
$products = Products::byCategory($slug);
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
            <div class="card product-card">
                <img src="/assets/images/product-placeholder.jpg" alt="">
                <div class="title"><?php echo I18N::t($product['nameKey']); ?></div>
                <p><?php echo I18N::t($product['descriptionKey']); ?></p>
                <form method="post" action="/cart.php">
                    <select name="sku" data-variant-select data-target="price-<?php echo $product['id']; ?>">
                        <?php foreach ($product['variants'] as $variant): ?>
                            <?php $priceLabel = $variant['priceCHF'] . ' ' . $currency; ?>
                            <option data-price="<?php echo $priceLabel; ?>" value="<?php echo $variant['sku']; ?>">
                                <?php echo ($variant['volume'] ?? '') . ' ' . ($variant['fragrance'] ?? ''); ?> - <?php echo $priceLabel; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="price" id="price-<?php echo $product['id']; ?>"><?php echo $product['variants'][0]['priceCHF'] . ' ' . $currency; ?></div>
                    <button class="button" type="submit" name="add_to_cart" value="1"><?php echo I18N::t('ui.actions.add_to_cart'); ?></button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>
