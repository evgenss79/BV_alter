<?php
/**
 * Checkout - Checkout form page
 */

require_once __DIR__ . '/init.php';

$currentLang = I18N::getLanguage();
$cart = getCart();
$cartTotal = getCartTotal();

// Redirect if cart is empty
if (empty($cart)) {
    header('Location: cart.php?lang=' . $currentLang);
    exit;
}

$success = false;
$orderId = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $requiredFields = ['first_name', 'last_name', 'email', 'phone', 'street', 'house', 'zip', 'city', 'country', 'payment'];
    
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
        }
    }
    
    // Validate email
    if (!empty($_POST['email']) && !isValidEmail($_POST['email'])) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    // Check stock for all items
    $stock = loadJSON('stock.json');
    foreach ($cart as $item) {
        $sku = $item['sku'] ?? '';
        $qty = $item['quantity'] ?? 1;
        
        if (isset($stock[$sku]) && $stock[$sku]['quantity'] < $qty) {
            $errors[] = 'Insufficient stock for ' . ($item['name'] ?? $sku);
        }
    }
    
    if (empty($errors)) {
        // Create order
        $orderId = generateOrderId();
        
        $order = [
            'id' => $orderId,
            'date' => date('Y-m-d H:i:s'),
            'status' => $_POST['payment'] === 'twint' ? 'pending_twint' : 'pending',
            'language' => $currentLang,
            'customer' => [
                'first_name' => sanitize($_POST['first_name']),
                'last_name' => sanitize($_POST['last_name']),
                'email' => sanitize($_POST['email']),
                'phone' => sanitize($_POST['phone'])
            ],
            'shipping' => [
                'street' => sanitize($_POST['street']),
                'house' => sanitize($_POST['house']),
                'zip' => sanitize($_POST['zip']),
                'city' => sanitize($_POST['city']),
                'country' => sanitize($_POST['country'])
            ],
            'billing' => [],
            'comment' => sanitize($_POST['comment'] ?? ''),
            'payment_method' => sanitize($_POST['payment']),
            'items' => $cart,
            'subtotal' => $cartTotal,
            'shipping_cost' => 0,
            'total' => $cartTotal
        ];
        
        // Handle billing address
        if (empty($_POST['same_as_shipping'])) {
            $order['billing'] = [
                'street' => sanitize($_POST['billing_street'] ?? ''),
                'house' => sanitize($_POST['billing_house'] ?? ''),
                'zip' => sanitize($_POST['billing_zip'] ?? ''),
                'city' => sanitize($_POST['billing_city'] ?? ''),
                'country' => sanitize($_POST['billing_country'] ?? '')
            ];
        } else {
            $order['billing'] = $order['shipping'];
        }
        
        // Save order
        $orders = loadJSON('orders.json');
        if (!is_array($orders)) {
            $orders = [];
        }
        $orders[] = $order;
        
        if (saveJSON('orders.json', $orders)) {
            // Decrease stock
            foreach ($cart as $item) {
                $sku = $item['sku'] ?? '';
                $qty = $item['quantity'] ?? 1;
                decreaseStock($sku, $qty);
            }
            
            // Clear cart
            clearCart();
            $success = true;
        } else {
            $errors[] = 'Could not save your order. Please try again.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="checkout-section">
    <div class="container">
        <?php if ($success): ?>
            <div class="text-center" style="max-width: 600px; margin: 0 auto; padding: 4rem 2rem;">
                <h1><?php echo I18N::t('page.checkout.orderSuccess', 'Thank you for your order!'); ?></h1>
                <p style="font-size: 1.2rem; margin: 2rem 0;">
                    <?php echo str_replace('{orderId}', $orderId, I18N::t('page.checkout.orderConfirmation', 'Order #{orderId} has been placed successfully. You will receive a confirmation email shortly.')); ?>
                </p>
                <a href="catalog.php?lang=<?php echo $currentLang; ?>" class="btn btn--gold">
                    <?php echo I18N::t('common.continueShopping', 'Continue shopping'); ?>
                </a>
            </div>
        <?php else: ?>
            <h1 class="text-center mb-4"><?php echo I18N::t('page.checkout.title', 'Checkout'); ?></h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert--error">
                    <ul style="list-style: disc; padding-left: 1.5rem;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="checkout-grid">
                <form method="post" action="" class="checkout-form" data-checkout-form>
                    <!-- Shipping Address -->
                    <div class="form-section">
                        <h3 class="form-section__title"><?php echo I18N::t('page.checkout.shipping', 'Shipping Address'); ?></h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><?php echo I18N::t('page.checkout.firstName', 'First name'); ?> *</label>
                                <input type="text" name="first_name" required value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label><?php echo I18N::t('page.checkout.lastName', 'Last name'); ?> *</label>
                                <input type="text" name="last_name" required value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><?php echo I18N::t('page.checkout.email', 'Email'); ?> *</label>
                                <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label><?php echo I18N::t('page.checkout.phone', 'Phone'); ?> *</label>
                                <input type="tel" name="phone" required value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><?php echo I18N::t('page.checkout.street', 'Street'); ?> *</label>
                                <input type="text" name="street" required value="<?php echo htmlspecialchars($_POST['street'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label><?php echo I18N::t('page.checkout.houseNumber', 'House number'); ?> *</label>
                                <input type="text" name="house" required value="<?php echo htmlspecialchars($_POST['house'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><?php echo I18N::t('page.checkout.zip', 'ZIP code'); ?> *</label>
                                <input type="text" name="zip" required value="<?php echo htmlspecialchars($_POST['zip'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label><?php echo I18N::t('page.checkout.city', 'City'); ?> *</label>
                                <input type="text" name="city" required value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><?php echo I18N::t('page.checkout.country', 'Country'); ?> *</label>
                            <select name="country" required>
                                <option value="Switzerland" <?php echo ($_POST['country'] ?? '') === 'Switzerland' ? 'selected' : ''; ?>>Switzerland</option>
                                <option value="Germany" <?php echo ($_POST['country'] ?? '') === 'Germany' ? 'selected' : ''; ?>>Germany</option>
                                <option value="Austria" <?php echo ($_POST['country'] ?? '') === 'Austria' ? 'selected' : ''; ?>>Austria</option>
                                <option value="France" <?php echo ($_POST['country'] ?? '') === 'France' ? 'selected' : ''; ?>>France</option>
                                <option value="Italy" <?php echo ($_POST['country'] ?? '') === 'Italy' ? 'selected' : ''; ?>>Italy</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Billing Address -->
                    <div class="form-section">
                        <div class="form-checkbox">
                            <input type="checkbox" name="same_as_shipping" id="same_as_shipping" checked data-same-as-shipping>
                            <label for="same_as_shipping"><?php echo I18N::t('page.checkout.sameAsShipping', 'Billing address same as shipping'); ?></label>
                        </div>
                        
                        <div data-billing-section style="display: none;">
                            <h3 class="form-section__title"><?php echo I18N::t('page.checkout.billing', 'Billing Address'); ?></h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label><?php echo I18N::t('page.checkout.street', 'Street'); ?></label>
                                    <input type="text" name="billing_street">
                                </div>
                                <div class="form-group">
                                    <label><?php echo I18N::t('page.checkout.houseNumber', 'House number'); ?></label>
                                    <input type="text" name="billing_house">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label><?php echo I18N::t('page.checkout.zip', 'ZIP code'); ?></label>
                                    <input type="text" name="billing_zip">
                                </div>
                                <div class="form-group">
                                    <label><?php echo I18N::t('page.checkout.city', 'City'); ?></label>
                                    <input type="text" name="billing_city">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label><?php echo I18N::t('page.checkout.country', 'Country'); ?></label>
                                <select name="billing_country">
                                    <option value="Switzerland">Switzerland</option>
                                    <option value="Germany">Germany</option>
                                    <option value="Austria">Austria</option>
                                    <option value="France">France</option>
                                    <option value="Italy">Italy</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Comment -->
                    <div class="form-section">
                        <div class="form-group">
                            <label><?php echo I18N::t('page.checkout.comment', 'Order comment (optional)'); ?></label>
                            <textarea name="comment" rows="3"><?php echo htmlspecialchars($_POST['comment'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="form-section">
                        <h3 class="form-section__title"><?php echo I18N::t('page.checkout.payment', 'Payment Method'); ?></h3>
                        
                        <div class="payment-methods">
                            <label class="payment-option">
                                <input type="radio" name="payment" value="twint" required checked>
                                <span><?php echo I18N::t('page.checkout.paymentTwint', 'TWINT'); ?></span>
                            </label>
                            
                            <label class="payment-option payment-option--disabled">
                                <input type="radio" name="payment" value="card" disabled>
                                <span><?php echo I18N::t('page.checkout.paymentCard', 'Credit Card (coming soon)'); ?></span>
                            </label>
                            
                            <label class="payment-option payment-option--disabled">
                                <input type="radio" name="payment" value="paypal" disabled>
                                <span><?php echo I18N::t('page.checkout.paymentPaypal', 'PayPal (coming soon)'); ?></span>
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn--gold" style="width: 100%;">
                        <?php echo I18N::t('page.checkout.placeOrder', 'Place Order'); ?>
                    </button>
                </form>
                
                <!-- Order Summary -->
                <div class="order-summary">
                    <h3 class="order-summary__title"><?php echo I18N::t('common.subtotal', 'Order Summary'); ?></h3>
                    
                    <?php foreach ($cart as $item): ?>
                        <?php
                        $fragranceName = '';
                        if (!empty($item['fragrance']) && $item['fragrance'] !== 'none') {
                            $fragranceName = I18N::t('fragrance.' . $item['fragrance'] . '.name', ucfirst(str_replace('_', ' ', $item['fragrance'])));
                        }
                        ?>
                        <div class="order-summary__item">
                            <div>
                                <div class="order-summary__item-name"><?php echo htmlspecialchars($item['name'] ?? 'Product'); ?> × <?php echo (int)($item['quantity'] ?? 1); ?></div>
                                <div class="order-summary__item-details">
                                    <?php if (!empty($item['volume']) && $item['volume'] !== 'standard'): ?>
                                        <?php echo htmlspecialchars($item['volume']); ?>
                                    <?php endif; ?>
                                    <?php if ($fragranceName): ?>
                                        • <?php echo htmlspecialchars($fragranceName); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span>CHF <?php echo number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="order-summary__item" style="font-weight: 700; border-top: 2px solid var(--color-charcoal); margin-top: 1rem; padding-top: 1rem;">
                        <span><?php echo I18N::t('common.total', 'Total'); ?></span>
                        <span>CHF <?php echo number_format($cartTotal, 2); ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
