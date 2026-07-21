<?php
require 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    exit("Unauthorized Access");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Explicitly update only the single clicked incident row
    $stmt = $conn->prepare("UPDATE incidents SET status = 'resolved' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}
?>