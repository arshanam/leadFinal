<?php
session_start();
$vid=$_SESSION['ses_vid'];
$q="select * from listmaster where list_id='$listid' and vertical_id='$vid'";
$res=$this->db->query($q);
$rw=$res->result_array();
?>
<html>
<head>
	<meta charset="utf-8">
	<title>Edit List</title>
	<script src="<?php echo static_files_url(); ?>/js/jquery.js" type="text/javascript"></script>
	<script src="<?php echo static_files_url(); ?>/js/jquery.validate.js" type="text/javascript"></script>
  	<script>
	//list name checking
	$(document).ready(function(){
			$("#listname").change(function(){
				a=$("#listname").val();
				$.ajax({url:"/lead_marketresearch/ajax_listname_check/",type:"post",data:"lname="+a,success:function(result)
					{
						if(result=="")
						{
							$("#submit").removeAttr('disabled','disabled');
						}
						else
						{
							$("#submit").attr('disabled','disabled');
						}
						$("#div1").html(result);
					}
				});
			});
		});
	
	//form validation
  	$(document).ready(function() {
    // Setup form validation on the #register-form element
    $("#listf").validate({

        // Specify the validation rules

        rules: {
            listname: {
            	required: true
            }
        },

        // Specify the validation error messages
        messages: {
            listname: "<br/><br/>Required<BR/>"
        },

        submitHandler: function(form) {
			//welcome();
            form.submit();
        }
    });

  });

  </script>
</head>
<body>
	<?php
	$form_attributes=array("name"=>"listf","id"=>"listf","novalidate"=>"false");
	echo form_open("/lead_marketresearch/lists_modify/editprocess",$form_attributes);
	echo form_hidden("listid",$rw[0]['list_id']);
	?>
		<table>
			<tr>
				<td>List Name</td>
				<td>
					<?php
					$data=array('name'=>'listname','id'=>'listname','value'=>$rw[0]['list_name']);
					echo form_input($data);
					?>
					<div class="err" id="div1"></div>
				</td>
			</tr>
			<tr>
				<td>List Comments</td>
				<td>
					<?php
					$data=array('name'=>'listcomment','value'=>$rw[0]['comment'],'rows'=>2,'cols'=>15);
					echo form_textarea($data);
					?>
				</td>
			</tr>
			<tr>
				<td>Active Status(1 for Active):</td>
				<td>
					<?php
					$options=array('0'=>'InActive','1'=>'Active');
					$tmpval=$rw[0]['status'];
					if($tmpval=="1")
						echo form_dropdown('listactive', $options, '1');
					else if($tmpval=="0")
						echo form_dropdown('listactive', $options, '0');
					?>
				</td>
			</tr>
			<tr>
				<td>
					<a href='/lead_marketresearch/backpage/vertical'>
						<img src="<?php echo static_files_url(); ?>images/goback.png" height="25" width="50"/>
					</a>
				</td>
				<td>
					<?php
					$atr=array("name"=>"submit","id"=>"submit");
					echo form_submit($atr,'UPDATE');
					?>
				</td>
			</tr>
		</table>
	<?php
	echo form_close();
	?>
</body>
</html>