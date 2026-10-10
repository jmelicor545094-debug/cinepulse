<?php
/**
 * API Key Settings Modal
 */
$currentKey = getActiveApiKey();
$isDefault = ($currentKey === DEFAULT_TMDB_API_KEY);
?>

<!-- API Key Settings Modal -->
<div id="apiKeyModal" class="modal-backdrop">
    <div class="modal-box" style="max-width: 520px;">
        <div class="modal-header">
            <h3 class="modal-title">⚙️ TMDB API Settings</h3>
            <button class="modal-close-btn">&times;</button>
        </div>
        <div class="modal-body">
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 20px; line-height: 1.6;">
                CinePulse works seamlessly out of the box with our built-in TMDB API Key. You can optionally use your own TMDB v3 API Key below.
            </p>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">
                    Active API Key:
                </label>
                <input type="text" id="customApiKeyInput" class="search-input-field" style="padding-left: 16px;" value="<?php echo htmlspecialchars($currentKey); ?>" placeholder="Enter TMDB v3 API Key...">
                <span style="font-size: 0.78rem; color: <?php echo $isDefault ? 'var(--accent-cyan)' : 'var(--accent-emerald)'; ?>; margin-top: 6px; display: block;">
                    <?php echo $isDefault ? '● Currently using default system key' : '● Custom user API key active'; ?>
                </span>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <?php if (!$isDefault): ?>
                    <button id="resetApiKeyBtn" class="btn btn-glass" style="padding: 10px 20px;">Reset Default</button>
                <?php endif; ?>
                <button id="saveApiKeyBtn" class="btn btn-primary" style="padding: 10px 24px;">Save Key</button>
            </div>
        </div>
    </div>
</div>

<!-- Video Trailer Player Modal -->
<div id="trailerModal" class="modal-backdrop">
    <div class="modal-box" style="max-width: 960px;">
        <div class="modal-header">
            <h3 id="trailerModalTitle" class="modal-title">🎬 Movie Trailer</h3>
            <button class="modal-close-btn">&times;</button>
        </div>
        <div class="modal-body" style="padding: 0;">
            <!-- Dynamic Iframe Content -->
        </div>
    </div>
</div>
