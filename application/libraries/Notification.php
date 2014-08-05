<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


/**
 * Core class to handle notifications
 *
 * @author Kyle Gifford
 */
class Notification {

    /**
     * Instance of CodeIgnitor
     * @var object
     */
    private $CI;

    /**
     * Array of all available notifications (system and user)
     * @var array
     */
    private $notifications;

    /**
     * Array of all notification methods
     * @var array
     */
    private $notification_methods;

    /**
     * Class constructor
     */
    public function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->config('user_notifications');
        $this->CI->load->config('system_notifications');
        $this->notifications = array_merge($this->CI->config->item('system_notifications'), $this->CI->config->item('user_notifications'));
        $this->notification_methods = $this->CI->config->item('user_notification_contact_types');
    }

    /**
     * Sends a notification to the recipient(s)
     *
     * @param string $notification_name Name of the notification to send
     * @param int/string/array $recipients List of recipients to send to (can be user_id, username, or email addresses)
     * @param array $custom_data Array of custom data that will be merged into the notification
     * @return bool Whether notification was sent successfully
     */
    public function send($notification_name, $recipients, $custom_data = array()) {
		// check if this notification exists
        if(!array_key_exists($notification_name, $this->notifications)) {
            return FALSE;
        }

        // if the recipients list is not already an array, convert it to one
        $recipients = (array)$recipients;
        if(!count($recipients)) {
            return FALSE;
        }

        // load notification data
        $notification_data = $this->notifications[$notification_name];
        $notification_methods = array();
        foreach($this->notification_methods as $method) {
            if($this->valid_method_for_notification($notification_data, $method)) {
                // lazy load the notification library, if not already loaded
                $this->CI->load->library('Notification_'.$method['name']);

                // add this method as a valid method to send with
                $notification_methods[] = $method;
            }
        }

        // if there are no valid notification methods defined for this notification, no need to continue
        if(!count($notification_methods)) {
            return FALSE;
        }

        // loop through each recipient, and notify them
        $result = array();
        foreach($recipients as $recipient) {

            // load data about the recipient
			$user_model = new Users();
			if(!$user_model->initialize($recipient)) {
                //return FALSE;
            }
			if($user_model->exists()) {
				$recipient_data = $user_model->get_user_row();
			} else {
				$recipient_data['email'] = $recipient;
			}

			$recipient_data['preferences'] = array();

            // loop through the notification methods
            foreach($notification_methods as $method) {
                // if the recipient has not opted out of receiving this notification by this method, send them the notification
                if(!$this->opted_out($notification_data, $method, $recipient_data['preferences'])) {
					$result[] = $this->CI->{'notification_'.$method['name']}->send($notification_data, $recipient_data, $custom_data);
                }
            }
        }

        // if none of the notifications return FALSE, then send was successful
        return !in_array(FALSE, $result, TRUE);
    }

 public function send_new($notification_name, $recipients, $custom_data = array()) {
		// check if this notification exists
        if(!array_key_exists($notification_name, $this->notifications)) {
            return FALSE;
        }

        // if the recipients list is not already an array, convert it to one
        $recipients = (array)$recipients;
        if(!count($recipients)) {
            return FALSE;
        }

        // load notification data
        $notification_data = $this->notifications[$notification_name];
        $notification_methods = array();
        foreach($this->notification_methods as $method) {
            if($this->valid_method_for_notification($notification_data, $method)) {
                // lazy load the notification library, if not already loaded
                $this->CI->load->library('Notification_'.$method['name']);

                // add this method as a valid method to send with
                $notification_methods[] = $method;
            }
        }

        // if there are no valid notification methods defined for this notification, no need to continue
        if(!count($notification_methods)) {
            return FALSE;
        }

        // loop through each recipient, and notify them
        $result = array();
        foreach($recipients as $recipient) {

            // load data about the recipient
			$recipient_data['email']=$recipient;
            // loop through the notification methods
            foreach($notification_methods as $method) {
                // if the recipient has not opted out of receiving this notification by this method, send them the notification
					$result[] = $this->CI->{'notification_'.$method['name']}->send($notification_data, $recipient_data, $custom_data);
            }
        }

        // if none of the notifications return FALSE, then send was successful
        return !in_array(FALSE, $result, TRUE);
    }
	
  /**
     * Sends a notification to the recipient(s)
     *
     * @param string $notification_name Name of the notification to send
     * @param int/string/array $recipients List of recipients to send to (can be user_id, username, or email addresses)
     * @param array $custom_data Array of custom data that will be merged into the notification
     * @return bool Whether notification was sent successfully
     */
    public function send_admin($notification_name, $recipients, $custom_data = array()) {
		// check if this notification exists
        if(!array_key_exists($notification_name, $this->notifications)) {
            return FALSE;
        }

        // if the recipients list is not already an array, convert it to one
        $recipients = (array)$recipients;
        if(!count($recipients)) {
            return FALSE;
        }

        // load notification data
        $notification_data = $this->notifications[$notification_name];

        $notification_methods = array();
        foreach($this->notification_methods as $method) {
            if($this->valid_method_for_notification($notification_data, $method)) {
                // lazy load the notification library, if not already loaded
                $this->CI->load->library('Notification_'.$method['name']);

                // add this method as a valid method to send with
              $notification_methods[] = $method;
            }
        }


        // if there are no valid notification methods defined for this notification, no need to continue
        if(!count($notification_methods)) {
            return FALSE;
        }

        // loop through each recipient, and notify them
        $result = array();
        foreach($recipients as $recipient) {

            // load data about the recipient

			$recipient_data['email']=$recipient;



            // loop through the notification methods
            foreach($notification_methods as $method) {
                // if the recipient has not opted out of receiving this notification by this method, send them the notification
     
                    $result[] = $this->CI->{'notification_'.$method['name']}->send($notification_data, $recipient_data, $custom_data);

            }
        }

        // if none of the notifications return FALSE, then send was successful
        return !in_array(FALSE, $result, TRUE);
    }
 /**
     * Sends a notification to the recipient(s)
     *
     * @param string $notification_name Name of the notification to send
     * @param int/string/array $recipients List of recipients to send to (can be user_id, username, or email addresses)
     * @param array $custom_data Array of custom data that will be merged into the notification
     * @return bool Whether notification was sent successfully
     */
    public function send_admin_new($notification_name, $recipients, $custom_data = array()) {
		// check if this notification exists
        if(!array_key_exists($notification_name, $this->notifications)) {
            return FALSE;
        }

        // if the recipients list is not already an array, convert it to one
        $recipients = (array)$recipients;
        if(!count($recipients)) {
            return FALSE;
        }

        // load notification data
        $notification_data = $this->notifications[$notification_name];

        $notification_methods = array();
        foreach($this->notification_methods as $method) {
            if($this->valid_method_for_notification($notification_data, $method)) {
                // lazy load the notification library, if not already loaded
                $this->CI->load->library('Notification_'.$method['name']);

                // add this method as a valid method to send with
              $notification_methods[] = $method;
            }
        }


        // if there are no valid notification methods defined for this notification, no need to continue
        if(!count($notification_methods)) {
            return FALSE;
        }

        // loop through each recipient, and notify them
        $result = array();
        foreach($recipients as $recipient) {

            // load data about the recipient

			$recipient_data['email']=$recipient;



            // loop through the notification methods
            foreach($notification_methods as $method) {
                // if the recipient has not opted out of receiving this notification by this method, send them the notification
     
                    $result[] = $this->CI->{'notification_'.$method['name']}->send_old($notification_data, $recipient_data, $custom_data);

            }
        }

        // if none of the notifications return FALSE, then send was successful
        return !in_array(FALSE, $result, TRUE);
    }
    /**
     * Checks if a notification can be sent by a given method
     * Used internally by send()
     *
     * @param array $notification_data
     * @param array $method
     * @return bool
     */
    private function valid_method_for_notification($notification_data, $method) {
        return (array_key_exists($method['name'], $notification_data) && (bool)$notification_data[$method['name']] == TRUE);
    }

    /**
     * Checks if a user has opted out of a notification by a particular method
     * Used internally by send()
     *
     * @param array $notification_data
     * @param array $method
     * @param array $user_preferences
     * @return bool
     */
    private function opted_out($notification_data, $method, $user_preferences) {
        return ( isset($notification_data['opt_out_preference_name']) &&
                 array_key_exists($notification_data['opt_out_preference_name'] . $method['preference_postfix'], $user_preferences) &&
                 (bool)$user_preferences[$notification_data['opt_out_preference_name'] . $method['preference_postfix']]
               ) ||
               ( array_key_exists('global_opt_out', $user_preferences) &&
                (bool)$user_preferences['global_opt_out']
               );
    }
}