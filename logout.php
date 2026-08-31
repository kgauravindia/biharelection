<?php
/**
 * BiharElection.com - Public User Logout
 */
require_once __DIR__ . '/includes/auth_helper.php';

logoutUser();

header("Location: login.php?msg=logged_out");
exit();
