<main class="container dashboard-page">
    <?php $success = flash('success'); ?>
    <?php if ($success !== null): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>

    <h1 class="dashboard-title">Welcome, <?= e($user['fullname'] ?? 'User') ?></h1>
    <p class="dashboard-subtitle">Authentication is now active. Projects and documents come in the next phase.</p>

    <div class="dashboard-actions">
        <a class="btn btn-primary" href="<?= e(url('/')) ?>">Back to Home</a>
        <a class="btn btn-ghost" href="<?= e(url('/logout')) ?>">Logout</a>
    </div>
</main>

