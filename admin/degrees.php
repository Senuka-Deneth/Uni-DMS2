<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Degrees';

function bind_stmt_params($stmt, $types, $values) {
    $bind = array_merge([$types], $values);
    $refs = [];
    foreach ($bind as $key => $value) {
        $refs[$key] = &$bind[$key];
    }
    return call_user_func_array([$stmt, 'bind_param'], $refs);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $degreeId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    if ($degreeId) {
        $stmt = $conn->prepare('DELETE FROM degrees WHERE id = ?');
        $stmt->bind_param('i', $degreeId);
        if ($stmt->execute()) {
            set_flash('success', 'Degree removed successfully.');
        } else {
            set_flash('error', 'Unable to delete the degree.');
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
    $filters[] = 'f.university_id = ?';
    $params[] = (int) $_GET['university'];
    $types .= 'i';
}

if (!empty($_GET['stream'])) {
    $allowedStreams = ['Maths', 'Bio', 'Commerce', 'Arts', 'Physical Science', 'Biological Science'];
    if (in_array($_GET['stream'], $allowedStreams, true)) {
        $filters[] = 'd.stream_requirement = ?';
        $params[] = $_GET['stream'];
        $types .= 's';
    } else {
        // silently discard invalid stream filter
        $_GET['stream'] = '';
    }
}

$whereClause = 'WHERE 1=1';
if ($filters) {
    $whereClause .= ' AND ' . implode(' AND ', $filters);
}

$countSql = "SELECT COUNT(*) AS total FROM degrees d JOIN departments dep ON d.department_id = dep.id JOIN faculties f ON dep.faculty_id = f.id $whereClause";
$countStmt = $conn->prepare($countSql);
if ($params) {
    bind_stmt_params($countStmt, $types, $params);
}
$countStmt->execute();
$total = (int) $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

$dataSql = "SELECT d.*, u.name AS university_name, f.name AS faculty_name, dep.name AS department_name FROM degrees d JOIN departments dep ON d.department_id = dep.id JOIN faculties f ON dep.faculty_id = f.id JOIN universities u ON f.university_id = u.id $whereClause ORDER BY d.created_at DESC LIMIT ?, ?";
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
        <h1>Degrees</h1>
        <a href="degree_form.php" class="btn">Add Degree</a>
    </div>
    <form method="GET" action="degrees.php" style="margin:1.25rem 0;display:flex;gap:1rem;flex-wrap:wrap;">
        <select name="university">
            <option value="">All universities</option>
            <?php foreach ($universities as $id => $name): ?>
                <option value="<?php echo $id; ?>" <?php echo ($_GET['university'] ?? '') == $id ? 'selected' : ''; ?>><?php echo htmlspecialchars($name); ?></option>
            <?php endforeach; ?>
        </select>
        <select name="stream">
            <option value="">All streams</option>
            <?php foreach (['Maths', 'Bio', 'Commerce', 'Arts', 'Physical Science', 'Biological Science'] as $streamOption): ?>
                <option value="<?php echo $streamOption; ?>" <?php echo ($_GET['stream'] ?? '') === $streamOption ? 'selected' : ''; ?>><?php echo $streamOption; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn" style="padding:0.5rem 1rem;">Filter</button>
    </form>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Degree</th>
                <th>University / Faculty</th>
                <th>Stream</th>
                <th>Min Z-score</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
                            <small style="color:var(--text-muted);"><?php echo htmlspecialchars($row['degree_type']); ?> · <?php echo htmlspecialchars($row['duration']); ?></small>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($row['university_name']); ?><br>
                            <small style="color:var(--text-muted);"><?php echo htmlspecialchars($row['faculty_name']); ?> / <?php echo htmlspecialchars($row['department_name']); ?></small>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($row['stream_requirement']); ?><br>
                            <small style="color:var(--text-muted);"><?php echo htmlspecialchars($row['medium']); ?></small>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($row['min_zscore'] ?? '—'); ?><br>
                            <small style="color:var(--text-muted);">Registered <?php echo date('Y', strtotime($row['created_at'])); ?></small>
                        </td>
                        <td>
                            <div class="admin-actions">
                                <a href="degree_form.php?id=<?php echo $row['id']; ?>" class="btn" style="background: rgba(59, 130, 246, 0.2); border:1px solid rgba(59, 130, 246, 0.8);">Edit</a>
                                <form method="POST" action="degrees.php" data-confirm="Remove this degree permanently?">
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
                    <td colspan="5">No degrees found.</td>
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
                if (!empty($_GET['stream'])) {
                    $query['stream'] = $_GET['stream'];
                }
                $link = 'degrees.php?' . http_build_query($query);
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