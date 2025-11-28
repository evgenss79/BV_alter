<?php
/**
 * Gift Sets - Gift set constructor page
 */

require_once __DIR__ . '/init.php';
include __DIR__ . '/includes/header.php';

$categories = loadJSON('categories.json');
$currentLang = I18N::getLanguage();

// Filter out non-product categories
$productCategories = array_filter($categories, function($cat, $slug) {
    return !in_array($slug, ['gift_sets', 'aroma_marketing']);
}, ARRAY_FILTER_USE_BOTH);
?>

<section class="page-hero">
    <div class="page-hero__content">
        <p class="section-heading__label"><?php echo I18N::t('nav.giftSets', 'Gift Sets'); ?></p>
        <h1 class="page-hero__title"><?php echo I18N::t('page.giftSets.title', 'Create Your Gift Set'); ?></h1>
        <p class="page-hero__subtitle"><?php echo I18N::t('page.giftSets.subtitle', 'Combine your favorite products with a 5% discount'); ?></p>
    </div>
</section>

<section class="gift-sets-section">
    <form data-gift-set-form>
        <div class="gift-slots">
            <?php for ($i = 1; $i <= 3; $i++): ?>
                <div class="gift-slot" data-gift-slot>
                    <h3 class="gift-slot__title"><?php echo I18N::t('page.giftSets.slot', 'Slot'); ?> <?php echo $i; ?></h3>
                    
                    <div class="gift-slot__selects">
                        <div class="form-group">
                            <label><?php echo I18N::t('page.giftSets.selectCategory', 'Select category'); ?></label>
                            <select data-gift-category>
                                <option value=""><?php echo I18N::t('page.giftSets.selectCategory', 'Select category'); ?></option>
                                <?php foreach ($productCategories as $slug => $cat): ?>
                                    <option value="<?php echo htmlspecialchars($slug); ?>">
                                        <?php echo htmlspecialchars(I18N::t('category.' . $slug . '.name', ucfirst(str_replace('_', ' ', $slug)))); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label><?php echo I18N::t('common.volume', 'Volume'); ?></label>
                            <select data-gift-volume style="display: none;">
                                <option value=""><?php echo I18N::t('common.selectVolume', 'Select volume'); ?></option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label><?php echo I18N::t('common.fragrance', 'Fragrance'); ?></label>
                            <select data-gift-fragrance>
                                <option value=""><?php echo I18N::t('common.selectFragrance', 'Select fragrance'); ?></option>
                                <?php 
                                $allFragrances = allowedFragrances('aroma_diffusers');
                                foreach ($allFragrances as $fragCode): ?>
                                    <option value="<?php echo htmlspecialchars($fragCode); ?>">
                                        <?php echo htmlspecialchars(I18N::t('fragrance.' . $fragCode . '.name', ucfirst(str_replace('_', ' ', $fragCode)))); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
        
        <div class="gift-total">
            <h3><?php echo I18N::t('page.giftSets.totalPrice', 'Total price'); ?></h3>
            <p class="gift-total__price" data-gift-total>CHF 0.00</p>
            <p class="gift-total__discount" data-gift-discount><?php echo I18N::t('page.giftSets.discount', '5% gift set discount'); ?>: -CHF 0.00</p>
            
            <button type="button" class="btn btn--gold" data-add-gift-set>
                <?php echo I18N::t('page.giftSets.addToCart', 'Add gift set to cart'); ?>
            </button>
        </div>
    </form>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
