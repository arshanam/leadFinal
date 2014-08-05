<?php
function display_short_codes() {
	return array(
		'[[SITE_ROOT]]' => 'The site root url (ex. http://www.beckett.com/)',
		'[[COMPANY_NAME]]' => 'The company name (Beckett Media, LLC)'
	);
}

function short_codes($var) {
	$var = str_replace("[[SITE_ROOT]]",base_url(),$var);
	$var = str_replace("[[COMPANY_NAME]]","Beckett Media, LLC",$var);
	return $var;
}