<?php

session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 🔒 Require authentication
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}
 


// Load configuration files
require_once('../config/config.php');
require_once('../config/database.php');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get raw POST data (JSON)
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['post_id']) || !isset($data['vote_type'])) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request. post_id and vote_type are required.'
        ]);
        exit;
    }

    $postId = intval($data['post_id']);
    $voteType = $data['vote_type'];
    $userIp = $_SERVER['REMOTE_ADDR'];

    if ($voteType !== 'like' && $voteType !== 'dislike') {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid vote_type. Must be "like" or "dislike".'
        ]);
        exit;
    }

    // Check if user already voted on this post
    $query = "SELECT id FROM post_votes WHERE post_id = ? AND user_ip = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $postId, $userIp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing vote
        $row = $result->fetch_assoc();
        $voteId = $row['id'];

        $updateQuery = "UPDATE post_votes SET vote_type = ?, updated_at = NOW() WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("si", $voteType, $voteId);
        $updateStmt->execute();
        $updateStmt->close();

        $response = [
            'status' => 'success',
            'message' => 'Vote updated successfully.'
        ];
    } else {
        // Insert new vote
        $insertQuery = "INSERT INTO post_votes (post_id, user_ip, vote_type, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("iss", $postId, $userIp, $voteType);
        $insertStmt->execute();
        $insertStmt->close();

        $response = [
            'status' => 'success',
            'message' => 'Vote recorded successfully.'
        ];
    }

    $stmt->close();
    $conn->close();

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else {
    // Invalid request method
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed. Use POST.'
    ]);
    exit;
}
