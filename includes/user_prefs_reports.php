<?php
require_once "includes/db.php";

$totO_res = $conn->query("SELECT COUNT(*) as c FROM user_preferences");
$totO = ($r = $totO_res->fetch_assoc()) && $r['c'] > 0 ? $r['c'] : 1;

$totB_res = $conn->query("SELECT COUNT(*) as c FROM user_preferences WHERE gender='Male'");
$totB = ($r = $totB_res->fetch_assoc()) && $r['c'] > 0 ? $r['c'] : 1;

$totG_res = $conn->query("SELECT COUNT(*) as c FROM user_preferences WHERE gender='Female'");
$totG = ($r = $totG_res->fetch_assoc()) && $r['c'] > 0 ? $r['c'] : 1;

// 1. Stream Preferences
$streamsData = [];
$streamRes = $conn->query("SELECT DISTINCT stream FROM user_preferences WHERE stream IS NOT NULL AND stream != ''");
$allStreams = [];
if ($streamRes) {
    while ($r = $streamRes->fetch_assoc()) {
        $allStreams[] = $r['stream'];
    }
}
if (empty($allStreams)) {
    $allStreams = ['Physical Science', 'Biological Science', 'Commerce', 'Arts', 'Engineering Technology', 'Biosystems Technology'];
}

foreach ($allStreams as $s) {
    $sb = $conn->query("SELECT COUNT(*) as c FROM user_preferences WHERE stream='" . $conn->real_escape_string($s) . "' AND gender='Male'")->fetch_assoc()['c'];
    $bPct = round(($sb / $totB) * 100, 1);

    $sg = $conn->query("SELECT COUNT(*) as c FROM user_preferences WHERE stream='" . $conn->real_escape_string($s) . "' AND gender='Female'")->fetch_assoc()['c'];
    $gPct = round(($sg / $totG) * 100, 1);

    $streamsData[] = [
        'name' => $s,
        'boys' => $bPct,
        'girls' => $gPct
    ];
}

// Function to get top degrees with university
function getTopDegreesData($conn, $gender, $totalCount)
{
    $data = [];
    $where = $gender ? "WHERE gender='" . $conn->real_escape_string($gender) . "'" : "";
    $q = "SELECT degree, university, COUNT(*) as c FROM user_preferences $where GROUP BY degree, university ORDER BY c DESC LIMIT 5";
    $res = $conn->query($q);
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $data[] = [
                'degree' => $r['degree'],
                'university' => $r['university'],
                'percent' => round(($r['c'] / $totalCount) * 100, 1)
            ];
        }
    }
    return $data;
}

$degBoys = getTopDegreesData($conn, 'Male', $totB);
$degGirls = getTopDegreesData($conn, 'Female', $totG);
$degOverall = getTopDegreesData($conn, null, $totO);
?>

<style>
    .reports-section {
        padding-top: 3rem;
        padding-bottom: 5rem;
        background: #ffffff !important;
    }

    .report-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: var(--space-8);
    }

    .report-card {
        display: flex;
        flex-direction: column;
        gap: var(--space-6);
        min-height: 100%;
        padding: var(--space-8);
        border-radius: var(--radius-xl);
        border: 1px solid rgba(0, 0, 0, 0.1);
        background: #ffffff !important;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .report-card__title {
        margin: 0;
        font-size: clamp(1.2rem, 2vw, 1.45rem);
        color: #000000 !important;
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }

    .report-card__subtitle {
        margin: 0;
        font-size: var(--text-sm);
        color: #555555 !important;
    }

    .report-content {
        display: flex;
        flex-direction: column;
        gap: var(--space-4);
    }

    .report-item {
        margin-bottom: var(--space-4);
    }

    .report-item:last-child {
        margin-bottom: 0;
    }

    .report-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #000000 !important;
        margin-bottom: 0.5rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .report-title span {
        color: #555555 !important;
        font-weight: 400;
    }

    .report-bar-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .report-bar-row--spaced {
        margin-bottom: 6px;
    }

    .report-bar-wrap {
        flex: 1;
        background: #e9ecef;
        height: 10px;
        border-radius: 6px;
        overflow: hidden;
    }

    .report-bar {
        height: 100%;
        border-radius: 6px;
        transition: width 1s ease-out;
    }

    .bg-pink {
        background: rgba(236, 72, 153, 0.85);
    }

    .bg-blue {
        background: rgba(59, 130, 246, 0.85);
    }

    .bg-green {
        background: rgba(16, 185, 129, 0.85);
    }

    .report-pct {
        width: 40px;
        font-size: 0.8rem;
        text-align: right;
        font-weight: bold;
        color: #000000 !important;
    }

    .report-gender-lbl {
        width: 36px;
        font-size: 0.75rem;
        color: #555555 !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .report-empty {
        margin: 0;
        color: #555555 !important;
    }

    @media (max-width: 768px) {
        .report-grid {
            grid-template-columns: 1fr;
        }

        .report-card {
            padding: var(--space-6);
        }
    }
</style>

<section class="section-shell reports-section">
    <div class="container">

        <div class="report-grid">

            <div class="report-card">
                <h3 class="report-card__title"><i class="fas fa-chart-line"></i>
                    Stream Preferences</h3>
                <p class="report-card__subtitle">Comparison between boys and
                    girls.</p>

                <div class="report-content">
                    <?php if (empty(array_filter(array_column($streamsData, 'boys'))) && empty(array_filter(array_column($streamsData, 'girls')))): ?>
                        <p class="report-empty">Not enough data yet.</p>
                    <?php else: ?>
                        <?php foreach ($streamsData as $s): ?>
                            <div class="report-item" title="<?php echo htmlspecialchars($s['name']); ?>">
                                <div class="report-title"><?php echo htmlspecialchars($s['name']); ?></div>

                                <div class="report-bar-row report-bar-row--spaced">
                                    <div class="report-gender-lbl">Boys</div>
                                    <div class="report-bar-wrap">
                                        <div class="report-bar bg-blue" style="width: <?php echo $s['boys']; ?>%;"></div>
                                    </div>
                                    <div class="report-pct"><?php echo $s['boys']; ?>%</div>
                                </div>

                                <div class="report-bar-row">
                                    <div class="report-gender-lbl">Girls</div>
                                    <div class="report-bar-wrap">
                                        <div class="report-bar bg-pink" style="width: <?php echo $s['girls']; ?>%;"></div>
                                    </div>
                                    <div class="report-pct"><?php echo $s['girls']; ?>%</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="report-card">
                <h3 class="report-card__title"><i class="fas fa-male"></i> Highest
                    Demanded Degrees: Boys</h3>
                <p class="report-card__subtitle">Top 5 demanded degrees.</p>

                <div class="report-content">
                    <?php if (empty($degBoys)): ?>
                        <p class="report-empty">Not enough data yet.</p>
                    <?php else: ?>
                        <?php foreach ($degBoys as $d): ?>
                            <div class="report-item"
                                title="<?php echo htmlspecialchars($d['degree'] . ' : ' . $d['university']); ?>">
                                <div class="report-title">
                                    <?php echo htmlspecialchars($d['degree']); ?> :
                                    <span><?php echo htmlspecialchars($d['university']); ?></span>
                                </div>
                                <div class="report-bar-row">
                                    <div class="report-bar-wrap">
                                        <div class="report-bar bg-blue" style="width: <?php echo $d['percent']; ?>%;"></div>
                                    </div>
                                    <div class="report-pct"><?php echo $d['percent']; ?>%</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="report-card">
                <h3 class="report-card__title"><i class="fas fa-female"></i>
                    Highest Demanded Degrees: Girls</h3>
                <p class="report-card__subtitle">Top 5 demanded degrees.</p>

                <div class="report-content">
                    <?php if (empty($degGirls)): ?>
                        <p class="report-empty">Not enough data yet.</p>
                    <?php else: ?>
                        <?php foreach ($degGirls as $d): ?>
                            <div class="report-item"
                                title="<?php echo htmlspecialchars($d['degree'] . ' : ' . $d['university']); ?>">
                                <div class="report-title">
                                    <?php echo htmlspecialchars($d['degree']); ?> :
                                    <span><?php echo htmlspecialchars($d['university']); ?></span>
                                </div>
                                <div class="report-bar-row">
                                    <div class="report-bar-wrap">
                                        <div class="report-bar bg-pink" style="width: <?php echo $d['percent']; ?>%;"></div>
                                    </div>
                                    <div class="report-pct"><?php echo $d['percent']; ?>%</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="report-card">
                <h3 class="report-card__title"><i class="fas fa-globe"></i> Highest
                    Demanded Degrees: Overall</h3>
                <p class="report-card__subtitle">Top 5 demanded degrees.</p>

                <div class="report-content">
                    <?php if (empty($degOverall)): ?>
                        <p class="report-empty">Not enough data yet.</p>
                    <?php else: ?>
                        <?php foreach ($degOverall as $d): ?>
                            <div class="report-item"
                                title="<?php echo htmlspecialchars($d['degree'] . ' : ' . $d['university']); ?>">
                                <div class="report-title">
                                    <?php echo htmlspecialchars($d['degree']); ?> :
                                    <span><?php echo htmlspecialchars($d['university']); ?></span>
                                </div>
                                <div class="report-bar-row">
                                    <div class="report-bar-wrap">
                                        <div class="report-bar bg-blue" style="width: <?php echo $d['percent']; ?>%;"></div>
                                    </div>
                                    <div class="report-pct"><?php echo $d['percent']; ?>%</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</section>