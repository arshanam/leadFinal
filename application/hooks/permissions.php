<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of permissions
 *
 * @author kgifford
 */
class permissions {

    /**
     * The CI controller object instance
     * @var object
     */
    private $CI;

    /*
     * Copy of the current_user object stored in the controller
     * @var array
     */
    private $current_user;

    /**
     * Constructor for permissions - not in use
     */
    function __constructor() {
    }

    /**
     * Core function, called by the post_controller_constructor hook
     * Reads permissions from the controller and performs appropriate lookups for access
     */
    public function check_access() {
        // load the current controller, the current action, and the permissions array
        $this->CI =& get_instance();
        $current_action = $this->CI->uri->rsegments[2];
        $controller_permissions = $this->CI->get_permissions();
        $this->current_user = $this->CI->get_current_user();

        // determine the permissions that $current_action requires to execute
        $permissions = array();
        // check for specific permissions for this action
       // check for specific permissions for this action
        if(key_exists($current_action, $controller_permissions)) {
            $permissions = $controller_permissions[$current_action];
        } else if(key_exists('*', $controller_permissions)) {
            $permissions = $controller_permissions['*'];
        }
        /*
         * Loop through each permission, and check access
         * Calls a member function is_{permission}, which should return bool true/false
         * If result is false, then the function not_{permission} function is called which acts accordingly
         */

		if(!empty($permissions)) {
			foreach($permissions as $permission) {
				if(!$this->{"is_$permission"}()) {
					$this->{"not_$permission"}();
					break;
				}
			}
		} else {
			 //show_404();
		}
    }

    /**
     * Checks if the user is logged in
     * @return bool
     */
    private function is_logged_in() {
        return (bool)is_logged_in();
    }

    /**
     * Shows the user login form if not logged in
     * @todo add error message
     */
    private function not_logged_in() {
        $this->CI->add_error('You must be logged in to access this page');
        $this->CI->override_user_action('user_login_form');
    }

    /**
     * Checks if the user is logged in
     * @return bool
     */
    private function is_logged_in_ajax() {
        return is_logged_in();
    }

    /**
     * Shows the user login form if not logged in
     * @todo add error message
     */
    private function not_logged_in_ajax() {
        show_404();
    }

    /**
     * Checks that the user is not logged in
     * @return bool
     */
    private function is_logged_out() {
        return !(bool)is_logged_in();
    }

    /**
     * If the user is not logged out, return them to the root page
     * @todo add a message here
     */
    private function not_logged_out() {
		//changed the redirect path by laxman to search page.
       // $this->CI->add_error('The page you were trying to access is only available for new users', TRUE);
        redirect('/home/');
    }

    /**
     * Checks if the logged in user is an administrator
     * @todo actually do the the check
     * @return bool
     */
    private function is_admin() {
        return (bool)is_admin();
    }

    /**
     * Shows the admin login form if not an admin
     * @todo change action to make more sense
     */
    private function not_admin() {
        $this->CI->override_user_action('login_form');
    }

	/**
     * Checks if the logged in user is an staff
     * @todo actually do the the check
     * @return bool
     */
    private function is_staff() {
        return (bool)is_staff();
    }

	/**
     * Shows the admin login form if not an staff
     * @todo change action to make more sense
     */
    private function not_staff() {
        $this->CI->override_user_action('login_form');
    }

	/**
     * Checks if the logged in user is an employee user
     * @todo actually do the the check
     * @return bool
     */
    private function is_user() {
        return (bool)is_user();
    }

	/**
     * Checks if the logged in user is an employee user
     * @todo actually do the the check
     * @return bool
     */
    private function not_user() {
        $this->CI->override_user_action('login_form');
    }

	/**
     * Checks if the logged in user is an employee user
     * @todo actually do the the check
     * @return bool
     */
    private function is_moderator() {
        return (bool)is_moderator();
    }

	/**
     * Checks if the logged in user is an employee user
     * @todo actually do the the check
     * @return bool
     */
    private function not_moderator() {
        $this->CI->override_user_action('login_form');
    }

    /**
     * Checks if the logged in user is active
     * @return bool
     */
    private function is_active() {
        return (bool)$this->current_user['permissions']['active'];
    }

    /**
     * Shows an error and denies access if the current logged in user is not active
     * @todo consider changing this action
     */
    private function not_active() {
        $this->CI->add_error('You must be an active user access this page', TRUE);
        redirect('/');
    }

    private function is_local_ip() {
        $check = FALSE;
		/*if(preg_match('/192.168./',$_SERVER['REMOTE_ADDR'])) {
			$check = TRUE;
		} else if(preg_match('/172.18./',$_SERVER['REMOTE_ADDR'])) {
			$check = TRUE;
		}else if(preg_match('/172.17./',$_SERVER['REMOTE_ADDR'])) {
			$check = TRUE;
		}else if(preg_match('/10.18.5/',$_SERVER['REMOTE_ADDR'])) {
			$check = TRUE;
		}*/

		return TRUE;
    }

    private function not_local_ip() {
        $this->CI->add_error('You do not have access to this page', TRUE);
        redirect('/');
    }
}