<?php
ob_start();
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
    <title>Analytics - Feed Forward</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
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

        /* Statistics Cards */
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

        /* Charts Section */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .chart-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            padding: 30px;
        }

        .chart-header {
            margin-bottom: 25px;
        }

        .chart-title {
            font-size: 20px;
            font-weight: 700;
            color: #003d3a;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .chart-title i {
            color: #4ade80;
        }

        .chart-subtitle {
            color: #4a5568;
            font-size: 14px;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
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

            .charts-grid {
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

            .chart-card {
                padding: 20px;
            }

            .chart-container {
                height: 250px;
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
                    <a href="admin.php" class="nav-link">
                        <i class="fa fa-dashboard"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link active">
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
                    <i class="fa fa-bar-chart"></i>
                    Analytics
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

            <!-- Charts Section -->
            <div class="charts-grid">
                <!-- User Gender Distribution Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h2 class="chart-title">
                            <i class="fa fa-users"></i>
                            User Gender Distribution
                        </h2>
                        <p class="chart-subtitle">Distribution of users by gender</p>
                    </div>
                    <div class="chart-container">
                        <canvas id="genderChart"></canvas>
                    </div>
                </div>

                <!-- Food Donation by Location Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h2 class="chart-title">
                            <i class="fa fa-map-marker"></i>
                            Donations by Location
                        </h2>
                        <p class="chart-subtitle">Food donations across different locations</p>
                    </div>
                    <div class="chart-container">
                        <canvas id="locationChart"></canvas>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Get data from PHP
        <?php
        $maleQuery = "SELECT count(*) as count FROM login WHERE gender='male'";
        $femaleQuery = "SELECT count(*) as count FROM login WHERE gender='female'";
        $maleResult = mysqli_query($connection, $maleQuery);
        $femaleResult = mysqli_query($connection, $femaleQuery);
        $maleRow = mysqli_fetch_assoc($maleResult);
        $femaleRow = mysqli_fetch_assoc($femaleResult);
        $male = $maleRow['count'];
        $female = $femaleRow['count'];
        
        $cbQuery = "SELECT count(*) as count FROM food_donations WHERE location='chikkaballapur'";
        $mysuruQuery = "SELECT count(*) as count FROM food_donations WHERE location='mysuru'";
        $bengaluruQuery = "SELECT count(*) as count FROM food_donations WHERE location='bengaluru'";
        $cbResult = mysqli_query($connection, $cbQuery);
        $mysuruResult = mysqli_query($connection, $mysuruQuery);
        $bengaluruResult = mysqli_query($connection, $bengaluruQuery);
        $cbRow = mysqli_fetch_assoc($cbResult);
        $mysuruRow = mysqli_fetch_assoc($mysuruResult);
        $bengaluruRow = mysqli_fetch_assoc($bengaluruResult);
        $chikkaballapur = $cbRow['count'];
        $mysuru = $mysuruRow['count'];
        $bengaluru = $bengaluruRow['count'];
        ?>

        // Gender Distribution Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: ['Male', 'Female'],
                datasets: [{
                    data: [<?php echo $male; ?>, <?php echo $female; ?>],
                    backgroundColor: [
                        '#003d3a',
                        '#4ade80'
                    ],
                    borderWidth: 0,
                    hoverBorderWidth: 3,
                    hoverBorderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        fontFamily: 'Poppins, sans-serif',
                        fontSize: 14
                    }
                },
                tooltips: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleFontFamily: 'Poppins, sans-serif',
                    bodyFontFamily: 'Poppins, sans-serif',
                    cornerRadius: 8
                }
            }
        });

        // Location Distribution Chart
        const locationCtx = document.getElementById('locationChart').getContext('2d');
        new Chart(locationCtx, {
            type: 'bar',
            data: {
                labels: ['Chikkaballapur', 'Mysuru', 'Bengaluru'],
                datasets: [{
                    label: 'Donations',
                    data: [<?php echo $chikkaballapur; ?>, <?php echo $mysuru; ?>, <?php echo $bengaluru; ?>],
                    backgroundColor: [
                        '#003d3a',
                        '#4ade80',
                        '#667eea'
                    ],
                    borderWidth: 0,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            fontFamily: 'Poppins, sans-serif',
                            fontSize: 12
                        },
                        gridLines: {
                            color: '#e2e8f0',
                            drawBorder: false
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            fontFamily: 'Poppins, sans-serif',
                            fontSize: 12
                        },
                        gridLines: {
                            display: false
                        }
                    }]
                },
                tooltips: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleFontFamily: 'Poppins, sans-serif',
                    bodyFontFamily: 'Poppins, sans-serif',
                    cornerRadius: 8
                }
            }
        });

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