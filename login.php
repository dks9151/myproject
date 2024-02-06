<?php
session_start();
include 'db_connect.php';

// Function to check if the user is logged in
function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] !== null;
}

// Function to log out the user
function logoutUser() {
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    session_destroy();
}

// Check if the logout action is triggered
if (isset($_GET['logout'])) {
    logoutUser();
    header("Location: index.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    // Retrieve the username and password from the form
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Prepare and execute a SQL query to check login credentials
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // User found, check hashed password
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Successful login, store user information in session
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];

            // Redirect to the dashboard or any other page
            header("Location: index.php");
            exit();
        }
    }

    // Invalid login, display an error message
    $error_message = "Invalid username or password";

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="utility.css">  
    <link rel="stylesheet" href="login.css">
</head>
<body>
<header>
    <div class="container">
        <nav class="navbar">
            <div class="logo">
                <img src="img/logo.png" alt="Your Blog Logo">
            </div>
            <ul class="nav-list">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="#" class="nav-link">About</a></li>
                <li><a href="register.php" class="nav-link">Register</a></li>
            </ul>
        </nav>
    </div>
</header>
<div class="container">
    <h1 class="title">Login</h1>
    <form action="login.php" method="post">
        <label for="uname"><b>Username</b></label>
        <input type="text" placeholder="Enter Username" id="uname" name="username" required>

        <label for="psw"><b>Password</b></label>
        <input type="password" placeholder="Enter Password" id="psw" name="password" required>

        <button type="submit" name="login">Login</button>

        <?php
        // Display error message if login fails
        if (isset($error_message)) {
            echo '<p style="color: red;">' . $error_message . '</p>';
        }
        ?>
    </form>
</div>
</body>
</html>
