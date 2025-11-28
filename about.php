<?php
/**
 * About Us - Homepage
 */

require_once __DIR__ . '/init.php';
include __DIR__ . '/includes/header.php';

$pageTitle = I18N::t('page.about.title', 'About Us');
$pageSubtitle = I18N::t('page.about.subtitle', 'Premium Home Fragrances by By Velcheva');
$pageContent = I18N::t('page.about.content', 'NicheHome.ch presents a carefully curated selection of home fragrances developed for discerning residential and hospitality concepts. Each creation arises from a passion for exceptional accords and long-lasting performance.

Our boutique serves interior designers, boutiques and private clients throughout Switzerland with flexible terms, customized consulting and ready-to-use collections.

By Velcheva combines elegance and warmth in a sophisticated format. Perfect for those who want premium fragrances that transform any space into a sensory experience.');
?>

<section class="page-hero">
    <div class="page-hero__content">
        <p class="section-heading__label"><?php echo I18N::t('nav.about', 'About Us'); ?></p>
        <h1 class="page-hero__title"><?php echo htmlspecialchars($pageTitle); ?></h1>
        <p class="page-hero__subtitle"><?php echo htmlspecialchars($pageSubtitle); ?></p>
    </div>
</section>

<section class="catalog-section">
    <div class="container">
        <div style="max-width: 800px; margin: 0 auto;">
            <p style="white-space: pre-line; line-height: 1.8; color: var(--color-text);">
                <?php echo nl2br(htmlspecialchars($pageContent)); ?>
            </p>
            
            <div style="margin-top: 3rem; text-align: center;">
                <a href="catalog.php?lang=<?php echo I18N::getLanguage(); ?>" class="btn btn--gold">
                    <?php echo I18N::t('nav.catalog', 'View Catalog'); ?>
                </a>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>