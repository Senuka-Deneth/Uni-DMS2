<?php
require_once __DIR__ . '/includes/auth.php';

$activityId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$activity = [
    'name' => '',
    'category' => 'Sports',
    'description' => '',
    'university_id' => '',
    'is_available' => 1,
];

if ($activityId) {
    $stmt = $conn->prepare('SELECT * FROM extracurricular_activities WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $activityId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && ($row = $result->fetch_assoc())) {
        $activity = $row;
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category = $_POST['category'] ?? 'Sports';
    $description = trim($_POST['description'] ?? '');
    $universityId = filter_var($_POST['university_id'] ?? null, FILTER_VALIDATE_INT);
    $isAvailable = isset($_POST['is_available']) ? 1 : 0;

    $activity = [
        'name' => $name,
        'category' => $category,
        'description' => $description,
        'university_id' => $universityId,
        'is_available' => $isAvailable,
    ];

    if ($name === '' || !$universityId || $category === '') {
        set_flash('error', 'Name, university, and category are required.');
    } else {
        if ($activityId) {
            $update = $conn->prepare('UPDATE extracurricular_activities SET name = ?, category = ?, description = ?, university_id = ?, is_available = ? WHERE id = ?');
            $update->bind_param('sssiii', $name, $category, $description, $universityId, $isAvailable, $activityId);
            if ($update->execute()) {
                set_flash('success', 'Activity updated.');
                header('Location: activities.php');
                exit;
            }
            set_flash('error', 'Failed to update the activity.');
            $update->close();
        } else {
            $insert = $conn->prepare('INSERT INTO extracurricular_activities (university_id, name, category, description, is_available) VALUES (?, ?, ?, ?, ?)');
            $insert->bind_param('isssi', $universityId, $name, $category, $description, $isAvailable);
            if ($insert->execute()) {
                set_flash('success', 'Activity added successfully.');
                header('Location: activities.php');
                exit;
            }
            set_flash('error', 'Could not add activity.');
            $insert->close();
        }
    }
}

$universities = [];
$uniStmt = $conn->query('SELECT id, name FROM universities ORDER BY name');
if ($uniStmt) {
    while ($row = $uniStmt->fetch_assoc()) {
        $universities[$row['id']] = $row['name'];
    }
    $uniStmt->free();
}

$heading = $activityId ? 'Edit Activity' : 'Add Activity';
$pageTitle = $heading;
include __DIR__ . '/includes/header.php';
?>
<div class="admin-panel">
    <h1><?php echo htmlspecialchars($heading); ?></h1>
    <form method="POST" action="activity_form.php<?php echo $activityId ? '?id=' . $activityId : ''; ?>">
        <div class="form-grid">
            <div>
                <label for="name">Activity name</label>
                <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($activity['name']); ?>">
            </div>
            <div>
                <label for="university_id">University</label>
                <select id="university_id" name="university_id" required>
                    <option value="">Select university</option>
                    <?php foreach ($universities as $id => $label): ?>
                        <option value="<?php echo $id; ?>" <?php echo ($activity['university_id'] ?? '') == $id ? 'selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="category">Category</label>
                <select id="category" name="category">
                    <?php foreach (['Sports', 'Arts', 'Clubs', 'Community', 'Research', 'Other'] as $categoryOption): ?>
                        <option value="<?php echo $categoryOption; ?>" <?php echo $activity['category'] === $categoryOption ? 'selected' : ''; ?>><?php echo $categoryOption; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Available?</label>
                <label style="display:flex;gap:0.75rem;align-items:center;">
                    <input type="checkbox" name="is_available" <?php echo $activity['is_available'] ? 'checked' : ''; ?>> Yes
                </label>
            </div>
        </div>
        <div style="margin-top:1.25rem;">
            <label for="description">Description</label>
            <textarea id="description" name="description"><?php echo htmlspecialchars($activity['description']); ?></textarea>
        </div>
        <button type="submit" class="btn" style="margin-top:1.25rem;">Save Activity</button>
    </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>