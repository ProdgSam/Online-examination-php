<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required class
include_once('master/Examination.php');
$exam = new Examination;

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$exam_id = $input['exam_id'] ?? '';
$answers = $input['answers'] ?? [];

// Validate input
if (empty($exam_id) || empty($answers)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

try {
    // Get correct answers from database
    $exam->query = "
        SELECT question_id, answer_option 
        FROM question_table 
        WHERE online_exam_id = '$exam_id'
    ";
    $correct_answers = $exam->query_result();
    
    // Calculate results
    $total_questions = count($correct_answers);
    $correct_count = 0;
    $total_marks = 0;
    
    foreach ($correct_answers as $answer) {
        $question_id = $answer['question_id'];
        $user_answer = $answers["question_$question_id"] ?? null;
        
        if ($user_answer == $answer['answer_option']) {
            $correct_count++;
            $marks = 1; // Add positive mark for correct answer
        } else {
            $marks = 0; // No negative marking
        }
        
        // Save user's answer
        $exam->query = "
            INSERT INTO user_exam_question_answer 
            (user_id, exam_id, question_id, user_answer_option, marks) 
            VALUES (
                '" . $_SESSION['user_id'] . "',
                '$exam_id',
                '$question_id',
                '" . ($user_answer ?? '') . "',
                '$marks'
            )
        ";
        $exam->execute_query();
        
        $total_marks += $marks;
    }
    
    // Calculate percentage
    $percentage = ($total_marks / $total_questions) * 100;
    
    // Update exam status
    $exam->query = "
        UPDATE online_exam_table 
        SET online_exam_status = 'Completed'
        WHERE online_exam_id = '$exam_id'
    ";
    $exam->execute_query();
    
    // Prepare results
    $results = [
        'total' => $total_questions,
        'correct' => $correct_count,
        'marks' => $total_marks,
        'percentage' => round($percentage, 2)
    ];
    
    echo json_encode([
        'success' => true,
        'results' => $results
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error processing exam: ' . $e->getMessage()
    ]);
}
?>