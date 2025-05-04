<?php
// Include database connection
require_once "config.php";

// Get article ID from URL parameter
$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no valid ID provided, redirect to homepage
if ($article_id <= 0) {
    header("Location: index.php");
    exit;
}

// Fetch the article from the database
try {
    $sql = "SELECT id, title, content, image_url, DATE_FORMAT(published_date, '%M %d, %Y') AS formatted_date, 
                   author_name, author_bio, author_image, 
                   (SELECT COUNT(*) FROM article_comments WHERE article_id = articles.id) AS comment_count,
                   reading_time
            FROM articles 
            WHERE id = :id AND status = 'published'";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":id", $article_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // If article doesn't exist, redirect to homepage
    if ($stmt->rowCount() == 0) {
        header("Location: index.php");
        exit;
    }
    
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Parse content to create table of contents
    $content = $article['content'];
    $headings = [];
    preg_match_all('/<h([2-3])[^>]*>(.*?)<\/h\1>/', $content, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        $level = $match[1];
        $text = strip_tags($match[2]);
        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $text));
        $headings[] = [
            'level' => $level,
            'text' => $text,
            'slug' => $slug
        ];
        
        // Replace headings in content with anchored headings
        $content = str_replace(
            $match[0],
            "<h{$level} id=\"{$slug}\">{$match[2]}</h{$level}>",
            $content
        );
    }
    $article['content'] = $content;
    
    // Fetch related articles
    $sql = "SELECT id, title, excerpt, image_url, DATE_FORMAT(published_date, '%M %d, %Y') AS formatted_date,
            category, reading_time 
            FROM articles 
            WHERE id != :id AND status = 'published'
            ORDER BY published_date DESC
            LIMIT 3";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":id", $article_id, PDO::PARAM_INT);
    $stmt->execute();
    $related_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("ERROR: Could not fetch article. " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($article['title']); ?> | Trauma to Triumph</title>
  <meta name="description" content="<?php echo htmlspecialchars(substr(strip_tags($article['content']), 0, 160)); ?>" />
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700&family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet" />
  <!-- Tailwind Config -->
  <script>
    tailwind.config = {
      darkMode: 'class',
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
          typography: (theme) => ({
            DEFAULT: {
              css: {
                'code::before': {
                  content: 'none',
                },
                'code::after': {
                  content: 'none',
                },
                maxWidth: 'none',
                a: {
                  color: theme('colors.primary.600'),
                  '&:hover': {
                    color: theme('colors.primary.800'),
                  },
                },
                h2: {
                  fontFamily: theme('fontFamily.heading'),
                  fontWeight: theme('fontWeight.bold'),
                  marginTop: '2em',
                },
                h3: {
                  fontFamily: theme('fontFamily.heading'),
                  fontWeight: theme('fontWeight.bold'),
                  marginTop: '1.5em',
                },
                img: {
                  borderRadius: theme('borderRadius.lg'),
                },
                blockquote: {
                  borderLeftColor: theme('colors.primary.600'),
                  backgroundColor: theme('colors.primary.50'),
                  borderRadius: theme('borderRadius.md'),
                  padding: theme('spacing.4'),
                },
              },
            },
            dark: {
              css: {
                color: theme('colors.neutral.300'),
                a: {
                  color: theme('colors.primary.400'),
                  '&:hover': {
                    color: theme('colors.primary.300'),
                  },
                },
                h1: {
                  color: theme('colors.white'),
                },
                h2: {
                  color: theme('colors.white'),
                },
                h3: {
                  color: theme('colors.white'),
                },
                blockquote: {
                  borderLeftColor: theme('colors.primary.500'),
                  backgroundColor: theme('colors.neutral.800'),
                  color: theme('colors.neutral.300'),
                },
                strong: {
                  color: theme('colors.white'),
                },
              },
            },
          }),
        },
      },
      plugins: [
        require('@tailwindcss/typography'),
      ],
    };
  </script>
  <style>
    /* Progress bar styles */
    .progress-container {
      width: 100%;
      height: 5px;
      background: transparent;
      position: fixed;
      top: 64px;
      left: 0;
      z-index: 50;
    }
    
    .progress-bar {
      height: 5px;
      background: #0ea5e9;
      width: 0%;
    }
    
    /* Table of contents */
    .toc-list {
      list-style: none;
      padding: 0;
    }
    
    .toc-list li {
      margin-bottom: 0.5rem;
    }
    
    .toc-h2 {
      margin-left: 0;
    }
    
    .toc-h3 {
      margin-left: 1.5rem;
      font-size: 0.9em;
    }
    
    /* Animation classes */
    .fade-in {
      animation: fadeIn 0.5s ease-in-out;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    /* Selection color */
    ::selection {
      background-color: #bae6fd;
      color: #0c4a6e;
    }
    
    .dark ::selection {
      background-color: #0369a1;
      color: #e0f2fe;
    }
    
    /* Reading helper */
    .reading-focus {
      transition: all 0.3s ease;
    }
    
    .reading-focus:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    
    /* Custom scrollbar */
    ::-webkit-scrollbar {
      width: 12px;
    }
    
    ::-webkit-scrollbar-track {
      background: #f5f5f5;
    }
    
    ::-webkit-scrollbar-thumb {
      background-color: #a3a3a3;
      border-radius: 6px;
      border: 3px solid #f5f5f5;
    }
    
    .dark ::-webkit-scrollbar-track {
      background: #262626;
    }
    
    .dark ::-webkit-scrollbar-thumb {
      background-color: #525252;
      border: 3px solid #262626;
    }
    
    /* Code blocks styling */
    pre {
      border-radius: 0.5rem;
      padding: 1rem;
      margin: 1.5rem 0;
      overflow-x: auto;
    }
    
    code {
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
      font-size: 0.9em;
      padding: 0.2em 0.4em;
      border-radius: 0.25rem;
      background-color: #f5f5f5;
    }
    
    .dark code {
      background-color: #262626;
    }
    
    /* Image lightbox effect */
    .article-content img {
      cursor: pointer;
      transition: filter 0.3s ease;
    }
    
    .article-content img:hover {
      filter: brightness(1.05);
    }
    
    /* Share button pulse animation */
    .pulse-on-hover:hover {
      animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.05); }
      100% { transform: scale(1); }
    }
  </style>
</head>

<body class="font-body text-neutral-800 bg-neutral-50 dark:bg-neutral-900 dark:text-neutral-200">
  <!-- Skip to content link -->
  <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:p-4 focus:bg-primary-600 focus:text-white focus:z-50">Skip to main content</a>

  <!-- Progress bar -->
  <div class="progress-container">
    <div class="progress-bar" id="readingProgress"></div>
  </div>

  <!-- Mobile Sidebar Navigation -->
  <div id="mobileSidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-white dark:bg-neutral-800 shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto">
    <div class="p-6 space-y-4">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
          <img src="assets/logo.png" alt="Trauma to Triumph logo" class="w-10 h-10 mr-2" />
          <span class="font-heading font-bold text-lg dark:text-white">Trauma to Triumph</span>
        </div>
        <button id="closeMobileMenu" class="text-2xl text-neutral-600 dark:text-neutral-400">&times;</button>
      </div>
      <nav>
        <ul class="space-y-4">
          <li><a href="index.php" class="block py-2 hover:text-primary-700 dark:text-neutral-300 dark:hover:text-primary-400">Home</a></li>
          <li><a href="index.php#about" class="block py-2 hover:text-primary-700 dark:text-neutral-300 dark:hover:text-primary-400">About</a></li>
          <li><a href="index.php#services" class="block py-2 hover:text-primary-700 dark:text-neutral-300 dark:hover:text-primary-400">Services</a></li>
          <li><a href="index.php#courses" class="block py-2 hover:text-primary-700 dark:text-neutral-300 dark:hover:text-primary-400">Courses</a></li>
          <li><a href="index.php#testimonials" class="block py-2 hover:text-primary-700 dark:text-neutral-300 dark:hover:text-primary-400">Testimonials</a></li>
          <li><a href="index.php#contact" class="block py-2 hover:text-primary-700 dark:text-neutral-300 dark:hover:text-primary-400">Contact</a></li>
          <li><a href="index.php#articles" class="block py-2 text-primary-700 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300">Articles</a></li>
          <li><a href="index.php#donate" class="block mt-4 py-3 px-6 bg-primary-600 text-white rounded-md text-center hover:bg-primary-700 transition">Donate</a></li>
        </ul>
      </nav>
      
      <!-- Dark mode toggle in mobile menu -->
      <div class="mt-6 pt-6 border-t border-neutral-200 dark:border-neutral-700">
        <button id="darkModeMobileToggle" class="flex items-center w-full py-2 px-4 text-left rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-700 transition">
          <span id="darkModeIconMobile" class="mr-3">
            <i class="far fa-moon dark:hidden"></i>
            <i class="fas fa-sun hidden dark:inline"></i>
          </span>
          <span id="darkModeTextMobile">Dark Mode</span>
        </button>
      </div>
      
      <!-- Table of contents for mobile -->
      <?php if (!empty($headings)): ?>
      <div class="mt-6 pt-6 border-t border-neutral-200 dark:border-neutral-700">
        <h4 class="font-semibold mb-3 text-neutral-700 dark:text-neutral-300">Table of Contents</h4>
        <ul class="toc-list text-sm">
          <?php foreach ($headings as $heading): ?>
            <li class="toc-h<?php echo $heading['level']; ?>">
              <a href="#<?php echo $heading['slug']; ?>" class="hover:text-primary-600 dark:hover:text-primary-400" onclick="closeMobileMenu()">
                <?php echo htmlspecialchars($heading['text']); ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Header -->
  <header id="header" class="sticky top-0 z-40 bg-white dark:bg-neutral-800 shadow-md">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center py-4">
        <a href="index.php" class="flex items-center">
          <img src="assets/logo.png" alt="Trauma to Triumph logo" class="w-10 h-10 mr-2" />
          <span class="font-heading font-bold text-lg hidden sm:inline dark:text-white">Trauma to Triumph</span>
        </a>

        <div class="flex items-center">
          <!-- Dark mode toggle -->
          <button id="darkModeToggle" class="p-2 text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 focus:outline-none mr-3" aria-label="Toggle dark mode">
            <i class="far fa-moon dark:hidden"></i>
            <i class="fas fa-sun hidden dark:inline"></i>
          </button>
          
          <!-- Desktop Navigation -->
          <nav class="hidden lg:block">
            <ul class="flex space-x-6">
              <li><a href="index.php" class="hover:text-primary-700 dark:text-neutral-300 dark:hover:text-primary-400">Home</a></li>
              <li><a href="index.php#about" class="hover:text-primary-700 dark:text-neutral-300 dark:hover:text-primary-400">About</a></li>
              <li><a href="index.php#services" class="hover:text-primary-700 dark:text-neutral-300 dark:hover:text-primary-400">Services</a></li>
              <li><a href="index.php#courses" class="hover:text-primary-700 dark:text-neutral-300 dark:hover:text-primary-400">Courses</a></li>
              <li><a href="index.php#testimonials" class="hover:text-primary-700 dark:text-neutral-300 dark:hover:text-primary-400">Testimonials</a></li>
              <li><a href="index.php#contact" class="hover:text-primary-700 dark:text-neutral-300 dark:hover:text-primary-400">Contact</a></li>
              <li><a href="index.php#articles" class="text-primary-700 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300">Articles</a></li>
            </ul>
          </nav>
          
          <!-- Donate button (visible on desktop) -->
          <a href="index.php#donate" id="donateBtn" class="hidden lg:block ml-6 py-2 px-4 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition">Donate</a>
          
          <!-- Mobile menu toggle -->
          <button id="mobileMenuToggle" class="lg:hidden ml-4 text-neutral-600 dark:text-neutral-300 focus:outline-none" aria-label="Menu" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main id="main-content">
    <!-- Article Header -->
    <div class="bg-gradient-to-r from-primary-700 to-primary-600 text-white py-12">
      <div class="container mx-auto px-4">
        <a href="index.php#articles" class="inline-flex items-center text-primary-100 mb-4 hover:text-white transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
          </svg>
          Back to Articles
        </a>
        <h1 class="font-heading text-3xl md:text-4xl lg:text-5xl font-bold fade-in"><?php echo htmlspecialchars($article['title']); ?></h1>
        <div class="mt-6 flex flex-wrap items-center">
          <div class="mr-6 flex items-center mb-3 md:mb-0">
            <img src="<?php echo htmlspecialchars($article['author_image']); ?>" alt="<?php echo htmlspecialchars($article['author_name']); ?>" class="w-10 h-10 rounded-full border-2 border-white" />
            <div class="ml-3">
              <p class="font-medium"><?php echo htmlspecialchars($article['author_name']); ?></p>
              <p class="text-primary-200 text-sm"><?php echo htmlspecialchars($article['formatted_date']); ?></p>
            </div>
          </div>
          <div class="flex flex-wrap gap-4">
            <div class="flex items-center">
              <i class="far fa-clock mr-2"></i>
              <span class="text-sm"><?php echo htmlspecialchars($article['reading_time'] ?? '5 min read'); ?></span>
            </div>
            <?php if (isset($article['comment_count'])): ?>
            <div class="flex items-center">
              <i class="far fa-comment mr-2"></i>
              <span class="text-sm"><?php echo intval($article['comment_count']); ?> comments</span>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Article Content -->
    <div class="container mx-auto px-4 py-8">
      <div class="flex flex-col md:flex-row">
        <!-- Sidebar with TOC (desktop) -->
        <aside class="hidden md:block w-64 flex-shrink-0 sticky self-start top-24 mr-8">
          <!-- Table of contents for desktop -->
          <?php if (!empty($headings)): ?>
          <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm p-6 mb-6">
            <h3 class="font-heading text-lg font-bold mb-4 dark:text-white">Table of Contents</h3>
            <nav>
              <ul class="toc-list">
                <?php foreach ($headings as $heading): ?>
                  <li class="toc-h<?php echo $heading['level']; ?>">
                    <a href="#<?php echo $heading['slug']; ?>" class="text-neutral-700 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 transition">
                      <?php echo htmlspecialchars($heading['text']); ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </nav>
          </div>
          <?php endif; ?>
          
          <!-- Reading controls -->
          <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm p-4 mb-6">
            <h3 class="font-medium text-sm uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-3">Reading Tools</h3>
            <div class="space-y-3">
              <button id="fontSizeIncrease" class="w-full text-left flex justify-between items-center px-3 py-2 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-700 transition">
                <span>Increase Font</span>
                <i class="fas fa-plus-circle"></i>
              </button>
              <button id="fontSizeDecrease" class="w-full text-left flex justify-between items-center px-3 py-2 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-700 transition">
                <span>Decrease Font</span>
                <i class="fas fa-minus-circle"></i>
              </button>
              <button id="toggleReadingFocus" class="w-full text-left flex justify-between items-center px-3 py-2 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-700 transition">
                <span>Reading Focus</span>
                <i class="fas fa-glasses"></i>
              </button>
            </div>
          </div>
          
          <!-- Share buttons (vertical, desktop) -->
          <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm p-4">
            <h3 class="font-medium text-sm uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-3">Share Article</h3>
            <div class="flex flex-col space-y-2">
              <a href="#" onclick="shareArticle('facebook')" class="flex items-center p-2 rounded-md hover:bg-blue-100 dark:hover:bg-blue-900 text-blue-600 dark:text-blue-400 transition">
                <i class="fab fa-facebook-f w-6"></i>
                <span class="ml-2">Facebook</span>
              </a>
              <a href="#" onclick="shareArticle('twitter')" class="flex items-center p-2 rounded-md hover:bg-blue-100 dark:hover:bg-blue-900 text-blue-400 dark:text-blue-300 transition">
                <i class="fab fa-twitter w-6"></i>
                <span class="ml-2">Twitter</span>
              </a>
              <a href="#" onclick="shareArticle('linkedin')" class="flex items-center p-2 rounded-md hover:bg-blue-100 dark:hover:bg-blue-900 text-blue-700 dark:text-blue-500 transition">
                <i class="fab fa-linkedin-in w-6"></i>
                <span class="ml-2">LinkedIn</span>
              </a>
              <a href="#" onclick="shareArticle('email')" class="flex items-center p-2 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-700 text-neutral-600 dark:text-neutral-400 transition">
                <i class="fas fa-envelope w-6"></i>
                <span class="ml-2">Email</span>
              </a>
              <button onclick="copyArticleLink()" class="flex items-center p-2 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-700 text-neutral-600 dark:text-neutral-400 transition">
                <i class="fas fa-link w-6"></i>
                <span class="ml-2">Copy Link</span>
              </button>
            </div>
          </div>
        </aside>
        
        <!-- Main article content -->
        <div class="w-full md:max-w-3xl mx-auto">
          <!-- Featured image with caption -->
          <figure class="mb-8 fade-in">
            <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-full rounded-lg shadow-md" />
            <figcaption class="text-sm text-neutral-500 dark:text-neutral-400 mt-2 text-center italic">
              <?php echo htmlspecialchars($article['title']); ?> - Trauma to Triumph
            </figcaption>
          </figure>
          
          <!-- Article content -->
          <article class="prose prose-lg dark:prose-dark max-w-none article-content">
            <?php echo $article['content']; ?>
          </article>
          
          <!-- Tags (if available) -->
          <div class="mt-8 flex flex-wrap gap-2">
            <a href="#" class="px-3 py-1 bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200 text-sm rounded-full hover:bg-primary-200 dark:hover:bg-primary-800 transition">Trauma Recovery</a>
            <a href="#" class="px-3 py-1 bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200 text-sm rounded-full hover:bg-primary-200 dark:hover:bg-primary-800 transition">Healing</a>
            <a href="#" class="px-3 py-1 bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200 text-sm rounded-full hover:bg-primary-200 dark:hover:bg-primary-800 transition">Mental Health</a>
          </div>
          
          <!-- Mobile Share Buttons -->
          <div class="md:hidden mt-8 bg-white dark:bg-neutral-800 rounded-lg shadow-md p-4">
            <h3 class="font-heading text-lg font-bold mb-4 dark:text-white">Share This Article</h3>
            <div class="grid grid-cols-4 gap-2">
              <a href="#" onclick="shareArticle('facebook')" class="flex flex-col items-center p-3 rounded-md bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 hover:opacity-90 transition">
                <i class="fab fa-facebook-f text-xl"></i>
              </a>
              <a href="#" onclick="shareArticle('twitter')" class="flex flex-col items-center p-3 rounded-md bg-blue-100 dark:bg-blue-900 text-blue-400 dark:text-blue-300 hover:opacity-90 transition">
                <i class="fab fa-twitter text-xl"></i>
              </a>
              <a href="#" onclick="shareArticle('linkedin')" class="flex flex-col items-center p-3 rounded-md bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-500 hover:opacity-90 transition">
                <i class="fab fa-linkedin-in text-xl"></i>
              </a>
              <button onclick="copyArticleLink()" class="flex flex-col items-center p-3 rounded-md bg-neutral-100 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-400 hover:opacity-90 transition">
                <i class="fas fa-link text-xl"></i>
              </button>
            </div>
          </div>
          
          <!-- Author Bio -->
          <div class="mt-12 p-6 bg-white dark:bg-neutral-800 rounded-lg shadow-md">
            <div class="flex flex-col sm:flex-row items-start sm:items-center mb-4">
              <img src="<?php echo htmlspecialchars($article['author_image']); ?>" alt="<?php echo htmlspecialchars($article['author_name']); ?>" class="w-16 h-16 rounded-full mr-4 mb-4 sm:mb-0 border-2 border-primary-100 dark:border-primary-900" />
              <div>
                <h3 class="font-heading text-xl font-bold dark:text-white"><?php echo htmlspecialchars($article['author_name']); ?></h3>
                <p class="text-neutral-600 dark:text-neutral-400">Author</p>
                <div class="mt-2 flex space-x-3">
                  <a href="#" class="text-neutral-600 dark:text-neutral-400 hover:text-primary-600 dark:hover:text-primary-400 transition">
                    <i class="fab fa-twitter"></i>
                  </a>
                  <a href="#" class="text-neutral-600 dark:text-neutral-400 hover:text-primary-600 dark:hover:text-primary-400 transition">
                    <i class="fab fa-linkedin-in"></i>
                  </a>
                  <a href="#" class="text-neutral-600 dark:text-neutral-400 hover:text-primary-600 dark:hover:text-primary-400 transition">
                    <i class="fas fa-globe"></i>
                  </a>
                </div>
              </div>
            </div>
            <p class="text-neutral-700 dark:text-neutral-300"><?php echo htmlspecialchars($article['author_bio']); ?></p>
          </div>
          
          <!-- Newsletter signup -->
          <div class="mt-8 p-6 bg-gradient-to-r from-primary-600 to-primary-700 rounded-lg shadow-md text-white">
            <div class="flex flex-col md:flex-row items-center justify-between">
              <div class="mb-4 md:mb-0 md:pr-6">
                <h3 class="font-heading text-xl font-bold mb-2">Join our Newsletter</h3>
                <p class="text-primary-100">Get the latest articles and resources delivered to your inbox.</p>
              </div>
              <div class="w-full md:w-auto">
                <form class="flex flex-col sm:flex-row gap-2">
                  <input type="email" placeholder="Your email address" class="px-4 py-2 rounded-md text-neutral-800 focus:outline-none focus:ring-2 focus:ring-primary-300 w-full" required />
                  <button type="submit" class="bg-white text-primary-700 font-medium py-2 px-4 rounded-md hover:bg-primary-50 transition whitespace-nowrap">Subscribe</button>
                </form>
              </div>
            </div>
          </div>
          
          <!-- Comments section (placeholder) -->
          <div class="mt-12">
            <h3 class="font-heading text-2xl font-bold mb-6 dark:text-white">Comments</h3>
            
            <!-- Comment form -->
            <form class="mb-8 bg-white dark:bg-neutral-800 p-6 rounded-lg shadow-md">
              <div class="mb-4">
                <label for="comment" class="block text-neutral-700 dark:text-neutral-300 mb-2 font-medium">Leave a comment</label>
                <textarea id="comment" rows="4" class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white dark:bg-neutral-700 text-neutral-800 dark:text-neutral-200"></textarea>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                  <label for="name" class="block text-neutral-700 dark:text-neutral-300 mb-2 font-medium">Name</label>
                  <input type="text" id="name" class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white dark:bg-neutral-700 text-neutral-800 dark:text-neutral-200" />
                </div>
                <div>
                  <label for="email" class="block text-neutral-700 dark:text-neutral-300 mb-2 font-medium">Email</label>
                  <input type="email" id="email" class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white dark:bg-neutral-700 text-neutral-800 dark:text-neutral-200" />
                </div>
              </div>
              <button type="submit" class="bg-primary-600 text-white font-medium py-2 px-6 rounded-md hover:bg-primary-700 transition">Post Comment</button>
            </form>
            
            <!-- Comment list (sample comments) -->
            <div class="space-y-6">
              <div class="bg-white dark:bg-neutral-800 p-6 rounded-lg shadow-md">
                <div class="flex items-start space-x-4">
                  <div class="flex-shrink-0">
                    <img src="assets/avatar-1.jpg" alt="Comment author" class="w-12 h-12 rounded-full" onerror="this.src='assets/default-avatar.png'" />
                  </div>
                  <div class="flex-grow">
                    <div class="flex justify-between items-center mb-2">
                      <h4 class="font-medium dark:text-white">Sarah Johnson</h4>
                      <span class="text-sm text-neutral-500 dark:text-neutral-400">April 24, 2025</span>
                    </div>
                    <p class="text-neutral-700 dark:text-neutral-300">Thank you for this insightful article. The tips on managing anxiety have been incredibly helpful in my own journey.</p>
                    <div class="mt-2">
                      <button class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 font-medium">Reply</button>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="bg-white dark:bg-neutral-800 p-6 rounded-lg shadow-md">
                <div class="flex items-start space-x-4">
                  <div class="flex-shrink-0">
                    <img src="assets/avatar-2.jpg" alt="Comment author" class="w-12 h-12 rounded-full" onerror="this.src='assets/default-avatar.png'" />
                  </div>
                  <div class="flex-grow">
                    <div class="flex justify-between items-center mb-2">
                      <h4 class="font-medium dark:text-white">Michael Torres</h4>
                      <span class="text-sm text-neutral-500 dark:text-neutral-400">April 23, 2025</span>
                    </div>
                    <p class="text-neutral-700 dark:text-neutral-300">I've been struggling with this exact issue. Your perspective offers a new way of looking at trauma recovery that I hadn't considered before.</p>
                    <div class="mt-2">
                      <button class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 font-medium">Reply</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Related Articles -->
    <?php if (!empty($related_articles)): ?>
    <div class="bg-neutral-100 dark:bg-neutral-800 py-12 mt-12">
      <div class="container mx-auto px-4">
        <h2 class="font-heading text-2xl md:text-3xl font-bold mb-8 text-center dark:text-white">Related Articles</h2>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
          <?php foreach ($related_articles as $related): ?>
            <div class="bg-white dark:bg-neutral-700 rounded-lg shadow-md overflow-hidden transition transform hover:-translate-y-1 hover:shadow-lg reading-focus">
              <img src="<?php echo htmlspecialchars($related['image_url']); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>" class="w-full h-48 object-cover" />
              <div class="p-6">
                <div class="flex justify-between items-center mb-2">
                  <p class="text-primary-600 dark:text-primary-300 text-sm"><?php echo htmlspecialchars($related['formatted_date']); ?></p>
                  <span class="text-sm text-neutral-500 dark:text-neutral-400 flex items-center">
                    <i class="far fa-clock mr-1"></i>
                    <?php echo htmlspecialchars($related['reading_time'] ?? '5 min'); ?>
                  </span>
                </div>
                <h3 class="font-heading text-xl font-bold mb-3 dark:text-white"><?php echo htmlspecialchars($related['title']); ?></h3>
                <p class="text-neutral-600 dark:text-neutral-300 mb-4"><?php echo htmlspecialchars($related['excerpt']); ?></p>
                
                <?php if (isset($related['category'])): ?>
                <div class="mb-4">
                  <span class="inline-block bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 text-xs px-2 py-1 rounded-full">
                    <?php echo htmlspecialchars($related['category']); ?>
                  </span>
                </div>
                <?php endif; ?>
                
                <a href="article.php?id=<?php echo $related['id']; ?>" class="inline-flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 font-medium transition">
                  Read Article
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                  </svg>
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
    
    <!-- Call-to-action -->
    <div class="bg-primary-700 text-white py-16">
      <div class="container mx-auto px-4 text-center">
        <h2 class="font-heading text-3xl md:text-4xl font-bold mb-4">Ready to Begin Your Healing Journey?</h2>
        <p class="text-primary-100 max-w-2xl mx-auto mb-8">Our team of specialists is here to support you every step of the way. Contact us today to learn more about our services.</p>
        <div class="flex justify-center space-x-4 flex-wrap">
          <a href="index.php#contact" class="bg-white text-primary-700 py-3 px-8 rounded-md font-medium hover:bg-primary-50 transition mb-3 md:mb-0">Get in Touch</a>
          <a href="index.php#services" class="bg-transparent border-2 border-white text-white py-3 px-8 rounded-md font-medium hover:bg-primary-600 transition">Our Services</a>
        </div>
      </div>
    </div>
  </main>

  <!-- Back to top button -->
  <button id="backToTop" class="fixed bottom-6 right-6 bg-primary-600 text-white rounded-full p-3 shadow-lg opacity-0 invisible transition-all duration-300 hover:bg-primary-700">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
    </svg>
  </button>

  <!-- JavaScript for lightbox modal -->
  <div id="imageLightbox" class="fixed inset-0 z-50 bg-black bg-opacity-90 flex items-center justify-center invisible opacity-0 transition-opacity duration-300">
    <button id="closeLightbox" class="absolute top-4 right-4 text-white text-3xl hover:text-neutral-300">&times;</button>
    <img id="lightboxImage" src="" alt="Lightbox image" class="max-w-full max-h-[90vh]" />
    <button id="prevImage" class="absolute left-4 text-white text-5xl hover:text-neutral-300">&lsaquo;</button>
    <button id="nextImage" class="absolute right-4 text-white text-5xl hover:text-neutral-300">&rsaquo;</button>
  </div>

  <!-- Footer (same as index.php with dark mode support) -->
  <footer class="bg-neutral-800 text-white py-12">
    <div class="container mx-auto px-4">
      <div class="grid md:grid-cols-4 gap-8">
        <div>
          <h3 class="font-heading text-lg font-bold mb-4">Trauma to Triumph</h3>
          <p class="text-neutral-300 mb-4">
            Trauma-informed coaching and support for survivors on their healing journey.
          </p>
          <div class="flex space-x-4">
            <a href="#" class="text-neutral-300 hover:text-white transition">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" class="text-neutral-300 hover:text-white transition">
              <i class="fab fa-twitter"></i>
            </a>
            <a href="#" class="text-neutral-300 hover:text-white transition">
              <i class="fab fa-instagram"></i>
            </a>
          </div>
        </div>
        
        <div>
          <h3 class="font-heading text-lg font-bold mb-4">Quick Links</h3>
          <ul class="space-y-2">
            <li><a href="index.php" class="text-neutral-300 hover:text-white transition">Home</a></li>
            <li><a href="index.php#about" class="text-neutral-300 hover:text-white transition">About</a></li>
            <li><a href="index.php#services" class="text-neutral-300 hover:text-white transition">Services</a></li>
            <li><a href="index.php#courses" class="text-neutral-300 hover:text-white transition">Courses</a></li>
          </ul>
        </div>
        
        <div>
          <h3 class="font-heading text-lg font-bold mb-4">Resources</h3>
          <ul class="space-y-2">
            <li><a href="index.php#articles" class="text-neutral-300 hover:text-white transition">Articles</a></li>
            <li><a href="#" class="text-neutral-300 hover:text-white transition">Privacy Policy</a></li>
            <li><a href="#" class="text-neutral-300 hover:text-white transition">Terms of Service</a></li>
            <li><a href="index.php#donate" class="text-neutral-300 hover:text-white transition">Support Our Work</a></li>
          </ul>
        </div>
        
        <div>
          <h3 class="font-heading text-lg font-bold mb-4">Contact</h3>
          <address class="not-italic text-neutral-300 space-y-2">
            <p>1234 Healing Path</p>
            <p>Phoenix, AZ 85001</p>
            <p>Email: <a href="mailto:info@traumatotriumph.org" class="hover:text-white transition">info@traumatotriumph.org</a></p>
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

  <!-- JavaScript -->
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
    
    function closeMobileMenu() {
      mobileSidebar.classList.add('-translate-x-full');
    }
    
    // Reading progress indicator
    const progressBar = document.getElementById('readingProgress');
    window.addEventListener('scroll', () => {
      const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
      const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
      const scrolled = (winScroll / height) * 100;
      progressBar.style.width = scrolled + '%';
      
      // Back to top button visibility
      const backToTopBtn = document.getElementById('backToTop');
      if (winScroll > 300) {
        backToTopBtn.classList.remove('opacity-0', 'invisible');
        backToTopBtn.classList.add('opacity-100', 'visible');
      } else {
        backToTopBtn.classList.add('opacity-0', 'invisible');
        backToTopBtn.classList.remove('opacity-100', 'visible');
      }
    });
    
    // Back to top functionality
    document.getElementById('backToTop').addEventListener('click', () => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
    
    // Dark mode toggle
    const darkModeToggle = document.getElementById('darkModeToggle');
    const darkModeMobileToggle = document.getElementById('darkModeMobileToggle');
    const darkModeTextMobile = document.getElementById('darkModeTextMobile');
    
    // Check for saved theme preference or respect OS preference
    if (
      localStorage.getItem('darkMode') === 'true' || 
      (localStorage.getItem('darkMode') === null && window.matchMedia('(prefers-color-scheme: dark)').matches)
    ) {
      document.documentElement.classList.add('dark');
      darkModeTextMobile.textContent = 'Light Mode';
    } else {
      darkModeTextMobile.textContent = 'Dark Mode';
    }
    
    // Toggle dark mode
    function toggleDarkMode() {
      if (document.documentElement.classList.contains('dark')) {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('darkMode', 'false');
        darkModeTextMobile.textContent = 'Dark Mode';
      } else {
        document.documentElement.classList.add('dark');
        localStorage.setItem('darkMode', 'true');
        darkModeTextMobile.textContent = 'Light Mode';
      }
    }
    
    darkModeToggle.addEventListener('click', toggleDarkMode);
    darkModeMobileToggle.addEventListener('click', toggleDarkMode);
    
    // Font size control
    const articleContent = document.querySelector('.article-content');
    let fontSize = parseInt(window.getComputedStyle(articleContent).fontSize);
    const fontSizeStep = 1;
    
    document.getElementById('fontSizeIncrease').addEventListener('click', () => {
      if (fontSize < 24) {
        fontSize += fontSizeStep;
        articleContent.style.fontSize = `${fontSize}px`;
      }
    });
    
    document.getElementById('fontSizeDecrease').addEventListener('click', () => {
      if (fontSize > 14) {
        fontSize -= fontSizeStep;
        articleContent.style.fontSize = `${fontSize}px`;
      }
    });
    
    // Reading focus mode
    let readingFocusActive = false;
    const toggleReadingFocus = document.getElementById('toggleReadingFocus');
    
    toggleReadingFocus.addEventListener('click', () => {
      readingFocusActive = !readingFocusActive;
      
      if (readingFocusActive) {
        // Enable reading focus
        document.body.classList.add('reading-focus-mode');
        articleContent.style.maxWidth = '45rem';
        articleContent.style.margin = '0 auto';
        articleContent.style.lineHeight = '1.8';
        articleContent.style.background = document.documentElement.classList.contains('dark') ? '#1f2937' : '#ffffff';
        articleContent.style.padding = '2rem';
        articleContent.style.borderRadius = '0.5rem';
        articleContent.style.boxShadow = document.documentElement.classList.contains('dark') ? '0 0 20px rgba(0, 0, 0, 0.5)' : '0 0 20px rgba(0, 0, 0, 0.1)';
        
        // Change icon to show active state
        toggleReadingFocus.querySelector('i').classList.remove('fa-glasses');
        toggleReadingFocus.querySelector('i').classList.add('fa-eye');
      } else {
        // Disable reading focus
        document.body.classList.remove('reading-focus-mode');
        articleContent.style.maxWidth = '';
        articleContent.style.margin = '';
        articleContent.style.lineHeight = '';
        articleContent.style.background = '';
        articleContent.style.padding = '';
        articleContent.style.borderRadius = '';
        articleContent.style.boxShadow = '';
        
        // Change icon back to normal
        toggleReadingFocus.querySelector('i').classList.add('fa-glasses');
        toggleReadingFocus.querySelector('i').classList.remove('fa-eye');
      }
    });
    
    // Image lightbox functionality
    const articleImages = document.querySelectorAll('.article-content img');
    const lightbox = document.getElementById('imageLightbox');
    const lightboxImage = document.getElementById('lightboxImage');
    const closeLightbox = document.getElementById('closeLightbox');
    const prevImage = document.getElementById('prevImage');
    const nextImage = document.getElementById('nextImage');
    let currentImageIndex = 0;
    
    // Add click event to all images in the article
    articleImages.forEach((img, index) => {
      img.addEventListener('click', () => {
        lightboxImage.src = img.src;
        lightboxImage.alt = img.alt;
        currentImageIndex = index;
        
        // Show lightbox
        lightbox.classList.remove('invisible', 'opacity-0');
        document.body.style.overflow = 'hidden'; // Prevent scrolling
      });
    });
    
    // Close lightbox
    closeLightbox.addEventListener('click', () => {
      lightbox.classList.add('invisible', 'opacity-0');
      document.body.style.overflow = ''; // Re-enable scrolling
    });
    
    // Navigate previous/next images
    prevImage.addEventListener('click', () => {
      currentImageIndex = (currentImageIndex - 1 + articleImages.length) % articleImages.length;
      lightboxImage.src = articleImages[currentImageIndex].src;
      lightboxImage.alt = articleImages[currentImageIndex].alt;
    });
    
    nextImage.addEventListener('click', () => {
      currentImageIndex = (currentImageIndex + 1) % articleImages.length;
      lightboxImage.src = articleImages[currentImageIndex].src;
      lightboxImage.alt = articleImages[currentImageIndex].alt;
    });
    
    // Also close lightbox on ESC key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        lightbox.classList.add('invisible', 'opacity-0');
        document.body.style.overflow = ''; // Re-enable scrolling
      }
    });
    
    // Share article functionality
    function shareArticle(platform) {
      const articleUrl = encodeURIComponent(window.location.href);
      const articleTitle = encodeURIComponent('<?php echo htmlspecialchars(addslashes($article['title'])); ?>');
      
      switch(platform) {
        case 'facebook':
          window.open(`https://www.facebook.com/sharer/sharer.php?u=${articleUrl}`, '_blank');
          break;
        case 'twitter':
          window.open(`https://twitter.com/intent/tweet?url=${articleUrl}&text=${articleTitle}`, '_blank');
          break;
        case 'linkedin':
          window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${articleUrl}`, '_blank');
          break;
        case 'email':
          window.location.href = `mailto:?subject=${articleTitle}&body=${articleUrl}`;
          break;
      }
    }
    
    // Copy article link to clipboard
    function copyArticleLink() {
      navigator.clipboard.writeText(window.location.href).then(() => {
        alert('Link copied to clipboard!');
      }).catch(err => {
        console.error('Could not copy text: ', err);
      });
    }
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        
        // Skip for links not to an id (like # used for JavaScript events)
        if (href === '#') return;
        
        e.preventDefault();
        
        const target = document.querySelector(href);
        if (target) {
          const headerOffset = 80;
          const elementPosition = target.getBoundingClientRect().top;
          const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
          
          window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
          });
        }
      });
    });
    
    // Handle print functionality
    document.addEventListener('keydown', (e) => {
      // Check for Ctrl+P or Command+P
      if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
        // Print-specific styling could be added here
        // For example, you might want to hide certain elements before printing
      }
    });
  </script>
</body>
</html>