<?php
$host = '127.0.0.1'; // Using 127.0.0.1 forces TCP/IP instead of a socket connection
$user = 'root';
$pass = '';
$dbname = 'emergency_db';
$port = 3306;        // Defined your custom port explicitly here

// Added the $port variable as the 5th parameter
$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Make sure this is present at the very end of the file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>