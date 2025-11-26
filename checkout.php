<?php include __DIR__ . '/templates/header.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orders = DataStore::readJson(__DIR__ . '/data/orders.json');
    $orders[] = [
        'id' => uniqid('order_'),
        'timestamp' => time(),
        'language' => $currentLang,
        'customer' => [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? ''
        ],
        'shipping' => $_POST['address'] ?? '',
        'comment' => $_POST['comment'] ?? '',
        'items' => Cart::get(),
        'totalCHF' => Cart::total(),
        'payment' => $_POST['payment'] ?? 'twint'
    ];
    DataStore::writeJson(__DIR__ . '/data/orders.json', $orders);
    $_SESSION['cart'] = [];
    echo '<div class="card">' . I18N::t('ui.checkout.thanks') . '</div>';
}
?>
<section class="section">
    <h1><?php echo I18N::t('ui.checkout.title'); ?></h1>
    <form method="post">
        <div class="form-row">
            <label><?php echo I18N::t('ui.checkout.name'); ?></label>
            <input name="name" required>
        </div>
        <div class="form-row">
            <label><?php echo I18N::t('ui.checkout.email'); ?></label>
            <input type="email" name="email" required>
        </div>
        <div class="form-row">
            <label><?php echo I18N::t('ui.checkout.phone'); ?></label>
            <input name="phone">
        </div>
        <div class="form-row">
            <label><?php echo I18N::t('ui.checkout.address'); ?></label>
            <textarea name="address" required></textarea>
        </div>
        <div class="form-row">
            <label><?php echo I18N::t('ui.checkout.comment'); ?></label>
            <textarea name="comment"></textarea>
        </div>
        <div class="form-row">
            <label><?php echo I18N::t('ui.checkout.payment'); ?></label>
            <select name="payment">
                <option value="twint">TWINT</option>
                <option value="card">Credit Card</option>
                <option value="paypal">PayPal</option>
            </select>
        </div>
        <button class="button" type="submit"><?php echo I18N::t('ui.checkout.submit'); ?></button>
    </form>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>
