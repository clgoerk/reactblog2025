<?php

session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");  // frontend origin
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

require_once('../config/config.php');
require_once('../config/database.php');

// 🔒 Require authentication (same as class example)
if (!isset($_SESSION['user'])) {
  http_response_code(401);
  echo json_encode(["success" => false, "message" => "Unauthorized"]);
  exit;
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

// Validate required POST fields (multipart/form-data)
if (!isset($_POST['location'], $_POST['start_time'], $_POST['end_time'])) {
  http_response_code(400);
  echo json_encode(['message' => 'Missing required fields']);
  exit();
}

// Sanitize input
$location   = filter_var($_POST['location'], FILTER_SANITIZE_STRING);
$start_time = filter_var($_POST['start_time'], FILTER_SANITIZE_STRING);
$end_time   = filter_var($_POST['end_time'], FILTER_SANITIZE_STRING);

// --- Handle image upload (same as class example) ---
$uploadDir = __DIR__ . "/uploads/";
$imageName = "placeholder_100.jpg"; // default placeholder

// Ensure upload directory exists
if (!is_dir($uploadDir)) {
  @mkdir($uploadDir, 0755, true);
}

if (!empty($_FILES['image']['name'])) {
  $originalName   = basename($_FILES['image']['name']);
  $targetFilePath = $uploadDir . $originalName;

  // Block overwrite (exactly like class example)
  if (file_exists($targetFilePath)) {
    http_response_code(400);
    echo json_encode(['message' => 'File already exists: ' . $originalName]);
    exit();
  }

  if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
    http_response_code(500);
    echo json_encode([
      'message'   => 'Error uploading file',
      'php_error' => $_FILES['image']['error'] ?? 'unknown'
    ]);
    exit();
  }

  $imageName = $originalName; // replace placeholder with uploaded file name
}

// Insert into database (reservations table)
$stmt = $conn->prepare(
  'INSERT INTO reservations (location, start_time, end_time, image_name) VALUES (?, ?, ?, ?)'
);
$stmt->bind_param('ssss', $location, $start_time, $end_time, $imageName);

if ($stmt->execute()) {
  $id = $stmt->insert_id;
  http_response_code(201);
  echo json_encode([
    'message'   => 'Reservation created successfully',
    'id'        => $id,
    'imageName' => $imageName
  ]);
} else {
  http_response_code(500);
  echo json_encode(['message' => 'Error creating reservation: ' . $stmt->error]);
}

$stmt->close();
$conn->close();

?>