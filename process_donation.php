<?php
session_start();
if (!isset($_SESSION['name']) || $_SESSION['name'] == '') {
    header("Location: signin.php");
    exit();
}

include("connection.php");

// Function to handle file upload
function handleFileUpload($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Please upload an image of the food.'];
    }

    $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
    $fileType = mime_content_type($file['tmp_name']);
    
    if (!array_key_exists($fileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Only JPG, PNG, and GIF files are allowed.'];
    }

    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = uniqid('food_') . '.' . $allowedTypes[$fileType];
    $destination = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'success' => true,
            'path' => $destination,
            'filename' => $fileName
        ];
    }

    return ['success' => false, 'message' => 'Failed to upload file.'];
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic validation
    $required = ['foodname', 'meal', 'quantity', 'phoneno', 'district', 'address', 'name'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = "Please fill in all required fields.";
            header("Location: fooddonateform.php");
            exit();
        }
    }

    // Handle file upload
    $fileUpload = handleFileUpload($_FILES['food_image'] ?? null);
    if (!$fileUpload['success']) {
        $_SESSION['error'] = $fileUpload['message'];
        header("Location: fooddonateform.php");
        exit();
    }

    // Insert into database
    try {
        $stmt = $connection->prepare("INSERT INTO food_donations 
            (email, food, type, category, phoneno, location, address, name, quantity, image_path, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");

        $email = $_SESSION['email'] ?? '';
        $category = $_POST['image-choice'] ?? 'other';
        
        $stmt->bind_param('ssssssssss',
            $email,
            $_POST['foodname'],
            $_POST['meal'],
            $category,
            $_POST['phoneno'],
            $_POST['district'],
            $_POST['address'],
            $_POST['name'],
            $_POST['quantity'],
            $fileUpload['path']
        );

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: delivery.html");
            exit();
        } else {
            throw new Exception("Database error: " . $stmt->error);
        }
    } catch (Exception $e) {
        // Log the error
        error_log("Donation submission error: " . $e->getMessage());
        
        // Clean up uploaded file if there was an error
        if (isset($fileUpload['path']) && file_exists($fileUpload['path'])) {
            unlink($fileUpload['path']);
        }
        
        $_SESSION['error'] = "An error occurred while processing your donation. Please try again.";
        header("Location: fooddonateform.php");
        exit();
    }
} else {
    // If someone tries to access this file directly
    header("Location: fooddonateform.php");
    exit();
}
?>
