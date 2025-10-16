<?php
// Add this at the top of exam.php after including header.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug session
echo "Session Data:<br>";
print_r($_SESSION);
echo "<br><br>";

// Debug database connection
try {
    $exam = new Examination();
    $exam->query = "SELECT COUNT(*) as count FROM online_exam_table WHERE admin_id = '".$_SESSION['admin_id']."'";
    $result = $exam->query_result();
    echo "Exam Count: " . $result[0]['count'] . "<br>";
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "<br>";
}
?>