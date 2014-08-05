<html>
<head>

	<title></title>
</head>
<body>
	<table>
		<thead>
			<tr>
				<th>List Name</th>
				<th>Assigned By</th>
				<th>Date Assigned</th>
			</tr>
		</thead>

		<tbody>
		<?php foreach ($internhistory as  $value) { ?>
			<tr>
				<td><?php $q=$this->db->query("SELECT DISTINCT list_name FROM listmaster WHERE list_id='".$value['list_id']."'");
				$resq=$q->result_array();
				$listname=$resq[0]['list_name'];
				echo $listname;
					?></td>
					<td>
					<?php $q=$this->db->query("SELECT DISTINCT user_name FROM users WHERE user_id='".$value['tl_id']."'");
					$resq=$q->result_array();
					$username=$resq[0]['user_name'];
					echo $username;
					?>
					</td>
					<td>
						<?php echo $value['date_assigned']; ?>
					</td>
			</tr>
			<?php	} ?>
		</tbody>
	</table>
</body>
</html>