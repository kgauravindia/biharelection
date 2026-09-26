<?php
/**
 * BiharElection.com - Public User Logout & Impersonation Exit Handler
 */
require_once __DIR__ . '/includes/auth_helper.php';

$wasImpersonated = !empty($_SESSION['impersonated_by_admin']) || isset($_GET['exit_impersonation']);
logoutUser();

if ($wasImpersonated) {
    header("Location: admin/citizens.php?msg=impersonation_ended");
    exit();
}

header("Location: login.php?msg=logged_out");
exit();
