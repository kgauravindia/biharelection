<?php
/**
 * Bihar Election - Global Hindi Footer Component (Bootstrap 5.3)
 */
require_once __DIR__ . '/functions.php';
?>

    <!-- Footer with Clear Non-Government Disclaimer & Social Channels -->
    <footer class="site-footer mt-5 pt-5 pb-4 px-3 px-md-0">
        <div class="container">
            <div class="row g-4 mb-4">
                
                <!-- Col 1: Brand & Disclaimer -->
                <div class="col-12 col-lg-4">
                    <div class="d-flex align-items-center gap-3 mb-3 text-nowrap">
                        <div class="bg-white p-2 rounded-3 shadow-sm d-inline-flex align-items-center justify-content-center">
                            <img src="<?php echo SITE_URL; ?>/assets/image/logo.png" alt="बिहार इलेक्शन लोगो" class="footer-logo-img" width="38" height="38">
                        </div>
                        <h2 class="h5 mb-0 text-white fw-bold text-nowrap" style="font-family: var(--font-heading);">Bihar <span style="color: var(--accent);">Election</span></h2>
                    </div>
                    <p class="small text-white-50 lh-base mb-3">
                        बिहार इलेक्शन राज्य के सभी 38 जिलों, 534 प्रखंडों और 243 विधानसभा क्षेत्रों को कवर करने वाला एक निष्पक्ष एवं स्वतंत्र गैर-सरकारी चुनावी डेटा व राजनीतिक विश्लेषण मंच है।
                    </p>
                    <div class="disclaimer-box">
                        <strong class="text-warning d-block mb-1"><i class="bi bi-shield-exclamation me-1"></i> महत्वपूर्ण अस्वीकरण:</strong>
                        बिहार इलेक्शन एक स्वतंत्र नागरिक सूचना पोर्टल है। इसका भारत निर्वाचन आयोग (ECI) अथवा बिहार राज्य निर्वाचन आयोग (SEC) से कोई संबद्धता नहीं है।
                    </div>
                </div>

                <!-- Col 2: 9 Divisions (प्रमंडल) -->
                <div class="col-6 col-md-4 col-lg-2">
                    <h3 class="footer-heading">9 प्रमंडल</h3>
                    <ul class="list-unstyled footer-links small mb-0">
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('bhagalpur'); ?>" class="footer-link">भागलपुर</a></li>
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('darbhanga'); ?>" class="footer-link">दरभंगा</a></li>
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('saharsa'); ?>" class="footer-link">कोसी (सहरसा)</a></li>
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('gaya'); ?>" class="footer-link">मगध (गया)</a></li>
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('munger'); ?>" class="footer-link">मुंगेर</a></li>
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('patna'); ?>" class="footer-link text-warning fw-semibold">👑 पटना (राजधानी)</a></li>
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('purnia'); ?>" class="footer-link">पूर्णिया</a></li>
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('saran'); ?>" class="footer-link">सारण (छपरा)</a></li>
                        <li class="mb-2"><a href="<?php echo getDistrictUrl('muzaffarpur'); ?>" class="footer-link">तिरहुत (मुजफ्फरपुर)</a></li>
                        <li class="mb-2"><a href="<?php echo getCensusUrl(); ?>" class="footer-link text-warning fw-bold">📊 जनगणना 2011 हब &rarr;</a></li>
                        <li class="mb-2"><a href="<?php echo getCasteSurveyUrl(); ?>" class="footer-link text-warning fw-bold">📋 2022 जाति गणना कोड &rarr;</a></li>
                        <li class="mb-2"><a href="<?php echo getBiharActsUrl(); ?>" class="footer-link text-success fw-bold">⚖️ बिहार अधिनियम (1937–2026) &rarr;</a></li>
                    </ul>
                </div>

                <!-- Col 3: Legal & Policy Documents -->
                <div class="col-6 col-md-4 col-lg-3">
                    <h3 class="footer-heading">पोर्टल एवं नीतियां</h3>
                    <ul class="list-unstyled footer-links small mb-0">
                        <li class="mb-2"><a href="<?php echo hindi_base_url('about'); ?>" class="footer-link">🏢 हमारे बारे में</a></li>
                        <li class="mb-2"><a href="<?php echo hindi_base_url('contact'); ?>" class="footer-link">📞 संपर्क करें</a></li>
                        <li class="mb-2"><a href="<?php echo getBlogUrl(); ?>" class="footer-link">📰 समाचार एवं ब्लॉग</a></li>
                        <li class="mb-2"><a href="<?php echo hindi_base_url('search-pin-code'); ?>" class="footer-link">📮 पिन कोड खोजें</a></li>
                        <li class="mb-2"><a href="<?php echo hindi_base_url('mission-and-vision'); ?>" class="footer-link">🎯 उद्देश्य एवं विजन</a></li>
                        <li class="mb-2"><a href="<?php echo hindi_base_url('privacy-policy'); ?>" class="footer-link">🔒 गोपनीयता नीति</a></li>
                        <li class="mb-2"><a href="<?php echo hindi_base_url('disclaimer'); ?>" class="footer-link">⚠️ अस्वीकरण सूचना</a></li>
                        <li class="mb-2"><a href="<?php echo hindi_base_url('terms-and-conditions'); ?>" class="footer-link">📜 नियम एवं शर्तें</a></li>
                        <li class="mb-2"><a href="<?php echo hindi_base_url('advertise'); ?>" class="footer-link text-warning fw-semibold">📢 विज्ञापन एवं प्रचार</a></li>
                        <li class="mb-2"><a href="<?php echo hindi_base_url('login'); ?>" class="footer-link text-warning fw-semibold"><i class="bi bi-box-arrow-in-right"></i> नागरिक लॉगिन</a></li>
                    </ul>
                </div>

                <!-- Col 4: Official Social Channels -->
                <div class="col-12 col-md-4 col-lg-3">
                    <h3 class="footer-heading">आधिकारिक चैनल</h3>
                    <ul class="list-unstyled footer-links small mb-3">
                        <li class="mb-2">
                            <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="footer-link text-success fw-semibold">
                                <i class="bi bi-whatsapp fs-6"></i> व्हाट्सएप चैनल (WhatsApp)
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo INSTAGRAM_URL; ?>" target="_blank" class="footer-link">
                                <i class="bi bi-instagram fs-6 text-danger"></i> इंस्टाग्राम (@BiharElectionAI)
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo FACEBOOK_URL; ?>" target="_blank" class="footer-link">
                                <i class="bi bi-facebook fs-6 text-primary"></i> फेसबुक (/BiharElectionAI)
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo TWITTER_URL; ?>" target="_blank" class="footer-link">
                                <i class="bi bi-twitter-x fs-6"></i> एक्स / ट्विटर (@BiharElectionAI)
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo YOUTUBE_URL; ?>" target="_blank" class="footer-link">
                                <i class="bi bi-youtube fs-6 text-danger"></i> यूट्यूब (@BiharElectionAI)
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo TELEGRAM_URL; ?>" target="_blank" class="footer-link">
                                <i class="bi bi-telegram fs-6 text-info"></i> टेलीग्राम (@BiharElectionAI)
                            </a>
                        </li>
                    </ul>

                    <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);">
                        <div class="fw-bold text-white small mb-1"><i class="bi bi-envelope-at text-warning me-1"></i> संपर्क एवं पूछताछ:</div>
                        <a href="mailto:<?php echo CONTACT_EMAIL; ?>" class="small text-white-50 text-decoration-none hover-underline text-break"><?php echo CONTACT_EMAIL; ?></a>
                    </div>
                </div>

            </div>

            <!-- Bottom Copyright & Navigation Strip -->
            <div class="border-top border-secondary border-opacity-25 pt-4 mt-2 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 text-center text-md-start">
                <div class="small text-white-50">
                    &copy; <?php echo date('Y'); ?> <strong>BiharElection.com</strong> (बिहार इलेक्शन). सर्वाधिकार सुरक्षित।
                </div>
                <div class="d-flex flex-wrap justify-content-center gap-3 small text-white-50">
                    <a href="<?php echo hindi_base_url('privacy-policy'); ?>" class="text-white-50 text-decoration-none hover-underline">गोपनीयता नीति</a>
                    <span>&bull;</span>
                    <a href="<?php echo hindi_base_url('terms-and-conditions'); ?>" class="text-white-50 text-decoration-none hover-underline">नियम एवं शर्तें</a>
                    <span>&bull;</span>
                    <a href="<?php echo hindi_base_url('disclaimer'); ?>" class="text-white-50 text-decoration-none hover-underline">अस्वीकरण</a>
                    <span>&bull;</span>
                    <a href="<?php echo SITE_URL; ?>/sitemap.xml" class="text-white-50 text-decoration-none hover-underline">साइटमैप</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <!-- Custom Platform Script -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js?v=2.4"></script>
</body>
</html>
