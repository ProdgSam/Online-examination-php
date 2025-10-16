<?php
// Include required class and initialize
include('master/Examination.php');
$exam = new Examination;

$exam_id = null;

// Identify which exam is being accessed
if (isset($_GET['id'])) {
    $exam_id = $_GET['id'];
} elseif (isset($_GET['exam_id'])) {
    $exam_id = $_GET['exam_id'];
} elseif (isset($_GET['code'])) {
    // Lookup exam_id using exam code
    $exam->query = "
        SELECT online_exam_id 
        FROM online_exam_table 
        WHERE online_exam_code = '" . $_GET['code'] . "' 
        LIMIT 1
    ";
    $result = $exam->query_result();
    if (!empty($result)) {
        $exam_id = $result[0]['online_exam_id'];
    }
}

// Initialize exam status and time details
$exam_status = '';
$exam_datetime = '';
$exam_duration = 0;
$current_time = time();
$message = '';
$message_type = 'warning';
$can_take_exam = false;

if ($exam_id) {
    // Get exam details including datetime and duration
    $exam->query = "
        SELECT online_exam_datetime, online_exam_duration, online_exam_status
        FROM online_exam_table 
        WHERE online_exam_id = '$exam_id'
    ";
    $result = $exam->query_result();
    if (!empty($result)) {
        $exam_status = $result[0]['online_exam_status'];
        $exam_datetime = strtotime($result[0]['online_exam_datetime']);
        $exam_duration = intval($result[0]['online_exam_duration']) * 60; // Convert minutes to seconds
        
        // Get dates for comparison
        $exam_date = date('Y-m-d', $exam_datetime);
        $current_date = date('Y-m-d', $current_time);
        
        // If same day, allow exam anytime during the day
        if ($exam_date === $current_date) {
            $message = "Exam is available today! You can start anytime.";
            $message_type = 'success';
            $can_take_exam = true;
        }
        // If future date
        elseif ($exam_date > $current_date) {
            $days_until = floor(($exam_datetime - $current_time) / (60 * 60 * 24));
            if ($days_until == 1) {
                $message = "This exam starts tomorrow on " . date('F j, Y', $exam_datetime);
            } else {
                $message = "This exam starts in $days_until days on " . date('F j, Y', $exam_datetime);
            }
            $message_type = 'info';
        }
        // If past date
        else {
            $message = "This exam was scheduled for " . date('F j, Y', $exam_datetime);
            $message_type = 'danger';
        }
    }
}

// Handle different exam states
if ($exam_status == 'Completed') {

    // Fetch question and user answer data
    $exam->query = "
        SELECT * FROM question_table 
        INNER JOIN user_exam_question_answer 
        ON user_exam_question_answer.question_id = question_table.question_id 
        WHERE question_table.online_exam_id = '$exam_id' 
        AND user_exam_question_answer.user_id = '" . $_SESSION["user_id"] . "'
    ";
    $result = $exam->query_result();
    ?>

    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-8">Online Exam Result</div>
                <div class="col-md-4 text-end">
                    <a href="pdf_exam_result.php?code=<?php echo htmlspecialchars($_GET["code"]); ?>" 
                       class="btn btn-danger btn-sm" target="_blank">PDF</a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <tr>
                        <th>Question</th>
                        <th>Option 1</th>
                        <th>Option 2</th>
                        <th>Option 3</th>
                        <th>Option 4</th>
                        <th>Your Answer</th>
                        <th>Correct Answer</th>
                        <th>Result</th>
                        <th>Marks</th>
                    </tr>

                    <?php
                    $total_mark = 0;

                    foreach ($result as $row) {
                        // Get all options for this question
                        $exam->query = "
                            SELECT * FROM option_table 
                            WHERE question_id = '" . $row["question_id"] . "'
                        ";
                        $sub_result = $exam->query_result();

                        $user_answer = '';
                        $original_answer = '';
                        $question_result = '';

                        // Determine result label
                        if ($row['marks'] == '0') {
                            $question_result = '<h4 class="badge bg-secondary">Not Attempted</h4>';
                        } elseif ($row['marks'] > '0') {
                            $question_result = '<h4 class="badge bg-success">Correct</h4>';
                        } elseif ($row['marks'] < '0') {
                            $question_result = '<h4 class="badge bg-danger">Wrong</h4>';
                        }

                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['question_title']) . '</td>';

                        // Display all options
                        foreach ($sub_result as $sub_row) {
                            echo '<td>' . htmlspecialchars($sub_row["option_title"]) . '</td>';

                            if ($sub_row["option_number"] == $row['user_answer_option']) {
                                $user_answer = $sub_row['option_title'];
                            }

                            if ($sub_row['option_number'] == $row['answer_option']) {
                                $original_answer = $sub_row['option_title'];
                            }
                        }

                        echo '<td>' . htmlspecialchars($user_answer) . '</td>';
                        echo '<td>' . htmlspecialchars($original_answer) . '</td>';
                        echo '<td>' . $question_result . '</td>';
                        echo '<td>' . htmlspecialchars($row["marks"]) . '</td>';
                        echo '</tr>';
                    }

                    // Calculate total marks
                    $exam->query = "
                        SELECT SUM(marks) as total_mark 
                        FROM user_exam_question_answer 
                        WHERE user_id = '" . $_SESSION['user_id'] . "' 
                        AND exam_id = '" . $exam_id . "'
                    ";
                    $marks_result = $exam->query_result();

                    foreach ($marks_result as $row) {
                        echo '
                        <tr>
                            <td colspan="8" align="right"><b>Total Marks</b></td>
                            <td align="right"><b>' . htmlspecialchars($row["total_mark"]) . '</b></td>
                        </tr>';
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>

<?php
} elseif ($exam_status == 'Started') {
    // If exam is ongoing
    echo '
    <div class="card">
        <div class="card-header">Exam In Progress</div>
        <div class="card-body">
            The exam is currently active. Please complete it before checking the result.
        </div>
    </div>';
} else {
    // Show exam availability status
    ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg border-0">
                    <div class="card-body text-center p-5">
                        <div class="display-1 text-<?php echo $message_type; ?> mb-4">
                            <?php if ($can_take_exam): ?>
                                <i class="fas fa-play-circle"></i>
                            <?php elseif ($message_type == 'info'): ?>
                                <i class="fas fa-clock"></i>
                            <?php else: ?>
                                <i class="fas fa-calendar-times"></i>
                            <?php endif; ?>
                        </div>
                        
                        <h2 class="card-title mb-4 text-primary">
                            <?php echo $can_take_exam ? 'Exam Ready to Start' : 'Exam Not Available'; ?>
                        </h2>
                        
                        <div class="alert alert-<?php echo $message_type; ?> py-3 px-4 mb-4 mx-auto" style="max-width: 80%;">
                            <p class="lead mb-2"><?php echo htmlspecialchars($message); ?></p>
                            <p class="mb-0">
                                Scheduled Time: <br>
                                <strong><?php echo date('F j, Y, g:i a', $exam_datetime); ?></strong>
                                <br>
                                Duration: <strong><?php echo floor($exam_duration / 60); ?> minutes</strong>
                            </p>
                        </div>
                        
                        <?php if ($can_take_exam): ?>
                        <div class="d-grid gap-2 col-8 mx-auto mb-3">
                            <button onclick="startExam()" class="btn btn-success btn-lg shadow">
                                <i class="fas fa-play-circle me-2"></i> Start Exam Now
                            </button>
                        </div>
                        <?php endif; ?>
                        
                        <div class="d-grid gap-2 col-6 mx-auto">
                            <a href="enroll_exam.php" class="btn <?php echo $can_take_exam ? 'btn-outline-primary' : 'btn-primary'; ?> btn-lg shadow">
                                <i class="fas fa-arrow-left me-2"></i> Return to Exam List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Exam Info Modal (always shown) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <div class="modal fade" id="examModal" tabindex="-1" aria-labelledby="examModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-<?php echo $message_type; ?> text-white">
            <h5 class="modal-title" id="examModalLabel">
              <i class="fas fa-info-circle me-2"></i> Exam Information
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center">
            <p class="lead mb-2"><?php echo htmlspecialchars($message); ?></p>
            <p class="mb-0">
              Scheduled Date: <strong><?php echo date('F j, Y', $exam_datetime); ?></strong><br>
              Duration: <strong><?php echo floor($exam_duration / 60); ?> minutes</strong>
            </p>
          </div>
          <div class="modal-footer justify-content-center">
            <?php if ($can_take_exam): ?>
            <button type="button" class="btn btn-success btn-lg" onclick="startExam()">
              <i class="fas fa-play-circle me-2"></i> Take Exam
            </button>
            <?php else: ?>
            <button type="button" class="btn btn-secondary btn-lg" disabled title="You cannot take this exam now">
              <i class="fas fa-ban me-2"></i> Take Exam
            </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var examModal = new bootstrap.Modal(document.getElementById('examModal'));
        examModal.show();
      });
      function startExam() {
        window.location.href = 'take_exam.php?code=<?php echo htmlspecialchars($_GET["code"]); ?>';
      }
    </script>
    <?php
}
?>
