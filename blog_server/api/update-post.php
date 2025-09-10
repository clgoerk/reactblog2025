<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once('../config/config.php');
require_once('../config/database.php');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Require authentication
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}

// Only allow admin
if ($_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Forbidden: Admins only"]);
    exit();
}

// Validate POST data
if (!isset($_POST['id'], $_POST['title'], $_POST['content'], $_POST['author'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Missing required fields']);
    exit();
}

$id = intval($_POST['id']);
$title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
$content = filter_var($_POST['content'], FILTER_SANITIZE_STRING);
$author = filter_var($_POST['author'], FILTER_SANITIZE_STRING);

// Handle optional image upload
$uploadDir = __DIR__ . "/uploads/";
$imageName = null;

if (!empty($_FILES['image']['name'])) {
    $originalName = basename($_FILES['image']['name']);
    $targetFilePath = $uploadDir . $originalName;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
        http_response_code(500);
        echo json_encode(['message' => 'Error uploading file', 'php_error' => $_FILES['image']['error']]);
        exit();
    }

    $imageName = $originalName;
}

// Build SQL update statement
if ($imageName) {
    $stmt = $conn->prepare("UPDATE blog_posts SET title=?, content=?, author=?, imageName=? WHERE id=?");
    $stmt->bind_param("ssssi", $title, $content, $author, $imageName, $id);
} else {
    $stmt = $conn->prepare("UPDATE blog_posts SET title=?, content=?, author=? WHERE id=?");
    $stmt->bind_param("sssi", $title, $content, $author, $id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Post updated successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error updating post: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
