<?php
ini_set('memory_limit','2G');
mysql_connect('localhost','supercodernew','wRu!92suP@1');
mysql_select_db('supercodernew');

function MerchantAuthenticationBlock() 
		{

				/*$this->g_apihost = 'api.authorize.net';
				$this->g_apipath ='/xml/v1/request.api';
				$this->g_loginname = "5GGzub2U3m9"; // Keep this secure.
				$this->g_transactionkey = "8dM82j736HyTng5a"; // Keep this secure.
				*/

			$g_loginname = "5GGzub2U3m9"; // Keep this secure.
			$g_transactionkey = "8dM82j736HyTng5a"; // Keep this secure.
			return
			"<merchantAuthentication>".
			"<name>" . $g_loginname . "</name>".
			"<transactionKey>" .$g_transactionkey . "</transactionKey>".
			"</merchantAuthentication>";
	}
function parse_api_response($content)
		{
			$parsedresponse = @simplexml_load_string($content);			
			return $parsedresponse;
		}
function send_xml_request($content)
		{
			$g_apihost='api.authorize.net';
			$g_apipath='/xml/v1/request.api';	   
			
			return send_request_via_fsockopen($g_apihost,$g_apipath,$content);
		}
function check_subscription_period($price_id='',$daytosubtract='',$price_term='')
		{
			$period_data=array();
			$terms='';
			$period='';
			if(!empty($price_term))
				{
					$terms= strtolower(preg_replace("/[^a-z,A-Z]/","",$price_term));// Getting only Alphabets
					$period=preg_replace("/[^0-9]/","",$price_term);// Getting only Number
				}
			if(!empty($price_id) && empty($trial_day)) // In case of non Trial Product
				{
					$sql="select term_description,term from price_terms where price_id=$price_id";
					$res=mysql_query($sql);
					if($res)
						{
							$row=mysql_fetch_object($res);
						
					
						if($row->term!='')
							{
								$term_period='';
							if(!empty($row->term_description))
								{
									$term_period=$row->term_description;
								}
							else
								{
									$term_period=$row->term;
								}
								
								$terms= strtolower(preg_replace("/[^a-z,A-Z]/","",$term_period));// Getting only Alphabets
								$period=preg_replace("/[^0-9]/","",$term_period);// Getting only Number
							}
						}

				}
			
			if(!empty($terms))
				{
					switch($terms)
						{
								case 'months':
								case 'month':
								case 'm':
												$term_months = "+" .$period."month";
								break;

								case 'Y':
								case 'year':
								case 'years':
												$term_months = "+".($period*12)."month";
								break;
								case 'd':
								case 'day':
								case 'days':
												if($period==1)
													{
														$term_months = "+" .$period."day";
													}
													else
													{
														$term_months = "+" .$period."days";
													}

								break;

								default:
												$term_months = "+" .$period."month";
								break;
						}
						
						if($daytosubtract>=1)
							{
								$start_date=date('Y-m-d');
								$start_date = date("Y-m-d" ,strtotime(date("Y-m-d", strtotime($start_date)) . " -".$daytosubtract." day"));
							}
						else
							{
								$start_date=date('Y-m-d');// Start Date
							}
						$end_date = date('Y-m-d' ,strtotime(date("Y-m-d", strtotime($start_date)) . $term_months)); // End Date
						$period_data[]=$start_date;
						$period_data[]=$end_date;
					}


					return $period_data;// Array

		}		
		//function to send xml request via fsockopen
//It is a good idea to check the http status code.
function send_request_via_fsockopen($host,$path,$content)
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
function charge_transaction($payment_profile,$cutomer_profile,$amount, $itemm_string='', $ref_id='')
		{						
			//build xml to post
			$content =
				"<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
				"<createCustomerProfileTransactionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
				MerchantAuthenticationBlock().
				"<transaction>".
				"<profileTransAuthCapture>".
				"<amount>" .$amount . "</amount>". // should include tax, shipping, and everything.			
				"<customerProfileId>" . $cutomer_profile . "</customerProfileId>".
				"<customerPaymentProfileId>" .$payment_profile . "</customerPaymentProfileId>".				
				"<order><invoiceNumber>".$ref_id."</invoiceNumber><description>".str_replace("&"," and ",$itemm_string)."</description></order></profileTransAuthCapture>".
				"</transaction>".
				"</createCustomerProfileTransactionRequest>";	
				//echo $content;die();
			$response = send_xml_request($content);
	
			$parsedresponse = parse_api_response($response);
			
				return  $parsedresponse;
		

	}
	
 $sql="select * from order_masters where resource_type='subs' and active=1 and (rebill_retry<3 or rebill_retry is null) and (end_date=curdate() or  end_date=curdate() -interval 1 day) and is_recurring=1 and order_id = 80426";
$res=mysql_query($sql);
while($res && $row_order=mysql_fetch_object($res))
	{
		if(!empty($row_order->user_id))
			{
				$sql_users="select * from user_payment_profile where user_id=$row_order->user_id limit 1";
				$res_user=mysql_query($sql_users);
				if($res_user)
					{
						$row_user=mysql_fetch_object($res_user);						
						if(!empty($row_user->user_profile_id) && !empty($row_user->payment_profile_id))
							{
								$price=number_format($row_order->product_price,2);
								$ref_id =  time();
								$parsedresponse=charge_transaction($row_user->payment_profile_id,$row_user->user_profile_id,$price, $row_order->product_name, $ref_id);							
								
								if ("Ok" == $parsedresponse->messages->resultCode) 
									{
										if (isset($parsedresponse->directResponse)) 
											{
												$response=$parsedresponse->directResponse;
												$directResponseFields = explode(",", $parsedresponse->directResponse);
												$sql_user_address="select B.*,A.first_name as fname,A.last_name as lname,A.email,A.telephone from users A join address B on A.user_id=B.user_id where A.user_id=$row_order->user_id";
												$res_user_address=mysql_query($sql_user_address);
												if($res_user_address)
													{
														$add_date=date('Y-m-d h:i:s');
														$row_user_address=mysql_fetch_object($res_user_address);
														$sql_insert_order="insert into order_customers
														set customer_id=$row_order->user_id,
														first_name='$row_user_address->fname',
														last_name='$row_user_address->lname',
														email='$row_user_address->email',
														payment_firstname='$row_user_address->first_name',
														payment_lastname='$row_user_address->last_name',
														payment_company='$row_user_address->company',
														payment_address_1='$row_user_address->street_1',
														payment_address_2='$row_user_address->street_2',
														payment_city='$row_user_address->city',
														payment_postcode='$row_user_address->city',
														payment_state='$row_user_address->state',
														payment_state_id='$row_user_address->state_id',
														payment_method='Credit Card',
														total='$price',
														order_status='Processed',
														date_modified='$add_date',
														date_added='$add_date',
														refer_order=$row_order->order_id,
														order_reference_no = $ref_id,
														order_type = 1";
														$res_order_insert=mysql_query($sql_insert_order);
														$order_id=mysql_insert_id();
														if(!empty($order_id))
															{
																$period=check_subscription_period($row_order->product_term,$row_order->rebill_retry,$row_order->price_term_desc);
																if(is_array($period) && count($period)==2)
																	{ 
																		$sql_insert_order_master="insert into order_masters
																		set user_id=$row_order->user_id,
																		order_id='$order_id',
																		product_id='$row_order->product_id',
																		product_name='$row_order->product_name',
																		product_term='$row_order->product_term',
																		product_price='$row_order->product_price',
																		actual_price='$row_order->actual_price',
																		product_total='$row_order->product_total',
																		product_tax='$row_order->product_tax',
																		product_quantity='$row_order->product_quantity',
																		product_type='$row_order->product_type',
																		start_date='$period[0]',
																		end_date='$period[1]',
																		is_recurring='$row_order->is_recurring',
																		resource_type='$row_order->resource_type',
																		article_ids='$row_order->article_ids',
																		active='1',
																		products_alloted='$row_order->products_alloted',
																		price_term_desc='$row_order->price_term_desc',
																		specialty_id='$row_order->specialty_id',
																		article_quantity_allowed='$row_order->article_quantity_allowed',
																		article_quantity_taken='$row_order->article_quantity_taken',
																		package_reffer_id='$row_order->package_reffer_id',
																		date_created='$add_date',
																		date_modified='$add_date'";
																		$res_insert_order_master=mysql_query($sql_insert_order_master);
																		$order_master_id=mysql_insert_id();
																		if($order_master_id)
																			{
																				$sql_update_old_order="update order_masters set active=0 where subs_item_id=$row_order->subs_item_id";
																				mysql_query($sql_update_old_order);
																			}
																		$sql_insert_order_history="insert into 
																			order_history set order_id='$order_id',
																			order_status='Processing',
																			transdetails='$directResponseFields[3]',
																			pnref='$directResponseFields[6]',
																			date_added='$add_date',
																			response='$response'";
																		 mysql_query($sql_insert_order_history);
																	
																	}	
															}
													}
											
											}
										
									}										
								else
									{
										
										$sub_qry='';
										$interval=',end_date=end_date+interval 1 day';
										if($row_order->rebill_retry==2)
											{
												//$sub_qry=',active=0';
												$interval='';											
												
											}
										$sql_update_failure="update order_masters set rebill_retry=rebill_retry+1".$interval." where subs_item_id=$row_order->subs_item_id";
										$res_update= mysql_query($sql_update_failure);
										if($res_update)
											{
												if('Error'==$parsedresponse->messages->resultCode) // Failure
													{
														 $response=explode(',',$parsedresponse->validationDirectResponse);

															if(isset($response[0]) && empty($response[0]))
																{

																	$message='Error Code :'.$parsedresponse->messages->message->code.'<br/> Reason of Error :'.$parsedresponse->messages->message->text;

																}
															else
																{

																	$message=$response['3'];
																}															
													}															
												print_r($parsedresponse);
												$full_response='';
												if(isset($parsedresponse->directResponse))
													{
														$full_response=$parsedresponse->directResponse;
													}
												else
													{
														$full_response=$message;
													}
												
												$sql_rebill_log="insert into rebill_log(order_id,rebill_response,rebill_response_message) values($row_order->order_id,'".mysql_real_escape_string($full_response)."','".mysql_real_escape_string($message)."')";
												$res_insert=mysql_query($sql_rebill_log);
												$newmsg = print_r($parsedresponse, true);
												$headers = 'From: SuperCoder Rebill<support@supercoder.com>' . "\r\n" .
																'Reply-To: SuperCoder Rebill<support@supercoder>' . "\r\n";
												mail("lmoharana@beckett.com,sunilp@eliinfra.com,sandeepd@supercoder.com","Rebill Log",$newmsg, $headers);
											}
									}
							}
					}
			}
	}
?>