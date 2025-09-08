<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

require_once('../config/config.php');
require_once('../config/database.php');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['userName'], $data['password'])) {
    echo json_encode(["success" => false, "message" => "Missing fields"]);
    exit;
}

$userName = $data['userName'];
$password = $data['password'];

// Lookup user
$stmt = $conn->prepare("SELECT registrationID, userName, password, emailAddress FROM registrations WHERE userName = ?");
$stmt->bind_param("s", $userName);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password'])) {
        $_SESSION['user'] = [
            "registrationID" => $row['registrationID'],
            "userName" => $row['userName'],
            "emailAddress" => $row['emailAddress']
        ];
        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "user" => $_SESSION['user']  // send back user
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Invalid password"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "User not found"]);
}

$stmt->close();
$conn->close();
?>
