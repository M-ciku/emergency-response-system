<?php
// acknowledge_medic.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db.php';

// Safe authentication check
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Update incident status in database
    $stmt = $conn->prepare("UPDATE incidents SET status = 'resolved' WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo "ok";
    } else {
        http_response_code(500);
        echo "Database update error: " . $stmt->error;
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo "Missing ID parameter";
}
?>