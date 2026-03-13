<?php
use App\Utils\SVGRenderer;

$currentUrl = $currentUrl ?? current_path();
$normalizedCurrentPath = '/' . trim((string) $currentUrl, '/');
if ($normalizedCurrentPath === '//') {
    $normalizedCurrentPath = '/';
}

// Define sidebar links
$sidebarLinks = [
        [
                "key" => "projects",
                "label" => "Projects",
                "icon" => "folder",
                "href" => "/app",
        ],
        [
                "key" => "documents",
                "label" => "Documents",
                "icon" => "document",
                "href" => "/app/documents",
        ],
        [
                "key" => "reviews",
                "label" => "Reviews",
                "icon" => "reviews",
                "href" => "/app/reviews",
        ],
        [
                "key" => "team",
                "label" => "Team",
                "icon" => "users",
                "href" => "/app/team",
        ],
        [
                "key" => "settings",
                "label" => "Settings",
                "icon" => "settings",
                "href" => "/app/settings",
        ],
];



?>

<aside class="sidebar">
    <div class="sidebar-header">
        <a href="/" class="sidebar-logo">
            <?php
            // Using the static renderInline method
            SVGRenderer::renderInline('logo', [
                    'width' => '24',
                    'height' => '24',
                    'class' => 'sidebar-logo-icon'
            ]);
            ?>
            <span class="sidebar-logo-text">DocuFlow</span>
        </a>
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-nav-section">
            <span class="sidebar-nav-label">Main</span>
            <ul class="sidebar-menu">

                <?php foreach ($sidebarLinks as $link):

                    $normalizedHref = '/' . trim((string) $link['href'], '/');
                    $isActive = $normalizedCurrentPath === $normalizedHref
                        || str_starts_with($normalizedCurrentPath, $normalizedHref . '/');

                    ?>

                    <li class="sidebar-menu-item">

                        <a href="<?php echo e(url((string) $link['href'])); ?>"
                           class="sidebar-link <?php echo $isActive ? 'active' : ''; ?>">

                            <?php
                            echo SVGRenderer::render($link['icon'], [
                                    'class' => 'sidebar-icon' . ($isActive ? ' active' : ''),
                                    'stroke' => $isActive ? '#3b82f6' : 'currentColor',
                            ]);
                            ?>

                            <span><?php echo htmlspecialchars($link['label']); ?></span>

                            <?php if ($link['key'] === 'projects'): ?>
                                <span class="sidebar-badge">12</span>
                            <?php elseif ($link['key'] === 'reviews'): ?>
                                <span class="sidebar-badge badge-warning">5</span>
                            <?php endif; ?>

                        </a>

                    </li>

                <?php endforeach; ?>

            </ul>

        </div>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                <span>JD</span>
            </div>
            <div class="sidebar-user-info">
                <span class="sidebar-user-name">John Doe</span>
                <span class="sidebar-user-email">john@example.com</span>
            </div>
            <button class="sidebar-user-menu" aria-label="User menu">
                <?php SVGRenderer::renderInline('more-vertical', [
                        'width' => '20',
                        'height' => '20',
                ]); ?>
            </button>
        </div>
    </div>
</aside>