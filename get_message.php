<?php
include 'config.php'; // Include database configuration

// Set header to return JSON
header('Content-Type: application/json');

// Initialize response array
$response = array();

// Check if ID is provided and is numeric
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $message_id = intval($_GET['id']);
    
    // Create database connection
    $conn = new mysqli($host, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        $response['error'] = "Connection failed: " . $conn->connect_error;
        echo json_encode($response);
        exit();
    }
    
    // Prepare and execute query
    $stmt = $conn->prepare("SELECT * FROM contact_inquiries WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if message exists
    if ($result->num_rows > 0) {
        // Fetch message data
        $message = $result->fetch_assoc();
        
        // Sanitize the output
        foreach ($message as $key => $value) {
            $message[$key] = htmlspecialchars($value ?? '');
        }
        
        // Return message data
        echo json_encode($message);
    } else {
        $response['error'] = "Message not found";
        echo json_encode($response);
    }
    
    $stmt->close();
    $conn->close();
} else {
    $response['error'] = "Invalid message ID";
    echo json_encode($response);
}

/**
 * Helper function to sanitize output
 */
function sanitize_output($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitize_output($value);
        }
        return $data;
    } else {
        return htmlspecialchars($data ?? '');
    }
}
?>