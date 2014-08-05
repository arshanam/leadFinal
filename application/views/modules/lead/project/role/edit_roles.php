<?php
	//here $id is the Role ID for table Roles
	$q1 = $this->db->query("select * from roles where role_id='".$id."'");
	$row1 = $q1->row();
	$q2="select * from role_permissions where role_id='".$row1->role_id."'";
	$res2=$this->db->query($q2);
	$row2=$res2->result_array();
	$p_str=$row2[0]['permission_id'];
	$p_arr=explode(",",$p_str);
?>
<html>
<head>
	<meta charset="utf-8">
	<title>Edit Roles</title>
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
    $("#editrolef").validate({

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
	$form_attributes=array("name"=>"editrolef","id"=>"editrolef","novalidate"=>"false");
	echo form_open("/lead_superadmin/superadmin_editprocess/role",$form_attributes);
	echo form_hidden("r_id",$row1->role_id);
	?>
		<table>
			<tr>
				<td>Role Id :</td>
				<td><?php echo $row1->role_id;?></td>
			</tr>
			<tr>
				<td>Role Name:</td>
				<td>
					<?php
					$data=array('name'=>'rn','value'=>$row1->role_name);
					echo form_input($data);
					?>
				</td>
			</tr>
			<tr>
				<td>Role Priority:</td>
				<td>
					<?php
					$data=array('name'=>'rp','id'=>'rp','value'=>$row1->role_priority);
					echo form_input($data);
					?>
					<div class="err" id="div1" name="div1"></div>
				</td>
			</tr>
			<tr>
				<td>Permissions Selection : </td>
				<td>
					<?php
					$q3="select * from permissions";
					$res3=$this->db->query($q3);
					$row3=$res3->result_array();
					foreach($row3 as $val)
					{
						
						$flag=0;
						foreach($p_arr as $pid)
						{
							if($pid==$val['permission_id'])
							{
								$flag=1;
							}
						}
						//Checkbox
						if($flag==1)
						{
							$data1=array('name' =>'ps[]','value'=>$val['permission_id'],'checked'=>TRUE);
							echo form_checkbox($data1);
						}
						else
						{
							$data2=array('name' =>'ps[]','value'=>$val['permission_id']);
							echo form_checkbox($data2);
						}
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
					$tmpval=$row1->role_active;
					if($tmpval=="1")
						echo form_dropdown('ra', $options, '1');
					else if($tmpval=="0")
						echo form_dropdown('ra', $options, '0');	
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