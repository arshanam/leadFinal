<?php
//$internnameinternname
//$listname
$tlid=$_SESSION['ses']['user_id'];

$q="SELECT user_id from users WHERE user_name='".$internname."'";
$querytwo=$this->db->query($q);
$restwo=$querytwo->result_array();
$internid=$restwo[0]['user_id'];

$queryone=$this->db->query("SELECT * FROM tl_interns WHERE NOT intern_id='".$internid."' AND tl_id='".$tlid."'");
$resone=$queryone->result_array();



?>
<p>Select a Intern to reassign <?php echo $listname; ?></p>
<table>
	<tr>
		<form action="/lead_teamlead/reassign/<?php echo $internname;?>/<?php echo $listname; ?>/" method="POST" accept-charset="utf-8">
			<td>
			<select name="selectname" >
				<option value="">Select One</option>
				<?php foreach ($resone as $value) {
						$querythree=$this->db->query("SELECT DISTINCT user_name FROM users WHERE user_id='".$value['intern_id']."'");
						$resthree=$querythree->result_array();
						$intern=$resthree[0]['user_name'];
					?>
					<option value="<?php echo $value['intern_id']; ?>"><?php echo $intern; ?></option>
			<?php } ?>
			</select>
			</td>
			<td>
				<input type="submit" name="submit" value="ReAssign">
			</td>
		</form>
	</tr>
</table>