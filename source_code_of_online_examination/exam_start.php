<?php
include_once 'header.php';
include_once('master/Examination.php');
$exam = new Examination;

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 1;

// Fetch unique questions for this exam
$exam->query = "
    SELECT DISTINCT question_id, question_title 
    FROM question_table 
    WHERE online_exam_id = $exam_id
    ORDER BY question_id ASC
";
$questions = $exam->query_result();
?>

<div class="container mt-5">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Online Examination</h3>
            <button type="button" id="shuffleBtn" class="btn btn-warning">
                Shuffle Questions
            </button>
        </div>
        <div class="card-body">
            <form action="exam_result.php" method="POST" id="examForm">
                <input type="hidden" name="exam_id" value="<?php echo htmlspecialchars($exam_id); ?>">

                <div id="questionsContainer">
                    <?php
                    foreach ($questions as $q) {
                        echo '<div class="mb-4 p-3 border rounded bg-light question-card" data-qid="'.$q['question_id'].'">';
                        echo '<h5>' . htmlspecialchars($q['question_title']) . '</h5>';

                        // Fetch options
                        $exam->query = "
                            SELECT DISTINCT option_number, option_title 
                            FROM option_table 
                            WHERE question_id = " . intval($q['question_id']) . "
                            ORDER BY option_number ASC
                        ";
                        $options = $exam->query_result();

                        foreach ($options as $opt) {
                            echo '
                            <div class="form-check">
                                <input class="form-check-input" type="radio" 
                                       name="question_' . $q['question_id'] . '" 
                                       id="q' . $q['question_id'] . '_opt' . $opt['option_number'] . '" 
                                       value="' . $opt['option_number'] . '" required>
                                <label class="form-check-label" for="q' . $q['question_id'] . '_opt' . $opt['option_number'] . '">
                                    ' . htmlspecialchars($opt['option_title']) . '
                                </label>
                            </div>';
                        }

                        echo '</div>';
                    }
                    ?>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-success px-4 py-2">
                        Submit Exam
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
body { background: #f7f9fb; }
.card { border-radius: 10px; }
.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const shuffleBtn = document.getElementById('shuffleBtn');
    const container = document.getElementById('questionsContainer');

    if (shuffleBtn && container) {
        shuffleBtn.addEventListener('click', function() {
            const questions = Array.from(container.children);

            // Fisher-Yates shuffle
            for (let i = questions.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [questions[i], questions[j]] = [questions[j], questions[i]];
            }

            container.innerHTML = '';
            questions.forEach(q => container.appendChild(q));

            // Update numbering
            container.querySelectorAll('.question-card').forEach((qCard, index) => {
                const h5 = qCard.querySelector('h5');
                const text = h5.textContent.replace(/^\d+\.\s*/, ''); // remove old number
                h5.textContent = (index + 1) + '. ' + text;
            });
        });
    }
});
</script>
