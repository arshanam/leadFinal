<table border="2px" class="sortable">
<tr>
		<th>Serial &#35;</th><th>First Name</th><th>Last Name</th><th>UserName</th><th>Email </th><th>Phone</th><th>Department Name</th><th>Status</th>
	</tr>
	<?php
	$var=$this->uri->segment(4)?$this->uri->segment(4):0;
	$i=$var+1;
	foreach ($internshow as $row)
	{
		$q="select * from intern_lists where intern_name='".$row['user_name']."'";
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
	</tr>
	<?php
	}
	?>
</table>
<div style="float:right">
	<?php echo $pages; ?>
</div>
