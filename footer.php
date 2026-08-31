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
                            <img src="assets/image/logo.png" alt="Bihar Election Logo" class="footer-logo-img" height="38">
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

                <!-- Col 2: 38 District Hubs -->
                <div class="col-6 col-md-4 col-lg-2">
                    <h3 class="h6 text-white fw-bold mb-3">38 District Hubs</h3>
                    <ul class="list-unstyled footer-links small">
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('saran'); ?>" class="text-white-50 text-decoration-none">Saran (Chhapra)</a></li>
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('patna'); ?>" class="text-white-50 text-decoration-none">Patna</a></li>
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('muzaffarpur'); ?>" class="text-white-50 text-decoration-none">Muzaffarpur</a></li>
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('gaya'); ?>" class="text-white-50 text-decoration-none">Gaya</a></li>
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('bhagalpur'); ?>" class="text-white-50 text-decoration-none">Bhagalpur</a></li>
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('purnia'); ?>" class="text-white-50 text-decoration-none">Purnia</a></li>
                        <li class="mb-2"><a href="<?php echo getCensusUrl(); ?>" class="text-warning fw-bold text-decoration-none">📊 Census 2011 Hub &rarr;</a></li>
                    </ul>
                </div>

                <!-- Col 3: Assembly Constituencies -->
                <div class="col-6 col-md-4 col-lg-3">
                    <h3 class="h6 text-white fw-bold mb-3">Assembly Seats</h3>
                    <ul class="list-unstyled footer-links small">
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/mla/118-chapra" class="text-white-50 text-decoration-none">AC 118 - Chapra</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/mla/182-bankipur" class="text-white-50 text-decoration-none">AC 182 - Bankipur</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/mla/128-raghopur" class="text-white-50 text-decoration-none">AC 128 - Raghopur</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/mla/117-marhaura" class="text-white-50 text-decoration-none">AC 117 - Marhaura</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/mla/120-amnour" class="text-white-50 text-decoration-none">AC 120 - Amnour</a></li>
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
                        <li class="mt-3">
                            <a href="advertise.php" class="btn btn-warning btn-sm fw-bold px-3 py-1 text-dark shadow-sm">
                                <i class="bi bi-megaphone"></i> Advertise
                            </a>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Bottom Copyright & Social Icons Row -->
            <div class="pt-3 mt-3 border-top border-white border-opacity-10 d-flex flex-wrap justify-content-between align-items-center gap-3 small text-white-50">
                <div>&copy; <?php echo date('Y'); ?> Bihar Election. All Rights Reserved. &bull; <a href="<?php echo SITE_URL; ?>/disclaimer" class="text-white-50 text-decoration-none">Disclaimer</a> &bull; <a href="<?php echo SITE_URL; ?>/terms-and-conditions" class="text-white-50 text-decoration-none">Terms &amp; Conditions</a> &bull; <a href="admin/login.php" class="text-white-50 text-decoration-none"><i class="bi bi-shield-lock"></i> Admin Portal</a></div>
                <div class="d-flex gap-3 align-items-center">
                    <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="text-white-50 text-decoration-none fs-5"><i class="bi bi-whatsapp"></i></a>
                    <a href="<?php echo INSTAGRAM_URL; ?>" target="_blank" class="text-white-50 text-decoration-none fs-5"><i class="bi bi-instagram"></i></a>
                    <a href="<?php echo FACEBOOK_URL; ?>" target="_blank" class="text-white-50 text-decoration-none fs-5"><i class="bi bi-facebook"></i></a>
                    <a href="<?php echo TWITTER_URL; ?>" target="_blank" class="text-white-50 text-decoration-none fs-5"><i class="bi bi-twitter-x"></i></a>
                    <a href="<?php echo YOUTUBE_URL; ?>" target="_blank" class="text-white-50 text-decoration-none fs-5"><i class="bi bi-youtube"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JavaScript Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
