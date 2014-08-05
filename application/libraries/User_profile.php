<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * This class has functions that retrieve information about the user's
 * profile.
 *
 * @author     Pawan
 */
class User_profile
{

    private $CI;

    function User_profile()
	{
        $this->CI = & get_instance();
    }

    /**
     * Returns the user profile from the session. If expired, it rebuilds
     * the user profile, updates the session, then returns it.
     *
     * @return Array
     */
    function get_user_profile()
	{
		$user_data = $this->CI->session->userdata('user');
        $user_id = isset($user_data['user_id']) ? $user_data['user_id'] : 0;
		//echo "<pre>"; echo $user_id; print_r($user_data); exit;
        if ($user_id) {
            $interval = $this->CI->config->item('session_refresh_interval');
            $time_now = time();
            $time_refreshed = $this->CI->session->userdata('user_data_refresh');

            if (($time_now - $time_refreshed) > $interval) {
                $this->CI->session->set_userdata(array('user_data_refresh' => time()));
                $this->refresh_user_session($user_id);
            }

            return $user_data;
        } else {
            return FALSE;
        }
    }

    /**
     * Builds and returns the user profile array
     *
     * @return Array
     */
    function build_user_profile($param)
	{
		$data = array('user_id'=>$param);
		//$user_data = json_decode($this->CI->api_call('index',$data));
		$this->CI->load->model('Users');
		$user_obj = new Users();
		$role_perm_obj = new Role_perms();
		$adminuser_group_perm_obj =  new Adminuser_group_perms();

		$user_db_data = $user_obj->list_users(FALSE,"*",$data);

		if(!empty($user_db_data))
		{

			if(!empty($user_db_data) && is_array($user_db_data))
			{
				$user_data = (array)$user_db_data[0];
			}
			else
			{
				$user_data = array();
			}
			$user_role_perms = array();
			if(!empty($user_data))
			{

				// Check if user exists in mybb
				/*$forum_user_data = $this->CI->db->get_where('bb_forum.mybb_users',array('username'=>$user_data['user_name']))->result();
				if(!empty($forum_user_data)) {
					$user_data['coding_911_access'] = TRUE;
				} else {
					$user_data['coding_911_access'] = FALSE;
				}*/

				// Get user's role
				$user_roles = explode(",",$user_data['role']);
				// Get user's role permistions

				if(!empty($user_roles)) {
					$role_perm_obj->select('*');
					$role_perm_obj->where_in('role_id',$user_roles);
					$user_role_perm_data = $role_perm_obj->get();
					//$role_perm_obj->check_last_query();exit;
					if(!empty($user_role_perm_data)) {
						foreach($user_role_perm_data as $role_perm_data) {
							$user_role_perms[] = $role_perm_data->stored;
						}
					}
					$role_perm_obj->clear();
					/*foreach($user_roles as $role)
					{
						$user_role_perms[]=$role_perm_obj->list_role_perms("*",array('role_id'=>$role));
					}*/
				}
				$user_data['role_perms'] = $user_role_perms;
				/*  ------------------ Get user permission and merge them with roles permissions ----------------  */
				$user_perm_obj = new User_perms();
				$user_perm_data = $user_perm_obj->list_user_perms('*',array('user_id'=>$param));
				/* Code Added by Sunil Punyani  for new Medallian Logic */
				if(is_array($user_db_data) && count($user_db_data)>0  && !empty($user_db_data[0]->user_sub_type) && ($user_db_data[0]->user_sub_type=='Subuser' || $user_db_data[0]->user_sub_type=='Corporate' || $user_db_data[0]->user_sub_type=='Medallian'))
					{
						$query_subuser_access=$this->CI->db->query("select group_concat(distinct(p.master_product_ids)) as master_ids,
													group_concat(distinct(p.product_id)) as prdouct_ids from subuser_product sp 
													join products p on p.product_id=sp.product_id where sp.user_id='$param' and sp.status=1 group by sp.user_id");
						if(count($query_subuser_access->result_array())>0)
							{
								$result_access=$query_subuser_access->result_array();
								if(is_array($result_access) && count($result_access)>0)
									{
										
										$result_access[0]['master_ids'];
										$result_access[0]['prdouct_ids'];
										$pids='';
										$mids='';
										if(!empty($result_access[0]['prdouct_ids']))
											{
												$pids=$user_perm_data[0]->product_ids.','.$result_access[0]['prdouct_ids'];
												$user_perm_data[0]->product_ids=trim($pids,',');
											}
										if(!empty($result_access[0]['master_ids']))
											{
												$mids=$user_perm_data[0]->master_product_ids.','.$result_access[0]['master_ids'];
												$user_perm_data[0]->master_product_ids=trim($mids,',');
											}
									}
							}
					}
					/* Code Ended by Sunil Punyani*/
				if($user_perm_data) {
					$user_data['role_perms'] = array_merge($user_role_perms,$user_perm_data);
				}

				// Get user's adminuser group
				// Get uers's adminuser group permissions
				$user_data['adminuser_perms'] = array();
				if($user_data['employee'] == 1) {
					if($user_data['adminuser_group_id'] >0) {
						$user_group_perms = $adminuser_group_perm_obj->list_user_group_perms('*',array('user_group_id'=>$user_data['adminuser_group_id']));
						$admin_user_perms = (!empty($user_group_perms) ? unserialize($user_group_perms[0]->permissions) : array());
						if(!empty($user_group_perms)) {
							$user_data['adminuser_perms']['read'] = array();
							$user_data['adminuser_perms']['write'] = array();
							if(isset($admin_user_perms['read'])) {
								foreach($admin_user_perms['read'] as $key=>$value) {
									$user_data['adminuser_perms']['read'][$key] = $value['path'];
								}
							}
							if(isset($admin_user_perms['write'])) {
								foreach($admin_user_perms['write'] as $key=>$value) {
									$user_data['adminuser_perms']['write'][$key] = $value['path'];
								}
							}
						}
					}
				}
					if($this->CI->session->userdata('user_home_pref'))
						{
							$this->CI->session->unset_userdata('user_home_pref');
						}

					$query_home_page=$this->CI->db->query("select * from user_preference where user_pref='customize_home_page' and user_id='$param' limit 1");
					if(count($query_home_page->result_array())>0)
						{
							$result_home=$query_home_page->result_array();
							if(is_array($result_home) && count($result_home)>0)
								{
									$this->CI->session->set_userdata('user_home_pref',$result_home[0]['user_pref_value']);
								}
						}
					$query_check=$this->CI->db->query("select * from subuser_product where user_type='Subgroup' and user_id='$param' and status=1 limit 1");	
					if(count($query_check->result_array())>0)
						{
							$result_subuser=$query_check->result_array();
							if(is_array($result_subuser) && count($result_subuser)>0)
								{
									if(isset($result_subuser[0]['id']) && !empty($result_subuser[0]['id']))
										{
											$this->CI->session->set_userdata('allow_subuser','1');
										}
									//$this->CI->session->set_userdata('allow_subuser','1');
								}
						}
					
					$query_search_pref=$this->CI->db->query("select user_search_pref from user_search_preference where user_id='$param' limit 1");
					if(count($query_search_pref->result_array())>0)
						{
							$result_search_pref=$query_search_pref->result_array();
							if(is_array($result_search_pref) && count($result_search_pref)>0)
								{
									$user_data['user_search_pref'] = $result_search_pref[0]['user_search_pref'];
								}else
								{
									$user_data['user_search_pref'] = '';
								}
						}
						
				//$user_data['adminuser_perms'] = (!empty($user_group_perms) ? unserialize($user_group_perms[0]->permissions) : array());

				//echo "<pre>"; print_r($user_data); die('user_profile'); //return $user_data;
					$this->CI->session->set_userdata('user',$user_data);
					if(is_array($user_data) && count($user_data)>0)
						{
							$query=$this->CI->db->query("select * from user_questions_access where user_id='$param'");
							if(count($query->result_array())>0)
								{
									$result=$query->result_array();
									if(is_array($result) && count($result)>0)
										{

											$this->CI->session->set_userdata('total_allowed_questions',$result[0]['total_questions']);
											$this->CI->session->set_userdata('question_start_time',$result[0]['subs_start_date']);
											$this->CI->session->set_userdata('question_end_time',$result[0]['subs_end_date']);

										}
								}

						}


				//print_r($this->CI->session->userdata('user')); exit;
				return $user_data;
			}
		} else {
            return array();
        }
    }

    /**
     * Re-creates the user's profile and updates the session if needed
     *
     * @param int
     * @return BOOLEAN
     */
    function refresh_user_session($user_id) {
        $user_data = $this->build_user_profile($user_id);
        $this->CI->session->set_userdata(array('user' => $user_data));
        $this->CI->load_user_profile();

        return TRUE;
    }

    /**
     * Returns array of each user permission based on the user data.
     *
     * @return array
     */
    function get_user_permissions($user_data) {
        $permissions = array();
		if(isset($user_data['role'])) {
			$user_role = $user_data['role'];
		} else if(isset($user_data->role)) {
			$user_role = $user_data->role;
		} else {
			$user_role = '';
		}
        switch ($user_role) {
            case 'ADMIN':
                $permissions['admin'] = TRUE;
                $permissions['admin_users'] = TRUE;
                break;

            case 'CUSTOMER_SERVICE':
                $permissions['admin'] = TRUE;
                break;

            case 'MODERATOR':

                break;

            case 'USER':

                break;

            case 'MERCHANT':

                break;
            default:
        }

        $permissions['active'] = (isset($user_data['active']) && $user_data['active']) ? TRUE : FALSE;
        $permissions['employee'] = (isset($user_data['employee']) && $user_data['employee']) ? TRUE : FALSE;
        $permissions['admin'] = (isset($user_data['employee']) && $user_data['employee']) ? TRUE : FALSE;

		return $permissions;
    }

    /**
     * Returns array of each user subscribed features based on the active
     * subscriptions.
     *
     * @return array
     */
    function get_user_features($user_id) {
        $features = array();
        return $features;
    }

    /**
     * Hash password using sha1 plus seed
     *
     * @param1 string, password to be hashed
     * @return bool  TRUE if $_SERVER['APP_MODE'] is 'prod' otherwise FALSE
     */
    function hash_password($password) {

        $hash_seed = $this->CI->config->item('hash_seed');
        return sha1($hash_seed . $password);
    }


    /**
     * Accepts two string value: unencrypted (raw) password and legacy password salt.
     * Returns a hash that exactly matches the legacy password value stored in the db.
     *
     * @return STRING
     */
    function legacy_hash_password($raw_password, $legacy_salt) {

        /* .NET Code:
          byte[] data = Encoding.Unicode.GetBytes(val);
          byte[] saltData = Convert.FromBase64String(salt);
          byte[] buffer = new byte[((saltData.Length + data.Length))];
          byte[] result;
         */
        //$salt = (binary) base64_decode($legacy_salt);
		$salt = '';
        $password = mb_convert_encoding($raw_password, 'UTF-16LE', 'auto');


        /* .NET Code:
          Buffer.BlockCopy(saltData, 0, buffer, 0,               saltData.Length);
          Buffer.BlockCopy(data,     0, buffer, saltData.Length, data.Length);
         */
        $hash_string = $salt . $password;


        /* .NET Code:
          using (SHA1 sha = new SHA1Managed())
          {
          [...]

          result = sha.ComputeHash(buffer);
          }
         */
        $raw_hash = sha1($hash_string, TRUE);


        /* .NET Code:
          return Convert.ToBase64String(result);
         */
        $hash = base64_encode($raw_hash);
        return $hash;
    }

    /**
     * Generate 7 char token
     *
     * @return string, unique 7-character token
     */
    function generate_token() {
        $unique = FALSE;

        while (!$unique) {

            //create a token 20 chars long and the check to see if it already exists.
            $token = substr(str_replace(array('l','1','i'), '', sha1(microtime())), 0, 7);

            $query = $this->CI->db->get_where('tokens', array('value' => $token));
            if ($query->num_rows() == 0) {
                $unique = TRUE;
            }
        }

        return $token;
    }

    /**
     * Get token
     *
     * @param string, $value - 7 character token value
     * @return array with token data or boolean (FALSE) if token could not be found
     */
    function get_token($value) {
        if (!$value) {
            return FALSE;
        }

        $query = $this->CI->db->get_where('tokens', array('value' => $value));
        if ($query->num_rows() == 0) {
            return FALSE;
        }

        return $query->row();
    }

    /**
     * Get token
     *
     * @param string, $type - Token type
     * @param int, $user_id - User id
     * @return array with token data or boolean (FALSE) if token could not be found
     */
    function get_token_by_type($type, $user_id) {
        $query = $this->CI->db->get_where('tokens', array('type' => $type, 'user_id' => $user_id));

        if ($query->num_rows() == 0) {
            return FALSE;
        }

        return $query->row();
    }


    /**
     * Update token status
     *
     * @param int, $token_id - token id
     * @param string, $status - token status value ('PROCESSED', 'PENDING').
     */
    function update_token_status($token_id, $status) {
        $this->CI->db->where('token_id', $token_id);
        $this->CI->db->update('tokens', array('status' => $status));
    }

    /**
     * Sets the user's last login to current date and time
     * @param int $user_id
     * @return bool
     */
    public function update_last_login($user_id) {
		$user_model = new Users();
		$time = time();
        return $user_model->where('user_id', $user_id)->update('last_login', $time);
		return TRUE;
    }
}