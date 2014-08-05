<?php

//here $id is the Deaprtment ID for table Department
		$query = $this->db->query("select * from department where department_id='".$id."'");
		$row = $query->row();
?>
<html>
<head>
	<meta charset="utf-8">
	<title>Edit Department</title>
	<script src="<?php echo static_files_url(); ?>/js/jquery.js" type="text/javascript"></script>
   <script src="<?php echo static_files_url(); ?>/js/jquery.validate.js" type="text/javascript"></script>

  	<script>
  	$(document).ready(function() {

    // Setup form validation on the #register-form element
    $("#editdepartmentf").validate({

        // Specify the validation rules

        rules: {

            deptn: {
            	required: true
            },
            depta: {
            	required: true
            }

        },

        // Specify the validation error messages
        messages: {

            deptn: "<br/><br/>Required<BR/>",
            depta: "<br/><br/>Required<BR/>"
        },

        submitHandler: function(form) {
			//welcome();
            form.submit();
        }
    });

  });

  </script>
</head>
<body>
	<?php
	$form_attributes=array("name"=>"editdepartmentf","id"=>"editdepartmentf","novalidate"=>"false");
	echo form_open("/lead_superadmin/superadmin_editprocess/department",$form_attributes);
	echo form_hidden("d_id",$row->department_id);
	?>
		<table>
			<tr>
				<td>Department Id :</td>
				<td><?php echo $row->department_id;?></td>
			</tr>
			<tr>
				<td>Department Name:</td>
				<td>
					<?php
					$data=array('name'=>'deptn','value'=>$row->department_name);
					echo form_input($data);
					?>
				</td>
			</tr>
			<tr>
				<td>Active Status(1 for Active):</td>
				<td>
					<?php
					$options=array('0'=>'InActive','1'=>'Active');
					$tmpval=$row->department_active;
					if($tmpval=="1")
						echo form_dropdown('depta', $options, '1');
					else if($tmpval=="0")
						echo form_dropdown('depta', $options, '0');	
					?>
				</td>
			</tr>
			<tr>
				<td>
					<a href='/lead_superadmin/backpage/department'>
						<img src="<?php echo static_files_url(); ?>images/goback.png" height="25" width="50"/>
					</a>
				</td>
				<td>
					<?php
					echo form_submit('submit', 'UPDATE');
					?>
				</td>
			</tr>
		</table>
	<?php
	echo form_close();
	?>
</body>
</html>