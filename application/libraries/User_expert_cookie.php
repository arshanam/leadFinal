<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * This class has functions that retrieve information about the user's
 * profile.
 *
 * @author     Pawan
 */
class User_expert_cookie
{

    private $CI;

	public function __construct()
			{
				 $this->CI = & get_instance();
			}
	
	public function wp_hash($data, $scheme)
		{
			if (function_exists('hash_hmac'))
			{
				return hash_hmac('md5', $data, 'rtretretretertretretrtretrttttet');
			}
			else
			{
				return $this->wp_hash_hmac('md5', $data, 'rtretretretertretretrtretrttttet');
			}
		}
	public function wp_generate_auth_cookie($login, $pass, $expiration, $scheme)
		{
			/*if (intval($this->config['version'])<26)
				$wp_hash_delim = "";
			else */
				$wp_hash_delim = "|";

			/* if (intval($this->config['version'])<28)
				$pass_frag = "";
			else */
				$pass_frag = substr($pass, 1, 3);

			$key = $this->wp_hash($login . $pass_frag. $wp_hash_delim . $expiration, $scheme);

			if (function_exists('hash_hmac'))
			{
				$hash = hash_hmac('md5', $login . $wp_hash_delim . $expiration, $key);
			}
			else
			{
				$hash = $this->wp_hash_hmac('md5', $login . $wp_hash_delim . $expiration, $key);
			}

			$cookie = $login . '|' . $expiration . '|' . $hash;

			return $cookie;
		}
	
	public function wp_url_filter($url)
		{
			return preg_replace('|\/$|i', "", $url);
		}
		
	public function wp_hash_hmac($algo, $data, $key, $raw_output = false)
	{
		$packs = array('md5' => 'H32', 'sha1' => 'H40');

		if ( !isset($packs[$algo]) )
						return false;

		$pack = $packs[$algo];
		if (strlen($key) > 64)
			$key = pack($pack, $algo($key));
		$key = str_pad($key, 64, chr(0));		
		$ipad = (substr($key, 0, 64) ^ str_repeat(chr(0x36), 64));
		$opad = (substr($key, 0, 64) ^ str_repeat(chr(0x5C), 64));

		return $algo($opad . pack($pack, $algo($ipad . $data)));
	}
	public function wp_setcookie($login, $pass, $remember = false)
    {
		/* if ($this->config['secret_key'] || $this->config['auth_key'] || intval($this->config['version']) > 25)
		{ */
			//$this->get_wp_option('siteurl')
			$site_url = 'http://'.$_SERVER['HTTP_HOST'];
			$cookiehash = md5($this->wp_url_filter($site_url));
			$cookie_path = '/' ;
			$cookie_blog_path =  '/' ;

			//echo $cookiehash.'~~~~~'.$login.'~~~~~~~~~~~~'.$cookie_path; die;

			if (!$login)
			{
				setcookie('wordpress_'.$cookiehash, '', time() - 31536000, $cookie_path);
				setcookie('wordpresspass_'.$cookiehash, '', time() - 31536000, $cookie_path);
				//remove cookies for v. 2.6
				setcookie('wordpress_'.$cookiehash, $auth_cookie, $expire, $cookie_path.'wp-admin');
				setcookie('wordpress_'.$cookiehash, $auth_cookie, $expire, $cookie_path.'wp-content/plugins');
				setcookie('wordpress_sec_'.$cookiehash, $auth_cookie, $expire, $cookie_path.'wp-admin');
				setcookie('wordpress_sec_'.$cookiehash, $auth_cookie, $expire, $cookie_path.'wp-content/plugins');
				setcookie('wordpress_logged_in_'.$cookiehash, $logged_in_cookie, $expire, $cookie_path);
			}
			else
			{
				if ($remember)
				{
					$expiration = $expire = time() + 1209600;
				}
				else
				{
					$expiration = time() + 172800;
					$expire = 0;
				}

				$secure = false; //$this->config['is_ssl'] ? true : false;

				if ( $secure )
				{
					$auth_cookie_name = "wordpress_sec_";
					$scheme = 'secure_auth';
				}
				else
				{
					$auth_cookie_name = "wordpress_";
					$scheme = 'auth';
				}

				/*
				if (intval($this->config['version']) < 26)
				{
					$auth_cookie_name = "wordpress_";
					$scheme = '';
					$secure=false;
				}
				*/

				$auth_cookie      = $this->wp_generate_auth_cookie($login, $pass, $expiration, $scheme);
				$logged_in_cookie = $this->wp_generate_auth_cookie($login, $pass, $expiration, 'logged_in');

				//echo $expire.'~~~~~~~~~~~~~~'.$cookie_path; die;

				/*
				if (intval($this->config['version']) < 26)
				{
					setcookie($auth_cookie_name.$cookiehash, $auth_cookie, $expire, $cookie_path);
				}
				else
				{
				*/
					setcookie('wordpress_'.$cookiehash, '', time() - 31536000, $cookie_path);
					setcookie('wordpresspass_'.$cookiehash, '', time() - 31536000, $cookie_path);
					//remove cookies for v. 2.6
					setcookie('wordpress_'.$cookiehash, '', time() - 31536000, $cookie_path.'wp-admin');
					setcookie('wordpress_'.$cookiehash, '', time() - 31536000, $cookie_path.'wp-content/plugins');
					setcookie('wordpress_sec_'.$cookiehash, '', time() - 31536000, $cookie_path.'wp-admin');
					setcookie('wordpress_sec_'.$cookiehash, '', time() - 31536000, $cookie_path.'wp-content/plugins');
					setcookie('wordpress_logged_in_'.$cookiehash, '', time() - 31536000, $cookie_path);

				//echo $auth_cookie_name.'~~~~~'.$cookiehash.'~~~~'.$auth_cookie.'~~~~'.$expire.'~~~~'.$cookie_blog_path.'wp-admin'.'~~~~'.null.'~~~~'.$secure; die;
					setcookie($auth_cookie_name.$cookiehash, $auth_cookie, $expire, $cookie_blog_path.'wp-admin', null, $secure);
					setcookie($auth_cookie_name.$cookiehash, $auth_cookie, $expire, $cookie_blog_path.'wp-content/plugins', null, $secure);
					setcookie('wordpress_logged_in_'.$cookiehash, $logged_in_cookie, $expire, $cookie_path, null, $secure);
					//print_r($_COOKIE); die;
				//}
			}
		/*
		}
		else
		{
			$sh = md5($_SERVER['HTTP_HOST']);
			if (!$login)
			{
				setcookie('wordpressuser_'.$sh, '', time() - 31536000, '/');
				setcookie('wordpresspass_'.$sh, '', time() - 31536000, '/');
			}
			else
			{
				setcookie('wordpressuser_'.$sh, $login, 0, '/');
				setcookie('wordpresspass_'.$sh, $pass, 0, '/');
			}
		}
		*/
	}
}