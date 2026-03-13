<div class="auth-layout-register">
    <!-- Left Panel - Visual -->
    <div class="auth-visual">
        <!-- Background Pattern -->
        <div class="auth-pattern">
            <div class="auth-pattern-line" style="top: 20%;"></div>
            <div class="auth-pattern-line" style="top: 40%;"></div>
            <div class="auth-pattern-line" style="top: 60%;"></div>
            <div class="auth-pattern-line" style="top: 80%;"></div>
        </div>

        <div class="auth-visual-content">
            <svg class="auth-visual-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="8.5" cy="7" r="4" stroke="currentColor" stroke-width="1.5"/>
                <line x1="20" y1="8" x2="20" y2="14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <line x1="23" y1="11" x2="17" y2="11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>

            <h2 class="auth-visual-title">Join DocTrack</h2>
            <p class="auth-visual-text">
                Create your account and start streamlining your document approval workflow today.
            </p>

            <div class="register-visual-features">
                <div class="register-feature-item">
                    <div class="register-feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M22 4 12 14.01l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="register-feature-content">
                        <h4 class="register-feature-title">Free 14-Day Trial</h4>
                        <p class="register-feature-text">No credit card required</p>
                    </div>
                </div>

                <div class="register-feature-item">
                    <div class="register-feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="register-feature-content">
                        <h4 class="register-feature-title">Enterprise Security</h4>
                        <p class="register-feature-text">Bank-level encryption</p>
                    </div>
                </div>

                <div class="register-feature-item">
                    <div class="register-feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="register-feature-content">
                        <h4 class="register-feature-title">Unlimited Team Members</h4>
                        <p class="register-feature-text">Collaborate with your whole team</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Panel - Form -->
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
            <h1 class="auth-form-title">Create your account</h1>
            <p class="auth-form-subtitle">
                Start your free trial. No credit card required.
            </p>

            <form class="auth-form register-form" action="<?= e(url('/register')) ?>" method="POST">
                <div class="form-name-row">
                    <div class="form-group">
                        <label for="fullname" class="form-label">Full Name</label>
                        <input
                            type="text"
                            id="fullname"
                            name="fullname"
                            class="form-input"
                            placeholder="John"
                            value="<?= e(old('fullname')) ?>"
                            required
                            autocomplete="name"
                        >
                    </div>


                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-input"
                        placeholder="you@company.com"
                        value="<?= e(old('email')) ?>"
                        required
                        autocomplete="email"
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="form-input-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input"
                            placeholder="Create a strong password"
                            required
                            autocomplete="new-password"
                            minlength="8"
                        >

                    </div>
                </div>
                <button type="submit" class="btn btn-sm btn-primary form-submit mt-lg">
                    Create Account
                </button>
            </form>

            <div class="divider">OR</div>

            <div class="auth-footer">
                <p class="auth-footer-text">
                    Already have an account?
                    <a href="<?= e(url('/login')) ?>" class="auth-footer-link">Sign in</a>
                </p>
            </div>
        </div>
    </div>
</div>
