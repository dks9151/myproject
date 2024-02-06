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

// Function to get post details based on post ID
function getPostDetails($conn, $post_id, $username) {
    $getPostSql = "SELECT title, content FROM posts WHERE id = ? AND username = ?";
    $getPostStmt = $conn->prepare($getPostSql);
    $getPostStmt->bind_param("is", $post_id, $username);
    $getPostStmt->execute();
    $postDetailsResult = $getPostStmt->get_result();

    if ($postDetailsResult->num_rows === 1) {
        return $postDetailsResult->fetch_assoc();
    } else {
        return null;
    }
}

// Check if the user is logged in
if (isUserLoggedIn()) {
    $username = $_SESSION['username'];

    // Check if the post ID is provided in the URL
    if (isset($_GET['id'])) {
        $post_id = $_GET['id'];
        $postDetails = getPostDetails($conn, $post_id, $username);

        if ($postDetails) {
            $title = $postDetails['title'];
            $content = $postDetails['content'];
        } else {
            echo 'Error: Post not found or you do not have permission to edit.';
            exit();
        }
    } else {
        echo 'Error: Post ID not provided.';
        exit();
    }
} else {
    echo 'Error: User not logged in.';
    exit();
}

// Function to update a post in the database
function updatePost($conn, $post_id, $title, $content) {
    $updatePostSql = "UPDATE posts SET title = ?, content = ? WHERE id = ?";
    $updatePostStmt = $conn->prepare($updatePostSql);
    $updatePostStmt->bind_param("ssi", $title, $content, $post_id);

    if ($updatePostStmt->execute()) {
        echo "Post updated successfully!";
    } else {
        echo "Error updating post: " . $updatePostStmt->error;
    }

    $updatePostStmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="utility.css">
    <link rel="stylesheet" href="edit_post.css">
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

<main id="edit-post">
    <div class="container">

        <section id="edit-post-section">
            <h2>Edit Post</h2>
            <form action="edit_post.php?id=<?php echo $post_id; ?>" method="post">
                <label for="post_title">Post Title:</label>
                <input type="text" name="post_title" value="<?php echo $title; ?>" required>

                <label for="post_content">Post Content:</label>
                <textarea name="post_content" rows="4" required><?php echo $content; ?></textarea>

                <button type="submit" name="update_post">Save Changes</button>
            </form>
        </section>

        <?php
        // Check if the update form is submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_post'])) {
            $new_title = isset($_POST["post_title"]) ? $_POST["post_title"] : '';
            $new_content = isset($_POST["post_content"]) ? $_POST["post_content"] : '';

            if (!empty($new_title) && !empty($new_content)) {
                updatePost($conn, $post_id, $new_title, $new_content);

                // Redirect to the profile page after updating
                header('Location: profile.php');
                exit();
            } else {
                echo "Title and content cannot be empty.";
            }
        }
        ?>

    </div>
</main>

<footer>
    <!-- Your footer content goes here -->
</footer>

<script src="script.js"></script>
</body>
</html>
