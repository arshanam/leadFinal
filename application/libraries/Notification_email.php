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
		$this->CI->load->helper('phpmailer');
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
            'email' => (isset($email_data['from_address']) ? $email_data['from_address'] : 'supercoder@supercoder.com'),
            'label' => (isset($email_data['from_label']) ? $email_data['from_label'] : 'SuperCoder Order Confirmation')
        );
       $to = $recipient_data['email'];
  	   if(isset($email_data['mail_type']) && $email_data['mail_type']=='text')
	   {
		$this->CI->email->set_mailtype('text');
	   }else
	   {
		$this->CI->email->set_mailtype('html');
	   }
	  /* if(preg_match('/'.$this->CI->config->item('temp_email_domain_name').'/i', $to)) {
			//return TRUE;
		}*/
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
		
		
		$mail             = new PHPMailer();		
		$mail->IsSMTP(); // telling the class to use SMTP
		$mail->Host       = "smtp.apptixemail.net"; // sets the SMTP server
		$mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
												   // 1 = errors and messages

		$mail->SMTPAuth   = True;                  // enable SMTP authentication
		$mail->SMTPSecure = "none";                 // sets the prefix to the servier
		$mail->Host       = "smtp.apptixemail.net"; // sets the SMTP server
		$mail->Port       = 587;                    // set the SMTP port for the GMAIL server

		if(!empty($from['email']))
			{				
				 if($from['email']=='customerservice@supercoder.com')
					{
						$mail->Username   = "customerservice@supercoder.com"; // SMTP account username
						$mail->Password   = "password";  
					}
				else if($from['email']=='support@supercoder.com')
					{
						$mail->Username   = "support@supercoder.com"; // SMTP account username
						$mail->Password   = "password@123";  
					}
				else
					{
						$mail->Username   = "supercoder@supercoder.com"; // SMTP account username
						$mail->Password   = "password@123"; 					
					}
				$mail->SetFrom($from['email'], $from['label']);
				$mail->AddReplyTo($from['email'], $from['label']);		
			}
		else
			{
				$mail->Username   = "supercoder@supercoder.com"; // SMTP account username
				$mail->Password   = "password@123";  
				$mail->SetFrom('supercoder@supercoder.com', 'SuperCoder');
				$mail->AddReplyTo('supercoder@supercoder.com', 'SuperCoder');
				
			}
			
		
		if(isset($email_data['attachment'])) 
			{
				$mail->AddAttachment($email_data['attachment']);  
				
			}
	
		$email_array=array();	
		if(!empty($to))
			{
				$email_array=$this->_str_to_array($to);				
				foreach($email_array as $email)
					{
						$mail->AddAddress($email);
					}
			}
		$bcc = (isset($email_data['bcc']) ? $email_data['bcc'] : '');	
		if(isset($bcc) && !empty($bcc))
			{
				$mail->AddBCC($bcc);		
			}
		else
			{
				$mail->AddBCC('archive@supercoder.com');		
			}
        $mail->Subject    = $subject;
		$mail->AltBody    = $view_data['body']; // optional, comment out and test
		$mail->MsgHTML($message);
        return $mail->Send();
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
    public function send_old($notification_data, $recipient_data, $custom_data) {
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
            'email' => (isset($email_data['from_address']) ? $email_data['from_address'] : 'supercoder@supercoder.com'),
            'label' => (isset($email_data['from_label']) ? $email_data['from_label'] : 'SuperCoder Order Confirmation')
        );
       $to = $recipient_data['email'];
  	   if(isset($email_data['mail_type']) && $email_data['mail_type']=='text')
	   {
		$this->CI->email->set_mailtype('text');
	   }else
	   {
		$this->CI->email->set_mailtype('html');
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
		if(isset($email_data['attachment'])) 
		{
			$this->CI->email->attach($email_data['attachment']);
		}
		//////////////////////////////
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