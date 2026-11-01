<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed Forward - Home</title>
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

        .cta-btn {
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .cta-btn:hover {
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
            min-height: 100vh;
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

        .hero::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 100px;
            background: linear-gradient(transparent, rgba(255, 255, 255, 0.1));
            z-index: 1;
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
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 2rem;
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
            font-size: 1.4rem;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 3rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            line-height: 1.6;
            font-weight: 400;
        }

        .hero-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: white;
            padding: 1.25rem 2.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(0, 61, 58, 0.3);
            position: relative;
            overflow: hidden;
        }
        .hero-cta::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        .hero-cta:hover::before {
            left: 100%;
        }
        .hero-cta:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 35px rgba(0, 61, 58, 0.4);
        }

        /* Welcome Message */
        .welcome-message {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 3rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transform: translateY(20px);
            animation: slideInDown 0.8s ease-out 0.3s both;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .welcome-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--brand);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .welcome-title::before {
            content: "👋";
            font-size: 1.5rem;
        }

        .welcome-text {
            color: var(--muted-ink);
            font-size: 1.1rem;
            font-weight: 500;
        }

        /* Content Sections */
        .section {
            padding: 6rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
        }

        .section::before {
            content: "";
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--brand), var(--brand-2));
            border-radius: 2px;
        }

        .section-title {
            font-size: 3rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 1.5rem;
            color: var(--ink);
            position: relative;
        }
        .section-title::after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--brand-2), transparent);
            border-radius: 2px;
        }

        .section-subtitle {
            font-size: 1.2rem;
            text-align: center;
            color: var(--muted-ink);
            margin-bottom: 4rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        /* Quote Section */
        .quote-section {
            background: linear-gradient(135deg, var(--surface), #f8fbfb);
            border-radius: 25px;
            padding: 4rem 3rem;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border);
            margin: 3rem auto;
            max-width: 900px;
            position: relative;
            overflow: hidden;
        }
        .quote-section::before {
            content: """;
            position: absolute;
            top: -20px;
            left: 30px;
            font-size: 120px;
            color: rgba(14, 165, 164, 0.1);
            font-family: serif;
        }

        .quote-text {
            font-size: 1.8rem;
            font-style: italic;
            color: var(--ink);
            margin-bottom: 2rem;
            line-height: 1.5;
            position: relative;
            z-index: 1;
        }

        .quote-author {
            color: var(--brand);
            font-weight: 700;
            font-size: 1.1rem;
            position: relative;
            z-index: 1;
        }
        .quote-author::before {
            content: "— ";
        }

        /* Works Section */
        .works-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2.5rem;
            margin-top: 3rem;
        }

        .work-card {
            background: var(--surface);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            position: relative;
        }
        .work-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--brand), var(--brand-2));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        .work-card:hover::before {
            transform: scaleX(1);
        }
        .work-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .work-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .work-card:hover .work-image {
            transform: scale(1.05);
        }

        .work-content {
            padding: 2rem;
        }

        .work-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .work-title::before {
            content: "✨";
            font-size: 1.2rem;
        }

        .work-description {
            color: var(--muted-ink);
            font-size: 1rem;
            line-height: 1.6;
        }

        /* Delivery Section */
        .delivery-section {
            background: linear-gradient(135deg, rgba(14, 165, 164, 0.08), rgba(0, 61, 58, 0.08));
            border-radius: 25px;
            padding: 4rem 3rem;
            text-align: center;
            margin: 3rem auto;
            max-width: 900px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(14, 165, 164, 0.1);
        }
        .delivery-section::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(14, 165, 164, 0.03) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .delivery-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--brand);
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .delivery-text {
            font-size: 1.2rem;
            color: var(--muted-ink);
            margin-bottom: 3rem;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }

        .delivery-image {
            max-width: 350px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1;
            transition: transform 0.3s ease;
        }
        .delivery-image:hover {
            transform: scale(1.05);
        }

        /* Footer */
        .footer {
            background: var(--ink);
            color: white;
            padding: 3rem 2rem 2rem;
            margin-top: 4rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .footer-section h3 {
            color: var(--brand-2);
            margin-bottom: 1rem;
            font-size: 1.25rem;
        }

        .footer-section p {
            color: #a0a0a0;
            line-height: 1.6;
            margin-bottom: 0.5rem;
        }

        .footer-section a {
            color: #a0a0a0;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .footer-section a:hover {
            color: var(--brand-2);
        }

        .footer-logo {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }
        .footer-logo .brand-name {
            color: var(--brand-2);
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

            .section-title {
                font-size: 2rem;
            }

            .quote-text {
                font-size: 1.25rem;
            }

            .works-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .hero {
                padding: 100px 1rem 1rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .section {
                padding: 2rem 1rem;
            }

            .quote-section,
            .delivery-section {
                padding: 2rem 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="#" class="logo">Feed <span class="brand-name">Forward</span></a>
            
            <ul class="nav-menu">
                <li><a href="#home" class="nav-link active">Home</a></li>
                <li><a href="about.html" class="nav-link">About</a></li>
                <li><a href="contact.html" class="nav-link">Contact</a></li>
                <?php if(isset($_SESSION['name']) && $_SESSION['name'] != ''): ?>
                    <li><a href="profile.php" class="nav-link">Profile</a></li>
                    <li><a href="logout.php" class="cta-btn">Logout</a></li>
                <?php else: ?>
                    <li><a href="signin.php" class="cta-btn">Sign In</a></li>
                <?php endif; ?>
            </ul>
            
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <?php if(isset($_SESSION['name']) && $_SESSION['name'] != ''): ?>
                <div class="welcome-message">
                    <h2 class="welcome-title">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
                    <p class="welcome-text">Ready to make a difference today?</p>
            </div>
        <?php endif; ?>
            
            <h1 class="hero-title">
                Reduce Food Waste, <span class="highlight">Feed Forward</span>
            </h1>
            <p class="hero-subtitle">
                Connect donors, administrators, and delivery partners to route surplus food to those who need it most. 
                Together, we can make a meaningful impact on food waste and hunger.
            </p>
            <a href="<?php echo isset($_SESSION['name']) && $_SESSION['name'] != '' ? 'fooddonateform.php' : 'signin.php'; ?>" class="hero-cta">
                <i class="fas fa-heart"></i>
                <?php echo isset($_SESSION['name']) && $_SESSION['name'] != '' ? 'Donate Food Now' : 'Get Started'; ?>
            </a>
        </div>
    </section>

    <!-- Quote Section -->
    <section class="section">
        <div class="quote-section">
            <p class="quote-text">
            "Cutting food waste is a delicious way of saving money, helping to feed the world and protect the planet."
        </p>
            <p class="quote-author">— Food Waste Reduction Initiative</p>
        </div>
    </section>

    <!-- Our Works Section -->
    <section class="section">
        <h2 class="section-title">Our Impact</h2>
        <p class="section-subtitle">Look what we can do together</p>
        
        <div class="works-grid">
            <div class="work-card">
                <img src="img/p6.jpg" alt="Food Donation" class="work-image">
                <div class="work-content">
                    <h3 class="work-title">Food Collection</h3>
                    <p class="work-description">Efficiently collecting surplus food from restaurants, hotels, and events.</p>
                </div>
            </div>
            
            <div class="work-card">
                <img src="img/p2.jpeg" alt="Food Distribution" class="work-image">
                <div class="work-content">
                    <h3 class="work-title">Smart Distribution</h3>
                    <p class="work-description">Quick and reliable delivery to communities in need.</p>
                </div>
            </div>
            
            <div class="work-card">
                <img src="img/p.jpg" alt="Community Impact" class="work-image">
                <div class="work-content">
                    <h3 class="work-title">Community Impact</h3>
                    <p class="work-description">Making a real difference in people's lives through food security.</p>
    </div>
    </div>
    </div>
    </section>

    <!-- Door Pickup Section -->
    <section class="section">
        <div class="delivery-section">
            <h2 class="delivery-title">Door Pickup Service</h2>
            <p class="delivery-text">
                "Your generous donation is collected instantly and reaches those in need when it matters most."
            </p>
            <img src="img/delivery.gif" alt="Delivery Service" class="delivery-image">
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>About Us</h3>
                <p>
                    The basic concept of this project Food Waste Management is to collect the excess/leftover food 
                    from donors such as hotels, restaurants, marriage halls, etc and distribute to the needy people.
                </p>
            </div>
            
            <div class="footer-section">
                <h3>Contact</h3>
                <p><i class="fas fa-phone"></i> (+00) 0000 000 000</p>
                <p><i class="fas fa-envelope"></i> <a href="mailto:FeedForward@gmail.com">FeedForward@gmail.com</a></p>
            </div>

            <div class="footer-section">
                <div class="footer-logo">Feed <span class="brand-name">Forward</span></div>
            <p class="menu">
                    <a href="#home">Home</a> |
                <a href="about.html">About</a> |
                <?php if(isset($_SESSION['name']) && $_SESSION['name'] != ''): ?>
                    <a href="profile.php">Profile</a> |
                <?php else: ?>
                    <a href="signin.php">Sign In</a> |
                <?php endif; ?>
                <a href="contact.html">Contact</a>
            </p>
            </div>
        </div>
    </footer>

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

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
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
    </script>
</body>
</html> 