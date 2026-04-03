<?php
require_once "includes/db.php";
require_once "includes/require_user_details.php";

$pageTitle = "Analytics & Reports";
include "includes/header.php";
?>

<section class="page-hero reveal-on-scroll" aria-label="Reports hero" style="background-color: var(--dark-950) !important;">
    <div class="container text-center">
        <p class="eyebrow">Student Preferences</p>
        <h1>Analytics & Insights</h1>
        <p class="page-hero-meta">Explore data-driven distributions of preferred university streams and the highest demanded degrees.</p>
    </div>
</section>

<?php include 'includes/user_prefs_reports.php'; ?>

<?php include "includes/footer.php"; ?>