<?php
/**
 * Bihar Election - Global Footer Component (Bootstrap 5.3)
 */
require_once __DIR__ . '/config.php';
?>

    <!-- Footer with Clear Non-Government Disclaimer & Social Channels -->
    <footer class="site-footer mt-5 pt-5 pb-4">
        <div class="container">
            <div class="row g-4 mb-4">
                
                <!-- Col 1: Brand & Disclaimer -->
                <div class="col-12 col-lg-4">
                    <div class="d-flex align-items-center gap-3 mb-3 text-nowrap">
                        <div class="bg-white p-2 rounded-3 shadow-sm d-inline-flex align-items-center justify-content-center">
                            <img src="<?php echo SITE_URL; ?>/assets/image/logo.png" alt="Bihar Election Logo" class="footer-logo-img" height="38">
                        </div>
                        <h2 class="h5 mb-0 text-white fw-bold text-nowrap" style="font-family: 'Outfit', sans-serif;">Bihar <span style="color: var(--accent-saffron);">Election</span></h2>
                    </div>
                    <p class="small text-white-50 lh-base">
                        Bihar Election is an independent non-governmental election data and political intelligence platform covering all 38 districts and 243 Vidhan Sabha constituencies in Bihar.
                    </p>
                    <div class="disclaimer-box p-3 rounded-end small text-white-50 bg-white bg-opacity-10 border-start border-3 border-warning">
                        <strong>Important Disclaimer:</strong> Bihar Election is a private, independent information platform. It is not affiliated with the Election Commission of India (ECI) or the Bihar State Election Commission (SEC).
                    </div>
                </div>

                <!-- Col 2: 9 Commissionaries (Alphabetical A-Z) -->
                <div class="col-6 col-md-4 col-lg-2">
                    <h3 class="h6 text-white fw-bold mb-3">9 Commissionaries</h3>
                    <ul class="list-unstyled footer-links small">
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('bhagalpur'); ?>" class="text-white-50 text-decoration-none">Bhagalpur</a></li>
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('darbhanga'); ?>" class="text-white-50 text-decoration-none">Darbhanga</a></li>
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('saharsa'); ?>" class="text-white-50 text-decoration-none">Kosi (Saharsa)</a></li>
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('gaya'); ?>" class="text-white-50 text-decoration-none">Magadh (Gaya)</a></li>
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('munger'); ?>" class="text-white-50 text-decoration-none">Munger</a></li>
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('patna'); ?>" class="text-white-50 text-decoration-none">👑 Patna (Capital)</a></li>
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('purnia'); ?>" class="text-white-50 text-decoration-none">Purnia</a></li>
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('saran'); ?>" class="text-white-50 text-decoration-none">Saran (Chhapra)</a></li>
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('muzaffarpur'); ?>" class="text-white-50 text-decoration-none">Tirhut (Muzaffarpur)</a></li>
                        <li class="mb-2"><a href="<?php echo getCensusUrl(); ?>" class="text-warning fw-bold text-decoration-none">📊 Census 2011 Hub &rarr;</a></li>
                    </ul>
                </div>

                <!-- Col 3: Legal & Policy Documents -->
                <div class="col-6 col-md-4 col-lg-3">
                    <h3 class="h6 text-white fw-bold mb-3">Company &amp; Portals</h3>
                    <ul class="list-unstyled footer-links small">
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/about" class="text-white-50 text-decoration-none">🏢 About Us</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/contact" class="text-white-50 text-decoration-none">📞 Contact Us</a></li>
                        <li class="mb-2"><a href="<?php echo getBlogUrl(); ?>" class="text-white-50 text-decoration-none">📰 News &amp; Blog</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/search-pin-code" class="text-white-50 text-decoration-none">📮 Search PIN Code</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/mission-and-vision" class="text-white-50 text-decoration-none">🎯 Mission &amp; Vision</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/privacy-policy" class="text-white-50 text-decoration-none">🔒 Privacy Policy</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/disclaimer" class="text-white-50 text-decoration-none">⚠️ Disclaimer Notice</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/terms-and-conditions" class="text-white-50 text-decoration-none">📜 Terms &amp; Conditions</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/advertise" class="text-white-50 text-decoration-none">📢 Advertise &amp; Sponsorships</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/login" class="text-warning fw-semibold text-decoration-none"><i class="bi bi-box-arrow-in-right"></i> Client / Citizen Login</a></li>
                    </ul>
                </div>

                <!-- Col 4: Official Social Channels -->
                <div class="col-12 col-md-4 col-lg-3">
                    <h3 class="h6 text-white fw-bold mb-3">Follow Official Channels</h3>
                    <ul class="list-unstyled footer-links small">
                        <li class="mb-2">
                            <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="text-success text-decoration-none d-flex align-items-center gap-2">
                                <i class="bi bi-whatsapp"></i> WhatsApp Channel
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo INSTAGRAM_URL; ?>" target="_blank" class="text-danger text-decoration-none d-flex align-items-center gap-2">
                                <i class="bi bi-instagram"></i> Instagram (@BiharElectionAI)
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo FACEBOOK_URL; ?>" target="_blank" class="text-primary text-decoration-none d-flex align-items-center gap-2">
                                <i class="bi bi-facebook"></i> Facebook (/BiharElectionAI)
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo TWITTER_URL; ?>" target="_blank" class="text-white-50 text-decoration-none d-flex align-items-center gap-2">
                                <i class="bi bi-twitter-x"></i> X / Twitter (@BiharElectionAI)
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo YOUTUBE_URL; ?>" target="_blank" class="text-danger text-decoration-none d-flex align-items-center gap-2">
                                <i class="bi bi-youtube"></i> YouTube (@BiharElectionAI)
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo TELEGRAM_URL; ?>" target="_blank" class="text-info text-decoration-none d-flex align-items-center gap-2">
                                <i class="bi bi-telegram"></i> Telegram (@BiharElectionAI)
                            </a>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Bottom Copyright & Social Icons Row -->
            <div class="pt-3 mt-3 border-top border-white border-opacity-10 d-flex flex-wrap justify-content-between align-items-center gap-3 small text-white-50">
                <div>&copy; <?php echo date('Y'); ?> Bihar Election. All Rights Reserved. &bull; Non-Government Civic Information Platform &bull; <a href="<?php echo SITE_URL; ?>/login" class="text-white-50 text-decoration-none me-2"><i class="bi bi-person-lock"></i> Client Login</a> &bull; <a href="<?php echo SITE_URL; ?>/admin/login.php" class="text-white-50 text-decoration-none"><i class="bi bi-shield-lock"></i> Admin Portal</a></div>
                <div class="d-flex gap-3 align-items-center">
                    <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="text-white-50 text-decoration-none fs-5"><i class="bi bi-whatsapp"></i></a>
                    <a href="<?php echo TELEGRAM_URL; ?>" target="_blank" class="text-white-50 text-decoration-none fs-5"><i class="bi bi-telegram"></i></a>
                    <a href="<?php echo INSTAGRAM_URL; ?>" target="_blank" class="text-white-50 text-decoration-none fs-5"><i class="bi bi-instagram"></i></a>
                    <a href="<?php echo FACEBOOK_URL; ?>" target="_blank" class="text-white-50 text-decoration-none fs-5"><i class="bi bi-facebook"></i></a>
                    <a href="<?php echo TWITTER_URL; ?>" target="_blank" class="text-white-50 text-decoration-none fs-5"><i class="bi bi-twitter-x"></i></a>
                    <a href="<?php echo YOUTUBE_URL; ?>" target="_blank" class="text-white-50 text-decoration-none fs-5"><i class="bi bi-youtube"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Citizen Login Required Modal for Phone Number Access -->
    <div class="modal fade" id="citizenLoginPhoneModal" tabindex="-1" aria-labelledby="citizenLoginPhoneModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                <div class="modal-header border-0 pb-0 bg-light">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4 pt-2">
                    <div class="rounded-circle bg-warning bg-opacity-15 text-warning p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-shield-lock-fill fs-1 text-warning"></i>
                    </div>
                    <h4 class="fw-bold text-navy font-heading mb-2">Citizen Login Required</h4>
                    <p class="text-muted small mb-4">
                        To protect public representatives from automated telemarketing and spam, please log in with your free <strong>Citizen Account</strong> to reveal full contact numbers.
                    </p>
                    
                    <div class="d-grid gap-2">
                        <a href="<?php echo SITE_URL; ?>/login" id="modalLoginBtn" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Citizen Login (Instant OTP / Password)
                        </a>
                        <a href="<?php echo SITE_URL; ?>/register" class="btn btn-outline-secondary rounded-pill fw-semibold">
                            <i class="bi bi-person-plus me-1"></i> New Citizen? Register Free
                        </a>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light justify-content-center py-2">
                    <small class="text-muted" style="font-size: 0.75rem;">🔒 DLT Compliant &bull; 100% Free Civic Platform</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Phone Reveal Quota Limit (10/day) Modal -->
    <div class="modal fade" id="phoneDailyLimitModal" tabindex="-1" aria-labelledby="phoneDailyLimitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                <div class="modal-header border-0 pb-0 bg-light">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4 pt-2">
                    <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-exclamation-octagon-fill fs-1 text-danger"></i>
                    </div>
                    <span class="badge bg-danger bg-opacity-15 text-danger fw-bold px-3 py-1 rounded-pill small mb-2 d-inline-block">Quota Limit Exceeded</span>
                    <h4 class="fw-bold text-navy font-heading mb-2">Daily Contact Limit Reached (10/10)</h4>
                    <p class="text-muted small mb-3">
                        To prevent automated data harvesting and protect elected representatives from bulk telemarketing, each citizen account is allowed to view up to <strong>10 contact numbers per day</strong>.
                    </p>
                    <div class="bg-light rounded-3 p-3 border mb-4 text-start small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Today's Contacts Viewed:</span>
                            <strong class="text-danger">10 / 10 Numbers (100%)</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Quota Reset Time:</span>
                            <strong class="text-dark">Tomorrow, 12:00 AM</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Previously Seen Contacts:</span>
                            <a href="<?php echo SITE_URL; ?>/dashboard.php" class="fw-bold text-primary text-decoration-none">View in Dashboard &rarr;</a>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="<?php echo SITE_URL; ?>/dashboard.php" class="btn btn-primary rounded-pill fw-bold shadow-sm py-2">
                            <i class="bi bi-speedometer2 me-1"></i> Open Citizen Dashboard
                        </a>
                        <button type="button" class="btn btn-outline-secondary rounded-pill fw-semibold py-2" data-bs-dismiss="modal">
                            Close
                        </button>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light justify-content-center py-2">
                    <small class="text-muted" style="font-size: 0.75rem;">🛡️ Data Protection &bull; Fair Usage Policy</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JavaScript Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/app.js"></script>

    <script>
    async function revealPhoneNumber(btn) {
        if (!btn) return;
        const phone = btn.getAttribute('data-phone');
        const name = btn.getAttribute('data-name') || '';
        if (!phone) return;

        // If already revealed and calling enabled
        if (btn.classList.contains('btn-success')) {
            window.location.href = 'tel:+91' + phone;
            return;
        }

        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span><span class="small">Verifying...</span>';

        try {
            const res = await fetch('<?php echo SITE_URL; ?>/api/reveal-phone.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ phone: phone, name: name })
            });
            const data = await res.json();

            btn.disabled = false;

            if (data.success) {
                const formatted = data.formatted_phone || ('+91 ' + phone);
                const raw = data.raw_phone || phone;
                
                btn.className = 'btn btn-sm btn-success rounded-pill px-2.5 py-0.5 small d-inline-flex align-items-center gap-1 reveal-phone-btn text-white shadow-sm';
                btn.innerHTML = `<i class="bi bi-telephone-outbound-fill"></i><span class="phone-text font-monospace fw-bold">${formatted}</span>`;
                btn.setAttribute('onclick', `window.location.href='tel:+91${raw}'`);
                btn.setAttribute('title', `Click to call ${formatted} (${data.reveals_today}/10 used today)`);

                // Update any daily counter badge on page if present
                const counterBadges = document.querySelectorAll('.user-daily-reveal-counter');
                counterBadges.forEach(b => {
                    b.innerText = data.reveals_today + '/10';
                });
            } else if (data.limit_reached) {
                btn.innerHTML = originalHtml;
                const limitModalEl = document.getElementById('phoneDailyLimitModal');
                if (limitModalEl && window.bootstrap) {
                    const modal = new bootstrap.Modal(limitModalEl);
                    modal.show();
                } else {
                    alert('Daily contact limit of 10 numbers reached. Quota resets tomorrow at 12:00 AM.');
                }
            } else if (data.require_login) {
                btn.innerHTML = originalHtml;
                const loginModalEl = document.getElementById('citizenLoginPhoneModal');
                if (loginModalEl && window.bootstrap) {
                    const modal = new bootstrap.Modal(loginModalEl);
                    modal.show();
                } else {
                    window.location.href = '<?php echo SITE_URL; ?>/login';
                }
            } else {
                btn.innerHTML = originalHtml;
                alert(data.message || 'Unable to reveal contact at this moment.');
            }
        } catch (err) {
            console.error('Error revealing phone:', err);
            btn.disabled = false;
            // Fallback direct reveal if offline/network error
            const phoneSpan = btn.querySelector('.phone-text');
            if (phoneSpan) {
                phoneSpan.innerText = '+91 ' + phone;
            }
            btn.classList.remove('btn-outline-success');
            btn.classList.add('btn-success', 'text-white');
            btn.setAttribute('onclick', `window.location.href='tel:+91${phone}'`);
        }
    }
    </script>
</body>
</html>
