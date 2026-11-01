<?php
ob_start();
include("connect.php");
include '../connection.php';
if (!isset($_SESSION['name']) || $_SESSION['name'] == '') {
    header("Location: deliverylogin.php");
    exit();
}
$name = $_SESSION['name'];
$city = $_SESSION['city'];
$id = $_SESSION['Did'];

// Fetch orders - exclude fake photos
$sql = "SELECT fd.Fid AS Fid, fd.location as cure, fd.name, fd.phoneno, fd.date, fd.delivery_by, fd.address as From_address, ad.name AS delivery_person_name, ad.address AS To_address FROM food_donations fd LEFT JOIN admin ad ON fd.assigned_to = ad.Aid WHERE assigned_to IS NOT NULL AND delivery_by IS NULL AND (fd.is_fake_photo IS NULL OR fd.is_fake_photo = 0) AND fd.location='$city'";
$result = mysqli_query($connection, $sql);
if (!$result) {
    die("Error executing query: " . mysqli_error($connection));
}
$data = array();
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}
// Handle order assignment
if (isset($_POST['food']) && isset($_POST['delivery_person_id'])) {
    $order_id = $_POST['order_id'];
    $delivery_person_id = $_POST['delivery_person_id'];
    $sql = "SELECT * FROM food_donations WHERE Fid = $order_id AND delivery_by IS NOT NULL";
    $result = mysqli_query($connection, $sql);
    if (mysqli_num_rows($result) > 0) {
        die("Sorry, this order has already been assigned to someone else.");
    }
    $sql = "UPDATE food_donations SET delivery_by = $delivery_person_id WHERE Fid = $order_id";
    $result = mysqli_query($connection, $sql);
    if (!$result) {
        die("Error assigning order: " . mysqli_error($connection));
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    ob_end_flush();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Dashboard - Feed Forward</title>
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

        /* Hero Section */
        .hero-section {
            text-align: center;
            padding: 3rem 2rem;
            margin-bottom: 3rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
            background: var(--surface);
            border-radius: 25px;
            padding: 3rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .hero-text-content h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--brand);
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .hero-text-content p {
            font-size: 1.1rem;
            color: var(--muted-ink);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .hero-image {
            max-width: 100%;
            height: auto;
            border-radius: 20px;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }

        .hero-image:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }

        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: white;
            padding: 1rem 2rem;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            background: linear-gradient(135deg, var(--brand-2), var(--brand));
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
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--brand);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--muted-ink);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Orders Table Section */
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

        .table-container {
            background: var(--surface);
            border-radius: 25px;
            box-shadow: var(--shadow);
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: var(--surface);
            font-size: 0.9rem;
        }

        .table thead {
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: white;
        }

        .table th {
            padding: 1.5rem 1rem;
            text-align: center;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
            position: relative;
        }

        .table th:not(:last-child)::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            height: 60%;
            width: 1px;
            background: rgba(255, 255, 255, 0.3);
        }

        .table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid var(--border);
        }

        .table tbody tr:hover {
            background: rgba(14, 165, 164, 0.05);
            transform: scale(1.01);
        }

        .table td {
            padding: 1.5rem 1rem;
            text-align: center;
            border: none;
            vertical-align: middle;
        }

        .table td:first-child {
            font-weight: 600;
            color: var(--brand);
        }

        /* Button Styling */
        .btn {
            background: linear-gradient(135deg, var(--success), #20c997);
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
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            background: linear-gradient(135deg, #20c997, var(--success));
        }

        .btn:active {
            transform: translateY(0);
        }

        /* Status Indicators */
        .status-assigned {
            background: linear-gradient(135deg, var(--success), #20c997);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-other {
            background: linear-gradient(135deg, var(--error), #e74c3c);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
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

            .hero-content {
                grid-template-columns: 1fr;
                text-align: center;
                padding: 2rem;
            }

            .hero-text-content h2 {
                font-size: 2rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .table-container {
                margin: 1rem;
                border-radius: 15px;
            }

            .table th,
            .table td {
                padding: 1rem 0.5rem;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 600px) {
            .table thead {
                display: none;
            }

            .table tbody tr {
                display: block;
                margin-bottom: 1.5rem;
                border: 2px solid var(--border);
                border-radius: 15px;
                padding: 1rem;
                background: var(--surface);
            }

            .table td {
                display: block;
                text-align: right;
                padding: 0.75rem 1rem;
                border-bottom: 1px solid var(--border);
                position: relative;
            }

            .table td:last-child {
                border-bottom: none;
            }

            .table td::before {
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

        .table tbody tr {
            animation: fadeInUp 0.6s ease-out;
        }

        .table tbody tr:nth-child(1) { animation-delay: 0.1s; }
        .table tbody tr:nth-child(2) { animation-delay: 0.2s; }
        .table tbody tr:nth-child(3) { animation-delay: 0.3s; }
        .table tbody tr:nth-child(4) { animation-delay: 0.4s; }
        .table tbody tr:nth-child(5) { animation-delay: 0.5s; }

        /* Enhanced focus states for accessibility */
        .btn:focus,
        .action-button:focus,
        .nav-link:focus {
            outline: 3px solid rgba(0, 61, 58, 0.3);
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="#" class="logo">Feed <span class="brand-name">Forward</span></a>
            
            <ul class="nav-menu">
                <li><a href="#dashboard" class="nav-link active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
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
                <i class="fas fa-truck"></i> Welcome, <?php echo htmlspecialchars($name); ?>!
            </h1>
            <p class="welcome-subtitle">
                Ready to deliver food to those in need? View available orders below and make a difference in your community.
            </p>
        </div>
    </section>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <div class="hero-text-content">
                <h2>Your Delivery Dashboard</h2>
                <p>Connect with food donors and recipients in your area. Every delivery you make helps reduce food waste and feeds someone in need.</p>
                <a href="deliverymyord.php" class="action-button">
                    <i class="fas fa-list-alt"></i> View My Orders
                </a>
            </div>
            <div class="hero-image-container">
                <img src="../img/delivery.gif" alt="Delivery" class="hero-image">
            </div>
        </div>
    </section>

    <!-- Dashboard Stats -->
    <section class="stats-section">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-number"><?php echo count($data); ?></div>
                <div class="stat-label">Available Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="stat-number"><?php echo htmlspecialchars($city); ?></div>
                <div class="stat-label">Service Area</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number">24/7</div>
                <div class="stat-label">Service Hours</div>
            </div>
        </div>
    </section>

    <!-- Orders Section -->
    <section class="orders-section">
        <div class="section-header">
            <h2 class="section-title">Available Orders</h2>
            <p class="section-subtitle">Take orders to start delivering food to those in need</p>
        </div>

        <div class="table-container">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-user"></i> Name</th>
                            <th><i class="fas fa-phone"></i> Phone No</th>
                            <th><i class="fas fa-calendar-alt"></i> Date/Time</th>
                            <th><i class="fas fa-map-marker-alt"></i> Pickup Address</th>
                            <th><i class="fas fa-flag-checkered"></i> Delivery Address</th>
                            <th><i class="fas fa-cogs"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data)) { ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-inbox"></i>
                                        </div>
                                        <div class="empty-title">No Orders Available</div>
                                        <div class="empty-description">Check back later for new delivery opportunities!</div>
                                    </div>
                                </td>
                            </tr>
                        <?php } else { ?>
                            <?php foreach ($data as $row) { ?>
                                <tr>
                                    <td data-label="Name">
                                        <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                    </td>
                                    <td data-label="Phone">
                                        <a href="tel:<?php echo htmlspecialchars($row['phoneno']); ?>" style="color: var(--brand); text-decoration: none;">
                                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($row['phoneno']); ?>
                                        </a>
                                    </td>
                                    <td data-label="Date/Time">
                                        <i class="fas fa-clock"></i> <?php echo htmlspecialchars($row['date']); ?>
                                    </td>
                                    <td data-label="Pickup Address">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['From_address']); ?>
                                    </td>
                                    <td data-label="Delivery Address">
                                        <i class="fas fa-flag-checkered"></i> <?php echo htmlspecialchars($row['To_address']); ?>
                                    </td>
                                    <td data-label="Action">
                                        <?php if ($row['delivery_by'] == null) { ?>
                                            <form method="post" action="">
                                                <input type="hidden" name="order_id" value="<?php echo $row['Fid']; ?>">
                                                <input type="hidden" name="delivery_person_id" value="<?php echo $id; ?>">
                                                <button type="submit" name="food" class="btn">
                                                    <i class="fas fa-hand-paper"></i> Take Order
                                                </button>
                                            </form>
                                        <?php } else if ($row['delivery_by'] == $id) { ?>
                                            <span class="status-assigned">
                                                <i class="fas fa-check-circle"></i> Assigned to you
                                            </span>
                                        <?php } else { ?>
                                            <span class="status-assigned">
                                                <i class="fas fa-times-circle"></i> Assigned to another
                                            </span>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
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

        // Add smooth animations
        document.addEventListener('DOMContentLoaded', () => {
            const elements = document.querySelectorAll('.stat-card, .table-container');
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