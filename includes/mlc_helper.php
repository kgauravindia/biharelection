<?php
/**
 * BiharElection.com - MLC Constituencies & Booths Data Helper
 */

function getMlcAllConstituenciesData() {
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $jsonFile = __DIR__ . '/mlc_booths_data.json';
    if (!file_exists($jsonFile)) {
        return [];
    }
    $raw = file_get_contents($jsonFile);
    $cache = json_decode($raw, true) ?: [];
    return $cache;
}

function getMlcConstituency($key) {
    $all = getMlcAllConstituenciesData();
    // Allow key with or without -constituency or alias
    $normalized = strtolower(trim($key));
    if (isset($all[$normalized])) {
        return $all[$normalized];
    }
    // Check aliases
    $aliases = [
        'saran-teachers-constituency' => 'saran-teacher-constituency',
        'patna-teachers-constituency' => 'patna-teacher-constituency',
        'tirhut-teachers-constituency' => 'tirhut-teacher-constituency',
        'darbhanga-teachers-constituency' => 'darbhanga-teacher-constituency',
        'patna-graduates-constituency' => 'patna-graduate-constituency',
        'tirhut-graduates-constituency' => 'tirhut-graduate-constituency',
        'darbhanga-graduates-constituency' => 'darbhanga-graduate-constituency',
        'kosi-graduates-constituency' => 'kosi-graduate-constituency',
    ];
    if (isset($aliases[$normalized]) && isset($all[$aliases[$normalized]])) {
        return $all[$aliases[$normalized]];
    }
    return null;
}
