<?php
// index.php
include('header.php'); // header includes Examination class and admin session check
?>

<div class="container-fluid">

    <!-- Dashboard Stats -->
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card bg-primary text-white shadow-sm p-3">
                <div class="card-body">
                    <h5>Total Exams</h5>
                    <h2><?php echo $exam->get_total_exams(); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-success text-white shadow-sm p-3">
                <div class="card-body">
                    <h5>Total Users</h5>
                    <h2><?php echo $exam->get_total_users(); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-warning text-white shadow-sm p-3">
                <div class="card-body">
                    <h5>Active Exams</h5>
                    <h2><?php echo $exam->get_active_exams(); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Exams Table -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Recent Exams</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="exam_data_table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Exam Title</th>
                            <th>Date & Time</th>
                            <th>Duration</th>
                            <th>Total Questions</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $exam->query = "SELECT * FROM online_exam_table ORDER BY online_exam_id DESC LIMIT 5";
                        $exams = $exam->query_result();
                        foreach ($exams as $row) {
                            echo '<tr>
                                <td>'.$row["online_exam_title"].'</td>
                                <td>'.$row["online_exam_datetime"].'</td>
                                <td>'.$row["online_exam_duration"].' min</td>
                                <td>'.$row["total_question"].'</td>
                                <td>'.$row["online_exam_status"].'</td>
                            </tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Users Table -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Recent Users</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="user_data_table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Email</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $exam->query = "SELECT * FROM user_table ORDER BY user_id DESC LIMIT 5";
                        $users = $exam->query_result();
                        foreach ($users as $row) {
                            echo '<tr>
                                <td>'.htmlspecialchars($row["user_name"]).'</td>
                                <td>'.htmlspecialchars($row["user_email_address"]).'</td>
                                <td>'.date('F j, Y', strtotime($row["user_created_on"])).'</td>
                            </tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div> <!-- container-fluid -->

<script>
$(document).ready(function(){
    $('#exam_data_table').DataTable();
    $('#user_data_table').DataTable();
});
</script>

<?php
include('footer.php');
?>
