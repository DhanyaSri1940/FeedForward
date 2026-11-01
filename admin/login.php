<?php
include '../connection.php';
$acc = 0;
$msg = 0;
if (isset($_POST['signup'])) {
    $username = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $location = $_POST['district'] ?? '';
    $pass = password_hash($password, PASSWORD_DEFAULT);
    $sql = "SELECT * FROM admin WHERE email = '" . mysqli_real_escape_string($connection, $email) . "'";
    $result = mysqli_query($connection, $sql);
    $num = mysqli_num_rows($result);
    if ($num == 1) {
        $acc = 1;
    } else {
        $query = "INSERT INTO admin(name, email, password, location) VALUES ('" . mysqli_real_escape_string($connection, $username) . "', '" . mysqli_real_escape_string($connection, $email) . "', '$pass', '" . mysqli_real_escape_string($connection, $location) . "')";
        $query_run = mysqli_query($connection, $query);
        if ($query_run) {
            $msg = 1;
        } else {
            echo '<script type="text/javascript">alert("Data not saved")</script>';
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
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="login.css">
    <title>Admin Login & Registration</title>
    <style>
        .text.signup-link, .logo b {
            color: #003d3a !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="forms">
            <div class="form login">
                <?php if ($msg == 1): ?>
                    <p><center style="color:#003d3a;">Account created successfully</center></p>
                <?php endif; ?>
                <?php if ($acc == 1): ?>
                    <p><center style="color:crimson;">Account already exists</center></p>
                <?php endif; ?>
                <span class="title">Login</span>
                <form action="" method="post">
                    <div class="input-field">
                        <input type="text" placeholder="Enter your email" name="email" required>
                        <i class="uil uil-envelope icon"></i>
                    </div>
                    <div class="input-field">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <i class="uil uil-lock icon"></i>
                        <i class="uil uil-eye-slash showHidePw"></i>
                    </div>
                    <div class="input-field button">
                        <button type="submit" name="Login">Login</button>
                    </div>
                </form>
                <div class="login-signup">
                    <span class="text">Not a member?
                        <a href="#" class="text signup-link">Signup Now</a>
                    </span>
                </div>
            </div>
            <div class="form signup">
                <?php if ($acc == 1): ?>
                    <p><center style="color:crimson;">Account already exists</center></p>
                <?php endif; ?>
                <span class="title">Registration</span>
                <form action="" method="post">
                    <div class="input-field">
                        <input type="text" placeholder="Enter your name" name="name" required>
                        <i class="uil uil-user"></i>
                    </div>
                    <div class="input-field">
                        <input type="text" placeholder="Enter your email" name="email" required>
                        <i class="uil uil-envelope icon"></i>
                    </div>
                    <div class="input-field">
                        <select id="district" name="district" style="padding:10px; padding-left: 20px;">
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
                        <i class="uil uil-map-marker icon"></i>
                    </div>
                    <div class="input-field">
                        <input type="password" id="password" name="password" placeholder="Confirm a password" required>
                        <i class="uil uil-lock icon"></i>
                        <i class="uil uil-eye-slash showHidePw"></i>
                    </div>
                    <div class="input-field button">
                        <button type="submit" name="signup">Signup</button>
                    </div>
                </form>
                <div class="login-signup">
                    <span class="text">Already a member?
                        <a href="#" class="text login-link">Login Now</a>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <script src="login.js"></script>
</body>
</html>
<?php
if (isset($_POST['Login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $sanitized_emailid = mysqli_real_escape_string($connection, $email);
    $sanitized_password = mysqli_real_escape_string($connection, $password);
    $sql = "SELECT * FROM admin WHERE email = '$sanitized_emailid'";
    $result = mysqli_query($connection, $sql);
    if ($result && mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($sanitized_password, $row['password'])) {
            $_SESSION['email'] = $row['email'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['location'] = $row['location'];
            header('Location: admin.php');
            exit();
        } else {
            $msg = 1;
            // Optionally, set an error message for incorrect password
        }
    } else {
        echo '<h1><center>Account does not exist</center></h1>';
    }
}
?> 