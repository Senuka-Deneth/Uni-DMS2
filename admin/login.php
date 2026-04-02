<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/flash.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirectTarget = $_POST['redirect'] ?? $_GET['redirect'] ?? 'dashboard.php';

    if ($username === '' || $password === '') {
        set_flash('error', 'Enter both username and password.');
    } else {
        $stmt = $conn->prepare('SELECT id, password_hash, fullname FROM admin_users WHERE username = ? LIMIT 1');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result && ($row = $result->fetch_assoc())) {
            if (password_verify($password, $row['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $row['id'];
                $_SESSION['admin_name'] = $row['fullname'];
                set_flash('success', 'Welcome back, ' . htmlspecialchars($row['fullname']));
                // Strict redirect: only allow safe relative paths with no protocol or path traversal
                $allowed = ['dashboard.php', 'universities.php', 'degrees.php', 'activities.php'];
                $redirect = ltrim($redirectTarget, '/');
                if (!in_array($redirect, $allowed, true)) {
                    $redirect = 'dashboard.php';
                }
                header('Location: ' . $redirect);
                exit;
            }
        }
        set_flash('error', 'Invalid credentials.');
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uni-DMS Admin Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<section class="container" style="padding-top: 4rem; padding-bottom: 4rem;">
    <div class="card" style="max-width: 480px; margin: 0 auto;">
        <h1 style="margin-bottom: 1rem;">Admin Login</h1>
        <?php render_flash_messages(); ?>
        <form method="POST" action="login.php" novalidate>
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect'] ?? '', ENT_QUOTES); ?>">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autofocus>
            <label for="password" style="margin-top: 1rem;">Password</label>
            <input type="password" id="password" name="password" required>
            <button type="submit" class="btn" style="margin-top: 1.5rem; width: 100%;">Login</button>
        </form>
    </div>
</section>
</body>
</html>
