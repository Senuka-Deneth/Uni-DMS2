<?php
require_once 'includes/db.php';
require_once 'includes/ui-helpers.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$university = null;
$degrees = [];

if ($id) {
    $stmt = $conn->prepare('SELECT * FROM universities WHERE id = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && ($row = $result->fetch_assoc())) {
            $university = $row;
        }
        $stmt->close();
    }
}

if ($university) {
    $degreeStmt = $conn->prepare('SELECT d.name AS degree_name, d.duration, d.medium, d.description, f.name AS faculty_name FROM degrees d JOIN departments dep ON d.department_id = dep.id JOIN faculties f ON dep.faculty_id = f.id WHERE f.university_id = ? ORDER BY d.name ASC');
    if ($degreeStmt) {
        $degreeStmt->bind_param('i', $id);
        $degreeStmt->execute();
        $degrees = $degreeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $degreeStmt->close();
    }
}

include 'includes/header.php';
?>
<?php if ($university): ?>
    <section class="page-hero reveal-on-scroll detail-hero">
        <div class="container hero-detail">
            <div class="hero-text">
                <p class="eyebrow">University story</p>
                <h1><?php echo htmlspecialchars($university['name']); ?></h1>
                <p class="page-hero-meta"><?php echo htmlspecialchars(getUniversityDescription($university, 320)); ?></p>
                <div class="breadcrumb">
                    <a href="/index.php">Home</a>
                    <span> / </span>
                    <a href="/universities.php">Universities</a>
                    <span> / </span>
                    <span><?php echo htmlspecialchars($university['name']); ?></span>
                </div>
                <div class="pills">
                    <span class="pill"><?php echo htmlspecialchars(getUniversityType($university)); ?></span>
                    <span class="pill"><?php echo htmlspecialchars(getUniversityLocation($university)); ?></span>
                </div>
            </div>
            <div class="hero-media">
                <?php $heroImage = getUniversityImagePath($university); ?>
                <?php if ($heroImage): ?>
                    <img src="<?php echo htmlspecialchars($heroImage, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($university['name']); ?>" loading="lazy">
                <?php else: ?>
                    <div class="hero-media-placeholder"></div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="container reveal-on-scroll">
        <div class="glass-panel">
            <h2>Overview</h2>
            <p><?php echo htmlspecialchars($university['description']); ?></p>
            <?php if (!empty($university['website']) || !empty($university['contact']) || !empty($university['established_year'])): ?>
                <div class="detail-meta">
                    <div class="pills">
                        <?php if (!empty($university['established_year'])): ?><span class="pill">Est. <?php echo htmlspecialchars($university['established_year']); ?></span><?php endif; ?>
                        <?php if (!empty($university['website'])): ?><span class="pill"><a href="<?php echo htmlspecialchars($university['website']); ?>" target="_blank" rel="noreferrer">Website</a></span><?php endif; ?>
                    </div>
                    <?php if (!empty($university['contact'])): ?><p class="helper-text">Contact: <?php echo htmlspecialchars($university['contact']); ?></p><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="container reveal-on-scroll">
        <h2>Featured degrees</h2>
        <?php if ($degrees): ?>
            <div class="finder-results">
                <?php foreach ($degrees as $degree): ?>
                    <article class="finder-card reveal-on-scroll">
                        <h3><?php echo htmlspecialchars($degree['degree_name']); ?></h3>
                        <p><strong>Faculty:</strong> <?php echo htmlspecialchars($degree['faculty_name']); ?></p>
                        <p><strong>Duration:</strong> <?php echo htmlspecialchars($degree['duration']); ?></p>
                        <p><strong>Medium:</strong> <?php echo htmlspecialchars($degree['medium']); ?></p>
                        <p><?php echo htmlspecialchars($degree['description']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="section-subtitle">No cataloged degrees are visible right now. Check back soon.</p>
        <?php endif; ?>
    </section>
<?php else: ?>
    <section class="page-hero reveal-on-scroll">
        <div class="container">
            <p class="eyebrow">University not found</p>
            <h1>We could not locate that university.</h1>
            <p class="page-hero-meta">Return to the directory and continue your exploration.</p>
            <div class="breadcrumb">
                <a href="/index.php">Home</a>
                <span> / </span>
                <a href="/universities.php">Universities</a>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>