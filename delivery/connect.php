<?php
session_start();
include '../connection.php';
$msg = 0;

if (isset($_POST['sign'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $sanitized_email = mysqli_real_escape_string($connection, $email);
    $sanitized_password = mysqli_real_escape_string($connection, $password);

    $sql = "SELECT * FROM admin WHERE email = '$sanitized_email'";
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
            $msg = 1;
            // Optionally, set an error message for incorrect password
        }
    } else {
        echo '<h1><center>Account does not exist</center></h1>';
    }
}
?> 