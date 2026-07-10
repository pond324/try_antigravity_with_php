<?php
/**
 * logout.php - ออกจากระบบ
 * UDRU E-Sports Club Portal
 */
session_start();
session_destroy();
header('Location: index.php');
exit;
