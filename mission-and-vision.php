<?php
/**
 * Bihar Election - Mission & Vision (हमारा मिशन और विजन)
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth_helper.php';

$pageTitle = 'Mission & Vision (हमारा मिशन) — Bihar Election';
$pageDescription = 'BiharElection.com का मिशन है बिहार के प्रत्येक नागरिक एवं मतदाता तक निष्पक्ष, सटीक और समय पर चुनावी व लोकतांत्रिक जानकारी पहुँचाना।';
$pageKeywords = 'Mission Bihar Election, Bihar Election Vision, निष्पक्ष चुनावी कवरेज, बिहार चुनाव 2026, Voter Awareness Bihar';
$pageCanonical = SITE_URL . '/mission-and-vision/';
$activeNav = 'mission';

include __DIR__ . '/header.php';
?>

<main class="py-5" style="background: #f8fafc; min-height: 85vh;">
    <div class="container">
        
        <!-- Header Banner -->
        <div class="text-center mb-5 pb-2">
            <span class="badge bg-danger-subtle text-danger fw-bold text-uppercase px-3 py-2 rounded-pill mb-3">Our Core Purpose</span>
            <h1 class="display-6 fw-bold text-dark mb-3" style="font-family: 'Outfit', sans-serif;">
                हमारा मिशन और विज़न (Mission & Vision)
            </h1>
            <p class="lead text-muted mx-auto" style="max-width: 720px;">
                "हर मतदाता तक सही, निष्पक्ष और पारदर्शी जानकारी पहुँचाना — ताकि बिहार का लोकतंत्र बने और अधिक सशक्त!"
            </p>
        </div>

        <!-- Vision Hero Card -->
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="badge bg-warning text-dark fw-bold text-uppercase px-3 py-1 rounded-pill mb-2">लोकतांत्रिक संकल्प</span>
                    <h3 class="fw-bold text-dark mb-3" style="font-family: 'Outfit', sans-serif;">
                        📢 बिहार चुनाव की सबसे विश्वसनीय और निष्पक्ष खबरों का एकमात्र पता!
                    </h3>
                    <p class="text-muted" style="line-height: 1.8;">
                        <strong>BiharElection.com</strong> पर आपका स्वागत है — बिहार की राजनीति, विधान सभा सीटों (243 ACs), लोक सभा (40 PCs), नगर निकाय एवं त्रिस्तरीय पंचायती राज व्यवस्था (8,053+ ग्राम पंचायत, मुखिया, सरपंच, वार्ड एवं जिला परिषद) के विश्लेषण का सबसे भरोसेमंद मंच।
                    </p>
                    <p class="text-muted mb-0" style="line-height: 1.8;">
                        हमारा मानना है कि एक जागरूक मतदाता ही मजबूत लोकतंत्र की नींव है। इसलिए हमारा उद्देश्य बिना किसी राजनीतिक पूर्वाग्रह के केवल तथ्यों, डेटा और धरातल की सच्चाई को जनता के सामने प्रस्तुत करना है।
                    </p>
                </div>
                <div class="col-lg-4 text-center">
                    <div class="bg-light p-4 rounded-4 border">
                        <div class="display-3 text-danger mb-2">🗳️</div>
                        <h5 class="fw-bold text-dark mb-1">जय बिहार | जय लोकतंत्र</h5>
                        <p class="text-muted small mb-3">आपका वोट, आपका अधिकार, आपकी ताकत</p>
                        <a href="<?php echo SITE_URL; ?>/vidhan-sabha" class="btn btn-danger fw-bold rounded-pill px-4 btn-sm">
                            विधान सभा सीटें देखें &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4 Commitments -->
        <div class="row g-4 mb-5">
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                    <div class="bg-danger-subtle text-danger p-3 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">सटीक और समय पर रिपोर्टिंग</h5>
                    <p class="text-muted small mb-0">मतदान, मतगणना, नामांकन, और उम्मीदवार के हलफनामे की त्वरित और प्रमाणित जानकारी।</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                    <div class="bg-primary-subtle text-primary p-3 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                        <i class="bi bi-shield-check fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">निष्पक्ष और संतुलित कवरेज</h5>
                    <p class="text-muted small mb-0">किसी भी पार्टी या विचारधारा के प्रभाव से मुक्त रहकर केवल चुनावी डेटा और जनहित के मुद्दों पर केंद्रित रिपोर्टिंग।</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                    <div class="bg-success-subtle text-success p-3 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                        <i class="bi bi-lightbulb fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">मतदाता शिक्षा और जागरूकता</h5>
                    <p class="text-muted small mb-0">वोटर लिस्ट में नाम चेक करने, मतदान केंद्र खोजने और नए मतदाताओं को जागरूक करने की डिजिटल सुविधा।</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                    <div class="bg-warning-subtle text-dark p-3 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                        <i class="bi bi-award fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">लोकतंत्र को सशक्त बनाना</h5>
                    <p class="text-muted small mb-0">नागरिकों और उनके चुने हुए जनप्रतिनिधियों (MLA, MP, मुखिया, सरपंच) के बीच जवाबदेही और पारदर्शिता को बढ़ावा देना।</p>
                </div>
            </div>
        </div>

        <!-- Voter Checklist & Community -->
        <div class="row g-4 mb-5">
            <!-- Voter Checklist -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 h-100 bg-white">
                    <h4 class="fw-bold text-dark mb-3" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-check2-square text-success me-2"></i> जागरूक मतदाता चेकलिस्ट
                    </h4>
                    <p class="text-muted small mb-4">चुनाव के दिन मतदान केंद्र जाने से पहले यह 5 महत्वपूर्ण बिंदु अवश्य सुनिश्चित करें:</p>
                    
                    <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                        <li class="d-flex align-items-center gap-3 p-2 bg-light rounded-3">
                            <span class="badge bg-success rounded-circle p-2"><i class="bi bi-check-lg"></i></span>
                            <span class="text-dark fw-semibold small">अपना वोटर आईडी कार्ड और मतदाता सूची में नाम चेक करें।</span>
                        </li>
                        <li class="d-flex align-items-center gap-3 p-2 bg-light rounded-3">
                            <span class="badge bg-success rounded-circle p-2"><i class="bi bi-check-lg"></i></span>
                            <span class="text-dark fw-semibold small">अपने नजदीकी मतदान केंद्र (Polling Booth) की सही लोकेशन जानें।</span>
                        </li>
                        <li class="d-flex align-items-center gap-3 p-2 bg-light rounded-3">
                            <span class="badge bg-success rounded-circle p-2"><i class="bi bi-check-lg"></i></span>
                            <span class="text-dark fw-semibold small">अपने क्षेत्र के सभी प्रत्याशियों के ट्रैक रिकॉर्ड और पृष्ठभूमि की जांच करें।</span>
                        </li>
                        <li class="d-flex align-items-center gap-3 p-2 bg-light rounded-3">
                            <span class="badge bg-success rounded-circle p-2"><i class="bi bi-check-lg"></i></span>
                            <span class="text-dark fw-semibold small">बिना किसी प्रलोभन या दबाव के स्वतंत्र व विवेकपूर्ण निर्णय लें।</span>
                        </li>
                        <li class="d-flex align-items-center gap-3 p-2 bg-danger-subtle rounded-3">
                            <span class="badge bg-danger rounded-circle p-2"><i class="bi bi-heart-fill"></i></span>
                            <span class="text-danger fw-bold small">लोकतंत्र के महापर्व में अपना बहुमूल्य वोट ज़रूर दें!</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Social Media Hubs -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 h-100 bg-white">
                    <h4 class="fw-bold text-dark mb-3" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-globe text-primary me-2"></i> सोशल मीडिया कम्युनिटी (@BiharElectionAI)
                    </h4>
                    <p class="text-muted small mb-3">दैनिक समाचार बुलेटिन और त्वरित विश्लेषण के लिए हमसे जुड़ें:</p>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>प्लेटफॉर्म</th>
                                    <th>हैंडल / लिंक</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><i class="bi bi-whatsapp text-success me-2"></i> WhatsApp Channel</td>
                                    <td><a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="fw-bold text-success text-decoration-none">Bihar Election Official &rarr;</a></td>
                                </tr>
                                <tr>
                                    <td><i class="bi bi-instagram text-danger me-2"></i> Instagram</td>
                                    <td><a href="<?php echo INSTAGRAM_URL; ?>" target="_blank" class="text-decoration-none">instagram.com/BiharElectionAI</a></td>
                                </tr>
                                <tr>
                                    <td><i class="bi bi-facebook text-primary me-2"></i> Facebook</td>
                                    <td><a href="<?php echo FACEBOOK_URL; ?>" target="_blank" class="text-decoration-none">facebook.com/BiharElectionAI</a></td>
                                </tr>
                                <tr>
                                    <td><i class="bi bi-twitter-x text-dark me-2"></i> X / Twitter</td>
                                    <td><a href="<?php echo TWITTER_URL; ?>" target="_blank" class="text-decoration-none">x.com/BiharElectionAI</a></td>
                                </tr>
                                <tr>
                                    <td><i class="bi bi-youtube text-danger me-2"></i> YouTube</td>
                                    <td><a href="<?php echo YOUTUBE_URL; ?>" target="_blank" class="text-decoration-none">youtube.com/@BiharElectionAI</a></td>
                                </tr>
                                <tr>
                                    <td><i class="bi bi-telegram text-info me-2"></i> Telegram</td>
                                    <td><a href="<?php echo TELEGRAM_URL; ?>" target="_blank" class="text-decoration-none">t.me/BiharElectionAI</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<?php include __DIR__ . '/footer.php'; ?>
