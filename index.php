<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Blog Title</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="utility.css">
    <style>
    
    </style>
</head>

<body>

    <?php
    session_start();

    // Function to check if the user is logged in
    function isUserLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    // Function to log out the user
    function logoutUser()
    {
        unset($_SESSION['user_id']);
        session_destroy();
    }

    // Check if the logout action is triggered
    if (isset($_GET['logout'])) {
        logoutUser();
        header("Location: index.php"); // Redirect to the home page after logout
        exit();
    }
    ?>

    <header>
        <div class="container">
            <nav class="navbar">
                <div class="logo">
                    <img src="img/logo.png" alt="Your Blog Logo">
                </div>
                <span class="menu-icon">&#9776;</span>
                <ul class="nav-list">
                    <li><a href="#" class="nav-link">Home</a></li>
                    <li><a href="#" class="nav-link">About</a></li>
                    <li><a href="#" class="nav-link">Contact Us</a></li>
                    <li class="search-bar">
                        <form id="search-bar" action="#" method="post">
                            <input type="text" name="search" id="searchInput" placeholder="Search..." value="<?php if (isset($_POST['search_btn'])) {
                                                                                                                echo htmlspecialchars($_POST['search']);
                                                                                                            } ?>">
                            <span class="close-btn" onclick="clearSearch()">&times;</span>
                            <button type="submit" name="search_btn">Search</button>
                        </form>
                    </li>

                    <?php
                    // Dynamically display "Login" or "Profile" button based on login status
                    if (isUserLoggedIn()) {
                        echo '<li class="nav-login"><a href="profile.php" class="nav-link">Profile</a></li>';
                    } else {
                        echo '<li class="nav-login"><a href="login.php" class="nav-link">Login</a></li>';
                    }
                    ?>

                </ul>
            </nav>
        </div>
    </header>

    <main id="blog-posts">
        <?php
        // Fetch and display all blog posts with titles, content, and usernames
        include('db_connect.php'); // Include your database connection file

        if (isset($_POST['search_btn'])) {
            // Handle search functionality
            $searchTerm = mysqli_real_escape_string($conn, $_POST['search']);

            $searchQuery = "SELECT title, content, username FROM posts WHERE title LIKE '%$searchTerm%' OR content LIKE '%$searchTerm%'";

            $searchResult = mysqli_query($conn, $searchQuery);

            if (!$searchResult) {
                die("Search query failed: " . mysqli_error($conn));
            }

            echo '<div id="search-results">';
            while ($searchRow = mysqli_fetch_assoc($searchResult)) {
                echo '<article>';
                echo '<h2>' . htmlspecialchars($searchRow['title']) . '</h2>';
                echo '<p>' . htmlspecialchars($searchRow['content']) . '</p>';
                echo '<p>Posted by: ' . htmlspecialchars($searchRow['username']) . '</p>';
                echo '</article>';
            }
            echo '</div>';
        } else {
            // Display all blog posts
            $query = "SELECT posts.title, posts.content, posts.username FROM posts";

            $result = mysqli_query($conn, $query);

            if (!$result) {
                die("Query failed: " . mysqli_error($conn));
            }

            echo '<div id="blog-posts">';
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<article>';
                echo '<h2>' . htmlspecialchars($row['title']) . '</h2>';
                echo '<p>' . htmlspecialchars($row['content']) . '</p>';
                echo '<p>Posted by: ' . htmlspecialchars($row['username']) . '</p>';
                echo '</article>';
            }
            echo '</div>';
        }

        // Close the database connection
        mysqli_close($conn);
        ?>
    </main>

    <footer class="text-center p-2">
        <p class="foot">&copy; <?php echo date('Y'); ?> Your Blog Name. All rights reserved.</p>
    </footer>

    <script src="script.js"></script>
    <script>
        document.querySelector('.menu-icon').addEventListener('click', function () {
            document.querySelector('.nav-list').classList.toggle('show-menu');
        });

        // Function to clear the search input
        function clearSearch() {
            document.getElementById('searchInput').value = '';
            document.querySelector('.close-btn').style.display = 'none';
        }

        // Show close button when there is text in the search input
        document.getElementById('searchInput').addEventListener('input', function () {
            var closeBtn = document.querySelector('.close-btn');
            closeBtn.style.display = this.value ? 'block' : 'none';
        });
    </script>
</body>

</html>
