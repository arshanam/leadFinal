<?php
class Model_permission extends MY_Model
{
	private $CI;
	//var $table='users';
	public function __construct()
	{
        $this->primary_keys = array('user_id');
        parent::__construct();
        $this->CI = & get_instance();
    }
	//pagination functions
	public function get_count($a)
	{
		if($a=='user')
		{
			$res_arr=$this->showuser();
			$count=count($res_arr);
			return $count;
		}
		else if($a=='role')
		{
			$res_arr=$this->showrole();
			$count=count($res_arr);
			return $count;
		}
		else if($a=='department')
		{
			$res_arr=$this->showdepartment();
			$count=count($res_arr);
			return $count;
		}
		else if($a=='permission')
		{
			$res_arr=$this->showpermission();
			$count=count($res_arr);
			return $count;
		}
		else if($a=='vertical')
		{
			$res_arr=$this->showverticals();
			$count=count($res_arr);
			return $count;
		}
		else if($a=='teamlead')
		{
			$res_arr=$this->showuser("filtertl");
			$count=count($res_arr);
			return $count;
		}
		else if($a=='intern')
		{
			$res_arr=$this->showuser("filterintern");
			$count=count($res_arr);
			return $count;
		}
	}

	public function get_result($a,$per_pg,$offSET)
	{
		if($a=='user')
		{
			$res_arr=$this->showuser();
			$show_arr=array_slice($res_arr,$offSET,$per_pg);
			return $show_arr;
		}
		else if($a=='role')
		{
			$res_arr=$this->showrole();
			$show_arr=array_slice($res_arr,$offSET,$per_pg);
			return $show_arr;
		}
		else if($a=='department')
		{
			$res_arr=$this->showdepartment();
			$show_arr=array_slice($res_arr,$offSET,$per_pg);
			return $show_arr;
		}
		else if($a=='permission')
		{
			$res_arr=$this->showpermission();
			$show_arr=array_slice($res_arr,$offSET,$per_pg);
			return $show_arr;
		}
		else if($a=='vertical')
		{
			$res_arr=$this->showverticals();
			$show_arr=array_slice($res_arr,$offSET,$per_pg);
			return $show_arr;
		}
		else if($a=='teamlead')
		{
			$res_arr=$this->showuser("filtertl");
			$show_arr=array_slice($res_arr,$offSET,$per_pg);
			return $show_arr;
		}
		else if($a=='intern')
		{
			$res_arr=$this->showuser("filterintern");
			$show_arr=array_slice($res_arr,$offSET,$per_pg);
			return $show_arr;
		}
	}

	public function get_count_lists($vid)
	{
		$var=$this->managelists($vid);
		$res_arr=$this->fetchlists($var);
		$count=count($res_arr);
		return $count;
	}

	public function get_result_lists($vid,$per_pg,$offSET)
	{
		$var=$this->managelists($vid);
		$res_arr=$this->fetchlists($var);
		$show_arr=array_slice($res_arr,$offSET,$per_pg);
		return $show_arr;
	}
	//pagination functions end

	//user functions
	public function showuser($par='')
	{
		if($par=="")
		{
			if($_SESSION['ses']['is_admin']==1)
			{
				$q1="SELECT DISTINCT u.user_id,u.first_name,u.last_name,u.user_name,u.email,u.user_date_added,u.user_date_modified,u.user_active,u.password,u.phone,u.department_id,u.role_id,d.department_name,r.role_name,u.is_superadmin,u.is_admin FROM users u, roles r, department d WHERE u.department_id=d.department_id AND u.role_id=r.role_id AND u.is_superadmin<>'1' ";
			}
			else
			{
				$q1="SELECT DISTINCT u.user_id,u.first_name,u.last_name,u.user_name,u.email,u.user_date_added,u.user_date_modified,u.user_active,u.password,u.phone,u.department_id,u.role_id,d.department_name,r.role_name,u.is_superadmin,u.is_admin FROM users u, roles r, department d WHERE u.department_id=d.department_id AND u.role_id=r.role_id";
			}
		}
		else if($par=="filtertl")
		{
			$q1="SELECT DISTINCT u.user_id,u.first_name,u.last_name,u.user_name,u.email,u.user_date_added,u.user_date_modified,u.user_active,u.password,u.phone,u.department_id,u.role_id,d.department_name,r.role_name,u.is_superadmin,u.is_admin FROM users u, roles r, department d WHERE u.department_id=d.department_id AND u.role_id=r.role_id AND r.role_name='teamlead'";
		}
		else if($par=="filterintern")
		{
			$q1="SELECT DISTINCT u.user_id,u.first_name,u.last_name,u.user_name,u.email,u.user_date_added,u.user_date_modified,u.user_active,u.password,u.phone,u.department_id,u.role_id,d.department_name,r.role_name,u.is_superadmin,u.is_admin FROM users u, roles r, department d WHERE u.department_id=d.department_id AND u.role_id=r.role_id AND r.role_name='intern'";
		}
		$res1=$this->db->query($q1);
		$row1=$res1->result_array();
		return $row1;
	}
	public function deleteuser($u_id)
	{
		$q="delete FROM users WHERE user_id='".$u_id."'";
		$res=$this->db->query($q);
	}
	public function adduser($fname,$lname,$uname,$email,$pwd,$phone,$deptname,$rolename,$tlid=NULL,$issuadmin,$isadmin)
	{
		$date=date('Y-m-d H:i:s');
		$r1=$this->db->query("SELECT department_id FROM department WHERE department_name='".$deptname."'")->result_array();
		$deptid=$r1[0]['department_id'];
		$r2=$this->db->query("SELECT role_id FROM roles WHERE role_name='".$rolename."'")->result_array();
		$roleid=$r2[0]['role_id'];
		$q="INSERT INTO users (first_name,last_name,user_name,email,user_date_added,password,phone,department_id,role_id,is_superadmin,is_admin) VALUES('$fname','$lname','$uname','$email','$date','$pwd','$phone','$deptid','$roleid','$issuadmin','$isadmin')";
		$res=$this->db->query($q);
		if($tlid!=NULL && $rolename=='intern' || $rolename=='INTERN')
		{
			$q2="SELECT max(user_id) FROM users";
			$res2=$this->db->query($q2);
			$row2=$res2->result_array();
			$u_id=$row2[0]['max(user_id)'];
			$q3="INSERT INTO tl_interns (tl_id,intern_id) VALUES('$tlid','$u_id')";
			$res3=$this->db->query($q3);
		}
	}
	public function edituser($u_id,$fname,$lname,$uname,$email,$pwd,$phone,$deptname,$rolename,$tlid=NULL,$issuadmin,$isadmin)
	{
		$r1=$this->db->query("SELECT department_id FROM department WHERE department_name='".$deptname."'")->result_array();
		$deptid=$r1[0]['department_id'];
		$r2=$this->db->query("SELECT role_id FROM roles WHERE role_name='".$rolename."'")->result_array();
		$roleid=$r2[0]['role_id'];
		$q="update users SET first_name='$fname',last_name='$lname',user_name='$uname',email='$email',password='$pwd',phone='$phone',department_id='$deptid',role_id='$roleid',is_superadmin='$issuadmin',is_admin='$isadmin' WHERE user_id='".$u_id."'";
		$res=$this->db->query($q);
		if($tlid!=NULL && $rolename=='intern' || $rolename=='INTERN')
		{
			$q2="update tl_interns SET tl_id='$tlid' WHERE intern_id='".$u_id."'";
			$res2=$this->db->query($q2);
		}
	}
	//user functions end

	//role functions
	public function showrole()
	{
		$q="SELECT * FROM roles";
		$res=$this->db->query($q);
		$row=$res->result_array();
		return $row;
	}

	public function deleterole($r_id)
	{
		$q1="delete FROM roles WHERE role_id='".$r_id."'";
		$res1=$this->db->query($q1);
		$q2="delete FROM role_permissions WHERE role_id='".$r_id."'";
		$res2=$this->db->query($q2);
	}
	public function addrole($rolename,$rolepriority,$roleactive,$permissions)
	{
		$date=date('Y-m-d H:i:s');
		$q1="INSERT INTO roles (role_name,role_priority,role_date_added,role_active) VALUES('$rolename','$rolepriority','$date','$roleactive')";
		$res1=$this->db->query($q1);
		$q2="SELECT role_id FROM roles ORDER BY role_id DESC LIMIT 1;";
		$res2=$this->db->query($q2);
		$row2=$res2->result_array();
		$rid=$row2[0]['role_id'];
		if(empty($permissions))
		{
			/*$var="view";
			$q3="SELECT * FROM permissions WHERE module_name='".$var."'";
			$res3=$this->db->query($q3);
			$row3=$res3->result_array();
			$permissions=$row3[0]['permission_id'];*/
			$permissions="1";
		}
		$q4="INSERT INTO role_permissions (role_id,permission_id) VALUES('$rid','$permissions')";
		$res4=$this->db->query($q4);
	}
	public function editrole($rid,$rname,$rpriority,$ractive,$permissions)
	{
		$q1="update roles SET role_name='$rname',role_priority='$rpriority',role_active='$ractive' WHERE role_id='".$rid."'";
		$res1=$this->db->query($q1);
		if(empty($permissions))
		{
			/*$var="view";
			$q2="SELECT permission_id FROM permissions WHERE module_name='".$var."'";
			$res2=$this->db->query($q3);
			$row2=$res2->result_array();
			$permissions=(string)$row2[0]['permission_id'];*/
			$permissions="1";
		}
		$q3="update role_permissions SET permission_id='".$permissions."' WHERE role_id='".$rid."'";
		$res3=$this->db->query($q3);
	}
	//role functions end

	//department functions
	public function showdepartment()
	{
		$q="SELECT * FROM department";
		$res=$this->db->query($q);
		$row=$res->result_array();
		return $row;
	}
	public function deletedepartment($d_id)
	{
		$q="delete FROM department WHERE department_id='".$d_id."'";
		$res=$this->db->query($q);
	}
	public function adddepartment($dept_name,$dept_active)
	{
		$q="INSERT INTO department (department_name,department_active) VALUES('$dept_name','$dept_active')";
		$res=$this->db->query($q);
	}
	public function editdepartment($d_id,$dname,$dactive)
	{
		$q="update department SET department_name='".$dname."',department_active='".$dactive."' WHERE department_id='".$d_id."'";
		$res=$this->db->query($q);
	}
	//department functions end

	//permission functions
	public function showpermission()
	{
		$q="SELECT * FROM permissions";
		$res=$this->db->query($q);
		$row=$res->result_array();
		return $row;
	}
	public function deletepermission($p_id)
	{
		$q1="delete FROM permissions WHERE permission_id='".$p_id."'";
		$res1=$this->db->query($q1);
		$q2="SELECT * FROM role_permissions";
		$res2=$this->db->query($q2);
		$row2=$res2->result_array();
		foreach($row2 as $arr)
		{
			$val1=$arr['permission_id'];
			$p_arr1=explode(',', $val1);
			$p_arr2 = array_diff($p_arr1, array($p_id));
			$val2=implode(',', $p_arr2);
			$q3="update role_permissions SET permission_id='".$val2."' WHERE rp_id='".$arr['rp_id']."'";
			$res3=$this->db->query($q3);
		}
	}
	public function addpermission($mod_name)
	{
		$q="INSERT INTO permissions (module_name) VALUES('$mod_name')";
		$res=$this->db->query($q);
	}
	public function editpermission($p_id,$mname)
	{
		$q="update permissions SET module_name='$mname' WHERE permission_id='".$p_id."'";
		$res=$this->db->query($q);
	}
	//permission functions end

	//vertical functions
	public function showverticals()
	{
		if($_SESSION['ses']['role_name']=="market_researcher")
		{
			$q="SELECT v.vertical_id, v.vertical_name, v.active, v.date_added, v.date_modified FROM verticals v, mkt_verticals m WHERE v.vertical_id=m.vertical_id AND m.mkt_id='".$_SESSION['ses']['user_id']."'";
		}
		else
		{
			$q="SELECT v.vertical_id, v.vertical_name, v.active, v.date_added, v.date_modified, m.mkt_id, u.user_name FROM verticals v, mkt_verticals m, users u WHERE v.vertical_id=m.vertical_id AND m.mkt_id=u.user_id";
		}
		$res=$this->db->query($q);
		$row=$res->result_array();
		return $row;
	}
	public function updateverticals($name,$active,$id,$mkt)
	{
		$q="update verticals SET vertical_name='$name', active='$active' WHERE vertical_id='$id' ";
		$res_q=$this->db->query($q);
		$qry="update mkt_verticals SET mkt_id='$mkt' WHERE vertical_id='$id' ";
		$res_qry=$this->db->query($qry);
	}
	public function deleteverticals($id)
	{
		$q1="delete FROM verticals WHERE vertical_id='".$id."'";
		$res1=$this->db->query($q1);
		$q2="delete FROM mkt_verticals WHERE vertical_id='".$id."'";
		$res2=$this->db->query($q2);
	}
	public function addverticals($name, $active, $mkt)
	{
		$q1="INSERT INTO verticals (vertical_name, active) VALUES('$name', '$active')";
		$res1=$this->db->query($q1);
		$q2="SELECT vertical_id FROM verticals ORDER BY vertical_id DESC LIMIT 1;";
		$res2=$this->db->query($q2);
		$row2=$res2->result_array();
		$vid=$row2[0]['vertical_id'];
		$q3="INSERT INTO mkt_verticals (vertical_id, mkt_id) VALUES('$vid','$mkt')";
	}
	//vertical functions end

	//list functions
	public function managelists($id)
	{
		$q="SELECT lists FROM vertical_lists WHERE vertical_id='".$id."'";
		$res=$this->db->query($q);
		$row=$res->result_array();
		$var=$row[0]['lists'];
		$vararr=explode(',', $var);
		return $vararr;
	}
	public function fetchlists($vars)
	{
		$vid=$_SESSION['ses_vid'];
		$i=0;
		foreach ($vars as $value) {
			$q="SELECT * FROM listmaster WHERE list_id='".$value."' AND vertical_id='".$vid."'";
			$res=$this->db->query($q);
			$variable[$i]=$res->result_array();
			$i++;
		}
		return $variable;
	}

	public function addlists($lname,$lcomment,$lactive)
	{
		$date=date('Y-m-d H:i:s');
		$vid=$_SESSION['ses_vid'];
		$q1="INSERT INTO listmaster(list_name,vertical_id,date_added,comment,status) VALUES('$lname','$vid','$date','$lcomment','$lactive')";
		$res1=$this->db->query($q1);
		$q2="SELECT list_id FROM listmaster WHERE vertical_id='$vid'";
		$res2=$this->db->query($q2);
		$row2=$res2->result_array();
		foreach($row2 as $key => $val)
		{
			if($key==0)
			{
				$listid=$val['list_id'];
			}
			else
			{
				$listid=$listid.",".$val['list_id'];
			}
		}
		$q3="update vertical_lists SET lists='$listid' WHERE vertical_id='$vid'";
		$res3=$this->db->query($q3);
	}

	public function editlists($lid,$lname,$lcomment,$lactive)
	{
		$vid=$_SESSION['ses_vid'];
		$q1="update listmaster SET list_name='$lname',comment='$lcomment',status='$lactive' WHERE vertical_id='$vid' AND list_id='$lid'";
		$res1=$this->db->query($q1);
		$q2="update intern_lists SET list_name='$lname' WHERE list_id='$lid'";
		$res2=$this->db->query($q2);
	}

	public function deletelists($lid)
	{
		$vid=$_SESSION['ses_vid'];
		$q1="delete FROM listmaster WHERE list_id='$lid'";
		$res1=$this->db->query($q1);
		$q2="SELECT list_id FROM listmaster WHERE vertical_id='$vid'";
		$res2=$this->db->query($q2);
		$row2=$res2->result_array();
		foreach($row2 as $key => $val)
		{
			if($key==0)
			{
				$listid=$val['list_id'];
			}
			else
			{
				$listid=$listid.",".$val['list_id'];
			}
		}
		$q3="update vertical_lists SET lists='$listid' WHERE vertical_id='$vid'";
		$res3=$this->db->query($q3);
	}//list functions end
	public function mkt_showlist($listid)
	{
		$q="SELECT * FROM recordmaster WHERE list_id='".$listid."'";
		$query=$this->db->query($q);
		$tmpvar=$query->result_array();
		return $tmpvar;
	}

}
?>