-- ==========================================================
-- BiharElection.com - Production Database Schema
-- Target Database: u305984835_biharelection
-- All Tables use the official `be_` prefix
-- ==========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Districts Hub
CREATE TABLE IF NOT EXISTS `be_districts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `name_hi` VARCHAR(100) DEFAULT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `headquarters` VARCHAR(100) NOT NULL,
  `division` VARCHAR(100) NOT NULL,
  `total_ac` INT DEFAULT 0,
  `total_electors` BIGINT DEFAULT 0,
  `ac_list` JSON DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Assembly Constituencies (243 Seats)
CREATE TABLE IF NOT EXISTS `be_constituencies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ac_no` INT NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `name_hi` VARCHAR(100) DEFAULT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `district` VARCHAR(100) NOT NULL,
  `district_hi` VARCHAR(100) DEFAULT NULL,
  `lok_sabha` VARCHAR(100) DEFAULT NULL,
  `reservation` VARCHAR(20) DEFAULT 'GEN',
  `total_electors` INT DEFAULT 0,
  `male_electors` INT DEFAULT 0,
  `female_electors` INT DEFAULT 0,
  `polling_stations` INT DEFAULT 0,
  `blocks` JSON DEFAULT NULL,
  `total_panchayats` INT DEFAULT 0,
  `current_mla` VARCHAR(150) DEFAULT NULL,
  `current_party` VARCHAR(50) DEFAULT NULL,
  `election_2020` JSON DEFAULT NULL,
  `election_2015` JSON DEFAULT NULL,
  `key_issues` JSON DEFAULT NULL,
  `party_history` TEXT DEFAULT NULL,
  `candidates_2026_expected` JSON DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Candidates & Leaders Directory
CREATE TABLE IF NOT EXISTS `be_candidates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `name_hi` VARCHAR(150) DEFAULT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `party` VARCHAR(100) NOT NULL,
  `party_short` VARCHAR(50) NOT NULL,
  `constituency` VARCHAR(100) NOT NULL,
  `district` VARCHAR(100) NOT NULL,
  `designation` VARCHAR(255) DEFAULT NULL,
  `age` INT DEFAULT NULL,
  `education` VARCHAR(255) DEFAULT NULL,
  `profession` VARCHAR(255) DEFAULT NULL,
  `assets_declared` VARCHAR(100) DEFAULT NULL,
  `liabilities` VARCHAR(100) DEFAULT NULL,
  `criminal_cases` INT DEFAULT 0,
  `verified` TINYINT(1) DEFAULT 0,
  `photo` VARCHAR(255) DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `social_links` JSON DEFAULT NULL,
  `election_record` JSON DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Panchayats Master (8,053+ Gram Panchayats)
CREATE TABLE IF NOT EXISTS `be_panchayats` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `district` VARCHAR(100) NOT NULL,
  `district_hi` VARCHAR(100) DEFAULT NULL,
  `district_slug` VARCHAR(100) DEFAULT NULL,
  `block` VARCHAR(100) NOT NULL,
  `block_hi` VARCHAR(100) DEFAULT NULL,
  `panchayat_name` VARCHAR(150) NOT NULL,
  `panchayat_hi` VARCHAR(150) DEFAULT NULL,
  `total_wards` INT DEFAULT 0,
  `total_voters` INT DEFAULT 0,
  `current_mukhiya` VARCHAR(150) DEFAULT NULL,
  `current_sarpanch` VARCHAR(150) DEFAULT NULL,
  `reservation_2026_mukhiya` VARCHAR(100) DEFAULT NULL,
  `reservation_2026_sarpanch` VARCHAR(100) DEFAULT NULL,
  `zila_parishad_territory_no` VARCHAR(50) DEFAULT NULL,
  `delimitation_status` VARCHAR(100) DEFAULT 'Delimitation Finalized',
  `key_issues` JSON DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`district`),
  INDEX (`district_slug`),
  INDEX (`block`),
  INDEX (`panchayat_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Elected Mukhiyas Roster
CREATE TABLE IF NOT EXISTS `be_mukhiyas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `candidate_name` VARCHAR(150) NOT NULL,
  `post` VARCHAR(50) DEFAULT 'मुखिया',
  `district` VARCHAR(100) NOT NULL,
  `district_slug` VARCHAR(100) DEFAULT NULL,
  `block` VARCHAR(100) NOT NULL,
  `panchayat` VARCHAR(150) NOT NULL,
  `gender` VARCHAR(20) DEFAULT NULL,
  `category` VARCHAR(50) DEFAULT NULL,
  `mobile` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `votes_received` INT DEFAULT 0,
  `tenure` VARCHAR(50) DEFAULT '2021-2026',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`district`),
  INDEX (`block`),
  INDEX (`panchayat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Elected Sarpanchs Roster
CREATE TABLE IF NOT EXISTS `be_sarpanchs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `candidate_name` VARCHAR(150) NOT NULL,
  `post` VARCHAR(50) DEFAULT 'सरपंच',
  `district` VARCHAR(100) NOT NULL,
  `district_slug` VARCHAR(100) DEFAULT NULL,
  `block` VARCHAR(100) NOT NULL,
  `panchayat` VARCHAR(150) NOT NULL,
  `gender` VARCHAR(20) DEFAULT NULL,
  `category` VARCHAR(50) DEFAULT NULL,
  `mobile` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `votes_received` INT DEFAULT 0,
  `tenure` VARCHAR(50) DEFAULT '2021-2026',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`district`),
  INDEX (`block`),
  INDEX (`panchayat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Zila Parishad Territorial Members
CREATE TABLE IF NOT EXISTS `be_zila_parishad_members` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `candidate_name` VARCHAR(150) NOT NULL,
  `district` VARCHAR(100) NOT NULL,
  `district_slug` VARCHAR(100) DEFAULT NULL,
  `block` VARCHAR(100) DEFAULT NULL,
  `territory_no` VARCHAR(50) NOT NULL,
  `gender` VARCHAR(20) DEFAULT NULL,
  `category` VARCHAR(50) DEFAULT NULL,
  `mobile` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `votes_received` INT DEFAULT 0,
  `tenure` VARCHAR(50) DEFAULT '2021-2026',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`district`),
  INDEX (`territory_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Zila Parishad Officials (Chairpersons/Vice-Chairpersons)
CREATE TABLE IF NOT EXISTS `be_zila_parishad_officials` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `candidate_name` VARCHAR(150) NOT NULL,
  `post` VARCHAR(100) NOT NULL,
  `district` VARCHAR(100) NOT NULL,
  `district_slug` VARCHAR(100) DEFAULT NULL,
  `gender` VARCHAR(20) DEFAULT NULL,
  `category` VARCHAR(50) DEFAULT NULL,
  `mobile` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `tenure` VARCHAR(50) DEFAULT '2021-2026',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`district`),
  INDEX (`post`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Vidhan Sabha 2025: Successful Candidates
CREATE TABLE IF NOT EXISTS `be_election_2025_successful_candidates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ac_no` INT NOT NULL UNIQUE,
  `constituency` VARCHAR(150) NOT NULL,
  `winner_name` VARCHAR(150) NOT NULL,
  `winner_gender` VARCHAR(20) DEFAULT NULL,
  `winner_party` VARCHAR(100) NOT NULL,
  `winner_symbol` VARCHAR(100) DEFAULT NULL,
  `winner_age` INT DEFAULT NULL,
  `winner_category` VARCHAR(50) DEFAULT NULL,
  `winner_votes` INT NOT NULL DEFAULT 0,
  `runner_up_name` VARCHAR(150) NOT NULL,
  `runner_up_gender` VARCHAR(20) DEFAULT NULL,
  `runner_up_party` VARCHAR(100) NOT NULL,
  `runner_up_votes` INT NOT NULL DEFAULT 0,
  `margin` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Vidhan Sabha 2025: Detailed Results
CREATE TABLE IF NOT EXISTS `be_election_2025_detailed_results` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ac_no` INT NOT NULL,
  `ac_name` VARCHAR(150) NOT NULL,
  `candidate_name` VARCHAR(150) NOT NULL,
  `gender` VARCHAR(20) DEFAULT NULL,
  `age` INT DEFAULT NULL,
  `category` VARCHAR(50) DEFAULT NULL,
  `party` VARCHAR(100) NOT NULL,
  `symbol` VARCHAR(100) DEFAULT NULL,
  `votes_general` INT DEFAULT 0,
  `votes_postal` INT DEFAULT 0,
  `votes_total` INT NOT NULL DEFAULT 0,
  `vote_share_valid` DECIMAL(5,2) DEFAULT 0.00,
  `vote_share_electors` DECIMAL(5,2) DEFAULT 0.00,
  `total_electors` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`ac_no`),
  INDEX (`party`),
  INDEX (`candidate_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Vidhan Sabha 2025: AC Electors
CREATE TABLE IF NOT EXISTS `be_election_2025_ac_electors` (
  `ac_no` INT PRIMARY KEY,
  `ac_name` VARCHAR(150) NOT NULL,
  `general_male` INT DEFAULT 0,
  `general_female` INT DEFAULT 0,
  `general_third_gender` INT DEFAULT 0,
  `general_total` INT DEFAULT 0,
  `service_male` INT DEFAULT 0,
  `service_female` INT DEFAULT 0,
  `service_total` INT DEFAULT 0,
  `all_male` INT DEFAULT 0,
  `all_female` INT DEFAULT 0,
  `all_third_gender` INT DEFAULT 0,
  `all_total` INT DEFAULT 0,
  `nri_total` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Vidhan Sabha 2025: AC Voters & Turnout
CREATE TABLE IF NOT EXISTS `be_election_2025_ac_voters` (
  `ac_no` INT PRIMARY KEY,
  `ac_name` VARCHAR(150) NOT NULL,
  `total_electors` INT DEFAULT 0,
  `voted_male` INT DEFAULT 0,
  `voted_female` INT DEFAULT 0,
  `voted_third_gender` INT DEFAULT 0,
  `voted_postal` INT DEFAULT 0,
  `voted_total` INT DEFAULT 0,
  `turnout_percent` DECIMAL(5,2) DEFAULT 0.00,
  `rejected_postal` INT DEFAULT 0,
  `nota_votes` INT DEFAULT 0,
  `valid_votes` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Vidhan Sabha 2025: Party Performance
CREATE TABLE IF NOT EXISTS `be_election_2025_party_performance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `party_type` VARCHAR(100) NOT NULL,
  `abbreviation` VARCHAR(50) NOT NULL,
  `contested` INT DEFAULT 0,
  `won` INT DEFAULT 0,
  `fd` INT DEFAULT 0,
  `votes_polled` BIGINT DEFAULT 0,
  `vote_share_valid` VARCHAR(50) DEFAULT NULL,
  `vote_share_total` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Public Registered Users (Citizens, Voters, Candidates, Mukhiyas)
CREATE TABLE IF NOT EXISTS `be_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `mobile` VARCHAR(20) NOT NULL UNIQUE,
  `email` VARCHAR(100) DEFAULT NULL,
  `password` VARCHAR(255) DEFAULT NULL,
  `role` VARCHAR(50) DEFAULT 'voter',
  `district` VARCHAR(100) DEFAULT NULL,
  `constituency` VARCHAR(100) DEFAULT NULL,
  `panchayat` VARCHAR(150) DEFAULT NULL,
  `status` VARCHAR(20) DEFAULT 'ACTIVE',
  `otp_code` VARCHAR(10) DEFAULT NULL,
  `otp_expiry` DATETIME DEFAULT NULL,
  `is_mobile_verified` TINYINT(1) DEFAULT 0,
  `profile_photo` VARCHAR(255) DEFAULT NULL,
  `last_login` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`mobile`),
  INDEX (`email`),
  INDEX (`role`),
  INDEX (`district`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Admin Users
CREATE TABLE IF NOT EXISTS `be_admin_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `role` VARCHAR(20) DEFAULT 'admin',
  `status` VARCHAR(20) DEFAULT 'active',
  `last_login` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Contact Inquiries & Leads
CREATE TABLE IF NOT EXISTS `be_contacts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `mobile` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `inquiry_type` VARCHAR(50) DEFAULT 'general',
  `message` TEXT NOT NULL,
  `status` VARCHAR(20) DEFAULT 'NEW',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Advertisements & Commercial Sponsors
CREATE TABLE IF NOT EXISTS `be_advertisements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_type` VARCHAR(50) NOT NULL,
  `client_name` VARCHAR(150) NOT NULL,
  `contact_phone` VARCHAR(20) NOT NULL,
  `contact_email` VARCHAR(100) DEFAULT NULL,
  `target_entity` VARCHAR(150) DEFAULT NULL,
  `amount` DECIMAL(10,2) DEFAULT 0.00,
  `banner_url` VARCHAR(255) DEFAULT NULL,
  `status` VARCHAR(20) DEFAULT 'PENDING',
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Census 2011 Districts Demographics
CREATE TABLE IF NOT EXISTS `be_census_districts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `district_code` VARCHAR(20) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `households` INT DEFAULT 0,
  `population` INT DEFAULT 0,
  `male` INT DEFAULT 0,
  `female` INT DEFAULT 0,
  `sex_ratio` INT DEFAULT 0,
  `pop_0_6` INT DEFAULT 0,
  `sc_population` INT DEFAULT 0,
  `sc_percentage` DECIMAL(5,2) DEFAULT 0,
  `st_population` INT DEFAULT 0,
  `st_percentage` DECIMAL(5,2) DEFAULT 0,
  `literates` INT DEFAULT 0,
  `literacy_rate` DECIMAL(5,2) DEFAULT 0,
  `rural_population` INT DEFAULT 0,
  `urban_population` INT DEFAULT 0,
  `total_workers` INT DEFAULT 0,
  `main_workers` INT DEFAULT 0,
  `cultivators` INT DEFAULT 0,
  `agricultural_labourers` INT DEFAULT 0,
  `marginal_workers` INT DEFAULT 0,
  `non_workers` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Census 2011 Sub-Districts (534 Blocks/Tehsils)
CREATE TABLE IF NOT EXISTS `be_census_subdistricts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `district_name` VARCHAR(100) NOT NULL,
  `district_slug` VARCHAR(100) NOT NULL,
  `sub_dist_code` VARCHAR(20) NOT NULL,
  `sub_district` VARCHAR(150) NOT NULL,
  `households` INT DEFAULT 0,
  `population` INT DEFAULT 0,
  `male` INT DEFAULT 0,
  `female` INT DEFAULT 0,
  `sex_ratio` INT DEFAULT 0,
  `sc_population` INT DEFAULT 0,
  `st_population` INT DEFAULT 0,
  `literates` INT DEFAULT 0,
  `literacy_rate` DECIMAL(5,2) DEFAULT 0,
  `total_workers` INT DEFAULT 0,
  `cultivators` INT DEFAULT 0,
  `agricultural_labourers` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`district_slug`),
  INDEX (`sub_district`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
