<?php
// Initialize the session
session_start();

// Check if the user is logged in and is a faculty, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "faculty") {
    header("location: login.php");
    exit;
}

require_once __DIR__ . '/../includes/config.php';

$faculty_id = $_SESSION["id"];
$requests = [];

// Handle request actions (approve/reject)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id']) && isset($_POST['action'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['action']; // 'approve' or 'reject'

    if ($action === 'approve' || $action === 'reject') {
        $sql = "UPDATE requests SET status = ? WHERE id = ? AND course_id IN (SELECT id FROM courses WHERE faculty_id = ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sii", $action, $request_id, $faculty_id);
            if ($stmt->execute()) {
                // Success, reload page to reflect changes
                header("location: manage_requests.php");
                exit;
            } else {
                echo "Error updating request: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch requests for courses managed by the logged-in faculty
$sql = "SELECT r.id AS request_id, u.username AS student_name, c.course_name AS course_name, r.status
        FROM requests r
        JOIN users u ON r.student_id = u.id
        JOIN courses c ON r.course_id = c.id
        WHERE c.faculty_id = ? AND r.status = 'pending'"; // Only show pending requests

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $faculty_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
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
    <title>Manage Student Requests</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <h2>Manage Student Requests</h2>
        <p>Here you can view and manage student requests to join your courses.</p>

        <?php if (empty($requests)): ?>
            <p>No pending student requests at the moment.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Course Name</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($request['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($request['status']); ?></td>
                            <td>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display:inline;">
                                    <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <input type="submit" class="btn btn-primary" value="Approve">
                                </form>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display:inline;">
                                    <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <input type="submit" class="btn btn-default" value="Reject">
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
