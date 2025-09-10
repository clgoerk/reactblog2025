<?php
session_start();

// CORS headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

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

require_once('../config/config.php');
require_once('../config/database.php');

// Only allow POST for delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Accept both JSON and form data
    $rawInput = file_get_contents("php://input");
    $data = json_decode($rawInput, true);

    if (isset($data['id'])) {
        $id = intval($data['id']);
    } elseif (isset($_POST['id'])) {
        $id = intval($_POST['id']);
    } else {
        $id = 0;
    }

    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Post deleted successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Failed to delete post."]);
        }

        $stmt->close();
    } else {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Invalid post ID."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}

$conn->close();
?>

