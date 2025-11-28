<?php
/**
 * Admin - Stock Management
 */

require_once __DIR__ . '/../init.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$stock = loadJSON('stock.json');
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sku'])) {
    $sku = $_POST['sku'];
    
    if (isset($stock[$sku])) {
        $stock[$sku]['quantity'] = max(0, intval($_POST['quantity'] ?? 0));
        $stock[$sku]['lowStockThreshold'] = max(0, intval($_POST['threshold'] ?? 3));
        
        if (saveJSON('stock.json', $stock)) {
            $success = 'Stock updated successfully.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock - Admin</title>
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
                <a href="stock.php" class="admin-sidebar__link active">Stock</a>
                <a href="orders.php" class="admin-sidebar__link">Orders</a>
                <a href="logout.php" class="admin-sidebar__link">Logout</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>Stock Management</h1>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert--success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <div class="admin-card">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Product</th>
                            <th>Volume</th>
                            <th>Fragrance</th>
                            <th>Quantity</th>
                            <th>Low Stock Threshold</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stock as $sku => $item): ?>
                            <?php
                            $isLowStock = ($item['quantity'] ?? 0) <= ($item['lowStockThreshold'] ?? 3);
                            $isOutOfStock = ($item['quantity'] ?? 0) <= 0;
                            $fragName = I18N::t('fragrance.' . ($item['fragrance'] ?? '') . '.name', ucfirst(str_replace('_', ' ', $item['fragrance'] ?? '')));
                            ?>
                            <tr style="<?php echo $isLowStock ? 'background: rgba(199, 74, 74, 0.1);' : ''; ?>">
                                <td><?php echo htmlspecialchars($sku); ?></td>
                                <td><?php echo htmlspecialchars($item['productId'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($item['volume'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($fragName); ?></td>
                                <td>
                                    <form method="post" action="" style="display: flex; gap: 0.5rem; align-items: center;">
                                        <input type="hidden" name="sku" value="<?php echo htmlspecialchars($sku); ?>">
                                        <input type="number" name="quantity" value="<?php echo htmlspecialchars($item['quantity'] ?? 0); ?>" 
                                               style="width: 60px; padding: 0.25rem; text-align: center;" min="0">
                                </td>
                                <td>
                                        <input type="number" name="threshold" value="<?php echo htmlspecialchars($item['lowStockThreshold'] ?? 3); ?>" 
                                               style="width: 60px; padding: 0.25rem; text-align: center;" min="0">
                                </td>
                                <td>
                                    <?php if ($isOutOfStock): ?>
                                        <span style="color: var(--color-error); font-weight: 600;">Out of Stock</span>
                                    <?php elseif ($isLowStock): ?>
                                        <span style="color: #d4a017; font-weight: 600;">Low Stock</span>
                                    <?php else: ?>
                                        <span style="color: var(--color-success);">In Stock</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                        <button type="submit" class="btn btn--text">Save</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
