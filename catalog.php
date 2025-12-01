<?php
/**
 * Catalog - Category Overview
 */

require_once __DIR__ . '/init.php';

$categories = loadJSON('categories.json');
$currentLang = I18N::getLanguage();

// Define which categories to display on catalog page
// We show accessories instead of gift_sets on catalog cards
// gift_sets is still accessible via navigation
$catalogCategories = [
    'aroma_diffusers',
    'scented_candles',
    'home_perfume',
    'car_perfume',
    'textile_perfume',
    'limited_edition',
    'accessories',       // instead of gift_sets
    'aroma_marketing',
];

// Filter and keep only catalog categories in the correct order
$displayCategories = [];
foreach ($catalogCategories as $slug) {
    if (isset($categories[$slug])) {
        $displayCategories[$slug] = $categories[$slug];
    }
}

// Custom header for catalog page (no footer)
$cartCount = getCartCount();
?>
<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo I18N::t('site.title', 'NicheHome.ch - Premium Home Fragrances'); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Manrope:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="body--catalog">
    <header class="site-header">
        <div class="utility-bar">
            <p class="utility-bar__item"><?php echo I18N::t('common.utilityShipping', 'Free shipping from CHF 80'); ?></p>
            <p class="utility-bar__item"><?php echo I18N::t('common.utilityDelivery', 'Delivery within 1-3 business days'); ?></p>
        </div>
        <div class="site-header__main">
            <button class="site-header__burger" aria-label="<?php echo I18N::t('common.menuToggle', 'Toggle menu'); ?>">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <a href="about.php?lang=<?php echo $currentLang; ?>" class="site-header__logo">NicheHome.ch</a>
            <nav class="primary-nav" aria-label="<?php echo I18N::t('common.mainMenu', 'Main menu'); ?>">
                <ul class="primary-nav__list">
                    <li class="primary-nav__item primary-nav__item--mega">
                        <a class="primary-nav__link" href="catalog.php?lang=<?php echo $currentLang; ?>" data-mega-toggle aria-haspopup="true" aria-expanded="false">
                            <?php echo I18N::t('nav.catalog', 'Catalog'); ?>
                        </a>
                        <div class="mega-panel mega-panel--catalog">
                            <ul class="mega-panel__list">
                                <li><a href="category.php?slug=aroma_diffusers&lang=<?php echo $currentLang; ?>"><?php echo I18N::t('category.aroma_diffusers.name', 'Aroma Diffusers'); ?></a></li>
                                <li><a href="category.php?slug=scented_candles&lang=<?php echo $currentLang; ?>"><?php echo I18N::t('category.scented_candles.name', 'Scented Candles'); ?></a></li>
                                <li><a href="category.php?slug=home_perfume&lang=<?php echo $currentLang; ?>"><?php echo I18N::t('category.home_perfume.name', 'Home Perfume'); ?></a></li>
                                <li><a href="category.php?slug=car_perfume&lang=<?php echo $currentLang; ?>"><?php echo I18N::t('category.car_perfume.name', 'Car Perfume'); ?></a></li>
                                <li><a href="category.php?slug=textile_perfume&lang=<?php echo $currentLang; ?>"><?php echo I18N::t('category.textile_perfume.name', 'Textile Perfume'); ?></a></li>
                                <li><a href="category.php?slug=limited_edition&lang=<?php echo $currentLang; ?>"><?php echo I18N::t('category.limited_edition.name', 'Limited Edition'); ?></a></li>
                                <li><a href="category.php?slug=accessories&lang=<?php echo $currentLang; ?>"><?php echo I18N::t('category.accessories.name', 'Accessories'); ?></a></li>
                            </ul>
                        </div>
                    </li>
                    <li class="primary-nav__item">
                        <a class="primary-nav__link" href="gift-sets.php?lang=<?php echo $currentLang; ?>">
                            <?php echo I18N::t('nav.giftSets', 'Gift Sets'); ?>
                        </a>
                    </li>
                    <li class="primary-nav__item">
                        <a class="primary-nav__link" href="aroma-marketing.php?lang=<?php echo $currentLang; ?>">
                            <?php echo I18N::t('nav.aromaMarketing', 'Aroma Marketing'); ?>
                        </a>
                    </li>
                    <li class="primary-nav__item">
                        <a class="primary-nav__link" href="about.php?lang=<?php echo $currentLang; ?>">
                            <?php echo I18N::t('nav.about', 'About Us'); ?>
                        </a>
                    </li>
                    <li class="primary-nav__item">
                        <a class="primary-nav__link" href="contacts.php?lang=<?php echo $currentLang; ?>">
                            <?php echo I18N::t('nav.contacts', 'Contact'); ?>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="site-header__actions">
                <a href="cart.php?lang=<?php echo $currentLang; ?>" class="site-header__cart">
                    <span class="cart-icon">🛒</span>
                    <span class="cart-label"><?php echo I18N::t('common.cart', 'Cart'); ?></span>
                    <span class="cart-count" data-cart-count>(<?php echo $cartCount; ?>)</span>
                </a>
                <div class="lang-dropdown" data-lang-dropdown>
                    <button type="button" class="lang-dropdown__toggle" data-lang-toggle aria-haspopup="listbox" aria-expanded="false">
                        <span data-current-lang-label><?php echo I18N::getLanguageLabel($currentLang); ?></span>
                        <span class="lang-dropdown__chevron" aria-hidden="true"></span>
                    </button>
                    <ul class="lang-dropdown__list" role="listbox">
                        <?php foreach (I18N::getSupportedLanguages() as $langCode): ?>
                            <li>
                                <button type="button" 
                                        data-lang="<?php echo $langCode; ?>" 
                                        class="lang-dropdown__option <?php echo $langCode === $currentLang ? 'is-active' : ''; ?>">
                                    <?php echo I18N::getLanguageLabel($langCode); ?>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </header>

<main class="catalog-page">
<section class="page-hero catalog-hero">
    <div class="page-hero__content">
        <h1 class="page-title"><?php echo I18N::t('page.catalog.title', 'Catalog'); ?></h1>
    </div>
</section>

<section class="catalog-section">
    <div class="catalog-grid">
        <?php foreach ($displayCategories as $slug => $category): ?>
            <?php
            $name = I18N::t('category.' . $slug . '.name', ucfirst(str_replace('_', ' ', $slug)));
            $image = getCategoryImage($slug);
            
            // Determine link
            if (isset($category['redirect'])) {
                $link = $category['redirect'] . '?lang=' . $currentLang;
            } else {
                $link = 'category.php?slug=' . urlencode($slug) . '&lang=' . $currentLang;
            }
            
            // Determine catalog item class
            $catalogItemClass = 'catalog-card';
            if (in_array($slug, ['home_perfume', 'car_perfume', 'textile_perfume'])) {
                $catalogItemClass .= ' catalog-item--' . $slug;
            }
            
            // Determine image wrapper class for special bottle categories
            $imageWrapperClass = 'catalog-card-image-wrapper';
            if ($slug === 'home_perfume') {
                $imageWrapperClass .= ' catalog-card-image-wrapper--home-perfume';
            } elseif ($slug === 'car_perfume') {
                $imageWrapperClass .= ' catalog-card-image-wrapper--car-perfume';
            } elseif ($slug === 'textile_perfume') {
                $imageWrapperClass .= ' catalog-card-image-wrapper--textile-perfume';
            }
            ?>
            <a href="<?php echo htmlspecialchars($link); ?>" class="<?php echo htmlspecialchars($catalogItemClass); ?>" data-category-slug="<?php echo htmlspecialchars($slug); ?>">
                <div class="catalog-card__title-bar">
                    <?php echo htmlspecialchars($name); ?>
                </div>
                <div class="<?php echo htmlspecialchars($imageWrapperClass); ?>">
                    <img src="<?php echo htmlspecialchars($image); ?>" 
                         alt="<?php echo htmlspecialchars($name); ?>" 
                         class="catalog-card__image"
                         onerror="this.src='/img/placeholder.svg'">
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
</main>

<script src="assets/js/app.js"></script>
</body>
</html>