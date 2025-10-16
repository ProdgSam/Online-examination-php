<?php
// user_ajax_action.php

include('master/Examination.php');
require_once('class/class.phpmailer.php');

$exam = new Examination;
$current_datetime = date("Y-m-d") . ' ' . date("H:i:s", STRTOTIME(date('h:i:sa')));

if (isset($_POST['page'])) {

    /* =========================
       USER LOGIN LOGIC
    ========================== */
    if ($_POST['page'] == 'login' && $_POST['action'] == 'login') {
        $output = array();

        $email = trim($_POST['user_email_address']);
        $password = trim($_POST['user_password']);

        // Prepare SQL to check email
        $exam->data = array(':user_email_address' => $email);
        $exam->query = "SELECT * FROM user_table WHERE user_email_address = :user_email_address";
        $result = $exam->query_result();

        if ($exam->total_row() > 0) {
            foreach ($result as $row) {
                if ($row['user_email_verified'] == 'yes') {
                    // ✅ Verify password (use password_hash during registration)
                    if (password_verify($password, $row['user_password'])) {
                        $_SESSION['user_id'] = $row['user_id'];
                        $_SESSION['user_name'] = $row['user_name'];
                        $_SESSION['user_email'] = $row['user_email_address'];
                        $output['success'] = true;
                    } else {
                        $output['error'] = 'Incorrect password. Please try again.';
                    }
                } else {
                    $output['error'] = 'Please verify your email before logging in.';
                }
            }
        } else {
            $output['error'] = 'Invalid email address.';
        }

        echo json_encode($output);
        exit;
    }

    /* =========================
       FETCH EXAM LIST (for DataTable)
    ========================== */
    if ($_POST['page'] == 'enroll_exam' && $_POST['action'] == 'fetch') {
        $output = array();

        $exam->query = "
        SELECT 
            online_exam_table.*,
            CASE 
                WHEN user_exam_enroll_table.exam_id IS NOT NULL THEN 'Enrolled'
                ELSE 'Not Enrolled'
            END as enrollment_status
        FROM online_exam_table 
        LEFT JOIN user_exam_enroll_table 
            ON online_exam_table.online_exam_id = user_exam_enroll_table.exam_id 
            AND user_exam_enroll_table.user_id = '" . $_SESSION['user_id'] . "'
        WHERE 1=1
        ";

        // Search
        if (isset($_POST["search"]["value"])) {
            $exam->query .= '
            AND (
                online_exam_table.online_exam_title LIKE "%' . $_POST["search"]["value"] . '%" 
                OR online_exam_table.online_exam_datetime LIKE "%' . $_POST["search"]["value"] . '%"
            )';
        }

        // Sorting
        if (isset($_POST["order"])) {
            $exam->query .= '
            ORDER BY ' . (1 + $_POST['order']['0']['column']) . ' ' . $_POST['order']['0']['dir'] . ' 
            ';
        } else {
            $exam->query .= ' ORDER BY online_exam_table.online_exam_datetime ASC ';
        }

        $extra_query = '';
        if ($_POST["length"] != -1) {
            $extra_query .= 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
        }

        // Get filtered and total counts
        $filtered_query = $exam->query;
        $exam->query = $filtered_query;
        $filtered_rows = $exam->total_row();

        $exam->query = $filtered_query . $extra_query;
        $result = $exam->query_result();

        $exam->query = "SELECT COUNT(*) as total FROM online_exam_table";
        $total_rows = $exam->total_row();

        $data = array();

        foreach ($result as $row) {
            $sub_array = array();
            $sub_array[] = html_entity_decode($row["online_exam_title"]);
            $sub_array[] = $row["online_exam_datetime"];
            $sub_array[] = $row["online_exam_duration"] . ' Minute';
            $sub_array[] = $row["total_question"] . ' Question';
            $sub_array[] = $row["marks_per_right_answer"] . ' Mark';
            $sub_array[] = '-' . $row["marks_per_wrong_answer"] . ' Mark';
            $sub_array[] = $row["enrollment_status"];
            $sub_array[] = $row["online_exam_status"];
            $sub_array[] = $row["online_exam_code"];
            $sub_array[] = $row["online_exam_id"];

            // Compute exam active status
            $is_active = false;
            $can_take = false;
            if (!empty($row["online_exam_datetime"])) {
                $start_ts = strtotime($row["online_exam_datetime"]);
                $duration_minutes = intval($row["online_exam_duration"]);
                $end_ts = $start_ts + ($duration_minutes * 60);
                $now_ts = time();
                if ($now_ts >= $start_ts && $now_ts <= $end_ts) {
                    $is_active = true;
                }
            }

            if ($row["enrollment_status"] == 'Enrolled' && $is_active) {
                $can_take = true;
            }

            $sub_array[] = $is_active ? '1' : '0';
            $sub_array[] = $can_take ? '1' : '0';
            $data[] = $sub_array;
        }

        $output = array(
            "draw" => intval($_POST["draw"]),
            "recordsTotal" => $total_rows,
            "recordsFiltered" => $filtered_rows,
            "data" => $data
        );

        echo json_encode($output);
        exit;
    }

    /* =========================
       ENROLL IN EXAM
    ========================== */
    if ($_POST['page'] == 'index' && $_POST['action'] == 'enroll_exam') {
        $exam->data = array(
            ':user_id' => $_SESSION['user_id'],
            ':exam_id' => $_POST['exam_id']
        );

        $exam->query = "
        INSERT INTO user_exam_enroll_table (user_id, exam_id) 
        VALUES (:user_id, :exam_id)
        ";
        $exam->execute_query();

        // Insert default question answers for this user
        $exam->query = "
        SELECT question_id FROM question_table 
        WHERE online_exam_id = '" . $_POST['exam_id'] . "'
        ";
        $result = $exam->query_result();

        foreach ($result as $row) {
            $exam->data = array(
                ':user_id' => $_SESSION['user_id'],
                ':exam_id' => $_POST['exam_id'],
                ':question_id' => $row['question_id'],
                ':user_answer_option' => '0',
                ':marks' => '0'
            );

            $exam->query = "
            INSERT INTO user_exam_question_answer 
            (user_id, exam_id, question_id, user_answer_option, marks) 
            VALUES (:user_id, :exam_id, :question_id, :user_answer_option, :marks)
            ";
            $exam->execute_query();
        }

        echo json_encode(['success' => true]);
        exit;
    }
}
?>
