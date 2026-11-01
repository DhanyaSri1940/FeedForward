<?php
include("connect.php");
if (!isset($_SESSION['name']) || $_SESSION['name'] == '') {
    header("Location: signin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - Feed Forward</title>
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

        /* Profile Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
            gap: 20px;
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

        .stat-icon.donations {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .stat-icon.completed {
            background: linear-gradient(135deg, #f093fb, #f5576c);
        }

        .stat-icon.pending {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
        }

        .stat-info h3 {
            font-size: 32px;
            font-weight: 700;
            color: #003d3a;
            margin: 0 0 5px 0;
        }

        .stat-info p {
            color: #6b7280;
            font-weight: 500;
            margin: 0;
        }

        /* Profile Info Card */
        .profile-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .profile-card::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(14, 165, 164, 0.02) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
            z-index: 0;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #003d3a, #4ade80);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            font-weight: 700;
            box-shadow: 0 10px 25px rgba(0, 61, 58, 0.3);
        }

        .profile-info h2 {
            font-size: 28px;
            font-weight: 700;
            color: #003d3a;
            margin: 0 0 5px 0;
        }

        .profile-info p {
            color: #6b7280;
            font-size: 16px;
            margin: 0;
        }

        .profile-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            position: relative;
            z-index: 1;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 12px;
            border-left: 4px solid #4ade80;
        }

        .detail-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #003d3a, #4ade80);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }

        .detail-content h4 {
            font-size: 14px;
            color: #6b7280;
            margin: 0 0 5px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-content p {
            font-size: 16px;
            color: #003d3a;
            font-weight: 600;
            margin: 0;
        }

        /* Donation History Table */
        .history-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            position: relative;
        }

        .history-container::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(14, 165, 164, 0.02) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
            z-index: 0;
        }

        .history-header {
            background: linear-gradient(135deg, #003d3a, #006d68);
            color: white;
            padding: 25px 30px;
            position: relative;
            z-index: 1;
        }

        .history-header h2 {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            position: relative;
            z-index: 1;
        }

        .history-table th {
            background: #f8fafc;
            color: #003d3a;
            font-weight: 600;
            padding: 20px;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .history-table td {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            color: #4a5568;
            vertical-align: top;
        }

        .history-table tr:hover {
            background: #f7fafc;
            transition: all 0.2s ease;
        }

        .history-table tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-completed {
            background: #dcfce7;
            color: #166534;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .empty-state {
            text-align: center;
            padding: 60px 30px;
            color: #6b7280;
            position: relative;
            z-index: 1;
        }

        .empty-icon {
            font-size: 64px;
            color: #d1d5db;
            margin-bottom: 20px;
        }

        .empty-text {
            font-size: 20px;
            margin-bottom: 10px;
            font-weight: 600;
            color: #374151;
        }

        .empty-subtext {
            color: #6b7280;
            font-size: 16px;
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

            .profile-details {
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

            .history-table {
                font-size: 14px;
            }

            .history-table th,
            .history-table td {
                padding: 15px 10px;
            }

            .history-container {
                overflow-x: auto;
            }

            .history-table {
                min-width: 800px;
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

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
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
                    <a href="admin.php" class="nav-link">
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
                    <a href="adminprofile.php" class="nav-link active">
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
                    <i class="fa fa-user"></i>
                    Admin Profile
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
                <?php
                $id = $_SESSION['Aid'];
                
                // Total donations assigned to this admin
                $total_query = "SELECT COUNT(*) as total FROM food_donations WHERE assigned_to = $id";
                $total_result = mysqli_query($connection, $total_query);
                $total_donations = mysqli_fetch_assoc($total_result)['total'];
                
                // Completed donations
                $completed_query = "SELECT COUNT(*) as completed FROM food_donations WHERE assigned_to = $id AND delivery_by IS NOT NULL";
                $completed_result = mysqli_query($connection, $completed_query);
                $completed_donations = mysqli_fetch_assoc($completed_result)['completed'];
                
                // Pending donations
                $pending_donations = $total_donations - $completed_donations;
                ?>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon donations">
                            <i class="fa fa-heart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_donations; ?></h3>
                            <p>Total Donations</p>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon completed">
                            <i class="fa fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $completed_donations; ?></h3>
                            <p>Completed</p>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon pending">
                            <i class="fa fa-clock-o"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $pending_donations; ?></h3>
                            <p>Pending</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Information -->
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                    </div>
                    <div class="profile-info">
                        <h2><?php echo $_SESSION['name']; ?></h2>
                        <p>Administrator</p>
                    </div>
                </div>
                
                <div class="profile-details">
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="detail-content">
                            <h4>Name</h4>
                            <p><?php echo $_SESSION['name']; ?></p>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fa fa-envelope"></i>
                        </div>
                        <div class="detail-content">
                            <h4>Email</h4>
                            <p><?php echo $_SESSION['email'] ?? 'admin@feedforward.com'; ?></p>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fa fa-shield"></i>
                        </div>
                        <div class="detail-content">
                            <h4>Role</h4>
                            <p>System Administrator</p>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        <div class="detail-content">
                            <h4>Member Since</h4>
                            <p><?php echo date('M Y'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Donation History -->
            <div class="history-container">
                <div class="history-header">
                    <h2>
                        <i class="fa fa-history"></i>
                        Donation History
                    </h2>
                </div>
                
                <?php
                $sql = "SELECT * FROM food_donations WHERE assigned_to = $id ORDER BY date DESC";
                $result = mysqli_query($connection, $sql);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    echo '<table class="history-table">';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th><i class="fa fa-user"></i> Donor Name</th>';
                    echo '<th><i class="fa fa-cutlery"></i> Food Item</th>';
                    echo '<th><i class="fa fa-tag"></i> Category</th>';
                    echo '<th><i class="fa fa-phone"></i> Phone</th>';
                    echo '<th><i class="fa fa-calendar"></i> Date</th>';
                    echo '<th><i class="fa fa-map-marker"></i> Address</th>';
                    echo '<th><i class="fa fa-balance-scale"></i> Quantity</th>';
                    echo '<th><i class="fa fa-info-circle"></i> Status</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        $status = $row['delivery_by'] ? 'completed' : 'pending';
                        $statusClass = $status === 'completed' ? 'status-completed' : 'status-pending';
                        $statusText = $status === 'completed' ? 'Completed' : 'Pending';
                        
                        echo '<tr>';
                        echo '<td><strong>' . htmlspecialchars($row['name']) . '</strong></td>';
                        echo '<td>' . htmlspecialchars($row['food']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['category']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['phoneno']) . '</td>';
                        echo '<td>' . date('M d, Y H:i', strtotime($row['date'])) . '</td>';
                        echo '<td>' . htmlspecialchars($row['address']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['quantity']) . '</td>';
                        echo '<td><span class="status-badge ' . $statusClass . '">' . $statusText . '</span></td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody>';
                    echo '</table>';
                } else {
                    echo '<div class="empty-state">';
                    echo '<div class="empty-icon">📋</div>';
                    echo '<div class="empty-text">No donation history yet</div>';
                    echo '<div class="empty-subtext">Donations assigned to you will appear here</div>';
                    echo '</div>';
                }
                ?>
            </div>
        </main>
    </div>

    <script>
        // Mobile sidebar toggle
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

        // Add smooth animations
        document.addEventListener('DOMContentLoaded', () => {
            const profileCard = document.querySelector('.profile-card');
            const historyContainer = document.querySelector('.history-container');
            
            if (profileCard) {
                profileCard.style.opacity = '0';
                profileCard.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    profileCard.style.transition = 'all 0.6s ease-out';
                    profileCard.style.opacity = '1';
                    profileCard.style.transform = 'translateY(0)';
                }, 300);
            }
            
            if (historyContainer) {
                historyContainer.style.opacity = '0';
                historyContainer.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    historyContainer.style.transition = 'all 0.6s ease-out';
                    historyContainer.style.opacity = '1';
                    historyContainer.style.transform = 'translateY(0)';
                }, 600);
            }
        });
    </script>
</body>
</html> 