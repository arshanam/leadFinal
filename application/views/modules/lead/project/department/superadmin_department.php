<?php
session_start();
$pname=$_SESSION['ses']['permissions'];
?>
<table border='2px' class="sortable" >
	<?php
	if(in_array("add",$pname) || ($_SESSION['ses']['is_superadmin']==1))
	{
	?>
	<div align="right"><a href='/lead_superadmin/superadmin_add/department'><img src="<?php echo static_files_url(); ?>images/add_item.png" height="50" width="50"/></a></div>
	<?php
	}
	?>
	<tr>
			<th>Serial Serial &#35;</th><th>Department ID</th><th>Department Name</th><th>Active Status</th><th colspan="2">Operations</th>
	</tr>
	</tr>
	<?php
	$var=$this->uri->segment(4)?$this->uri->segment(4):0;
	$i=$var+1;
	foreach ($departmentshow as $row)
	{
	?>
	<tr>
		<td><?php echo $i++; ?></td>
		<td><?php echo $row['department_id']; ?></td>
		<td><?php echo strtoupper($row['department_name']); ?></td>
		<td>
		<?php
		if($row['department_active']=="1")
			echo "Active";
		else if($row['department_active']=="0")
			echo "Inactive";
		?>
		</td>
		<?php
		if(in_array("edit",$pname) || ($_SESSION['ses']['is_superadmin']==1))
		{
		?>
		<td><a href='/lead_superadmin/superadmin_edit/department/<?php echo $row['department_id'] ?>'><img src="<?php echo static_files_url(); ?>images/edit_icon.png" /></a></td>
		<?php
		}
		if(in_array("delete",$pname) || ($_SESSION['ses']['is_superadmin']==1))
		{
		?>
		<td><a href='/lead_superadmin/superadmin_delete/department/<?php echo $row['department_id'] ?>' onclick="return(confirm('Are you sure?'))"><img src="<?php echo static_files_url(); ?>images/delete_icon.png" /></a></td>
		<?php
		}
		?>
	</tr>
	<?php
	}
	?>
</table>
<div style="float:right">
	<?php echo $pages; ?>
</div>