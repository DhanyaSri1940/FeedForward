<?php
session_start();
include '../connection.php';
$msg = 0;
$error_message = '';

if (isset($_POST['sign'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $sanitized_emailid = mysqli_real_escape_string($connection, $email);
    $sanitized_password = mysqli_real_escape_string($connection, $password);
    
    $sql = "SELECT * FROM delivery_persons WHERE email='$sanitized_emailid'";
    $result = mysqli_query($connection, $sql);
    
    if ($result && mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($sanitized_password, $row['password'])) {
            $_SESSION['email'] = $row['email'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['Did'] = $row['Did'];
            $_SESSION['city'] = $row['city'];
            header('Location: delivery.php');
            exit();
        } else {
            $error_message = "Invalid password. Please try again.";
        }
    } else {
        $error_message = "Account does not exist. Please check your email or sign up.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Partner Login - Feed Forward</title>
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

        /* Login Section */
        .login-section {
            padding: 4rem 2rem;
            max-width: 450px;
            margin: 0 auto;
        }

        .login-card {
            background: var(--surface);
            border-radius: 25px;
            padding: 3rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }
        .login-card::before {
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

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }

        .login-icon {
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

        .login-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
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

        /* Sign Up Link */
        .signup-link {
            text-align: center;
            margin-top: 2rem;
            position: relative;
            z-index: 1;
        }

        .signup-text {
            color: var(--muted-ink);
            font-size: 0.95rem;
        }

        .signup-btn-link {
            color: var(--brand);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .signup-btn-link:hover {
            color: var(--brand-2);
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .login-section {
                padding: 2rem 1rem;
            }

            .login-card {
                padding: 2rem;
            }

            .login-title {
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

            .login-card {
                padding: 1.5rem;
            }

            .form-input {
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
                Welcome Back <span class="highlight">Delivery Partner</span>
            </h1>
            <p class="hero-subtitle">
                Access your delivery dashboard and continue making a difference in your community by connecting food donors with those in need.
            </p>
        </div>
    </section>

    <!-- Login Section -->
    <section class="login-section">
        <div class="login-card">
            <div class="login-header">
                <div class="login-icon">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
                <h2 class="login-title">Delivery Partner Login</h2>
                <p class="login-subtitle">Sign in to your account</p>
            </div>

            <?php if ($error_message): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="" id="loginForm">
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
                           placeholder="Enter your password">
                </div>

                <button type="submit" name="sign" class="submit-btn">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <div class="signup-link">
                <p class="signup-text">
                    Not a delivery partner yet? 
                    <a href="deliverysignup.php" class="signup-btn-link">Sign up here</a>
                </p>
            </div>
        </div>
    </section>

    <script>

        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email.includes('@')) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return false;
            }
            
            if (password.length < 1) {
                e.preventDefault();
                alert('Please enter your password.');
                return false;
            }
        });

        // Add smooth animations
        document.addEventListener('DOMContentLoaded', () => {
            const loginCard = document.querySelector('.login-card');
            if (loginCard) {
                loginCard.style.opacity = '0';
                loginCard.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    loginCard.style.transition = 'all 0.6s ease-out';
                    loginCard.style.opacity = '1';
                    loginCard.style.transform = 'translateY(0)';
                }, 300);
            }
        });
    </script>
</body>
</html> 