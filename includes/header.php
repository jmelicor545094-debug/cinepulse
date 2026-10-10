<?php
/**
 * Global Header Component
 * Project: CinePulse
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Melicor Movie Room - Discover trending movies, top-rated TV shows, high quality trailers, cast details, and manage your custom movie watchlist powered by TMDB.">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - Melicor Movie Room' : 'Melicor Movie Room - Premium TMDB Movie & TV Experience'; ?></title>
    
    <!-- Google Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Main Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container">
        <nav class="navbar">
            <!-- Brand Logo -->
            <a href="index.php" class="brand-logo">
                <div class="logo-icon">🎬</div>
                <div class="brand-text">Melicor <span class="brand-highlight">Movie Room</span></div>
            </a>

            <!-- Navigation Links -->
            <ul class="nav-links">
                <li><a href="index.php" class="nav-link <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>">Home</a></li>
                <li><a href="search.php?type=movie" class="nav-link <?php echo (isset($_GET['type']) && $_GET['type'] == 'movie') ? 'active' : ''; ?>">Movies</a></li>
                <li><a href="search.php?type=tv" class="nav-link <?php echo (isset($_GET['type']) && $_GET['type'] == 'tv') ? 'active' : ''; ?>">TV Shows</a></li>
                <li><a href="watchlist.php" class="nav-link <?php echo ($currentPage == 'watchlist.php') ? 'active' : ''; ?>">Watchlist</a></li>
            </ul>

            <!-- Header Actions: Live Search & Buttons -->
            <div class="header-actions">
                <!-- Search Box -->
                <form action="search.php" method="GET" class="search-box-wrapper">
                    <span class="search-icon-btn">🔍</span>
                    <input type="text" name="q" id="searchInput" class="search-input-field" placeholder="Search movies, TV shows..." autocomplete="off">
                    <div id="searchDropdown" class="search-results-dropdown"></div>
                </form>

                <!-- Watchlist Counter Button -->
                <a href="watchlist.php" class="icon-action-btn" title="View Watchlist">
                    ❤️
                    <span id="watchlistBadge" class="badge-count">0</span>
                </a>

                <!-- API Key Config Button -->
                <button id="openKeyModalBtn" class="icon-action-btn" title="TMDB API Key Settings">
                    ⚙️
                </button>

                <!-- User Account Profile / Login Button -->
                <?php if ($currentUser): ?>
                    <a href="profile.php" class="nav-link active" style="display: flex; align-items: center; gap: 8px; padding: 6px 14px;" title="User Profile">
                        <span><?php echo $currentUser['avatar'] ?? '🍿'; ?></span>
                        <span><?php echo htmlspecialchars($currentUser['username']); ?></span>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary" style="padding: 8px 18px; font-size: 0.88rem;">
                        Sign In
                    </a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>
