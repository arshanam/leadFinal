<?php
session_start();
//fetching all department names
$q1="select department_name from department";
$res1=$this->db->query($q1);
$rowz=$res1->result_array();
$j=0;
for($i=0;$i<($res1->num_rows());$i++)
{
	$arrval1[$j++]=$rowz[$i]['department_name'];
}

//fetching all role names
$q2="select role_name from roles";
$res2=$this->db->query($q2);
$row2=$res2->result_array();
$j=0;
for($i=0;$i<($res2->num_rows());$i++)
{
	$arrval2[$j++]=$row2[$i]['role_name'];
}
//here $id is the UID for table USERS
		$q1="select * from users where user_id='".$id."'";
		$res1=$this->db->query($q1);
		$row=$res1->result_array();
		if($row[0]['department_id']==0)
		{
			$row[0]['department_name']='Not Applicable';
		}
		if($row[0]['role_id']==0)
		{
			$row[0]['role_name']='Not Applicable';
		}
		if($row[0]['department_id']!=0)
		{
			$q2="select department_name from department where department_id='".$row[0]['department_id']."'";
			$res2=$this->db->query($q2);
			$row2=$res2->result_array();
			$row[0]['department_name']=$row2[0]['department_name'];
		}
		if($row[0]['role_id']!=0)
		{
			$q3="select role_name from roles where role_id='".$row[0]['role_id']."'";
			$res3=$this->db->query($q3);
			$row3=$res3->result_array();
			$row[0]['role_name']=$row3[0]['role_name'];
		}
?>
<html>
<head>
	<meta charset="utf-8">
	<title>Edit Users</title>
	<script src="<?php echo static_files_url(); ?>/js/jquery.js" type="text/javascript"></script>
    <script src="<?php echo static_files_url(); ?>/js/jquery.validate.js" type="text/javascript"></script>

    <script>
	//dropdown of tl name for assigning tl to interns
		function tldropdown_role(rname,iid)
		{
			dname=$("#dn").val();
			if((rname=='intern' || rname=='INTERN') && dname!="")
			{
				$.ajax({url:"/lead_superadmin/edit_tl_to_intern/",type:"post",data:"deptname="+dname+"& internid="+iid,success:function(result)
					{
						$("#tldrop").html(result);
					}
				});
			}
			$("#tldrop").html("");
		}
		function tldropdown_dept(dname,iid)
		{
			rname=$("#rn").val();
			if((rname=='intern' || rname=='INTERN') && dname!="")
			{
				$.ajax({url:"/lead_superadmin/edit_tl_to_intern/",type:"post",data:"deptname="+dname+"& internid="+iid,success:function(result)
					{
						$("#tldrop").html(result);
					}
				});
			}
			$("#tldrop").html("");
		}
		
		//names checking
		$(document).ready(function(){
			$("#frm").submit(function() {
				var reg1 = new RegExp(/^[a-zA-Z]+[a-zA-Z0-9_]$/);
				var reg2 = new RegExp(/^[a-zA-Z]+[a-zA-Z]$/);
				//alert("hello");
				u=$("#un").val();
				f=$("#fn").val();
				l=$("#ln").val();
				if (!(u.match(reg1))) {
					$("#divun").html("Initial must be alphabet & Alphanumerics allowed");
					return false;
				}
				
				if (!(f.match(reg2))) {
					$("#divfn").html("Enter alphabets only");
					return false;
				}
				
				if (!(l.match(reg2))) {
					$("#divln").html("Enter alphabets only");
					return false;
				}
			});
		});
	
		//existing username checking
		$(document).ready(function(){
			$("#un").change(function(){
				a=$("#un").val();
				$.ajax({url:"/lead_superadmin/ajax_username_check/",type:"post",data:"uname="+a,success:function(result)
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

		//existing email checking
		$(document).ready(function(){
			$("#em").change(function(){
				b=$("#em").val();
				$.ajax({url:"/lead_superadmin/ajax_email_check/",type:"post",data:"email="+b,success:function(result)
					{
						if(result=="")
						{
							$("#submit").removeAttr('disabled','disabled');
						}
						else
						{
							$("#submit").attr('disabled','disabled');
						}
						$("#div2").html(result);
					}
				});
			});
		});

		//form validation
		$(document).ready(function() {
		// Setup form validation on the #register-form element
			$("#editfrm").validate({

				// Specify the validation rules
				rules: {
					un: {
						required: true
					},
					fn: {
						required: true
					},
					ln: {
						required: true
					},
					em: {
						required: true,
						email: true
					},
					dn: {
						required: true
					},
					rn: {
						required: true
					},
					pw: {
						required: true,
						minlength: 5
					},
					pn: {
						digits: true,
						minlength: 10,
						maxlength: 12
					}
				},

				// Specify the validation error messages
				messages: {
					em: "<br/><br/>Required<BR/>",
					un: "<br/><br/>Required<BR/>",
					fn: "<br/><br/>Required<BR/>",

					ln: "<br/><br/>Required<BR/>",
					dn: {
							required: "Please select an option from the list",
					},
					rn: {
							required: "Please select an option from the list",
					},
					pw: {
						required: "<br/>Please provide a password<BR/>",
						minlength: "<br/><br/>Your password must be at least 5 characters long<BR/>"
					},
					pn: {
						digits: "Enter appropriate phone number",
						minlength: "Enter appropriate phone number",
						maxlength: "Enter appropriate phone number"
					}
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
	$form_attributes=array("name"=>"editfrm","id"=>"editfrm","novalidate"=>"false");
	echo form_open("/lead_superadmin/superadmin_editprocess/user",$form_attributes);
	echo form_hidden("u_id",$row[0]['user_id']);
	?>
		<table>
			<tr>
				<td>UserName:</td>
				<td>
					<?php
					$data=array('name'=>'un','id'=>'un','value'=>$row[0]['user_name']);
					echo form_input($data);
					?>
					<div class="err" id="div1"></div>
					<div class="err" id="divun"></div>
				</td>
			</tr>
			<tr>
				<td>First Name:</td>
				<td>
					<?php
					$data=array('name'=>'fn','id'=>'fn','value'=>$row[0]['first_name']);
					echo form_input($data);
					?>
					<div class="err" id="divfn"></div>
				</td>
			</tr>
			<tr>
				<td>Last Name:</td>
				<td>
					<?php
					$data=array('name'=>'ln','id'=>'ln','value'=>$row[0]['last_name']);
					echo form_input($data);
					?>
					<div class="err" id="divln"></div>
				</td>
			</tr>
			<tr>
				<td>Email:</td>
				<td>
					<?php
					$data=array('name'=>'em','id'=>'em','value'=>$row[0]['email']);
					echo form_input($data);
					?>
					<div class="err" id="div2"></div>
				</td>
			</tr>
			<tr>
				<td>Password :</td>
				<td>
					<?php
					$data=array('name'=>'pw','value'=>$row[0]['password']);
					echo form_input($data);
					?>
				</td>
			</tr>
			<tr>
				<td>Phone :</td>
				<td>
					<?php
					$data=array('name'=>'pn','value'=>$row[0]['phone']);
					echo form_input($data);
					?>
				</td>
			</tr>
			<tr>
			<tr>
				<td>Department name :</td>
				<td>
					<?php
					$selected1="";
					$options1=array(""=>'Select a Department');
					$tmpval1=$row[0]['department_name'];
					foreach ($arrval1 as $val)
					{
						$options1[$val]=$val;
						if($val==$tmpval1)
							$selected1=$val;
					}
					$js = 'id="dn" onChange="tldropdown_dept(this.value,'.$row[0]['user_id'].')"';
					echo form_dropdown('dn',$options1,$selected1,$js);
					?>
				</td>
			</tr>
			<tr>
				<td>Role name :</td>
				<td>
					<?php
					$selected2="";
					$options2=array(""=>'Select a Role');
					$tmpval2=$row[0]['role_name'];
					foreach ($arrval2 as $val)
					{
						$options2[$val]=$val;
						if($val==$tmpval2)
							$selected2=$val;
					}
					$js = 'id="rn" onChange="tldropdown_role(this.value,'.$row[0]['user_id'].')"';
					echo form_dropdown('rn',$options2,$selected2,$js);
					?>
					<div id="tldrop" style="width:200px; height:30px; margin-top:-25px; margin-left:300px;">
					<!-- team lead drop down only if role is intern -->
					</div>
				</td>
			</tr>
		<?php
		if($_SESSION['ses']['is_superadmin']==1)
		{ ?>
			<tr>
				<td>Is SuperAdmin &#63;:</td>
				<td>
					<?php
					$options3=array('0'=>'InActive','1'=>'Active');
					$tmpval3=$row[0]['is_superadmin'];
					if($tmpval3=="0")
						echo form_dropdown('suad', $options3, '0');
					else if($tmpval3=="1")
						echo form_dropdown('suad', $options3, '1');
					?>
				</td>
			</tr>
			<tr>
				<td>Is Admin &#63;:</td>
				<td>
					<?php
					$options4=array('0'=>'InActive','1'=>'Active');
					$tmpval4=$row[0]['is_admin'];
					if($tmpval4=="0")
						echo form_dropdown('ad', $options4, '0');
					else if($tmpval4=="1")
						echo form_dropdown('ad', $options4, '1');
					?>
				</td>
			</tr>
		<?php } ?>
			<tr>
				<td>
					<a href='/lead_superadmin/backpage/user'>
						<img src="<?php echo static_files_url(); ?>images/goback.png" height="25" width="50"/>
					</a>
				</td>
				<td>
					<?php
					$atr=array("name"=>"submit","id"=>"submit");
					echo form_submit($atr,'UPDATE');
					?>
				</td>
			</tr>
		</table>
	<?php
	echo form_close();
	?>
</body>
</html>