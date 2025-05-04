<?php
include 'config.php';

// Create database connection
$conn = new mysqli($host, $username, $password, $dbname);
 // Check connection
 if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Check if a delete request is made
if (isset($_GET['delete_article']) && is_numeric($_GET['delete_article'])) {
    $article_id = intval($_GET['delete_article']);

    // Prepare and execute the delete query
    $sql = "DELETE FROM articles WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $article_id);

    if ($stmt->execute()) {
        // Redirect with a success message
        header("Location: admin.php?message=Article deleted successfully");
        exit();
    } else {
        // Handle errors
        $error_message = "Error deleting article: " . $conn->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Trauma to Triumph</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            light: '#6366f1',
                            DEFAULT: '#4f46e5',
                            dark: '#4338ca',
                        },
                        secondary: {
                            light: '#f8fafc',
                            DEFAULT: '#f1f5f9',
                            dark: '#e2e8f0',
                        },
                        dark: {
                            light: '#334155',
                            DEFAULT: '#1e293b',
                            dark: '#0f172a',
                        }
                    },
                    fontFamily: {
                        sans: ['Open Sans', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans">
    <?php
    // Include database configuration

   
    
    // Fetch dashboard statistics
    $stats = [
        'articles' => 0,
        'registrations' => 0,
        'messages' => 0,
        'visits' => 0
    ];
    
    // Count total articles
    $sql = "SELECT COUNT(*) as count FROM articles";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stats['articles'] = $row['count'];
    }
    
    // Count total course registrations
    $sql = "SELECT COUNT(*) as count FROM registrations";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stats['registrations'] = $row['count'];
    }
    
 // Count new messages (unread)
$sql = "SELECT COUNT(*) as count FROM contact_inquiries WHERE status = 'new'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stats['messages'] = $row['count'];
}
    
    // Get website visits (from a hypothetical visits table or analytics)
    $sql = "SELECT COUNT(*) as count FROM page_visits WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stats['visits'] = $row['count'];
    }
    
    // Fetch recent articles
    $recent_articles = [];
    $sql = "SELECT id, title, published_date, status FROM articles ORDER BY published_date DESC LIMIT 5";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $recent_articles[] = $row;
        }
    }
    
    // Fetch recent registrations
    $recent_registrations = [];
    $sql = "SELECT r.id, r.name, c.title as course_title, r.registration_date, r.status 
            FROM registrations r 
            JOIN courses c ON r.course_id = c.id 
            ORDER BY r.registration_date DESC LIMIT 5";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $recent_registrations[] = $row;
        }
    }
    ?>

    <div class="flex flex-col md:flex-row min-h-screen">
        <!-- Mobile Menu Toggle -->
        <div class="md:hidden bg-dark-dark text-white p-4 flex justify-between items-center">
            <h2 class="flex items-center">
                <img src="assets/logo.png" alt="Trauma to Triumph Logo" class="h-8 mr-2">
                <span class="font-semibold text-xl">Admin</span>
            </h2>
            <button id="mobileMenuToggle" class="text-white focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>

        <!-- Sidebar - Hidden on mobile by default -->
        <div id="sidebar" class="hidden md:block w-full md:w-64 bg-dark-dark text-white flex-shrink-0">
            <div class="p-4 border-b border-gray-700 hidden md:block">
                <h2 class="flex items-center">
                    <img src="assets/logo.png" alt="Trauma to Triumph Logo" class="h-8 mr-2">
                    <span class="font-semibold text-xl">Admin</span>
                </h2>
            </div>
            <div class="py-4">
                <a href="admin.php" class="flex items-center px-6 py-3 bg-dark-light text-white">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span class="ml-3">Dashboard</span>
                </a>
                <a href="create_article.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-dark-light hover:text-white">
                    <i class="fas fa-newspaper w-5"></i>
                    <span class="ml-3">Articles</span>
                </a>
                <a href="courses.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-dark-light hover:text-white">
                    <i class="fas fa-book w-5"></i>
                    <span class="ml-3">Courses</span>
                </a>
                <a href="registrations.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-dark-light hover:text-white">
                    <i class="fas fa-users w-5"></i>
                    <span class="ml-3">Registrations</span>
                </a>
                <a href="messages.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-dark-light hover:text-white">
                    <i class="fas fa-envelope w-5"></i>
                    <span class="ml-3">Messages</span>
                </a>
                <a href="settings.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-dark-light hover:text-white">
                    <i class="fas fa-cog w-5"></i>
                    <span class="ml-3">Settings</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <div class="bg-white shadow px-4 md:px-6 py-4 flex justify-between items-center">
                <h1 class="text-lg md:text-xl font-semibold">Admin Dashboard</h1>
                <div class="flex items-center">
                    <?php
                    // Get admin info from session or database
                    $admin_initials = isset($_SESSION['admin_initials']) ? $_SESSION['admin_initials'] : 'AD';
                    ?>
                    <div class="h-8 w-8 rounded-full bg-primary-dark text-white flex items-center justify-center">
                        <?php echo $admin_initials; ?>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="p-4 md:p-6 overflow-y-auto">
                <!-- Dashboard Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-md p-4 md:p-6 border-l-4 border-blue-500">
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Total Articles</h3>
                        <div class="flex items-center">
                            <span class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['articles']); ?></span>
                            <i class="fas fa-newspaper text-blue-500 ml-auto text-xl"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-4 md:p-6 border-l-4 border-green-500">
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Course Registrations</h3>
                        <div class="flex items-center">
                            <span class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['registrations']); ?></span>
                            <i class="fas fa-users text-green-500 ml-auto text-xl"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-4 md:p-6 border-l-4 border-yellow-500">
                        <h3 class="text-sm font-medium text-gray-500 mb-1">New Messages</h3>
                        <div class="flex items-center">
                            <span class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['messages']); ?></span>
                            <i class="fas fa-envelope text-yellow-500 ml-auto text-xl"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-4 md:p-6 border-l-4 border-purple-500">
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Website Visits</h3>
                        <div class="flex items-center">
                            <span class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['visits']); ?></span>
                            <i class="fas fa-chart-line text-purple-500 ml-auto text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Recent Articles Section -->
                <div class="bg-white rounded-lg shadow-md mb-6">
                    <div class="px-4 md:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-700 mb-2 sm:mb-0">Recent Articles</h2>
                        <a href="create_article.php" class="flex items-center justify-center px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark transition">
                            <i class="fas fa-plus mr-2"></i> New Article
                        </a>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($recent_articles)): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No articles found</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_articles as $article): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($article['title']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($article['published_date'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($article['status'] == 'published'): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Published
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    Draft
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <a href="edit_article.php?id=<?php echo $article['id']; ?>" class="text-primary hover:text-primary-dark mr-3">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
    <a href="edit_article.php?id=<?php echo $article['id']; ?>" class="text-primary hover:text-primary-dark mr-3">
        <i class="fas fa-edit"></i> Edit
    </a>
    <a href="admin.php?delete_article=<?php echo $article['id']; ?>" 
       class="text-red-600 hover:text-red-800"
       onclick="return confirm('Are you sure you want to delete this article?');">
        <i class="fas fa-trash"></i> Delete
    </a>
</td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($recent_articles) > 0): ?>
                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 text-right">
                        <a href="articles.php" class="text-sm text-primary hover:text-primary-dark">
                            View all articles <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Registrations Section -->
                <div class="bg-white rounded-lg shadow-md">
                    <div class="px-4 md:px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-700">Recent Course Registrations</h2>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($recent_registrations)): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No registrations found</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_registrations as $registration): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($registration['name']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($registration['course_title']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($registration['registration_date'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($registration['status'] == 'confirmed'): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Confirmed
                                                </span>
                                            <?php elseif ($registration['status'] == 'pending'): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Pending
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    <?php echo ucfirst($registration['status']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($recent_registrations) > 0): ?>
                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 text-right">
                        <a href="registrations.php" class="text-sm text-primary hover:text-primary-dark">
                            View all registrations <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const sidebar = document.getElementById('sidebar');
            
            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('hidden');
                });
            }
            
            // Handle window resize events
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) { // md breakpoint
                    sidebar.classList.remove('hidden');
                } else {
                    sidebar.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>