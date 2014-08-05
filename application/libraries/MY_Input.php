<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Overwrites the CodeIgnitor session class to also clean up the search_sessions table when a session is cleaned up
 */
class MY_Input extends CI_Input {
     /**
     * Clean Keys
     *
     * This is a helper function. To prevent malicious users
     * from trying to exploit keys we make sure that keys are
     * only named with alpha-numeric text and a few other items.
     *
     * @access	private
     * @param	string
     * @return	string
     */
    function _clean_input_keys($str) {
        if (!preg_match("/^[a-z0-9:_\/-]+$/i", $str)) {
            //exit("Disallowed Key Characters. [{$str}]");
            $str = 'bk_invalid';
        }

        return $str;
    }

	function ip_address()
	{
		if ($this->ip_address !== FALSE)
		{
			return $this->ip_address;
		}
 
		/*if ($this->server('HTTP_X_CLIENTIP'))
		{
			 $this->ip_address = $_SERVER['HTTP_X_CLIENTIP'];
		}
		elseif ($this->server('REMOTE_ADDR') AND $this->server('HTTP_CLIENT_IP'))
		{
			 $this->ip_address = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif ($this->server('REMOTE_ADDR'))
		{
			 $this->ip_address = $_SERVER['REMOTE_ADDR'];
		}
		elseif ($this->server('HTTP_CLIENT_IP'))
		{
			 $this->ip_address = $_SERVER['HTTP_CLIENT_IP'];
		}
		else*/
		if ($this->server('HTTP_X_FORWARDED_FOR'))
		{
			 $this->ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
 
		if ($this->ip_address === FALSE)
		{
			$this->ip_address = '0.0.0.0';
			return $this->ip_address;
		}
 
		if (strstr($this->ip_address, ','))
		{
			$x = explode(',', $this->ip_address);
			$this->ip_address = end($x);
		}
 
		if ( ! $this->valid_ip($this->ip_address))
		{
			$this->ip_address = '0.0.0.0';
		}
		//echo "###IP###".$this->ip_address;die;
		return $this->ip_address;
	}
	
	function ip_address__() 	{
 
			// call the core version of the class to get any updates
			// (note this may also break the code below...
			// ...When updating be sure to check the core ip_address() code!)             
 
			$this->ip_address = parent::ip_address();
 
            // let's get a CI instance so we can search config, etc.
            $CI =& get_instance();
 
            $CI->benchmark->mark('custom_ip_address_start');
 
            // let's get the load balancer value of config array, if it exists.
            // Make sure you autoload or call config->load() in your constructor
            // if you use a separate config file.
            //$lb_ip = $CI->config->item('load_balancer_ip');
			$lb_ip = "10.18.5.254";
            // check to see if we have the load balancer.
            if ( $lb_ip !== FALSE && $this->ip_address == '0.0.0.0' ) {
                // check to see if the original value was the load balancer.
                if ($this->server('REMOTE_ADDR') == $lb_ip) {
                    $is_load_balancer = TRUE;
                }
            // check to see if this ip matches the load balancer ip.
            } elseif ( $lb_ip !== FALSE && $this->ip_address == $lb_ip ) {
                $is_load_balancer = TRUE;
            } else {
                $is_load_balancer = FALSE;
            }
 
            // let's attmept to find the real IP. If load balancer not detected, do nothing.
            if ( $is_load_balancer ) {
                // okay get the result from the header that is sent by the load balancer.
                $lb_header_key = $CI->config->item('load_balancer_header');
                if ( $lb_header_key !== FALSE ) {
                    $real_ip = $this->server($lb_header_key);
                    if ( $this->valid_ip($real_ip)) {
                        $this->ip_address = $real_ip;
                    } else {
                        // IP from load balancer is not valid.
                        $this->ip_address = '0.0.0.0';
                    }
                }
            }
 
            $CI->benchmark->mark('custom_ip_address_end');
			echo "@@@IP@@@".$this->ip_address;die;
            return $this->ip_address;
	}

}