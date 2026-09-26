<?php
require __DIR__.'/common.php';
unset($_SESSION['ops_user']);
$_SESSION['ops_restore_suppressed']=true;
header('Location: /operations/login.php');
exit;
