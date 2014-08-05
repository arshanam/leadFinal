<?php
session_start();
$pname=$_SESSION['ses']['permissions'];
?>
<table border='2px' class="sortable" >
	<?php
	if(in_array("add",$pname) || ($_SESSION['ses']['is_superadmin']==1))
	{
	?>
	<div align="right"><a href='/lead_superadmin/superadmin_add/role'><img src="<?php echo static_files_url(); ?>images/add_item.png" height="50" width="50"/></a></div>
	<?php
	}
	?>
	<tr>
		<th>Serial &#35;</th><th>Role ID</th><th>Role Name</th><th>Role Priority</th><th>Active Status</th><th>Permission Assigned</th><th colspan="2">Operations</th>
	</tr>
	</tr>
	<?php
	$var=$this->uri->segment(4)?$this->uri->segment(4):0;
	$i=$var+1;
	foreach ($roleshow as $row)
	{
	?>
	<tr>
		<?php
		$q1="select permission_id from role_permissions where role_id='".$row['role_id']."'";
		$res1=$this->db->query($q1);
		$row1=$res1->result_array();
		$p_arr1=explode(",",$row1[0]['permission_id']);
		$p_arr2=array();
		foreach ($p_arr1 as $p)
		{
			$q2="select module_name from permissions where permission_id='".$p."'";
			$res2=$this->db->query($q2);
			$row2=$res2->result_array();
			array_push($p_arr2,$row2[0]['module_name']);
		}
		$p_str=implode(",",$p_arr2);
		?>
		<td><?php echo $i++; ?></td>
		<td><?php echo $row['role_id']; ?></td>
		<td><?php echo strtoupper($row['role_name']); ?></td>
		<td><?php echo $row['role_priority']; ?></td>
		<td>
		<?php
		if($row['role_active']=="1")
			echo "Active";
		else if($row['role_active']=="0")
			echo "Inactive";
		?>
		</td>
		<td><?php echo $p_str; ?></td>
		<?php
		if(in_array("edit",$pname) || ($_SESSION['ses']['is_superadmin']==1))
		{
		?>
		<td><a href='/lead_superadmin/superadmin_edit/role/<?php echo $row['role_id'] ?>'><img src="<?php echo static_files_url(); ?>images/edit_icon.png" /></a></td>
		<?php
		}
		if(in_array("delete",$pname) || ($_SESSION['ses']['is_superadmin']==1))
		{
		?>
		<td><a href='/lead_superadmin/superadmin_delete/role/<?php echo $row['role_id'] ?>' onclick="return(confirm('Are you sure?'))"><img src="<?php echo static_files_url(); ?>images/delete_icon.png" /></a></td>
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