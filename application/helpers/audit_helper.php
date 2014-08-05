<?php
function get_state_name($id){
	
	$CI = get_instance();
	$CI->db->select('name')->from("states");
	$CI->db->where('id',$id);
	$result =	$CI->db->get()->result_array();
	
	if($result)
		return $result[0]['name'];
	else
		return false;
		 
}

function get_role_name($id){
	
	
	$CI = get_instance();
	$CI->db->select('role_name')->from("audit_user_role");
	$CI->db->where('role_id',$id);
	$result =	$CI->db->get()->result_array();
	
	if($result)
		return $result[0]['role_name'];
	else
		return false; 
}

function get_credential_name($id){
	
	
	$CI = get_instance();
	$CI->db->select('cre_name')->from("audit_user_credentials");
	$CI->db->where('credential_id',$id);
	$result =	$CI->db->get()->result_array();
	
	if($result)
		return $result[0]['cre_name'];
	else
		return false;
}

/**
 * Checks if a given user has access to Audit area
 *
 * @param int $user_id
 * @param int filename, action
 * @return bool
 */
function has_audit_access($permission='read', $action='index',$filename, $permission_type='admin') {
	$CI =& get_instance();
    $current_user = $CI->session->userdata('audit_user');
	$module_reads=$current_user['audit_user_module_read'];
	/*print_r($module_reads);
	exit;*/
	$module_writes=$current_user['audit_user_module_write'];
	if(!empty($permission)) {
		
		switch($permission) {
			case 'read':
				if(!empty($filename)) {
					if(in_array($filename,$module_reads))
					{
						return true;
					}
					else
					 return false;
					
				}
				else
				{
					return false;
				}
				
			break;
			case 'write':
				if(!empty($filename)) {
					if(in_array($filename,$module_writes))
					{
						return true;
					}
					else
					 return false;
					
				}
				else
				{
					return false;
				}
			break;
		}

		
	} else {
		return FALSE;
	}
}
/*check sesssion audit_user_id*/
function check_audit()
{
	$CI = get_instance();
	if(isset($_SESSION['audit_user']))
	{
		 $current_user = $CI->session->userdata('audit_user');
		 if(empty($current_user['audit_user_id']))
		 {
			$CI->add_success("Login Here to Access this feature",true);
			redirect(current_base_url().'audit_login');
		 }
	}
	else
	{
			$CI->add_success("Login Here to Access this feature",true);
			redirect(current_base_url().'audit_login');
	}
}



function split_file($file_id, $dos_id)
{	
	$CI = get_instance();
	$quality=90;
	$res='300x300';
	//$uploadedFileName = $this->getDocumentName($file_id);
	//die("1234567--->".$uploadedFileName);
	$CI->db->where('medical_record_id',$file_id);
	$res=$CI->db->get('audit_medical_records');
	$row=$res->row();
	$uploadedFileName = $row->filename;
	$path = '/var/www/html/uataapc/webroot/audit/audit_documents/';
	//$path = '/var/www/html/uataapc/webroot/audit/audit_documents/';
	$pdf=$path.$uploadedFileName;
	//die("1234567--->".$pdf);
	//$pdf=$path.'TableofContents_final11072013.pdf';
	$quality=90;
	$res='300x300';
	mkdir($path."file_".$file_id,0777,true);
	$exportPath=$path."/file_".$file_id."/file_%d.jpg"; 
	exec("'gs' '-dNOPAUSE' '-sDEVICE=jpeg' '-dUseCIEColor' '-dTextAlphaBits=4' '-dGraphicsAlphaBits=4' '-o$exportPath' '-r$res' '-dJPEGQ=$quality' '$pdf'",$output);
	
	$scanDir	=	scandir($path."/file_".$file_id."/");
	
	unset($scanDir[0]);unset($scanDir[1]);
	
	//print_r($scanDir);die;
	
	for($i=0;$i<count($scanDir);$i++)
	{
		$fileId = $i+1;
		$file_part_data=array("part_id"=>$dos_id,"split_name"=>"file_".$fileId.".jpg");
		$CI->db->insert('audit_medical_records_split_files',$file_part_data);
	}
}
