<?php
/**
 * Add to Cart AJAX Endpoint
 * Syncs JavaScript cart to PHP session
 */

require_once __DIR__ . '/init.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$action = $input['action'] ?? 'add';

header('Content-Type: application/json');

switch ($action) {
    case 'add':
        if (!isset($input['item']) || !is_array($input['item'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing item data']);
            exit;
        }
        
        $item = $input['item'];
        
        // Validate required fields
        if (empty($item['sku']) || empty($item['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }
        
        // Sanitize the item
        $cartItem = [
            'sku' => sanitize($item['sku']),
            'productId' => sanitize($item['productId'] ?? ''),
            'name' => sanitize($item['name']),
            'category' => sanitize($item['category'] ?? ''),
            'volume' => sanitize($item['volume'] ?? 'standard'),
            'fragrance' => sanitize($item['fragrance'] ?? 'none'),
            'price' => floatval($item['price'] ?? 0),
            'quantity' => intval($item['quantity'] ?? 1)
        ];
        
        addToCart($cartItem);
        
        echo json_encode([
            'success' => true,
            'cartCount' => getCartCount(),
            'cartTotal' => getCartTotal()
        ]);
        break;
        
    case 'update':
        if (empty($input['sku']) || !isset($input['quantity'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing sku or quantity']);
            exit;
        }
        
        $sku = sanitize($input['sku']);
        $quantity = intval($input['quantity']);
        
        updateCartQuantity($sku, $quantity);
        
        echo json_encode([
            'success' => true,
            'cartCount' => getCartCount(),
            'cartTotal' => getCartTotal()
        ]);
        break;
        
    case 'remove':
        if (empty($input['sku'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing sku']);
            exit;
        }
        
        $sku = sanitize($input['sku']);
        removeFromCart($sku);
        
        echo json_encode([
            'success' => true,
            'cartCount' => getCartCount(),
            'cartTotal' => getCartTotal()
        ]);
        break;
        
    case 'sync':
        // Sync entire cart from JavaScript
        if (!isset($input['cart']) || !is_array($input['cart'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing cart data']);
            exit;
        }
        
        // Clear existing cart and rebuild from JS data
        clearCart();
        
        foreach ($input['cart'] as $item) {
            if (!empty($item['sku']) && !empty($item['name'])) {
                $cartItem = [
                    'sku' => sanitize($item['sku']),
                    'productId' => sanitize($item['productId'] ?? ''),
                    'name' => sanitize($item['name']),
                    'category' => sanitize($item['category'] ?? ''),
                    'volume' => sanitize($item['volume'] ?? 'standard'),
                    'fragrance' => sanitize($item['fragrance'] ?? 'none'),
                    'price' => floatval($item['price'] ?? 0),
                    'quantity' => intval($item['quantity'] ?? 1)
                ];
                addToCart($cartItem);
            }
        }
        
        echo json_encode([
            'success' => true,
            'cartCount' => getCartCount(),
            'cartTotal' => getCartTotal()
        ]);
        break;
        
    case 'get':
        // Return current session cart
        echo json_encode([
            'success' => true,
            'cart' => getCart(),
            'cartCount' => getCartCount(),
            'cartTotal' => getCartTotal()
        ]);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown action']);
}
