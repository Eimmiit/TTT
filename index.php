<?php
// Include database connection
require_once "config.php";

// Determine which form was submitted based on POST data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['form_type'])) {
        $form_type = $_POST['form_type'];
        
        switch ($form_type) {
            case 'contact':
                handleContactForm($pdo);
                break;
            case 'course_registration':
                handleCourseRegistration($pdo);
                break;
            case 'donation':
                handleDonation($pdo);
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid form submission.']);
                break;
        }
        exit; // Stop execution after handling AJAX request
    } else {
        echo json_encode(['success' => false, 'message' => 'Form type not specified.']);
        exit;
    }
}

// Function to handle the contact form submission
function handleContactForm($pdo) {
    // Get form data and sanitize inputs
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
    $service = isset($_POST['service']) ? htmlspecialchars(trim($_POST['service'])) : '';
    $message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';
    $consent = isset($_POST['consent']) ? 1 : 0;
    $status = 'new'; // Default status for new inquiries
    $submission_date = date('Y-m-d H:i:s');
    
    // Validate the required fields
    $errors = [];
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    if (empty($service)) {
        $errors[] = "Service selection is required";
    }
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    if (!$consent) {
        $errors[] = "You must consent to be contacted";
    }
    
    // If no errors, proceed with database insertion
    if (empty($errors)) {
        try {
            // Prepare SQL statement to insert contact inquiry
            $sql = "INSERT INTO contact_inquiries (name, email, service_interest, message, consent, status, submission_date) 
                    VALUES (:name, :email, :service, :message, :consent, :status, :submission_date)";
                    
            $stmt = $pdo->prepare($sql);
            
            // Bind parameters
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':service', $service);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':consent', $consent);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':submission_date', $submission_date);
            
            // Execute the statement
            $result = $stmt->execute();
            
            if ($result) {
                // Return success response (for AJAX)
                echo json_encode(['success' => true, 'message' => 'Thank you for reaching out. We\'ll be in touch within 24-48 hours.']);
            } else {
                // Return error response (for AJAX)
                echo json_encode(['success' => false, 'message' => 'Failed to submit form. Please try again.']);
            }
        } catch(PDOException $e) {
            // Log the error and return generic error message
            error_log("Database Error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'System error. Please try again later or contact us directly by phone.']);
        }
    } else {
        // Return validation errors (for AJAX)
        echo json_encode(['success' => false, 'message' => 'Please correct the following errors:', 'errors' => $errors]);
    }
}

// Function to handle course registration
function handleCourseRegistration($pdo) {
  // Get form data and sanitize inputs
  $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
  $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
  $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
  $phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : '';
  $special_needs = isset($_POST['special_needs']) ? htmlspecialchars(trim($_POST['special_needs'])) : '';
  $registration_date = date('Y-m-d H:i:s');
  $status = 'pending'; // Default status for new registrations
  
  // Validate the required fields
  $errors = [];
  if ($course_id <= 0) {
      $errors[] = "Invalid course selection";
  }
  if (empty($name)) {
      $errors[] = "Name is required";
  }
  if (empty($email)) {
      $errors[] = "Email is required";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = "Invalid email format";
  }
  
  // If no errors, proceed with database insertion
  if (empty($errors)) {
      try {
          // Start transaction
          $pdo->beginTransaction();
          
          // First, check if the course exists and has available spots
          $courseCheckSql = "SELECT id, title, capacity, price,
                            (SELECT COUNT(*) FROM course_registrations WHERE course_id = courses.id) as registered_count
                            FROM courses WHERE id = :course_id AND status = 'active'";
          $courseCheck = $pdo->prepare($courseCheckSql);
          $courseCheck->bindParam(':course_id', $course_id);
          $courseCheck->execute();
          $course = $courseCheck->fetch(PDO::FETCH_ASSOC);
          
          if (!$course) {
              $pdo->rollBack();
              echo json_encode(['success' => false, 'message' => 'Course not found or no longer active.']);
              exit;
          }
          
          // Check if course is full
          if ($course['registered_count'] >= $course['capacity']) {
              $pdo->rollBack();
              echo json_encode(['success' => false, 'message' => 'Sorry, this course is currently full. Please check back later or choose another course.']);
              exit;
          }
          
          // Check if user already registered for this course
          $checkDuplicateSql = "SELECT id FROM course_registrations 
                                WHERE course_id = :course_id AND email = :email";
          $checkDuplicate = $pdo->prepare($checkDuplicateSql);
          $checkDuplicate->bindParam(':course_id', $course_id);
          $checkDuplicate->bindParam(':email', $email);
          $checkDuplicate->execute();
          
          if ($checkDuplicate->rowCount() > 0) {
              $pdo->rollBack();
              echo json_encode(['success' => false, 'message' => 'You are already registered for this course.']);
              exit;
          }
          
          // Prepare SQL statement to insert registration
          $sql = "INSERT INTO course_registrations (course_id, name, email, phone, special_needs, registration_date, status) 
                  VALUES (:course_id, :name, :email, :phone, :special_needs, :registration_date, :status)";
                  
          $stmt = $pdo->prepare($sql);
          
          // Bind parameters
          $stmt->bindParam(':course_id', $course_id);
          $stmt->bindParam(':name', $name);
          $stmt->bindParam(':email', $email);
          $stmt->bindParam(':phone', $phone);
          $stmt->bindParam(':special_needs', $special_needs);
          $stmt->bindParam(':registration_date', $registration_date);
          $stmt->bindParam(':status', $status);
          
          // Execute the statement
          $result = $stmt->execute();
          
          if ($result) {
              // Commit transaction
              $pdo->commit();
              
              // Return success response (for AJAX)
              echo json_encode([
                  'success' => true, 
                  'message' => 'Thank you for registering for "' . $course['title'] . '". We\'ll send confirmation details to your email shortly.'
              ]);
              
              // In a production environment, you would send a confirmation email here
          } else {
              $pdo->rollBack();
              // Return error response (for AJAX)
              echo json_encode(['success' => false, 'message' => 'Failed to register. Please try again.']);
          }
      } catch(PDOException $e) {
          if ($pdo->inTransaction()) {
              $pdo->rollBack();
          }
          // Log the error and return generic error message
          error_log("Database Error: " . $e->getMessage());
          echo json_encode(['success' => false, 'message' => 'System error. Please try again later or contact us directly by phone.']);
      }
  } else {
      // Return validation errors (for AJAX)
      echo json_encode(['success' => false, 'message' => 'Please correct the following errors:', 'errors' => $errors]);
  }
}

// Function to handle donation submissions
function handleDonation($pdo) {
    // Get form data and sanitize inputs
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $type = isset($_POST['type']) ? htmlspecialchars(trim($_POST['type'])) : 'one-time';
    $dedicated = isset($_POST['dedicated']) ? intval($_POST['dedicated']) : 0; 
    $designation = isset($_POST['designation']) ? htmlspecialchars(trim($_POST['designation'])) : 'general';
    $comment = isset($_POST['comment']) ? htmlspecialchars(trim($_POST['comment'])) : '';
    $status = 'pending'; // Default status for new donation intents
    $donation_date = date('Y-m-d H:i:s');
    
    // Validate the amount
    if ($amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid donation amount.']);
        exit;
    }
    
    try {
        // Prepare SQL statement to insert donation intent
        $sql = "INSERT INTO donations (amount, type, dedicated, designation, comment, status, donation_date) 
                VALUES (:amount, :type, :dedicated, :designation, :comment, :status, :donation_date)";
                
        $stmt = $pdo->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':dedicated', $dedicated);
        $stmt->bindParam(':designation', $designation);
        $stmt->bindParam(':comment', $comment);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':donation_date', $donation_date);
        
        // Execute the statement
        $stmt->execute();
        
        // Get the ID of the inserted record
        $donation_id = $pdo->lastInsertId();
        
        // Return success with donation ID (which could be used for tracking in payment processor)
        echo json_encode([
            'success' => true, 
            'message' => 'Thank you for your donation!',
            'donation_id' => $donation_id
        ]);
        
    } catch(PDOException $e) {
        // Log the error and return generic error message
        error_log("Database Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'System error. Please try again later.']);
    }
}

// Fetch the latest articles from the database
try {
    $sql = "SELECT id, title, excerpt, image_url, DATE_FORMAT(published_date, '%M %d, %Y') AS formatted_date 
            FROM articles 
            WHERE status = 'published'
            ORDER BY published_date DESC
            LIMIT 3";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Simply initialize empty array if query fails - don't kill the page
    error_log("Database Error: " . $e->getMessage());
    $articles = [];
}// Fetch active courses from the database
try {
  $sql = "SELECT id, title, description, capacity, start_date, 
          FORMAT(price, 2) as formatted_price,
          (SELECT COUNT(*) FROM course_registrations WHERE course_id = courses.id) as registered_count 
          FROM courses 
          WHERE status = 'active' /* AND start_date > CURDATE() */
          ORDER BY start_date ASC
          LIMIT 4";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  // Debug: Print the number of courses found
  error_log("Found " . count($courses) . " courses");
} catch(PDOException $e) {
  // Initialize empty array if query fails
  error_log("Database Error: " . $e->getMessage());
  $courses = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trauma to Triumph | Healing Through Compassion</title>
  <meta name="description" content="Trauma-informed coaching and support services for survivors. Reclaim your strength with compassionate guidance." />
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700&family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet" />
  <!-- Custom Tailwind Config -->
   <!-- Add this script to your HTML (use sandbox client ID for testing) -->
<script src="https://www.paypal.com/sdk/js?client-id=AbyKDtf8hDSwxu-A2vELQBVca5T8bhObxp6TkbWqA2PXhx_wXfD6TOhqi4hbBywlLdNXwibw3fjx6vXi&currency=USD"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: {
              50: '#f0f9ff',
              100: '#e0f2fe',
              200: '#bae6fd',
              300: '#7dd3fc',
              400: '#38bdf8',
              500: '#0ea5e9',
              600: '#0284c7',
              700: '#0369a1',
              800: '#075985',
              900: '#0c4a6e',
            },
            secondary: {
              50: '#fdf2f8',
              100: '#fce7f3',
              200: '#fbcfe8',
              300: '#f9a8d4',
              400: '#f472b6',
              500: '#ec4899',
              600: '#db2777',
              700: '#be185d',
              800: '#9d174d',
              900: '#831843',
            },
            neutral: {
              50: '#fafafa',
              100: '#f5f5f5',
              200: '#e5e5e5',
              300: '#d4d4d4',
              400: '#a3a3a3',
              500: '#737373',
              600: '#525252',
              700: '#404040',
              800: '#262626',
              900: '#171717',
            },
          },
          fontFamily: {
            heading: ['Merriweather', 'serif'],
            body: ['Open Sans', 'sans-serif'],
          },
        },
      },
    };
  </script>
  <style>
    /* Custom styles that are difficult with Tailwind */
    .checkmark__circle {
      stroke-dasharray: 166;
      stroke-dashoffset: 166;
      stroke-width: 2;
      stroke-miterlimit: 10;
      stroke: #4ade80;
      fill: none;
      animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
    }
    .checkmark__check {
      transform-origin: 50% 50%;
      stroke-dasharray: 48;
      stroke-dashoffset: 48;
      stroke-width: 3;
      stroke: #4ade80;
      animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
    }
    @keyframes stroke {
      100% {
        stroke-dashoffset: 0;
      }
    }
  </style>
</head>
<body class="font-body text-neutral-800 bg-neutral-50">
  <!-- Skip to content link -->
  <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:p-4 focus:bg-primary-600 focus:text-white focus:z-50">Skip to main content</a>

  <!-- Mobile Sidebar Navigation -->
  <div id="mobileSidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out">
    <div class="p-6 space-y-4">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
          <img src="assets/logo.png" alt="Trauma to Triumph logo" class="w-10 h-10 mr-2" />
          <span class="font-heading font-bold text-lg">Trauma to Triumph</span>
        </div>
        <button id="closeMobileMenu" class="text-2xl text-neutral-600">&times;</button>
      </div>
      <nav>
        <ul class="space-y-4">
          <li><a href="#" class="block py-2 text-primary-700 hover:text-primary-900">Home</a></li>
          <li><a href="#about" class="block py-2 hover:text-primary-700">About</a></li>
          <li><a href="#services" class="block py-2 hover:text-primary-700">Services</a></li>
          <li><a href="#courses" class="block py-2 hover:text-primary-700">Courses</a></li>
          <li><a href="#testimonials" class="block py-2 hover:text-primary-700">Testimonials</a></li>
          <li><a href="#contact" class="block py-2 hover:text-primary-700">Contact</a></li>
          <li><a href="#articles" class="block py-2 hover:text-primary-700">Articles</a></li>
          <li><a href="#donate" id="donateBtnMobile" class="block mt-4 py-3 px-6 bg-primary-600 text-white rounded-md text-center hover:bg-primary-700 transition" href="#">Donate</a></li>
        </ul>
      </nav>
    </div>
  </div>

  <!-- Header -->
  <header id="header" class="sticky top-0 z-40 bg-white shadow-md">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center py-4">
        <a href="#" class="flex items-center">
          <img src="assets/logo.png" alt="Trauma to Triumph logo" class="w-10 h-10 mr-2" />
          <span class="font-heading font-bold text-lg hidden sm:inline">Trauma to Triumph</span>
        </a>

        <div class="flex items-center">
          <!-- Desktop Navigation -->
          <nav class="hidden lg:block">
            <ul class="flex space-x-6">
              <li><a href="#" class="text-primary-700 hover:text-primary-900">Home</a></li>
              <li><a href="#about" class="hover:text-primary-700">About</a></li>
              <li><a href="#services" class="hover:text-primary-700">Services</a></li>
              <li><a href="#courses" class="hover:text-primary-700">Courses</a></li>
              <li><a href="#testimonials" class="hover:text-primary-700">Testimonials</a></li>
              <li><a href="#contact" class="hover:text-primary-700">Contact</a></li>
              <li><a href="#articles" class="hover:text-primary-700">Articles</a></li>
            </ul>
          </nav>
          
          <!-- Donate button (visible on desktop) -->
          <a href="#" id="donateBtn" class="hidden lg:block ml-6 py-2 px-4 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition">Donate</a>
          
          <!-- Mobile menu toggle -->
          <button id="mobileMenuToggle" class="lg:hidden ml-4 text-neutral-600 focus:outline-none" aria-label="Menu" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </header>
<!-- Donation Panel with PayPal Integration -->
<div id="donationOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4 mt-3">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 relative">
    <button id="closeDonation" class="absolute top-4 right-4 text-2xl text-neutral-600 hover:text-neutral-800 transition">&times;</button>
    
    <h2 class="font-heading text-2xl font-bold mb-2">Support Our Mission</h2>
    <p class="text-neutral-600 mb-6">Your donation helps us provide trauma-informed care to survivors</p>
    
    <div class="flex border-b border-neutral-200 mb-4">
      <button class="tab-btn active py-2 px-4 font-medium border-b-2 border-primary-600" data-tab="one-time">One-time</button>
      <button class="tab-btn py-2 px-4 font-medium text-neutral-500 hover:text-neutral-700 transition" data-tab="monthly">Monthly</button>
    </div>
    
    <div id="one-time-tab" class="donation-tab">
      <h3 class="font-medium text-center mb-4">Choose your donation amount</h3>
      <div class="grid grid-cols-3 gap-3 mb-6">
        <button class="amount-btn py-3 px-2 border border-primary-200 rounded-md hover:bg-primary-50 transition" data-amount="50">$50</button>
        <button class="amount-btn py-3 px-2 border border-primary-200 rounded-md hover:bg-primary-50 transition" data-amount="100">$100</button>
        <button class="amount-btn py-3 px-2 border border-primary-200 rounded-md hover:bg-primary-50 transition" data-amount="250">$250</button>
        </div>
    </div>
    
    <div id="monthly-tab" class="donation-tab hidden">
      <h3 class="font-medium text-center mb-4">Choose your monthly donation</h3>
      <div class="grid grid-cols-3 gap-3 mb-6">
        <button class="amount-btn py-3 px-2 border border-primary-200 rounded-md hover:bg-primary-50 transition" data-amount="10">$10</button>
        <button class="amount-btn py-3 px-2 border border-primary-200 rounded-md hover:bg-primary-50 transition" data-amount="25">$25</button>
        <button class="amount-btn py-3 px-2 border border-primary-200 rounded-md hover:bg-primary-50 transition" data-amount="50">$50</button>
        </div>
    </div>
    
    <div class="mb-6">
      <label for="customAmount" class="block text-sm font-medium mb-2">Or enter custom amount:</label>
      <div class="relative">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-500">$</span>
        <input type="number" id="customAmount" placeholder="100" class="w-full pl-8 pr-4 py-3 border border-neutral-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
      </div>
    </div>
    
    <div class="space-y-4 mb-6">
      <div class="flex items-center">
        <input type="checkbox" id="dedicateDonation" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 rounded">
        <label for="dedicateDonation" class="ml-2 text-sm">Dedicate this donation</label>
      </div>
      
      <div id="dedicationFields" class="pl-6 space-y-3 hidden">
        <div>
          <label for="dedicationType" class="block text-sm mb-1">Dedication type:</label>
          <select id="dedicationType" class="w-full px-3 py-2 border border-neutral-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            <option value="honor">In honor of</option>
            <option value="memory">In memory of</option>
          </select>
        </div>
        
        <div>
          <label for="dedicationName" class="block text-sm mb-1">Name:</label>
          <input type="text" id="dedicationName" class="w-full px-3 py-2 border border-neutral-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
        </div>
      </div>
      
      <div>
        <label for="donationDesignation" class="block text-sm font-medium mb-1">Designate to:</label>
        <select id="donationDesignation" class="w-full px-3 py-2 border border-neutral-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
          <option value="general">Where it is needed most</option>
          <option value="scholarships">Survivor Scholarships</option>
          <option value="programs">Healing Programs</option>
          <option value="outreach">Community Outreach</option>
        </select>
      </div>
      
      <div>
        <label for="donationComment" class="block text-sm font-medium mb-1">Add comment (optional):</label>
        <textarea id="donationComment" rows="2" class="w-full px-3 py-2 border border-neutral-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
      </div>
    </div>
    
    <div class="mb-6">
    <h3 class="font-medium text-center mb-4">Payment Method</h3>
    <div id="paypal-button-container" class="mt-4"></div>
    <p class="text-xs text-center mt-2 text-neutral-500">Secure payment processing via PayPal</p>
  </div>

    
    <button id="submitDonation" class="w-full py-3 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition font-medium flex items-center justify-center">
      <span>Donate Now with PayPal</span>
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
      </svg>
    </button>
    
    <p class="text-xs text-center mt-4 text-neutral-500">Your donation is tax-deductible. A receipt will be emailed to you.</p>
  </div>
</div>

  <!-- Hero Section -->
  <section class="hero bg-gradient-to-r from-primary-600 to-primary-800 text-white py-20 lg:py-32" id="main-content">
    <div class="container mx-auto px-4">
      <div class="max-w-2xl mx-auto text-center">
        <h1 class="font-heading text-3xl md:text-4xl lg:text-5xl font-bold mb-6">Empowering Your Journey to Healing</h1>
        <p class="text-lg md:text-xl mb-8 text-primary-50">
          Trauma-informed coaching and support services designed to help you
          reclaim your strength and build resilience through compassionate,
          lived-experience guidance.
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
          <a href="#services" class="py-3 px-6 bg-white text-primary-700 rounded-md font-medium hover:bg-primary-50 transition">Explore Services</a>
          <a href="#contact" class="py-3 px-6 bg-primary-500 text-white rounded-md font-medium hover:bg-primary-400 transition">Book a Consult</a>
        </div>
      </div>
    </div>
  </section>

  <!-- About Section -->
<section class="py-20 lg:py-32 bg-gradient-to-b from-primary-50 to-white" id="about">
  <div class="container mx-auto px-4 max-w-7xl">
    <div class="grid md:grid-cols-2 gap-16 items-center">
      <!-- Image Column -->
      <div class="order-2 md:order-1 relative group">
        <div class="absolute inset-0 bg-primary-100/30 rounded-2xl transform rotate-2 translate-x-4 translate-y-4 group-hover:rotate-1 transition-all"></div>
        <img src="assets/eliza.jpg" alt="Coach Eliza - A compassionate trauma-informed coach" 
             class="w-full rounded-2xl shadow-xl relative transform transition-all duration-300 group-hover:-translate-y-2" 
             loading="lazy" />
      </div>

      <!-- Content Column -->
      <div class="order-1 md:order-2 space-y-8">
        <h2 class="font-heading text-4xl lg:text-5xl font-bold bg-gradient-to-r from-primary-600 to-primary-800 bg-clip-text text-transparent">
          My Journey & Philosophy
        </h2>

        <div class="prose lg:prose-lg max-w-none text-gray-700">
          <p class="text-xl font-semibold text-primary-700 mb-8">
            This is My Sacred Work. This is My Soul's Purpose.
          </p>

          <div class="space-y-6">
            <p class="leading-relaxed">
              <span class="font-medium text-gray-900">I stand before you not because the path was easy,</span> 
              but because I chose to rise. From the suffocating silence, 
              through systems that sought to erase me, past the grasp of those 
              who denied my humanity - I emerged, unbroken.
            </p>

            <div class="relative pl-6 border-l-4 border-primary-500/30">
              <p class="text-gray-900 italic font-medium">
                "I am both survivor and guide.<br>
                A weaver of reclaimed destinies.<br>
                A sacred mirror reflecting strength<br>
                For those still finding their light."
              </p>
            </div>

            <p class="leading-relaxed">
              As a survivor of human trafficking, I've transformed pain into purpose. 
              My approach is rooted in radical empathy and empowered storytelling - 
              creating spaces where darkness transforms into dawn.
            </p>
          </div>
        </div>

        <!-- Read More Button -->
        <button id="readMoreToggle" 
                class="mt-6 inline-flex items-center group font-semibold text-primary-700 hover:text-primary-900 transition-colors"
                aria-expanded="false">
          <span>Continue My Story</span>
          <svg xmlns="http://www.w3.org/2000/svg" 
               class="h-5 w-5 ml-2 transform group-hover:translate-x-1 transition-transform" 
               viewBox="0 0 20 20" 
               fill="currentColor">
            <path fill-rule="evenodd" 
                  d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" 
                  clip-rule="evenodd"/>
          </svg>
        </button>
      </div>
    </div>
  </div>
</section>

  <!-- Services Section -->
  <section class="py-16 lg:py-24 bg-neutral-50" id="services">
    <div class="container mx-auto px-4">
      <div class="text-center mb-12">
        <h2 class="font-heading text-3xl font-bold mb-4">Coaching & Support Services</h2>
        <p class="text-lg text-neutral-600">Comprehensive trauma-informed services tailored to your unique needs</p>
      </div>

      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
          <div class="text-primary-600 text-3xl mb-4">
            <i class="fas fa-star"></i>
          </div>
          <h3 class="font-heading text-xl font-bold mb-3">1:1 Coaching Sessions</h3>
          <p class="text-neutral-600 mb-4">
            Personalized trauma recovery coaching that meets you where you are
            in your healing journey, available in-person or virtually.
          </p>
          <button class="learn-more-btn text-primary-600 hover:text-primary-800 font-medium" data-service="1">
            Learn More
          </button>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
          <div class="text-primary-600 text-3xl mb-4">
            <i class="fas fa-shield-alt"></i>
          </div>
          <h3 class="font-heading text-xl font-bold mb-3">Appointment Accompaniment</h3>
          <p class="text-neutral-600 mb-4">
            Support during medical, legal, or therapeutic appointments to help
            you feel safe and empowered.
          </p>
          <button class="learn-more-btn text-primary-600 hover:text-primary-800 font-medium" data-service="2">
            Learn More
          </button>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
          <div class="text-primary-600 text-3xl mb-4">
            <i class="fas fa-fist-raised"></i>
          </div>
          <h3 class="font-heading text-xl font-bold mb-3">Empowerment Classes</h3>
          <p class="text-neutral-600 mb-4">
            Trauma-informed self-defense and boundary-setting workshops designed
            to rebuild confidence and personal agency.
          </p>
          <button class="learn-more-btn text-primary-600 hover:text-primary-800 font-medium" data-service="3">
            Learn More
          </button>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
          <div class="text-primary-600 text-3xl mb-4">
            <i class="fas fa-hands-helping"></i>
          </div>
          <h3 class="font-heading text-xl font-bold mb-3">Wraparound Support</h3>
          <p class="text-neutral-600 mb-4">
            Holistic assistance connecting you with resources and creating a
            personalized support network.
          </p>
          <button class="learn-more-btn text-primary-600 hover:text-primary-800 font-medium" data-service="4">
            Learn More
          </button>
        </div>
      </div>
    </div>
  </section>

  <section class="py-16 lg:py-24 bg-white" id="articles">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="font-heading text-3xl font-bold mb-4">Latest Articles</h2>
            <p class="text-lg text-neutral-600">Insights and resources for your healing journey</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8" id="articlesContainer">
            <?php foreach ($articles as $article): ?>
                <div class="bg-neutral-50 rounded-lg shadow-md overflow-hidden">
                    <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-full h-48 object-cover" loading="lazy" />
                    <div class="p-6">
                        <p class="text-primary-600 text-sm mb-2"><?php echo htmlspecialchars($article['formatted_date']); ?></p>
                        <h3 class="font-heading text-xl font-bold mb-3"><?php echo htmlspecialchars($article['title']); ?></h3>
                        <p class="text-neutral-600 mb-4"><?php echo htmlspecialchars($article['excerpt']); ?></p>
                        <a href="article.php?id=<?php echo $article['id']; ?>" class="text-primary-600 hover:text-primary-800 font-medium">Read More →</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

  <!-- Progress Tracker -->
  <section class="py-16 lg:py-24 bg-neutral-50">
    <div class="container mx-auto px-4">
      <h2 class="font-heading text-3xl font-bold mb-12 text-center">Your Healing Journey</h2>
      
      <div class="relative max-w-3xl mx-auto">
        <!-- Progress line -->
        <div class="absolute left-4 lg:left-1/2 transform lg:-translate-x-1/2 top-0 bottom-0 w-1 bg-neutral-200"></div>
        <!-- Progress fill -->
        <div class="absolute left-4 lg:left-1/2 transform lg:-translate-x-1/2 top-0 w-1 bg-primary-500 h-1/4"></div>
        
        <!-- Milestones -->
        <div class="space-y-16">
          <!-- Milestone 1 -->
          <div class="relative pl-16 lg:pl-0">
            <div class="lg:grid lg:grid-cols-2 lg:gap-8 items-center">
              <div class="lg:text-right lg:pr-16">
                <h3 class="font-heading text-xl font-bold">Initial Assessment</h3>
                <p class="text-primary-600">Completed on 02/15/25</p>
              </div>
              <div class="absolute left-0 lg:left-1/2 transform lg:-translate-x-1/2 w-8 h-8 rounded-full bg-primary-500 text-white flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
          </div>
          
          <!-- Milestone 2 -->
          <div class="relative pl-16 lg:pl-0">
            <div class="lg:grid lg:grid-cols-2 lg:gap-8 items-center">
              <div class="lg:text-right lg:pr-16">
                <h3 class="font-heading text-xl font-bold">Boundary Setting</h3>
                <p class="text-primary-600">In progress</p>
              </div>
              <div class="absolute left-0 lg:left-1/2 transform lg:-translate-x-1/2 w-8 h-8 rounded-full bg-white border-2 border-primary-500 text-primary-600 flex items-center justify-center font-medium">
                2
              </div>
            </div>
          </div>
          
          <!-- Milestone 3 -->
          <div class="relative pl-16 lg:pl-0">
            <div class="lg:grid lg:grid-cols-2 lg:gap-8 items-center">
              <div class="lg:text-right lg:pr-16">
                <h3 class="font-heading text-xl font-bold">Emotional Regulation</h3>
              </div>
              <div class="absolute left-0 lg:left-1/2 transform lg:-translate-x-1/2 w-8 h-8 rounded-full bg-white border-2 border-neutral-300 text-neutral-600 flex items-center justify-center font-medium">
                3
              </div>
            </div>
          </div>
          
          <!-- Milestone 4 -->
          <div class="relative pl-16 lg:pl-0">
            <div class="lg:grid lg:grid-cols-2 lg:gap-8 items-center">
              <div class="lg:text-right lg:pr-16">
                <h3 class="font-heading text-xl font-bold">Integration</h3>
              </div>
              <div class="absolute left-0 lg:left-1/2 transform lg:-translate-x-1/2 w-8 h-8 rounded-full bg-white border-2 border-neutral-300 text-neutral-600 flex items-center justify-center font-medium">
                4
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Courses Section -->
<section class="py-16 lg:py-24 bg-white" id="courses">
  <div class="container mx-auto px-4">
    <div class="text-center mb-12">
      <h2 class="font-heading text-3xl font-bold mb-4">Courses & Workshops</h2>
      <p class="text-lg text-neutral-600">Upcoming educational offerings to support your healing journey</p>
    </div>

    <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
      <?php 
      // Debug information
      if (!isset($courses) || !is_array($courses)) {
        $courses = [];
        error_log("Courses variable is not properly set");
      }
      
      if (empty($courses)): ?>
        <div class="col-span-2 text-center py-8">
          <p>No upcoming courses available at this time. Please check back later.</p>
        </div>
      <?php else: ?>
        <?php foreach ($courses as $course): ?>
          <div class="bg-neutral-50 rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <h3 class="font-heading text-xl font-bold mb-3"><?php echo htmlspecialchars($course['title']); ?></h3>
            <p class="text-neutral-600 mb-4"><?php echo htmlspecialchars($course['description']); ?></p>
            <div class="flex justify-between items-center mb-4">
              <p class="font-bold">Next session: <?php echo date('F, Y', strtotime($course['start_date'])); ?></p>
              <p class="text-primary-600"><?php echo ($course['formatted_price'] == '0.00') ? 'Free' : '$' . $course['formatted_price']; ?></p>
            </div>
            <p class="text-sm mb-4"><?php echo $course['registered_count']; ?>/<?php echo $course['capacity']; ?> spots filled</p>
            <button class="register-course-btn w-full py-3 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition" 
        data-course-id="<?php echo $course['id']; ?>" 
        data-course-title="<?php echo htmlspecialchars($course['title']); ?>"
        <?php echo ($course['registered_count'] >= $course['capacity']) ? 'disabled' : ''; ?>>
    <?php echo ($course['registered_count'] >= $course['capacity']) ? 'Course Full' : 'Register Now'; ?>
</button>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

  <!-- Testimonials Section -->
  <section class="py-16 lg:py-24 bg-neutral-50" id="testimonials">
    <div class="container mx-auto px-4">
      <div class="text-center mb-12">
        <h2 class="font-heading text-3xl font-bold mb-4">Client Experiences</h2>
        <p class="text-lg text-neutral-600">Hear from those who've walked this path</p>
      </div>

      <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-primary-500">
          <p class="italic mb-4">
            "Working with Eliza transformed my healing journey. Their lived
            experience combined with professional knowledge created a safe space
            where I finally felt understood."
          </p>
          <p class="font-bold">- Jamie S.</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-primary-500">
          <p class="italic mb-4">
            "The self-defense class was about so much more than physical skills—it
            helped me reconnect with my body in a way I didn't think was possible
            after trauma."
          </p>
          <p class="font-bold">- Taylor M.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section class="py-16 lg:py-24 bg-primary-100" id="contact">
    <div class="container mx-auto px-4">
      <div class="text-center mb-12">
        <h2 class="font-heading text-3xl font-bold mb-4">Begin Your Healing Journey</h2>
        <p class="text-lg text-neutral-600">Reach out for a consultation or with any questions</p>
      </div>

      <div class="max-w-md mx-auto">
        <form id="contactForm" class="bg-white rounded-lg shadow-md p-6 md:p-8">
          <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-neutral-700 mb-1">Full Name</label>
            <input type="text" id="name" name="name" required class="w-full px-3 py-2 border border-neutral-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
          </div>
          
          <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-neutral-700 mb-1">Email</label>
            <input type="email" id="email" name="email" required class="w-full px-3 py-2 border border-neutral-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
          </div>
          
          <div class="mb-4">
            <label for="service" class="block text-sm font-medium text-neutral-700 mb-1">Service Interest</label>
            <select id="service" name="service" required class="w-full px-3 py-2 border border-neutral-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
              <option value="">Please select...</option>
              <option value="coaching">1:1 Coaching</option>
              <option value="accompaniment">Appointment Accompaniment</option>
              <option value="empowerment">Empowerment Classes</option>
              <option value="wraparound">Wraparound Support</option>
              <option value="other">Other (please specify)</option>
            </select>
          </div>
          
          <div class="mb-6">
            <label for="message" class="block text-sm font-medium text-neutral-700 mb-1">Message</label>
            <textarea id="message" name="message" rows="4" required class="w-full px-3 py-2 border border-neutral-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
          </div>
          
          <div class="flex items-center mb-6">
            <input type="checkbox" id="consent" name="consent" required class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 rounded">
            <label for="consent" class="ml-2 text-sm text-neutral-700">I consent to be contacted about my inquiry</label>
          </div>
          
          <button type="submit" class="w-full py-3 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition">
            Send Message
          </button>
        </form>
      </div>
    </div>
  </section>

  <!-- Emergency Resources Section -->
  <section class="py-12 bg-white">
    <div class="container mx-auto px-4">
      <div class="max-w-4xl mx-auto">
        <h2 class="font-heading text-2xl font-bold mb-6 text-center">Emergency Resources</h2>
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
          <p class="font-bold mb-4">If you're in immediate danger, please call emergency services:</p>
          <div class="grid md:grid-cols-2 gap-4 mb-6">
            <div>
              <p class="font-bold">United States:</p>
              <ul class="list-disc pl-5 space-y-2">
                <li>Emergency: 911</li>
                <li>National Domestic Violence Hotline: 1-800-799-7233</li>
                <li>National Human Trafficking Hotline: 1-888-373-7888</li>
              </ul>
            </div>
            <div>
              <p class="font-bold">International:</p>
              <ul class="list-disc pl-5 space-y-2">
                <li>Find local emergency numbers</li>
                <li>UN Human Rights Council: +41 22 917 9220</li>
                <li>International Red Cross: +41 22 730 4222</li>
              </ul>
            </div>
          </div>
          <p class="text-sm italic">This site is not a crisis service. These resources are provided for emergency situations only.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Success Message Modal (hidden by default) -->
  <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
      <div class="text-center">
        <div class="mb-4 inline-block">
          <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 52 52">
            <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
            <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
          </svg>
        </div>
        <h3 class="font-heading text-xl font-bold mb-2">Message Sent Successfully!</h3>
        <p class="text-neutral-600 mb-6">Thank you for reaching out. We'll be in touch within 24-48 hours.</p>
        <button id="closeSuccessModal" class="py-2 px-4 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition">Close</button>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-neutral-800 text-white py-12">
    <div class="container mx-auto px-4">
      <div class="grid md:grid-cols-4 gap-8">
        <div>
          <h3 class="font-heading text-lg font-bold mb-4">Trauma to Triumph</h3>
          <p class="text-neutral-300 mb-4">
            Trauma-informed coaching and support for survivors on their healing journey.
          </p>
          <div class="flex space-x-4">
            <a href="#" class="text-neutral-300 hover:text-white">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" class="text-neutral-300 hover:text-white">
              <i class="fab fa-twitter"></i>
            </a>
            <a href="#" class="text-neutral-300 hover:text-white">
              <i class="fab fa-instagram"></i>
            </a>
          </div>
        </div>
        
        <div>
          <h3 class="font-heading text-lg font-bold mb-4">Quick Links</h3>
          <ul class="space-y-2">
            <li><a href="#" class="text-neutral-300 hover:text-white">Home</a></li>
            <li><a href="#about" class="text-neutral-300 hover:text-white">About</a></li>
            <li><a href="#services" class="text-neutral-300 hover:text-white">Services</a></li>
            <li><a href="#courses" class="text-neutral-300 hover:text-white">Courses</a></li>
          </ul>
        </div>
        
        <div>
          <h3 class="font-heading text-lg font-bold mb-4">Resources</h3>
          <ul class="space-y-2">
            <li><a href="#articles" class="text-neutral-300 hover:text-white">Articles</a></li>
            <li><a href="#" class="text-neutral-300 hover:text-white">Privacy Policy</a></li>
            <li><a href="#" class="text-neutral-300 hover:text-white">Terms of Service</a></li>
            <li><a href="#donate" class="text-neutral-300 hover:text-white">Support Our Work</a></li>
          </ul>
        </div>
        
        <div>
          <h3 class="font-heading text-lg font-bold mb-4">Contact</h3>
          <address class="not-italic text-neutral-300 space-y-2">
            <p>1234 Healing Path</p>
            <p>Phoenix, AZ 85001</p>
            <p>Email: <a href="mailto:info@traumatotriumph.org" class="hover:text-white">info@traumatotriumph.org</a></p>
            <p>Phone: (555) 123-4567</p>
          </address>
        </div>
      </div>
      
      <div class="border-t border-neutral-700 mt-8 pt-8 text-center">
        <p class="text-neutral-400">
          &copy; 2025 Trauma to Triumph. All rights reserved.
        </p>
      </div>
    </div>
  </footer>
<!-- Course Registration Modal -->
<div id="courseRegistrationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 relative">
        <button id="closeCourseRegistration" class="absolute top-4 right-4 text-2xl text-neutral-600">&times;</button>
        
        <h2 id="modalCourseTitle" class="font-heading text-2xl font-bold mb-2">Course Registration</h2>
        <p class="text-neutral-600 mb-6">Complete the form below to register for this course</p>
        
        <form id="courseRegistrationForm">
            <input type="hidden" id="course_id" name="course_id">
            
            <div class="mb-4">
                <label for="reg_name" class="block text-sm font-medium text-neutral-700 mb-1">Full Name</label>
                <input type="text" id="reg_name" name="name" required class="w-full px-3 py-2 border border-neutral-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
            </div>
            
            <div class="mb-4">
                <label for="reg_email" class="block text-sm font-medium text-neutral-700 mb-1">Email</label>
                <input type="email" id="reg_email" name="email" required class="w-full px-3 py-2 border border-neutral-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
            </div>
            
            <div class="mb-4">
                <label for="reg_phone" class="block text-sm font-medium text-neutral-700 mb-1">Phone Number (optional)</label>
                <input type="tel" id="reg_phone" name="phone" class="w-full px-3 py-2 border border-neutral-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
            </div>
            
            <div class="mb-6">
                <label for="reg_special_needs" class="block text-sm font-medium text-neutral-700 mb-1">Special Accommodations (optional)</label>
                <textarea id="reg_special_needs" name="special_needs" rows="3" class="w-full px-3 py-2 border border-neutral-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
            </div>
            
            <input type="hidden" name="form_type" value="course_registration">
            
            <button type="submit" class="w-full py-3 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition">
                Complete Registration
            </button>
        </form>
    </div>
</div>
  <script src="js/main.js"></script>
  <script>
    // Mobile menu functionality
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mobileSidebar = document.getElementById('mobileSidebar');
    const closeMobileMenu = document.getElementById('closeMobileMenu');
    
    mobileMenuToggle.addEventListener('click', () => {
      mobileSidebar.classList.remove('-translate-x-full');
    });
    
    closeMobileMenu.addEventListener('click', () => {
      mobileSidebar.classList.add('-translate-x-full');
    });
    
    // Read more toggle functionality
    const readMoreToggle = document.getElementById('readMoreToggle');
    const poeticContent = document.getElementById('poeticContent');
    
    let isExpanded = false;
    readMoreToggle.addEventListener('click', () => {
      isExpanded = !isExpanded;
      if (isExpanded) {
        // Add the extended content
        poeticContent.innerHTML += `
          <p class="mb-4">
            My trauma-informed approach isn't theoretical—it's lived. When I speak of
            trafficking, I am not reciting statistics but remembering the concrete
            floor beneath my body. When I teach boundaries, I am showing you the
            hard-won edges I've had to carve with a desperate kind of precision.
          </p>
          <p class="mb-4">
            In this journey, I don't promise fixes or cures.
            I offer instead a companionship in the darkness,
            a hand that has felt its way along similar walls,
            a presence that knows the particular silence of aftermath.
          </p>
          <p class="mb-4">
            Come as you are. Broken or numb,
            raging or silent, disbelieving or desperately hopeful.
            This space holds it all.
          </p>`;
        
        readMoreToggle.innerHTML = `Read Less 
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1 transform rotate-180" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>`;
      } else {
        // Reset to original content
        poeticContent.innerHTML = `
          <p class="text-lg mb-4">This is My Work. This is My Life.</p>

          <p class="mb-4">
            I am not here because it was easy. I am here because I lived.
            Because I clawed my way back from silence, from systems that
            devoured me, from the hands of those who never saw my humanity.
          </p>

          <p class="mb-4">
            I am a survivor of human trafficking.<br />
            But I am not just a survivor.<br />
            I am a weaver of worlds.<br />
            A witness to the unspeakable.<br />
            A sacred mirror for those who are still crawling out of the dark.
          </p>`;
          
        readMoreToggle.innerHTML = `Read More 
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>`;
      }
      readMoreToggle.setAttribute('aria-expanded', isExpanded);
    });
    
    // Contact form submission
    const contactForm = document.getElementById('contactForm');
    const successModal = document.getElementById('successModal');
    const closeSuccessModal = document.getElementById('closeSuccessModal');
    
    contactForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Get form data
      const formData = new FormData(contactForm);
      formData.append('form_type', 'contact');
      
      // Submit the form via AJAX
      fetch('index.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Show success modal
          successModal.classList.remove('hidden');
          // Reset form
          contactForm.reset();
        } else {
          // Handle errors
          alert(data.message);
          console.error(data.errors);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again later.');
      });
    });
    
    // Close success modal
    closeSuccessModal.addEventListener('click', () => {
      successModal.classList.add('hidden');
    });
    
    // Course registration handlers
    const registerCourse1 = document.getElementById('registerCourse1');
    const registerCourse2 = document.getElementById('registerCourse2');
    
    registerCourse1.addEventListener('click', () => {
      // Here you would typically open a modal with a registration form
      alert('Registration form for "Understanding Trauma" will open here.');
      // In a real implementation, you'd show a modal with a form that submits with form_type=course_registration
    });
    
    registerCourse2.addEventListener('click', () => {
      alert('Registration form for "Boundary Setting Workshop" will open here.');
    });
    
    
    // Service learn more buttons
    const learnMoreBtns = document.querySelectorAll('.learn-more-btn');
    learnMoreBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        const serviceId = btn.dataset.service;
        // Here you would show more information about the selected service
        alert(`More information about service ${serviceId} will be shown here.`);
      });
    });

// Course registration modal handlers
const courseRegistrationModal = document.getElementById('courseRegistrationModal');
const closeCourseRegistration = document.getElementById('closeCourseRegistration');
const courseRegistrationForm = document.getElementById('courseRegistrationForm');
const modalCourseTitle = document.getElementById('modalCourseTitle');

// Add event listeners for all register buttons
document.querySelectorAll('.register-course-btn').forEach(button => {
    button.addEventListener('click', () => {
        if (!button.disabled) {
            // Set the course ID in the hidden field
            document.getElementById('course_id').value = button.dataset.courseId;
            // Set the course title in the modal
            modalCourseTitle.textContent = `Register for: ${button.dataset.courseTitle}`;
            // Show the modal
            courseRegistrationModal.classList.remove('hidden');
        }
    });
});

// Close course registration modal
closeCourseRegistration.addEventListener('click', () => {
    courseRegistrationModal.classList.add('hidden');
    courseRegistrationForm.reset();
});
// Handle course registration form submission
courseRegistrationForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form data
    const formData = new FormData(courseRegistrationForm);
    
    // Submit the form via AJAX
    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            alert(data.message);
            // Reset form and close modal
            courseRegistrationForm.reset();
            courseRegistrationModal.classList.add('hidden');
        } else {
            // Handle errors
            alert(data.message);
            console.error(data.errors);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again later.');
    });
});
  </script>

  <!-- PayPal Button Container -->
  <div id="paypal-button-container" class="mt-4"></div>
</body>
</html>
