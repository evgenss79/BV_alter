<?php
/**
 * Contacts - Contact page
 */

require_once __DIR__ . '/init.php';
include __DIR__ . '/includes/header.php';

$currentLang = I18N::getLanguage();
$success = false;
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'All fields are required.';
    } elseif (!isValidEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else {
        // In production, send email here
        $success = true;
    }
}
?>

<section class="page-hero">
    <div class="page-hero__content">
        <p class="section-heading__label"><?php echo I18N::t('nav.contacts', 'Contact'); ?></p>
        <h1 class="page-hero__title"><?php echo I18N::t('page.contacts.title', 'Contact Us'); ?></h1>
        <p class="page-hero__subtitle"><?php echo I18N::t('page.contacts.subtitle', 'Get in touch with our team'); ?></p>
    </div>
</section>

<section class="contact-section">
    <div class="contact-grid">
        <div class="contact-info">
            <div class="contact-info__item">
                <div class="contact-info__icon">📧</div>
                <div>
                    <strong>Email</strong><br>
                    <a href="mailto:<?php echo htmlspecialchars($CONFIG['contact_email'] ?? 'info@nichehome.ch'); ?>">
                        <?php echo htmlspecialchars($CONFIG['contact_email'] ?? 'info@nichehome.ch'); ?>
                    </a>
                </div>
            </div>
            
            <div class="contact-info__item">
                <div class="contact-info__icon">📍</div>
                <div>
                    <strong><?php echo I18N::t('contact.address', 'Address'); ?></strong><br>
                    <?php echo htmlspecialchars($CONFIG['address']['street'] ?? 'Sample Street 123'); ?><br>
                    <?php echo htmlspecialchars(($CONFIG['address']['zip'] ?? '8000') . ' ' . ($CONFIG['address']['city'] ?? 'Zurich')); ?><br>
                    <?php echo htmlspecialchars($CONFIG['address']['country'] ?? 'Switzerland'); ?>
                </div>
            </div>
        </div>
        
        <div class="contact-form">
            <?php if ($success): ?>
                <div class="alert alert--success">
                    <?php echo I18N::t('page.contacts.formSuccess', 'Thank you for your message. We will get back to you soon.'); ?>
                </div>
            <?php else: ?>
                <?php if ($error): ?>
                    <div class="alert alert--error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="form-group mb-3">
                        <label><?php echo I18N::t('page.contacts.formName', 'Your name'); ?></label>
                        <input type="text" name="name" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label><?php echo I18N::t('page.contacts.formEmail', 'Email address'); ?></label>
                        <input type="email" name="email" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label><?php echo I18N::t('page.contacts.formMessage', 'Your message'); ?></label>
                        <textarea name="message" rows="5" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn--gold">
                        <?php echo I18N::t('page.contacts.formSubmit', 'Send message'); ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>