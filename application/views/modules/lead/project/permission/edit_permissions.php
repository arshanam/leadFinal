<?php

//here $id is the Permission ID for table Permissions
		$query = $this->db->query("select * from permissions where permission_id='".$id."'");
		$row = $query->row();
?>
<html>
<head>
	<meta charset="utf-8">
	<title>Edit Permissions</title>
	<script src="<?php echo static_files_url(); ?>/js/jquery.js" type="text/javascript"></script>

   <script src="<?php echo static_files_url(); ?>/js/jquery.validate.js" type="text/javascript"></script>

  	<script>
  	$(document).ready(function() {

    // Setup form validation on the #register-form element
    $("#editpermissionf").validate({

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
	$form_attributes=array("name"=>"editpermissionf","id"=>"editpermissionf","novalidate"=>"false");
	echo form_open("/lead_superadmin/superadmin_editprocess/permission",$form_attributes);
	echo form_hidden("p_id",$row->permission_id);
	?>
		<table>
			<tr>
				<td>Permission Id :</td>
				<td><?php echo $row->permission_id; ?></td>
			</tr>
			<tr>
				<td>Permission Name:</td>
				<td>
					<?php
					$data=array('name'=>'pn','id'=>'pn','value'=>$row->module_name);
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