<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

$id = intval($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

if ($id && in_array($action, ['approve','reject'])) {
    $status = $action === 'approve' ? 'approved' : 'rejected';
    $stmt = $conn->prepare("UPDATE courses SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    header("Location: pending-courses.php?msg=" . $status);
    exit;
}

header('Location: courses.php');
exit;
