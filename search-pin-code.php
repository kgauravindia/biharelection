<?php
/**
 * Bihar Election - PIN Code Intelligence & Post Office Lookup
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth_helper.php';

$pageTitle = 'Search PIN Code — Bihar & India Postal Code Directory';
$pageDescription = 'Search PIN codes and post offices across Bihar and India. Instant lookup for Patna, Gaya, Muzaffarpur, Bhagalpur, and all 38 districts.';
$pageKeywords = 'Bihar PIN Code Search, Patna Pincode, Postal Codes Bihar, Post Office Bihar Election, District Pincode Directory';
$pageCanonical = SITE_URL . '/search-pin-code/';
$activeNav = 'pincode';

// Popular Bihar Pincodes
$popularPins = [
    ['pin' => '800001', 'name' => 'Patna G.P.O.', 'district' => 'Patna'],
    ['pin' => '823001', 'name' => 'Gaya H.O.', 'district' => 'Gaya'],
    ['pin' => '842001', 'name' => 'Muzaffarpur H.O.', 'district' => 'Muzaffarpur'],
    ['pin' => '812001', 'name' => 'Bhagalpur H.O.', 'district' => 'Bhagalpur'],
    ['pin' => '846004', 'name' => 'Darbhanga H.O.', 'district' => 'Darbhanga'],
    ['pin' => '854301', 'name' => 'Purnia H.O.', 'district' => 'Purnia'],
    ['pin' => '803101', 'name' => 'Biharsharif H.O.', 'district' => 'Nalanda'],
    ['pin' => '841301', 'name' => 'Chhapra H.O.', 'district' => 'Saran'],
    ['pin' => '802301', 'name' => 'Ara H.O.', 'district' => 'Bhojpur'],
    ['pin' => '851101', 'name' => 'Begusarai H.O.', 'district' => 'Begusarai'],
    ['pin' => '845401', 'name' => 'Motihari H.O.', 'district' => 'East Champaran'],
    ['pin' => '844101', 'name' => 'Hajipur H.O.', 'district' => 'Vaishali'],
];

include __DIR__ . '/header.php';
?>

<main class="py-4 py-md-5" style="background: #f8fafc; min-height: 85vh;">
    <div class="container px-3 px-sm-4">
        
        <!-- Hero Header -->
        <div class="text-center mb-4 mb-md-5">
            <span class="badge bg-danger-subtle text-danger fw-bold text-uppercase px-3 py-1.5 rounded-pill mb-2" style="font-size: 0.75rem;">
                <i class="bi bi-geo-alt-fill me-1"></i> Postal Intelligence
            </span>
            <h1 class="h3 h2-md fw-bold text-dark mb-2" style="font-family: 'Outfit', sans-serif;">
                Search Postal PIN Codes & Post Offices
            </h1>
            <p class="text-muted mx-auto small fs-md-6 mb-0 px-2" style="max-width: 650px;">
                Instant lookup for all Post Offices, Delivery Status, Branch Offices, and Districts across Bihar & India.
            </p>
        </div>

        <!-- Search Box Card -->
        <div class="card border-0 shadow-sm rounded-4 p-3 p-sm-4 p-md-5 bg-white mb-4 mb-md-5 mx-auto" style="max-width: 750px;">
            <div class="mb-3 mb-md-4 text-center">
                <h2 class="h5 fw-bold text-dark mb-1 d-flex align-items-center justify-content-center flex-wrap gap-1">
                    <i class="bi bi-search text-danger"></i>
                    <span>Enter 6-Digit PIN or Locality</span>
                </h2>
                <p class="text-muted small mb-0">Search by PIN code (e.g. <code>800001</code>) or office name (e.g. <code>Patna</code>, <code>Gaya</code>)</p>
            </div>

            <form id="pincodeSearchForm" onsubmit="handlePinSearch(event)" class="mb-3">
                <div class="pin-search-input-wrap">
                    <div class="input-group shadow-sm rounded-pill border overflow-hidden bg-white p-1">
                        <span class="input-group-text bg-white border-0 ps-3 text-muted">
                            <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
                        </span>
                        <input type="text" id="pinInput" class="form-control border-0 px-2 fw-semibold fs-6" placeholder="e.g. 800001 or Chapra" autocomplete="off" autofocus aria-label="Enter PIN code or location">
                        <button type="button" id="clearBtn" class="btn btn-link text-muted border-0 pe-2 d-none" onclick="clearPinInput()" title="Clear">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                        <button type="submit" class="btn btn-danger px-3 px-sm-4 fw-bold rounded-pill d-flex align-items-center gap-1" id="searchSubmitBtn">
                            <span>Search</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Quick Suggestions -->
            <div class="mt-2 pt-2 border-top">
                <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-1 gap-sm-2">
                    <span class="small fw-bold text-muted text-nowrap" style="font-size: 0.8rem;">
                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i>Quick Hubs:
                    </span>
                    <div class="d-flex flex-wrap gap-1.5 w-100">
                        <?php foreach (array_slice($popularPins, 0, 6) as $p): ?>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill py-1 px-2.5 small text-dark d-inline-flex align-items-center gap-1 quick-pin-btn" onclick="setAndSearch('<?php echo $p['pin']; ?>')">
                                <span class="fw-semibold"><?php echo $p['district']; ?></span>
                                <span class="badge bg-secondary-subtle text-dark font-monospace" style="font-size: 0.7rem;"><?php echo $p['pin']; ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loadingBox" class="d-none text-center py-5">
            <div class="spinner-border text-danger mb-3" role="status" style="width: 2.75rem; height: 2.75rem;"></div>
            <h5 class="fw-bold text-dark mb-1">Searching Postal Records...</h5>
            <p class="text-muted small">Querying official post office database...</p>
        </div>

        <!-- No Results State -->
        <div id="errorBox" class="d-none text-center py-4 py-md-5 card border-0 shadow-sm rounded-4 p-4 bg-white mb-4 mb-md-5 mx-auto" style="max-width: 650px;">
            <i class="bi bi-exclamation-circle-fill display-4 text-warning mb-2 d-block"></i>
            <h5 class="fw-bold text-dark mb-1" id="errorMessage">No Records Found</h5>
            <p class="text-muted small mb-0" id="errorSubMessage">Please verify the 6-digit postal code or locality spelling and try again.</p>
        </div>

        <!-- Results Area Container -->
        <div id="resultsContainer" class="d-none mb-4 mb-md-5">
            <div class="card border-0 shadow-sm rounded-4 p-3 p-sm-4 bg-white">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 pb-3 border-bottom">
                    <div>
                        <h2 class="h5 fw-bold text-dark mb-0" id="resultsTitle">Search Results</h2>
                        <span class="small text-muted" id="resultsSubtitle">Found matching post offices</span>
                    </div>
                    <div id="resultsBadge"></div>
                </div>

                <div id="resultsList" class="row g-2 g-sm-3">
                    <!-- Dynamic Post Office Cards Injected Here -->
                </div>
            </div>
        </div>

        <!-- Popular Bihar District Pincodes Grid -->
        <div class="card border-0 shadow-sm rounded-4 p-3 p-sm-4 p-md-5 bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 mb-md-4 pb-2 border-bottom gap-2">
                <h2 class="h5 fw-bold text-dark mb-0" style="font-family: 'Outfit', sans-serif;">
                    <i class="bi bi-buildings me-2 text-primary"></i> Major District Headquarters & PIN Codes of Bihar
                </h2>
                <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 small">38 Districts Hub</span>
            </div>

            <div class="row g-2 g-sm-3">
                <?php foreach ($popularPins as $p): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="p-2.5 p-sm-3 bg-light rounded-3 border h-100 d-flex flex-column justify-content-between district-pin-card" onclick="setAndSearch('<?php echo $p['pin']; ?>')">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-danger font-monospace px-2 py-1" style="font-size: 0.78rem;"><?php echo $p['pin']; ?></span>
                                    <i class="bi bi-arrow-up-right-circle text-primary opacity-75 d-none d-sm-inline"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-0 fs-6"><?php echo $p['district']; ?></h6>
                                <small class="text-muted d-block text-truncate" style="font-size: 0.75rem;"><?php echo $p['name']; ?></small>
                            </div>
                            <div class="mt-2 pt-1 border-top text-end">
                                <span class="small text-primary fw-semibold" style="font-size: 0.75rem;">Lookup &rarr;</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</main>

<!-- Copy Toast Notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <div id="pinToast" class="toast align-items-center text-bg-dark border-0 rounded-3 shadow" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2 py-2">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span id="pinToastMsg">PIN code copied to clipboard!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<style>
.district-pin-card {
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
}
.district-pin-card:hover, .district-pin-card:active {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    border-color: #dc3545 !important;
}
.quick-pin-btn {
    transition: background-color 0.15s, transform 0.1s;
    font-size: 0.78rem;
}
.quick-pin-btn:hover, .quick-pin-btn:active {
    background-color: #e2e8f0;
    transform: scale(0.98);
}
.pin-result-card {
    transition: box-shadow 0.15s ease, border-color 0.15s ease;
}
.pin-result-card:hover {
    box-shadow: 0 4px 14px rgba(0,0,0,0.05);
}
@media (max-width: 575.98px) {
    .pin-search-input-wrap .input-group {
        border-radius: 1rem !important;
    }
    .pin-search-input-wrap .btn {
        padding-left: 0.85rem !important;
        padding-right: 0.85rem !important;
        font-size: 0.88rem;
    }
}
</style>

<script>
const pinInput = document.getElementById('pinInput');
const clearBtn = document.getElementById('clearBtn');

pinInput.addEventListener('input', function() {
    if (this.value.trim().length > 0) {
        clearBtn.classList.remove('d-none');
    } else {
        clearBtn.classList.add('d-none');
    }
});

function clearPinInput() {
    pinInput.value = '';
    clearBtn.classList.add('d-none');
    pinInput.focus();
}

function setAndSearch(pin) {
    pinInput.value = pin;
    clearBtn.classList.remove('d-none');
    handlePinSearch(new Event('submit'));
}

function copyPin(pin) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(pin).then(() => {
            showToast(`PIN Code ${pin} copied!`);
        });
    }
}

function showToast(msg) {
    const toastEl = document.getElementById('pinToast');
    document.getElementById('pinToastMsg').textContent = msg;
    if (window.bootstrap && bootstrap.Toast) {
        const toast = new bootstrap.Toast(toastEl, { delay: 2000 });
        toast.show();
    } else {
        toastEl.classList.add('show');
        setTimeout(() => toastEl.classList.remove('show'), 2000);
    }
}

function handlePinSearch(e) {
    if (e && e.preventDefault) e.preventDefault();
    const query = pinInput.value.trim();
    if (!query) return;

    const resultsContainer = document.getElementById('resultsContainer');
    const resultsList = document.getElementById('resultsList');
    const loadingBox = document.getElementById('loadingBox');
    const errorBox = document.getElementById('errorBox');

    resultsContainer.classList.add('d-none');
    errorBox.classList.add('d-none');
    loadingBox.classList.remove('d-none');

    // Scroll smoothly towards search feedback on mobile
    if (window.innerWidth < 768) {
        loadingBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Determine if PIN code or Locality query
    const isPincode = /^\d{6}$/.test(query);
    const apiUrl = isPincode 
        ? `https://api.postalpincode.in/pincode/${query}` 
        : `https://api.postalpincode.in/postoffice/${encodeURIComponent(query)}`;

    fetch(apiUrl)
    .then(res => res.json())
    .then(data => {
        loadingBox.classList.add('d-none');
        if (data && data[0] && data[0].Status === 'Success' && data[0].PostOffice && data[0].PostOffice.length > 0) {
            const list = data[0].PostOffice;
            document.getElementById('resultsTitle').textContent = `Postal Records for "${query}"`;
            document.getElementById('resultsSubtitle').textContent = `Found ${list.length} matching post office(s).`;
            document.getElementById('resultsBadge').innerHTML = `<span class="badge bg-success-subtle text-success px-2.5 py-1.5 rounded-pill fw-bold" style="font-size:0.75rem;">${list.length} Found</span>`;

            let html = '';
            list.forEach(item => {
                const isBihar = item.State && item.State.toLowerCase() === 'bihar';
                const districtSlug = item.District ? item.District.toLowerCase().replace(/[^a-z0-9]+/g, '-') : '';
                const itemPin = item.Pincode || query;

                html += `
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="p-3 bg-light rounded-3 border h-100 pin-result-card d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center gap-1.5">
                                    <span class="badge bg-danger font-monospace fs-6 px-2.5 py-1">${itemPin}</span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1.5 rounded-2 border-0" onclick="copyPin('${itemPin}')" title="Copy PIN">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                                <span class="badge bg-secondary-subtle text-dark small text-truncate" style="max-width: 120px;">${item.BranchType || 'Post Office'}</span>
                            </div>

                            <h6 class="fw-bold text-dark mb-2 fs-6">${item.Name}</h6>
                            
                            <div class="small text-muted mb-2 d-flex flex-column gap-1">
                                <div><i class="bi bi-geo-alt me-1 text-danger"></i><strong>District:</strong> ${item.District || 'N/A'}</div>
                                <div><i class="bi bi-map me-1 text-primary"></i><strong>State:</strong> ${item.State || 'N/A'}</div>
                                <div><i class="bi bi-box me-1 text-success"></i><strong>Delivery:</strong> ${item.DeliveryStatus || 'Available'}</div>
                                ${item.Circle ? `<div><i class="bi bi-circle me-1 text-secondary"></i><strong>Circle:</strong> ${item.Circle}</div>` : ''}
                            </div>
                        </div>

                        <div class="pt-2 border-top mt-2">
                            ${isBihar && districtSlug ? `
                                <a href="<?php echo SITE_URL; ?>/district/${districtSlug}" class="btn btn-outline-danger btn-sm w-100 rounded-pill d-flex align-items-center justify-content-center gap-1 py-1.5">
                                    <i class="bi bi-building"></i>
                                    <span>${item.District} Vidhan Sabha</span>
                                </a>
                            ` : `
                                <button type="button" class="btn btn-outline-secondary btn-sm w-100 rounded-pill d-flex align-items-center justify-content-center gap-1 py-1.5" onclick="copyPin('${itemPin}')">
                                    <i class="bi bi-copy"></i>
                                    <span>Copy ${itemPin}</span>
                                </button>
                            `}
                        </div>
                    </div>
                </div>`;
            });

            resultsList.innerHTML = html;
            resultsContainer.classList.remove('d-none');
            
            // Smooth scroll to results
            resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            document.getElementById('errorMessage').textContent = `No Records Found for "${query}"`;
            document.getElementById('errorSubMessage').textContent = 'Please check the spelling or 6-digit postal code and try again.';
            errorBox.classList.remove('d-none');
            errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    })
    .catch(err => {
        console.error('API Error:', err);
        // Fallback to secondary API
        fetch(`https://olaw.in/api.php?api_key=OLAW_MUL3RFOEQXYHKGIC&action=pincode&pincode=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            loadingBox.classList.add('d-none');
            if (data && data.status === 'success' && data.data && data.data.length > 0) {
                document.getElementById('resultsTitle').textContent = `Postal Records for "${query}"`;
                document.getElementById('resultsSubtitle').textContent = `Found ${data.data.length} matching post office(s).`;
                document.getElementById('resultsBadge').innerHTML = `<span class="badge bg-success-subtle text-success px-2.5 py-1.5 rounded-pill fw-bold" style="font-size:0.75rem;">${data.data.length} Found</span>`;

                let html = '';
                data.data.forEach(item => {
                    html += `
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="p-3 bg-light rounded-3 border h-100 pin-result-card d-flex flex-column justify-content-between">
                            <div>
                                <span class="badge bg-danger font-monospace fs-6 mb-2 px-2.5 py-1">${query}</span>
                                <h6 class="fw-bold text-dark mb-1 fs-6">${item.locality_name || item.office_name}</h6>
                                <div class="small text-muted mb-2 d-flex flex-column gap-1">
                                    <div><strong>Office:</strong> ${item.office_name}</div>
                                    <div><strong>District:</strong> ${item.district_name}</div>
                                    <div><strong>State:</strong> ${item.state_name}</div>
                                </div>
                            </div>
                            <div class="pt-2 border-top mt-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm w-100 rounded-pill py-1.5" onclick="copyPin('${query}')">
                                    <i class="bi bi-copy me-1"></i> Copy PIN
                                </button>
                            </div>
                        </div>
                    </div>`;
                });
                resultsList.innerHTML = html;
                resultsContainer.classList.remove('d-none');
                resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                errorBox.classList.remove('d-none');
                errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        })
        .catch(err2 => {
            loadingBox.classList.add('d-none');
            document.getElementById('errorMessage').textContent = 'Unable to fetch postal records';
            document.getElementById('errorSubMessage').textContent = 'Please check your internet connection or try again shortly.';
            errorBox.classList.remove('d-none');
            errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    });
}
</script>

<?php include __DIR__ . '/footer.php'; ?>
