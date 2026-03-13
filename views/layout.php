<?php

declare(strict_types=1);

$pageTitle = trim((string) ($title ?? ''));
$fullTitle = $pageTitle !== '' ? $pageTitle . ' | DocTrack' : 'DocTrack - Document Approval & Review Platform';
$toastsJson = json_encode(take_flash_messages(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$toastsJson = is_string($toastsJson) ? $toastsJson : '[]';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DocTrack - Web-based document review and approval platform.">
    <title><?= e($fullTitle) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= e(url('/css/main.css')) ?>">
</head>
<body>
<?= $content ?>
<div id="toast-container" data-toasts='<?= e($toastsJson) ?>'></div>
<script src="<?= e(url('/js/toast.js')) ?>" defer></script>
</body>
</html>
