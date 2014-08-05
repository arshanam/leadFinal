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
		$this->CI->load->helper('phpmailer');
        $this->notifications = array_merge($this->CI->config->item('system_notifications'), $this->CI->config->item('user_notifications'));
        $this->notification_methods = $this->CI->config->item('user_notification_contact_types');
		 $this->mail             = new PHPMailer();		
		 $this->mail->IsSMTP(); // telling the class to use SMTP
		 $this->mail->Host       = "smtp.apptixemail.net"; // sets the SMTP server
		 $this->mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
												   // 1 = errors and messages
		 $this->mail->SMTPAuth   = True;                  // enable SMTP authentication
		 $this->mail->SMTPSecure = "none";                 // sets the prefix to the servier
		 $this->mail->Host       = "smtp.apptixemail.net"; // sets the SMTP server
		 $this->mail->Port       = 587;                    // set the SMTP port for the GMAIL server
		

		
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
$notification_data = $this->notifications[$notification_name];
        // load notification data
        /*$notification_data = $this->notifications[$notification_name];
        $notification_methods = array();
        foreach($this->notification_methods as $method) {
            if($this->valid_method_for_notification($notification_data, $method)) {
                // lazy load the notification library, if not already loaded
                $this->CI->load->library('Notification_'.$method['name']);

                // add this method as a valid method to send with
                $notification_methods[] = $method;
            }
        }*/

        // if there are no valid notification methods defined for this notification, no need to continue
       /* if(!count($notification_methods)) {
            return FALSE;
        }*/

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
            //foreach($notification_methods as $method) {
                // if the recipient has not opted out of receiving this notification by this method, send them the notification
               /* if(!$this->opted_out($notification_data, $method, $recipient_data['preferences'])) {
					$result[] = $this->CI->{'notification_'.$method['name']}->send($notification_data, $recipient_data, $custom_data);
                }*/
				$result[]=$this->send_email($notification_data, $recipient_data, $custom_data);
            //}
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

       /* $notification_methods = array();
        foreach($this->notification_methods as $method) {
            if($this->valid_method_for_notification($notification_data, $method)) {
                // lazy load the notification library, if not already loaded
                $this->CI->load->library('Notification_'.$method['name']);

                // add this method as a valid method to send with
              $notification_methods[] = $method;
            }
        }*/


        // if there are no valid notification methods defined for this notification, no need to continue
       /* if(!count($notification_methods)) {
            return FALSE;
        }*/

        // loop through each recipient, and notify them
        $result = array();
        foreach($recipients as $recipient) {
            // load data about the recipient

			$recipient_data['email']=$recipient;
            // loop through the notification methods
           
                // if the recipient has not opted out of receiving this notification by this method, send them the notification
     
                    $result[] = $this->send_email($notification_data, $recipient_data, $custom_data);
        }

        // if none of the notifications return FALSE, then send was successful
        return !in_array(FALSE, $result, TRUE);
    }
	
	 public function send_email($notification_data, $recipient_data, $custom_data) {
        // load all the email data into one array (so custom data can over-ride the default notification data)
        $email_data = (array_key_exists('email', $notification_data) && is_array($notification_data['email'])) ? array_merge($notification_data['email'], $custom_data) : $custom_data;

        // if there's no body template, then no reason to continue
        $body_view = isset($email_data['body']) ? $email_data['body'] : FALSE;
        if(!$body_view) {
            return FALSE;
        }
	
        // retrieve the message body from a view
        $view_data = $email_data;
        $view_data['recipient'] = $recipient_data;
        $view_data['current_user'] = $this->CI->get_current_user();
        $view_data['body'] = $this->CI->load->view('email/body/' . strtolower($body_view), $view_data, TRUE);

        // get the message
        $message_view = isset($email_data['template']) ? $email_data['template'] : 'generic';
        $message = $this->CI->load->view('email/template/' . strtolower($message_view), $view_data, TRUE);

        // define the from, to, and subject parameters
        $from = array(
            'email' => (isset($email_data['from_address']) ? $email_data['from_address'] : 'support@supercoder.com'),
            'label' => (isset($email_data['from_label']) ? $email_data['from_label'] : 'SuperCoder Order Confirmation')
        );
       $to = $recipient_data['email'];
  	   if(isset($email_data['mail_type']) && $email_data['mail_type']=='text')
	   {
		//$this->CI->email->set_mailtype('text');
	   }else
	   {
		//$this->CI->email->set_mailtype('html');
	   }
	   if(preg_match('/'.$this->CI->config->item('temp_email_domain_name').'/i', $to)) {
			//return TRUE;
		}
		/*if(isset($email_data['order_data']['order_id'])) {
			if(isset($email_data['payment']) && $email_data['payment'] == 1) {
				$subject = $email_data['subject'] = 'Your order receipt #'.$email_data['order_data']['order_id'];
			} else {
				$subject = $email_data['subject'] = 'Your order invoice #'.$email_data['order_data']['order_id'];
			}
		} else {
		   $subject = (isset($email_data['subject']) ? $email_data['subject'] : 'Your order invoice');
		}*/
		$subject = (isset($email_data['subject']) ? $email_data['subject'] : 'Your order invoice');
		// send the message
		//////// Attachment  /////////
		
		//////////////////////////////
		
		
	
		if(!empty($from['email']))
			{				
				 if($from['email']=='customerservice@supercoder.com')
					{
						$this->mail->Username   = "customerservice@supercoder.com"; // SMTP account username
						$this->mail->Password   = "password";  
					}
				else if($from['email']=='supercoder@supercoder.com')
					{
						$this->mail->Username   = "supercoder@supercoder.com"; // SMTP account username
						$this->mail->Password   = "password@123";  
					}
				else
					{
						$this->mail->Username   = "support@supercoder.com"; // SMTP account username
						$this->mail->Password   = "password@123"; 					
					}
				$this->mail->SetFrom($from['email'], $from['label']);
				$this->mail->AddReplyTo($from['email'], $from['label']);		
			}
		else
			{
				$this->mail->Username   = "support@supercoder.com"; // SMTP account username
				$this->mail->Password   = "password@123";  
				$this->mail->SetFrom('support@supercoder.com', 'SuperCoder');
				$this->mail->AddReplyTo('support@supercoder.com', 'SuperCoder');
				
			}
			
		
		if(isset($email_data['attachment'])) 
			{
				$this->mail->AddAttachment($email_data['attachment']);  
				
			}
	
		$email_array=array();	
		if(!empty($to))
			{
				$email_array=$this->_str_to_array($to);				
				foreach($email_array as $email)
					{
						$this->mail->AddAddress($email);
					}
			}
        $this->mail->Subject    = $subject;
		$this->mail->AltBody    = $view_data['body']; // optional, comment out and test
		$this->mail->MsgHTML($message);
        return $this->mail->Send();
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
public function _str_to_array($email)
	{
		if ( ! is_array($email))
		{
			if (strpos($email, ',') !== FALSE)
			{
				$email = preg_split('/[\s,]/', $email, -1, PREG_SPLIT_NO_EMPTY);
			}
			else
			{
				$email = trim($email);
				settype($email, "array");
			}
		}
		return $email;
	}
}