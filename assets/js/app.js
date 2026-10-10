/**
 * CinePulse / Melicor Movie Room - Interactive JavaScript Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    initSearch();
    initWatchlist();
    initTrailerModal();
    initApiKeyModal();
});

/* ==========================================================================
   1. Live Debounced Search Auto-complete
   ========================================================================== */
function initSearch() {
    const searchInput = document.getElementById('searchInput');
    const dropdown = document.getElementById('searchDropdown');
    const searchForm = searchInput ? searchInput.closest('form') : null;
    if (!searchInput || !dropdown) return;

    let debounceTimer;

    const performLiveSearch = () => {
        const query = searchInput.value.trim();
        clearTimeout(debounceTimer);

        if (query.length < 2) {
            dropdown.classList.remove('active');
            dropdown.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`api.php?action=search&query=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.results || data.results.length === 0) {
                        dropdown.innerHTML = '<div class="search-result-item" style="justify-content:center; color:var(--text-muted); padding:16px;">No movies or TV shows found</div>';
                    } else {
                        dropdown.innerHTML = data.results.map(item => `
                            <a href="${item.media_type === 'tv' ? 'tv.php' : 'movie.php'}?id=${item.id}" class="search-result-item">
                                <img src="${item.poster}" alt="${escapeHtml(item.title)}" onerror="this.src='https://via.placeholder.com/92x138/121026/8b5cf6?text=No+Image'">
                                <div class="search-result-info">
                                    <h4>${escapeHtml(item.title)}</h4>
                                    <span>${item.media_type.toUpperCase()} • ${item.year || 'N/A'} • ⭐ ${item.rating}</span>
                                </div>
                            </a>
                        `).join('');
                    }
                    dropdown.classList.add('active');
                })
                .catch(err => {
                    console.error('Search error:', err);
                });
        }, 250);
    };

    searchInput.addEventListener('input', performLiveSearch);
    searchInput.addEventListener('focus', () => {
        if (searchInput.value.trim().length >= 2) {
            dropdown.classList.add('active');
        }
    });

    // Form submit listener
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            const query = searchInput.value.trim();
            if (!query) {
                e.preventDefault();
            }
        });
    }

    // Close dropdown on click outside
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('active');
        }
    });
}

/* ==========================================================================
   2. LocalStorage Watchlist Manager
   ========================================================================== */
function getWatchlist() {
    try {
        return JSON.parse(localStorage.getItem('CinePulse_Watchlist')) || [];
    } catch (e) {
        return [];
    }
}

function saveWatchlist(list) {
    localStorage.setItem('CinePulse_Watchlist', JSON.stringify(list));
    updateWatchlistBadge();
}

function updateWatchlistBadge() {
    const badge = document.getElementById('watchlistBadge');
    if (!badge) return;
    const list = getWatchlist();
    badge.textContent = list.length;
}

function toggleWatchlist(id, type, title, poster, rating, year) {
    let list = getWatchlist();
    const index = list.findIndex(item => item.id == id && item.type === type);
    let added = false;

    if (index > -1) {
        list.splice(index, 1);
        showToast(`Removed "${title}" from Watchlist`);
    } else {
        list.push({ id, type, title, poster, rating, year, addedAt: Date.now() });
        added = true;
        showToast(`Added "${title}" to Watchlist! ❤️`);
    }

    saveWatchlist(list);
    syncFavButtons();
    return added;
}

function syncFavButtons() {
    const list = getWatchlist();
    document.querySelectorAll('.card-favorite-btn, .btn-favorite-action').forEach(btn => {
        const id = btn.getAttribute('data-id');
        const type = btn.getAttribute('data-type') || 'movie';
        const isFav = list.some(item => item.id == id && item.type === type);
        
        if (isFav) {
            btn.classList.add('active');
            btn.setAttribute('title', 'Remove from Watchlist');
        } else {
            btn.classList.remove('active');
            btn.setAttribute('title', 'Add to Watchlist');
        }
    });
}

function initWatchlist() {
    updateWatchlistBadge();
    syncFavButtons();

    document.addEventListener('click', (e) => {
        const favBtn = e.target.closest('.card-favorite-btn, .btn-favorite-action');
        if (favBtn) {
            e.preventDefault();
            e.stopPropagation();
            const id = favBtn.getAttribute('data-id');
            const type = favBtn.getAttribute('data-type') || 'movie';
            const title = favBtn.getAttribute('data-title') || 'Movie';
            const poster = favBtn.getAttribute('data-poster') || '';
            const rating = favBtn.getAttribute('data-rating') || '0.0';
            const year = favBtn.getAttribute('data-year') || '';

            toggleWatchlist(id, type, title, poster, rating, year);
        }
    });
}

/* ==========================================================================
   3. Trailer Video Modal Manager
   ========================================================================== */
function initTrailerModal() {
    const modal = document.getElementById('trailerModal');
    if (!modal) return;

    const modalBody = modal.querySelector('.modal-body');
    const closeBtns = modal.querySelectorAll('.modal-close-btn, .modal-backdrop');

    closeBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            if (e.target === btn) {
                closeTrailerModal();
            }
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeTrailerModal();
        }
    });

    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('.btn-play-trailer');
        if (trigger) {
            e.preventDefault();
            const id = trigger.getAttribute('data-id');
            const type = trigger.getAttribute('data-type') || 'movie';
            const title = trigger.getAttribute('data-title') || 'Trailer';

            openTrailerModal(id, type, title);
        }
    });
}

function openTrailerModal(id, type, title) {
    const modal = document.getElementById('trailerModal');
    const modalTitle = document.getElementById('trailerModalTitle');
    const modalBody = modal.querySelector('.modal-body');
    if (!modal) return;

    modalTitle.textContent = `${title} - Official Trailer`;
    modalBody.innerHTML = `
        <div style="text-align:center; padding: 40px; color: var(--text-secondary);">
            <div class="spinner" style="font-size:2rem; margin-bottom:12px;">🎬</div>
            <p>Fetching official trailer from TMDB...</p>
        </div>
    `;

    modal.classList.add('active');

    fetch(`api.php?action=trailer&id=${id}&type=${type}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.youtube_url) {
                modalBody.innerHTML = `
                    <div class="iframe-responsive">
                        <iframe src="${data.youtube_url}" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                    </div>
                `;
            } else {
                modalBody.innerHTML = `
                    <div style="text-align:center; padding: 40px;">
                        <p style="color:var(--text-secondary); margin-bottom: 20px;">No official trailer video found for this title.</p>
                        <a href="https://www.youtube.com/results?search_query=${encodeURIComponent(title + ' trailer')}" target="_blank" class="btn btn-primary">
                            Search on YouTube ↗
                        </a>
                    </div>
                `;
            }
        })
        .catch(err => {
            modalBody.innerHTML = `<div style="text-align:center; padding:40px; color:#f43f5e;">Failed to load trailer data.</div>`;
        });
}

function closeTrailerModal() {
    const modal = document.getElementById('trailerModal');
    if (!modal) return;
    modal.classList.remove('active');
    const modalBody = modal.querySelector('.modal-body');
    if (modalBody) modalBody.innerHTML = '';
}

/* ==========================================================================
   4. API Key Manager Modal
   ========================================================================== */
function initApiKeyModal() {
    const modal = document.getElementById('apiKeyModal');
    const keyBtn = document.getElementById('openKeyModalBtn');
    if (!modal || !keyBtn) return;

    keyBtn.addEventListener('click', () => {
        modal.classList.add('active');
    });

    const closeBtns = modal.querySelectorAll('.modal-close-btn, .modal-backdrop');
    closeBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            if (e.target === btn) {
                modal.classList.remove('active');
            }
        });
    });

    const saveBtn = document.getElementById('saveApiKeyBtn');
    const resetBtn = document.getElementById('resetApiKeyBtn');
    const keyInput = document.getElementById('customApiKeyInput');

    if (saveBtn && keyInput) {
        saveBtn.addEventListener('click', () => {
            const val = keyInput.value.trim();
            if (!val) {
                showToast('Please enter an API Key');
                return;
            }
            fetch(`api.php?action=set_key&key=${encodeURIComponent(val)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast('Custom API Key saved! Reloading page...');
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        showToast(data.message || 'Error saving key');
                    }
                });
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            fetch('api.php?action=set_key&key=reset')
                .then(res => res.json())
                .then(data => {
                    showToast('API Key reset to default!');
                    setTimeout(() => location.reload(), 1000);
                });
        /* ==========================================================================
   5. Toast Helper Notification
   ========================================================================== */================== */
function showToast(message) {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `<span>✨ ${escapeHtml(message)}</span>`;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function escapeHtml(str) {
    return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
