<?php
/**
 * BiharElection.com - Bihar Legislative Council (Vidhan Parishad) Biennial Election 2026
 * Dedicated hub for the ongoing 4 Graduates' & 4 Teachers' Constituencies Biennial Elections.
 * Reference: CEO Bihar Notification No. M2–03(TC&GC)/2026-5369 / ECI TCGCGajat2026.pdf
 */
require_once __DIR__ . '/config.php';

$pageTitle = 'Bihar Vidhan Parishad Election 2026: 8 Graduates & Teachers Seats Gazette, Schedule & Voter Guide';
$pageDescription = 'Official schedule, gazette notification (TCGCGajat2026.pdf), district-wise constituency mapping, Form 18/19 eligibility and polling data for the Bihar Legislative Council Biennial Election 2026.';
$pageKeywords = 'Bihar Vidhan Parishad Election 2026, Bihar Legislative Council Election, Graduates Constituency Bihar, Teachers Constituency Bihar, Patna Graduate MLC, Tirhut Graduate MLC, Darbhanga Graduate, Kosi Graduate, Saran Teacher, Form 18 Bihar, Form 19 Bihar, CEO Bihar TCGCGajat2026';
$pageCanonical = SITE_URL . '/vidhan-parishad-election';
$activeNav = 'mlc';

include 'header.php';

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

// 8 Constituencies Live Voting Turnout & Incumbent MLCs Report (October 2026)
$turnoutReport = [
    [
        'category' => 'Graduates',
        'category_hi' => 'स्नातक',
        'name' => 'Patna Graduates',
        'name_hi' => 'पटना स्नातक निर्वाचन क्षेत्र',
        'slug' => 'patna-graduates',
        'turnout' => 36.19,
        'turnout_rank' => 'Lowest in Graduates / सबसे कम',
        'rank_badge' => 'bg-danger text-white',
        'sitting_mlc' => 'Neeraj Kumar',
        'sitting_mlc_hi' => 'नीरज कुमार',
        'party' => 'JD(U)',
        'tenure' => '16 Nov 2026',
        'booth_count' => 134
    ],
    [
        'category' => 'Graduates',
        'category_hi' => 'स्नातक',
        'name' => 'Tirhut Graduates',
        'name_hi' => 'तिरहुत स्नातक निर्वाचन क्षेत्र',
        'slug' => 'tirhut-graduates',
        'turnout' => 42.15,
        'turnout_rank' => 'Moderate / सामान्य',
        'rank_badge' => 'bg-secondary text-white',
        'sitting_mlc' => 'Banshidhar Brajwasi',
        'sitting_mlc_hi' => 'वंशीधर ब्रजवासी',
        'party' => 'IND',
        'tenure' => '16 Nov 2026',
        'booth_count' => 145
    ],
    [
        'category' => 'Graduates',
        'category_hi' => 'स्नातक',
        'name' => 'Darbhanga Graduates',
        'name_hi' => 'दरभंगा स्नातक निर्वाचन क्षेत्र',
        'slug' => 'darbhanga-graduates',
        'turnout' => 42.00,
        'turnout_rank' => 'Moderate / सामान्य',
        'rank_badge' => 'bg-secondary text-white',
        'sitting_mlc' => 'Sarvesh Kumar',
        'sitting_mlc_hi' => 'सर्वेश कुमार',
        'party' => 'BJP',
        'tenure' => '16 Nov 2026',
        'booth_count' => 138
    ],
    [
        'category' => 'Graduates',
        'category_hi' => 'स्नातक',
        'name' => 'Kosi Graduates',
        'name_hi' => 'कोसी स्नातक निर्वाचन क्षेत्र',
        'slug' => 'kosi-graduates',
        'turnout' => 52.51,
        'turnout_rank' => 'Highest in Graduates / स्नातकों में सर्वाधिक',
        'rank_badge' => 'bg-success text-white',
        'sitting_mlc' => 'Dr. N.K. Yadav',
        'sitting_mlc_hi' => 'डॉ. एनके यादव',
        'party' => 'BJP',
        'tenure' => '16 Nov 2026',
        'booth_count' => 181
    ],
    [
        'category' => 'Teachers',
        'category_hi' => 'शिक्षक',
        'name' => 'Patna Teachers',
        'name_hi' => 'पटना शिक्षक निर्वाचन क्षेत्र',
        'slug' => 'patna-teachers',
        'turnout' => 56.89,
        'turnout_rank' => 'Lowest in Teachers / शिक्षकों में कम',
        'rank_badge' => 'bg-warning text-dark',
        'sitting_mlc' => 'Nawal Kishore Yadav',
        'sitting_mlc_hi' => 'नवल किशोर यादव',
        'party' => 'BJP',
        'tenure' => '16 Nov 2026',
        'booth_count' => 80
    ],
    [
        'category' => 'Teachers',
        'category_hi' => 'शिक्षक',
        'name' => 'Tirhut Teachers',
        'name_hi' => 'तिरहुत शिक्षक निर्वाचन क्षेत्र',
        'slug' => 'tirhut-teachers',
        'turnout' => 78.10,
        'turnout_rank' => 'High Voting / भारी मतदान',
        'rank_badge' => 'bg-primary text-white',
        'sitting_mlc' => 'Sanjay Kumar Singh',
        'sitting_mlc_hi' => 'संजय कुमार सिंह',
        'party' => 'CPI',
        'tenure' => '16 Nov 2026',
        'booth_count' => 58
    ],
    [
        'category' => 'Teachers',
        'category_hi' => 'शिक्षक',
        'name' => 'Darbhanga Teachers',
        'name_hi' => 'दरभंगा शिक्षक निर्वाचन क्षेत्र',
        'slug' => 'darbhanga-teachers',
        'turnout' => 79.00,
        'turnout_rank' => 'High Voting / भारी मतदान',
        'rank_badge' => 'bg-primary text-white',
        'sitting_mlc' => 'Dr. Madan Mohan Jha',
        'sitting_mlc_hi' => 'डॉ. मदन मोहन झा',
        'party' => 'INC',
        'tenure' => '16 Nov 2026',
        'booth_count' => 99
    ],
    [
        'category' => 'Teachers',
        'category_hi' => 'शिक्षक',
        'name' => 'Saran Teachers',
        'name_hi' => 'सारण शिक्षक निर्वाचन क्षेत्र',
        'slug' => 'saran-teachers',
        'turnout' => 87.16,
        'turnout_rank' => 'Highest Across Bihar / राज्य में रिकॉर्ड',
        'rank_badge' => 'bg-success text-white',
        'sitting_mlc' => 'Afaq Ahmad',
        'sitting_mlc_hi' => 'अफाक अहमद',
        'party' => 'Jan Suraaj Party',
        'tenure' => '16 Nov 2026',
        'booth_count' => 98
    ]
];

// Verified Media Outlets & Official Sources Reporting on Election Results 2026
$newsSources = [
    [
        'id' => 'jagran',
        'name' => 'Dainik Jagran',
        'name_hi' => 'दैनिक जागरण',
        'badge' => 'bg-warning text-dark',
        'icon' => 'bi-newspaper',
        'url' => 'https://www.jagran.com/bihar',
        'coverage' => 'Ground counting hall reports, CCTV transparency dispute at Patna Arts College centre, security cordon at MIT Muzaffarpur.',
        'coverage_hi' => 'मतगणना केंद्र से ग्राउंड रिपोर्ट, पटना केंद्र पर सीसीटीवी मॉनिटर व्यवस्था पर आपत्ति, मुजफ्फरपुर व दरभंगा में सुरक्षा बंदोबस्त।'
    ],
    [
        'id' => 'hindustan',
        'name' => 'Live Hindustan',
        'name_hi' => 'हिन्दुस्तान',
        'badge' => 'bg-danger text-white',
        'icon' => 'bi-broadcast',
        'url' => 'https://www.livehindustan.com/bihar/bihar-mlc-election-2026-voting-live-bjp-rjd-jdu-chunav-latest-updates-live-blog-201792729617020.html',
        'coverage' => 'Continuous live blog, preferential round tallies, candidate reactions, Mokama political stakes and turnout benchmarks.',
        'coverage_hi' => 'निरंतर लाइव ब्लॉग, वरीयता मतों के चक्र, उम्मीदवार प्रतिक्रियाएं, मोकामा राजनीतिक प्रतिष्ठा एवं मतदान प्रतिशत।'
    ],
    [
        'id' => 'prabhat',
        'name' => 'Prabhat Khabar',
        'name_hi' => 'प्रभात खबर',
        'badge' => 'bg-info text-dark',
        'icon' => 'bi-journal-check',
        'url' => 'https://www.prabhatkhabar.com/state/bihar',
        'coverage' => 'Divisional counting updates across Tirhut, Darbhanga, Kosi and Saran; postal ballot segregation and PR-STV quota analysis.',
        'coverage_hi' => 'तिरहुत, दरभंगा, कोसी एवं सारण प्रमंडलीय मतगणना केंद्र, डाक मतपत्र छंटनी व एकल संक्रमणीय मत कोटा विश्लेषण।'
    ],
    [
        'id' => 'ceo_bihar',
        'name' => 'CEO Bihar / ECI',
        'name_hi' => 'मुख्य निर्वाचन पदाधिकारी, बिहार',
        'badge' => 'bg-success text-white',
        'icon' => 'bi-patch-check-fill',
        'url' => 'https://ceoelection.bihar.gov.in/',
        'coverage' => 'Statutory Gazette Notification No. M2–03(TC&GC)/2026-5369, Returning Officer oversight, Form 21C / 21D formal certifications.',
        'coverage_hi' => 'आधिकारिक गजट अधिसूचना, प्रमंडलीय आयुक्त पर्यवेक्षण एवं प्ररूप 21C / 21D वैधानिक परिणाम प्रमाणीकरण।'
    ]
];

// 8 Seats Live Counting Status, Candidate Positions & Multi-Source Intelligence (Updated 27 Oct 2026)
$electionResults = [
    [
        'id' => 'tirhut-teachers',
        'type' => 'Teachers',
        'type_hi' => 'शिक्षक',
        'name' => 'Tirhut Teachers',
        'name_hi' => 'तिरहुत शिक्षक निर्वाचन क्षेत्र',
        'slug' => 'tirhut-teachers',
        'status' => 'DECLARED WON (विजेता घोषित)',
        'status_hi' => 'विजयी घोषित (Elected MLC)',
        'status_badge' => 'bg-success text-white',
        'is_declared' => true,
        'turnout' => 78.10,
        'booths' => 58,
        'counting_centre' => 'MIT Campus, Muzaffarpur',
        'counting_centre_hi' => 'एमआईटी परिसर, मुजफ्फरपुर',
        'returning_officer' => 'Divisional Commissioner, Tirhut',
        'returning_officer_hi' => 'प्रमंडलीय आयुक्त, तिरहुत',
        'leading' => [
            'name' => 'Dr. Narendra Prasad Singh',
            'name_hi' => 'डॉ. नरेंद्र प्रसाद सिंह',
            'party' => 'BJP',
            'role' => 'Elected MLC (Won by 594 Votes) / NDA Candidate',
            'role_hi' => 'विजयी घोषित (594 मतों से जीते) / एनडीए प्रत्याशी'
        ],
        'trailing' => [
            'name' => 'Bhushan Jha',
            'name_hi' => 'भूषण झा',
            'party' => 'IND',
            'role' => 'Runner-up (Independent - 2,351 Votes)',
            'role_hi' => 'मुख्य प्रतिद्वंद्वी (निर्दलीय - 2,351 मत)'
        ],
        'other_key' => [
            'name' => 'Sanjay Kumar Singh',
            'name_hi' => 'संजय कुमार सिंह',
            'party' => 'CPI',
            'role' => 'Sitting MLC (3rd Position - Eliminated in Earlier Rounds)',
            'role_hi' => 'निवर्तमान एमएलसी (तीसरे स्थान पर रहे)'
        ],
        'trend_analysis' => 'OFFICIAL RESULT DECLARED: Dr. Narendra Prasad Singh (BJP/NDA) elected MLC with 2,945 votes, defeating Independent Bhushan Jha (2,351 votes) by 594 votes.',
        'trend_analysis_hi' => 'आधिकारिक परिणाम घोषित: भाजपा के डॉ. नरेंद्र प्रसाद सिंह 594 मतों से विजयी घोषित (2,945 मत पाकर निर्दलीय भूषण झा को हराया)।',
        'sources_cited' => [
            'Live Hindustan' => 'Official certification: BJP candidate Dr. Narendra Prasad Singh won Tirhut Teachers seat by 594 votes margin.',
            'Dainik Jagran' => 'Senior BJP leadership congratulated Dr. Narendra Prasad Singh as official victory certificate handed over at MIT Muzaffarpur.',
            'Prabhat Khabar' => 'Independent Bhushan Jha secured 2,351 votes; CPI sitting MLC Sanjay Kumar Singh bowed out in quota race.'
        ]
    ],
    [
        'id' => 'saran-teachers',
        'type' => 'Teachers',
        'type_hi' => 'शिक्षक',
        'name' => 'Saran Teachers',
        'name_hi' => 'सारण शिक्षक निर्वाचन क्षेत्र',
        'slug' => 'saran-teachers',
        'status' => 'DECLARED WON (विजेता घोषित)',
        'status_hi' => 'विजयी घोषित (Elected MLC)',
        'status_badge' => 'bg-success text-white',
        'is_declared' => true,
        'turnout' => 87.16,
        'booths' => 98,
        'counting_centre' => 'Saran Divisional HQ, Chapra',
        'counting_centre_hi' => 'सारण प्रमंडलीय मुख्यालय, छपरा',
        'returning_officer' => 'Divisional Commissioner, Saran (Chapra)',
        'returning_officer_hi' => 'प्रमंडलीय आयुक्त, सारण (छपरा)',
        'leading' => [
            'name' => 'Rahul Raj',
            'name_hi' => 'राहुल राज',
            'party' => 'IND',
            'role' => 'Elected MLC (Independent / BJP Rebel)',
            'role_hi' => 'विजयी घोषित (निर्दलीय / बागी प्रत्याशी)'
        ],
        'trailing' => [
            'name' => 'Afaq Ahmad',
            'name_hi' => 'अफाक अहमद',
            'party' => 'Jan Suraaj',
            'role' => 'Sitting MLC / Runner-up in Elimination Rounds',
            'role_hi' => 'निवर्तमान एमएलसी / उप-विजेता (जन सुराज)'
        ],
        'other_key' => [
            'name' => 'Anand Pushkar',
            'name_hi' => 'आनंद पुष्कर',
            'party' => 'JD(U)',
            'role' => 'NDA Candidate (Son of Late Kedar Nath Pandey)',
            'role_hi' => 'एनडीए प्रत्याशी (स्व. केदार नाथ पांडेय के पुत्र)'
        ],
        'trend_analysis' => 'RESULT DECLARED: Independent candidate Rahul Raj pulled off historic upset by 800+ votes in 2nd preference value transfers, defeating Jan Suraaj sitting MLC Afaq Ahmad and JD(U)\'s Anand Pushkar.',
        'trend_analysis_hi' => 'परिणाम घोषित: द्वितीय वरीयता मतों के हस्तांतरण के बाद निर्दलीय राहुल राज 800+ मतों से विजयी घोषित; जन सुराज के मौजूदा एमएलसी अफाक अहमद पराजित।',
        'sources_cited' => [
            'Live Hindustan' => 'Independent candidate and BJP rebel Rahul Raj secured victory by over 800 votes after decisive second-preference transfers.',
            'Dainik Jagran' => 'Record 87.16% turnout in Saran resulted in prolonged elimination rounds where Rahul Raj outpaced Afaq Ahmad and Anand Pushkar.',
            'Prabhat Khabar' => 'Formal announcement completed by Returning Officer Saran at Chapra counting hall under heavy security.'
        ]
    ],
    [
        'id' => 'patna-graduates',
        'type' => 'Graduates',
        'type_hi' => 'स्नातक',
        'name' => 'Patna Graduates',
        'name_hi' => 'पटना स्नातक निर्वाचन क्षेत्र',
        'slug' => 'patna-graduates',
        'status' => 'JD(U) Leading (Ahead in Trends)',
        'status_hi' => 'जदयू बढ़त पर (रुझान में आगे)',
        'status_badge' => 'bg-warning text-dark',
        'is_declared' => false,
        'turnout' => 36.19,
        'booths' => 134,
        'counting_centre' => 'College of Arts & Crafts, Patna',
        'counting_centre_hi' => 'कला एवं शिल्प महाविद्यालय, पटना',
        'returning_officer' => 'Divisional Commissioner, Patna',
        'returning_officer_hi' => 'प्रमंडलीय आयुक्त, पटना',
        'leading' => [
            'name' => 'Neeraj Kumar',
            'name_hi' => 'नीरज कुमार',
            'party' => 'JD(U)',
            'role' => 'Sitting MLC / NDA Candidate',
            'role_hi' => 'वर्तमान विधान पार्षद / एनडीए प्रत्याशी'
        ],
        'trailing' => [
            'name' => 'Anuj Singh',
            'name_hi' => 'अनुज सिंह',
            'party' => 'Jan Suraaj',
            'role' => 'Jan Suraaj Supported (Backed by Anant Singh)',
            'role_hi' => 'जन सुराज समर्थित (अनंत सिंह समर्थित)'
        ],
        'other_key' => [
            'name' => 'Dilip Kumar',
            'name_hi' => 'दिलीप कुमार',
            'party' => 'RJD',
            'role' => 'Mahagathbandhan Candidate',
            'role_hi' => 'महागठबंधन प्रत्याशी'
        ],
        'trend_analysis' => 'High-profile prestige duel: Ruling JD(U)\'s Neeraj Kumar maintaining edge against Jan Suraaj-supported Anuj Singh amidst rigorous table supervision at Arts College.',
        'trend_analysis_hi' => 'प्रतिष्ठापूर्ण मुकाबला: जदयू के नीरज कुमार जन सुराज समर्थित अनुज सिंह के खिलाफ कला एवं शिल्प महाविद्यालय केंद्र पर बढ़त बनाए हुए हैं।',
        'sources_cited' => [
            'Dainik Jagran' => 'Reported candidate Anuj Singh raising objections over CCTV monitor visibility; counting progressing under direct Divisional Commissioner vigil.',
            'Live Hindustan' => 'Mokama political stakes high as Anant Singh camp closely tracks ballot bundles; Neeraj Kumar holds early round advantage.',
            'Prabhat Khabar' => 'Patna, Nalanda, and Nawada bundles segregated; multi-round preferential quota counting extending into late evening.'
        ]
    ],
    [
        'id' => 'patna-teachers',
        'type' => 'Teachers',
        'type_hi' => 'शिक्षक',
        'name' => 'Patna Teachers',
        'name_hi' => 'पटना शिक्षक निर्वाचन क्षेत्र',
        'slug' => 'patna-teachers',
        'status' => 'RJD Leading in Tight Battle',
        'status_hi' => 'राजद आगे (कांटे की टक्कर)',
        'status_badge' => 'bg-danger text-white blink-anim',
        'is_declared' => false,
        'turnout' => 56.89,
        'booths' => 80,
        'counting_centre' => 'College of Arts & Crafts, Patna',
        'counting_centre_hi' => 'कला एवं शिल्प महाविद्यालय, पटना',
        'returning_officer' => 'Divisional Commissioner, Patna',
        'returning_officer_hi' => 'प्रमंडलीय आयुक्त, पटना',
        'leading' => [
            'name' => 'Dr. Sanjeev Kumar Singh',
            'name_hi' => 'डॉ. संजीव कुमार सिंह',
            'party' => 'RJD',
            'role' => 'Mahagathbandhan Challenger (Leading in Round 1/2)',
            'role_hi' => 'महागठबंधन प्रत्याशी (शुरुआती चक्रों में आगे)'
        ],
        'trailing' => [
            'name' => 'Nawal Kishore Yadav',
            'name_hi' => 'नवल किशोर यादव',
            'party' => 'BJP',
            'role' => 'Sitting MLC (5-time Winner) / NDA',
            'role_hi' => 'वर्तमान विधान पार्षद (5 बार विजेता) / एनडीए'
        ],
        'other_key' => [
            'name' => 'Jan Suraaj Nominee',
            'name_hi' => 'जन सुराज प्रत्याशी',
            'party' => 'Jan Suraaj',
            'role' => 'Third Challenger',
            'role_hi' => 'तृतीय दावेदार'
        ],
        'trend_analysis' => 'Sensational battle: RJD-backed Dr. Sanjeev Kumar Singh establishes narrow edge over 5-time BJP veteran Nawal Kishore Yadav across collegiate faculty ballots.',
        'trend_analysis_hi' => 'सनसनीखेज मुकाबला: 5 बार के भाजपा एमएलसी नवल किशोर यादव पर राजद के डॉ. संजीव कुमार सिंह ने बढ़त बनाई।',
        'sources_cited' => [
            'Live Hindustan' => 'RJD-backed Dr. Sanjeev Kumar Singh pulled ahead of veteran Nawal Kishore Yadav in collegiate teacher vote bundles.',
            'Amar Ujala' => 'Razor-thin preferential duel underway at Arts College campus between RJD and 5-time sitting BJP MLC.',
            'Prabhat Khabar' => 'Strict verification of teacher preference markings across 80 booths; 2nd preference value transfers may prove decisive.'
        ]
    ],
    [
        'id' => 'tirhut-graduates',
        'type' => 'Graduates',
        'type_hi' => 'स्नातक',
        'name' => 'Tirhut Graduates',
        'name_hi' => 'तिरहुत स्नातक निर्वाचन क्षेत्र',
        'slug' => 'tirhut-graduates',
        'status' => 'JD(U) Leading in Multi-Cornered Tally',
        'status_hi' => 'जदयू आगे (त्रिकोणीय मुकाबला)',
        'status_badge' => 'bg-warning text-dark',
        'is_declared' => false,
        'turnout' => 42.15,
        'booths' => 145,
        'counting_centre' => 'MIT Campus, Muzaffarpur',
        'counting_centre_hi' => 'एमआईटी परिसर, मुजफ्फरपुर',
        'returning_officer' => 'Divisional Commissioner, Tirhut',
        'returning_officer_hi' => 'प्रमंडलीय आयुक्त, तिरहुत',
        'leading' => [
            'name' => 'Abhishek Jha',
            'name_hi' => 'अभिषेक झा',
            'party' => 'JD(U)',
            'role' => 'NDA Candidate',
            'role_hi' => 'एनडीए प्रत्याशी'
        ],
        'trailing' => [
            'name' => 'Dr. Vimohan Kumar',
            'name_hi' => 'डॉ. विमोहन कुमार',
            'party' => 'Jan Suraaj',
            'role' => 'Jan Suraaj Party Contender',
            'role_hi' => 'जन सुराज प्रत्याशी'
        ],
        'other_key' => [
            'name' => 'Banshidhar Brajwasi / Dr. Rashmi Gautam',
            'name_hi' => 'वंशीधर ब्रजवासी / डॉ. रश्मि गौतम',
            'party' => 'IND / RJD',
            'role' => 'Outgoing Independent MLC & RJD Nominee',
            'role_hi' => 'निवर्तमान एमएलसी एवं राजद प्रत्याशी'
        ],
        'trend_analysis' => 'Abhishek Jha (JD-U) holds narrow edge in multi-cornered contest with Jan Suraaj contender Dr. Vimohan and outgoing MLC Banshidhar Brajwasi across 4 Tirhut districts.',
        'trend_analysis_hi' => 'जदयू के अभिषेक झा जन सुराज के डॉ. विमोहन और निर्दलीय वंशीधर ब्रजवासी के त्रिकोणीय मुकाबले में मामूली बढ़त पर।',
        'sources_cited' => [
            'Prabhat Khabar' => '145 booth boxes being counted across 14 tables under 3-tier security at MIT Muzaffarpur.',
            'Dainik Jagran' => 'Abhishek Jha establishes initial edge in Muzaffarpur and Vaishali bundles; Jan Suraaj polling strongly in rural belts.',
            'Live Hindustan' => 'Due to 42.15% turnout, elimination rounds will be required before any candidate crosses the statutory quota.'
        ]
    ],
    [
        'id' => 'darbhanga-graduates',
        'type' => 'Graduates',
        'type_hi' => 'स्नातक',
        'name' => 'Darbhanga Graduates',
        'name_hi' => 'दरभंगा स्नातक निर्वाचन क्षेत्र',
        'slug' => 'darbhanga-graduates',
        'status' => 'RJD Leading over BJP',
        'status_hi' => 'राजद बढ़त पर (प्रथम चक्र)',
        'status_badge' => 'bg-primary text-white',
        'is_declared' => false,
        'turnout' => 42.00,
        'booths' => 138,
        'counting_centre' => 'Divisional Commissioner Campus, Darbhanga',
        'counting_centre_hi' => 'प्रमंडलीय आयुक्त परिसर, दरभंगा',
        'returning_officer' => 'Divisional Commissioner, Darbhanga',
        'returning_officer_hi' => 'प्रमंडलीय आयुक्त, दरभंगा',
        'leading' => [
            'name' => 'Anil Kumar Jha',
            'name_hi' => 'अनिल कुमार झा',
            'party' => 'RJD',
            'role' => 'Mahagathbandhan Candidate (Leading)',
            'role_hi' => 'महागठबंधन प्रत्याशी (बढ़त पर)'
        ],
        'trailing' => [
            'name' => 'Dr. Sarvesh Kumar',
            'name_hi' => 'डॉ. सर्वेश कुमार',
            'party' => 'BJP',
            'role' => 'Sitting MLC / NDA Candidate',
            'role_hi' => 'वर्तमान विधान पार्षद / एनडीए प्रत्याशी'
        ],
        'other_key' => [
            'name' => 'Mohammad Imteyaz (Noorani)',
            'name_hi' => 'मोहम्मद इम्तियाज (नूरानी)',
            'party' => 'Jan Suraaj',
            'role' => 'Jan Suraaj Party Nominee',
            'role_hi' => 'जन सुराज प्रत्याशी'
        ],
        'trend_analysis' => 'Major upset brewing: RJD\'s Anil Kumar Jha holding solid lead over incumbent Dr. Sarvesh Kumar (BJP) across Darbhanga, Madhubani, Samastipur, and Begusarai.',
        'trend_analysis_hi' => 'बड़ा उलटफेर: राजद के अनिल कुमार झा मिथिलांचल के स्नातक मतों में मौजूदा भाजपा एमएलसी डॉ. सर्वेश कुमार पर बढ़त बनाए हुए हैं।',
        'sources_cited' => [
            'Amar Ujala' => 'RJD candidate Anil Kumar Jha ahead of sitting BJP MLC Dr. Sarvesh Kumar in initial Darbhanga graduate trends.',
            'Live Hindustan' => 'Counting proceeding smoothly across 138 booths; RJD leading in Samastipur and Madhubani rural bundles.',
            'Prabhat Khabar' => 'Strict 3-tier security deployed under Darbhanga Commissioner; bundle segregation advancing to round 2.'
        ]
    ],
    [
        'id' => 'darbhanga-teachers',
        'type' => 'Teachers',
        'type_hi' => 'शिक्षक',
        'name' => 'Darbhanga Teachers',
        'name_hi' => 'दरभंगा शिक्षक निर्वाचन क्षेत्र',
        'slug' => 'darbhanga-teachers',
        'status' => 'Jan Suraaj Leading in Close Contest',
        'status_hi' => 'जन सुराज आगे (कांटे की टक्कर)',
        'status_badge' => 'bg-warning text-dark',
        'is_declared' => false,
        'turnout' => 79.00,
        'booths' => 99,
        'counting_centre' => 'Divisional Commissioner Campus, Darbhanga',
        'counting_centre_hi' => 'प्रमंडलीय आयुक्त परिसर, दरभंगा',
        'returning_officer' => 'Divisional Commissioner, Darbhanga',
        'returning_officer_hi' => 'प्रमंडलीय आयुक्त, दरभंगा',
        'leading' => [
            'name' => 'Suresh Prasad Rai',
            'name_hi' => 'सुरेश प्रसाद राय',
            'party' => 'Jan Suraaj',
            'role' => 'Jan Suraaj Party Candidate (Ahead in Preference Count)',
            'role_hi' => 'जन सुराज प्रत्याशी (वरीयता मतों में आगे)'
        ],
        'trailing' => [
            'name' => 'Dr. Madan Mohan Jha',
            'name_hi' => 'डॉ. मदन मोहन झा',
            'party' => 'INC',
            'role' => 'Sitting MLC (Former BPCC Chief & Minister)',
            'role_hi' => 'वर्तमान विधान पार्षद (पूर्व कांग्रेस प्रदेश अध्यक्ष)'
        ],
        'other_key' => [
            'name' => 'Ramdev Rai',
            'name_hi' => 'रामदेव राय',
            'party' => 'BJP',
            'role' => 'NDA Supported Contender',
            'role_hi' => 'एनडीए समर्थित दावेदार'
        ],
        'trend_analysis' => 'Jan Suraaj Party\'s Suresh Prasad Rai maintaining lead in tight contest against Congress heavyweight Dr. Madan Mohan Jha; preferential transfers crucial.',
        'trend_analysis_hi' => 'जन सुराज के सुरेश प्रसाद राय कांग्रेस के वरिष्ठ नेता डॉ. मदन मोहन झा के खिलाफ करीबी मुकाबले में बढ़त बनाए हुए हैं।',
        'sources_cited' => [
            'The Week' => 'Jan Suraaj candidate Suresh Prasad Rai took significant lead over Congress veteran Madan Mohan Jha in Darbhanga Teachers.',
            'Hindustan Times' => 'Darbhanga Teachers turns into fierce triangular battle where Jan Suraaj party makes strong inroads.',
            'Prabhat Khabar' => 'High turnout (79%) being tabulated on 10 tables; 2nd preference markings expected to decide final winner.'
        ]
    ],
    [
        'id' => 'kosi-graduates',
        'type' => 'Graduates',
        'type_hi' => 'स्नातक',
        'name' => 'Kosi Graduates',
        'name_hi' => 'कोसी स्नातक निर्वाचन क्षेत्र',
        'slug' => 'kosi-graduates',
        'status' => 'BJP Leading with Solid Margin',
        'status_hi' => 'भाजपा मजबूत बढ़त पर',
        'status_badge' => 'bg-primary text-white',
        'is_declared' => false,
        'turnout' => 52.51,
        'booths' => 181,
        'counting_centre' => 'Purnia / Saharsa Counting Complex',
        'counting_centre_hi' => 'पूर्णिया / सहरसा मतगणना केंद्र',
        'returning_officer' => 'Divisional Commissioner, Kosi / Purnia',
        'returning_officer_hi' => 'प्रमंडलीय आयुक्त, कोसी / पूर्णिया',
        'leading' => [
            'name' => 'Dr. N.K. Yadav',
            'name_hi' => 'डॉ. एनके यादव',
            'party' => 'BJP',
            'role' => 'Sitting MLC / NDA Candidate',
            'role_hi' => 'वर्तमान विधान पार्षद / एनडीए प्रत्याशी'
        ],
        'trailing' => [
            'name' => 'Rajnish Ranjan',
            'name_hi' => 'रजनीश रंजन',
            'party' => 'Jan Suraaj',
            'role' => 'Jan Suraaj Party Nominee',
            'role_hi' => 'जन सुराज प्रत्याशी'
        ],
        'other_key' => [
            'name' => 'Nitesh Kumar',
            'name_hi' => 'नितेश कुमार',
            'party' => 'RJD',
            'role' => 'Mahagathbandhan Challenger',
            'role_hi' => 'महागठबंधन प्रत्याशी'
        ],
        'trend_analysis' => 'Incumbent Dr. N.K. Yadav (BJP) establishing commanding lead across 14-district collegiate bundles (181 booths) over Jan Suraaj\'s Rajnish Ranjan.',
        'trend_analysis_hi' => 'भाजपा के मौजूदा एमएलसी डॉ. एनके यादव 14 जिलों के 181 बूथों की छंटनी में जन सुराज के रजनीश रंजन पर मजबूत बढ़त बनाए हुए हैं।',
        'sources_cited' => [
            'Live Hindustan' => 'BJP candidate Dr. N.K. Yadav established a commanding lead across Kosi and Purnia divisions through initial counting rounds.',
            'ETV Bharat' => 'Dr. N.K. Yadav consolidating strong frontline position across 14 districts in the largest constituency.',
            'Dainik Jagran' => 'Bundle compilation advancing smoothly at Purnia/Saharsa complex with Dr. Yadav comfortably leading.'
        ]
    ]
];

// 8 Constituencies Polling Booth & Dedicated Hub Mapping (Created Oct 2026)
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
        'desc' => 'Polling concluded successfully across designated polling stations via ballot paper (PR-STV system). Strongrooms sealed.'
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
                        <span class="live-pulse-dot" style="background:#fff;"></span> LIVE COUNTING &bull; 27 OCT 2026
                    </span>
                    <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold">
                        Multi-Source Ground Intelligence
                    </span>
                    <span class="badge bg-light text-dark px-3 py-1.5 rounded-pill fw-semibold">
                        8 Seats (4 Grad + 4 Teach)
                    </span>
                </div>

                <h1 class="display-6 fw-bold mb-2" style="font-family: 'Outfit', sans-serif;">
                    Bihar Vidhan Parishad Election Results 2026
                </h1>
                <h2 class="h5 fw-normal text-light opacity-90 mb-3" style="font-family: 'Noto Sans Devanagari', sans-serif;">
                    बिहार विधान परिषद् द्विवार्षिक निर्वाचन 2026 — 8 सीटों की मतगणना एवं चुनाव परिणाम (लाइव अपडेट्स)
                </h2>
                
                <p class="lead fs-6 text-light opacity-90 mb-4" style="max-width: 720px; line-height: 1.6;">
                    Live vote counting trends, preferential round tallies, and cross-verified reports from <strong>Dainik Jagran, Live Hindustan, Prabhat Khabar &amp; Chief Electoral Officer Bihar</strong> for 8 contested Graduates' and Teachers' constituencies.
                </p>

                <div class="d-flex flex-wrap gap-2 pt-1">
                    <a href="#election-results" class="btn btn-danger px-3.5 py-2.5 rounded-3 fw-bold text-white shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="bi bi-trophy-fill"></i>
                        <span>Live Results &amp; Trends (मतगणना परिणाम) &rarr;</span>
                    </a>
                    <a href="#media-sources" class="btn btn-warning px-3 py-2.5 rounded-3 fw-bold text-dark d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-newspaper"></i> Verified Sources (स्रोत संदर्भ)
                    </a>
                    <a href="#voting-turnout" class="btn btn-outline-light px-3 py-2.5 rounded-3 fw-semibold">
                        <i class="bi bi-pie-chart-fill me-1"></i> Voting Turnout (मतदान प्रतिशत)
                    </a>
                    <a href="https://ceoelection.bihar.gov.in/PDF/Year_2026/ImportantInstructionsAndLetters/TCGCGajat2026.pdf" target="_blank" rel="noopener" class="btn gazette-btn px-3.5 py-2.5 rounded-3 fw-bold">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Official Gazette PDF &rarr;
                    </a>
                    <a href="#schedule" class="btn btn-outline-light px-3 py-2.5 rounded-3 fw-semibold">
                        <i class="bi bi-calendar-event me-1"></i> Timeline
                    </a>
                </div>
            </div>

            <!-- Countdown / Live Counting Status Card -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="background: rgba(15, 23, 42, 0.90); backdrop-filter: blur(14px); border: 1px solid rgba(255,255,255,0.18);">
                    <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 text-center">
                        <div class="d-inline-flex align-items-center gap-1.5 px-3 py-1 bg-success text-white rounded-pill extra-small fw-bold mb-2 shadow-sm">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>2 RESULTS DECLARED &bull; 6 COUNTING</span>
                        </div>
                        <h6 class="text-uppercase small fw-bold text-warning mb-0">
                            <i class="bi bi-broadcast me-1"></i> Vote Counting &amp; Results Declared
                        </h6>
                        <span class="text-white small fw-bold">Tuesday, 27 October 2026 (Evening Live Bulletin)</span>
                    </div>
                    <div class="card-body p-4 text-center">
                        <!-- Round Status Pulse -->
                        <div class="p-3 rounded-3 mb-3 text-start" style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35);">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="live-pulse-dot" style="background:#22c55e;"></span>
                                <strong class="text-warning small text-uppercase">Status: 2 Declared &bull; Elimination Rounds Underway</strong>
                            </div>
                            <p class="small text-light opacity-90 mb-0" style="font-size: 12px; line-height: 1.45;">
                                BJP's Dr. Narendra Prasad Singh declared winner in Tirhut Teachers. Independent Rahul Raj won Saran Teachers. High-stakes elimination rounds active across Patna, Darbhanga, Tirhut, and Kosi.
                            </p>
                        </div>

                        <div class="d-flex justify-content-between align-items-center text-start small text-light opacity-90">
                            <div>
                                <i class="bi bi-clock-history text-info me-1"></i> <strong>Counting Started:</strong>
                            </div>
                            <span class="badge bg-secondary text-white fw-bold px-2 py-1">27 Oct 2026, 08:00 AM</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center text-start small text-light opacity-90 mt-2">
                            <div>
                                <i class="bi bi-trophy-fill text-warning me-1"></i> <strong>Declared Seats:</strong>
                            </div>
                            <span class="badge bg-success text-white fw-bold px-2 py-1">2 of 8 Seats Won</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center text-start small text-light opacity-90 mt-2">
                            <div>
                                <i class="bi bi-hourglass-split text-danger me-1"></i> <strong>In Progress:</strong>
                            </div>
                            <span class="text-warning fw-semibold">6 Seats in Quota Rounds</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center text-start small text-light opacity-90 mt-2">
                            <div>
                                <i class="bi bi-newspaper text-info me-1"></i> <strong>Multi-Sources:</strong>
                            </div>
                            <span class="text-light fw-semibold">Jagran, Hindustan, Prabhat, CEO</span>
                        </div>
                        <div class="mt-3 pt-2 border-top border-secondary border-opacity-25">
                            <a href="#election-results" class="btn btn-warning btn-sm w-100 fw-bold rounded-pill text-dark shadow-sm">
                                <i class="bi bi-arrow-down-circle-fill me-1"></i> View Live Results &amp; Trends
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
                    <div class="text-success fw-bold display-6 mb-1" style="font-family: 'Outfit', sans-serif;">2 / 8</div>
                    <div class="fw-semibold text-dark small">Results Declared Won</div>
                    <small class="text-muted">Tirhut Teach (BJP) &bull; Saran Teach (IND)</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-white p-3 rounded-3 shadow-sm border h-100">
                    <div class="text-warning fw-bold display-6 mb-1" style="font-family: 'Outfit', sans-serif;">6 / 8</div>
                    <div class="fw-semibold text-dark small">Preferential Rounds Active</div>
                    <small class="text-muted">Patna, Tirhut, Dbg, Kosi</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-white p-3 rounded-3 shadow-sm border h-100">
                    <div class="text-primary fw-bold display-6 mb-1" style="font-family: 'Outfit', sans-serif;">150</div>
                    <div class="fw-semibold text-dark small">Total Contenders</div>
                    <small class="text-muted">90 Grad + 60 Teachers</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-white p-3 rounded-3 shadow-sm border h-100">
                    <div class="text-danger fw-bold display-6 mb-1 d-flex align-items-center justify-content-center gap-1.5" style="font-family: 'Outfit', sans-serif;">
                        <span class="live-pulse-dot"></span>
                        <span>LIVE</span>
                    </div>
                    <div class="fw-semibold text-dark small">PR-STV Counting Active</div>
                    <small class="text-muted">Single Transferable Quota System</small>
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
            <!-- 2.4 LIVE ELECTION RESULTS & COUNTING TRENDS (MULTI-SOURCE) -->
            <!-- ============================================================= -->
            <div id="election-results" class="mb-5">
                <!-- Section Header -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-danger text-white fw-bold text-uppercase px-2.5 py-1 d-inline-flex align-items-center gap-1.5 shadow-sm">
                                <span class="live-pulse-dot" style="background:#fff;"></span> LIVE COUNTING RESULTS
                            </span>
                            <span class="badge bg-success text-white extra-small fw-semibold px-2 py-0.5 rounded-pill">
                                2 Winners Declared
                            </span>
                        </div>
                        <h3 class="fw-bold mb-0 text-dark" style="font-family: 'Outfit', sans-serif;">
                            Vidhan Parishad 8-Seat Election Results &amp; Trends
                        </h3>
                        <div class="text-muted small" style="font-family: 'Noto Sans Devanagari', sans-serif;">
                            बिहार विधान परिषद् 8 सीटों (4 स्नातक + 4 शिक्षक) के आधिकारिक परिणाम, बढ़त एवं प्रमुख मीडिया स्रोतों की ग्राउंड रिपोर्ट
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                            <i class="bi bi-clock-history me-1"></i> Updated 09:15 PM IST
                        </span>
                        <div class="extra-small text-muted mt-1">27 October 2026 Evening</div>
                    </div>
                </div>

                <!-- Live Results Party Scoreboard Summary -->
                <div class="row g-2 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 border shadow-sm text-center bg-white h-100" style="border-top: 4px solid #f97316 !important;">
                            <div class="text-uppercase extra-small fw-bold text-muted mb-1">NDA (BJP / JDU)</div>
                            <div class="d-flex align-items-baseline justify-content-center gap-1">
                                <span class="fs-3 fw-bolder text-success" style="font-family: 'Outfit', sans-serif;">1</span>
                                <span class="small fw-bold text-success">Won</span>
                                <span class="text-muted mx-1">|</span>
                                <span class="fs-4 fw-bold text-primary">3</span>
                                <span class="extra-small text-muted">Lead</span>
                            </div>
                            <div class="extra-small text-muted mt-1">Won: Tirhut Teach &bull; Lead: Kosi, Patna, Tirhut Grad</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 border shadow-sm text-center bg-white h-100" style="border-top: 4px solid #16a34a !important;">
                            <div class="text-uppercase extra-small fw-bold text-muted mb-1">MGB (RJD / INC)</div>
                            <div class="d-flex align-items-baseline justify-content-center gap-1">
                                <span class="fs-3 fw-bolder text-secondary" style="font-family: 'Outfit', sans-serif;">0</span>
                                <span class="small fw-bold text-secondary">Won</span>
                                <span class="text-muted mx-1">|</span>
                                <span class="fs-4 fw-bold text-success">2</span>
                                <span class="extra-small text-muted">Lead</span>
                            </div>
                            <div class="extra-small text-muted mt-1">Lead: Patna Teachers, Darbhanga Graduates</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 border shadow-sm text-center bg-white h-100" style="border-top: 4px solid #eab308 !important;">
                            <div class="text-uppercase extra-small fw-bold text-muted mb-1">Jan Suraaj Party</div>
                            <div class="d-flex align-items-baseline justify-content-center gap-1">
                                <span class="fs-3 fw-bolder text-secondary" style="font-family: 'Outfit', sans-serif;">0</span>
                                <span class="small fw-bold text-secondary">Won</span>
                                <span class="text-muted mx-1">|</span>
                                <span class="fs-4 fw-bold text-warning">1</span>
                                <span class="extra-small text-muted">Lead</span>
                            </div>
                            <div class="extra-small text-muted mt-1">Lead: Darbhanga Teachers (Suresh Rai)</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 border shadow-sm text-center bg-white h-100" style="border-top: 4px solid #64748b !important;">
                            <div class="text-uppercase extra-small fw-bold text-muted mb-1">Independents / Others</div>
                            <div class="d-flex align-items-baseline justify-content-center gap-1">
                                <span class="fs-3 fw-bolder text-success" style="font-family: 'Outfit', sans-serif;">1</span>
                                <span class="small fw-bold text-success">Won</span>
                                <span class="text-muted mx-1">|</span>
                                <span class="fs-4 fw-bold text-secondary">0</span>
                                <span class="extra-small text-muted">Lead</span>
                            </div>
                            <div class="extra-small text-muted mt-1">Won: Saran Teachers (Rahul Raj)</div>
                        </div>
                    </div>
                </div>

                <!-- Verified Multi-Source Coverage Bar -->
                <div id="media-sources" class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff;">
                    <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-2.5 px-3.5 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shield-check text-warning fs-5"></i>
                            <span class="fw-bold small text-uppercase tracking-wider">Multi-Source Verification &amp; Citations (प्रमुख मीडिया एवं आधिकारिक स्रोत)</span>
                        </div>
                        <span class="badge bg-light text-dark extra-small rounded-pill fw-bold">4 Verified Outlets</span>
                    </div>
                    <div class="card-body p-3.5">
                        <p class="small text-light opacity-90 mb-3" style="line-height: 1.5;">
                            Results and preferential trends for this Single Transferable Vote (PR-STV) election are cross-verified and aggregated from ground reports by premier state news bureaus and official Election Commission notifications:
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
                                                <span class="text-light opacity-75 extra-small" style="font-family: 'Noto Sans Devanagari', sans-serif;">(<?php echo htmlspecialchars($src['name_hi']); ?>)</span>
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
                            <strong class="text-dark">Key Ground Intelligence &amp; Verified Declarations (प्रमुख परिणाम एवं रुझान):</strong>
                            <ul class="mb-0 ps-3 mt-1">
                                <li>
                                    <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Tirhut Teachers (Live Hindustan / प्रभात खबर):</strong> NDA-backed BJP candidate <strong>Dr. Narendra Prasad Singh</strong> officially declared elected MLC with 2,945 votes, defeating Independent Bhushan Jha (2,351 votes) by 594 votes. Sitting CPI MLC Sanjay Kumar Singh finished third.
                                </li>
                                <li>
                                    <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Saran Teachers (दैनिक जागरण / Live Hindustan):</strong> Independent candidate and BJP rebel <strong>Rahul Raj</strong> pulled off an 800+ vote victory in the decisive 2nd preference value transfers, defeating Jan Suraaj sitting MLC Afaq Ahmad and JD(U)'s Anand Pushkar.
                                </li>
                                <li>
                                    <strong>Patna Teachers (Amar Ujala / Live Hindustan):</strong> High-voltage battle at College of Arts &amp; Crafts where RJD-backed <strong>Dr. Sanjeev Kumar Singh</strong> holds a narrow edge over veteran 5-time BJP MLC Nawal Kishore Yadav in tight preferential bundles.
                                </li>
                                <li>
                                    <strong>Darbhanga Constituencies (The Week / Hindustan Times):</strong> Major political shifts emerging in Mithila. Jan Suraaj Party nominee <strong>Suresh Prasad Rai</strong> leads Congress leader Dr. Madan Mohan Jha in Teachers seat; RJD's <strong>Anil Kumar Jha</strong> leads BJP incumbent Dr. Sarvesh Kumar in Graduates.
                                </li>
                                <li>
                                    <strong>Kosi &amp; Patna Graduates (दैनिक जागरण / प्रभात खबर):</strong> BJP's <strong>Dr. N.K. Yadav</strong> consolidating strong lead across 14 Kosi/Purnia districts; JD(U)'s <strong>Neeraj Kumar</strong> maintains edge against Jan Suraaj-supported Anuj Singh in Patna.
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
                            <span class="fw-bold text-dark">Constituency-Wise Live Counting Status &amp; Leading Candidates</span>
                        </div>
                        <div class="btn-group btn-group-sm" role="group" aria-label="Results Filter">
                            <button type="button" class="btn btn-outline-dark active fw-semibold" onclick="filterResultsTable('all', this)">All (8)</button>
                            <button type="button" class="btn btn-outline-primary fw-semibold" onclick="filterResultsTable('Graduates', this)">Graduates (4)</button>
                            <button type="button" class="btn btn-outline-success fw-semibold" onclick="filterResultsTable('Teachers', this)">Teachers (4)</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="resultsReportTable">
                            <thead class="table-light text-uppercase text-secondary small" style="font-size: 11px;">
                                <tr>
                                    <th class="ps-3.5 py-3">Constituency</th>
                                    <th class="py-3">Winner / Leading</th>
                                    <th class="py-3">Key Challenger / Runner-up</th>
                                    <th class="py-3">Status / Round</th>
                                    <th class="py-3">Counting Venue &amp; RO</th>
                                    <th class="py-3 text-end pe-3.5">Verified By</th>
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
                                                <?php echo htmlspecialchars($row['name']); ?>
                                            </a>
                                            <?php if ($isWon): ?>
                                                <span class="badge bg-success text-white extra-small ms-1 rounded-pill px-2 py-0.5"><i class="bi bi-check-circle-fill me-1"></i>Won</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted extra-small" style="font-family: 'Noto Sans Devanagari', sans-serif;">
                                            <?php echo htmlspecialchars($row['name_hi']); ?>
                                        </div>
                                        <div class="d-flex align-items-center gap-1.5 mt-1">
                                            <span class="badge <?php echo $row['type'] === 'Graduates' ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success'; ?> extra-small rounded-pill px-2 py-0.5">
                                                <?php echo $row['type']; ?>
                                            </span>
                                            <span class="extra-small text-muted">
                                                <?php echo number_format($row['turnout'], 2); ?>% Turnout
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
                                                <div class="text-muted extra-small" style="font-family: 'Noto Sans Devanagari', sans-serif;">
                                                    <?php echo htmlspecialchars($row['leading']['name_hi']); ?>
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
                                        <div class="text-muted extra-small" style="font-family: 'Noto Sans Devanagari', sans-serif;">
                                            <?php echo htmlspecialchars($row['trailing']['name_hi']); ?>
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
                                            RO: <?php echo htmlspecialchars($row['returning_officer']); ?>
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
                            <strong>Source Attribution:</strong> Compiled from Live Hindustan, Dainik Jagran, Prabhat Khabar &amp; CEO Bihar. Final certificates issued by Returning Officers under ECI Rule 75.
                        </div>
                        <a href="#seat-deep-dive" class="text-primary fw-semibold text-decoration-none hover-underline d-inline-flex align-items-center gap-1">
                            <span>Explore Seat-by-Seat Media Reports</span>
                            <i class="bi bi-arrow-down-circle"></i>
                        </a>
                    </div>
                </div>

                <!-- Seat-by-Seat Media Ground Intelligence Breakdown -->
                <div id="seat-deep-dive" class="mb-4">
                    <h5 class="fw-bold text-dark mb-3" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-chat-square-text-fill text-primary me-2"></i>
                        Seat-by-Seat Media Intelligence &amp; Ground Reports (8 सीटें विस्तृत विश्लेषण)
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
                                                <?php echo htmlspecialchars($row['name']); ?>
                                            </a>
                                        </div>
                                        <div class="text-muted extra-small" style="font-family: 'Noto Sans Devanagari', sans-serif;">
                                            <?php echo htmlspecialchars($row['name_hi']); ?>
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
                                                <?php echo $isWon ? 'ELECTED MLC / WINNER (विजेता घोषित):' : 'Ahead / Leading in Trend:'; ?>
                                            </span>
                                            <span class="badge-party <?php echo $leadPartyClass; ?> extra-small px-2 py-0.5 rounded-pill">
                                                <?php echo htmlspecialchars($row['leading']['party']); ?>
                                            </span>
                                        </div>
                                        <div class="fw-bold text-dark" style="font-size: 1.05rem;">
                                            <?php echo htmlspecialchars($row['leading']['name']); ?>
                                            <span class="text-muted fs-6 fw-normal" style="font-family: 'Noto Sans Devanagari', sans-serif;">(<?php echo htmlspecialchars($row['leading']['name_hi']); ?>)</span>
                                        </div>
                                        <small class="text-muted d-block"><?php echo htmlspecialchars($row['leading']['role']); ?></small>
                                        
                                        <hr class="my-2 border-secondary border-opacity-25">
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="extra-small text-uppercase fw-bold text-secondary">
                                                <?php echo $isWon ? 'Nearest Contender / Runner-Up:' : 'Chief Challenger:'; ?>
                                            </span>
                                            <span class="badge-party <?php echo $trailPartyClass; ?> extra-small px-2 py-0.5 rounded-pill">
                                                <?php echo htmlspecialchars($row['trailing']['party']); ?>
                                            </span>
                                        </div>
                                        <div class="fw-semibold text-secondary">
                                            <?php echo htmlspecialchars($row['trailing']['name']); ?>
                                            <span class="extra-small text-muted" style="font-family: 'Noto Sans Devanagari', sans-serif;">(<?php echo htmlspecialchars($row['trailing']['name_hi']); ?>)</span>
                                        </div>
                                        <small class="text-muted d-block"><?php echo htmlspecialchars($row['trailing']['role']); ?></small>
                                    </div>

                                    <!-- Media Specific Reporting Points -->
                                    <div class="bg-light p-2.5 rounded-3 mb-3 border">
                                        <span class="extra-small fw-bold text-uppercase text-secondary d-block mb-1.5">
                                            <i class="bi bi-newspaper text-danger me-1"></i> What Different Sources Report:
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
                                                <span><?php echo $row['booths']; ?> Booths Data &rarr;</span>
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
                            <h6 class="fw-bold text-primary mb-1">How Vidhan Parishad PR-STV Quota Is Calculated (एकल संक्रमणीय मत प्रणाली)</h6>
                            <p class="small text-secondary mb-2" style="line-height: 1.5;">
                                Under the Representation of the People Act, 1951 and the Conduct of Elections Rules, 1961 (Rule 75), biennial elections to the Legislative Council use <strong>Proportional Representation by means of the Single Transferable Vote (PR-STV)</strong> via paper ballot. Unlike direct assembly polls (where first-past-the-post applies), a candidate must achieve the <strong>Statutory Quota</strong> of first-preference votes to be declared elected:
                            </p>
                            <div class="bg-white p-2.5 rounded-3 d-inline-block border shadow-sm mb-2 font-monospace text-dark small fw-bold">
                                Quota = [ Total Valid Preference Ballots / (1 + 1) ] + 1
                            </div>
                            <p class="extra-small text-muted mb-0">
                                If no candidate crosses this threshold on 1st preferences, the candidate with the lowest tally is eliminated, and their 2nd preference markings are transferred at full value. This continues round-by-round until a candidate reaches the quota.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- 2.5 LIVE VOTING TURNOUT & INCUMBENT MLCS (4 PM UPDATE) -->
            <!-- ============================================================= -->
            <div id="voting-turnout" class="mb-5">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                    <div>
                        <span class="badge bg-danger text-white fw-bold text-uppercase px-2.5 py-1 mb-1 blink-anim">
                            <i class="bi bi-broadcast me-1"></i> Voting Concluded &bull; Turnout Report
                        </span>
                        <h3 class="fw-bold mb-0 text-dark" style="font-family: 'Outfit', sans-serif;">
                            8 Seats Voting Turnout &amp; Incumbent MLCs
                        </h3>
                        <div class="text-muted small" style="font-family: 'Noto Sans Devanagari', sans-serif;">
                            शाम 4 बजे तक मतदान प्रतिशत, वर्तमान विधान पार्षद एवं 27 अक्टूबर मतगणना विवरण
                        </div>
                    </div>
                    <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                        <i class="bi bi-clock-history me-1"></i> As of 04:00 PM Poll Close
                    </span>
                </div>

                <!-- Live Highlight Metrics Cards -->
                <div class="row g-2 g-sm-3 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-4 bg-white shadow-sm border border-danger border-opacity-25 h-100 text-center">
                            <span class="text-muted extra-small fw-bold text-uppercase d-block mb-1">State Turnout</span>
                            <div class="h3 fw-extrabold text-danger mb-0 font-monospace">59%+</div>
                            <small class="text-muted" style="font-size: 11px;">Overall Average</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-4 bg-white shadow-sm border border-success border-opacity-25 h-100 text-center">
                            <span class="text-muted extra-small fw-bold text-uppercase d-block mb-1">Teachers Quota</span>
                            <div class="h3 fw-extrabold text-success mb-0 font-monospace">74.55%</div>
                            <small class="text-success fw-semibold" style="font-size: 11px;">Heavy Turnout</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-4 bg-white shadow-sm border border-primary border-opacity-25 h-100 text-center">
                            <span class="text-muted extra-small fw-bold text-uppercase d-block mb-1">Graduates Quota</span>
                            <div class="h3 fw-extrabold text-primary mb-0 font-monospace">43.87%</div>
                            <small class="text-muted" style="font-size: 11px;">Average Voting</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-4 bg-white shadow-sm border border-warning border-opacity-50 h-100 text-center">
                            <span class="text-muted extra-small fw-bold text-uppercase d-block mb-1">Counting Day</span>
                            <div class="h4 fw-extrabold text-dark mb-0 font-monospace">27 Oct</div>
                            <small class="text-muted" style="font-size: 11px;">Results from 8 AM</small>
                        </div>
                    </div>
                </div>

                <!-- Analysis Summary Alert -->
                <div class="alert alert-light border border-secondary border-opacity-25 rounded-4 p-3 mb-4 shadow-sm" style="background-color: #fdfefe;">
                    <div class="d-flex align-items-start gap-2.5">
                        <i class="bi bi-info-circle-fill text-primary fs-5 mt-0.5"></i>
                        <div class="small text-secondary" style="line-height: 1.55;">
                            <strong class="text-dark">Key Highlights:</strong>
                            शिक्षकों में मतदान को लेकर भारी उत्साह देखा गया जबकि स्नातकों में अपेक्षाकृत कम रुचि रही। 
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
                            <span class="fw-bold text-dark">Constituency-Wise Polling &amp; Sitting Member Roster</span>
                        </div>
                        <div class="btn-group btn-group-sm" role="group" aria-label="Turnout Filter">
                            <button type="button" class="btn btn-outline-dark active fw-semibold" onclick="filterTurnoutTable('all', this)">All (8)</button>
                            <button type="button" class="btn btn-outline-primary fw-semibold" onclick="filterTurnoutTable('Graduates', this)">Graduates (4)</button>
                            <button type="button" class="btn btn-outline-success fw-semibold" onclick="filterTurnoutTable('Teachers', this)">Teachers (4)</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="turnoutReportTable">
                            <thead class="table-light text-uppercase text-secondary small" style="font-size: 11px;">
                                <tr>
                                    <th class="ps-3.5 py-3">Constituency</th>
                                    <th class="py-3" style="min-width: 170px;">Turnout (शाम 4 बजे तक)</th>
                                    <th class="py-3">Sitting MLC (वर्तमान सदस्य)</th>
                                    <th class="py-3 text-center">Party</th>
                                    <th class="py-3">Tenure Expiry</th>
                                    <th class="py-3 text-end pe-3.5">Hub / Booths</th>
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
                                                <?php echo htmlspecialchars($row['name']); ?>
                                            </a>
                                        </div>
                                        <div class="text-muted extra-small" style="font-family: 'Noto Sans Devanagari', sans-serif;">
                                            <?php echo htmlspecialchars($row['name_hi']); ?>
                                        </div>
                                        <span class="badge <?php echo $row['category'] === 'Graduates' ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success'; ?> extra-small rounded-pill px-2 py-0.5 mt-1">
                                            <?php echo $row['category']; ?> Quota
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
                                        <div class="text-muted extra-small" style="font-family: 'Noto Sans Devanagari', sans-serif;">
                                            <?php echo htmlspecialchars($row['sitting_mlc_hi']); ?>
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
                                        <a href="<?php echo $bUrl; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-1 extra-small fw-semibold text-nowrap" title="Explore Booths and Voter Guide">
                                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                            <span><?php echo $row['booth_count']; ?> Booths</span>
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
                            <strong>Source / संदर्भ:</strong> Live Hindustan Voting Live Blog &bull; Final tally subject to ECI validation.
                        </div>
                        <a href="https://www.livehindustan.com/bihar/bihar-mlc-election-2026-voting-live-bjp-rjd-jdu-chunav-latest-updates-live-blog-201792729617020.html" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="text-primary fw-semibold text-decoration-none hover-underline d-inline-flex align-items-center gap-1">
                            <span>Read Live Hindustan Report</span>
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
                                            <?php echo htmlspecialchars($seat['ro']); ?>
                                            <?php if (!empty($seat['ro_url'])): ?>
                                                <a href="<?php echo htmlspecialchars($seat['ro_url']); ?>" target="_blank" rel="noopener" class="ms-1 text-primary text-decoration-none hover-underline extra-small" title="Divisional Commissioner Official Portal">
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
                                                <span>View <?php echo $bCard['count']; ?> Polling Booths (मतदान केंद्र)</span>
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
                            <i class="bi bi-person-check-fill me-1"></i> Electoral Roll &amp; Voter Guide
                        </span>
                        <h3 class="fw-bold mb-0 text-dark" style="font-family: 'Outfit', sans-serif;">
                            Voter List &amp; Electoral Roll (मतदाता सूची एवं निर्वाचक नामावली)
                        </h3>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="https://ceo.bihar.gov.in/tcgcelectorsearch/" target="_blank" rel="noopener" class="btn btn-warning text-dark btn-sm rounded-pill fw-bold shadow-sm d-inline-flex align-items-center gap-1.5">
                            <i class="bi bi-person-search"></i> CEO Elector Search (नाम खोजें) &rarr;
                        </a>
                        <a href="https://ceo.bihar.gov.in/tcgconline/" target="_blank" rel="noopener" class="btn btn-primary btn-sm rounded-pill fw-bold shadow-sm d-inline-flex align-items-center gap-1.5">
                            <i class="bi bi-pencil-square"></i> Apply Online (Form 18/19) &rarr;
                        </a>
                        <a href="https://voters.eci.gov.in/" target="_blank" rel="noopener" class="btn btn-outline-success btn-sm rounded-pill fw-semibold d-inline-flex align-items-center gap-1">
                            <i class="bi bi-check2-circle"></i> ECI Portal
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
                            <h5 class="fw-bold text-dark mb-1">Special Notice on Legislative Council Electoral Rolls</h5>
                            <p class="text-secondary small mb-3" style="line-height: 1.6;">
                                Unlike the Legislative Assembly (Vidhan Sabha) voter list, the <strong>Electoral Roll for Council Graduates' &amp; Teachers' Constituencies is prepared afresh before every biennial election</strong>. Ordinary inclusion in the general voter list does <em>not</em> automatically entitle you to vote in Council elections — voters must specifically register via <strong>Form 18</strong> (Graduates) or <strong>Form 19</strong> (Teachers).
                            </p>
                            
                            <div class="row g-2 pt-1">
                                <div class="col-md-3 col-sm-6">
                                    <div class="p-2.5 bg-light rounded-3 border h-100">
                                        <span class="text-muted d-block" style="font-size: 11px;">QUALIFYING DATE:</span>
                                        <strong class="text-dark small">01 November 2025</strong>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="p-2.5 bg-light rounded-3 border h-100">
                                        <span class="text-muted d-block" style="font-size: 11px;">ROLL REVISION STATUS:</span>
                                        <span class="badge bg-success-subtle text-success fw-bold">Final Roll (06.10.2026)</span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="p-2.5 bg-light rounded-3 border h-100">
                                        <span class="text-muted d-block" style="font-size: 11px;">ELECTOR SEARCH:</span>
                                        <a href="https://ceo.bihar.gov.in/tcgcelectorsearch/" target="_blank" rel="noopener" class="text-warning fw-bold small text-decoration-none hover-underline d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-search"></i> CEO Name Search &rarr;
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="p-2.5 bg-light rounded-3 border h-100">
                                        <span class="text-muted d-block" style="font-size: 11px;">ONLINE REGISTRATION:</span>
                                        <a href="https://ceo.bihar.gov.in/tcgconline/" target="_blank" rel="noopener" class="text-primary fw-bold small text-decoration-none hover-underline d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-box-arrow-up-right"></i> CEO Online Portal &rarr;
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
                                <i class="bi bi-table text-primary me-2"></i> 8 Constituencies Electoral Roll &amp; ERO Directory
                            </h6>
                            <small class="text-muted">Explore dedicated polling booths directory, search voter names, or download CEO PDF rolls</small>
                        </div>
                        <span class="badge bg-primary text-white small px-2.5 py-1">8 Biennial Seats</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light text-uppercase text-secondary" style="font-size: 11px;">
                                <tr>
                                    <th class="ps-4">Constituency Name</th>
                                    <th>Type</th>
                                    <th>Districts Covered</th>
                                    <th>Electoral Registration Officer (ERO)</th>
                                    <th class="text-end pe-4" style="min-width: 320px;">Roll Link / Action</th>
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
                                            <?php echo htmlspecialchars($s['name']); ?>
                                        </a>
                                        <div class="text-muted" style="font-size: 11px;"><?php echo htmlspecialchars($s['name_hi']); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $s['type'] === 'Graduates' ? 'bg-primary' : 'bg-success'; ?> rounded-pill px-2.5 py-1">
                                            <?php echo htmlspecialchars($s['type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-secondary"><?php echo implode(', ', array_slice($s['districts'], 0, 3)); ?><?php echo count($s['districts']) > 3 ? ' +' . (count($s['districts']) - 3) . ' more' : ''; ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark d-block"><?php echo htmlspecialchars($s['ro']); ?></span>
                                        <?php if (!empty($s['ro_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($s['ro_url']); ?>" target="_blank" rel="noopener" class="text-primary fw-semibold extra-small text-decoration-none hover-underline d-inline-flex align-items-center gap-1 mt-0.5" title="Official Commissioner Portal">
                                                <i class="bi bi-building"></i>
                                                <span><?php echo parse_url($s['ro_url'], PHP_URL_HOST); ?></span>
                                                <i class="bi bi-box-arrow-up-right" style="font-size: 9px;"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex flex-wrap justify-content-end align-items-center gap-1.5">
                                            <!-- Dedicated Polling Booths Link -->
                                            <a href="<?php echo $boothUrl; ?>" class="btn <?php echo $s['type'] === 'Graduates' ? 'btn-primary' : 'btn-success'; ?> btn-sm rounded-pill py-1 px-2.5 fw-bold shadow-sm d-inline-flex align-items-center gap-1" style="font-size: 11.5px;" title="View all <?php echo $boothCount; ?> polling booths for <?php echo htmlspecialchars($s['name']); ?>">
                                                <i class="bi bi-geo-alt-fill text-warning"></i>
                                                <span>Polling Booths (<?php echo $boothCount; ?>)</span>
                                                <i class="bi bi-arrow-right"></i>
                                            </a>
                                            <!-- CEO Elector Search Link -->
                                            <a href="https://ceo.bihar.gov.in/tcgcelectorsearch/" target="_blank" rel="noopener" class="btn btn-outline-warning text-dark btn-sm rounded-pill py-1 px-2 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px;" title="Search your name on CEO Bihar Elector Search portal">
                                                <i class="bi bi-person-search text-primary"></i>
                                                <span>Search Name</span>
                                            </a>
                                            <!-- Official CEO Roll PDF Link -->
                                            <a href="<?php echo $pdfUrl; ?>" target="_blank" rel="noopener" class="btn btn-outline-danger btn-sm rounded-pill py-1 px-2 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px;" title="Download Certified Electoral Roll PDF from CEO Bihar">
                                                <i class="bi bi-file-earmark-pdf-fill"></i>
                                                <span>Roll PDF</span>
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
                                    <h5 class="fw-bold mb-0 text-dark">Graduates Voter (Form 18 / प्ररूप 18)</h5>
                                    <small class="text-muted">स्नातक निर्वाचक नामावली पंजीकरण</small>
                                </div>
                            </div>

                            <h6 class="fw-bold text-dark small text-uppercase mb-2">Eligibility Criteria:</h6>
                            <ul class="text-secondary small ps-3 mb-3" style="line-height: 1.6;">
                                <li>Citizen of India ordinarily resident in the constituency.</li>
                                <li>Graduated at least <strong>3 years prior</strong> to the qualifying date (01 Nov 2025) from any university recognized by UGC / Govt of India.</li>
                                <li>Or possesses recognized equivalent degree/diploma.</li>
                            </ul>

                            <h6 class="fw-bold text-dark small text-uppercase mb-2">Mandatory Documents:</h6>
                            <ul class="text-secondary small ps-3 mb-3" style="line-height: 1.6;">
                                <li>Degree Certificate / Provisional Degree / Final Year Marksheet (Self-attested).</li>
                                <li>Aadhaar Card / EPIC (Voter ID) for identity proof.</li>
                                <li>Proof of residence in constituency (Electricity bill, Passport, Domicile).</li>
                            </ul>

                            <div class="d-flex flex-column gap-2 mt-auto">
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="https://ceo.bihar.gov.in/tcgconline/" target="_blank" rel="noopener" class="btn btn-warning btn-sm rounded-pill fw-bold text-dark shadow-sm flex-grow-1">
                                        <i class="bi bi-pencil-square me-1 text-danger"></i> Apply Online (CEO tcgconline) &rarr;
                                    </a>
                                    <a href="https://ceo.bihar.gov.in/tcgcelectorsearch/" target="_blank" rel="noopener" class="btn btn-outline-dark btn-sm rounded-pill fw-semibold">
                                        <i class="bi bi-person-search me-1"></i> Search Name
                                    </a>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="https://ceoelection.bihar.gov.in/" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill fw-semibold flex-grow-1">
                                        <i class="bi bi-download me-1"></i> Download Form 18 PDF
                                    </a>
                                    <a href="https://voters.eci.gov.in/" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill fw-semibold">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> ECI Portal
                                    </a>
                                </div>
                                <a href="https://ceo.bihar.gov.in/GCTCPDFVIEW/GCPDF.ASPX" target="_blank" rel="noopener" class="btn btn-primary btn-sm rounded-pill fw-bold text-white shadow-sm">
                                    <i class="bi bi-file-earmark-pdf-fill me-1 text-warning"></i> View / Download Graduates Voter List PDF (GCPDF) &rarr;
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
                                    <h5 class="fw-bold mb-0 text-dark">Teachers Voter (Form 19 / प्ररूप 19)</h5>
                                    <small class="text-muted">शिक्षक निर्वाचक नामावली पंजीकरण</small>
                                </div>
                            </div>

                            <h6 class="fw-bold text-dark small text-uppercase mb-2">Eligibility Criteria:</h6>
                            <ul class="text-secondary small ps-3 mb-3" style="line-height: 1.6;">
                                <li>Citizen of India ordinarily resident in the constituency.</li>
                                <li>Engaged in teaching for a total of at least <strong>3 years within the preceding 6 years</strong>.</li>
                                <li>Teaching in educational institutions not lower in standard than a secondary school as specified by CEO Bihar.</li>
                            </ul>

                            <div class="p-2.5 bg-success-subtle rounded-3 border border-success-subtle mb-3 small">
                                <div class="fw-bold text-success-emphasis mb-1">
                                    <i class="bi bi-check2-circle me-1"></i> Specified Educational Institutions:
                                </div>
                                <div class="text-secondary" style="font-size: 11.5px; line-height: 1.5;">
                                    Teachers from Higher Secondary (+2) Schools, Affiliated Colleges, Universities, Technical &amp; Research Institutes listed in <strong>CEO Bihar Orders (Letter No. 4487 &amp; 4010)</strong> are eligible.
                                </div>
                            </div>

                            <h6 class="fw-bold text-dark small text-uppercase mb-2">Mandatory Documents:</h6>
                            <ul class="text-secondary small ps-3 mb-3" style="line-height: 1.6;">
                                <li>Service Certificate issued by the Head of Institution (Principal/Director).</li>
                                <li>Affiliation / Recognition proof of educational institution.</li>
                                <li>EPIC / Aadhaar Card &amp; residential proof.</li>
                            </ul>

                            <div class="d-flex flex-column gap-2 mt-auto">
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="https://ceo.bihar.gov.in/tcgconline/" target="_blank" rel="noopener" class="btn btn-warning btn-sm rounded-pill fw-bold text-dark shadow-sm flex-grow-1">
                                        <i class="bi bi-pencil-square me-1 text-danger"></i> Apply Online (CEO tcgconline) &rarr;
                                    </a>
                                    <a href="https://ceo.bihar.gov.in/tcgcelectorsearch/" target="_blank" rel="noopener" class="btn btn-outline-dark btn-sm rounded-pill fw-semibold">
                                        <i class="bi bi-person-search me-1"></i> Search Name
                                    </a>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="https://ceoelection.bihar.gov.in/" target="_blank" class="btn btn-outline-success btn-sm rounded-pill fw-semibold flex-grow-1">
                                        <i class="bi bi-download me-1"></i> Form 19 PDF
                                    </a>
                                    <a href="https://voters.eci.gov.in/" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill fw-semibold">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> ECI Portal
                                    </a>
                                </div>
                                <a href="https://ceo.bihar.gov.in/GCTCPDFVIEW/TCPDF.ASPX" target="_blank" rel="noopener" class="btn btn-success btn-sm rounded-pill fw-bold text-white shadow-sm">
                                    <i class="bi bi-file-earmark-pdf-fill me-1 text-warning"></i> View / Download Teachers Voter List PDF (TCPDF) &rarr;
                                </a>
                                <div class="d-flex gap-2">
                                    <a href="https://ceoelection.bihar.gov.in/PDF/Year_2025/tcgc2026/4487-List%20of%20Educational%20Institutions%20for%20Preparation%20of%20Electoral%20Roll%20of%20Teacher%20Constituency.pdf" target="_blank" rel="noopener" class="btn btn-warning btn-sm rounded-pill fw-bold text-dark shadow-sm flex-grow-1" style="font-size: 12px;">
                                        <i class="bi bi-file-earmark-pdf-fill me-1 text-danger"></i> Letter 4487 (Updated Institutions) &rarr;
                                    </a>
                                    <a href="https://ceoelection.bihar.gov.in/PDF/Year_2025/tcgc2026/4010-List%20of%20Educational%20Institutions%20for%20Preparation%20of%20Electoral%20Roll%20of%20Teacher%20Constituency.pdf" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm rounded-pill fw-semibold" style="font-size: 12px;">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> Letter 4010
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CEO Bihar Letter 4487 & 4010 Highlight Banner -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border-left: 5px solid #16a34a !important;">
                    <div class="row align-items-center g-3">
                        <div class="col-lg-7">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-success fw-bold px-2.5 py-1 rounded-pill">
                                    <i class="bi bi-patch-check-fill me-1"></i> CEO Bihar Official Notifications
                                </span>
                                <span class="badge bg-warning text-dark fw-bold">Letter No. 4487 (Latest)</span>
                                <span class="badge bg-light text-dark border fw-semibold">Letter No. 4010</span>
                            </div>
                            <h5 class="fw-bold text-dark mb-1" style="font-family: 'Outfit', sans-serif;">
                                Lists of Specified Educational Institutions for Teachers' Electoral Roll
                            </h5>
                            <p class="small text-secondary mb-0" style="line-height: 1.5;">
                                Statutory lists of recognized Secondary Schools, Higher Secondary (+2) Schools, Degree Colleges, Universities, and Professional Institutes whose teachers qualify for inclusion in the Teachers' Constituency Electoral Roll (Form 19).
                            </p>
                        </div>
                        <div class="col-lg-5 text-lg-end d-flex flex-wrap gap-2 justify-content-lg-end">
                            <a href="https://ceoelection.bihar.gov.in/PDF/Year_2025/tcgc2026/4487-List%20of%20Educational%20Institutions%20for%20Preparation%20of%20Electoral%20Roll%20of%20Teacher%20Constituency.pdf" target="_blank" rel="noopener" class="btn btn-success fw-bold px-3 py-2 rounded-3 shadow-sm text-white">
                                <i class="bi bi-file-earmark-pdf-fill me-1 text-warning"></i> Letter 4487 PDF (Latest) &rarr;
                            </a>
                            <a href="https://ceoelection.bihar.gov.in/PDF/Year_2025/tcgc2026/4010-List%20of%20Educational%20Institutions%20for%20Preparation%20of%20Electoral%20Roll%20of%20Teacher%20Constituency.pdf" target="_blank" rel="noopener" class="btn btn-outline-success fw-bold px-3 py-2 rounded-3">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Letter 4010 PDF &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 3-Step Guide to Search Name in Voter List -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold text-dark mb-3" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-question-circle-fill text-warning me-2"></i> How to Check Your Name in Council Voter List (मतदाता सूची में नाम कैसे जांचें)
                    </h5>
                    <div class="row g-3 small">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 h-100 border">
                                <div class="badge bg-primary rounded-circle mb-2" style="width: 24px; height: 24px;">1</div>
                                <h6 class="fw-bold text-dark mb-1">Step 1: Open CEO Bihar Portal</h6>
                                <p class="text-muted mb-0">Open <a href="https://ceo.bihar.gov.in/GCTCPDFVIEW/GCPDF.ASPX" target="_blank" class="fw-semibold text-primary">Graduates Roll Portal (GCPDF)</a>, <a href="https://ceo.bihar.gov.in/GCTCPDFVIEW/TCPDF.ASPX" target="_blank" class="fw-semibold text-success">Teachers Roll Portal (TCPDF)</a>, or <code>ceoelection.bihar.gov.in</code>.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 h-100 border">
                                <div class="badge bg-primary rounded-circle mb-2" style="width: 24px; height: 24px;">2</div>
                                <h6 class="fw-bold text-dark mb-1">Step 2: Select Constituency &amp; District</h6>
                                <p class="text-muted mb-0">Choose your specific Council Constituency (e.g. Patna Graduates or Saran Teachers) and your home District/Block.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 h-100 border">
                                <div class="badge bg-primary rounded-circle mb-2" style="width: 24px; height: 24px;">3</div>
                                <h6 class="fw-bold text-dark mb-1">Step 3: Download Booth Roll / Search EPIC</h6>
                                <p class="text-muted mb-0">Search using your EPIC number or download your local designated polling booth PDF to verify your name, serial number, and polling station location.</p>
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
                <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom" style="font-family: 'Outfit', sans-serif;">
                    <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i> Official CEO Bihar Documents
                </h5>
                
                <!-- Document 1: Gazette -->
                <div class="p-2.5 bg-light rounded-3 border mb-2.5 small">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="text-dark">Election Gazette Notification</strong>
                        <span class="badge bg-danger">TCGCGajat2026</span>
                    </div>
                    <div class="text-muted mb-2" style="font-size: 11.5px;">File No: M2–03(TC&amp;GC)/2026-5369</div>
                    <a href="https://ceoelection.bihar.gov.in/PDF/Year_2026/ImportantInstructionsAndLetters/TCGCGajat2026.pdf" target="_blank" rel="noopener" class="btn btn-outline-danger btn-sm w-100 fw-bold rounded-pill" style="font-size: 11.5px;">
                        <i class="bi bi-download me-1"></i> Download Gazette PDF
                    </a>
                </div>

                <!-- Document 2: Letter 4487 (Latest Updated List) -->
                <div class="p-2.5 bg-light rounded-3 border mb-2.5 small">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="text-dark">Teachers' Institutions (Latest)</strong>
                        <span class="badge bg-success">Letter 4487</span>
                    </div>
                    <div class="text-muted mb-2" style="font-size: 11.5px;">Updated recognized institutions list for Form 19</div>
                    <a href="https://ceoelection.bihar.gov.in/PDF/Year_2025/tcgc2026/4487-List%20of%20Educational%20Institutions%20for%20Preparation%20of%20Electoral%20Roll%20of%20Teacher%20Constituency.pdf" target="_blank" rel="noopener" class="btn btn-success btn-sm w-100 fw-bold rounded-pill text-white" style="font-size: 11.5px;">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Download Letter 4487 PDF
                    </a>
                </div>

                <!-- Document 3: Letter 4010 List of Educational Institutions -->
                <div class="p-2.5 bg-light rounded-3 border small">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="text-dark">Teachers' Institutions (Initial)</strong>
                        <span class="badge bg-secondary">Letter 4010</span>
                    </div>
                    <div class="text-muted mb-2" style="font-size: 11.5px;">Specified institutions list for Teachers' roll</div>
                    <a href="https://ceoelection.bihar.gov.in/PDF/Year_2025/tcgc2026/4010-List%20of%20Educational%20Institutions%20for%20Preparation%20of%20Electoral%20Roll%20of%20Teacher%20Constituency.pdf" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm w-100 fw-bold rounded-pill" style="font-size: 11.5px;">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Download Letter 4010 PDF
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

<?php include 'footer.php'; ?>

