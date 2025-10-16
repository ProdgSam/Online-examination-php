
<?php
//index.php
include('master/Examination.php');
$exam = new Examination;
include('header.php');
?>

<div class="container mt-5">
	<?php if(isset($_SESSION["user_id"])) { ?>
	<div class="row justify-content-center">
		<div class="col-md-6">
			<div class="card shadow-sm">
				<div class="card-body text-center">
					<h4 class="mb-4">Take Exam</h4>
					<select name="exam_list" id="exam_list" class="form-control input-lg mb-3">
						<option value="">Select Exam</option>
						<?php echo $exam->Fill_exam_list(); ?>
					</select>
					<span id="exam_details"></span>
				</div>
			</div>
		</div>
	</div>

	<div class="row justify-content-center mt-4">
		<div class="col-md-8">
			<div class="card shadow-sm">
				<div class="card-body text-center">
					<h2 class="mb-3 text-primary">Welcome to the Online Examination System</h2>
					<p class="lead">This application allows students to register, enroll, and take online exams securely and efficiently. Results are instantly available with analytics and print options. Teachers can create exams, manage questions, and monitor student performance.</p>
					<hr>
					<p class="mb-0">To get started, select an exam above and begin your test!</p>
				</div>
			</div>
		</div>
	</div>
	<!-- logo/jumbotron moved here -->
	<div class="row justify-content-center mt-4">
		<div class="col-md-12 text-center">
			<div class="jumbotron" style="padding: 1rem 1rem; background: transparent; border: none;">
				<img src="master/logo.png" class="img-fluid" width="300" alt="Online Examination System in PHP" />
			</div>
		</div>
	</div>
	<script>
	$(document).ready(function(){
		$('#exam_list').parsley();
		var exam_id = '';
		$('#exam_list').change(function(){
			$('#exam_list').attr('required', 'required');
			if($('#exam_list').parsley().validate()) {
				exam_id = $('#exam_list').val();
				$.ajax({
					url:"user_ajax_action.php",
					method:"POST",
					data:{action:'fetch_exam', page:'index', exam_id:exam_id},
					success:function(data) {
						$('#exam_details').html(data);
					}
				});
			}
		});
		$(document).on('click', '#enroll_button', function(){
			exam_id = $('#enroll_button').data('exam_id');
			$.ajax({
				url:"user_ajax_action.php",
				method:"POST",
				data:{action:'enroll_exam', page:'index', exam_id:exam_id},
				beforeSend:function() {
					$('#enroll_button').attr('disabled', 'disabled');
					$('#enroll_button').text('please wait');
				},
				success:function() {
					$('#enroll_button').attr('disabled', false);
					$('#enroll_button').removeClass('btn-warning');
					$('#enroll_button').addClass('btn-success');
					$('#enroll_button').text('Enroll success');
				}
			});
		});
	});
	</script>
	<?php } else { ?>
	<div class="row justify-content-center">
		<div class="col-md-6 text-center">
			<a href="register.php" class="btn btn-warning btn-lg me-2">Register</a>
			<a href="login.php" class="btn btn-dark btn-lg">Login</a>
		</div>
	</div>
	<?php } ?>
</div>
</body>
</html>