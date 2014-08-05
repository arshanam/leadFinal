<?php
//here $id is the Role ID for table Roles
	$q="SELECT v.vertical_id, v.vertical_name, v.active, v.date_added, v.date_modified, m.mkt_id, u.user_name FROM verticals v, mkt_verticals m, users u WHERE v.vertical_id=m.vertical_id AND m.mkt_id=u.user_id AND v.vertical_id='".$id."'";
	$query = $this->db->query($q);
	$row = $query->row();
?>
<head>
<title>Edit Verticals</title>
<script src="<?php echo static_files_url(); ?>/js/jquery.js" type="text/javascript"></script>
<script src="<?php echo static_files_url(); ?>/js/jquery.validate.js" type="text/javascript"></script>
<script>
  	$(document).ready(function() {

    // Setup form validation on the #register-form element
		$("#editverticalf").validate({

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
	$form_attributes=array("name"=>"editverticalf","id"=>"editverticalf","novalidate"=>"false");
	echo form_open("/lead_superadmin/verticals_modify/update",$form_attributes);
	echo form_hidden("id",$row->vertical_id);
	?>
		<table>
			<tr>
				<td>Vertical Name</td>
				<td>
					<?php
					$data=array('name'=>'verticalname','value'=>$row->vertical_name);
					echo form_input($data);
					?>
				</td>
			</tr>
			<tr>
				<td>Active Status(1 for Active):</td>
				<td>
					<?php
					$options=array('0'=>'InActive','1'=>'Active');
					$tmpval=$row->active;
					if($tmpval=="1")
						echo form_dropdown('verticalactive', $options, '1');
					else if($tmpval=="0")
						echo form_dropdown('verticalactive', $options, '0');
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
					foreach($row_qry as $val)
					{
						$optionsmkt[$val['user_id']]=$val['user_name'];
						if($val['user_id']==$row->mkt_id)
							$selected=$val['user_id'];
					}
					echo form_dropdown('verticalmkt', $optionsmkt, $selected);
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