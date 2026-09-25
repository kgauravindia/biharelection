<?php
/**
 * Bihar Legislative Assembly - Enacted Acts from 1937 to 2026
 * Master Dataset extracted from Official Bihar Vidhan Sabha Document:
 * https://vidhansabha.bihar.gov.in/pdf/enacted%20Bill/Act%20List%20from%201937.pdf
 */

class BiharActsDataProvider {
    public static function getAllActs() {
        static $acts = null;
        if ($acts === null) {
            $jsonFile = __DIR__ . '/bihar_acts_data.json';
            if (file_exists($jsonFile)) {
                $acts = json_decode(file_get_contents($jsonFile), true) ?: [];
            } else {
                $acts = [];
            }
        }
        return $acts;
    }

    public static function getSummary() {
        $acts = self::getAllActs();
        $totalActs = count($acts);
        $years = array_unique(array_column($acts, 'year'));
        sort($years);

        $categories = [];
        foreach ($acts as $a) {
            $cat = $a['category'] ?? 'General & Governance';
            $categories[$cat] = ($categories[$cat] ?? 0) + 1;
        }
        arsort($categories);

        $categoryColors = [
            'Land Reforms & Revenue' => '#854d0e',
            'Finance, Budget & Taxation' => '#1d4ed8',
            'Education & Universities' => '#4338ca',
            'Panchayati Raj & Local Bodies' => '#047857',
            'Social Welfare & Reservation' => '#b91c1c',
            'Law, Justice & Police' => '#475569',
            'Agriculture, Irrigation & Co-ops' => '#15803d',
            'Infrastructure, Industry & Energy' => '#0e7490',
            'Health & Medical' => '#be123c',
            'General & Governance' => '#334155'
        ];

        $categoryIcons = [
            'Land Reforms & Revenue' => 'bi-geo-alt-fill',
            'Finance, Budget & Taxation' => 'bi-cash-coin',
            'Education & Universities' => 'bi-mortarboard-fill',
            'Panchayati Raj & Local Bodies' => 'bi-building-fill',
            'Social Welfare & Reservation' => 'bi-heart-pulse-fill',
            'Law, Justice & Police' => 'bi-shield-check',
            'Agriculture, Irrigation & Co-ops' => 'bi-flower1',
            'Infrastructure, Industry & Energy' => 'bi-lightning-charge-fill',
            'Health & Medical' => 'bi-hospital-fill',
            'General & Governance' => 'bi-bank2'
        ];

        return [
            'total_acts' => $totalActs,
            'total_years' => count($years),
            'start_year' => !empty($years) ? reset($years) : 1937,
            'end_year' => !empty($years) ? end($years) : 2026,
            'years_list' => $years,
            'categories' => $categories,
            'category_colors' => $categoryColors,
            'category_icons' => $categoryIcons,
            'pdf_url' => 'https://vidhansabha.bihar.gov.in/pdf/enacted%20Bill/Act%20List%20from%201937.pdf',
            'official_source' => 'Bihar Legislative Assembly (बिहार विधान सभा)',
            'official_portal' => 'https://vidhansabha.bihar.gov.in/'
        ];
    }
}
