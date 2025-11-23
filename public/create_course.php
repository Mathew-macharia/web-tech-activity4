<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "faculty") {
    header("location: login.php");
    exit;
}

require_once __DIR__ . '/../includes/config.php';

$course_name = "";
$course_name_err = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate course name
    if (empty(trim($_POST["course_name"]))) {
        $course_name_err = "Please enter a course name.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM courses WHERE course_name = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_course_name);
            $param_course_name = trim($_POST["course_name"]);

            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $course_name_err = "This course name already exists.";
                } else {
                    $course_name = trim($_POST["course_name"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }

    // Check input errors before inserting in database
    if (empty($course_name_err)) {
        $sql = "INSERT INTO courses (course_name, faculty_id) VALUES (?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $param_course_name, $param_faculty_id);
            $param_course_name = $course_name;
            $param_faculty_id = $_SESSION["id"]; // Faculty ID from session

            if ($stmt->execute()) {
                $success_message = "Course created successfully!";
                $course_name = ""; // Clear the form field
            } else {
                echo "Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Course</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <h2>Create New Course</h2>
        <p>Please fill in this form to create a new course.</p>

        <?php
        if (!empty($success_message)) {
            echo '<div class="alert alert-success">' . $success_message . '</div>';
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($course_name_err)) ? 'has-error' : ''; ?>">
                <label>Course Name</label>
                <input type="text" name="course_name" class="form-control" value="<?php echo $course_name; ?>">
                <span class="help-block"><?php echo $course_name_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Create Course">
            </div>
            <p><a href="dashboard.php" class="btn btn-default">Back to Dashboard</a></p>
        </form>
    </div>
</body>
</html>
