<?php
// contacts.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    // Validate the input
    if (empty($name) || empty($email) || empty($message)) {
        echo 'All fields are required.';
        exit;
    }

    // Here you would typically send the email, but we'll just echo it
    echo "Name: $name\n";
    echo "Email: $email\n";
    echo "Message: $message\n";

    // Respond to the user
    echo 'Thank you for your message!';
} else {
    echo 'Invalid request method.';
}
?>