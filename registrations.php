<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrations | Trauma to Triumph</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php
    include 'config.php'; // Include database configuration

    // Create connection
   
    $conn = new mysqli($host, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Process new course submission
    if(isset($_POST['submit_course'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $date = $_POST['date'];
        $capacity = $_POST['capacity'];
        $price = $_POST['price'];
        
        $sql = "INSERT INTO courses (title, description, start_date, capacity, price, status) 
                VALUES ('$title', '$description', '$date', '$capacity', '$price', 'active')";
        
        if ($conn->query($sql) === TRUE) {
            echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4' role='alert'>
                    <strong class='font-bold'>Success!</strong>
                    <span class='block sm:inline'> New course created successfully.</span>
                </div>";
        } else {
            echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>
                    <strong class='font-bold'>Error!</strong>
                    <span class='block sm:inline'> " . $conn->error . "</span>
                </div>";
        }
    }

    // Process confirm registration
    if(isset($_GET['confirm_id'])) {
        $reg_id = $_GET['confirm_id'];
        
        $sql = "UPDATE registrations SET status = 'Confirmed' WHERE id = $reg_id";
        
        if ($conn->query($sql) === TRUE) {
            echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4' role='alert'>
                    <strong class='font-bold'>Success!</strong>
                    <span class='block sm:inline'> Registration confirmed successfully.</span>
                </div>";
        } else {
            echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>
                    <strong class='font-bold'>Error!</strong>
                    <span class='block sm:inline'> " . $conn->error . "</span>
                </div>";
        }
    }

    // Fetch registrations
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $course_filter = isset($_GET['course_filter']) ? $_GET['course_filter'] : '';
    $status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';

    $sql = "SELECT r.id, r.name, r.email, c.title as course, r.registration_date, r.status
            FROM registrations r 
            JOIN courses c ON r.course_id = c.id
            WHERE 1=1";

    if(!empty($search)) {
        $sql .= " AND (r.name LIKE '%$search%' OR r.email LIKE '%$search%')";
    }

    if(!empty($course_filter) && $course_filter != 'All Courses') {
        $sql .= " AND c.title = '$course_filter'";
    }

    if(!empty($status_filter) && $status_filter != 'All Status') {
        $sql .= " AND r.status = '$status_filter'";
    }

    $registrations_result = $conn->query($sql);

    // Fetch courses for dropdown
    $courses_sql = "SELECT id, title FROM courses";
    $courses_result = $conn->query($courses_sql);
    $courses = [];
    
    if ($courses_result->num_rows > 0) {
        while($row = $courses_result->fetch_assoc()) {
            $courses[] = $row;
        }
    }
    ?>

    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-indigo-800 text-white">
            <div class="p-4 border-b border-indigo-700">
                <h2 class="flex items-center text-xl font-bold">
                    <img src="assets/logo.png" alt="Trauma to Triumph Logo" class="h-8 w-8 mr-2">
                    <span>Admin</span>
                </h2>
            </div>
            <div class="mt-4">
                <a href="admin.php" class="flex items-center px-4 py-3 text-indigo-300 hover:bg-indigo-700">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    <span>Dashboard</span>
                </a>
                <a href="create_article.php" class="flex items-center px-4 py-3 text-indigo-300 hover:bg-indigo-700">
                    <i class="fas fa-newspaper mr-3"></i>
                    <span>Articles</span>
                </a>
                <a href="courses.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-dark-light hover:text-white">
                    <i class="fas fa-book w-5"></i>
                    <span class="ml-3">Courses</span>
                </a>
                <a href="registrations.php" class="flex items-center px-4 py-3 bg-indigo-700 text-white">
                    <i class="fas fa-users mr-3"></i>
                    <span>Registrations</span>
                </a>
                <a href="messages.php" class="flex items-center px-4 py-3 text-indigo-300 hover:bg-indigo-700">
                    <i class="fas fa-envelope mr-3"></i>
                    <span>Messages</span>
                </a>
                <a href="settings.php" class="flex items-center px-4 py-3 text-indigo-300 hover:bg-indigo-700">
                    <i class="fas fa-cog mr-3"></i>
                    <span>Settings</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto">
            <div class="px-6 py-4 bg-white shadow flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-700">Course Registrations</h1>
                <div class="flex items-center">
                    <button id="openCreateCourseModal" class="mr-4 bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        <i class="fas fa-plus mr-2"></i> New Course
                    </button>
                    <div class="h-10 w-10 rounded-full bg-indigo-600 flex items-center justify-center text-white">
                        AD
                    </div>
                </div>
            </div>

            <!-- Registrations Section -->
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-700">All Registrations</h2>
                    <a href="export_registrations.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        <i class="fas fa-download mr-2"></i> Export
                    </a>
                </div>
                
                <form action="" method="GET" class="flex flex-wrap gap-4 mb-6">
                    <div class="flex-grow">
                        <input type="text" name="search" value="<?php echo $search; ?>" placeholder="Search registrations..." 
                            class="w-full px-4 py-2 border rounded-md">
                    </div>
                    <div class="w-48">
                        <select name="course_filter" class="w-full px-4 py-2 border rounded-md">
                            <option value="All Courses">All Courses</option>
                            <?php foreach($courses as $course): ?>
                                <option value="<?php echo $course['title']; ?>" <?php if($course_filter == $course['title']) echo 'selected'; ?>>
                                    <?php echo $course['title']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="w-48">
                        <select name="status_filter" class="w-full px-4 py-2 border rounded-md">
                            <option value="All Status">All Status</option>
                            <option value="Pending" <?php if($status_filter == 'Pending') echo 'selected'; ?>>Pending</option>
                            <option value="Confirmed" <?php if($status_filter == 'Confirmed') echo 'selected'; ?>>Confirmed</option>
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                </form>
                
                <div class="overflow-x-auto bg-white rounded-lg shadow">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php
                            if ($registrations_result->num_rows > 0) {
                                while($row = $registrations_result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap'>" . $row["name"] . "</td>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap'>" . $row["email"] . "</td>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap'>" . $row["course"] . "</td>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap'>" . $row["registration_date"] . "</td>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap'>";
                                    if($row["status"] == "Pending") {
                                        echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800'>Pending</span>";
                                    } else {
                                        echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800'>Confirmed</span>";
                                    }
                                    echo "</td>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap'>";
                                    if($row["status"] == "Pending") {
                                        echo "<a href='?confirm_id=" . $row["id"] . "' class='text-indigo-600 hover:text-indigo-900 mr-3'><i class='fas fa-check mr-1'></i> Confirm</a>";
                                    } else {
                                        echo "<a href='mailto:" . $row["email"] . "' class='text-indigo-600 hover:text-indigo-900 mr-3'><i class='fas fa-envelope mr-1'></i> Email</a>";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='px-6 py-4 text-center text-gray-500'>No registrations found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="flex justify-center mt-6">
                    <nav class="inline-flex rounded-md shadow">
                        <a href="#" class="px-3 py-2 border border-gray-300 bg-white text-gray-500 rounded-l-md hover:bg-gray-50">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <a href="#" class="px-3 py-2 border-t border-b border-gray-300 bg-indigo-50 text-indigo-600">1</a>
                        <a href="#" class="px-3 py-2 border border-gray-300 bg-white text-gray-500 hover:bg-gray-50">2</a>
                        <a href="#" class="px-3 py-2 border border-gray-300 bg-white text-gray-500 rounded-r-md hover:bg-gray-50">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Course Modal -->
    <div id="createCourseModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-700">Create New Course</h3>
                <button id="closeCreateCourseModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="" method="POST">
                <div class="mb-4">
                    <label for="title" class="block text-gray-700 text-sm font-medium mb-2">Course Title</label>
                    <input type="text" id="title" name="title" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div class="mb-4">
                    <label for="description" class="block text-gray-700 text-sm font-medium mb-2">Description</label>
                    <textarea id="description" name="description" rows="4" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="date" class="block text-gray-700 text-sm font-medium mb-2">Date</label>
                        <input type="date" id="date" name="date" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="capacity" class="block text-gray-700 text-sm font-medium mb-2">Capacity</label>
                        <input type="number" id="capacity" name="capacity" min="1" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="price" class="block text-gray-700 text-sm font-medium mb-2">Price ($)</label>
                    <input type="number" id="price" name="price" min="0" step="0.01" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div class="flex justify-end">
                    <button type="button" id="cancelCreateCourse" class="mr-2 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" name="submit_course" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Create Course
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functionality
        const openCreateCourseModal = document.getElementById('openCreateCourseModal');
        const createCourseModal = document.getElementById('createCourseModal');
        const closeCreateCourseModal = document.getElementById('closeCreateCourseModal');
        const cancelCreateCourse = document.getElementById('cancelCreateCourse');

        openCreateCourseModal.addEventListener('click', () => {
            createCourseModal.classList.remove('hidden');
        });

        closeCreateCourseModal.addEventListener('click', () => {
            createCourseModal.classList.add('hidden');
        });

        cancelCreateCourse.addEventListener('click', () => {
            createCourseModal.classList.add('hidden');
        });
    </script>
</body>
</html>