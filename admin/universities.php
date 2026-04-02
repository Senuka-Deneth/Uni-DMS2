<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Universities';
include __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $deleteId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    if ($deleteId) {
        $select = $conn->prepare('SELECT image FROM universities WHERE id = ? LIMIT 1');
        $select->bind_param('i', $deleteId);
        $select->execute();
        $result = $select->get_result();
        $select->close();
        $avatar = $result ? $result->fetch_assoc()['image'] : null;

        $stmt = $conn->prepare('DELETE FROM universities WHERE id = ?');
        $stmt->bind_param('i', $deleteId);
        if ($stmt->execute()) {
            if ($avatar) {
                $path = __DIR__ . '/../uploads/' . $avatar;
                if (file_exists($path)) {
                    unlink($path);
                }
            }
            set_flash('success', 'University removed permanently.');
        } else {
            set_flash('error', 'Could not delete the university.');
        }
        $stmt->close();
    }
}

$perPage = 10;
$page = max(1, (int) ($_GET['page'] ?? 1));
$search = trim($_GET['search'] ?? '');
$offset = ($page - 1) * $perPage;
$terms = [];
$total = 0;

if ($search !== '') {
    $like = "%{$search}%";
    $countStmt = $conn->prepare('SELECT COUNT(*) AS total FROM universities WHERE name LIKE ? OR location LIKE ?');
    $countStmt->bind_param('ss', $like, $like);
    $countStmt->execute();
    $count = $countStmt->get_result()->fetch_assoc()['total'];
    $total = $count ? (int) $count : 0;
    $countStmt->close();

    $dataStmt = $conn->prepare('SELECT * FROM universities WHERE name LIKE ? OR location LIKE ? ORDER BY created_at DESC LIMIT ?, ?');
    $dataStmt->bind_param('ssii', $like, $like, $offset, $perPage);
    $dataStmt->execute();
    $result = $dataStmt->get_result();
} else {
    $countStmt = $conn->prepare('SELECT COUNT(*) AS total FROM universities');
    $countStmt->execute();
    $total = (int) $countStmt->get_result()->fetch_assoc()['total'];
    $countStmt->close();

    $dataStmt = $conn->prepare('SELECT * FROM universities ORDER BY created_at DESC LIMIT ?, ?');
    $dataStmt->bind_param('ii', $offset, $perPage);
    $dataStmt->execute();
    $result = $dataStmt->get_result();
}

$totalPages = (int) ceil($total / $perPage);
?>
<div class="admin-panel">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;">
        <h1>Universities</h1>
        <a href="university_form.php" class="btn">Add University</a>
    </div>
    <form method="GET" action="universities.php" style="margin:1.25rem 0;">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name or location" style="width:100%;max-width:420px;">
    </form>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Logo</th>
                <th>Name</th>
                <th>Type / Location</th>
                <th>Contact</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    $imagePath = '';
                    if (!empty($row['image'])) {
                        $uploads = __DIR__ . '/../uploads/' . $row['image'];
                        $images = dirname(__DIR__) . '/images/' . $row['image'];
                        if (file_exists($uploads)) {
                            $imagePath = 'uploads/' . $row['image'];
                        } elseif (file_exists($images)) {
                            $imagePath = 'images/' . $row['image'];
                        }
                    }
                    ?>
                    <tr>
                        <td>
                            <?php if ($imagePath): ?>
                                <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" style="width:60px; height:60px; object-fit:cover; border-radius:10px;">
                            <?php else: ?>
                                <span style="color:var(--text-muted); font-size:0.85rem;">No logo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                            <p style="color:var(--text-muted); margin-top:0.3rem;"><abbr title="Established Year">Est.</abbr> <?php echo htmlspecialchars($row['established_year'] ?: '—'); ?></p>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($row['type']); ?> / <?php echo htmlspecialchars($row['location']); ?><br>
                            <small><a href="<?php echo htmlspecialchars($row['website']); ?>" target="_blank" style="color:var(--accent);">Visit site</a></small>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($row['contact'] ?? '—'); ?><br>
                            <small style="color:var(--text-muted);">Added <?php echo date('M j, Y', strtotime($row['created_at'])); ?></small>
                        </td>
                        <td>
                            <div class="admin-actions">
                                <a href="university_form.php?id=<?php echo $row['id']; ?>" class="btn" style="background: rgba(59, 130, 246, 0.2); border:1px solid rgba(59, 130, 246, 0.8);">Edit</a>
                                <form method="POST" action="universities.php" data-confirm="Delete this university and all related data?">
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
                    <td colspan="5">No universities found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php
                $query = ['page' => $i];
                if ($search !== '') {
                    $query['search'] = $search;
                }
                $link = 'universities.php?' . http_build_query($query);
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