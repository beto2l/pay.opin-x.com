<?php
require_once dirname(__DIR__) . '/scripts/admin-auth.php';
admin_logout();
header('Location: index.php');
exit;
