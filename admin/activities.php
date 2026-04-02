<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Activities';

function bind_stmt_params($stmt, $types, $values) {
    $bind = array_merge([$types], $values);
    $refs = [];
    foreach ($bind as $key => $value) {
        $refs[$key] = &$bind[$key];
    }
    return call_user_func_array([$stmt, 'bind_param'], $refs);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $activityId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    if ($activityId) {
        $stmt = $conn->prepare('DELETE FROM extracurricular_activities WHERE id = ?');
        $stmt->bind_param('i', $activityId);
        if ($stmt->execute()) {
            set_flash('success', 'Activity removed.');
        } else {
            set_flash('error', 'Unable to remove the activity.');
        }
        $stmt->close();
    }
}

$perPage = 10;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;
$filters = [];
$params = [];
$types = '';

if (!empty($_GET['university'])) {
    $filters[] = 'ea.university_id = ?';
    $params[] = (int) $_GET['university'];
    $types .= 'i';
}

$whereClause = 'WHERE 1=1';
if ($filters) {
    $whereClause .= ' AND ' . implode(' AND ', $filters);
}

$countSql = "SELECT COUNT(*) AS total FROM extracurricular_activities ea $whereClause";
$countStmt = $conn->prepare($countSql);
if ($params) {
    bind_stmt_params($countStmt, $types, $params);
}
$countStmt->execute();
$total = (int) $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

$dataSql = "SELECT ea.*, u.name AS university_name FROM extracurricular_activities ea JOIN universities u ON ea.university_id = u.id $whereClause ORDER BY ea.created_at DESC LIMIT ?, ?";
$dataStmt = $conn->prepare($dataSql);
$bindParams = array_merge($params, [$offset, $perPage]);
$bindTypes = $types . 'ii';
if ($params) {
    bind_stmt_params($dataStmt, $bindTypes, $bindParams);
} else {
    $dataStmt->bind_param('ii', $offset, $perPage);
}
$dataStmt->execute();
$result = $dataStmt->get_result();
$totalPages = (int) ceil($total / $perPage);

$universities = [];
$uniStmt = $conn->query('SELECT id, name FROM universities ORDER BY name');
if ($uniStmt) {
    while ($row = $uniStmt->fetch_assoc()) {
        $universities[$row['id']] = $row['name'];
    }
    $uniStmt->free();
}

include __DIR__ . '/includes/header.php';
?>
<div class="admin-panel">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;">
        <h1>Extracurricular Activities</h1>
        <a href="activity_form.php" class="btn">Add Activity</a>
    </div>
    <form method="GET" action="activities.php" style="margin:1.25rem 0;display:flex;gap:1rem;flex-wrap:wrap;">
        <select name="university">
            <option value="">All universities</option>
            <?php foreach ($universities as $id => $label): ?>
                <option value="<?php echo $id; ?>" <?php echo ($_GET['university'] ?? '') == $id ? 'selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn" style="padding:0.5rem 1rem;">Filter</button>
    </form>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Activity</th>
                <th>University</th>
                <th>Category</th>
                <th>Available</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                            <p style="color:var(--text-muted); margin-top:0.3rem; font-size:0.85rem;"><?php echo htmlspecialchars($row['description']); ?></p>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($row['university_name']); ?><br>
                            <small style="color:var(--text-muted);">Added <?php echo date('M Y', strtotime($row['created_at'])); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td><?php echo $row['is_available'] ? 'Yes' : 'No'; ?></td>
                        <td>
                            <div class="admin-actions">
                                <a href="activity_form.php?id=<?php echo $row['id']; ?>" class="btn" style="background: rgba(59, 130, 246, 0.2); border:1px solid rgba(59, 130, 246, 0.8);">Edit</a>
                                <form method="POST" action="activities.php" data-confirm="Remove this activity?">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn" style="background:#ef4444;">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No activities recorded.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php
                $query = ['page' => $i];
                if (!empty($_GET['university'])) {
                    $query['university'] = $_GET['university'];
                }
                $link = 'activities.php?' . http_build_query($query);
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
<?php
if ($result instanceof mysqli_result) {
    $result->free();
}
$dataStmt->close();
include __DIR__ . '/includes/footer.php';
?>