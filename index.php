<?php
/**
 * CinePulse - Main Home Page
 * Displays Spotlight Hero, Genre Chips, Trending, Top Rated & TV Shows
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

// Force login page first for all unauthenticated users
requireLogin();

$pageTitle = 'Home - Discover Trending Movies & TV Shows';

// 1. Fetch Trending Movies for Hero & Row
$trendingData = getTMDBData('trending/movie/day', ['page' => 1]);
$trendingMovies = $trendingData['results'] ?? [];
$heroMovie = !empty($trendingMovies) ? $trendingMovies[0] : null;

// 2. Fetch Top Rated Movies
$topData = getTMDBData('movie/top_rated', ['page' => 1]);
$topRatedMovies = array_slice($topData['results'] ?? [], 0, 10);

// 3. Fetch Now Playing Movies
$nowPlayingData = getTMDBData('movie/now_playing', ['page' => 1]);
$nowPlayingMovies = array_slice($nowPlayingData['results'] ?? [], 0, 10);

// 4. Fetch Popular TV Shows
$tvData = getTMDBData('tv/popular', ['page' => 1]);
$popularTv = array_slice($tvData['results'] ?? [], 0, 10);

// 5. Fetch Movie Genres
$genreData = getTMDBData('genre/movie/list');
$genres = $genreData['genres'] ?? [];

require_once __DIR__ . '/includes/header.php';
?>

<main class="container">
    <!-- Spotlight Hero Banner -->
    <?php if ($heroMovie): ?>
        <section class="hero-spotlight">
            <div class="hero-backdrop" style="background-image: url('<?php echo getBackdropUrl($heroMovie['backdrop_path'] ?? '', 'original'); ?>');"></div>
            <div class="hero-overlay">
                <div class="hero-content">
                    <div class="hero-tag">🔥 Trending Spotlight #1</div>
                    <h1 class="hero-title"><?php echo htmlspecialchars($heroMovie['title'] ?? 'Featured Title'); ?></h1>
                    
                    <div class="hero-meta">
                        <span class="rating-star">⭐ <?php echo formatRating($heroMovie['vote_average'] ?? 0); ?></span>
                        <span>•</span>
                        <span class="meta-pill"><?php echo formatYear($heroMovie['release_date'] ?? ''); ?></span>
                        <span>•</span>
                        <span class="meta-pill">MOVIE</span>
                    </div>

                    <p class="hero-overview">
                        <?php echo htmlspecialchars($heroMovie['overview'] ?? 'No overview available.'); ?>
                    </p>

                    <div class="hero-actions">
                        <a href="movie.php?id=<?php echo $heroMovie['id']; ?>" class="btn btn-primary">
                            ▶ Watch Full Movie
                        </a>
                        <button class="btn btn-glass btn-favorite-action"
                                data-id="<?php echo $heroMovie['id']; ?>"
                                data-type="movie"
                                data-title="<?php echo htmlspecialchars($heroMovie['title']); ?>"
                                data-poster="<?php echo getPosterUrl($heroMovie['poster_path'] ?? '', 'w500'); ?>"
                                data-rating="<?php echo formatRating($heroMovie['vote_average'] ?? 0); ?>"
                                data-year="<?php echo formatYear($heroMovie['release_date'] ?? ''); ?>">
                            ❤️ Watchlist
                        </button>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Interactive Genre Filter Chips -->
    <?php if (!empty($genres)): ?>
        <div class="genre-scroll-wrapper">
            <a href="index.php" class="genre-chip active">All Genres</a>
            <?php foreach (array_slice($genres, 0, 14) as $g): ?>
                <a href="search.php?genre=<?php echo $g['id']; ?>" class="genre-chip">
                    <?php echo htmlspecialchars($g['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Section 1: Trending Movies Grid -->
    <section style="margin-bottom: 48px;">
        <div class="section-header">
            <h2 class="section-title">Trending Movies</h2>
            <a href="search.php?sort=popularity.desc" class="view-all-link">View All ↗</a>
        </div>
        
        <div class="movies-grid">
            <?php 
            if (!empty($trendingMovies)) {
                foreach (array_slice($trendingMovies, 1, 10) as $item) {
                    $item['media_type'] = 'movie';
                    require __DIR__ . '/includes/movie_card.php';
                }
            } else {
                echo '<p style="color:var(--text-secondary);">Unable to load trending movies. Please check your API Key settings.</p>';
            }
            ?>
        </div>
    </section>

    <!-- Section 2: Top Rated Movies Grid -->
    <section style="margin-bottom: 48px;">
        <div class="section-header">
            <h2 class="section-title">Top Rated Classics</h2>
            <a href="search.php?sort=vote_average.desc" class="view-all-link">View All ↗</a>
        </div>
        
        <div class="movies-grid">
            <?php 
            if (!empty($topRatedMovies)) {
                foreach ($topRatedMovies as $item) {
                    $item['media_type'] = 'movie';
                    require __DIR__ . '/includes/movie_card.php';
                }
            }
            ?>
        </div>
    </section>

    <!-- Section 3: Popular TV Shows Grid -->
    <section style="margin-bottom: 48px;">
        <div class="section-header">
            <h2 class="section-title">Popular TV Series</h2>
            <a href="search.php?type=tv" class="view-all-link">View All ↗</a>
        </div>
        
        <div class="movies-grid">
            <?php 
            if (!empty($popularTv)) {
                foreach ($popularTv as $item) {
                    $item['media_type'] = 'tv';
                    require __DIR__ . '/includes/movie_card.php';
                }
            }
            ?>
        </div>
    </section>

    <!-- Section 4: In Theaters / Now Playing -->
    <section style="margin-bottom: 48px;">
        <div class="section-header">
            <h2 class="section-title">Now Playing in Theaters</h2>
            <a href="search.php?sort=primary_release_date.desc" class="view-all-link">View All ↗</a>
        </div>
        
        <div class="movies-grid">
            <?php 
            if (!empty($nowPlayingMovies)) {
                foreach ($nowPlayingMovies as $item) {
                    $item['media_type'] = 'movie';
                    require __DIR__ . '/includes/movie_card.php';
                }
            }
            ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
