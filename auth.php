<?php
// Include your database connection file
// Include 'db_connect.php' for database connection
include('db_connect.php');

// Function to register a new user
function registerUser($conn, $first_name, $last_name, $username, $password, $mobile_no, $dob, $gender, $address) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (first_name, last_name, username, password, mobile_no, dob, gender, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "ssssssss", $first_name, $last_name, $username, $hashed_password, $mobile_no, $dob, $gender, $address);

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Function to authenticate a user
function authenticateUser($conn, $username, $password) {
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "s", $username);

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        return $user;
    } else {
        return false;
    }
}

// Function to check if a username already exists
function checkExistingUser($conn, $username) {
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "s", $username);

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $existing_user = mysqli_fetch_assoc($result);

    if ($existing_user) {
        return true;
    } else {
        return false;
    }
}

// Close the database connection
mysqli_close($conn);

