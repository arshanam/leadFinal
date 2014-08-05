<html>
<head>
	<meta charset="utf-8">
	<title>Add Roles</title>
	<script src="<?php echo static_files_url(); ?>/js/jquery.js" type="text/javascript"></script>
   <script src="<?php echo static_files_url(); ?>/js/jquery.validate.js" type="text/javascript"></script>
  	<script>
	//role priority check
	$(document).ready(function(){
			$("#rp").blur(function(){
				a=$("#rp").val();
				$.ajax({url:"/lead_superadmin/ajax_rolepriority_check/",type:"post",data:"rpriority="+a,success:function(result)
					{
						if(result=="")
						{
							$("#submit").removeAttr('disabled','disabled');
						}
						else
						{
							$("#submit").attr('disabled','disabled');
						}
						$("#div1").html(result);
					}
				});
			});
		});
	
  	$(document).ready(function() {

    // Setup form validation on the #register-form element
    $("#rolef").validate({

        // Specify the validation rules

        rules: {
            rn: {
            	required: true
            },
            rp: {
            	required: true
            },
            ra: {
            	required: true
            }

        },

        // Specify the validation error messages
        messages: {
            rn: "<br/><br/>Required<BR/>",
            rp: "<br/><br/>Required<BR/>",
            ra: "<br/><br/>Required<BR/>"
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
	$form_attributes=array("name"=>"rolef","id"=>"rolef","novalidate"=>"false");
	echo form_open("/lead_superadmin/superadmin_addprocess/role",$form_attributes);
	?>
		<table>
			<tr>
				<td>Role Name:</td>
				<td>
					<?php
					$data=array('name'=>'rn');
					echo form_input($data);
					?>
				</td>
			</tr>
			<tr>
				<td>Role Priority:</td>
				<td>
					<?php
					$data=array('name'=>'rp','id'=>'rp');
					echo form_input($data);
					?>
					<div class="err" id="div1" name="div1"></div>
				</td>
			</tr>
			<tr>
				<td>Permissions Selection : </td>
				<td>
					<?php
					$q1="select * from permissions";
					$res1=$this->db->query($q1);
					$row1=$res1->result_array();
					foreach($row1 as $val)
					{
						//Checkbox
						echo form_checkbox("ps[]", $val['permission_id']);
						//Label
						echo $val['module_name'];
						echo "<br/>";
					}
					?>
				</td>
			</tr>
			<tr>
				<td>Active Status(1 for Active):</td>
				<td>
					<?php
					$options=array('0'=>'InActive','1'=>'Active');
					echo form_dropdown('ra', $options, '1');
					?>
				</td>
			</tr>
			<tr>
				<td>
					<a href='/lead_superadmin/backpage/role'>
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