<?php

class Fragrances {
    private static $allCodes = [
        'cherry_blossom', 'bellini', 'eden', 'rosso',
        'salted_caramel', 'santal', 'lime_basil', 'bamboo',
        'tobacco_vanilla', 'salty_water', 'christmas_tree',
        'fleur', 'blanc', 'green_mango', 'carolina', 'sugar',
        'dubai', 'africa', 'dune', 'valencia', 'etna',
        'new_york', 'abu_dhabi', 'palermo'
    ];

    private static $baseExclusions = ['new_york', 'abu_dhabi', 'palermo'];

    private static $extraExclusions = [
        'scented_candles' => ['etna', 'valencia'],
        'textile_perfume' => ['salted_caramel', 'cherry_blossom', 'dubai', 'salty_water', 'rosso', 'christmas_tree'],
    ];

    private static $fragranceImages = [
        'cherry_blossom' => 'CHERRY_BLOSSOM.jpg',
        'bellini' => 'BELLINI.jpg',
        'eden' => 'EDEN.jpg',
        'rosso' => 'ROSSO.jpg',
        'salted_caramel' => 'SALTED_CARAMEL.jpg',
        'santal' => 'SANTAL.jpg',
        'lime_basil' => 'LIME_BASIL.jpg',
        'bamboo' => 'BAMBOO.jpg',
        'tobacco_vanilla' => 'TOBACCO_VANILLA.jpg',
        'salty_water' => 'SALTY_WATER.jpg',
        'christmas_tree' => 'CHRISTMAS_TREE.jpg',
        'fleur' => 'FLEUR.jpg',
        'blanc' => 'BLANC.jpg',
        'green_mango' => 'GREEN_MANGO.jpg',
        'carolina' => 'CAROLINA.jpg',
        'sugar' => 'SUGAR.jpg',
        'dubai' => 'DUBAI.jpg',
        'africa' => 'AFRICA.jpg',
        'dune' => 'DUNE.jpg',
        'valencia' => 'VALENCIA.jpg',
        'etna' => 'ETNA.jpg',
        'new_york' => 'NEW_YORK.jpg',
        'abu_dhabi' => 'ABU_DHABI.jpg',
        'palermo' => 'PALERMO.jpg',
    ];

    public static function allowedFragrances(string $category): array {
        $all = self::$allCodes;
        $exclude = self::$baseExclusions;

        if ($category === 'scented_candles') {
            $exclude = array_merge($exclude, ['etna', 'valencia']);
        }

        if ($category === 'textile_perfume') {
            $exclude = array_merge($exclude, [
                'salted_caramel', 'cherry_blossom', 'dubai',
                'salty_water', 'rosso', 'christmas_tree'
            ]);
        }

        return array_values(array_diff($all, $exclude));
    }

    public static function getImagePath(string $code): string {
        $fileName = self::$fragranceImages[$code] ?? strtoupper($code) . '.jpg';
        return '/assets/img/fragrances/' . $fileName;
    }
}
