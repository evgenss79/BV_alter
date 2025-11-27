<?php include __DIR__ . '/templates/header.php'; ?>
<?php
$id = $_GET['id'] ?? '';
$product = Products::get($id);
$currencyLabel = $currency ?? 'CHF';
$volumeFragranceCategories = ['aroma_diffusers', 'home_perfume', 'scented_candles'];
$fragranceOnlyCategories = ['car_perfume', 'limited_edition', 'textile_perfume'];
?>
<?php if ($product): ?>
<?php
    $variants = $product['variants'];
    $volumes = array_values(array_unique(array_filter(array_map(fn($v) => $v['volume'] ?? null, $variants))));
    $fragrances = array_values(array_unique(array_filter(array_map(fn($v) => $v['fragranceCode'] ?? null, $variants))));
    $fragranceData = [];
    foreach ($fragrances as $code) {
        $fragranceData[$code] = [
            'name' => I18N::tFragrance($code, 'name'),
            'shortDescription' => I18N::tFragrance($code, 'shortDescription'),
            'recommendedSpaces' => I18N::tFragrance($code, 'recommendedSpaces'),
            'olfactoryPyramid' => [
                'top' => I18N::tFragrance($code, 'olfactoryPyramid')['top'] ?? [],
                'heart' => I18N::tFragrance($code, 'olfactoryPyramid')['heart'] ?? [],
                'base' => I18N::tFragrance($code, 'olfactoryPyramid')['base'] ?? [],
            ],
        ];
    }
    $firstFragrance = $fragrances[0] ?? null;
    $variantPayload = array_map(function($v) use ($currencyLabel) {
        $v['priceLabel'] = $v['priceCHF'] . ' ' . $currencyLabel;
        $v['stock'] = Products::getStock($v['sku']);
        $v['fragranceCode'] = $v['fragranceCode'] ?? null;
        return $v;
    }, $variants);
    $initialPrice = $variantPayload[0]['priceLabel'] ?? ($variants[0]['priceCHF'] . ' ' . $currencyLabel);
?>
<section class="section product-detail">
    <div class="section-heading">
        <p class="section-heading__label"><?php echo I18N::t('ui.product.label'); ?></p>
        <h1 class="hero__title"><?php echo I18N::t($product['nameKey']); ?></h1>
        <p class="hero__subtitle"><?php echo I18N::t($product['descriptionKey']); ?></p>
    </div>
    <div class="product-detail__grid">
        <div class="product-detail__media">
            <div class="product-image-placeholder"></div>
        </div>
        <div class="product-detail__form">
            <form method="post" action="/cart.php" class="product-config">
                <?php if (in_array($product['category'], $volumeFragranceCategories)): ?>
                    <div class="form-row">
                        <label for="volume-select"><?php echo I18N::t('ui.product.volume'); ?></label>
                        <select id="volume-select" data-volume-select>
                            <?php foreach ($volumes as $volume): ?>
                                <option value="<?php echo htmlspecialchars($volume); ?>"><?php echo htmlspecialchars($volume); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="fragrance-select"><?php echo I18N::t('ui.product.fragrance'); ?></label>
                        <select id="fragrance-select" data-fragrance-select>
                            <?php foreach ($fragrances as $code): ?>
                                <option value="<?php echo htmlspecialchars($code); ?>"><?php echo htmlspecialchars(I18N::tFragrance($code, 'name')); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php elseif (in_array($product['category'], $fragranceOnlyCategories)): ?>
                    <div class="form-row">
                        <label for="fragrance-select-single"><?php echo I18N::t('ui.product.fragrance'); ?></label>
                        <select id="fragrance-select-single" data-single-fragrance>
                            <?php foreach ($fragrances as $code): ?>
                                <option value="<?php echo htmlspecialchars($code); ?>"><?php echo htmlspecialchars(I18N::tFragrance($code, 'name')); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <input type="hidden" data-sku-input name="sku" value="<?php echo htmlspecialchars($variants[0]['sku']); ?>">
                <?php endif; ?>
                <div class="price-row">
                    <div>
                        <p class="price-label"><?php echo I18N::t('ui.product.price'); ?></p>
                        <p class="price-value" data-price><?php echo $initialPrice; ?></p>
                        <p class="stock-label" data-stock-label></p>
                    </div>
                </div>
                <input type="hidden" name="sku" data-sku-input value="<?php echo htmlspecialchars($variants[0]['sku']); ?>">
                <button class="btn btn--gold" type="submit" name="add_to_cart" value="1" data-add-to-cart>
                    <?php echo I18N::t('ui.actions.add_to_cart'); ?>
                </button>
            </form>
            <div class="fragrance-panel">
                <h3 data-fragrance-name><?php echo $firstFragrance ? I18N::tFragrance($firstFragrance, 'name') : ''; ?></h3>
                <p class="fragrance-panel__intro" data-fragrance-short><?php echo $firstFragrance ? I18N::tFragrance($firstFragrance, 'shortDescription') : ''; ?></p>
                <div class="olfactory">
                    <div>
                        <strong><?php echo I18N::t('ui.product.olfactory_top'); ?></strong>
                        <ul data-pyramid-top></ul>
                    </div>
                    <div>
                        <strong><?php echo I18N::t('ui.product.olfactory_heart'); ?></strong>
                        <ul data-pyramid-heart></ul>
                    </div>
                    <div>
                        <strong><?php echo I18N::t('ui.product.olfactory_base'); ?></strong>
                        <ul data-pyramid-base></ul>
                    </div>
                </div>
                <p class="fragrance-panel__recommended" data-fragrance-recommended></p>
            </div>
        </div>
    </div>
</section>
<script id="product-data" type="application/json"><?php echo json_encode([
    'variants' => $variantPayload,
    'fragrances' => $fragranceData,
    'labels' => [
        'addToCart' => I18N::t('ui.actions.add_to_cart'),
        'notify' => I18N::t('ui.actions.notify'),
        'inStock' => I18N::t('ui.product.in_stock'),
        'outOfStock' => I18N::t('ui.product.out_of_stock'),
    ]
]); ?></script>
<?php endif; ?>
<?php include __DIR__ . '/templates/footer.php'; ?>
