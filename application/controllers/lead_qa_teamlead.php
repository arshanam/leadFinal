<?php
/**
* QA TeamLead Controller
*/
class Lead_qa_teamlead extends MY_Controller
{
	protected $controller_url = '';

	private $CI;
	public function __construct()
	{
		parent::__construct();
		$this->layout = 'layouts/site8';
		session_start();
		$this->load->model('qa_teamlead_model','mod');
		$this->load->model('validation_module','modi');
		$this->load->model('model_permission','modl');
		//pagination library loading
		$this->load->library('pagination');
	}
	public function index()
	{
		redirect('/lead_qa_teamlead/qa_teamlead_choice/DASHBOARD');
	}
	//QA TeamLead functions->
	public function qa_teamlead_choice($a)
	{
		$this->data['active_menu']=$a;
		if($a=='DASHBOARD')
		{
			$this->render('modules/lead/project/qa_teamlead/dashboard');
		}
		else if($a=='QA')
		{
			/*$total=$this->mod->get_qa_count();
			$config['base_url'] ='/lead_teamlead/teamlead_choice/INTERN/';
			$config['total_rows'] = $total;
			$config['per_page'] = $per_pg;
			$config['uri_segment']= 4;
			$config['num_links'] = 2;
			$this->pagination->initialize($config);
			$this->data['pages']=$this->pagination->create_links();
			$this->data['internshow']=$this->mod->get_qa($_SESSION['ses']['user_id'],$per_pg,$offset);
			//pagination for user view <>
			$this->render('modules/lead/project/user/tl_show_intern');*/
		}
		else
			redirect('/lead_qa_teamlead/qa_teamlead_choice/DASHBOARD');
	}
	//QA TeamLead functions <>
}
?>