<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");  // Or your frontend domain
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (isset($_SESSION['user'])) {
    echo json_encode(["authenticated" => true, "user" => $_SESSION['user']]);
} else {
    echo json_encode(["authenticated" => false]);
}
?>
