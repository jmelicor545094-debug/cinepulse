<?php
/**
 * CinePulse - Watchlist Page
 * Manages user's saved movies & TV shows stored locally with live backend details sync
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

// Require login to access watchlist
requireLogin();

$pageTitle = 'My Watchlist - Saved Movies & TV Shows';

require_once __DIR__ . '/includes/header.php';
?>

<main class="container" style="min-height: 70vh; padding-top: 36px;">
    <div class="section-header" style="align-items: center;">
        <div>
            <h1 class="section-title" style="font-size: 2rem;">❤️ My Favorite Watchlist</h1>
            <p style="color: var(--text-secondary); margin-top: 6px;" id="watchlistCountSubtitle">Your personal collection of saved titles.</p>
        </div>

        <div style="display: flex; gap: 12px;">
            <button id="clearWatchlistBtn" class="btn btn-glass" style="color: var(--accent-pink);">
                🗑️ Clear All
            </button>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="genre-scroll-wrapper" style="margin-bottom: 24px;">
        <button class="genre-chip active" onclick="filterWatchlistUI('all', event)">All Items</button>
        <button class="genre-chip" onclick="filterWatchlistUI('movie', event)">Movies Only</button>
        <button class="genre-chip" onclick="filterWatchlistUI('tv', event)">TV Shows Only</button>
    </div>

    <!-- Container for dynamic Watchlist cards -->
    <div id="watchlistGrid" class="movies-grid">
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 0; color: var(--text-secondary);">
            <div style="font-size: 2.5rem; margin-bottom: 12px;">🎬</div>
            <p>Loading your saved watchlist items...</p>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    renderWatchlistPage();
});

let currentWatchlistFilter = 'all';

function renderWatchlistPage() {
    const grid = document.getElementById('watchlistGrid');
    const subtitle = document.getElementById('watchlistCountSubtitle');
    const items = getWatchlist();

    if (!items || items.length === 0) {
        grid.innerHTML = `
            <div style="grid-column: 1 / -1; text-align: center; padding: 80px 20px; background: var(--bg-card); border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
                <div style="font-size: 3.5rem; margin-bottom: 16px;">🍿</div>
                <h3 style="font-size: 1.5rem; margin-bottom: 8px;">Your Watchlist is empty</h3>
                <p style="color: var(--text-secondary); margin-bottom: 24px;">Explore movies and TV shows and click the ❤️ icon to save them for later.</p>
                <a href="index.php" class="btn btn-primary">Browse Trending Movies</a>
            </div>
        `;
        if (subtitle) subtitle.textContent = '0 items saved';
        return;
    }

    const filtered = items.filter(item => {
        if (currentWatchlistFilter === 'movie') return item.type === 'movie';
        if (currentWatchlistFilter === 'tv') return item.type === 'tv';
        return true;
    });

    if (subtitle) subtitle.textContent = `${filtered.length} saved title${filtered.length === 1 ? '' : 's'}`;

    if (filtered.length === 0) {
        grid.innerHTML = `<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-secondary);">No ${currentWatchlistFilter.toUpperCase()} items found in your watchlist.</div>`;
        return;
    }

    grid.innerHTML = filtered.map(item => `
        <div class="movie-card">
            <div class="poster-container">
                <img src="${item.poster || 'https://via.placeholder.com/500x750/121026/8b5cf6?text=No+Poster'}" alt="${escapeHtml(item.title)}" class="movie-poster" loading="lazy">
                
                <div class="rating-badge">
                    ⭐ <span>${item.rating || '0.0'}</span>
                </div>

                <button class="card-favorite-btn active" 
                        data-id="${item.id}" 
                        data-type="${item.type || 'movie'}" 
                        data-title="${escapeHtml(item.title)}" 
                        title="Remove from Watchlist">
                    ❤️
                </button>

                <div class="poster-hover-overlay">
                    <a href="${item.type === 'tv' ? 'tv.php' : 'movie.php'}?id=${item.id}" 
                       class="play-circle-btn" 
                       title="Watch Full Movie">
                        ▶
                    </a>
                </div>
            </div>

            <div class="movie-card-info">
                <a href="${item.type === 'tv' ? 'tv.php' : 'movie.php'}?id=${item.id}" class="card-title" title="${escapeHtml(item.title)}">
                    ${escapeHtml(item.title)}
                </a>
                <div class="card-sub">
                    <span>${item.year || 'N/A'}</span>
                    <span class="media-tag">${(item.type || 'movie').toUpperCase()}</span>
                </div>
            </div>
        </div>
    `).join('');
}

function filterWatchlistUI(type, event) {
    currentWatchlistFilter = type;
    document.querySelectorAll('.genre-chip').forEach(c => c.classList.remove('active'));
    event.target.classList.add('active');
    renderWatchlistPage();
}

document.getElementById('clearWatchlistBtn')?.addEventListener('click', () => {
    if (confirm('Are you sure you want to clear your entire watchlist?')) {
        saveWatchlist([]);
        renderWatchlistPage();
        showToast('Watchlist cleared');
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
