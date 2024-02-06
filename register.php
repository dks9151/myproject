<?php
session_start();

// Check if the user is already logged in, redirect to home if true
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Database connection details
$host = "localhost";
$username = "root";
$password = "";
$database = "royaldk";

// Create a database connection
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input data (you may need more validation based on your requirements)
    $username = htmlspecialchars($_POST["username"]);
    $email = htmlspecialchars($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT); // Hash the password for security

    // Save the user registration data to the database
    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        // Registration successful, redirect to the login page
        header("Location: login.php");
        exit();
    } else {
        // Registration failed, handle the error (you may want to display an error message)
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="utility.css">
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
                <li class="nav-login"><a href="login.php" class="nav-link">Login</a></li>
            </ul>
        </nav>
    </div>
</header>

<div class="register-container">
    <h2>Register</h2>
    <form class="register" action="register.php" method="post">
        <label for="username">Username:</label>
        <input type="text" name="username" required>

        <label for="email">Email:</label>
        <input type="email" name="email" required>

        <label for="password">Password:</label>
        <input type="password" name="password" required>

        <button type="submit">Register</button>
    </form>
</div>
</body>
</html>
