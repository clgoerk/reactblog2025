<?php
  session_start();

  header("Access-Control-Allow-Origin: http://localhost:3000");
  header("Access-Control-Allow-Credentials: true");
  header("Access-Control-Allow-Methods: POST, OPTIONS");
  header("Access-Control-Allow-Headers: Content-Type");
  header("Content-Type: application/json");

  require_once('../config/config.php');
  require_once('../config/database.php');
  
  if (!isset($_SESSION['user'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

  // define configuration options
  $allowedMethods = ['GET'];
  $maxPostsPerPage = 4;

  // implement basic pagination
  $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $offset = ($page - 1) * $maxPostsPerPage;

  // query to count total posts
  $countQuery = "SELECT COUNT(*) as total FROM blog_posts";
  $countResult = mysqli_query($conn, $countQuery);
  $countRow = mysqli_fetch_assoc($countResult);
  $totalPosts = $countRow['total'];

  // check if posts query is successful
  if (!$countResult) {
    http_response_code(500);
    echo json_encode(['message' => 'Error fetching posts count: ' . mysqli_error($conn)]);
    mysqli_close($conn);
    exit();
  } 
  // query to get posts with pagination and ordering
  $query = "SELECT * FROM blog_posts ORDER BY publish_date DESC LIMIT $offset, $maxPostsPerPage";
  $result = mysqli_query($conn, $query);

  // check if posts query is successful
  if (!$result) {
    http_response_code(500);
    echo json_encode(['message' => 'Error fetching posts: ' . mysqli_error($conn)]);
    mysqli_close($conn);
    exit();
  } 

  // convert query result to associative array
  $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

  // check if posts are found
  if (empty($posts)) {
    http_response_code(404); // Not Found Error
    echo json_encode(['message' => 'No posts found', 'totalPosts' => $totalPosts]);
  } else {
    // return posts as JSON
    http_response_code(200); // OK
    echo json_encode(['posts' => $posts, 'totalPosts' => $totalPosts]);
  }
  // close database connection
  mysqli_close($conn); 
?>