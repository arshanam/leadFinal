<table border="2px" class="sortable">
<div align="right"><a href='/lead_marketresearch/add_teamlead'><img src="<?php echo static_files_url(); ?>images/add_user.png" height="50" width="50"/></a></div>
<tr>
		<th>Serial &#35;</th><th>First Name</th><th>Last Name</th><th>UserName</th><th>Email </th><th>Phone</th><th>Department Name</th><th>Status</th><th>Operations</th>
	</tr>
	<?php
	$var=$this->uri->segment(4)?$this->uri->segment(4):0;
	$i=$var+1;
	foreach ($teamleadshow as $row)
	{
		$q="select * from tl_lists where tl_id='".$row['user_id']."'";
		$res=$this->db->query($q);
		$a=$res->num_rows();
	?>
	<tr>
		<td width="5"><?php echo $i++; ?></td>
		<td><?php echo strtoupper($row['first_name']); ?></td>
		<td><?php echo strtoupper($row['last_name']); ?></td>
		<td><?php echo $row['user_name']; ?></td>
		<td><?php echo $row['email']; ?></td>
		<td><?php echo strtoupper($row['phone']); ?></td>
		<td width="11"><?php echo strtoupper($row['department_name']); ?></td>
		<?php if($a>0) { ?>
		<td>Assigned</td>
		<?php } else { ?>
		<td>Not Assigned</td>
		<?php } ?>
		<td width="5"><a href='/lead_marketresearch/delete_teamlead/<?php echo $row['user_id'] ?>' onclick="return(confirm('Are you sure?'))"><img src="<?php echo static_files_url(); ?>images/delete_icon.png" /></a></td>
	</tr>
	<?php
	}
	?>
</table>
<div style="float:right">
	<?php echo $pages; ?>
</div>
