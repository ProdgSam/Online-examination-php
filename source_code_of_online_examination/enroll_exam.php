<?php

//enroll_exam.php

include('master/Examination.php');

$exam = new Examination;

$exam->user_session_private();

$exam->Change_exam_status($_SESSION['user_id']);

include('header.php');

?>

<br />
<div class="card">
	<div class="card-header">Online Exam List</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered table-striped table-hover" id="exam_data_table">
				<thead>
					<tr>
						<th>Exam Title</th>
						<th>Date & Time</th>
						<th>Duration</th>
						<th>Total Question</th>
						<th>Right Answer Mark</th>
						<th>Wrong Answer Mark</th>
						<th>Status</th>
						<th>Action</th>
					</tr>
				</thead>
			</table>
		</div>
	</div>
</div>
</div>
</body>
</html>

<script>

$(document).ready(function(){

	var dataTable = $('#exam_data_table').DataTable({
		"processing" : true,
		"serverSide" : true,
		"order" : [],
		"ajax" : {
			url:"user_ajax_action.php",
			type:"POST",
			data: function(d){
				// forward DataTables params and include page/action plus any force_take query
				var params = $.extend({}, d, { action: 'fetch', page: 'enroll_exam' });
				// read force_take from page URL
				var urlParams = new URLSearchParams(window.location.search);
				if(urlParams.get('force_take')){
					params.force_take = urlParams.get('force_take');
				}
				return params;
			}
		},
		"columnDefs":[
			{
				"targets":[7],
				"orderable":false,
				"render": function(data, type, row) {
					// Columns returned by server:
					// 0:title,1:datetime,2:duration,3:total_question,4:right_mark,5:wrong_mark,
					// 6:enrollment_status,7:online_exam_status,8:exam_code,9:exam_id,10:is_active,11:can_take
					var html = '';
					var enrollment = row[6];
					var exam_status = row[7];
					var exam_code = row[8];
					var exam_id = row[9];
					var is_active = (row[10] == '1');
					var can_take = (row[11] == '1');

					// Show a visible 'Take Exam' button for enrolled users so they can navigate to the exam page.
					if(enrollment === 'Enrolled') {
						html = '<button type="button" class="btn btn-success btn-sm take_exam" data-exam_code="' + exam_code + '">Take Exam</button>';
					} else {
						html = '<button type="button" class="btn btn-warning btn-sm enroll_exam" data-exam_id="' + exam_id + '">Enroll</button>';
					}

					return html;
				}
			},
		],
	});

	$(document).on('click', '.enroll_exam', function(){
		var exam_id = $(this).data('exam_id');
		var $btn = $(this);
		$btn.prop('disabled', true).text('Enrolling...');
		$.ajax({
			url: 'user_ajax_action.php',
			type: 'POST',
			data: {action: 'enroll_exam', page: 'index', exam_id: exam_id},
			success: function(){
				dataTable.ajax.reload(null, false);
			},
			error: function(){
				$btn.prop('disabled', false).text('Enroll');
				alert('Failed to enroll. Please try again.');
			}
		});
	});

	$(document).on('click', '.take_exam', function(){
		var exam_code = $(this).data('exam_code');
		window.location.href = 'view_exam.php?code=' + exam_code;
	});

});

</script>