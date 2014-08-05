<?php
class Lead_superadmin extends MY_Controller
{
	protected $controller_url = '';

	private $CI;

   public function __construct()
	{
		parent::__construct();
		$this->layout = 'layouts/site2';
		session_start();
		$this->load->model('model_permission','mod');
		$this->load->model('validation_module','modi');
		//pagination library loading
		$this->load->library('pagination');
   }

	public function index()
	{
		$rname=$_SESSION['ses']['role_name'];
		if($rname=="market_researcher")
			redirect('/lead_marketresearch');
		else if($rname=="teamlead")
			redirect('/lead_teamlead');
		else if($rname=="intern")
			redirect('/lead_intern');
		else if($rname=="qa_teamlead")
			redirect('/lead_qa_teamlead');
		else if($rname=="admin" || $rname=="superadmin")
			redirect('/lead_superadmin/superadmin_choice/USER');
	}

	//assigning tl to interns
	public function add_tl_to_intern()
	{
		$dn=$this->input->post('deptname');
		$dn=strtolower($dn);
		$q="select u.user_id,u.user_name from users u,department d,roles r where r.role_id=u.role_id AND r.role_name='teamlead' AND u.department_id=d.department_id AND d.department_name='$dn'";
		$res=$this->db->query($q);
		$row=$res->result_array();
		$options1=array(""=>'Select a Team Lead');
		foreach ($row as $val)
		{
			$uid=$val['user_id'];
			$options1[$uid]=$val['user_name'];
		}
		echo form_dropdown('tln',$options1,"");
	}
	public function edit_tl_to_intern()
	{
		$dn=$this->input->post('deptname');
		$internid=$this->input->post('internid');
		$q="select * from tl_interns where intern_id='$internid'";
		$res=$this->db->query($q);
		$row=$res->result_array();
		$tlid=$row[0]['tl_id'];
		$dn=strtolower($dn);
		$q1="select u.user_id,u.user_name from users u,department d,roles r where r.role_id=u.role_id AND r.role_name='teamlead' AND u.department_id=d.department_id AND d.department_name='$dn'";
		$res1=$this->db->query($q1);
		$row1=$res1->result_array();
		$options1=array(""=>'Select a Team Lead');
		$selected="";
		foreach ($row1 as $val)
		{
			$uid=$val['user_id'];
			if($uid==$tlid)
			{
				$selected=$uid;
			}
			$options1[$uid]=$val['user_name'];
		}
		echo form_dropdown('tln',$options1,$selected);
	}

	//to update path of role when released
	public function superadmin_choice($a)
	{
		if(isset($_SESSION['ses_vid']))
		{
			unset($_SESSION['ses_vid']);
		}
		$this->data['active_menu']=$a;
		//pagination variables -->
		$per_pg=5;
		$offset=$this->uri->segment(4);
		//pagination variables <>
		if($a=='USER')
		{
			//pagination for user view -->
			$total=$this->mod->get_count('user');
			$config['base_url'] ='/lead_superadmin/superadmin_choice/USER/';
			$config['total_rows'] = $total;
			$config['per_page'] = $per_pg;
			$config['uri_segment']= 4;
			$config['num_links'] = 2;
			$this->pagination->initialize($config);
			$this->data['pages']=$this->pagination->create_links();
			$this->data['usershow']=$this->mod->get_result('user',$per_pg,$offset);
			//pagination for user view <>
			$this->render('modules/lead/project/user/superadmin_user');
		}
		else if($a=='ROLE')
		{
			//pagination for role view -->
			$total=$this->mod->get_count('role');
			$config['base_url'] ='/lead_superadmin/superadmin_choice/ROLE/';
			$config['total_rows'] = $total;
			$config['per_page'] = $per_pg;
			$config['uri_segment']= 4;
			$config['num_links'] = 2;
			$this->pagination->initialize($config);
			$this->data['pages']=$this->pagination->create_links();
			$this->data['roleshow']=$this->mod->get_result('role',$per_pg,$offset);
			//pagination for role view <>
			$this->render('modules/lead/project/role/superadmin_role');
		}
		else if($a=='DEPARTMENT')
		{
			//pagination for department view -->
			$total=$this->mod->get_count('department');
			$config['base_url'] ='/lead_superadmin/superadmin_choice/DEPARTMENT/';
			$config['total_rows'] = $total;
			$config['per_page'] = $per_pg;
			$config['uri_segment']= 4;
			$config['num_links'] = 2;
			$this->pagination->initialize($config);
			$this->data['pages']=$this->pagination->create_links();
			$this->data['departmentshow']=$this->mod->get_result('department',$per_pg,$offset);
			//pagination for department view <>
			$this->render('modules/lead/project/department/superadmin_department');
		}
		else if($a=='PERMISSION')
		{
			//pagination for user view -->
			$total=$this->mod->get_count('permission');
			$config['base_url'] ='/lead_superadmin/superadmin_choice/PERMISSION/';
			$config['total_rows'] = $total;
			$config['per_page'] = $per_pg;
			$config['uri_segment']= 4;
			$config['num_links'] = 2;
			$this->pagination->initialize($config);
			$this->data['pages']=$this->pagination->create_links();
			$this->data['permissionshow']=$this->mod->get_result('permission',$per_pg,$offset);
			//pagination for user view <>
			$this->render('modules/lead/project/permission/superadmin_permission');
		}
		else
			redirect('/lead_superadmin/index');
	}

	public function superadmin_delete($par,$id)
	{
		if($par=='user')
		{
			$this->mod->deleteuser($id);
		}
		else if($par=='role')
		{
			$this->mod->deleterole($id);
		}
		else if($par=='department')
		{
			$this->mod->deletedepartment($id);
		}
		else if($par=='permission')
		{
			$this->mod->deletepermission($id);
		}
		$par=strtoupper($par);
		$path='/lead_superadmin/superadmin_choice/'.$par;
		redirect($path);
	}

	public function superadmin_edit($par,$id)
	{
		$this->data['id']=$id;
		$this->data['active_menu']=strtoupper($par);
		if($par=='user')
		{
			$this->render('modules/lead/project/user/edit_users');
		}
		else if($par=='role')
		{
			$this->render('modules/lead/project/role/edit_roles');
		}
		else if($par=='department')
		{
			$this->render('modules/lead/project/department/edit_departments');
		}
		else if($par=='permission')
		{
			$this->render('modules/lead/project/permission/edit_permissions');
		}
		else $this->render('index.html');

	}

	public function superadmin_editprocess($par)
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
			$tlid=$this->input->post('tln');
			$pw=$this->input->post('pw');
			$pn=$this->input->post('pn');
			$suad=$this->input->post('suad');
			$ad=$this->input->post('ad');
			$arrval=$this->modi->sanitizeuser($fn,$ln,$un,$em,$pw);
			$this->mod->edituser($id,$arrval['firstname'],$arrval['lastname'],$arrval['username'],$arrval['email'],$arrval['password'],$pn,$dn,$rn,$tlid,$suad,$ad);
		}
		else if($par=='role')
		{
			$id=$this->input->post('r_id');
			$rn=$this->input->post('rn');
			$rp=$this->input->post('rp');
			$ra=$this->input->post('ra');
			$pn=$this->input->post('pn');
			$ps_arr=$this->input->post('ps');
			$ps=implode(",",$ps_arr);
			$var=$this->modi->sanitizename($rn);
			$this->mod->editrole($id,$var,$rp,$ra,$ps);
		}
		else if($par=='department')
		{
			$id=$this->input->post('d_id');
			$deptn=$this->input->post('deptn');
			$depta=$this->input->post('depta');
			$var=$this->modi->sanitizename($deptn);
			$this->mod->editdepartment($id,$var,$depta);
		}
		else if($par=='permission')
		{
			$id=$this->input->post('p_id');
			$pn=$this->input->post('pn');
			$var=$this->modi->sanitizename($pn);
			$this->mod->editpermission($id,$var);
		}
		$par=strtoupper($par);
		$path='/lead_superadmin/superadmin_choice/'.$par;
		redirect($path);
	}

	public function superadmin_add($par)
	{
		$this->data['active_menu']=strtoupper($par);
		if($par=='user')
		{
			$this->render('modules/lead/project/user/new_users');
		}
		else if($par=='role')
		{
			$this->render('modules/lead/project/role/new_roles');
		}
		else if($par=='department')
		{
			$this->render('modules/lead/project/department/new_departments');
		}
		else if($par=='permission')
		{
			$this->render('modules/lead/project/permission/new_permissions');
		}
	}

	public function superadmin_addprocess($par)
	{
		if($par=='user')
		{
			$un=$this->input->post('un');
			$fn=$this->input->post('fn');
			$ln=$this->input->post('ln');
			$em=$this->input->post('em');
			$dn=$this->input->post('dn');
			$rn=$this->input->post('rn');
			$tlid=$this->input->post('tln');
			$pw=$this->input->post('pw');
			$pn=$this->input->post('pn');
			$suad=$this->input->post('suad');
			$ad=$this->input->post('ad');
			$arrval=$this->modi->sanitizeuser($fn,$ln,$un,$em,$pw);
			$this->mod->adduser($arrval['firstname'],$arrval['lastname'],$arrval['username'],$arrval['email'],$arrval['password'],$pn,$dn,$rn,$tlid,$suad,$ad);
		}
		else if($par=='role')
		{
			$rn=$this->input->post('rn');
			$rp=$this->input->post('rp');
			$ra=$this->input->post('ra');
			$ps_arr=$this->input->post('ps');
			$ps=implode(",",$ps_arr);
			$var=$this->modi->sanitizename($rn);
			$this->mod->addrole($var,$rp,$ra,$ps);
		}
		else if($par=='department')
		{
			$deptn=$this->input->post('deptn');
			$depta=$this->input->post('depta');
			$var=$this->modi->sanitizename($deptn);
			$this->mod->adddepartment($var,$depta);
		}
		else if($par=='permission')
		{
			$pn=$this->input->post('pn');
			$var=$this->modi->sanitizename($pn);
			$this->mod->addpermission($var);
		}
		$par=strtoupper($par);
		$path='/lead_superadmin/superadmin_choice/'.$par;
		redirect($path);
	}

	//ajax function
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

	public function ajax_rolepriority_check()
	{
		$rp=$this->input->post('rpriority');
		$q="select * from roles where role_priority='".$rp."'";
		$res=$this->db->query($q);
		if($res->num_rows()>0)
		{
			echo "Existing role priority";
		}
	}

	public function ajax_listname_check()
	{
		$listname=$this->input->post('lname');
		$ln=$this->modi->sanitizename($listname);
		$q="select * from listmaster where list_name='".$ln."'";
		$res=$this->db->query($q);
		if($res->num_rows()>0)
		{
			echo "Existing listname";
		}
	}
	//ajax function ends


	//--------------------------------------------
	public function verticals($a)
	{
		if(isset($_SESSION['ses_vid']))
		{
			unset($_SESSION['ses_vid']);
		}
		$this->data['active_menu']=$a;
		//pagination variables -->
		$per_pg=5;
		$offset=$this->uri->segment(4);
		//pagination variables <>
		if($a=='VERTICAL')
		{
			//pagination for user view -->
			$total=$this->mod->get_count('vertical');
			$config['base_url'] ='/lead_superadmin/verticals/VERTICAL/';
			$config['total_rows'] = $total;
			$config['per_page'] = $per_pg;
			$config['uri_segment']= 4;
			$config['num_links'] = 2;
			$this->pagination->initialize($config);
			$this->data['pages']=$this->pagination->create_links();
			$this->data['verticalshow']=$this->mod->get_result('vertical',$per_pg,$offset);
			//pagination for user view <>
			$this->render('modules/lead/project/vertical/vertical_view');
		}

	}
	public function verticals_modify($param,$id=NULL)
	{
		if ($param=='add') {
			$this->data['active_menu']="VERTICAL";
			$this->render('modules/lead/project/vertical/vertical_add');
		}
		elseif ($param=='edit') {
			$this->data['active_menu']="VERTICAL";
			$this->data['id']=$id;
			$this->render('modules/lead/project/vertical/vertical_edit');

		}
		elseif ($param=='delete' ) {
			$this->mod->deleteverticals($id);
			header('Location: /lead_superadmin/verticals/VERTICAL');
		}
		elseif ($param=='addnew' ) {
			$verticalname=$this->input->post('verticalname');
			$var=$this->modi->sanitizename($verticalname);
			$verticalactive=$this->input->post('verticalactive');
			$vertical_mkt=$this->input->post('verticalmkt');
			$this->mod->addverticals($var,$verticalactive,$vertical_mkt);
			header('Location: /lead_superadmin/verticals/VERTICAL');
		}
		elseif ($param=='update') {
			$id=$this->input->post('id');
			$verticalname=$this->input->post('verticalname');
			$var=$this->modi->sanitizename($verticalname);
			$verticalactive=$this->input->post('verticalactive');
			$vertical_mkt=$this->input->post('verticalmkt');
			$this->data['verticalshow']=$this->mod->updateverticals($var,$verticalactive,$id,$vertical_mkt);
			header('Location: /lead_superadmin/verticals/VERTICAL');
		}

	}

	public function lists($vid=NULL)
	{
		if(isset($_SESSION['ses_vid']))
		{
			$vid=$_SESSION['ses_vid'];
		}
		else
		{
			$_SESSION['ses_vid']=$vid;
		}
		$this->data['active_menu']="VERTICAL";
		//pagination variables -->
		$per_pg=5;
		$offset=$this->uri->segment(4);
		//pagination variables <>
		//pagination for list view -->
		$total=$this->mod->get_count_lists($vid);
		$config['base_url'] ='/lead_superadmin/lists/'.$vid.'/';
		$config['total_rows'] = $total;
		$config['per_page'] = $per_pg;
		$config['uri_segment']= 4;
		$config['num_links'] = 2;
		$this->pagination->initialize($config);
		$this->data['pages']=$this->pagination->create_links();
		$this->data['arrvar']=$this->mod->get_result_lists($vid,$per_pg,$offset);
		//pagination for list view <>
		$this->render('modules/lead/project/vertical/lists/list_view');
	}

	public function lists_modify($param,$id=NULL)//$id is blank in case of add operation
	{
		if ($param=='add') {
			$this->data['active_menu']="VERTICAL";
			$this->render('modules/lead/project/vertical/lists/list_add');
		}
		else if ($param=='edit') {
			$this->data['active_menu']="VERTICAL";
			$this->data['listid']=$id;
			$this->render('modules/lead/project/vertical/lists/list_edit');
		}
		else if ($param=='delete' ) {
			$this->mod->deletelists($id);
			header('Location: /lead_superadmin/lists');
		}
		else if ($param=='addprocess' ) {
			$listname=$this->input->post('listname');
			$var=$this->modi->sanitizename($listname);
			$listcomment=$this->input->post('listcomment');
			$listactive=$this->input->post('listactive');
			$this->mod->addlists($var,$listcomment,$listactive);
			header('Location: /lead_superadmin/lists');
		}
		else if ($param=='editprocess' ) {
			$listid=$this->input->post('listid');
			$listname=$this->input->post('listname');
			$var=$this->modi->sanitizename($listname);
			$listcomment=$this->input->post('listcomment');
			$listactive=$this->input->post('listactive');
			$this->mod->editlists($listid,$var,$listcomment,$listactive);
			header('Location: /lead_superadmin/lists');
		}
	}
	public function backpage($par)// To go back to the main section
	{
		$par=strtoupper($par);
		if ($par=='VERTICAL') {
			$path='/lead_superadmin/verticals/'.$par;
			redirect($path);
		}
		else {
			$path='/lead_superadmin/superadmin_choice/'.$par;
			redirect($path);
		}
	}
	public function assignlist($id)
	{
		$this->data['listid']=$id;
		$this->layout = 'layouts/zerolayout';
		$this->render('/modules/lead/project/demo');
	}
	public function upload_view($listid)
	{
		$this->layout = 'layouts/zerolayout';
		$this->data['error']="";
		$this->data['list_id']=$listid;
		$this->render('/modules/lead/project/vertical/lists/upload_form');
	}

	public function upload_it($listid)
	{
		$config['upload_path'] = "./webroot/uploads";
		$config['allowed_types'] = 'csv|xlsx|xls|pdf';
		$config['max_size']	= '0'; 	//for unrestricted max size

		$this->load->library('upload', $config);

		if (!($this->upload->do_upload()))
		{
			$this->layout = 'layouts/zerolayout';
			$this->data['error']=$this->upload->display_errors();
			$this->render('/modules/lead/project/vertical/lists/upload_form');
		}
		else   	//Successfully Uploaded
		{
			$this->layout = 'layouts/zerolayout';
			$a=$this->upload->data();
			$uname=$_SESSION['ses']['user_name'];
			$uid=$_SESSION['ses']['user_id'];

			if(chmod("./webroot/uploads/".$a[file_name],0777))
			{
				if ($a[file_ext]==".pdf") {
					$name=uniqid().$a[file_name];
					//$cmdstr="mv ./webroot/uploads/".$a[file_name].", ./webroot/uploads/".$name."";
					//exec($cmdstr);
					$location='./application/views/modules/lead/records/';
					move_uploaded_file($_FILES['userfile']['tmp_name'],$location.$name);
					$this->modi->uploadpdf($listid, $uname, $uid, $name);

					$this->render('/modules/lead/project/vertical/lists/upload_success');
				}
				elseif ($a[file_ext]==".csv" || $a[file_ext]==".xls" || $a[file_ext]==".xlsx") {
					$msg=$this->modi->record_in_db($a[file_name],$a[file_ext],$listid);
					if($msg=="")
					{
						if(unlink("./webroot/uploads/".$a[file_name]))
							$this->render('/modules/lead/project/vertical/lists/upload_success');
					}
					else
					{
						$this->data['error']=$msg;
						if(unlink("./webroot/uploads/".$a[file_name]))
							$this->render('/modules/lead/project/vertical/lists/upload_form');
					}
				}
			}
			else
			{
				$this->data['error']="Permissions not Changed";
				if(unlink("./webroot/uploads/".$a[file_name]))
					$this->render('/modules/lead/project/vertical/lists/upload_form');
			}
		}
	}
}

?>