<?php
/**
 * CinePulse - Search & Discovery Page
 * Advanced movie and TV search, genre filtering, sorting, and pagination
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

// Force login page first for all unauthenticated users
requireLogin();

$query = trim($_GET['q'] ?? '');
$genre = $_GET['genre'] ?? '';
$mediaType = ($_GET['type'] ?? 'movie') === 'tv' ? 'tv' : 'movie';
$sort = $_GET['sort'] ?? 'popularity.desc';
$page = max(1, (int)($_GET['page'] ?? 1));

// Fetch genres list for filter dropdown
$genreData = getTMDBData("genre/{$mediaType}/list");
$genresList = $genreData['genres'] ?? [];

// Perform TMDB API Request
if (!empty($query)) {
    // Text search query
    $pageTitle = "Search results for '{$query}'";
    $endpoint = ($mediaType === 'tv') ? 'search/tv' : 'search/movie';
    $params = [
        'query' => $query,
        'page' => $page,
        'include_adult' => 'false'
    ];
} else {
    // Discover by genre & sorting
    $pageTitle = ($mediaType === 'tv') ? "Discover TV Shows" : "Discover Movies";
    $endpoint = ($mediaType === 'tv') ? 'discover/tv' : 'discover/movie';
    $params = [
        'sort_by' => $sort,
        'page' => $page,
        'include_adult' => 'false'
    ];
    if (!empty($genre)) {
        $params['with_genres'] = $genre;
    }
}

$searchResults = getTMDBData($endpoint, $params);
$items = $searchResults['results'] ?? [];
$totalPages = min($searchResults['total_pages'] ?? 1, 500); // TMDB caps at 500
$totalResults = number_format($searchResults['total_results'] ?? 0);

require_once __DIR__ . '/includes/header.php';
?>

<main class="container" style="padding-top: 32px;">

    <!-- Search Header & Title -->
    <div style="margin-bottom: 32px;">
        <h1 class="section-title" style="font-size: 2rem; margin-bottom: 8px;">
            <?php 
            if (!empty($query)) {
                echo "🔍 Search Results for \"<span style='color: var(--accent-cyan);'>" . htmlspecialchars($query) . "</span>\"";
            } else {
                echo ($mediaType === 'tv') ? "📺 TV Series Catalog" : "🎬 Movies Directory";
            }
            ?>
        </h1>
        <p style="color: var(--text-secondary); font-size: 0.95rem;">
            Found <?php echo $totalResults; ?> title<?php echo ($searchResults['total_results'] ?? 0) === 1 ? '' : 's'; ?> • Page <?php echo $page; ?> of <?php echo $totalPages; ?>
        </p>
    </div>

    <!-- Filter Control Bar -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); padding: 20px 24px; border-radius: var(--radius-lg); margin-bottom: 40px;">
        <form method="GET" action="search.php" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; align-items: flex-end;">
            
            <!-- Search Text -->
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">Keyword</label>
                <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" placeholder="Title keyword..." class="search-input-field" style="padding-left: 16px;">
            </div>

            <!-- Media Type -->
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">Type</label>
                <select name="type" class="search-input-field" style="padding-left: 16px;">
                    <option value="movie" <?php echo ($mediaType === 'movie') ? 'selected' : ''; ?>>Movies</option>
                    <option value="tv" <?php echo ($mediaType === 'tv') ? 'selected' : ''; ?>>TV Shows</option>
                </select>
            </div>

            <!-- Genre -->
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">Genre</label>
                <select name="genre" class="search-input-field" style="padding-left: 16px;">
                    <option value="">All Genres</option>
                    <?php foreach ($genresList as $g): ?>
                        <option value="<?php echo $g['id']; ?>" <?php echo ($genre == $g['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($g['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Sort By -->
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">Sort By</label>
                <select name="sort" class="search-input-field" style="padding-left: 16px;">
                    <option value="popularity.desc" <?php echo ($sort === 'popularity.desc') ? 'selected' : ''; ?>>Most Popular</option>
                    <option value="vote_average.desc" <?php echo ($sort === 'vote_average.desc') ? 'selected' : ''; ?>>Highest Rated</option>
                    <option value="primary_release_date.desc" <?php echo ($sort === 'primary_release_date.desc') ? 'selected' : ''; ?>>Newest Release</option>
                </select>
            </div>

            <!-- Apply Filter Button -->
            <div>
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 11px;">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Results Grid -->
    <?php if (!empty($items)): ?>
        <div class="movies-grid">
            <?php 
            foreach ($items as $item) {
                $item['media_type'] = $mediaType;
                require __DIR__ . '/includes/movie_card.php';
            }
            ?>
        </div>

        <!-- Pagination Controls -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-btn">← Prev</a>
                <?php endif; ?>

                <?php 
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                for ($p = $startPage; $p <= $endPage; $p++): 
                ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $p])); ?>" class="page-btn <?php echo ($p === $page) ? 'active' : ''; ?>">
                        <?php echo $p; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-btn">Next →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div style="text-align: center; padding: 80px 20px; background: var(--bg-card); border-radius: var(--radius-lg); border: 1px solid var(--border-color); margin-bottom: 60px;">
            <div style="font-size: 3rem; margin-bottom: 12px;">🔎</div>
            <h3>No results found</h3>
            <p style="color: var(--text-secondary); margin-top: 8px;">Try searching for a different title or resetting genre filters.</p>
        </div>
    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
