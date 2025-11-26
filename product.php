<?php include __DIR__ . '/templates/header.php'; ?>
<?php
$id = $_GET['id'] ?? '';
$product = Products::get($id);
?>
<?php if ($product): ?>
<section class="hero">
    <div>
        <div class="headline"><?php echo I18N::t($product['nameKey']); ?></div>
        <p><?php echo I18N::t($product['descriptionKey']); ?></p>
    </div>
    <div class="card">
        <p><?php echo I18N::t('ui.product.fragrance'); ?></p>
    </div>
</section>
<section class="section">
    <form method="post" action="/cart.php">
        <select name="sku" data-variant-select data-target="price-product">
            <?php foreach ($product['variants'] as $variant): ?>
                <?php $priceLabel = $variant['priceCHF'] . ' ' . $currency; ?>
                <option data-price="<?php echo $priceLabel; ?>" value="<?php echo $variant['sku']; ?>">
                    <?php echo ($variant['volume'] ?? '') . ' ' . ($variant['fragrance'] ?? ''); ?> - <?php echo $priceLabel; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="price" id="price-product"><?php echo $product['variants'][0]['priceCHF'] . ' ' . $currency; ?></div>
        <button class="button" type="submit" name="add_to_cart" value="1"><?php echo I18N::t('ui.actions.add_to_cart'); ?></button>
    </form>
</section>
<?php endif; ?>
<?php include __DIR__ . '/templates/footer.php'; ?>
