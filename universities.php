<?php
require_once 'includes/db.php';
require_once 'includes/ui-helpers.php';

$pageTitle = 'Universities';
$pageStyles = ['css/pages/universities.css'];

$searchTerm = trim($_GET['search'] ?? '');
$perPage = 9;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;
$totalRecords = 0;
$universities = [];
$whereClause = '';
$params = [];
$paramTypes = '';

if ($searchTerm !== '') {
    $whereClause = ' WHERE name LIKE ? OR location LIKE ? OR description LIKE ?';
    $like = "%{$searchTerm}%";
    $params = [$like, $like, $like];
    $paramTypes = 'sss';
}

$countQuery = 'SELECT COUNT(*) AS total FROM universities' . $whereClause;
$countStmt = $conn->prepare($countQuery);
if ($countStmt && $params) {
    $countStmt->bind_param($paramTypes, ...$params);
}
if ($countStmt) {
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalRecords = (int) ($countResult->fetch_assoc()['total'] ?? 0);
    $countStmt->close();
}

$query = 'SELECT * FROM universities' . $whereClause . ' ORDER BY name ASC LIMIT ?, ?';
$stmt = $conn->prepare($query);
$bindTypes = $paramTypes . 'ii';
if ($stmt) {
    if ($params) {
        $allParams = array_merge($params, [$offset, $perPage]);
        $stmt->bind_param($bindTypes, ...$allParams);
    } else {
        $stmt->bind_param('ii', $offset, $perPage);
    }
    $stmt->execute();
    $universities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

foreach ($universities as &$university) {
    $uniName = $university['name'];

    $streamStmt = $conn->prepare('SELECT DISTINCT stream FROM university_degrees WHERE university = ?');
    $streams = [];
    if ($streamStmt) {
        $streamStmt->bind_param('s', $uniName);
        $streamStmt->execute();
        $streamResult = $streamStmt->get_result();
        while ($row = $streamResult->fetch_assoc()) {
            if (!empty($row['stream']) && $row['stream'] !== 'Other Programs') {
                $streams[] = $row['stream'];
            }
        }
        $streamStmt->close();
    }
    $university['stream_list'] = $streams ? implode(', ', $streams) : 'All';

    $degreeCountStmt = $conn->prepare('SELECT COUNT(*) AS total FROM university_degrees WHERE university = ?');
    $degreeCount = 0;
    if ($degreeCountStmt) {
        $degreeCountStmt->bind_param('s', $uniName);
        $degreeCountStmt->execute();
        $degreeCountResult = $degreeCountStmt->get_result();
        $degreeCount = (int) ($degreeCountResult->fetch_assoc()['total'] ?? 0);
        $degreeCountStmt->close();
    }
    $university['degree_count'] = $degreeCount;
}
unset($university);

$totalPages = $perPage > 0 ? max(1, (int) ceil($totalRecords / $perPage)) : 1;
$rankedIds = array_map(function ($item) {
    return $item['id'];
}, array_slice($universities, 0, 2));

include 'includes/header.php';
?>
<section class="page-hero reveal-on-scroll" aria-label="Universities hero">
    <div class="container">
        <p class="eyebrow">Universities</p>
        <h1>Universities in Sri Lanka</h1>
        <p class="page-hero-meta">Universities in Sri Lanka provide competitive, high-quality education that shapes skilled professionals and drives national development.
</p>
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <span>/</span>
            <span>Universities</span>
        </div>
    </div>
</section>

<section class="section-shell" aria-label="Search universities">
    <div class="container">
        <form class="search-panel reveal-on-scroll" method="GET" action="universities.php">
            <div class="search-input-wrapper">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input
                    type="search"
                    id="uniSearchInput"
                    name="search"
                    class="search-input"
                    placeholder="Search universities (e.g. 'Colombo', 'Peradeniya')..."
                    value="<?php echo htmlspecialchars($searchTerm); ?>">
            </div>
        </form>
        <?php if ($universities): ?>
            <div class="university-grid">
                <?php foreach ($universities as $university): ?>
                    <?php $image = getUniversityImagePath($university); ?>
                    <article class="university-card reveal-on-scroll" data-streams="<?php echo htmlspecialchars($university['stream_list']); ?>">
                        <div class="university-image">
                            <?php if ($image): ?>
                                <img src="<?php echo htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" alt="<?php echo htmlspecialchars($university['name']); ?>">
                            <?php else: ?>
                                <div class="university-image university-image--placeholder"></div>
                            <?php endif; ?>
                            <div class="image-overlay">
                                <div class="location-tag"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <?php echo htmlspecialchars(getUniversityLocation($university)); ?></div>
                                <?php if (!empty($university['type'])): ?>
                                    <span class="type-badge type-<?php echo strtolower($university['type']); ?>"><?php echo htmlspecialchars($university['type']); ?></span>
                                <?php else: ?>
                                    <span class="type-badge type-government">Government</span>
                                <?php endif; ?>
                                <div class="university-name"><?php echo htmlspecialchars($university['name']); ?></div>
                            </div>
                        </div>
                        <div class="university-content">
                            <p><?php echo htmlspecialchars(getUniversityDescription($university)); ?></p>
                            <div class="pills">
                                <span class="pill degree-pill"><?php echo $university['degree_count']; ?> Degrees</span>
                            <?php if (!empty($university['stream_list']) && $university['stream_list'] !== 'All'): ?>
                                <?php foreach (explode(', ', $university['stream_list']) as $stream): ?>
                                    <span class="pill"><?php echo htmlspecialchars($stream); ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </div>
                            <div class="program-cta">
                                <a class="btn btn-primary" href="university.php?id=<?php echo $university['id']; ?>">View Programs →</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-results">No universities match that search yet — try another keyword.</p>
        <?php endif; ?>

        <?php if ($totalPages > 1): ?>
            <div class="pagination reveal-on-scroll">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php
                    $query = ['page' => $i];
                    if ($searchTerm !== '') {
                        $query['search'] = $searchTerm;
                    }
                    $link = 'universities.php?' . http_build_query($query);
                    ?>
                    <?php if ($i === $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="<?php echo $link; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
