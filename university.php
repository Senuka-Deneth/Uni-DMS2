<?php
require_once 'includes/db.php';
require_once 'includes/ui-helpers.php';

$pageStyles = ['css/pages/universities.css'];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$university = null;
$degrees = [];

$groupedDegrees = [];

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
    $uniName = $university['name'];
    $degreeStmt = $conn->prepare('
        SELECT DISTINCT 
            fz.degree_name, 
            fz.subject1, 
            fz.subject2, 
            fz.subject3,
            d.duration,
            d.medium,
            d.description,
            fac.name AS faculty_name
        FROM flat_zscores fz
        LEFT JOIN degrees d ON d.name = fz.degree_name
        LEFT JOIN departments dep ON d.department_id = dep.id
        LEFT JOIN faculties fac ON dep.faculty_id = fac.id AND fac.university_id = ?
        WHERE fz.university_name = ?
        ORDER BY fz.degree_name ASC
    ');

    if ($degreeStmt) {
        $degreeStmt->bind_param('is', $id, $uniName);
        $degreeStmt->execute();
        $degrees = $degreeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $degreeStmt->close();

        foreach ($degrees as $deg) {
            $sub1 = strtoupper($deg['subject1'] ?? '');
            $sub2 = strtoupper($deg['subject2'] ?? '');
            $sub3 = strtoupper($deg['subject3'] ?? '');
            $degUpper = strtoupper($deg['degree_name']);
            $subs = $sub1 . " " . $sub2 . " " . $sub3;
            
            $stream = 'Other Programs';
            if (strpos($subs, 'COMBINED MATHEMATICS') !== false) {
                $stream = 'Maths';
            } elseif (strpos($subs, 'BIOLOGY') !== false) {
                $stream = 'Bio';
            } elseif (strpos($subs, 'ACCOUNTING') !== false || strpos($subs, 'BUSINESS') !== false || strpos($subs, 'ECONOMICS') !== false || strpos($degUpper, 'MANAGEMENT') !== false || strpos($degUpper, 'COMMERCE') !== false) {
                $stream = 'Commerce';
            } elseif (strpos($subs, 'ANY') !== false || strpos($degUpper, 'ARTS') !== false || strpos($degUpper, 'LANGUAGES') !== false) {
                $stream = 'Arts';
            } else {
                if (strpos($degUpper, 'MEDICINE') !== false || strpos($degUpper, 'DENTAL') !== false || strpos($degUpper, 'BIOLOGICAL') !== false) {
                    $stream = 'Bio';
                } elseif (strpos($degUpper, 'ENGINEERING') !== false || strpos($degUpper, 'PHYSICAL') !== false || strpos($degUpper, 'ICT') !== false || strpos($degUpper, 'COMPUTER') !== false) {
                    $stream = 'Maths';
                }
            }
            
            if (!isset($groupedDegrees[$stream])) {
                $groupedDegrees[$stream] = [];
            }
            $groupedDegrees[$stream][] = $deg;
        }
    }
}

include 'includes/header.php';
?>
<?php if ($university): ?>
    <section class="page-hero reveal-on-scroll detail-hero">
        <div class="container">
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
        <?php if (!empty($groupedDegrees)): ?>
            <?php foreach ($groupedDegrees as $streamName => $streamDegrees): ?>
                <div class="stream-group">
                    <h3 class="stream-title" style="margin-top: 2rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem; display: inline-block;">
                        <?php echo htmlspecialchars($streamName); ?>
                    </h3>
                    <div class="finder-results" style="margin-top: 1rem;">
                        <?php foreach ($streamDegrees as $degree): ?>
                            <article class="finder-card reveal-on-scroll">
                                <h3><?php echo htmlspecialchars($degree['degree_name']); ?></h3>
                                <?php if (!empty($degree['faculty_name'])): ?>
                                    <p><strong>Faculty:</strong> <?php echo htmlspecialchars($degree['faculty_name']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($degree['duration'])): ?>
                                    <p><strong>Duration:</strong> <?php echo htmlspecialchars($degree['duration']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($degree['medium'])): ?>
                                    <p><strong>Medium:</strong> <?php echo htmlspecialchars($degree['medium']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($degree['description'])): ?>
                                    <p><?php echo htmlspecialchars($degree['description']); ?></p>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
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