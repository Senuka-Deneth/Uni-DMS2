<?php
require_once __DIR__ . '/includes/auth.php';

$universityId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$university = [
    'name' => '',
    'location' => '',
    'type' => 'Government',
    'website' => '',
    'contact' => '',
    'established_year' => '',
    'description' => '',
    'image' => ''
];

if ($universityId) {
    $stmt = $conn->prepare('SELECT * FROM universities WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $universityId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && ($row = $result->fetch_assoc())) {
        $university = $row;
    }
    $stmt->close();
}

$hasErrors = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $type = $_POST['type'] ?? 'Government';
    $website = trim($_POST['website'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $establishedYear = $_POST['established_year'] ?? null;
    $description = trim($_POST['description'] ?? '');
    $newImage = null;

    if ($name === '' || $location === '') {
        set_flash('error', 'Name and location are required.');
        $hasErrors = true;
    }

    if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);
        if (!in_array($fileType, $allowedTypes, true)) {
            set_flash('error', 'Only JPG, PNG, and WEBP logos are allowed.');
            $hasErrors = true;
        } elseif ($_FILES['image']['size'] > 3 * 1024 * 1024) {
            set_flash('error', 'Logo must be smaller than 3MB.');
            $hasErrors = true;
        } else {
            $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $newImage = uniqid('uni_', true) . '.' . $extension;
            $target = __DIR__ . '/../uploads/' . $newImage;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                set_flash('error', 'Failed to store the logo image.');
                $hasErrors = true;
                $newImage = null;
            }
        }
    }

    if (!$hasErrors) {
        $establishedYear = $establishedYear === '' ? null : $establishedYear;
        if ($universityId) {
            $updateSql = 'UPDATE universities SET name = ?, location = ?, type = ?, website = ?, contact = ?, established_year = ?, description = ?';
            $params = [$name, $location, $type, $website, $contact, $establishedYear, $description];
            $types = 'sssssss';

            if ($newImage) {
                $updateSql .= ', image = ?';
                $types .= 's';
                $params[] = $newImage;
            }

            $updateSql .= ' WHERE id = ?';
            $types .= 'i';
            $params[] = $universityId;

            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param($types, ...$params);

            if ($newImage && !empty($university['image'])) {
                $oldPath = __DIR__ . '/../uploads/' . $university['image'];
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            if ($stmt->execute()) {
                set_flash('success', 'University record updated.');
                header('Location: universities.php');
                exit;
            }
            set_flash('error', 'Could not update university.');
            $stmt->close();
        } else {
            $insert = $conn->prepare('INSERT INTO universities (name, location, type, website, contact, established_year, description, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $insert->bind_param('ssssssss', $name, $location, $type, $website, $contact, $establishedYear, $description, $newImage);
            if ($insert->execute()) {
                set_flash('success', 'University added successfully.');
                header('Location: universities.php');
                exit;
            }
            set_flash('error', 'Failed to add university.');
            $insert->close();
        }
    }

    $university = [
        'name' => $name,
        'location' => $location,
        'type' => $type,
        'website' => $website,
        'contact' => $contact,
        'established_year' => $establishedYear,
        'description' => $description,
        'image' => $newImage ?? $university['image']
    ];
}

include __DIR__ . '/includes/header.php';
$heading = $universityId ? 'Edit University' : 'Add University';
?>
<div class="admin-panel">
    <h1><?php echo htmlspecialchars($heading); ?></h1>
    <form method="POST" action="university_form.php<?php echo $universityId ? '?id=' . $universityId : ''; ?>" enctype="multipart/form-data">
        <div class="form-grid">
            <div>
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($university['name']); ?>">
            </div>
            <div>
                <label for="location">Location / District</label>
                <input type="text" id="location" name="location" required value="<?php echo htmlspecialchars($university['location']); ?>">
            </div>
            <div>
                <label for="type">Type</label>
                <select id="type" name="type">
                    <?php foreach (['Government', 'Private', 'Foreign'] as $option): ?>
                        <option value="<?php echo $option; ?>" <?php echo $university['type'] === $option ? 'selected' : ''; ?>><?php echo $option; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="website">Website URL</label>
                <input type="url" id="website" name="website" value="<?php echo htmlspecialchars($university['website']); ?>">
            </div>
            <div>
                <label for="contact">Contact number / email</label>
                <input type="text" id="contact" name="contact" value="<?php echo htmlspecialchars($university['contact']); ?>">
            </div>
            <div>
                <label for="established_year">Established year</label>
                <input type="number" id="established_year" name="established_year" min="1900" max="2090" value="<?php echo htmlspecialchars($university['established_year']); ?>">
            </div>
            <div>
                <label for="image">Logo / image</label>
                <input type="file" id="image" name="image" accept="image/png,image/jpeg,image/webp">
                <?php if (!empty($university['image'])): ?>
                    <p style="color:var(--text-muted); font-size:0.85rem;">Current file: <?php echo htmlspecialchars($university['image']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div style="margin-top:1.25rem;">
            <label for="description">Short description</label>
            <textarea id="description" name="description"><?php echo htmlspecialchars($university['description']); ?></textarea>
        </div>
        <button type="submit" class="btn" style="margin-top:1.25rem;">Save University</button>
    </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
