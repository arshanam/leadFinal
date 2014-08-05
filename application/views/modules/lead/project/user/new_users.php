<?php
session_start();
$q1="select department_name from department";
$res1=$this->db->query($q1);
$row=$res1->result_array();
$j=0;
for($i=0;$i<($res1->num_rows());$i++)
{

$arrval[$j++]=strtoupper($row[$i]['department_name']);
}

$q2="select role_name from roles";
$res2=$this->db->query($q2);
$row2=$res2->result_array();
$j=0;
for($i=0;$i<($res2->num_rows());$i++)
{

$arrval1[$j++]=strtoupper($row2[$i]['role_name']);
}
?>
<html>
<head>
    <meta charset="utf-8">
    <title>Add User</title>
    <script src="<?php echo static_files_url(); ?>/js/jquery.js" type="text/javascript"></script>
    <script src="<?php echo static_files_url(); ?>/js/jquery.validate.js" type="text/javascript"></script>
	<script>
		//dropdown of tl name for assigning tl to interns
		function tldropdown_role(rname)
		{
			dname=$("#dn").val();
			if((rname=='intern' || rname=='INTERN') && dname!="")
			{
				$.ajax({url:"/lead_superadmin/add_tl_to_intern/",type:"post",data:"deptname="+dname,success:function(result)
					{
						$("#tldrop").html(result);
					}
				});
			}
			$("#tldrop").html("");
		}
		function tldropdown_dept(dname)
		{
			rname=$("#rn").val();
			if((rname=='intern' || rname=='INTERN') && dname!="")
			{
				$.ajax({url:"/lead_superadmin/add_tl_to_intern/",type:"post",data:"deptname="+dname,success:function(result)
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
			$("#un").blur(function(){
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
			$("#em").blur(function(){
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
			$("#frm").validate({

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
					em: "<br/><br/>Required<br/>",
					un: {
						required: "<br/><br/>Required<br/>"
					},
					fn: "<br/><br/>Required<br/>",

					ln: "<br/><br/>Required<br/>",
					dn: {
						required: "Please select an option from the list",
					},
					rn: {
						required: "Please select an option from the list",
					},
					pw: {
						required: "<br/>Please provide a password<br/>",
						minlength: "<br/><br/>Your password must be at least 5 characters long<br/>"
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
	$form_attributes=array("name"=>"frm","id"=>"frm","novalidate"=>"false");
	echo form_open("/lead_superadmin/superadmin_addprocess/user",$form_attributes);
	?>
		<table>
			<tr>
				<td>User Name:</td>
				<td>
					<?php
					$data=array('name'=>'un','id'=>'un');
					echo form_input($data);
					?>
					<div class="err" id="div1" name="div1"></div>
					<div class="err" id="divun" name="div1"></div>
				</td>
			</tr>
			<tr>
				<td>First Name:</td>
				<td>
					<?php
					$data=array('name'=>'fn','id'=>'fn');
					echo form_input($data);
					?>
					<div class="err" id="divfn" name="divfn"></div>
				</td>
			</tr>
			<tr>
				<td>Last Name:</td>
				<td>
					<?php
					$data=array('name'=>'ln','id'=>'ln');
					echo form_input($data);
					?>
					<div class="err" id="divln" name="divln"></div>
				</td>
			</tr>
			<tr>
				<td>Email:</td>
				<td>
					<?php
					$data=array('name'=>'em','id'=>'em');
					echo form_input($data);
					?>
					<div class="err" id="div2"></div>
				</td>
			</tr>
			<tr>
				<td>Password :</td>
				<td>
					<?php
					$data=array('name'=>'pw');
					echo form_input($data);
					?>
				</td>
			</tr>
			<tr>
				<td>Phone :</td>
				<td>
					<?php
					$data=array('name'=>'pn');
					echo form_input($data);
					?>
				</td>
			</tr>
			<tr>
				<td>Department name :</td>
				<td>
					<?php
					$options1=array(""=>'Select a Department');
					foreach ($arrval as $val)
					{
						$options1[$val]=$val;
					}
					$js = 'id="dn" onChange="tldropdown_dept(this.value)"';
					echo form_dropdown('dn',$options1,"",$js);
					?>
				</td>
			</tr>
			<tr>
				<td>Role name :</td>
				<td>
					<?php
					$options2=array(""=>'Select a Role');
					foreach ($arrval1 as $val)
					{
						$options2[$val]=$val;
					}
					$js = 'id="rn" onChange="tldropdown_role(this.value)"';
					echo form_dropdown('rn',$options2,"",$js);
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
				<td>Is SuperAdmin &#63; :</td>
				<td>
					<?php
					$options3=array('0'=>'InActive','1'=>'Active');
					echo form_dropdown('suad', $options3, '0');
					?>
				</td>
			</tr>
			<tr>
				<td>Is Admin &#63; :</td>
				<td>
					<?php
					$options4=array('0'=>'InActive','1'=>'Active');
					echo form_dropdown('ad', $options4, '0');
					?>
				</td>
			</tr>
		<?php } ?>
		</table>
		<div style="float:left;width:45%;" >
			<a href='/lead_superadmin/backpage/user'>
				<img src="<?php echo static_files_url(); ?>images/goback.png" height="35" width="100"/>
			</a>
		</div>
		<div style="float:right;width:45%;">
			<?php
			$atr=array("name"=>"submit","id"=>"submit");
			echo form_submit($atr,'Add');
			?>
		</div>
    <?php
	echo form_close();
	?>
</body>
</html>