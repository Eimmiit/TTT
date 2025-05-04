<?php
include 'config.php'; // Include database configuration

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sanitize function to prevent SQL injection
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Get the message ID
$message_id = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;

if ($message_id <= 0) {
    echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4">';
    echo '<p>Invalid message ID.</p>';
    echo '</div>';
    exit;
}

// Prepare and execute the query
$stmt = $conn->prepare("SELECT * FROM contact_inquiries WHERE id = ?");
$stmt->bind_param("i", $message_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if message exists
if ($result->num_rows === 0) {
    echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4">';
    echo '<p>Message not found.</p>';
    echo '</div>';
    $stmt->close();
    $conn->close();
    exit;
}

// Get message data
$message = $result->fetch_assoc();
$stmt->close();

// Mark message as read if it's new
if ($message['status'] === 'new') {
    $update_stmt = $conn->prepare("UPDATE contact_inquiries SET status = 'read' WHERE id = ?");
    $update_stmt->bind_param("i", $message_id);
    $update_stmt->execute();
    $update_stmt->close();
    $message['status'] = 'read'; // Update local status
}

// Format date
$formatted_date = date('M d, Y H:i', strtotime($message['submission_date']));

// Determine status class for display
$status_class = 'bg-gray-100 text-gray-800';
if ($message['status'] === 'new') {
    $status_class = 'bg-yellow-100 text-yellow-800';
} elseif ($message['status'] === 'read') {
    $status_class = 'bg-green-100 text-green-800';
} elseif ($message['status'] === 'replied') {
    $status_class = 'bg-purple-100 text-purple-800';
}

// Close the database connection
$conn->close();
?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <div class="mb-4">
            <span class="font-semibold">Name:</span>
            <span class="ml-2"><?php echo htmlspecialchars($message['name']); ?></span>
        </div>
        <div class="mb-4">
            <span class="font-semibold">Email:</span>
            <span class="ml-2"><?php echo htmlspecialchars($message['email']); ?></span>
        </div>
        <div class="mb-4">
            <span class="font-semibold">Service Interest:</span>
            <span class="ml-2"><?php echo htmlspecialchars($message['service_interest']); ?></span>
        </div>
        <div class="mb-4">
            <span class="font-semibold">Date:</span>
            <span class="ml-2"><?php echo $formatted_date; ?></span>
        </div>
        <div class="mb-4">
            <span class="font-semibold">Status:</span>
            <span class="ml-2 px-2 py-1 rounded <?php echo $status_class; ?>">
                <?php echo ucfirst($message['status']); ?>
            </span>
        </div>
    </div>
    <div>
        <div class="mb-4">
            <span class="font-semibold">Message:</span>
            <div class="mt-2 p-4 bg-gray-50 rounded border whitespace-pre-line">
                <?php echo htmlspecialchars($message['message']); ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($message['notes'])): ?>
<div class="mt-6 border-t pt-4">
    <h4 class="font-semibold text-lg mb-2">Notes & Previous Replies</h4>
    <div class="bg-gray-50 p-4 rounded whitespace-pre-line">
        <?php echo nl2br(htmlspecialchars($message['notes'])); ?>
    </div>
</div>
<?php endif; ?>

<div class="mt-6 border-t pt-4">
    <h4 class="font-semibold text-lg mb-2">Reply to Message</h4>
    <form method="POST" action="messages.php" id="replyForm">
        <input type="hidden" name="reply_to_id" value="<?php echo $message['id']; ?>">
        <input type="hidden" name="reply_to_email" value="<?php echo htmlspecialchars($message['email']); ?>">
        
        <div class="mb-4">
            <label for="reply_subject" class="block text-gray-700 mb-1">Subject</label>
            <input type="text" id="reply_subject" name="reply_subject" 
                   value="RE: Inquiry about <?php echo htmlspecialchars($message['service_interest']); ?>" 
                   class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div class="mb-4">
            <label for="reply_message" class="block text-gray-700 mb-1">Message</label>
            <textarea id="reply_message" name="reply_message" rows="5" 
                      class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                      placeholder="Type your reply here..."></textarea>
        </div>
        
        <div class="flex justify-end">
            <button type="submit" name="reply_submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-paper-plane mr-1"></i> Send Reply
            </button>
        </div>
    </form>
</div>