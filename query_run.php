<?php
// create_table.php

// Include the database connection
include 'db_connect.php';
// Database configuratio
// SQL query to create the "posts" table
$sqlCreatePostsTable = "CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)";

// Execute the query to create "posts" table
if (mysqli_query($conn, $sqlCreatePostsTable)) {
    echo "Table 'posts' created successfully";
} else {
    echo "Error creating table 'posts': " . mysqli_error($conn);
}

// Close the database connection
mysqli_close($conn);


