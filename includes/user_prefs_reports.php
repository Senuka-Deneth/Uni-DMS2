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
    .report-item {
        margin-bottom: 1.5rem;
    }

    .report-item:last-child {
        margin-bottom: 0;
    }

    .report-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--dark-900);
        margin-bottom: 0.5rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .report-title span {
        color: var(--dark-500);
        font-weight: 400;
    }

    .report-bar-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .report-bar-wrap {
        flex: 1;
        background: #eef2f6;
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
        color: var(--dark-600);
    }

    .report-gender-lbl {
        width: 36px;
        font-size: 0.75rem;
        color: var(--dark-400);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>

<section class="section-shell" style="padding-top: 3rem; background: var(--surface-light); padding-bottom: 5rem;">
    <div class="container">

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 2rem;">

            <div
                style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-light-md); border: 1px solid var(--light-300);">
                <h3 style="color: black; margin-bottom: 0.5rem; font-size: 1.5rem;"><i class="fas fa-chart-line"></i>
                    Stream Preferences</h3>
                <p style="font-size: 0.95rem; black; margin-bottom: 2rem;">Comparison between boys and
                    girls.</p>

                <div class="report-content">
                    <?php if (empty(array_filter(array_column($streamsData, 'boys'))) && empty(array_filter(array_column($streamsData, 'girls')))): ?>
                        <p style="color: var(--dark-400);">Not enough data yet.</p>
                    <?php else: ?>
                        <?php foreach ($streamsData as $s): ?>
                            <div class="report-item" title="<?php echo htmlspecialchars($s['name']); ?>">
                                <div class="report-title"><?php echo htmlspecialchars($s['name']); ?></div>

                                <div class="report-bar-row" style="margin-bottom: 6px;">
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

            <div
                style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-light-md); border: 1px solid var(--light-300);">
                <h3 style="color: black; margin-bottom: 0.5rem; font-size: 1.5rem;"><i class="fas fa-male"></i> Highest
                    Demanded Degrees: Boys</h3>
                <p style="font-size: 0.95rem; color: black; margin-bottom: 2rem;">Top 5 demanded degrees.</p>

                <div class="report-content">
                    <?php if (empty($degBoys)): ?>
                        <p style="color: var(--dark-400);">Not enough data yet.</p>
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

            <div
                style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-light-md); border: 1px solid var(--light-300);">
                <h3 style="color: black; margin-bottom: 0.5rem; font-size: 1.5rem;"><i class="fas fa-female"></i>
                    Highest Demanded Degrees: Girls</h3>
                <p style="font-size: 0.95rem; color: black; margin-bottom: 2rem;">Top 5 demanded degrees.</p>

                <div class="report-content">
                    <?php if (empty($degGirls)): ?>
                        <p style="color: var(--dark-400);">Not enough data yet.</p>
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

            <div
                style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-light-md); border: 1px solid var(--light-300);">
                <h3 style="color: black; margin-bottom: 0.5rem; font-size: 1.5rem;"><i class="fas fa-globe"></i> Highest
                    Demanded Degrees: Overall</h3>
                <p style="font-size: 0.95rem; color: black; margin-bottom: 2rem;">Top 5 demanded degrees.</p>

                <div class="report-content">
                    <?php if (empty($degOverall)): ?>
                        <p style="color: var(--dark-400);">Not enough data yet.</p>
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
                                        <div class="report-bar bg-green" style="width: <?php echo $d['percent']; ?>%;"></div>
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