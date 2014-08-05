<?php
class Lead_admin extends MY_Controller
{
	protected $controller_url = '';

	private $CI;

    public function __construct()
	{
		parent::__construct();
		$this->load->model('model_permission','mod');
		session_start();
		$this->layout = 'layouts/site';
	}
	public function index()
	{
		redirect('/lead_admin/admin_choice/USER');
		//header('Location: /lead_admin/admin_choice/USER')
	}

	public function admin_choice($a)
	{
		$this->data['active_menu']=$a;
		if($a=='USER')
		{
			//echo "hello";
			$this->data['usershow']=$this->mod->showuser();
			$this->render('modules/lead/project/user/admin_user');
		}
		else if($a=='ROLE')
		{
			$this->data['roleshow']=$this->mod->showrole();
			$this->render('modules/lead/project/role/admin_role');
		}
		else if($a=='DEPARTMENT')
		{
			$this->data['departmentshow']=$this->mod->showdepartment();
			$this->render('modules/lead/project/department/admin_department');
		}
		else if($a=='PERMISSION')
		{
			$this->data['permissionshow']=$this->mod->showpermission();
			$this->render('modules/lead/project/permission/admin_permission');
		}
		else
			redirect('/lead_admin/index');
	}

	public function admin_edit($par,$id)
	{
		if($par=='user')
		{
			$this->data['id']=$id;
			$this->render('modules/lead/project/user/admin_edit_users');
		}
		else if($par=='role')
		{
			$this->data['id']=$id;
			$this->render('modules/lead/project/roles/admin_edit_roles');
		}
		else if($par=='department')
		{
			$this->data['id']=$id;
			$this->render('modules/lead/project/department/admin_edit_departments');
		}
		else if($par=='permission')
		{
			$this->data['id']=$id;
			$this->render('modules/lead/project/permission/admin_edit_permissions');
		}

	}

    public function admin_editprocess($par)
	{
		if($par=='user')
		{
			$id=$this->input->post('u_id');
			$un=$this->input->post('un');
			$fn=$this->input->post('fn');
			$ln=$this->input->post('ln');
			$em=$this->input->post('em');
			$dn=$this->input->post('dn');
			$rn=$this->input->post('rn');
			$pw=$this->input->post('pw');
			$pn=$this->input->post('pn');
			$suad=$this->input->post('suad');
			$ad=$this->input->post('ad');
			$this->mod->edituser($id,$fn,$ln,$un,$em,$pw,$pn,$dn,$rn,$suad,$ad);
		}
		else if($par=='role')
		{
			$id=$this->input->post('r_id');
			$rn=$this->input->post('rn');
			$rp=$this->input->post('rp');
			$ra=$this->input->post('ra');
			$this->mod->editrole($id,$rn,$rp,$ra);
		}
		else if($par=='department')
		{
			$id=$this->input->post('d_id');
			$deptn=$this->input->post('deptn');
			$depta=$this->input->post('depta');
			$this->mod->editdepartment($id,$deptn,$depta);
		}
		else if($par=='permission')
		{
			$id=$this->input->post('p_id');
			$pn=$this->input->post('pn');
			$this->mod->editpermission($id,$pn);
		}
		//redirect('/lead_superadmin/index');
		$par=strtoupper($par);
		$path='/lead_admin/admin_choice/'.$par;
		redirect($path);
	}

	public function admin_add($par)
	{
		if($par=='user')
		{
			$this->render('admin_new_users');
		}
		else if($par=='role')
		{
			$this->render('admin_new_roles');
		}
		else if($par=='department')
		{
			$this->render('admin_new_departments');
		}
		else if($par=='permission')
		{
			$this->render('admin_new_permissions');
		}
	}

	public function admin_addprocess($par)
	{
		if($par=='user')
		{
			$un=$this->input->post('un');
			$fn=$this->input->post('fn');
			$ln=$this->input->post('ln');
			$em=$this->input->post('em');
			$dn=$this->input->post('dn');
			$rn=$this->input->post('rn');
			$pw=$this->input->post('pw');
			$pn=$this->input->post('pn');
			$suad=$this->input->post('suad');
			$ad=$this->input->post('ad');
			$this->mod->adduser($fn,$ln,$un,$em,$pw,$pn,$dn,$rn,$suad,$ad);
		}
		else if($par=='role')
		{
			$rn=$this->input->post('rn');
			$rp=$this->input->post('rp');
			$ra=$this->input->post('ra');
			$this->mod->addrole($rn,$rp,$ra);
		}
		else if($par=='department')
		{
			$deptn=$this->input->post('deptn');
			$depta=$this->input->post('depta');
			$this->mod->adddepartment($deptn,$depta);
		}
		else if($par=='permission')
		{
			$pn=$this->input->post('pn');
			$this->mod->addpermission($pn);
		}
		$par=strtoupper($par);
		$path='/lead_admin/admin_choice/'.$par;
		redirect($path);
	}

	public function ajax_username_check()
	{
		$un=$this->input->post('uname');
		$q="select * from users where user_name='".$un."'";
		$res=$this->db->query($q);
		if($res->num_rows()>0)
		{
			echo "Existing username";
		}
	}

	public function ajax_email_check()
	{
		$em=$this->input->post('email');
		$q="select * from users where email='".$em."'";
		$res=$this->db->query($q);
		if($res->num_rows()>0)
		{
			echo "Existing email";
		}
	}


	//--------------------------------------------
	public function verticals($a)
	{
		$this->data['active_menu']=$a;
		if($a=='VERTICALS')
		{
			$this->data['verticalshow']=$this->mod->showverticals();
			$this->render('admin_vertical_view');
		}

	}

	public function verticals_modify($param,$id='BLANK')
	{
		if ($param=='add') {
			$this->render('admin_vertical_add');
		}
		elseif ($param=='edit') {
			//$id=$this->input->post('vertical_id');
			$this->data['id']=$id;
			$this->render('admin_vertical_edit');

		}
		elseif ($param=='addnew' ) {
				$verticalname=$this->input->post('verticalname');
				$verticalactive=$this->input->post('verticalactive');
				$this->mod->addverticals($verticalname,$verticalactive);
				header('Location: /lead_admin/verticals/VERTICALS');
		}
		elseif ($param=='update') {
			$id=$this->input->post('id');
			$verticalname=$this->input->post('verticalname');
			$verticalactive=$this->input->post('verticalactive');
			$this->data['verticalshow']=$this->mod->updateverticals($verticalname,$verticalactive,$id);
			header('Location: /lead_admin/verticals/VERTICALS');
		}



    }




}
?>
