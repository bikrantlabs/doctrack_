<?php


?>

<header class="header-wrapper">
    <div class="header">
        <div class="container header-inner">
            <a href="/" class="logo">
                <svg class="logo-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" stroke="currentColor"
                          stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"/>
                </svg>
                DocTrack
            </a>

            <nav class="nav">
                <ul class="nav-links">
                    <li><a href="#features" class="nav-link">Features</a></li>
                    <li><a href="#workflow" class="nav-link">How it Works</a></li>
                    <li><a href="#" class="nav-link">Pricing</a></li>
                    <li><a href="#" class="nav-link">Enterprise</a></li>
                </ul>
                <div class="nav-actions">

                        <a href="/login" class="btn btn-ghost btn-sm">Sign In</a>
                        <a href="/register" class="btn btn-primary btn-sm">Get Started</a>

                        <a href="/profile"
                           class="nav-link active"> <?php echo Application::$app->user->getName(); ?> </a>
                        <form action="/logout" method="post">
                            <button class="btn btn-secondary btn-sm">Logout</button>

                        </form>

                </div>
            </nav>
        </div>
    </div>
</header>