<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['user_notification_contact_types'] = array(
    array(
        'name' => 'email',
        'label' => 'Email',
        'preference_postfix' => '_email'
    )
);

$config['user_notifications'] = array(
	 // User notifications
    'unauthorised_access' => array(
        'from_label' => 'Unauthorised Login',
        'description' => 'Sent when internal user try to login from outer IP',
        'name' => 'system_unauthorised_access',
        'opt_out_preference_name' => FALSE,
        'email' => array(
            'body' => 'user/unauthorised_access',
			'from_label' => 'Unauthorised Login',
			'template' => 'generic_promotion',
            'subject' => 'Unauthorised Login on SuperCoder'
        )
    ),
    'reset_password' => array(
        'from_label' => 'Request to change a user\'s password initiated',
        'description' => 'Sent when a request to change user\'s password is initiated',
        'name' => 'system_reset_password',
        'opt_out_preference_name' => FALSE,
        'email' => array(
            'body' => 'user/reset_password',
			'from_label' => 'Request to change a user\'s password initiated',
			'template' => 'generic_promotion',
            'subject' => 'Confirm Password Reset'
        )
    ),
	'reset_password_changed' => array(
        'from_label' => 'Request to change a user\'s password initiated',
        'description' => 'Sent when a request to change user\'s password is initiated',
        'name' => 'system_reset_password',
        'opt_out_preference_name' => FALSE,
        'email' => array(
            'body' => 'user/reset_pass_changed',
			'from_label' => 'SuperCoder Password Changed',
			'template' => 'generic_promotion',
            'subject' => 'SuperCoder Password Changed'
        )
    ),	
	 // User notifications
    'sc_demo' => array(
		'from_address'=>'supercoder@supercoder.com',
		'from_label'=>'SuperCoder',
        'label' => 'Request for demo',
        'description' => 'Sent when a request to demo is initiated',
        'name' => 'system_sc_demo',
        'opt_out_preference_name' => FALSE,
        'email' => array(
            'body' => 'user/sc_demo',
            'subject' => 'Supercoder Demo'
        )
    ),
	// Order Recived
	'order_email' => array(
        'from_address'=>'supercoder@supercoder.com',
		'label' => 'SuperCoder',
		'subject' => 'SuperCoder Order Confirmation Receipt',
        'description' => '',
        'name' => 'order_received',
        'opt_out_preference_name' => 'notification_order_email_opt_out',
        'email' => array(
            'body' => 'order/invoice',
            'template' => 'generic',
            'subject' => 'SuperCoder Order Confirmation Receipt'
        )
    ),
	// COF Order with shipping address Recived
	'cof_order_email_with_shipping' => array(
        'from_address'=>'supercoder@supercoder.com',
		'from_label'=>'SuperCoder Order Confirmation',
		'label' => 'SuperCoder',
		'subject' => 'Your order invoice',
        'description' => '',
        'name' => 'cof_order_email_with_shipping',
        'opt_out_preference_name' => 'notification_cof_order_email_with_shipping_opt_out',
        'email' => array(
            'body' => 'order/cof_order_email_with_shipping',
            'template' => 'generic_mail',
            'subject' => 'Your order invoice.'
        )
    ),
	// COF Order without shipping address Recived
	'cof_order_email_without_shipping' => array(
        'from_address'=>'supercoder@supercoder.com',
		'from_label'=>'SuperCoder Order Confirmation',
		'label' => 'SuperCoder',
		'subject' => 'Your order invoice',
        'description' => '',
        'name' => 'cof_order_email_without_shipping',
        'opt_out_preference_name' => 'notification_cof_order_email_without_shipping_opt_out',
        'email' => array(
            'body' => 'order/cof_order_email_without_shipping',
            'template' => 'plain_mail',
            'subject' => 'Your order invoice.'
        )
    ),
	// COF Order without cc details - So emailed to user with token to process
	'cof_token_cc_process' => array(
        'from_address'=>'supercoder@supercoder.com',
		'from_label'=>'SuperCoder Order Confirmation',
		'label' => 'SuperCoder',
		'subject' => 'Process with secure CC',
        'description' => '',
        'name' => 'cof_token_cc_process',
        'opt_out_preference_name' => 'notification_cof_token_cc_process_opt_out',
        'email' => array(
            'body' => 'order/cof_token_cc_process',
            'template' => 'generic_mail',
            'subject' => 'Process with secure CC'
        )
    ),

	//Registered User Email
	'registered_user' => array(
        'from_address'=>'supercoder@supercoder.com',
		'from_label'=>'SuperCoder Registration',
		'subject' => 'Welcome to the Supercoder.com family!',
        'description' => '',
        'name' => 'registered_user',
        'opt_out_preference_name' => 'notification_registered_user_opt_out',
        'email' => array(
            'body' => 'user/registered_user',
            'template' => 'generic_promotion',
            'subject' => 'Welcome to the Supercoder.com family'
        )
    ),

	//Refer a friend Email
	'users_referals' => array(
        'from_address'=>'supercoder@supercoder.com',
		'from_label'=>'SuperCoder Registration',
		'subject' => 'Welcome to the Supercoder.com family!',
        'description' => '',
        'name' => 'registered_user',
        'opt_out_preference_name' => 'notification_registered_user_opt_out',
        'email' => array(
            'body' => 'user/refer_a_friend',
            'template' => 'generic_promotion',
            'subject' => 'Invitation to join SuperCoder'
        )
    ),

	// Order with shipping address Recived
	'invoice_with_shipping' => array(
        'from_address'=>'supercoder@supercoder.com',
		'label' => 'SuperCoder',
		'subject' => 'SuperCoder Order Confirmation Receipt',
        'description' => '',
        'name' => 'invoice_with_shipping',
        'opt_out_preference_name' => 'notification_order_email_with_shipping_opt_out',
        'email' => array(
            'body' => 'order/invoice_with_shipping',
            'template' => 'plain_mail',
            'subject' => 'SuperCoder Order Confirmation Receipt'
        )
    ),
	// Order without shipping address Recived
	'invoice_without_shipping' => array(
        'from_address'=>'supercoder@supercoder.com',
		'label' => 'SuperCoder',
		'subject' => 'SuperCoder Order Confirmation Receipt',
        'description' => '',
        'name' => 'invoice_without_shipping',
        'opt_out_preference_name' => 'notification_order_email_without_shipping_opt_out',
        'email' => array(
            'body' => 'order/invoice_without_shipping',
            'template' => 'plain_mail',
            'subject' => 'SuperCoder Order Confirmation Receipt'
        )
    ),
	// Resend Issue Download Link
	'resend_article_download_mail' => array(
		'from_address'=>'supercoder@supercoder.com',
		'from_label'=>'SuperCoder Issue Download',
		'subject' => 'Your Issue Download Link',
        'description' => '',
        'name' => 'resend_article_download_mail',
        'opt_out_preference_name' => 'notification_resend_article_download_mail',
        'email' => array(
            'body' => 'order/resend_article_download_mail',
            'template' => 'generic_mail',
            'subject' => 'SuperCoder Issue Download Link',
			'from_label'=>'SuperCoder Issue Download Link'
        )
	),	
	// Order only trial
	'order_trial' => array(
        'from_address'=>'supercoder@supercoder.com',
		'label' => 'SuperCoder',
		'subject' => 'SuperCoder Trial Confirmation Receipt',
        'description' => '',
        'name' => 'order_trial',
        'opt_out_preference_name' => 'notification_order_trial_opt_out',
        'email' => array(
            'body' => 'order/trial',
            'template' => 'generic_promotion',
            'subject' => 'SuperCoder Trial Confirmation Receipt'
        )
    ),
	// Order Lead Email
	'order_lead_email' => array(
        'from_address'=>'supercoder@supercoder.com',
		'label' => 'www.supercoder.com',
		'subject' => 'SuperCoder Order Confirmation Alert',
        'description' => '',
        'name' => 'order_lead_email',
        'opt_out_preference_name' => 'notification_order_lead_email_opt_out',
        'email' => array(
            'body' => 'order/order_lead_email',
            'template' => 'generic_promotion',
            'subject' => 'SuperCoder Order Confirmation Alert'
        )
    ),
// Order Lead Trial Email
	'order_lead_email_trial' => array(
        'from_address'=>'supercoder@supercoder.com',
		'label' => 'www.supercoder.com',
		'subject' => 'SuperCoder Trial Confirmation Alert',
        'description' => '',
        'name' => 'order_lead_email_trial',
        'opt_out_preference_name' => 'notification_order_lead_email_opt_out',
        'email' => array(
            'body' => 'order/order_lead_email_trial',
            'template' => 'generic_promotion',
            'subject' => 'SuperCoder Trial Confirmation Alert'
        )
    ),
	// Order Processing Email
	'order_process_email' => array(
        'from_address'=>'supercoder@supercoder.com',
		'label' => 'www.supercoder.com',
		'subject' => 'SuperCoder Order Processing Alert',
        'description' => '',
        'name' => 'order_process_email',
        'opt_out_preference_name' => 'notification_order_lead_email_opt_out',
        'email' => array(
            'body' => 'order/order_processing',
            'template' => 'generic_mail',
            'subject' => 'SuperCoder Order Processing Alert'
        )
    ),
	// Trial Cancellation email
	'order_trial_deactivation' => array(
        'from_address'=>'supercoder@supercoder.com',
		'label' => 'SuperCoder',
		'subject' => 'SuperCoder Trial Cancellation Notice' ,
        'description' => '',
        'name' => 'order_received',
        'opt_out_preference_name' => 'notification_order_trial_deactivation_opt_out',
        'email' => array(
            'body' => 'order/trialcancel',
            'template' => 'generic',
            'subject' => 'SuperCoder Trial Cancellation Notice'
        )
    ),
	// Trial Form
	'trial_submitted' => array(
        'from_address'=>'supercoder@supercoder.com',
		'label' => 'www.supercoder.com',
		'subject' => 'User signed up from landing page',
        'description' => '',
        'name' => 'trial_submitted',
        'opt_out_preference_name' => 'notification_trial_submitted_opt_out',
        'email' => array(
            'body' => 'form/trial_submitted',
            'template' => 'generic_promotion',
            'subject' => 'User signed up from landing page'
        )
    ),
	// AAE Form
	'lead_generation_from_aae' => array(
        'from_address'=>'supercoder@supercoder.com',
		'label' => 'www.supercoder.com',
		'subject' => 'User signed up from AAE page',
        'description' => '',
        'name' => 'lead_generation_from_aae',
        'opt_out_preference_name' => 'notification_lead_generation_from_aae',
        'email' => array(
            'body' => 'form/lead_generation',
            'template' => 'generic_promotion',
            'subject' => 'User signed up from AAE page'
        )
    ),
	// Payment notifications
	'payment_received' => array(
        'from_address'=>'grading@beckett.com',
		'label' => 'Beckett Grading Services',
		'subject' => 'Your payment has been received',
        'description' => '',
        'name' => 'payment_received',
        'opt_out_preference_name' => 'notification_payment_received_opt_out',
        'email' => array(
            'body' => 'payment/received',
            'template' => 'generic_promotion',
            'subject' => 'Your payment has been received.'
        )
    ),
	// Contact Us Form
	'contact_form_submitted' => array(
        'from_address'=>'supercoder@supercoder.com',
		'from_label' => 'SuperCoder',
		'subject' => 'Contact US form Submitted',
        'description' => '',
        'name' => 'contact_form_submitted',
        'opt_out_preference_name' => 'notification_contact_form_submitted_opt_out',
        'email' => array(
            'body' => 'form/contact_us',
            'template' => 'generic_mail',
            'subject' => 'Contact US form Submitted',
			'from_address'=>'supercoder@supercoder.com',
			'from_label' => 'From SuperCoder Contact Us Page'
        )
    ),
	// Ondemand question
	'ondemand_question_submitted' => array(
        'from_address'=>'supercoder@supercoder.com',
		'from_label' => 'SuperCoder',
		'subject' => 'SuperCoder Ondemand Question Submitted',
        'description' => '',
        'name' => 'ondemand_question_submitted',
        'opt_out_preference_name' => 'notification_ondemand_question_submitted_opt_out',
        'email' => array(
            'body' => 'user/ondemand_question',
            'template' => 'generic_mail',
            'subject' => 'SuperCoder Ondemand Question Submitted',
			'from_address'=>'supercoder@supercoder.com',
			'from_label' => 'From SuperCoder On Demand'
        )
    ),
	// Reset username and password
	'reset_user_pass' => array(
        'from_address'=>'supercoder@supercoder.com',
		'from_label' => 'SuperCoder',
		'subject' => 'Login details for SuperCoder',
        'description' => '',
        'name' => 'reset_user_pass',
        'opt_out_preference_name' => 'notification_reset_user_pass_opt_out',
        'email' => array(
            'body' => 'user/reset_user_pass',
            'template' => 'generic_mail',
            'subject' => 'Login details for SuperCoder',
			'from_address'=>'supercoder@supercoder.com',
			'from_label' => 'SuperCoder Customer Service'
        )
    ),
		//send cof discount email
		'cof_discount_email' => array(
				'from_address'=>'supercoder@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'cof_discount_mail',
				'opt_out_preference_name' => 'notification_cof_discount_mail_opt_out',
				'email' => array(
						'body' => 'order/cof_discount_mail',
						'template' => 'generic_promotion',
						'subject' => 'Rep has given discount in COF',
						'from_address'=>'supercoder@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
		
		###  Product Emailer Templates Start #####
	//send email in case of Batch Scrubber Purchase
	'batch_scrubber' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'batch_scrubber',
				'opt_out_preference_name' => 'notification_code_search',
				'email' => array(
						'body' => 'order/batch_scrubber',
						'template' => 'generic_mail',
						'subject' => 'Scrubber CMS-1500 for Batch Processing',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
	'cardiology_coder_coolkit' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'cardiology_coder_coolkit',
				'opt_out_preference_name' => 'notification_cardiology_coder_coolkit',
				'email' => array(
						'body' => 'order/christmas/cardiology_coder_coolkit',
						'template' => 'generic_mail',
						'subject' => 'SuperCoder Cardiology Coder Toolkit',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),	
	'dermatology_coder_coolkit' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'dermatology_coder_coolkit',
				'opt_out_preference_name' => 'notification_dermatology_coder_coolkit',
				'email' => array(
						'body' => 'order/christmas/dermatology_coder_coolkit',
						'template' => 'generic_mail',
						'subject' => 'SuperCoder Dermatology Coder Toolkit',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
	'family_practice_coder_coolkit' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'family_practice_coder_coolkit',
				'opt_out_preference_name' => 'notification_family_practice_coder_coolkit',
				'email' => array(
						'body' => 'order/christmas/family_practice_coder_coolkit',
						'template' => 'generic_mail',
						'subject' => 'SuperCoder Family Practice Coder Toolkit',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),	
	'gastroenterology_coder_coolkit' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'gastroenterology_coder_coolkit',
				'opt_out_preference_name' => 'notification_gastroenterology_coder_coolkit',
				'email' => array(
						'body' => 'order/christmas/gastroenterology_coder_coolkit',
						'template' => 'generic_mail',
						'subject' => 'SuperCoder Gastroenterology Coder Toolkit',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
	
	'general_surgery_coder_coolkit' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'general_surgery_coder_coolkit',
				'opt_out_preference_name' => 'notification_general_surgery_coder_coolkit',
				'email' => array(
						'body' => 'order/christmas/general_surgery_coder_coolkit',
						'template' => 'generic_mail',
						'subject' => 'SuperCoder General Surgery Coder Toolkit',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
	
	'ob_gyn_coder_coolkit' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'ob_gyn_coder_coolkit',
				'opt_out_preference_name' => 'notification_ob_gyn_coder_coolkit',
				'email' => array(
						'body' => 'order/christmas/ob_gyn_coder_coolkit',
						'template' => 'generic_mail',
						'subject' => 'SuperCoder Ob-Gyn Coder Toolkit',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
	
	'ophthalmology_coder_coolkit' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'ophthalmology_coder_coolkit',
				'opt_out_preference_name' => 'notification_ophthalmology_coder_coolkit',
				'email' => array(
						'body' => 'order/christmas/ophthalmology_coder_coolkit',
						'template' => 'generic_mail',
						'subject' => 'SuperCoder Ophthalmology Coder Toolkit',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
	
	'orthopedic_coder_coolkit' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'orthopedic_coder_coolkit',
				'opt_out_preference_name' => 'notification_orthopedic_coder_coolkit',
				'email' => array(
						'body' => 'order/christmas/orthopedic_coder_coolkit',
						'template' => 'generic_mail',
						'subject' => 'SuperCoder Orthopedic Coder Toolkit',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
	'otolaryngology_coder_coolkit' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'otolaryngology_coder_coolkit',
				'opt_out_preference_name' => 'notification_otolaryngology_coder_coolkit',
				'email' => array(
						'body' => 'order/christmas/otolaryngology_coder_coolkit',
						'template' => 'generic_mail',
						'subject' => 'SuperCoder Otolaryngology Coder Toolkit',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
	'pediatric_coder_coolkit' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'pediatric_coder_coolkit',
				'opt_out_preference_name' => 'notification_pediatric_coder_coolkit',
				'email' => array(
						'body' => 'order/christmas/pediatric_coder_coolkit',
						'template' => 'generic_mail',
						'subject' => 'SuperCoder Pediatric Coder Toolkit',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
	'urology_coder_coolkit' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'urology_coder_coolkit',
				'opt_out_preference_name' => 'notification_urology_coder_coolkit',
				'email' => array(
						'body' => 'order/christmas/urology_coder_coolkit',
						'template' => 'generic_mail',
						'subject' => 'SuperCoder Urology Coder Toolkit',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),	
	'compliance_edge' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'compliance_edge',
				'opt_out_preference_name' => 'notification_compliance_edge',
				'email' => array(
						'body' => 'order/compliance_edge',
						'template' => 'generic_mail',
						'subject' => 'SuperCoder Compliance Edge',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),		
	'icd10_elearning' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'icd10_elearning',
				'opt_out_preference_name' => 'notification_icd10_elearning',
				'email' => array(
						'body' => 'order/icd10_elearning',
						'template' => 'generic_mail',
						'subject' => 'SuperCoder ICD-10 Elearning',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),	
	'icd10_elearning_pretest' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'icd10_elearning',
				'opt_out_preference_name' => 'notification_icd10_elearning',
				'email' => array(
						'body' => 'order/icd10_elearning_pretest',
						'template' => 'generic_mail',
						'subject' => 'SuperCoder Purchase Confirmation and Pretest Directions',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),	
	'icd10_elearning_pretest_complete' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'icd10_elearning',
				'opt_out_preference_name' => 'notification_icd10_elearning',
				'email' => array(
						'body' => 'order/icd10_elearning_pretest_complete',
						'template' => 'generic_mail',
						'subject' => 'SuperCoder ICD-10 e-Learning Login Details',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),	
	'icd10_elearning_posttest' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'icd10_elearning',
				'opt_out_preference_name' => 'notification_icd10_elearning',
				'email' => array(
						'body' => 'order/icd10_elearning_posttest',
						'template' => 'generic_mail',
						'subject' => 'SuperCoder Posttest Directions',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),	
	'icd10_elearning_posttest_complete' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'icd10_elearning',
				'opt_out_preference_name' => 'notification_icd10_elearning',
				'email' => array(
						'body' => 'order/icd10_elearning_posttest_complete',
						'template' => 'generic_mail',
						'subject' => 'SuperCoder Posttest Directions',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),	
	//send email in case of Code Search Purchase
	'code_search' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'code_search',
				'opt_out_preference_name' => 'notification_code_search',
				'email' => array(
						'body' => 'order/code_search',
						'template' => 'generic_mail',
						'subject' => 'Code Search',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
	'fast_coder' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'fast_coder',
				'opt_out_preference_name' => 'notification_fast_coder',
				'email' => array(
						'body' => 'order/fast_coder',
						'template' => 'generic_mail',
						'subject' => 'Fast Coder',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
	'coding_newsletters' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'coding_newsletters',
				'opt_out_preference_name' => 'notification_coding_newsletters',
				'email' => array(
						'body' => 'order/coding_newsletters',
						'template' => 'generic_mail',
						'subject' => 'Coding Newsletters',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
	'coding_print_newsletters' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'coding_print_newsletters',
				'opt_out_preference_name' => 'notification_coding_print_newsletters',
				'email' => array(
						'body' => 'order/coding_print_newsletters',
						'template' => 'generic_mail',
						'subject' => 'Coding Print Newsletters',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
	'print_only_newsletters' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'print_only_newsletters',
				'opt_out_preference_name' => 'notification_print_only_newsletters',
				'email' => array(
						'body' => 'order/print_only_newsletters',
						'template' => 'generic_mail',
						'subject' => 'Print Only Newsletters',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),	
	'print_only_ehth_newsletters' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'print_only_ehth_newsletters',
				'opt_out_preference_name' => 'notification_print_only_ehth_newsletters',
				'email' => array(
						'body' => 'order/print_only_ehth_newsletters',
						'template' => 'generic_mail',
						'subject' => 'Congratulations! You’re Now Officially Part of The Coding Institute Family',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),		
	'coding_ehth_newsletters' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'coding_ehth_newsletters',
				'opt_out_preference_name' => 'notification_coding_ehth_newsletters',
				'email' => array(
						'body' => 'order/coding_ehth_newsletters',
						'template' => 'generic_mail',
						'subject' => 'ELI HC Alert',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
	'coding_solutions' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'coding_solutions',
				'opt_out_preference_name' => 'notification_coding_solutions',
				'email' => array(
						'body' => 'order/coding_solutions',
						'template' => 'generic_mail',
						'subject' => 'Coding Solutions',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
	'ask_an_expert' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'ask_an_expert',
				'opt_out_preference_name' => 'notification_ask_an_expert',
				'email' => array(
						'body' => 'order/ask_an_expert',
						'template' => 'generic_mail',
						'subject' => 'Ask An Expert',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
		'coding_on_demand' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'coding_on_demand',
				'opt_out_preference_name' => 'notification_coding_on_demand',
				'email' => array(
						'body' => 'order/coding_on_demand',
						'template' => 'generic_mail',
						'subject' => 'SuperCoding on Demand',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
	// AHIMA EMAIL
	'ahima_email' => array(
        'from_address'=>'supercoder@supercoder.com',
		'label' => 'SuperCoder',
		'subject' => 'Claim Your SuperCoder Shirt at Booth 818',
        'description' => '',
        'name' => 'ahima_email',
        'opt_out_preference_name' => 'notification_ahima_email',
        'email' => array(
            'body' => 'user/ahima',
            'template' => 'generic_promotion',
			'from_address'=>'supercoder@supercoder.com',
            'subject' => 'Claim Your SuperCoder Shirt at Booth 818',
			'bcc' => 'uksingh@codinginstitute.us,saurabhv@codinginstitute.us,sunilp@eliinfra.com'
        )
    ),
	// Trial Email Confirmation
	'trial_email_confirmation' => array(
        'from_address'=>'supercoder@supercoder.com',
		'label' => 'SuperCoder',
		'subject' => 'SuperCoder Email Confirmation',
        'description' => '',
        'name' => 'trial_email_confirmation',
        'opt_out_preference_name' => 'notification_order_email_with_shipping_opt_out',
        'email' => array(
            'body' => 'order/trial_email_confirmation',
            'template' => 'plain_mail',
            'subject' => 'SuperCoder Email Confirmation'
        )
    ),
	// Latest newsletter email 
	'newsletter_email' => array(
        'from_address'=>'supercoder@supercoder.com',
		'label' => 'SuperCoder',
        'name' => 'newsletter_email',
        'opt_out_preference_name' => 'notification_newsletter_email',
        'email' => array(
            'body' => 'user/newsletter_email',
            'template' => 'plain_mail',
			'from_address'=>'supercoder@supercoder.com'
        )
    ),
	//	Trial Products Email Setup
	'code_search_trial' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'code_search_trial',
				'opt_out_preference_name' => 'notification_code_search_trial',
				'email' => array(
						'body' => 'order/code_search_trial',
						'template' => 'generic_mail',
						'subject' => 'Code Search',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
	'fast_coder_trial' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'fast_coder_trial',
				'opt_out_preference_name' => 'notification_fast_coder_trial',
				'email' => array(
						'body' => 'order/fast_coder_trial',
						'template' => 'generic_mail',
						'subject' => 'Fast Coder',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
	'coding_newsletters_trial' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'coding_newsletters_trial',
				'opt_out_preference_name' => 'notification_coding_newsletters_trial',
				'email' => array(
						'body' => 'order/coding_newsletters_trial',
						'template' => 'generic_mail',
						'subject' => 'Coding Newsletters',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
	'coding_ehth_newsletters_trial' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'coding_ehth_newsletters_trial',
				'opt_out_preference_name' => 'notification_coding_ehth_newsletters_trial',
				'email' => array(
						'body' => 'order/coding_ehth_newsletters_trial',
						'template' => 'generic_mail',
						'subject' => 'Congratulations! You are Now Officially Part of The Coding Institute Family',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),	
	'coding_solutions_trial' => array(
				'from_address'=>'supercoder@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'coding_solutions_trial',
				'opt_out_preference_name' => 'notification_coding_solutions_trial',
				'email' => array(
						'body' => 'order/coding_solutions_trial',
						'template' => 'generic_mail',
						'subject' => 'Coding Solutions',
						'from_address'=>'supercoder@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),			
	'multispecialty_article' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'multispecialty_article',
				'opt_out_preference_name' => 'notification_multispecialty_article',
				'email' => array(
						'body' => 'order/multispecialty_article',
						'template' => 'generic_mail',
						'subject' => 'SuperCoder.com Login Details for Multispecialty Articles Pack',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
	'multispecialty_coder' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'multispecialty_coder',
				'opt_out_preference_name' => 'notification_multispecialty_coder',
				'email' => array(
						'body' => 'order/multispecialty_coder',
						'template' => 'generic_mail',
						'subject' => 'SuperCoder.com Login Details for Multispecialty Coder',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
	'coding_certification' => array(
				'from_address'=>'support@supercoder.com',
				'from_label' => 'SuperCoder',
				'name' => 'coding_certification',
				'opt_out_preference_name' => 'notification_coding_certification',
				'email' => array(
						'body' => 'order/coding_certification',
						'template' => 'generic_mail',
						'subject' => 'SuperCoder.com Order Confirmation for CodingCertification.Org Product',
						'from_address'=>'support@supercoder.com',
						'from_label' => 'SuperCoder Customer Service'
				)
		),
	 'sc_aae_tracker' => array(
		'from_address'=>'support@supercoder.com',
		'from_label'=>'SuperCoder',
        'label' => 'Request for demo',
        'description' => 'Sent when a request to demo is initiated',
        'name' => 'sc_aae_tracker',
        'opt_out_preference_name' => 'notification_aae_tracker',
        'email' => array(
            'body' => 'user/sc_aae_tracker',
            'subject' => 'Ask An Expert - Daily Report '.date("Y/m/d",strtotime('-1 days'))
        )
    ),
	 'sc_layterms' => array(
		'from_address'=>'support@supercoder.com',
		'from_label'=>'SuperCoder',
        'label' => 'Approve new lay terms',
        'description' => 'Approve new lay terms',
        'name' => 'sc_layterms',
        'opt_out_preference_name' => 'notification_sc_layterms',
        'email' => array(
            'body' => 'user/sc_layterms',
            'subject' => 'Approve new lay terms',
			'from_address'=>'support@supercoder.com',
			'from_label'=>'SuperCoder Lay terms Add/Update'
        )
    ),
	
	'sc_layterms_approved' => array(
		'from_address'=>'support@supercoder.com',
		'from_label'=>'SuperCoder',
        'label' => 'A lay terms has been approved by approved user',
        'description' => 'A lay terms has been approved by approved user',
        'name' => 'sc_layterms_approved',
        'opt_out_preference_name' => 'notification_sc_layterms_approved',
        'email' => array(
            'body' => 'user/sc_layterms_approved',
            'subject' => 'A lay terms has been approved by approved user',
			'from_address'=>'support@supercoder.com',
			'from_label'=>'SuperCoder Lay terms Approved'
        )
    ),
	
	'sc_layterms_assignee_changed' => array(
		'from_address'=>'support@supercoder.com',
		'from_label'=>'SuperCoder',
        'label' => 'An Lay Terms Assignee has been changed',
        'description' => 'An Lay Terms Assignee has been changed',
        'name' => 'sc_layterms_assignee_changed',
        'opt_out_preference_name' => 'notification_sc_layterms_assignee_changed',
        'email' => array(
            'body' => 'user/sc_layterms_assignee_changed',
            'subject' => 'An Lay Terms Assignee has been changed',
			'from_address'=>'support@supercoder.com',
			'from_label'=>'SuperCoder Lay Terms Assignee Changed'
        )
    ),
	
	'sc_layterms_bulk_assignee_changed' => array(
		'from_address'=>'support@supercoder.com',
		'from_label'=>'SuperCoder',
        'label' => 'Bulk Lay Terms assignment',
        'description' => 'Bulk Lay Terms assignment',
        'name' => 'sc_layterms_bulk_assignee_changed',
        'opt_out_preference_name' => 'notification_sc_layterms_bulk_assignee_changed',
        'email' => array(
            'body' => 'user/sc_layterms_bulk_assignee_changed',
            'subject' => 'Bulk Lay Terms assignment',
			'from_address'=>'support@supercoder.com',
			'from_label'=>'SuperCoder Bulk Lay Terms Assignment'
        )
    ),
	'sc_modifiers' => array(
		'from_address'=>'support@supercoder.com',
		'from_label'=>'SuperCoder',
        'label' => 'Approve new modifier',
        'description' => 'Approve new modifier',
        'name' => 'sc_modifiers',
        'opt_out_preference_name' => 'notification_sc_modifiers',
        'email' => array(
            'body' => 'user/sc_modifiers',
            'subject' => 'Approve new modifier',
			'from_address'=>'support@supercoder.com',
			'from_label'=>'SuperCoder modifier Add/Update'
        )
    ),
	'sc_modifier_approved' => array(
		'from_address'=>'support@supercoder.com',
		'from_label'=>'SuperCoder',
        'label' => 'A modifier has been approved by approved user',
        'description' => 'A modifier has been approved by approved user',
        'name' => 'sc_modifier_approved',
        'opt_out_preference_name' => 'notification_sc_modifier_approved',
        'email' => array(
            'body' => 'user/sc_modifier_approved',
            'subject' => 'A modifier has been approved by approved user',
			'from_address'=>'support@supercoder.com',
			'from_label'=>'SuperCoder Modifier Approved'
        )
    ),
	'sc_modifier_assignee_changed' => array(
		'from_address'=>'support@supercoder.com',
		'from_label'=>'SuperCoder',
        'label' => 'An modifier Assignee has been changed',
        'description' => 'An modifier Assignee has been changed',
        'name' => 'sc_modifier_assignee_changed',
        'opt_out_preference_name' => 'notification_sc_modifier_assignee_changed',
        'email' => array(
            'body' => 'user/sc_modifier_assignee_changed',
            'subject' => 'An modifier Assignee has been changed',
			'from_address'=>'support@supercoder.com',
			'from_label'=>'SuperCoder Modifier Assignee Changed'
        )
    ),
	'sc_modifier_bulk_assignee_changed' => array(
		'from_address'=>'support@supercoder.com',
		'from_label'=>'SuperCoder',
        'label' => 'Bulk Modifiers assignment',
        'description' => 'Bulk Modifiers assignment',
        'name' => 'sc_modifier_bulk_assignee_changed',
        'opt_out_preference_name' => 'notification_sc_modifier_bulk_assignee_changed',
        'email' => array(
            'body' => 'user/sc_modifier_bulk_assignee_changed',
            'subject' => 'Bulk Modifier assignment',
			'from_address'=>'support@supercoder.com',
			'from_label'=>'SuperCoder Bulk Modifiers Assignment'
        )
    ),
	'cci_beta' => array(
		'from_address'=>'support@supercoder.com',
		'from_label'=>'SuperCoder',
        'label' => 'SuperCoder CCI Edits Beta Feedback',
        'description' => 'Sent when a comment is entered in CCI Edits Beta form',
        'name' => 'cci_beta',
        'opt_out_preference_name' => FALSE,
        'email' => array(
            'body' => 'user/cci_beta',
            'subject' => 'SuperCoder CCI Edits Beta Feedback',
			'from_address'=>'support@supercoder.com',
			'from_label' => 'SuperCoder'
        )
    ),
	'webinar_register' => array(
		'from_address'=>'support@supercoder.com',
		'from_label'=>'SuperCoder',
        'label' => 'Registration confirmation in SuperCoder Webinar',
        'description' => 'Registration confirmation in SuperCoder Webinar',
        'name' => 'webinar_register',
        'opt_out_preference_name' => FALSE,
        'email' => array(
            'body' => 'order/webinar_register',
            'subject' => 'Registration confirmation in SuperCoder Webinar',
			'from_address'=>'support@supercoder.com',
			'from_label' => 'SuperCoder'
        )
    ),
	'ICD10_Search_Android_Register' => array(
		'from_address'=>'support@supercoder.com',
		'from_label'=>'SuperCoder',
        'label' => 'Registration confirmation in SuperCoder ICD10 Search Application',
        'description' => 'Registration confirmation in SuperCoder ICD10 Search Application',
        'name' => 'ICD10_Search_Android_Register',
        'opt_out_preference_name' => FALSE,
        'email' => array(
            'body' => 'order/ICD10_Search_Android_Register',
            'subject' => 'Registration confirmation in SuperCoder ICD10 Search Application',
			'from_address'=>'support@supercoder.com',
			'from_label' => 'SuperCoder'
        )
    )	
);

/* End of file config.php */
/* Location: ./system/application/config/config.php */
