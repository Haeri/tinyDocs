<?php

// Get the request URI and parse it
$requestUri = $_SERVER['REQUEST_URI'];

if (preg_match('/^\/api\/search/', $requestUri)) {
    // Handle the search request
    include './src/search/index.php';
    exit();
}


require './src/CustomParsedown.php';

$parsedown = new CustomParsedown();

// Set the directory where your Markdown files are stored
$mdDirectory = 'pages';

function humanize($string) {
    $string = str_replace('_', ' ', $string);
    return ucwords($string);
}

// Function to recursively scan a directory and return an array of files and folders
function scanDirectory($dir) {
    $result = [];
    foreach (scandir($dir) as $filename) {
        if ($filename[0] === '.') {
            continue;
        }
        $filePath = $dir . '/' . $filename;
        if (is_dir($filePath)) {
            $result[$filename] = scanDirectory($filePath);
        } else {
            $result[] = $filename;
        }
    }
    return $result;
}

// Function to render the sidenav based on the scanned directory structure
function renderSidenav($dir, $structure, $baseUrl = '') {
    $page = isset($_GET['page']) ? $_GET['page'] : '';

    $html = '<ul>';
    foreach ($structure as $key => $value) {
        if (is_array($value)) {
            $active = $page == $baseUrl.$key ? 'active' : '';
            // Directory
            $html .= '<li><a class="'. $active.'" href="/' . $baseUrl . $key. '">' . humanize(htmlspecialchars($key)) . '</a>' .renderSidenav($dir . '/' . $key, $value, $baseUrl . $key . '/') . '</li>';
        } else {
            // File
            $pageName = pathinfo($value, PATHINFO_FILENAME);

            if ($pageName == "index") {
                continue;
            }
            $active = $page == $baseUrl . $pageName ? 'active' : '';
            $html .= '<li><a class="'. $active.'" href="/' . $baseUrl . $pageName . '">' . humanize(htmlspecialchars($pageName)) . '</a></li>';
        }
    }
    $html .= '</ul>';
    return $html;
}

// Get the requested file from the URL path
$page = isset($_GET['page']) ? $_GET['page'] : '';
$pagePath = $mdDirectory . '/' . $page;
$pageArr = explode("/", $page);
$pageTitle = humanize(end($pageArr));

if (is_dir($pagePath)) {
    $mdFile = $pagePath . '/index.md';
} else {
    $mdFile = $pagePath . '.md';
}

// Check if the Markdown file exists
if (file_exists($mdFile)) {
    // Read the content of the Markdown file
    $mdContent = file_get_contents($mdFile);

    // Convert the Markdown content to HTML
    $htmlContent = $parsedown->text($mdContent);
} else {
    // Handle the case where the file doesn't exist (404)
    $htmlContent = '<h1>404 - Page Not Found</h1><p>The page that you are looking for could not be found.</p>';
    http_response_code(404);
}

// Scan the directory to create the sidenav structure
$sidenavStructure = scanDirectory($mdDirectory);
$sidenavHtml = renderSidenav($mdDirectory, $sidenavStructure);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($pageTitle); ?> | DataSpace
        Docs</title>
    <meta name="description" content="An approachable data Transformation, Analysis and Lineage platform">
    <meta name="keywords"
    content="DataSpace, data, data-transformation, data-analysis, data-lineage, ETL, ELT, python, data-engineering, data-science">
    <meta name="content-language" content="en">

    <meta property="og:title" content="DataSpace - Data-Transformation, Analysis and Lineage platform">
    <meta property="og:description" content="An approachable data Transformation, Analysis and Lineage platform">
    <meta property="og:type" content="website">
    <meta property="og:image" content="/public/img/favicon.png">

    <link rel="apple-touch-icon" href="/public/img/favicon.png">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/public/img/favicon.png">
    <link href="/public/fonts/BlenderPro/stylesheet.css" rel="stylesheet">

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/stackoverflow-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/python.min.js"></script>


    <link rel="stylesheet" href="/public/style.css">
</head>

<body>
    <header class="container" style="display: flex; justify-content: space-between; align-items: center;">
        <a href="/" style="padding: 12px 0px; display: flex; gap: 1rem; align-items: center;">
            <img src="/public/img/favicon.png" alt="logo" style="height: 1.7rem" />
        <span style="font-size: 1.8rem;">DataSpace Docs</span>
    </a>

    <a href="#open-modal">🔍 Search</a>
</header>

<div class="container" style="display: flex;">
    <nav style="padding: 20px;">
        <?php echo $sidenavHtml; ?>
    </nav>
    <main style="flex: 1; display: flex; justify-content: center">
        <div style="padding: 20px; max-width: 780px;">
            <?php echo $htmlContent; ?>
        </div>
    </main>
</div>

<footer class="container">
    <small>version</small>
</footer>

<script src="/public/app.js"></script>
<script>
    hljs.highlightAll();
</script>


<div id="open-modal" class="modal-window">
    <div>
        <a href="#" title="Close" class="modal-close">Close</a>
        <h1>Search</h1>

        <div style="display: flex; flex-direction: column">
            <input type="text" id="searchInput" placeholder="Search...">
            <div style="margin-bottom: 10px">
                Results: <span id="resutls_num"></span>
            </div>
            <div id="results"></div>
        </div>
    </div>
</div>


</body>

</html>