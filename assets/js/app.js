/**
 * Bihar Election - Interactive Client Logic & Real-time Search Engine
 */

document.addEventListener('DOMContentLoaded', () => {
    initLiveSearch();
});

// Real-time Autocomplete and Instant Search Engine
function initLiveSearch() {
    const searchInput = document.getElementById('globalSearchInput');
    const dropdown = document.getElementById('searchDropdown');

    if (!searchInput || !dropdown) return;

    let debounceTimer;

    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.trim();
        clearTimeout(debounceTimer);

        if (query.length < 2) {
            dropdown.style.display = 'none';
            dropdown.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetchSearchData(query);
        }, 200);
    });

    // Close dropdown on outside click
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
}

function fetchSearchData(query) {
    const dropdown = document.getElementById('searchDropdown');
    
    fetch(`api/search.php?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            if (!data || data.length === 0) {
                dropdown.innerHTML = `
                    <div style="padding: 14px; text-align: center; color: #64748b; font-size: 0.9rem;">
                        No constituencies, districts, or candidates found for "<strong>${escapeHtml(query)}</strong>"
                    </div>`;
                dropdown.style.display = 'block';
                return;
            }

            let html = '';
            data.forEach(item => {
                let icon = '🏛️';
                let tag = item.type.toUpperCase();
                let url = item.url || '#';

                if (item.type === 'constituency') {
                    icon = '🗳️';
                } else if (item.type === 'district') {
                    icon = '📍';
                } else if (item.type === 'candidate') {
                    icon = '👤';
                }

                html += `
                    <a href="${url}" class="search-item">
                        <div>
                            <div style="font-weight: 700; font-size: 0.95rem; color: #0b192c;">
                                ${icon} ${item.title} <span style="font-size: 0.85rem; color: #64748b; font-weight: normal;">(${item.subtitle})</span>
                            </div>
                            <div class="search-item-meta">${item.extra || ''}</div>
                        </div>
                        <span class="ac-tag">${tag}</span>
                    </a>
                `;
            });

            dropdown.innerHTML = html;
            dropdown.style.display = 'block';
        })
        .catch(err => {
            console.error('Search query failed:', err);
        });
}

function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}
