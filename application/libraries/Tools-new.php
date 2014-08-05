<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * This class has functions that are used throughout the application.
 *
 * @author     Masa Gumiro
 */
class Tools {

    private $app_mode;
    private $memcache_connection;
    private $CI;

    public function __construct() {
        $this->app_mode = $_SERVER['APP_MODE'];
        $this->CI = & get_instance();
    }

    /**
     * Get APP_MODE value.
     *
     * @return string  APP_MODE value.
     */
    public function get_app_mode() {
        return $this->app_mode;
    }

    /**
     * Checks if APP_MODE is 'local'
     *
     * @return bool  TRUE if $_SERVER['APP_MODE'] is 'local' otherwise FALSE
     */
    public function is_local_mode() {
        return ( $this->app_mode == 'local' ) ? TRUE : FALSE;
    }

    /**
     * Checks if APP_MODE is 'dev'
     *
     * @return bool  TRUE if $_SERVER['APP_MODE'] is 'dev' otherwise FALSE
     */
    public function is_dev_mode() {
        return ( $this->app_mode == 'dev' ) ? TRUE : FALSE;
    }

    /**
     * Checks if APP_MODE is 'test'
     *
     * @return bool  TRUE if $_SERVER['APP_MODE'] is 'test' otherwise FALSE
     */
    public function is_test_mode() {
        return ( $this->app_mode == 'test' ) ? TRUE : FALSE;
    }

    /**
     * Checks if APP_MODE is 'prod'
     *
     * @return bool  TRUE if $_SERVER['APP_MODE'] is 'prod' otherwise FALSE
     */
    public function is_prod_mode() {
        return ( $this->app_mode == 'prod' ) ? TRUE : FALSE;
    }

    /**
     * Accepts string, Returns encrypted string. Uses sha1 hash of simple seed, TWOFISH method
     * Encodes the result in base64 for UTF friendliness
     *
     * @return STRING
     */
    public function simple_encrypt_string($decrypted_string) {
        $key = md5($this->CI->config->item('encrypt_key'));
        $encrypted_string = mcrypt_encrypt(MCRYPT_TWOFISH, $key, $decrypted_string, MCRYPT_MODE_ECB);

        $encrypted_string = base64_encode($encrypted_string);

        return $encrypted_string;
    }

    /**
     * Accepts encrypted string, Returns decrypted string. Uses sha1 hash of simple seed, TWOFISH method
     * Decodes the accepted value from base64 for UTF friendliness
     * @return STRING
     */
    public function simple_decrypt_string($encrypted_string) {
        $encrypted_string = base64_decode($encrypted_string);
        $key = md5($this->CI->config->item('encrypt_key'));
        $decrypted_string = mcrypt_decrypt(MCRYPT_TWOFISH, $key, $encrypted_string, MCRYPT_MODE_ECB);

        return $decrypted_string;
    }

    /**
     * Accepts string, Returns encrypted string. Uses TIGER128 hash of simple alternate, RIJNDAEL_128 method
     *
     * @return STRING
     */
    public function secure_encrypt_string($decrypted_string) {
        $key = hash('tiger128,3', $this->CI->config->item('encrypt_key_alt'));
        $encrypted_string = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $decrypted_string, MCRYPT_MODE_ECB);
        $encrypted_string = base64_encode($encrypted_string);
        return $encrypted_string;
    }

    /**
     * Accepts encrypted string, Returns decrypted string. Uses TIGER128 hash of alternate seed, RIJNDAEL_128 method
     *
     * @return STRING
     */
    public function secure_decrypt_string($encrypted_string) {
        $encrypted_string = base64_decode($encrypted_string);
        $key = hash('tiger128,3', $this->CI->config->item('encrypt_key_alt'));
        $decrypted_string = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $encrypted_string, MCRYPT_MODE_ECB);
        // trimming off hidden characters as part of the decryption algorithm
        return rtrim($decrypted_string, "\0\4");
    }

    /**
     * Accepts variable name for cached data as well as the data itself
     * Optional third parameter ($expire) sets the number of seconds
     * and Item is held in cache before being erased.
     *
     * @return BOOLEAN (success/fail)
     */
    public function shared_cache_store($key, $data, $expire=0, $compress = FALSE) {
		$compress = $compress ? MEMCACHE_COMPRESSED : false;
        if (($memcache = $this->shared_cache_connect()) && is_string($key)) {
            if ($memcache->set($key, $data, $compress, $expire)) {
                return TRUE;
            } else if ($memcache->replace($key, $data, $compress, $expire)) {
                return TRUE;
            }
        }

        return FALSE;

    }

    /**
     * Accepts variable name for cached data and returns the cached data
     *
     * @return MIXED (successful) OR FALSE (failed)
     */
    public function shared_cache_retrieve($key) {
        if (($memcache = $this->shared_cache_connect()) && is_string($key)) {
            return $memcache->get($key);
        }

        return FALSE;
    }

    /**
     * Retrieves a collection of values from the memcache server, by their keys.
     * If a value is not found for a given key, the key will be omitted from the
     * returned array.
     *
     * @param array $keys
     * @return mixed ARRAY if successful or FALSE if error
     */
    public function shared_cache_retrieve_multiple(array $keys) {
        if ($memcache = $this->shared_cache_connect()) {
            return $memcache->get($keys);
        }

        return FALSE;
    }

    /**
     * Accepts a variable name for cached data and deletes the data
     *
     * @return BOOLEAN (success/fail)
     */
    public function shared_cache_delete($key) {
        if (($memcache = $this->shared_cache_connect()) && is_string($key)) {
            return $memcache->delete($key, 0);
        }

        return FALSE;
    }

    /**
     * Removes all variables stored in the shared cache
     *
     * @return BOOLEAN (success/fail)
     */
    public function shared_cache_delete_all() {
        if ($memcache = $this->shared_cache_connect()) {
            return $memcache->flush();
        }
    }

    /**
     * Makes a connection to the shared cache server
     *
     * @return object
     */
    private function shared_cache_connect() {
		if (!$this->memcache_connection) {
            $this->memcache_connection = new Memcache;
            if(!$this->memcache_connection->connect($this->CI->config->item('memcache_server'), $this->CI->config->item('memcache_server_port'))) {
                return FALSE;
            }
        }
        return $this->memcache_connection;

    }
	
	 /**
     * Makes a connection to the aapc shared cache server
     *
     * @return object
     */
    private function shared_cache_aapcconnect() {
		if (!$this->memcache_connection) {
            $this->memcache_connection = new Memcache;
            if(!$this->memcache_connection->connect($this->CI->config->item('aapc_memcache_server'), $this->CI->config->item('aapc_memcache_server_port'))) {
                return FALSE;
            }
        }
        return $this->memcache_connection;

    }

	/**
	 * Array to CSV
	 *
	 * download == "" -> return CSV string
	 * download == "toto.csv" -> download file toto.csv
	 */
	function array_to_csv($array, $download = "")
    {
        if ($download != "")
        {
            header('Content-Type: application/csv');
            header('Content-Disposition: attachement; filename="' . $download . '"');
        }
        ob_start();
        $f = fopen('php://output', 'w') or show_error("Can't open php://output");
        $n = 0;
        foreach ($array as $line)
        {
            $n++;
            if ( ! fputcsv($f, $line))
            {
                show_error("Can't write line $n: $line");
            }
        }
        fclose($f) or show_error("Can't close php://output");
        $str = ob_get_contents();
        ob_end_clean();

        if ($download == "")
        {
            return $str;
        }
        else
        {
            echo $str;
        }
    }

	public function download_csv_file($download_filename='') {
		$reportpath = $this->CI->config->item('DIR_REPORT_PATH');
		if(!$download_filename) {
			$download_filename = $this->CI->uri->segment(3);
		}
		if($download_filename) {
			header('Content-Type: application/csv');
            header('Content-Disposition: attachement; filename="' . $download_filename . '"');

			$row = 1;
			if (($handle = fopen($reportpath.$download_filename, "r")) !== FALSE) {
				while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
					$num = count($data);
					for ($c=0; $c < $num; $c++) {
						$array[$row][] = $data[$c];
					}
					$row++;
				}
				fclose($handle);
			}
			ob_start();
			$f = fopen('php://output', 'w') or show_error("Can't open php://output");
			$n = 0;
			foreach ($array as $line)
			{
				$n++;
				if ( ! fputcsv($f, $line))
				{
					show_error("Can't write line $n: $line");
				}
			}
			fclose($f) or show_error("Can't close php://output");
			$str = ob_get_contents();
			ob_end_clean();

			if ($download_filename == "")
			{
				return $str;
			}
			else
			{
				echo $str;
			}
		}
	}
}