<?php
/**
 * Admin - Accessories Management
 */

require_once __DIR__ . '/../init.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

/**
 * Load accessory descriptions from i18n JSON files
 */
function loadAccessoryDescriptions(string $productId): array {
    $langs = ['en', 'de', 'fr', 'it', 'ru', 'ukr'];
    $result = [];
    foreach ($langs as $lang) {
        $path = __DIR__ . '/../data/i18n/ui_' . $lang . '.json';
        if (!file_exists($path)) continue;
        
        $content = file_get_contents($path);
        if ($content === false) continue;
        
        $data = json_decode($content, true);
        if (!is_array($data)) continue;
        
        if (isset($data['product'][$productId]['desc'])) {
            $result[$lang] = $data['product'][$productId]['desc'];
        }
    }
    return $result;
}

/**
 * Save accessory descriptions to i18n JSON files
 * Note: If a product name is not set in the i18n file, it will be initialized with the product ID
 */
function saveAccessoryDescriptions(string $productId, array $descriptions): void {
    $langs = ['en', 'de', 'fr', 'it', 'ru', 'ukr'];
    foreach ($langs as $lang) {
        $desc = $descriptions[$lang] ?? '';
        if ($desc === '') {
            // Skip empty descriptions to avoid overwriting existing ones
            continue;
        }
        $path = __DIR__ . '/../data/i18n/ui_' . $lang . '.json';
        if (!file_exists($path)) {
            continue;
        }
        
        $content = file_get_contents($path);
        if ($content === false) {
            error_log("Failed to read i18n file: $path");
            continue;
        }
        
        $data = json_decode($content, true);
        if (!is_array($data)) {
            error_log("Failed to decode JSON from i18n file: $path");
            $data = [];
        }
        
        if (!isset($data['product'])) {
            $data['product'] = [];
        }
        if (!isset($data['product'][$productId])) {
            $data['product'][$productId] = [];
        }
        $data['product'][$productId]['desc'] = $desc;
        
        // Initialize name field if not present (for consistency in i18n files)
        if (empty($data['product'][$productId]['name'])) {
            $data['product'][$productId]['name'] = $productId;
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $result = file_put_contents($path, $json);
        
        if ($result === false) {
            error_log("Failed to write i18n file: $path");
        }
    }
}

$accessories = loadJSON('accessories.json');
$fragrances = loadJSON('fragrances.json');

// Get all fragrances except salted_caramel for allowed_fragrances selector
$availableFragrances = array_filter(array_keys($fragrances), function($code) {
    return $code !== 'salted_caramel';
});

$success = '';
$error = '';
$editingId = $_GET['edit'] ?? '';
$editingItem = $editingId && isset($accessories[$editingId]) ? $accessories[$editingId] : null;
$currentDescriptions = [];
if ($editingId) {
    $currentDescriptions = loadAccessoryDescriptions($editingId);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_accessory') {
        $id = trim($_POST['id'] ?? '');
        $name_key = trim($_POST['name_key'] ?? '');
        $desc_key = trim($_POST['desc_key'] ?? '');
        $priceCHF = floatval($_POST['priceCHF'] ?? 0);
        $active = isset($_POST['active']) ? true : false;
        
        // Validate ID (only lowercase letters, numbers, underscores)
        if (!preg_match('/^[a-z0-9_]+$/', $id)) {
            $error = 'ID must contain only lowercase letters, numbers, and underscores.';
        } else {
            // Process images - can be textarea with one per line or multiple inputs
            $images = [];
            if (!empty($_POST['images'])) {
                if (is_array($_POST['images'])) {
                    // Multiple text inputs
                    foreach ($_POST['images'] as $img) {
                        $img = trim($img);
                        if ($img !== '') {
                            $images[] = $img;
                        }
                    }
                } else {
                    // Textarea - split by newlines
                    $lines = explode("\n", $_POST['images']);
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if ($line !== '') {
                            $images[] = $line;
                        }
                    }
                }
            }
            
            // Process allowed_fragrances
            $allowed_fragrances = $_POST['allowed_fragrances'] ?? [];
            if (!is_array($allowed_fragrances)) {
                $allowed_fragrances = [];
            }
            
            // Validate at least one image
            if (empty($images)) {
                $error = 'At least one image is required.';
            } else {
                // Create or update accessory
                $accessories[$id] = [
                    'id' => $id,
                    'name_key' => $name_key,
                    'desc_key' => $desc_key,
                    'images' => $images,
                    'priceCHF' => $priceCHF,
                    'active' => $active,
                    'allowed_fragrances' => $allowed_fragrances
                ];
                
                if (saveJSON('accessories.json', $accessories)) {
                    // Save descriptions to i18n files
                    $descriptions = [
                        'en'  => trim($_POST['description_en'] ?? ''),
                        'de'  => trim($_POST['description_de'] ?? ''),
                        'fr'  => trim($_POST['description_fr'] ?? ''),
                        'it'  => trim($_POST['description_it'] ?? ''),
                        'ru'  => trim($_POST['description_ru'] ?? ''),
                        'ukr' => trim($_POST['description_ukr'] ?? ''),
                    ];
                    saveAccessoryDescriptions($id, $descriptions);
                    
                    $success = 'Accessory saved successfully!';
                    $editingId = '';
                    $editingItem = null;
                    $currentDescriptions = [];
                    // Reload data
                    $accessories = loadJSON('accessories.json');
                } else {
                    $error = 'Failed to save accessories.json file.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accessories - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Manrope:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.95rem;
        }
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        .form-group select[multiple] {
            min-height: 200px;
        }
        .form-group .help-text {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.25rem;
        }
        .image-inputs .image-input-row {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .image-inputs .image-input-row input {
            flex: 1;
        }
        .btn-add-image {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: var(--color-sand);
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .alert { padding: 1rem; margin-bottom: 1rem; border-radius: 4px; }
        .alert--success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert--error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-sidebar__logo">NicheHome Admin</div>
            <nav class="admin-sidebar__nav">
                <a href="index.php" class="admin-sidebar__link">Dashboard</a>
                <a href="products.php" class="admin-sidebar__link">Products</a>
                <a href="accessories.php" class="admin-sidebar__link active">Accessories</a>
                <a href="fragrances.php" class="admin-sidebar__link">Fragrances</a>
                <a href="categories.php" class="admin-sidebar__link">Categories</a>
                <a href="stock.php" class="admin-sidebar__link">Stock</a>
                <a href="orders.php" class="admin-sidebar__link">Orders</a>
                <a href="logout.php" class="admin-sidebar__link">Logout</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>Accessories Management</h1>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert--success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert--error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <!-- Accessories Table -->
            <div class="admin-card">
                <h2>All Accessories</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Images</th>
                            <th>Price</th>
                            <th>Active</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($accessories)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 2rem; color: #999;">
                                    No accessories found. Create one using the form below.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($accessories as $id => $item): ?>
                                <?php 
                                $itemName = I18N::t($item['name_key'] ?? '', $id);
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($id); ?></td>
                                    <td><?php echo htmlspecialchars($itemName); ?></td>
                                    <td>
                                        <?php if (!empty($item['images'])): ?>
                                            <?php foreach ($item['images'] as $img): ?>
                                                <span style="display: inline-block; background: var(--color-sand); padding: 0.25rem 0.5rem; border-radius: 4px; margin: 0.1rem; font-size: 0.85rem;">
                                                    <?php echo htmlspecialchars($img); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>CHF <?php echo number_format($item['priceCHF'] ?? 0, 2); ?></td>
                                    <td><?php echo ($item['active'] ?? false) ? 'Yes' : 'No'; ?></td>
                                    <td>
                                        <a href="?edit=<?php echo urlencode($id); ?>" class="btn btn--text">Edit</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Add/Edit Form -->
            <div class="admin-card" style="margin-top: 2rem;">
                <h2><?php echo $editingItem ? 'Edit Accessory' : 'Add New Accessory'; ?></h2>
                <form method="post" action="">
                    <input type="hidden" name="action" value="save_accessory">
                    
                    <div class="form-group">
                        <label for="id">ID (slug) *</label>
                        <input type="text" 
                               id="id" 
                               name="id" 
                               required 
                               pattern="[a-z0-9_]+"
                               value="<?php echo $editingItem ? htmlspecialchars($editingItem['id']) : ''; ?>"
                               <?php echo $editingItem ? 'readonly' : ''; ?>>
                        <div class="help-text">Only lowercase letters, numbers, and underscores. Cannot be changed after creation.</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="name_key">Name Key *</label>
                        <input type="text" 
                               id="name_key" 
                               name="name_key" 
                               required 
                               value="<?php echo $editingItem ? htmlspecialchars($editingItem['name_key']) : ''; ?>">
                        <div class="help-text">Translation key, e.g. product.aroma_sashe.name</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="desc_key">Description Key *</label>
                        <input type="text" 
                               id="desc_key" 
                               name="desc_key" 
                               required 
                               value="<?php echo $editingItem ? htmlspecialchars($editingItem['desc_key']) : ''; ?>">
                        <div class="help-text">Translation key, e.g. product.aroma_sashe.desc</div>
                    </div>
                    
                    <!-- Description Fields for all languages -->
                    <div class="form-group">
                        <label for="description_en">Description (EN)</label>
                        <textarea id="description_en" 
                                  name="description_en" 
                                  rows="4"><?php echo htmlspecialchars($currentDescriptions['en'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="description_de">Description (DE)</label>
                        <textarea id="description_de" 
                                  name="description_de" 
                                  rows="4"><?php echo htmlspecialchars($currentDescriptions['de'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="description_fr">Description (FR)</label>
                        <textarea id="description_fr" 
                                  name="description_fr" 
                                  rows="4"><?php echo htmlspecialchars($currentDescriptions['fr'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="description_it">Description (IT)</label>
                        <textarea id="description_it" 
                                  name="description_it" 
                                  rows="4"><?php echo htmlspecialchars($currentDescriptions['it'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="description_ru">Description (RU)</label>
                        <textarea id="description_ru" 
                                  name="description_ru" 
                                  rows="4"><?php echo htmlspecialchars($currentDescriptions['ru'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="description_ukr">Description (UKR)</label>
                        <textarea id="description_ukr" 
                                  name="description_ukr" 
                                  rows="4"><?php echo htmlspecialchars($currentDescriptions['ukr'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="images">Images *</label>
                        <textarea id="images" 
                                  name="images" 
                                  required><?php 
                            if ($editingItem && !empty($editingItem['images'])) {
                                echo htmlspecialchars(implode("\n", $editingItem['images']));
                            }
                        ?></textarea>
                        <div class="help-text">Enter one image filename per line (e.g., 2-Sashe.jpg). First image will be the main image.</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="priceCHF">Price (CHF) *</label>
                        <input type="number" 
                               id="priceCHF" 
                               name="priceCHF" 
                               step="0.01" 
                               min="0" 
                               required 
                               value="<?php echo $editingItem ? htmlspecialchars($editingItem['priceCHF']) : '0.00'; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="allowed_fragrances">Allowed Fragrances *</label>
                        <select id="allowed_fragrances" 
                                name="allowed_fragrances[]" 
                                multiple 
                                required>
                            <?php foreach ($availableFragrances as $fragCode): ?>
                                <?php
                                $fragName = I18N::t('fragrance.' . $fragCode . '.name', ucfirst(str_replace('_', ' ', $fragCode)));
                                $isSelected = $editingItem && in_array($fragCode, $editingItem['allowed_fragrances'] ?? []);
                                ?>
                                <option value="<?php echo htmlspecialchars($fragCode); ?>" <?php echo $isSelected ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($fragName); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="help-text">Hold Ctrl/Cmd to select multiple. salted_caramel is excluded.</div>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" 
                                   name="active" 
                                   <?php echo ($editingItem && ($editingItem['active'] ?? false)) || !$editingItem ? 'checked' : ''; ?>>
                            Active (visible on website)
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn--gold">Save Accessory</button>
                        <?php if ($editingItem): ?>
                            <a href="accessories.php" class="btn" style="margin-left: 1rem;">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
