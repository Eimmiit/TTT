<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Trauma to Triumph</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        .toggle-switch input[type="checkbox"] {
            height: 0;
            width: 0;
            visibility: hidden;
        }
        .toggle-label {
            cursor: pointer;
            width: 50px;
            height: 25px;
            background: #d1d5db;
            display: block;
            border-radius: 25px;
            position: relative;
            transition: 0.3s;
        }
        .toggle-label:after {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            width: 19px;
            height: 19px;
            background: #fff;
            border-radius: 19px;
            transition: 0.3s;
        }
        input:checked + .toggle-label {
            background: #6c63ff;
        }
        input:checked + .toggle-label:after {
            left: calc(100% - 3px);
            transform: translateX(-100%);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div id="sidebar" class="w-64 bg-gray-800 text-white flex-shrink-0 hidden md:block">
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
                <a href="messages.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white">
                    <i class="fas fa-envelope w-5"></i>
                    <span class="ml-3">Messages</span>
                </a>
                <a href="settings.php" class="flex items-center px-6 py-3 bg-gray-700 text-white">
                    <i class="fas fa-cog w-5"></i>
                    <span class="ml-3">Settings</span>
                </a>
            </div>
        </div>

        <!-- Mobile menu button -->
        <div class="md:hidden fixed right-4 top-4 z-50">
            <button id="menuToggle" class="bg-gray-800 text-white p-2 rounded-md">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <!-- Main Content -->
        <div id="mainContent" class="relative flex-1">
   <!-- Locked Cover -->
   <div id="lockedCover" class="absolute inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50">
        <div class="text-center text-white">
            <h1 class="text-3xl font-bold mb-4">Page Locked</h1>
            <p class="text-lg mb-6">You do not have access to this section. Please contact the administrator for access.</p>
            <button id="unlockButton" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-md">
                Unlock Section
            </button>
        </div>
    </div>
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <button id="notificationBtn" class="text-gray-500 hover:text-gray-700">
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 text-xs flex items-center justify-center">3</span>
                                <i class="fas fa-bell text-xl"></i>
                            </button>
                        </div>
                        <div class="relative">
                            <button id="userMenuBtn" class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-purple-600 flex items-center justify-center text-white font-semibold">AD</div>
                                <i class="fas fa-chevron-down ml-2 text-gray-500"></i>
                            </button>
                            <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Profile</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sign out</a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <!-- Settings Tabs -->
                <div class="mb-6 bg-white shadow rounded-lg overflow-hidden">
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <button class="tab-btn text-left w-1/6 py-4 px-6 border-b-2 border-purple-500 font-medium text-purple-600" data-tab="general">
                                General
                            </button>
                            <button class="tab-btn text-left w-1/6 py-4 px-6 border-b-2 border-transparent hover:border-gray-300 font-medium text-gray-500 hover:text-gray-700" data-tab="home">
                                Home Page
                            </button>
                            <button class="tab-btn text-left w-1/6 py-4 px-6 border-b-2 border-transparent hover:border-gray-300 font-medium text-gray-500 hover:text-gray-700" data-tab="about">
                                About Page
                            </button>
                            <button class="tab-btn text-left w-1/6 py-4 px-6 border-b-2 border-transparent hover:border-gray-300 font-medium text-gray-500 hover:text-gray-700" data-tab="services">
                                Services
                            </button>
                            <button class="tab-btn text-left w-1/6 py-4 px-6 border-b-2 border-transparent hover:border-gray-300 font-medium text-gray-500 hover:text-gray-700" data-tab="profile">
                                Profile
                            </button>
                            <button class="tab-btn text-left w-1/6 py-4 px-6 border-b-2 border-transparent hover:border-gray-300 font-medium text-gray-500 hover:text-gray-700" data-tab="security">
                                Security
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- General Settings -->
                <div class="tab-content active bg-white shadow rounded-lg p-6" id="general-tab">
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">General Settings</h2>
                        <p class="text-gray-600">Manage your website's basic information and appearance.</p>
                    </div>
                    
                    <form id="generalSettingsForm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="siteName" class="block text-sm font-medium text-gray-700 mb-1">Website Name</label>
                                <input type="text" id="siteName" value="Trauma to Triumph" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                            </div>
                            
                            <div>
                                <label for="siteTagline" class="block text-sm font-medium text-gray-700 mb-1">Tagline</label>
                                <input type="text" id="siteTagline" value="Transforming Trauma into Strength" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label for="siteDescription" class="block text-sm font-medium text-gray-700 mb-1">Website Description</label>
                            <textarea id="siteDescription" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">Helping individuals transform trauma into triumph through education and support.</textarea>
                            <p class="mt-1 text-sm text-gray-500">Brief description of your website for SEO purposes.</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <label for="primaryColor" class="block text-sm font-medium text-gray-700 mb-1">Primary Color</label>
                                <div class="flex items-center">
                                    <input type="color" id="primaryColor" value="#6c63ff" class="h-10 w-10 rounded border border-gray-300 cursor-pointer">
                                    <input type="text" value="#6c63ff" class="ml-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                </div>
                            </div>
                            
                            <div>
                                <label for="secondaryColor" class="block text-sm font-medium text-gray-700 mb-1">Secondary Color</label>
                                <div class="flex items-center">
                                    <input type="color" id="secondaryColor" value="#ff6584" class="h-10 w-10 rounded border border-gray-300 cursor-pointer">
                                    <input type="text" value="#ff6584" class="ml-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                </div>
                            </div>
                            
                            <div>
                                <label for="accentColor" class="block text-sm font-medium text-gray-700 mb-1">Accent Color</label>
                                <div class="flex items-center">
                                    <input type="color" id="accentColor" value="#4caf50" class="h-10 w-10 rounded border border-gray-300 cursor-pointer">
                                    <input type="text" value="#4caf50" class="ml-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">Logo</label>
                                <div class="flex items-center">
                                    <div class="w-16 h-16 mr-4 bg-gray-100 rounded-md flex items-center justify-center overflow-hidden">
                                        <img src="assets/logo.png" alt="Current Logo" id="currentLogo" class="max-w-full max-h-full">
                                    </div>
                                    <div class="flex-1">
                                        <label for="logoUpload" class="relative cursor-pointer bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm font-medium text-gray-700 hover:bg-gray-50">
                                            <span>Upload New Logo</span>
                                            <input id="logoUpload" type="file" accept="image/*" class="sr-only">
                                        </label>
                                        <p class="mt-1 text-xs text-gray-500">PNG or JPG, max 1MB</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">Favicon</label>
                                <div class="flex items-center">
                                    <div class="w-16 h-16 mr-4 bg-gray-100 rounded-md flex items-center justify-center overflow-hidden">
                                        <img src="assets/favicon.png" alt="Current Favicon" id="currentFavicon" class="max-w-full max-h-full">
                                    </div>
                                    <div class="flex-1">
                                        <label for="faviconUpload" class="relative cursor-pointer bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm font-medium text-gray-700 hover:bg-gray-50">
                                            <span>Upload New Favicon</span>
                                            <input id="faviconUpload" type="file" accept="image/*" class="sr-only">
                                        </label>
                                        <p class="mt-1 text-xs text-gray-500">PNG, max 256x256px</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Social Media Links</h3>
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <div class="w-10 flex-shrink-0">
                                        <i class="fab fa-facebook text-xl text-blue-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <input type="text" value="https://facebook.com/traumatotriumph" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-10 flex-shrink-0">
                                        <i class="fab fa-instagram text-xl text-pink-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <input type="text" value="https://instagram.com/traumatotriumph" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-10 flex-shrink-0">
                                        <i class="fab fa-twitter text-xl text-blue-400"></i>
                                    </div>
                                    <div class="flex-1">
                                        <input type="text" value="https://twitter.com/traumatotriumph" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-10 flex-shrink-0">
                                        <i class="fab fa-youtube text-xl text-red-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <input type="text" value="https://youtube.com/traumatotriumph" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="button" class="mr-3 py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                Reset to Default
                            </button>
                            <button type="submit" class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Home Page Settings -->
                <div class="tab-content hidden bg-white shadow rounded-lg p-6" id="home-tab">
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Home Page Settings</h2>
                        <p class="text-gray-600">Customize your website's landing page content and appearance.</p>
                    </div>
                    
                    <form id="homePageSettingsForm">
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Hero Section</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                                <div>
                                    <label for="heroTitle" class="block text-sm font-medium text-gray-700 mb-1">Hero Title</label>
                                    <input type="text" id="heroTitle" value="Transform Your Trauma Into Strength" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                </div>
                                <div>
                                    <label for="heroSubtitle" class="block text-sm font-medium text-gray-700 mb-1">Hero Subtitle</label>
                                    <input type="text" id="heroSubtitle" value="Find healing, growth, and resilience through our resources and community" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="heroDescription" class="block text-sm font-medium text-gray-700 mb-1">Hero Description</label>
                                <textarea id="heroDescription" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">Join our compassionate community where together we transform challenging experiences into pathways for growth, resilience, and personal empowerment.</textarea>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="ctaButtonText" class="block text-sm font-medium text-gray-700 mb-1">CTA Button Text</label>
                                    <input type="text" id="ctaButtonText" value="Get Started" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                </div>
                                <div>
                                    <label for="ctaButtonLink" class="block text-sm font-medium text-gray-700 mb-1">CTA Button Link</label>
                                    <input type="text" id="ctaButtonLink" value="/courses" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                </div>
                            </div>
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Hero Background Image</label>
                                <div class="flex items-center">
                                    <div class="w-24 h-16 mr-4 bg-gray-100 rounded-md flex items-center justify-center overflow-hidden">
                                        <img src="assets/hero-bg.jpg" alt="Current Hero Image" id="currentHeroImage" class="max-w-full max-h-full">
                                    </div>
                                    <div class="flex-1">
                                        <label for="heroImageUpload" class="relative cursor-pointer bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm font-medium text-gray-700 hover:bg-gray-50">
                                            <span>Upload New Image</span>
                                            <input id="heroImageUpload" type="file" accept="image/*" class="sr-only">
                                        </label>
                                        <p class="mt-1 text-xs text-gray-500">Recommended size: 1920x1080px</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Features Section</h3>
                            <p class="text-gray-600 mb-4">Add up to 4 feature boxes highlighting your main offerings</p>
                            
                            <div class="border border-gray-200 rounded-md mb-4">
                                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex justify-between items-center">
                                    <h4 class="font-medium">Feature 1</h4>
                                    <button type="button" class="text-gray-400 hover:text-gray-500">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </div>
                                <div class="p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div class="md:col-span-1">
                                            <label for="feature1Icon" class="block text-sm font-medium text-gray-700 mb-1">Icon</label>
                                            <select id="feature1Icon" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                                <option value="fa-heart">Heart</option>
                                                <option value="fa-book" selected>Book</option>
                                                <option value="fa-users">Users</option>
                                                <option value="fa-comments">Comments</option>
                                                <option value="fa-star">Star</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-3">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label for="feature1Title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                                    <input type="text" id="feature1Title" value="Educational Resources" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                                </div>
                                                <div>
                                                    <label for="feature1Link" class="block text-sm font-medium text-gray-700 mb-1">Link (optional)</label>
                                                    <input type="text" id="feature1Link" value="/resources" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                                </div>
                                            </div>
                                            <div class="mt-4">
                                                <label for="feature1Description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                                <textarea id="feature1Description" rows="2" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">Access our comprehensive library of articles, guides, and courses designed to support your healing journey.</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="border border-gray-200 rounded-md mb-4">
                                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex justify-between items-center">
                                    <h4 class="font-medium">Feature 2</h4>
                                    <button type="button" class="text-gray-400 hover:text-gray-500">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </div>
                                <div class="p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div class="md:col-span-1">
                                            <label for="feature2Icon" class="block text-sm font-medium text-gray-700 mb-1">Icon</label>
                                            <select id="feature2Icon" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                                <option value="fa-heart">Heart</option>
                                                <option value="fa-book">Book</option>
                                                <option value="fa-users" selected>Users</option>
                                                <option value="fa-comments">Comments</option>
                                                <option value="fa-star">Star</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-3">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label for="feature2Title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                                    <input type="text" id="feature2Title" value="Supportive Community" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                                </div>
                                                <div>
                                                    <label for="feature2Link" class="block text-sm font-medium text-gray-700 mb-1">Link (optional)</label>
                                                    <input type="text" id="feature2Link" value="/community" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                                </div>
                                            </div>
                                            <div class="mt-4">
                                                <label for="feature2Description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                                <textarea id="feature2Description" rows="2" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">Connect with others on similar journeys, share experiences, and find strength in our compassionate community.</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" class="flex items-center text-purple-600 hover:text-purple-800">
                                <i class="fas fa-plus-circle mr-2"></i>
                                <span>Add Another Feature</span>
                            </button>
                        </div>
                        
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Testimonials Section</h3>
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <label for="showTestimonials" class="text-sm font-medium text-gray-700 mr-2">Show Testimonials</label>
                                    <div class="toggle-switch">
                                        <input type="checkbox" id="showTestimonials" checked>
                                        <label for="showTestimonials" class="toggle-label"></label>
                                    </div>
                                </div>
                                <button type="button" class="text-sm font-medium text-purple-600 hover:text-purple-800">
                                    Manage All Testimonials
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="border border-gray-200                                rounded-md p-4">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <img src="assets/testimonial1.jpg" alt="User" class="h-12 w-12 rounded-full">
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <h4 class="font-medium text-gray-900">Sarah Johnson</h4>
                                                    <p class="text-sm text-gray-500">@sarahj</p>
                                                </div>
                                                <button type="button" class="text-gray-400 hover:text-gray-600">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <p class="mt-2 text-sm text-gray-600">"This platform completely changed my perspective on healing. The resources are incredible!"</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="border border-gray-200 rounded-md p-4">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <img src="assets/testimonial2.jpg" alt="User" class="h-12 w-12 rounded-full">
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <h4 class="font-medium text-gray-900">Michael Chen</h4>
                                                    <p class="text-sm text-gray-500">@michaelc</p>
                                                </div>
                                                <button type="button" class="text-gray-400 hover:text-gray-600">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <p class="mt-2 text-sm text-gray-600">"The community support here is unmatched. Truly a life-changing experience."</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" class="mt-4 flex items-center text-purple-600 hover:text-purple-800">
                                <i class="fas fa-plus-circle mr-2"></i>
                                <span>Add Testimonial</span>
                            </button>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Call to Action Section</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                                <div>
                                    <label for="ctaTitle" class="block text-sm font-medium text-gray-700 mb-1">CTA Title</label>
                                    <input type="text" id="ctaTitle" value="Start Your Healing Journey Today" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                </div>
                                <div>
                                    <label for="ctaSubtitle" class="block text-sm font-medium text-gray-700 mb-1">CTA Subtitle</label>
                                    <input type="text" id="ctaSubtitle" value="Join thousands of others finding strength through our resources" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="finalCtaText" class="block text-sm font-medium text-gray-700 mb-1">Button Text</label>
                                    <input type="text" id="finalCtaText" value="Begin Healing Now" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                </div>
                                <div>
                                    <label for="finalCtaLink" class="block text-sm font-medium text-gray-700 mb-1">Button Link</label>
                                    <input type="text" id="finalCtaLink" value="/signup" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="button" class="mr-3 py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                Reset Section
                            </button>
                            <button type="submit" class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.getElementById('menuToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('hidden');
        });

        // User dropdown toggle
        document.getElementById('userMenuBtn').addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('userDropdown').classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('userDropdown');
            if (!e.target.closest('#userMenuBtn') && !dropdown.classList.contains('hidden')) {
                dropdown.classList.add('hidden');
            }
        });

        // Tab functionality
        document.querySelectorAll('.tab-btn').forEach(button => {
            button.addEventListener('click', function() {
                const tab = this.dataset.tab;
                
                // Remove active class from all tabs and buttons
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                    content.classList.add('hidden');
                });
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.classList.remove('border-purple-500', 'text-purple-600');
                    btn.classList.add('border-transparent', 'text-gray-500');
                });

                // Add active class to selected tab and button
                document.getElementById(`${tab}-tab`).classList.remove('hidden');
                document.getElementById(`${tab}-tab`).classList.add('active');
                this.classList.add('border-purple-500', 'text-purple-600');
                this.classList.remove('border-transparent', 'text-gray-500');
            });
        });
    </script>
</body>
</html>