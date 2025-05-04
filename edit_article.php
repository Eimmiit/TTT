<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Article | Trauma to Triumph</title>
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
    <!-- Include TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/your-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
      tinymce.init({
        selector: '#article_content',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        images_upload_url: 'upload_image.php',
        automatic_uploads: true,
        file_picker_types: 'image',
        file_picker_callback: function(cb, value, meta) {
          var input = document.createElement('input');
          input.setAttribute('type', 'file');
          input.setAttribute('accept', 'image/*');

          input.onchange = function() {
            var file = this.files[0];
            
            var reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function () {
              var id = 'blobid' + (new Date()).getTime();
              var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
              var base64 = reader.result.split(',')[1];
              var blobInfo = blobCache.create(id, file, base64);
              blobCache.add(blobInfo);
              cb(blobInfo.blobUri(), { title: file.name });
            };
          };
          input.click();
        }
      });
    </script>
</head>
<body class="bg-gray-100 font-sans">
    <?php
    // Include database configuration
    include 'config.php';
    
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Create database connection
    $conn = new mysqli($host, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Check if ID is provided
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: create_article.php");
        exit();
    }
    
    $article_id = $_GET['id'];
    $message = '';
    $article = [];
    
    // Image upload handling
    $upload_dir = 'uploads/images/';
    $image_url = '';
    $image_error = '';
    
    // Handle form submission for updating article
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $excerpt = $_POST['excerpt'];
        $status = $_POST['status'];
        $categories = isset($_POST['categories']) ? implode(',', $_POST['categories']) : '';
        
        // Handle image upload if a new image is provided
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['name'] != '') {
            // Create uploads directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = basename($_FILES['featured_image']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $new_file_name = uniqid() . '.' . $file_ext;
            $target_file = $upload_dir . $new_file_name;
            
            // Check if image file is a actual image
            $check = getimagesize($_FILES['featured_image']['tmp_name']);
            if ($check !== false) {
                // Check file size (limit to 5MB)
                if ($_FILES['featured_image']['size'] < 5000000) {
                    // Allow certain file formats
                    if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
                            $image_url = $target_file;
                        } else {
                            $image_error = 'Sorry, there was an error uploading your file.';
                        }
                    } else {
                        $image_error = 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.';
                    }
                } else {
                    $image_error = 'Sorry, your file is too large. Maximum size is 5MB.';
                }
            } else {
                $image_error = 'File is not an image.';
            }
        } else {
            // Keep existing image
            $image_url = isset($_POST['existing_image']) ? $_POST['existing_image'] : '';
        }
        
        // Update the article in the database
        $sql = "UPDATE articles SET 
                title = ?, 
                content = ?, 
                excerpt = ?, 
                status = ?,
                categories = ?,
                image_url = ?,
                last_updated = NOW()
                WHERE id = ?";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $title, $content, $excerpt, $status, $categories, $image_url, $article_id);
        
        if ($stmt->execute()) {
            $message = '<div class="mb-4 p-4 bg-green-100 text-green-700 rounded">Article updated successfully!</div>';
            
            // Reload article data to get updated values
            $article_stmt = $conn->prepare("SELECT * FROM articles WHERE id = ?");
            $article_stmt->bind_param("i", $article_id);
            $article_stmt->execute();
            $result = $article_stmt->get_result();
            if ($result->num_rows > 0) {
                $article = $result->fetch_assoc();
            }
            $article_stmt->close();
        } else {
            $message = '<div class="mb-4 p-4 bg-red-100 text-red-700 rounded">Error updating article: ' . $conn->error . '</div>';
        }
        
        $stmt->close();
        
        // Display image error if any
        if (!empty($image_error)) {
            $message .= '<div class="mb-4 p-4 bg-red-100 text-red-700 rounded">' . $image_error . '</div>';
        }
    } else {
        // Fetch article data
        $sql = "SELECT * FROM articles WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $article_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $article = $result->fetch_assoc();
        } else {
            header("Location: create_article.php");
            exit();
        }
        $stmt->close();
    }
    
    // Fetch categories for dropdown
    $categories = [];
    $sql = "SELECT id, name FROM categories ORDER BY name";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    
    // Close the connection
    $conn->close();
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
                <a href="admin.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-dark-light hover:text-white">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span class="ml-3">Dashboard</span>
                </a>
                <a href="create_article.php" class="flex items-center px-6 py-3 bg-dark-light text-white">
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
                <h1 class="text-lg md:text-xl font-semibold">Edit Article</h1>
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

            <!-- Form Content -->
            <div class="p-4 md:p-6 overflow-y-auto">
                <?php echo $message; ?>
                
                <div class="bg-white rounded-lg shadow-md mb-6">
                    <div class="px-4 md:px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-700">Article Details</h2>
                        <div>
                            <a href="create_article.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition mr-2">
                                Cancel
                            </a>
                            <button form="articleForm" type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark transition">
                                Update Article
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-4 md:p-6">
                        <form id="articleForm" method="POST" action="" enctype="multipart/form-data">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <div class="lg:col-span-2">
                                    <div class="mb-6">
                                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Article Title</label>
                                        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($article['title'] ?? ''); ?>" required
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                                            placeholder="Enter article title">
                                    </div>
                                    
                                    <div class="mb-6">
                                        <label for="article_content" class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                                        <textarea name="content" id="article_content" rows="10" 
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"><?php echo htmlspecialchars($article['content'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-6">
                                        <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
                                        <textarea name="excerpt" id="excerpt" rows="3" 
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                                            placeholder="Enter a short excerpt for this article"><?php echo htmlspecialchars($article['excerpt'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="lg:col-span-1">
                                    <div class="mb-6">
                                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <select name="status" id="status" 
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                                            <option value="draft" <?php echo ($article['status'] ?? '') == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                            <option value="published" <?php echo ($article['status'] ?? '') == 'published' ? 'selected' : ''; ?>>Published</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Categories</label>
                                        <div class="max-h-48 overflow-y-auto border rounded-md border-gray-300 p-2">
                                            <?php
                                            $article_categories = isset($article['categories']) && $article['categories'] !== null ? 
                                                explode(',', $article['categories']) : [];
                                            foreach ($categories as $category): ?>
                                                <div class="flex items-center mb-2">
                                                    <input type="checkbox" name="categories[]" id="cat_<?php echo $category['id']; ?>" value="<?php echo $category['id']; ?>" 
                                                        <?php echo in_array($category['id'], $article_categories) ? 'checked' : ''; ?>
                                                        class="rounded border-gray-300 text-primary focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                                                    <label for="cat_<?php echo $category['id']; ?>" class="ml-2 text-sm text-gray-700"><?php echo htmlspecialchars($category['name'] ?? ''); ?></label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Featured Image</label>
                                        <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($article['image_url'] ?? ''); ?>">
                                        
                                        <div class="mt-2 flex items-center">
                                            <label for="featured_image" class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300 transition cursor-pointer">
                                                Choose Image
                                                <input type="file" id="featured_image" name="featured_image" accept="image/*" class="hidden" onchange="previewImage(this)">
                                            </label>
                                            <span id="file_name" class="ml-2 text-sm text-gray-500"></span>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <div id="image_preview_container" class="<?php echo empty($article['image_url']) ? 'hidden' : ''; ?>">
                                                <img id="image_preview" src="<?php echo htmlspecialchars($article['image_url'] ?? ''); ?>" alt="Featured image preview" class="w-full h-32 object-cover rounded">
                                                <button type="button" onclick="removeImage()" class="mt-2 px-3 py-1 bg-red-100 text-red-700 rounded text-sm hover:bg-red-200 transition">
                                                    Remove Image
                                                </button>
                                            </div>
                                            <div id="no_image_container" class="<?php echo !empty($article['image_url']) ? 'hidden' : ''; ?> mt-2">
                                                <div class="w-full h-32 bg-gray-100 border border-dashed border-gray-300 rounded flex items-center justify-center">
                                                    <span class="text-sm text-gray-500">No image selected</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-6">
                                        <p class="text-sm text-gray-500">
                                            <strong>Created:</strong> <?php echo isset($article['published_date']) ? date('M d, Y', strtotime($article['published_date'])) : 'N/A'; ?><br>
                                            <strong>Last Updated:</strong> <?php echo isset($article['last_updated']) ? date('M d, Y', strtotime($article['last_updated'])) : 'N/A'; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
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
        
        // Image preview functionality
        function previewImage(input) {
            const fileInput = input;
            const fileName = document.getElementById('file_name');
            const imagePreview = document.getElementById('image_preview');
            const previewContainer = document.getElementById('image_preview_container');
            const noImageContainer = document.getElementById('no_image_container');
            
            if (fileInput.files && fileInput.files[0]) {
                const file = fileInput.files[0];
                fileName.textContent = file.name;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                    noImageContainer.classList.add('hidden');
                }
                reader.readAsDataURL(file);
            }
        }
        
        // Remove image functionality
        function removeImage() {
            const fileInput = document.getElementById('featured_image');
            const fileName = document.getElementById('file_name');
            const existingImage = document.querySelector('input[name="existing_image"]');
            const previewContainer = document.getElementById('image_preview_container');
            const noImageContainer = document.getElementById('no_image_container');
            
            // Clear the file input and reset the preview
            fileInput.value = '';
            fileName.textContent = '';
            existingImage.value = '';
            
            // Hide preview, show no image container
            previewContainer.classList.add('hidden');
            noImageContainer.classList.remove('hidden');
        }
    </script>
</body>
</html>