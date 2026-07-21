<?php
// update_to_waiting.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db.php';

// Access Firewall Validation
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit("Unauthorized context access.");
}

// Validate target payload parameter
if (isset($_GET['id'])) {
    $incident_id = $_GET['id'];

    // Update query to alter the status value flags. 
    // Adjust your column name (e.g., 'status') to match your actual database schema.
    $query = "UPDATE incidents SET status = 'waiting' WHERE id = ? AND status != 'waiting'";
    
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $incident_id); // Assuming ID is an integer; use "s" if it's a string/UUID
        
        if ($stmt->execute()) {
            http_response_code(200);
            echo "Status updated successfully.";
        } else {
            http_response_code(500);
            echo "Database execution error.";
        }
        $stmt->close();
    } else {
        http_response_code(500);
        echo "Statement preparation failure.";
    }
} else {
    http_response_code(400);
    echo "Bad Request: Missing incident ID parameter.";
}
$conn->close();
?>