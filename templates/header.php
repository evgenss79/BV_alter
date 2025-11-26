<?php
require_once __DIR__ . '/../init.php';
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($currentLang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(I18N::t('ui.meta.title', 'NICHEHOME.CH')); ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script>window.__LANG__ = '<?php echo $currentLang; ?>';</script>
</head>
<body>
<header class="site-header">
    <div class="logo-area">
        <a href="/index.php" class="logo">NICHEHOME.CH</a>
        <span class="brand">By Velcheva</span>
    </div>
    <nav class="main-nav">
        <a href="/index.php"><?php echo I18N::t('ui.nav.home'); ?></a>
        <a href="/catalog.php"><?php echo I18N::t('ui.nav.catalog'); ?></a>
        <a href="/about.php"><?php echo I18N::t('ui.nav.about'); ?></a>
        <a href="/blog.php"><?php echo I18N::t('ui.nav.blog'); ?></a>
        <a href="/contacts.php"><?php echo I18N::t('ui.nav.contacts'); ?></a>
    </nav>
    <div class="header-actions">
        <form method="get" action="" class="lang-switcher">
            <select name="lang" onchange="this.form.submit()">
                <?php foreach ($supportedLanguages as $lang): ?>
                    <option value="<?php echo $lang; ?>" <?php echo $lang === $currentLang ? 'selected' : ''; ?>><?php echo strtoupper($lang); ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <a class="cart-link" href="/cart.php">
            <span>🛒</span>
            <span class="cart-count"><?php echo count(Cart::get()); ?></span>
        </a>
        <div class="auth-links">
            <a href="#">Login</a> /
            <a href="#">Register</a>
        </div>
    </div>
</header>
<main>
