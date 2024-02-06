<?php
session_start();

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'royaldk';

$conn = mysqli_connect($host, $username, $password, $database);

// Function to check if the user is logged in
function isUserLoggedIn() {
    return isset($_SESSION['username']) && $_SESSION['username'] !== null;
}

// Function to insert a user into the database
function insertUser($conn, $username) {
    $sql = "INSERT INTO users (username) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);

    if ($stmt->execute()) {
        echo "User inserted successfully!";
    } else {
        echo "Error inserting user: " . $stmt->error;
    }

    $stmt->close();
}

// Function to insert a post into the database
function insertPost($conn, $username, $title, $content) {
    // Check if the username exists in the users table
    $checkUserSql = "SELECT * FROM users WHERE username = ?";
    $checkUserStmt = $conn->prepare($checkUserSql);
    $checkUserStmt->bind_param("s", $username);
    $checkUserStmt->execute();
    $checkUserResult = $checkUserStmt->get_result();

    if ($checkUserResult->num_rows === 1) {
        // User exists, insert the post
        $insertPostSql = "INSERT INTO posts (username, title, content) VALUES (?, ?, ?)";
        $insertPostStmt = $conn->prepare($insertPostSql);
        $insertPostStmt->bind_param("sss", $username, $title, $content);

        if ($insertPostStmt->execute()) {
            echo "Post inserted successfully!";
        } else {
            echo "Error inserting post: " . $insertPostStmt->error;
        }

        $insertPostStmt->close();
    } else {
        // User does not exist, handle accordingly
        echo "Error: User with username '$username' does not exist.";
    }

    $checkUserStmt->close();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = isset($_POST["post_title"]) ? $_POST["post_title"] : '';
    $content = isset($_POST["post_content"]) ? $_POST["post_content"] : '';

    if (!empty($title) && !empty($content)) {
        $username = isUserLoggedIn() ? $_SESSION['username'] : null;

        if ($username) {
            // Check if the user exists, and insert if not
            $checkUserSql = "SELECT * FROM users WHERE username = ?";
            $checkUserStmt = $conn->prepare($checkUserSql);
            $checkUserStmt->bind_param("s", $username);
            $checkUserStmt->execute();
            $checkUserResult = $checkUserStmt->get_result();

            if ($checkUserResult->num_rows === 0) {
                // User does not exist, insert the user
                insertUser($conn, $username);
            }

            // Now you can safely insert the post
            insertPost($conn, $username, $title, $content);

            // Redirect to the same page to prevent re-submission on refresh
            header('Location: profile.php');
            exit(); // Stop script execution
        } else {
            echo "User not logged in.";
        }
    } else {
        echo "Title and content cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="utility.css">
    <link rel="stylesheet" href="profile.css">
</head>
<body>

<?php
// Function to log out the user
function logoutUser() {
    unset($_SESSION['user_id']);
    session_destroy();
}

// Check if the logout action is triggered
if (isset($_GET['logout'])) {
    logoutUser();
    header("Location: index.php");
    exit();
}

// Get the username from the session
$username = isUserLoggedIn() ? $_SESSION['username'] : 'Guest';
?>

<header>
    <div class="container">
        <nav class="navbar">
            <div class="logo">
                <img src="img/logo.png" alt="Your Blog Logo">
            </div>
            <ul class="nav-list">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="#" class="nav-link">About</a></li>
                <li><a href="#" class="nav-link">Contact Us</a></li>
                <li class="search-bar">
                    <form action="#" method="post">
                        <input type="text" name="search" placeholder="Search...">
                        <button type="submit">Search</button>
                    </form>
                </li>

                <?php
                if (isUserLoggedIn()) {
                    echo '<li class="nav-login"><a href="profile.php" class="nav-link">' . $username . '</a></li>';
                    echo '<li class="nav-login"><a href="?logout=true" class="nav-link">Logout</a></li>';
                } else {
                    echo '<li class="nav-login"><a href="login.php" class="nav-link">Login</a></li>';
                }
                ?>
            </ul>
        </nav>
    </div>
</header>

<main id="user-profile">
    <div class="container">

        <section id="create-post-section">
            <h2>Create Post</h2>
            <form action="profile.php" method="post">
                <label for="post_title">Post Title:</label>
                <input type="text" name="post_title" required>
                
                <label for="post_content">Post Content:</label>
                <textarea name="post_content" rows="4" required></textarea>
                
                <button type="submit">Create Post</button>
            </form>
        </section>

        <section id="all-post-section">
            <h2>All Posts</h2>

            <?php
            // Check if the user is logged in
            if (isUserLoggedIn()) {
                $user_id = $_SESSION['username'];

                // Fetch posts for the logged-in user from the database
                $userPostsSql = "SELECT id, title, content FROM posts WHERE username = ?";
                $userPostsStmt = $conn->prepare($userPostsSql);
                $userPostsStmt->bind_param("s", $user_id);
                $userPostsStmt->execute();
                $userPostsResult = $userPostsStmt->get_result();

                // Check if there are any posts for the user
                if ($userPostsResult->num_rows > 0) {
                    while ($row = $userPostsResult->fetch_assoc()) {
                        echo '<div class="post-item">';
                        echo '<h2>' . $row['title'] . '</h2>';
                        echo '<p>' . $row['content'] . '</p>';
                        echo '<div class="post-actions">';
                        echo '<a href="edit_post.php?id=' . $row['id'] . '" class="edit-btn">Edit</a>';
                        echo '<form method="post" action="profile.php" style="display:inline;">';
                        echo '<input type="hidden" name="delete_id" value="' . $row['id'] . '">';
                        echo '<button type="submit" class="delete-btn" onclick="return confirm(\'Are you sure you want to delete this post?\')">Delete</button>';
                        echo '</form>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No posts available for this user.</p>';
                }

                $userPostsStmt->close();
            } else {
                echo '<p>User not logged in.</p>';
            }
            ?>

        </section>

        <?php
        // Check if the delete form is submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
            $delete_id = $_POST['delete_id'];
            // Perform the delete operation using $delete_id
            $deletePostSql = "DELETE FROM posts WHERE id = ?";
            $deletePostStmt = $conn->prepare($deletePostSql);
            $deletePostStmt->bind_param("i", $delete_id);
            $deletePostStmt->execute();

            // Redirect to the same page after delete
            header('Location: profile.php');
            exit();
        }
        ?>

    </div>
</main>

<footer class="text-center p-2">
    <p>&copy; <?php echo date('Y'); ?> Your Blog Name. All rights reserved.</p>
</footer>

<script src="script.js"></script>
</body>
</html>
