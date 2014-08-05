<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


/**
 * The Notification_email Class is used by the application to send a notification by email
 *
 * @author Kyle Gifford
 */
class Notification_email {

    /**
     * Instance of CodeIgnitor
     * @var object
     */
    private $CI;

    /**
     * Constructor
     */
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->library('email');
        $this->CI->load->helper('Image');
        $this->CI->load->helper('Address');
    }

    /**
     * Sends an email notification
     *
     * @param array $notification_data
     * @param array $recipient_data
     * @param array $custom_data
     * @return bool
     */
    public function send($notification_data, $recipient_data, $custom_data) {
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
		$bcc = (isset($email_data['bcc']) ? $email_data['bcc'] : '');
        $this->CI->email->from($from['email'], $from['label']);
        $this->CI->email->reply_to('supercoder@supercoder.com');
		if(isset($bcc) && !empty($bcc))
			$this->CI->email->bcc($bcc);
        $this->CI->email->to($to);
        $this->CI->email->subject($subject);
		//echo "<pre>"; print_r($email_data);
		//echo "message:: ".$message;exit;
        $this->CI->email->message($message);
        $this->CI->email->set_alt_message($view_data['body']);
        return $this->CI->email->send();
    }
}