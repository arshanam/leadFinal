<html>
<head>
	<link href="<?php echo static_files_url(); ?>css/colorbox.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo static_files_url(); ?>css/jquery-ui.css" rel="stylesheet" type="text/css" />
	<script src="<?php echo static_files_url(); ?>/js/jquery.js" type="text/javascript"></script>
	<script src="<?php echo static_files_url(); ?>/js/jquery.colorbox.js" type="text/javascript"></script>
	<script src="<?php echo static_files_url(); ?>/js/jquery-ui.js" type="text/javascript"></script>

	<!--script>
	//Assigning status of list assign to tl in DB
	function dropdownajax(tl_name,list_id)
	{
		$.ajax({url:"/lead_list/tlassign/",type:"post",data:"tlname="+tl_name+"&listid="+list_id,success:function(result)
			{
				alert(result);
			}
		});
	}
	</script-->

	<script>
		$(function() {
			$( "#datepicker" ).datepicker();
		});
	</script>
</head>

<body>
<form action="/lead_list/tlassign/<?php echo $listid?>/" method="post" accept-charset="utf-8">
	<table border='2px'>
		<tr>
			<th>Select A TL</th><th>DeadLine</th>
		</tr>
		<tr>
		<td>
			<select name="tlid" ><!--onchange="dropdownajax(this.value,<?php echo $listid; ?>)"-->
			<option value="" >Select TL</option>

			<?php
				$qw="SELECT DISTINCT u.user_name, u.user_id FROM users u, roles r, tl_lists t WHERE u.role_id=r.role_id AND r.role_name='teamlead'";//All teamleads
				$res2=$this->db->query($qw);
				$r2=$res2->result_array();

				$query=$this->db->query("SELECT distinct u.user_name from users u, tl_lists t WHERE t.tl_id=u.user_id AND t.list_id='".$listid."'"); //Current TL assigned
				$rx2=$query->result_array();
				//echo $actvar;
				$actvar=$rx2[0]['user_name'];
				foreach ($r2 as $var2) { ?>
					<option <?php if($actvar==$var2['user_name']) { echo "selected"; }?>   value="<?php echo $var2['user_id'];?>" ><?php echo $var2['user_name'];?></option>
			<?php }?>
				</select>
		</td>
		<td>
			<?php
				$deadlineq=$this->db->query("SELECT DISTINCT t.deadline FROM tl_lists t, users u WHERE u.user_name='".$actvar."' AND u.user_id=t.tl_id AND t.vertical_id='".$vid."'");
				$deadlineqarr=$deadlineq->result_array();
				$deadline=$deadlineqarr[0]['deadline'];
			?>
			<input name="date" type="text" id="datepicker" value="<?php echo $deadline; ?>" >
		</td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" name="submit" value="Assign"></td>
		</tr>
	</table>
</form>
</body>
</html>