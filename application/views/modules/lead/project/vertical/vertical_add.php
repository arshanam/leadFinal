<head>
	<meta charset="utf-8">
	<title>Add Verticals</title>
	<script src="<?php echo static_files_url(); ?>/js/jquery.js" type="text/javascript"></script>
	<script src="<?php echo static_files_url(); ?>/js/jquery.validate.js" type="text/javascript"></script>
	<script>
	$(document).ready(function() {

		// Setup form validation on the #register-form element
		$("#addverticalf").validate({

			// Specify the validation rules

				rules: {
				verticalname: {
					required: true
				},
				verticalactive: {
					required: true
				},
				verticalmkt: {
					required: true
				},
			},

			// Specify the validation error messages
			messages: {
				verticalname: "<br/><br/>Required<BR/>",
				verticalactive: "<br/><br/>Required<BR/>",
				verticalmkt: "<br/><br/>Required<BR/>"
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
	$form_attributes=array("name"=>"addverticalf","id"=>"addverticalf","novalidate"=>"false");
	echo form_open("/lead_superadmin/verticals_modify/addnew",$form_attributes);
	?>
		<table>
			<tr>
				<td>Vertical Name</td>
				<td>
					<?php
					$data=array('name'=>'verticalname');
					echo form_input($data);
					?>
				</td>
			</tr>
			<tr>
				<td>Active Status(1 for Active):</td>
				<td>
					<?php
					$options=array('0'=>'InActive','1'=>'Active');
					echo form_dropdown('verticalactive', $options, '1');
					?>
				</td>
			</tr>
			<tr>
				<td>Select Market Researcher:</td>
				<td>
					<?php
					$qry="SELECT u.user_id, u.user_name FROM users u, roles r WHERE u.role_id=r.role_id AND r.role_name='market_researcher'";
					$res_qry=$this->db->query($qry);
					$row_qry=$res_qry->result_array();
					$optionsmkt['']='Select One';
					foreach($row_qry as $val)
						$optionsmkt[$val['user_id']]=$val['user_name'];
					echo form_dropdown('verticalmkt', $optionsmkt, '');
					?>
				</td>
			</tr>
			<tr>
				<td>
					<a href='/lead_superadmin/backpage/vertical'>
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