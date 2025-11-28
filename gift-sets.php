<?php include __DIR__ . '/templates/header.php'; ?>
<?php
$products = Products::all();
$allowedCategories = ['aroma_diffusers', 'scented_candles', 'home_perfume', 'car_perfume', 'textile_perfume', 'limited_edition'];
$notice = '';
$summary = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gift_set'])) {
    $selected = array_filter($_POST['variant'] ?? []);
    $seen = [];
    $components = [];
    $subtotal = 0.0;
    foreach ($selected as $sku) {
        if (in_array($sku, $seen, true)) {
            continue;
        }
        $seen[] = $sku;
        $info = Products::getProductBySku($sku);
        if ($info) {
            $stockQty = Products::getStock($sku);
            if ($stockQty <= 0) {
                $notice = I18N::t('ui.messages.gift_out_of_stock');
                continue;
            }
            $price = $info['variant']['priceCHF'];
            $components[] = ['sku' => $sku, 'productId' => $info['product']['id'], 'price' => $price];
            $subtotal += $price;
        }
    }
    $discount = $subtotal * 0.05;
    $final = $subtotal - $discount;
    if ($components) {
        Cart::addGiftSet($components, $subtotal, $discount, $final);
        $summary = ['subtotal' => $subtotal, 'discount' => $discount, 'final' => $final];
    }
}
?>
<section class="section">
    <h1><?php echo I18N::t('ui.gift.title'); ?></h1>
    <p><?php echo I18N::t('ui.gift.subtitle'); ?></p>
    <form method="post">
        <input type="hidden" name="gift_set" value="1">
        <div class="grid">
            <?php for ($i = 0; $i < 3; $i++): ?>
                <div class="card gift-row">
                    <div class="form-row">
                        <label><?php echo I18N::t('ui.gift.slot'); ?> <?php echo $i+1; ?></label>
                        <select class="gift-category" name="category[<?php echo $i; ?>]">
                            <option value=""><?php echo I18N::t('ui.actions.select'); ?></option>
                            <?php foreach ($allowedCategories as $slug): ?>
                                <option value="<?php echo $slug; ?>"><?php echo I18N::tCategory($slug, 'name'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <select class="gift-product" name="product[<?php echo $i; ?>]"></select>
                    </div>
                    <div class="form-row">
                        <select class="gift-variant" name="variant[<?php echo $i; ?>]"></select>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
        <button class="button" type="submit"><?php echo I18N::t('ui.gift.add'); ?></button>
        <?php if ($summary): ?>
            <div class="gift-summary">
                <p><?php echo I18N::t('ui.gift.subtotal'); ?>: <?php echo number_format($summary['subtotal'], 2); ?> CHF</p>
                <p><?php echo I18N::t('ui.gift.discount'); ?>: -<?php echo number_format($summary['discount'], 2); ?> CHF</p>
                <strong><?php echo I18N::t('ui.gift.total'); ?>: <?php echo number_format($summary['final'], 2); ?> CHF</strong>
            </div>
        <?php endif; ?>
        <?php if ($notice): ?>
            <p class="gift-notice"><?php echo htmlspecialchars($notice); ?></p>
        <?php endif; ?>
    </form>
    <script type="application/json" id="products-data"><?php echo json_encode(array_map(function($p){
        return [
            'id' => $p['id'],
            'category' => $p['category'],
            'name' => I18N::t($p['nameKey']),
            'variants' => array_map(function($v){
                return [
                    'sku' => $v['sku'],
                    'volume' => $v['volume'] ?? '',
                    'fragrance' => isset($v['fragranceCode']) ? I18N::tFragrance($v['fragranceCode'], 'name') : '',
                    'priceCHF' => $v['priceCHF']
                ];
            }, $p['variants'])
        ];
    }, $products)); ?></script>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>
