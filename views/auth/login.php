<div class="auth-layout">
    <div class="auth-visual">
        <div class="auth-pattern">
            <div class="auth-pattern-line" style="top: 20%;"></div>
            <div class="auth-pattern-line" style="top: 40%;"></div>
            <div class="auth-pattern-line" style="top: 60%;"></div>
            <div class="auth-pattern-line" style="top: 80%;"></div>
        </div>

        <div class="auth-visual-content">
            <h2 class="auth-visual-title">Welcome Back</h2>
            <p class="auth-visual-text">Sign in to continue your document review workflow.</p>
        </div>


    <div class="auth-form-panel">
        <div class="auth-form-header">
            <a href="<?= e(url('/')) ?>" class="logo">
                <svg class="logo-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                DocTrack
            </a>
        </div>

        <div class="auth-form-container">
            <h1 class="auth-form-title">Sign in to your account</h1>
            <p class="auth-form-subtitle">Use your email and password to access your workspace.</p>

            <form class="auth-form" action="<?= e(url('/login')) ?>" method="POST">
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-input"
                        placeholder="you@example.com"
                        value="<?= e(old('email')) ?>"
                        required
                        autocomplete="email"
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-sm form-submit">Sign In</button>
            </form>

            <div class="auth-footer">
                <p class="auth-footer-text">
                    New to DocTrack?
                    <a href="<?= e(url('/register')) ?>" class="auth-footer-link">Create an account</a>
                </p>
            </div>
        </div>
    </div>   </div>
</div>
