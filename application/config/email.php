<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
  | -------------------------------------------------------------------
  | EMAIL SETTINGS
  | -------------------------------------------------------------------
  | This file will contain the settings needed to send email.
  |
 */
/**
 * IMPORTANT: when updating this config, also update the forum configuration
 *  under Mail Settings
 */

if(isset($_SERVER['APP_MODE'])) {
	switch ($_SERVER['APP_MODE']) {
		/*
		case 'prod':
			$config['protocol'] = 'smtp';
			$config['smtp_host'] = '10.18.5.202';
			$config['smtp_port'] = '25';
			$config['smtp_timeout'] = '7';
			$config['smtp_user'] = '';
			$config['smtp_pass'] = '';
			$config['charset'] = 'utf-8';
			$config['newline'] = "\r\n";
			$config['mailtype'] = 'html'; //or html
			break;
			*/
		case 'prod':
		default:
			//$config['protocol'] = 'mail';
			/*$config['protocol']    = 'smtp';
			$config['smtp_host']    = 'ssl://smtp.gmail.com';
			$config['smtp_port']    = '465';
			$config['smtp_timeout'] = '7';
			$config['smtp_user'] = 'spunyani@gmail.com';
			$config['smtp_pass'] = 'gfhghgh';
			$config['charset']    = 'utf-8';
			$config['newline']    = "\r\n";
			$config['mailtype'] = 'text'; // or html
			$config['validation'] = TRUE; // bool whether to validate email or not */
			
			/*$config['protocol']    = 'smtp';
			$config['smtp_host']    = 'smtp.apptixemail.net';
			$config['smtp_port']    = '587';
			$config['smtp_timeout'] = '7';
			$config['smtp_user'] = 'supercoder@supercoder.com';
			$config['smtp_pass'] = 'password@123';
			$config['charset']    = 'utf-8';
			$config['newline']    = "";
			$config['mailtype'] = 'html'; // or html
			$config['validation'] = TRUE; // bool whether to validate email or not*/
			
			
			

			
		$config['protocol'] = 'smtp';
        $config['smtp_host'] = '10.18.5.117';
                                
        $config['smtp_port'] = '25';
        $config['smtp_timeout'] = '7';
        $config['smtp_user'] = '';
        $config['smtp_pass'] = '';
        $config['charset'] = 'utf-8';
        $config['newline'] = "";
        $config['mailtype'] = 'html'; //or html

			
		
			break;
	}
} else {
		$config['protocol'] = 'mail';
		$config['smtp_host'] = 'localhost';
		$config['smtp_port'] = '25';
		$config['smtp_timeout'] = '7';
		$config['smtp_user'] = '';
		$config['smtp_pass'] = '';
		$config['charset'] = 'utf-8';
		$config['newline'] = "\r\n";
		$config['mailtype'] = 'html'; //or html
}
/* End of file email.php */
/* Location: ./system/application/config/email.php */