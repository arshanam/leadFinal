<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MY_Exceptions
 *
 * @author kgifford
 */
class MY_Exceptions extends CI_Exceptions {
function MY_Exceptions(){
        parent::CI_Exceptions();
	
    }

  function show_404($page=''){
			set_status_header('404');
		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();	
		}
		ob_start();
		include(APPPATH.'views/modules/global/front/header_error.php');
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
		
	
   
    }
//    public function show_404($page = '') {
//        $code = '404';
//        $text = 'Page not found';
//
//        // add this line to get the base URL
//        $this->config = & get_config();
//        $base_url = $this->config['base_url'];
//
//        $server_protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : FALSE;
//
//        if (substr(php_sapi_name(), 0, 3) == 'cgi') {
//            header("Status: {$code} {$text}", TRUE);
//        } elseif ($server_protocol == 'HTTP/1.1' OR $server_protocol == 'HTTP/1.0') {
//            header($server_protocol . " {$code} {$text}", TRUE, $code);
//        } else {
//            header("HTTP/1.1 {$code} {$text}", TRUE, $code);
//        }
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $base_url . '/home/error_404/');
//        curl_setopt($ch, CURLOPT_HEADER, 0);
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, 'originalURL=' . urlencode($_SERVER['REQUEST_URI']));
//        curl_exec($ch); // WordPress won't let me post the word e x e c - remove the space in the previous code to make it run...
//        curl_close($ch);
//    }
}