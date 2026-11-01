<?php
session_start();
include '../connection.php';

// Check if user is logged in
if (!isset($_SESSION['name']) || $_SESSION['name'] == '') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get POST data
$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$delivery_person_id = $_SESSION['Did'];

// Validate input
if ($order_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit();
}

try {
    // Fetch order details - ensure it belongs to this delivery person
    $sql = "SELECT 
                fd.Fid,
                fd.name,
                fd.phoneno,
                fd.date,
                fd.address as From_address,
                fd.location,
                fd.delivery_by,
                ad.name AS delivery_person_name,
                ad.address AS To_address,
                COALESCE(fd.status, 'pending') as status,
                TIMESTAMPDIFF(MINUTE, fd.date, NOW()) AS age_minutes
            FROM food_donations fd 
            LEFT JOIN admin ad ON fd.assigned_to = ad.Aid 
            WHERE fd.Fid = ? AND fd.delivery_by = ?";
    
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("ii", $order_id, $delivery_person_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found or not assigned to you']);
        exit();
    }
    
    $order = $result->fetch_assoc();
    $stmt->close();
    
    // Handle date fields safely
    $order['order_age'] = '';
    if (!empty($order['date'])) {
        // Use DB-computed minutes to avoid PHP timezone differences
        if (isset($order['age_minutes']) && $order['age_minutes'] !== null) {
            $ageMinutes = (int)$order['age_minutes'];
            if ($ageMinutes < 1) {
                $order['order_age'] = 'Just now';
            } elseif ($ageMinutes < 60) {
                $order['order_age'] = $ageMinutes . ' minute' . ($ageMinutes === 1 ? '' : 's') . ' ago';
            } elseif ($ageMinutes < 60 * 24) {
                $hours = intdiv($ageMinutes, 60);
                $order['order_age'] = $hours . ' hour' . ($hours === 1 ? '' : 's') . ' ago';
            } else {
                $days = intdiv($ageMinutes, 60 * 24);
                $order['order_age'] = $days . ' day' . ($days === 1 ? '' : 's') . ' ago';
            }
        }
        // Format date for display once
        try {
            $originalDateTime = new DateTime($order['date']);
            $order['date'] = $originalDateTime->format('F j, Y \\a\\t g:i A');
        } catch (Exception $e) {
            // keep original string
        }
    }
    
    // Add status information
    $order['status_display'] = ucfirst($order['status']);
    $order['status_color'] = $order['status'] === 'delivered' ? 'success' : 'warning';
    
    // Success response
    echo json_encode([
        'success' => true,
        'order' => $order,
        'message' => 'Order details retrieved successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$connection->close();
?>

