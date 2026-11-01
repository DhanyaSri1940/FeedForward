<?php
session_start();
include 'connection.php';

if (isset($_POST['sign'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Sanitize input
    $sanitized_email = mysqli_real_escape_string($connection, $email);
    $sanitized_password = mysqli_real_escape_string($connection, $password);

    $sql = "SELECT * FROM login WHERE email = '$sanitized_email'";
    $result = mysqli_query($connection, $sql);

    if ($result && mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($sanitized_password, $row['password'])) {
            $_SESSION['email'] = $row['email'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['gender'] = $row['gender'];
            header("Location: home.php");
            exit();
        } else {
            echo "<h1><center>Login Failed: Incorrect password</center></h1>";
        }
    } else {
        echo "<h1><center>Account does not exist</center></h1>";
    }
}
?> 