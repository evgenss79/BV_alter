<?php include __DIR__ . '/templates/header.php'; ?>
<?php $slug = basename($_SERVER['SCRIPT_NAME'], '.php'); ?>
<section class="section">
    <h1><?php echo I18N::tPage($slug . '.title'); ?></h1>
    <p><?php echo nl2br(I18N::tPage($slug . '.body')); ?></p>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>
