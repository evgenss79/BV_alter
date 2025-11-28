<?php
require_once __DIR__ . '/DataStore.php';
require_once __DIR__ . '/I18N.php';

class Products {
    private static $products;
    private static $stock;

    private static $priceTable = [
        'aroma_diffusers' => [
            '125 ml' => 20.9,
            '250 ml' => 29.9,
            '500 ml' => 50.9,
        ],
        'scented_candles' => [
            '160 ml' => 19.9,
            '500 ml' => 41.9,
        ],
        'home_perfume' => [
            '10 ml' => 0,
            '50 ml' => 0,
        ],
        'car_perfume' => [
            'default' => 12.9,
        ],
        'textile_perfume' => [
            '100 ml' => 0,
            '250 ml' => 0,
        ],
    ];

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

    public static function getPrice(string $category, ?string $volume, float $fallback): float {
        $volume = $volume ?? 'default';
        if (isset(self::$priceTable[$category][$volume]) && self::$priceTable[$category][$volume] > 0) {
            return self::$priceTable[$category][$volume];
        }

        if ($category === 'car_perfume' && isset(self::$priceTable['car_perfume']['default'])) {
            return self::$priceTable['car_perfume']['default'];
        }

        return $fallback;
    }
}
