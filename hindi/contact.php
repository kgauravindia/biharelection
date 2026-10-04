<?php
/**
 * Bihar Election - Official Contact & Grievance Desk (Hindi)
 */
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'संपर्क करें — बिहार इलेक्शन संपादकीय एवं सहायता केंद्र';
$pageDescription = 'संपादकीय पूछताछ, उम्मीदवार प्रोफाइल सुधार, विज्ञापन बुकिंग और नागरिक साझेदारी हेतु बिहार इलेक्शन टीम से संपर्क करें।';
$pageKeywords = 'संपर्क करें बिहार इलेक्शन, बिहार चुनाव हेल्पलाइन, उम्मीदवार प्रोफाइल सत्यापन, ऑफरप्लांट टेक्नोलॉजीज छपरा';
$pageCanonical = hindi_base_url('contact');
$activeNav = 'contact';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    $name = trim($_POST['name'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? 'General Inquiry');
    $message = trim($_POST['message'] ?? '');
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';

    if (empty($name) || empty($message)) {
        $error = 'कृपया अपना नाम और संदेश दर्ज करें।';
    } elseif (empty($mobile) && empty($email)) {
        $error = 'कृपया मोबाइल नंबर या ईमेल आईडी दर्ज करें ताकि हम आपसे संपर्क कर सकें।';
    } else {
        $pdo = Database::getConnection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO `contacts` (`name`, `mobile`, `email`, `subject`, `message`, `status`, `ip_address`, `created_at`)
                    VALUES (:name, :mobile, :email, :subject, :message, 'NEW', :ip, NOW())
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':mobile' => $mobile,
                    ':email' => $email,
                    ':subject' => $subject,
                    ':message' => $message,
                    ':ip' => $ip_address
                ]);
                $success = 'धन्यवाद! आपका संदेश सफलतापूर्वक प्राप्त हो गया है। हमारी संपादकीय टीम शीघ्र ही आपसे संपर्क करेगी।';
            } catch (Exception $e) {
                $error = 'तकनीकी समस्या के कारण संदेश नहीं भेजा जा सका। कृपया पुनः प्रयास करें अथवा सीधे ईमेल भेजें।';
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<main class="py-5" style="background: #f8fafc; min-height: 85vh;">
    <div class="container">
        
        <!-- Hero Header -->
        <div class="text-center mb-5">
            <span class="badge bg-danger-subtle text-danger fw-bold text-uppercase px-3 py-2 rounded-pill mb-3">सहायता एवं संवाद</span>
            <h1 class="display-6 fw-bold text-dark mb-3" style="font-family: 'Outfit', sans-serif;">
                हमसे संपर्क करें (Contact Us)
            </h1>
            <p class="lead text-muted mx-auto" style="max-width: 680px;">
                चुनावी डेटा में सुधार, उम्मीदवार प्रोफाइल अपडेट, विज्ञापन अनुरोध अथवा किसी भी सुझाव के लिए हमारी टीम से जुड़ें।
            </p>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- Contact Form -->
            <div class="col-lg-7">
                <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border">
                    <h3 class="fw-bold text-dark mb-4" style="font-family: 'Outfit', sans-serif;">संदेश भेजें</h3>

                    <?php if ($success): ?>
                        <div class="alert alert-success d-flex align-items-center gap-2 rounded-3 mb-4" role="alert">
                            <i class="bi bi-check-circle-fill fs-5"></i>
                            <div><?php echo htmlspecialchars($success); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold text-dark">आपका पूरा नाम <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-3 py-2.5" id="name" name="name" required placeholder="उदा. रमेश कुमार सिंह">
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="mobile" class="form-label fw-semibold text-dark">मोबाइल नंबर</label>
                                <input type="tel" class="form-control rounded-3 py-2.5" id="mobile" name="mobile" placeholder="उदा. 9876543210">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold text-dark">ईमेल आईडी</label>
                                <input type="email" class="form-control rounded-3 py-2.5" id="email" name="email" placeholder="उदा. name@example.com">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label fw-semibold text-dark">विषय / संदर्भ</label>
                            <select class="form-select rounded-3 py-2.5" id="subject" name="subject">
                                <option value="General Inquiry">सामान्य पूछताछ (General Inquiry)</option>
                                <option value="Data Correction">डेटा में सुधार / त्रुटि निवारण (Data Correction)</option>
                                <option value="Candidate Profile Update">उम्मीदवार प्रोफाइल अद्यतन (Candidate Profile)</option>
                                <option value="Advertisement Request">विज्ञापन एवं प्रायोजन (Advertisement)</option>
                                <option value="Editorial Feedback">संपादकीय सुझाव (Feedback)</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="message" class="form-label fw-semibold text-dark">संदेश / विवरण <span class="text-danger">*</span></label>
                            <textarea class="form-control rounded-3 p-3" id="message" name="message" rows="5" required placeholder="कृपया अपना संदेश या डेटा सुधार का विस्तृत विवरण यहाँ लिखें..."></textarea>
                        </div>

                        <button type="submit" name="submit_contact" class="btn btn-danger btn-lg rounded-pill px-5 fw-bold shadow-sm w-100">
                            संदेश भेजें &rarr;
                        </button>
                    </form>
                </div>
            </div>

            <!-- Contact Information Sidebar -->
            <div class="col-lg-5">
                <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border mb-4">
                    <h4 class="fw-bold text-dark mb-4" style="font-family: 'Outfit', sans-serif;">कार्यालय एवं संपर्क विवरण</h4>

                    <div class="d-flex gap-3 mb-4">
                        <div class="bg-danger-subtle text-danger p-3 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-geo-alt fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">प्रधान कार्यालय</h6>
                            <p class="text-muted small mb-0">
                                <strong>OfferPlant Technologies Pvt. Ltd.</strong><br>
                                छपरा, सारण (बिहार) - 841301
                            </p>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mb-4">
                        <div class="bg-primary-subtle text-primary p-3 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-envelope fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">आधिकारिक ईमेल</h6>
                            <p class="text-muted small mb-0">
                                <a href="mailto:<?php echo CONTACT_EMAIL; ?>" class="text-decoration-none text-dark fw-semibold"><?php echo CONTACT_EMAIL; ?></a>
                            </p>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mb-4">
                        <div class="bg-success-subtle text-success p-3 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-whatsapp fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">व्हाट्सएप चैनल</h6>
                            <p class="text-muted small mb-0">
                                <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="text-success text-decoration-none fw-semibold">दैनिक चुनावी अपडेट्स से जुड़ें &rarr;</a>
                            </p>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="p-3 bg-light rounded-3">
                        <div class="fw-bold text-dark small mb-1"><i class="bi bi-info-circle text-primary me-1"></i> अस्वीकरण:</div>
                        <p class="text-muted small mb-0">
                            बिहार इलेक्शन एक स्वतंत्र गैर-सरकारी पोर्टल है। सरकारी वोटर हेल्पलाइन के लिए भारत निर्वाचन आयोग के टोल-फ्री नंबर <strong>1950</strong> पर संपर्क करें।
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
