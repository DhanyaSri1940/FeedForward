<?php
include("connect.php");
include '../connection.php';
if (!isset($_SESSION['name']) || $_SESSION['name'] == '') {
    header("Location: deliverylogin.php");
    exit();
}
$name = $_SESSION['name'];
$city = $_SESSION['city'];
$id = $_SESSION['Did'];

// Fetch assigned orders
$sql = "SELECT fd.Fid AS Fid, fd.name, fd.phoneno, fd.date, fd.address as From_address, ad.name AS delivery_person_name, ad.address AS To_address, fd.delivery_by FROM food_donations fd LEFT JOIN admin ad ON fd.assigned_to = ad.Aid WHERE fd.delivery_by = $id AND fd.location='$city'";
$result = mysqli_query($connection, $sql);
if (!$result) {
    die("Error executing query: " . mysqli_error($connection));
}
$data = array();
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

// Check if status column exists and fetch orders accordingly
$status_column_exists = false;
$check_status_sql = "SHOW COLUMNS FROM food_donations LIKE 'status'";
$status_result = $connection->query($check_status_sql);
if ($status_result && $status_result->num_rows > 0) {
    $status_column_exists = true;
    
    // Fetch pending orders (status != 'delivered' or status is NULL)
    $sql_pending = "SELECT fd.Fid AS Fid, fd.name, fd.phoneno, fd.date, fd.address as From_address, ad.name AS delivery_person_name, ad.address AS To_address, fd.delivery_by FROM food_donations fd LEFT JOIN admin ad ON fd.assigned_to = ad.Aid WHERE fd.delivery_by = $id AND fd.location='$city' AND (fd.status IS NULL OR fd.status != 'delivered')";
    $result_pending = mysqli_query($connection, $sql_pending);
    $pending_orders = array();
    if ($result_pending) {
        while ($row = mysqli_fetch_assoc($result_pending)) {
            $pending_orders[] = $row;
        }
    }
    
    // Fetch completed orders (status = 'delivered')
    $sql_completed = "SELECT fd.Fid AS Fid, fd.name, fd.phoneno, fd.date, fd.address as From_address, ad.name AS delivery_person_name, ad.address AS To_address, fd.delivery_by FROM food_donations fd LEFT JOIN admin ad ON fd.assigned_to = ad.Aid WHERE fd.delivery_by = $id AND fd.location='$city' AND fd.status = 'delivered'";
    $result_completed = mysqli_query($connection, $sql_completed);
    $completed_orders = array();
    if ($result_completed) {
        while ($row = mysqli_fetch_assoc($result_completed)) {
            $completed_orders[] = $row;
        }
    }
} else {
    // Status column doesn't exist, treat all orders as pending
    $pending_orders = $data;
    $completed_orders = array();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Feed Forward</title>
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
            --success: #10b981;
            --error: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
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
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .header-container {
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
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .nav-link:hover {
            color: var(--brand);
            background: rgba(14, 165, 164, 0.1);
            transform: translateY(-2px);
        }
        .nav-link.active {
            color: var(--brand);
            background: rgba(14, 165, 164, 0.15);
            font-weight: 600;
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

        /* Welcome Section */
        .welcome-section {
            text-align: center;
            padding: 3rem 2rem;
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-2) 100%);
            color: white;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        .welcome-section::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(800px 400px at 0% -10%, rgba(255, 255, 255, 0.1), transparent 60%),
                radial-gradient(600px 300px at 100% 110%, rgba(255, 255, 255, 0.1), transparent 60%);
            z-index: 0;
        }

        .welcome-content {
            position: relative;
            z-index: 2;
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .welcome-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 300;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Dashboard Stats */
        .stats-section {
            padding: 2rem;
            margin-bottom: 3rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .stat-card {
            background: var(--surface);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.pending {
            background: linear-gradient(135deg, var(--warning), #fbbf24);
        }

        .stat-icon.completed {
            background: linear-gradient(135deg, var(--success), #20c997);
        }

        .stat-icon.total {
            background: linear-gradient(135deg, var(--info), #60a5fa);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-number.pending {
            color: var(--warning);
        }

        .stat-number.completed {
            color: var(--success);
        }

        .stat-number.total {
            color: var(--info);
        }

        .stat-label {
            color: var(--muted-ink);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Orders Section */
        .orders-section {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto 3rem;
        }

        .section-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--brand);
            margin-bottom: 0.5rem;
        }

        .section-subtitle {
            color: var(--muted-ink);
            font-size: 1rem;
        }

        .orders-container {
            background: var(--surface);
            border-radius: 25px;
            box-shadow: var(--shadow);
            overflow: hidden;
            border: 1px solid var(--border);
            margin-bottom: 2rem;
        }

        .orders-wrapper {
            overflow-x: auto;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--surface);
            font-size: 0.9rem;
        }

        .orders-table thead {
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: white;
        }

        .orders-table th {
            padding: 1.5rem 1rem;
            text-align: center;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
            position: relative;
        }

        .orders-table th:not(:last-child)::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            height: 60%;
            width: 1px;
            background: rgba(255, 255, 255, 0.3);
        }

        .orders-table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid var(--border);
        }

        .orders-table tbody tr:hover {
            background: rgba(14, 165, 164, 0.05);
            transform: scale(1.01);
        }

        .orders-table td {
            padding: 1.5rem 1rem;
            text-align: center;
            border: none;
            vertical-align: middle;
        }

        .orders-table td:first-child {
            font-weight: 600;
            color: var(--brand);
        }

        /* Status Indicators */
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: linear-gradient(135deg, var(--warning), #fbbf24);
            color: white;
        }

        .status-completed {
            background: linear-gradient(135deg, var(--success), #20c997);
            color: white;
        }

        .status-delivering {
            background: linear-gradient(135deg, var(--info), #60a5fa);
            color: white;
        }

        /* Action Buttons */
        .action-btn {
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0.25rem;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            background: linear-gradient(135deg, var(--brand-2), var(--brand));
        }

        .action-btn:active {
            transform: translateY(0);
        }

        .action-btn.secondary {
            background: linear-gradient(135deg, var(--muted-ink), #64748b);
        }

        .action-btn.secondary:hover {
            background: linear-gradient(135deg, #64748b, var(--muted-ink));
        }

        .action-btn.success {
            background: linear-gradient(135deg, var(--success), #20c997);
        }

        .action-btn.success:hover {
            background: linear-gradient(135deg, #20c997, var(--success));
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--muted-ink);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
            color: var(--brand);
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--ink);
        }

        .empty-description {
            font-size: 1rem;
            opacity: 0.8;
            margin-bottom: 1.5rem;
        }

        /* Action Buttons Section */
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .main-action-btn {
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .main-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            background: linear-gradient(135deg, var(--brand-2), var(--brand));
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

            .welcome-title {
                font-size: 2rem;
            }

            .orders-section {
                padding: 1rem;
            }

            .orders-container {
                margin: 1rem;
                border-radius: 15px;
            }

            .orders-table th,
            .orders-table td {
                padding: 1rem 0.5rem;
                font-size: 0.8rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .main-action-btn {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
        }

        @media (max-width: 600px) {
            .orders-table thead {
                display: none;
            }

            .orders-table tbody tr {
                display: block;
                margin-bottom: 1.5rem;
                border: 2px solid var(--border);
                border-radius: 15px;
                padding: 1rem;
                background: var(--surface);
            }

            .orders-table td {
                display: block;
                text-align: right;
                padding: 0.75rem 1rem;
                border-bottom: 1px solid var(--border);
                position: relative;
            }

            .orders-table td:last-child {
                border-bottom: none;
            }

            .orders-table td::before {
                content: attr(data-label);
                float: left;
                font-weight: 600;
                color: var(--brand);
                text-transform: uppercase;
                font-size: 0.8rem;
            }
        }

        /* Animation for table rows */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .orders-table tbody tr {
            animation: fadeInUp 0.6s ease-out;
        }

        .orders-table tbody tr:nth-child(1) { animation-delay: 0.1s; }
        .orders-table tbody tr:nth-child(2) { animation-delay: 0.2s; }
        .orders-table tbody tr:nth-child(3) { animation-delay: 0.3s; }
        .orders-table tbody tr:nth-child(4) { animation-delay: 0.4s; }
        .orders-table tbody tr:nth-child(5) { animation-delay: 0.5s; }

        /* Enhanced focus states for accessibility */
        .action-btn:focus,
        .main-action-btn:focus,
        .nav-link:focus {
            outline: 3px solid rgba(0, 61, 58, 0.3);
            outline-offset: 2px;
        }

        /* Notification System */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--surface);
            border-radius: 15px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            padding: 1rem 1.5rem;
            z-index: 10000;
            min-width: 300px;
            max-width: 400px;
            animation: slideInRight 0.3s ease-out;
            border-left: 4px solid var(--info);
        }

        .notification-success {
            border-left-color: var(--success);
        }

        .notification-error {
            border-left-color: var(--error);
        }

        .notification-warning {
            border-left-color: var(--warning);
        }

        .notification-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--ink);
        }

        .notification-content i {
            font-size: 1.2rem;
        }

        .notification-success .notification-content i {
            color: var(--success);
        }

        .notification-error .notification-content i {
            color: var(--error);
        }

        .notification-warning .notification-content i {
            color: var(--warning);
        }

        .notification-close {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: none;
            border: none;
            color: var(--muted-ink);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .notification-close:hover {
            background: rgba(0, 0, 0, 0.1);
            color: var(--ink);
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
        }

        .modal-content {
            background: var(--surface);
            border-radius: 25px;
            box-shadow: var(--shadow);
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            transform: scale(0.9) translateY(20px);
            transition: all 0.3s ease;
        }

        .modal-overlay.active .modal-content {
            transform: scale(1) translateY(0);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2rem 2rem 1rem;
            border-bottom: 1px solid var(--border);
        }

        .modal-header h3 {
            color: var(--brand);
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--muted-ink);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-size: 1.2rem;
        }

        .modal-close:hover {
            background: rgba(0, 0, 0, 0.1);
            color: var(--ink);
        }

        .modal-body {
            padding: 2rem;
        }

        .order-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .detail-section {
            background: rgba(14, 165, 164, 0.05);
            border-radius: 15px;
            padding: 1.5rem;
            border: 1px solid var(--border);
        }

        .detail-section h4 {
            color: var(--brand);
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(14, 165, 164, 0.1);
        }

        .detail-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .detail-label {
            font-weight: 600;
            color: var(--muted-ink);
            font-size: 0.9rem;
        }

        .detail-value {
            color: var(--ink);
            font-weight: 500;
            text-align: right;
            max-width: 60%;
        }

        .order-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* Responsive Modal */
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 1rem;
            }

            .modal-header,
            .modal-body {
                padding: 1.5rem;
            }

            .order-details-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .order-actions {
                flex-direction: column;
                align-items: center;
            }

            .order-actions .action-btn {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="delivery.php" class="logo">Feed <span class="brand-name">Forward</span></a>
            
            <ul class="nav-menu">
                <li><a href="delivery.php" class="nav-link active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="openmap.php" class="nav-link"><i class="fas fa-map-marked-alt"></i> Map</a></li>
                <li><a href="deliverymyord.php" class="nav-link"><i class="fas fa-list-alt"></i> My Orders</a></li>
                <li><a href="../logout.php" class="nav-link" style="color: #ef4444;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
            
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </header>

    <!-- Welcome Section -->
    <section class="welcome-section">
        <div class="welcome-content">
            <h1 class="welcome-title">
                <i class="fas fa-list-alt"></i> My Orders
            </h1>
            <p class="welcome-subtitle">
                Track your assigned deliveries and manage your delivery schedule. Stay organized and efficient in serving your community.
            </p>
        </div>
    </section>

    <!-- Dashboard Stats -->
    <section class="stats-section">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-number total"><?php echo count($data); ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number pending"><?php echo count($pending_orders); ?></div>
                <div class="stat-label">Pending Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon completed">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number completed"><?php echo count($completed_orders); ?></div>
                <div class="stat-label">Completed Orders</div>
            </div>
        </div>
    </section>

    <!-- Pending Orders Section -->
    <section class="orders-section">
        <div class="section-header">
            <h2 class="section-title">Pending Orders</h2>
            <p class="section-subtitle">Orders that need to be delivered</p>
        </div>

        <div class="orders-container">
            <div class="orders-wrapper">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-user"></i> Customer Name</th>
                            <th><i class="fas fa-phone"></i> Phone Number</th>
                            <th><i class="fas fa-calendar-alt"></i> Order Date</th>
                            <th><i class="fas fa-map-marker-alt"></i> Pickup Address</th>
                            <th><i class="fas fa-flag-checkered"></i> Delivery Address</th>
                            <th><i class="fas fa-info-circle"></i> Status</th>
                            <th><i class="fas fa-cogs"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pending_orders)) { ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-inbox"></i>
                                        </div>
                                        <div class="empty-title">No Pending Orders</div>
                                        <div class="empty-description">You're all caught up! Check the dashboard for new delivery opportunities.</div>
                                        <a href="delivery.php" class="main-action-btn">
                                            <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php } else { ?>
                            <?php foreach ($pending_orders as $row) { ?>
                                <tr>
                                    <td data-label="Customer Name">
                                        <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                    </td>
                                    <td data-label="Phone Number">
                                        <a href="tel:<?php echo htmlspecialchars($row['phoneno']); ?>" style="color: var(--brand); text-decoration: none;">
                                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($row['phoneno']); ?>
                                        </a>
                                    </td>
                                    <td data-label="Order Date">
                                        <i class="fas fa-clock"></i> <?php echo htmlspecialchars($row['date']); ?>
                                    </td>
                                    <td data-label="Pickup Address">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['From_address']); ?>
                                    </td>
                                    <td data-label="Delivery Address">
                                        <i class="fas fa-flag-checkered"></i> <?php echo htmlspecialchars($row['To_address']); ?>
                                    </td>
                                    <td data-label="Status">
                                        <span class="status-badge status-pending">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    </td>
                                    <td data-label="Actions">
                                        <button class="action-btn success" onclick="markAsDelivered(<?php echo $row['Fid']; ?>)">
                                            <i class="fas fa-check"></i> Mark Delivered
                                        </button>
                                        <button class="action-btn secondary" onclick="viewDetails(<?php echo $row['Fid']; ?>)">
                                            <i class="fas fa-eye"></i> View Details
                                        </button>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Completed Orders Section -->
    <section class="orders-section">
        <div class="section-header">
            <h2 class="section-title">Completed Orders</h2>
            <p class="section-subtitle">Successfully delivered orders</p>
        </div>

        <div class="orders-container">
            <div class="orders-wrapper">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-user"></i> Customer Name</th>
                            <th><i class="fas fa-phone"></i> Phone Number</th>
                            <th><i class="fas fa-calendar-alt"></i> Order Date</th>
                            <th><i class="fas fa-map-marker-alt"></i> Pickup Address</th>
                            <th><i class="fas fa-flag-checkered"></i> Delivery Address</th>
                            <th><i class="fas fa-info-circle"></i> Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($completed_orders)) { ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-trophy"></i>
                                        </div>
                                        <div class="empty-title">No Completed Orders Yet</div>
                                        <div class="empty-description">Start delivering orders to see your completed deliveries here!</div>
                                    </div>
                                </td>
                            </tr>
                        <?php } else { ?>
                            <?php foreach ($completed_orders as $row) { ?>
                                <tr>
                                    <td data-label="Customer Name">
                                        <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                    </td>
                                    <td data-label="Phone Number">
                                        <a href="tel:<?php echo htmlspecialchars($row['phoneno']); ?>" style="color: var(--brand); text-decoration: none;">
                                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($row['phoneno']); ?>
                                        </a>
                                    </td>
                                    <td data-label="Order Date">
                                        <i class="fas fa-clock"></i> <?php echo htmlspecialchars($row['date']); ?>
                                    </td>
                                    <td data-label="Pickup Address">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['From_address']); ?>
                                    </td>
                                    <td data-label="Delivery Address">
                                        <i class="fas fa-flag-checkered"></i> <?php echo htmlspecialchars($row['To_address']); ?>
                                    </td>
                                    <td data-label="Status">
                                        <span class="status-badge status-completed">
                                            <i class="fas fa-check-circle"></i> Completed
                                        </span>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Action Buttons -->
    <section class="orders-section">
        <div class="action-buttons">
            <a href="delivery.php" class="main-action-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <a href="openmap.php" class="main-action-btn secondary">
                <i class="fas fa-map-marked-alt"></i> View Map
            </a>
            <a href="delivery.php" class="main-action-btn">
                <i class="fas fa-sync-alt"></i> Refresh Orders
            </a>
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

        // Mark order as delivered
        function markAsDelivered(orderId) {
            if (confirm('Are you sure you want to mark this order as delivered?')) {
                // Make AJAX call to update order status
                fetch('update_order_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'order_id=' + orderId + '&status=delivered'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showNotification('Order marked as delivered successfully!', 'success');
                        // Refresh the page after a short delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showNotification('Error updating order status: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error updating order status. Please try again.', 'error');
                });
            }
        }

        // View order details
        function viewDetails(orderId) {
            // Make AJAX call to get order details
            fetch('get_order_details.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'order_id=' + orderId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.order) {
                    showOrderDetailsModal(data.order);
                }
                // Silently handle errors without showing notification
            })
            .catch(error => {
                console.error('Error:', error);
                // Don't show error notification to user
            });
        }

        // Show order details modal
        function showOrderDetailsModal(order) {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3><i class="fas fa-box"></i> Order Details</h3>
                        <button class="modal-close" onclick="closeModal()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="order-details-grid">
                            <div class="detail-section">
                                <h4><i class="fas fa-user"></i> Customer Information</h4>
                                <div class="detail-item">
                                    <span class="detail-label">Name:</span>
                                    <span class="detail-value">${order.name}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Phone:</span>
                                    <span class="detail-value">
                                        <a href="tel:${order.phoneno}" style="color: var(--brand); text-decoration: none;">
                                            <i class="fas fa-phone"></i> ${order.phoneno}
                                        </a>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="detail-section">
                                <h4><i class="fas fa-calendar-alt"></i> Order Information</h4>
                                <div class="detail-item">
                                    <span class="detail-label">Order ID:</span>
                                    <span class="detail-value">#${order.Fid}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Order Date:</span>
                                    <span class="detail-value">
                                        <i class="fas fa-clock"></i> ${order.date}
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Order Age:</span>
                                    <span class="detail-value">
                                        <i class="fas fa-history"></i> ${order.order_age || 'Calculating...'}
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Status:</span>
                                    <span class="detail-value">
                                        <span class="status-badge status-${order.status_color || 'pending'}">
                                            <i class="fas fa-${order.status === 'delivered' ? 'check-circle' : 'clock'}"></i> 
                                            ${order.status_display || 'Pending'}
                                        </span>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="detail-section">
                                <h4><i class="fas fa-map-marker-alt"></i> Pickup Location</h4>
                                <div class="detail-item">
                                    <span class="detail-value">${order.From_address}</span>
                                </div>
                            </div>
                            
                            <div class="detail-section">
                                <h4><i class="fas fa-flag-checkered"></i> Delivery Location</h4>
                                <div class="detail-item">
                                    <span class="detail-value">${order.To_address}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="order-actions">
                            <button class="action-btn success" onclick="markAsDelivered(${order.Fid}); closeModal();">
                                <i class="fas fa-check"></i> Mark as Delivered
                            </button>
                            <button class="action-btn secondary" onclick="closeModal()">
                                <i class="fas fa-times"></i> Close
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Add animation
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);
        }

        // Close modal
        function closeModal() {
            const modal = document.querySelector('.modal-overlay');
            if (modal) {
                modal.classList.remove('active');
                setTimeout(() => {
                    modal.remove();
                }, 300);
            }
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                closeModal();
            }
        });

        // Show notification
        function showNotification(message, type = 'info') {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(notification => notification.remove());

            // Create notification element
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                    <span>${message}</span>
                </div>
                <button class="notification-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;

            // Add to page
            document.body.appendChild(notification);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }

        // Add smooth animations
        document.addEventListener('DOMContentLoaded', () => {
            const elements = document.querySelectorAll('.stat-card, .orders-container');
            elements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    element.style.transition = 'all 0.6s ease-out';
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, 300 + (index * 100));
            });
        });
    </script>
</body>
</html> 