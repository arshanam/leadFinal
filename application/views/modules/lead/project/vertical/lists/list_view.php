<?php
	session_start();
	$vid=$_SESSION['ses_vid'];
	$q="select vertical_name from verticals where vertical_id='".$vid."' ";
	$res=$this->db->query($q);
	$r1=$res->result_array();
	$size=(sizeof($arrvar)/sizeof($arrvar[0]));
?>
<html>
<head>
	<meta charset="utf-8">
	<title>List View</title>
	<link href="<?php echo static_files_url(); ?>css/colorbox.css" rel="stylesheet" type="text/css" />
	<script src="<?php echo static_files_url(); ?>/js/jquery.js" type="text/javascript"></script>
	<script src="<?php echo static_files_url(); ?>/js/jquery.colorbox.js" type="text/javascript"></script>
	<script>
	//Assigning status of list assign to tl in DB
	function dropdownajax(tl_name,list_id)
	{
		$.ajax({url:"/lead_list/tlassign/",type:"post",data:"tlname="+tl_name+"&listid="+list_id,success:function(result)
			{
				alert(result);
			}
		});
	}

	$(document).ready(function(){
		$(".iframe1").colorbox({ iframe:true, width:"70%", height:"30%", onClosed:function(){ location.reload(true); } });
	});
	</script>

</head>
<body>

<table border='2px' class="sortable" ><h2>
<div style="float:left; color:purple; "><?php echo $r1[0]['vertical_name'];	?></div></h2>
<div style="float:right"><a href='/lead_superadmin/lists_modify/add'><img src="<?php echo static_files_url(); ?>images/add_item.png" height="50" width="50"/></a></div>
<tr>
		<th>Serial &#35;</th><th>List Name</th><th>Date Added</th><th>Last Modified</th><th>Upload Record </th><th>Comments</th><th>Assign Status</th><th colspan="2">Operations</th>
</tr>

<?php
$var=$this->uri->segment(4)?$this->uri->segment(4):0;
$k=$var+1;
for ($i=0; $i < $size ; $i++)
{
	$row=$arrvar[$i][0];
?>
	<tr>
		<td><?php echo $k; ?></td>
		<td><?php echo strtoupper($row['list_name']); ?></td>
		<td><?php echo $row['date_added']; ?></td>
		<td><?php echo $row['date_modified']; ?></td>
		<td><a class='iframe1' href='/lead_superadmin/upload_view/<?php echo $row['list_id']; ?>/'>Upload List</a></td>
		<td><?php echo $row['comment']; ?></td>
		<td>
			<select name="tlname" onchange="dropdownajax(this.value,<?php echo $row['list_id']; ?>)">
			<option value="" >Select TL</option>

			<?php
				$qw="select distinct u.user_name from users u, roles r, tl_lists t where u.role_id=r.role_id and r.role_name='teamlead'";//All teamleads
				$res2=$this->db->query($qw);
				$r2=$res2->result_array();

				$query=$this->db->query("SELECT distinct u.user_name from users u, tl_lists t WHERE t.tl_id=u.user_id AND t.list_id='".$row['list_id']."'"); //Current TL assigned
				$rx2=$query->result_array();
				echo $actvar;
				$actvar=$rx2[0]['user_name'];
				foreach ($r2 as $var2) { ?>
					<option <?php if($actvar==$var2['user_name']) { echo "selected"; }?>   value="<?php echo $var2['user_name'];?>" ><?php echo $var2['user_name'];?></option>
			<?php }?>
				</select>

		</td>
		<td>
			<a href='/lead_superadmin/lists_modify/edit/<?php echo $row['list_id'] ?>'>
				<img src="<?php echo static_files_url(); ?>images/edit_icon.png" />
			</a>
		</td>
		<td>
			<a href='/lead_superadmin/lists_modify/delete/<?php echo $row['list_id'] ?>'>
				<img src="<?php echo static_files_url(); ?>images/delete_icon.png" />
			</a>
		</td>
	</tr>


<?php
$k++;
}
?>
</table>
<div style="float:right">
	<?php echo $pages; ?>
</div>
</body>
</html>