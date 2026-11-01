<?php
include '../connection.php';
$msg = 0;
$error_message = '';
$success_message = '';

if (isset($_POST['sign'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $location = $_POST['district'] ?? '';
    $pass = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "SELECT * FROM delivery_persons WHERE email = '" . mysqli_real_escape_string($connection, $email) . "'";
    $result = mysqli_query($connection, $sql);
    $num = mysqli_num_rows($result);
    
    if ($num == 1) {
        $error_message = "Account already exists with this email address.";
    } else {
        $query = "INSERT INTO delivery_persons(name, email, password, city) VALUES ('" . mysqli_real_escape_string($connection, $username) . "', '" . mysqli_real_escape_string($connection, $email) . "', '$pass', '" . mysqli_real_escape_string($connection, $location) . "')";
        $query_run = mysqli_query($connection, $query);
        if ($query_run) {
            $success_message = "Registration successful! Redirecting to login...";
            header('refresh:2;url=deliverylogin.php');
        } else {
            $error_message = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Partner Registration - Feed Forward</title>
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



        /* Hero Section */
        .hero {
            min-height: 40vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 4rem 2rem;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(0, 61, 58, 0.8), rgba(14, 165, 164, 0.7)), url('../img/delivery.gif');
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

        /* Registration Section */
        .registration-section {
            padding: 4rem 2rem;
            max-width: 500px;
            margin: 0 auto;
        }

        .registration-card {
            background: var(--surface);
            border-radius: 25px;
            padding: 3rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }
        .registration-card::before {
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

        .registration-header {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }

        .registration-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: white;
            box-shadow: 0 10px 25px rgba(0, 61, 58, 0.3);
        }

        .registration-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 0.5rem;
        }

        .registration-subtitle {
            color: var(--muted-ink);
            font-size: 1rem;
        }

        /* Form Styling */
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .form-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--brand);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-input {
            width: 100%;
            padding: 1rem 1.5rem;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 1rem;
            background: white;
            transition: all 0.3s ease;
            position: relative;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--brand-2);
            box-shadow: 0 0 0 3px rgba(14, 165, 164, 0.1);
        }

        .form-select {
            width: 100%;
            padding: 1rem 1.5rem;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 1rem;
            background: white;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .form-select:focus {
            outline: none;
            border-color: var(--brand-2);
            box-shadow: 0 0 0 3px rgba(14, 165, 164, 0.1);
        }

        .submit-btn {
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
            margin-top: 1rem;
            position: relative;
            z-index: 1;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 61, 58, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* Message Styling */
        .message {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            position: relative;
            z-index: 1;
        }

        .message.error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .message.success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        /* Sign In Link */
        .signin-link {
            text-align: center;
            margin-top: 2rem;
            position: relative;
            z-index: 1;
        }

        .signin-text {
            color: var(--muted-ink);
            font-size: 0.95rem;
        }

        .signin-btn {
            color: var(--brand);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .signin-btn:hover {
            color: var(--brand-2);
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .registration-section {
                padding: 2rem 1rem;
            }

            .registration-card {
                padding: 2rem;
            }

            .registration-title {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 480px) {
            .hero {
                padding: 100px 1rem 1rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .registration-card {
                padding: 1.5rem;
            }

            .form-input,
            .form-select {
                padding: 0.875rem 1.25rem;
            }
        }
    </style>
</head>
<body>


    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1 class="hero-title">
                Join Our <span class="highlight">Delivery Team</span>
            </h1>
            <p class="hero-subtitle">
                Become a delivery partner and help us connect food donors with those in need. Make a difference in your community.
            </p>
        </div>
    </section>

    <!-- Registration Section -->
    <section class="registration-section">
        <div class="registration-card">
            <div class="registration-header">
                <div class="registration-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <h2 class="registration-title">Delivery Partner Registration</h2>
                <p class="registration-subtitle">Join our network of delivery partners</p>
            </div>

            <?php if ($error_message): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="" id="registrationForm">
                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-user"></i> Full Name
                    </label>
                    <input type="text" id="username" name="username" class="form-input" required 
                           placeholder="Enter your full name" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input type="email" id="email" name="email" class="form-input" required 
                           placeholder="Enter your email address" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" id="password" name="password" class="form-input" required 
                           placeholder="Create a strong password" minlength="6">
                </div>

                <div class="form-group">
                    <label for="district" class="form-label">
                        <i class="fas fa-map-marker-alt"></i> Service District
                    </label>
                    <select id="district" name="district" class="form-select" required>
                        <option value="">Select your service district</option>
                        <option value="bagalkot" <?php echo (isset($_POST['district']) && $_POST['district'] == 'bagalkot') ? 'selected' : ''; ?>>Bagalkot</option>
                        <option value="ballari" <?php echo (isset($_POST['district']) && $_POST['district'] == 'ballari') ? 'selected' : ''; ?>>Ballari</option>
                        <option value="belagavi" <?php echo (isset($_POST['district']) && $_POST['district'] == 'belagavi') ? 'selected' : ''; ?>>Belagavi</option>
                        <option value="bengaluru rural" <?php echo (isset($_POST['district']) && $_POST['district'] == 'bengaluru rural') ? 'selected' : ''; ?>>Bengaluru Rural</option>
                        <option value="bengaluru urban" <?php echo (isset($_POST['district']) && $_POST['district'] == 'bengaluru urban') ? 'selected' : ''; ?>>Bengaluru Urban</option>
                        <option value="bidar" <?php echo (isset($_POST['district']) && $_POST['district'] == 'bidar') ? 'selected' : ''; ?>>Bidar</option>
                        <option value="chamarajanagar" <?php echo (isset($_POST['district']) && $_POST['district'] == 'chamarajanagar') ? 'selected' : ''; ?>>Chamarajanagar</option>
                        <option value="chikkaballapur" <?php echo (isset($_POST['district']) && $_POST['district'] == 'chikkaballapur') ? 'selected' : ''; ?> selected>Chikkaballapur</option>
                        <option value="chikkamagaluru" <?php echo (isset($_POST['district']) && $_POST['district'] == 'chikkamagaluru') ? 'selected' : ''; ?>>Chikkamagaluru</option>
                        <option value="chitradurga" <?php echo (isset($_POST['district']) && $_POST['district'] == 'chitradurga') ? 'selected' : ''; ?>>Chitradurga</option>
                        <option value="dakshina kannada" <?php echo (isset($_POST['district']) && $_POST['district'] == 'dakshina kannada') ? 'selected' : ''; ?>>Dakshina Kannada</option>
                        <option value="davanagere" <?php echo (isset($_POST['district']) && $_POST['district'] == 'davanagere') ? 'selected' : ''; ?>>Davanagere</option>
                        <option value="dharwad" <?php echo (isset($_POST['district']) && $_POST['district'] == 'dharwad') ? 'selected' : ''; ?>>Dharwad</option>
                        <option value="gadag" <?php echo (isset($_POST['district']) && $_POST['district'] == 'gadag') ? 'selected' : ''; ?>>Gadag</option>
                        <option value="hassan" <?php echo (isset($_POST['district']) && $_POST['district'] == 'hassan') ? 'selected' : ''; ?>>Hassan</option>
                        <option value="haveri" <?php echo (isset($_POST['district']) && $_POST['district'] == 'haveri') ? 'selected' : ''; ?>>Haveri</option>
                        <option value="kalaburagi" <?php echo (isset($_POST['district']) && $_POST['district'] == 'kalaburagi') ? 'selected' : ''; ?>>Kalaburagi</option>
                        <option value="kodagu" <?php echo (isset($_POST['district']) && $_POST['district'] == 'kodagu') ? 'selected' : ''; ?>>Kodagu</option>
                        <option value="kolar" <?php echo (isset($_POST['district']) && $_POST['district'] == 'kolar') ? 'selected' : ''; ?>>Kolar</option>
                        <option value="koppal" <?php echo (isset($_POST['district']) && $_POST['district'] == 'koppal') ? 'selected' : ''; ?>>Koppal</option>
                        <option value="mandya" <?php echo (isset($_POST['district']) && $_POST['district'] == 'mandya') ? 'selected' : ''; ?>>Mandya</option>
                        <option value="mysuru" <?php echo (isset($_POST['district']) && $_POST['district'] == 'mysuru') ? 'selected' : ''; ?>>Mysuru</option>
                        <option value="raichur" <?php echo (isset($_POST['district']) && $_POST['district'] == 'raichur') ? 'selected' : ''; ?>>Raichur</option>
                        <option value="ramanagara" <?php echo (isset($_POST['district']) && $_POST['district'] == 'ramanagara') ? 'selected' : ''; ?>>Ramanagara</option>
                        <option value="shivamogga" <?php echo (isset($_POST['district']) && $_POST['district'] == 'shivamogga') ? 'selected' : ''; ?>>Shivamogga</option>
                        <option value="tumakuru" <?php echo (isset($_POST['district']) && $_POST['district'] == 'tumakuru') ? 'selected' : ''; ?>>Tumakuru</option>
                        <option value="udupi" <?php echo (isset($_POST['district']) && $_POST['district'] == 'udupi') ? 'selected' : ''; ?>>Udupi</option>
                        <option value="uttara kannada" <?php echo (isset($_POST['district']) && $_POST['district'] == 'uttara kannada') ? 'selected' : ''; ?>>Uttara Kannada</option>
                        <option value="vijayanagara" <?php echo (isset($_POST['district']) && $_POST['district'] == 'vijayanagara') ? 'selected' : ''; ?>>Vijayanagara</option>
                        <option value="vijayapura" <?php echo (isset($_POST['district']) && $_POST['district'] == 'vijayapura') ? 'selected' : ''; ?>>Vijayapura</option>
                        <option value="yadgir" <?php echo (isset($_POST['district']) && $_POST['district'] == 'yadgir') ? 'selected' : ''; ?>>Yadgir</option>
                    </select>
                </div>

                <button type="submit" name="sign" class="submit-btn">
                    <i class="fas fa-user-plus"></i> Register as Delivery Partner
                </button>
            </form>

            <div class="signin-link">
                <p class="signin-text">
                    Already a delivery partner? 
                    <a href="deliverylogin.php" class="signin-btn">Sign in here</a>
                </p>
            </div>
        </div>
    </section>

    <script>

        // Form validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const email = document.getElementById('email').value;
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long.');
                return false;
            }
            
            if (!email.includes('@')) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return false;
            }
        });

        // Add smooth animations
        document.addEventListener('DOMContentLoaded', () => {
            const registrationCard = document.querySelector('.registration-card');
            if (registrationCard) {
                registrationCard.style.opacity = '0';
                registrationCard.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    registrationCard.style.transition = 'all 0.6s ease-out';
                    registrationCard.style.opacity = '1';
                    registrationCard.style.transform = 'translateY(0)';
                }, 300);
            }
        });
    </script>
</body>
</html> 