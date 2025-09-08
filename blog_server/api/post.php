<?php
   session_start();
   
   header("Access-Control-Allow-Origin: http://localhost:3000");  // Or your frontend domain
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Credentials: true");
    header("Content-Type: application/json");

   // Load configuration files
   require_once('../config/config.php');
   require_once('../config/database.php');

   // 🔒 Require authentication
    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Unauthorized"]);
        exit;
    }

   if ($_SERVER['REQUEST_METHOD'] === 'GET')
   {
    $requestUri = $_SERVER['REQUEST_URI'];
    $parts = explode('/', $requestUri);
    $id = end($parts);

    $query = "SELECT bp.*,
        (SELECT COUNT(*) FROM post_votes WHERE post_id = bp.id AND vote_type = 'like') AS numLikes,
        (SELECT COUNT(*) FROM post_votes WHERE post_id = bp.id AND vote_type = 'dislike') AS numDislikes
        FROM blog_posts AS bp WHERE bp.id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1)
    {
        $post = $result->fetch_assoc();

        $response = [
            'status' => 'success',
            'data' => [
                'id' => $post['id'],
                'title' => $post['title'],
                'content' => $post['content'],
                'author' => $post['author'],
                'date' => date("l jS \of F Y", strtotime($post['publish_date'])),
                'likes' => $post['numLikes'],
                'dislikes' => $post['numDislikes'],
                'imageName' => $post['imageName'] ?? null
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($response);

    }
    else
    {
        $response = [
            'status' => 'error',
            'message' => 'Post not found'
        ];

        header('Content-Type: application/json');
        echo json_encode($response);

    }

    $stmt->close();
    $conn->close();

   }
?>