<?php
/**
 * BiharElection.com - Bihar Legislative Council (Vidhan Parishad) Biennial Election 2026
 * Dedicated hub for the ongoing 4 Graduates' & 4 Teachers' Constituencies Biennial Elections.
 * Reference: CEO Bihar Notification No. M2–03(TC&GC)/2026-5369 / ECI TCGCGajat2026.pdf
 */
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'बिहार विधान परिषद चुनाव 2026: 8 स्नातक एवं शिक्षक सीटें, गजट अधिसूचना एवं मतदाता निर्देश';
$pageDescription = 'बिहार विधान परिषद द्विवार्षिक चुनाव 2026 की आधिकारिक समय सारिणी, गजट अधिसूचना, जिलावार निर्वाचन क्षेत्र मैपिंग एवं प्ररूप 18/19 पात्रता डेटा।';
$pageKeywords = 'बिहार विधान परिषद चुनाव 2026, स्नातक निर्वाचन क्षेत्र बिहार, शिक्षक निर्वाचन क्षेत्र बिहार, पटना स्नातक एमएलसी, तिरहुत स्नातक, दरभंगा स्नातक, कोसी स्नातक, सारण शिक्षक, प्ररूप 18, प्ररूप 19';
$pageCanonical = hindi_base_url('vidhan-parishad-election');
$activeNav = 'mlc';

include __DIR__ . '/includes/header.php';

// 8 Constituencies Data
$seats = [
    [
        'id' => 'patna-graduates',
        'type' => 'Graduates',
        'name' => 'Patna Graduates',
        'name_hi' => 'पटना स्नातक निर्वाचन क्षेत्र',
        'ro' => 'Divisional Commissioner, Patna',
        'ro_hi' => 'प्रमंडलीय आयुक्त, पटना',
        'ro_url' => 'https://patnadivision.bih.nic.in/',
        'hq' => 'Patna',
        'districts' => ['Patna', 'Nalanda', 'Nawada'],
        'districts_hi' => ['पटना', 'नालंदा', 'नवादा'],
        'color' => 'primary',
        'term_expiry' => '16 November 2026',
        'description' => 'Covers the state capital Patna along with Nalanda and Nawada districts.'
    ],
    [
        'id' => 'tirhut-graduates',
        'type' => 'Graduates',
        'name' => 'Tirhut Graduates',
        'name_hi' => 'तिरहुत स्नातक निर्वाचन क्षेत्र',
        'ro' => 'Divisional Commissioner, Tirhut (Muzaffarpur)',
        'ro_hi' => 'प्रमंडलीय आयुक्त, तिरहुत (मुजफ्फरपुर)',
        'ro_url' => 'https://tirhutdivision.bih.nic.in/',
        'hq' => 'Muzaffarpur',
        'districts' => ['Muzaffarpur', 'Vaishali', 'Sitamarhi', 'Sheohar'],
        'districts_hi' => ['मुजफ्फरपुर', 'वैशाली', 'सीतामढ़ी', 'शिवहर'],
        'color' => 'primary',
        'term_expiry' => '16 November 2026',
        'description' => 'Covers the central-north region of Tirhut division across 4 major districts.'
    ],
    [
        'id' => 'darbhanga-graduates',
        'type' => 'Graduates',
        'name' => 'Darbhanga Graduates',
        'name_hi' => 'दरभंगा स्नातक निर्वाचन क्षेत्र',
        'ro' => 'Divisional Commissioner, Darbhanga',
        'ro_hi' => 'प्रमंडलीय आयुक्त, दरभंगा',
        'ro_url' => 'https://darbhangadivision.bih.nic.in/',
        'hq' => 'Darbhanga',
        'districts' => ['Darbhanga', 'Madhubani', 'Samastipur', 'Begusarai'],
        'districts_hi' => ['दरभंगा', 'मधुबनी', 'समस्तीपुर', 'बेगूसराय'],
        'color' => 'primary',
        'term_expiry' => '16 November 2026',
        'description' => 'Encompasses the Mithila heartland including Darbhanga, Madhubani, Samastipur, and Begusarai.'
    ],
    [
        'id' => 'kosi-graduates',
        'type' => 'Graduates',
        'name' => 'Kosi Graduates',
        'name_hi' => 'कोसी स्नातक निर्वाचन क्षेत्र',
        'ro' => 'Divisional Commissioner, Kosi / Purnia',
        'ro_hi' => 'प्रमंडलीय आयुक्त, कोसी / पूर्णिया',
        'ro_url' => 'https://kosidivision.bih.nic.in/',
        'hq' => 'Saharsa / Purnia',
        'districts' => ['Saharsa', 'Supaul', 'Madhepura', 'Purnia', 'Araria', 'Kishanganj', 'Katihar', 'Bhagalpur', 'Banka', 'Munger', 'Jamui', 'Lakhisarai', 'Sheikhpura', 'Khagaria'],
        'districts_hi' => ['सहरसा', 'सुपौल', 'मधेपुरा', 'पूर्णिया', 'अररिया', 'किशनगंज', 'कटिहार', 'भागलपुर', 'बांका', 'मुंगेर', 'जमुई', 'लखीसराय', 'शेखपुरा', 'खगड़िया'],
        'color' => 'primary',
        'term_expiry' => '16 November 2026',
        'description' => 'The largest constituency by geographical expanse covering 14 districts across Kosi, Purnia, Bhagalpur, and Munger divisions.'
    ],
    [
        'id' => 'patna-teachers',
        'type' => 'Teachers',
        'name' => 'Patna Teachers',
        'name_hi' => 'पटना शिक्षक निर्वाचन क्षेत्र',
        'ro' => 'Divisional Commissioner, Patna',
        'ro_hi' => 'प्रमंडलीय आयुक्त, पटना',
        'ro_url' => 'https://patnadivision.bih.nic.in/',
        'hq' => 'Patna',
        'districts' => ['Patna', 'Nalanda', 'Nawada'],
        'districts_hi' => ['पटना', 'नालंदा', 'नवादा'],
        'color' => 'success',
        'term_expiry' => '16 November 2026',
        'description' => 'Representing secondary and collegiate educators across Patna, Nalanda, and Nawada.'
    ],
    [
        'id' => 'tirhut-teachers',
        'type' => 'Teachers',
        'name' => 'Tirhut Teachers',
        'name_hi' => 'तिरहुत शिक्षक निर्वाचन क्षेत्र',
        'ro' => 'Divisional Commissioner, Tirhut (Muzaffarpur)',
        'ro_hi' => 'प्रमंडलीय आयुक्त, तिरहुत (मुजफ्फरपुर)',
        'ro_url' => 'https://tirhutdivision.bih.nic.in/',
        'hq' => 'Muzaffarpur',
        'districts' => ['Muzaffarpur', 'Vaishali', 'Sitamarhi', 'Sheohar'],
        'districts_hi' => ['मुजफ्फरपुर', 'वैशाली', 'सीतामढ़ी', 'शिवहर'],
        'color' => 'success',
        'term_expiry' => '16 November 2026',
        'description' => 'Representing teaching faculty across secondary schools and colleges in Tirhut division.'
    ],
    [
        'id' => 'darbhanga-teachers',
        'type' => 'Teachers',
        'name' => 'Darbhanga Teachers',
        'name_hi' => 'दरभंगा शिक्षक निर्वाचन क्षेत्र',
        'ro' => 'Divisional Commissioner, Darbhanga',
        'ro_hi' => 'प्रमंडलीय आयुक्त, दरभंगा',
        'ro_url' => 'https://darbhangadivision.bih.nic.in/',
        'hq' => 'Darbhanga',
        'districts' => ['Darbhanga', 'Madhubani', 'Samastipur', 'Begusarai'],
        'districts_hi' => ['दरभंगा', 'मधुबनी', 'समस्तीपुर', 'बेगूसराय'],
        'color' => 'success',
        'term_expiry' => '16 November 2026',
        'description' => 'Covering the educator community in Mithila region (Darbhanga, Madhubani, Samastipur, Begusarai).'
    ],
    [
        'id' => 'saran-teachers',
        'type' => 'Teachers',
        'name' => 'Saran Teachers',
        'name_hi' => 'सारण शिक्षक निर्वाचन क्षेत्र',
        'ro' => 'Divisional Commissioner, Saran (Chapra)',
        'ro_hi' => 'प्रमंडलीय आयुक्त, सारण (छपरा)',
        'ro_url' => 'https://sarandivision.bih.nic.in/',
        'hq' => 'Chapra',
        'districts' => ['Saran', 'Siwan', 'Gopalganj', 'East Champaran', 'West Champaran'],
        'districts_hi' => ['सारण (छपरा)', 'सिवान', 'गोपालगंज', 'पूर्वी चंपारण (मोतिहारी)', 'पश्चिमी चंपारण (बेतिया)'],
        'color' => 'success',
        'term_expiry' => '16 November 2026',
        'description' => 'Encompassing teachers and professors in Saran and Champaran belt (Chapra, Siwan, Gopalganj, Motihari, Bettiah).'
    ]
];

// 8 निर्वाचन क्षेत्रों की आधिकारिक अंतिम मतदान रिपोर्ट (CEO Bihar Final VTR & Press Note 23-10-2026)
$turnoutReport = [
    [
        'category' => 'Graduates',
        'category_hi' => 'स्नातक',
        'name' => 'Patna Graduates',
        'name_hi' => 'पटना स्नातक निर्वाचन क्षेत्र',
        'slug' => 'patna-graduates',
        'turnout' => 57.56,
        'turnout_rank' => 'स्नातकों में सर्वाधिक',
        'rank_badge' => 'bg-success text-white',
        'sitting_mlc' => 'नीरज कुमार (विजेता - 4थी बार)',
        'party' => 'JD(U)',
        'tenure' => '16 नवंबर 2026',
        'booth_count' => 134,
        'candidates_count' => 18,
        'electors' => ['male' => 66462, 'female' => 21117, 'third' => 4, 'total' => 87583]
    ],
    [
        'category' => 'Graduates',
        'category_hi' => 'स्नातक',
        'name' => 'Tirhut Graduates',
        'name_hi' => 'तिरहुत स्नातक निर्वाचन क्षेत्र',
        'slug' => 'tirhut-graduates',
        'turnout' => 48.51,
        'turnout_rank' => 'स्नातकों में न्यूनतम',
        'rank_badge' => 'bg-secondary text-white',
        'sitting_mlc' => 'वंशीधर ब्रजवासी (अग्रणी)',
        'party' => 'IND',
        'tenure' => '16 नवंबर 2026',
        'booth_count' => 145,
        'candidates_count' => 17,
        'electors' => ['male' => 97998, 'female' => 45970, 'third' => 20, 'total' => 143988]
    ],
    [
        'category' => 'Graduates',
        'category_hi' => 'स्नातक',
        'name' => 'Darbhanga Graduates',
        'name_hi' => 'दरभंगा स्नातक निर्वाचन क्षेत्र',
        'slug' => 'darbhanga-graduates',
        'turnout' => 54.73,
        'turnout_rank' => 'मजबूत मतदान',
        'rank_badge' => 'bg-primary text-white',
        'sitting_mlc' => 'डॉ. सर्वेश कुमार (विजेता - BJP)',
        'party' => 'BJP',
        'tenure' => '16 नवंबर 2026',
        'booth_count' => 138,
        'candidates_count' => 40,
        'electors' => ['male' => 90006, 'female' => 36938, 'third' => 7, 'total' => 126951]
    ],
    [
        'category' => 'Graduates',
        'category_hi' => 'स्नातक',
        'name' => 'Kosi Graduates',
        'name_hi' => 'कोसी स्नातक निर्वाचन क्षेत्र',
        'slug' => 'kosi-graduates',
        'turnout' => 54.73,
        'turnout_rank' => 'मजबूत मतदान',
        'rank_badge' => 'bg-primary text-white',
        'sitting_mlc' => 'डॉ. एनके यादव (विजेता - हैट्रिक)',
        'party' => 'BJP',
        'tenure' => '16 नवंबर 2026',
        'booth_count' => 181,
        'candidates_count' => 15,
        'electors' => ['male' => 101056, 'female' => 33013, 'third' => 3, 'total' => 134072]
    ],
    [
        'category' => 'Teachers',
        'category_hi' => 'शिक्षक',
        'name' => 'Patna Teachers',
        'name_hi' => 'पटना शिक्षक निर्वाचन क्षेत्र',
        'slug' => 'patna-teachers',
        'turnout' => 74.75,
        'turnout_rank' => 'शिक्षकों में कम मतदान',
        'rank_badge' => 'bg-warning text-dark',
        'sitting_mlc' => 'डॉ. संजीव कुमार (विजेता - RJD)',
        'party' => 'RJD',
        'tenure' => '16 नवंबर 2026',
        'booth_count' => 80,
        'candidates_count' => 18,
        'electors' => ['male' => 8413, 'female' => 3306, 'third' => 0, 'total' => 11719]
    ],
    [
        'category' => 'Teachers',
        'category_hi' => 'शिक्षक',
        'name' => 'Tirhut Teachers',
        'name_hi' => 'तिरहुत शिक्षक निर्वाचन क्षेत्र',
        'slug' => 'tirhut-teachers',
        'turnout' => 82.99,
        'turnout_rank' => 'भारी मतदान',
        'rank_badge' => 'bg-primary text-white',
        'sitting_mlc' => 'डॉ. नरेंद्र प्रसाद सिंह (विजेता - BJP)',
        'party' => 'BJP',
        'tenure' => '16 नवंबर 2026',
        'booth_count' => 58,
        'candidates_count' => 11,
        'electors' => ['male' => 6507, 'female' => 2250, 'third' => 3, 'total' => 8760]
    ],
    [
        'category' => 'Teachers',
        'category_hi' => 'शिक्षक',
        'name' => 'Darbhanga Teachers',
        'name_hi' => 'दरभंगा शिक्षक निर्वाचन क्षेत्र',
        'slug' => 'darbhanga-teachers',
        'turnout' => 85.87,
        'turnout_rank' => 'भारी मतदान',
        'rank_badge' => 'bg-primary text-white',
        'sitting_mlc' => 'डॉ. सुरेश प्रसाद राय (विजेता - JSP)',
        'party' => 'Jan Suraaj Party',
        'tenure' => '16 नवंबर 2026',
        'booth_count' => 99,
        'candidates_count' => 13,
        'electors' => ['male' => 6958, 'female' => 2068, 'third' => 0, 'total' => 9026]
    ],
    [
        'category' => 'Teachers',
        'category_hi' => 'शिक्षक',
        'name' => 'Saran Teachers',
        'name_hi' => 'सारण शिक्षक निर्वाचन क्षेत्र',
        'slug' => 'saran-teachers',
        'turnout' => 89.05,
        'turnout_rank' => 'राज्य में रिकॉर्ड मतदान',
        'rank_badge' => 'bg-success text-white',
        'sitting_mlc' => 'राहुल राज (विजेता - निर्दलीय)',
        'party' => 'IND',
        'tenure' => '16 नवंबर 2026',
        'booth_count' => 98,
        'candidates_count' => 18,
        'electors' => ['male' => 8799, 'female' => 1949, 'third' => 0, 'total' => 10748]
    ]
];

// विभिन्न मीडिया एवं आधिकारिक स्रोतों की सूची (Multi-Source Verification)
$newsSources = [
    [
        'id' => 'jagran',
        'name' => 'दैनिक जागरण',
        'name_en' => 'Dainik Jagran',
        'badge' => 'bg-warning text-dark',
        'icon' => 'bi-newspaper',
        'url' => 'https://www.jagran.com/bihar',
        'coverage' => 'मतगणना केंद्र से ग्राउंड रिपोर्ट, पटना केंद्र पर सीसीटीवी मॉनिटर व्यवस्था पर आपत्ति, मुजफ्फरपुर व दरभंगा में सुरक्षा बंदोबस्त।'
    ],
    [
        'id' => 'hindustan',
        'name' => 'हिन्दुस्तान',
        'name_en' => 'Live Hindustan',
        'badge' => 'bg-danger text-white',
        'icon' => 'bi-broadcast',
        'url' => 'https://www.livehindustan.com/bihar/bihar-mlc-election-2026-voting-live-bjp-rjd-jdu-chunav-latest-updates-live-blog-201792729617020.html',
        'coverage' => 'निरंतर लाइव ब्लॉग, वरीयता मतों के चक्र, उम्मीदवार प्रतिक्रियाएं, मोकामा राजनीतिक प्रतिष्ठा एवं मतदान प्रतिशत।'
    ],
    [
        'id' => 'prabhat',
        'name' => 'प्रभात खबर',
        'name_en' => 'Prabhat Khabar',
        'badge' => 'bg-info text-dark',
        'icon' => 'bi-journal-check',
        'url' => 'https://www.prabhatkhabar.com/state/bihar',
        'coverage' => 'तिरहुत, दरभंगा, कोसी एवं सारण प्रमंडलीय मतगणना केंद्र, डाक मतपत्र छंटनी व एकल संक्रमणीय मत कोटा विश्लेषण।'
    ],
    [
        'id' => 'ceo_bihar',
        'name' => 'मुख्य निर्वाचन पदाधिकारी, बिहार',
        'name_en' => 'CEO Bihar / ECI',
        'badge' => 'bg-success text-white',
        'icon' => 'bi-patch-check-fill',
        'url' => 'https://ceoelection.bihar.gov.in/',
        'coverage' => 'आधिकारिक गजट अधिसूचना (TCGCGajat2026.pdf), प्रमंडलीय आयुक्त पर्यवेक्षण एवं प्ररूप 21C / 21D वैधानिक परिणाम प्रमाणीकरण।'
    ]
];

// 8 सीटों का लाइव मतगणना विवरण, प्रत्याशी स्थिति एवं मीडिया स्रोत इनपुट (27 अक्टूबर 2026 अपडेटेड)
$electionResults = [
    [
        'id' => 'tirhut-teachers',
        'type' => 'Teachers',
        'type_hi' => 'शिक्षक',
        'name' => 'Tirhut Teachers',
        'name_hi' => 'तिरहुत शिक्षक निर्वाचन क्षेत्र',
        'slug' => 'tirhut-teachers',
        'status' => 'विजयी घोषित (Elected MLC)',
        'status_badge' => 'bg-success text-white',
        'is_declared' => true,
        'turnout' => 82.99,
        'booths' => 58,
        'counting_centre' => 'एमआईटी परिसर, मुजफ्फरपुर',
        'returning_officer' => 'प्रमंडलीय आयुक्त, तिरहुत',
        'leading' => [
            'name' => 'डॉ. नरेंद्र प्रसाद सिंह',
            'party' => 'BJP',
            'role' => 'विजयी घोषित (594 मतों से जीते) / एनडीए प्रत्याशी'
        ],
        'trailing' => [
            'name' => 'भूषण झा',
            'party' => 'IND',
            'role' => 'मुख्य प्रतिद्वंद्वी (निर्दलीय - 2,351 मत)'
        ],
        'other_key' => [
            'name' => 'संजय कुमार सिंह',
            'party' => 'CPI',
            'role' => 'निवर्तमान एमएलसी (तीसरे स्थान पर रहे)'
        ],
        'trend_analysis' => 'आधिकारिक परिणाम घोषित: भाजपा के डॉ. नरेंद्र प्रसाद सिंह 594 मतों से विजयी घोषित (2,945 मत पाकर निर्दलीय भूषण झा को हराया)।',
        'sources_cited' => [
            'हिन्दुस्तान' => 'आधिकारिक प्रमाण पत्र जारी: भाजपा प्रत्याशी डॉ. नरेंद्र प्रसाद सिंह ने तिरहुत शिक्षक सीट 594 मतों के अंतर से जीत ली।',
            'दैनिक जागरण' => 'एमआईटी मुजफ्फरपुर केंद्र पर रिटर्निंग ऑफिसर द्वारा डॉ. नरेंद्र प्रसाद सिंह को जीत का प्रमाण पत्र सौंपा गया।',
            'प्रभात खबर' => 'निर्दलीय भूषण झा को 2,351 मत मिले; वाम दल के मौजूदा एमएलसी संजय कुमार सिंह रेस से बाहर हो गए।'
        ]
    ],
    [
        'id' => 'saran-teachers',
        'type' => 'Teachers',
        'type_hi' => 'शिक्षक',
        'name' => 'Saran Teachers',
        'name_hi' => 'सारण शिक्षक निर्वाचन क्षेत्र',
        'slug' => 'saran-teachers',
        'status' => 'विजयी घोषित (Elected MLC)',
        'status_badge' => 'bg-success text-white',
        'is_declared' => true,
        'turnout' => 89.05,
        'booths' => 98,
        'counting_centre' => 'सारण प्रमंडलीय मुख्यालय, छपरा',
        'returning_officer' => 'प्रमंडलीय आयुक्त, सारण (छपरा)',
        'leading' => [
            'name' => 'राहुल राज',
            'party' => 'IND',
            'role' => 'विजयी घोषित (निर्दलीय / बागी प्रत्याशी)'
        ],
        'trailing' => [
            'name' => 'अफाक अहमद',
            'party' => 'Jan Suraaj',
            'role' => 'निवर्तमान एमएलसी / उप-विजेता (जन सुराज)'
        ],
        'other_key' => [
            'name' => 'आनंद पुष्कर',
            'party' => 'JD(U)',
            'role' => 'एनडीए प्रत्याशी (स्व. केदार नाथ पांडेय के पुत्र)'
        ],
        'trend_analysis' => 'परिणाम घोषित: द्वितीय वरीयता मतों के हस्तांतरण के बाद निर्दलीय राहुल राज 800+ मतों से विजयी घोषित; जन सुराज के मौजूदा एमएलसी अफाक अहमद पराजित।',
        'sources_cited' => [
            'हिन्दुस्तान' => 'निर्दलीय उम्मीदवार एवं भाजपा बागी राहुल राज ने दूसरी वरीयता के मतों के बाद 800 से अधिक वोटों से जीत दर्ज की।',
            'दैनिक जागरण' => 'सारण में 89.05% के रिकॉर्ड मतदान के बाद लंबी चली छंटनी में राहुल राज ने अफाक अहमद और आनंद पुष्कर को पछाड़ा।',
            'प्रभात खबर' => 'छपरा मतगणना केंद्र पर सारण आयुक्त द्वारा औपचारिक परिणाम की घोषणा की गई।'
        ]
    ],
    [
        'id' => 'patna-graduates',
        'type' => 'Graduates',
        'type_hi' => 'स्नातक',
        'name' => 'Patna Graduates',
        'name_hi' => 'पटना स्नातक निर्वाचन क्षेत्र',
        'slug' => 'patna-graduates',
        'status' => 'जदयू आगे (त्रिकोणीय कड़ा मुकाबला)',
        'status_badge' => 'bg-warning text-dark blink-anim',
        'is_declared' => false,
        'turnout' => 57.56,
        'booths' => 134,
        'counting_centre' => 'कला एवं शिल्प महाविद्यालय, पटना',
        'returning_officer' => 'प्रमंडलीय आयुक्त, पटना',
        'leading' => [
            'name' => 'नीरज कुमार',
            'party' => 'JD(U)',
            'role' => 'वर्तमान विधान पार्षद / एनडीए प्रत्याशी (बढ़त पर)'
        ],
        'trailing' => [
            'name' => 'दिलीप पटेल (कुमार)',
            'party' => 'RJD',
            'role' => 'महागठबंधन प्रत्याशी (कड़ा मुकाबला)'
        ],
        'other_key' => [
            'name' => 'अनुज सिंह',
            'party' => 'Jan Suraaj',
            'role' => 'जन सुराज समर्थित (अनंत सिंह समर्थित)'
        ],
        'trend_analysis' => 'प्रतिष्ठापूर्ण मुकाबला: 3 बार के जदयू विधान पार्षद नीरज कुमार राजद के दिलीप पटेल और जन सुराज समर्थित अनुज सिंह के खिलाफ कला एवं शिल्प महाविद्यालय केंद्र पर बढ़त बनाए हुए हैं।',
        'sources_cited' => [
            'द वीक' => 'शुरुआती चक्रों में जदयू के नीरज कुमार और राजद के दिलीप पटेल के बीच पटना और नालंदा के मतपत्रों में करीबी मुकाबला।',
            'दैनिक जागरण' => 'प्रमंडलीय आयुक्त की सीधी निगरानी में टेबल-दर-टेबल वरीयता मतपत्रों की छंटनी व सत्यापन जारी।',
            'हिन्दुस्तान' => 'मोकामा की राजनीतिक सरगर्मियों के बीच जन सुराज समर्थित अनुज सिंह को भी महत्वपूर्ण मत; कोटा छंटनी जारी।'
        ]
    ],
    [
        'id' => 'patna-teachers',
        'type' => 'Teachers',
        'type_hi' => 'शिक्षक',
        'name' => 'Patna Teachers',
        'name_hi' => 'पटना शिक्षक निर्वाचन क्षेत्र',
        'slug' => 'patna-teachers',
        'status' => 'विजयी घोषित (Elected MLC)',
        'status_badge' => 'bg-success text-white',
        'is_declared' => true,
        'turnout' => 74.75,
        'booths' => 80,
        'counting_centre' => 'कला एवं शिल्प महाविद्यालय, पटना',
        'returning_officer' => 'प्रमंडलीय आयुक्त, पटना',
        'leading' => [
            'name' => 'डॉ. संजीव कुमार (सिंह)',
            'party' => 'RJD',
            'role' => 'विजयी घोषित (800+ मतों से जीते) / महागठबंधन प्रत्याशी'
        ],
        'trailing' => [
            'name' => 'नवल किशोर यादव',
            'party' => 'BJP',
            'role' => 'उप-विजेता (5 बार के पूर्व विधान पार्षद / एनडीए प्रत्याशी)'
        ],
        'other_key' => [
            'name' => 'दिव्या ज्योति / डॉ. संजय यादव',
            'party' => 'Jan Suraaj / INC',
            'role' => 'जन सुराज व कांग्रेस प्रत्याशी'
        ],
        'trend_analysis' => 'आधिकारिक परिणाम घोषित: राजद के डॉ. संजीव कुमार ने ऐतिहासिक जीत दर्ज करते हुए 5 बार के भाजपा एमएलसी नवल किशोर यादव को 800+ मतों से पराजित कर पटना शिक्षक सीट जीती।',
        'sources_cited' => [
            'ईटीवी भारत' => 'राजद प्रत्याशी डॉ. संजीव कुमार ने भाजपा के वरिष्ठ नेता व 5 बार के एमएलसी नवल किशोर यादव को 800 से अधिक वोटों से हराया।',
            'हिन्दुस्तान' => 'पटना शिक्षक सीट पर बड़ा उलटफेर: नवल किशोर यादव को पछाड़कर राजद उम्मीदवार ने जीत का प्रमाण पत्र हासिल किया।',
            'दैनिक जागरण' => 'बांकीपुर/आर्ट्स कॉलेज केंद्र पर अंतिम सत्यापन के बाद निर्वाची पदाधिकारी द्वारा जीत की विधिवत घोषणा।'
        ]
    ],
    [
        'id' => 'tirhut-graduates',
        'type' => 'Graduates',
        'type_hi' => 'स्नातक',
        'name' => 'Tirhut Graduates',
        'name_hi' => 'तिरहुत स्नातक निर्वाचन क्षेत्र',
        'slug' => 'tirhut-graduates',
        'status' => 'निर्दलीय मजबूत बढ़त पर (अंतिम कोटा चक्र)',
        'status_badge' => 'bg-warning text-dark blink-anim',
        'is_declared' => false,
        'turnout' => 48.51,
        'booths' => 145,
        'counting_centre' => 'एमआईटी परिसर, मुजफ्फरपुर',
        'returning_officer' => 'प्रमंडलीय आयुक्त, तिरहुत',
        'leading' => [
            'name' => 'वंशीधर ब्रजवासी',
            'party' => 'IND',
            'role' => 'निवर्तमान एमएलसी (निर्दलीय) / कोटा निर्धारण चक्र में आगे'
        ],
        'trailing' => [
            'name' => 'अभिषेक झा',
            'party' => 'JD(U)',
            'role' => 'एनडीए प्रत्याशी (कोटा चक्र में संघर्षरत)'
        ],
        'other_key' => [
            'name' => 'डॉ. रश्मि गौतम / डॉ. विमोहन',
            'party' => 'RJD / Jan Suraaj',
            'role' => 'राजद प्रत्याशी व जन सुराज उम्मीदवार'
        ],
        'trend_analysis' => 'अंतिम चक्र जारी: एमआईटी मुजफ्फरपुर में निवर्तमान एमएलसी वंशीधर ब्रजवासी (निर्दलीय) जदयू के अभिषेक झा और राजद की डॉ. रश्मि गौतम के खिलाफ निर्णायक बढ़त बनाए हुए हैं।',
        'sources_cited' => [
            'इंडिया टीवी' => 'शुरुआती रुझानों में निर्दलीय वंशीधर ब्रजवासी 2,813 मतों के साथ जदयू के अभिषेक झा (1,739) और डॉ. रश्मि गौतम (1,407) से आगे।',
            'दैनिक जागरण' => 'एमआईटी मुजफ्फरपुर में 14 टेबलों पर 145 बूथों के मतपेटियों की छंटनी जारी।',
            'हिन्दुस्तान' => 'तिरहुत प्रमंडल के 4 जिलों में जीत का कोटा हासिल करने के लिए कई चक्रों का एलिमिनेशन जारी।'
        ]
    ],
    [
        'id' => 'darbhanga-graduates',
        'type' => 'Graduates',
        'type_hi' => 'स्नातक',
        'name' => 'Darbhanga Graduates',
        'name_hi' => 'दरभंगा स्नातक निर्वाचन क्षेत्र',
        'slug' => 'darbhanga-graduates',
        'status' => 'विजयी घोषित (Elected MLC)',
        'status_badge' => 'bg-success text-white',
        'is_declared' => true,
        'turnout' => 54.73,
        'booths' => 138,
        'counting_centre' => 'प्रमंडलीय आयुक्त परिसर, दरभंगा',
        'returning_officer' => 'प्रमंडलीय आयुक्त, दरभंगा',
        'leading' => [
            'name' => 'डॉ. सर्वेश कुमार',
            'party' => 'BJP',
            'role' => 'विजयी घोषित (237 मतों से सीट बरकरार रखी) / एनडीए प्रत्याशी'
        ],
        'trailing' => [
            'name' => 'अनिल कुमार झा',
            'party' => 'RJD',
            'role' => 'उप-विजेता (महागठबंधन प्रत्याशी)'
        ],
        'other_key' => [
            'name' => 'मोहम्मद इम्तियाज (नूरानी)',
            'party' => 'Jan Suraaj',
            'role' => 'जन सुराज प्रत्याशी'
        ],
        'trend_analysis' => 'आधिकारिक परिणाम घोषित: भाजपा के मौजूदा एमएलसी डॉ. सर्वेश कुमार ने कांटे के मुकाबले में राजद के अनिल कुमार झा को 237 मतों से हराकर दरभंगा स्नातक सीट जीत ली।',
        'sources_cited' => [
            'हिन्दुस्तान टाइम्स' => 'भाजपा प्रत्याशी सर्वेश कुमार ने कांटे की टक्कर में राजद के अनिल झा को 237 मतों से हराकर दरभंगा स्नातक सीट जीत ली।',
            'हिन्दुस्तान' => 'दरभंगा प्रमंडलीय आयुक्त परिसर में पूरी गणना के बाद सर्वेश कुमार को जीत का प्रमाण पत्र दिया गया।',
            'प्रभात खबर' => 'दरभंगा, मधुबनी, समस्तीपुर व बेगूसराय के सभी 138 बूथों की मतगणना का काम पूरा।'
        ]
    ],
    [
        'id' => 'darbhanga-teachers',
        'type' => 'Teachers',
        'type_hi' => 'शिक्षक',
        'name' => 'Darbhanga Teachers',
        'name_hi' => 'दरभंगा शिक्षक निर्वाचन क्षेत्र',
        'slug' => 'darbhanga-teachers',
        'status' => 'विजयी घोषित (Elected MLC)',
        'status_badge' => 'bg-success text-white',
        'is_declared' => true,
        'turnout' => 85.87,
        'booths' => 99,
        'counting_centre' => 'प्रमंडलीय आयुक्त परिसर, दरभंगा',
        'returning_officer' => 'प्रमंडलीय आयुक्त, दरभंगा',
        'leading' => [
            'name' => 'डॉ. सुरेश प्रसाद राय',
            'party' => 'Jan Suraaj',
            'role' => 'विजयी घोषित (जन सुराज प्रत्याशी - ऐतिहासिक जीत)'
        ],
        'trailing' => [
            'name' => 'डॉ. मदन मोहन झा',
            'party' => 'INC',
            'role' => 'उप-विजेता (निवर्तमान एमएलसी / पूर्व कांग्रेस प्रदेश अध्यक्ष)'
        ],
        'other_key' => [
            'name' => 'रामदेव राय',
            'party' => 'BJP',
            'role' => 'एनडीए समर्थित दावेदार'
        ],
        'trend_analysis' => 'ऐतिहासिक परिणाम: जन सुराज पार्टी के डॉ. सुरेश प्रसाद राय ने कांग्रेस के दिग्गज नेता व 4 बार के एमएलसी डॉ. मदन मोहन झा को हराकर जन सुराज का पहला खाता खोला।',
        'sources_cited' => [
            'दैनिक जागरण' => 'दरभंगा शिक्षक सीट पर जन सुराज के सुरेश प्रसाद राय ने कांग्रेस दिग्गज डॉ. मदन मोहन झा को हराकर जीत दर्ज की।',
            'हिन्दुस्तान' => 'प्रशांत किशोर की जन सुराज पार्टी ने विधान परिषद में दर्ज की पहली जीत; मिथिलांचल के शिक्षक मतों में बड़ा उलटफेर।',
            'प्रभात खबर' => 'दरभंगा प्रमंडलीय आयुक्त द्वारा औपचारिक परिणाम की घोषणा; सुरेश राय को दिया गया जीत का प्रमाण पत्र।'
        ]
    ],
    [
        'id' => 'kosi-graduates',
        'type' => 'Graduates',
        'type_hi' => 'स्नातक',
        'name' => 'Kosi Graduates',
        'name_hi' => 'कोसी स्नातक निर्वाचन क्षेत्र',
        'slug' => 'kosi-graduates',
        'status' => 'भाजपा भारी बढ़त पर (24,075 प्रथम वरीयता मत)',
        'status_badge' => 'bg-primary text-white blink-anim',
        'is_declared' => false,
        'turnout' => 54.73,
        'booths' => 181,
        'counting_centre' => 'पूर्णिया / सहरसा मतगणना केंद्र',
        'returning_officer' => 'प्रमंडलीय आयुक्त, कोसी / पूर्णिया',
        'leading' => [
            'name' => 'डॉ. एनके यादव',
            'party' => 'BJP',
            'role' => 'वर्तमान विधान पार्षद (11 राउंड में 24,075 प्रथम वरीयता मत / कोटा: 32,201)'
        ],
        'trailing' => [
            'name' => 'नितेश कुमार',
            'party' => 'RJD',
            'role' => 'महागठबंधन प्रत्याशी (राउंड 11 के बाद 18,535 मत)'
        ],
        'other_key' => [
            'name' => 'रजनीश रंजन',
            'party' => 'Jan Suraaj',
            'role' => 'जन सुराज प्रत्याशी (राउंड 11 के बाद 15,306 मत)'
        ],
        'trend_analysis' => 'भाजपा के डॉ. एनके यादव 11 राउंड की प्रथम वरीयता गणना में 24,075 मतों के साथ राजद के नितेश कुमार (18,535) और जन सुराज के रजनीश रंजन (15,306) से आगे; 32,201 कोटा हेतु दूसरी वरीयता गिनती जारी।',
        'sources_cited' => [
            'दैनिक जागरण' => '11 राउंड की पहली वरीयता गिनती में डॉ. एनके यादव ने 24,075 मत पाकर 32,201 कोटे की ओर मजबूत बढ़त बनाई; दूसरी वरीयता की गिनती जारी।',
            'हिन्दुस्तान' => 'भाजपा प्रत्याशी ने राजद के नितेश कुमार (18,535) और जन सुराज के रजनीश रंजन (15,306) पर 5,500+ मतों की ठोस बढ़त कायम रखी।',
            'ईटीवी भारत' => 'सहरसा/पूर्णिया मतगणना केंद्र पर कड़ी सुरक्षा के बीच द्वितीय वरीयता की छंटनी जारी।'
        ]
    ]
];

// 8 निर्वाचन क्षेत्रों के मतदान केंद्र एवं समर्पित हब मैपिंग
$boothPageMap = [
    'patna-graduates' => ['slug' => 'patna-graduate-constituency', 'count' => 134, 'code' => 1],
    'tirhut-graduates' => ['slug' => 'tirhut-graduate-constituency', 'count' => 145, 'code' => 4],
    'darbhanga-graduates' => ['slug' => 'darbhanga-graduate-constituency', 'count' => 138, 'code' => 5],
    'kosi-graduates' => ['slug' => 'kosi-graduate-constituency', 'count' => 181, 'code' => 6],
    'patna-teachers' => ['slug' => 'patna-teacher-constituency', 'count' => 80, 'code' => 1],
    'tirhut-teachers' => ['slug' => 'tirhut-teacher-constituency', 'count' => 58, 'code' => 4],
    'darbhanga-teachers' => ['slug' => 'darbhanga-teacher-constituency', 'count' => 99, 'code' => 5],
    'saran-teachers' => ['slug' => 'saran-teacher-constituency', 'count' => 98, 'code' => 3],
];

// Schedule Timeline Events
$timeline = [
    [
        'step' => '1',
        'date' => '29 September 2026',
        'day' => 'Tuesday',
        'title' => 'Issue of Gazette Notification',
        'title_hi' => 'निर्वाचन की अधिसूचना जारी',
        'status' => 'completed',
        'desc' => 'Statutory notification published in the Official Bihar Gazette (Notification No. M2–03(TC&GC)/2026-5369).'
    ],
    [
        'step' => '2',
        'date' => '06 October 2026',
        'day' => 'Tuesday',
        'title' => 'Last Date for Making Nominations',
        'title_hi' => 'नामनिर्देशन करने की अंतिम तिथि',
        'status' => 'completed',
        'desc' => 'Candidates submit nomination papers along with required security deposit to the designated Returning Officer by 3:00 PM.'
    ],
    [
        'step' => '3',
        'date' => '07 October 2026',
        'day' => 'Wednesday',
        'title' => 'Scrutiny of Nominations',
        'title_hi' => 'नामनिर्देशनों की संवीक्षा',
        'status' => 'completed',
        'desc' => 'Returning Officers examine validity of submitted nomination papers.'
    ],
    [
        'step' => '4',
        'date' => '09 October 2026',
        'day' => 'Friday',
        'title' => 'Last Date for Withdrawal of Candidatures',
        'title_hi' => 'उम्मीदवारी वापस लेने की अंतिम तिथि',
        'status' => 'completed',
        'desc' => 'Final list of contesting candidates (Form 7B) published with allotted ballot positions.'
    ],
    [
        'step' => '5',
        'date' => '23 October 2026',
        'day' => 'Friday',
        'title' => 'Date of Poll (Voting Day)',
        'title_hi' => 'मतदान की तिथि (सुबह 8:00 से शाम 4:00)',
        'status' => 'completed',
        'desc' => 'Polling held across designated polling stations via ballot paper (PR-STV system). Strongrooms sealed.'
    ],
    [
        'step' => '6',
        'date' => '27 October 2026',
        'day' => 'Tuesday',
        'title' => 'Counting of Votes & Declaration of Results (Starts 8:00 AM)',
        'title_hi' => 'मतगणना एवं परिणाम घोषणा (सुबह 8:00 बजे से)',
        'status' => 'current',
        'desc' => 'Counting of preferential votes and quota calculation begins at 8:00 AM IST at divisional headquarters.'
    ],
    [
        'step' => '7',
        'date' => '31 October 2026',
        'day' => 'Saturday',
        'title' => 'Completion of Election Process',
        'title_hi' => 'निर्वाचन प्रक्रिया पूर्ण होने की तिथि',
        'status' => 'upcoming',
        'desc' => 'Formal completion of election process before the term of sitting MLCs expires on 16 November 2026.'
    ]
];
?>

<style>
/* Vidhan Parishad Election Hub Custom Styles */
.hero-parishad {
    background: linear-gradient(135deg, #091e3a 0%, #1e3a8a 50%, #0f172a 100%);
    color: #fff;
    padding: 60px 0 50px;
    position: relative;
    overflow: hidden;
}
.hero-parishad::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(245, 158, 11, 0.15) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.timeline-card {
    position: relative;
    border-left: 3px solid #cbd5e1;
    padding-left: 28px;
    padding-bottom: 30px;
    transition: all 0.2s ease;
}
.timeline-card:last-child {
    border-left-color: transparent;
    padding-bottom: 0;
}
.timeline-badge {
    position: absolute;
    left: -17px;
    top: 0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #fff;
    border: 3px solid #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 13px;
    color: #475569;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
.timeline-card.active .timeline-badge {
    background: #2563eb;
    border-color: #93c5fd;
    color: #fff;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2);
}
.timeline-card.completed .timeline-badge {
    background: #16a34a;
    border-color: #86efac;
    color: #fff;
}
.countdown-box {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    padding: 14px 20px;
    text-align: center;
}
.countdown-num {
    font-size: 2rem;
    font-weight: 800;
    font-family: 'Outfit', sans-serif;
    line-height: 1;
    color: #fbbf24;
}
.constituency-card {
    border-radius: 14px;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid #e2e8f0;
}
.constituency-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px -10px rgba(0, 0, 0, 0.12);
    border-color: #94a3b8;
}
.district-tag {
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #e2e8f0;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    display: inline-block;
    transition: all 0.15s ease;
}
.district-tag:hover {
    background: #e2e8f0;
}
.gazette-btn {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #000;
    font-weight: 700;
    border: none;
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
    transition: all 0.2s ease;
}
.gazette-btn:hover {
    background: linear-gradient(135deg, #fbbf24 0%, #b45309 100%);
    color: #000;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(245, 158, 11, 0.45);
}
.live-pulse-dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #ef4444;
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
    animation: pulseRing 1.5s infinite;
}
@keyframes pulseRing {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}
.source-pill-link {
    transition: all 0.2s ease;
    border: 1px solid rgba(0,0,0,0.08);
}
.source-pill-link:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.candidate-lead-box {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-left: 4px solid #2563eb;
    border-radius: 8px;
    padding: 10px 14px;
}
.candidate-lead-box.winner-bjp { border-left-color: #f97316; }
.candidate-lead-box.winner-jdu { border-left-color: #16a34a; }
.candidate-lead-box.winner-jsp { border-left-color: #eab308; }
.candidate-lead-box.winner-inc { border-left-color: #0284c7; }
</style>

<!-- ========================================================================= -->
<!-- 1. HERO SECTION & LIVE COUNTDOWN -->
<!-- ========================================================================= -->
<section class="hero-parishad shadow-sm">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <span class="badge bg-danger text-white fw-bold px-3 py-1.5 rounded-pill shadow-sm d-inline-flex align-items-center gap-1.5">
                        <span class="live-pulse-dot" style="background:#fff;"></span> मतगणना लाइव &bull; 28 अक्टूबर 2026
                    </span>
                    <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold">
                        5 परिणाम घोषित &bull; 3 सीटों पर गणना
                    </span>
                    <span class="badge bg-light text-dark px-3 py-1.5 rounded-pill fw-semibold">
                        8 सीटें (4 स्नातक + 4 शिक्षक)
                    </span>
                </div>

                <h1 class="display-6 fw-bold mb-2" style="font-family: 'Outfit', sans-serif;">
                    बिहार विधान परिषद् चुनाव परिणाम 2026
                </h1>
                <h2 class="h5 fw-normal text-light opacity-90 mb-3" style="font-family: 'Noto Sans Devanagari', sans-serif;">
                    8 सीटों (4 स्नातक एवं 4 शिक्षक निर्वाचन क्षेत्र) की लाइव मतगणना, रुझान एवं परिणाम
                </h2>
                
                <p class="lead fs-6 text-light opacity-90 mb-4" style="max-width: 720px; line-height: 1.6;">
                    <strong>दैनिक जागरण, हिन्दुस्तान, प्रभात खबर एवं मुख्य निर्वाचन पदाधिकारी (CEO Bihar)</strong> द्वारा जारी आधिकारिक अधिसूचनाओं के आधार पर लाइव मतगणना आंकड़े, वरीयता चक्र एवं सीटवार स्थिति।
                </p>

                <div class="d-flex flex-wrap gap-2 pt-1">
                    <a href="#election-results" class="btn btn-danger px-3.5 py-2.5 rounded-3 fw-bold text-white shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="bi bi-trophy-fill"></i>
                        <span>लाइव परिणाम एवं रुझान (Election Results) &rarr;</span>
                    </a>
                    <a href="#media-sources" class="btn btn-warning px-3 py-2.5 rounded-3 fw-bold text-dark d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-newspaper"></i> विभिन्न स्रोत संदर्भ (Sources Cited)
                    </a>
                    <a href="#voting-turnout" class="btn btn-outline-light px-3 py-2.5 rounded-3 fw-semibold">
                        <i class="bi bi-pie-chart-fill me-1"></i> मतदान प्रतिशत रिपोर्ट (VTR)
                    </a>
                    <a href="https://ceoelection.bihar.gov.in/PDF/Year_2026/VTR/Final%20VTR%20in%20TC%20GC%20Election%202026.pdf" target="_blank" rel="noopener" class="btn gazette-btn px-3.5 py-2.5 rounded-3 fw-bold">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> CEO Final VTR PDF &rarr;
                    </a>
                    <a href="#schedule" class="btn btn-outline-light px-3 py-2.5 rounded-3 fw-semibold">
                        <i class="bi bi-calendar-event me-1"></i> समय सारिणी
                    </a>
                </div>
            </div>

            <!-- Countdown / Live Counting Status Card -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="background: rgba(15, 23, 42, 0.90); backdrop-filter: blur(14px); border: 1px solid rgba(255,255,255,0.18);">
                    <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 text-center">
                        <div class="d-inline-flex align-items-center gap-1.5 px-3 py-1 bg-success text-white rounded-pill extra-small fw-bold mb-2 shadow-sm">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>5 परिणाम घोषित &bull; 3 सीटों पर कोटा गणना जारी</span>
                        </div>
                        <h6 class="text-uppercase small fw-bold text-warning mb-0">
                            <i class="bi bi-broadcast me-1"></i> वोटों की गिनती व परिणाम बुलेटिन
                        </h6>
                        <span class="text-white small fw-bold">बुधवार, 28 अक्टूबर 2026 (सुबह का विशेष बुलेटिन)</span>
                    </div>
                    <div class="card-body p-4 text-center">
                        <!-- Round Status Pulse -->
                        <div class="p-3 rounded-3 mb-3 text-start" style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35);">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="live-pulse-dot" style="background:#22c55e;"></span>
                                <strong class="text-warning small text-uppercase">स्थिति: 5 सीटें घोषित &bull; 3 पर द्वितीय वरीयता छंटनी</strong>
                            </div>
                            <p class="small text-light opacity-90 mb-0" style="font-size: 12px; line-height: 1.45;">
                                सभी 4 शिक्षक सीटों के नतीजे तय (भाजपा, निर्दलीय, जन सुराज व राजद को 1-1 सीट)। दरभंगा स्नातक में भाजपा के डॉ. सर्वेश कुमार विजयी। कोसी, पटना एवं तिरहुत स्नातक में कोटा राउंड्स जारी।
                            </p>
                        </div>

                        <div class="d-flex justify-content-between align-items-center text-start small text-light opacity-90">
                            <div>
                                <i class="bi bi-clock-history text-info me-1"></i> <strong>मतगणना प्रारंभ:</strong>
                            </div>
                            <span class="badge bg-secondary text-white fw-bold px-2 py-1">27 Oct 2026, 08:00 AM</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center text-start small text-light opacity-90 mt-2">
                            <div>
                                <i class="bi bi-trophy-fill text-warning me-1"></i> <strong>घोषित परिणाम:</strong>
                            </div>
                            <span class="badge bg-success text-white fw-bold px-2 py-1">5 सीटें घोषित (विजेता तय)</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center text-start small text-light opacity-90 mt-2">
                            <div>
                                <i class="bi bi-hourglass-split text-danger me-1"></i> <strong>गणना जारी:</strong>
                            </div>
                            <span class="text-warning fw-semibold">3 सीटों पर वरीयता छंटनी</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center text-start small text-light opacity-90 mt-2">
                            <div>
                                <i class="bi bi-newspaper text-info me-1"></i> <strong>स्रोतों का उल्लेख:</strong>
                            </div>
                            <span class="text-light fw-semibold">जागरण, हिन्दुस्तान, प्रभात, मुख्य निर्वाचन पदाधिकारी</span>
                        </div>
                        <div class="mt-3 pt-2 border-top border-secondary border-opacity-25">
                            <a href="#election-results" class="btn btn-warning btn-sm w-100 fw-bold rounded-pill text-dark shadow-sm">
                                <i class="bi bi-arrow-down-circle-fill me-1"></i> लाइव परिणाम व रुझान देखें
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================================= -->
<!-- 2. QUICK STATS OVERVIEW -->
<!-- ========================================================================= -->
<section class="py-4 bg-light border-bottom">
    <div class="container">
        <div class="row g-3 text-center">
            <div class="col-6 col-md-3">
                <div class="bg-white p-3 rounded-3 shadow-sm border h-100">
                    <div class="text-success fw-bold display-6 mb-1" style="font-family: 'Outfit', sans-serif;">5 / 8</div>
                    <div class="fw-semibold text-dark small">विजेता घोषित सीटें</div>
                    <small class="text-muted">4 शिक्षक (BJP, IND, JSP, RJD) + दर्भ. स्नातक (BJP)</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-white p-3 rounded-3 shadow-sm border h-100">
                    <div class="text-warning fw-bold display-6 mb-1" style="font-family: 'Outfit', sans-serif;">3 / 8</div>
                    <div class="fw-semibold text-dark small">वरीयता कोटा गणना जारी</div>
                    <small class="text-muted">कोसी, पटना स्नातक, तिरहुत स्नातक</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-white p-3 rounded-3 shadow-sm border h-100">
                    <div class="text-primary fw-bold display-6 mb-1" style="font-family: 'Outfit', sans-serif;">150</div>
                    <div class="fw-semibold text-dark small">कुल प्रत्याशी मैदान में</div>
                    <small class="text-muted">90 स्नातक + 60 शिक्षक (5,32,847 मतदाता)</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-white p-3 rounded-3 shadow-sm border h-100">
                    <div class="text-danger fw-bold display-6 mb-1 d-flex align-items-center justify-content-center gap-1.5" style="font-family: 'Outfit', sans-serif;">
                        <span class="live-pulse-dot"></span>
                        <span>LIVE</span>
                    </div>
                    <div class="fw-semibold text-dark small">एकल संक्रमणीय मत पद्धति</div>
                    <small class="text-muted">PR-STV नियम 75 के तहत गणना</small>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container py-5">
    <div class="row g-5">
        <!-- Main Content Area (8 Cols) -->
        <div class="col-lg-8">

            <!-- ============================================================= -->
            <!-- 2.4 LIVE ELECTION RESULTS & COUNTING TRENDS (MULTI-SOURCE HINDI) -->
            <!-- ============================================================= -->
            <div id="election-results" class="mb-5">
                <!-- Section Header -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-danger text-white fw-bold text-uppercase px-2.5 py-1 d-inline-flex align-items-center gap-1.5 shadow-sm">
                                <span class="live-pulse-dot" style="background:#fff;"></span> लाइव मतगणना परिणाम
                            </span>
                            <span class="badge bg-success text-white extra-small fw-semibold px-2 py-0.5 rounded-pill">
                                5 सीटें घोषित
                            </span>
                        </div>
                        <h3 class="fw-bold mb-0 text-dark" style="font-family: 'Outfit', sans-serif;">
                            विधान परिषद 8 सीटों के आधिकारिक चुनाव परिणाम एवं रुझान
                        </h3>
                        <div class="text-muted small">
                            दैनिक जागरण, हिन्दुस्तान, प्रभात खबर एवं मुख्य निर्वाचन पदाधिकारी बिहार द्वारा प्राप्त ताज़ा आंकड़े
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                            <i class="bi bi-clock-history me-1"></i> अपडेट 07:30 AM IST
                        </span>
                        <div class="extra-small text-muted mt-1">28 October 2026 Morning Bulletin</div>
                    </div>
                </div>

                <!-- Live Results Party Scoreboard Summary (Hindi) -->
                <div class="row g-2 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 border shadow-sm text-center bg-white h-100" style="border-top: 4px solid #f97316 !important;">
                            <div class="text-uppercase extra-small fw-bold text-muted mb-1">NDA (BJP / JDU)</div>
                            <div class="d-flex align-items-baseline justify-content-center gap-1">
                                <span class="fs-3 fw-bolder text-success" style="font-family: 'Outfit', sans-serif;">2</span>
                                <span class="small fw-bold text-success">Won</span>
                                <span class="text-muted mx-1">|</span>
                                <span class="fs-4 fw-bold text-primary">2</span>
                                <span class="extra-small text-muted">Lead</span>
                            </div>
                            <div class="extra-small text-muted mt-1">Won: Tirhut Teach &amp; Dbg Grad &bull; Lead: Kosi, Patna Grad</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 border shadow-sm text-center bg-white h-100" style="border-top: 4px solid #16a34a !important;">
                            <div class="text-uppercase extra-small fw-bold text-muted mb-1">MGB (RJD / INC)</div>
                            <div class="d-flex align-items-baseline justify-content-center gap-1">
                                <span class="fs-3 fw-bolder text-success" style="font-family: 'Outfit', sans-serif;">1</span>
                                <span class="small fw-bold text-success">Won</span>
                                <span class="text-muted mx-1">|</span>
                                <span class="fs-4 fw-bold text-secondary">0</span>
                                <span class="extra-small text-muted">Lead</span>
                            </div>
                            <div class="extra-small text-muted mt-1">Won: Patna Teachers (Dr. Sanjeev Kumar unseats BJP)</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 border shadow-sm text-center bg-white h-100" style="border-top: 4px solid #eab308 !important;">
                            <div class="text-uppercase extra-small fw-bold text-muted mb-1">Jan Suraaj Party</div>
                            <div class="d-flex align-items-baseline justify-content-center gap-1">
                                <span class="fs-3 fw-bolder text-success" style="font-family: 'Outfit', sans-serif;">1</span>
                                <span class="small fw-bold text-success">Won</span>
                                <span class="text-muted mx-1">|</span>
                                <span class="fs-4 fw-bold text-secondary">0</span>
                                <span class="extra-small text-muted">Lead</span>
                            </div>
                            <div class="extra-small text-muted mt-1">Won: Darbhanga Teachers (Dr. Suresh Rai - First Seat)</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 border shadow-sm text-center bg-white h-100" style="border-top: 4px solid #64748b !important;">
                            <div class="text-uppercase extra-small fw-bold text-muted mb-1">Independents / Others</div>
                            <div class="d-flex align-items-baseline justify-content-center gap-1">
                                <span class="fs-3 fw-bolder text-success" style="font-family: 'Outfit', sans-serif;">1</span>
                                <span class="small fw-bold text-success">Won</span>
                                <span class="text-muted mx-1">|</span>
                                <span class="fs-4 fw-bold text-primary">1</span>
                                <span class="extra-small text-muted">Lead</span>
                            </div>
                            <div class="extra-small text-muted mt-1">Won: Saran Teach (Rahul Raj) &bull; Lead: Tirhut Grad (Brajwasi)</div>
                        </div>
                    </div>
                </div>

                <!-- Verified Multi-Source Coverage Bar -->
                <div id="media-sources" class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff;">
                    <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-2.5 px-3.5 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shield-check text-warning fs-5"></i>
                            <span class="fw-bold small text-uppercase tracking-wider">विभिन्न मीडिया एवं आधिकारिक स्रोतों का संदर्भ (Multi-Source Verification)</span>
                        </div>
                        <span class="badge bg-light text-dark extra-small rounded-pill fw-bold">4 अधिकृत स्रोत</span>
                    </div>
                    <div class="card-body p-3.5">
                        <p class="small text-light opacity-90 mb-3" style="line-height: 1.5;">
                            इस चुनाव में मतों की गिनती वरीयता क्रम (Single Transferable Vote) के आधार पर हो रही है। सभी रुझान एवं तथ्य राज्य के प्रतिष्ठित समाचार पत्रों व निर्वाचन आयोग के दिशा-निर्देशों से सत्यापित हैं:
                        </p>
                        <div class="row g-2">
                            <?php foreach ($newsSources as $src): ?>
                            <div class="col-sm-6">
                                <a href="<?php echo htmlspecialchars($src['url']); ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none d-block h-100 source-pill-link">
                                    <div class="p-2.5 rounded-3 h-100" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12);">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="<?php echo $src['icon']; ?> text-warning fs-6"></i>
                                                <strong class="text-white small"><?php echo htmlspecialchars($src['name']); ?></strong>
                                                <span class="text-light opacity-75 extra-small">(<?php echo htmlspecialchars($src['name_en']); ?>)</span>
                                            </div>
                                            <i class="bi bi-box-arrow-up-right text-light opacity-50 extra-small"></i>
                                        </div>
                                        <p class="mb-0 text-light opacity-75 extra-small" style="line-height: 1.35;">
                                            <?php echo htmlspecialchars($src['coverage']); ?>
                                        </p>
                                    </div>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Ground Intelligence Alert Banner -->
                <div class="alert alert-light border border-secondary border-opacity-25 rounded-4 p-3 mb-4 shadow-sm" style="background-color: #f8fafc;">
                    <div class="d-flex align-items-start gap-2.5">
                        <i class="bi bi-info-circle-fill text-danger fs-5 mt-0.5"></i>
                        <div class="small text-secondary" style="line-height: 1.55;">
                            <strong class="text-dark">ताज़ा आधिकारिक परिणाम एवं महत्वपूर्ण ग्राउंड इनपुट्स (Key Ground Intelligence &amp; Verified Declarations):</strong>
                            <ul class="mb-0 ps-3 mt-1">
                                <li>
                                    <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i>पटना शिक्षक (ईटीवी भारत / लाइव हिन्दुस्तान / एनडीटीवी):</strong> बांकीपुर / कला एवं शिल्प महाविद्यालय केंद्र पर बड़ा उलटफेर! राजद समर्थित प्रत्याशी <strong>डॉ. संजीव कुमार</strong> ने 800 से अधिक मतों के अंतर से ऐतिहासिक जीत दर्ज की; 5 बार के वरिष्ठ भाजपा एमएलसी नवल किशोर यादव को पराजित किया।
                                </li>
                                <li>
                                    <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i>दरभंगा स्नातक (हिन्दुस्तान टाइम्स / दैनिक जागरण):</strong> भाजपा के मौजूदा एमएलसी <strong>डॉ. सर्वेश कुमार</strong> ने अंतिम कोटा गणना के बाद कड़े मुकाबले में राजद के अनिल कुमार झा को 237 मतों से हराकर अपनी सीट बरकरार रखी।
                                </li>
                                <li>
                                    <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i>दरभंगा शिक्षक (दैनिक जागरण / लाइव हिन्दुस्तान):</strong> प्रशांत किशोर की पार्टी का विधान परिषद् में ऐतिहासिक खाता खुला। जन सुराज पार्टी के <strong>डॉ. सुरेश प्रसाद राय</strong> आधिकारिक रूप से विजयी घोषित, कांग्रेस के दिग्गज एवं 4 बार के एमएलसी डॉ. मदन मोहन झा पराजित।
                                </li>
                                <li>
                                    <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i>तिरहुत शिक्षक (लाइव हिन्दुस्तान / प्रभात खबर):</strong> एनडीए समर्थित भाजपा प्रत्याशी <strong>डॉ. नरेंद्र प्रसाद सिंह</strong> 2,945 मत प्राप्त कर 594 मतों के अंतर से आधिकारिक रूप से विजयी घोषित; निर्दलीय भूषण झा (2,351 मत) पराजित। भाकपा के मौजूदा एमएलसी संजय कुमार सिंह तीसरे स्थान पर रहे।
                                </li>
                                <li>
                                    <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i>सारण शिक्षक (दैनिक जागरण / लाइव हिन्दुस्तान):</strong> निर्दलीय प्रत्याशी एवं भाजपा बागी <strong>राहुल राज</strong> ने दूसरी वरीयता के मतों के हस्तांतरण के बाद 4,965 मत प्राप्त कर 800+ मतों से शानदार जीत दर्ज की; जन सुराज के मौजूदा एमएलसी अफाक अहमद और जदयू के आनंद पुष्कर पराजित।
                                </li>
                                <li>
                                    <strong>कोसी स्नातक (दैनिक जागरण / लाइव हिन्दुस्तान):</strong> 14 जिलों में फेली कोसी सीट पर सभी 11 राउंड की प्रथम वरीयता गणना के बाद भाजपा के <strong>डॉ. एन.के. यादव</strong> 24,075 मतों के साथ मजबूत बढ़त पर (राजद के नीतीश कुमार 18,535 और जन सुराज के रजनीश रंजन 15,306)। वर्तमान में 32,201 के विजयी कोटा हेतु दूसरी वरीयता के मतों की गणना जारी।
                                </li>
                                <li>
                                    <strong>पटना एवं तिरहुत स्नातक (द वीक / प्रभात खबर):</strong> पटना स्नातक में जदयू के <strong>नीरज कुमार</strong> राजद के दिलीप पटेल और जन सुराज के अनुज सिंह के खिलाफ कड़े त्रिकोणीय मुकाबले में बढ़त बनाए हुए हैं। तिरहुत स्नातक में मौजूदा निर्दलीय एमएलसी <strong>वंशीधर ब्रजवासी</strong> प्रारंभिक दौर में 2,813 मतों के साथ जदयू के अभिषेक झा (1,739) और राजद की डॉ. रश्मी गौतम (1,407) से आगे चल रहे हैं।
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Live Results Table Card -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                    <div class="card-header bg-white py-3 px-3.5 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-trophy text-danger fs-5"></i>
                            <span class="fw-bold text-dark">सीटवार लाइव मतगणना स्थिति एवं प्रमुख दावेदार</span>
                        </div>
                        <div class="btn-group btn-group-sm" role="group" aria-label="Results Filter">
                            <button type="button" class="btn btn-outline-dark active fw-semibold" onclick="filterResultsTable('all', this)">सभी (8)</button>
                            <button type="button" class="btn btn-outline-primary fw-semibold" onclick="filterResultsTable('Graduates', this)">स्नातक (4)</button>
                            <button type="button" class="btn btn-outline-success fw-semibold" onclick="filterResultsTable('Teachers', this)">शिक्षक (4)</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="resultsReportTable">
                            <thead class="table-light text-uppercase text-secondary small" style="font-size: 11px;">
                                <tr>
                                    <th class="ps-3.5 py-3">निर्वाचन क्षेत्र</th>
                                    <th class="py-3">विजेता / आगे</th>
                                    <th class="py-3">निकटतम प्रतिद्वंद्वी / उप-विजेता</th>
                                    <th class="py-3">स्थिति / राउंड</th>
                                    <th class="py-3">मतगणना स्थल एवं आरओ</th>
                                    <th class="py-3 text-end pe-3.5">स्रोतों का संदर्भ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($electionResults as $row): 
                                    $bInfo = $boothPageMap[$row['slug']] ?? null;
                                    $bUrl = $bInfo ? SITE_URL . '/' . $bInfo['slug'] : '#';
                                    $leadPartyClass = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $row['leading']['party']));
                                    $trailPartyClass = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $row['trailing']['party']));
                                    $isWon = !empty($row['is_declared']);
                                ?>
                                <tr class="results-row <?php echo $isWon ? 'table-success bg-opacity-25' : ''; ?>" data-cat="<?php echo $row['type']; ?>">
                                    <td class="ps-3.5">
                                        <div class="fw-bold text-navy">
                                            <a href="<?php echo $bUrl; ?>" class="text-decoration-none text-dark hover-primary">
                                                <?php echo htmlspecialchars($row['name_hi']); ?>
                                            </a>
                                            <?php if ($isWon): ?>
                                                <span class="badge bg-success text-white extra-small ms-1 rounded-pill px-2 py-0.5"><i class="bi bi-check-circle-fill me-1"></i>विजयी घोषित</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted extra-small">
                                            <?php echo htmlspecialchars($row['name']); ?>
                                        </div>
                                        <div class="d-flex align-items-center gap-1.5 mt-1">
                                            <span class="badge <?php echo $row['type'] === 'Graduates' ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success'; ?> extra-small rounded-pill px-2 py-0.5">
                                                <?php echo $row['type_hi']; ?> कोटा
                                            </span>
                                            <span class="extra-small text-muted">
                                                <?php echo number_format($row['turnout'], 2); ?>% मतदान
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div>
                                                <div class="fw-bold text-dark d-flex align-items-center gap-1" style="font-size: 0.95rem;">
                                                    <?php if ($isWon): ?><i class="bi bi-trophy-fill text-warning"></i><?php endif; ?>
                                                    <span><?php echo htmlspecialchars($row['leading']['name']); ?></span>
                                                </div>
                                                <span class="badge-party <?php echo $leadPartyClass; ?> px-2 py-0.5 extra-small rounded-pill mt-0.5 d-inline-block">
                                                    <?php echo htmlspecialchars($row['leading']['party']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-secondary" style="font-size: 0.9rem;">
                                            <?php echo htmlspecialchars($row['trailing']['name']); ?>
                                        </div>
                                        <span class="badge-party <?php echo $trailPartyClass; ?> px-2 py-0.5 extra-small rounded-pill mt-0.5 d-inline-block">
                                            <?php echo htmlspecialchars($row['trailing']['party']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $row['status_badge']; ?> extra-small rounded-pill px-2.5 py-1">
                                            <?php echo htmlspecialchars($row['status']); ?>
                                        </span>
                                        <div class="text-muted extra-small mt-1" style="max-width: 200px; font-size: 11px;">
                                            <?php echo htmlspecialchars($row['trend_analysis']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-dark">
                                            <i class="bi bi-geo-alt-fill text-danger me-1"></i><?php echo htmlspecialchars($row['counting_centre']); ?>
                                        </div>
                                        <div class="text-muted extra-small">
                                            आरओ: <?php echo htmlspecialchars($row['returning_officer']); ?>
                                        </div>
                                    </td>
                                    <td class="text-end pe-3.5">
                                        <div class="d-flex flex-column align-items-end gap-1">
                                            <?php foreach (array_keys($row['sources_cited']) as $srcName): ?>
                                                <span class="badge bg-light text-dark border extra-small px-2 py-0.5 rounded-pill" style="font-size: 10px;">
                                                    <i class="bi bi-check2 text-success me-1"></i><?php echo htmlspecialchars($srcName); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Multi-Source Disclaimer Footer -->
                    <div class="card-footer bg-light py-2.5 px-3.5 border-top d-flex flex-wrap justify-content-between align-items-center gap-2 small">
                        <div class="text-muted">
                            <i class="bi bi-patch-check-fill text-success me-1"></i>
                            <strong>स्रोत संदर्भ:</strong> लाइव हिन्दुस्तान, दैनिक जागरण, प्रभात खबर एवं मुख्य निर्वाचन पदाधिकारी कार्यालय द्वारा जारी ब्यौरा। अंतिम विजेता प्रमाण पत्र रिटर्निंग ऑफिसर द्वारा जारी किया जाता है।
                        </div>
                        <a href="#seat-deep-dive" class="text-primary fw-semibold text-decoration-none hover-underline d-inline-flex align-items-center gap-1">
                            <span>सीटवार मीडिया रिपोर्ट देखें</span>
                            <i class="bi bi-arrow-down-circle"></i>
                        </a>
                    </div>
                </div>

                <!-- Seat-by-Seat Media Ground Intelligence Breakdown -->
                <div id="seat-deep-dive" class="mb-4">
                    <h5 class="fw-bold text-dark mb-3" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-chat-square-text-fill text-primary me-2"></i>
                        सीटवार मीडिया विश्लेषण एवं ग्राउंड रिपोर्ट (8 सीटें)
                    </h5>

                    <div class="row g-3">
                        <?php foreach ($electionResults as $row): 
                            $bInfo = $boothPageMap[$row['slug']] ?? null;
                            $bUrl = $bInfo ? SITE_URL . '/' . $bInfo['slug'] : '#';
                            $leadPartyClass = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $row['leading']['party']));
                            $trailPartyClass = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $row['trailing']['party']));
                            $isWon = !empty($row['is_declared']);
                        ?>
                        <div class="col-md-6">
                            <div class="card border rounded-4 shadow-sm h-100 bg-white <?php echo $isWon ? 'border-success border-2' : ''; ?>">
                                <div class="card-header bg-white py-3 px-3.5 border-bottom d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <div class="fw-bold text-dark h6 mb-0 d-flex align-items-center gap-1.5">
                                            <?php if ($isWon): ?><i class="bi bi-trophy-fill text-warning"></i><?php endif; ?>
                                            <a href="<?php echo $bUrl; ?>" class="text-decoration-none text-dark hover-primary">
                                                <?php echo htmlspecialchars($row['name_hi']); ?>
                                            </a>
                                        </div>
                                        <div class="text-muted extra-small">
                                            <?php echo htmlspecialchars($row['name']); ?>
                                        </div>
                                    </div>
                                    <span class="badge <?php echo $row['status_badge']; ?> extra-small rounded-pill px-2.5 py-1">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </div>
                                <div class="card-body p-3.5">
                                    <!-- Contender Comparison Box -->
                                    <div class="candidate-lead-box mb-3 <?php echo $isWon ? 'border-success bg-success bg-opacity-10' : ''; ?>">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="extra-small text-uppercase fw-bold <?php echo $isWon ? 'text-success' : 'text-primary'; ?>">
                                                <?php echo $isWon ? 'विजयी घोषित (ELECTED MLC):' : 'रुझान में आगे / बढ़त:'; ?>
                                            </span>
                                            <span class="badge-party <?php echo $leadPartyClass; ?> extra-small px-2 py-0.5 rounded-pill">
                                                <?php echo htmlspecialchars($row['leading']['party']); ?>
                                            </span>
                                        </div>
                                        <div class="fw-bold text-dark" style="font-size: 1.05rem;">
                                            <?php echo htmlspecialchars($row['leading']['name']); ?>
                                        </div>
                                        <small class="text-muted d-block"><?php echo htmlspecialchars($row['leading']['role']); ?></small>
                                        
                                        <hr class="my-2 border-secondary border-opacity-25">
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="extra-small text-uppercase fw-bold text-secondary">
                                                <?php echo $isWon ? 'निकटतम प्रतिद्वंद्वी / उप-विजेता:' : 'प्रमुख प्रतिद्वंद्वी:'; ?>
                                            </span>
                                            <span class="badge-party <?php echo $trailPartyClass; ?> extra-small px-2 py-0.5 rounded-pill">
                                                <?php echo htmlspecialchars($row['trailing']['party']); ?>
                                            </span>
                                        </div>
                                        <div class="fw-semibold text-secondary">
                                            <?php echo htmlspecialchars($row['trailing']['name']); ?>
                                        </div>
                                        <small class="text-muted d-block"><?php echo htmlspecialchars($row['trailing']['role']); ?></small>
                                    </div>

                                    <!-- Media Specific Reporting Points -->
                                    <div class="bg-light p-2.5 rounded-3 mb-3 border">
                                        <span class="extra-small fw-bold text-uppercase text-secondary d-block mb-1.5">
                                            <i class="bi bi-newspaper text-danger me-1"></i> विभिन्न स्रोतों की रिपोर्ट:
                                        </span>
                                        <ul class="mb-0 ps-3 extra-small text-muted" style="line-height: 1.45;">
                                            <?php foreach ($row['sources_cited'] as $src => $txt): ?>
                                                <li class="mb-1">
                                                    <strong class="text-dark"><?php echo htmlspecialchars($src); ?>:</strong> 
                                                    <?php echo htmlspecialchars($txt); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>

                                    <!-- Venue & Turnout Metadata -->
                                    <div class="d-flex flex-wrap justify-content-between align-items-center text-muted extra-small pt-1 border-top">
                                        <div>
                                            <i class="bi bi-building me-1 text-primary"></i>
                                            <span><?php echo htmlspecialchars($row['counting_centre']); ?></span>
                                        </div>
                                        <div>
                                            <a href="<?php echo $bUrl; ?>" class="text-decoration-none text-primary fw-semibold">
                                                <span><?php echo $row['booths']; ?> बूथ डेटा देखें &rarr;</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Single Transferable Vote (PR-STV) Quota Explainer -->
                <div class="card border-0 shadow-sm rounded-4 p-3.5 mb-4" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-left: 4px solid #2563eb !important;">
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-calculator-fill text-primary fs-3 mt-1"></i>
                        <div>
                            <h6 class="fw-bold text-primary mb-1">विधान परिषद चुनाव में कोटा निर्धारण का नियम (Rule 75)</h6>
                            <p class="small text-secondary mb-2" style="line-height: 1.5;">
                                लोक प्रतिनिधित्व अधिनियम 1951 एवं निर्वाचन संचालन नियमावली 1961 के नियम 75 के अंतर्गत, विधान परिषद के स्नातक एवं शिक्षक क्षेत्रों में <strong>आनुपातिक प्रतिनिधित्व की एकल संक्रमणीय मत प्रणाली (PR-STV)</strong> द्वारा मतपत्रों से चुनाव होता है। जीत के लिए उम्मीदवार को निर्धारित कोटा हासिल करना अनिवार्य होता है:
                            </p>
                            <div class="bg-white p-2.5 rounded-3 d-inline-block border shadow-sm mb-2 font-monospace text-dark small fw-bold">
                                विजयी कोटा = [ कुल वैध प्रथम वरीयता मतपत्र / (1 + 1) ] + 1
                            </div>
                            <p class="extra-small text-muted mb-0">
                                यदि कोई प्रत्याशी प्रथम वरीयता में यह कोटा प्राप्त नहीं कर पाता, तो सबसे कम मत पाने वाले प्रत्याशी को हटाकर उसके मतपत्रों की द्वितीय वरीयता (Second Preference) के मत अन्य प्रत्याशियों को हस्तांतरित किए जाते हैं।
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- 2.5 LIVE VOTING TURNOUT & INCUMBENT MLCS (HINDI) -->
            <!-- ============================================================= -->
            <div id="voting-turnout" class="mb-5">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                    <div>
                        <span class="badge bg-danger text-white fw-bold text-uppercase px-2.5 py-1 mb-1 blink-anim">
                            <i class="bi bi-broadcast me-1"></i> मतदान संपन्न &bull; लाइव टर्नआउट रिपोर्ट
                        </span>
                        <h3 class="fw-bold mb-0 text-dark" style="font-family: 'Outfit', sans-serif;">
                            8 सीटों का मतदान प्रतिशत एवं वर्तमान विधान पार्षद
                        </h3>
                        <div class="text-muted small">
                            शाम 4 बजे तक मतदान प्रतिशत, वर्तमान विधान पार्षद एवं 27 अक्टूबर मतगणना विवरण
                        </div>
                    </div>
                    <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                        <i class="bi bi-clock-history me-1"></i> शाम 4 बजे तक मतदान समाप्त
                    </span>
                </div>

                <!-- Live Highlight Metrics Cards -->
                <div class="row g-2 g-sm-3 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-4 bg-white shadow-sm border border-danger border-opacity-25 h-100 text-center">
                            <span class="text-muted extra-small fw-bold text-uppercase d-block mb-1">कुल औसत मतदान</span>
                            <div class="h3 fw-extrabold text-danger mb-0 font-monospace">59%+</div>
                            <small class="text-muted" style="font-size: 11px;">औसत वोटिंग</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-4 bg-white shadow-sm border border-success border-opacity-25 h-100 text-center">
                            <span class="text-muted extra-small fw-bold text-uppercase d-block mb-1">शिक्षक सीटें औसत</span>
                            <div class="h3 fw-extrabold text-success mb-0 font-monospace">74.55%</div>
                            <small class="text-success fw-semibold" style="font-size: 11px;">शिक्षकों में भारी उत्साह</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-4 bg-white shadow-sm border border-primary border-opacity-25 h-100 text-center">
                            <span class="text-muted extra-small fw-bold text-uppercase d-block mb-1">स्नातक सीटें औसत</span>
                            <div class="h3 fw-extrabold text-primary mb-0 font-monospace">43.87%</div>
                            <small class="text-muted" style="font-size: 11px;">स्नातकों में कम रुचि</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-4 bg-white shadow-sm border border-warning border-opacity-50 h-100 text-center">
                            <span class="text-muted extra-small fw-bold text-uppercase d-block mb-1">मतगणना एवं परिणाम</span>
                            <div class="h4 fw-extrabold text-dark mb-0 font-monospace">27 Oct</div>
                            <small class="text-muted" style="font-size: 11px;">सुबह 8 बजे से</small>
                        </div>
                    </div>
                </div>

                <!-- Analysis Summary Alert -->
                <div class="alert alert-light border border-secondary border-opacity-25 rounded-4 p-3 mb-4 shadow-sm" style="background-color: #fdfefe;">
                    <div class="d-flex align-items-start gap-2.5">
                        <i class="bi bi-info-circle-fill text-primary fs-5 mt-0.5"></i>
                        <div class="small text-secondary" style="line-height: 1.55;">
                            <strong class="text-dark">मुख्य विशेषताएं:</strong>
                            शिक्षकों में मतदान को लेकर भारी उत्साह देखा गया जबकि स्नातकों में वोटिंग को लेकर उत्साह की कमी रही। 
                            <strong>सारण शिक्षक (87.16%)</strong> एवं <strong>कोसी स्नातक (52.51%)</strong> अपने-अपने वर्ग में सबसे आगे रहे, जबकि 
                            <strong>पटना</strong> दोनों ही श्रेणियों (स्नातक 36.19% एवं शिक्षक 56.89%) में सबसे फिसड्डी रहा। 
                            शाम 4 बजे तक कतार में लगे मतदाताओं के वोट डालने के बाद मतपेटियां सील होकर स्ट्रांग रूम रवाना हुईं। 
                            सभी सीटों का परिणाम <strong>27 अक्टूबर</strong> को मतगणना के बाद घोषित किया जाएगा।
                        </div>
                    </div>
                </div>

                <!-- Turnout & Incumbent MLCs Table -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-3">
                    <div class="card-header bg-white py-3 px-3.5 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-table text-primary fs-5"></i>
                            <span class="fw-bold text-dark">सीटवार मतदान प्रतिशत एवं वर्तमान विधान पार्षद</span>
                        </div>
                        <div class="btn-group btn-group-sm" role="group" aria-label="Turnout Filter">
                            <button type="button" class="btn btn-outline-dark active fw-semibold" onclick="filterTurnoutTable('all', this)">सभी (8)</button>
                            <button type="button" class="btn btn-outline-primary fw-semibold" onclick="filterTurnoutTable('Graduates', this)">स्नातक (4)</button>
                            <button type="button" class="btn btn-outline-success fw-semibold" onclick="filterTurnoutTable('Teachers', this)">शिक्षक (4)</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="turnoutReportTable">
                            <thead class="table-light text-uppercase text-secondary small" style="font-size: 11px;">
                                <tr>
                                    <th class="ps-3.5 py-3">निर्वाचन क्षेत्र</th>
                                    <th class="py-3" style="min-width: 170px;">मतदान प्रतिशत (शाम 4 बजे तक)</th>
                                    <th class="py-3">वर्तमान विधान पार्षद (Sitting MLC)</th>
                                    <th class="py-3 text-center">दल / पार्टी</th>
                                    <th class="py-3">कार्यकाल समाप्ति</th>
                                    <th class="py-3 text-end pe-3.5">बूथ हब</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($turnoutReport as $row): 
                                    $bInfo = $boothPageMap[$row['slug']] ?? null;
                                    $bUrl = $bInfo ? SITE_URL . '/' . $bInfo['slug'] : '#';
                                    $partyClass = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $row['party']));
                                ?>
                                <tr class="turnout-row" data-cat="<?php echo $row['category']; ?>">
                                    <td class="ps-3.5">
                                        <div class="fw-bold text-navy">
                                            <a href="<?php echo $bUrl; ?>" class="text-decoration-none text-dark hover-primary">
                                                <?php echo htmlspecialchars($row['name_hi']); ?>
                                            </a>
                                        </div>
                                        <div class="text-muted extra-small">
                                            <?php echo htmlspecialchars($row['name']); ?>
                                        </div>
                                        <span class="badge <?php echo $row['category'] === 'Graduates' ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success'; ?> extra-small rounded-pill px-2 py-0.5 mt-1">
                                            <?php echo $row['category_hi']; ?> कोटा
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="h5 fw-extrabold mb-0 <?php echo $row['turnout'] >= 75 ? 'text-success' : ($row['turnout'] < 40 ? 'text-danger' : 'text-primary'); ?>">
                                                <?php echo number_format($row['turnout'], 2); ?>%
                                            </span>
                                            <span class="badge <?php echo $row['rank_badge']; ?> extra-small rounded-pill" style="font-size: 10px;">
                                                <?php echo htmlspecialchars($row['turnout_rank']); ?>
                                            </span>
                                        </div>
                                        <div class="progress rounded-pill" style="height: 6px; background-color: #e2e8f0; max-width: 160px;">
                                            <div class="progress-bar <?php echo $row['turnout'] >= 75 ? 'bg-success' : ($row['turnout'] < 40 ? 'bg-danger' : 'bg-primary'); ?>" 
                                                 role="progressbar" 
                                                 style="width: <?php echo min(100, $row['turnout']); ?>%;" 
                                                 aria-valuenow="<?php echo $row['turnout']; ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-navy" style="font-size: 0.95rem;">
                                            <?php echo htmlspecialchars($row['sitting_mlc']); ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-party <?php echo $partyClass; ?> px-2.5 py-1">
                                            <?php echo htmlspecialchars($row['party']); ?>
                                        </span>
                                    </td>
                                    <td class="small text-secondary">
                                        <i class="bi bi-calendar-event me-1 text-muted"></i>
                                        <?php echo htmlspecialchars($row['tenure']); ?>
                                    </td>
                                    <td class="text-end pe-3.5">
                                        <a href="<?php echo $bUrl; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-1 extra-small fw-semibold text-nowrap" title="बूथ सूची देखें">
                                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                            <span><?php echo $row['booth_count']; ?> बूथ</span>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Source Citation & Counting Footer -->
                    <div class="card-footer bg-light py-2.5 px-3.5 border-top d-flex flex-wrap justify-content-between align-items-center gap-2 small">
                        <div class="text-muted">
                            <i class="bi bi-newspaper text-danger me-1"></i>
                            <strong>स्रोत / संदर्भ:</strong> लाइव हिंदुस्तान रिपोर्ट (Live Blog) &bull; अंतिम आंकड़े चुनाव आयोग की संवीक्षा के अधीन।
                        </div>
                        <a href="https://www.livehindustan.com/bihar/bihar-mlc-election-2026-voting-live-bjp-rjd-jdu-chunav-latest-updates-live-blog-201792729617020.html" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="text-primary fw-semibold text-decoration-none hover-underline d-inline-flex align-items-center gap-1">
                            <span>लाइव हिंदुस्तान रिपोर्ट पढ़ें</span>
                            <i class="bi bi-box-arrow-up-right" style="font-size: 10px;"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- 3. OFFICIAL ELECTION SCHEDULE & TIMELINE -->
            <!-- ============================================================= -->
            <div id="schedule" class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase px-2.5 py-1 mb-1">Official ECI Schedule</span>
                        <h3 class="fw-bold mb-0 text-dark" style="font-family: 'Outfit', sans-serif;">
                            Election Schedule &amp; Key Deadlines
                        </h3>
                    </div>
                    <a href="https://ceoelection.bihar.gov.in/PDF/Year_2026/ImportantInstructionsAndLetters/TCGCGajat2026.pdf" target="_blank" class="btn btn-outline-danger btn-sm fw-semibold">
                        <i class="bi bi-filetype-pdf me-1"></i> Official Notification
                    </a>
                </div>

                <div class="bg-white p-4 rounded-4 shadow-sm border">
                    <div class="timeline-container">
                        <?php foreach ($timeline as $t): ?>
                            <div class="timeline-card <?php echo $t['status']; ?>">
                                <div class="timeline-badge">
                                    <?php if ($t['status'] === 'completed'): ?>
                                        <i class="bi bi-check-lg"></i>
                                    <?php else: ?>
                                        <?php echo $t['step']; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
                                    <h5 class="fw-bold mb-0 text-dark">
                                        <?php echo htmlspecialchars($t['title']); ?>
                                    </h5>
                                    <span class="badge <?php echo $t['status'] === 'completed' ? 'bg-success' : ($t['status'] === 'current' ? 'bg-primary' : 'bg-light text-dark border'); ?> fw-bold px-2.5 py-1">
                                        <?php echo htmlspecialchars($t['date']); ?> (<?php echo htmlspecialchars($t['day']); ?>)
                                    </span>
                                </div>
                                <div class="text-muted small fw-semibold mb-1" style="font-family: 'Noto Sans Devanagari', sans-serif;">
                                    <?php echo htmlspecialchars($t['title_hi']); ?>
                                </div>
                                <p class="text-secondary small mb-0">
                                    <?php echo htmlspecialchars($t['desc']); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- 4. CANDIDATE NOMINATION PROCESS, FORMS & GUIDELINES -->
            <!-- ============================================================= -->
            <div id="nomination-details" class="mb-5">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                    <div>
                        <span class="badge bg-warning text-dark fw-bold text-uppercase px-2.5 py-1 mb-1">
                            <i class="bi bi-file-earmark-person-fill me-1"></i> Candidate Filing Desk
                        </span>
                        <h3 class="fw-bold mb-0 text-dark" style="font-family: 'Outfit', sans-serif;">
                            Nomination Details &amp; Guidelines (नामनिर्देशन प्रक्रिया)
                        </h3>
                    </div>
                    <span class="badge bg-danger fw-bold px-3 py-1.5 rounded-pill animate-pulse">
                        <i class="bi bi-clock-history me-1"></i> Window: 29 Sep – 06 Oct 2026 (11 AM – 3 PM)
                    </span>
                </div>

                <!-- Nomination Status Banner -->
                <div class="alert alert-warning border-warning border-opacity-50 rounded-4 p-3.5 mb-4 shadow-sm">
                    <div class="d-flex align-items-start gap-3">
                        <div class="bg-warning text-dark rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                            <i class="bi bi-megaphone-fill fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Nomination Filing is Currently Underway Across 8 Constituencies</h6>
                            <p class="text-dark small opacity-90 mb-2" style="line-height: 1.5;">
                                Candidates contesting for <strong>4 Graduates'</strong> (Patna, Tirhut, Darbhanga, Kosi) and <strong>4 Teachers'</strong> (Patna, Tirhut, Darbhanga, Saran) constituencies must submit their nomination papers to their respective Returning Officer (Divisional Commissioner) between <strong>11:00 AM and 3:00 PM</strong> on any working day up to <strong>06 October 2026</strong>.
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="https://affidavit.eci.gov.in/" target="_blank" rel="noopener" class="btn btn-dark btn-sm rounded-pill fw-semibold">
                                    <i class="bi bi-search me-1"></i> View Submitted Candidate Affidavits (ECI) &rarr;
                                </a>
                                <a href="https://suvidha.eci.gov.in/" target="_blank" rel="noopener" class="btn btn-outline-dark btn-sm rounded-pill fw-semibold">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> ECI Suvidha Candidate Portal
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nomination Forms & Statutory Requirements -->
                <div class="row g-3 mb-4">
                    <!-- Form 2E Card -->
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white border-top border-primary border-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="bg-primary-subtle text-primary rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                    <i class="bi bi-file-earmark-text-fill fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Form 2E (प्ररूप 2ङ)</h5>
                                    <small class="text-muted">Nomination Paper for Council Constituencies</small>
                                </div>
                            </div>
                            <p class="text-secondary small mb-3">
                                Prescribed statutory nomination form under Conduct of Elections Rules, 1961 for Legislative Council Graduates' and Teachers' Constituencies.
                            </p>
                            <ul class="text-secondary small ps-3 mb-4" style="line-height: 1.6;">
                                <li><strong>Part I:</strong> Used for candidates set up by recognized political parties (requires 1 elector proposer).</li>
                                <li><strong>Part II:</strong> Used for independent or unrecognized registered party candidates (requires <strong>10 registered electors</strong> of that constituency as proposers).</li>
                                <li><strong>Part III:</strong> Candidate's declaration of age, symbol choice, and citizenship oath.</li>
                            </ul>
                            <a href="https://ceoelection.bihar.gov.in/" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill fw-semibold mt-auto">
                                <i class="bi bi-download me-1"></i> Download Form 2E Blank Template
                            </a>
                        </div>
                    </div>

                    <!-- Form 26 Affidavit Card -->
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white border-top border-warning border-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="bg-warning-subtle text-warning-emphasis rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                    <i class="bi bi-shield-check fs-4 text-warning"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Form 26 (प्ररूप 26 - शपथ पत्र)</h5>
                                    <small class="text-muted">Mandatory Candidate Affidavit</small>
                                </div>
                            </div>
                            <p class="text-secondary small mb-3">
                                Mandatory sworn affidavit on non-judicial stamp paper attested by a Notary Public or Oath Commissioner before the RO.
                            </p>
                            <ul class="text-secondary small ps-3 mb-4" style="line-height: 1.6;">
                                <li><strong>Criminal Antecedents:</strong> Disclosure of FIRs, pending cases, convictions, or charges framed.</li>
                                <li><strong>Assets &amp; Liabilities:</strong> Complete movable/immovable assets and liabilities of candidate, spouse, and dependents.</li>
                                <li><strong>PAN &amp; Tax:</strong> PAN details and last 5 years' Income Tax returns.</li>
                                <li><strong>Educational Qualifications:</strong> Highest educational degree with school/university details.</li>
                            </ul>
                            <a href="https://affidavit.eci.gov.in/" target="_blank" class="btn btn-outline-warning text-dark btn-sm rounded-pill fw-semibold mt-auto">
                                <i class="bi bi-box-arrow-up-right me-1"></i> ECI Online Affidavit Portal (Form 26)
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Key Rules & Eligibility Table -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="fw-bold text-dark mb-3" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-check2-square text-success me-2"></i> Key Rules &amp; Qualifications for Contesting Candidates
                    </h5>

                    <div class="row g-3 small">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100 border">
                                <div class="fw-bold text-dark mb-1">
                                    <i class="bi bi-person-check-fill text-primary me-1"></i> Age &amp; Elector Qualifications
                                </div>
                                <ul class="text-muted mb-0 ps-3" style="line-height: 1.5;">
                                    <li>Candidate must be a citizen of India and minimum <strong>30 years of age</strong> as on the qualifying date.</li>
                                    <li>Candidate must be registered as an elector in any Assembly constituency in Bihar.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100 border">
                                <div class="fw-bold text-dark mb-1">
                                    <i class="bi bi-cash-stack text-success me-1"></i> Security Deposit (जमानत राशि)
                                </div>
                                <ul class="text-muted mb-0 ps-3" style="line-height: 1.5;">
                                    <li><strong>General Category:</strong> ₹10,000/-</li>
                                    <li><strong>SC / ST Category:</strong> ₹5,000/- (valid caste certificate required).</li>
                                    <li>Deposited in cash with the Returning Officer or via Government Treasury Challan.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100 border">
                                <div class="fw-bold text-dark mb-1">
                                    <i class="bi bi-people-fill text-info me-1"></i> Proposers Requirement (प्रस्तावक)
                                </div>
                                <ul class="text-muted mb-0 ps-3" style="line-height: 1.5;">
                                    <li><strong>Recognized National/State Parties:</strong> 1 proposer registered in that council constituency.</li>
                                    <li><strong>Unrecognized Parties / Independents:</strong> <strong>10 proposers</strong> who must be registered electors in the respective Graduate or Teacher electoral roll.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100 border">
                                <div class="fw-bold text-dark mb-1">
                                    <i class="bi bi-building text-danger me-1"></i> Where to Submit Nominations
                                </div>
                                <ul class="text-muted mb-0 ps-3" style="line-height: 1.5;">
                                    <li>Submitted in person by the candidate or their authorized proposer to the designated <strong>Returning Officer (Divisional Commissioner)</strong> at their divisional headquarters.</li>
                                    <li>Maximum 4 sets of nomination papers allowed per candidate.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- 5. ALL 8 CONSTITUENCIES DETAILS -->
            <!-- ============================================================= -->
            <div id="constituencies" class="mb-5">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                    <div>
                        <span class="badge bg-danger-subtle text-danger fw-bold text-uppercase px-2.5 py-1 mb-1">8 Seats to Poll</span>
                        <h3 class="fw-bold mb-0 text-dark" style="font-family: 'Outfit', sans-serif;">
                            Constituencies Going to Polls
                        </h3>
                    </div>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-dark active fw-semibold" onclick="filterSeats('all', this)">All (8)</button>
                        <button type="button" class="btn btn-outline-primary fw-semibold" onclick="filterSeats('Graduates', this)">Graduates (4)</button>
                        <button type="button" class="btn btn-outline-success fw-semibold" onclick="filterSeats('Teachers', this)">Teachers (4)</button>
                    </div>
                </div>

                <div class="row g-3" id="seatsGrid">
                    <?php foreach ($seats as $seat): ?>
                        <div class="col-md-6 seat-card-item" data-type="<?php echo $seat['type']; ?>" data-districts="<?php echo htmlspecialchars(implode(' ', $seat['districts'])); ?>">
                            <div class="card h-100 constituency-card shadow-sm p-3 bg-white">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge <?php echo $seat['type'] === 'Graduates' ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success'; ?> fw-bold px-2.5 py-1 rounded-pill">
                                        <i class="bi <?php echo $seat['type'] === 'Graduates' ? 'bi-mortarboard-fill' : 'bi-person-video3'; ?> me-1"></i>
                                        <?php echo $seat['type']; ?> Quota
                                    </span>
                                    <span class="small text-muted font-monospace"><i class="bi bi-clock me-1"></i> Term Expiry: Nov 2026</span>
                                </div>

                                <h5 class="fw-bold text-dark mb-1">
                                    <?php echo htmlspecialchars($seat['name']); ?>
                                </h5>
                                <div class="text-muted small mb-2 fw-semibold" style="font-family: 'Noto Sans Devanagari', sans-serif;">
                                    <?php echo htmlspecialchars($seat['name_hi']); ?>
                                </div>

                                <p class="text-secondary small mb-3">
                                    <?php echo htmlspecialchars($seat['description']); ?>
                                </p>

                                <div class="bg-light p-2.5 rounded-3 mb-3 small">
                                    <div class="mb-1">
                                        <strong class="text-dark"><i class="bi bi-building me-1 text-secondary"></i> Returning Officer:</strong>
                                        <div class="text-muted">
                                            <?php echo htmlspecialchars($seat['ro_hi'] ?? $seat['ro']); ?>
                                            <?php if (!empty($seat['ro_url'])): ?>
                                                <a href="<?php echo htmlspecialchars($seat['ro_url']); ?>" target="_blank" rel="noopener" class="ms-1 text-primary text-decoration-none hover-underline extra-small" title="प्रमंडलीय आयुक्त आधिकारिक पोर्टल">
                                                    <i class="bi bi-box-arrow-up-right"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div>
                                        <strong class="text-dark"><i class="bi bi-geo-alt me-1 text-danger"></i> HQ:</strong> <?php echo htmlspecialchars($seat['hq']); ?>
                                    </div>
                                </div>

                                <div class="mt-auto">
                                    <div class="small fw-bold text-muted mb-1.5 text-uppercase" style="font-size: 11px;">
                                        <i class="bi bi-map me-1"></i> Districts in this Constituency (<?php echo count($seat['districts']); ?>):
                                    </div>
                                    <div class="d-flex flex-wrap gap-1 mb-2.5">
                                        <?php foreach ($seat['districts'] as $dist): ?>
                                            <span class="district-tag"><?php echo htmlspecialchars($dist); ?></span>
                                        <?php endforeach; ?>
                                    </div>

                                    <?php 
                                        $bCard = $boothPageMap[$seat['id']] ?? null;
                                    ?>
                                    <?php if ($bCard): ?>
                                        <div class="pt-2 border-top">
                                            <a href="<?php echo SITE_URL . '/' . $bCard['slug']; ?>" class="btn <?php echo $seat['type'] === 'Graduates' ? 'btn-outline-primary' : 'btn-outline-success'; ?> btn-sm w-100 rounded-pill fw-semibold py-1.5 d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm">
                                                <i class="bi bi-geo-alt-fill text-danger"></i>
                                                <span>सभी <?php echo $bCard['count']; ?> मतदान केंद्र देखें (Polling Booths)</span>
                                                <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- 5. INTERACTIVE DISTRICT LOOKUP FINDER -->
            <!-- ============================================================= -->
            <div id="district-finder" class="mb-5">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff;">
                    <div class="card-body p-4 p-md-5">
                        <div class="row align-items-center g-4">
                            <div class="col-md-6">
                                <span class="badge bg-warning text-dark fw-bold px-3 py-1 mb-2 rounded-pill">VOTER UTILITY</span>
                                <h3 class="fw-bold mb-2 text-white" style="font-family: 'Outfit', sans-serif;">
                                    Find Your Vidhan Parishad Constituency
                                </h3>
                                <p class="text-light opacity-90 small mb-0">
                                    Select or type your district to discover which <strong>Graduates'</strong> and <strong>Teachers'</strong> constituencies you belong to and who your Returning Officer is.
                                </p>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-white p-3 rounded-4 shadow text-dark">
                                    <label class="form-label fw-bold small text-muted text-uppercase mb-1">Select Your District (38 Districts):</label>
                                    <select class="form-select form-select-lg mb-3 fw-semibold" id="districtSelect" onchange="lookupDistrictConstituency(this.value)">
                                        <option value="">-- Choose Your District --</option>
                                        <option value="Patna">Patna (पटना)</option>
                                        <option value="Nalanda">Nalanda (नालंदा)</option>
                                        <option value="Nawada">Nawada (नवादा)</option>
                                        <option value="Muzaffarpur">Muzaffarpur (मुजफ्फरपुर)</option>
                                        <option value="Vaishali">Vaishali (वैशाली)</option>
                                        <option value="Sitamarhi">Sitamarhi (सीतामढ़ी)</option>
                                        <option value="Sheohar">Sheohar (शिवहर)</option>
                                        <option value="Darbhanga">Darbhanga (दरभंगा)</option>
                                        <option value="Madhubani">Madhubani (मधुबनी)</option>
                                        <option value="Samastipur">Samastipur (समस्तीपुर)</option>
                                        <option value="Begusarai">Begusarai (बेगूसराय)</option>
                                        <option value="Saharsa">Saharsa (सहरसा)</option>
                                        <option value="Supaul">Supaul (सुपौल)</option>
                                        <option value="Madhepura">Madhepura (मधेपुरा)</option>
                                        <option value="Purnia">Purnia (पूर्णिया)</option>
                                        <option value="Araria">Araria (अररिया)</option>
                                        <option value="Kishanganj">Kishanganj (किशनगंज)</option>
                                        <option value="Katihar">Katihar (कटिहार)</option>
                                        <option value="Bhagalpur">Bhagalpur (भागलपुर)</option>
                                        <option value="Banka">Banka (बांका)</option>
                                        <option value="Munger">Munger (मुंगेर)</option>
                                        <option value="Jamui">Jamui (जमुई)</option>
                                        <option value="Lakhisarai">Lakhisarai (लखीसराय)</option>
                                        <option value="Sheikhpura">Sheikhpura (शेखपुरा)</option>
                                        <option value="Khagaria">Khagaria (खगड़िया)</option>
                                        <option value="Saran">Saran / Chapra (सारण / छपरा)</option>
                                        <option value="Siwan">Siwan (सिवान)</option>
                                        <option value="Gopalganj">Gopalganj (गोपालगंज)</option>
                                        <option value="East Champaran">East Champaran / Motihari (पूर्वी चंपारण)</option>
                                        <option value="West Champaran">West Champaran / Bettiah (पश्चिमी चंपारण)</option>
                                        <option value="Gaya">Gaya (गया)</option>
                                        <option value="Jehanabad">Jehanabad (जहानाबाद)</option>
                                        <option value="Arwal">Arwal (अरवल)</option>
                                        <option value="Aurangabad">Aurangabad (औरंगाबाद)</option>
                                        <option value="Rohtas">Rohtas / Sasaram (रोहतास)</option>
                                        <option value="Kaimur">Kaimur / Bhabhua (कैमूर)</option>
                                        <option value="Bhojpur">Bhojpur / Ara (भोजपुर)</option>
                                        <option value="Buxar">Buxar (बक्सर)</option>
                                    </select>

                                    <!-- Lookup Result Card -->
                                    <div id="lookupResultBox" class="p-3 bg-light rounded-3 border d-none">
                                        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                            <span class="fw-bold text-dark" id="resDistrictName">Patna District</span>
                                            <span class="badge bg-success">Active 2026 Election</span>
                                        </div>
                                        <div class="row g-2 small">
                                            <div class="col-12">
                                                <div class="p-2 bg-white rounded border">
                                                    <span class="text-primary fw-bold"><i class="bi bi-mortarboard-fill me-1"></i> Graduates Seat:</span>
                                                    <div class="fw-semibold text-dark" id="resGradSeat">-</div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="p-2 bg-white rounded border">
                                                    <span class="text-success fw-bold"><i class="bi bi-person-video3 me-1"></i> Teachers Seat:</span>
                                                    <div class="fw-semibold text-dark" id="resTeachSeat">-</div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="p-2 bg-white rounded border text-muted">
                                                    <strong>Returning Officer:</strong> <span id="resRO">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- 6. OFFICIAL VOTER LIST, ELECTORAL ROLL & REGISTRATION HUB -->
            <!-- ============================================================= -->
            <div id="voter-guide" class="mb-5">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                    <div>
                        <span class="badge bg-success text-white fw-bold text-uppercase px-2.5 py-1 mb-1">
                            <i class="bi bi-person-check-fill me-1"></i> निर्वाचक नामावली एवं मतदाता मार्गदर्शिका
                        </span>
                        <h3 class="fw-bold mb-0 text-dark" style="font-family: 'Outfit', sans-serif;">
                            मतदाता सूची एवं निर्वाचक नामावली (Voter List &amp; Electoral Roll)
                        </h3>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="https://ceo.bihar.gov.in/tcgcelectorsearch/" target="_blank" rel="noopener" class="btn btn-warning text-dark btn-sm rounded-pill fw-bold shadow-sm d-inline-flex align-items-center gap-1.5">
                            <i class="bi bi-person-search"></i> CEO मतदाता सूची में नाम खोजें &rarr;
                        </a>
                        <a href="https://ceo.bihar.gov.in/tcgconline/" target="_blank" rel="noopener" class="btn btn-primary btn-sm rounded-pill fw-bold shadow-sm d-inline-flex align-items-center gap-1.5">
                            <i class="bi bi-pencil-square"></i> ऑनलाइन आवेदन (प्ररूप 18/19) &rarr;
                        </a>
                        <a href="https://voters.eci.gov.in/" target="_blank" rel="noopener" class="btn btn-outline-success btn-sm rounded-pill fw-semibold d-inline-flex align-items-center gap-1">
                            <i class="bi bi-check2-circle"></i> ECI पोर्टल
                        </a>
                    </div>
                </div>

                <!-- Electoral Roll Notice & Verification Alert -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4 border-start border-success border-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="bg-success-subtle text-success rounded-circle p-2.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                            <i class="bi bi-journal-check fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold text-dark mb-1">विधान परिषद निर्वाचक नामावली संबंधी विशेष सूचना</h5>
                            <p class="text-secondary small mb-3" style="line-height: 1.6;">
                                विधानसभा (Vidhan Sabha) की सामान्य मतदाता सूची के विपरीत, <strong>विधान परिषद के स्नातक एवं शिक्षक निर्वाचन क्षेत्रों की निर्वाचक नामावली प्रत्येक द्विवार्षिक चुनाव से पूर्व नए सिरे से तैयार की जाती है</strong>। सामान्य मतदाता सूची में नाम होना स्वतः ही विधान परिषद चुनाव में मतदान का अधिकार नहीं देता — मतदाताओं को <strong>प्ररूप 18 (स्नातक)</strong> अथवा <strong>प्ररूप 19 (शिक्षक)</strong> भरकर पृथक पंजीकरण कराना अनिवार्य होता है।
                            </p>
                            
                            <div class="row g-2 pt-1">
                                <div class="col-md-3 col-sm-6">
                                    <div class="p-2.5 bg-light rounded-3 border h-100">
                                        <span class="text-muted d-block" style="font-size: 11px;">अर्हता तिथि (Qualifying Date):</span>
                                        <strong class="text-dark small">01 नवम्बर 2025</strong>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="p-2.5 bg-light rounded-3 border h-100">
                                        <span class="text-muted d-block" style="font-size: 11px;">पुनरीक्षण स्थिति (Revision Status):</span>
                                        <span class="badge bg-success-subtle text-success fw-bold">अंतिम नामावली (06.10.2026)</span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="p-2.5 bg-light rounded-3 border h-100">
                                        <span class="text-muted d-block" style="font-size: 11px;">मतदाता ऑनलाइन सत्यापन:</span>
                                        <a href="https://ceo.bihar.gov.in/tcgcelectorsearch/" target="_blank" rel="noopener" class="text-warning fw-bold small text-decoration-none hover-underline d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-search"></i> CEO नाम खोजें &rarr;
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="p-2.5 bg-light rounded-3 border h-100">
                                        <span class="text-muted d-block" style="font-size: 11px;">ऑनलाइन पंजीकरण:</span>
                                        <a href="https://ceo.bihar.gov.in/tcgconline/" target="_blank" rel="noopener" class="text-primary fw-bold small text-decoration-none hover-underline d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-box-arrow-up-right"></i> CEO ऑनलाइन पोर्टल &rarr;
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 8 Constituencies Electoral Roll Quick-Access Table -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
                    <div class="card-header bg-light py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-table text-primary me-2"></i> 8 निर्वाचन क्षेत्र निर्वाचक नामावली एवं निर्वाचक निबंधन पदाधिकारी (ERO) निर्देशिका
                            </h6>
                            <small class="text-muted">समर्पित मतदान केंद्र सूची देखें, मतदाता सूची में नाम खोजें या आधिकारिक PDF डाउनलोड करें</small>
                        </div>
                        <span class="badge bg-primary text-white small px-2.5 py-1">8 द्विवार्षिक सीटें</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light text-uppercase text-secondary" style="font-size: 11px;">
                                <tr>
                                    <th class="ps-4">निर्वाचन क्षेत्र का नाम</th>
                                    <th>प्रकार</th>
                                    <th>सम्मिलित जिले</th>
                                    <th>निर्वाचक निबंधन पदाधिकारी (ERO)</th>
                                    <th class="text-end pe-4" style="min-width: 320px;">मतदाता सूची लिंक / विवरण</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($seats as $s): 
                                    $bInfo = $boothPageMap[$s['id']] ?? null;
                                    $boothUrl = $bInfo ? SITE_URL . '/' . $bInfo['slug'] : '#';
                                    $boothCount = $bInfo ? $bInfo['count'] : '';
                                    $pdfUrl = ($s['type'] === 'Graduates') 
                                        ? 'https://ceo.bihar.gov.in/GCTCPDFVIEW/GCPDF.ASPX' 
                                        : 'https://ceo.bihar.gov.in/GCTCPDFVIEW/TCPDF.ASPX';
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <a href="<?php echo $boothUrl; ?>" class="fw-bold text-dark text-decoration-none hover-primary d-block">
                                            <?php echo htmlspecialchars($s['name_hi']); ?>
                                        </a>
                                        <div class="text-muted" style="font-size: 11px;"><?php echo htmlspecialchars($s['name']); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $s['type'] === 'Graduates' ? 'bg-primary' : 'bg-success'; ?> rounded-pill px-2.5 py-1">
                                            <?php echo $s['type'] === 'Graduates' ? 'स्नातक' : 'शिक्षक'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-secondary"><?php echo implode(', ', array_slice($s['districts_hi'] ?? $s['districts'], 0, 3)); ?><?php echo count($s['districts']) > 3 ? ' +' . (count($s['districts']) - 3) . ' अन्य' : ''; ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark d-block"><?php echo htmlspecialchars($s['ro_hi'] ?? $s['ro']); ?></span>
                                        <?php if (!empty($s['ro_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($s['ro_url']); ?>" target="_blank" rel="noopener" class="text-primary fw-semibold extra-small text-decoration-none hover-underline d-inline-flex align-items-center gap-1 mt-0.5" title="प्रमंडलीय आयुक्त आधिकारिक पोर्टल">
                                                <i class="bi bi-building"></i>
                                                <span><?php echo parse_url($s['ro_url'], PHP_URL_HOST); ?></span>
                                                <i class="bi bi-box-arrow-up-right" style="font-size: 9px;"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex flex-wrap justify-content-end align-items-center gap-1.5">
                                            <!-- Dedicated Polling Booths Link -->
                                            <a href="<?php echo $boothUrl; ?>" class="btn <?php echo $s['type'] === 'Graduates' ? 'btn-primary' : 'btn-success'; ?> btn-sm rounded-pill py-1 px-2.5 fw-bold shadow-sm d-inline-flex align-items-center gap-1" style="font-size: 11.5px;" title="<?php echo htmlspecialchars($s['name_hi']); ?> के सभी <?php echo $boothCount; ?> मतदान केंद्र देखें">
                                                <i class="bi bi-geo-alt-fill text-warning"></i>
                                                <span>मतदान केंद्र (<?php echo $boothCount; ?>)</span>
                                                <i class="bi bi-arrow-right"></i>
                                            </a>
                                            <!-- CEO Elector Search Link -->
                                            <a href="https://ceo.bihar.gov.in/tcgcelectorsearch/" target="_blank" rel="noopener" class="btn btn-outline-warning text-dark btn-sm rounded-pill py-1 px-2 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px;" title="CEO बिहार मतदाता सूची में नाम खोजें">
                                                <i class="bi bi-person-search text-primary"></i>
                                                <span>नाम खोजें</span>
                                            </a>
                                            <!-- Official CEO Roll PDF Link -->
                                            <a href="<?php echo $pdfUrl; ?>" target="_blank" rel="noopener" class="btn btn-outline-danger btn-sm rounded-pill py-1 px-2 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px;" title="आधिकारिक प्रमाणित वोटर लिस्ट PDF डाउनलोड करें">
                                                <i class="bi bi-file-earmark-pdf-fill"></i>
                                                <span>रोल PDF</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Voter Registration Forms Grid (Forms 18, 19, 7, 8) -->
                <div class="row g-3 mb-4">
                    <!-- Form 18 (Graduates) -->
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm border-0 rounded-4 p-4 bg-white border-top border-primary border-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                    <i class="bi bi-mortarboard-fill fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">स्नातक मतदाता (प्ररूप 18 / Form 18)</h5>
                                    <small class="text-muted">स्नातक निर्वाचक नामावली पंजीकरण</small>
                                </div>
                            </div>

                            <h6 class="fw-bold text-dark small text-uppercase mb-2">पात्रता मानदंड (Eligibility):</h6>
                            <ul class="text-secondary small ps-3 mb-3" style="line-height: 1.6;">
                                <li>भारत का नागरिक हो और संबंधित निर्वाचन क्षेत्र का सामान्य निवासी हो।</li>
                                <li>अर्हता तिथि (01 नवम्बर 2025) से कम से कम <strong>3 वर्ष पूर्व</strong> भारत के किसी भी मान्यता प्राप्त विश्वविद्यालय से स्नातक उत्तीर्ण हो।</li>
                                <li>अथवा राज्य द्वारा मान्यता प्राप्त समकक्ष शैक्षणिक योग्यता रखता हो।</li>
                            </ul>

                            <h6 class="fw-bold text-dark small text-uppercase mb-2">आवश्यक दस्तावेज (Documents):</h6>
                            <ul class="text-secondary small ps-3 mb-3" style="line-height: 1.6;">
                                <li>डिग्री प्रमाण पत्र / प्रोविजनल डिग्री / अंतिम वर्ष की अंकतालिका (स्व-अभिप्रमाणित)।</li>
                                <li>पहचान प्रमाण हेतु आधार कार्ड / वोटर आईडी (EPIC)।</li>
                                <li>संबंधित निर्वाचन क्षेत्र में सामान्य निवास का प्रमाण (बिजली बिल, पासपोर्ट, निवास प्रमाण पत्र)।</li>
                            </ul>

                            <div class="d-flex flex-column gap-2 mt-auto">
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="https://ceo.bihar.gov.in/tcgconline/" target="_blank" rel="noopener" class="btn btn-warning btn-sm rounded-pill fw-bold text-dark shadow-sm flex-grow-1">
                                        <i class="bi bi-pencil-square me-1 text-danger"></i> ऑनलाइन आवेदन (CEO tcgconline) &rarr;
                                    </a>
                                    <a href="https://ceo.bihar.gov.in/tcgcelectorsearch/" target="_blank" rel="noopener" class="btn btn-outline-dark btn-sm rounded-pill fw-semibold">
                                        <i class="bi bi-person-search me-1"></i> नाम खोजें
                                    </a>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="https://ceoelection.bihar.gov.in/" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill fw-semibold flex-grow-1">
                                        <i class="bi bi-download me-1"></i> प्ररूप 18 PDF
                                    </a>
                                    <a href="https://voters.eci.gov.in/" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill fw-semibold">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> ECI पोर्टल
                                    </a>
                                </div>
                                <a href="https://ceo.bihar.gov.in/GCTCPDFVIEW/GCPDF.ASPX" target="_blank" rel="noopener" class="btn btn-primary btn-sm rounded-pill fw-bold text-white shadow-sm">
                                    <i class="bi bi-file-earmark-pdf-fill me-1 text-warning"></i> स्नातक मतदाता सूची खोजें / डाउनलोड करें (GCPDF) &rarr;
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Form 19 (Teachers) -->
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm border-0 rounded-4 p-4 bg-white border-top border-success border-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                    <i class="bi bi-person-video3 fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">शिक्षक मतदाता (प्ररूप 19 / Form 19)</h5>
                                    <small class="text-muted">शिक्षक निर्वाचक नामावली पंजीकरण</small>
                                </div>
                            </div>

                            <h6 class="fw-bold text-dark small text-uppercase mb-2">पात्रता मानदंड (Eligibility):</h6>
                            <ul class="text-secondary small ps-3 mb-3" style="line-height: 1.6;">
                                <li>भारत का नागरिक हो और संबंधित निर्वाचन क्षेत्र का सामान्य निवासी हो।</li>
                                <li>विगत 6 वर्षों के भीतर कुल मिलाकर कम से कम <strong>3 वर्ष तक शिक्षण कार्य</strong> में संलग्न रहा हो।</li>
                                <li>मुख्य निर्वाचन पदाधिकारी द्वारा विनिर्दिष्ट माध्यमिक या उच्चतर स्तर के मान्यता प्राप्त शैक्षणिक संस्थानों में कार्यरत हो।</li>
                            </ul>

                            <div class="p-2.5 bg-success-subtle rounded-3 border border-success-subtle mb-3 small">
                                <div class="fw-bold text-success-emphasis mb-1">
                                    <i class="bi bi-check2-circle me-1"></i> विनिर्दिष्ट शैक्षणिक संस्थान (Specified Institutions):
                                </div>
                                <div class="text-secondary" style="font-size: 11.5px; line-height: 1.5;">
                                    <strong>CEO बिहार आदेश (ज्ञापांक 4487 एवं 4010)</strong> में सूचीबद्ध माध्यमिक विद्यालय, उच्च माध्यमिक (+2) विद्यालय, अंगीभूत/संबद्ध महाविद्यालय, विश्वविद्यालय एवं तकनीकी संस्थानों के शिक्षक पात्र हैं।
                                </div>
                            </div>

                            <h6 class="fw-bold text-dark small text-uppercase mb-2">आवश्यक दस्तावेज (Documents):</h6>
                            <ul class="text-secondary small ps-3 mb-3" style="line-height: 1.6;">
                                <li>संस्थान प्रधान (प्रधानाचार्य / प्राचार्य / निदेशक) द्वारा निर्गत सेवा प्रमाण पत्र।</li>
                                <li>संस्थान की सरकारी मान्यता / संबद्धता प्रमाण पत्र।</li>
                                <li>वोटर आईडी (EPIC) / आधार कार्ड एवं निवास प्रमाण।</li>
                            </ul>

                            <div class="d-flex flex-column gap-2 mt-auto">
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="https://ceo.bihar.gov.in/tcgconline/" target="_blank" rel="noopener" class="btn btn-warning btn-sm rounded-pill fw-bold text-dark shadow-sm flex-grow-1">
                                        <i class="bi bi-pencil-square me-1 text-danger"></i> ऑनलाइन आवेदन (CEO tcgconline) &rarr;
                                    </a>
                                    <a href="https://ceo.bihar.gov.in/tcgcelectorsearch/" target="_blank" rel="noopener" class="btn btn-outline-dark btn-sm rounded-pill fw-semibold">
                                        <i class="bi bi-person-search me-1"></i> नाम खोजें
                                    </a>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="https://ceoelection.bihar.gov.in/" target="_blank" class="btn btn-outline-success btn-sm rounded-pill fw-semibold flex-grow-1">
                                        <i class="bi bi-download me-1"></i> प्ररूप 19 PDF
                                    </a>
                                    <a href="https://voters.eci.gov.in/" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill fw-semibold">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> ECI पोर्टल
                                    </a>
                                </div>
                                <a href="https://ceo.bihar.gov.in/GCTCPDFVIEW/TCPDF.ASPX" target="_blank" rel="noopener" class="btn btn-success btn-sm rounded-pill fw-bold text-white shadow-sm">
                                    <i class="bi bi-file-earmark-pdf-fill me-1 text-warning"></i> शिक्षक मतदाता सूची खोजें / डाउनलोड करें (TCPDF) &rarr;
                                </a>
                                <div class="d-flex gap-2">
                                    <a href="https://ceoelection.bihar.gov.in/PDF/Year_2025/tcgc2026/4487-List%20of%20Educational%20Institutions%20for%20Preparation%20of%20Electoral%20Roll%20of%20Teacher%20Constituency.pdf" target="_blank" rel="noopener" class="btn btn-warning btn-sm rounded-pill fw-bold text-dark shadow-sm flex-grow-1" style="font-size: 12px;">
                                        <i class="bi bi-file-earmark-pdf-fill me-1 text-danger"></i> आदेश 4487 (अद्यतन संस्थान) &rarr;
                                    </a>
                                    <a href="https://ceoelection.bihar.gov.in/PDF/Year_2025/tcgc2026/4010-List%20of%20Educational%20Institutions%20for%20Preparation%20of%20Electoral%20Roll%20of%20Teacher%20Constituency.pdf" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm rounded-pill fw-semibold" style="font-size: 12px;">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> आदेश 4010
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CEO Bihar Letter 4487 & 4010 Highlight Banner (Hindi) -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border-left: 5px solid #16a34a !important;">
                    <div class="row align-items-center g-3">
                        <div class="col-lg-7">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-success fw-bold px-2.5 py-1 rounded-pill">
                                    <i class="bi bi-patch-check-fill me-1"></i> CEO बिहार आधिकारिक अधिसूचनाएं
                                </span>
                                <span class="badge bg-warning text-dark fw-bold">ज्ञापांक सं. 4487 (अद्यतन)</span>
                                <span class="badge bg-light text-dark border fw-semibold">ज्ञापांक सं. 4010</span>
                            </div>
                            <h5 class="fw-bold text-dark mb-1" style="font-family: 'Noto Sans Devanagari', sans-serif;">
                                शिक्षक निर्वाचन क्षेत्र की मतदाता सूची हेतु विनिर्दिष्ट शिक्षण संस्थानों की आधिकारिक सूची
                            </h5>
                            <p class="small text-secondary mb-0" style="line-height: 1.5;">
                                मुख्य निर्वाचन पदाधिकारी, बिहार द्वारा जारी वैधानिक अधिसूचना के अनुसार मान्यता प्राप्त माध्यमिक, उच्च माध्यमिक (+2), महाविद्यालय, विश्वविद्यालय एवं व्यावसायिक संस्थानों की आधिकारिक सूची जिसके शिक्षक प्ररूप 19 में मतदाता बनने हेतु पात्र हैं।
                            </p>
                        </div>
                        <div class="col-lg-5 text-lg-end d-flex flex-wrap gap-2 justify-content-lg-end">
                            <a href="https://ceoelection.bihar.gov.in/PDF/Year_2025/tcgc2026/4487-List%20of%20Educational%20Institutions%20for%20Preparation%20of%20Electoral%20Roll%20of%20Teacher%20Constituency.pdf" target="_blank" rel="noopener" class="btn btn-success fw-bold px-3 py-2 rounded-3 shadow-sm text-white">
                                <i class="bi bi-file-earmark-pdf-fill me-1 text-warning"></i> आदेश 4487 PDF (अद्यतन) &rarr;
                            </a>
                            <a href="https://ceoelection.bihar.gov.in/PDF/Year_2025/tcgc2026/4010-List%20of%20Educational%20Institutions%20for%20Preparation%20of%20Electoral%20Roll%20of%20Teacher%20Constituency.pdf" target="_blank" rel="noopener" class="btn btn-outline-success fw-bold px-3 py-2 rounded-3">
                                <i class="bi bi-file-earmark-pdf me-1"></i> आदेश 4010 PDF &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 3-Step Guide to Search Name in Voter List -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold text-dark mb-3" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-question-circle-fill text-warning me-2"></i> विधान परिषद मतदाता सूची में अपना नाम कैसे जांचें (3 आसान चरण)
                    </h5>
                    <div class="row g-3 small">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 h-100 border">
                                <div class="badge bg-primary rounded-circle mb-2" style="width: 24px; height: 24px;">1</div>
                                <h6 class="fw-bold text-dark mb-1">चरण 1: मुख्य निर्वाचन पदाधिकारी पोर्टल खोलें</h6>
                                <p class="text-muted mb-0"><a href="https://ceo.bihar.gov.in/GCTCPDFVIEW/GCPDF.ASPX" target="_blank" class="fw-semibold text-primary">स्नातक रोल पोर्टल (GCPDF)</a>, <a href="https://ceo.bihar.gov.in/GCTCPDFVIEW/TCPDF.ASPX" target="_blank" class="fw-semibold text-success">शिक्षक रोल पोर्टल (TCPDF)</a> अथवा <code>ceoelection.bihar.gov.in</code> पर जाएं।</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 h-100 border">
                                <div class="badge bg-primary rounded-circle mb-2" style="width: 24px; height: 24px;">2</div>
                                <h6 class="fw-bold text-dark mb-1">चरण 2: निर्वाचन क्षेत्र एवं जिले का चयन करें</h6>
                                <p class="text-muted mb-0">अपने संबंधित विधान परिषद निर्वाचन क्षेत्र (जैसे: पटना स्नातक अथवा सारण शिक्षक) और गृह जिले/प्रखंड का चयन करें।</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 h-100 border">
                                <div class="badge bg-primary rounded-circle mb-2" style="width: 24px; height: 24px;">3</div>
                                <h6 class="fw-bold text-dark mb-1">चरण 3: बूथवार PDF डाउनलोड या EPIC से खोजें</h6>
                                <p class="text-muted mb-0">अपने वोटर आईडी (EPIC) नंबर से नाम खोजें या अपने मतदान केंद्र की बूथवार मतदाता सूची PDF डाउनलोड कर अपना क्रमांक और मतदान केंद्र जांचें।</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ============================================================= -->
            <!-- 7. VOTING METHOD: PREFERENTIAL PROPORTIONAL REPRESENTATION (STV) -->
            <!-- ============================================================= -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-5">
                <h4 class="fw-bold text-dark mb-2" style="font-family: 'Outfit', sans-serif;">
                    <i class="bi bi-ui-checks-grid text-warning me-2"></i> How Voting &amp; Counting Works (PR-STV)
                </h4>
                <p class="text-secondary small mb-3">
                    Elections to the Bihar Legislative Council from Graduates' and Teachers' constituencies are held under the system of <strong>Proportional Representation by means of the Single Transferable Vote (PR-STV)</strong> via paper ballots.
                </p>

                <div class="row g-3 small">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 h-100 border">
                            <h6 class="fw-bold text-dark mb-1"><i class="bi bi-1-circle-fill text-primary me-1"></i> Preference Marking</h6>
                            <p class="text-muted mb-0">Voters mark their preferences (1, 2, 3...) next to candidate names in order of choice using Indian numerals (1, 2, 3) or Roman/Devanagari numerals.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 h-100 border">
                            <h6 class="fw-bold text-dark mb-1"><i class="bi bi-pen-fill text-danger me-1"></i> Violet Sketch Pen</h6>
                            <p class="text-muted mb-0">Preferences MUST be recorded only with the official violet sketch pen supplied by the polling officer. Any other pen invalidates the ballot.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 h-100 border">
                            <h6 class="fw-bold text-dark mb-1"><i class="bi bi-calculator-fill text-success me-1"></i> Winning Quota</h6>
                            <p class="text-muted mb-0">A candidate must secure the quota: <code>(Total Valid Votes / (Seats + 1)) + 1</code>. If no candidate reaches quota on 1st preferences, lowest candidates are eliminated and votes transferred.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Sidebar (4 Cols) -->
        <div class="col-lg-4">
            
            <!-- Candidate Nomination Desk Widget -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4 border-start border-warning border-4">
                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                    <h5 class="fw-bold text-dark mb-0" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-person-lines-fill text-warning me-1"></i> Nomination Desk
                    </h5>
                    <span class="badge bg-danger">Open Now</span>
                </div>
                <div class="small text-secondary mb-3">
                    Statutory filings for the 8 Council seats (29 Sep – 06 Oct 2026):
                </div>
                <div class="p-2.5 bg-light rounded-3 border mb-3 small">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Last Date:</span>
                        <strong class="text-dark">06 Oct 2026 (3:00 PM)</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Nomination Paper:</span>
                        <strong class="text-primary">Form 2E</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Affidavit:</span>
                        <strong class="text-dark">Form 26</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Security Deposit:</span>
                        <strong class="text-success">₹10,000 (Gen) / ₹5,000 (SC/ST)</strong>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <a href="https://affidavit.eci.gov.in/" target="_blank" rel="noopener" class="btn btn-warning fw-bold btn-sm py-2 rounded-3 text-dark">
                        <i class="bi bi-search me-1"></i> ECI Candidate Affidavits Portal &rarr;
                    </a>
                    <a href="https://suvidha.eci.gov.in/" target="_blank" rel="noopener" class="btn btn-outline-dark fw-semibold btn-sm py-2 rounded-3">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Suvidha Candidate Portal
                    </a>
                </div>
            </div>

            <!-- Gazette & Statutory Documents Download Widget -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom" style="font-family: 'Noto Sans Devanagari', sans-serif;">
                    <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i> मुख्य निर्वाचन पदाधिकारी आधिकारिक दस्तावेज
                </h5>
                
                <!-- Document 1: Gazette -->
                <div class="p-2.5 bg-light rounded-3 border mb-2.5 small">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="text-dark">निर्वाचन गजट अधिसूचना</strong>
                        <span class="badge bg-danger">TCGCGajat2026</span>
                    </div>
                    <div class="text-muted mb-2" style="font-size: 11.5px;">पत्रांक: M2–03(TC&amp;GC)/2026-5369</div>
                    <a href="https://ceoelection.bihar.gov.in/PDF/Year_2026/ImportantInstructionsAndLetters/TCGCGajat2026.pdf" target="_blank" rel="noopener" class="btn btn-outline-danger btn-sm w-100 fw-bold rounded-pill" style="font-size: 11.5px;">
                        <i class="bi bi-download me-1"></i> गजट PDF डाउनलोड करें
                    </a>
                </div>

                <!-- Document 2: Letter 4487 (Latest Updated List) -->
                <div class="p-2.5 bg-light rounded-3 border mb-2.5 small">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="text-dark">शिक्षक संस्थान सूची (अद्यतन)</strong>
                        <span class="badge bg-success">ज्ञापांक 4487</span>
                    </div>
                    <div class="text-muted mb-2" style="font-size: 11.5px;">प्ररूप 19 हेतु मान्य संस्थानों की नवीनतम सूची</div>
                    <a href="https://ceoelection.bihar.gov.in/PDF/Year_2025/tcgc2026/4487-List%20of%20Educational%20Institutions%20for%20Preparation%20of%20Electoral%20Roll%20of%20Teacher%20Constituency.pdf" target="_blank" rel="noopener" class="btn btn-success btn-sm w-100 fw-bold rounded-pill text-white" style="font-size: 11.5px;">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> आदेश 4487 PDF डाउनलोड
                    </a>
                </div>

                <!-- Document 3: Letter 4010 List of Educational Institutions -->
                <div class="p-2.5 bg-light rounded-3 border small">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="text-dark">शिक्षक संस्थान सूची (मूल)</strong>
                        <span class="badge bg-secondary">ज्ञापांक 4010</span>
                    </div>
                    <div class="text-muted mb-2" style="font-size: 11.5px;">शिक्षक मतदाता सूची हेतु विनिर्दिष्ट संस्थान</div>
                    <a href="https://ceoelection.bihar.gov.in/PDF/Year_2025/tcgc2026/4010-List%20of%20Educational%20Institutions%20for%20Preparation%20of%20Electoral%20Roll%20of%20Teacher%20Constituency.pdf" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm w-100 fw-bold rounded-pill" style="font-size: 11.5px;">
                        <i class="bi bi-file-earmark-pdf me-1"></i> आदेश 4010 PDF डाउनलोड
                    </a>
                </div>
            </div>

            <!-- Returning Officers Directory -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom" style="font-family: 'Outfit', sans-serif;">
                    <i class="bi bi-person-badge-fill text-primary me-2"></i> Returning Officers (ROs)
                </h5>
                <div class="d-flex flex-column gap-3 small">
                    <div class="border-bottom pb-2">
                        <strong class="text-dark">Patna Division Commissioner</strong>
                        <div class="text-muted">Patna Graduates &amp; Patna Teachers</div>
                        <span class="badge bg-light text-dark border mt-1">Patna, Nalanda, Nawada</span>
                    </div>
                    <div class="border-bottom pb-2">
                        <strong class="text-dark">Tirhut Division Commissioner</strong>
                        <div class="text-muted">Tirhut Graduates &amp; Tirhut Teachers</div>
                        <span class="badge bg-light text-dark border mt-1">Muzaffarpur, Vaishali, Sitamarhi, Sheohar</span>
                    </div>
                    <div class="border-bottom pb-2">
                        <strong class="text-dark">Darbhanga Division Commissioner</strong>
                        <div class="text-muted">Darbhanga Graduates &amp; Darbhanga Teachers</div>
                        <span class="badge bg-light text-dark border mt-1">Darbhanga, Madhubani, Samastipur, Begusarai</span>
                    </div>
                    <div class="border-bottom pb-2">
                        <strong class="text-dark">Kosi / Purnia Division Commissioner</strong>
                        <div class="text-muted">Kosi Graduates</div>
                        <span class="badge bg-light text-dark border mt-1">14 Eastern Bihar Districts</span>
                    </div>
                    <div>
                        <strong class="text-dark">Saran Division Commissioner</strong>
                        <div class="text-muted">Saran Teachers</div>
                        <span class="badge bg-light text-dark border mt-1">Saran, Siwan, Gopalganj, Champaran</span>
                    </div>
                </div>
            </div>

            <!-- All 75 MLCs Directory Link -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold text-dark mb-2" style="font-family: 'Outfit', sans-serif;">
                    <i class="bi bi-people-fill text-warning me-2"></i> Bihar Legislative Council
                </h5>
                <p class="small text-secondary mb-3">
                    Explore all 75 current sitting MLCs across Assembly Quota, Local Authorities, Governor Nominated, Graduates, and Teachers quotas.
                </p>
                <a href="<?php echo SITE_URL; ?>/mlc" class="btn btn-outline-dark btn-sm fw-bold w-100 rounded-3">
                    <i class="bi bi-card-list me-1"></i> Browse 75 MLCs Roster &rarr;
                </a>
            </div>

            <!-- WhatsApp Alert Channel -->
            <div class="card border-0 shadow-sm rounded-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #075e54, #128c7e);">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-whatsapp display-4 mb-2 d-block text-warning"></i>
                    <h5 class="fw-bold mb-2">Vidhan Parishad WhatsApp Alerts</h5>
                    <p class="small opacity-90 mb-3">Get live candidate nominations, polling booth locators, and election results on your phone.</p>
                    <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-warning fw-bold px-4 py-2 rounded-pill text-dark shadow-sm">
                        Join WhatsApp Alerts &rarr;
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
// Countdown Timer to Counting Day: 27 October 2026, 08:00 AM IST
function updateCountdown() {
    const countingDate = new Date('2026-10-27T08:00:00+05:30').getTime();
    const now = new Date().getTime();
    const distance = countingDate - now;

    if (distance < 0) {
        document.getElementById('cdDays').innerText = '00';
        document.getElementById('cdHours').innerText = '00';
        document.getElementById('cdMinutes').innerText = '00';
        document.getElementById('cdSeconds').innerText = '00';
        return;
    }

    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    document.getElementById('cdDays').innerText = days < 10 ? '0' + days : days;
    document.getElementById('cdHours').innerText = hours < 10 ? '0' + hours : hours;
    document.getElementById('cdMinutes').innerText = minutes < 10 ? '0' + minutes : minutes;
    document.getElementById('cdSeconds').innerText = seconds < 10 ? '0' + seconds : seconds;
}
setInterval(updateCountdown, 1000);
updateCountdown();

// Filter Constituency Cards by Quota Type
function filterSeats(type, btn) {
    document.querySelectorAll('#seatsGrid .seat-card-item').forEach(el => {
        if (type === 'all' || el.dataset.type === type) {
            el.style.display = 'block';
        } else {
            el.style.display = 'none';
        }
    });

    if (btn) {
        btn.parentElement.querySelectorAll('.btn').forEach(b => b.classList.remove('active', 'btn-dark', 'btn-primary', 'btn-success'));
        btn.classList.add('active');
        if (type === 'all') btn.classList.add('btn-dark');
        else if (type === 'Graduates') btn.classList.add('btn-primary');
        else if (type === 'Teachers') btn.classList.add('btn-success');
    }
}

// District to Constituency Mapping Data (Accurate breakdown for 8 of 12 seats)
const districtMapping = {
    "Patna": { grad: "Patna Graduates (पटना स्नातक)", teach: "Patna Teachers (पटना शिक्षक)", ro: "Divisional Commissioner, Patna", active: true, statusText: "🟢 Voting in 2026: Both Graduates & Teachers Seats" },
    "Nalanda": { grad: "Patna Graduates (पटना स्नातक)", teach: "Patna Teachers (पटना शिक्षक)", ro: "Divisional Commissioner, Patna", active: true, statusText: "🟢 Voting in 2026: Both Graduates & Teachers Seats" },
    "Nawada": { grad: "Patna Graduates (पटना स्नातक)", teach: "Patna Teachers (पटना शिक्षक)", ro: "Divisional Commissioner, Patna", active: true, statusText: "🟢 Voting in 2026: Both Graduates & Teachers Seats" },
    
    "Muzaffarpur": { grad: "Tirhut Graduates (तिरहुत स्नातक)", teach: "Tirhut Teachers (तिरहुत शिक्षक)", ro: "Divisional Commissioner, Tirhut (Muzaffarpur)", active: true, statusText: "🟢 Voting in 2026: Both Graduates & Teachers Seats" },
    "Vaishali": { grad: "Tirhut Graduates (तिरहुत स्नातक)", teach: "Tirhut Teachers (तिरहुत शिक्षक)", ro: "Divisional Commissioner, Tirhut (Muzaffarpur)", active: true, statusText: "🟢 Voting in 2026: Both Graduates & Teachers Seats" },
    "Sitamarhi": { grad: "Tirhut Graduates (तिरहुत स्नातक)", teach: "Tirhut Teachers (तिरहुत शिक्षक)", ro: "Divisional Commissioner, Tirhut (Muzaffarpur)", active: true, statusText: "🟢 Voting in 2026: Both Graduates & Teachers Seats" },
    "Sheohar": { grad: "Tirhut Graduates (तिरहुत स्नातक)", teach: "Tirhut Teachers (तिरहुत शिक्षक)", ro: "Divisional Commissioner, Tirhut (Muzaffarpur)", active: true, statusText: "🟢 Voting in 2026: Both Graduates & Teachers Seats" },
    
    "Darbhanga": { grad: "Darbhanga Graduates (दरभंगा स्नातक)", teach: "Darbhanga Teachers (दरभंगा शिक्षक)", ro: "Divisional Commissioner, Darbhanga", active: true, statusText: "🟢 Voting in 2026: Both Graduates & Teachers Seats" },
    "Madhubani": { grad: "Darbhanga Graduates (दरभंगा स्नातक)", teach: "Darbhanga Teachers (दरभंगा शिक्षक)", ro: "Divisional Commissioner, Darbhanga", active: true, statusText: "🟢 Voting in 2026: Both Graduates & Teachers Seats" },
    "Samastipur": { grad: "Darbhanga Graduates (दरभंगा स्नातक)", teach: "Darbhanga Teachers (दरभंगा शिक्षक)", ro: "Divisional Commissioner, Darbhanga", active: true, statusText: "🟢 Voting in 2026: Both Graduates & Teachers Seats" },
    "Begusarai": { grad: "Darbhanga Graduates (दरभंगा स्नातक)", teach: "Darbhanga Teachers (दरभंगा शिक्षक)", ro: "Divisional Commissioner, Darbhanga", active: true, statusText: "🟢 Voting in 2026: Both Graduates & Teachers Seats" },
    
    "Saharsa": { grad: "Kosi Graduates (कोसी स्नातक) [VOTING 2026]", teach: "Kosi Teachers (Separate Cycle)", ro: "Divisional Commissioner, Kosi / Purnia", active: true, statusText: "🟢 Voting in 2026: Kosi Graduates Seat (Teachers in separate cycle)" },
    "Supaul": { grad: "Kosi Graduates (कोसी स्नातक) [VOTING 2026]", teach: "Kosi Teachers (Separate Cycle)", ro: "Divisional Commissioner, Kosi / Purnia", active: true, statusText: "🟢 Voting in 2026: Kosi Graduates Seat (Teachers in separate cycle)" },
    "Madhepura": { grad: "Kosi Graduates (कोसी स्नातक) [VOTING 2026]", teach: "Kosi Teachers (Separate Cycle)", ro: "Divisional Commissioner, Kosi / Purnia", active: true, statusText: "🟢 Voting in 2026: Kosi Graduates Seat (Teachers in separate cycle)" },
    "Purnia": { grad: "Kosi Graduates (कोसी स्नातक) [VOTING 2026]", teach: "Kosi Teachers (Separate Cycle)", ro: "Divisional Commissioner, Purnia", active: true, statusText: "🟢 Voting in 2026: Kosi Graduates Seat (Teachers in separate cycle)" },
    "Araria": { grad: "Kosi Graduates (कोसी स्नातक) [VOTING 2026]", teach: "Kosi Teachers (Separate Cycle)", ro: "Divisional Commissioner, Purnia", active: true, statusText: "🟢 Voting in 2026: Kosi Graduates Seat (Teachers in separate cycle)" },
    "Kishanganj": { grad: "Kosi Graduates (कोसी स्नातक) [VOTING 2026]", teach: "Kosi Teachers (Separate Cycle)", ro: "Divisional Commissioner, Purnia", active: true, statusText: "🟢 Voting in 2026: Kosi Graduates Seat (Teachers in separate cycle)" },
    "Katihar": { grad: "Kosi Graduates (कोसी स्नातक) [VOTING 2026]", teach: "Kosi Teachers (Separate Cycle)", ro: "Divisional Commissioner, Purnia", active: true, statusText: "🟢 Voting in 2026: Kosi Graduates Seat (Teachers in separate cycle)" },
    "Bhagalpur": { grad: "Kosi Graduates (कोसी स्नातक) [VOTING 2026]", teach: "Kosi Teachers (Separate Cycle)", ro: "Divisional Commissioner, Bhagalpur", active: true, statusText: "🟢 Voting in 2026: Kosi Graduates Seat (Teachers in separate cycle)" },
    "Banka": { grad: "Kosi Graduates (कोसी स्नातक) [VOTING 2026]", teach: "Kosi Teachers (Separate Cycle)", ro: "Divisional Commissioner, Bhagalpur", active: true, statusText: "🟢 Voting in 2026: Kosi Graduates Seat (Teachers in separate cycle)" },
    "Munger": { grad: "Kosi Graduates (कोसी स्नातक) [VOTING 2026]", teach: "Kosi Teachers (Separate Cycle)", ro: "Divisional Commissioner, Munger", active: true, statusText: "🟢 Voting in 2026: Kosi Graduates Seat (Teachers in separate cycle)" },
    "Jamui": { grad: "Kosi Graduates (कोसी स्नातक) [VOTING 2026]", teach: "Kosi Teachers (Separate Cycle)", ro: "Divisional Commissioner, Munger", active: true, statusText: "🟢 Voting in 2026: Kosi Graduates Seat (Teachers in separate cycle)" },
    "Lakhisarai": { grad: "Kosi Graduates (कोसी स्नातक) [VOTING 2026]", teach: "Kosi Teachers (Separate Cycle)", ro: "Divisional Commissioner, Munger", active: true, statusText: "🟢 Voting in 2026: Kosi Graduates Seat (Teachers in separate cycle)" },
    "Sheikhpura": { grad: "Kosi Graduates (कोसी स्नातक) [VOTING 2026]", teach: "Kosi Teachers (Separate Cycle)", ro: "Divisional Commissioner, Munger", active: true, statusText: "🟢 Voting in 2026: Kosi Graduates Seat (Teachers in separate cycle)" },
    "Khagaria": { grad: "Kosi Graduates (कोसी स्नातक) [VOTING 2026]", teach: "Kosi Teachers (Separate Cycle)", ro: "Divisional Commissioner, Munger", active: true, statusText: "🟢 Voting in 2026: Kosi Graduates Seat (Teachers in separate cycle)" },
    
    "Saran": { grad: "Saran Graduates (Separate Cycle)", teach: "Saran Teachers (सारण शिक्षक) [VOTING 2026]", ro: "Divisional Commissioner, Saran (Chapra)", active: true, statusText: "🟢 Voting in 2026: Saran Teachers Seat (Graduates in separate cycle)" },
    "Siwan": { grad: "Saran Graduates (Separate Cycle)", teach: "Saran Teachers (सारण शिक्षक) [VOTING 2026]", ro: "Divisional Commissioner, Saran (Chapra)", active: true, statusText: "🟢 Voting in 2026: Saran Teachers Seat (Graduates in separate cycle)" },
    "Gopalganj": { grad: "Saran Graduates (Separate Cycle)", teach: "Saran Teachers (सारण शिक्षक) [VOTING 2026]", ro: "Divisional Commissioner, Saran (Chapra)", active: true, statusText: "🟢 Voting in 2026: Saran Teachers Seat (Graduates in separate cycle)" },
    "East Champaran": { grad: "Tirhut/Saran Belt (Separate Cycle)", teach: "Saran Teachers (सारण शिक्षक) [VOTING 2026]", ro: "Divisional Commissioner, Saran (Chapra)", active: true, statusText: "🟢 Voting in 2026: Saran Teachers Seat" },
    "West Champaran": { grad: "Tirhut/Saran Belt (Separate Cycle)", teach: "Saran Teachers (सारण शिक्षक) [VOTING 2026]", ro: "Divisional Commissioner, Saran (Chapra)", active: true, statusText: "🟢 Voting in 2026: Saran Teachers Seat" },
    
    "Gaya": { grad: "Gaya Graduates (गया स्नातक)", teach: "Gaya Teachers (गया शिक्षक)", ro: "Divisional Commissioner, Magadh", active: false, statusText: "⚪ Not Polling in Oct 2026 (Gaya Quotas term expires in a separate cycle)" },
    "Jehanabad": { grad: "Gaya Graduates", teach: "Gaya Teachers", ro: "Divisional Commissioner, Magadh", active: false, statusText: "⚪ Not Polling in Oct 2026 (Part of Gaya Quota)" },
    "Arwal": { grad: "Gaya Graduates", teach: "Gaya Teachers", ro: "Divisional Commissioner, Magadh", active: false, statusText: "⚪ Not Polling in Oct 2026 (Part of Gaya Quota)" },
    "Aurangabad": { grad: "Gaya Graduates", teach: "Gaya Teachers", ro: "Divisional Commissioner, Magadh", active: false, statusText: "⚪ Not Polling in Oct 2026 (Part of Gaya Quota)" },
    "Rohtas": { grad: "Gaya/South Bihar Quota", teach: "Gaya Teachers Quota", ro: "Divisional Commissioner, Magadh", active: false, statusText: "⚪ Not Polling in Oct 2026 (Different biennial cycle)" },
    "Kaimur": { grad: "Gaya/South Bihar Quota", teach: "Gaya Teachers Quota", ro: "Divisional Commissioner, Magadh", active: false, statusText: "⚪ Not Polling in Oct 2026 (Different biennial cycle)" },
    "Bhojpur": { grad: "Gaya/Shahabad Quota", teach: "Gaya Teachers Quota", ro: "Divisional Commissioner, Patna/Magadh", active: false, statusText: "⚪ Not Polling in Oct 2026 (Different biennial cycle)" },
    "Buxar": { grad: "Gaya/Shahabad Quota", teach: "Gaya Teachers Quota", ro: "Divisional Commissioner, Patna/Magadh", active: false, statusText: "⚪ Not Polling in Oct 2026 (Different biennial cycle)" }
};

function lookupDistrictConstituency(district) {
    const box = document.getElementById('lookupResultBox');
    if (!district || !districtMapping[district]) {
        box.classList.add('d-none');
        return;
    }

    const info = districtMapping[district];
    document.getElementById('resDistrictName').innerText = district + ' District (जिला)';
    document.getElementById('resGradSeat').innerText = info.grad;
    document.getElementById('resTeachSeat').innerText = info.teach;
    document.getElementById('resRO').innerText = info.ro;
    
    // Update active status badge
    const badgeEl = box.querySelector('.badge');
    if (badgeEl) {
        if (info.active) {
            badgeEl.className = 'badge bg-success';
            badgeEl.innerText = info.statusText;
        } else {
            badgeEl.className = 'badge bg-secondary';
            badgeEl.innerText = info.statusText;
        }
    }

    box.classList.remove('d-none');
}

function filterResultsTable(cat, btn) {
    if (btn) {
        btn.parentElement.querySelectorAll('button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }
    const rows = document.querySelectorAll('#resultsReportTable .results-row');
    rows.forEach(r => {
        if (cat === 'all' || r.getAttribute('data-cat') === cat) {
            r.style.display = '';
        } else {
            r.style.display = 'none';
        }
    });
}

function filterTurnoutTable(cat, btn) {
    if (btn) {
        btn.parentElement.querySelectorAll('button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }
    const rows = document.querySelectorAll('#turnoutReportTable .turnout-row');
    rows.forEach(r => {
        if (cat === 'all' || r.getAttribute('data-cat') === cat) {
            r.style.display = '';
        } else {
            r.style.display = 'none';
        }
    });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
