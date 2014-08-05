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
	$(document).ready(function(){
		$(".iframe1").colorbox({iframe:true, width:"70%", height:"30%",onClosed:function(){ location.reload(true); }});
		$(".iframe2").colorbox({iframe:true, width:"60%", height:"50%", onClosed:function(){ location.reload(true);}});
	});
	</script>

</head>
<body>

<table border='2px' class="sortable" ><h2>
<div style="float:left; color:purple; "><?php echo $r1[0]['vertical_name'];	?></div></h2>
<div style="float:right"><a href='/lead_marketresearch/lists_modify/add'><img src="<?php echo static_files_url(); ?>images/add_item.png" height="50" width="50"/></a></div>
<tr>
		<th>Serial &#35;</th><th>List Name</th><th>Date Added</th><th>Last Modified</th><th>Upload Record </th><th>Comments</th><th>Assignment</th><th colspan="3">Operations</th>
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
		<td><a class='iframe1' href='/lead_marketresearch/upload_view/<?php echo $row['list_id']; ?>/'><center><img src="<?php echo static_files_url(); ?>images/upload.png" alt="VIEW" height="42" width="42"  /></center></a></td>
		<td><?php echo $row['comment']; ?></td>
		<td>
			<a class="iframe2" href='/lead_marketresearch/assign_tl_view/<?php echo $row['list_id'] ?>'><center>
				<img src="<?php echo static_files_url(); ?>images/assign.png" alt="VIEW" height="42" width="42"  /></center>
		</td>

		<td>
			<a href='/lead_marketresearch/list_view/<?php echo $row['list_id'] ?>'>
				<img src="<?php echo static_files_url(); ?>images/view-icon.png" alt="VIEW" height="42" width="42"  />
		</td>
		<td>
			<a href='/lead_marketresearch/lists_modify/edit/<?php echo $row['list_id'] ?>'>
				<img src="<?php echo static_files_url(); ?>images/edit_icon.png" />
			</a>
		</td>
		<td>
			<a href='/lead_marketresearch/lists_modify/delete/<?php echo $row['list_id'] ?>'>
				<img src="<?php echo static_files_url(); ?>images/delete_icon.png" />
			</a>
		</td>
	</tr>



<?php
$k++;
}
?>
</table>
<div style="float:left;">
					<a href='/lead_marketresearch/backpage/vertical'>
						<img src="<?php echo static_files_url(); ?>images/goback.png" height="25" width="50"/>
					</a>
</div>
<div style="float:right">
	<?php echo $pages; ?>
</div>
</body>
</html>