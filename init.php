<?php
session_start();
require_once __DIR__ . '/lib/DataStore.php';
require_once __DIR__ . '/lib/I18N.php';
require_once __DIR__ . '/lib/Fragrances.php';
require_once __DIR__ . '/lib/Products.php';
require_once __DIR__ . '/lib/Cart.php';

$config = DataStore::readJson(__DIR__ . '/data/config.json');
I18N::init($config);
$currentLang = I18N::getLanguage();
$supportedLanguages = $config['supportedLanguages'] ?? ['en'];
$currency = $config['currency'] ?? 'CHF';
$freeShippingThreshold = $config['freeShippingThreshold'] ?? 0;
