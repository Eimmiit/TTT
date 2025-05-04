<?php
include 'config.php'; // Include database configuration

// Set header to return JSON
header('Content-Type: application/json');

// Check if ID is provided and is numeric
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid message ID']);
    exit;
}

$message_id = intval($_GET['id']);

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Prepare statement to prevent SQL injection
$stmt = $conn->prepare("UPDATE contact_inquiries SET status = 'read' WHERE id = ?");
$stmt->bind_param("i", $message_id);

$result = $stmt->execute();

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Message marked as read']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update message status']);
}

$stmt->close();
$conn->close();