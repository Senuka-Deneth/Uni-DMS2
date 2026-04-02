<?php
require_once __DIR__ . '/includes/auth.php';

function ensureFacultyId($conn, $universityId, $facultyName) {
    $stmt = $conn->prepare('SELECT id FROM faculties WHERE university_id = ? AND name = ? LIMIT 1');
    $stmt->bind_param('is', $universityId, $facultyName);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $found = $result->fetch_assoc()) {
        $stmt->close();
        return $found['id'];
    }
    $stmt->close();

    $insert = $conn->prepare('INSERT INTO faculties (university_id, name) VALUES (?, ?)');
    $insert->bind_param('is', $universityId, $facultyName);
    $insert->execute();
    $insert->close();
    return $conn->insert_id;
}

function ensureDepartmentId($conn, $facultyId, $departmentName) {
    $stmt = $conn->prepare('SELECT id FROM departments WHERE faculty_id = ? AND name = ? LIMIT 1');
    $stmt->bind_param('is', $facultyId, $departmentName);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $found = $result->fetch_assoc()) {
        $stmt->close();
        return $found['id'];
    }
    $stmt->close();

    $insert = $conn->prepare('INSERT INTO departments (faculty_id, name) VALUES (?, ?)');
    $insert->bind_param('is', $facultyId, $departmentName);
    $insert->execute();
    $insert->close();
    return $conn->insert_id;
}

function upsertCutoff($conn, $degreeId, $stream, $cutoff) {
    $stmt = $conn->prepare('SELECT id FROM zscore_cutoffs WHERE degree_id = ? AND stream = ? LIMIT 1');
    $stmt->bind_param('is', $degreeId, $stream);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && ($row = $result->fetch_assoc())) {
        $stmt->close();
        $update = $conn->prepare('UPDATE zscore_cutoffs SET cutoff = ?, year = ? WHERE id = ?');
        $year = date('Y');
        $update->bind_param('dii', $cutoff, $year, $row['id']);
        $update->execute();
        $update->close();
    } else {
        $stmt->close();
        $insert = $conn->prepare('INSERT INTO zscore_cutoffs (degree_id, stream, cutoff, year) VALUES (?, ?, ?, ?)');
        $year = date('Y');
        $insert->bind_param('isdi', $degreeId, $stream, $cutoff, $year);
        $insert->execute();
        $insert->close();
    }
}

$degreeId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$data = [
    'name' => '',
    'duration' => '',
    'degree_type' => 'BSc',
    'stream_requirement' => '',
    'min_zscore' => '',
    'medium' => 'English',
    'description' => '',
    'career_paths' => '',
    'faculty_name' => '',
    'department_name' => '',
    'university_id' => ''
];

if ($degreeId) {
    $stmt = $conn->prepare('SELECT d.*, dep.name AS department_name, f.name AS faculty_name, f.university_id FROM degrees d JOIN departments dep ON d.department_id = dep.id JOIN faculties f ON dep.faculty_id = f.id WHERE d.id = ? LIMIT 1');
    $stmt->bind_param('i', $degreeId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && ($row = $result->fetch_assoc())) {
        $data = array_merge($data, $row);
    }
    $stmt->close();
}

$hasErrors = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $degreeType = $_POST['degree_type'] ?? 'BSc';
    $streamRequirement = trim($_POST['stream_requirement'] ?? '');
    $minZscore = $_POST['min_zscore'] ?? '';
    $medium = trim($_POST['medium'] ?? 'English');
    $description = trim($_POST['description'] ?? '');
    $careerPaths = trim($_POST['career_paths'] ?? '');
    $facultyName = trim($_POST['faculty_name'] ?? '');
    $departmentName = trim($_POST['department_name'] ?? '');
    $universityId = filter_var($_POST['university_id'] ?? null, FILTER_VALIDATE_INT);

    if ($name === '' || !$universityId || $facultyName === '' || $departmentName === '' || $streamRequirement === '') {
        set_flash('error', 'Fill out the required fields (name, university, faculty, department, stream).');
        $hasErrors = true;
    }

    if (!$hasErrors) {
        $facultyId = ensureFacultyId($conn, $universityId, $facultyName);
        $departmentId = ensureDepartmentId($conn, $facultyId, $departmentName);
        $minZscore = $minZscore === '' ? null : number_format((float) $minZscore, 3, '.', '');

        if ($degreeId) {
            $update = $conn->prepare('UPDATE degrees SET name = ?, duration = ?, degree_type = ?, stream_requirement = ?, min_zscore = ?, medium = ?, description = ?, career_paths = ?, department_id = ? WHERE id = ?');
            $update->bind_param('ssssssssii', $name, $duration, $degreeType, $streamRequirement, $minZscore, $medium, $description, $careerPaths, $departmentId, $degreeId);
            if ($update->execute()) {
                upsertCutoff($conn, $degreeId, $streamRequirement, $minZscore ?? 0.0);
                set_flash('success', 'Degree updated successfully.');
                header('Location: degrees.php');
                exit;
            }
            set_flash('error', 'Could not update the degree.');
            $update->close();
        } else {
            $insert = $conn->prepare('INSERT INTO degrees (department_id, name, faculty, duration, degree_type, stream_requirement, min_zscore, medium, description, career_paths) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $insert->bind_param('isssssssss', $departmentId, $name, $facultyName, $duration, $degreeType, $streamRequirement, $minZscore, $medium, $description, $careerPaths);
            if ($insert->execute()) {
                upsertCutoff($conn, $insert->insert_id, $streamRequirement, $minZscore ?? 0.0);
                set_flash('success', 'Degree added successfully.');
                header('Location: degrees.php');
                exit;
            }
            set_flash('error', 'Failed to add degree.');
            $insert->close();
        }
    }

    $data = [
        'name' => $name,
        'duration' => $duration,
        'degree_type' => $degreeType,
        'stream_requirement' => $streamRequirement,
        'min_zscore' => $minZscore,
        'medium' => $medium,
        'description' => $description,
        'career_paths' => $careerPaths,
        'faculty_name' => $facultyName,
        'department_name' => $departmentName,
        'university_id' => $universityId
    ];
}

$universities = [];
$uniStmt = $conn->query('SELECT id, name FROM universities ORDER BY name');
if ($uniStmt) {
    while ($row = $uniStmt->fetch_assoc()) {
        $universities[$row['id']] = $row['name'];
    }
    $uniStmt->free();
}

$heading = $degreeId ? 'Edit Degree' : 'Add Degree';
$pageTitle = $heading;
include __DIR__ . '/includes/header.php';
?>
<div class="admin-panel">
    <h1><?php echo htmlspecialchars($heading); ?></h1>
    <form method="POST" action="degree_form.php<?php echo $degreeId ? '?id=' . $degreeId : ''; ?>">
        <div class="form-grid">
            <div>
                <label for="name">Degree name</label>
                <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($data['name']); ?>">
            </div>
            <div>
                <label for="university_id">University</label>
                <select id="university_id" name="university_id" required>
                    <option value="">Select university</option>
                    <?php foreach ($universities as $id => $label): ?>
                        <option value="<?php echo $id; ?>" <?php echo ($data['university_id'] ?? '') == $id ? 'selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="faculty_name">Faculty</label>
                <input type="text" id="faculty_name" name="faculty_name" required value="<?php echo htmlspecialchars($data['faculty_name']); ?>">
            </div>
            <div>
                <label for="department_name">Department</label>
                <input type="text" id="department_name" name="department_name" required value="<?php echo htmlspecialchars($data['department_name']); ?>">
            </div>
            <div>
                <label for="degree_type">Degree type</label>
                <select id="degree_type" name="degree_type">
                    <?php foreach (['BSc', 'BA', 'BEng', 'BBA', 'LLB'] as $typeOption): ?>
                        <option value="<?php echo $typeOption; ?>" <?php echo $data['degree_type'] === $typeOption ? 'selected' : ''; ?>><?php echo $typeOption; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="stream_requirement">Stream requirement</label>
                <select id="stream_requirement" name="stream_requirement" required>
                    <?php foreach (['Maths', 'Bio', 'Commerce', 'Arts', 'Physical Science', 'Biological Science'] as $streamOption): ?>
                        <option value="<?php echo $streamOption; ?>" <?php echo $data['stream_requirement'] === $streamOption ? 'selected' : ''; ?>><?php echo $streamOption; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="min_zscore">Minimum Z-score</label>
                <input type="number" id="min_zscore" name="min_zscore" step="0.001" min="0" max="4" value="<?php echo htmlspecialchars($data['min_zscore']); ?>">
            </div>
            <div>
                <label for="duration">Duration</label>
                <input type="text" id="duration" name="duration" value="<?php echo htmlspecialchars($data['duration']); ?>" placeholder="e.g., 4 years">
            </div>
            <div>
                <label for="medium">Medium of instruction</label>
                <input type="text" id="medium" name="medium" value="<?php echo htmlspecialchars($data['medium']); ?>">
            </div>
        </div>
        <div style="margin-top:1.25rem;">
            <label for="description">Degree description</label>
            <textarea id="description" name="description"><?php echo htmlspecialchars($data['description']); ?></textarea>
        </div>
        <div style="margin-top:1.25rem;">
            <label for="career_paths">Career paths</label>
            <textarea id="career_paths" name="career_paths"><?php echo htmlspecialchars($data['career_paths']); ?></textarea>
        </div>
        <button type="submit" class="btn" style="margin-top:1.25rem;">Save Degree</button>
    </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>