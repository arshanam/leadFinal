<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Overwrites the CodeIgnitor session class to also clean up the search_sessions table when a session is cleaned up
 */
class MY_Session extends CI_Session {

    public $sess_use_shared_cache = FALSE;
	public $sess_regenerate_id_on_update = true;
    public $sess_shared_cache_prefix = 'sess_';

    function __construct($params = array()) {
        // duplicated from parent::__construct()
        $this->CI = & get_instance();

        // Set shared_cache config values
        foreach (array('sess_regenerate_id_on_update', 'sess_use_shared_cache', 'sess_shared_cache_prefix') as $key) {
            $this->$key = (isset($params[$key])) ? $params[$key] : $this->CI->config->item($key);
        }

        parent::__construct($params);
    }

    /**
     * Destroy the current session
     *
     * @access	public
     * @return	void
     */
    public function sess_destroy() {
        // Log the user out of the forums
        //$this->CI->load->library('MyBBIntegrator');
        //$this->CI->mybbintegrator->logout($this->CI->mybbintegrator->mybb->session->sid);

        // Kill the session from memcached
        if ($this->sess_use_shared_cache === TRUE AND isset($this->userdata['session_id'])) {
            $this->CI->tools->shared_cache_delete($this->sess_shared_cache_prefix . $this->userdata['session_id']);
        }

        // Kill the cookie
        setcookie(
                $this->sess_cookie_name,
                addslashes(serialize(array())),
                ($this->now - 31500000),
                $this->cookie_path,
                $this->cookie_domain,
                0
        );
    }

    /**
	 * Update an existing session
	 *
	 * @access	public
	 * @return	void
	 */
	function sess_update()
	{
		// We only update the session every five minutes by default
		if (($this->userdata['last_activity'] + $this->sess_time_to_update) >= $this->now)
		{
			return;
		}

		// _set_cookie() will handle this for us if we aren't using database sessions
		// by pushing all userdata to the cookie.
		$cookie_data = NULL;

		if (!$this->sess_regenerate_id_on_update || $this->CI->request->getIsAjaxRequest())
		{
			$this->userdata['last_activity'] = $this->now;

			// Update the session ID and last_activity field in memcache if needed
			if ($this->sess_use_shared_cache === TRUE) {
				// set the custom userdata, the session data we will set in a second
				$custom_userdata = $this->userdata;

				// set cookie explicitly to only have our session data
				$cookie_data = array();
				foreach (array('session_id','ip_address','user_agent','last_activity') as $val)
				{
					unset($custom_userdata[$val]);
					$cookie_data[$val] = $this->userdata[$val];
				}

				// Did we find any custom data?  If not, we turn the empty array into a string
				// since there's no reason to serialize and store an empty array in the DB
				if (count($custom_userdata) === 0) {
					$custom_userdata = '';
				} else {
					// Serialize the custom data array so we can store it
					$custom_userdata = $this->_serialize($custom_userdata);
				}

				$updated_session = $cookie_data;
				$updated_session['user_data'] = $custom_userdata;
				$updated_session['last_activity'] = $this->userdata['last_activity'];

				// set new id
				$this->CI->tools->shared_cache_store($this->sess_shared_cache_prefix . $this->userdata['session_id'], $updated_session, $this->sess_expiration);
			}

			// Write the cookie
			return $this->_set_cookie($cookie_data);
		}

		// Save the old session id so we know which record to
		// update in the database if we need it
		$old_sessid = $this->userdata['session_id'];

		$new_sessid = '';
		do
		{
			$new_sessid .= mt_rand(0, mt_getrandmax());
		}
		while (strlen($new_sessid) < 32);

		// To make the session ID even more secure we'll combine it with the user's IP
		$new_sessid .= $this->CI->input->ip_address();

		// Turn it into a hash
		$new_sessid = md5(uniqid($new_sessid, TRUE));

		// Update the session data in the session data array
		$this->userdata['session_id'] = $new_sessid;
		$this->userdata['last_activity'] = $this->now;

		// Update the session ID and last_activity field in memcache if needed
        if ($this->sess_use_shared_cache === TRUE) {
			// set the custom userdata, the session data we will set in a second
			$custom_userdata = $this->userdata;

			// set cookie explicitly to only have our session data
			$cookie_data = array();
			foreach (array('session_id','ip_address','user_agent','last_activity') as $val)
			{
				unset($custom_userdata[$val]);
				$cookie_data[$val] = $this->userdata[$val];
			}

			// Did we find any custom data?  If not, we turn the empty array into a string
			// since there's no reason to serialize and store an empty array in the DB
			if (count($custom_userdata) === 0) {
				$custom_userdata = '';
			} else {
				// Serialize the custom data array so we can store it
				$custom_userdata = $this->_serialize($custom_userdata);
			}

			$updated_session = $cookie_data;
			$updated_session['user_data'] = $custom_userdata;
			$updated_session['last_activity'] = $this->userdata['last_activity'];

			$old_session = $updated_session;
			$old_session['session_id'] = $old_sessid;

			// expire old session id
			$this->CI->tools->shared_cache_store($this->sess_shared_cache_prefix . $old_sessid, $old_session, $this->sess_time_to_update);

			// set new id
			$this->CI->tools->shared_cache_store($this->sess_shared_cache_prefix . $this->userdata['session_id'], $updated_session, $this->sess_expiration);
        }

		// Write the cookie
		$this->_set_cookie($cookie_data);
	}

    /**
     * Fetch the current session data if it exists
     *
     * @overridden to support memcached session storage
     * @access	public
     * @return	bool
     */
    function sess_read() {
        // Fetch the cookie
        $session = $this->CI->input->cookie($this->sess_cookie_name);

        // No cookie?  Goodbye cruel world!...
        if ($session === FALSE) {
            log_message('debug', 'A session cookie was not found.');

            return FALSE;
        }

        // Decrypt the cookie data
        if ($this->sess_encrypt_cookie == TRUE) {
            $session = $this->CI->encrypt->decode($session);
        } else {
            // encryption was not used, so we need to check the md5 hash
            $hash = substr($session, strlen($session) - 32); // get last 32 chars
            $session = substr($session, 0, strlen($session) - 32);

            // Does the md5 hash match?  This is to prevent manipulation of session data in userspace
            if ($hash !== md5($session . $this->encryption_key)) {

                log_message('error', 'The session cookie data did not match what was expected. This could be a possible hacking attempt.');
                $this->sess_destroy();
                return FALSE;
            }
        }

        // Unserialize the session array
        $session = $this->_unserialize($session);

        // Is the session data we unserialized an array with the correct format?
        if (!is_array($session) OR !isset($session['session_id']) OR !isset($session['ip_address']) OR !isset($session['user_agent']) OR !isset($session['last_activity'])) {

            $this->sess_destroy();
            return FALSE;
        }

        // Is the session current?
        if (($session['last_activity'] + $this->sess_expiration) < $this->now) {

			$this->sess_destroy();
            return FALSE;
        }

        // Is there a corresponding session in memcached?
        if ($this->sess_use_shared_cache === TRUE) {
            if ($cached_session = $this->CI->tools->shared_cache_retrieve($this->sess_shared_cache_prefix . $session['session_id'])) {
                if ($this->sess_match_ip == TRUE && isset($cached_session['ip_address']) && $cached_session['ip_address'] != $session['ip_address']) {
                    $this->sess_destroy();
                    return FALSE;
                }

                if ($this->sess_match_useragent == TRUE && isset($cached_session['user_agent']) && $cached_session['user_agent'] != $session['user_agent']) {
                    $this->sess_destroy();
                    return FALSE;
                }
            } else {
				$this->CI->load->library('Datamapper');
				$this->CI->load->model('Logs');

				$logger = new Logs();
				$session['time_since_last_activity'] = time() - $session['last_activity'];
				$logger->description = 'Memcache session retrieval failed: ' . print_r($session, 1);
				$logger->save();
				unset($session['time_since_last_activity']);

                // No result?  Kill it!
                $this->sess_destroy();

                return FALSE;
            }

            // Is there custom data?  If so, add it to the main session array
            if (isset($cached_session['user_data']) AND $cached_session['user_data'] != '') {
                $custom_data = $this->_unserialize($cached_session['user_data']);

                if (is_array($custom_data)) {
                    foreach ($custom_data as $key => $val) {
                        $session[$key] = $val;
                    }
                }
            }
        } else {
			// Does the IP Match?
			if ($this->sess_match_ip == TRUE AND $session['ip_address'] != $this->CI->input->ip_address()) {
				$this->sess_destroy();
				return FALSE;
			}

			// Does the User Agent Match?
			if ($this->sess_match_useragent == TRUE AND trim($session['user_agent']) != trim(substr($this->CI->input->user_agent(), 0, 50))) {
				$this->sess_destroy();
				return FALSE;
			}
		}

        // Session is valid!
        $this->userdata = $session;

        return TRUE;
    }

    /**
     * Write the session data
     *
     * @access	public
     * @return	void
     */
    function sess_write() {
        // Are we saving custom data to the DB?  If not, all we do is update the cookie
        if ($this->sess_use_shared_cache === FALSE) {
            $this->_set_cookie();
            return;
        }

        // set the custom userdata, the session data we will set in a second
        $custom_userdata = $this->userdata;
        $cookie_userdata = array();

        // Before continuing, we need to determine if there is any custom data to deal with.
        // Let's determine this by removing the default indexes to see if there's anything left in the array
        // and set the session data while we're at it
        foreach (array('session_id', 'ip_address', 'user_agent', 'last_activity') as $val) {
            unset($custom_userdata[$val]);
            $cookie_userdata[$val] = $this->userdata[$val];
        }

        // Did we find any custom data?  If not, we turn the empty array into a string
        // since there's no reason to serialize and store an empty array in the DB
        if (count($custom_userdata) === 0) {
            $custom_userdata = '';
        } else {
            // Serialize the custom data array so we can store it
            $custom_userdata = $this->_serialize($custom_userdata);
        }

        $updated_session = $cookie_userdata;
        $updated_session['user_data'] = $custom_userdata;
        $updated_session['last_activity'] = $this->userdata['last_activity'];

        // update memcached

        $this->CI->tools->shared_cache_store($this->sess_shared_cache_prefix . $this->userdata['session_id'], $updated_session, $this->sess_expiration);

        // Write the cookie.  Notice that we manually pass the cookie data array to the
        // _set_cookie() function. Normally that function will store $this->userdata, but
        // in this case that array contains custom data, which we do not want in the cookie.
        $this->_set_cookie($cookie_userdata);
    }

    /**
     * Create a new session
     *
     * @access	public
     * @return	void
     */
    function sess_create() {
        $sessid = '';
        while (strlen($sessid) < 32) {
            $sessid .= mt_rand(0, mt_getrandmax());
        }

        // To make the session ID even more secure we'll combine it with the user's IP
        $sessid .= $this->CI->input->ip_address();

        $this->userdata = array(
            'session_id' => md5(uniqid($sessid, TRUE)),
            'ip_address' => $this->CI->input->ip_address(),
            'user_agent' => substr($this->CI->input->user_agent(), 0, 50),
            'last_activity' => $this->now
        );

        // Save the data to memcached if needed
        if ($this->sess_use_shared_cache === TRUE) {
            $this->CI->tools->shared_cache_store($this->sess_shared_cache_prefix . $this->userdata['session_id'], $this->userdata, $this->sess_expiration);
        }

        // Write the cookie
        $this->_set_cookie();
    }

}
