<?php
/**
* Intern Controller
*/
class Lead_intern extends MY_Controller
{
	protected $controller_url = '';

	private $CI;
	public function __construct()
	{
		parent::__construct();
		$this->layout = 'layouts/site7';
		session_start();
		$this->load->model('intern_model','mod');
	}
	public function index()
	{
		$this->render('modules/lead/test');
		redirect('/lead_intern/intern_choice/DASHBOARD');
	}

	public function intern_choice($a)
	{

		$this->data['active_menu']=$a;

		if($a=='DASHBOARD')
		{

			$intern_id=$_SESSION['ses']['user_id'];
			$intern_name=$_SESSION['ses']['user_name'];
			$this->data['interndata']=$this->mod->interndashboard($intern_id,$intern_name);
			$this->render('modules/lead/project/intern/dashboard');
		}
		if($a=='HISTORY')
		{

			$intern_id=$_SESSION['ses']['user_id'];
			$intern_name=$_SESSION['ses']['user_name'];
			$this->data['internhistory']=$this->mod->intern_history($intern_id);
			$this->render('modules/lead/project/intern/history');
		}
	}
	public function download($internid, $listname)
	{
		$fname=$listname."-".$internid.".csv";
		$this->mod->download_send_headers($fname);
		//This page downloads records.
		$var=$this->mod->downloadrecord($internid,$listname);
		echo $this->mod->array2csv($var);
		die();
	}

	public function upload_view($listid)
	{
		$this->layout = 'layouts/zerolayout';
		$this->data['error']="";
		$this->data['list_id']=$listid;
		$this->render('/modules/lead/project/intern/intern_upload');
	}

	public function upload_it($listid)
	{
		$config['upload_path'] = "./webroot/uploads";
		$config['allowed_types'] = 'csv|xlsx|xls';
		$config['max_size']	= '0';

		$this->load->library('upload', $config);

		if (!($this->upload->do_upload()))
		{
			$this->layout = 'layouts/zerolayout';
			$this->data['error']=$this->upload->display_errors();
			$this->render('/modules/lead/project/intern/intern_upload');
		}
		else   	//Successfully Uploaded
		{
			$this->layout = 'layouts/zerolayout';
			$a=$this->upload->data();
			$uname=$_SESSION['ses']['user_name'];

			if(chmod("./webroot/uploads/".$a[file_name],0777))
			{
				$msg=$this->mod->record_in_db($a[file_name],$a[file_ext],$listid);
				if($msg=="Success")
				{
					if(unlink("./webroot/uploads/".$a[file_name]))
						$this->render('/modules/lead/project/vertical/lists/upload_success');
				}
				else
				{
					$this->data['error']=$msg;
					if(unlink("./webroot/uploads/".$a[file_name]))
						$this->render('/modules/lead/project/intern/intern_upload');
				}
			}
			else
			{
				$this->data['error']="Permissions not Changed";
				if(unlink("./webroot/uploads/".$a[file_name]))
					$this->render('/modules/lead/project/intern/intern_upload');
			}
		}
	}
}