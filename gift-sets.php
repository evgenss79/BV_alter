<?php include __DIR__ . '/templates/header.php'; ?>
<?php
$products = Products::all();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gift_set'])) {
    $selected = array_filter($_POST['variant'] ?? []);
    $components = [];
    $subtotal = 0.0;
    foreach ($selected as $sku) {
        $info = Products::getProductBySku($sku);
        if ($info) {
            $components[] = ['sku' => $sku, 'productId' => $info['product']['id'], 'price' => $info['variant']['priceCHF']];
            $subtotal += $info['variant']['priceCHF'];
        }
    }
    $discount = $subtotal * 0.05;
    $final = $subtotal - $discount;
    if ($components) {
        Cart::addGiftSet($components, $subtotal, $discount, $final);
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
                            <?php foreach (DataStore::readJson(__DIR__ . '/data/i18n/categories_en.json') as $slug => $label): ?>
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
