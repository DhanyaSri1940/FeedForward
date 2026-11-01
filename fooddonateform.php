<?php
session_start();
if (!isset($_SESSION['name']) || $_SESSION['name'] == '') {
    header("location: signin.php");
    exit();
}
include("connection.php");

/**
 * Check if a column exists in a given table.
 */
function columnExists(mysqli $connection, string $table, string $column): bool {
    $stmt = $connection->prepare("
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
        LIMIT 1
    ");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows === 1;
    $stmt->close();
    return $exists;
}

// Simple 8x8 average perceptual hash using GD
function computePerceptualHash(string $filePath): ?string {
    if (!extension_loaded('gd') || !function_exists('imagecreatefromstring')) {
        return null;
    }
    $imageData = @file_get_contents($filePath);
    if ($imageData === false) {
        return null;
    }

    $source = @imagecreatefromstring($imageData);
    if (!$source) {
        return null;
    }

    $resized = imagecreatetruecolor(8, 8);
    imagecopyresampled($resized, $source, 0, 0, 0, 0, 8, 8, imagesx($source), imagesy($source));

    $pixels = [];
    $total = 0;
    for ($y = 0; $y < 8; $y++) {
        for ($x = 0; $x < 8; $x++) {
            $rgb = imagecolorat($resized, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $gray = (0.299 * $r) + (0.587 * $g) + (0.114 * $b);
            $pixels[] = $gray;
            $total += $gray;
        }
    }

    $average = $total / 64;
    $bits = '';
    foreach ($pixels as $value) {
        $bits .= ($value >= $average) ? '1' : '0';
    }

    $hash = '';
    foreach (str_split($bits, 4) as $chunk) {
        $hash .= dechex(bindec($chunk));
    }

    imagedestroy($source);
    imagedestroy($resized);

    return $hash;
}

function hammingDistance(string $hash1, string $hash2): ?int {
    if (strlen($hash1) !== strlen($hash2)) {
        return null;
    }

    $distance = 0;
    $length = strlen($hash1);
    for ($i = 0; $i < $length; $i++) {
        $xor = hexdec($hash1[$i]) ^ hexdec($hash2[$i]);
        $distance += substr_count(decbin($xor), '1');
    }
    return $distance;
}

function ensureUploadDirectory(string $path): bool {
    if (is_dir($path)) {
        return true;
    }
    return mkdir($path, 0755, true);
}

$duplicateThreshold = 5; // lower = stricter duplicate detection
$uploadDir = __DIR__ . '/uploads';
$imagePathRel = null;
$defaultStatus = 'pending';
$rejectionNote = null;
$defaultStatus = 'pending';
$imagePathRel = null;

if (isset($_POST['submit'])) {
    $foodname = trim($_POST['foodname'] ?? '');
    $meal = trim($_POST['meal'] ?? '');
    $category = $_POST['image-choice'] ?? '';
    $quantity = trim($_POST['quantity'] ?? '');
    $phoneno = trim($_POST['phoneno'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $name = trim($_POST['name'] ?? '');

    // Blocked donor check
    $strikes = 0;
    if (!columnExists($connection, 'login', 'strikes')) {
        $error_message = "System update required. Please try again shortly.";
    } else {
        $strikeStmt = $connection->prepare("SELECT strikes FROM login WHERE email = ?");
        $strikeStmt->bind_param('s', $_SESSION['email']);
        if ($strikeStmt->execute()) {
            $strikeResult = $strikeStmt->get_result();
            if ($strikeResult && $strikeResult->num_rows === 1) {
                $row = $strikeResult->fetch_assoc();
                $strikes = (int)$row['strikes'];
            }
        }
        $strikeStmt->close();
    }

    if ($strikes >= 3) {
        $error_message = "Your account is temporarily blocked due to repeated fake submissions.";
    }

    // Require image upload
    if (!isset($error_message)) {
        $image_path = '';
        $image_hash = null;
        
        // Handle file upload if exists
        if (isset($_FILES['food_image']) && $_FILES['food_image']['error'] === UPLOAD_ERR_OK) {
            $image_hash = computePerceptualHash($_FILES['food_image']['tmp_name']);
            $image_ext = pathinfo($_FILES['food_image']['name'], PATHINFO_EXTENSION);
            $image_filename = uniqid('food_') . '.' . $image_ext;
            $image_path = 'uploads/' . $image_filename;
            
            if (!move_uploaded_file($_FILES['food_image']['tmp_name'], $image_path)) {
                $error_message = "Failed to upload image.";
            }
        }
        
        if (!isset($error_message)) {
            $stmt = $connection->prepare("INSERT INTO food_donations(email, food, type, category, phoneno, location, address, name, quantity, image_hash, image_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            
            if ($stmt) {
                $stmt->bind_param('ssssssssssss', 
                    $_SESSION['email'],
                    $foodname,
                    $meal,
                    $category,
                    $phoneno,
                    $district,
                    $address,
                    $name,
                    $quantity,
                    $image_hash,
                    $image_path
                );
                
                if ($stmt->execute()) {
                    $stmt->close();
                    header("Location: delivery.html");
                    exit();
                } else {
                    $error_message = "Error: " . $stmt->error;
                }
                $stmt->close();
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $fileTmp);
            finfo_close($finfo);

            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp'
            ];

            if (!isset($allowedTypes[$mimeType])) {
                $error_message = "Only JPEG, PNG, GIF, or WEBP images are allowed.";
            } else {
                // Try to compute hash if GD is available, otherwise set to NULL
                $imageHash = null;
                if (extension_loaded('gd') && function_exists('imagecreatefromstring')) {
                    $imageHash = computePerceptualHash($fileTmp);
                    if ($imageHash) {
                        // Compare against donor's previous uploads only if hash was computed
                        $dupStmt = $connection->prepare("SELECT image_hash FROM food_donations WHERE email = ? AND image_hash IS NOT NULL");
                        $dupStmt->bind_param('s', $_SESSION['email']);
                        $dupStmt->execute();
                        $dupResult = $dupStmt->get_result();

                        $isDuplicate = false;
                        if ($dupResult) {
                            while ($row = $dupResult->fetch_assoc()) {
                                $existingHash = $row['image_hash'];
                                if (!$existingHash) {
                                    continue;
                                }
                                $distance = hammingDistance($imageHash, $existingHash);
                                if ($distance !== null && $distance <= $duplicateThreshold) {
                                    $isDuplicate = true;
                                    break;
                                }
                            }
                        }
                        $dupStmt->close();

                        if ($isDuplicate) {
                            $error_message = "Please upload a fresh photo. This image appears to be reused.";
                        }
                    }
                }
                // Continue with file upload even if hash couldn't be computed
                if (!isset($error_message)) {
                    if (!ensureUploadDirectory($uploadDir)) {
                        $error_message = "Unable to prepare upload directory. Please try again later.";
                    } else {
                        $extension = $allowedTypes[$mimeType];
                        $newFilename = uniqid('donation_', true) . '.' . $extension;
                        $destination = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $newFilename;

                        if (!move_uploaded_file($fileTmp, $destination)) {
                            $error_message = "Failed to save uploaded photo. Please try again.";
                        } else {
                            // store relative path for admin viewing
                            $imagePathRel = 'uploads/' . $newFilename;
                        }
                    }
                }
            }
        }
    }

    }

    if (!isset($error_message)) {
        $stmt = $connection->prepare("INSERT INTO food_donations(email, food, type, category, phoneno, location, address, name, quantity, image_hash, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssssssss', $_SESSION['email'], $foodname, $meal, $category, $phoneno, $district, $address, $name, $quantity, $imageHash, $imagePathRel);
        
        if ($stmt->execute()) {
            $success_message = "Donation submitted successfully!";
            header("refresh:2;url=delivery.html");
        } else {
            $error_message = "Failed to submit donation. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate Food - Feed Forward</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,200;0,300;0,500;0,700;0,800;1,400;1,600&display=swap');

        :root {
            --brand: #003d3a;
            --brand-2: #0ea5a4;
            --ink: #14212b;
            --muted-ink: #41515e;
            --surface: #ffffff;
            --border: #e6efee;
            --shadow: 0 10px 30px rgba(0, 61, 58, 0.12);
            --shadow-sm: 0 6px 18px rgba(0, 61, 58, 0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(180deg, #f6fbfb, #eef6f5);
            color: var(--ink);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Navigation */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
            z-index: 1000;
            padding: 1rem 2rem;
            transition: all 0.3s ease;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            color: var(--ink);
            text-decoration: none;
        }
        .brand-name {
            color: var(--brand);
            background: linear-gradient(90deg, var(--brand), var(--brand-2));
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            color: var(--ink);
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .nav-link:hover,
        .nav-link.active {
            color: var(--brand);
            background: rgba(14, 165, 164, 0.1);
        }

        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            gap: 4px;
        }
        .hamburger span {
            width: 25px;
            height: 3px;
            background: var(--ink);
            transition: all 0.3s ease;
        }

        /* Hero Section */
        .hero {
            min-height: 40vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 120px 2rem 4rem;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(0, 61, 58, 0.8), rgba(14, 165, 164, 0.7)), url('img/coverimage2.jpeg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .hero::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(1200px 600px at 0% -10%, rgba(14, 165, 164, 0.40), transparent 60%),
                radial-gradient(1000px 500px at 100% 110%, rgba(0, 61, 58, 0.35), transparent 60%);
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 900px;
            animation: fadeInUp 1s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            line-height: 1.1;
            color: white;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
            letter-spacing: -0.02em;
        }
        .hero-title .highlight {
            color: #ffffff;
            background: linear-gradient(90deg, #ffffff, #e0f2fe);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: none;
            position: relative;
        }
        .hero-title .highlight::after {
            content: "";
            position: absolute;
            bottom: -5px;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--brand-2), transparent);
            border-radius: 2px;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            line-height: 1.6;
            font-weight: 400;
        }

        /* Form Section */
        .form-section {
            padding: 4rem 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .form-container {
            background: var(--surface);
            border-radius: 25px;
            padding: 3rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }
        .form-container::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(14, 165, 164, 0.03) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }

        .form-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .form-title::before {
            content: "🍽️";
            font-size: 1.5rem;
        }

        .form-subtitle {
            color: var(--muted-ink);
            font-size: 1rem;
        }

        /* Messages */
        .message {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
            text-align: center;
            font-weight: 500;
        }

        .success-message {
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            border: 1px solid #22c55e;
            color: #166534;
        }

        .error-message {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            border: 1px solid #ef4444;
            color: #dc2626;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            color: var(--ink);
            background: var(--surface);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--brand-2);
            box-shadow: 0 0 0 3px rgba(14, 165, 164, 0.1);
        }

        .form-select {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            color: var(--ink);
            background: var(--surface);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }
        .form-select:focus {
            outline: none;
            border-color: var(--brand-2);
            box-shadow: 0 0 0 3px rgba(14, 165, 164, 0.1);
        }

        /* Radio Groups */
        .radio-group {
            display: flex;
            gap: 2rem;
            margin-top: 0.5rem;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .radio-option input[type="radio"] {
            width: 18px;
            height: 18px;
            accent-color: var(--brand);
            cursor: pointer;
        }

        .radio-option label {
            font-size: 1rem;
            color: var(--ink);
            cursor: pointer;
            user-select: none;
            font-weight: 500;
        }

        /* Image Radio Group */
        .image-radio-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }

        .image-radio-option {
            position: relative;
            cursor: pointer;
        }

        .image-radio-option input[type="radio"] {
            display: none;
        }

        .image-radio-option label {
            display: block;
            border: 3px solid var(--border);
            border-radius: 16px;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
            background: var(--surface);
            cursor: pointer;
        }

        .image-radio-option input[type="radio"]:checked + label {
            border-color: var(--brand-2);
            background: linear-gradient(135deg, rgba(14, 165, 164, 0.1), rgba(0, 61, 58, 0.1));
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 61, 58, 0.15);
        }

        .image-radio-option img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 0.5rem;
            transition: transform 0.3s ease;
        }

        .image-radio-option input[type="radio"]:checked + label img {
            transform: scale(1.1);
        }

        .image-radio-option .image-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--ink);
            margin-top: 0.5rem;
        }

        /* Section Divider */
        .section-divider {
            text-align: center;
            margin: 2rem 0;
            position: relative;
            z-index: 1;
        }

        .section-divider::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--border);
        }

        .section-divider span {
            background: var(--surface);
            padding: 0 1rem;
            color: var(--brand);
            font-weight: 700;
            font-size: 1.1rem;
        }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: white;
            border: none;
            padding: 1.25rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 1rem;
        }
        .submit-btn::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        .submit-btn:hover::before {
            left: 100%;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 61, 58, 0.3);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hamburger {
                display: flex;
            }

            .nav-menu {
                position: fixed;
                top: 80px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 80px);
                background: var(--surface);
                flex-direction: column;
                justify-content: flex-start;
                align-items: center;
                padding-top: 2rem;
                transition: left 0.3s ease;
                box-shadow: var(--shadow);
            }

            .nav-menu.active {
                left: 0;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .image-radio-group {
                grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
                gap: 1rem;
            }

            .form-container {
                padding: 2rem;
            }
        }

        @media (max-width: 480px) {
            .hero {
                padding: 100px 1rem 1rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .form-section {
                padding: 2rem 1rem;
            }

            .form-container {
                padding: 1.5rem;
            }

            .form-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="home.php" class="logo">Feed <span class="brand-name">Forward</span></a>
            
            <ul class="nav-menu">
                <li><a href="home.php" class="nav-link">Home</a></li>
                <li><a href="about.html" class="nav-link">About</a></li>
                <li><a href="contact.html" class="nav-link">Contact</a></li>
                <li><a href="profile.php" class="nav-link">Profile</a></li>
            </ul>
            
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1 class="hero-title">
                Donate <span class="highlight">Food</span>
            </h1>
            <p class="hero-subtitle">
                Make a difference by donating food to those in need. Every contribution helps reduce food waste and feed communities.
            </p>
        </div>
    </section>

    <!-- Form Section -->
    <section class="form-section">
        <div class="form-container">
            <div class="form-header">
                <h2 class="form-title">Food Donation Form</h2>
                <p class="form-subtitle">Fill in the details below to donate food</p>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="message success-message">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="message error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php
// Display error message if any
if (isset($_SESSION['error'])) {
    echo '<div class="error-message" style="background: #ffebee; color: #c62828; padding: 15px; margin: 20px 0; border-radius: 4px; border-left: 4px solid #c62828;">' . htmlspecialchars($_SESSION['error']) . '</div>';
    unset($_SESSION['error']); // Clear the error message after displaying it
}
?>

<form action="process_donation.php" method="post" enctype="multipart/form-data">
                <!-- Food Details -->
                <div class="form-group">
                    <label class="form-label" for="foodname">Food Name</label>
                    <input type="text" id="foodname" name="foodname" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Meal Type</label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="meal" id="veg" value="veg" required>
                            <label for="veg">Vegetarian</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="meal" id="Non-veg" value="Non-veg">
                            <label for="Non-veg">Non-Vegetarian</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Food Category</label>
                    <div class="image-radio-group">
                        <div class="image-radio-option">
                            <input type="radio" id="raw-food" name="image-choice" value="raw-food">
                            <label for="raw-food">
                                <img src="img/raw-food.png" alt="Raw Food">
                                <div class="image-label">Raw Food</div>
                            </label>
                        </div>
                        <div class="image-radio-option">
                            <input type="radio" id="cooked-food" name="image-choice" value="cooked-food" checked>
                            <label for="cooked-food">
                                <img src="img/cooked-food.png" alt="Cooked Food">
                                <div class="image-label">Cooked Food</div>
                            </label>
                        </div>
                        <div class="image-radio-option">
                            <input type="radio" id="packed-food" name="image-choice" value="packed-food">
                            <label for="packed-food">
                                <img src="img/packed-food.png" alt="Packed Food">
                                <div class="image-label">Packed Food</div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="quantity">Quantity (number of persons / kg)</label>
                    <input type="text" id="quantity" name="quantity" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="food_image">Upload Food Photo</label>
                    <input type="file" id="food_image" name="food_image" class="form-input" accept="image/*" required>
                    <small style="color: var(--muted-ink); display: block; margin-top: 8px;">Photo is required. Please upload a clear, recent image of the food you are donating.</small>
                </div>

                <!-- Contact Details Section -->
                <div class="section-divider">
                    <span>Contact Details</span>
                </div>

                <div class="form-group">
                    <div class="form-row">
                        <div>
                            <label class="form-label" for="name">Name</label>
                            <input type="text" id="name" name="name" class="form-input" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" required>
                        </div>
                        <div>
                            <label class="form-label" for="phoneno">Phone Number</label>
                            <input type="text" id="phoneno" name="phoneno" class="form-input" maxlength="10" pattern="[0-9]{10}" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-row">
                        <div>
                            <label class="form-label" for="district">District</label>
                            <select id="district" name="district" class="form-select">
                                <option value="bagalkot">Bagalkot</option>
                                <option value="ballari">Ballari</option>
                                <option value="belagavi">Belagavi</option>
                                <option value="bengaluru rural">Bengaluru Rural</option>
                                <option value="bengaluru urban">Bengaluru Urban</option>
                                <option value="bidar">Bidar</option>
                                <option value="chamarajanagar">Chamarajanagar</option>
                                <option value="chikkaballapur" selected>Chikkaballapur</option>
                                <option value="chikkamagaluru">Chikkamagaluru</option>
                                <option value="chitradurga">Chitradurga</option>
                                <option value="dakshina kannada">Dakshina Kannada</option>
                                <option value="davanagere">Davanagere</option>
                                <option value="dharwad">Dharwad</option>
                                <option value="gadag">Gadag</option>
                                <option value="hassan">Hassan</option>
                                <option value="haveri">Haveri</option>
                                <option value="kalaburagi">Kalaburagi</option>
                                <option value="kodagu">Kodagu</option>
                                <option value="kolar">Kolar</option>
                                <option value="koppal">Koppal</option>
                                <option value="mandya">Mandya</option>
                                <option value="mysuru">Mysuru</option>
                                <option value="raichur">Raichur</option>
                                <option value="ramanagara">Ramanagara</option>
                                <option value="shivamogga">Shivamogga</option>
                                <option value="tumakuru">Tumakuru</option>
                                <option value="udupi">Udupi</option>
                                <option value="uttara kannada">Uttara Kannada</option>
                                <option value="vijayanagara">Vijayanagara</option>
                                <option value="vijayapura">Vijayapura</option>
                                <option value="yadgir">Yadgir</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="address">Address</label>
                            <input type="text" id="address" name="address" class="form-input" required>
                        </div>
                    </div>
                </div>

                <button type="submit" name="submit" class="submit-btn">
                    <i class="fas fa-heart"></i> Submit Donation
                </button>
            </form>
        </div>
    </section>

    <script>
        // Mobile Navigation Toggle
        const hamburger = document.querySelector('.hamburger');
        const navMenu = document.querySelector('.nav-menu');

        hamburger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });

        // Close mobile menu when clicking on a link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
            });
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Navbar background on scroll
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 100) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                navbar.style.boxShadow = 'none';
            }
        });
    </script>
</body>
</html> 