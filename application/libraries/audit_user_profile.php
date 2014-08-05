<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * This class has functions that retrieve information about the user's
 * profile.
 *
 * @author     Shankar
 */
class Audit_user_profile
{

    private $CI;

    function __construct()
	{
        $this->CI = & get_instance();
    }

   
    /**
     * Builds and returns the user profile array
     *
     * @return Array
     */
    function audit_user_build_profile($user_id)
	{
		$session_user=array();
		$this->CI->load->model('audit_users');
		$user_obj = new Audit_users();
		$user=$user_obj->getUser($user_id);
		$this->CI->load->model('audit_user_roles');
		$this->CI->load->model('audit_files');
		if($user)
		{
			$roles=$this->CI->audit_user_roles->get_by_user_role_id($user->user_role_id);
			$session_user['audit_user_id']=$user->user_id;
			$session_user['audit_user_fname']=$user->user_fname;
			$session_user['audit_user_lname']=$user->user_lname;
			$session_user['audit_user_email']=$user->user_email;
			$session_user['audit_user_client_id']=$user->client_id;
			$session_user['audit_user_parent_id']=$user->user_parent_id;
			$session_user['audit_user_city']=$user->user_city;
			$session_user['audit_user_state']=$user->user_state;
			$session_user['audit_user_account_type']=$user->user_account_type;
			$session_user['audit_client_id']=$user->client_id;
			$session_user['audit_user_job_title']=$user->job_title;
			$session_user['audit_user_role_id']=$user->user_role_id;
			 if($roles)
			 {
				$session_user['audit_user_role_name']=$roles->role_name;
				$session_user['audit_user_module_read_ids']=$roles->module_read;
				$session_user['audit_user_module_write_ids']=$roles->module_write;
				if(!empty($roles->module_read)){
				$modread=explode(",",$roles->module_read);
				$module_read=$this->CI->audit_files->getModuleNameByModule_Id('module_id',$modread);
				}
				else
				{
				$module_read=array();
				}
				if(!empty($roles->module_write)){
				$modwrite=explode(",",$roles->module_write);
				$module_write=$this->CI->audit_files->getModuleNameByModule_Id('module_id',$modwrite);
				}
				else
				{
				$module_write=array();
				}
				$session_user['audit_user_module_read']=$module_read;
				$session_user['audit_user_module_write']=$module_write;
			 }
		}
		
		return $session_user;
				
		
    }

    /**
     * Re-creates the user's profile and updates the session if needed
     *
     * @param int
     * @return BOOLEAN
     */
    function audit_set_session($user_id) {
        $user_data = $this->audit_user_build_profile($user_id);
        $this->CI->session->set_userdata(array('audit_user' => $user_data));

        return TRUE;
    }
    
}