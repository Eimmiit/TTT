<?php
include 'config.php'; // Include database configuration
// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Mark a message as read
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $message_id = intval($_GET['mark_read']);
    $stmt = $conn->prepare("UPDATE contact_inquiries SET status = 'read' WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    
    if ($stmt->execute()) {
        // Redirect to avoid resubmission on refresh
        header("Location: messages.php");
        exit();
    } else {
        $error_message = "Error updating message: " . $conn->error;
    }
    $stmt->close();
}

// Add reply to database and send email
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reply_submit'])) {
    // Sanitize inputs
    $reply_to_id = intval($_POST['reply_to_id']);
    $reply_to_email = sanitize_input($_POST['reply_to_email']);
    $reply_subject = sanitize_input($_POST['reply_subject']);
    $reply_message = sanitize_input($_POST['reply_message']);
    
    // Add notes to the inquiry
    $reply_date = date('Y-m-d H:i:s');
    $notes_content = "\n\n--- Reply sent on " . $reply_date . " ---\n" . $reply_message;
    
    // Prepare statement
    $notes_sql = "UPDATE contact_inquiries SET 
                  notes = CASE 
                      WHEN notes IS NULL OR notes = '' THEN ? 
                      ELSE CONCAT(notes, ?) 
                  END, 
                  status = 'replied' 
                  WHERE id = ?";
    
    $stmt = $conn->prepare($notes_sql);
    
    if ($stmt) {
        // If notes is null/empty, use just the new content; otherwise, append
        $stmt->bind_param("ssi", $notes_content, $notes_content, $reply_to_id);
        
        if ($stmt->execute()) {
            // In a real application, you would send an email here
            // Example:
            /*
            $headers = "From: your-email@yourdomain.com\r\n";
            $headers .= "Reply-To: your-email@yourdomain.com\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            mail($reply_to_email, $reply_subject, $reply_message, $headers);
            */
            
            // Set a success message in session
            session_start();
            $_SESSION['message'] = "Reply sent successfully to $reply_to_email";
            
            // Redirect to avoid resubmission on refresh
            header("Location: messages.php");
            exit();
        } else {
            $error_message = "Error sending reply: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Error preparing statement: " . $conn->error;
    }
}

// Initialize variables for pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Initialize variables for filtering
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : 'all';

// Build the WHERE clause for filtering
$where_clause = "1=1"; // Always true condition to start

if (!empty($search)) {
    $where_clause .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR 
                      service_interest LIKE '%$search%' OR message LIKE '%$search%')";
}

if ($status_filter != 'all') {
    $where_clause .= " AND status = '$status_filter'";
}

// Count total filtered messages
$count_sql = "SELECT COUNT(*) as total FROM contact_inquiries WHERE $where_clause";
$count_result = $conn->query($count_sql);
$count_row = $count_result->fetch_assoc();
$total_messages = $count_row['total'];
$total_pages = ceil($total_messages / $items_per_page);

// Count messages by status
$status_counts_sql = "SELECT status, COUNT(*) as count FROM contact_inquiries GROUP BY status";
$status_counts_result = $conn->query($status_counts_sql);
$status_counts = [
    'total' => 0,
    'new' => 0,
    'read' => 0,
    'replied' => 0
];

while ($row = $status_counts_result->fetch_assoc()) {
    $status_counts[$row['status']] = $row['count'];
    $status_counts['total'] += $row['count'];
}

// Fetch messages with pagination and filtering
$sql = "SELECT * FROM contact_inquiries 
        WHERE $where_clause
        ORDER BY submission_date DESC 
        LIMIT $offset, $items_per_page";

$result = $conn->query($sql);
$messages = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}

// Check for flash messages

$flash_message = '';
if (isset($_SESSION['message'])) {
    $flash_message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages Dashboard | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar - Hidden on mobile by default -->
        <div id="sidebar" class="hidden md:block w-64 bg-gray-800 text-white flex-shrink-0">
            <div class="p-4 border-b border-gray-700">
                <h2 class="flex items-center">
                    <img src="assets/logo.png" alt="Logo" class="h-8 mr-2">
                    <span class="font-semibold text-xl">Admin</span>
                </h2>
            </div>
            <div class="py-4">
                <a href="admin.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span class="ml-3">Dashboard</span>
                </a>
                <a href="articles.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white">
                    <i class="fas fa-newspaper w-5"></i>
                    <span class="ml-3">Articles</span>
                </a>
                <a href="courses.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white">
                    <i class="fas fa-book w-5"></i>
                    <span class="ml-3">Courses</span>
                </a>
                <a href="registrations.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white">
                    <i class="fas fa-users w-5"></i>
                    <span class="ml-3">Registrations</span>
                </a>
                <a href="messages.php" class="flex items-center px-6 py-3 bg-gray-700 text-white">
                    <i class="fas fa-envelope w-5"></i>
                    <span class="ml-3">Messages</span>
                </a>
                <a href="settings.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white">
                    <i class="fas fa-cog w-5"></i>
                    <span class="ml-3">Settings</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-white shadow">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center">
                        <button id="sidebarToggle" class="md:hidden text-gray-600 focus:outline-none">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h1 class="ml-4 text-xl font-semibold">Messages Dashboard</h1>
                    </div>
                    <div class="flex items-center">
                        <div class="relative">
                            <button class="flex items-center text-gray-600 focus:outline-none">
                                <img src="assets/admin-avatar.png" alt="Admin" class="h-8 w-8 rounded-full mr-2">
                                <span class="hidden md:inline-block">Admin User</span>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <?php if (!empty($flash_message)): ?>
                <div id="flashMessage" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p><?php echo $flash_message; ?></p>
                        </div>
                        <div class="ml-auto">
                            <button onclick="document.getElementById('flashMessage').style.display='none'" class="text-green-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                <div id="errorMessage" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p><?php echo $error_message; ?></p>
                        </div>
                        <div class="ml-auto">
                            <button onclick="document.getElementById('errorMessage').style.display='none'" class="text-red-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="container mx-auto">
                    <!-- Messages Overview Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                                    <i class="fas fa-envelope fa-2x"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold">Total Messages</h3>
                                    <p class="text-2xl font-bold"><?php echo $status_counts['total']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                                    <i class="fas fa-bell fa-2x"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold">New Messages</h3>
                                    <p class="text-2xl font-bold"><?php echo $status_counts['new']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-green-100 text-green-500">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold">Read Messages</h3>
                                    <p class="text-2xl font-bold"><?php echo $status_counts['read']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-purple-100 text-purple-500">
                                    <i class="fas fa-reply fa-2x"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold">Replied</h3>
                                    <p class="text-2xl font-bold"><?php echo $status_counts['replied']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Messages Table -->
                    <div class="bg-white p-6 rounded-lg shadow mb-6">
                        <div class="flex flex-col md:flex-row justify-between items-center mb-4">
                            <h2 class="text-xl font-semibold mb-4 md:mb-0">All Messages</h2>
                            <form method="GET" action="messages.php" class="flex flex-col md:flex-row items-center w-full md:w-auto">
                                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search messages..." class="border rounded-lg px-3 py-2 mb-2 md:mb-0 md:mr-2 w-full md:w-auto focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <select name="status" class="border rounded-lg px-3 py-2 mb-2 md:mb-0 md:mr-2 w-full md:w-auto focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                                    <option value="new" <?php echo $status_filter == 'new' ? 'selected' : ''; ?>>New</option>
                                    <option value="read" <?php echo $status_filter == 'read' ? 'selected' : ''; ?>>Read</option>
                                    <option value="replied" <?php echo $status_filter == 'replied' ? 'selected' : ''; ?>>Replied</option>
                                </select>
                                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg w-full md:w-auto">
                                    <i class="fas fa-search mr-1"></i> Filter
                                </button>
                            </form>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="px-4 py-2 border">Name</th>
                                        <th class="px-4 py-2 border">Email</th>
                                        <th class="px-4 py-2 border">Service Interest</th>
                                        <th class="px-4 py-2 border">Message</th>
                                        <th class="px-4 py-2 border">Status</th>
                                        <th class="px-4 py-2 border">Date</th>
                                        <th class="px-4 py-2 border">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($messages)): ?>
                                        <tr>
                                            <td colspan="7" class="px-4 py-2 text-center text-gray-500">No messages found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($messages as $message): ?>
                                            <tr class="<?php echo $message['status'] === 'new' ? 'bg-blue-50' : ''; ?> hover:bg-gray-50">
                                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($message['name']); ?></td>
                                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($message['email']); ?></td>
                                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($message['service_interest']); ?></td>
                                                <td class="px-4 py-2 border"><?php echo htmlspecialchars(substr($message['message'], 0, 40)) . (strlen($message['message']) > 40 ? '...' : ''); ?></td>
                                                <td class="px-4 py-2 border">
                                                    <?php 
                                                        $status_class = 'bg-gray-100 text-gray-800';
                                                        if ($message['status'] === 'new') {
                                                            $status_class = 'bg-yellow-100 text-yellow-800';
                                                        } elseif ($message['status'] === 'read') {
                                                            $status_class = 'bg-green-100 text-green-800';
                                                        } elseif ($message['status'] === 'replied') {
                                                            $status_class = 'bg-purple-100 text-purple-800';
                                                        }
                                                    ?>
                                                    <span class="px-2 py-1 rounded <?php echo $status_class; ?>">
                                                        <?php echo ucfirst($message['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2 border"><?php echo date('M d, Y H:i', strtotime($message['submission_date'])); ?></td>
                                                <td class="px-4 py-2 border">
                                                <button class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-sm view-message" 
                                                        data-id="<?php echo $message['id']; ?>">
                                                    <i class="fas fa-eye"></i> View
                                                </button>  
                                                    <?php if ($message['status'] === 'new'): ?>
                                                        <a href="?mark_read=<?php echo $message['id']; ?>" class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-sm ml-1">
                                                            <i class="fas fa-check"></i> Mark Read
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <div class="mt-4 flex flex-col md:flex-row justify-between items-center">
                            <div class="text-gray-500 text-sm mb-2 md:mb-0">
                                Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $items_per_page, $total_messages); ?> of <?php echo $total_messages; ?> messages
                            </div>
                            <div class="flex">
                                <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" class="px-3 py-1 border rounded-l-lg bg-gray-100 text-gray-600 hover:bg-gray-200">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php
                                // Display page numbers
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                for ($i = $start_page; $i <= $end_page; $i++):
                                ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" class="px-3 py-1 border-t border-b border-r <?php echo $i === $page ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">
                                    <?php echo $i; ?>
                                </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" class="px-3 py-1 border-t border-b border-r rounded-r-lg bg-gray-100 text-gray-600 hover:bg-gray-200">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Message View Modal -->
    <div id="messageModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl mx-4 max-h-screen overflow-y-auto">
            <div class="p-6 border-b sticky top-0 bg-white">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold">Message Details</h3>
                    <button id="closeModal" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="p-6" id="modalContent">
                <!-- Content will be loaded dynamically -->
                <div class="animate-pulse">
                    <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2 mb-4"></div>
                    <div class="h-32 bg-gray-200 rounded mb-4"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
      // Direct message viewing implementation
document.addEventListener('DOMContentLoaded', function() {
    const messageModal = document.getElementById('messageModal');
    const modalContent = document.getElementById('modalContent');
    const closeModal = document.getElementById('closeModal');
    const viewButtons = document.querySelectorAll('.view-message');
    
    // Handle the sidebar toggle functionality
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('hidden');
        });
    }
    
    // Close modal when clicking the close button
    if (closeModal) {
        closeModal.addEventListener('click', function() {
            messageModal.classList.add('hidden');
        });
    }
    
    // Close modal when clicking outside the modal content
    messageModal.addEventListener('click', function(e) {
        if (e.target === messageModal) {
            messageModal.classList.add('hidden');
        }
    });
    
    // Function to display message details directly without AJAX
    function displayMessageDetails(messageId) {
        // Show the modal with loading state
        messageModal.classList.remove('hidden');
        
        // Simple loading indicator
        modalContent.innerHTML = `
            <div class="text-center p-10">
                <i class="fas fa-spinner fa-spin fa-3x"></i>
                <p class="mt-4">Loading message details...</p>
            </div>
        `;
        
        // Create a direct form submission to get the message data
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'view_message_direct.php'; // New PHP file we'll create
        form.target = 'temp_iframe';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'message_id';
        input.value = messageId;
        
        form.appendChild(input);
        document.body.appendChild(form);
        
        // Create a hidden iframe to receive the response
        let iframe = document.getElementById('temp_iframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.name = 'temp_iframe';
            iframe.id = 'temp_iframe';
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
            
            // Add event listener to process the iframe content when it loads
            iframe.addEventListener('load', function() {
                try {
                    // Get the content from the iframe
                    const iframeContent = iframe.contentDocument || iframe.contentWindow.document;
                    const responseText = iframeContent.body.innerHTML;
                    
                    // Update the modal content
                    modalContent.innerHTML = responseText;
                    
                    // Set up the reply form validation
                    const replyForm = document.getElementById('replyForm');
                    if (replyForm) {
                        replyForm.addEventListener('submit', function(e) {
                            const subject = document.getElementById('reply_subject').value.trim();
                            const message = document.getElementById('reply_message').value.trim();
                            
                            if (subject === '' || message === '') {
                                e.preventDefault();
                                alert('Please fill out both subject and message fields.');
                            }
                        });
                    }
                } catch (error) {
                    console.error('Error processing iframe content:', error);
                    modalContent.innerHTML = `
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
                            <p>Error loading message details. Please try again.</p>
                        </div>
                    `;
                }
            });
        }
        
        // Submit the form to the hidden iframe
        form.submit();
        document.body.removeChild(form);
    }
    
    // Add click handlers to all view buttons
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const messageId = this.getAttribute('data-id');
            displayMessageDetails(messageId);
        });
    });
    
    // Auto-hide flash messages after 5 seconds
    const flashMessage = document.getElementById('flashMessage');
    const errorMessage = document.getElementById('errorMessage');
    
    if (flashMessage) {
        setTimeout(() => {
            flashMessage.style.display = 'none';
        }, 5000);
    }
    
    if (errorMessage) {
        setTimeout(() => {
            errorMessage.style.display = 'none';
        }, 5000);
    }
});
    </script>
</body>
</html>