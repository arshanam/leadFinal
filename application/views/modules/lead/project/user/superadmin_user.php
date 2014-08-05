<?php
session_start();
$pname=$_SESSION['ses']['permissions'];
?>
<table border="2px" class="sortable">
	<?php
	if(in_array("add",$pname) || ($_SESSION['ses']['is_superadmin']==1))
	{
	?>
	<div align="right"><a href='/lead_superadmin/superadmin_add/user'><img src="<?php echo static_files_url(); ?>images/add_user.png" height="50" width="50"/></a></div>
	<?php
	}
	?>
	<tr>
		<th>Serial &#35;</th><th>First Name</th><th>Last Name</th><th>UserName</th><th>Email </th><th>Password</th><th>Phone</th><th>Department Name</th><th>Role Name</th><th colspan="2">Operations</th>
	</tr>
	<?php
	$var=$this->uri->segment(4)?$this->uri->segment(4):0;
	$i=$var+1;
	foreach ($usershow as $row)
	{
	?>
	<tr>
		<td width="5"><?php echo $i++; ?></td>
		<td><?php echo strtoupper($row['first_name']); ?></td>
		<td><?php echo strtoupper($row['last_name']); ?></td>
		<td><?php echo $row['user_name']; ?></td>
		<td><?php echo $row['email']; ?></td>
		<td width="25"><?php echo $row['password']; ?></td>
		<td><?php echo strtoupper($row['phone']); ?></td>
		<td width="11"><?php echo strtoupper($row['department_name']); ?></td>
		<td><?php echo strtoupper($row['role_name']); ?></td>
		<?php
		if(in_array("edit",$pname) || ($_SESSION['ses']['is_superadmin']==1))
		{
		?>
		<td width="5"><a href='/lead_superadmin/superadmin_edit/user/<?php echo $row['user_id'] ?>'><img src="<?php echo static_files_url(); ?>images/edit_icon.png" /></a></td>
		<?php
		}
		if(in_array("delete",$pname) || ($_SESSION['ses']['is_superadmin']==1))
		{
		?>
		<td width="5"><a href='/lead_superadmin/superadmin_delete/user/<?php echo $row['user_id'] ?>' onclick="return(confirm('Are you sure?'))"><img src="<?php echo static_files_url(); ?>images/delete_icon.png" /></a></td>
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