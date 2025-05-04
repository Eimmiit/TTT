<?php
include 'config.php'; // Include database configuration

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle new course submission
if (isset($_POST['submit_course'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $capacity = $_POST['capacity'];
    $price = $_POST['price'];
    $status = 'inactive'; // Default status for new courses

    $sql = "INSERT INTO courses (title, description, start_date, capacity, price, status) 
            VALUES ('$title', '$description', '$date', '$capacity', '$price', '$status')";

    if ($conn->query($sql) === TRUE) {
        $success_message = "New course created successfully.";
    } else {
        $error_message = "Error: " . $conn->error;
    }
}

// Handle status toggle
if (isset($_GET['toggle_status'])) {
    $course_id = intval($_GET['toggle_status']);
    $current_status = $_GET['current_status'];

    $new_status = ($current_status === 'active') ? 'inactive' : 'active';
    $sql = "UPDATE courses SET status = '$new_status' WHERE id = $course_id";

    if ($conn->query($sql) === TRUE) {
        $success_message = "Course status updated successfully.";
    } else {
        $error_message = "Error: " . $conn->error;
    }
}

// Fetch all courses
$courses_sql = "SELECT * FROM courses ORDER BY start_date ASC";
$courses_result = $conn->query($courses_sql);

// Get count stats
$total_courses = $courses_result->num_rows;
$active_courses_sql = "SELECT COUNT(*) as count FROM courses WHERE status = 'active'";
$active_result = $conn->query($active_courses_sql);
$active_courses = $active_result->fetch_assoc()['count'];
$inactive_courses = $total_courses - $active_courses;

// Get upcoming courses (next 30 days)
$upcoming_sql = "SELECT COUNT(*) as count FROM courses WHERE start_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
$upcoming_result = $conn->query($upcoming_sql);
$upcoming_courses = $upcoming_result->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Dashboard | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-indigo-800 text-white p-4">
            <div class="text-xl font-bold p-4 border-b border-indigo-700">Course Admin</div>
            <nav class="mt-8">
            <a href="admin.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-dark-light hover:text-white">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span class="ml-3">Dashboard</span>
                </a>
                <a href="create_article.php" class="flex items-center px-6 py-3 bg-dark-light text-white">
                    <i class="fas fa-newspaper w-5"></i>
                    <span class="ml-3">Articles</span>
                </a>
                <a href="#" class="flex items-center p-3 bg-indigo-900 rounded mb-2">
                    <i class="fas fa-book mr-3"></i>
                    <span>Courses</span>
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
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold">Course Dashboard</h1>
                <button id="openModalBtn" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 flex items-center">
                    <i class="fas fa-plus mr-2"></i> Create New Course
                </button>
            </div>

            <!-- Success/Error Messages -->
            <?php if (isset($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow border-l-4 border-indigo-500">
                    <h3 class="text-gray-500 text-sm font-medium">Total Courses</h3>
                    <p class="text-3xl font-bold"><?php echo $total_courses; ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow border-l-4 border-green-500">
                    <h3 class="text-gray-500 text-sm font-medium">Active Courses</h3>
                    <p class="text-3xl font-bold"><?php echo $active_courses; ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow border-l-4 border-red-500">
                    <h3 class="text-gray-500 text-sm font-medium">Inactive Courses</h3>
                    <p class="text-3xl font-bold"><?php echo $inactive_courses; ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow border-l-4 border-yellow-500">
                    <h3 class="text-gray-500 text-sm font-medium">Upcoming Courses</h3>
                    <p class="text-3xl font-bold"><?php echo $upcoming_courses; ?></p>
                </div>
            </div>

            <!-- Courses Table -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">All Courses</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if ($courses_result->num_rows > 0): ?>
                                <?php while ($course = $courses_result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $course['title']; ?></td>
                                        <td class="px-6 py-4">
                                            <div class="truncate max-w-xs"><?php echo $course['description']; ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M d, Y', strtotime($course['start_date'])); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $course['capacity']; ?> students</td>
                                        <td class="px-6 py-4 whitespace-nowrap">$<?php echo number_format($course['price'], 2); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $course['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                <?php echo ucfirst($course['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex space-x-2">
                                                <a href="?toggle_status=<?php echo $course['id']; ?>&current_status=<?php echo $course['status']; ?>" 
                                                   class="text-indigo-600 hover:text-indigo-900">
                                                    <i class="fas fa-toggle-on mr-1"></i> Toggle
                                                </a>
                                                <a href="#" class="text-blue-600 hover:text-blue-900">
                                                    <i class="fas fa-edit mr-1"></i> Edit
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No courses found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="courseModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-8 rounded-lg shadow-lg max-w-2xl w-full max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold">Create New Course</h2>
                <button id="closeModalBtn" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="" method="POST">
                <div class="mb-4">
                    <label for="title" class="block text-gray-700 font-medium mb-2">Course Title</label>
                    <input type="text" id="title" name="title" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="mb-4">
                    <label for="description" class="block text-gray-700 font-medium mb-2">Description</label>
                    <textarea id="description" name="description" placeholder="maximum words 200" maxlength="200" rows="4" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="date" class="block text-gray-700 font-medium mb-2">Start Date</label>
                        <input type="date" id="date" name="date" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="capacity" class="block text-gray-700 font-medium mb-2">Capacity</label>
                        <input type="number" id="capacity" name="capacity" min="1" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="mb-6">
                    <label for="price" class="block text-gray-700 font-medium mb-2">Price ($)</label>
                    <input type="number" id="price" name="price" min="0" step="0.01" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex justify-end">
                    <button type="button" id="cancelBtn" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 mr-2">
                        Cancel
                    </button>
                    <button type="submit" name="submit_course" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        Create Course
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Modal functionality
        const modal = document.getElementById('courseModal');
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const cancelBtn = document.getElementById('cancelBtn');

        openModalBtn.addEventListener('click', () => {
            modal.classList.remove('hidden');
        });

        const closeModal = () => {
            modal.classList.add('hidden');
        };

        closeModalBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);

        // Close modal when clicking outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });
    </script>
</body>
</html>