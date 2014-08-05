<html>
<head>
	<meta charset="utf-8">
	<title>Edit Permissions</title>
	<script src="<?php echo static_files_url(); ?>/js/jquery.js" type="text/javascript"></script>
   <script src="<?php echo static_files_url(); ?>/js/jquery.validate.js" type="text/javascript"></script>

  	<script>
  	$(document).ready(function() {

    // Setup form validation on the #register-form element
    $("#permissionf").validate({

        // Specify the validation rules

        rules: {

            pn: {
            	required: true
            }
         },

        // Specify the validation error messages
        messages: {
				pn: "<br/><br/>Required<BR/>"
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
	$form_attributes=array("name"=>"permissionf","id"=>"permissionf","novalidate"=>"false");
	echo form_open("/lead_superadmin/superadmin_addprocess/permission",$form_attributes);
	?>
		<table>
			<tr>
				<td>Permission Name:</td>
				<td>
					<?php
					$data=array('name'=>'pn','id'=>'pn');
					echo form_input($data);
					?>
				</td>
			</tr>
			<tr>
				<td>
					<a href='/lead_superadmin/backpage/permission'>
						<img src="<?php echo static_files_url(); ?>images/goback.png" height="25" width="50"/>
					</a>
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