<html>
<head>
	<meta charset="utf-8">
	<title>Add Department</title>
	<script src="<?php echo static_files_url(); ?>/js/jquery.js" type="text/javascript"></script>
	<script src="<?php echo static_files_url(); ?>/js/jquery.validate.js" type="text/javascript"></script>
  	<script>
  	$(document).ready(function() {

    // Setup form validation on the #register-form element
    $("#departmentf").validate({

        // Specify the validation rules

        rules: {
            deptid: {
            	required: true
            },
            deptn: {
            	required: true
            },
            depta: {
            	required: true
            }

        },

        // Specify the validation error messages
        messages: {
            deptid: "<br/><br/>Required<BR/>",
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
	$form_attributes=array("name"=>"departmentf","id"=>"departmentf","novalidate"=>"false");
	echo form_open("/lead_superadmin/superadmin_addprocess/department",$form_attributes);
	?>
		<table>
			<tr>
				<td>Department Name :</td>
				<td>
					<?php
					$data=array('name'=>'deptn','id'=>'deptn');
					echo form_input($data);
					?>
				</td>
			</tr>
			<tr>
				<td>Active Status(1 for Active) :</td>
				<td>
					<?php
					$options=array('0'=>'InActive','1'=>'Active');
					echo form_dropdown('depta', $options, '1');
					?>
				</td>
			</tr>
			<tr>
				<td>
					<a href='/lead_superadmin/backpage/department'><img src="<?php echo static_files_url(); ?>images/goback.png" height="25" width="50"/></a>
				</td>
				<td>
					<?php
					echo form_submit('submit', 'Add');
					?>
				</td>
			</tr>
		</table>
	<?php
	echo form_close();
	?>
</body>
</html>