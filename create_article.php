<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Article | Trauma to Triumph</title>
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
    <style>
        @media (max-width: 768px) {
            .editor-toolbar {
                flex-wrap: wrap;
                justify-content: center;
            }
            .editor-toolbar button {
                margin: 2px;
            }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <?php
    // Database connection
    include 'config.php'; // Include your database configuration file
    
    $conn = new mysqli($host, $username, $password, $dbname);
    $successMessage = '';
    $errorMessage = '';
    
    // Process form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get form data
        $title = $_POST['title'];
        $excerpt = $_POST['excerpt'];
        $content = $_POST['content'];
        $status = $_POST['status'];
        $author_name = $_POST['author_name'];
        $author_bio = $_POST['author_bio'];
        $published_date = $_POST['published_date'];
        
        // Image handling for article image
        $image_url = "assets/default-article.jpg"; // Default image
        if(isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
            $target_dir = "assets/";
            $file_extension = pathinfo($_FILES["image_file"]["name"], PATHINFO_EXTENSION);
            $new_filename = "article_" . time() . "." . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_file)) {
                $image_url = $target_file;
            }
        }
        
        // Image handling for author image
        $author_image = "assets/default-author.jpg"; // Default image
        if(isset($_FILES['author_image_file']) && $_FILES['author_image_file']['error'] == 0) {
            $target_dir = "assets/";
            $file_extension = pathinfo($_FILES["author_image_file"]["name"], PATHINFO_EXTENSION);
            $new_filename = "author_" . time() . "." . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["author_image_file"]["tmp_name"], $target_file)) {
                $author_image = $target_file;
            }
        }
        
        // Convert datetime-local to MySQL datetime format
        $published_date_formatted = date('Y-m-d H:i:s', strtotime($published_date));
        
        // Prepare SQL statement
        $sql = "INSERT INTO articles (title, excerpt, content, image_url, published_date, status, author_name, author_bio, author_image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssss", $title, $excerpt, $content, $image_url, $published_date_formatted, $status, $author_name, $author_bio, $author_image);
        
        if ($stmt->execute()) {
            $successMessage = "Article created successfully!";
            // Redirect after successful submission
            if ($status == "published") {
                header("Location: admin.php?success=published");
                exit;
            } else {
                header("Location: admin.php?success=draft");
                exit;
            }
        } else {
            $errorMessage = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
    $conn = null;
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
                <h1 class="text-lg md:text-xl font-semibold">Create New Article</h1>
                <div class="flex items-center">
                    <div class="h-8 w-8 rounded-full bg-primary-dark text-white flex items-center justify-center">
                        AD
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <?php if($successMessage): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mx-4 md:mx-6 my-4" role="alert">
                <p><?php echo $successMessage; ?></p>
            </div>
            <?php endif; ?>
            
            <?php if($errorMessage): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mx-4 md:mx-6 my-4" role="alert">
                <p><?php echo $errorMessage; ?></p>
            </div>
            <?php endif; ?>

            <!-- Form Section -->
            <div class="p-4 md:p-6 overflow-y-auto">
                <div class="bg-white rounded-lg shadow-md p-4 md:p-6">
                    <form id="articleForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data" class="space-y-6">
                        <!-- Title & Status -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
                            <div class="md:col-span-2">
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Article Title</label>
                                <input type="text" id="title" name="title" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" placeholder="Enter article title" required>
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select id="status" name="status" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                </select>
                            </div>
                        </div>

                        <!-- Excerpt -->
                        <div>
                            <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
                            <textarea id="excerpt" name="excerpt" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" placeholder="Brief summary of the article" required></textarea>
                        </div>

                        <!-- Content -->
                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                            <div class="border border-gray-300 rounded-md mb-2">
                                <div class="bg-gray-50 px-2 py-2 border-b border-gray-300 flex flex-wrap gap-1 editor-toolbar">
                                    <button type="button" class="p-1 hover:bg-gray-200 rounded editor-btn" data-tag="b"><i class="fas fa-bold"></i></button>
                                    <button type="button" class="p-1 hover:bg-gray-200 rounded editor-btn" data-tag="i"><i class="fas fa-italic"></i></button>
                                    <button type="button" class="p-1 hover:bg-gray-200 rounded editor-btn" data-tag="a"><i class="fas fa-link"></i></button>
                                    <button type="button" class="p-1 hover:bg-gray-200 rounded editor-btn" data-tag="ul"><i class="fas fa-list-ul"></i></button>
                                    <button type="button" class="p-1 hover:bg-gray-200 rounded editor-btn" data-tag="ol"><i class="fas fa-list-ol"></i></button>
                                    <button type="button" class="p-1 hover:bg-gray-200 rounded" onclick="insertHeading()"><i class="fas fa-heading"></i></button>
                                    <button type="button" class="p-1 hover:bg-gray-200 rounded editor-btn" data-tag="blockquote"><i class="fas fa-quote-right"></i></button>
                                    <button type="button" class="p-1 hover:bg-gray-200 rounded editor-btn" data-tag="code"><i class="fas fa-code"></i></button>
                                </div>
                                <textarea id="content" name="content" rows="12" class="w-full px-3 py-2 border-0 focus:ring-0" placeholder="Article content goes here..." required></textarea>
                            </div>
                            <div class="text-xs text-gray-500">HTML formatting is supported</div>
                        </div>

                        <!-- Author Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                            <div>
                                <label for="author_name" class="block text-sm font-medium text-gray-700 mb-1">Author Name</label>
                                <input type="text" id="author_name" name="author_name" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" placeholder="Author's name" required>
                            </div>
                            <div>
                                <label for="published_date" class="block text-sm font-medium text-gray-700 mb-1">Publish Date</label>
                                <input type="datetime-local" id="published_date" name="published_date" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" required>
                            </div>
                        </div>

                        <div>
                            <label for="author_bio" class="block text-sm font-medium text-gray-700 mb-1">Author Bio</label>
                            <textarea id="author_bio" name="author_bio" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" placeholder="Brief author biography"></textarea>
                        </div>

                        <!-- Image Uploads -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="image_file" class="block text-sm font-medium text-gray-700 mb-1">Featured Image</label>
                                <div class="flex items-center">
                                    <label class="w-full flex flex-col items-center px-4 py-4 md:py-6 bg-white rounded-md border-2 border-dashed border-gray-300 cursor-pointer hover:bg-gray-50">
                                        <span class="mb-2"><i class="fas fa-cloud-upload-alt text-gray-400 text-2xl md:text-3xl"></i></span>
                                        <span class="text-xs md:text-sm text-gray-600 text-center">Drop files here or click to upload</span>
                                        <input id="image_file" name="image_file" type="file" class="hidden" accept="image/*">
                                    </label>
                                </div>
                                <div id="image_preview" class="mt-2 hidden">
                                    <img src="" alt="Preview" class="h-20 md:h-24 rounded border">
                                </div>
                            </div>
                            <div>
                                <label for="author_image_file" class="block text-sm font-medium text-gray-700 mb-1">Author Image</label>
                                <div class="flex items-center">
                                    <label class="w-full flex flex-col items-center px-4 py-4 md:py-6 bg-white rounded-md border-2 border-dashed border-gray-300 cursor-pointer hover:bg-gray-50">
                                        <span class="mb-2"><i class="fas fa-cloud-upload-alt text-gray-400 text-2xl md:text-3xl"></i></span>
                                        <span class="text-xs md:text-sm text-gray-600 text-center">Drop files here or click to upload</span>
                                        <input id="author_image_file" name="author_image_file" type="file" class="hidden" accept="image/*">
                                    </label>
                                </div>
                                <div id="author_image_preview" class="mt-2 hidden">
                                    <img src="" alt="Preview" class="h-20 md:h-24 rounded-full border">
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row justify-end gap-2 sm:space-x-3">
                            <a href="articles.html" class="text-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 mb-2 sm:mb-0">
                                Cancel
                            </a>
                            <button type="button" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 mb-2 sm:mb-0" onclick="saveDraft()">
                                Save Draft
                            </button>
                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark">
                                <i class="fas fa-save mr-1"></i> Publish Article
                            </button>
                        </div>
                    </form>
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
            
            // Initialize current date/time for the publish date field
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            
            const formattedDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
            document.getElementById('published_date').value = formattedDateTime;
            
            // Image preview handling
            document.getElementById('image_file').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('image_preview').classList.remove('hidden');
                        document.getElementById('image_preview').querySelector('img').src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });
            
            document.getElementById('author_image_file').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('author_image_preview').classList.remove('hidden');
                        document.getElementById('author_image_preview').querySelector('img').src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });
            
            // Editor button handling
            document.querySelectorAll('.editor-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const tag = this.getAttribute('data-tag');
                    const textarea = document.getElementById('content');
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    const selectedText = textarea.value.substring(start, end);
                    
                    let insertText = '';
                    
                    switch(tag) {
                        case 'a':
                            const url = prompt('Enter URL:', 'https://');
                            if (url) {
                                insertText = `<a href="${url}">${selectedText || 'link text'}</a>`;
                            }
                            break;
                        case 'ul':
                            insertText = `<ul>\n    <li>${selectedText || 'List item'}</li>\n    <li>Another item</li>\n</ul>`;
                            break;
                        case 'ol':
                            insertText = `<ol>\n    <li>${selectedText || 'List item'}</li>\n    <li>Another item</li>\n</ol>`;
                            break;
                        case 'blockquote':
                            insertText = `<blockquote>${selectedText || 'Quote text'}</blockquote>`;
                            break;
                        default:
                            insertText = `<${tag}>${selectedText || `${tag} text`}</${tag}>`;
                    }
                    
                    if (insertText) {
                        textarea.value = textarea.value.substring(0, start) + insertText + textarea.value.substring(end);
                        textarea.focus();
                        textarea.selectionStart = start + insertText.length;
                        textarea.selectionEnd = start + insertText.length;
                    }
                });
            });
        });
        
        function insertHeading() {
            const size = prompt('Enter heading size (1-6):', '2');
            if (size && size >= 1 && size <= 6) {
                const textarea = document.getElementById('content');
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const selectedText = textarea.value.substring(start, end);
                const insertText = `<h${size}>${selectedText || 'Heading text'}</h${size}>`;
                
                textarea.value = textarea.value.substring(0, start) + insertText + textarea.value.substring(end);
                textarea.focus();
                textarea.selectionStart = start + insertText.length;
                textarea.selectionEnd = start + insertText.length;
            }
        }
        
        function saveDraft() {
            document.getElementById('status').value = 'draft';
            document.getElementById('articleForm').submit();
        }
    </script>
</body>
</html>