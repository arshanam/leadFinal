<?php
/**
* This Class processes the sql queries
*/
class Login_model extends MY_Model
{
	public $table = "users";
	private $CI;


	function __construct()
	{
		$this->primary_keys = array('user_id');
        parent::__construct();

        $this->CI = & get_instance();
	}

	public function verify($login, $pass)
	{
		$query = $this->db->query("SELECT * FROM users WHERE (lower(email)='".addslashes(strtolower($login))."' OR email='".addslashes(strtolower($login))."') AND password='".addslashes($pass)."' AND user_active='0'");
		$row = $query->row();
		if($query->num_rows() != 1)
			{
				return 0;
			}

		else return 1;

	}
	//This function fetches and returns entire row contents
	public function fetchval($login)
	{
		$q1 = "SELECT DISTINCT u.user_id,u.first_name,u.last_name,u.user_name,u.email,u.user_date_added,u.user_date_modified,u.user_active,u.password,u.phone,u.department_id,u.role_id,d.department_name,r.role_name,u.is_superadmin,u.is_admin FROM users u, roles r, department d WHERE u.department_id=d.department_id AND u.role_id=r.role_id AND lower(u.email)='".addslashes(strtolower($login))."'";
		$res1 = $this->db->query($q1);
		$r1 = $res1->result_array();
		if($r1[0]['role_id']!=0)
		{
			$rid=$r1[0]['role_id'];
			$q2="SELECT * FROM role_permissions WHERE role_id='".$rid."'";
			$res2=$this->db->query($q2);
			$r2=$res2->result_array();
			$pid=explode(",",$r2[0]['permission_id']);
			$pname=array();
			foreach($pid as $val)
			{
				$q3="SELECT * FROM permissions WHERE permission_id='".$val."'";
				$res3=$this->db->query($q3);
				$r3=$res3->result_array();
				array_push($pname,$r3[0]['module_name']);
			}
			$r1[0]['permissions']=$pname;
		}
		return $r1[0];
	}
}

?>