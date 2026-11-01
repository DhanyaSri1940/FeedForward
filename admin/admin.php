<?php
ob_start();
include("connect.php");
if (!isset($_SESSION['name']) || $_SESSION['name'] == '') {
    header("Location: signin.php");
    exit();
}

// Check and add is_hidden column if it doesn't exist
$checkColumn = $connection->query("SHOW COLUMNS FROM food_donations LIKE 'is_hidden'");
if($checkColumn->num_rows == 0) {
    $connection->query("ALTER TABLE food_donations ADD COLUMN is_hidden TINYINT(1) DEFAULT 0");
}

// Handle removal of fake donations
if (isset($_POST['remove_fake_donation']) && isset($_POST['donation_id'])) {
    $donationId = (int)$_POST['donation_id'];
    // Update the donation to mark it as hidden
    $updateQuery = "UPDATE food_donations SET is_hidden = 1 WHERE Fid = ?";
    $stmt = $connection->prepare($updateQuery);
    $stmt->bind_param('i', $donationId);
    $stmt->execute();
    $stmt->close();
    
    // Refresh the page to show updated list
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Feed Forward</title>
    <link rel="stylesheet" href="../home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #003d3a 0%, #006d68 100%);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 0 30px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .admin-logo {
            font-size: 24px;
            font-weight: 700;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-logo i {
            font-size: 28px;
            color: #4ade80;
        }

        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 15px 30px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: #4ade80;
        }

        .nav-link i {
            font-size: 18px;
            margin-right: 15px;
            width: 20px;
            text-align: center;
        }

        .logout-section {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .logout-link {
            display: flex;
            align-items: center;
            padding: 15px 30px;
            color: #ff6b6b;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .logout-link:hover {
            background: rgba(255,107,107,0.1);
            color: #ff5252;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 20px;
        }

        .top-bar {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #003d3a;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-title i {
            color: #4ade80;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            color: #4a5568;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #003d3a, #4ade80);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        /* Dashboard Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #003d3a, #4ade80);
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .stat-icon.users { background: linear-gradient(135deg, #667eea, #764ba2); }
        .stat-icon.feedback { background: linear-gradient(135deg, #f093fb, #f5576c); }
        .stat-icon.donations { background: linear-gradient(135deg, #4facfe, #00f2fe); }

        .stat-info h3 {
            font-size: 2.5em;
            font-weight: 700;
            color: #003d3a;
            margin: 0;
        }

        .stat-info p {
            color: #4a5568;
            margin: 5px 0 0 0;
            font-weight: 500;
        }

        /* Table Section */
        .table-section {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .section-header {
            padding: 30px;
            border-bottom: 1px solid #e2e8f0;
        }

        .section-title {
            font-size: 24px;
            font-weight: 700;
            color: #003d3a;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .section-title i {
            color: #4ade80;
        }

        .table-container {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background: #f8fafc;
            padding: 20px 15px;
            text-align: left;
            font-weight: 600;
            color: #003d3a;
            border-bottom: 2px solid #e2e8f0;
        }

        .data-table td {
            padding: 20px 15px;
            border-bottom: 1px solid #e2e8f0;
            color: #4a5568;
        }

        .data-table tr:hover {
            background: #f8fafc;
        }

        .action-btn {
            background: linear-gradient(135deg, #003d3a, #006d68);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,61,58,0.3);
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-assigned {
            background: #dcfce7;
            color: #166534;
        }

        .status-available {
            background: #fef3c7;
            color: #92400e;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }

            .top-bar {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .data-table {
                font-size: 14px;
            }

            .data-table th,
            .data-table td {
                padding: 15px 10px;
            }
        }

        /* Mobile Menu Toggle */
        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: #003d3a;
            cursor: pointer;
        }

        @media (max-width: 1024px) {
            .mobile-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="admin-logo">
                    <i class="fa fa-shield"></i>
                    <span>ADMIN PANEL</span>
                </div>
            </div>

            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="#" class="nav-link active">
                        <i class="fa fa-dashboard"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="analytics.php" class="nav-link">
                        <i class="fa fa-bar-chart"></i>
                        <span>Analytics</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="donate.php" class="nav-link">
                        <i class="fa fa-heart"></i>
                        <span>Donations</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="feedback.php" class="nav-link">
                        <i class="fa fa-comments"></i>
                        <span>Feedbacks</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="adminprofile.php" class="nav-link">
                        <i class="fa fa-user"></i>
                        <span>Profile</span>
                    </a>
                </li>
            </ul>

            <div class="logout-section">
                <a href="../logout.php" class="logout-link">
                    <i class="fa fa-sign-out"></i>
                    <span>Logout</span>
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <div class="top-bar">
                <button class="mobile-toggle" onclick="toggleSidebar()">
                    <i class="fa fa-bars"></i>
                </button>
                <h1 class="page-title">
                    <i class="fa fa-tachometer"></i>
                    Dashboard
                </h1>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['name']; ?></span>
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon users">
                            <i class="fa fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <?php
                            $query = "SELECT count(*) as count FROM login";
                            $result = mysqli_query($connection, $query);
                            $row = mysqli_fetch_assoc($result);
                            echo "<h3>" . $row['count'] . "</h3>";
                            ?>
                            <p>Total Users</p>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon feedback">
                            <i class="fa fa-comments"></i>
                        </div>
                        <div class="stat-info">
                            <?php
                            $query = "SELECT count(*) as count FROM user_feedback";
                            $result = mysqli_query($connection, $query);
                            $row = mysqli_fetch_assoc($result);
                            echo "<h3>" . $row['count'] . "</h3>";
                            ?>
                            <p>Feedbacks</p>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon donations">
                            <i class="fa fa-heart"></i>
                        </div>
                        <div class="stat-info">
                            <?php
                            $query = "SELECT count(*) as count FROM food_donations";
                            $result = mysqli_query($connection, $query);
                            $row = mysqli_fetch_assoc($result);
                            echo "<h3>" . $row['count'] . "</h3>";
                            ?>
                            <p>Total Donations</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Donations Table -->
            <div class="table-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fa fa-clock-o"></i>
                        Recent Donations
                    </h2>
                </div>

                <div class="table-container">
                    <?php
                    $loc = $_SESSION['location'];
                    // Debug: Uncomment to see what location is being used
                    // echo "<!-- Debug: Admin location = " . htmlspecialchars($loc) . " -->";
                    
                    // Only show unassigned and non-hidden donations for the admin's location
                    $sql = "SELECT * FROM food_donations 
                            WHERE (assigned_to IS NULL OR assigned_to = '') 
                            AND (is_hidden IS NULL OR is_hidden = 0)
                            AND LOWER(location) = LOWER('" . mysqli_real_escape_string($connection, $loc) . "') 
                            ORDER BY date DESC";
                    $result = mysqli_query($connection, $sql);
                    $id = $_SESSION['Aid'];
                    if (!$result) {
                        die("Error executing query: " . mysqli_error($connection));
                    }
                    $data = array();
                    while ($row = mysqli_fetch_assoc($result)) {
                        $data[] = $row;
                    }
                    if (isset($_POST['food']) && isset($_POST['delivery_person_id'])) {
                        $order_id = $_POST['order_id'];
                        $delivery_person_id = $_POST['delivery_person_id'];
                        
                        // First check if donation exists and get its current status
                        $checkQuery = "SELECT email, food, name, assigned_to FROM food_donations WHERE Fid = $order_id";
                        $checkResult = mysqli_query($connection, $checkQuery);
                        
                        if (!$checkResult || mysqli_num_rows($checkResult) == 0) {
                            die("Donation not found.");
                        }
                        
                        $donationData = mysqli_fetch_assoc($checkResult);
                        
                        // Redirect if already assigned
                        if ($donationData['assigned_to'] != null && $donationData['assigned_to'] != '') {
                            header('Location: ' . $_SERVER['HTTP_REFERER']);
                            exit();
                        }
                        
                        // Update assignment and mark as hidden
                        $sql = "UPDATE food_donations SET assigned_to = $delivery_person_id, is_hidden = 1 WHERE Fid = $order_id AND (assigned_to IS NULL OR assigned_to = '')";
                        $result = mysqli_query($connection, $sql);
                        
                        if (!$result) {
                            die("Error assigning order: " . mysqli_error($connection));
                        }
                        
                        // Redirect after successful assignment
                        header('Location: ' . $_SERVER['PHP_SELF']);
                        ob_end_flush();
                        exit();
                    }
                    ?>
                    
                    <?php if (empty($data)): ?>
                        <div style="padding: 40px; text-align: center; background: #f8fafc; border-radius: 12px; border: 2px dashed #cbd5e1;">
                            <div style="font-size: 48px; margin-bottom: 16px;">📦</div>
                            <h3 style="color: #475569; margin-bottom: 8px;">No Donations Available</h3>
                            <p style="color: #64748b;">There are currently no unassigned donations for your location (<?php echo htmlspecialchars($loc); ?>).</p>
                            <p style="color: #64748b; font-size: 0.9em; margin-top: 8px;">Total donations in database: <?php 
                                $count_sql = "SELECT COUNT(*) as total FROM food_donations WHERE LOWER(location) = LOWER('" . mysqli_real_escape_string($connection, $loc) . "')";
                                $count_result = mysqli_query($connection, $count_sql);
                                if ($count_result) {
                                    $count_row = mysqli_fetch_assoc($count_result);
                                    echo $count_row['total'];
                                } else {
                                    echo "Error";
                                }
                            ?></p>
                            <p style="color: #64748b; font-size: 0.9em;">Unassigned donations: <?php 
                                $unassigned_sql = "SELECT COUNT(*) as total FROM food_donations WHERE assigned_to IS NULL AND LOWER(location) = LOWER('" . mysqli_real_escape_string($connection, $loc) . "')";
                                $unassigned_result = mysqli_query($connection, $unassigned_sql);
                                if ($unassigned_result) {
                                    $unassigned_row = mysqli_fetch_assoc($unassigned_result);
                                    echo $unassigned_row['total'];
                                } else {
                                    echo "Error";
                                }
                            ?></p>
                        </div>
                    <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Food</th>
                                <th>Category</th>
                                <th>Phone</th>
                                <th>Date/Time</th>
                                <th>Address</th>
                                <th>Quantity</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $row) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['food']); ?></td>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phoneno']); ?></td>
                                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                                    <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                    <td>
                                        <?php if ($row['assigned_to'] == null || $row['assigned_to'] == '') { ?>
                                            <span class="status-badge status-available">Available</span>
                                        <?php } else if ($row['assigned_to'] == $id) { ?>
                                            <span class="status-badge status-assigned">Assigned to You</span>
                                        <?php } else { ?>
                                            <span class="status-badge status-assigned">Assigned to Other</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php 
                                        // Check if food is marked as fake first
                                        if (!empty($row['is_fake_photo'])) { ?>
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <span style="color: #b91c1c; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                                                    <i class="fa fa-ban" style="font-size: 14px;"></i>
                                                    Marked as fake - Not eligible for distribution
                                                </span>
                                                <form method="post" action="" style="margin: 0;">
                                                    <input type="hidden" name="donation_id" value="<?php echo $row['Fid']; ?>">
                                                    <button type="submit" name="remove_fake_donation" class="action-btn" style="background: #4ade80; color: white; padding: 5px 10px; border-radius: 4px; border: none; cursor: pointer;">
                                                        OK
                                                    </button>
                                                </form>
                                            </div>
                                        <?php } else if ($row['assigned_to'] == null || $row['assigned_to'] == '') { ?>
                                            <form method="post" action="" style="margin: 0;">
                                                <input type="hidden" name="order_id" value="<?php echo $row['Fid']; ?>">
                                                <input type="hidden" name="delivery_person_id" value="<?php echo $id; ?>">
                                                <button type="submit" name="food" class="action-btn">
                                                    <i class="fa fa-hand-grab-o"></i> Get Food
                                                </button>
                                            </form>
                                        <?php } else if ($row['assigned_to'] == $id) { ?>
                                            <span style="color: #4ade80; font-weight: 600;">✓ Assigned to you</span>
                                        <?php } else { ?>
                                            <span style="color: #f59e0b; font-weight: 600;">Assigned to another delivery person</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const mobileToggle = document.querySelector('.mobile-toggle');
            
            if (window.innerWidth <= 1024) {
                if (!sidebar.contains(event.target) && !mobileToggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
    </script>
</body>
</html> 