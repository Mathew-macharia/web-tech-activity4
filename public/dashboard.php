<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
        <p>You are logged in as a <strong><?php echo htmlspecialchars($_SESSION["role"]); ?></strong>.</p>

        <?php if ($_SESSION["role"] === "faculty"): ?>
            <h3>Faculty Actions</h3>
            <ul>
                <li><a href="create_course.php" class="btn btn-primary">Create New Course</a></li>
                <li><a href="manage_requests.php" class="btn btn-primary">Manage Student Requests</a></li>
            </ul>
        <?php elseif ($_SESSION["role"] === "student"): ?>
            <h3>Student Actions</h3>
            <ul>
                <li><a href="browse_courses.php" class="btn btn-primary">Browse Courses</a></li>
                <li><a href="enrolled_courses.php" class="btn btn-primary">View Enrolled Courses</a></li>
            </ul>
        <?php endif; ?>

        <p><a href="logout.php" class="btn btn-default">Logout</a></p>
    </div>
</body>
</html>
