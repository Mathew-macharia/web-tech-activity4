<?php
// Initialize the session
session_start();

// Check if the user is logged in and is a student, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "student") {
    header("location: login.php");
    exit;
}

require_once __DIR__ . '/../includes/config.php';

$student_id = $_SESSION["id"];
$enrolled_courses = [];

// Fetch courses the student is enrolled in
$sql = "SELECT c.course_name, u.username AS faculty_name
        FROM requests r
        JOIN courses c ON r.course_id = c.id
        JOIN users u ON c.faculty_id = u.id
        WHERE r.student_id = ? AND r.status = 'approved'";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $student_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $enrolled_courses[] = $row;
        }
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enrolled Courses</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <h2>Your Enrolled Courses</h2>
        <p>Here is a list of courses you are currently enrolled in.</p>

        <?php if (empty($enrolled_courses)): ?>
            <p>You are not currently enrolled in any courses.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Course Name</th>
                        <th>Faculty</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrolled_courses as $course): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($course['faculty_name']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <p><a href="dashboard.php" class="btn btn-default">Back to Dashboard</a></p>
    </div>
</body>
</html>
