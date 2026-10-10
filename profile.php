<?php
/**
 * CinePulse - User Profile Page
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$pageTitle = htmlspecialchars($user['username']) . '\'s Profile - CinePulse';

require_once __DIR__ . '/includes/header.php';
?>

<main class="container" style="min-height: 75vh; padding-top: 40px;">
    <div style="max-width: 720px; margin: 0 auto; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
        
        <!-- Profile Avatar Header -->
        <div style="display: flex; align-items: center; gap: 24px; padding-bottom: 30px; border-bottom: 1px solid var(--border-color); margin-bottom: 30px;">
            <div style="width: 80px; height: 80px; border-radius: var(--radius-full); background: var(--grad-primary); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; box-shadow: var(--shadow-glow);">
                <?php echo $user['avatar'] ?? '🍿'; ?>
            </div>

            <div>
                <h1 style="font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: 4px;">
                    <?php echo htmlspecialchars($user['username']); ?>
                </h1>
                <p style="color: var(--text-secondary); font-size: 0.95rem;">
                    📧 <?php echo htmlspecialchars($user['email']); ?> • Member since <?php echo date('M Y', strtotime($user['created_at'] ?? 'now')); ?>
                </p>
            </div>
        </div>

        <!-- Account Highlights & Stats -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 36px;">
            <a href="watchlist.php" style="background: rgba(255,255,255,0.04); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; display: block; transition: var(--transition);">
                <div style="font-size: 1.8rem; margin-bottom: 8px;">❤️</div>
                <h3 style="font-size: 1.1rem; color: #fff;">My Watchlist</h3>
                <p style="font-size: 0.82rem; color: var(--text-secondary); margin-top: 4px;">View your saved movies & TV shows</p>
            </a>

            <button onclick="document.getElementById('openKeyModalBtn').click()" style="background: rgba(255,255,255,0.04); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; text-align: left; cursor: pointer; color: inherit; transition: var(--transition);">
                <div style="font-size: 1.8rem; margin-bottom: 8px;">⚙️</div>
                <h3 style="font-size: 1.1rem; color: #fff;">TMDB API Settings</h3>
                <p style="font-size: 0.82rem; color: var(--text-secondary); margin-top: 4px;">Configure custom API Key</p>
            </button>
        </div>

        <!-- Actions -->
        <div style="display: flex; justify-content: space-between; align-items: center; pt-4;">
            <a href="index.php" class="btn btn-glass">← Back to Movies</a>
            <a href="logout.php" class="btn btn-primary" style="background: var(--accent-pink);">
                🚪 Sign Out
            </a>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
