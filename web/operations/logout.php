<?php
require __DIR__.'/common.php';
unset($_SESSION['ops_user']);
header('Location: /operations/login.php');
exit;
