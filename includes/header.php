<?php
$pageTitle = isset($pageTitle) ? $pageTitle : 'University Degree Management System';
$pageStyles = isset($pageStyles) && is_array($pageStyles) ? $pageStyles : [];
$navLinks = [
    'index.php' => 'Home',
    'universities.php' => 'Universities',
    'finder.php' => 'Z-Score Finder',
    'gallery.php' => 'Gallery',
    'about.php' => 'About'
];
$currentScript = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0A0E1A">
    <title>Uni-DMS | <?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-+hW1gqk0Kp48DStaK0p9dRDe7H4qUi6V7u8UAUCJw+dR/q1LN5fZtL7Zio6bd99Js3lW2+L3g3lj5kS+6JgV0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/design-tokens.css">
    <link rel="stylesheet" href="css/typography.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/animations.css">
    <?php foreach ($pageStyles as $style): ?>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($style); ?>">
    <?php endforeach; ?>
</head>
<body>
<div class="site-shell">
    <header class="floating-navbar">
        <div class="floating-navbar__brand">
            <a href="index.php" style="display:flex; align-items:center; text-decoration:none; color:inherit;">
                <span class="floating-navbar__brand-mark" aria-hidden="true"></span>
                <span>Uni-DMS</span>
            </a>
        </div>
        <nav>
            <div class="nav-links" role="menubar">
                <?php foreach ($navLinks as $file => $label): ?>
                    <a class="nav-link<?php echo $currentScript === $file ? ' is-active' : ''; ?>" href="<?php echo $file; ?>"><?php echo $label; ?></a>
                <?php endforeach; ?>
            </div>
        </nav>
        <div class="nav-actions">
            <a class="btn btn-secondary" href="login.php">Sign In</a>
            <a class="btn btn-primary" href="finder.php">Find My Degree</a>
            <button class="nav-toggle" type="button" aria-controls="navDrawer" aria-expanded="false" aria-label="Open navigation menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>
    <div class="nav-drawer" id="navDrawer" aria-hidden="true">
        <div class="nav-drawer__inner">
            <div class="nav-drawer__header">
                <span>Menu</span>
                <button class="nav-drawer__close" type="button" aria-label="Close navigation menu">
                    &times;
                </button>
            </div>
            <ul class="nav-drawer__links" role="menu">
                <?php foreach ($navLinks as $file => $label): ?>
                    <li>
                        <a class="drawer-link" href="<?php echo $file; ?>"><?php echo $label; ?></a>
                    </li>
                <?php endforeach; ?>
                <li class="drawer-mobile-only" style="display: none; margin-top: 1rem;">
                    <a class="btn btn-secondary" style="width: 100%; display: block; text-align: center; margin-bottom: 0.5rem;" href="login.php">Sign In</a>
                    <a class="btn btn-primary" style="width: 100%; display: block; text-align: center;" href="finder.php">Find My Degree</a>
                </li>
                <li style="margin-top: 1rem;">
                    <button id="theme-toggle" class="btn-theme" aria-label="Toggle Dark Mode">🌙 Dark Mode</button>
                </li>
            </ul>
        </div>
    </div>
    <main class="site-main">
<?php // Begin page content ?>
