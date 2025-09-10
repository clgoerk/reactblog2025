<?php
// ---- CORS config ----
$allowedOrigins = ['http://localhost:3000'];
$allowedMethods = ['GET', 'POST', 'OPTIONS'];
$allowedHeaders = ['Content-Type'];

// Detect origin
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins, true)) {
  header('Access-Control-Allow-Origin: ' . $origin);
  header('Access-Control-Allow-Credentials: true');   // needed for cookies
  header('Vary: Origin');                             // caching correctness
}

// Always advertise what we allow (esp. for preflight)
header('Access-Control-Allow-Methods: ' . implode(', ', $allowedMethods));
header('Access-Control-Allow-Headers: ' . implode(', ', $allowedHeaders));

// Handle preflight early
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
  http_response_code(200); // or 204
  exit();
}

// (Optional) Default content type for APIs
header('Content-Type: application/json');