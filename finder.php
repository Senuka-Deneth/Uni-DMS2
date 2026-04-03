<?php
require_once "includes/db.php";
require_once "includes/require_user_details.php";

$pageTitle = "Find My Degree";
$pageStyles = ["css/pages/finder.css"];

$degrees = [];
$error = "";
$searchMode = ""; // "zscore" or "name"

$subjects = ["Combined Mathematics", "Physics", "Chemistry", "ICT", "Biology", "Accounting", "Business Statistics", "Economics", "Any"];
$districts = ["COLOMBO", "GAMPAHA", "KALUTARA", "MATALE", "KANDY", "NUWARA ELIYA", "GALLE", "MATARA", "HAMBANTOTA", "JAFFNA", "KILINOCHCHI", "MANNAR", "MULLAITIVU", "VAVUNIYA", "TRINCOMALEE", "BATTICALOA", "AMPARA", "PUTTALAM", "KURUNEGALA", "ANURADHAPURA", "POLONNARUWA", "BADULLA", "MONARAGALA", "All Island Ranges"];

// Fetch distinct universities and degree names from flat_zscores
$uniList = [];
$degreeNamesList = [];
$res = $conn->query("SELECT DISTINCT university_name FROM flat_zscores ORDER BY university_name ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $uniList[] = $row['university_name'];
    }
}
$resDeg = $conn->query("SELECT DISTINCT degree_name FROM flat_zscores ORDER BY degree_name ASC");
if ($resDeg) {
    while ($row = $resDeg->fetch_assoc()) {
        $degreeNamesList[] = $row['degree_name'];
    }
}

$sub1 = $_POST["subject1"] ?? "";
$sub2 = $_POST["subject2"] ?? "";
$sub3 = $_POST["subject3"] ?? "";
$district = $_POST["district"] ?? "";

$degreeSearchName = $_POST["search_degree_name"] ?? "";
$searchUniversity = $_POST["search_university"] ?? "";

$submittedZscore = $_POST["zscore"] ?? "";
$zscore = is_numeric($submittedZscore) ? floatval($submittedZscore) : 0;
$sliderValue = $zscore > 0 ? $zscore : 3.0;
$sliderValueFormatted = number_format($sliderValue, 3, ".", "");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["zscore_search"])) {
        $searchMode = "zscore";
        if ($zscore > 0 && $sub1 && $sub2 && $sub3 && $district) {
            $dCol = strtolower(str_replace(' ', '_', $district));
            $stmt = $conn->prepare("
                SELECT f.degree_name, f.university_name, 
                       f.`$dCol` as cutoff,
                       0 AS university_id, '' AS duration, '' AS medium, '' AS description
                FROM flat_zscores f 
                WHERE 
                (
                    (? IN (f.subject1, f.subject2, f.subject3) OR f.subject1 = 'Any') AND
                    (? IN (f.subject1, f.subject2, f.subject3) OR f.subject1 = 'Any') AND
                    (? IN (f.subject1, f.subject2, f.subject3) OR f.subject1 = 'Any')
                )
                AND f.`$dCol` <= ? AND f.`$dCol` IS NOT NULL
                ORDER BY f.`$dCol` DESC
            ");
            
            $stmt->bind_param(
                "sssd", 
                $sub1, $sub2, $sub3, 
                $zscore
            );
            
            $stmt->execute();
            $degrees = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        } else {
            $error = "Please enter a valid Z-score, select all 3 subjects and a district.";
        }
    } elseif (isset($_POST["name_search"])) {
        $searchMode = "name";
        
        $hasDegree = !empty(trim($degreeSearchName));
        $hasUni = !empty($searchUniversity);
        
        if ($hasDegree || $hasUni) {
            $query = "
                SELECT f.*, 
                       MAX(d.duration) as duration, 
                       MAX(d.medium) as medium, 
                       MAX(d.description) as description,
                       MAX(d.department_id) as dept_id
                FROM flat_zscores f 
                LEFT JOIN degrees d ON f.degree_name = d.name
                WHERE 1=1 ";
            
            $types = "";
            $params = [];
            
            if ($hasDegree) {
                $query .= " AND f.degree_name LIKE ?";
                $types .= "s";
                $params[] = "%" . trim($degreeSearchName) . "%";
            }
            
            if ($hasUni) {
                $query .= " AND f.university_name = ?";
                $types .= "s";
                $params[] = trim($searchUniversity);
            }
            
            $query .= " GROUP BY f.id ORDER BY f.degree_name ASC";
            
            $stmt = $conn->prepare($query);
            if (!empty($types)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $degrees = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        } else {
            $error = "Please enter a degree name or select a university.";
        }
    }
}

include "includes/header.php";
?>
<section class="page-hero reveal-on-scroll" aria-label="Finder hero">
    <div class="container">
        <p class="eyebrow">Find My Degree</p>
        <h1>Find My Degree</h1>
        <p class="page-hero-meta">Match your A/L results to degrees according to your subject combination and district, or search by degree name directly.</p>
    </div>
</section>

<section class="section-shell" aria-label="Finder wizard">
    <div class="container">
        <?php if ($error): ?>
            <div class="alert-panel reveal-on-scroll">
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>
        
        <div class="finder-forms-grid">
            
            <!-- Z-Score Search Form -->
            <form class="finder-stage reveal-on-scroll" method="POST" action="finder.php#results">
                <input type="hidden" name="zscore_search" value="1">
                <div class="finder-stage__header">
                    <h3 class="finder-stage__title">Search by Z-Score &amp; Subjects</h3>
                    <p class="finder-stage__description">Match your subject combination and district to find the degrees that fit your score.</p>
                </div>
                
                <div class="form-field">
                    <label>Subject 1</label>
                    <select name="subject1" class="form-input" required>
                        <option value="">Select Subject 1</option>
                        <?php foreach($subjects as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo $sub1===$s ? "selected" : ""; ?>><?php echo $s; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-field">
                    <label>Subject 2</label>
                    <select name="subject2" class="form-input" required>
                        <option value="">Select Subject 2</option>
                        <?php foreach($subjects as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo $sub2===$s ? "selected" : ""; ?>><?php echo $s; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-field">
                    <label>Subject 3</label>
                    <select name="subject3" class="form-input" required>
                        <option value="">Select Subject 3</option>
                        <?php foreach($subjects as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo $sub3===$s ? "selected" : ""; ?>><?php echo $s; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-field">
                    <label>District</label>
                    <select name="district" class="form-input" required>
                        <option value="">Select District</option>
                        <?php foreach($districts as $d): ?>
                            <option value="<?php echo $d; ?>" <?php echo $district===$d ? "selected" : ""; ?>><?php echo $d; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="slider-wrapper">
                    <label for="zscoreRange">Your Z-Score</label>
                    <div style="display:flex; align-items:center; gap:16px;">
                        <input type="range" class="range-slider" id="zscoreRange" min="0" max="4" step="0.001" value="<?php echo htmlspecialchars($sliderValueFormatted); ?>" style="flex:1;">
                        <input type="number" id="zscoreInput" name="zscore" min="0" max="4" step="0.001" class="form-input" value="<?php echo htmlspecialchars($sliderValueFormatted); ?>" style="width:100px;" required>
                    </div>
                </div>
                <!-- Remove the old hidden input and strong text logic, simply adding event listeners to sync them -->
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const slider = document.getElementById('zscoreRange');
                        const input = document.getElementById('zscoreInput');
                        if(slider && input) {
                            slider.addEventListener('input', function() {
                                input.value = slider.value;
                            });
                            input.addEventListener('input', function() {
                                slider.value = input.value;
                            });
                        }
                    });
                </script>
                
                <div class="finder-actions">
                    <button type="submit" class="btn btn-primary">Find Degrees</button>
                    <button type="button" class="btn btn-ghost" onclick="resetZscoreForm()">Reset</button>
                </div>
            </form>
            
            <!-- Degree Name Search Form -->
            <form class="finder-stage reveal-on-scroll" method="POST" action="finder.php#results">
                <input type="hidden" name="name_search" value="1">
                <div class="finder-stage__header">
                    <h3 class="finder-stage__title">Search Degree</h3>
                    <p class="finder-stage__description">Look up a degree directly by name or narrow it down by university.</p>
                </div>
                
                <div class="form-field">
                    <label>Degree Name</label>
                    <input type="text" id="searchInput" name="search_degree_name" list="degreeNameOptions" class="form-input" placeholder="Optional if university is selected" value="<?php echo htmlspecialchars($degreeSearchName); ?>">
                    <datalist id="degreeNameOptions">
                        <?php foreach($degreeNamesList as $dn): ?>
                            <option value="<?php echo htmlspecialchars($dn); ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
                
                <div class="form-field">
                    <label>University (Optional)</label>
                    <select name="search_university" class="form-input">
                        <option value="">All Universities</option>
                        <?php foreach($uniList as $uni): ?>
                            <option value="<?php echo htmlspecialchars($uni); ?>" <?php echo $searchUniversity === $uni ? "selected" : ""; ?>><?php echo htmlspecialchars($uni); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="finder-actions">
                    <button type="submit" class="btn btn-primary">Search Degree</button>
                    <button type="button" class="btn btn-ghost" onclick="resetNameSearch()">Reset</button>
                </div>
            </form>
            
        </div>
    </div>
</section>

<section class="section-shell" aria-label="Finder results" id="results">
    <div class="container">
        <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
            <div class="finder-results">
                <?php if ($degrees): ?>
                    <div class="finder-results-scroll">
                        <?php if ($searchMode === 'name'): ?>
                            <?php foreach ($degrees as $index => $deg): ?>
                                <?php
                                $curriculumMap = [
                                    'moratuwa' => 'https://uom.lk/eugs/curriculam',
                                    'colombo' => 'https://cmb.ac.lk/undergraduate-programmes',
                                    'jayewardenepura' => 'https://www.sjp.ac.lk/undergraduate-courses/',
                                    'trincomalee' => 'https://www.tc.esn.ac.lk/',
                                    'south eastern' => 'https://www.seu.ac.lk/undergraduate_studies.php',
                                    'eastern' => 'https://esn.ac.lk/academic-programs/undergraduate',
                                    'kelaniya' => 'https://cdce.kln.ac.lk/',
                                    'sabaragamuwa' => 'https://www.sab.ac.lk/',
                                    'rajarata' => 'https://www.rjt.ac.lk/courses/',
                                    'jaffna' => 'https://jfn.ac.lk/degree-programmes/',
                                    'ruhuna' => 'https://docslib.org/doc/247346/student-handbook#google_vignette',
                                    'vavuniya' => 'https://www.vau.ac.lk/degree-programmes/',
                                    'uva wellassa' => '#',
                                    'wayamba' => 'https://wyb.ac.lk/',
                                    'peradeniya' => 'https://www.pdn.ac.lk/Download',
                                    'gampaha' => 'https://gwu.ac.lk/index.php/undergraduate'
                                ];
                                $curriculumLink = '#';
                                foreach ($curriculumMap as $key => $link) {
                                    if (stripos($deg["university_name"], $key) !== false) {
                                        $curriculumLink = $link;
                                        break;
                                    }
                                }
                                ?>
                                <div class="finder-result-window">
                                    
                                    <!-- Degree Details (Left side) -->
                                    <div class="finder-result-main">
                                        <h3 class="finder-result-title"><?php echo htmlspecialchars($deg["degree_name"]); ?></h3>
                                        <p class="finder-result-university"><strong>University:</strong> <?php echo htmlspecialchars($deg["university_name"]); ?></p>
                                        
                                        <div class="finder-result-meta-grid">
                                            <div><strong>Duration:</strong> <br>4 years</div>
                                            <div><strong>Medium:</strong> <br>English</div>
                                            <div><strong>Subject Req 1:</strong> <br><?php echo !empty($deg["subject1"]) ? htmlspecialchars($deg["subject1"]) : "-"; ?></div>
                                            <div><strong>Subject Req 2:</strong> <br><?php echo !empty($deg["subject2"]) ? htmlspecialchars($deg["subject2"]) : "-"; ?></div>
                                            <div><strong>Subject Req 3:</strong> <br><?php echo !empty($deg["subject3"]) ? htmlspecialchars($deg["subject3"]) : "-"; ?></div>
                                        </div>
                                        
                                        <?php if(!empty($deg["description"])): ?>
                                            <div class="finder-result-description-box">
                                                <strong>Description:</strong>
                                                <p><?php echo htmlspecialchars($deg["description"]); ?></p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="finder-result-actions">
                                            <a class="btn btn-primary" href="<?php echo htmlspecialchars($curriculumLink); ?>" target="_blank" rel="noopener noreferrer">
                                                <i class="fa-solid fa-book"></i> Curriculum
                                            </a>
                                        </div>
                                    </div>

                                    <?php if (!empty(trim($degreeSearchName))): ?>
                                    <!-- Cutoffs Table (Right side vertical) -->
                                    <div class="finder-cutoff-pane">
                                        <table class="finder-cutoff-table">
                                            <thead>
                                                <tr>
                                                    <th>District</th>
                                                    <th>Z-Score Cutoff</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $districtCols = [
                                                    'colombo','gampaha','kalutara','matale','kandy','nuwara_eliya','galle','matara','hambantota','jaffna','kilinochchi','mannar','mullaitivu','vavuniya','trincomalee','batticaloa','ampara','puttalam','kurunegala','anuradhapura','polonnaruwa','badulla','monaragala','ratnapura','kegalle'
                                                ];
                                                foreach($districtCols as $dc): 
                                                    $dName = ucwords(str_replace('_', ' ', $dc));
                                                    $val = $deg[$dc] ?? null;
                                                ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($dName); ?></td>
                                                        <td>
                                                            <?php if($val !== null): ?>
                                                                <span class="finder-cutoff-badge"><?php echo htmlspecialchars($val); ?></span>
                                                            <?php else: ?>
                                                                <span class="finder-cutoff-empty">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <table class="finder-results-table">
                                <thead>
                                    <tr>
                                        <th>Degree</th>
                                        <th>University</th>
                                        <th>Duration</th>
                                        <th>Medium</th>
                                        <th>Z-Score Cutoff</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($degrees as $index => $deg): ?>
                                    <?php
                                    $curriculumMap = [
                                        'moratuwa' => 'https://uom.lk/eugs/curriculam',
                                        'colombo' => 'https://cmb.ac.lk/undergraduate-programmes',
                                        'jayewardenepura' => 'https://www.sjp.ac.lk/undergraduate-courses/',
                                        'trincomalee' => 'https://www.tc.esn.ac.lk/',
                                        'south eastern' => 'https://www.seu.ac.lk/undergraduate_studies.php',
                                        'eastern' => 'https://esn.ac.lk/academic-programs/undergraduate',
                                        'kelaniya' => 'https://cdce.kln.ac.lk/',
                                        'sabaragamuwa' => 'https://www.sab.ac.lk/',
                                        'rajarata' => 'https://www.rjt.ac.lk/courses/',
                                        'jaffna' => 'https://jfn.ac.lk/degree-programmes/',
                                        'ruhuna' => 'https://docslib.org/doc/247346/student-handbook#google_vignette',
                                        'vavuniya' => 'https://www.vau.ac.lk/degree-programmes/',
                                        'uva wellassa' => '#',
                                        'wayamba' => 'https://wyb.ac.lk/',
                                        'peradeniya' => 'https://www.pdn.ac.lk/Download',
                                        'gampaha' => 'https://gwu.ac.lk/index.php/undergraduate'
                                    ];
                                    $curriculumLink = '#';
                                    foreach ($curriculumMap as $key => $link) {
                                        if (stripos($deg["university_name"], $key) !== false) {
                                            $curriculumLink = $link;
                                            break;
                                        }
                                    }
                                    ?>
                                    <tr id="row-<?php echo $index; ?>" class="finder-results-row">
                                        <td><?php echo htmlspecialchars($deg["degree_name"]); ?></td>
                                        <td><?php echo htmlspecialchars($deg["university_name"]); ?></td>
                                        <td>4 years</td>
                                        <td>English</td>
                                        <td>
                                            <?php if(isset($deg["cutoff"]) && $deg["cutoff"] !== null): ?>
                                                <span class="finder-cutoff-badge"><?php echo htmlspecialchars($deg["cutoff"]); ?></span>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-ghost finder-detail-toggle" onclick="showDegreeDetails(<?php echo $index; ?>)">View Details</button>
                                        </td>
                                    </tr>
                                    <tr id="detail-row-<?php echo $index; ?>" class="finder-detail-row" style="display: none;">
                                        <td colspan="6">
                                            <fieldset class="finder-detail-panel">
                                                <legend>Degree Details</legend>
                                                <h3><?php echo htmlspecialchars($deg["degree_name"]); ?></h3>
                                                
                                                <div class="finder-detail-grid">
                                                    <div><strong>University:</strong> <br><?php echo htmlspecialchars($deg["university_name"]); ?></div>
                                                    <?php if(isset($deg["cutoff"]) && $deg["cutoff"] !== null): ?>
                                                        <div><strong>Cutoff:</strong> <br><?php echo htmlspecialchars($deg["cutoff"]); ?></div>
                                                    <?php endif; ?>
                                                    <div><strong>Duration:</strong> <br>4 years</div>
                                                    <div><strong>Medium:</strong> <br>English</div>
                                                </div>
                                                
                                                <?php if(!empty($deg["description"])): ?>
                                                    <div class="finder-result-description-box">
                                                        <strong>Description:</strong>
                                                        <p><?php echo htmlspecialchars($deg["description"]); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="finder-result-actions finder-detail-actions">
                                                    <?php if (!empty($deg["university_id"])): ?>
                                                        <a class="btn btn-primary" href="university.php?id=<?php echo $deg["university_id"]; ?>">Go to University Page &rarr;</a>
                                                    <?php endif; ?>
                                                    <a class="btn btn-primary" href="<?php echo htmlspecialchars($curriculumLink); ?>" target="_blank" rel="noopener noreferrer">
                                                        <i class="fa-solid fa-book"></i> Curriculum
                                                    </a>
                                                </div>
                                            </fieldset>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="section-subtitle">
                        <?php if($searchMode === "zscore"): ?>
                            No matching degrees found for your subject combination and district. Relax the cutoff or verify your inputs.
                        <?php else: ?>
                            No matching degree names found. Try another keyword.
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
const DETAIL_ANIMATION_MS = 220;

function closeDetailRow(detailRow, mainRow) {
    if (!detailRow) {
        return;
    }

    detailRow.classList.remove('is-open');
    if (mainRow) {
        mainRow.classList.remove('is-active');
    }

    if (detailRow.hideTimer) {
        window.clearTimeout(detailRow.hideTimer);
    }

    detailRow.hideTimer = window.setTimeout(() => {
        detailRow.style.display = 'none';
    }, DETAIL_ANIMATION_MS);
}

function openDetailRow(detailRow, mainRow) {
    if (!detailRow) {
        return;
    }

    if (detailRow.hideTimer) {
        window.clearTimeout(detailRow.hideTimer);
    }

    detailRow.style.display = 'table-row';
    window.requestAnimationFrame(() => {
        detailRow.classList.add('is-open');
    });

    if (mainRow) {
        mainRow.classList.add('is-active');
    }
}

function showDegreeDetails(index) {
    const detailRow = document.getElementById('detail-row-' + index);
    const mainRow = document.getElementById('row-' + index);
    const isVisible = detailRow && detailRow.style.display !== 'none';
    const isOpen = detailRow && detailRow.classList.contains('is-open');
    
    // If it's already shown, just hide it
    if (isVisible && isOpen) {
        closeDetailRow(detailRow, mainRow);
        return;
    }

    // Hide all detail rows first
    const allDetailRows = document.querySelectorAll('tr[id^="detail-row-"]');
    allDetailRows.forEach(el => {
        const rowId = el.id.replace('detail-row-', '');
        const pairedMainRow = document.getElementById('row-' + rowId);
        closeDetailRow(el, pairedMainRow);
    });

    // Reset table row highlights
    const allRows = document.querySelectorAll('tr[id^="row-"]');
    allRows.forEach(el => {
        el.classList.remove('is-active');
    });

    // Show specific detail row and highlight main row
    if (detailRow) {
        openDetailRow(detailRow, mainRow);
    }
}

function resetZscoreForm() {
    // Reset all select dropdowns strictly inside the zscore form
    const selects = document.querySelectorAll("input[name='zscore_search']").forEach(e => {
        const form = e.closest("form");
        const formSelects = form.querySelectorAll("select");
        formSelects.forEach(select => select.selectedIndex = 0);
    });
    
    // Reset the slider and text input to 0
    const slider = document.getElementById("zscoreRange");
    const zInput = document.getElementById("zscoreInput");
    
    if(slider) slider.value = "0";
    if(zInput) zInput.value = "0";
}

function resetNameSearch() {
    const input = document.getElementById("searchInput");
    if(input) input.value = "";
}
</script>

<?php include "includes/footer.php"; ?>