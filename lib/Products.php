<?php
require_once __DIR__ . '/DataStore.php';
require_once __DIR__ . '/I18N.php';

class Products {
    private static $products;
    private static $stock;

    public static function load(): void {
        if (self::$products === null) {
            self::$products = DataStore::readJson(__DIR__ . '/../data/products.json');
        }
        if (self::$stock === null) {
            self::$stock = DataStore::readJson(__DIR__ . '/../data/stock.json');
        }
    }

    public static function all(): array {
        self::load();
        return self::$products;
    }

    public static function byCategory(string $category): array {
        self::load();
        return array_values(array_filter(self::$products, fn($p) => $p['category'] === $category));
    }

    public static function get(string $id): ?array {
        self::load();
        foreach (self::$products as $product) {
            if ($product['id'] === $id) {
                return $product;
            }
        }
        return null;
    }

    public static function getProductBySku(string $sku): ?array {
        self::load();
        foreach (self::$products as $product) {
            foreach ($product['variants'] as $variant) {
                if ($variant['sku'] === $sku) {
                    return ['product' => $product, 'variant' => $variant];
                }
            }
        }
        return null;
    }

    public static function getStock(string $sku): int {
        self::load();
        return (int) (self::$stock[$sku]['quantity'] ?? 0);
    }
}
