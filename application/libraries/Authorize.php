<?php

/**
 * Authorize Payment Gateway Class
 *
 * @author Sunil Punyani
 */
class Authorize
	{

	 public function __construct($test = FALSE)
			{
				//https://api.authorize.net/xml/v1/request.api
				$host = ($test) ? 'apitest' : 'api';
				$this->g_apihost = 'api.authorize.net';
				$this->g_apipath ='/xml/v1/request.api';
				$this->g_loginname = "5GGzub2U3m9"; // Keep this secure.
				$this->g_transactionkey = "8dM82j736HyTng5a"; // Keep this secure.
				$this->CI = & get_instance();
				/*$this->g_apihost='apitest.authorize.net';
				$this->g_apipath='/xml/v1/request.api';
				$this->g_loginname = "7CF4t6MB8zq"; // Keep this secure.
				$this->g_transactionkey = "38rK8X7gg75dZ7YM"; // Keep this secure.*/
			}

	public function send_xml_request($content)
		{

			return $this->send_request_via_fsockopen($this->g_apihost,$this->g_apipath,$content);
		}
		//function to send xml request via fsockopen
//It is a good idea to check the http status code.
	public function send_request_via_fsockopen($host,$path,$content)
		{

			$posturl = "ssl://" . $host;
			$header = "Host: $host\r\n";
			$header .= "User-Agent: PHP Script\r\n";
			$header .= "Content-Type: text/xml\r\n";
			$header .= "Content-Length: ".strlen($content)."\r\n";
			$header .= "Connection: close\r\n\r\n";
			$fp = fsockopen($posturl, 443, $errno, $errstr, 30);
			if (!$fp)
			{
				$body = false;
			}
			else
			{
				error_reporting(E_ERROR);
				fputs($fp, "POST $path  HTTP/1.1\r\n");
				fputs($fp, $header.$content);
				fwrite($fp, $out);
				$response = "";
				while (!feof($fp))
				{
					$response = $response . fgets($fp, 128);
				}
				fclose($fp);
				error_reporting(E_ALL ^ E_NOTICE);

				$len = strlen($response);
				$bodypos = strpos($response, "\r\n\r\n");
				if ($bodypos <= 0)
				{
					$bodypos = strpos($response, "\n\n");
				}
				while ($bodypos < $len && $response[$bodypos] != '<')
				{
					$bodypos++;
				}
				$body = substr($response, $bodypos);
			}
			return $body;
		}
		//function to send xml request via curl
	public	function send_request_via_curl($host,$path,$content)
		{
			$posturl = "https://" . $host . $path;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $posturl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$response = curl_exec($ch);
			return $response;
		}
	public function parse_api_response($content)
		{
			$parsedresponse = @simplexml_load_string($content);
			return $parsedresponse;
		}

	public function MerchantAuthenticationBlock()
		{
			return
			"<merchantAuthentication>".
			"<name>" . $this->g_loginname . "</name>".
			"<transactionKey>" .$this->g_transactionkey . "</transactionKey>".
			"</merchantAuthentication>";
		}
	public function creat_user_profile($user_id,$email)
		{
				//build xml to post
				$content =
						"<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
						"<createCustomerProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
						$this->MerchantAuthenticationBlock().
						"<profile>".
						"<merchantCustomerId>".$user_id."</merchantCustomerId>". // Your own identifier for the customer.
						"<description></description>".
						"<email>" . $email . "</email>".
						"</profile>".
						"</createCustomerProfileRequest>";
					$response = $this->send_xml_request($content);
					$parsedresponse = $this->parse_api_response($response);

					if ("Ok" == $parsedresponse->messages->resultCode)
						{
								return htmlspecialchars($parsedresponse->customerProfileId);
						}
					else
						{
							if($parsedresponse->messages->message->code=='E00039')
								{
									return preg_replace("/[^0-9]/","",$parsedresponse->messages->message->text);// Getting only Number
								}
							else
								{
									return false;
								}
						}
		}
	public function creat_user_shipping_profile($customer_profile_id,$shipping_data)
		{

//build xml to post
$content =
	"<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
	"<createCustomerShippingAddressRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
	$this->MerchantAuthenticationBlock().
	"<customerProfileId>" . $customer_profile_id . "</customerProfileId>".
	"<address>".
	"<firstName>".$shipping_data['first_name']."</firstName>".
	"<lastName>".$shipping_data['last_name']."</lastName>".
	"<company>".$shipping_data['company']."</company>".

	"<address>".$shipping_data['street_1']."</address>".
		"<city>".$shipping_data['city']."</city>".
	"<phoneNumber>".$shipping_data['phone']."</phoneNumber>".


	"</address>".
	"</createCustomerShippingAddressRequest>";

					$response = $this->send_xml_request($content);
					$parsedresponse = $this->parse_api_response($response);
					if ("Ok" == $parsedresponse->messages->resultCode) {

							return htmlspecialchars($parsedresponse->customerAddressId);

					}
					else
					{
						return false;
					}
		}
public function update_user_shipping_profile($customer_profile_id,$shipping_data,$shipping_profile_id)
		{

//build xml to post
				$content =
					"<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
					"<updateCustomerShippingAddressRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
					$this->MerchantAuthenticationBlock().
					"<customerProfileId>" . $customer_profile_id . "</customerProfileId>".
					"<address>".
					"<firstName>".$shipping_data['first_name']."</firstName>".
					"<lastName>".$shipping_data['last_name']."</lastName>".
					"<company>".$shipping_data['company']."</company>".

					"<address>".$shipping_data['street_1']."</address>".
						"<city>".$shipping_data['city']."</city>".
					"<phoneNumber>".$shipping_data['phone']."</phoneNumber>".
					"<customerAddressId>".$shipping_profile_id ."</customerAddressId>".
					"</address>".
					"</updateCustomerShippingAddressRequest>";
					$response = $this->send_xml_request($content);
					$parsedresponse = $this->parse_api_response($response);


					if ("Ok" == $parsedresponse->messages->resultCode) {

							return true;

					}
					else
					{
						return false;
					}
		}
public function create_user_payment_profile($customer_profile_id,$card_data,$blill_address='')
		{
			$mode='liveMode';
				if($card_data['c_card']=='4111111111111111')
					{
						$mode='testMode';
					}
				else
					{
						$mode='liveMode';
					}
			$billprofile='';
			if(is_array($blill_address) && count($blill_address)>0)
				{
					
					/*$billprofile='<firstName>'.$blill_address['first_name'].'</firstName>';
					$billprofile.='<lastName>'.$blill_address['last_name'].'</lastName>';
					$billprofile.='<company>'.$blill_address['company'].'</company>';
					$add1='';
					$add2='';
					if(!empty($blill_address['street_1']))
						{
							$add1=preg_replace("/[^a-zA-Z0-9\s]/","", $blill_address['street_1']);
						}
					if(!empty($blill_address['street_2']))
						{
							$add2=preg_replace("/[^a-zA-Z0-9\s]/","", $blill_address['street_2']);
						}	
					$billprofile.='<address>'.$add1.','.$add2.'</address>';
					$billprofile.='<city>'.$blill_address['city'].'</city>';
					$billprofile.='<state>'.$blill_address['state'].'</state>';
					$billprofile.='<zip>'.$blill_address['zipcode'].'</zip>';
					$billprofile.='<country>'.$blill_address['country'].'</country>';
					$billprofile.='<phoneNumber>'.$blill_address['phone'].'</phoneNumber>';	*/					
					$billprofile='';
					if(!empty($blill_address['first_name']))
						{
							$billprofile.='<firstName>'.preg_replace("/[^a-zA-Z0-9\s]/","",$blill_address['first_name']).'</firstName>';
						}
					if(!empty($blill_address['last_name']))
						{
							$billprofile.='<lastName>'.preg_replace("/[^a-zA-Z0-9\s]/","",$blill_address['last_name']).'</lastName>';
						}
					
					if(!empty($blill_address['company']))
						{
							$billprofile.='<company>'.preg_replace("/[^a-zA-Z0-9\s]/","",$blill_address['company']).'</company>';
						}
					
					$add1='';
					$add2='';
					if(!empty($blill_address['street_1']))
						{
							$add1=preg_replace("/[^a-zA-Z0-9\s]/","", $blill_address['street_1']);
						}
					if(!empty($blill_address['street_2']))
						{
							$add2=preg_replace("/[^a-zA-Z0-9\s]/","", $blill_address['street_2']);
						}
					$billprofile.='<address>'.$add1.','.$add2.'</address>';
					if(!empty($blill_address['city']))
						{
							$billprofile.='<city>'.preg_replace("/[^a-zA-Z0-9\s]/","",$blill_address['city']).'</city>';
						}
					
					if(!empty($blill_address['state']))
						{
							$billprofile.='<state>'.preg_replace("/[^a-zA-Z0-9\s]/","",$blill_address['state']).'</state>';
						}
					
					if(!empty($blill_address['zipcode']))
						{
							$billprofile.='<zip>'.preg_replace("/[^a-zA-Z0-9\s]/","",$blill_address['zipcode']).'</zip>';
						}
					
					if(!empty($blill_address['country']))
						{
							$billprofile.='<country>'.preg_replace("/[^a-zA-Z0-9\s]/","",$blill_address['country']).'</country>';
						}
				
					if(!empty($blill_address['phone']))
						{
							
							$billprofile.='<phoneNumber>'.$blill_address['phone'].'</phoneNumber>';
						}
					
					
					
				}
			else
				{
					$billprofile.='<firstName>'.preg_replace("/[^a-zA-Z0-9\s]/","",$card_data['c_name']).'</firstName>';
				}	
			//build xml to post
			/*$str='';
			if(isset($card_data['c_code']) && !empty($card_data['c_code']))
				{
					$str="<validationMode>".$mode."</validationMode>";
				}*/	
				$content =
				"<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
				"<createCustomerPaymentProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
				$this->MerchantAuthenticationBlock().
				"<customerProfileId>" . $customer_profile_id . "</customerProfileId>".
				"<paymentProfile>".
				"<billTo>".$billprofile."</billTo>".
				"<payment>".
				 "<creditCard>".
				  "<cardNumber>".$card_data['c_card']."</cardNumber>".
				  "<expirationDate>".$card_data['c_expire']."</expirationDate>". // required format for API is YYYY-MM
				 "</creditCard>".
				"</payment>".
				"</paymentProfile><validationMode>".$mode."</validationMode>".
				"</createCustomerPaymentProfileRequest>";
					$response = $this->send_xml_request($content);
					$parsedresponse = $this->parse_api_response($response);

					return $parsedresponse;

		}

	public function update_user_payment_profile($customer_profile_id,$card_data,$payment_profile_id,$blill_address='')
		{
			$mode='liveMode';
			if($card_data['c_card']=='4111111111111111')
				{
					$mode='testMode';
				}
			else
				{
					$mode='liveMode';
				}
			$str='';
		/*	if(isset($card_data['c_code']) && !empty($card_data['c_code']))
				{
					$str="<validationMode>".$mode."</validationMode>";
				}*/	
			$billprofile='';
			if(is_array($blill_address) && count($blill_address)>0)
			{
					
				/*$billprofile='<firstName>'.$blill_address['first_name'].'</firstName>';
				$billprofile.='<lastName>'.$blill_address['last_name'].'</lastName>';
				$billprofile.='<company>'.$blill_address['company'].'</company>';
				$add1='';
				$add2='';
				if(!empty($blill_address['street_1']))
				{
					$add1=preg_replace("/[^a-zA-Z0-9\s]/","", $blill_address['street_1']);
				}
				if(!empty($blill_address['street_2']))
				{
					$add2=preg_replace("/[^a-zA-Z0-9\s]/","", $blill_address['street_2']);
				}
				$billprofile.='<address>'.$add1.','.$add2.'</address>';
				$billprofile.='<city>'.$blill_address['city'].'</city>';
				$billprofile.='<state>'.$blill_address['state'].'</state>';
				$billprofile.='<zip>'.$blill_address['zipcode'].'</zip>';
				$billprofile.='<country>'.$blill_address['country'].'</country>';
				$billprofile.='<phoneNumber>'.$blill_address['phone'].'</phoneNumber>';*/
				$billprofile='';
					if(!empty($blill_address['first_name']))
						{
							$billprofile.='<firstName>'.preg_replace("/[^a-zA-Z0-9\s]/","",$blill_address['first_name']).'</firstName>';
						}
					if(!empty($blill_address['last_name']))
						{
							$billprofile.='<lastName>'.preg_replace("/[^a-zA-Z0-9\s]/","",$blill_address['last_name']).'</lastName>';
						}
					
					if(!empty($blill_address['company']))
						{
							$billprofile.='<company>'.preg_replace("/[^a-zA-Z0-9\s]/","",$blill_address['company']).'</company>';
						}
					
					$add1='';
					$add2='';
					if(!empty($blill_address['street_1']))
						{
							$add1=preg_replace("/[^a-zA-Z0-9\s]/","", $blill_address['street_1']);
						}
					if(!empty($blill_address['street_2']))
						{
							$add2=preg_replace("/[^a-zA-Z0-9\s]/","", $blill_address['street_2']);
						}
					$billprofile.='<address>'.$add1.','.$add2.'</address>';
					if(!empty($blill_address['city']))
						{
							$billprofile.='<city>'.preg_replace("/[^a-zA-Z0-9\s]/","",$blill_address['city']).'</city>';
						}
					
					if(!empty($blill_address['state']))
						{
							$billprofile.='<state>'.preg_replace("/[^a-zA-Z0-9\s]/","",$blill_address['state']).'</state>';
						}
					
					if(!empty($blill_address['zipcode']))
						{
							$billprofile.='<zip>'.preg_replace("/[^a-zA-Z0-9\s]/","",$blill_address['zipcode']).'</zip>';
						}
					
					if(!empty($blill_address['country']))
						{
							$billprofile.='<country>'.preg_replace("/[^a-zA-Z0-9\s]/","",$blill_address['country']).'</country>';
						}
				
					if(!empty($blill_address['phone']))
						{
							
							$billprofile.='<phoneNumber>'.$blill_address['phone'].'</phoneNumber>';
						}
					
					
			}
			else
			{
				$billprofile.='<firstName>'.preg_replace("/[^a-zA-Z0-9\s]/","",$card_data['c_name']).'</firstName>';
			}
			$content =
				"<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
				"<updateCustomerPaymentProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
				$this->MerchantAuthenticationBlock().
				"<customerProfileId>" . $customer_profile_id . "</customerProfileId>".
				"<paymentProfile>".
				"<billTo>".$billprofile."</billTo>".
				"<payment>".
				 "<creditCard>".
				  "<cardNumber>".$card_data['c_card']."</cardNumber>".
				  "<expirationDate>".$card_data['c_expire']."</expirationDate>". // required format for API is YYYY-MM
				 "</creditCard>".
				"</payment>".
				"<customerPaymentProfileId>".$payment_profile_id."</customerPaymentProfileId>".
				"</paymentProfile><validationMode>".$mode."</validationMode>"."</updateCustomerPaymentProfileRequest>";
					$response = $this->send_xml_request($content);
					if($card_data['c_card']=='4050281000360922')
					{
						/*echo $billprofile;
						print_r($content);
						echo '----------';
						print_r($response);
						die("ss");*/
					}
					$parsedresponse = $this->parse_api_response($response);
						return $parsedresponse;


		}
		
			
public function normalize_special_characters( $str ) 
	{	 
		# Quotes cleanup 
		$str = preg_replace('\''.chr(ord("`")).'\'', "'", $str );        # ` 
		$str = preg_replace('\''.chr(ord("�")).'\'', "'", $str );        # � 
		$str = preg_replace('\''.chr(ord("�")).'\'', ",", $str );        # � 
		$str = preg_replace('\''.chr(ord("`")).'\'', "'", $str );        # ` 
		$str = preg_replace('\''.chr(ord("�")).'\'', "'", $str );        # � 
		$str = preg_replace('\''.chr(ord("�")).'\'', "\"", $str );        # � 
		$str = preg_replace('\''.chr(ord("�")).'\'', "\"", $str );        # � 
		$str = preg_replace('\''.chr(ord("�")).'\'', "'", $str );        # � 


		 $unwanted_array = array(    '�'=>'S', '�'=>'s', '�'=>'Z', '�'=>'z', '�'=>'A', '�'=>'A', '�'=>'A', '�'=>'A', '�'=>'A', '�'=>'A', '�'=>'A', '�'=>'C', '�'=>'E', '�'=>'E', 
									'�'=>'E', '�'=>'E', '�'=>'I', '�'=>'I', '�'=>'I', '�'=>'I', '�'=>'N', '�'=>'O', '�'=>'O', '�'=>'O', '�'=>'O', '�'=>'O', '�'=>'O', '�'=>'U', 
									'�'=>'U', '�'=>'U', '�'=>'U', '�'=>'Y', '�'=>'B', '�'=>'Ss', '�'=>'a', '�'=>'a', '�'=>'a', '�'=>'a', '�'=>'a', '�'=>'a', '�'=>'a', '�'=>'c', 
									'�'=>'e', '�'=>'e', '�'=>'e', '�'=>'e', '�'=>'i', '�'=>'i', '�'=>'i', '�'=>'i', '�'=>'o', '�'=>'n', '�'=>'o', '�'=>'o', '�'=>'o', '�'=>'o', 
									'�'=>'o', '�'=>'o', '�'=>'u', '�'=>'u', '�'=>'u', '�'=>'y', '�'=>'y', '�'=>'b', '�'=>'y' ); 
		$str = strtr( $str, $unwanted_array ); 

		# Bullets, dashes, and trademarks 
		$str = preg_replace('\''.chr(149).'\'', "", $str );    # bullet � 
		$str = preg_replace('\''.chr(150).'\'', "", $str );    # en dash 
		$str = preg_replace('\''.chr(151).'\'', "", $str );    # em dash 
		$str = preg_replace('\''.chr(153).'\'', "", $str );    # trademark 
		$str = preg_replace('\''.chr(169).'\'', "", $str );    # copyright mark 
		$str = preg_replace('\''.chr(174).'\'', "", $str );        # registration mark 

		return $str; 
	} 

	/**
	* Remove any non-ASCII characters and convert known non-ASCII characters
	* to their ASCII equivalents, if possible.
	*
	* @param string $string
	* @return string $string
	* @author Jay Williams <myd3.com>
	* @license MIT License
	* @link http://gist.github.com/119517
	*/
public	function convert_ascii($string)
		{
			  // Replace Single Curly Quotes
			  $search[] = chr(226).chr(128).chr(152);
			  $replace[] = "";
			  $search[] = chr(226).chr(128).chr(153);
			  $replace[] = "";
			  $search[] = '';
			  $replace[] = "";
			  $search[] = '';
			  $replace[] = "";  

			  // Replace Smart Double Curly Quotes
			  $search[] = chr(226).chr(128).chr(156);
			  $replace[] = '';
			  $search[] = chr(226).chr(128).chr(157);
			  $replace[] = '';

			  $search[] = '';
			  $replace[] = '';
			  $search[] = '';
			  $replace[] = '';

			  // Replace En Dash
			  $search[] = chr(226).chr(128).chr(147);
			  $replace[] = '';
			  
			  // Replace Em Dash
			  $search[] = chr(226).chr(128).chr(148);
			  $replace[] = '';
			  
			  // Replace Bullet
			  $search[] = chr(226).chr(128).chr(162);
			  $replace[] = '';
			  
			  // Replace Middle Dot
			  $search[] = chr(194).chr(183);
			  $replace[] = '';
			 
			  // Apply Replacements
			  $string = str_replace($search, $replace, $string); 
			  
			  // Remove any non-ASCII Characters
			  $string = preg_replace("/[^\x01-\x7F]/","", $string);
			  
			  return $string;
		}	
	public function charge_transaction($payment_profile,$cutomer_profile,$amount,$item_array='',$ref_id='')
		{
				
			$itemm_string='';
			$order_string='';
			if(!empty($item_array) && is_array($item_array) && count($item_array)>0 && !empty($ref_id))
				{
					foreach($item_array as $item)
						{
							$itemm_string.=preg_replace("/[^a-zA-Z0-9\:\s]/","", $item['pname']).',';			
						}
						$itemm_string=rtrim($itemm_string,',');
						
						$order_string='<order><invoiceNumber>'.$ref_id.'</invoiceNumber><description>'.$itemm_string.'</description></order>';				
				}
				
			//build xml to post
			$content =
				"<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
				"<createCustomerProfileTransactionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
				$this->MerchantAuthenticationBlock().
				"<transaction>".
				"<profileTransAuthCapture>".
				"<amount>" .$amount . "</amount>
				<customerProfileId>" . $cutomer_profile . "</customerProfileId>".
				"<customerPaymentProfileId>" .$payment_profile . "</customerPaymentProfileId>".$order_string."</profileTransAuthCapture>".
				"</transaction>".
				"</createCustomerProfileTransactionRequest>";
			
			$response = $this->send_xml_request($content);
			$parsedresponse = $this->parse_api_response($response);
				if(!empty($content))
				{
					$data=array();
					$data=array('user_profile_id'=>$cutomer_profile,'user_payment_profile'=>$payment_profile,'user_request'=>$content);
					$this->CI->db->insert('user_anet_request',$data);
				
				}
				return  $parsedresponse;


	}

	public function refund_transaction($payment_profile,$cutomer_profile,$amount,$transaction_id,$test=FALSE)
	{
		if($test) {
			$response = '<?xml version="1.0" encoding="utf-8"?>
							<createCustomerProfileTransactionResponse xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
							xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
							<messages>
							<resultCode>Ok</resultCode>
							<message>
							<code>I00001</code>
							<text>Successful.</text>
							</message>
							</messages>
							<directResponse>1,1,1,This transaction has been approved.,,P,2165736189,INV000001,,0.00,CC,void,12345,,,,,,,12345,,,,,,,,,,,,,,,,,,80492250B8FE91653C24E90D201C9742,,,,,,,,,,,,,XXXX1111,Visa,,,,,,,,,,,,,,,,,4907537,100.0.0.1]]&gt;</directResponse>
							</createCustomerProfileTransactionResponse>';
		} else {
			//build xml to post
			$content = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
							<createCustomerProfileTransactionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">".$this->MerchantAuthenticationBlock()."
									<transaction>
										<profileTransRefund>
											<amount>".$amount."</amount>
											<customerProfileId>".$cutomer_profile."</customerProfileId>
											<customerPaymentProfileId>".$payment_profile."</customerPaymentProfileId>
											<transId>".$transaction_id."</transId>
										</profileTransRefund>
									</transaction>
								<extraOptions><![CDATA[]]></extraOptions>
							</createCustomerProfileTransactionRequest>";

			$response = $this->send_xml_request($content);
		}
		$parsedresponse = $this->parse_api_response($response);

		return  $parsedresponse;
	}
public function get_user_profile($customer_profile_id)
	{
		
		$content =
			"<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
			"<getCustomerProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
			$this->MerchantAuthenticationBlock().
			"<customerProfileId>" . $customer_profile_id . "</customerProfileId>"."</getCustomerProfileRequest>";				
				$response = $this->send_xml_request($content);
				$parsedresponse = $this->parse_api_response($response);					
				$response = $this->send_xml_request($content);
				$parsedresponse = $this->parse_api_response($response);
				
					return $parsedresponse;


	}
public function update_profile_email($email,$customer_profile_id,$useid)
	{						
			$content ="<?xml version=\"1.0\" encoding=\"utf-8\"?>
						<updateCustomerProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">". $this->MerchantAuthenticationBlock()."
						<profile>
							<merchantCustomerId>".$useid."</merchantCustomerId>
							<email>".$email."</email>							
							<customerProfileId>".$customer_profile_id."</customerProfileId>
						</profile>
						</updateCustomerProfileRequest>";
					$response =  $this->send_xml_request($content);
					$parsedresponse =  $this->parse_api_response($response);
					return  $parsedresponse; 
	}	
}
