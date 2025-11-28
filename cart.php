<?php
/**
 * Cart - Shopping cart page
 */

require_once __DIR__ . '/init.php';
include __DIR__ . '/includes/header.php';

$currentLang = I18N::getLanguage();
$cart = getCart();
$cartTotal = getCartTotal();
$freeShippingThreshold = $CONFIG['free_shipping_threshold'] ?? 80;
$amountToFreeShipping = max(0, $freeShippingThreshold - $cartTotal);
?>

<section class="cart-section">
    <div class="container">
        <h1 class="text-center mb-4"><?php echo I18N::t('page.cart.title', 'Shopping Cart'); ?></h1>
        
        <?php if (empty($cart)): ?>
            <div class="cart-empty">
                <p><?php echo I18N::t('page.cart.empty', 'Your cart is empty'); ?></p>
                <a href="catalog.php?lang=<?php echo $currentLang; ?>" class="btn btn--gold">
                    <?php echo I18N::t('common.continueShopping', 'Continue shopping'); ?>
                </a>
            </div>
        <?php else: ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th><?php echo I18N::t('page.cart.item', 'Item'); ?></th>
                        <th><?php echo I18N::t('page.cart.price', 'Price'); ?></th>
                        <th><?php echo I18N::t('page.cart.quantity', 'Qty'); ?></th>
                        <th><?php echo I18N::t('page.cart.total', 'Total'); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart as $item): ?>
                        <?php
                        $itemTotal = ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
                        $fragranceName = '';
                        if (!empty($item['fragrance']) && $item['fragrance'] !== 'none') {
                            $fragranceName = I18N::t('fragrance.' . $item['fragrance'] . '.name', ucfirst(str_replace('_', ' ', $item['fragrance'])));
                        }
                        ?>
                        <tr>
                            <td>
                                <div class="cart-item__name"><?php echo htmlspecialchars($item['name'] ?? 'Product'); ?></div>
                                <div class="cart-item__details">
                                    <?php if (!empty($item['volume']) && $item['volume'] !== 'standard'): ?>
                                        <?php echo htmlspecialchars($item['volume']); ?>
                                    <?php endif; ?>
                                    <?php if ($fragranceName): ?>
                                        • <?php echo htmlspecialchars($fragranceName); ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>CHF <?php echo number_format($item['price'] ?? 0, 2); ?></td>
                            <td>
                                <input type="number" 
                                       class="cart-item__quantity" 
                                       value="<?php echo (int)($item['quantity'] ?? 1); ?>" 
                                       min="1" 
                                       onchange="updateCartQuantity('<?php echo htmlspecialchars($item['sku']); ?>', this.value); window.location.reload();">
                            </td>
                            <td>CHF <?php echo number_format($itemTotal, 2); ?></td>
                            <td>
                                <button class="cart-item__remove" onclick="removeFromCart('<?php echo htmlspecialchars($item['sku']); ?>')">
                                    ✕ <?php echo I18N::t('common.remove', 'Remove'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="cart-summary">
                <div class="cart-summary__row">
                    <span><?php echo I18N::t('common.subtotal', 'Subtotal'); ?></span>
                    <span>CHF <?php echo number_format($cartTotal, 2); ?></span>
                </div>
                <div class="cart-summary__row">
                    <span><?php echo I18N::t('common.shipping', 'Shipping'); ?></span>
                    <span>
                        <?php if ($amountToFreeShipping <= 0): ?>
                            <?php echo I18N::t('common.freeShipping', 'Free'); ?>
                        <?php else: ?>
                            <?php echo str_replace('{amount}', number_format($amountToFreeShipping, 2), I18N::t('common.amountToFreeShipping', 'Add CHF {amount} for free shipping')); ?>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="cart-summary__row cart-summary__row--total">
                    <span><?php echo I18N::t('common.total', 'Total'); ?></span>
                    <span>CHF <?php echo number_format($cartTotal, 2); ?></span>
                </div>
                
                <div style="margin-top: 1.5rem;">
                    <a href="checkout.php?lang=<?php echo $currentLang; ?>" class="btn btn--gold" style="width: 100%;">
                        <?php echo I18N::t('common.proceedToCheckout', 'Proceed to checkout'); ?>
                    </a>
                </div>
                
                <div style="margin-top: 1rem; text-align: center;">
                    <a href="catalog.php?lang=<?php echo $currentLang; ?>" class="btn btn--text">
                        <?php echo I18N::t('common.continueShopping', 'Continue shopping'); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
