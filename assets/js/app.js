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
    
    const baseUrl = window.SITE_URL || '';
    fetch(`${baseUrl}/api/search.php?q=${encodeURIComponent(query)}`)
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
                let tag = (item.type || 'INFO').toUpperCase();
                let tagStyle = 'background: #f1f5f9; color: #1e3a8a;';
                let url = item.url || '#';

                if (item.type === 'mla' || item.type === 'constituency') {
                    icon = '🗳️';
                    tag = 'MLA';
                    tagStyle = 'background: #dbeafe; color: #1e40af;';
                } else if (item.type === 'mp') {
                    icon = '🏛️';
                    tag = 'MP';
                    tagStyle = 'background: #f1f5f9; color: #0f172a;';
                } else if (item.type === 'mlc') {
                    icon = '📜';
                    tag = 'MLC';
                    tagStyle = 'background: #e0e7ff; color: #3730a3;';
                } else if (item.type === 'mukhiya') {
                    icon = '👑';
                    tag = 'MUKHIYA';
                    tagStyle = 'background: #fef3c7; color: #92400e; font-weight: 700;';
                } else if (item.type === 'sarpanch') {
                    icon = '⚖️';
                    tag = 'SARPANCH';
                    tagStyle = 'background: #ede9fe; color: #6b21a8; font-weight: 700;';
                } else if (item.type === 'village') {
                    icon = '🏘️';
                    tag = 'VILLAGE';
                    tagStyle = 'background: #dcfce7; color: #166534; font-weight: 700;';
                } else if (item.type === 'town') {
                    icon = '🏙️';
                    tag = 'TOWN';
                    tagStyle = 'background: #fce7f3; color: #9d174d;';
                } else if (item.type === 'district') {
                    icon = '🏢';
                    tag = 'DISTRICT';
                    tagStyle = 'background: #fef08a; color: #854d0e;';
                } else if (item.type === 'block') {
                    icon = '📍';
                    tag = 'BLOCK';
                    tagStyle = 'background: #ccfbf1; color: #115e59;';
                } else if (item.type === 'candidate') {
                    icon = '👤';
                    tag = 'LEADER';
                    tagStyle = 'background: #e2e8f0; color: #334155;';
                }

                // If title already includes an emoji prefix, don't double prepend
                let displayTitle = item.title || '';
                let titleHtml = escapeHtml(displayTitle);
                if (!displayTitle.match(/^[\u{1F300}-\u{1F9FF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}]/u)) {
                    titleHtml = `${icon} ${titleHtml}`;
                }

                let subHtml = item.subtitle ? `<span style="font-size: 0.82rem; color: #64748b; font-weight: normal; margin-left: 6px;">(${escapeHtml(item.subtitle)})</span>` : '';

                html += `
                    <a href="${url}" class="search-item">
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 700; font-size: 0.95rem; color: #0b192c; line-height: 1.35;">
                                ${titleHtml} ${subHtml}
                            </div>
                            <div class="search-item-meta" style="margin-top: 3px;">${escapeHtml(item.extra || '')}</div>
                        </div>
                        <span class="ac-tag" style="${tagStyle} flex-shrink: 0; margin-left: 10px;">${tag}</span>
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
