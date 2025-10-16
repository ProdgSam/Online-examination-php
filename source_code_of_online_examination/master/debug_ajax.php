<?php
include('Examination.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$exam = new Examination();

// Debug session
echo "Session Data:";
print_r($_SESSION);
echo "\n\n";

// Debug database connection
if (isset($_SESSION['admin_id'])) {
    try {
        $exam->query = "SELECT COUNT(*) as count FROM online_exam_table WHERE admin_id = '".$_SESSION['admin_id']."'";
        $result = $exam->query_result();
        echo "Total exams for admin: " . $result[0]['count'] . "\n";

        // Get sample exam data
        $exam->query = "SELECT * FROM online_exam_table WHERE admin_id = '".$_SESSION['admin_id']."' LIMIT 1";
        $result = $exam->query_result();
        echo "\nSample exam data:\n";
        print_r($result);
    } catch (Exception $e) {
        echo "Database Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "No admin_id in session\n";
}
?>