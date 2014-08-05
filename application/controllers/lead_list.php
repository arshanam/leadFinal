<?php
/**
*
*/
class Lead_list extends MY_Controller
{
	//protected $controller_url = '';

	private $CI;
	public function __construct()
	{

		parent::__construct();
		$this->layout = 'layouts/site2';
		session_start();
		$this->load->model('validation_module','modl');

	}

	public function index()
	{
		//$homepage=current_base_url();
		//$homepage=$homepage."/index.html";
		//redirect($homepage);
	}
	public function tlassign($listid)
	{
		$verticalid=$_SESSION['ses_vid'];
		$tlid=$this->input->post('tlid');
		$date=$this->input->post('date');
		//echo $date;

		//echo $tlid;
		$dateInfo = date_parse_from_format('m/d/Y', $date);
		$newdate="".$dateInfo['year']."-".$dateInfo['month']."-".$dateInfo['day']."";
		//echo $newdate;
		$this->modl->mailnotification($_SESSION['ses']['user_name'],$tlid,$listid, $newdate);
		$this->modl->assigntl($tlid, $verticalid, $listid, $newdate);
		$this->render('/modules/lead/project/vertical/lists/upload_success');
	}
}
