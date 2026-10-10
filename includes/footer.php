<?php
/**
 * Global Footer Component
 * Project: CinePulse
 */
?>

<footer class="site-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-brand">
                <div class="brand-logo">
                    <div class="logo-icon">🎬</div>
                    <div class="brand-text">Melicor <span class="brand-highlight">Movie Room</span></div>
                </div>
                <p>Your ultimate portal for exploring trending movies, TV shows, cast details, trailers, and personal watchlists in real-time.</p>
            </div>

            <div class="footer-column">
                <h4>Navigation</h4>
                <ul>
                    <li><a href="index.php">Home Spotlight</a></li>
                    <li><a href="search.php?type=movie">Browse Movies</a></li>
                    <li><a href="search.php?type=tv">TV Series</a></li>
                    <li><a href="watchlist.php">My Watchlist</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h4>Categories</h4>
                <ul>
                    <li><a href="search.php?genre=28">Action & Sci-Fi</a></li>
                    <li><a href="search.php?genre=35">Comedy & Humor</a></li>
                    <li><a href="search.php?genre=18">Drama & Romance</a></li>
                    <li><a href="search.php?genre=27">Horror & Thriller</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h4>Data Source</h4>
                <p style="font-size:0.85rem; line-height:1.6;">
                    Powered by <strong style="color:var(--text-main);">The Movie Database (TMDB) API</strong>. This product uses the TMDB API but is not endorsed or certified by TMDB.
                </p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Melicor Movie Room. Crafted with PHP & TMDB API. Built for cinematic movie lovers.</p>
        </div>
    </div>
</footer>

<?php require_once __DIR__ . '/key_modal.php'; ?>

<!-- Main App JavaScript -->
<script src="/assets/js/app.js"></script>
</body>
</html>
