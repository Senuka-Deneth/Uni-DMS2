<?php
require_once __DIR__ . '/bootstrap.php';
$current = basename($_SERVER['PHP_SELF']);
$navLinks = [
    'Dashboard' => 'dashboard.php',
    'Universities' => 'universities.php',
    'Degrees' => 'degrees.php',
    'Activities' => 'activities.php',
    'Logout' => 'logout.php'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uni-DMS Admin<?php echo isset($pageTitle) ? ' - ' . htmlspecialchars($pageTitle) : ''; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div>
            <h2>Uni-DMS Admin</h2>
            <p>Manage universities, degrees, and activities.</p>
        </div>
        <?php foreach ($navLinks as $label => $href):
            $classes = $current === $href ? 'active' : '';
        ?>
            <a href="<?= $href ?>" class="<?= $classes ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </aside>
    <div class="admin-content">
        <?php render_flash_messages(); ?>
