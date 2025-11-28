<?php

// about.php

// This file provides information about the homepage.

// Define the homepage information
$homepageInfo = [
    'title' => 'Welcome to Our Homepage',
    'description' => 'This homepage provides an overview of our services and features. Enjoy your visit!'
];

// Function to display homepage information
function displayHomepageInfo($info) {
    echo '<h1>' . $info['title'] . '</h1>';
    echo '<p>' . $info['description'] . '</p>';
}

displayHomepageInfo($homepageInfo);

?>