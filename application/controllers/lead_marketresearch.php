<?php
/**
* Market Research Controller
*/
class Lead_marketresearch extends MY_Controller
{
	protected $controller_url = '';

	private $CI;

	public function __construct()
	{
		parent::__construct();
		$this->layout = 'layouts/site5';
		session_start();
		$this->load->model('model_permission','mod');
		$this->load->model('validation_module','modi');
		//pagination library loading
		$this->load->library('pagination');
	}

	public function index()
	{
		header('Location: /lead_marketresearch/mktresearch_choice/VERTICAL');
	}
	public function assign_tl_view($list_id)
	{
		$this->layout = 'layouts/zerolayout';
		$this->data['listid']=$list_id;
		$this->data['vid']=$_SESSION['ses_vid'];
		$this->render('modules/lead/project/vertical/lists/assign_tl_popup');
	}
	//ajax function
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

	//intern adding and removing functions
	public function add_teamlead()
	{
		$this->data['active_menu']='TEAMLEAD';
		$this->render('modules/lead/project/user/new_teamleads');
	}

	public function addprocess_teamlead()
	{
		$un=$this->input->post('un');
		$fn=$this->input->post('fn');
		$ln=$this->input->post('ln');
		$em=$this->input->post('em');
		$dn=$this->input->post('dn');
		$rn=$this->input->post('rn');
		$pw=$this->input->post('pw');
		$pn=$this->input->post('pn');
		$suad=$ad="0";
		$arrval=$this->modi->sanitizeuser($fn,$ln,$un,$em,$pw);
		$this->modl->adduser($arrval['firstname'],$arrval['lastname'],$arrval['username'],$arrval['email'],$arrval['password'],$pn,$dn,$rn,$suad,$ad);
		redirect("/lead_marketresearch/mktresearch_choice/TEAMLEAD/");
	}

	public function delete_teamlead($id)
	{
		$this->mod->deleteuser($id);
		redirect("/lead_marketresearch/mktresearch_choice/TEAMLEAD/");
	}
	//teamlead adding and removing functions end

	//market researcher functions->
	public function mktresearch_choice($a)
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
		if($a=='TEAMLEAD')
		{
			//pagination for user view -->
			$total=$this->mod->get_count('teamlead');
			$config['base_url'] ='/lead_marketresearch/mktresearch_choice/TEAMLEAD/';
			$config['total_rows'] = $total;
			$config['per_page'] = $per_pg;
			$config['uri_segment']= 4;
			$config['num_links'] = 2;
			$this->pagination->initialize($config);
			$this->data['pages']=$this->pagination->create_links();
			$this->data['teamleadshow']=$this->mod->get_result('teamlead',$per_pg,$offset);
			//pagination for user view <>
			$this->render('modules/lead/project/user/show_teamlead');
		}
		else if($a=='INTERN')
		{
			$total=$this->mod->get_count('intern');
			$config['base_url'] ='/lead_marketresearch/mktresearch_choice/INTERN/';
			$config['total_rows'] = $total;
			$config['per_page'] = $per_pg;
			$config['uri_segment']= 4;
			$config['num_links'] = 2;
			$this->pagination->initialize($config);
			$this->data['pages']=$this->pagination->create_links();
			$this->data['internshow']=$this->mod->get_result('intern',$per_pg,$offset);
			//pagination for user view <>
			$this->layout = 'layouts/site5';
			$this->render('modules/lead/project/user/show_intern');
		}
		else if($a=='VERTICAL')
		{
			//pagination for user view -->
			$total=$this->mod->get_count('vertical');
			$config['base_url'] ='/lead_marketresearch/mktresearch_choice/VERTICAL/';
			$config['total_rows'] = $total;
			$config['per_page'] = $per_pg;
			$config['uri_segment']= 4;
			$config['num_links'] = 2;
			$this->pagination->initialize($config);
			$this->data['pages']=$this->pagination->create_links();
			$this->data['verticalshow']=$this->mod->get_result('vertical',$per_pg,$offset);
			//pagination for user view <>
			//$this->layout = 'layouts/site5';
			$this->render('modules/lead/project/vertical/show_vertical');
		}
		else
			redirect('/lead_marketresearch/mktresearch_choice/TEAMLEAD');
	}
	//market researcher functions end<>

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
		$config['base_url'] ='/lead_marketresearch/lists/'.$vid.'/';
		$config['total_rows'] = $total;
		$config['per_page'] = $per_pg;
		$config['uri_segment']= 4;
		$config['num_links'] = 2;
		$this->pagination->initialize($config);
		$this->data['pages']=$this->pagination->create_links();
		$this->data['arrvar']=$this->mod->get_result_lists($vid,$per_pg,$offset);
		//pagination for list view <>
		$this->render('modules/lead/project/vertical/lists/show_list');
	}

	public function lists_modify($param,$id=NULL)//$id is blank in case of add operation
	{
		if ($param=='add') {
			$this->data['active_menu']="VERTICAL";
			$this->render('modules/lead/project/vertical/lists/add_list');
		}
		else if ($param=='edit') {
			$this->data['active_menu']="VERTICAL";
			$this->data['listid']=$id;
			$this->render('modules/lead/project/vertical/lists/edit_lists');
		}
		else if ($param=='delete' ) {
			$this->mod->deletelists($id);
			header('Location: /lead_marketresearch/lists');
		}
		else if ($param=='addprocess' ) {
			$listname=$this->input->post('listname');
			$var=$this->modi->sanitizename($listname);
			$listcomment=$this->input->post('listcomment');
			$listactive=$this->input->post('listactive');
			$this->mod->addlists($var,$listcomment,$listactive);
			header('Location: /lead_marketresearch/lists');
		}
		else if ($param=='editprocess' ) {
			$listid=$this->input->post('listid');
			$listname=$this->input->post('listname');
			$var=$this->modi->sanitizename($listname);
			$listcomment=$this->input->post('listcomment');
			$listactive=$this->input->post('listactive');
			$this->mod->editlists($listid,$var,$listcomment,$listactive);
			header('Location: /lead_marketresearch/lists');
		}
	}
	public function upload_view($listid)
	{
		$this->layout = 'layouts/zerolayout';
		$this->data['error']="";
		$this->data['list_id']=$listid;
		$this->render('/modules/lead/project/vertical/lists/mkt_upload');
	}

	public function upload_itz($listid)
	{
		$config['upload_path'] = "./webroot/uploads";
		$config['allowed_types'] = 'csv|xlsx|xls|pdf';
		$config['max_size']	= '0'; 	//for unrestricted max size

		$this->load->library('upload', $config);

		if (!($this->upload->do_upload()))
		{

			$this->layout = 'layouts/zerolayout';
			$this->data['error']=$this->upload->display_errors();
			$this->render('/modules/lead/project/vertical/lists/mkt_upload');
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
					//$name=uniqid().$a[file_name];
					//$cmdstr="mv ./webroot/uploads/".$a[file_name].", ./webroot/uploads/".$name."";
					//exec($cmdstr);
					//$location='./application/views/modules/lead/records/';
					//move_uploaded_file($_FILES['userfile']['tmp_name'],$location.$name);
					$name=$a[file_name];
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
							$this->render('/modules/lead/project/vertical/lists/mkt_upload');
					}
				}
			}
			else
			{
				$this->data['error']="Permissions not Changed";
				if(unlink("./webroot/uploads/".$a[file_name]))
					$this->render('/modules/lead/project/vertical/lists/mkt_upload');
			}
		}
	}
	public function downloadpdf($listid,$filename)
	{
		$path="./webroot/uploads/".$a[file_name]."";
		$data = file_get_contents($path); // Read the file's contents
		$name = 'filename.pdf';
		force_download($name, $data);

	}
	public function backpage($par)// To go back to the main section
	{
		$par=strtoupper($par);
		$path='/lead_marketresearch/mktresearch_choice/'.$par;
		redirect($path);
	}
	public function list_view($listid)
	{
		$this->data['active_menu']="VERTICAL";
		//This is comment
		$xvar=$this->mod->mkt_showlist($listid);
		//print_r($xvar);
		$this->data['listid']=$listid;
		$this->data['arrval']=$xvar;
		$this->render('/modules/lead/project/vertical/lists/listcontent');
	}
}


?>