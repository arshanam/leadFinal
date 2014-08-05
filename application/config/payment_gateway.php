<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['payment'] = array(
    'name' => 'Payflow Payment Gateway',
    'library' => 'payflow_payment_gateway',
    'gateway_config_name' => 'payflow_payment_gateway_config',
    'accepted_card_types' => array('AMEX', 'VISA', 'MASTERCARD', 'DISCOVER'),
);

if ($_SERVER['APP_MODE'] == 'prod') {
    $config['payment']['test_mode'] = FALSE;
} else {
    $config['payment']['test_mode'] = TRUE;
}

/*
	'COMMENT1'	=>'beckettmedia.com',
			'USER'           => $this->config->get('pp_pro_username'),
			'PWD'            => $this->config->get('pp_pro_password'),
			'PARTNER'		 => $this->config->get('pp_pro_partner'),
			'VENDOR'		 => $this->config->get('pp_pro_vendor'),
		*/

//USER=Beckett1&PWD=whoareyou27&PARTNER=firstomaha&VENDOR=Beckett1

$config['payflow_payment_gateway'] = array(
    'vendor' => 'Beckett1',
    'partner' => 'firstomaha',
    'username' => 'Beckett1',
    'password' => 'whoareyou27',
    'payment_action' => 'AUTH_CAPTURE', // AUTH or AUTH_CAPTURE
    'use_proxy' => FALSE,
    'proxy_host' => NULL, // http://someproxyhostname.tld
    'proxy_port' => NULL, // 3284
);