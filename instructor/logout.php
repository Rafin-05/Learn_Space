<?php
require_once __DIR__ . '/../includes/db.php';
session_start();
session_destroy();
header('Location: login.php');
exit;
