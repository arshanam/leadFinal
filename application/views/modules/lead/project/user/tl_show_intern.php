<table border="2px" class="sortable">
<div align="right"><a href='/lead_teamlead/add_intern'><img src="<?php echo static_files_url(); ?>images/add_user.png" height="50" width="50"/></a></div>
<tr>
		<th>Serial &#35;</th><th>First Name</th><th>Last Name</th><th>UserName</th><th>Email </th><th>Phone</th><th>Status</th><th>Operations</th>
	</tr>
	<?php
	$var=$this->uri->segment(4)?$this->uri->segment(4):0;
	$i=$var+1;
	foreach ($internshow as $rowm)
	{
		$q="SELECT * from users WHERE user_id='".$rowm['intern_id']."'";
		$tmpvar=$this->db->query($q);
		$result=$tmpvar->result_array();
		foreach ($result as $row)
		{
			$q1="select * from intern_lists where intern_name='".$row['user_name']."'";
			$res1=$this->db->query($q1);
			$a=$res1->num_rows();
	?>
	<tr>
		<td width="5"><?php echo $i++; ?></td>
		<td><?php echo strtoupper($row['first_name']); ?></td>
		<td><?php echo strtoupper($row['last_name']); ?></td>
		<td><?php echo $row['user_name']; ?></td>
		<td><?php echo $row['email']; ?></td>
		<td><?php echo strtoupper($row['phone']); ?></td>
		<?php if($a>0) { ?>
		<td>Assigned</td>
		<?php } else { ?>
		<td>Not Assigned</td>
		<?php } ?>
		<td width="5"><a href='/lead_teamlead/delete_intern/<?php echo $row['user_id'] ?>' onclick="return(confirm('Are you sure?'))"><img src="<?php echo static_files_url(); ?>images/delete_icon.png" /></a></td>
	</tr>
	<?php
		}
	}
	?>
</table>
<div style="float:right">
	<?php echo $pages; ?>
</div>
