<?php
require 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    exit("Unauthorized Access");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Checks for both lowercase 'resolved' and uppercase 'RESOLVED' out of safety
    $stmt = $conn->prepare("DELETE FROM incidents WHERE id = ? AND (status = 'resolved' OR status = 'RESOLVED')");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}
?>