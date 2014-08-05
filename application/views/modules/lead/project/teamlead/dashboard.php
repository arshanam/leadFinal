<?php
//This query filters all the lists which are neither flagged done nor flagged nothing at all.
$query=$this->db->query("SELECT * FROM tl_lists WHERE NOT status='' AND NOT LOWER(status)='done' AND tl_id='".$id."'");

$queryvar=$query->result_array();
$x=0;
foreach ($queryvar as  $value) {
	$idl=$value['list_id'];
	$qdx="SELECT * FROM listmaster WHERE list_id='".$idl."'";
	$resultq=$this->db->query($qdx);
	$resultx=$resultq->result_array();
	$var=$resultx[0]['list_name'];
	$thrdarr[$x++]=array('id'=>$idl, 'lname'=>$var);
}
?>
<html>
	<head>
		<meta charset="utf-8">
		<title>DashBoard</title>
		<link href="<?php echo static_files_url(); ?>css/jquery-ui.css" rel="stylesheet" type="text/css" />
		<script src="<?php echo static_files_url(); ?>/js/jquery-ui.js" type="text/javascript"></script>
		<script src="<?php echo static_files_url(); ?>js/sorttable.js" type="text/javascript"></script>
		<script src="<?php echo static_files_url(); ?>js/jquery.simplePagination.js" type="text/javascript"></script>
		<script src="<?php echo static_files_url(); ?>js/jquery.js" type="text/javascript"></script>
		<script src="<?php echo static_files_url(); ?>js/jquery.validate.js" type="text/javascript"></script>
		<link href="<?php echo static_files_url(); ?>css/colorbox.css" rel="stylesheet" type="text/css" />
		<script src="<?php echo static_files_url(); ?>/js/jquery.colorbox.js" type="text/javascript"></script>
		<script>
		$(function() {
			$( "#datepicker" ).datepicker();
		});
		</script>
		<script>
			$(document).ready(function(){
				$(".iframe1").colorbox({iframe:true, width:"70%", height:"30%", onClosed:function(){ location.reload(true); } });
			});
		</script>
		<script>
			function check_endrec(n_val,max_val)
			{
				if(n_val>max_val || n_val=="")
				{
					$("#submit").attr('disabled','disabled');
					if(n_val=="")
						alert("Enter End Record value");
					else
						alert("End Record value cannot exceed "+max_val);
				}
				else
				{
					$("#submit").removeAttr('disabled','disabled');
				}
			}
		</script>
		<script>
			function check_selectintern(selectval)
			{
				if(selectval=="")
				{
					$("#submit").attr('disabled','disabled');
					alert("Select Intern");
				}
				else
					$("#submit").removeAttr('disabled','disabled');
			}
		</script>
		<!--script>
			function filter_list_by_name(listname)
			{
				if(listname!="")
				{
					$.ajax({url:"/lead_teamlead/filter_by_listname/",type:"post",data:"lname="+listname,success:function(result)
						{
							//alert(result);
							$("o_b_div").removeData();
							$("n_b_div").html(result);
						}
					});
				}
			}
		</script>
		<script>
			function filter_list_by_intern(internname)
			{
				if(listname!="")
				{
					$.ajax({url:"/lead_teamlead/filter_by_internname/",type:"post",data:"iname="+internname,success:function(result)
						{
							//alert(result);
							$("o_b_div").removeData();
							$("n_b_div").html(result);
						}
					});
				}
			}
		</script-->
	</head>
	<body>
		<div class="top" style='overflow-y: auto; width:auto;height:400px;'>
		<table >    <!--class="sortable"-->
			<tr>
				<th>Serial # </th><th>List Name</th><th>Assign to Interns</th><th>Start Record</th><th>End Record</th><th>DeadLine</th><th>Operation</th>
			</tr>
			<?php
			$y=1;
				foreach ($thrdarr as $value) {
					$listid=$value['id'];
					$queryfour=$this->db->query("SELECT * FROM record_operations where list_id='".$listid."'");
					$startrec=($queryfour->num_rows()+1);
					$listid=$value['id'];
					$queryfive=$this->db->query("SELECT * FROM recordmaster where list_id='".$listid."' AND vertical_id='".$vid."'");
					$endrec=count($queryfive->result_array());
					$tmpvar="opt".$value['id'];
					$tmpvar1="endrec".$value['id'];
					$tmpvar2="startrec".$value['id'];
					$formid="formx".$value['id'];
					if(($startrec>=$endrec) && ($endrec>0)) continue;
			?>
			<tr>
			<form class="common" name="<?php echo $formid; ?>" id="<?php echo $formid; ?>" novalidate="false" action="/lead_teamlead/assignliststointerns/<?php echo $id;?>" method="POST" accept-charset="utf-8">
				<input type="hidden" name="list_id" value="<?php echo $value['id']; ?>">
				<input type="hidden" name="v_id" value="<?php echo $vid; ?>">
				<td>
					<?php echo $y++;?>
				</td>
				<td>
					<?php echo $value['lname'];?>
				</td>
				<?php  ?>
				<td><select name="<?php	echo $tmpvar; ?>" id="<?php	echo $tmpvar; ?>" class="num" onblur="check_selectintern(this.value)">
				<option value="">Select Intern</option>
					<?php
					//following query lists all UNASSIGNED interns associated with this TL
					$queryforinterns=$this->db->query("SELECT u.user_name FROM users u, tl_interns t WHERE (t.intern_id=u.user_id) AND t.tl_id='".$id."' AND t.intern_id NOT IN (SELECT assigned_intern_id from record_operations)");		$tres=$queryforinterns->result_array();
						foreach ($tres as  $vdx) { ?>
						<!--Name of each option is "opt" append with list_id-->
							<option value="<?php echo $vdx['user_name'];?>" ><?php echo $vdx['user_name'];?>
							</option>
						<?php }
						?>
					</select>
				</td>
				<td>
					<input type="text" name="<?php	echo $tmpvar2; ?>" value="<?php echo $startrec; ?>"  readonly>
				</td>
				<td>
					<input type="text" name="<?php echo $tmpvar1; ?>" id="<?php echo $tmpvar1; ?>" value="<?php echo $endrec; ?>" onchange="check_endrec(this.value,<?php echo $endrec; ?>)">
				</td>
				<td>
					<input type="text" id="datepicker" name="deadline" value="" placeholder="">
				</td>
				<td>
					<input type="submit" name="submit" id="submit" value="Assign Now"/>
				</td>
			</form>
			</tr>

			<?php } ?>
		</table>
		</div>

		<?php
		$querymn=$this->db->query("SELECT * FROM intern_lists ORDER BY date DESC");
		$resvar=$querymn->result_array();
		?>
		<div class="bottom" id="o_b_div" style='overflow-y: auto; width:auto;height:400px;'>
			<table class="sortable">
				<thead>
					<tr>
						<th>Serial #</th><th>List Name</th><th> Assigned Intern</th><th>From </th><th>Upto </th><th>Date Assigned</th><th>ReAssign</th>
					</tr>
				</thead>
				<tbody>
				<tr> <!-- Filteration -->
					<td></td>
					<td>
						<input type="text" name="list_filter" id="list_filter" onchange="filter_list_by_name(this.value)"/>
					</td>
					<td>
						<input type="text" name="intern_filter" id="intern_filter" onchange="filter_list_by_intern(this.value)"/>
					</td>
					<td></td><td></td><td></td><td></td>
				</tr>
			<?php $i=1;
				foreach ($resvar as $value) { ?>
				<tr>
					<td><?php echo $i++; ?></td>
					<td><?php echo $value['list_name']; ?> </td>
					<td><?php echo $value['intern_name']; ?> </td>
					<td><?php echo $value['start_rec']; ?> </td>
					<td><?php echo $value['end_rec']; ?> </td>
					<td><?php echo $value['date']; ?> </td>
					<td><a class='iframe1' href='/lead_teamlead/reassignview/<?php echo $value['intern_name']; ?>/<?php echo $value['list_name']; ?>/' >ReAssign</a></td>
				</tr>
			<?php } ?>
				</tbody>
			</table>
		</div>

	</body>
</html>