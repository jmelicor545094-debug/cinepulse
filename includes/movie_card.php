<?php
/**
 * Movie Card Component Snippet
 * Expects $item array containing TMDB movie or TV show data
 */

if (!isset($item) || !is_array($item)) return;

$id = $item['id'] ?? 0;
$title = $item['title'] ?? $item['name'] ?? 'Untitled';
$type = $item['media_type'] ?? ($mediaType ?? 'movie');
$date = $item['release_date'] ?? $item['first_air_date'] ?? '';
$year = formatYear($date);
$rating = formatRating($item['vote_average'] ?? 0);
$poster = getPosterUrl($item['poster_path'] ?? '', 'w500');
$detailUrl = ($type === 'tv') ? "tv.php?id={$id}" : "movie.php?id={$id}";
?>

<div class="movie-card">
    <div class="poster-container">
        <a href="<?php echo $detailUrl; ?>">
            <img src="<?php echo htmlspecialchars($poster); ?>" alt="<?php echo htmlspecialchars($title); ?>" class="movie-poster" loading="lazy" onerror="this.src='https://via.placeholder.com/500x750/121026/8b5cf6?text=No+Poster+Available';">
        </a>
        
        <!-- Rating Badge -->
        <div class="rating-badge">
            ⭐ <span><?php echo $rating; ?></span>
        </div>

        <!-- Favorite / Watchlist Button -->
        <button class="card-favorite-btn" 
                data-id="<?php echo $id; ?>" 
                data-type="<?php echo $type; ?>" 
                data-title="<?php echo htmlspecialchars($title); ?>" 
                data-poster="<?php echo htmlspecialchars($poster); ?>"
                data-rating="<?php echo $rating; ?>"
                data-year="<?php echo $year; ?>"
                title="Add to Watchlist">
            ❤️
        </button>

        <!-- Hover Overlay linking directly to full movie page -->
        <div class="poster-hover-overlay">
            <a href="<?php echo $detailUrl; ?>" class="play-circle-btn" title="Watch Full Movie">
                ▶
            </a>
        </div>
    </div>

    <div class="movie-card-info">
        <a href="<?php echo $detailUrl; ?>" class="card-title" title="<?php echo htmlspecialchars($title); ?>">
            <?php echo htmlspecialchars($title); ?>
        </a>
        <div class="card-sub">
            <span><?php echo $year; ?></span>
            <span class="media-tag"><?php echo strtoupper($type); ?></span>
        </div>
    </div>
</div>
