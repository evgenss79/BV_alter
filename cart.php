<?php include __DIR__ . '/templates/header.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_cart']) && isset($_POST['sku'])) {
        Cart::add($_POST['sku']);
    }
    if (isset($_POST['remove'])) {
        Cart::remove($_POST['remove']);
    }
}
$cart = Cart::get();
$total = Cart::total();
?>
<section class="section">
    <h1><?php echo I18N::t('ui.cart.title'); ?></h1>
    <table class="table">
        <thead><tr><th><?php echo I18N::t('ui.cart.item'); ?></th><th><?php echo I18N::t('ui.cart.qty'); ?></th><th><?php echo I18N::t('ui.cart.price'); ?></th><th></th></tr></thead>
        <tbody>
        <?php foreach ($cart as $key => $item): ?>
            <tr>
                <td><?php echo $item['sku']; ?></td>
                <td><?php echo $item['qty']; ?></td>
                <td><?php echo number_format($item['priceCHF'] * $item['qty'], 2); ?> CHF</td>
                <td>
                    <form method="post">
                        <button class="button" name="remove" value="<?php echo $key; ?>">&times;</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <h3><?php echo I18N::t('ui.cart.total'); ?>: <?php echo number_format($total, 2); ?> CHF</h3>
    <a class="button" href="/checkout.php"><?php echo I18N::t('ui.cart.checkout'); ?></a>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>
