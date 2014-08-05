<div class="top" style='overflow-x: auto; overflow-y: auto; width:auto;height:400px;'>
<?php

$qr="SELECT DISTINCT * FROM list_pdf WHERE list_id='".$listid."'";

//$resqr=$qr->row();
$mres=$this->db->query($qr);
$resq=$mres->result_array();
$filename=$resq[0]['pdf_name'];
if($mres->num_rows() == 1){ ?>
			<div align="right"><p>This list contains a PDF file. Download Here</p>
				<a href='<?php echo current_base_url();?>webroot/uploads/<?php echo $filename; ?>'>
						<img src="<?php echo static_files_url(); ?>images/download.png" height="68" width="100"/>
				</a>
			</div>
		<?php } ?>

<table class='sortable'>
<tr>
	<th>Email</th><th>FirstName</th><th>MiddleName</th><th>LastName</th><th>title</th><th>Company</th><th>Department</th><th colspan="2">Address</th><th>City</th><th>State</th><th>Zipcode</th><th>Phone</th><th>Fax</th>

</tr>

	<?php

	foreach ($arrval as $value) {echo ("<tr>");
		echo "<td>".$value['email']."</td>";
		echo "<td>".$value['firstname']."</td>";
		echo "<td>".$value['middlename']."</td>";
		echo "<td>".$value['lastname']."</td>";
		echo "<td>".$value['title']."</td>";
		echo "<td>".$value['company']."</td>";
		echo "<td>".$value['department']."</td>";
		echo "<td>".$value['address1']."</td>";
		echo "<td>".$value['address2']."</td>";
		echo "<td>".$value['city']."</td>";
		echo "<td>".$value['state']."</td>";
		echo "<td>".$value['zipcode']."</td>";
		echo "<td>".$value['phone']."</td>";
		echo "<td>".$value['fax']."</td>";
		echo("</tr>");
		}
	?>
</table>


</div>
<div style="float:left">
	<a href='/lead_marketresearch/backpage/vertical'>
						<img src="<?php echo static_files_url(); ?>images/goback.png" height="25" width="50"/>
	</a>
</div>