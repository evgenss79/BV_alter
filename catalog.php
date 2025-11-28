<?php
// catalog.php

// Sample array of product categories
$categories = [
    [
        'id' => 1,
        'name' => 'Electronics',
        'description' => 'Devices and gadgets'
    ],
    [
        'id' => 2,
        'name' => 'Clothing',
        'description' => 'Apparel and accessories'
    ],
    [
        'id' => 3,
        'name' => 'Home & Garden',
        'description' => 'Furniture and outdoor'
    ],
    [
        'id' => 4,
        'name' => 'Health & Beauty',
        'description' => 'Wellness and personal care'
    ],
];

// Function to display categories
function displayCategories($categories) {
    foreach ($categories as $category) {
        echo "<h2>{$category['name']}</h2>";
        echo "<p>{$category['description']}</p>";
    }
}

displayCategories($categories);
?>