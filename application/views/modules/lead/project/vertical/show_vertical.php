<?php
session_start();
$pname=$_SESSION['ses']['permissions'];
?>
<table border='2px' class="sortable" >

	<tr>
		<th>Serial &#35;</th><th>Vertical Name</th><th>Status</th><th>Manage Lists</th>
	</tr>
	</tr>
	<?php
	$var=$this->uri->segment(4)?$this->uri->segment(4):0;
	$i=$var+1;
	foreach ($verticalshow as $row)
	{
	?>
	<tr>
		<td><?php echo $i++; ?></td>
		<td><?php echo strtoupper($row['vertical_name']); ?></td>
		<td><?php if($row['active']==1) echo "Active"; else echo "InActive"; ?></td>
		<td><a href='/lead_marketresearch/lists/<?php echo $row['vertical_id'] ?>'>Manage Lists</a></td>


	</tr>
	<?php
	}
	?>
</table>
<div style="float:right">
	<?php echo $pages; ?>
</div>