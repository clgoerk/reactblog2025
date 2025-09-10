<?php
session_start();

// CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
  http_response_code(200);
  exit();
}

/* ---- Load .env first ---- */
require_once __DIR__ . '/../vendor/autoload.php';             // adjust if vendor lives elsewhere
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));   // project root that contains .env
$dotenv->load();

require_once('../config/config.php');
require_once('../config/database.php');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['userName'], $data['password'], $data['emailAddress'], $data['role'])) {
  echo json_encode(["success" => false, "message" => "Missing fields"]);
  exit;
}

$userName     = mysqli_real_escape_string($conn, $data['userName']);
$emailAddress = mysqli_real_escape_string($conn, $data['emailAddress']);
$passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
$role         = mysqli_real_escape_string($conn, $data['role']);

/* ---- Read secret from env ---- */
$adminSecret = $_ENV['ADMIN_SECRET'] ?? getenv('ADMIN_SECRET') ?? '';

/* ---- If admin role, validate provided secretKey against env ---- */
if ($role === 'admin') {
  $provided = $data['secretKey'] ?? '';
  // timing-safe comparison
  if ($adminSecret === '' || !hash_equals($adminSecret, (string)$provided)) {
    echo json_encode(["success" => false, "message" => "Invalid admin secret"]);
    exit;
  }
}

/* ---- Uniqueness check ---- */
$check = $conn->prepare("SELECT registrationID FROM registrations WHERE userName = ? OR emailAddress = ?");
$check->bind_param("ss", $userName, $emailAddress);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
  echo json_encode(["success" => false, "message" => "Username or email already taken"]);
  $check->close();
  exit;
}
$check->close();

/* ---- Insert ---- */
$stmt = $conn->prepare("INSERT INTO registrations (userName, password, emailAddress, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $userName, $passwordHash, $emailAddress, $role);

if ($stmt->execute()) {
  echo json_encode(["success" => true, "message" => "Registration successful"]);
} else {
  echo json_encode(["success" => false, "message" => "Registration failed"]);
}

$stmt->close();
$conn->close();
