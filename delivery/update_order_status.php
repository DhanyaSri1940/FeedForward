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
$status = isset($_POST['status']) ? $_POST['status'] : '';
$delivery_person_id = $_SESSION['Did'];

// Validate input
if ($order_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit();
}

if ($status !== 'delivered') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

try {
    // First, check if the order exists and belongs to this delivery person
    $check_sql = "SELECT Fid, delivery_by FROM food_donations WHERE Fid = ? AND delivery_by = ?";
    $check_stmt = $connection->prepare($check_sql);
    $check_stmt->bind_param("ii", $order_id, $delivery_person_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found or not assigned to you']);
        exit();
    }
    
    $check_stmt->close();
    
    // Check if the food_donations table has a status column
    $column_check_sql = "SHOW COLUMNS FROM food_donations LIKE 'status'";
    $column_result = $connection->query($column_check_sql);
    
    if ($column_result->num_rows > 0) {
        // Status column exists, update it
        $update_sql = "UPDATE food_donations SET status = ? WHERE Fid = ? AND delivery_by = ?";
        $update_stmt = $connection->prepare($update_sql);
        $update_stmt->bind_param("sii", $status, $order_id, $delivery_person_id);
    } else {
        // Status column doesn't exist, create it first
        $alter_sql = "ALTER TABLE food_donations ADD COLUMN status VARCHAR(50) DEFAULT 'pending'";
        if ($connection->query($alter_sql)) {
            // Now update the status
            $update_sql = "UPDATE food_donations SET status = ? WHERE Fid = ? AND delivery_by = ?";
            $update_stmt = $connection->prepare($update_sql);
            $update_stmt->bind_param("sii", $status, $order_id, $delivery_person_id);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create status column']);
            exit();
        }
    }
    
    // Execute the update
    if ($update_stmt->execute()) {
        if ($update_stmt->affected_rows > 0) {
            // Success - order status updated
            echo json_encode([
                'success' => true, 
                'message' => 'Order marked as delivered successfully',
                'order_id' => $order_id,
                'status' => $status
            ]);
        } else {
            // No rows affected
            echo json_encode(['success' => false, 'message' => 'No changes made to order']);
        }
    } else {
        // Update failed
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
    }
    
    $update_stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$connection->close();
?>

