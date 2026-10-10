<?php
/**
 * CinePulse - Movie Details Page
 * Displays full metadata, cast & crew, embedded video player tabs, and recommendations
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

// Require login to watch movies
requireLogin();

$movieId = (int)($_GET['id'] ?? 0);

if ($movieId <= 0) {
    header('Location: index.php');
    exit;
}

// Fetch comprehensive movie details with append_to_response
$movie = getTMDBData("movie/{$movieId}", [
    'append_to_response' => 'credits,videos,recommendations,similar,keywords'
]);

if (empty($movie['id'])) {
    $pageTitle = 'Movie Not Found';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container" style="padding: 100px 0; text-align: center;">';
    echo '<h2>🎬 Movie Not Found</h2>';
    echo '<p style="color: var(--text-secondary); margin: 16px 0;">The requested movie could not be loaded from TMDB.</p>';
    echo '<a href="index.php" class="btn btn-primary">Return to Home</a>';
    echo '</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$title = $movie['title'] ?? 'Untitled Movie';
$pageTitle = $title . ' (' . formatYear($movie['release_date'] ?? '') . ')';

$poster = getPosterUrl($movie['poster_path'] ?? '', 'w500');
$backdrop = getBackdropUrl($movie['backdrop_path'] ?? '', 'original');
$rating = formatRating($movie['vote_average'] ?? 0);
$voteCount = number_format($movie['vote_count'] ?? 0);
$runtime = formatRuntime($movie['runtime'] ?? 0);
$year = formatYear($movie['release_date'] ?? '');
$releaseDate = formatDate($movie['release_date'] ?? '');
$tagline = $movie['tagline'] ?? '';
$overview = $movie['overview'] ?? 'No synopsis available for this movie.';

// Cast & Crew
$cast = array_slice($movie['credits']['cast'] ?? [], 0, 12);
$directors = array_filter($movie['credits']['crew'] ?? [], function($c) {
    return ($c['job'] ?? '') === 'Director';
});
$directorNames = implode(', ', array_map(function($d) { return $d['name']; }, $directors)) ?: 'N/A';

// Recommendations & Similar
$recommendations = array_slice($movie['recommendations']['results'] ?? $movie['similar']['results'] ?? [], 0, 10);

// Find YouTube Trailer
$videos = $movie['videos']['results'] ?? [];
$trailerKey = null;
foreach ($videos as $v) {
    if (($v['site'] ?? '') === 'YouTube' && (($v['type'] ?? '') === 'Trailer' || ($v['type'] ?? '') === 'Teaser')) {
        $trailerKey = $v['key'];
        if (($v['type'] ?? '') === 'Trailer' && ($v['official'] ?? false)) break;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Movie Detail Hero Backdrop Header -->
<div class="movie-detail-hero">
    <div class="movie-detail-backdrop" style="background-image: url('<?php echo $backdrop; ?>');"></div>
    <div class="container">
        <div class="movie-detail-overlay">
            
            <!-- Left: Poster -->
            <div class="movie-detail-poster-wrapper">
                <img src="<?php echo $poster; ?>" alt="<?php echo htmlspecialchars($title); ?>" style="width: 100%; display: block;" onerror="this.src='https://via.placeholder.com/500x750/121026/8b5cf6?text=No+Poster';">
            </div>

            <!-- Right: Details Info -->
            <div class="movie-detail-main">
                <h1 class="detail-title"><?php echo htmlspecialchars($title); ?> <span style="font-weight: 400; color: var(--text-secondary);">(<?php echo $year; ?>)</span></h1>
                
                <?php if ($tagline): ?>
                    <div class="detail-tagline">"<?php echo htmlspecialchars($tagline); ?>"</div>
                <?php endif; ?>

                <div class="detail-pills">
                    <span class="rating-star" style="font-size: 1.1rem; background: rgba(255,255,255,0.08); padding: 6px 14px; border-radius: var(--radius-full);">
                        ⭐ <?php echo $rating; ?> <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 400;">(<?php echo $voteCount; ?> votes)</span>
                    </span>
                    
                    <span class="meta-pill">⏱️ <?php echo $runtime; ?></span>
                    <span class="meta-pill">📅 <?php echo $releaseDate; ?></span>
                    <span class="meta-pill">🎬 Director: <?php echo htmlspecialchars($directorNames); ?></span>
                </div>

                <!-- Genre Badges -->
                <?php if (!empty($movie['genres'])): ?>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 24px;">
                        <?php foreach ($movie['genres'] as $g): ?>
                            <a href="search.php?genre=<?php echo $g['id']; ?>" class="detail-genre-chip">
                                <?php echo htmlspecialchars($g['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Synopsis Box -->
                <div class="detail-synopsis-box">
                    <h3>Synopsis</h3>
                    <p><?php echo htmlspecialchars($overview); ?></p>
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; flex-wrap: wrap; gap: 16px;">
                    <a href="#streamSection" class="btn btn-primary">
                        ▶ Watch Full Movie (HD)
                    </a>

                    <?php if ($trailerKey): ?>
                        <button class="btn btn-glass btn-play-trailer" 
                                data-id="<?php echo $movieId; ?>" 
                                data-type="movie" 
                                data-title="<?php echo htmlspecialchars($title); ?>">
                            🎬 Official Trailer
                        </button>
                    <?php endif; ?>

                    <button class="btn btn-glass btn-favorite-action"
                            data-id="<?php echo $movieId; ?>"
                            data-type="movie"
                            data-title="<?php echo htmlspecialchars($title); ?>"
                            data-poster="<?php echo $poster; ?>"
                            data-rating="<?php echo $rating; ?>"
                            data-year="<?php echo $year; ?>">
                        ❤️ Add to Watchlist
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<main class="container">

    <!-- Embedded Player Section -->
    <section id="streamSection" style="margin-bottom: 54px;">
        <div class="section-header">
            <h2 class="section-title">Full Movie HD Stream Player</h2>
        </div>

        <div class="video-player-container">
            <div class="player-tabs">
                <button class="player-tab-btn active" onclick="switchPlayerSource('vidsrc_me', <?php echo $movieId; ?>)">Server 1 (VidSrc ME)</button>
                <button class="player-tab-btn" onclick="switchPlayerSource('vidsrc_cc', <?php echo $movieId; ?>)">Server 2 (VidSrc CC)</button>
                <button class="player-tab-btn" onclick="switchPlayerSource('embed_su', <?php echo $movieId; ?>)">Server 3 (Embed.su)</button>
                <button class="player-tab-btn" onclick="switchPlayerSource('superembed', <?php echo $movieId; ?>)">Server 4 (MultiEmbed)</button>
                <button class="player-tab-btn" onclick="switchPlayerSource('vidsrc_icu', <?php echo $movieId; ?>)">Server 5 (VidSrc ICU)</button>
                <?php if ($trailerKey): ?>
                    <button class="player-tab-btn" onclick="switchPlayerSource('youtube', '<?php echo $trailerKey; ?>')">YouTube Trailer</button>
                <?php endif; ?>
            </div>

            <div class="iframe-responsive">
                <iframe id="mainEmbedIframe" src="https://vidsrc.me/embed/movie?tmdb=<?php echo $movieId; ?>" allowfullscreen allow="autoplay; encrypted-media"></iframe>
            </div>
        </div>
    </section>

    <!-- Top Cast & Crew -->
    <?php if (!empty($cast)): ?>
        <section style="margin-bottom: 54px;">
            <div class="section-header">
                <h2 class="section-title">Top Billed Cast</h2>
            </div>
            
            <div class="cast-grid">
                <?php foreach ($cast as $actor): ?>
                    <?php 
                    $photo = !empty($actor['profile_path']) 
                        ? getPosterUrl($actor['profile_path'], 'w185')
                        : 'https://via.placeholder.com/185x225/121026/8b5cf6?text=No+Photo';
                    ?>
                    <div class="cast-card">
                        <img src="<?php echo $photo; ?>" alt="<?php echo htmlspecialchars($actor['name']); ?>" class="cast-photo" loading="lazy">
                        <div class="cast-info">
                            <div class="cast-name" title="<?php echo htmlspecialchars($actor['name']); ?>"><?php echo htmlspecialchars($actor['name']); ?></div>
                            <div class="cast-character" title="<?php echo htmlspecialchars($actor['character'] ?? ''); ?>"><?php echo htmlspecialchars($actor['character'] ?? ''); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Recommendations & Similar Movies -->
    <?php if (!empty($recommendations)): ?>
        <section style="margin-bottom: 60px;">
            <div class="section-header">
                <h2 class="section-title">More Like This</h2>
            </div>

            <div class="movies-grid">
                <?php 
                foreach ($recommendations as $item) {
                    $item['media_type'] = 'movie';
                    require __DIR__ . '/includes/movie_card.php';
                }
                ?>
            </div>
        </section>
    <?php endif; ?>

</main>

<script>
function switchPlayerSource(source, param) {
    const iframe = document.getElementById('mainEmbedIframe');
    const tabs = document.querySelectorAll('.player-tab-btn');
    
    tabs.forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');

    if (source === 'vidsrc_me') {
        iframe.src = `https://vidsrc.me/embed/movie?tmdb=${param}`;
    } else if (source === 'vidsrc_cc') {
        iframe.src = `https://vidsrc.cc/v2/embed/movie/${param}`;
    } else if (source === 'embed_su') {
        iframe.src = `https://embed.su/embed/movie/${param}`;
    } else if (source === 'superembed') {
        iframe.src = `https://multiembed.mov/?video_id=${param}&tmdb=1`;
    } else if (source === 'vidsrc_icu') {
        iframe.src = `https://vidsrc.icu/embed/movie/${param}`;
    } else if (source === 'youtube') {
        iframe.src = `https://www.youtube.com/embed/${param}?autoplay=1`;
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
