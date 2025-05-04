<?php
// File for handling AJAX image uploads from TinyMCE editor

// Include database configuration if needed
include 'config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in (add your authentication check here)
// if (!isset($_SESSION['admin_id'])) {
//     header('HTTP/1.1 401 Unauthorized');
//     echo json_encode(['error' => 'Not authorized']);
//     exit;
// }

// Define upload directory
$upload_dir = 'assets';

// Create directory if it doesn't exist
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Response array
$response = ['location' => '', 'error' => ''];

// Check if file was uploaded
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_name = basename($_FILES['file']['name']);
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Generate unique filename
    $new_file_name = uniqid() . '.' . $file_ext;
    $target_file = $upload_dir . $new_file_name;
    
    // Validate file
    $check = getimagesize($file_tmp);
    if ($check !== false) {
        // Check file size (5MB limit)
        if ($_FILES['file']['size'] < 5000000) {
            // Allow certain file formats
            if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                // Upload file
                if (move_uploaded_file($file_tmp, $target_file)) {
                    $response['location'] = $target_file;
                } else {
                    $response['error'] = 'Failed to move uploaded file.';
                }
            } else {
                $response['error'] = 'Only JPG, JPEG, PNG & GIF files are allowed.';
            }
        } else {
            $response['error'] = 'File is too large. Maximum size is 5MB.';
        }
    } else {
        $response['error'] = 'File is not an image.';
    }
} else {
    $response['error'] = 'No file uploaded or upload error occurred.';
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>