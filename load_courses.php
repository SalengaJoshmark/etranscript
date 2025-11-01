<?php
include("db_connect.php");

if (!isset($_GET['department_id']) || !is_numeric($_GET['department_id'])) {
    echo json_encode([]);
    exit;
}

$department_id = intval($_GET['department_id']);
$result = $conn->query("SELECT course_id, course_name FROM course WHERE department_id = $department_id ORDER BY course_name ASC");

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

echo json_encode($courses);
?>
