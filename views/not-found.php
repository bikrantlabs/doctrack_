<main class="not-found">
    <div class="not-found-container">
        <div class="not-found-code" aria-hidden="true">
            <span class="digit">4</span>
            <span class="digit-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="9" y1="15" x2="15" y2="15"/>
                </svg>
            </span>
            <span class="digit">4</span>
        </div>

        <div class="not-found-content">
            <h1 class="not-found-title">Page not found</h1>
            <p class="not-found-description">
                The page you are looking for is not registered in DocuFlow.
            </p>
            <div class="not-found-actions">
                <a class="btn btn-primary" href="<?= e(url('/')) ?>">Back to Home</a>
                <a class="btn btn-secondary" href="<?= e(url('/app')) ?>">Open Projects</a>
            </div>
        </div>
    </div>
</main>
