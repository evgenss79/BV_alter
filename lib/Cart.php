<?php
require_once __DIR__ . '/DataStore.php';
require_once __DIR__ . '/Products.php';

class Cart {
    public static function get(): array {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        return $_SESSION['cart'];
    }

    public static function add(string $sku, int $qty = 1): void {
        $products = Products::getProductBySku($sku);
        if (!$products) {
            return;
        }
        $cart = self::get();
        if (isset($cart[$sku])) {
            $cart[$sku]['qty'] += $qty;
        } else {
            $cart[$sku] = [
                'sku' => $sku,
                'productId' => $products['product']['id'],
                'qty' => $qty,
                'priceCHF' => $products['variant']['priceCHF'],
                'type' => 'product'
            ];
        }
        $_SESSION['cart'] = $cart;
    }

    public static function addGiftSet(array $components, float $subtotal, float $discount, float $final): void {
        $cart = self::get();
        $lineId = 'gift_' . time();
        $cart[$lineId] = [
            'type' => 'gift_set',
            'components' => $components,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'priceCHF' => $final,
            'qty' => 1
        ];
        $_SESSION['cart'] = $cart;
    }

    public static function remove(string $key): void {
        $cart = self::get();
        if (isset($cart[$key])) {
            unset($cart[$key]);
            $_SESSION['cart'] = $cart;
        }
    }

    public static function total(): float {
        $sum = 0.0;
        foreach (self::get() as $item) {
            $sum += ($item['priceCHF'] ?? 0) * ($item['qty'] ?? 1);
        }
        return $sum;
    }
}
