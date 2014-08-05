<?php
session_start();
$pname=$_SESSION['ses']['permissions'];
?>
<table border='2px' class="sortable" >
	<?php
	if(in_array("add",$pname) || ($_SESSION['ses']['is_superadmin']==1))
	{
	?>
	<div align="right"><a href='/lead_superadmin/verticals_modify/add'><img src="<?php echo static_files_url(); ?>images/add_item.png" height="50" width="50"/></a></div>
	<?php
	}
	?>
	<tr>
		<th>Serial &#35;</th><th>Vertical Name</th><th>Status</th><th>Manage Lists</th><th>Assigned Market Researcher</th><th colspan="2">Operations</th>
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
		<td><a href='/lead_superadmin/lists/<?php echo $row['vertical_id'] ?>'>Manage Lists</a></td>
		<td><?php echo $row['user_name'];  ?></td>
		<?php
		if(in_array("edit",$pname) || ($_SESSION['ses']['is_superadmin']==1))
		{
		?>
		<td><a href='/lead_superadmin/verticals_modify/edit/<?php echo $row['vertical_id'] ?>'><img src="<?php echo static_files_url(); ?>images/edit_icon.png" /></a></td>
		<?php
		}
		if(in_array("delete",$pname) || ($_SESSION['ses']['is_superadmin']==1))
		{
		?>
		<td><a href='/lead_superadmin/verticals_modify/delete/<?php echo $row['vertical_id'] ?>' onclick="return(confirm('Are you sure?'))"><img src="<?php echo static_files_url(); ?>images/delete_icon.png" /></a></td>
		<?php
		}
		?>
	</tr>
	<tr>
		
	</tr>
	<?php
	}
	?>
</table>
<div style="float:right">
	<?php echo $pages; ?>
</div>