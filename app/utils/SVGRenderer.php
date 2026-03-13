<?php

declare(strict_types=1);

namespace App\Utils;



class SVGRenderer
{
    private static array $svgCache = [];
    private static ?string $iconsPath = null;

    /**
     * Initialize the icons path
     */
    private static function initPath(): void
    {
        if (self::$iconsPath === null) {
            $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
            self::$iconsPath = rtrim((string) $basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR;
        }
    }

    /**
     * Set custom icons path
     */
    public static function setIconsPath(string $path): void
    {
        self::$iconsPath = rtrim($path, '/') . '/';
    }

    /**
     * Render an SVG icon with custom attributes
     *
     * @param string $iconName The name of the icon file (without .svg extension)
     * @param array $props Properties to override (width, height, fill, stroke, class, etc.)
     * @return string The rendered SVG HTML
     */
    public static function render($iconName, $props = []): string
    {
        self::initPath();
        $svg = self::loadSVG($iconName);

        if (!$svg) {
            return self::renderFallbackIcon($iconName);
        }

        return self::applyPropsToSVG($svg, $props);
    }

    /**
     * Echo the rendered SVG (convenience method)
     */
    public static function renderInline($iconName, $props = []): void
    {
        echo self::render($iconName, $props);
    }

    /**
     * Load SVG content from file
     */
    private static function loadSVG($iconName)
    {
        // Check cache first
        if (isset(self::$svgCache[$iconName])) {
            return self::$svgCache[$iconName];
        }

        $filePath = self::$iconsPath . $iconName . '.svg';

        if (!file_exists($filePath)) {
            return null;
        }

        $svg = file_get_contents($filePath);

        // Remove XML declaration if present
        $svg = preg_replace('/<\?xml.*?\?>/', '', $svg);

        // Clean up any unnecessary attributes we might want to override
        $svg = preg_replace('/\s(width|height|class|style)="[^"]*"/', '', $svg);

        // Cache it
        self::$svgCache[$iconName] = $svg;

        return $svg;
    }

    /**
     * Apply properties to SVG
     */
    private static function applyPropsToSVG($svg, $props): string
    {
        // Default props
        $defaultProps = [
            'width' => '24',
            'height' => '24',
            'class' => 'sidebar-icon',
            'fill' => 'none',
            'stroke' => 'currentColor',
            'stroke-width' => '1.5'
        ];

        // Merge with provided props
        $mergedProps = array_merge($defaultProps, $props);

        // Preserve viewBox if it exists in original SVG
        $viewBox = '';
        if (preg_match('/viewBox="([^"]*)"/', $svg, $matches)) {
            $viewBox = ' viewBox="' . $matches[1] . '"';
        } else {
            $viewBox = ' viewBox="0 0 24 24"';
        }

        // Preserve stroke-linecap and stroke-linejoin if present in original SVG
        $extraAttrs = [];
        if (preg_match('/stroke-linecap="([^"]*)"/', $svg, $matches)) {
            $extraAttrs['stroke-linecap'] = $matches[1];
        }

        if (preg_match('/stroke-linejoin="([^"]*)"/', $svg, $matches)) {
            $extraAttrs['stroke-linejoin'] = $matches[1];
        }

        // Parse the SVG to get its inner content
        if (preg_match('/<svg[^>]*>(.*?)<\/svg>/s', $svg, $matches)) {
            $innerContent = $matches[1];
        } else {
            $innerContent = $svg;
        }

        // Build attributes string
        $attrs = [];
        foreach ($mergedProps as $key => $value) {
            $attrs[] = $key . '="' . htmlspecialchars($value) . '"';
        }

        // Add extra attributes
        foreach ($extraAttrs as $key => $value) {
            $attrs[] = $key . '="' . htmlspecialchars($value) . '"';
        }

        $attrString = implode(' ', $attrs);

        // Return the modified SVG
        return '<svg ' . $attrString . $viewBox . '>' . $innerContent . '</svg>';
    }

    /**
     * Render a fallback icon if the requested icon doesn't exist
     */
    private static function renderFallbackIcon($iconName): string
    {
        // Create a simple fallback SVG (a circle with the first letter)
        $letter = strtoupper(substr($iconName, 0, 1));

        return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="sidebar-icon fallback-icon">
            <circle cx="12" cy="12" r="10" stroke="currentColor" fill="none"/>
            <text x="12" y="16" text-anchor="middle" fill="currentColor" font-size="10" font-family="Arial, sans-serif">' . $letter . '</text>
        </svg>';
    }

    /**
     * Clear the SVG cache
     */
    public static function clearCache(): void
    {
        self::$svgCache = [];
    }

    /**
     * Check if an icon exists
     */
    public static function iconExists($iconName): bool
    {
        self::initPath();
        $filePath = self::$iconsPath . $iconName . '.svg';
        return file_exists($filePath);
    }

    /**
     * Get list of all available icons
     */
    public static function getAvailableIcons(): array
    {
        self::initPath();
        $icons = [];

        if (is_dir(self::$iconsPath)) {
            $files = scandir(self::$iconsPath);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'svg') {
                    $icons[] = pathinfo($file, PATHINFO_FILENAME);
                }
            }
        }

        return $icons;
    }
}