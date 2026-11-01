<?php
session_start();
include '../connection.php';
$msg = 0;
if (isset($_POST['sign'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $sanitized_emailid = mysqli_real_escape_string($connection, $email);
    $sanitized_password = mysqli_real_escape_string($connection, $password);
    $sql = "SELECT * FROM admin WHERE email='$sanitized_emailid'";
    $result = mysqli_query($connection, $sql);
    if ($result && mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($sanitized_password, $row['password'])) {
            $_SESSION['email'] = $row['email'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['location'] = $row['location'];
            $_SESSION['Aid'] = $row['Aid'];
            header('Location: admin.php');
            exit();
        } else {
            $error_message = "Invalid password. Please try again.";
        }
    } else {
        $error_message = "Account does not exist. Please check your email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sign In - Feed Forward</title>
    <link rel="stylesheet" href="../home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
        }

        .signin-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            margin-top: 80px;
        }

        .signin-card {
            background: white;
            padding: 60px 50px;
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .signin-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #003d3a, #4ade80, #006d68, #003d3a);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .signin-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .signin-title {
            font-size: 2.5em;
            color: #003d3a;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .signin-subtitle {
            color: #4a5568;
            font-size: 1.1em;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: #003d3a;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            color: #1a202c;
            background: white;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #003d3a;
            box-shadow: 0 0 0 3px rgba(0, 61, 58, 0.1);
        }

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #4a5568;
            cursor: pointer;
            font-size: 1.2rem;
        }

        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #003d3a, #006d68);
            color: white;
            border: none;
            padding: 18px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            box-shadow: 0 10px 25px rgba(0,61,58,0.3);
            position: relative;
            overflow: hidden;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0,61,58,0.4);
        }

        .register-link {
            text-align: center;
            margin-top: 30px;
            color: #4a5568;
        }

        .register-link a {
            color: #003d3a;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
            color: #006d68;
        }

        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #fecaca;
        }

        @media (max-width: 767px) {
            .signin-container {
                padding: 10px;
                margin-top: 60px;
            }
            
            .signin-card {
                padding: 40px 25px;
            }
            
            .signin-title {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">Feed <b style="color: #003d3a;">Forward</b></div>
        <div class="hamburger">
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
        </div>
        <nav class="nav-bar">
            <ul>
                <li><a href="../home.html">Home</a></li>
                <li><a href="../about.html">About</a></li>
                <li><a href="../contact.html">Contact</a></li>
                <li><a href="../profile.php">Profile</a></li>
            </ul>
        </nav>
    </header>

    <script>
        const hamburger = document.querySelector(".hamburger");
        hamburger.onclick = function () {
            const navBar = document.querySelector(".nav-bar");
            navBar.classList.toggle("active");
        }
    </script>

    <div class="signin-container">
        <div class="signin-card">
            <div class="signin-header">
                <h1 class="signin-title">Admin Sign In</h1>
                <p class="signin-subtitle">Welcome back! Please sign in to your account</p>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form action="" id="form" method="post">
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="password-container">
                        <input type="password" name="password" id="password" class="form-input" required />
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fa fa-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" name="sign" class="submit-btn">
                    <i class="fa fa-sign-in" style="margin-right: 10px;"></i>
                    Sign In
                </button>

                <div class="register-link">
                    <span>Don't have an account? 
                        <a href="signup.php">Register</a>
                    </span>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.className = 'fa fa-eye';
            } else {
                passwordInput.type = 'password';
                toggleBtn.className = 'fa fa-eye-slash';
            }
        }
    </script>
</body>
</html> 