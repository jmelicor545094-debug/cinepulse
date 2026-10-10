<?php
/**
 * AJAX API Proxy Handler
 * Project: CinePulse - PHP & TMDB Movie Application
 */

header('Content-Type: application/json');
require_once __DIR__ . '/config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    // Live search auto-complete
    case 'search':
        $query = trim($_GET['query'] ?? '');
        if (strlen($query) < 2) {
            echo json_encode(['results' => []]);
            exit;
        }
        $data = getTMDBData('search/multi', [
            'query' => $query,
            'page' => 1,
            'include_adult' => 'false'
        ]);
        
        // Filter out items without posters/backdrops or non-movie/tv
        $results = [];
        if (!empty($data['results'])) {
            foreach ($data['results'] as $item) {
                if (($item['media_type'] ?? '') === 'person') continue;
                $results[] = [
                    'id' => $item['id'],
                    'title' => $item['title'] ?? $item['name'] ?? 'Untitled',
                    'media_type' => $item['media_type'] ?? 'movie',
                    'year' => formatYear($item['release_date'] ?? $item['first_air_date'] ?? ''),
                    'rating' => formatRating($item['vote_average'] ?? 0),
                    'poster' => getPosterUrl($item['poster_path'] ?? '', 'w92'),
                    'overview' => $item['overview'] ?? ''
                ];
                if (count($results) >= 8) break; // top 8 quick results
            }
        }
        echo json_encode(['results' => $results]);
        exit;

    // Get movie/TV trailers
    case 'trailer':
        $id = (int)($_GET['id'] ?? 0);
        $type = ($_GET['type'] ?? 'movie') === 'tv' ? 'tv' : 'movie';
        
        if ($id <= 0) {
            echo json_encode(['error' => 'Invalid ID']);
            exit;
        }

        $videos = getTMDBData("{$type}/{$id}/videos");
        $trailerKey = null;

        if (!empty($videos['results'])) {
            // First look for Official YouTube Trailer
            foreach ($videos['results'] as $video) {
                if ($video['site'] === 'YouTube' && ($video['type'] === 'Trailer' || $video['type'] === 'Teaser')) {
                    $trailerKey = $video['key'];
                    if ($video['type'] === 'Trailer' && $video['official'] ?? false) {
                        break;
                    }
                }
            }
            // Fallback to any youtube video if official not found
            if (!$trailerKey && isset($videos['results'][0]['key'])) {
                $trailerKey = $videos['results'][0]['key'];
            }
        }

        echo json_encode([
            'success' => !empty($trailerKey),
            'trailer_key' => $trailerKey,
            'youtube_url' => $trailerKey ? "https://www.youtube.com/embed/{$trailerKey}?autoplay=1&rel=0" : null
        ]);
        exit;

    // Filter movies by genre or category
    case 'filter':
        $genreId = $_GET['genre'] ?? '';
        $sortBy = $_GET['sort'] ?? 'popularity.desc';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $mediaType = $_GET['media_type'] ?? 'movie';

        $endpoint = ($mediaType === 'tv') ? 'discover/tv' : 'discover/movie';
        $params = [
            'sort_by' => $sortBy,
            'page' => $page,
            'include_adult' => 'false'
        ];

        if (!empty($genreId)) {
            $params['with_genres'] = $genreId;
        }

        $data = getTMDBData($endpoint, $params);
        echo json_encode($data);
        exit;

    // Batch details for Watchlist items
    case 'watchlist_batch':
        $input = json_decode(file_get_contents('php_input') ?: '{}', true);
        $items = $_POST['items'] ?? $input['items'] ?? [];

        if (is_string($items)) {
            $items = json_decode($items, true) ?: [];
        }

        $results = [];
        foreach ($items as $item) {
            $id = (int)($item['id'] ?? 0);
            $type = ($item['type'] ?? 'movie') === 'tv' ? 'tv' : 'movie';
            if ($id <= 0) continue;

            $details = getTMDBData("{$type}/{$id}");
            if (!empty($details['id'])) {
                $results[] = [
                    'id' => $details['id'],
                    'type' => $type,
                    'title' => $details['title'] ?? $details['name'] ?? 'Untitled',
                    'poster' => getPosterUrl($details['poster_path'] ?? '', 'w342'),
                    'backdrop' => getBackdropUrl($details['backdrop_path'] ?? '', 'w780'),
                    'rating' => formatRating($details['vote_average'] ?? 0),
                    'year' => formatYear($details['release_date'] ?? $details['first_air_date'] ?? ''),
                    'overview' => substr($details['overview'] ?? '', 0, 120) . '...'
                ];
            }
        }

        echo json_encode(['results' => $results]);
        exit;

    // Save custom TMDB API key to session & cookie
    case 'set_key':
        $key = trim($_POST['key'] ?? $_GET['key'] ?? '');
        if ($key === 'reset') {
            unset($_SESSION['user_tmdb_api_key']);
            setcookie('user_tmdb_api_key', '', time() - 3600, '/');
            echo json_encode(['success' => true, 'message' => 'API Key reset to default']);
            exit;
        }

        if (strlen($key) > 10) {
            $_SESSION['user_tmdb_api_key'] = $key;
            setcookie('user_tmdb_api_key', $key, time() + (86400 * 30), '/'); // 30 days
            echo json_encode(['success' => true, 'message' => 'Custom TMDB API key saved successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid API key format']);
        }
        exit;

    default:
        echo json_encode(['error' => 'Unknown action']);
        exit;
}
