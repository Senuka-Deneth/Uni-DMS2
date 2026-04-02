<?php
require_once "includes/db.php";

$pageTitle = "Z-Score Finder";
$pageStyles = ["css/pages/finder.css"];

$degrees = [];
$error = "";

$subjects = ["Combined Mathematics", "Physics", "Chemistry", "ICT", "Biology"];
$districts = ["Colombo", "Gampaha", "Galle", 'All'];

$sub1 = $_POST["subject1"] ?? "";
$sub2 = $_POST["subject2"] ?? "";
$sub3 = $_POST["subject3"] ?? "";
$district = $_POST["district"] ?? "";

$submittedZscore = $_POST["zscore"] ?? "";
$zscore = is_numeric($submittedZscore) ? floatval($submittedZscore) : 0;
$sliderValue = $zscore > 0 ? $zscore : 3.0;
$sliderValueFormatted = number_format($sliderValue, 3, ".", "");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($zscore > 0 && $sub1 && $sub2 && $sub3 && $district) {
        $stmt = $conn->prepare("
            SELECT d.name AS degree_name, u.name AS university_name, u.id AS university_id, 
                   z.cutoff, d.duration, d.medium, d.description 
            FROM degrees d 
            JOIN departments dep ON d.department_id = dep.id 
            JOIN faculties f ON dep.faculty_id = f.id 
            JOIN universities u ON f.university_id = u.id 
            JOIN zscore_cutoffs z ON d.id = z.degree_id 
            WHERE 
            (
                (z.subject1 = ? AND z.subject2 = ? AND z.subject3 = ?) OR 
                (z.subject1 = ? AND z.subject3 = ? AND z.subject2 = ?) OR
                (z.subject2 = ? AND z.subject1 = ? AND z.subject3 = ?) OR
                (z.subject2 = ? AND z.subject3 = ? AND z.subject1 = ?) OR
                (z.subject3 = ? AND z.subject1 = ? AND z.subject2 = ?) OR
                (z.subject3 = ? AND z.subject2 = ? AND z.subject1 = ?)
            )
            AND (z.district = 'All' OR z.district = ?) 
            AND z.cutoff <= ? 
            ORDER BY z.cutoff DESC
        ");
        
        $stmt->bind_param(
            "sssssssssssssssssssd", 
            $sub1, $sub2, $sub3, 
            $sub1, $sub3, $sub2,
            $sub2, $sub1, $sub3,
            $sub2, $sub3, $sub1,
            $sub3, $sub1, $sub2,
            $sub3, $sub2, $sub1,
            $district, 
            $zscore
        );
        
        $stmt->execute();
        $degrees = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        $error = "Please enter a valid Z-score, select all 3 subjects and a district.";
    }
}

include "includes/header.php";
?>
<section class="page-hero reveal-on-scroll" aria-label="Finder hero">
    <div class="container">
        <p class="eyebrow">Z-Score Finder</p>
        <h1>Z-Score Degree Finder</h1>
        <p class="page-hero-meta">Match your A/L results to degrees according to your subject combination and district.</p>
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
                    ["label" => "Select Subjects", "active" => !empty($sub1)],
                    ["label" => "Select District", "active" => !empty($district)],
                    ["label" => "Set Target", "active" => $zscore > 0],
                    ["label" => "See results", "active" => $_SERVER["REQUEST_METHOD"] === "POST"],
                ];
                foreach ($progressConfig as $index => $step):
                ?>
                    <div class="progress-step<?php echo $step["active"] ? " is-active" : ""; ?>">
                        <strong><?php echo $index + 1; ?></strong>
                        <span><?php echo htmlspecialchars($step["label"]); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div style="display:flex; gap:16px; margin: 16px 0;">
                <div class="form-field" style="flex:1;">
                    <label>Subject 1</label>
                    <select name="subject1" required style="width:100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                        <option value="">Select Subject 1</option>
                        <?php foreach($subjects as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo $sub1===$s ? "selected" : ""; ?>><?php echo $s; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-field" style="flex:1;">
                    <label>Subject 2</label>
                    <select name="subject2" required style="width:100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                        <option value="">Select Subject 2</option>
                        <?php foreach($subjects as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo $sub2===$s ? "selected" : ""; ?>><?php echo $s; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-field" style="flex:1;">
                    <label>Subject 3</label>
                    <select name="subject3" required style="width:100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                        <option value="">Select Subject 3</option>
                        <?php foreach($subjects as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo $sub3===$s ? "selected" : ""; ?>><?php echo $s; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-field" style="margin-bottom:16px;">
                <label>District</label>
                <select name="district" required style="width:100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                    <option value="">Select District</option>
                    <?php foreach($districts as $d): ?>
                        <option value="<?php echo $d; ?>" <?php echo $district===$d ? "selected" : ""; ?>><?php echo $d; ?></option>
                    <?php endforeach; ?>
                </select>
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
            
            <div class="finder-actions">
                <button type="submit" class="btn btn-primary">Find Degrees</button>
                <button type="button" class="btn btn-ghost" data-reset-slider>Reset score</button>
            </div>
        </form>
    </div>
</section>

<section class="section-shell" aria-label="Finder results">
    <div class="container">
        <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
            <div class="finder-results">
                <?php if ($degrees): ?>
                    <?php foreach ($degrees as $deg): ?>
                        <article class="finder-card reveal-on-scroll">
                            <div class="finder-card-head">
                                <h3><?php echo htmlspecialchars($deg["degree_name"]); ?></h3>
                                <span class="zscore-badge">Z-Score cutoff: <?php echo htmlspecialchars($deg["cutoff"]); ?></span>
                            </div>
                            <div class="finder-card-meta">
                                <span><strong>University:</strong> <?php echo htmlspecialchars($deg["university_name"]); ?></span>
                            </div>
                            <?php if(!empty($deg["duration"])): ?>
                            <p><strong>Duration:</strong> <?php echo htmlspecialchars($deg["duration"]); ?></p>
                            <?php endif; ?>
                            <?php if(!empty($deg["medium"])): ?>
                            <p><strong>Medium:</strong> <?php echo htmlspecialchars($deg["medium"]); ?></p>
                            <?php endif; ?>
                            <p><?php echo htmlspecialchars($deg["description"]); ?></p>
                            <?php if (!empty($deg["university_id"])): ?>
                                <a class="btn btn-ghost" href="university.php?id=<?php echo $deg["university_id"]; ?>">View Details &rarr;</a>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="section-subtitle">No matching degrees found for your subject combination and district. Relax the cutoff or verify your inputs.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include "includes/footer.php"; ?>
