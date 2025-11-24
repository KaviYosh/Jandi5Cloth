<?php
// Start the session
session_start();

// Set the content type to JSON
header('Content-Type: application/json');

// Invalidate the JWT token by removing it from the client-side
if (isset($_COOKIE['jwt'])) {
    // Unset the JWT cookie
    setcookie('jwt', '', time() - 3600, '/'); // Set expiration time to the past
    unset($_COOKIE['jwt']);
}

// Destroy the session
session_destroy();

// Return a success response
echo json_encode([
    'success' => true,
    'message' => 'Logout successful'
]);
?>