<?php include __DIR__ . '/templates/header.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orders = DataStore::readJson(__DIR__ . '/data/orders.json');
    $orders[] = [
        'id' => uniqid('order_'),
        'timestamp' => time(),
        'language' => $currentLang,
        'customer' => [
            'firstName' => $_POST['first_name'] ?? '',
            'lastName' => $_POST['last_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? ''
        ],
        'shipping' => [
            'street' => $_POST['street'] ?? '',
            'house' => $_POST['house'] ?? '',
            'apartment' => $_POST['apartment'] ?? '',
            'postal' => $_POST['postal_code'] ?? '',
            'city' => $_POST['city'] ?? '',
            'country' => $_POST['country'] ?? ''
        ],
        'billing' => isset($_POST['billing_enabled']) ? [
            'street' => $_POST['billing_street'] ?? '',
            'house' => $_POST['billing_house'] ?? '',
            'apartment' => $_POST['billing_apartment'] ?? '',
            'postal' => $_POST['billing_postal_code'] ?? '',
            'city' => $_POST['billing_city'] ?? '',
            'country' => $_POST['billing_country'] ?? ''
        ] : null,
        'comment' => $_POST['comment'] ?? '',
        'items' => Cart::get(),
        'totalCHF' => Cart::total(),
        'payment' => $_POST['payment'] ?? 'twint'
    ];
    DataStore::writeJson(__DIR__ . '/data/orders.json', $orders);
    $_SESSION['cart'] = [];
    echo '<div class="section"><div class="promo-card">' . I18N::t('ui.checkout.thanks') . '</div></div>';
}
?>
<section class="section checkout">
    <div class="section-heading">
        <h1><?php echo I18N::t('ui.checkout.title'); ?></h1>
    </div>
    <form method="post" class="checkout__grid">
        <div class="card checkout__panel">
            <div class="form-row"><label><?php echo I18N::t('ui.checkout.first_name'); ?></label><input name="first_name" required></div>
            <div class="form-row"><label><?php echo I18N::t('ui.checkout.last_name'); ?></label><input name="last_name" required></div>
            <div class="form-row"><label><?php echo I18N::t('ui.checkout.email'); ?></label><input type="email" name="email" required></div>
            <div class="form-row"><label><?php echo I18N::t('ui.checkout.phone'); ?></label><input name="phone"></div>
            <h3><?php echo I18N::t('ui.checkout.address'); ?></h3>
            <div class="form-row"><label><?php echo I18N::t('ui.checkout.street'); ?></label><input name="street" required></div>
            <div class="form-row dual">
                <div><label><?php echo I18N::t('ui.checkout.house'); ?></label><input name="house"></div>
                <div><label><?php echo I18N::t('ui.checkout.apartment'); ?></label><input name="apartment"></div>
            </div>
            <div class="form-row dual">
                <div><label><?php echo I18N::t('ui.checkout.postal_code'); ?></label><input name="postal_code" required></div>
                <div><label><?php echo I18N::t('ui.checkout.city'); ?></label><input name="city" required></div>
            </div>
            <div class="form-row"><label><?php echo I18N::t('ui.checkout.country'); ?></label><input name="country" value="Switzerland"></div>
            <div class="form-row checkbox-row">
                <label><input type="checkbox" name="billing_enabled" value="1"> <?php echo I18N::t('ui.checkout.billing_toggle'); ?></label>
            </div>
            <div class="billing-fields">
                <h3><?php echo I18N::t('ui.checkout.billing'); ?></h3>
                <div class="form-row"><label><?php echo I18N::t('ui.checkout.street'); ?></label><input name="billing_street"></div>
                <div class="form-row dual">
                    <div><label><?php echo I18N::t('ui.checkout.house'); ?></label><input name="billing_house"></div>
                    <div><label><?php echo I18N::t('ui.checkout.apartment'); ?></label><input name="billing_apartment"></div>
                </div>
                <div class="form-row dual">
                    <div><label><?php echo I18N::t('ui.checkout.postal_code'); ?></label><input name="billing_postal_code"></div>
                    <div><label><?php echo I18N::t('ui.checkout.city'); ?></label><input name="billing_city"></div>
                </div>
                <div class="form-row"><label><?php echo I18N::t('ui.checkout.country'); ?></label><input name="billing_country"></div>
            </div>
            <div class="form-row">
                <label><?php echo I18N::t('ui.checkout.comment'); ?></label>
                <textarea name="comment" rows="3"></textarea>
            </div>
        </div>
        <div class="card checkout__panel">
            <div class="form-row">
                <label><?php echo I18N::t('ui.checkout.payment'); ?></label>
                <select name="payment">
                    <option value="twint">TWINT</option>
                    <option value="card">Credit Card (soon)</option>
                    <option value="paypal">PayPal (soon)</option>
                </select>
            </div>
            <button class="btn btn--gold" type="submit"><?php echo I18N::t('ui.checkout.submit'); ?></button>
        </div>
    </form>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>
