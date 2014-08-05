<?php

?>
<table border="2px" class="sortable">
<tr>
		<th>Serial &#35;</th><th>First Name</th><th>Last Name</th><th>UserName</th><th>Email </th><th>Phone</th><th>Department Name</th>
	</tr>
	<?php
	$var=$this->uri->segment(4)?$this->uri->segment(4):0;
	$i=$var+1;
	foreach ($internshow as $rowm)
	{
		$q="SELECT * from users WHERE user_id='".$rowm['intern_id']."'";
		$tmpvar=$this->db->query($q);
		$result=$tmpvar->result_array();
		foreach ($result as $row) {
	?>
	<tr>
		<td width="5"><?php echo $i++; ?></td>
		<td><?php echo strtoupper($row['first_name']); ?></td>
		<td><?php echo strtoupper($row['last_name']); ?></td>
		<td><?php echo $row['user_name']; ?></td>
		<td><?php echo $row['email']; ?></td>
		<td><?php echo strtoupper($row['phone']); ?></td>

	</tr>
	<?php
		}

	}
	?>
</table>
<div style="float:right">
	<?php echo $pages; ?>
</div>
