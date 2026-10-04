<?php
/**
 * Bihar Election - About Platform & Editorial Mission (Hindi)
 */
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'हमारे बारे में — बिहार इलेक्शन डेटा एवं राजनीतिक सूचना हब';
$pageDescription = 'BiharElection.com बिहार का अग्रणी स्वतंत्र एवं निष्पक्ष नागरिक चुनावी डेटा पोर्टल है, जो 243 विधानसभा सीटों, 8,000+ पंचायतों और जनप्रतिनिधियों की सत्यापित जानकारी प्रदान करता है।';
$pageKeywords = 'हमारे बारे में बिहार इलेक्शन, बिहार चुनाव पोर्टल, ऑफरप्लांट टेक्नोलॉजीज छपरा, बिहार विधानसभा विश्लेषण, पंचायत डेटा बिहार';
$pageCanonical = hindi_base_url('about');
$activeNav = 'about';

include __DIR__ . '/includes/header.php';
?>

<main class="py-5" style="background: #f8fafc; min-height: 85vh;">
    <div class="container">
        
        <!-- Hero Section -->
        <div class="row align-items-center mb-5 pb-3">
            <div class="col-lg-7">
                <span class="badge bg-danger-subtle text-danger fw-bold text-uppercase px-3 py-2 rounded-pill mb-3">लोकतंत्र का सशक्तिकरण</span>
                <h1 class="display-5 fw-bold text-dark mb-3" style="font-family: 'Outfit', sans-serif;">
                    बिहार का सबसे व्यापक <span class="text-danger">चुनावी एवं नागरिक</span> सूचना पोर्टल
                </h1>
                <p class="lead text-muted mb-4" style="line-height: 1.7;">
                    <strong>BiharElection.com</strong> (संचालित: <strong>OfferPlant Technologies Private Limited</strong>) एक स्वतंत्र, गैर-पक्षपाती नागरिक डेटा मंच है जो बिहार के 38 जिलों, 243 विधानसभा क्षेत्रों और 8,053+ ग्राम पंचायतों में पारदर्शी, तथ्यात्मक एवं ऐतिहासिक चुनावी डेटा उपलब्ध कराने हेतु प्रतिबद्ध है।
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?php echo hindi_base_url('mla'); ?>" class="btn btn-danger btn-lg rounded-pill px-4 fw-bold shadow-sm">
                        243 विधानसभा क्षेत्र देखें &rarr;
                    </a>
                    <a href="<?php echo getPanchayatUrl(); ?>" class="btn btn-outline-dark btn-lg rounded-pill px-4 fw-bold">
                        पंचायत निर्देशिका
                    </a>
                </div>
            </div>
            <div class="col-lg-5 mt-4 mt-lg-0 text-center">
                <div class="bg-white p-4 rounded-4 shadow-sm border">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <div class="h2 fw-bold text-danger mb-0" style="font-family: 'Outfit', sans-serif;">243</div>
                                <span class="small text-muted fw-semibold">विधानसभा सीटें</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <div class="h2 fw-bold text-primary mb-0" style="font-family: 'Outfit', sans-serif;">40</div>
                                <span class="small text-muted fw-semibold">लोकसभा सीटें</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <div class="h2 fw-bold text-success mb-0" style="font-family: 'Outfit', sans-serif;">8,053+</div>
                                <span class="small text-muted fw-semibold">ग्राम पंचायतें</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <div class="h2 fw-bold text-warning mb-0" style="font-family: 'Outfit', sans-serif;">38</div>
                                <span class="small text-muted fw-semibold">जिला हब</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top text-start">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shield-check text-success fs-4"></i>
                            <span class="small text-muted">100% गैर-पक्षपाती एवं सत्यापित चुनाव आयोग व जनगणना रिकॉर्ड</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pillar Highlights -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                    <div class="bg-danger-subtle text-danger p-3 rounded-4 d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                        <i class="bi bi-newspaper fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2" style="font-family: 'Outfit', sans-serif;">जमीनी पत्रकारिता</h5>
                    <p class="text-muted small mb-0">बिहार के कोने-कोने से चुनावी माहौल, मतदान रुझान, स्थानीय जनमुद्दों और जमीनी नागरिक विमर्श पर निष्पक्ष रिपोर्टिंग।</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                    <div class="bg-primary-subtle text-primary p-3 rounded-4 d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                        <i class="bi bi-bar-chart-line fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2" style="font-family: 'Outfit', sans-serif;">डेटा एवं जनसांख्यिकी</h5>
                    <p class="text-muted small mb-0">जनगणना 2011 जनसांख्यिकी, प्रखंडवार आंकड़े, ऐतिहासिक विधायक/मुखिया रिकॉर्ड एवं आधिकारिक चुनाव आयोग डेटा अभिलेखागार।</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                    <div class="bg-success-subtle text-success p-3 rounded-4 d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                        <i class="bi bi-people fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2" style="font-family: 'Outfit', sans-serif;">नागरिक जुड़ाव</h5>
                    <p class="text-muted small mb-0">मतदाताओं को अपने स्थानीय प्रतिनिधियों (विधायक, सांसद, मुखिया, सरपंच, वार्ड पार्षद) से जुड़ने और उनकी जवाबदेही परखने का मंच।</p>
                </div>
            </div>
        </div>

        <!-- Mission & Editorial Standards -->
        <div class="bg-white rounded-4 shadow-sm p-4 p-lg-5 mb-5 border">
            <h3 class="fw-bold text-dark mb-4" style="font-family: 'Outfit', sans-serif;">हमारे सिद्धांत एवं संपादकीय मानक</h3>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <div class="text-danger fs-3"><i class="bi bi-check-circle-fill"></i></div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">सख्त गैर-पक्षपात एवं निष्पक्षता</h6>
                            <p class="text-muted small mb-0">हम किसी भी राजनीतिक दल, गठबंधन या उम्मीदवार का समर्थन नहीं करते। हमारा एकमात्र उद्देश्य नागरिकों तक शुद्ध तथ्य एवं प्रामाणिक आंकड़े पहुंचाना है।</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <div class="text-danger fs-3"><i class="bi bi-file-earmark-check-fill"></i></div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">सत्यापित सार्वजनिक स्रोत</h6>
                            <p class="text-muted small mb-0">हमारे चुनावी व जनसांख्यिकीय आंकड़े भारत निर्वाचन आयोग (ECI), राज्य निर्वाचन आयोग (SEC Bihar), भारत की जनगणना एवं आधिकारिक राजपत्रों पर आधारित हैं।</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <div class="text-danger fs-3"><i class="bi bi-shield-lock-fill"></i></div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">गोपनीयता एवं डेटा सुरक्षा</h6>
                            <p class="text-muted small mb-0">हम नागरिक गोपनीयता का पूर्ण सम्मान करते हैं और उपयोगकर्ता डेटा को व्यावसायिक उद्देश्यों के लिए किसी तीसरे पक्ष के साथ साझा नहीं करते।</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <div class="text-danger fs-3"><i class="bi bi-arrow-repeat"></i></div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">निरंतर अद्यतन एवं सुधार</h6>
                            <p class="text-muted small mb-0">यदि किसी भी आंकड़े या सूचना में विसंगति पाई जाती है, तो हमारी शोध टीम त्वरित समीक्षा कर उसे तुरंत अद्यतन करती है।</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
