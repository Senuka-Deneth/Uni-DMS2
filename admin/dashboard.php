<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Dashboard';
include __DIR__ . '/includes/header.php';

$stats = [];
$queries = [
    'universities' => 'SELECT COUNT(*) AS total FROM universities',
    'degrees' => 'SELECT COUNT(*) AS total FROM degrees',
    'activities' => 'SELECT COUNT(*) AS total FROM extracurricular_activities',
];

foreach ($queries as $key => $sql) {
    $result = $conn->query($sql);
    if ($result) {
        $stats[$key] = (int) $result->fetch_assoc()['total'];
        $result->free_result();
    } else {
        $stats[$key] = 0;
    }
}
?>
<div class="admin-panel">
    <h1>Admin Dashboard</h1>
    <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Overview of the Uni-DMS directory.</p>
    <div class="stats-grid">
        <a href="universities.php" class="stats-card" style="text-decoration:none; color:inherit;">
            <span><?php echo $stats['universities']; ?></span>
            <p>Total Universities</p>
        </a>
        <a href="degrees.php" class="stats-card" style="text-decoration:none; color:inherit;">
            <span><?php echo $stats['degrees']; ?></span>
            <p>Total Degrees</p>
        </a>
        <a href="activities.php" class="stats-card" style="text-decoration:none; color:inherit;">
            <span><?php echo $stats['activities']; ?></span>
            <p>Extracurricular Programs</p>
        </a>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>