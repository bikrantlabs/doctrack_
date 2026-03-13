<?php
/** @var array{id:int,email:string,fullname:string}|null $user */
$error = flash('error');
$success = flash('success');
?>
<header class="header">
    <div class="container header-inner">
        <a href="<?= e(url('/')) ?>" class="logo">
            <svg class="logo-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            DocTrack
        </a>

        <nav class="nav-actions">
            <?php if ($user === null): ?>
                <a href="<?= e(url('/login')) ?>" class="btn btn-ghost btn-sm">Sign In</a>
                <a href="<?= e(url('/register')) ?>" class="btn btn-primary btn-sm">Create Account</a>
            <?php else: ?>
                <a href="<?= e(url('/dashboard')) ?>" class="btn btn-ghost btn-sm">Dashboard</a>
                <a href="<?= e(url('/logout')) ?>" class="btn btn-primary btn-sm">Logout</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main>
    <section class="hero">
        <div class="container hero-inner">
            <div class="hero-content animate-slide-up">
                <?php if ($error !== null): ?>
                    <div class="flash flash-error"><?= e($error) ?></div>
                <?php endif; ?>
                <?php if ($success !== null): ?>
                    <div class="flash flash-success"><?= e($success) ?></div>
                <?php endif; ?>

                <span class="hero-badge">Structured review workflow for teams</span>
                <h1 class="hero-title">Track every version.<br><span class="text-gradient">Approve with confidence.</span></h1>
                <p class="hero-subtitle">
                    Keep documents, comments, and approval decisions in one dark-themed workspace.
                    No more scattered email threads or lost feedback.
                </p>
                <div class="hero-actions">
                    <?php if ($user === null): ?>
                        <a href="<?= e(url('/register')) ?>" class="btn btn-primary btn-lg">Get Started</a>
                        <a href="<?= e(url('/login')) ?>" class="btn btn-secondary btn-lg">I already have an account</a>
                    <?php else: ?>
                        <a href="<?= e(url('/dashboard')) ?>" class="btn btn-primary btn-lg">Go to Dashboard</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="hero-visual animate-fade-in">
                <div class="doc-preview">
                    <div class="doc-preview-header">
                        <div class="doc-preview-title">Research_Proposal_v3.pdf</div>
                        <span class="doc-preview-status"><span class="doc-preview-status-dot"></span>Under review</span>
                    </div>
                    <div class="doc-preview-body">
                        <div class="doc-line long"></div>
                        <div class="doc-line medium"></div>
                        <div class="doc-line long"></div>
                        <div class="doc-line short"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
