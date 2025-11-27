<?php
require_once __DIR__ . '/../init.php';
$currentPage = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '.php');
if ($currentPage === '' || $currentPage === 'index') {
    $currentPage = 'about';
}
$navItems = [
    ['key' => 'catalog', 'label' => 'ui.nav.catalog', 'href' => '/catalog.php'],
    ['key' => 'gift-sets', 'label' => 'ui.nav.gifts', 'href' => '/gift-sets.php'],
    ['key' => 'about', 'label' => 'ui.nav.about', 'href' => '/about.php'],
    ['key' => 'contacts', 'label' => 'ui.nav.contacts', 'href' => '/contacts.php'],
    ['key' => 'support', 'label' => 'ui.nav.support', 'href' => '/support.php'],
];
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($currentLang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(I18N::t('ui.meta.title', 'NICHEHOME.CH')); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script>window.__LANG__ = '<?php echo $currentLang; ?>';</script>
</head>
<body>
<header class="site-header" id="siteHeader">
    <div class="utility-bar">
        <p class="utility-bar__item"><?php echo I18N::t('ui.utility.shipping'); ?></p>
        <p class="utility-bar__item"><?php echo I18N::t('ui.utility.delivery'); ?></p>
    </div>
    <div class="site-header__main">
        <button class="site-header__burger" aria-label="<?php echo I18N::t('ui.nav.toggle'); ?>">
            <span></span><span></span><span></span>
        </button>
        <a href="/about.php" class="site-header__logo">NICHEHOME.CH</a>
        <nav class="primary-nav" aria-label="<?php echo I18N::t('ui.nav.main'); ?>">
            <ul class="primary-nav__list">
                <?php foreach ($navItems as $item): ?>
                    <?php $isActive = $currentPage === $item['key']; ?>
                    <li class="primary-nav__item<?php echo $isActive ? ' is-active' : ''; ?>">
                        <a class="primary-nav__link<?php echo $isActive ? ' primary-nav__link--active' : ''; ?>" href="<?php echo $item['href']; ?>"><?php echo I18N::t($item['label']); ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <div class="site-header__actions">
            <a class="site-header__icon site-header__cart" href="/cart.php">
                <span><?php echo I18N::t('ui.cart.title'); ?></span>
                <span>(<span data-cart-count><?php echo count(Cart::get()); ?></span> <?php echo I18N::t('ui.cart.items'); ?>)</span>
            </a>
            <div class="lang-dropdown">
                <form method="get" action="" class="lang-dropdown__form">
                    <select name="lang" onchange="this.form.submit()" aria-label="<?php echo I18N::t('ui.nav.language'); ?>">
                        <?php foreach ($supportedLanguages as $lang): ?>
                            <option value="<?php echo $lang; ?>" <?php echo $lang === $currentLang ? 'selected' : ''; ?>><?php echo strtoupper($lang); ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>
    </div>
</header>
<main>
