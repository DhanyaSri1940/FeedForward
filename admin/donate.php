<?php
include "../connection.php";
include "connect.php";
if (!isset($_SESSION['name']) || $_SESSION['name'] == '') {
    header("Location: signin.php");
    exit();
}

$action_message = '';
$action_error = '';

// Check if 'strikes' column exists in login table, if not add it
$checkColumn = $connection->query("SHOW COLUMNS FROM `login` LIKE 'strikes'");
if($checkColumn->num_rows == 0) {
    $connection->query("ALTER TABLE `login` ADD COLUMN `strikes` INT NOT NULL DEFAULT '0' AFTER `password`");
}

if (isset($_POST['mark_fake'], $_POST['donation_id'])) {
    $donationId = (int)$_POST['donation_id'];

    mysqli_begin_transaction($connection);

    $donationStmt = $connection->prepare("SELECT Fid, email, is_fake_photo FROM food_donations WHERE Fid = ? FOR UPDATE");
    $donationStmt->bind_param('i', $donationId);
    $donationStmt->execute();
    $donationResult = $donationStmt->get_result();

    if ($donationResult && $donationResult->num_rows === 1) {
        $donation = $donationResult->fetch_assoc();

        if ((int)$donation['is_fake_photo'] === 1) {
            $action_error = "This donation has already been marked as a fake photo.";
            mysqli_rollback($connection);
        } else {
            $updateDonation = $connection->prepare("UPDATE food_donations SET is_fake_photo = 1 WHERE Fid = ?");
            $updateDonation->bind_param('i', $donationId);

            $incrementStrikes = $connection->prepare("UPDATE login SET strikes = strikes + 1 WHERE email = ?");
            $incrementStrikes->bind_param('s', $donation['email']);

            $updateOk = $updateDonation->execute() && $incrementStrikes->execute();

            if ($updateOk) {
                $strikeStmt = $connection->prepare("SELECT strikes FROM login WHERE email = ?");
                $strikeStmt->bind_param('s', $donation['email']);
                $strikeStmt->execute();
                $strikeResult = $strikeStmt->get_result();
                $currentStrikes = 0;
                if ($strikeResult && $strikeResult->num_rows === 1) {
                    $strikeRow = $strikeResult->fetch_assoc();
                    $currentStrikes = (int)$strikeRow['strikes'];
                }
                $strikeStmt->close();

                mysqli_commit($connection);

                $action_message = "Donation #{$donationId} marked as Fake Photo.";
                if ($currentStrikes >= 3) {
                    $action_message .= " Donor is now blocked (strikes: {$currentStrikes}).";
                }

                // Notify donor about rejection (best-effort; suppress warnings if mail is not configured)
                $donorEmail = $donation['email'];
                $subject = "Your Donation Was Not Accepted - Feed Forward";
                $body = "Hello,\n\n";
                $body .= "We're sorry to inform you that your donation (ID: {$donationId}) was not accepted.\n\n";
                $body .= "Unfortunately, the uploaded photo was flagged as fake or reused. Please upload a fresh, clear photo of the actual food you wish to donate and resubmit your donation.\n\n";
                $body .= "Thank you for understanding.\n\n";
                $body .= "Best regards,\nFeed Forward Team";
                @mail($donorEmail, $subject, $body);
            } else {
                $action_error = "Unable to mark donation as fake at this time. Please try again.";
                mysqli_rollback($connection);
            }
            $updateDonation->close();
            $incrementStrikes->close();
        }
    } else {
        $action_error = "Donation not found.";
        mysqli_rollback($connection);
    }
    $donationStmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donations - Feed Forward</title>
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

        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }

        .filter-header {
            margin-bottom: 25px;
        }

        .filter-title {
            font-size: 20px;
            font-weight: 700;
            color: #003d3a;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .filter-title i {
            color: #4ade80;
        }

        .filter-subtitle {
            color: #4a5568;
            font-size: 14px;
        }

        .filter-form {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 600;
            color: #003d3a;
        }

        .form-select {
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            color: #1a202c;
            background: white;
            transition: all 0.3s ease;
            min-width: 200px;
        }

        .form-select:focus {
            outline: none;
            border-color: #003d3a;
            box-shadow: 0 0 0 3px rgba(0, 61, 58, 0.1);
        }

        .submit-btn {
            background: linear-gradient(135deg, #003d3a, #006d68);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,61,58,0.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,61,58,0.4);
        }

        /* Table Section */
        .table-section {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-header {
            padding: 30px;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-title {
            font-size: 20px;
            font-weight: 700;
            color: #003d3a;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .table-title i {
            color: #4ade80;
        }

        .table-subtitle {
            color: #4a5568;
            font-size: 14px;
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
            font-size: 14px;
        }

        .data-table td {
            padding: 20px 15px;
            border-bottom: 1px solid #e2e8f0;
            color: #4a5568;
            font-size: 14px;
        }

        .data-table tr:hover {
            background: #f8fafc;
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #4a5568;
        }

        .no-results i {
            font-size: 48px;
            color: #cbd5e0;
            margin-bottom: 20px;
        }

        .no-results h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #2d3748;
        }

        .no-results p {
            font-size: 14px;
            color: #718096;
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

            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }

            .form-select {
                min-width: auto;
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

            .filter-section,
            .table-section {
                padding: 20px;
            }

            .data-table {
                font-size: 12px;
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
                    <a href="#" class="nav-link active">
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
                    <i class="fa fa-heart"></i>
                    Donations
                </h1>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['name']; ?></span>
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($action_message)): ?>
                <div style="margin: 0 0 20px; padding: 14px 18px; border-radius: 10px; background: #ecfdf3; border: 1px solid #22c55e; color: #166534; font-weight: 600;">
                    <?php echo htmlspecialchars($action_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($action_error)): ?>
                <div style="margin: 0 0 20px; padding: 14px 18px; border-radius: 10px; background: #fef2f2; border: 1px solid #ef4444; color: #b91c1c; font-weight: 600;">
                    <?php echo htmlspecialchars($action_error); ?>
                </div>
            <?php endif; ?>

            <!-- Results Section -->
            <div class="table-section">
                    <div class="table-header">
                        <h2 class="table-title">
                            <i class="fa fa-list"></i>
                            Donation Details
                        </h2>
                        <p class="table-subtitle">Showing unassigned donations</p>
                    </div>

                    <div class="table-container">
                        <?php
                        $sql = "SELECT * FROM food_donations 
                               WHERE (is_hidden = 0 OR is_hidden IS NULL) 
                               AND (assigned_to IS NULL OR assigned_to = '')
                               ORDER BY date DESC";
                        $result = mysqli_query($connection, $sql);
                        
                        if ($result && mysqli_num_rows($result) > 0):
                        ?>
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
                                        <th>Photo</th>
                                        <th>Photo Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['food']); ?></td>
                                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                                            <td><?php echo htmlspecialchars($row['phoneno']); ?></td>
                                            <td><?php echo htmlspecialchars($row['date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                                            <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                            <td>
                                                <?php if (!empty($row['image_path'])): ?>
                                                    <a href="<?php echo '../' . htmlspecialchars($row['image_path']); ?>" target="_blank" style="color:#0ea5a4; font-weight:600; text-decoration:none;">
                                                        View
                                                    </a>
                                                    <br>
                                                    <img src="<?php echo '../' . htmlspecialchars($row['image_path']); ?>" alt="Donation photo" style="width:90px; height:90px; object-fit:cover; border-radius:10px; border:1px solid #e2e8f0; margin-top:6px;">
                                                <?php else: ?>
                                                    <span style="color:#9ca3af;">No image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($row['is_fake_photo'])): ?>
                                                    <span style="color:#b91c1c; font-weight:600;">Fake Photo</span>
                                                <?php else: ?>
                                                    <span style="color:#16a34a; font-weight:600;">Clean</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($row['is_fake_photo'])): ?>
                                                    <button type="button" disabled class="submit-btn" style="opacity:0.6; cursor:not-allowed;">Marked</button>
                                                <?php else: ?>
                                                    <form method="post" style="margin:0;">
                                                        <input type="hidden" name="donation_id" value="<?php echo (int)$row['Fid']; ?>">
                                                        <button type="submit" name="mark_fake" class="submit-btn" style="background: linear-gradient(135deg, #b91c1c, #ef4444); box-shadow: 0 5px 15px rgba(185,28,28,0.3);">
                                                            Mark as Fake Photo
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="no-results">
                                <i class="fa fa-inbox"></i>
                                <h3>No donations found</h3>
                                <p>There are no donations recorded at the moment.</p>
                            </div>
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