<?php
/**
* TeamLead Controller
*/
class Lead_teamlead extends MY_Controller
{
	protected $controller_url = '';

	private $CI;
	public function __construct()
	{
		parent::__construct();
		$this->layout = 'layouts/site6';
		session_start();
		$this->load->model('teamlead_model','mod');
		$this->load->model('validation_module','modi');
		$this->load->model('model_permission','modl');
		//pagination library loading
		$this->load->library('pagination');
	}
	public function index()
	{
		redirect('/lead_teamlead/teamlead_choice/DASHBOARD');
	}
	//TeamLead functions->

	//ajax function for filtering
	/*function filter_by_listname()
	{
		$ln=$this->input->post("lname");
		$listname=$this->modi->sanitizename($ln);
		$q="SELECT * FROM intern_lists WHERE list_name='$listname' ORDER BY date DESC";
		$res=$this->db->query($q);
		$resvar1=$res->result_array();
		echo "<table class='sortable'>
				<thead>
					<tr>
						<th>Serial #</th><th>List Name</th><th> Assigned Intern</th><th>From </th><th>Upto </th><th>Date Assigned</th><th>ReAssign</th>
					</tr>
				</thead>
				<tbody>
				<tr> <!-- Filteration -->
					<td></td>
					<td>
						<input type='text' name='list_filter' id='list_filter' onchange='filter_list(this.value)'/>
					</td>
					<td>
						<input type='text' name='intern_filter' id='intern_filter'/>
					</td>
				</tr>";
				$i=1;
				foreach ($resvar1 as $value) {
				echo "
				<tr>
					<td>".$i++."</td>
					<td>".$value['list_name']."</td>
					<td>".$value['intern_name']."</td>
					<td>".$value['start_rec']."</td>
					<td>".$value['end_rec']."</td>
					<td>".$value['date']."</td>
					<td><a class='iframe1' href='/lead_teamlead/reassignview/".$value['intern_name']."/".$value['list_name']."/' >ReAssign</a></td>
				</tr>";
				}
			echo "</tbody>
			</table>";
	}
	function filter_by_internname()
	{
		$in=$this->input->post("iname");
		$internname=$this->modi->sanitizename($in);
		$q="SELECT * FROM intern_lists WHERE intern_name='$internname' ORDER BY date DESC";
		$res=$this->db->query($q);
		$resvar1=$res->result_array();
		echo "<table class='sortable'>
				<thead>
					<tr>
						<th>Serial #</th><th>List Name</th><th> Assigned Intern</th><th>From </th><th>Upto </th><th>Date Assigned</th><th>ReAssign</th>
					</tr>
				</thead>
				<tbody>
				<tr> <!-- Filteration -->
					<td></td>
					<td>
						<input type='text' name='list_filter' id='list_filter' onchange='filter_list(this.value)'/>
					</td>
					<td>
						<input type='text' name='intern_filter' id='intern_filter'/>
					</td>
				</tr>";
				$i=1;
				foreach ($resvar1 as $value) {
				echo "
				<tr>
					<td>".$i++."</td>
					<td>".$value['list_name']."</td>
					<td>".$value['intern_name']."</td>
					<td>".$value['start_rec']."</td>
					<td>".$value['end_rec']."</td>
					<td>".$value['date']."</td>
					<td><a class='iframe1' href='/lead_teamlead/reassignview/".$value['intern_name']."/".$value['list_name']."/' >ReAssign</a></td>
				</tr>";
				}
			echo "</tbody>
			</table>";
	}*/
	//ajax function ends

	//intern adding and removing functions
	public function add_intern()
	{
		$this->data['active_menu']='INTERN';
		$this->render('modules/lead/project/user/new_interns');
	}

	public function addprocess_intern()
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
		$suad=$ad="0";
		$arrval=$this->modi->sanitizeuser($fn,$ln,$un,$em,$pw);
		$this->modl->adduser($arrval['firstname'],$arrval['lastname'],$arrval['username'],$arrval['email'],$arrval['password'],$pn,$dn,$rn,$tlid,$suad,$ad);
		redirect("/lead_teamlead/teamlead_choice/INTERN");
	}

	public function delete_intern($id)
	{
		$this->modl->deleteuser($id);
		redirect("/lead_teamlead/teamlead_choice/INTERN");
	}
	//intern adding and removing functions end

	public function teamlead_choice($a)
	{

		$this->data['active_menu']=$a;
		//pagination variables -->
		$per_pg=5;
		$offset=$this->uri->segment(4);
		//pagination variables <>
		if($a=='DASHBOARD')
		{
			$this->data['id']=$_SESSION['ses']['user_id'];
			$query=$this->db->query("SELECT * from tl_lists WHERE tl_id ='".$_SESSION['ses']['user_id']."'");
			$res=$query->result_array();
			$this->data['vid']=$res[0]['vertical_id'];
			$this->render('modules/lead/project/teamlead/dashboard');
		}
		else if($a=='INTERN')
		{
			$total=$this->mod->get_interns_count($_SESSION['ses']['user_id']);
			$config['base_url'] ='/lead_teamlead/teamlead_choice/INTERN/';
			$config['total_rows'] = $total;
			$config['per_page'] = $per_pg;
			$config['uri_segment']= 4;
			$config['num_links'] = 2;
			$this->pagination->initialize($config);
			$this->data['pages']=$this->pagination->create_links();
			$this->data['internshow']=$this->mod->get_interns($_SESSION['ses']['user_id'],$per_pg,$offset);
			$this->render('modules/lead/project/user/tl_show_intern');
		}
		else if($a=='LIST')
		{
			$total=$this->mod->get_lists_count($_SESSION['ses']['user_id']);
			$config['base_url'] ='/lead_teamlead/teamlead_choice/LIST/';
			$config['total_rows'] = $total;
			$config['per_page'] = $per_pg;
			$config['uri_segment']= 4;
			$config['num_links'] = 2;
			$this->pagination->initialize($config);
			$this->data['pages']=$this->pagination->create_links();
			$this->data['listshow']=$this->mod->get_lists($_SESSION['ses']['user_id'],$per_pg,$offset);
			$this->data['id']=$_SESSION['ses']['user_id'];
			$this->render('modules/lead/project/vertical/lists/tl_show_lists');
		}
		else
			redirect('/lead_teamlead/teamlead_choice/DASHBOARD');
	}
	public function backpage($par)// To go back to the main section
	{
		$par=strtoupper($par);
		$path='/lead_teamlead/teamlead_choice/'.$par;
		redirect($path);
	}
	public function insertstatus($id,$k)
	{
		$statusvar="status".$k;
		$statusval=$this->input->post($statusvar);
		$this->mod->insertstatus($id,$statusval);
		//$this->render('modules/lead/project/vertical/lists/tl_show_lists');
		//If it does not work then try the following
		redirect('/lead_teamlead/teamlead_choice/LIST');
	}

	//For Ashutosh    ---Bro Need to work from here
	public function assignliststointerns($tlid)
	{
		$listid=$this->input->post('list_id');
		$vid=$this->input->post('v_id');
		$vid=$this->input->post('deadline');
		$dateInfo = date_parse_from_format('m/d/Y', $date);
		$newdate="".$dateInfo['year']."-".$dateInfo['month']."-".$dateInfo['day']."";
		$optname="opt".$listid;
		$endrec_name="endrec".$listid;
		$startrec_name="startrec".$listid;
		$internname=$this->input->post($optname);
		$startrec=$this->input->post($startrec_name);
		$endrec=$this->input->post($endrec_name);
		$this->mod->assignintern($vid,$listid,$internname,$startrec,$endrec,$tlid,$newdate);
		redirect('/lead_teamlead/teamlead_choice/DASHBOARD');
	}
	public function reassignview($internname, $listname)
	{
		$this->layout = 'layouts/zerolayout';
		$this->data['internname']=$internname;
		$this->data['listname']=$listname;
		$this->render('modules/lead/project/teamlead/reassignform');
	}
	public function reassign($oldinternname, $listname)
	{
		$this->layout = 'layouts/zerolayout';
		$newinternid=$this->input->post('selectname');
		$tlid=$_SESSION['ses']['user_id'];
		$this->mod->updateassignment($newinternid, $listname, $tlid, $oldinternname);
		$this->render('modules/lead/project/teamlead/reassign_success');
	}
}
?>