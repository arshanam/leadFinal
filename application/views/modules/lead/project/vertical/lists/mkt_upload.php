<html>
<head>
	<meta charset="utf-8">
	<title>Upload List</title>
</head>
<body>
	<?php
	echo $error;
	$form_attributes=array("name"=>"uploadf","id"=>"uploadf");
	$path="/lead_marketresearch/upload_itz/".$list_id;
	echo form_open_multipart($path,$form_attributes);
	?>
		<table>
			<tr>
				<td>
					<?php
					$attributes=array('name'=>'userfile');
					echo form_upload($attributes);
					?>
				</td>
				<td>
					<?php
					echo form_submit('submit', 'Upload Your File');
					?>
				</td>
			</tr>
		</table>
	<?php
	echo form_close();
	?>
</body>
</html>