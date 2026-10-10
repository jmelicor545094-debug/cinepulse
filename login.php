<?php
/**
 * CinePulse - Login Page (Protected Access)
 * Credentials: joshuagwapo / mamagwapa12
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$pageTitle = 'Sign In Required - Melicor Movie Room';

$error = '';
$redirect = $_GET['redirect'] ?? 'index.php';
$msg = $_GET['msg'] ?? '';

if ($msg === 'login_required') {
    $error = '🔒 Access Restricted! You must sign in to watch movies & TV shows.';
}

// Process Login Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $res = loginUser($username, $password);
    if ($res['success']) {
        $target = (!empty($redirect) && strpos($redirect, 'login.php') === false) ? $redirect : 'index.php';
        header('Location: ' . $target);
        exit;
    } else {
        $error = $res['message'];
    }
}

// Redirect if already logged in
if (isLoggedIn()) {
    $target = (!empty($redirect) && strpos($redirect, 'login.php') === false) ? $redirect : 'index.php';
    header('Location: ' . $target);
    exit;
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="container" style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px;">
    <div style="width: 100%; max-width: 440px; background: var(--bg-glass); backdrop-filter: blur(24px); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 36px 32px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8);">
        
        <!-- Header Logo -->
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="font-size: 2.8rem; margin-bottom: 8px;">🔒</div>
            <h2 style="font-size: 1.6rem; font-weight: 800; color: #fff;">
                Sign In to <span style="background: var(--grad-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Melicor Movie Room</span>
            </h2>
            <p style="color: var(--text-secondary); font-size: 0.88rem; margin-top: 6px;">Sign in with your account to unlock full movie streaming.</p>
        </div>

        <!-- Alert Banner -->
        <?php if ($error): ?>
            <div style="background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.4); color: #f43f5e; padding: 12px 16px; border-radius: var(--radius-md); font-size: 0.88rem; margin-bottom: 20px; text-align: center;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- LOGIN FORM -->
        <form method="POST" action="login.php?redirect=<?php echo urlencode($redirect); ?>">

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Username</label>
                <input type="text" id="loginUsername" name="username" value="" required placeholder="Enter username..." class="search-input-field" style="padding-left: 16px;">
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Password</label>
                <input type="password" id="loginPassword" name="password" value="" required placeholder="Enter password..." class="search-input-field" style="padding-left: 16px;">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 1rem;">
                ▶ Sign In & Watch Movies
            </button>
        </form>

    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
