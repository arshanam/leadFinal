<html>
	<head>
		<meta charset="utf-8">
		<title>DashBoard</title>
		<script src="<?php echo static_files_url(); ?>js/sorttable.js" type="text/javascript"></script>
		<script src="<?php echo static_files_url(); ?>js/jquery.simplePagination.js" type="text/javascript"></script>
		<script src="<?php echo static_files_url(); ?>js/jquery.js" type="text/javascript"></script>
		<script src="<?php echo static_files_url(); ?>js/jquery.validate.js" type="text/javascript"></script>
		<link href="<?php echo static_files_url(); ?>css/colorbox.css" rel="stylesheet" type="text/css" />
		<script src="<?php echo static_files_url(); ?>/js/jquery.colorbox.js" type="text/javascript"></script>
		<script>
			$(document).ready(function(){
				$(".iframe1").colorbox({iframe:true, width:"70%", height:"30%", onClosed:function(){ location.reload(true); } });
			});
		</script>
	</head>
	<body>
		<div class="top" style='overflow-y: auto; width:auto;height:400px;'>
		<table class="sortable">
			<tr>
				<th>Serial # </th><th>List Name</th><th>Total Records</th><th>Date Assigned</th><th colspan="2"><center>Operation</center></th>
			</tr>

			<?php $x=1;
			foreach ($interndata as $value) { ?>
			<tr>
				<td>
					<?php echo $x++; ?>
				</td>
				<td>
					<?php echo $value['list_name']; ?>
				</td>
				<td>
					<?php $endval=(int)$value['end_rec'];
							$startval=(int)$value['start_rec'];
							$actval=(($endval-$startval)+1);
							echo $actval; ?>
				</td>
				<td>
					<?php echo $value['date']; ?>
				</td>
				<td>
					<a href='/lead_intern/download/<?php echo $value['intern_id']; ?>/<?php echo $value['list_name']; ?>/' >Download</a>
				</td>
				<td>
					<a class='iframe1' href='/lead_intern/upload_view/<?php echo $value['list_id']; ?>/' >Upload</a>
				</td>
			</tr>
			<?php	} ?>
		</table>
	</div>
	</body>
</html>