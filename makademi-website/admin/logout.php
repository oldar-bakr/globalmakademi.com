<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

// Logout must be POST + CSRF so a third-party site can't force a sign-out
// via <img src="…/logout.php">.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
csrf_check();
admin_logout();
header('Location: login.php');
exit;
