<?php
include '../connection.php';
$msg = 0;
if (isset($_POST['sign'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $location = $_POST['district'] ?? '';
    $address = $_POST['address'] ?? '';
    $pass = password_hash($password, PASSWORD_DEFAULT);
    $sql = "SELECT * FROM admin WHERE email = '" . mysqli_real_escape_string($connection, $email) . "'";
    $result = mysqli_query($connection, $sql);
    $num = mysqli_num_rows($result);
    if ($num == 1) {
        $error_message = "Account already exists";
    } else {
        $query = "INSERT INTO admin(name, email, password, location, address) VALUES ('" . mysqli_real_escape_string($connection, $username) . "', '" . mysqli_real_escape_string($connection, $email) . "', '$pass', '" . mysqli_real_escape_string($connection, $location) . "', '" . mysqli_real_escape_string($connection, $address) . "')";
        $query_run = mysqli_query($connection, $query);
        if ($query_run) {
            header('Location: signin.php');
            exit();
        } else {
            $error_message = "Data not saved";
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
    <title>Admin Register - Feed Forward</title>
    <link rel="stylesheet" href="../home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
        }

        .signup-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            margin-top: 80px;
        }

        .signup-card {
            background: white;
            padding: 60px 50px;
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .signup-card::before {
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

        .signup-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .signup-title {
            font-size: 2.5em;
            color: #003d3a;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .signup-subtitle {
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

        .form-textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            color: #1a202c;
            background: white;
            transition: all 0.3s ease;
            resize: vertical;
            min-height: 100px;
        }

        .form-textarea:focus {
            outline: none;
            border-color: #003d3a;
            box-shadow: 0 0 0 3px rgba(0, 61, 58, 0.1);
        }

        .form-select {
            width: 100%;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            color: #1a202c;
            background: white;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .form-select:focus {
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

        .login-link {
            text-align: center;
            margin-top: 30px;
            color: #4a5568;
        }

        .login-link a {
            color: #003d3a;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
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
            .signup-container {
                padding: 10px;
                margin-top: 60px;
            }
            
            .signup-card {
                padding: 40px 25px;
            }
            
            .signup-title {
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

    <div class="signup-container">
        <div class="signup-card">
            <div class="signup-header">
                <h1 class="signup-title">Admin Registration</h1>
                <p class="signup-subtitle">Join our team to help manage food donations</p>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form action="" method="post" id="form">
                <div class="form-group">
                    <label class="form-label" for="username">Full Name</label>
                    <input type="text" id="username" name="username" class="form-input" required />
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" required />
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

                <div class="form-group">
                    <label class="form-label" for="address">Address</label>
                    <textarea id="address" name="address" class="form-textarea" required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="district">District</label>
                    <select id="district" name="district" class="form-select">
                        <option value="bagalkot">Bagalkot</option>
                        <option value="ballari">Ballari</option>
                        <option value="belagavi">Belagavi</option>
                        <option value="bengaluru rural">Bengaluru Rural</option>
                        <option value="bengaluru urban">Bengaluru Urban</option>
                        <option value="bidar">Bidar</option>
                        <option value="chamarajanagar">Chamarajanagar</option>
                        <option value="chikkaballapur" selected>Chikkaballapur</option>
                        <option value="chikkamagaluru">Chikkamagaluru</option>
                        <option value="chitradurga">Chitradurga</option>
                        <option value="dakshina kannada">Dakshina Kannada</option>
                        <option value="davanagere">Davanagere</option>
                        <option value="dharwad">Dharwad</option>
                        <option value="gadag">Gadag</option>
                        <option value="hassan">Hassan</option>
                        <option value="haveri">Haveri</option>
                        <option value="kalaburagi">Kalaburagi</option>
                        <option value="kodagu">Kodagu</option>
                        <option value="kolar">Kolar</option>
                        <option value="koppal">Koppal</option>
                        <option value="mandya">Mandya</option>
                        <option value="mysuru">Mysuru</option>
                        <option value="raichur">Raichur</option>
                        <option value="ramanagara">Ramanagara</option>
                        <option value="shivamogga">Shivamogga</option>
                        <option value="tumakuru">Tumakuru</option>
                        <option value="udupi">Udupi</option>
                        <option value="uttara kannada">Uttara Kannada</option>
                        <option value="vijayanagara">Vijayanagara</option>
                        <option value="vijayapura">Vijayapura</option>
                        <option value="yadgir">Yadgir</option>
                    </select>
                </div>

                <button type="submit" name="sign" class="submit-btn">
                    <i class="fa fa-user-plus" style="margin-right: 10px;"></i>
                    Register as Admin
                </button>

                <div class="login-link">
                    <span>Already have an account? 
                        <a href="signin.php">Sign In</a>
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