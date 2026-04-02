<?php
require_once __DIR__ . '/includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['admin_id'])) {
    header('Location: admin/dashboard.php');
    exit;
}

if (!empty($_SESSION['user_id'])) {
    header('Location: universities.php');
    exit;
}

$errors = [];
$username = '';

function sanitize_redirect_target($target)
{
    $target = trim($target);
    if ($target === '') {
        return 'universities.php';
    }

    if (strpos($target, '://') !== false || strpos($target, '../') !== false) {
        return 'universities.php';
    }

    return ltrim($target, '/');
}

$redirectTarget = sanitize_redirect_target($_POST['redirect'] ?? $_GET['redirect'] ?? 'universities.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors[] = 'Enter both username and password.';
    } else {
        $loggedIn = false;

        $adminStmt = $conn->prepare('SELECT id, password_hash, fullname FROM admin_users WHERE username = ? LIMIT 1');
        if ($adminStmt) {
            $adminStmt->bind_param('s', $username);
            $adminStmt->execute();
            $adminResult = $adminStmt->get_result();
            if ($adminResult && ($adminRow = $adminResult->fetch_assoc())) {
                if (password_verify($password, $adminRow['password_hash'])) {
                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = $adminRow['id'];
                    $_SESSION['admin_name'] = $adminRow['fullname'];
                    $loggedIn = true;
                }
            }
            $adminStmt->close();
        }

        if ($loggedIn) {
            header('Location: admin/dashboard.php');
            exit;
        }

        $userStmt = $conn->prepare('SELECT id, fullname, password_hash FROM users WHERE username = ? LIMIT 1');
        if ($userStmt) {
            $userStmt->bind_param('s', $username);
            $userStmt->execute();
            $userResult = $userStmt->get_result();
            if ($userResult && ($userRow = $userResult->fetch_assoc())) {
                if (password_verify($password, $userRow['password_hash'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $userRow['id'];
                    $_SESSION['user_name'] = $userRow['fullname'];
                    header('Location: ' . $redirectTarget);
                    exit;
                }
            }
            $userStmt->close();
        }

        $errors[] = 'Invalid username or password.';
    }
}

$pageTitle = 'Sign In';
$pageStyles = ['css/pages/login.css'];

include __DIR__ . '/includes/header.php';
?>
<section class="page-hero reveal-on-scroll" aria-label="Sign in hero">
    <div class="container">
        <p class="eyebrow">Sign In</p>
        <h1>Welcome back</h1>
        <p class="page-hero-meta">Admin console or student access — pick the path and continue crafting your future.</p>
    </div>
</section>

<section class="section-shell" aria-label="Login form">
    <div class="container">
        <div class="login-card reveal-on-scroll">
            <?php if ($errors): ?>
                <div class="alert-panel">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="login.php" class="login-form" novalidate>
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirectTarget, ENT_QUOTES); ?>">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-input" value="<?php echo htmlspecialchars($username, ENT_QUOTES); ?>" required autofocus>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" required>
                <button type="submit" class="btn btn-primary">Log in</button>
            </form>
            <p class="helper-text">Admin credentials go to the dashboard while students continue into the public catalog.</p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
