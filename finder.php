<?php
require_once 'includes/db.php';

$pageTitle = 'Z-Score Finder';
$pageStyles = ['css/pages/finder.css'];

$degrees = [];
$error = '';
$allowedStreams = ['Maths','Bio','Commerce','Arts'];
$stream = $_POST['stream'] ?? $allowedStreams[0];
if (!in_array($stream, $allowedStreams, true)) {
    $stream = $allowedStreams[0];
}
$submittedZscore = $_POST['zscore'] ?? '';
$zscore = is_numeric($submittedZscore) ? floatval($submittedZscore) : 0;
$sliderValue = $zscore > 0 ? $zscore : 3.0;
$sliderValueFormatted = number_format($sliderValue, 3, '.', '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($zscore > 0 && in_array($stream, $allowedStreams, true)) {
        $stmt = $conn->prepare("SELECT d.name AS degree_name, u.name AS university_name, u.id AS university_id, z.cutoff, d.duration, d.medium, d.description FROM degrees d JOIN departments dep ON d.department_id = dep.id JOIN faculties f ON dep.faculty_id = f.id JOIN universities u ON f.university_id = u.id JOIN zscore_cutoffs z ON d.id = z.degree_id WHERE z.stream = ? AND z.cutoff <= ? ORDER BY z.cutoff DESC");
        $stmt->bind_param('sd', $stream, $zscore);
        $stmt->execute();
        $degrees = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        $error = 'Please enter a valid Z-score and select a stream.';
    }
}

include 'includes/header.php';
?>
<section class="page-hero reveal-on-scroll" aria-label="Finder hero">
    <div class="container">
        <p class="eyebrow">Z-Score Finder</p>
        <h1>Z-Score Degree Finder</h1>
        <p class="page-hero-meta">Match your A/L results to degrees where your stream, grit, and ambition align.</p>
    </div>
</section>

<section class="section-shell" aria-label="Finder wizard">
    <div class="container">
        <?php if ($error): ?>
            <div class="alert-panel reveal-on-scroll">
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>
        <form class="finder-stage reveal-on-scroll" method="POST" action="finder.php">
            <div class="finder-progress">
                <?php
                $progressConfig = [
                    ['label' => 'Set a target', 'active' => true],
                    ['label' => 'Choose a stream', 'active' => !empty($stream)],
                    ['label' => 'See results', 'active' => $_SERVER['REQUEST_METHOD'] === 'POST'],
                ];
                foreach ($progressConfig as $index => $step):
                ?>
                    <div class="progress-step<?php echo $step['active'] ? ' is-active' : ''; ?>">
                        <strong><?php echo $index + 1; ?></strong>
                        <span><?php echo htmlspecialchars($step['label']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="slider-wrapper">
                <label for="zscoreRange">Target Z-score</label>
                <input type="range" class="range-slider" id="zscoreRange" min="0" max="4" step="0.001" value="<?php echo htmlspecialchars($sliderValueFormatted); ?>">
                <div class="range-value">
                    <span>Current target:</span>
                    <strong id="zscoreValue"><?php echo htmlspecialchars($sliderValueFormatted); ?></strong>
                </div>
                <input type="hidden" id="zscoreInput" name="zscore" value="<?php echo htmlspecialchars($sliderValueFormatted); ?>">
            </div>
            <div class="form-field">
                <label>Stream</label>
                <div class="stream-grid">
                    <?php foreach ($allowedStreams as $option): ?>
                        <?php $isActive = $stream === $option; ?>
                        <button type="button" class="stream-tile<?php echo $isActive ? ' is-active' : ''; ?>" data-value="<?php echo htmlspecialchars($option); ?>" aria-pressed="<?php echo $isActive ? 'true' : 'false'; ?>">
                            <?php echo htmlspecialchars($option); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="stream" id="streamInput" value="<?php echo htmlspecialchars($stream); ?>">
            </div>
            <div class="finder-actions">
                <button type="submit" class="btn btn-primary">Find Degrees</button>
                <button type="button" class="btn btn-ghost" data-reset-slider>Reset score</button>
            </div>
        </form>
    </div>
</section>

<section class="section-shell" aria-label="Finder results">
    <div class="container">
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="finder-results">
                <?php if ($degrees): ?>
                    <?php foreach ($degrees as $deg): ?>
                        <article class="finder-card reveal-on-scroll">
                            <div class="finder-card-head">
                                <h3><?php echo htmlspecialchars($deg['degree_name']); ?></h3>
                                <span class="zscore-badge">Z-Score <?php echo htmlspecialchars($deg['cutoff']); ?></span>
                            </div>
                            <div class="finder-card-meta">
                                <span><strong>University:</strong> <?php echo htmlspecialchars($deg['university_name']); ?></span>
                                <span><strong>Cutoff:</strong> <?php echo htmlspecialchars($deg['cutoff']); ?></span>
                            </div>
                            <p><strong>Duration:</strong> <?php echo htmlspecialchars($deg['duration']); ?></p>
                            <p><strong>Medium:</strong> <?php echo htmlspecialchars($deg['medium']); ?></p>
                            <p><?php echo htmlspecialchars($deg['description']); ?></p>
                            <?php if (!empty($deg['university_id'])): ?>
                                <a class="btn btn-ghost" href="university.php?id=<?php echo $deg['university_id']; ?>">View Details →</a>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="section-subtitle">No matching degrees found yet; relax the cutoff or pick another stream.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
