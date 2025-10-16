<?php
//header.php
include('Examination.php');
$exam = new Examination;
$exam->admin_session_private();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard - Online Examination System</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="../style/bootstrap-datetimepicker.css">

    <!-- jQuery & Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <!-- Parsley JS -->
    <script src="https://cdn.jsdelivr.net/gh/guillaumepotier/Parsley.js@2.9.1/dist/parsley.min.js"></script>
    <!-- DateTimePicker -->
    <script src="../style/bootstrap-datetimepicker.js"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
        }
        .sidebar {
            height: 100vh;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar a {
            color: #c2c7d0;
            display: block;
            padding: 10px 20px;
            text-decoration: none;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #495057;
            color: #fff;
        }
        .content {
            margin-left: 220px;
            padding: 20px;
        }
        .card-stat {
            border-radius: 0.5rem;
            transition: 0.3s;
        }
        .card-stat:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        .navbar-dark .navbar-brand {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="d-flex">
        <div class="sidebar position-fixed">
            <h4 class="text-center text-white mb-4">Admin Panel</h4>
            <a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="exam.php"><i class="fas fa-file-alt"></i> Exams</a>
            <a href="user.php"><i class="fas fa-users"></i> Users</a>
            <span class="nav-link disabled text-muted"><i class="fas fa-chart-line"></i> Results</span>
            <span class="nav-link disabled text-muted"><i class="fas fa-cog"></i> Settings</span>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <!-- Page Content -->
        <div class="content flex-grow-1">
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
                <a class="navbar-brand" href="index.php">Online Exam Admin</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#topNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="topNavbar">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <span class="nav-link">Welcome, Admin</span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Dashboard Stats -->
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card bg-primary text-white card-stat p-3">
                        <div class="card-body">
                            <h5>Total Exams</h5>
                            <h2>
                                <?php echo $exam->get_total_exams(); ?>
                            </h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-success text-white card-stat p-3">
                        <div class="card-body">
                            <h5>Total Users</h5>
                            <h2>
                                <?php echo $exam->get_total_users(); ?>
                            </h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-warning text-white card-stat p-3">
                        <div class="card-body">
                            <h5>Active Exams</h5>
                            <h2>
                                <?php echo $exam->get_active_exams(); ?>
                            </h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content starts here -->
