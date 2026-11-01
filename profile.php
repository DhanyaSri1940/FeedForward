<?php
session_start();
if (!isset($_SESSION['name']) || $_SESSION['name'] == '') {
    header("location: signin.php");
    exit();
}
include("connection.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Feed Forward</title>
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

        .logout-btn {
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
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

        /* Profile Section */
        .profile-section {
            padding: 4rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .profile-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 3rem;
            margin-top: 2rem;
        }

        /* Profile Card */
        .profile-card {
            background: var(--surface);
            border-radius: 25px;
            padding: 3rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
            height: fit-content;
        }
        .profile-card::before {
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

        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2.5rem;
            color: white;
            box-shadow: 0 10px 25px rgba(0, 61, 58, 0.3);
        }

        .profile-name {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 0.5rem;
        }

        .profile-role {
            color: var(--brand);
            font-weight: 600;
            font-size: 1rem;
        }

        .profile-info {
            position: relative;
            z-index: 5;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border);
        }
        .info-item:last-child {
            border-bottom: none;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, rgba(14, 165, 164, 0.1), rgba(0, 61, 58, 0.1));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--brand);
            font-size: 1.2rem;
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-size: 0.9rem;
            color: var(--muted-ink);
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 1.1rem;
            color: var(--ink);
            font-weight: 600;
        }

        .logout-button {
            width: 100%;
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 2rem;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            position: relative;
            z-index: 10;
        }
        .logout-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 61, 58, 0.3);
        }

        /* Donations Section */
        .donations-section {
            background: var(--surface);
            border-radius: 25px;
            padding: 3rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }

        .section-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--ink);
        }

        .donations-table {
            width: 100%;
            border-collapse: collapse;
            position: relative;
            z-index: 1;
        }

        .donations-table th {
            background: linear-gradient(135deg, rgba(14, 165, 164, 0.1), rgba(0, 61, 58, 0.1));
            color: var(--brand);
            font-weight: 600;
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid var(--border);
        }

        .donations-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            color: var(--ink);
        }

        .donations-table tr:hover {
            background: rgba(14, 165, 164, 0.05);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--muted-ink);
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--border);
            margin-bottom: 1rem;
        }

        .empty-text {
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .empty-subtext {
            color: var(--muted-ink);
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

            .profile-container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .profile-card,
            .donations-section {
                padding: 2rem;
            }

            .donations-table {
                font-size: 0.9rem;
            }

            .donations-table th,
            .donations-table td {
                padding: 0.75rem 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .hero {
                padding: 100px 1rem 1rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .profile-section {
                padding: 2rem 1rem;
            }

            .profile-card,
            .donations-section {
                padding: 1.5rem;
            }

            .section-title {
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
                <li><a href="profile.php" class="nav-link active">Profile</a></li>
                <li><a href="logout.php" class="logout-btn">Logout</a></li>
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
                Welcome, <span class="highlight"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
            </h1>
            <p class="hero-subtitle">
                Manage your profile and track your food donation contributions
            </p>
        </div>
    </section>

    <!-- Profile Section -->
    <section class="profile-section">
        <div class="profile-container">
            <!-- Profile Card -->
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h2 class="profile-name"><?php echo htmlspecialchars($_SESSION['name']); ?></h2>
                    <p class="profile-role">Food Donor</p>
                </div>
                
                <div class="profile-info">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Name</div>
                            <div class="info-value"><?php echo htmlspecialchars($_SESSION['name']); ?></div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-venus-mars"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Gender</div>
                            <div class="info-value"><?php echo htmlspecialchars(ucfirst($_SESSION['gender'])); ?></div>
                        </div>
                    </div>
                </div>
                
                <a href="logout.php" class="logout-button" onclick="console.log('Logout button clicked');">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <!-- Donations Section -->
            <div class="donations-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h2 class="section-title">Your Donations</h2>
                </div>
                
                        <?php
                        $email = $_SESSION['email'];
                $query = "SELECT * FROM food_donations WHERE email=? ORDER BY date DESC";
                $stmt = $connection->prepare($query);
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result && $result->num_rows > 0) {
                    echo '<table class="donations-table">';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th>Food Item</th>';
                    echo '<th>Type</th>';
                    echo '<th>Category</th>';
                    echo '<th>Date/Time</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['food']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['type']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['category']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['date']) . '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody>';
                    echo '</table>';
                } else {
                    echo '<div class="empty-state">';
                    echo '<div class="empty-icon">🍽️</div>';
                    echo '<div class="empty-text">No donations yet</div>';
                    echo '<div class="empty-subtext">Start making a difference by donating food today!</div>';
                    echo '</div>';
                }
                $stmt->close();
                ?>
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

        // Ensure logout button is working
        document.addEventListener('DOMContentLoaded', () => {
            const logoutButton = document.querySelector('.logout-button');
            if (logoutButton) {
                logoutButton.addEventListener('click', (e) => {
                    console.log('Logout button clicked via event listener');
                    // Allow the default behavior (navigation to logout.php)
                });
            }
        });
    </script>
</body>
</html> 