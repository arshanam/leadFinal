<?php
	session_start();
	$vid=$_SESSION['ses_vid'];
?>
<html>
<head>
	<meta charset="utf-8">
	<title>List View</title>
</head>
<body>

<table border='2px' class="sortable" ><h2>
<div style="float:left; color:purple; "><?php echo $vid;?></div></h2>
<tr>
		<th>Serial &#35;</th><th>List Name</th><th>Comment</th><th>Date Assigned</th><th>Assigned By</th><th colspan="2"><center>List Status</center></th><th>Download Pdf</th>
</tr>

<?php
$k=1;

foreach ($listshow as $row) {

?>
	<tr><form action="/lead_teamlead/insertstatus/<?php echo $row['list_id']."/".$k; ?>" method="POST" accept-charset="utf-8">
		<td><?php echo $k; ?></td>
		<td><?php echo strtoupper($row['list_name']); ?></td>
		<td><?php echo $row['comment']; ?></td>
		<td><?php echo $row['date_assigned']; ?></td>
		<td><?php echo $row['assigned_by']; ?></td>
		<td><input type="text" name="<?php echo "status".$k;?>" value="<?php echo $row['status']; ?>" placeholder="Enter Status"></td>
		<td><input type="submit" name="submit" value="Update Status"></form></td><td>
		<?php
		$listid=$row['list_id'];
		$qr="SELECT DISTINCT * FROM list_pdf WHERE list_id='".$listid."'";

		//$resqr=$qr->row();
		$mres=$this->db->query($qr);
		$resq=$mres->result_array();
		$filename=$resq[0]['pdf_name'];
		if($mres->num_rows() == 1){ ?>
			<a href='<?php echo current_base_url();?>webroot/uploads/<?php echo $filename; ?>'>
						<img src="<?php echo static_files_url(); ?>images/download.png" height="68" width="100"/>
			</a>
		<?php } ?></td>
	</tr>
<?php
$k++;
}
?>
</table>
</body>
</html>