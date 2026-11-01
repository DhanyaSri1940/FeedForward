<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'connection.php';

if (isset($_POST['sign'])) {
    $username = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $gender = $_POST['gender'] ?? '';

    // Sanitize and validate email
    $sanitized_email = filter_var($email, FILTER_SANITIZE_EMAIL);
    if (!filter_var($sanitized_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email address";
    } else {
    // Check if account exists using prepared statement
    $stmt = $connection->prepare('SELECT * FROM login WHERE email = ?');
    $stmt->bind_param('s', $sanitized_email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows === 1) {
            $error_message = "Account already exists";
    } else {
        $pass = password_hash($password, PASSWORD_DEFAULT);
        $stmt_insert = $connection->prepare('INSERT INTO login (name, email, password, gender) VALUES (?, ?, ?, ?)');
        $stmt_insert->bind_param('ssss', $username, $sanitized_email, $pass, $gender);
        if ($stmt_insert->execute()) {
                header('Location: home.php');
            exit();
        } else {
                $error_message = "Data not saved";
        }
        $stmt_insert->close();
    }
    $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Feed Forward</title>
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
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            color: var(--ink);
            background:
                radial-gradient(1200px 600px at 0% -10%, rgba(14, 165, 164, 0.20), transparent 60%),
                radial-gradient(1000px 500px at 100% 110%, rgba(0, 61, 58, 0.15), transparent 60%),
                linear-gradient(180deg, #f6fbfb, #eef6f5);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .decor {
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }
        .decor::before,
        .decor::after {
            content: "";
            position: absolute;
            width: 380px;
            height: 380px;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.55;
            animation: float 12s ease-in-out infinite;
        }
        .decor::before {
            top: -60px;
            left: -60px;
            background: radial-gradient(circle at 30% 30%, rgba(14,165,164,.45), rgba(0,61,58,.25));
        }
        .decor::after {
            bottom: -40px;
            right: -40px;
            background: radial-gradient(circle at 70% 70%, rgba(0,61,58,.35), rgba(14,165,164,.25));
            animation-delay: 2s;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0); }
            50% { transform: translateY(-10px) translateX(6px); }
        }

        .form-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 480px;
            background: var(--surface);
            border-radius: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .form-header {
            text-align: center;
            padding: 40px 40px 20px;
            border-bottom: 1px solid var(--border);
        }

        .logo {
            font-size: 32px;
            font-weight: 800;
            margin: 0 0 8px 0;
            color: var(--ink);
        }
        .brand-name {
            color: var(--brand);
            background: linear-gradient(90deg, var(--brand), var(--brand-2));
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--ink);
            margin: 0;
        }

        .form-body {
            padding: 32px 40px 40px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 16px;
            font-family: inherit;
            color: var(--ink);
            background: var(--surface);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--brand-2);
            box-shadow: 0 0 0 3px rgba(14, 165, 164, 0.1);
        }
        
        /* Ensure no default values or autocomplete styling */
        .form-input:-webkit-autofill,
        .form-input:-webkit-autofill:hover,
        .form-input:-webkit-autofill:focus,
        .form-input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px var(--surface) inset !important;
            -webkit-text-fill-color: var(--ink) !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        .password-group {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--muted-ink);
            cursor: pointer;
            font-size: 18px;
            padding: 4px;
            border-radius: 4px;
            transition: color 0.2s ease;
        }
        .password-toggle:hover {
            color: var(--brand);
        }

        .radio-group {
            display: flex;
            gap: 24px;
            margin-top: 8px;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .radio-option input[type="radio"] {
            width: 18px;
            height: 18px;
            accent-color: var(--brand);
            cursor: pointer;
        }

        .radio-option label {
            font-size: 16px;
            color: var(--ink);
            cursor: pointer;
            user-select: none;
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-top: 8px;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        .submit-btn:active {
            transform: translateY(0);
        }

        .form-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
        }

        .form-footer p {
            margin: 0;
            font-size: 16px;
            color: var(--muted-ink);
        }

        .form-footer a {
            color: var(--brand);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }
        .form-footer a:hover {
            color: var(--brand-2);
            text-decoration: underline;
        }

        .error-message {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            border: 1px solid #f87171;
            color: #dc2626;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .error-message::before {
            content: "⚠️";
            font-size: 16px;
        }

        @media (max-width: 600px) {
            body {
                padding: 16px;
            }
            .form-container {
                border-radius: 16px;
            }
            .form-header {
                padding: 32px 24px 20px;
            }
            .form-body {
                padding: 24px 24px 32px;
            }
            .logo {
                font-size: 28px;
            }
            .form-title {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="decor"></div>
    <div class="form-container">
        <div class="form-header">
            <h1 class="logo">Feed <span class="brand-name">Forward</span></h1>
            <h2 class="form-title">Create your account</h2>
        </div>
        
        <form action="" method="post" autocomplete="off" class="form-body">
            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label class="form-label" for="name">Username</label>
                <input type="text" id="name" name="name" class="form-input" autocomplete="off" value="" required />
            </div>
            
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input" autocomplete="off" value="" required />
                </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="password-group">
                    <input type="password" name="password" id="password" class="form-input" autocomplete="new-password" value="" required />
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye-slash" id="password-icon"></i>
                    </button>
                </div>
                </div>
            
            <div class="form-group">
                <label class="form-label">Gender</label>
                <div class="radio-group">
                    <div class="radio-option">
                    <input type="radio" name="gender" id="male" value="male" required />
                    <label for="male">Male</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" name="gender" id="female" value="female" />
                    <label for="female">Female</label>
                </div>
                </div>
                </div>
            
            <button type="submit" name="sign" class="submit-btn">Sign Up</button>
            </form>
        
        <div class="form-footer">
            <p>Already have an account? <a href="signin.php">Sign in</a></p>
        </div>
    </div>

    <script>
        // Clear all input fields on page load
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-input');
            inputs.forEach(input => {
                input.value = '';
                input.setAttribute('autocomplete', 'off');
            });
        });
        
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.className = 'fas fa-eye';
            } else {
                passwordInput.type = 'password';
                passwordIcon.className = 'fas fa-eye-slash';
            }
        }
    </script>
</body>
</html> 