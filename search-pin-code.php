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

<main class="py-5" style="background: #f8fafc; min-height: 85vh;">
    <div class="container">
        
        <!-- Hero Header -->
        <div class="text-center mb-5">
            <span class="badge bg-danger-subtle text-danger fw-bold text-uppercase px-3 py-2 rounded-pill mb-2">Postal Intelligence</span>
            <h1 class="display-6 fw-bold text-dark mb-2" style="font-family: 'Outfit', sans-serif;">
                Search Postal PIN Codes & Post Offices
            </h1>
            <p class="text-muted mx-auto" style="max-width: 650px;">
                Instant lookup tool for all Post Offices, Branch Offices, Delivery Status, and Districts across Bihar and all Indian states.
            </p>
        </div>

        <!-- Search Box Card -->
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-5 mx-auto" style="max-width: 750px;">
            <div class="mb-4 text-center">
                <h5 class="fw-bold text-dark mb-1"><i class="bi bi-geo-alt-fill text-danger me-2"></i> Enter 6-Digit PIN Code or Office Name</h5>
                <p class="text-muted small">Search by PIN code (e.g. <code>800001</code>) or locality name (e.g. <code>Patna</code>, <code>Gaya</code>)</p>
            </div>

            <form id="pincodeSearchForm" onsubmit="handlePinSearch(event)" class="mb-3">
                <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden border">
                    <span class="input-group-text bg-white border-0 ps-4 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="pinInput" class="form-control border-0 px-2 fw-semibold" placeholder="e.g. 800001 or Patna" autocomplete="off" autofocus>
                    <button type="submit" class="btn btn-danger px-4 fw-bold" id="searchSubmitBtn">
                        Search PIN &rarr;
                    </button>
                </div>
            </form>

            <!-- Quick Suggestions -->
            <div class="mt-3">
                <span class="small fw-bold text-muted me-2">Popular Bihar Hubs:</span>
                <div class="d-inline-flex flex-wrap gap-1 mt-1">
                    <?php foreach (array_slice($popularPins, 0, 6) as $p): ?>
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2 small text-dark" onclick="setAndSearch('<?php echo $p['pin']; ?>')">
                            <?php echo $p['district']; ?> (<?php echo $p['pin']; ?>)
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Results Area Container -->
        <div id="resultsContainer" class="d-none mb-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h5 class="fw-bold text-dark mb-0" id="resultsTitle">Search Results</h5>
                        <span class="small text-muted" id="resultsSubtitle">Found matching post offices</span>
                    </div>
                    <div id="resultsBadge"></div>
                </div>

                <div id="resultsList" class="row g-3">
                    <!-- Dynamic Post Office Cards Injected Here -->
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loadingBox" class="d-none text-center py-5">
            <div class="spinner-border text-danger mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
            <h5 class="fw-bold text-dark">Searching Postal Records...</h5>
            <p class="text-muted small">Querying official post office directory...</p>
        </div>

        <!-- No Results State -->
        <div id="errorBox" class="d-none text-center py-5 card border-0 shadow-sm rounded-4 p-4 bg-white mb-5">
            <i class="bi bi-geo-alt display-3 text-warning mb-2 d-block"></i>
            <h5 class="fw-bold text-dark" id="errorMessage">No Records Found</h5>
            <p class="text-muted small" id="errorSubMessage">Please verify the PIN code or location name and try again.</p>
        </div>

        <!-- Popular Bihar District Pincodes Grid -->
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <h5 class="fw-bold text-dark mb-0" style="font-family: 'Outfit', sans-serif;">
                    <i class="bi bi-buildings me-2 text-primary"></i> Major District Headquarters & PIN Codes of Bihar
                </h5>
            </div>

            <div class="row g-3">
                <?php foreach ($popularPins as $p): ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="p-3 bg-light rounded-3 border h-100 d-flex flex-column justify-content-between cursor-pointer" onclick="setAndSearch('<?php echo $p['pin']; ?>')">
                            <div>
                                <span class="badge bg-danger mb-1 font-monospace"><?php echo $p['pin']; ?></span>
                                <h6 class="fw-bold text-dark mb-0"><?php echo $p['district']; ?></h6>
                                <span class="small text-muted"><?php echo $p['name']; ?></span>
                            </div>
                            <div class="mt-2 text-end">
                                <span class="small text-primary fw-semibold">Lookup &rarr;</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</main>

<script>
function setAndSearch(pin) {
    document.getElementById('pinInput').value = pin;
    handlePinSearch(new Event('submit'));
    window.scrollTo({ top: 150, behavior: 'smooth' });
}

function handlePinSearch(e) {
    if (e && e.preventDefault) e.preventDefault();
    const query = document.getElementById('pinInput').value.trim();
    if (!query) return;

    const resultsContainer = document.getElementById('resultsContainer');
    const resultsList = document.getElementById('resultsList');
    const loadingBox = document.getElementById('loadingBox');
    const errorBox = document.getElementById('errorBox');

    resultsContainer.classList.add('d-none');
    errorBox.classList.add('d-none');
    loadingBox.classList.remove('d-none');

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
            document.getElementById('resultsSubtitle').textContent = `Found ${list.length} Post Offices matching your search.`;
            document.getElementById('resultsBadge').innerHTML = `<span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold">${list.length} Records Found</span>`;

            let html = '';
            list.forEach(item => {
                const isBihar = item.State && item.State.toLowerCase() === 'bihar';
                const districtSlug = item.District ? item.District.toLowerCase().replace(/[^a-z0-9]+/g, '-') : '';

                html += `
                <div class="col-md-6 col-lg-4">
                    <div class="p-3 bg-light rounded-3 border h-100">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-danger font-monospace fs-6">${item.Pincode || query}</span>
                            <span class="badge bg-secondary-subtle text-dark small">${item.BranchType || 'Post Office'}</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">${item.Name}</h6>
                        <div class="small text-muted mb-2">
                            <i class="bi bi-geo-alt me-1 text-danger"></i> <strong>District:</strong> ${item.District}<br>
                            <i class="bi bi-map me-1 text-primary"></i> <strong>State:</strong> ${item.State}<br>
                            <i class="bi bi-box me-1 text-success"></i> <strong>Delivery:</strong> ${item.DeliveryStatus || 'Available'}
                        </div>
                        ${isBihar && districtSlug ? `
                            <a href="<?php echo SITE_URL; ?>/district/${districtSlug}" class="btn btn-outline-danger btn-sm w-100 rounded-pill mt-2">
                                <i class="bi bi-building me-1"></i> View ${item.District} Vidhan Sabha Data
                            </a>
                        ` : ''}
                    </div>
                </div>`;
            });

            resultsList.innerHTML = html;
            resultsContainer.classList.remove('d-none');
        } else {
            document.getElementById('errorMessage').textContent = `No Records Found for "${query}"`;
            document.getElementById('errorSubMessage').textContent = 'Please check the spelling or 6-digit postal code and try again.';
            errorBox.classList.remove('d-none');
        }
    })
    .catch(err => {
        console.error('API Error:', err);
        // Fallback to olaw API if available
        fetch(`https://olaw.in/api.php?api_key=OLAW_MUL3RFOEQXYHKGIC&action=pincode&pincode=${query}`)
        .then(res => res.json())
        .then(data => {
            loadingBox.classList.add('d-none');
            if (data && data.status === 'success' && data.data && data.data.length > 0) {
                document.getElementById('resultsTitle').textContent = `Postal Records for "${query}"`;
                document.getElementById('resultsSubtitle').textContent = `Found ${data.data.length} Post Offices matching your search.`;
                let html = '';
                data.data.forEach(item => {
                    html += `
                    <div class="col-md-6 col-lg-4">
                        <div class="p-3 bg-light rounded-3 border h-100">
                            <span class="badge bg-danger font-monospace fs-6 mb-2">${query}</span>
                            <h6 class="fw-bold text-dark mb-1">${item.locality_name || item.office_name}</h6>
                            <div class="small text-muted mb-2">
                                <strong>Office:</strong> ${item.office_name}<br>
                                <strong>District:</strong> ${item.district_name}<br>
                                <strong>State:</strong> ${item.state_name}
                            </div>
                        </div>
                    </div>`;
                });
                resultsList.innerHTML = html;
                resultsContainer.classList.remove('d-none');
            } else {
                errorBox.classList.remove('d-none');
            }
        })
        .catch(err2 => {
            loadingBox.classList.add('d-none');
            document.getElementById('errorMessage').textContent = 'Unable to fetch postal records';
            document.getElementById('errorSubMessage').textContent = 'Please check your internet connection or try again shortly.';
            errorBox.classList.remove('d-none');
        });
    });
}
</script>

<?php include __DIR__ . '/footer.php'; ?>
