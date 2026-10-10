<?php
/**
 * CinePulse - TV Show Details Page
 * Displays TV show metadata, seasons, episode player, cast, and recommendations
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

// Require login to watch TV shows
requireLogin();

$tvId = (int)($_GET['id'] ?? 0);

if ($tvId <= 0) {
    header('Location: index.php');
    exit;
}

$show = getTMDBData("tv/{$tvId}", [
    'append_to_response' => 'credits,videos,recommendations,similar'
]);

if (empty($show['id'])) {
    $pageTitle = 'TV Series Not Found';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container" style="padding: 100px 0; text-align: center;">';
    echo '<h2>🎬 TV Show Not Found</h2>';
    echo '<p style="color: var(--text-secondary); margin: 16px 0;">The requested TV series could not be loaded from TMDB.</p>';
    echo '<a href="index.php" class="btn btn-primary">Return to Home</a>';
    echo '</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$title = $show['name'] ?? 'Untitled TV Show';
$pageTitle = $title . ' (' . formatYear($show['first_air_date'] ?? '') . ')';

$poster = getPosterUrl($show['poster_path'] ?? '', 'w500');
$backdrop = getBackdropUrl($show['backdrop_path'] ?? '', 'original');
$rating = formatRating($show['vote_average'] ?? 0);
$voteCount = number_format($show['vote_count'] ?? 0);
$year = formatYear($show['first_air_date'] ?? '');
$firstAirDate = formatDate($show['first_air_date'] ?? '');
$seasonsCount = $show['number_of_seasons'] ?? 1;
$episodesCount = $show['number_of_episodes'] ?? 0;
$status = $show['status'] ?? 'N/A';
$tagline = $show['tagline'] ?? '';
$overview = $show['overview'] ?? 'No synopsis available for this TV series.';

// Cast & Crew
$cast = array_slice($show['credits']['cast'] ?? [], 0, 12);
$recommendations = array_slice($show['recommendations']['results'] ?? $show['similar']['results'] ?? [], 0, 10);

// Find YouTube Trailer
$videos = $show['videos']['results'] ?? [];
$trailerKey = null;
foreach ($videos as $v) {
    if (($v['site'] ?? '') === 'YouTube' && (($v['type'] ?? '') === 'Trailer' || ($v['type'] ?? '') === 'Teaser')) {
        $trailerKey = $v['key'];
        if (($v['type'] ?? '') === 'Trailer' && ($v['official'] ?? false)) break;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- TV Detail Hero Backdrop Header -->
<div class="movie-detail-hero">
    <div class="movie-detail-backdrop" style="background-image: url('<?php echo $backdrop; ?>');"></div>
    <div class="container">
        <div class="movie-detail-overlay">
            
            <!-- Poster -->
            <div class="movie-detail-poster-wrapper">
                <img src="<?php echo $poster; ?>" alt="<?php echo htmlspecialchars($title); ?>" style="width: 100%; display: block;" onerror="this.src='https://via.placeholder.com/500x750/121026/8b5cf6?text=No+Poster';">
            </div>

            <!-- Main Info -->
            <div class="movie-detail-main">
                <h1 class="detail-title"><?php echo htmlspecialchars($title); ?> <span style="font-weight: 400; color: var(--text-secondary);">(<?php echo $year; ?>)</span></h1>
                
                <?php if ($tagline): ?>
                    <div class="detail-tagline">"<?php echo htmlspecialchars($tagline); ?>"</div>
                <?php endif; ?>

                <div class="detail-pills">
                    <span class="rating-star" style="font-size: 1.1rem; background: rgba(255,255,255,0.08); padding: 6px 14px; border-radius: var(--radius-full);">
                        ⭐ <?php echo $rating; ?> <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 400;">(<?php echo $voteCount; ?> votes)</span>
                    </span>
                    
                    <span class="meta-pill">📺 <?php echo $seasonsCount; ?> Season<?php echo $seasonsCount > 1 ? 's' : ''; ?></span>
                    <span class="meta-pill">📼 <?php echo $episodesCount; ?> Episodes</span>
                    <span class="meta-pill">📌 Status: <?php echo htmlspecialchars($status); ?></span>
                </div>

                <!-- Genre Badges -->
                <?php if (!empty($show['genres'])): ?>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 24px;">
                        <?php foreach ($show['genres'] as $g): ?>
                            <a href="search.php?type=tv&genre=<?php echo $g['id']; ?>" class="detail-genre-chip">
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

                <!-- Actions -->
                <div style="display: flex; flex-wrap: wrap; gap: 16px;">
                    <a href="#tvStreamSection" class="btn btn-primary">
                        ▶ Watch TV Episode (HD)
                    </a>

                    <?php if ($trailerKey): ?>
                        <button class="btn btn-glass btn-play-trailer" 
                                data-id="<?php echo $tvId; ?>" 
                                data-type="tv" 
                                data-title="<?php echo htmlspecialchars($title); ?>">
                            🎬 Official Trailer
                        </button>
                    <?php endif; ?>

                    <button class="btn btn-glass btn-favorite-action"
                            data-id="<?php echo $tvId; ?>"
                            data-type="tv"
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

    <!-- TV Episode Player Section -->
    <section id="tvStreamSection" style="margin-bottom: 54px;">
        <div class="section-header">
            <h2 class="section-title">TV Series Episode HD Player</h2>
        </div>

        <div style="display: flex; gap: 16px; margin-bottom: 20px; align-items: center; flex-wrap: wrap;">
            <label style="font-weight: 700; color: var(--text-main);">Season:</label>
            <select id="seasonSelect" onchange="updateTvEpisodePlayer()" class="search-input-field" style="width: auto; padding-left: 16px;">
                <?php for ($s = 1; $s <= $seasonsCount; $s++): ?>
                    <option value="<?php echo $s; ?>">Season <?php echo $s; ?></option>
                <?php endfor; ?>
            </select>

            <label style="font-weight: 700; color: var(--text-main); margin-left: 12px;">Episode:</label>
            <input type="number" id="episodeInput" value="1" min="1" max="100" onchange="updateTvEpisodePlayer()" class="search-input-field" style="width: 100px; padding-left: 16px;">
        </div>

        <div class="video-player-container">
            <div class="player-tabs">
                <button class="player-tab-btn active" onclick="setTvSource('vidsrc_me')">Server 1 (VidSrc ME)</button>
                <button class="player-tab-btn" onclick="setTvSource('vidsrc_cc')">Server 2 (VidSrc CC)</button>
                <button class="player-tab-btn" onclick="setTvSource('embed_su')">Server 3 (Embed.su)</button>
                <button class="player-tab-btn" onclick="setTvSource('superembed')">Server 4 (MultiEmbed)</button>
                <button class="player-tab-btn" onclick="setTvSource('vidsrc_icu')">Server 5 (VidSrc ICU)</button>
            </div>

            <div class="iframe-responsive">
                <iframe id="tvEmbedIframe" src="https://vidsrc.me/embed/tv?tmdb=<?php echo $tvId; ?>&season=1&episode=1" allowfullscreen allow="autoplay; encrypted-media"></iframe>
            </div>
        </div>
    </section>

    <!-- Top Billed Cast -->
    <?php if (!empty($cast)): ?>
        <section style="margin-bottom: 54px;">
            <div class="section-header">
                <h2 class="section-title">Series Cast</h2>
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

    <!-- Similar TV Shows -->
    <?php if (!empty($recommendations)): ?>
        <section style="margin-bottom: 60px;">
            <div class="section-header">
                <h2 class="section-title">Similar TV Series</h2>
            </div>

            <div class="movies-grid">
                <?php 
                foreach ($recommendations as $item) {
                    $item['media_type'] = 'tv';
                    require __DIR__ . '/includes/movie_card.php';
                }
                ?>
            </div>
        </section>
    <?php endif; ?>

</main>

<script>
let currentTvSource = 'vidsrc_me';
const currentTvId = <?php echo $tvId; ?>;

function updateTvEpisodePlayer() {
    const season = document.getElementById('seasonSelect')?.value || 1;
    const episode = document.getElementById('episodeInput')?.value || 1;
    const iframe = document.getElementById('tvEmbedIframe');

    if (currentTvSource === 'vidsrc_me') {
        iframe.src = `https://vidsrc.me/embed/tv?tmdb=${currentTvId}&season=${season}&episode=${episode}`;
    } else if (currentTvSource === 'vidsrc_cc') {
        iframe.src = `https://vidsrc.cc/v2/embed/tv/${currentTvId}/${season}/${episode}`;
    } else if (currentTvSource === 'embed_su') {
        iframe.src = `https://embed.su/embed/tv/${currentTvId}/${season}/${episode}`;
    } else if (currentTvSource === 'superembed') {
        iframe.src = `https://multiembed.mov/?video_id=${currentTvId}&tmdb=1&s=${season}&e=${episode}`;
    } else if (currentTvSource === 'vidsrc_icu') {
        iframe.src = `https://vidsrc.icu/embed/tv/${currentTvId}/${season}/${episode}`;
    }
}

function setTvSource(src) {
    currentTvSource = src;
    const tabs = document.querySelectorAll('.player-tab-btn');
    tabs.forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');
    updateTvEpisodePlayer();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
