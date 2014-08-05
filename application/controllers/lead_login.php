<?php
/**
 * This class is responsible for processing all account registrations on the site.
 *
 * Login Controller
 */
class Lead_login extends MY_Controller
{

	protected $controller_url = '';
    /**
     * Class constructor
     */
	private $CI;
	public function __construct()
	{
	  parent::__construct();
		$this->CI = & get_instance();
		$this->load->model('login_model');
		session_start();
	}//Contructor End

    /**
     * Login action.
     */
   public function index()
   {
		if(isset($_SESSION['ses']))
		{
			header('Location: /lead_superadmin');
		}
		else
		{
			$this->layout = 'layouts/site4';
   			$this->render('modules/login/login_user');

			if($this->input->post('email') && $this->input->post('password'))
			{

				$login = trim($this->input->post('email'));
				$pass = trim($this->input->post('password'));

				$var=$this->login_model->verify($login, $pass);//var returns 0 for error
				if($var!=1)
				{ //Error Login
					header('Location: /lead_login/error_login');
				}
				else if($var==1)
				{	//Success Login
					$row=$this->login_model->fetchval($login);
					$_SESSION['ses']=$row;
					header('Location: /lead_superadmin');
				}
			}
		}
	}
	public function error_login()
	{
		$this->layout = 'layouts/site3';
		$this->data['msg']="Username Password combination error";
		$this->render('modules/error/error_login');
	}

	public function logout()
	{
		if(isset($_SESSION['ses_vid']))
		{
			unset($_SESSION['ses_vid']);
		}
		unset($_SESSION['ses']);
		$homepage=current_base_url();
		redirect($homepage);
	}
}
?>