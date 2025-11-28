<?php
/**
 * Admin - Orders Management
 */

require_once __DIR__ . '/../init.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$orders = loadJSON('orders.json');
if (!is_array($orders)) {
    $orders = [];
}
$success = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = $_POST['order_id'];
    $newStatus = $_POST['status'] ?? '';
    
    foreach ($orders as &$order) {
        if (($order['id'] ?? '') === $orderId) {
            $order['status'] = $newStatus;
            break;
        }
    }
    
    if (saveJSON('orders.json', $orders)) {
        $success = 'Order status updated successfully.';
    }
}

// Sort orders by date (newest first)
usort($orders, function($a, $b) {
    return strtotime($b['date'] ?? '0') - strtotime($a['date'] ?? '0');
});

$statuses = ['new', 'pending_twint', 'processing', 'shipped', 'completed', 'cancelled'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Manrope:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-sidebar__logo">NicheHome Admin</div>
            <nav class="admin-sidebar__nav">
                <a href="index.php" class="admin-sidebar__link">Dashboard</a>
                <a href="products.php" class="admin-sidebar__link">Products</a>
                <a href="fragrances.php" class="admin-sidebar__link">Fragrances</a>
                <a href="categories.php" class="admin-sidebar__link">Categories</a>
                <a href="stock.php" class="admin-sidebar__link">Stock</a>
                <a href="orders.php" class="admin-sidebar__link active">Orders</a>
                <a href="logout.php" class="admin-sidebar__link">Logout</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>Orders</h1>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert--success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (empty($orders)): ?>
                <div class="admin-card">
                    <p class="text-muted">No orders yet.</p>
                </div>
            <?php else: ?>
                <div class="admin-card">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['id'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($order['date'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars(($order['customer']['first_name'] ?? '') . ' ' . ($order['customer']['last_name'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars($order['customer']['email'] ?? ''); ?></td>
                                    <td>CHF <?php echo number_format($order['total'] ?? 0, 2); ?></td>
                                    <td>
                                        <form method="post" action="" style="display: flex; gap: 0.5rem;">
                                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id'] ?? ''); ?>">
                                            <select name="status" style="padding: 0.25rem;">
                                                <?php foreach ($statuses as $status): ?>
                                                    <option value="<?php echo $status; ?>" <?php echo ($order['status'] ?? '') === $status ? 'selected' : ''; ?>>
                                                        <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn--text">Save</button>
                                        </form>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn--text" onclick="showOrderDetails('<?php echo htmlspecialchars($order['id'] ?? ''); ?>')">View</button>
                                    </td>
                                </tr>
                                <tr id="details-<?php echo htmlspecialchars($order['id'] ?? ''); ?>" style="display: none;">
                                    <td colspan="7" style="background: var(--color-sand); padding: 1.5rem;">
                                        <h4>Order Details</h4>
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 1rem;">
                                            <div>
                                                <strong>Shipping Address:</strong><br>
                                                <?php echo htmlspecialchars(($order['shipping']['street'] ?? '') . ' ' . ($order['shipping']['house'] ?? '')); ?><br>
                                                <?php echo htmlspecialchars(($order['shipping']['zip'] ?? '') . ' ' . ($order['shipping']['city'] ?? '')); ?><br>
                                                <?php echo htmlspecialchars($order['shipping']['country'] ?? ''); ?>
                                            </div>
                                            <div>
                                                <strong>Payment:</strong> <?php echo htmlspecialchars($order['payment_method'] ?? ''); ?><br>
                                                <?php if (!empty($order['comment'])): ?>
                                                    <strong>Comment:</strong> <?php echo htmlspecialchars($order['comment']); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <h4 style="margin-top: 1.5rem;">Items:</h4>
                                        <ul style="margin-top: 0.5rem; padding-left: 1.5rem; list-style: disc;">
                                            <?php foreach ($order['items'] ?? [] as $item): ?>
                                                <li>
                                                    <?php echo htmlspecialchars($item['name'] ?? 'Product'); ?> 
                                                    (<?php echo htmlspecialchars($item['volume'] ?? ''); ?>, <?php echo htmlspecialchars($item['fragrance'] ?? ''); ?>)
                                                    × <?php echo (int)($item['quantity'] ?? 1); ?>
                                                    - CHF <?php echo number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 2); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <script>
        function showOrderDetails(orderId) {
            const row = document.getElementById('details-' + orderId);
            if (row) {
                row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
            }
        }
    </script>
</body>
</html>
