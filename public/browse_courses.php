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
$available_courses = [];
$message = "";

// Handle course request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['course_id'])) {
    $course_id = $_POST['course_id'];

    // Check if the student has already requested this course
    $sql_check = "SELECT id FROM requests WHERE student_id = ? AND course_id = ?";
    if ($stmt_check = $conn->prepare($sql_check)) {
        $stmt_check->bind_param("ii", $student_id, $course_id);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            $message = "<div class='alert alert-danger'>You have already requested or are enrolled in this course.</div>";
        }
        $stmt_check->close();
    }

    if (empty($message)) {
        $sql_insert = "INSERT INTO requests (student_id, course_id, status) VALUES (?, ?, 'pending')";
        if ($stmt_insert = $conn->prepare($sql_insert)) {
            $stmt_insert->bind_param("ii", $student_id, $course_id);
            if ($stmt_insert->execute()) {
                $message = "<div class='alert alert-success'>Request to join course sent successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error sending request: " . $stmt_insert->error . "</div>";
            }
            $stmt_insert->close();
        }
    }
}

// Fetch courses not yet requested or approved by the student
$sql = "SELECT c.id, c.course_name, u.username AS faculty_name
        FROM courses c
        JOIN users u ON c.faculty_id = u.id
        WHERE c.id NOT IN (
            SELECT r.course_id
            FROM requests r
            WHERE r.student_id = ? AND (r.status = 'pending' OR r.status = 'approved')
        )";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $student_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $available_courses[] = $row;
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
    <title>Browse Courses</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <h2>Browse Available Courses</h2>
        <p>Here you can browse courses and send requests to join them.</p>

        <?php echo $message; ?>

        <?php if (empty($available_courses)): ?>
            <p>No new courses available at the moment or you have already requested/enrolled in all of them.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Course Name</th>
                        <th>Faculty</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($available_courses as $course): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($course['faculty_name']); ?></td>
                            <td>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                    <input type="submit" class="btn btn-primary" value="Request to Join">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <p><a href="dashboard.php" class="btn btn-default">Back to Dashboard</a></p>
    </div>
</body>
</html>
