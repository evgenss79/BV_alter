<?php

function price_helper($price) {
    return number_format($price, 2);
}

function fragrance($type) {
    $fragrances = [
        'citrus' => 'Fresh and zesty.
',
        'floral' => 'Sweet and blooming.
',
        'woody' => 'Earthy and warm.
',
        'spicy' => 'Rich and aromatic.
'
    ];
    return $fragrances[$type] ?? 'Unknown fragrance type.';
}

?>