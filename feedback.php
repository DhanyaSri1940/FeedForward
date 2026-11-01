<?php
session_start();
include 'connection.php';

if (isset($_POST['send'])) {
    $email = $_POST['email'] ?? '';
    $name = $_POST['name'] ?? '';
    $msg = $_POST['message'] ?? '';

    // Sanitize input
    $sanitized_email = mysqli_real_escape_string($connection, $email);
    $sanitized_name = mysqli_real_escape_string($connection, $name);
    $sanitized_msg = mysqli_real_escape_string($connection, $msg);

    // Prepare and execute query
    $query = "INSERT INTO user_feedback (name, email, message) VALUES ('$sanitized_name', '$sanitized_email', '$sanitized_msg')";
    $query_run = mysqli_query($connection, $query);

    if ($query_run) {
        $success_message = "Thank you for your feedback! We'll get back to you soon.";
    } else {
        $error_message = "Sorry, there was an error saving your message. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - Feed Forward</title>
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

        /* Feedback Section */
        .feedback-section {
            padding: 4rem 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .feedback-card {
            background: var(--surface);
            border-radius: 25px;
            padding: 3rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
            text-align: center;
        }
        .feedback-card::before {
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

        .feedback-content {
            position: relative;
            z-index: 5;
        }

        .status-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 2.5rem;
            color: white;
            box-shadow: 0 10px 25px rgba(0, 61, 58, 0.3);
            animation: pulse 2s infinite;
        }

        .status-icon.success {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .status-icon.error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .status-icon.info {
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .feedback-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 1rem;
        }

        .feedback-message {
            font-size: 1.2rem;
            color: var(--muted-ink);
            margin-bottom: 2.5rem;
            line-height: 1.6;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: white;
            box-shadow: 0 10px 25px rgba(0, 61, 58, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(0, 61, 58, 0.4);
        }

        .btn-secondary {
            background: rgba(14, 165, 164, 0.1);
            color: var(--brand);
            border: 2px solid var(--brand-2);
        }

        .btn-secondary:hover {
            background: var(--brand-2);
            color: white;
            transform: translateY(-2px);
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

            .feedback-section {
                padding: 2rem 1rem;
            }

            .feedback-card {
                padding: 2rem;
            }

            .feedback-title {
                font-size: 2rem;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .hero {
                padding: 100px 1rem 1rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .feedback-card {
                padding: 1.5rem;
            }

            .status-icon {
                width: 80px;
                height: 80px;
                font-size: 2rem;
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
                <li><a href="profile.php" class="nav-link">Profile</a></li>
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
                Feedback <span class="highlight">Status</span>
            </h1>
            <p class="hero-subtitle">
                Thank you for sharing your thoughts with us. Your feedback helps us improve our services.
            </p>
        </div>
    </section>

    <!-- Feedback Section -->
    <section class="feedback-section">
        <div class="feedback-card">
            <div class="feedback-content">
                <?php if (isset($success_message)): ?>
                    <div class="status-icon success">
                        <i class="fas fa-check"></i>
                    </div>
                    <h2 class="feedback-title">Message Sent Successfully!</h2>
                    <p class="feedback-message"><?php echo $success_message; ?></p>
                    <div class="action-buttons">
                        <a href="contact.html" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Contact
                        </a>
                        <a href="home.php" class="btn btn-secondary">
                            <i class="fas fa-home"></i>
                            Go to Home
                        </a>
                    </div>
                <?php elseif (isset($error_message)): ?>
                    <div class="status-icon error">
                        <i class="fas fa-times"></i>
                    </div>
                    <h2 class="feedback-title">Oops! Something went wrong</h2>
                    <p class="feedback-message"><?php echo $error_message; ?></p>
                    <div class="action-buttons">
                        <a href="contact.html" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i>
                            Try Again
                        </a>
                        <a href="home.php" class="btn btn-secondary">
                            <i class="fas fa-home"></i>
                            Go to Home
                        </a>
                    </div>
                <?php else: ?>
                    <div class="status-icon info">
                        <i class="fas fa-info"></i>
                    </div>
                    <h2 class="feedback-title">Feedback Information</h2>
                    <p class="feedback-message">Please submit your feedback through the contact form to share your thoughts with us.</p>
                    <div class="action-buttons">
                        <a href="contact.html" class="btn btn-primary">
                            <i class="fas fa-envelope"></i>
                            Go to Contact Form
                        </a>
                        <a href="home.php" class="btn btn-secondary">
                            <i class="fas fa-home"></i>
                            Back to Home
                        </a>
                    </div>
                <?php endif; ?>
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

        // Add smooth animations
        document.addEventListener('DOMContentLoaded', () => {
            const feedbackCard = document.querySelector('.feedback-card');
            if (feedbackCard) {
                feedbackCard.style.opacity = '0';
                feedbackCard.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    feedbackCard.style.transition = 'all 0.6s ease-out';
                    feedbackCard.style.opacity = '1';
                    feedbackCard.style.transform = 'translateY(0)';
                }, 300);
            }
        });
    </script>
</body>
</html> 