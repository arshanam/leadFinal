<?php
session_start();
$pname=$_SESSION['ses']['permissions'];
?>
<table border='2px' class="sortable" >
	<?php
	if(in_array("add",$pname) || ($_SESSION['ses']['is_superadmin']==1))
	{
	?>
	<div align="right"><a href='/lead_superadmin/superadmin_add/permission'><img src="<?php echo static_files_url(); ?>images/add_item.png" height="50" width="50"/></a></div>
	<?php
	}
	?>
	<tr>
		<th>Serial Serial &#35;</th><th>Permission ID</th><th>Module Name</th><th colspan="2">Operations</th>
	</tr>
	</tr>
	<?php
	$var=$this->uri->segment(4)?$this->uri->segment(4):0;
	$i=$var+1;
	foreach ($permissionshow as $row)
	{
	?>
	<tr>
		<td><?php echo $i++; ?></td>
		<td><?php echo $row['permission_id']; ?></td>
		<td><?php echo strtoupper($row['module_name']); ?></td>
		<?php
		if(in_array("edit",$pname) || ($_SESSION['ses']['is_superadmin']==1))
		{
		?>
		<td><a href='/lead_superadmin/superadmin_edit/permission/<?php echo $row['permission_id'] ?>'><img src="<?php echo static_files_url(); ?>images/edit_icon.png" /></a></td>
		<?php
		}
		if(in_array("delete",$pname) || ($_SESSION['ses']['is_superadmin']==1))
		{
		?>
		<td><a href='/lead_superadmin/superadmin_delete/permission/<?php echo $row['permission_id'] ?>' onclick="return(confirm('Are you sure?'))"><img src="<?php echo static_files_url(); ?>images/delete_icon.png" /></a></td>
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