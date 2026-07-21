<?php
// fetch_metrics.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db.php';

header('Content-Type: application/json');

// Query total counts directly from database
$totalQuery = $conn->query("SELECT COUNT(*) AS count FROM incidents");
$total = $totalQuery ? $totalQuery->fetch_assoc()['count'] : 0;

$pendingQuery = $conn->query("SELECT COUNT(*) AS count FROM incidents WHERE status = 'pending'");
$pending = $pendingQuery ? $pendingQuery->fetch_assoc()['count'] : 0;

$resolvedQuery = $conn->query("SELECT COUNT(*) AS count FROM incidents WHERE status = 'resolved'");
$resolved = $resolvedQuery ? $resolvedQuery->fetch_assoc()['count'] : 0;

echo json_encode([
    'total' => (int)$total,
    'pending' => (int)$pending,
    'resolved' => (int)$resolved
]);
?>