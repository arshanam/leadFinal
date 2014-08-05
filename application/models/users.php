<?php
/**
 * This model is related to the table Supercoder_dev.users. It has functions for accessing (saving, deleting, updating) data in users.
 *
 * @author     Pawan Kumar Verma
 */
class Users extends MY_Model {

    /**
     * Table used by this model
     * @var string
     */
    public $table = "users";

    /**
     * Instance of the CodeIgnitor object
     * @var object
     */
    private $CI;

    /**
     * Category model constructor
     */
    function __construct() {
        $this->primary_keys = array('user_id');
        parent::__construct();
        $this->CI = & get_instance();
    }

	function initialize($param1)
	{
		if (empty($param1))
		{
			return FALSE;
		}

		// Query data from database
		$query = $this->where('lower(user_name)', strtolower($param1))->or_where('lower(email)', strtolower($param1))->or_where('user_id', $param1)->get();
		return $query;
	}

	/**
	 * Gets the full name of the user
	 *
	 * @return string
	 */
	function get_full_name()
	{
		return trim($this->first_name . ' ' . $this->last_name);
	}

	/**
	 * Gets the possessive full name of the user
	 *
	 * @example Kyle Gifford -> Kyle Gifford's
	 * @example Brian Thomas -> Brian Thomas'
	 * @return string
	 */
	function get_possessive_full_name()
	{
		$full_name = $this->get_full_name();
		$postfix = (substr($full_name, -1) == 's') ? "'" : "'s";
		return $full_name . $postfix;
	}

	/**
	 * Checks if an email address is available (unique)
	 *
	 * @param string $email
	 * @param mixed $excluded_user_ids
	 * @return bool true if the email is available (no results found)
	 */
	function is_email_available($email, $excluded_user_ids = array())
	{
		
		if ($excluded_user_ids)
		{
			if (!is_array($excluded_user_ids))
			{
				$excluded_user_ids = array($excluded_user_ids);
			}
			$this->where_not_in('user_id', $excluded_user_ids);
		}
		$email=addslashes(trim(strtolower($email)));
	
		$this->where('active !=',3);
		$result = $this->where('email',$email)->count();
		return (bool)!$result;
	}
	function is_trial_user_available($username, $excluded_user_ids = array())
	{
		
		if ($excluded_user_ids)
		{
			if (!is_array($excluded_user_ids))
			{
				$excluded_user_ids = array($excluded_user_ids);
			}
			$this->where_not_in('user_id', $excluded_user_ids);
		}
		$username=addslashes(trim(strtolower($username)));
	
		$this->where('active !=',3);
		$result = $this->where('user_name',$username)->count();
		return (bool)!$result;
	}
	function is_email_available_create($email, $excluded_user_ids = array())
	{
	
		if(!empty($email))
			{
			
				$email=trim(strtolower($email));
				$sql = "select user_id from users where email='$email' and active!=3 limit 1";
				$query = $this->db->query($sql);
				$result=$query->result_array();
				if(isset($result[0]) && count($result[0])>0 && !empty($result[0]['user_id']))
					{
						return false;
			
					}
			}
		return true;
	}
	function is_email_available_new($email)
	{
		if(!empty($email))
			{
				$email=trim(strtolower($email));
				$sql = "select user_id from users where email='$email' limit 1";
				$query = $this->db->query($sql);
				$result=$query->result_array();
				if(isset($result[0]) && count($result[0])>0)
					{
						return $result[0]['user_id'];
			
					}
			}
			return false;
	}

	/**
	 * Checks if an email address is available (unique)
	 *
	 * @param string $email
	 * @param mixed $excluded_user_ids
	 * @return bool true if the email is available (no results found)
	 */
	function is_email_exists($email, $excluded_user_ids = array())
	{
		if ($excluded_user_ids)
		{
			if (!is_array($excluded_user_ids))
			{
				$excluded_user_ids = array($excluded_user_ids);
			}
			$this->where_not_in('user_id', $excluded_user_ids);
		}

		$result = $this->where('LOWER(email)', strtolower($email))->count();
		return (bool)$result;
	}
	
	
	/**
	 * Checks if an email address is available (unique)
	 *
	 * @param string $email
	 * @param mixed $excluded_user_ids
	 * @return bool true if the email is available (no results found)
	 */
	function is_active_email_exists($email, $excluded_user_ids = array())
	{
		if ($excluded_user_ids)
		{
			if (!is_array($excluded_user_ids))
			{
				$excluded_user_ids = array($excluded_user_ids);
			}
			$this->where_not_in('user_id', $excluded_user_ids);
		}

		$result = $this->where('LOWER(email)', strtolower($email))->where_in('active', array('0','1'))->count();
		return (bool)$result;
	}

	/**
	 * Checks if an username is available (unique)
	 *
	 * @param string $username
	 * @param mixed $excluded_user_ids
	 * @return bool true if the username is available (no results found)
	 */
	function is_username_available($username, $excluded_user_ids = array())
	{
		if ($excluded_user_ids)
		{
			if (!is_array($excluded_user_ids))
			{
				$excluded_user_ids = array($excluded_user_ids);
			}
			$this->where_not_in('user_id', $excluded_user_ids);
		}

		$result = $this->where('LOWER(user_name)', strtolower($username))->count();
		return (bool)!$result;
	}
	
	/**
	 * Checks if an username is available (unique)
	 *
	 * @param string $username
	 * @param mixed $excluded_user_ids
	 * @return bool true if the username is available (no results found)
	 */
	function is_active_username_available($username, $excluded_user_ids = array())
	{
		if ($excluded_user_ids)
		{
			if (!is_array($excluded_user_ids))
			{
				$excluded_user_ids = array($excluded_user_ids);
			}
			$this->where_not_in('user_id', $excluded_user_ids);
		}

		$result = $this->where('LOWER(user_name)', strtolower($username))->where_in('active', array('0','1'))->count();
		return (bool)!$result;
	}
	
	
	

	/**
	 * Returns data from the users database row.
	 *
	 * @return array
	 */
	function get_user_row()
	{
		$user_data = array();
		$user_data['user_id'] = $this->user_id;
		$user_data['user_name'] = $this->user_name;
		$user_data['password'] = $this->password;
		$user_data['email'] = $this->email;
		$user_data['active'] = $this->active;
		$user_data['date_joined'] = $this->date_joined;
		$user_data['last_login'] = $this->last_login;
		$user_data['first_name'] = $this->first_name;
		$user_data['last_name'] = $this->last_name;
		$user_data['designation'] = $this->designation;
		$user_data['company_name'] = $this->company_name;
		$user_data['company_type'] = $this->company_type;
		$user_data['ip'] = $this->ip;
		$user_data['role'] = $this->role;
		$user_data['telephone'] = $this->telephone;
		$user_data['cart'] = $this->cart;
		$user_data['newsletter'] = $this->newsletter;
		$user_data['shipping_address_id'] = $this->shipping_address_id;
		$user_data['billing_address_id'] = $this->billing_address_id;
		$user_data['user_group_id'] = $this->user_group_id;
		$user_data['referer'] = $this->referer;
		$user_data['user_type'] = $this->user_type;
		$user_data['user_sub_type'] = $this->user_sub_type;
		$user_data['user_sub_status'] = $this->user_sub_status;
		$user_data['date_modified'] = $this->date_modified;
		$user_data['last_login'] = $this->last_login;
		$user_data['last_session_id'] = $this->last_session_id;
		$user_data['role'] = $this->role;
		$user_data['parent_id'] = $this->parent_id;
		$user_data['adv_user_id'] = $this->adv_user_id;
		
		return $user_data;
	}
	function get_user_row_using_email($email)
	{
		$email=strtolower($email);
		$sql = "select * from users where email='$email' or user_name='$email' limit 1";
		$query = $this->db->query($sql);
		$result=$query->result_array();
		if(isset($result[0]) && count($result[0])>0)
			{
				return $result[0];
	
			}
			return false;
	}
	function get_user_data($user_id)
	{
		$sql = "select * from users where user_id=$user_id limit 1";
		$query = $this->db->query($sql);
		$result=$query->result_array();
		if(isset($result[0]) && count($result[0])>0)
			{
				return $result[0];
	
			}
			return false;
	}

	// Returns list of users
    function list_users($use_join=FALSE,$select='*', $where='', $sort='', $limit='', $offset='', $where_like='', $group_by='', $exclude='',$where_likeOR='') {

        $this->db->select($select)->from($this->table);
		if($use_join)
		{
			$this->db->join('user_login_log', 'user_login_log.user_id = users.user_id','LEFT');
			$this->db->join('users as parent', 'users.parent_id = parent.user_id','LEFT');
		}
        if ($where) {
			if(isset($where['role']))
			{
	            $this->db->_like('users.role',$where['role']);
			}
			else
			{
				$this->db->where($where);
			}
        }
		if ($where_likeOR) {
			foreach($where_likeOR as $field=>$search_text) {
				$this->db->_like("LOWER(".$field.")",strtolower($search_text),'OR');
			}
		}
		if ($where_like) {
			//$this->db->like($where_like);
			foreach($where_like as $field=>$search_text) {
				$this->db->_like("LOWER(".$field.")",strtolower($search_text));
			}			
        }
		if($exclude) {
			$this->db->where_not_in('users.user_id', $exclude);
		}
		if ($group_by) {
            $this->db->group_by($group_by);
        }
        if ($sort) {
            $this->db->order_by($sort);
        }
        if ($limit) {
            $this->db->limit($limit,$offset);
        }
        $query = $this->db->get()->result();
		//echo $this->db->last_query();die;
        return $query;
    }

	// Returns list of users
   public function list_corporate_users($use_join=FALSE,$select='*', $where='', $sort='', $limit='', $offset='', $where_like='', $group_by='', $exclude='',$where_likeOR='') {

        $this->db->select($select)->from($this->table);
		if($use_join)
		{
			//$this->db->join('user_login_log', 'user_login_log.user_id = users.user_id','LEFT');
			//$this->db->join('users as parent', 'users.parent_id = parent.user_id','LEFT');
		}
        if ($where) {
			if(isset($where['role']))
			{
	            $this->db->_like('users.role',$where['role']);
			}
			else
			{
				$this->db->where($where);
			}
        }
		$this->db->where(array('users.user_sub_type'=>'Corporate'));
		if ($where_likeOR) {
			foreach($where_likeOR as $field=>$search_text) {
				$this->db->_like("LOWER(".$field.")",strtolower($search_text),'OR');
			}
		}
		if ($where_like) {
			//$this->db->like($where_like);
			foreach($where_like as $field=>$search_text) {
				$this->db->_like("LOWER(".$field.")",strtolower($search_text));
			}			
        }
		if($exclude) {
			$this->db->where_not_in('users.user_id', $exclude);
		}
		if ($group_by) {
            $this->db->group_by($group_by);
        }
        if ($sort) {
            $this->db->order_by($sort);
        }
        if ($limit) {
            $this->db->limit($limit,$offset);
        }
        $query = $this->db->get()->result();
		//echo $this->db->last_query();die;
        return $query;
    }

	public function inactive($id)
	{
		$sql = " Update users set active=0 where user_id = $id ";

		$result = $this->db->query($sql);
		return (bool)$result;
	}

	public function active($id)
	{
		$sql = " Update users set active=1 where user_id = $id ";

		$result = $this->db->query($sql);
		return (bool)$result;
	}

	public function get_user_address($address_field,$user_id)
	{
		$sql = "select A.* from address A join users B on B.$address_field=A.address_id where B.user_id=$user_id limit 1";
		$query = $this->db->query($sql);
		return $query->result_array();;
	}
	public function get_user_payment_profile_data($user_id)
	{
		$sql = "select * from user_payment_profile where user_id=$user_id limit 1";
		$query = $this->db->query($sql);
		return $query->result_array();;
	}

	public function update_billing_address_id($id,$billing_address_id)
	{
		$sql = " Update users set billing_address_id=$billing_address_id where user_id = $id ";

		$result = $this->db->query($sql);
		return (bool)$result;
	}

	public function update_shipping_address_id($id,$shipping_address_id)
	{
		$sql = " Update users set shipping_address_id=$shipping_address_id where user_id = $id ";

		$result = $this->db->query($sql);
		return (bool)$result;
	}

	public function get_searable_data($search_text, $search_field, $fields, $sort='', $limit='', $offset='')
	{
		//echo $search_field."##".$search_text;die;
		$this->db->select('*')->from($this->table);
		//$this->db->join('address', 'address.user_id = users.user_id','LEFT');
		if($search_field=== 'all') {
			unset($fields['all']);
			foreach($fields as $field=>$field_desc) {
				$this->db->_like($field,$search_text,'OR');
			}
		} else {
			$this->db->_like($search_field,$search_text);
		}
		if ($limit) {
            $this->db->limit($limit,$offset);
        }
		return $query = $this->db->get()->result();
	}

	/**
	* function to get all subusers of a user
	*
	*/
	public function get_subusers($user_id)
	{
			$this->db->select('user_id,first_name,last_name,user_name,email')->from($this->table)->where("parent_id",$user_id);

			$query = $this->db->get()->result_array();

			if(is_array($query))
			{
				return $query;
			}

	}

	/**
	* function to get all subusers
	*
	*/
	public function list_subusers($select='*',$where='',$where_in='',$sort='',$limit='',$offset='',$exclude_ids='')
	{
		$this->db->select($select)->from($this->table);
		if($where) {
			$this->db->where($where);
		}
		if($where_in) {
			$this->db->where_in("parent_id",$where_in);
		}
		if($sort) {
			$this->db->order_by($sort);
		}
		if($exclude_ids) {
			$this->db->where_not_in("parent_id",$exclude_ids);
		}
		if($limit) {
			$this->db->limit($limit,$offset);
		}

		$query = $this->db->get()->result_array();

		if(is_array($query))
		{
			return $query;
		}
		return '';
	}

	public function get_rep_id($user_id)
	{
		$sql = "select rep_id,other_specified as rep from users_extra where user_id=$user_id and heard_us='Sales Agent' limit 1";
		$query = $this->db->query($sql);
		return $query->result_array();;
	}


   	/**
     * Defines which model attributes are safe/allowed to be populated from post automatically.
     *
     * @return array
     */
    public function safeAttributes() {
        return array(
            'user_id',
			'first_name',
			'last_name',
            'email',
            'user_name',
			'telephone',
			'password',
			'street',
			'city',
			'state',
			'zip',
			'country',
			'is_male',
			'cart',
			'newsletter',
            'shipping_address_id',
            'billing_address_id',
			'active',
			'employee',
			'ip',
			'status',
			'aff_id',
			'is_affiliate',
			'aff_payout_type',
			'unsubscribed',
			'email_verified',
			'security_code',
			'securitycode_expire',
			'date_joined',
            'referer',
            'user_type',
			'date_modified',
			'last_login',
			'last_session_id',
			'role',
			'adminuser_group_id',
			'parent_id',
			'subusers_group_id',
			'designation',
			'company_name',
			'company_type',
			'current_sess_id',
			'user_sub_type',
			'user_sub_status',
			'ignore_sess_id',
			'user_login_time',
			'adv_user_id'
		);
    }
}

/* End of file users.php */
/* Location: ./application/models/users.php */