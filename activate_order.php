<?php 
session_start();
$link = mysql_connect('localhost','supercoderweb1','cSr54@!65f2g');
mysql_select_db('supercodernew');
if (!$link) { 
	die('Could not connect to MySQL: ' . mysql_error()); 
}

function check_users_order_active($user_id)		
{
	$today=date('Y-m-d');
	echo  $sql="select count(*) as tot from order_masters where user_id='$user_id' and  resource_type='subs' and  active=1 and end_date>='$today'";
	$res=mysql_query($sql);
	if($res)
	{
		$row=mysql_fetch_object($res);
		if($row)
		{
			return $row->tot;
		}
	}
	return false;	
}	
function update_user_perms_data($unsetpid,$user_id,$active,$order_id='',$unsetspecialtyid='')	
{
	
	$product_id_string='';
	$master_product_id='';
	$master_product_id_string='';
	$unset_master_product_id=array();
	$unset_product_id=array();
	$orignal_master_product_id=array();
	$unset_master_product_id_flip=array();
	$orignal_master_product_id_flip=array();
	$unset_specialty_id_flip=array();
	$unset_specialty_id=array();
	$user_order_perms='';
	$specialty_arra=array();
	$specialty_string="";
	$specialty_arra=explode(',',$specialty_string);
	
	$product_spec="";
	$product_spec_arra=explode(',',$product_spec);
	
	
	echo "order_id is ".$order_id;
	// Get All Master Product Ids of Product need to Be Unset
	/*if(!empty($unsetpid))
	{					
		$sql_master="select master_product_ids from products where product_id=$unsetpid limit 1";							
		$res=mysql_query($sql_master);
		if($res)
		{	
			$row_master=mysql_fetch_object($res);									
			if($row_master->master_product_ids)
			{
				$master_ids_unset=$unsetspecialtyid.','.$row_master->master_product_ids;
				$unset_master_product_id=explode(',',$master_ids_unset);
				$unset_master_product_id_flip=array_flip($unset_master_product_id); // Making Master Product Id's as Key
				$unset_product_id=$unsetpid;// Product Id need to be unset								
			}
			else if(!empty($unsetspecialtyid))
			{
				$unset_master_product_id=explode(',',$unsetspecialtyid);
				$unset_master_product_id_flip=array_flip($unset_master_product_id); // Making Master Product Id's as Key
				$unset_product_id=$unsetpid;// Product Id need to be unset	
				
			}
		}
	}	
	echo "<pre>";
	echo "<br/>unset pid<br/>";
	echo $unset_product_id;
	echo "<br/>unset master id<br/>";
	print_r($unset_master_product_id_flip);
	*/

	$sql="select * from user_perms where user_id=$user_id limit 1"; // Getting User Current Perms 
	$res_user=mysql_query($sql);
	if($res_user)
	{
					
		$row_user=mysql_fetch_object($res_user);

		$bupdate= $row_user->bupdate; 

		if(!empty($row_user->master_product_ids))
		{								
		
			$orignal_master_product_id=explode(',',$row_user->master_product_ids);								
			$orignal_master_product_id_flip=array_flip($orignal_master_product_id); // Getting Current User Perms
		}
		echo "<br/>Original Master Ids<br/>";
		print_r($orignal_master_product_id_flip);
		
		
		if(!empty($row_user->product_ids))
		{								
		
			$orignal_product_id=explode(',',$row_user->product_ids);								
			$orignal_product_id_flip=array_flip($orignal_product_id); // Getting Current Product ids
		}		
	echo "<br/>Original Product Ids<br/>";
		print_r($orignal_product_id_flip);
		// Unset All the master product ids of the Product need to be unset		
		if(is_array($orignal_master_product_id_flip) && count($orignal_master_product_id_flip)>0 && is_array($unset_master_product_id_flip) && count($unset_master_product_id_flip)>0)
		{
			foreach($unset_master_product_id_flip as $key=>$val)
			{
				if(array_key_exists($key,$orignal_master_product_id_flip))
					{
						unset($orignal_master_product_id_flip[$key]);
					}
				
			}
		}
		
		if($active==0)// If user has no active suscription
		{
			//echo "<br/><br/>";
			//echo "inside the active = 0";
			//echo "<br/><br/>";
			if(!empty($row_user->master_specialty_ids)) 
			{												
				$unset_specialty_id=explode(',',$row_user->master_specialty_ids);								
				$unset_specialty_id_flip=array_flip($unset_specialty_id); // Getting Current User Perms
			}
			
			if(is_array($orignal_master_product_id_flip) && count($orignal_master_product_id_flip)>0 && is_array($unset_specialty_id_flip) && count($unset_specialty_id_flip)>0)
				{
					foreach($unset_specialty_id_flip as $key=>$val)
						{
							if(array_key_exists($key,$orignal_master_product_id_flip))
								{
									unset($orignal_master_product_id_flip[$key]);
								}
							
						}
				}
			
		}
		
		// Unset All the product id of the Product need to be unset		
		if(is_array($orignal_product_id_flip) && count($orignal_product_id_flip)>0 && !empty($unset_product_id))
		{
				
			if(array_key_exists($unset_product_id,$orignal_product_id_flip))
				{
					unset($orignal_product_id_flip[$unset_product_id]);
				}
						
		}					
			
		
	}	
	
	$final_rest_master_ids='';			
	$new_array=array();
	$new_array_product=array();
	$final_rest_product_ids='';
	$final_rest_master_ids_another='';
	// Making String of Rest of the Master Product IDs
	if(is_array($orignal_master_product_id_flip) && count($orignal_master_product_id_flip)>0)
		{
			$new_array=array_flip($orignal_master_product_id_flip);
			foreach($new_array as $nvalue)
				{
					if(in_array($nvalue,$specialty_arra))
						{
							 $final_rest_master_ids_another.=$nvalue.',';
						}
				}
			$final_rest_master_ids_another=trim($final_rest_master_ids_another,',');
		}

		$final_rest_product_ids_another='';
		// Making String of Rest of the Product IDs
		if(is_array($orignal_product_id_flip) && count($orignal_product_id_flip)>0)
		{
			
			$new_array_product=array_flip($orignal_product_id_flip);
			$anotherproduct=0;
			
			foreach($new_array_product as $npvalue)
				{
					if(in_array($npvalue,$product_spec_arra))
						{
							$final_rest_product_ids.=$npvalue.',';
						}
					else
						{	
							$anotherproduct=1;
							$final_rest_product_ids_another='';
						}
				}

				$allowmerge=0;
		}	
	
		if(!isset($master_product_id)) $master_product_id = '';
		if(!isset($product_id_string)) $product_id_string = '';
		// If there is any Master product ID Left or Product Id Left
					if(!empty($final_rest_master_ids) || !empty($final_rest_product_ids_another))
					{
						$master_product_id=rtrim($final_rest_master_ids,','); 
					}
				
						$sql_order_master_perms="select om.product_id,om.user_id,om.order_id,p.master_product_ids,sum(p.speciality_allowed) as spallow from order_masters om 
join products p on om.product_id=p.product_id where om.end_date>=curdate() and om.active=1 and om.resource_type='subs' and om.user_id=$user_id group by p.product_id ";
							$res_order_masters_perms=mysql_query($sql_order_master_perms);	
							if($res_order_masters_perms)
							{
							
								
									while($res_order_masters_perms && $row_order_masters_perms=mysql_fetch_object($res_order_masters_perms))
									{
											$data_user_perms[$row_order_masters_perms->order_id][$row_order_masters_perms->product_id]=$row_order_masters_perms->master_product_ids;
											$master_product_id.=','.$row_order_masters_perms->master_product_ids;
											$product_id_string.=','.$row_order_masters_perms->product_id;
											if($row_order_masters_perms->spallow>0)
												{
													$allowmerge=1;														
												}
									}
									$user_order_perms=serialize($data_user_perms);	
							}
							echo $sql="select * from user_perms where user_id=$user_id limit 1"; // Getting Their Perms							
								$res_user=mysql_query($sql);
								if($res_user)
									{
										$row_user1=mysql_fetch_object($res_user);	
										$bupdate= $row_user1->bupdate; 

										// For old user having any active subscription getting thier Specialty IDS and merging them to Master Product Id String									
										if($active!=0 && $allowmerge>0) 
											{		
													echo "<br/>Allow merger 2<br/>";	
													echo "<br/>Rest Master Ids total<br/>";	
												echo $master_product_id.=','.$final_rest_master_ids_another.','.$row_user1->master_specialty_ids; 
												echo "<br/>Rest Products Ids Total<br/>";	
												$product_id_string.=','.$final_rest_product_ids.','.$row_user1->specialty_ids; 
											}
																				
											
									}
							echo "<br>user order perms is";
							echo $user_order_perms;
							//die();
						/* Line Added by Sunil For multi specialty */
						if(!empty($user_id))
								{
									$sqlmulti="select group_concat(specialty_id) as specialty from order_masters where product_type='multispecialty' and user_id=$user_id and active=1 and end_date>=curdate()";
									$res_user_multi=mysql_query($sqlmulti);
									if($res_user_multi)
										{
											$resultmulti=mysql_fetch_object($res_user_multi);;
										
											if($resultmulti)
												{
													$specialty_id=$resultmulti->specialty;
													$master_product_id =$master_product_id .','.$specialty_id;
												}
										}
								}
						/* Ended*/
						// Preparing Unique master product Ids String	
						if(!empty($master_product_id))
							{
								$master_product_id = trim($master_product_id,',');
								$master_array=explode(',',$master_product_id);
								$unique = array_unique($master_array);
								$master_product_id_string=implode(',',$unique);
								
							}
						// Preparing Unique Product Ids String	
						if(!empty($product_id_string))
								{
									$product_id_string = trim($product_id_string,',');
									$product_array=explode(',',$product_id_string);
									$unique_product = array_unique($product_array);
									$product_id_string=implode(',',$unique_product);
								
								}
								
						$modified="'".date('Y-m-d')."'";		
						
						$new_bupdate = 0;
						if($bupdate==2) $new_bupdate = 4;
						if($bupdate==3) $new_bupdate = 6;

						// Updating the database
						if($active==0)
							{
									$sql_update_user_perms="update user_perms set master_product_ids='',product_ids='',user_order_perms='',date_modified=$modified, master_specialty_ids='',specialty_ids='', bupdate=$new_bupdate where user_id=$user_id";
									mysql_query($sql_update_user_perms);
									echo $sql_update_user_perms;
									$exp_upd = "update users set user_type='E' where user_id=$user_id";
									mysql_query($exp_upd);
							}
						else
							{
								
								 if($allowmerge>0)
									{
										if(!empty($user_order_perms))
											{
												echo "<br>".$sql_update_user_perms="update user_perms set master_product_ids='$master_product_id_string',product_ids='$product_id_string',user_order_perms='$user_order_perms',date_modified=$modified, bupdate=$new_bupdate where user_id=$user_id";
												mysql_query($sql_update_user_perms);
											}
										else
											{
												echo $sql_update_user_perms="update user_perms set master_product_ids='$master_product_id_string',product_ids='$product_id_string',date_modified=$modified, bupdate=$new_bupdate where user_id=$user_id";
												mysql_query($sql_update_user_perms);
											}
									}
								else
									{
										if(!empty($user_order_perms))
											{
												echo "<br>".$sql_update_user_perms="update user_perms set master_product_ids='$master_product_id_string',product_ids='$product_id_string',user_order_perms='$user_order_perms',master_specialty_ids='7777',specialty_ids='7777',date_modified=$modified, bupdate=$new_bupdate where user_id=$user_id";
												mysql_query($sql_update_user_perms);
											}
										else
											{
												echo $sql_update_user_perms="update user_perms set master_product_ids='$master_product_id_string',product_ids='$product_id_string',master_specialty_ids='7777',specialty_ids='7777',date_modified=$modified, bupdate=$new_bupdate where user_id=$user_id";
												mysql_query($sql_update_user_perms);
											}
									}

							}	
					
	}
function update_user_subscription_status($user_id)
	{
	
		if(!empty($user_id))
			{
				$sql_users="select u.user_type,u.user_sub_type,u.user_id,u.parent_id,u.user_sub_status from users u where u.user_id=$user_id  limit 1";		
				$res_orders=mysql_query($sql_users);	
			
			if($res_orders)
				{
					
					$row_orders=mysql_fetch_object($res_orders);
					
					if(!empty($row_orders->user_id) && $row_orders->user_sub_type!='TCI' && $row_orders->user_sub_type!='Demo' && $row_orders->user_sub_type!='Comp')
						{
									$sql_user_order="select sum(is_recurring) as recurring,sum(om.active) as active,group_concat(distinct om.active) as activetype,om.user_id,
																	group_concat(distinct om.resource_type) as rtype,group_concat(distinct om.is_trial) as trialtype,
																	group_concat(distinct om.product_type) as ptype,group_concat(distinct om.product_name) as pname,
																	group_concat(distinct om.complementary) as complement											
																	from order_masters om 
																	join order_customers oc on oc.order_id=om.order_id 
																	where om.user_id='$row_orders->user_id' 
																	group by om.user_id";

									$res_user_order=mysql_query($sql_user_order);
									
									if($res_user_order)
										{
											
											//enum('article','product','package','book','audio','articlepack','other','specialty','survivalpack','addon','expertpack','questionpack'
											$row_user_order=mysql_fetch_object($res_user_order);
											if($row_user_order)
												{
												
													$ptype=trim($row_user_order->ptype);
													$rtype=trim($row_user_order->rtype);
													$active=$row_user_order->active;
													$activetype=$row_user_order->activetype;
													$trialtype=$row_user_order->trialtype;											
													$recurring=$row_user_order->recurring;
													$pname=trim($row_user_order->pname);
													$complement=trim($row_user_order->complement);
													$user_status='';										
													if(strpos($rtype,'subs')!==FALSE && $pname!='Print Only' && (strpos($ptype,'product')!==FALSE || strpos($ptype,'package')!==FALSE 
													|| strpos($ptype,'specialty')!==FALSE || strpos($ptype,'survivalpack')!==FALSE || strpos($ptype,'expertpack')!==FALSE || strpos($ptype,'questionpack')!==FALSE) )
														{
															if($trialtype=='1')
																{												
																	if(strpos($activetype,'1')!==FALSE)
																		{
																			$user_status='Active Trial';
																		}
																	else
																		{
																			$user_status='Expired Trial';
																		}															
																}
															else if($recurring>0 && strpos($activetype,'1')!==FALSE)
																{
																	$user_status='Active Recurring';
																}
															else if($recurring==0 && strpos($activetype,'1')!==FALSE)
																{
																	$user_status='Active Non Recurring';
																}													
															else
																{
																			$user_status='Expired';
																}										
															if(!empty($user_status) && $complement!='9' && $complement!='10' && $complement!='11')
																{
																	switch($row_orders->user_sub_type)
																		{
																			
																			case 'Medallian':
																				$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																				$res_update=mysql_query($sql_update);
																			break;
																			case 'Corporate':
																				$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																				$res_update=mysql_query($sql_update);
																			break;
																			case 'Subuser':
																				$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																				$res_update=mysql_query($sql_update);
																			break;
																			default:
																				$sql_update="update users set user_sub_type='Subscriber',user_sub_status='$user_status' where user_id=$row_orders->user_id";
																				$res_update=mysql_query($sql_update);
																			break;
																		}
																}
															else if($complement=='9')
																{
																	switch($row_orders->user_sub_type)
																		{
																			case 'Medallian':
																				$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																				$res_update=mysql_query($sql_update);
																			break;
																			case 'Corporate':
																				$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																				$res_update=mysql_query($sql_update);
																			break;
																			case 'Subuser':
																				$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																				$res_update=mysql_query($sql_update);
																			break;
																			default:
																					$sql_update="update users set user_sub_type='Print Buyer',user_sub_status='$user_status' where user_id=$row_orders->user_id";
																					$res_update=mysql_query($sql_update);
																			break;
																		}
																
																
																}
															else if($complement=='10' || $complement=='11')
																{
																	switch($row_orders->user_sub_type)
																		{
																			case 'Medallian':
																				$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																				$res_update=mysql_query($sql_update);
																			break;
																				case 'Corporate':
																				$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																				$res_update=mysql_query($sql_update);
																			break;
																			case 'Subuser':
																				$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																				$res_update=mysql_query($sql_update);
																			break;
																			default:
																					$sql_update="update users set user_sub_type='Comp',user_sub_status='Active Non Recurring' where user_id=$row_orders->user_id";
																					$res_update=mysql_query($sql_update);
																			break;
																		}
																	
																}
														}								
													else if(strpos($rtype,'subs')!==FALSE && strpos($pname,'Print Only')!==FALSE)
														{
															if($recurring>0 && $active>0)
																{
																		$user_status='Active Recurring';
																}
															else if($recurring==0 && $active>0)
																{
																	$user_status='Active Non Recurring';
																}
															else
																{
																	$user_status='Expired';
																}
														switch($row_orders->user_sub_type)
																{
																	case 'Medallian':
																		$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																		$res_update=mysql_query($sql_update);
																	break;
																	case 'Corporate':
																			$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																			$res_update=mysql_query($sql_update);
																		break;
																	case 'Subuser':
																		$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																		$res_update=mysql_query($sql_update);
																	break;
																	default:
																			$sql_update="update users set user_sub_type='Print Buyer',user_sub_status='$user_status' where user_id=$row_orders->user_id";
																			$res_update=mysql_query($sql_update);
																	break;
																}
															
														}
													else if($rtype=='singleoff' || (strpos($rtype,'subs')!==FALSE && (strpos($ptype,'articlepack')!==FALSE || strpos($ptype,'article')!==FALSE) ) && $row_orders->user_sub_type!='Medallian' && $row_orders->user_sub_type!='Subuser' &&  $row_orders->user_sub_type!='TCI')
														{
															switch($row_orders->user_sub_type)
																{
																	case 'Medallian':
																		$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																		$res_update=mysql_query($sql_update);
																	break;
																	case 'Corporate':
																			$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																			$res_update=mysql_query($sql_update);
																		break;
																	case 'Subuser':
																		$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																		$res_update=mysql_query($sql_update);
																	break;
																	default:
																			$sql_update="update users set user_sub_type='Registered Buyer',user_sub_status='Registered' where user_id=$row_orders->user_id";
																			$res_update=mysql_query($sql_update);
																	break;
																}
															
														}
													else if($pname==''){
														switch($row_orders->user_sub_type)
																{
																	case 'Medallian':
																		$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																		$res_update=mysql_query($sql_update);
																	break;
																	case 'Corporate':
																			$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																			$res_update=mysql_query($sql_update);
																	break;
																	case 'Subuser':
																		$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																		$res_update=mysql_query($sql_update);
																	break;
																	default:
																			$sql_update="update users set user_sub_type='Registered',user_sub_status='Registered' where user_id=$row_orders->user_id";
																			$res_update=mysql_query($sql_update);
																	break;
																}													
													}
													
												}
												else if($row_orders->user_sub_type!='Medallian' && $row_orders->user_sub_type!='Corporate' && $row_orders->user_sub_type!='Subuser' &&  $row_orders->user_sub_type!='TCI' )
													{
														$sql_update="update users set user_sub_type='Registered',user_sub_status='Registered' where user_id=$row_orders->user_id";
														$res_update=mysql_query($sql_update);
													}
												else if(empty($row_orders->user_sub_type))
													{
															echo $sql_update="update users set user_sub_type='Registered',user_sub_status='Registered' where user_id=$row_orders->user_id";
															$res_update=mysql_query($sql_update);
													}
										
											
										}
									else if($row_orders->user_sub_type!='Medallian' && $row_orders->user_sub_type!='Corporate'  && $row_orders->user_sub_type!='Subuser' &&  $row_orders->user_sub_type!='TCI' )
										{
											$sql_update="update users set user_sub_type='Registered',user_sub_status='Registered' where user_id=$row_orders->user_id";
											$res_update=mysql_query($sql_update);
										}
									else if(empty($row_orders->user_sub_type))
										{
												$sql_update="update users set user_sub_type='Registered',user_sub_status='Registered' where user_id=$row_orders->user_id";
												$res_update=mysql_query($sql_update);
										}
							if($row_orders->user_sub_type=='Subuser' && $user_status=='Expired')
								{
								
									$sql="select * from subuser_product where user_id=$row_orders->user_id and parent_id=$row_orders->parent_id and status=1";
									$res_sub=mysql_query($sql);
									if(mysql_num_rows($res_sub)>=1)
										{
											echo $sql_update="update users set user_sub_status='Active Non Recurring',active=1 where user_id=$row_orders->user_id";
											$res_update=mysql_query($sql_update);
										}
								}
						}
				}
			}
	}

if(isset($_POST['subs_item_id']) && $_POST['subs_item_id']!='') {
	$entered_by = '';
	$description ='';
	if(isset($_SESSION['user']) && !empty($_SESSION['user'])) {
		$entered_by = $_SESSION['user']['user_id'];
		$description = $_SESSION['user']['email']." activated this product";
	}
	$sql_update = "update order_masters set active=1 where subs_item_id=".$_POST['subs_item_id'];
	mysql_query($sql_update);
	$get_order_detail = mysql_query("select * from order_masters where subs_item_id=".$_POST['subs_item_id']." limit 1");
	if($order_rs = mysql_fetch_assoc($get_order_detail)) {
		$comment_update = "insert into comments (type, result, user_id, description, comment, entered_by, date_created, product_id, order_id, subs_item_id) values('ACTIVATE_PRODUCT','SUCCESSFUL',".$order_rs['user_id'].",'".$description."','".$_POST['comment']."',".$entered_by.",now(),".$order_rs['product_id'].",".$order_rs['order_id'].",".$order_rs['subs_item_id'].")";
		if( mysql_query($comment_update) or die(mysql_error()."==>".$comment_update))
			echo "Transaction Successful";
		
	}	
	$get_user_id = mysql_query("select email from users where user_id='".$_POST['user_id']."' limit 1") or die(mysql_error()." - Error in checking user query");
	$user_rs = mysql_fetch_assoc($get_user_id);
	$email = $user_rs['email'];
	update_user_perms_data(0,$_POST['user_id'],1);
	update_user_subscription_status($_POST['user_id']);
	//show_order_list($email);
}


?>
