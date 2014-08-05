<?php
/*
 * Provides helper functions dealing with users
 */

/**
 * Checks if the current user is logged in
 *
 * @return bool
 */
 
function is_logged_in() {
    //$CI =& get_instance();
    //$current_user = $CI->get_current_user();
if(isset($_SESSION['user']['user_id']) && !empty($_SESSION['user']['user_id']))
	{
	/*if($current_user) {*/	
		return (bool)$_SESSION['user']['user_id'];
	} else {
		return FALSE;
	}
}
/**
 * Checks if the current user is an administrator
 *
 * @return bool
 */
function is_admin() {
    $CI =& get_instance();
    $current_user = $CI->get_current_user();
	return (bool)($current_user['role'] == "ADMIN" && $current_user['employee'] == 1);
}

/**
 * Checks if the current user is an employee
 *
 * @return bool
 */
function is_employee() {
    $CI =& get_instance();
    $current_user = $CI->get_current_user();
    return (bool)($current_user['employee'] == 1);
}

/**
 * Checks if the current user is an staff
 *
 * @return bool
 */
function is_staff() {
    $CI =& get_instance();
    $current_user = $CI->get_current_user();
    return (bool)(($current_user['role'] == "STAFF" || $current_user['role'] == "USER") && $current_user['employee'] == 1);
}

/**
 * Checks if the current user is an employee user
 *
 * @return bool
 */
function is_user() {
    $CI =& get_instance();
    $current_user = $CI->get_current_user();
    return (bool)(($current_user['role'] == "USER" || $current_user['role'] == "STAFF") && $current_user['employee'] == 1);
}

/**
 * Get's the username based off the user id
 *
 * @author Dheeraj Kumar <dkumar@beckett.com>
 * @param int, $user_id
 * @return string
 */
function get_username($user_id) {
	$user = new Users;
	$user->initialize($user_id);
	return $user->user_name;
}
/**
 * Get's the username based off the user id
 *
 * @author Dheeraj Kumar <dkumar@beckett.com>
 * @param int, $user_id
 * @return string
 */
function get_userdata($user_id) {
	$user = new Users;
	$user->initialize($user_id);
	return $user;
}
/**
 * Get's the name of user from session
 *
 * @author Dheeraj Kumar
 * @return string
 */
function get_logged_user_name() {
	$CI =& get_instance();
    $current_user = $CI->get_current_user();
	return (!empty($current_user) ? $current_user['first_name'] .' '. $current_user['last_name'] : 'Guest');
}

function get_logged_username() {
	$CI =& get_instance();
    $current_user = $CI->get_current_user();
	return (!empty($current_user) ? $current_user['first_name']: 'Guest');
}

/**
 * Get's the user data from session
 *
 * @author Dheeraj Kumar <dkumar@beckett.com>
 * @return array
 */
function get_current_user_data() {
	//$CI =& get_instance();
    //$current_user = $CI->get_current_user();

	if(isset($_SESSION['user']) && !empty($_SESSION['user']) && !empty($_SESSION['user']['user_id']))
		{
			return $_SESSION['user'];
		}
	/*print_r($current_user);
	die("sss");
    if( $current_user && !empty($current_user) ) {
		return $current_user;
	}*/ else {
		return;
	}
}
/**
 * Get's the user id from session
 *
 * @author Dheeraj Kumar <dkumar@beckett.com>
 * @return string
 */

function get_current_user_id() {
	$CI =& get_instance();
    $current_user = $CI->get_current_user();
    if( $current_user && isset($current_user['user_id']) ) {
		return $current_user['user_id'];
	} else {
		return;
	}
}

/**
 * Get's the user id from session
 *
 * @author Dheeraj Kumar <dkumar@beckett.com>
 * @return string
 */

function get_current_user_type() {
	$CI =& get_instance();
    $current_user = $CI->get_current_user();
    if( $current_user && isset($current_user['user_sub_type']) ) {
		return $current_user['user_sub_type'];
	} else {
		return;
	}
}

/**
 * Checks if a given user has access to Admin area
 *
 * @param int $user_id
 * @param int filename, action
 * @return bool
 */
function has_admin_access($permission='read', $action='index',$filename='', $permission_type='admin') {
	$CI =& get_instance();
    $current_user = $CI->get_current_user();
	$adminuser_perms = (isset($current_user['adminuser_perms']) && !empty($current_user['adminuser_perms']) ? $current_user['adminuser_perms'] : array());
	if(!empty($adminuser_perms) && !empty($permission)) {
		$perm_array = array();
		switch($permission) {
			case 'read':
				if(isset($adminuser_perms['read']) && !empty($adminuser_perms['read'])) {
					foreach($adminuser_perms['read'] as $path) {
						$path_perms = explode('/',$path);
						$perm_array[$path_perms[0]] = $path_perms[1];
					}
				}
				//$perm_array = (isset($adminuser_perms['read']) ? toArray($adminuser_perms['read'],'admin_file_id','action') : array());
			break;
			case 'write':
				//$perm_array = (isset($adminuser_perms['write']) ? toArray($adminuser_perms['write'],'admin_file_id','action') : array());
				if(isset($adminuser_perms['write']) && !empty($adminuser_perms['write'])) {
					foreach($adminuser_perms['write'] as $path) {
						$path_perms = explode('/',$path);
						$perm_array[$path_perms[0]] = $path_perms[1];
					}
				}
			break;
		}

		if(!empty($perm_array)) {
			if(isset($perm_array[$filename]) && $perm_array[$filename] == '*') {
				return TRUE;
			} else if(isset($perm_array[$filename]) && $perm_array[$filename] == $action) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	} else {
		return FALSE;
	}
}

/**
 * Checks if a given user has access to select any article
 *
 * @param int $user_id
 * @param int $specialty_id
 * @return array
 */
function has_article_access($user_id,$specialty_id=0) {
	$CI =& get_instance();
	$sql="select * from user_article_preference where user_id=$user_id and specialty_id=$specialty_id";
	$result_array=array();
	$query=$CI->db->query($sql);
	if($query)
		{
			$result=$query->result_array();
			if(is_array($result) && count($result) && $result[0]['pref_id'])
				{
					if($result[0]['article_allowed']!=$result[0]['article_selected'])
						{
							$result_array['total']=$result[0]['article_allowed'];
							$result_array['selected']=$result[0]['article_selected'];
							$article=$result[0]['article_selected'];
							if($specialty_id!=0) // If user has access to choose article from Particular Specialty
								{
									$result_array['msg']="You have choosen $article out of ".$result[0]['article_allowed']." article for this category";
								}
							else
								{
									$result_array['msg']= "You have choosen $article out of ".$result[0]['article_allowed']." article";
								}
						}
				}
		}
	return $result_array;

}

/**
 * Function to check whether user have any active trial or not
 */
function check_trial_active($user_id,$product_id='')
		{
			if(!empty($user_id))
				{

					$CI =& get_instance();
					$sql="select B.product_name from user_trial_access_history A join products B on B.product_id=A.trial_product_id where user_id=$user_id and is_active=1";
					$query=$CI->db->query($sql);
					$result=$query->result_array();

					if(is_array($result) && count($result)>0)
						{
								$CI->session->set_userdata('error',"You have already ".$result[0]['product_name']." trial subscription active.Please remove from cart to continue.");
								return true;
						}

					if(!is_array($product_id) && !empty($product_id))
						{
							$sql_product="select B.product_name from user_trial_access_history A join products B on B.product_id=A.trial_product_id where user_id=$user_id and trial_product_id=$product_id";
							$query_product=$CI->db->query($sql_product);
							$result_product=$query_product->result_array();
							if(is_array($result_product) && count($result_product)>0)
								{
									$CI->session->set_userdata('error',"You have already used trial subscription for ".$result_product[0]['product_name'].".Please remove from cart to continue");
									return true;
								}
						}
					else if(is_array($product_id) && count($product_id)>0)
						{

							foreach($product_id as $pid)
								{
									$sql_product="select B.product_name from user_trial_access_history A join products B on B.product_id=A.trial_product_id where user_id=$user_id and trial_product_id=$pid";
									$query_product=$CI->db->query($sql_product);
									$result_product=$query_product->result_array();
									if(is_array($result_product) && count($result_product)>0)
										{
											$CI->session->set_userdata('error',"You have already used trial subscription for ".$result_product[0]['product_name'].". Please remove from cart to continue.");
											return true;
										}
								}
						}
					return false;
				}
			return true;
		}
	function check_trial_active_email($email,$product_id='')
		{
			if(!empty($email))
				{
					$CI =& get_instance();
					$email=trim(strtolower($email));
					$sql="select user_id from users where email='$email' and active=1";
					$query=$CI->db->query($sql);
					$result=$query->result_array();
					$user_id='';
					if(is_array($result) && count($result)>0 && !empty($result[0]['user_id']))
						{
							$user_id=$result[0]['user_id'];
						}
								
					if(!empty($user_id))
						{
							$sql="select B.product_name from user_trial_access_history A join products B on B.product_id=A.trial_product_id where user_id=$user_id and is_active=1";
							$query=$CI->db->query($sql);
							$result=$query->result_array();

							if(is_array($result) && count($result)>0)
								{
										$CI->session->set_userdata('error_trial',"You have already ".$result[0]['product_name']." trial subscription active.");
										return true;
								}

							if(!is_array($product_id) && !empty($product_id))
								{
									$sql_product="select B.product_name from user_trial_access_history A join products B on B.product_id=A.trial_product_id where user_id=$user_id and trial_product_id=$product_id";
									$query_product=$CI->db->query($sql_product);
									$result_product=$query_product->result_array();
									if(is_array($result_product) && count($result_product)>0)
										{
											$CI->session->set_userdata('error_trial',"You have already used trial subscription for ".$result_product[0]['product_name']);
											return true;
										}
								}
							else if(is_array($product_id) && count($product_id)>0)
								{

									foreach($product_id as $pid)
										{
											$sql_product="select B.product_name from user_trial_access_history A join products B on B.product_id=A.trial_product_id where user_id=$user_id and trial_product_id=$pid";
											$query_product=$CI->db->query($sql_product);
											$result_product=$query_product->result_array();
											if(is_array($result_product) && count($result_product)>0)
												{
													$CI->session->set_userdata('error_trial',"You have already used trial subscription for ".$result_product[0]['product_name']);
													return true;
												}
										}
								}
							return false;
						}
					else
						{
							return false;
						}
				}		
			return false;
		}	

/**
 * Update article permission for selcted articles_id for a user
 *
 * @param string $articles_id
 * @param int $user_id
 * @param int $specialty_id
 * @return array
 */
function update_user_article_access_data($articles_id,$user_id,$specialty_id=0,$specialty)
		{


			if(!empty($articles_id) && !empty($user_id))
				{
					$CI =& get_instance();
					$array_preference_specialty=array();
					$array_preference_general=array();
					$total_genral_allowed=0;
					$total_specialty_allowed=0;
					$total_allowed=0;


					if($specialty_id!=0)
						{
							$array_preference_specialty=has_article_access($user_id,$specialty_id);
							if(is_array($array_preference_specialty) && count($array_preference_specialty)==3)
								{
									$total_specialty_allowed=$array_preference_specialty['total']-$array_preference_specialty['selected'];
								}
							$array_preference_general=has_article_access($user_id,0);
							if(is_array($array_preference_general) && count($array_preference_general)==3)
								{
									$total_genral_allowed=$array_preference_general['total']-$array_preference_general['selected'];
								}
							$total_allowed=$total_specialty_allowed+$total_genral_allowed;
						}
					else
						{
							$array_preference_general=has_article_access($user_id,0);
							if(is_array($array_preference_general) && count($array_preference_general)==3)
								{
									$total_genral_allowed=$array_preference_general['total']-$array_preference_general['selected'];
								}
								$total_allowed=$total_genral_allowed;
						}

					$sql="select * from user_article_access where user_id='$user_id' limit 1";
					$query=$CI->db->query($sql);
					$result=$query->result_array();	// Product Data	Array
					$count=0;
					$article_array=array();
					$count=count(explode(',',rtrim($articles_id,',')));
					$article_array=explode(',',rtrim($articles_id,','));
					if($count<=$total_allowed)
						{

							if(is_array($result) && !empty($result[0]['user_access_id']))
									{
										$articleid=$result[0]['article_ids'].$articles_id;
										$specialtyid=$result[0]['specialty_ids'].$specialty;
										$specialty_array=explode(',',rtrim($specialtyid,','));
										$uni_speci_array=array_unique($specialty_array);
										$final_specialty_string=implode(',',$uni_speci_array).',';
										$data_update=array('article_ids'=>$articleid,'specialty_ids'=>$final_specialty_string);
										$CI->db->where('user_id',$user_id);
										$CI->db->update('user_article_access',$data_update);
									}
							else
									{
										$specialtyid=$specialty.',';
										$data_add=array('article_ids'=>$articles_id,'user_id'=>$user_id,'specialty_ids'=>$specialtyid);
										$CI->db->insert('user_article_access',$data_add);
									}
							if($specialty_id==0)
								{
									$new_selected=$array_preference_general['selected']+$count;
									$data_update=array('article_selected'=>$new_selected);
									$CI->db->where('user_id',$user_id);
									$CI->db->where('specialty_id',0);
									$CI->db->update('user_article_preference',$data_update);
									update_user_article_order_master($user_id,$specialty_id,$article_array);
								}
							else
								{
									if($count>$total_specialty_allowed)
										{
											$new_selected=$array_preference_specialty['total'];
											$data_update=array('article_selected'=>$new_selected);
											$CI->db->where('user_id',$user_id);
											$CI->db->where('specialty_id',$specialty_id);
											$CI->db->update('user_article_preference',$data_update);
											$genral_selected=$array_preference_general['selected']+($count-$total_specialty_allowed);
											$data_update=array('article_selected'=>$genral_selected);
											$CI->db->where('user_id',$user_id);
											$CI->db->where('specialty_id','0');
											$CI->db->update('user_article_preference',$data_update);
											update_user_article_order_master($user_id,$specialty_id,$article_array);
										}
									else
										{
											$new_selected=$array_preference_general['selected']+$count;
											$data_update=array('article_selected'=>$new_selected);
											$CI->db->where('user_id',$user_id);
											$CI->db->where('specialty_id',$specialty_id);
											$CI->db->update('user_article_preference',$data_update);
											update_user_article_order_master($user_id,$specialty_id,$article_array);
										}
								}


						}
				}

		}
/**
 * Function to update user article in order master table.
	@param  user_id type int, specialty_id type int, $article_array type array
	this function update all the article records in order master according to left quantity
 */
function update_user_article_order_master($user_id,$specialty_id,$article_array)
	{
		$CI =& get_instance();
		$article_id_list='';
		$sql_order_master="select subs_item_id,user_id,article_ids,article_quantity_allowed,article_quantity_taken,article_quantity_allowed-article_quantity_taken as leftamount from
		order_masters where user_id=$user_id and specialty_id=$specialty_id and article_quantity_allowed!=article_quantity_taken";
		$query_matser=$CI->db->query($sql_order_master);
		$result_master=$query_matser->result_array();	// Product Data	Array
		$count=count($article_array);

		if(is_array($result_master) && count($result_master)>0)
			{
				foreach($result_master as $key=>$val)
						{
							if($count<=$val['leftamount'])
								{
									$quantity_taken=$val['article_quantity_taken']+$count;
									if(!empty($val['article_ids']))
										{
											$article_id_list=$val['article_ids'].implode(',',$article_array).',';
										}
									else
										{
											$article_id_list=implode(',',$article_array).',';
										}

									$data=array('article_ids'=>$article_id_list,'article_quantity_taken'=>$quantity_taken);
									$CI->db->where('subs_item_id',$val['subs_item_id']);
									$CI->db->update('order_masters',$data);
									return true;
								}
							else
								{	$i=0;
									$article_str='';

									while($i<$val['leftamount'])
										{

											$article_str.=$article_array[$i].',';
											unset($article_array[$i]);
											$i++;
										}
									$quantity_taken=$val['article_quantity_taken']+$i;
									if(!empty($val['article_ids']))
										{
											$article_id_list=$val['article_ids'].$article_str;
										}
									else
										{
											$article_id_list=$article_str;
										}
									$data=array('article_ids'=>$article_id_list,'article_quantity_taken'=>$quantity_taken);
									$CI->db->where('subs_item_id',$val['subs_item_id']);
									$CI->db->update('order_masters',$data);
									return update_user_article_order_master($user_id,$specialty_id,$article_array);
								}

						}
			}
		else if($count>0 && count($result_master)==0)
			{
				$sql_order_master_new="select subs_item_id,user_id,article_ids,article_quantity_allowed,article_quantity_taken,article_quantity_allowed-article_quantity_taken as leftamount from
				order_masters where user_id=$user_id and specialty_id=0 and article_quantity_allowed!=article_quantity_taken";
				$query_matser_new=$CI->db->query($sql_order_master_new);
				$result_master_new=$query_matser_new->result_array();	// Product Data	Array
				$count=count($article_array);
				foreach($result_master_new as $key=>$val)
						{
							if($count<=$val['leftamount'])
								{
									$quantity_taken=$val['article_quantity_taken']+$count;
									if(!empty($val['article_ids']))
										{
											$article_id_list=$val['article_ids'].implode(',',$article_array).',';
										}
									else
										{
											$article_id_list=implode(',',$article_array).',';
										}

									$data=array('article_ids'=>$article_id_list,'article_quantity_taken'=>$quantity_taken);
									$CI->db->where('subs_item_id',$val['subs_item_id']);
									$CI->db->update('order_masters',$data);
									return true;
								}
							else
								{	$i=0;
									$article_str='';

									while($i<$val['leftamount'])
										{

											$article_str.=$article_array[$i].',';
											unset($article_array[$i]);
											$i++;
										}
									$quantity_taken=$val['article_quantity_taken']+$i;
									if(!empty($val['article_ids']))
										{
											$article_id_list=$val['article_ids'].$article_str;
										}
									else
										{
											$article_id_list=$article_str;
										}
									$data=array('article_ids'=>$article_id_list,'article_quantity_taken'=>$quantity_taken);
									$CI->db->where('subs_item_id',$val['subs_item_id']);
									$CI->db->update('order_masters',$data);
									return update_user_article_order_master($user_id,$specialty_id,$article_array);
								}

						}

			}

	}

/**
 * Add user into Forum
 * @return boolean
 */
	function add_forum_user($user_array)
	{
		$article_id_array=array();
		if(is_array($user_array) && count($user_array)>0)
		{
			if(!empty($user_array['user_id']) && !empty($user_array['user_name']) && !empty($user_array['email']) && !empty($user_array['password']) && !empty($user_array['password_orig']))
			{
				$CI =& get_instance();
				$DB1 = $CI->load->database('forum', TRUE);
				$sql="INSERT INTO `mybb_users` (`uid`, `username`, `password`,`oldpassword`, `salt`, `loginkey`, `email`, `postnum`, `avatar`, `avatardimensions`, `avatartype`, `usergroup`, `additionalgroups`, `displaygroup`, `usertitle`, `regdate`, `lastactive`, `lastvisit`, `lastpost`, `website`, `icq`, `aim`, `yahoo`, `msn`, `birthday`, `birthdayprivacy`, `signature`, `allownotices`, `hideemail`, `subscriptionmethod`, `invisible`, `receivepms`, `receivefrombuddy`, `pmnotice`, `pmnotify`, `threadmode`, `showsigs`, `showavatars`, `showquickreply`, `showredirect`, `ppp`, `tpp`, `daysprune`, `dateformat`, `timeformat`, `timezone`, `dst`, `dstcorrection`, `buddylist`, `ignorelist`, `style`, `away`, `awaydate`, `returndate`, `awayreason`, `pmfolders`, `notepad`, `referrer`, `referrals`, `reputation`, `regip`, `lastip`, `longregip`, `longlastip`, `language`, `timeonline`, `showcodebuttons`, `totalpms`, `unreadpms`, `warningpoints`, `moderateposts`, `moderationtime`, `suspendposting`, `suspensiontime`, `suspendsignature`, `suspendsigtime`, `coppauser`, `classicpostbit`, `loginattempts`, `failedlogin`, `usernotes`) VALUES
				($user_array[user_id], '$user_array[user_name]', '$user_array[password]','$user_array[password_orig]', 'UOd5ZkfP','zA6fL5kdgrW6vakiZKmP7uCOL2pHoaXMbYiIgofK5JQtxQaFz3', '$user_array[email]', 0, '', '', '', 2, '', 0, '', 1333640978, 1333640997, 1333640978, 0, '', '0', '', '', '', '', 'all', '', 1, 0, 0, 0, 1, 0, 1, 0, 'linear', 1, 1, 1, 1, 0, 0, 0, '', '', '10', 0, 2, '', '', 0, 0, 0, '0', '', '', '', 0, 0, 0, '0', '', 0, 0, '', 19, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, '')";
				$query=$DB1->query($sql);
				if($query)
				{
					$sql_update="UPDATE mybb_users t1
									INNER JOIN mybb_users t2 ON t1.uid=t2.uid
									SET t1.password = md5(CONCAT(md5(t2.salt), md5(t2.oldpassword)))
									WHERE t1.uid = $user_array[user_id] ";
					$query_update=$DB1->query($sql_update);
					if($query_update)
					{
						return true;
					}
					else
					{
						return false;
					}
				}
				else
				{
					return false;
				}
			}
		}
		return false;
	}
	function update_forum_user($user_array)
	{
		$article_id_array=array();
		if(is_array($user_array) && count($user_array)>0)
		{
			if(!empty($user_array['user_id']) && !empty($user_array['user_name']) && !empty($user_array['email']) && !empty($user_array['password']) && !empty($user_array['password_orig']))
			{
				$CI =& get_instance();
				$DB1 = $CI->load->database('forum', TRUE);
				$sql="update mybb_users set username='$user_array[user_name]',password='$user_array[password]',oldpassword='$user_array[password_orig]' where uid=$user_array[user_id]";
				$query=$DB1->query($sql);
				if($query)
				{
					$sql_update="UPDATE mybb_users t1
									INNER JOIN mybb_users t2 ON t1.uid=t2.uid
									SET t1.password = md5(CONCAT(md5(t2.salt), md5(t2.oldpassword)))
									WHERE t1.uid = $user_array[user_id] ";
					$query_update=$DB1->query($sql_update);
					if($query_update)
					{
						return true;
					}
					else
					{
						return false;
					}
				}
				else
				{
					return false;
				}
			}
		}
		return false;
	}	
/**
 * Get article permission array for a user
 * @param int $user_id
 * @return array
 */
function get_user_article_access_data($user_id,$type='')
		{
			$article_id_array=array();
			if(!empty($user_id))
				{
							$CI =& get_instance();
							if(!empty($type) && $type=='archive')
								{
									$sql="select * from user_article_access_archive where user_id='$user_id' limit 1";
								}
							else
								{
									$sql="select * from user_article_access where user_id='$user_id' limit 1";
								}
							$query=$CI->db->query($sql);
							$result=$query->result_array();	// Product Data	Array
							if(is_array($result) && !empty($result[0]['user_access_id']))
								{
										$article_data=rtrim($result[0]['article_ids'],',');
										$article_id_array=explode(',',$article_data);

								}

				}
			return $article_id_array;
		}

function has_specialty_access() {
			$CI =& get_instance();
			$specialty_array=$CI->config->item('master_article_slug_array');
			$user_data = get_current_user_data();
				if(empty($user_data)) { return FALSE; }
				$user_data['role_perms'] = array_filter($user_data['role_perms']);
				//echo "<pre>"; print_r(array_filter($user_data['role_perms']));exit;
				foreach($user_data['role_perms'] as $user_role_perms) {
					$user_role_master_products[] = $user_role_perms->master_product_ids;
				}
				$master_products = array();
				if(!empty($user_role_master_products)) {
					foreach($user_role_master_products as $user_role_master_product) {
						$master_products = array_merge($master_products, explode(',',$user_role_master_product));
					}

				} else {
					return FALSE;
				}
				foreach($master_products as $master_id)
					{
						if(in_array($master_id,$specialty_array))
							{
								return true;
							}
					}

				return false;
}
function has_coder_access($check=''){
			$CI =& get_instance();
			//$coder_array=$CI->config->item('all_coders');
			if(empty($check))
				{
					$coder_array=$CI->config->item('all_coders');
				}
			else
				{
					$coder_array=$CI->config->item('all_coders_new');
				}
			$user_data = get_current_user_data();
				if(empty($user_data)) { return FALSE; }
				$user_data['role_perms'] = array_filter($user_data['role_perms']);
				//echo "<pre>"; print_r(array_filter($user_data['role_perms']));exit;
				foreach($user_data['role_perms'] as $user_role_perms) {
					$user_role_products[] = $user_role_perms->product_ids;
				}
				$products = array();
				if(!empty($user_role_products)) {
					foreach($user_role_products as $user_role_product) {
						$products = array_merge($products, explode(',',$user_role_product));
					}

				} else {
					return FALSE;
				}

				foreach($products as $product_id)
					{
						if(array_key_exists($product_id,$coder_array))
							{
								return true;
							}
					}

				return false;
}
function has_old_coder_access(){
			$CI =& get_instance();
			$coder_array=$CI->config->item('all_coders_old');
			$user_data = get_current_user_data();
				if(empty($user_data)) { return FALSE; }
				$user_data['role_perms'] = array_filter($user_data['role_perms']);
				//echo "<pre>"; print_r(array_filter($user_data['role_perms']));exit;
				foreach($user_data['role_perms'] as $user_role_perms) {
					$user_role_products[] = $user_role_perms->product_ids;
				}
				$products = array();
				if(!empty($user_role_products)) {
					foreach($user_role_products as $user_role_product) {
						$products = array_merge($products, explode(',',$user_role_product));
					}

				} else {
					return FALSE;
				}
				foreach($products as $product_id)
					{
						if(!empty($coder_array) && in_array($product_id,$coder_array))
							{
								return true;
							}
					}

				return false;
}

/*
Function to get User Specialty ID's for article pucrhased by user from Single article or Pack of Article
*/
function get_user_article_specialty_data($user_id)
	{
		$specialty_id_array=array();
		if(!empty($user_id))
			{
				$CI =& get_instance();
				$sql="select * from user_article_access where user_id='$user_id' limit 1";
				$query=$CI->db->query($sql);
				$result=$query->result_array();	// Product Data	Array
				if(is_array($result) && !empty($result[0]['user_access_id']))
					{
						$speicalty_data=rtrim($result[0]['specialty_ids'],',');
						$specialty_id_array=explode(',',$speicalty_data);
					}
				}
			return $specialty_id_array;
	}

function has_access($value='',$filename='', $permission_type='master_product') {
	switch($permission_type) {
		case 'master_product':
			$user_data = get_current_user_data();
			if(empty($user_data)) { return FALSE; }
			$user_data['role_perms'] = array_filter($user_data['role_perms']);
			//echo "<pre>"; print_r(array_filter($user_data['role_perms']));exit;
			foreach($user_data['role_perms'] as $user_role_perms) {
				$user_role_master_products[] = $user_role_perms->master_product_ids;
			}
			//$user_role_master_products = toArray(array_filter($user_data['role_perms']),'role_id','master_product_ids');
			//echo "<pre>"; print_r(array_filter($user_role_master_products));exit;
			$master_products = array();
			if(!empty($user_role_master_products)) {
				foreach($user_role_master_products as $user_role_master_product) {
					$master_products = array_merge($master_products, explode(',',$user_role_master_product));
				}
				if(in_array($value,$master_products)) {
					return TRUE;
				} else {
					return FALSE;
				}
			} else {
				return FALSE;
			}
			//echo "<pre>"; print_r($user_master_products);exit('i m here');
		break;
		case 'product':
			$user_data = get_current_user_data();

			if(empty($user_data)) { return FALSE; }
			foreach($user_data['role_perms'] as $user_role_perms) {
				$user_role_products[] = $user_role_perms->product_ids;
			}
			$products = array();
			if(!empty($user_role_products)) {
				foreach($user_role_products as $user_role_product) {
					$products = array_merge($products, explode(',',$user_role_product));
				}
				if(in_array($value,$products)) {
					return TRUE;
				} else {
					return FALSE;
				}
			} else {
				return FALSE;
			}

		break;

	}
	return FALSE;
}
function has_group_access($value='') {
	$user_data = get_current_user_data();
	if(empty($user_data)) { return FALSE; }

	if(isset($user_data['adminuser_group_id']) && $user_data['adminuser_group_id']!='' && $user_data['adminuser_group_id']==$value)
	{
		return TRUE;
	} else {
		return FALSE;
	}
}


function check_forum_flag($user_id)
{
	$CI =& get_instance();
	$show_flag = 0;
	$query=$CI->db->query("SELECT * FROM user_questions_subscription WHERE user_id = $user_id");
	$result=$query->result_array();
	if(is_array($result) && !empty($result[0]))
	{
		foreach($result as $res)
		{
			//print_r($res);
			if( ($res['monthly_asked_ques'] < $res['ques_monthly_limit']) && (date('Y-m-d H:i:s') >= $res['month_start_date'] ) && (date('Y-m-d H:i:s') <= $res['month_end_date']) && ($res['is_expired']== 0) )
			{
            $show_flag = 1;
            break;
            }
		}
	}
	return $show_flag;
    /*$result = mysql_query( "SELECT * FROM user_questions_subscription WHERE user_id = $user_id" ) or die($CONF['debug']?("ERROR: mysql query failed: ".mysql_error()):"ERROR: Please try later");
    $show_flag = 0;
    while( $subscription = mysql_fetch_assoc($result))
        {
        if( ($subscription['monthly_asked_ques'] < $subscription['ques_monthly_limit']) && (date('Y-m-d H:i:s') >= $subscription['month_start_date'] ) && (date('Y-m-d H:i:s') <= $subscription['month_end_date']) && ($subscription['is_expired']== 0) )
            {
            $show_flag = 1;
            break;
            }
        }
        return $show_flag;*/
}


function subs_monthly_remaining_ques_count($user_id)
{
	$CI =& get_instance();
	$remaining_ques_count = 0;

	if( isset($user_id) && (check_forum_flag($user_id) == 1) )
	{
		$query=$CI->db->query("SELECT user_id, SUM(ques_monthly_limit - monthly_asked_ques) AS remaining_ques_count
									FROM user_questions_subscription
									WHERE user_id =  $user_id
										AND is_expired = 0");
		$result=$query->result_array();
		if(is_array($result) && !empty($result[0]) && $result[0]['remaining_ques_count'])
		{
			$remaining_ques_count = $result[0]['remaining_ques_count'];
		}
	}

	//echo "Remaining Question Count: ".$remaining_ques_count; die;
	return $remaining_ques_count;
}


function has_subscription_entry($user_id)
{
	$CI =& get_instance();

	$has_subs_entry = 0;

	if( isset($user_id) )
	{
		$query = $CI->db->query("SELECT * FROM user_questions_subscription WHERE user_id = $user_id AND is_expired = 0");
		$result = $query->result_array();
		if(is_array($result) && !empty($result[0]))
		{
			$has_subs_entry = 1;
		}
	}
	return $has_subs_entry;
}


function check_user_access($product_id)
	{
		if(is_array($product_id) && count($product_id)>0)
			{
				foreach($product_id as $pid)
					{
						if(has_access($pid,'index','product'))
							{
								return true;
							}
					}
			}
		return false;
	}

function check_count_specialty($coder='')
		{
		$arr_specialty = array(29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,54,62,63);
		$arr_coder = array(29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,62,63);


			$arr_newsletters_url = array(
					"29"=>"coding-newsletters/my-anesthesia-coding-alert",
					"30"=>"coding-newsletters/my-cardiology-coding-alert",
					"31"=>"coding-newsletters/my-dermatology-coding-alert",
					"32"=>"coding-newsletters/my-emergency-medicine-coding-alert",
					"33"=>"coding-newsletters/my-family-practice-coding-alert",
					"34"=>"coding-newsletters/my-gastroenterology-coding-alert",
					"35"=>"coding-newsletters/my-general-surgery-coding-alert",
					"36"=>"coding-newsletters/my-internal-medicine-coding-alert",
					"37"=>"coding-newsletters/my-practice-management-alert",
					"38"=>"coding-newsletters/my-neurosurgery-coding-alert",
					"39"=>"coding-newsletters/my-oncology-hematology-coding-alert",
					"40"=>"coding-newsletters/my-ophthalmology-coding-alert",
					"41"=>"coding-newsletters/my-optometry-coding-alert",
					"42"=>"coding-newsletters/my-orthopedic-coding-alert",
					"43"=>"coding-newsletters/my-otolaryngology-coding-alert",
					"44"=>"coding-newsletters/my-part-b-coding-alert",
					"45"=>"coding-newsletters/my-pathology-lab-coding-alert",
					"46"=>"coding-newsletters/my-pediatric-coding-alert",
					"47"=>"coding-newsletters/my-physical-medicine-rehab-coding-alert",
					"48"=>"coding-newsletters/my-podiatry-coding-alert",
					"49"=>"coding-newsletters/my-pulmonology-coding-alert",
					"50"=>"coding-newsletters/my-radiology-coding-alert",
					"51"=>"coding-newsletters/my-urology-coding-alert",
					"54"=>"coding-newsletters/my-icd-10-coding-alert",
					"62"=>"coding-newsletters/my-neurology-coding-alert",
					"63"=>"coding-newsletters/my-ob-gyn-coding-alert"
					
					);

				$arr_coder_url = array(
					"29"=>"coding-solutions/my-anesthesia-coder",
					"30"=>"coding-solutions/my-cardiology-coder",
					"31"=>"coding-solutions/my-dermatology-coder",
					"32"=>"coding-solutions/my-emergency-medicine-coder",
					"33"=>"coding-solutions/my-family-practice-coder",
					"34"=>"coding-solutions/my-gastroenterology-coder",
					"35"=>"coding-solutions/my-general-surgery-coder",
					"36"=>"coding-solutions/my-internal-medicine-coder",
					"37"=>"coding-solutions/my-medical-office-biller-coder",
					"38"=>"coding-solutions/my-neurosurgery-coder",
					"39"=>"coding-solutions/my-oncology-coder",
					"40"=>"coding-solutions/my-ophthalmology-coder",
					"41"=>"coding-solutions/my-optometry-coder",
					"42"=>"coding-solutions/my-orthopedic-coder",
					"43"=>"coding-solutions/my-otolaryngology-coder",
					"44"=>"coding-solutions/my-part-b-coder",
					"45"=>"coding-solutions/my-pathology-coder",
					"46"=>"coding-solutions/my-pediatric-coder",
					"47"=>"coding-solutions/my-physical-medicine-coder",
					"48"=>"coding-solutions/my-podiatry-coder",
					"49"=>"coding-solutions/my-pulmonology-coder",
					"50"=>"coding-solutions/my-radiology-coder",
					"51"=>"coding-solutions/my-urology-coder",
					"52"=>"coding-solutions/my-drg-coder",
					"53"=>"coding-solutions/my-outpatient-facility-coder",
					"54"=>"coding-solutions/my-icd-10-coder",
					"62"=>"coding-solutions/my-neurology-coder",
					"63"=>"coding-solutions/my-ob-gyn-coder");
					$all_coders_url_new = array("36"=>"coding-solutions/my-outpatient-facility-coder",
						"54"=>"coding-solutions/my-anesthesia-coder",
						"53"=>"coding-solutions/my-anesthesia-coder",
						"56"=>"coding-solutions/my-physician-coder",
						"58"=>"coding-solutions/my-drg-coder",
						"158"=>"coding-solutions/my-icd-10-coder",
						"119"=>"coding-solutions/my-ob-gyn-coder",
						"121"=>"coding-solutions/my-cardiology-coder",
						"123"=>"coding-solutions/my-ophthalmology-coder",
						"125"=>"coding-solutions/my-general-surgery-coder",
						"127"=>"coding-solutions/my-pediatric-coder",
						"128"=>"coding-solutions/my-orthopedic-coder",
						"130"=>"coding-solutions/my-neurology-coder",
						"132"=>"coding-solutions/my-oncology-coder",
						"134"=>"coding-solutions/my-otolaryngology-coder",
						"136"=>"coding-solutions/my-urology-coder",
						"160"=>"coding-solutions/my-family-practice-coder",
						"162"=>"coding-solutions/my-podiatry-coder",
						"164"=>"coding-solutions/my-neurosurgery-coder",
						"166"=>"coding-solutions/my-radiology-coder",
						"168"=>"coding-solutions/my-pulmonology-coder",
						"170"=>"coding-solutions/my-gastroenterology-coder",
						"173"=>"coding-solutions/my-dermatology-coder",
						"175"=>"coding-solutions/my-emergency-medicine-coder",
						"177"=>"coding-solutions/my-internal-medicine-coder",
						"179"=>"coding-solutions/my-pathology-coder",
						"181"=>"coding-solutions/my-part-b-coder",						
						"183"=>"coding-solutions/my-optometry-coder",
						"185"=>"coding-solutions/my-medical-office-biller",
						"187"=>"coding-solutions/my-physical-medicine-coder");


				$count=0;

				$url='';
				if(!empty($coder) && $coder=='coder')
					{
						foreach($arr_coder as $val)
							{
								if($count==2)
									{
										return false;
									}
								if(has_access($val))
									{
										$count++;
										$url=$arr_coder_url[$val];
									}
							}
						return $url;
					}
				else if(!empty($coder) && $coder=='codernew')
					{
						
						foreach($all_coders_url_new as $key=>$val)
							{								
							
								if($count==2)
									{									
										return false;
									}
								if(has_access($key,'index','product'))
									{
										
										
										$count++;
										$url=$val;
										
									}
							}
							
						return $url;
					}
				else
					{
						foreach($arr_specialty as $val)
							{
								if($count==2)
									{
										return false;
									}
								if(has_access($val))
									{
										$count++;
										$url=$arr_newsletters_url[$val];
									}
							}
						return $url;
					}
				return false;

		}
 
 function get_user_home($user_id)
	{
		$code_search_array=array(22,23,31,33,60,71,62,63,255,251);
		$hcpc_coding_clinic=array(46,48,66,192);
		$icd_coding_clinic=array(45,47,67,191);
		$batch_scrubber=array(34,);
		$icd_coding_clinic=array(45,47,67,191);
		$icd_coding_clinic=array(45,47,67,191);
		$ask_an_expert=array(257,258,259,260,652);
		$url_array=array('1'=>'code-lookup','2'=>'cci-centric','3'=>'coding-newsletters/articles','4'=>'coding-solutions/my-drg-coder','5'=>'coding-solutions/my-outpatient-facility-coder',
		'6'=>'coding-references/codingclinic-hcpcs/','7'=>'coding-references/codingclinic-icd/','8'=>'scrubber/batch-processing/','9'=>'scrubber/cms1500',
		'10'=>'scrubber/ub04/','11'=>'icd-10/my-icd-10-bridge','12'=>'code-lookup','13'=>'coding-references/my-code-connect','14'=>'my-ask-an-expert/?new=1',
		'15'=>'stedman','16'=>'my-profile/expert-advice','17'=>'my-profile/subscription','18'=>'coding-solutions/my-coders','19'=>'coding-newsletters/articles','20'=>'coding-solutions/my-compliance-edge',
		'21'=>'exclusives/ceus','22'=>'my-profile/','23'=>'code-lookup','160'=>'coding-solutions/my-anesthesia-coder','179'=>'coding-solutions/my-medical-office-biller-coder','45012'=>'coding-solutions/my-cardiology-coder',
		'460014'=>'coding-solutions/my-dermatology-coder','175'=>'coding-solutions/my-emergency-medicine-coder',
		'176'=>'coding-solutions/my-family-practice-coder','177'=>'coding-solutions/my-gastroenterology-coder','178'=>'coding-solutions/my-general-surgery-coder',
		'464510'=>'coding-solutions/my-icd-10-coder','45011'=>'coding-solutions/my-internal-medicine-coder','181'=>'coding-solutions/my-neurology-coder',
		'182'=>'coding-solutions/my-neurosurgery-coder','183'=>'coding-solutions/my-ob-gyn-coder','184'=>'coding-solutions/my-oncology-coder',
		'185'=>'coding-solutions/my-ophthalmology-coder','186'=>'coding-solutions/my-optometry-coder','187'=>'coding-solutions/my-orthopedic-coder',
		'188'=>'coding-solutions/my-otolaryngology-coder','190'=>'coding-solutions/my-part-b-coder','189'=>'coding-solutions/my-pathology-coder',
		'191'=>'coding-solutions/my-pediatric-coder','192'=>'coding-solutions/my-physical-medicine-coder','460013'=>'coding-solutions/my-podiatry-coder',
		'193'=>'coding-solutions/my-pulmonology-coder','194'=>'coding-solutions/my-radiology-coder','195'=>'coding-solutions/my-urology-coder');
		$arr_newsletters_landing = array(
					"aca"=>"coding-newsletters/anesthesia-coding-alert",
					"cca"=>"coding-newsletters/cardiology-coding-alert",
					"der"=>"coding-newsletters/dermatology-coding-alert",
					"eca"=>"coding-newsletters/emergency-medicine-coding-alert",
					"fca"=>"coding-newsletters/family-practice-coding-alert",
					"gac"=>"coding-newsletters/gastroenterology-coding-alert",
					"gca"=>"coding-newsletters/general-surgery-coding-alert",
					"ica"=>"coding-newsletters/internal-medicine-coding-alert",
					"mob"=>"coding-newsletters/practice-management-alert",
					"nec"=>"coding-newsletters/neurosurgery-coding-alert",
					"onc"=>"coding-newsletters/oncology-hematology-coding-alert",
					"opc"=>"coding-newsletters/ophthalmology-coding-alert",
					"opt"=>"coding-newsletters/optometry-coding-alert",
					"orc"=>"coding-newsletters/orthopedic-coding-alert",
					"otc"=>"coding-newsletters/otolaryngology-coding-alert",
					"pbi"=>"coding-newsletters/part-b-coding-alert",
					"pac"=>"coding-newsletters/pathology-lab-coding-alert",
					"pca"=>"coding-newsletters/pediatric-coding-alert",
					"pmc"=>"coding-newsletters/physical-medicine-rehab-coding-alert",
					"pod"=>"coding-newsletters/podiatry-coding-alert",
					"puc"=>"coding-newsletters/pulmonology-coding-alert",
					"rca"=>"coding-newsletters/radiology-coding-alert",
					"uca"=>"coding-newsletters/urology-coding-alert",
					"drg"=>"coding-newsletters/inpatient-facility-coding-alert",
					"ofc"=>"coding-newsletters/outpatient-facility-coding-alert",
					"ict"=>"coding-newsletters/icd-10-coding-alert",
					"nca"=>"coding-newsletters/neurology-coding-alert",
					"oca"=>"coding-newsletters/ob-gyn-coding-alert",
					"hcw"=>"coding-newsletters/homecare-week-alert",
					"hop"=>"coding-newsletters/hospice-insider-alert",
					"mlr"=>"medicare-compliance-reimbursement-alert",
					"hica"=>"coding-newsletters/health-information-compliance-alert",
					"lsa"=>"coding-newsletters/long-term-care-survey-alert",
					"osa"=>"coding-newsletters/oasis-alert",
					"mds"=>"coding-newsletters/mds-alert",
					"icd"=>"coding-newsletters/homehealth-icd-9-alert",
					"enm"=>"coding-newsletters/evaluation-management-coding-alert");
					
	
		if(has_access('653','index','product')) {
			update_user_home_option($user_id,20);
			return 'coding-solutions/my-compliance-edge';
		}
		elseif(has_coder_access(1)) // ALL Coders
		{	
				if(has_old_coder_access() || has_access(152,'index','product') || has_access(55,'index','product')
				|| has_access(56,'index','product') || has_access(35,'index','product') || has_access(78,'index','product') || has_access(4,'index','product')
				|| has_access(5,'index','product') || has_access(26,'index','product') || has_access(14,'index','product') || has_access(21,'index','product')
				|| has_access(17,'index','product') || has_access(11,'index','product') || has_access(15,'index','product') || has_access(6,'index','product')
				|| has_access(7,'index','product') || has_access(10,'index','product') || has_access(20,'index','product') || has_access(19,'index','product') || has_access(44,'index','product')) // ALL Coders
				{
						$one_coder_url=check_count_specialty('codernew');
						
					}
				else
					{
						$one_coder_url=check_count_specialty('coder');
					}
		
			
			if(!empty($one_coder_url))
				{
					if(in_array($one_coder_url,$url_array))
						{
							$set_user_option=array_search($one_coder_url,$url_array);
							if(!empty($set_user_option))
								{
									update_user_home_option($user_id,$set_user_option);
								}
						}
					
					return $one_coder_url;
				}
			else
				{
					update_user_home_option($user_id,18);
					return 'coding-solutions/my-coders';
				}
		}
	else if(has_old_coder_access() || has_access(152,'index','product') || has_access(55,'index','product')
		|| has_access(56,'index','product') || has_access(35,'index','product') || has_access(78,'index','product') || has_access(4,'index','product')
		|| has_access(5,'index','product') || has_access(26,'index','product') || has_access(14,'index','product') || has_access(21,'index','product')
		|| has_access(17,'index','product') || has_access(11,'index','product') || has_access(15,'index','product') || has_access(6,'index','product')
		|| has_access(7,'index','product') || has_access(10,'index','product') || has_access(20,'index','product') || has_access(19,'index','product') || has_access(44,'index','product')) // ALL Coders
		{
			$one_coder_url=check_count_specialty('coder');
			if(!empty($one_coder_url))
				{
					if(in_array($one_coder_url,$url_array))
						{
							$set_user_option=array_search($one_coder_url,$url_array);
							if(!empty($set_user_option))
								{
									update_user_home_option($user_id,$set_user_option);
								}
						}
					
				return $one_coder_url;
				}
			else
				{
					update_user_home_option($user_id,18);
					return 'coding-solutions/my-coders';
				}
		}
	else if(has_access('405','index','product'))
		{
				update_user_home_option($user_id,5);
				return 'coding-solutions/my-outpatient-facility-coder';
		}	
	else if(has_access(700,'index','product') || has_access(701,'index','product') || has_access(702,'index','product') || has_access(703,'index','product') || has_access(704,'index','product') || has_access(705,'index','product') || has_access(706,'index','product') || has_access(707,'index','product') || has_access(708,'index','product') || has_access(709,'index','product') || has_access(710,'index','product'))
			{
				update_user_home_option($user_id,1);
				return 'code-lookup';
			}
	else if(has_access(931,'index','product')) // FAST Coder
		{
			update_user_home_option($user_id,23);
			return 'code-lookup';
		}			
	else if(has_access(297,'index','product') || has_specialty_access() || has_access(12,'index','product') || has_access(23,'index','product')|| has_access(92,'index','product') || has_access(24,'index','product') || has_access(64,'index','product') || has_access(30,'index','product')) // ALL Alerts
		{
			$one_spec_url=check_count_specialty();
			if(!empty($one_spec_url))
				{
					if(in_array($one_spec_url,$arr_newsletters_landing))
						{
							$set_user_option=array_search($one_spec_url,$arr_newsletters_landing);
							if(!empty($set_user_option))
								{
									update_user_home_option($user_id,$set_user_option);
								}
						}
					return $one_spec_url;
				}
			else
				{
					update_user_home_option($user_id,19);
					return 'coding-newsletters/articles';
				}

		}
		else if(check_user_access($hcpc_coding_clinic)) // HCPCS Coding Clinic
		{
			
			update_user_home_option($user_id,6);
			return 'coding-references/codingclinic-hcpcs/';
		}
		else if(check_user_access($icd_coding_clinic)) // ICD9 Coding Clinic
		{
			update_user_home_option($user_id,7);
			return 'coding-references/codingclinic-icd/';
		}
		else if(has_access(34,'index','product') || has_access(68,'index','product')) // Batch Scrubber
		{
			update_user_home_option($user_id,8);
			return 'scrubber/batch-processing/';
		}
		else if(has_access(32,'index','product') || has_access(69,'index','product')) // CMS1500
		{
			update_user_home_option($user_id,9);
			return 'scrubber/cms1500';
		}
		else if(has_access(40,'index','product') || has_access(70,'index','product')) // UB04
		{
			update_user_home_option($user_id,10);
			return 'scrubber/ub04/';
		}
		else if(has_access(172,'index','product')) // ICD 10 Bridge
		{
			update_user_home_option($user_id,11);
			return 'icd-10/my-icd-10-bridge';
		}
		else if(has_access(254,'index','product')) // FAST Coder
		{
				update_user_home_option($user_id,12);
			return 'code-lookup';
		}
		else if(has_access(25,'index','product') || has_access(59,'index','product') || has_access(293,'index','product')) // Code Connect
		{
			update_user_home_option($user_id,13);
			return 'coding-references/my-code-connect';
		}
		else if(has_access('292','index','product') || has_access('633','index','product'))
		{
				update_user_home_option($user_id,2);
				return 'cci-centric';
		}
		else if(check_user_access($ask_an_expert)) // Ask and Expert
		{
			update_user_home_option($user_id,14);
			return 'my-ask-an-expert/?new=1';
			//return 'my-ask-an-expert/';
		}
		else if(has_access(28,'index','product')) // Stedman
		{
			update_user_home_option($user_id,15);
			return 'stedman';
		}
		else if(check_user_access($code_search_array))  // Code Search
		{
				update_user_home_option($user_id,1);			
				return 'code-lookup';
		}
		else if(has_access(256,'index','product')) // SuperCoding on Demand
		{
			update_user_home_option($user_id,16);
			return 'my-profile/expert-advice';
		}
		else if(has_access(58))
		{
			update_user_home_option($user_id,21);
			return 'exclusives/ceus';
		}
		else
		{
			$CI =& get_instance();
			$current_user = $CI->get_current_user();
			if($current_user) {
				$article_array=get_user_article_access_data($current_user['user_id']);
				$link_array=array();
				if(is_array($article_array) && count($article_array)>0 && !empty($article_array[0]))
					{
						update_user_home_option($user_id,17);
						return 'my-profile/subscription';
						
					}
				$article_array_archive=get_user_article_access_data($current_user['user_id'],'archive');			
				if(is_array($article_array_archive) && count($article_array_archive)>0 && !empty($article_array_archive[0]))
					{
							update_user_home_option($user_id,17);
						return 'my-profile/subscription';
						
					}
					
				return FALSE;
			} else {
				return FALSE;
			}
			
			return false;
		}

	}
function get_home_url_by_id($urloption)
	{
		$url_array=array('1'=>'code-lookup','2'=>'cci-centric','3'=>'coding-newsletters/articles','4'=>'coding-solutions/my-drg-coder','5'=>'coding-solutions/my-outpatient-facility-coder',
		'6'=>'coding-references/codingclinic-hcpcs/','7'=>'coding-references/codingclinic-icd/','8'=>'scrubber/batch-processing/','9'=>'scrubber/cms1500',
		'10'=>'scrubber/ub04/','11'=>'icd-10/my-icd-10-bridge','12'=>'code-lookup','13'=>'coding-references/my-code-connect','14'=>'my-ask-an-expert/?new=1',
		'15'=>'stedman','16'=>'my-profile/expert-advice','17'=>'my-profile/subscription','18'=>'coding-solutions/my-coders','19'=>'coding-newsletters/articles','20'=>'coding-solutions/my-compliance-edge',
		'21'=>'exclusives/ceus','22'=>'my-profile/','23'=>'code-lookup','160'=>'coding-solutions/my-anesthesia-coder','179'=>'coding-solutions/my-medical-office-biller-coder','45012'=>'coding-solutions/my-cardiology-coder',
		'460014'=>'coding-solutions/my-dermatology-coder','175'=>'coding-solutions/my-emergency-medicine-coder',
		'176'=>'coding-solutions/my-family-practice-coder','177'=>'coding-solutions/my-gastroenterology-coder','178'=>'coding-solutions/my-general-surgery-coder',
		'464510'=>'coding-solutions/my-icd-10-coder','45011'=>'coding-solutions/my-internal-medicine-coder','181'=>'coding-solutions/my-neurology-coder',
		'182'=>'coding-solutions/my-neurosurgery-coder','183'=>'coding-solutions/my-ob-gyn-coder','184'=>'coding-solutions/my-oncology-coder',
		'185'=>'coding-solutions/my-ophthalmology-coder','186'=>'coding-solutions/my-optometry-coder','187'=>'coding-solutions/my-orthopedic-coder',
		'188'=>'coding-solutions/my-otolaryngology-coder','190'=>'coding-solutions/my-part-b-coder','189'=>'coding-solutions/my-pathology-coder',
		'191'=>'coding-solutions/my-pediatric-coder','192'=>'coding-solutions/my-physical-medicine-coder','460013'=>'coding-solutions/my-podiatry-coder',
		'193'=>'coding-solutions/my-pulmonology-coder','194'=>'coding-solutions/my-radiology-coder','195'=>'coding-solutions/my-urology-coder');
		$arr_newsletters_landing = array(
					"aca"=>"anesthesia-coding-alert",
					"cca"=>"cardiology-coding-alert",
					"der"=>"dermatology-coding-alert",
					"eca"=>"emergency-medicine-coding-alert",
					"fca"=>"family-practice-coding-alert",
					"gac"=>"gastroenterology-coding-alert",
					"gca"=>"general-surgery-coding-alert",
					"ica"=>"internal-medicine-coding-alert",
					"mob"=>"practice-management-alert",
					"nec"=>"neurosurgery-coding-alert",
					"onc"=>"oncology-hematology-coding-alert",
					"opc"=>"ophthalmology-coding-alert",
					"opt"=>"optometry-coding-alert",
					"orc"=>"orthopedic-coding-alert",
					"otc"=>"otolaryngology-coding-alert",
					"pbi"=>"part-b-coding-alert",
					"pac"=>"pathology-lab-coding-alert",
					"pca"=>"pediatric-coding-alert",
					"pmc"=>"physical-medicine-rehab-coding-alert",
					"pod"=>"podiatry-coding-alert",
					"puc"=>"pulmonology-coding-alert",
					"rca"=>"radiology-coding-alert",
					"uca"=>"urology-coding-alert",
					"drg"=>"inpatient-facility-coding-alert",
					"ofc"=>"outpatient-facility-coding-alert",
					"ict"=>"icd-10-coding-alert",
					"nca"=>"neurology-coding-alert",
					"oca"=>"ob-gyn-coding-alert",
					"hcw"=>"homecare-week-alert",
					"hop"=>"hospice-insider-alert",
					"mlr"=>"medicare-compliance-reimbursement-alert",
					"hica"=>"health-information-compliance-alert",
					"lsa"=>"long-term-care-survey-alert",
					"osa"=>"oasis-alert",
					"mds"=>"mds-alert",
					"icd"=>"homehealth-icd-9-alert",
					"enm"=>"evaluation-management-coding-alert");


		if(array_key_exists($urloption,$url_array))
			{
				return $url_array[$urloption];
			}
		else if(array_key_exists($urloption,$arr_newsletters_landing))
			{
				$url='coding-newsletters/my-'.$arr_newsletters_landing[$urloption];
				return $url;
			}
		else
			{
				return 'coding-tools/my-search/';
			}


	}
function update_user_home_option($user_id,$useroption)
		{
			$CI =& get_instance();
			$query=$CI->db->query("SELECT * FROM user_preference WHERE user_pref='customize_home_page' && user_id = $user_id");
			$result=$query->result_array();
			if(is_array($result) && isset($result[0]) && !empty($result[0]))
				{
					$sdata=array('user_pref_value'=>$useroption);
					$CI->db->where('user_id',$user_id);
					$CI->db->where('user_pref','customize_home_page');
					$CI->db->update('user_preference',$sdata);
				}
			else
				{
					$sdata=array('user_pref_value'=>$useroption,'user_id'=>$user_id,'user_pref'=>'customize_home_page');
					$CI->db->insert('user_preference',$sdata);
				}

		}

	function update_user_search_pref($user_id,$usersearchpref)
		{
			$CI =& get_instance();
			$query=$CI->db->query("SELECT * FROM user_search_preference WHERE user_id = $user_id");
			$result=$query->result_array();
			if(is_array($result) && isset($result[0]) && !empty($result[0]))
				{
					$sdata=array('user_search_pref'=>$usersearchpref);
					$CI->db->where('user_id',$user_id);
					$CI->db->update('user_search_preference',$sdata);
				}
			else
				{
					$sdata=array('user_search_pref'=>$usersearchpref,'user_id'=>$user_id);
					$CI->db->insert('user_search_preference',$sdata);
				}

		}
	
	function get_user_search_pref($user_id)
		{
			$CI =& get_instance();
			$query=$CI->db->query("SELECT user_search_pref FROM user_search_preference WHERE user_id = $user_id");
			$result=$query->result_array();
			if(is_array($result) && isset($result[0]) && !empty($result[0]))
				{
					return $result[0]['user_search_pref'];
				}
				else
				{
					return FALSE;
				}
		}
		
function update_user_perm($subs_item_id, $perm_type='add',$process_user_order_perms=TRUE) {
	$response = array();
	if(!empty($perm_type) && !empty($subs_item_id)) {
		$CI =& get_instance();
		switch($perm_type) {
			case 'sub':
				if($subs_item_id) {
					$subs_item_data = $CI->db->get_where('order_masters',array('subs_item_id'=> $subs_item_id))->result();
					//echo "<pre>"; print_r($subs_item_data);exit;
					if(!empty($subs_item_data)) {
						$subs_item_data = $subs_item_data[0];
						$CI->db->where('subs_item_id', $subs_item_id);
						$CI->db->update('order_masters',array('active'=>2, 'is_recurring'=>0));
						$user_perm_data = $CI->db->get_where('user_perms',array('user_id'=>$subs_item_data->user_id))->result();
						if(!empty($user_perm_data)) {
							$user_perm_data = $user_perm_data[0];
							if(isset($user_perm_data->master_product_ids) && !empty($user_perm_data->master_product_ids)) {
								$user_perm_master_product_ids = explode(',',$user_perm_data->master_product_ids);
							} else {
								$user_perm_master_product_ids = array();
							}
							//echo $subs_item_data->product_id; print_r($user_perm_data->product_ids);
							$user_exisintg_products = explode(',',$user_perm_data->product_ids);
							if(in_array($subs_item_data->product_id, $user_exisintg_products)) {
								//$user_perm_data->product_ids = str_replace($subs_item_data->product_id, '',$user_perm_data->product_ids);
									$user_exisintg_products_flip=array_flip($user_exisintg_products);
									unset($user_exisintg_products_flip[$subs_item_data->product_id]);
									$user_exisintg_products=array_flip($user_exisintg_products_flip);
									$user_perm_data->product_ids = implode(',',$user_exisintg_products);
								//echo $user_perm_data->product_ids;exit;
								$user_order_perms = array();
								if(!empty($user_perm_data->user_order_perms) && $process_user_order_perms) {
									$user_order_perms = unserialize($user_perm_data->user_order_perms);
									foreach($user_order_perms as $order_id=>$order_products) {
										if($order_id == $subs_item_data->order_id) {
											if(!empty($order_products)) {
												foreach($order_products as $product_id=>$master_product_ids) {
													if($product_id != $subs_item_data->product_id) {
														if($product_id == $subs_item_data->product_id) {
															unset($user_order_perms[$subs_item_data->order_id][$subs_item_data->product_id]);
														} else {
															$user_perm_data->product_ids .= ','. $product_id;
														}
													} else {
														unset($user_order_perms[$subs_item_data->order_id][$subs_item_data->product_id]);
													}
												}
											}
										} else {
											foreach($order_products as $product_id=>$master_product_ids) {
												$user_perm_data->product_ids .= ','. $product_id;
											}
										}
									}
								}
								$user_product_ids = array_unique(array_filter(explode(',', $user_perm_data->product_ids)));
								$CI->load->model('Products');
								$product_obj = new Products();
								$user_master_product_ids = array();
								$master_product_ids = $product_obj->list_products(FALSE,'product_id, master_product_ids','','','','','','','',$user_product_ids);
								if(!empty($master_product_ids)) {
									/*$master_product_ids = implode(',',toArray($master_product_ids,'product_id','master_product_ids'));
									$user_master_products = array_unique(array_filter(array_merge($user_master_product_ids, explode(',',$master_product_ids))));*/
									$master_product_ids = implode(',',toArray($master_product_ids,'product_id','master_product_ids'));
									//$user_perm_master_product_ids = array_diff($user_perm_master_product_ids, explode(',',$master_product_ids));
									$user_perm_master_product_ids = array_intersect($user_perm_master_product_ids,explode(',',$master_product_ids));
									$user_master_products = array_unique(array_filter(array_merge($user_perm_master_product_ids, $user_master_product_ids, explode(',',$master_product_ids))));
								}
								/** Added by Sunil Punyani **/
								
								if(!empty($subs_item_data->user_id))
									{
										$sqlmulti="select group_concat(specialty_id) as specialty from order_masters where product_type='multispecialty' and user_id=$subs_item_data->user_id and active=1 and end_date>=curdate()";
										$query=$CI->db->query($sqlmulti);
										if($query)
											{
												$resultmulti=$query->result_array();
											
												if(is_array($resultmulti) && count($resultmulti)>0 && isset($resultmulti[0]) && count($resultmulti[0])>0)
													{
														$specialty_id=$resultmulti[0]['specialty'];
														$user_master_products = array_unique(array_filter(array_merge($user_perm_master_product_ids, $user_master_product_ids, explode(',',$specialty_id))));
													}
											}
									}
								/* Ended */
								if(!empty($user_master_products) && !empty($user_product_ids)) {
									$CI->db->where('user_id',$subs_item_data->user_id);
									$CI->db->update('user_perms',array('product_ids'=> implode(',', $user_product_ids), 'master_product_ids' => implode(',',$user_master_products ), 'user_order_perms'=>serialize($user_order_perms)));
								} else {
									$CI->db->where('user_id',$subs_item_data->user_id);
									$CI->db->update('user_perms',array('product_ids'=> '', 'master_product_ids' => '', 'user_order_perms'=>serialize($user_order_perms)));
								}
							}
							$response['success'] = TRUE;
							$response['error'] = FALSE;
						}
					} else {
						$response['success'] = FALSE;
						$response['error'] = TRUE;
					}
				} else {
					$response['success'] = FALSE;
					$response['error'] = TRUE;
				}
			break;
			case 'add':
				if($subs_item_id) {
					$subs_item_data = $CI->db->get_where('order_masters',array('subs_item_id'=> $subs_item_id))->result();
					//echo "<pre>"; print_r($subs_item_data);exit;
					$CI->load->model('Products');
					$CI->load->model('User_perms');
					$user_perm_obj = new User_perms();
					$product_obj = new Products();
					if(!empty($subs_item_data)) {
						$subs_item_data = $subs_item_data[0];
						$user_perm_data = $CI->db->get_where('user_perms',array('user_id'=>$subs_item_data->user_id))->result();
						//echo "<pre>";print_r($user_perm_data);exit;
						if(!empty($user_perm_data)) {
							$user_perm_data = $user_perm_data[0];
							//echo $subs_item_data->product_id; print_r($user_perm_data->product_ids);
							if(isset($user_perm_data->master_product_ids) && !empty($user_perm_data->master_product_ids)) {
								$user_perm_master_product_ids = explode(',',$user_perm_data->master_product_ids);
							} else {
								$user_perm_master_product_ids = array();
							}
							$user_exisintg_products = explode(',',$user_perm_data->product_ids);
							$master_product_ids = $product_obj->list_products(FALSE,' master_product_ids',array('product_id'=>$subs_item_data->product_id));
							if(!empty($master_product_ids)) {
								$master_product_ids = $master_product_ids[0]->master_product_ids;
							}
							/* ------- Checked removed to ensure we didn't missed the product ---------------- */
							//if(!in_array($subs_item_data->product_id, $user_exisintg_products)) {
								$user_perm_data->product_ids = $user_perm_data->product_ids.','.$subs_item_data->product_id;
								//echo $user_perm_data->product_ids;exit;
								$user_order_perms = array();
								if(!empty($user_perm_data->user_order_perms) && $process_user_order_perms) {
									$user_order_perms = unserialize($user_perm_data->user_order_perms);
									foreach($user_order_perms as $order_id=>$order_products) {
										foreach($order_products as $product_id=>$master_product_ids) {
											$user_perm_data->product_ids .= ','. $product_id;
										}
									}
									$user_order_perms[$subs_item_data->order_id][$subs_item_data->product_id] = $master_product_ids;
								} else {
									$user_order_perms[$subs_item_data->order_id][$subs_item_data->product_id] = $master_product_ids;
								}
								$user_product_ids = array_unique(array_filter(explode(',', $user_perm_data->product_ids)));


								$user_master_product_ids = array();
								$master_product_ids = $product_obj->list_products(FALSE,'product_id, master_product_ids','','','','','','','',$user_product_ids);
								if(!empty($master_product_ids)) {
									/*$master_product_ids = implode(',',toArray($master_product_ids,'product_id','master_product_ids'));
									$user_master_products = array_unique(array_filter(array_merge($user_master_product_ids, explode(',',$master_product_ids))));*/
									$master_product_ids = implode(',',toArray($master_product_ids,'product_id','master_product_ids'));
									$user_perm_master_product_ids = array_diff($user_perm_master_product_ids, explode(',',$master_product_ids));
									$user_master_products = array_unique(array_filter(array_merge($user_perm_master_product_ids, $user_master_product_ids, explode(',',$master_product_ids))));
								}
								if(!empty($user_master_products) && !empty($user_product_ids)) {
									$CI->db->where('user_id',$subs_item_data->user_id);
									$CI->db->update('user_perms',array('product_ids'=> implode(',', $user_product_ids), 'master_product_ids' => implode(',',$user_master_products ), 'user_order_perms'=>serialize($user_order_perms)));
								} else {
									$CI->db->where('user_id',$subs_item_data->user_id);
									$CI->db->update('user_perms',array('product_ids'=> '', 'master_product_ids' => '', 'user_order_perms'=>serialize($user_order_perms)));
								}
							//}
							$response['success'] = TRUE;
							$response['error'] = FALSE;
						} else {
							// add data in user_perms
							$user_master_product_ids = array();
							$master_product_ids = $product_obj->list_products(FALSE,'master_product_ids',array('product_id'=>$subs_item_data->product_id));
							$user_master_products = array_unique(array_filter(array_merge($user_master_product_ids, explode(',',$master_product_ids[0]->master_product_ids))));
							$user_perm_obj->get_by_user_id($subs_item_data->user_id);
							$user_perm_obj->user_id = $subs_item_data->user_id;
							$user_perm_obj->master_product_ids = (!empty($user_master_products) ? implode(',',$user_master_products) : '');

							if(empty($user_perm_obj->product_ids)) {
								$user_perm_obj->product_ids = $subs_item_data->product_id;
							} else {
								//echo $user_perm_obj->product_ids. '--' .$order_product['product_details_product'];exit;
								$user_product_ids = array_unique(array_filter(explode(',', $user_perm_obj->product_ids. ',' .$order_product['product_details_product'])));

								$user_perm_obj->product_ids = implode(',', $user_product_ids);
							}
							$user_perm_obj->date_added = date('Y-m-d');
							$user_perm_obj->save();
							$response['success'] = TRUE;
							$response['error'] = FALSE;
						}
					} else {
						$response['success'] = FALSE;
						$response['error'] = TRUE;
					}
				} else {
					$response['success'] = FALSE;
					$response['error'] = TRUE;
				}
			break;

			case 'merge':
				if($subs_item_id) {
					$subs_item_data = $CI->db->get_where('order_masters',array('subs_item_id'=> $subs_item_id))->result();
					//echo $this->db->last_query();
					//echo "<pre>"; print_r($subs_item_data);
					$CI->load->model('Products');
					$product_obj = new Products();
					if(!empty($subs_item_data)) {
						$subs_item_data = $subs_item_data[0];
						$user_perm_data = $CI->db->get_where('user_perms',array('user_id'=>$subs_item_data->user_id))->result();
						if(!empty($user_perm_data)) {
							$user_perm_data = $user_perm_data[0];
							$master_product_ids = $product_obj->list_products(FALSE,' master_product_ids',array('product_id'=>$subs_item_data->product_id));
							if(!empty($master_product_ids)) {
								$master_product_ids = $master_product_ids[0]->master_product_ids;
							}

							if(!empty($user_perm_data->product_ids))
								$user_perm_data->product_ids = $user_perm_data->product_ids.','.$subs_item_data->product_id;
							else
								$user_perm_data->product_ids = $subs_item_data->product_id;


							if(!empty($user_perm_data->master_product_ids))
								$user_perm_data->master_product_ids = $user_perm_data->master_product_ids.','.$master_product_ids;
							else
								$user_perm_data->master_product_ids = $master_product_ids;

							//echo $user_perm_data->product_ids;
							//echo "after";
							//get the permissions from user_order_permissions and merge
							$user_order_perms = array();
							if(!empty($user_perm_data->user_order_perms))
							{
								$user_order_perms = unserialize($user_perm_data->user_order_perms);
								foreach($user_order_perms as $order_id=>$order_products)
								{
									foreach($order_products as $product_idnew=>$master_userprod_ids)
									{
											$user_perm_data->product_ids .= ','. $product_idnew;
											$user_perm_data->master_product_ids .= ','.$master_userprod_ids;
									}
								}

							}
							//echo "here";
							//echo $user_perm_data->product_ids;
							//echo $user_perm_data->product_ids;
							//echo "==";
							//echo $user_perm_data->master_product_ids;
							//echo "<br>";
							$user_product_ids = "";
							$user_master_products = '';
							$user_product_ids = array_unique(explode(',', $user_perm_data->product_ids));
							$user_master_products = array_unique(explode(',', $user_perm_data->master_product_ids));

							if(!empty($user_master_products) && !empty($user_product_ids)) {
								echo "inside";
									$CI->db->where('user_id',$subs_item_data->user_id);
									$CI->db->update('user_perms',array('product_ids'=> implode(',', $user_product_ids), 'master_product_ids' => implode(',',$user_master_products )));
								}

						    //echo $this->db->last_query();
							echo "---";
							echo "pids=".implode(',', $user_product_ids);
							echo "mids=".implode(',',$user_master_products );
							echo "user_id=".$subs_item_data->user_id;
							//$user_product_ids = array();
							//$user_master_products = array();

							//die();
							////
							$response['success'] = TRUE;
							$response['error'] = FALSE;
						}
					} else {
						$response['success'] = FALSE;
						$response['error'] = TRUE;
					}
				} else {
					$response['success'] = FALSE;
					$response['error'] = TRUE;
				}
			break;
		}
	}
	return $response;
}
function show_arch_webinar($userid){
$CI =& get_instance();
	if(!empty($userid)) {
		$sql="select order_id from order_masters where lower(price_term_desc) like '%y%' and lower(price_term_desc) not like '%ay' and active=1 and user_id=$userid and is_trial=0 limit 1";
		$result_array=array();
		$query=$CI->db->query($sql);
		if($query)
		{
			$result=$query->result_array();			
			if(count($result)>0)
				return 1;
			else
				return 0;
		}
	}
	else {
	return 0;
	}
		
}
function user_part_payment_orders($user_id,$order_id='')
	{
		if(!empty($user_id))
			{
				$CI =& get_instance();
				if(!empty($order_id))
					{
						$sql_order="select oc.customer_id,ob.order_balance_id,oc.cc_number,oc.order_id,ob.reminder_date,ob.status,oc.total,ob.paid,ob.balance,oc.email,oc.first_name,oc.last_name,oc.date_added from order_customers oc join order_balance ob on oc.order_id=ob.order_id
						where oc.customer_id=$user_id and oc.order_id=$order_id order by oc.order_id desc"; 
					}
				else
					{
						$sql_order="select oc.order_id,oc.total,ob.paid,ob.balance,ob.reminder_date,ob.status,oc.email,oc.first_name,oc.last_name,oc.date_added from order_customers oc join order_balance ob on oc.order_id=ob.order_id
						where oc.customer_id=$user_id order by oc.order_id desc"; 
					}
				$query=$CI->db->query($sql_order);
				$result=$query->result_array();	
				return $result;
			}
		return false;
	}
function update_user_cancel_order($order_id)
	{
		if(!empty($order_id))
			{
				$CI =& get_instance();
				$sql_order="select count(subs_item_id) as oitem from order_masters where order_id='$order_id'"; 
				$query=$CI->db->query($sql_order);
				if($query)
					{
						$result=$query->result_array();
						if(is_array($result) && count($result)>0)
							{
								if(isset($result[0]['oitem']) && $result[0]['oitem']==1)
									{
										$update_qry="update order_customers set order_status='Canceled' where order_id='$order_id'";
										$query_update=$CI->db->query($update_qry);
										return true;
									}
							}
					}
			}
	
		return false;
	}
#for multi login feature - Laxman
function check_multi_login($userid, $sessid='')
{

	$CI =& get_instance();
	$sql="select current_sess_id from users where user_id=$userid and ignore_sess_id=0";
	$result_array=array();
	$query=$CI->db->query($sql);
	$ip_array=array();
	$cutomer_ip='';
	$cutomer_ip=$CI->input->ip_address();
	$ip_array=$CI->config->item('login_ip_pass');
	$update_login=0;
	if(is_array($ip_array) && in_array($cutomer_ip,$ip_array))
			{
				$update_login=1;
			}
	if($query->num_rows()>0)
		{
			$result=$query->result_array();	
			if($result[0]['current_sess_id']=='')
			{
				$current_time = time();
				if($update_login==0){
					$query = "update users set current_sess_id='".$sessid."', user_login_time='".$current_time."' where user_id=".$userid;
					$iquery=$CI->db->query($query);
				}
				return 0;
			}
			if($result[0]['current_sess_id']!=$sessid)
				return 1;
			else
				return 0;
		}
	else
		return 0;
}

/*function check_login_as($userid)
{
	$CI =& get_instance();
	$sql="select id from login_as_users where user_id=$userid";
	$result_array=array();
	$query=$CI->db->query($sql);
	if($query->num_rows()>0)
		return 1;
	else
		return 0;
}*/
## Multiple user Login - End ##
/* Function to update user Status*/
function update_user_subscription_status($user_id)
	{

		if(!empty($user_id))
			{
		
			$CI =& get_instance();
			 $sql_users="select u.user_type,u.user_sub_type,u.user_id from users u where u.user_id=$user_id  limit 1";		
			$query=$CI->db->query($sql_users);
			
			$result=$query->result_object();	
			
			if($result)
				{
					
					$row_orders=$result[0];
					 $row_orders->user_id;
					
					if(!empty($row_orders->user_id) && $row_orders->user_sub_type!='TCI' && $row_orders->user_sub_type!='Demo' && $row_orders->user_sub_type!='Comp')
						{
					
							$sql_user_order="select sum(is_recurring) as recurring,sum(om.active) as active,group_concat(distinct om.active) as activetype,om.user_id,
												group_concat(distinct om.resource_type) as rtype,group_concat(distinct om.is_trial) as trialtype,
												group_concat(distinct om.product_type) as ptype,group_concat(distinct om.product_name) as pname,
												group_concat(distinct om.complementary) as complement,group_concat(distinct oc.isTest) as test											
												from order_masters om 
												join order_customers oc on oc.order_id=om.order_id 
												where om.user_id='$row_orders->user_id' 
												group by om.user_id";
									$query_new=$CI->db->query($sql_user_order);
									$result_new=$query_new->result_object();									
								
								if($result_new)
									{
										//enum('article','product','package','book','audio','articlepack','other','specialty','survivalpack','addon','expertpack','questionpack'
									$row_user_order=$result_new[0];
									if($row_user_order)
										{
											$ptype=trim($row_user_order->ptype);
											$rtype=trim($row_user_order->rtype);
											$active=$row_user_order->active;
											$activetype=$row_user_order->activetype;
											$trialtype=$row_user_order->trialtype;											
											$recurring=$row_user_order->recurring;
											$testuser=trim($row_user_order->test);
											$pname=trim($row_user_order->pname);
											$complement=trim($row_user_order->complement);
											$user_status='';										
										if($testuser!='1')
										{
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
													else if($recurring==0 && strpos($activetype,'3')!==FALSE)
														{
															$user_status='Pending';
														}	
													else
														{
															if($row_orders->user_sub_type!='Medallian' && $row_orders->user_sub_type!='Corporate' && $row_orders->user_sub_type!='Subuser')
																{
																	$user_status='Expired';
																}
														
														}
													
													if(!empty($user_status) && $complement!='9' && $complement!='10' && $complement!='11')
														{
															switch($row_orders->user_sub_type)
																{
																	case 'Corporate':
																		$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																		$CI->db->query($sql_update);
																	break;
																	case 'Medallian':
																		$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																		$CI->db->query($sql_update);
																	break;
																	case 'Subuser':
																		$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																		$CI->db->query($sql_update);
																	break;
																	default:
																		$sql_update="update users set user_sub_type='Subscriber',user_sub_status='$user_status' where user_id=$row_orders->user_id";
																		$CI->db->query($sql_update);
																	break;
																}
														}
													else if($complement=='9')
														{
															
															switch($row_orders->user_sub_type)
																{
																	case 'Medallian':
																		$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																		$CI->db->query($sql_update);
																	break;
																	case 'Corporate':
																		$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																		$CI->db->query($sql_update);
																	break;
																	case 'Subuser':
																		$sql_update="update users set user_sub_status='$user_status' where user_id=$row_orders->user_id";
																		$CI->db->query($sql_update);
																	break;
																	default:
																		$sql_update="update users set user_sub_type='Print Buyer',user_sub_status='$user_status' where user_id=$row_orders->user_id";
																		$CI->db->query($sql_update);
																	break;
																}
																
															
														}
													else if($complement=='10' || $complement=='11')
														{
															switch($row_orders->user_sub_type)
																{
																	case 'Corporate':
																		$sql_update="update users set user_sub_status='Active Non Recurring' where user_id=$row_orders->user_id";
																		$CI->db->query($sql_update);
																	break;
																	case 'Medallian':
																		$sql_update="update users set user_sub_status='Active Non Recurring' where user_id=$row_orders->user_id";
																		$CI->db->query($sql_update);
																	break;
																	case 'Subuser':
																		$sql_update="update users set user_sub_status='Active Non Recurring' where user_id=$row_orders->user_id";
																		$CI->db->query($sql_update);
																	break;
																	default:
																		$sql_update="update users set user_sub_type='Comp',user_sub_status='Active Non Recurring' where user_id=$row_orders->user_id";
																		$CI->db->query($sql_update);
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
																	case 'Corporate':
																		$sql_update="update users set user_sub_status='Active Non Recurring' where user_id=$row_orders->user_id";
																		$CI->db->query($sql_update);
																	break;
																	case 'Medallian':
																		$sql_update="update users set user_sub_status='Active Non Recurring' where user_id=$row_orders->user_id";
																		$CI->db->query($sql_update);
																	break;
																	case 'Subuser':
																		$sql_update="update users set user_sub_status='Active Non Recurring' where user_id=$row_orders->user_id";
																		$CI->db->query($sql_update);
																	break;
																	default:
																		$sql_update="update users set user_sub_type='Print Buyer',user_sub_status='Active Non Recurring' where user_id=$row_orders->user_id";
																		$CI->db->query($sql_update);
																	break;
																}														
												}
											else if(($rtype=='singleoff' || (strpos($rtype,'subs')!==FALSE && (strpos($ptype,'articlepack')!==FALSE || strpos($ptype,'article')!==FALSE) )) && $row_orders->user_sub_type!='Medallian' && $row_orders->user_sub_type!='Corporate' && $row_orders->user_sub_type!='Subuser' &&  $row_orders->user_sub_type!='TCI')
												{
													$sql_update="update users set user_sub_type='Registered Buyer',user_sub_status='Registered' where user_id=$row_orders->user_id";
													$CI->db->query($sql_update);
												}
											}
											else
												{
													$sql_update="update users set user_sub_type='TCI',user_sub_status='Active Non Recurring' where user_id=$row_orders->user_id";
													$CI->db->query($sql_update);
												}
											
										}
									else if($row_orders->user_sub_type!='Medallian' && $row_orders->user_sub_type!='Corporate' && $row_orders->user_sub_type!='Subuser' &&  $row_orders->user_sub_type!='TCI')
										{
											$sql_update="update users set user_sub_type='Registered',user_sub_status='Registered' where user_id=$row_orders->user_id";
											$CI->db->query($sql_update);
										}
									else if(empty($row_orders->user_sub_type))
										{
											$sql_update="update users set user_sub_type='Registered',user_sub_status='Registered' where user_id=$row_orders->user_id";
											$CI->db->query($sql_update);
										}
									
								}
							else if($row_orders->user_sub_type!='Medallian' && $row_orders->user_sub_type!='Corporate' && $row_orders->user_sub_type!='Subuser' &&  $row_orders->user_sub_type!='TCI')
								{
									$sql_update="update users set user_sub_type='Registered',user_sub_status='Registered' where user_id=$row_orders->user_id";
									$CI->db->query($sql_update);
								}
							else if(empty($row_orders->user_sub_type))
								{
									$sql_update="update users set user_sub_type='Registered',user_sub_status='Registered' where user_id=$row_orders->user_id";
									$CI->db->query($sql_update);
								}	
							
						}
				}
			}
	}		
function get_user_order_reminder($order_id)
	{
		$CI =& get_instance();		 
		$sql_master="select * from order_balance where order_id=$order_id and balance>0";
		$query=$CI->db->query($sql_master);
		$result_balance=$query->result_array();	
		
		$sql_end_date="select min(start_date) as start_date from order_masters where order_id=$order_id and active=1 and is_trial=0";
		$query_end_date=$CI->db->query($sql_end_date);
		$result_end_date=$query_end_date->result_array();
		$start_date=date('Y-m-d');
						if(isset($result_end_date) && isset($result_end_date[0]) && count($result_end_date[0])>0)
							{
								if(!empty($result_end_date[0]['start_date']))
									{
										$start_date=$result_end_date[0]['start_date'];
									}
								
							}
		$physical_price=0;
	
		if($result_balance)
			{
				
			
				if(isset($result_balance) && isset($result_balance[0]) && count($result_balance[0])>0)
					{
						
						$ototal=$result_balance[0]['order_total'];
						$obalance=$result_balance[0]['balance'];
						$opaid=$result_balance[0]['paid'];
						
						$sql_master="select product_price,product_type,resource_type from order_masters where order_id=$order_id and active=1 and is_trial=0";
						$query=$CI->db->query($sql_master);
						$result=$query->result_array();	
						
						if($result)
							{
								if(isset($result) && isset($result[0]) && count($result[0])>0)
									{
									
										foreach($result as $key=>$val)
											{											
													if($val['product_type']!='package' && $val['product_type']!='product' && $val['product_type']!='specialty' && $val['product_type']!='survivalpack'
													&& $val['product_type']!='questionpack' && $val['product_type']!='expertpack')
														{	
														
															$physical_price+=$val['product_price'];
														}
												
											}
										$obtotal= $ototal-$physical_price;
										$perdaycharge=number_format(($obtotal/365),2);
										$paidamount= $opaid-$physical_price;
										$noofdays=	ceil($paidamount/$perdaycharge);
									
										if($noofdays==1)
													{
														$term_days = "+" .$noofdays."day";
													}
													else
													{
														$term_days = "+" .$noofdays."days";
													}
										return $end_date_new = date('Y-m-d' ,strtotime(date("Y-m-d", strtotime($start_date)) .$term_days)); // End Date
									}
							}
							
					}
			}
		
		return false;
	}
	
	function check_product_with_user($user_id,$product_id)
	{
		if(!empty($user_id))
		{
			$CI =& get_instance();
			$sql="select om.product_id 
			from order_masters om 
			join order_customers oc on oc.order_id=om.order_id 
			where om.user_id=$user_id 
			and om.product_id=$product_id
			";
			$query=$CI->db->query($sql);
			$result=$query->result_array();	
			if(count($result)>0)
				return TRUE;
			else
				return FALSE;
		}
		return false;
	}	
	
	function is_this_trial_product($slug,$user_id)
	{
		$specialty_product_array = array(
		"aca"=>"223",//Anesthesia Coding Alert
		"cca"=>"245",//Cardiology Coding Alert
		"der"=>"247",//Dermatology Coding Alert
		"drg"=>"250",//DRG Coding Alert
		"eca"=>"224",//Emergency Department Coding &amp; Reimbursement Alert
		"fca"=>"225",//Family Practice Coding Alert
		"gac"=>"226",//Gastroenterology Coding Alert
		"gca"=>"227",//General Surgery Coding Alert
		"ict"=>"248",//ICD-10 Coding Alert
		"ica"=>"244",//Internal Medicine Coding Alert
		"mob"=>"228",//Medical Office Billing &amp; Collections Alert
		"nca"=>"229",//Neurology &amp; Pain Management Coding Alert
		"nec"=>"230",//Neurosurgery Coding Alert
		"oca"=>"231",//Ob-Gyn Coding Alert
		"onc"=>"232",//Oncology &amp; Hematology Coding Alert
		"opc"=>"233",//Ophthalmology Coding Alert
		"opt"=>"234",//Optometry Coding &amp; Billing Alert
		"orc"=>"235",//Orthopedic Coding Alert
		"otc"=>"236",//Otolaryngology Coding Alert
		"ofc"=>"249",//Outpatient Facility Coding Alert
		"pbi"=>"238",//Part B Insider (Multispecialty) Coding Alert
		"pac"=>"237",//Pathology/Lab Coding Alert
		"pca"=>"239",//Pediatric Coding Alert
		"pmc"=>"240",//Physical Medicine &amp; Rehab Coding Alert
		"pod"=>"246",//Podiatry Coding &amp; Billing Alert
		"puc"=>"241",//Pulmonology Coding Alert
		"rca"=>"242",//Radiology Coding Alert
		"uca"=>"243",//Urology Coding Alert
		"psy"=>"402",//Psychiatry Coding &amp; Reimbursement Alert
		"osa"=>"442",//OASIS Alert
		"mlr"=>"443",//Medicare Compliance &amp; Reimbursement
		"mds"=>"441",//MDS Alert
		"lsa"=>"440",//Long-Term Care Survey Alert
		"icd"=>"439",//Home Health ICD-9 Alert
		"hcw"=>"437",//Home Care Week
		"hica"=>"444",//Health Information Compliance Alert
		"hop"=>"438"//Eli Hospice Insider
		);
		if(!empty($user_id) && !empty($slug))
		{
			if(array_key_exists($slug,$specialty_product_array))
			{
				$product_id = $specialty_product_array[$slug];
				$CI =& get_instance();
				$sql="select om.product_id 
				from order_masters om 
				join order_customers oc on oc.order_id=om.order_id 
				where om.user_id=$user_id 
				and om.product_id=$product_id
				and is_trial = 1
				and active=1
				and end_date>=now()
				";
				$query=$CI->db->query($sql);
				$result=$query->result_array();	
				if(count($result)>0)
					return TRUE;
				else
					return FALSE;
			}else
				return FALSE;
		}
		return false;
	}		
	
	function is_this_trial_product_downloadable($slug,$user_id,$pdf)
	{
		$specialty_product_array = array(
		"aca"=>"223",//Anesthesia Coding Alert
		"cca"=>"245",//Cardiology Coding Alert
		"der"=>"247",//Dermatology Coding Alert
		"drg"=>"250",//DRG Coding Alert
		"eca"=>"224",//Emergency Department Coding &amp; Reimbursement Alert
		"fca"=>"225",//Family Practice Coding Alert
		"gac"=>"226",//Gastroenterology Coding Alert
		"gca"=>"227",//General Surgery Coding Alert
		"ict"=>"248",//ICD-10 Coding Alert
		"ica"=>"244",//Internal Medicine Coding Alert
		"mob"=>"228",//Medical Office Billing &amp; Collections Alert
		"nca"=>"229",//Neurology &amp; Pain Management Coding Alert
		"nec"=>"230",//Neurosurgery Coding Alert
		"oca"=>"231",//Ob-Gyn Coding Alert
		"onc"=>"232",//Oncology &amp; Hematology Coding Alert
		"opc"=>"233",//Ophthalmology Coding Alert
		"opt"=>"234",//Optometry Coding &amp; Billing Alert
		"orc"=>"235",//Orthopedic Coding Alert
		"otc"=>"236",//Otolaryngology Coding Alert
		"ofc"=>"249",//Outpatient Facility Coding Alert
		"pbi"=>"238",//Part B Insider (Multispecialty) Coding Alert
		"pac"=>"237",//Pathology/Lab Coding Alert
		"pca"=>"239",//Pediatric Coding Alert
		"pmc"=>"240",//Physical Medicine &amp; Rehab Coding Alert
		"pod"=>"246",//Podiatry Coding &amp; Billing Alert
		"puc"=>"241",//Pulmonology Coding Alert
		"rca"=>"242",//Radiology Coding Alert
		"uca"=>"243",//Urology Coding Alert
		"psy"=>"402",//Psychiatry Coding &amp; Reimbursement Alert
		"osa"=>"442",//OASIS Alert
		"mlr"=>"443",//Medicare Compliance &amp; Reimbursement
		"mds"=>"441",//MDS Alert
		"lsa"=>"440",//Long-Term Care Survey Alert
		"icd"=>"439",//Home Health ICD-9 Alert
		"hcw"=>"437",//Home Care Week
		"hica"=>"444",//Health Information Compliance Alert
		"hop"=>"438"//Eli Hospice Insider
		);
		if(!empty($user_id) && !empty($slug))
		{
			if(array_key_exists($slug,$specialty_product_array))
			{
				$product_id = $specialty_product_array[$slug];
				$CI =& get_instance();
				$sql="select om.product_id 
				from order_masters om 
				join order_customers oc on oc.order_id=om.order_id 
				where om.user_id=$user_id 
				and om.product_id=$product_id
				and is_trial = 1
				and end_date>=now()
				";
				$query=$CI->db->query($sql);
				$result=$query->result_array();	
					//echo $pdf;
					//echo in_array($pdf,$visited_articles_array);
				//print_r($visited_articles_array);die;
				if(count($result)>0)
				{
				//check user_trial history
					$sql_trial_history="select downloaded_articles from user_trial_access_history where trial_product_id=$product_id and user_id = $user_id and is_active = 1";
					$query_trial_history=$CI->db->query($sql_trial_history);
					$result_trial_history=$query_trial_history->result_array();	
					$visited_articles_array = array();
					if(count($result_trial_history)>0)
					{
						$visited_articles = $result_trial_history[0]['downloaded_articles'];
						$visited_articles_array = explode(',',$visited_articles);
					}
					if(count($visited_articles_array)<2 || in_array($pdf,$visited_articles_array))					
					{
						return true;
					}else
					{
						return false;
					}
					
				}
				else
				{
					return true;
				}
			}else
				return false;
		}
		return false;
	}		

	
	function is_this_trial_article($slug,$user_id,$article_id)
	{
		$CI =& get_instance();
		$specialty_product_array = array(
		"aca"=>"223",//Anesthesia Coding Alert
		"cca"=>"245",//Cardiology Coding Alert
		"der"=>"247",//Dermatology Coding Alert
		"drg"=>"250",//DRG Coding Alert
		"eca"=>"224",//Emergency Department Coding &amp; Reimbursement Alert
		"fca"=>"225",//Family Practice Coding Alert
		"gac"=>"226",//Gastroenterology Coding Alert
		"gca"=>"227",//General Surgery Coding Alert
		"ict"=>"248",//ICD-10 Coding Alert
		"ica"=>"244",//Internal Medicine Coding Alert
		"mob"=>"228",//Medical Office Billing &amp; Collections Alert
		"nca"=>"229",//Neurology &amp; Pain Management Coding Alert
		"nec"=>"230",//Neurosurgery Coding Alert
		"oca"=>"231",//Ob-Gyn Coding Alert
		"onc"=>"232",//Oncology &amp; Hematology Coding Alert
		"opc"=>"233",//Ophthalmology Coding Alert
		"opt"=>"234",//Optometry Coding &amp; Billing Alert
		"orc"=>"235",//Orthopedic Coding Alert
		"otc"=>"236",//Otolaryngology Coding Alert
		"ofc"=>"249",//Outpatient Facility Coding Alert
		"pbi"=>"238",//Part B Insider (Multispecialty) Coding Alert
		"pac"=>"237",//Pathology/Lab Coding Alert
		"pca"=>"239",//Pediatric Coding Alert
		"pmc"=>"240",//Physical Medicine &amp; Rehab Coding Alert
		"pod"=>"246",//Podiatry Coding &amp; Billing Alert
		"puc"=>"241",//Pulmonology Coding Alert
		"rca"=>"242",//Radiology Coding Alert
		"uca"=>"243",//Urology Coding Alert
		"psy"=>"402",//Psychiatry Coding &amp; Reimbursement Alert
		"osa"=>"442",//OASIS Alert
		"mlr"=>"443",//Medicare Compliance &amp; Reimbursement
		"mds"=>"441",//MDS Alert
		"lsa"=>"440",//Long-Term Care Survey Alert
		"icd"=>"439",//Home Health ICD-9 Alert
		"hcw"=>"437",//Home Care Week
		"hica"=>"444",//Health Information Compliance Alert
		"hop"=>"438"//Eli Hospice Insider
		);
		$trial_product = false;
		if(!empty($user_id) && !empty($slug))
		{
			if(array_key_exists($slug,$specialty_product_array))
			{
				$product_id = $specialty_product_array[$slug];
				$sql="select om.product_id 
				from order_masters om 
				join order_customers oc on oc.order_id=om.order_id 
				where om.user_id=$user_id 
				and om.product_id=$product_id
				and is_trial = 1
				and end_date>=now()
				";
				$query=$CI->db->query($sql);
				$result=$query->result_array();	
				if(count($result)>0)
					$trial_product = TRUE;
				else
					$trial_product = FALSE;
			}else{
				return FALSE;
			}	
		}
		if(!empty($article_id) && $trial_product)
		{
			$sql_post_pdf="select id from articles_post_pdf where specialty='$slug' order by id desc limit 0,3";
			$query_post_pdf=$CI->db->query($sql_post_pdf);
			$result_post_pdf=$query_post_pdf->result_array();
			
			
			if(is_array($result_post_pdf) && count($result_post_pdf)>0)
			{
				$ids = '';
				foreach($result_post_pdf as $item)
					$ids .= $item['id'].",";
				$ids = trim($ids,",");
				$sql="select ID from wp_posts where pdf_id in ($ids) and ID = $article_id";
				$query=$CI->db->query($sql);
				$result=$query->result_array();	
				//check user_trial history
					$sql_trial_history="select visited_articles from user_trial_access_history where trial_product_id=$product_id and user_id = $user_id and is_active = 1";
					$query_trial_history=$CI->db->query($sql_trial_history);
					$result_trial_history=$query_trial_history->result_array();	
					$visited_articles_array = array();
					if(count($result_trial_history)>0)
					{
						$visited_articles = $result_trial_history[0]['visited_articles'];
						$visited_articles_array = explode(',',$visited_articles);
					}
					
				//print_r($result_trial_history);die;
				if(count($result)>0 || (count($visited_articles_array)>=18 && !in_array($article_id,$visited_articles_array)))
				{
					return TRUE;
				}else
				{
					//update trial user visited articles
					//update_visited_article($user_id,$product_id,$article_id);
					return FALSE;
				}
			}
		}
		else
		{
			return FALSE;
		}
	}	
	
	function check_trial_article_and_update($slug,$user_id,$article_id)
	{
		$CI =& get_instance();
		$specialty_product_array = array(
		"aca"=>"223",//Anesthesia Coding Alert
		"cca"=>"245",//Cardiology Coding Alert
		"der"=>"247",//Dermatology Coding Alert
		"drg"=>"250",//DRG Coding Alert
		"eca"=>"224",//Emergency Department Coding &amp; Reimbursement Alert
		"fca"=>"225",//Family Practice Coding Alert
		"gac"=>"226",//Gastroenterology Coding Alert
		"gca"=>"227",//General Surgery Coding Alert
		"ict"=>"248",//ICD-10 Coding Alert
		"ica"=>"244",//Internal Medicine Coding Alert
		"mob"=>"228",//Medical Office Billing &amp; Collections Alert
		"nca"=>"229",//Neurology &amp; Pain Management Coding Alert
		"nec"=>"230",//Neurosurgery Coding Alert
		"oca"=>"231",//Ob-Gyn Coding Alert
		"onc"=>"232",//Oncology &amp; Hematology Coding Alert
		"opc"=>"233",//Ophthalmology Coding Alert
		"opt"=>"234",//Optometry Coding &amp; Billing Alert
		"orc"=>"235",//Orthopedic Coding Alert
		"otc"=>"236",//Otolaryngology Coding Alert
		"ofc"=>"249",//Outpatient Facility Coding Alert
		"pbi"=>"238",//Part B Insider (Multispecialty) Coding Alert
		"pac"=>"237",//Pathology/Lab Coding Alert
		"pca"=>"239",//Pediatric Coding Alert
		"pmc"=>"240",//Physical Medicine &amp; Rehab Coding Alert
		"pod"=>"246",//Podiatry Coding &amp; Billing Alert
		"puc"=>"241",//Pulmonology Coding Alert
		"rca"=>"242",//Radiology Coding Alert
		"uca"=>"243",//Urology Coding Alert
		"psy"=>"402",//Psychiatry Coding &amp; Reimbursement Alert
		"osa"=>"442",//OASIS Alert
		"mlr"=>"443",//Medicare Compliance &amp; Reimbursement
		"mds"=>"441",//MDS Alert
		"lsa"=>"440",//Long-Term Care Survey Alert
		"icd"=>"439",//Home Health ICD-9 Alert
		"hcw"=>"437",//Home Care Week
		"hica"=>"444",//Health Information Compliance Alert
		"hop"=>"438"//Eli Hospice Insider
		);
		$trial_product = false;
		if(!empty($user_id) && !empty($slug))
		{
			if(array_key_exists($slug,$specialty_product_array))
			{
				$product_id = $specialty_product_array[$slug];
				$sql="select om.product_id 
				from order_masters om 
				join order_customers oc on oc.order_id=om.order_id 
				where om.user_id=$user_id 
				and om.product_id=$product_id
				and is_trial = 1
				and active=1
				and end_date>=now()
				";
				$query=$CI->db->query($sql);
				$result=$query->result_array();	
				if(count($result)>0)
					$trial_product = TRUE;
				else
					$trial_product = FALSE;
			}else{
				return FALSE;
			}	
		}
		if(!empty($article_id) && $trial_product)
		{
			$sql_post_pdf="select id from articles_post_pdf where specialty='$slug' order by id desc limit 0,3";
			$query_post_pdf=$CI->db->query($sql_post_pdf);
			$result_post_pdf=$query_post_pdf->result_array();
			
			
			if(is_array($result_post_pdf) && count($result_post_pdf)>0)
			{
				$ids = '';
				foreach($result_post_pdf as $item)
					$ids .= $item['id'].",";
				$ids = trim($ids,",");
				$sql="select ID from wp_posts where pdf_id in ($ids) and ID = $article_id";
				$query=$CI->db->query($sql);
				$result=$query->result_array();	
				//check user_trial history
					$sql_trial_history="select visited_articles from user_trial_access_history where trial_product_id=$product_id and user_id = $user_id and is_active = 1";
					$query_trial_history=$CI->db->query($sql_trial_history);
					$result_trial_history=$query_trial_history->result_array();	
					$visited_articles_array = array();
					if(count($result_trial_history)>0)
					{
						$visited_articles = $result_trial_history[0]['visited_articles'];
						$visited_articles_array = explode(',',$visited_articles);
					}
				//print_r($result);die;
				if(count($result)>0  || (count($visited_articles_array)>=18 && !in_array($article_id,$visited_articles_array)))
				{
					return TRUE;
				}else
				{
					//update trial user visited articles
					update_visited_article($user_id,$product_id,$article_id);
					return FALSE;
				}
			}
		}
		else
		{
			return FALSE;
		}
	}
	function update_visited_article($user_id,$product_id,$article_id)
		{
			$CI =& get_instance();
			$article_id_string='';
			$article_array=array();
			
			if(!empty($article_id) && !empty($user_id) && !empty($product_id))
				{
					$sql_user="select * from user_trial_access_history where user_id=$user_id and trial_product_id = $product_id and is_active=1";
					$query=$CI->db->query($sql_user);
					$result_user=$query->result_array();	// Product Data	Array
					if(is_array($result_user) && $result_user[0]['id'])
						{				
							$id =$result_user[0]['id'];
							$data_perms=array();
							$article_id_string=$result_user[0]['visited_articles'].','.$article_id;
							$unique=array();
							
							if(!empty($article_id_string))
								{
									$product_array=explode(',',$article_id_string);
									$unique_article = array_unique($product_array);
									$article_id_string=implode(',',$unique_article);
									$data_perms['visited_articles']=trim($article_id_string,',');
								}
						
							$CI->db->where('id',$id);
							$CI->db->update('user_trial_access_history',$data_perms);
						
						}
				}
	
		}
	
	function update_trial_product_downloaded_pdf($pdffinal,$user_id)
	{
		$pdf_parts = explode('/',$pdffinal);
		if(is_array($pdf_parts) && count($pdf_parts)>1)
		{
			$slug = $pdf_parts[1]; 
			$pdf = $pdf_parts[2];
			$specialty_product_array = array(
			"aca"=>"223",//Anesthesia Coding Alert
			"cca"=>"245",//Cardiology Coding Alert
			"der"=>"247",//Dermatology Coding Alert
			"drg"=>"250",//DRG Coding Alert
			"eca"=>"224",//Emergency Department Coding &amp; Reimbursement Alert
			"fca"=>"225",//Family Practice Coding Alert
			"gac"=>"226",//Gastroenterology Coding Alert
			"gca"=>"227",//General Surgery Coding Alert
			"ict"=>"248",//ICD-10 Coding Alert
			"ica"=>"244",//Internal Medicine Coding Alert
			"mob"=>"228",//Medical Office Billing &amp; Collections Alert
			"nca"=>"229",//Neurology &amp; Pain Management Coding Alert
			"nec"=>"230",//Neurosurgery Coding Alert
			"oca"=>"231",//Ob-Gyn Coding Alert
			"onc"=>"232",//Oncology &amp; Hematology Coding Alert
			"opc"=>"233",//Ophthalmology Coding Alert
			"opt"=>"234",//Optometry Coding &amp; Billing Alert
			"orc"=>"235",//Orthopedic Coding Alert
			"otc"=>"236",//Otolaryngology Coding Alert
			"ofc"=>"249",//Outpatient Facility Coding Alert
			"pbi"=>"238",//Part B Insider (Multispecialty) Coding Alert
			"pac"=>"237",//Pathology/Lab Coding Alert
			"pca"=>"239",//Pediatric Coding Alert
			"pmc"=>"240",//Physical Medicine &amp; Rehab Coding Alert
			"pod"=>"246",//Podiatry Coding &amp; Billing Alert
			"puc"=>"241",//Pulmonology Coding Alert
			"rca"=>"242",//Radiology Coding Alert
			"uca"=>"243",//Urology Coding Alert
			"psy"=>"402",//Psychiatry Coding &amp; Reimbursement Alert
			"osa"=>"442",//OASIS Alert
			"mlr"=>"443",//Medicare Compliance &amp; Reimbursement
			"mds"=>"441",//MDS Alert
			"lsa"=>"440",//Long-Term Care Survey Alert
			"icd"=>"439",//Home Health ICD-9 Alert
			"hcw"=>"437",//Home Care Week
			"hica"=>"444",//Health Information Compliance Alert
			"hop"=>"438"//Eli Hospice Insider
			);
			if(!empty($user_id) && !empty($slug))
			{
				if(array_key_exists($slug,$specialty_product_array))
				{
					$product_id = $specialty_product_array[$slug];
					$CI =& get_instance();
					$sql="select om.product_id 
					from order_masters om 
					join order_customers oc on oc.order_id=om.order_id 
					where om.user_id=$user_id 
					and om.product_id=$product_id
					and is_trial = 1
					and end_date>=now()
					";
					$query=$CI->db->query($sql);
					$result=$query->result_array();	
					if(count($result)>0)
					{
						$sql_user="select * from user_trial_access_history where user_id=$user_id and trial_product_id = $product_id and is_active=1";
						$query=$CI->db->query($sql_user);
						$result_user=$query->result_array();	// Product Data	Array
						if(is_array($result_user) && $result_user[0]['id'])
							{				
								$id =$result_user[0]['id'];
								$data_perms=array();
								$article_pdf_string=$result_user[0]['downloaded_articles'].','.$pdf;
								$unique=array();
								
								if(!empty($article_pdf_string))
									{
										$product_array=explode(',',$article_pdf_string);
										$unique_article = array_unique($product_array);
										$article_pdf_string=implode(',',$unique_article);
										$data_perms['downloaded_articles']=trim($article_pdf_string,',');
									}
							
								$CI->db->where('id',$id);
								$CI->db->update('user_trial_access_history',$data_perms);
							
							}
					}
				}
			}
		}
	}		
	
/* Functions are used in Admin Users Specialty Edit Module - Start*/	
	function get_extra_specialty_products()
	{
			$CI =& get_instance();
			$sql="select product_name,product_id from products where speciality_allowed>0 and product_type!='specialty' and (product_name not like '%coder%' or product_name like 'Physician coder%') and product_name not like '%biller%'";
			$query=$CI->db->query($sql);
			$result=$query->result_array();	
			if(count($result)>0)
			{
			$newresult = array();
				foreach($result as $r)
				{
					$newresult[] = $r['product_id'];
				}
				return $newresult;
			}
			else
				return FALSE;

	}
	
	function get_users_extra_specialties($user_id)
	{
		if(!empty($user_id))
		{
			$CI =& get_instance();
			$sql = "select product_id,product_name,master_product_ids from products where product_type = 'specialty' 
and product_name not like '%Print & Web%'
and master_product_ids not like '%,%'
				";	
			$result = $CI->db->query($sql)->result_array();

			$CI =& get_instance();
			$sql = "select om.product_id,om.product_name,p.master_product_ids from order_masters om 
			JOIN products p on om.product_id=p.product_id 
			where p.product_type = 'specialty' 
and p.product_name not like '%Print & Web%'
and p.master_product_ids not like '%,%' AND om.user_id = $user_id
				";	
			$exclude_result = $CI->db->query($sql)->result_array();
			
			print_r($exclude_result);//die;
			
			$sql_perms = "SELECT master_product_ids from user_perms where user_id = $user_id";	
			$result_perms = $CI->db->query($sql_perms)->result_array();

			print_r($result_perms);
			if(count($result)>0)
					return $result;
				else
					return false;
		}
		return false;
	}

	function get_users_specialties($user_id)
	{
	$arr_spec_prods = array("193"=>"Anesthesia Coding Alert",
"194"=>"Emergency Department Coding &amp; Reimbursement Alert",
"195"=>"Family Practice Coding Alert",
"196"=>"Gastroenterology Coding Alert",
"197"=>"General Surgery Coding Alert",
"198"=>"Practice Management Alert",
"199"=>"Neurology &amp; Pain Management Coding Alert",
"200"=>"Neurosurgery Coding Alert",
"201"=>"Ob-Gyn Coding Alert",
"202"=>"Oncology &amp; Hematology Coding Alert",
"203"=>"Ophthalmology Coding Alert",
"204"=>"Optometry Coding &amp; Billing Alert",
"205"=>"Orthopedic Coding Alert",
"206"=>"Otolaryngology Coding Alert",
"207"=>"Pathology/Lab Coding Alert",
"208"=>"Part B Insider (Multispecialty) Coding Alert",
"209"=>"Pediatric Coding Alert",
"210"=>"Physical Medicine &amp; Rehab Coding Alert",
"211"=>"Pulmonology Coding Alert",
"212"=>"Radiology Coding Alert",
"213"=>"Urology Coding Alert",
"214"=>"Internal Medicine Coding Alert",
"215"=>"Cardiology Coding Alert",
"216"=>"Podiatry Coding &amp; Billing Alert",
"217"=>"Dermatology Coding Alert",
"218"=>"ICD-10 Coding Alert",
"219"=>"Outpatient Facility Coding Alert",
"220"=>"DRG Coding Alert",
"223"=>"Anesthesia Coding Alert",
"224"=>"Emergency Department Coding &amp; Reimbursement Alert",
"225"=>"Family Practice Coding Alert",
"226"=>"Gastroenterology Coding Alert",
"227"=>"General Surgery Coding Alert",
"228"=>"Practice Management Alert",
"229"=>"Neurology &amp; Pain Management Coding Alert",
"230"=>"Neurosurgery Coding Alert",
"231"=>"Ob-Gyn Coding Alert",
"232"=>"Oncology &amp; Hematology Coding Alert",
"233"=>"Ophthalmology Coding Alert",
"234"=>"Optometry Coding &amp; Billing Alert",
"235"=>"Orthopedic Coding Alert",
"236"=>"Otolaryngology Coding Alert",
"237"=>"Pathology/Lab Coding Alert",
"238"=>"Part B Insider (Multispecialty) Coding Alert",
"239"=>"Pediatric Coding Alert",
"240"=>"Physical Medicine &amp; Rehab Coding Alert",
"241"=>"Pulmonology Coding Alert",
"242"=>"Radiology Coding Alert",
"243"=>"Urology Coding Alert",
"244"=>"Internal Medicine Coding Alert",
"245"=>"Cardiology Coding Alert",
"246"=>"Podiatry Coding &amp; Billing Alert",
"247"=>"Dermatology Coding Alert",
"248"=>"ICD-10 Coding Alert",
"249"=>"Outpatient Facility Coding Alert",
"250"=>"DRG Coding Alert",
"262"=>"Part B Insider (Multispecialty) Coding Alert - Print & Web",
"263"=>"Anesthesia Coding Alert - Print & Web",
"264"=>"Cardiology Coding Alert - Print & Web",
"265"=>"Dermatology Coding Alert - Print & Web",
"266"=>"DRG Coding Alert - Print & Web",
"267"=>"Emergency Department Coding &amp; Reimbursement Alert - Print & Web",
"268"=>"Family Practice Coding Alert - Print & Web",
"269"=>"Gastroenterology Coding Alert - Print & Web",
"270"=>"General Surgery Coding Alert - Print & Web",
"271"=>"ICD-10 Coding Alert - Print & Web",
"272"=>"Internal Medicine Coding Alert - Print & Web",
"273"=>"Practice Management Alert - Print & Web",
"274"=>"Neurology &amp; Pain Management Coding Alert - Print & Web",
"275"=>"Neurosurgery Coding Alert - Print & Web",
"276"=>"Ob-Gyn Coding Alert - Print & Web",
"277"=>"Oncology &amp; Hematology Coding Alert - Print & Web",
"278"=>"Ophthalmology Coding Alert - Print & Web",
"279"=>"Optometry Coding &amp; Billing Alert - Print & Web",
"280"=>"Orthopedic Coding Alert - Print & Web",
"281"=>"Otolaryngology Coding Alert - Print & Web",
"282"=>"Outpatient Facility Coding Alert - Print & Web",
"283"=>"Pathology/Lab Coding Alert - Print & Web",
"284"=>"Pediatric Coding Alert - Print & Web",
"285"=>"Physical Medicine &amp; Rehab Coding Alert - Print & Web",
"286"=>"Podiatry Coding &amp; Billing Alert - Print & Web",
"287"=>"Pulmonology Coding Alert - Print & Web",
"288"=>"Radiology Coding Alert - Print & Web",
"289"=>"Urology Coding Alert - Print & Web",
"402"=>"Psychiatry Coding &amp; Reimbursement Alert",
"403"=>"Psychiatry Coding &amp; Reimbursement Alert Print & Web",
"437"=>"Home Care Week",
"438"=>"Eli Hospice Insider",
"439"=>"Home Health ICD-9 Alert",
"440"=>"Long-Term Care",
"441"=>"MDS",
"442"=>"OASIS",
"443"=>"Medicare Compliance & Reimbursement",
"444"=>"Health Information Compliance Alert",
"459"=>"Home Care Week - Print & Web",
"460"=>"Eli Hospice Insider - Print & Web",
"461"=>"Home Health ICD-9 Alert - Print & Web",
"462"=>"Long-Term Care - Print & Web",
"463"=>"MDS - Print & Web",
"465"=>"OASIS - Print & Web",
"466"=>"Medicare Compliance & Reimbursement - Print & Web",
"467"=>"Health Information Compliance Alert - Print & Web",);
	
		$CI =& get_instance();
		$sql = "select product_ids from user_perms where user_id=".$user_id;
		$result = $CI->db->query($sql)->result_array();
		$user_products = $result[0]['product_ids'];
		$arr_user_prods = explode(",",$user_products);
		$user_prods = array();
		foreach($arr_user_prods as $prod)
		{	
			if(array_key_exists($prod, $arr_spec_prods))
			{	
				//array_push($user_prods,$prod);				
				$user_prods[$prod] = $arr_spec_prods[$prod];
			}
		}
		/*print_r($user_prods);
		die("adsf");*/
		return $user_prods;
	}
function get_user_products($user_id)
{
	$CI =& get_instance();
	$arr_user_prods ='';
	$sql = "select product_ids from user_perms where user_id=".$user_id;
	$result = $CI->db->query($sql)->result_array();
	$user_products = $result[0]['product_ids'];
	$arr_user_prods = explode(",",$user_products);
	return $arr_user_prods;
}
function get_user_master_products($user_id)
{
	$CI =& get_instance();
	$arr_user_mprods = '';
	$sql = "select master_product_ids from user_perms where user_id=".$user_id;
	$result = $CI->db->query($sql)->result_array();
	$user_mproducts = $result[0]['master_product_ids'];
	$arr_user_mprods = explode(",",$user_mproducts);
	return $arr_user_mprods;
}
function get_user_specs($user_id)
{
	$CI =& get_instance();
	$arr_user_prods ='';
	$sql = "select specialty_ids from user_perms where user_id=".$user_id;
	$result = $CI->db->query($sql)->result_array();
	$user_products = $result[0]['specialty_ids'];
	$arr_user_prods = explode(",",$user_products);
	return $arr_user_prods;
}
function get_user_master_specs($user_id)
{
	$CI =& get_instance();
	$arr_user_mprods = '';
	$sql = "select master_specialty_ids from user_perms where user_id=".$user_id;
	$result = $CI->db->query($sql)->result_array();
	$user_mproducts = $result[0]['master_specialty_ids'];
	$arr_user_mprods = explode(",",$user_mproducts);
	return $arr_user_mprods;
}
function check_specialty_product($product_id)
{
	if(!empty($product_id))
	{
		$CI =& get_instance();
		$sql="select product_type from products where product_id=$product_id";
		$query=$CI->db->query($sql);
		$result=$query->result_array();	
		if(count($result)>0)
		{
			if($result[0]['product_type']=='specialty')
				return true;
			else
				return false;
		}
		else
			return FALSE;
	}
	return false;
}
/* Functions are used in Admin Users Specialty Edit Module - End*/	
function user_to_show_popup($user_id)
{
	$CI =& get_instance();
	$user = '';
	$sql = "select id from user_specialty_news where user_id=".$user_id." and display_popup=1";
	$result = $CI->db->query($sql)->result_array();
	if(count($result)>0) {
		$user = $result[0]['id'];
	}
	if($user!='')
		return 1;
	else
		return 0;
}
function check_coder($product_id){
$arr_coders = array("326"=>"Anesthesia Coder",
"303"=>"Cardiology Coder",
"318"=>"Dermatology Coder",
"319"=>"Emergency Medicine Coder",
"312"=>"Family Practice Coder",
"317"=>"Gastroenterology Coder",
"305"=>"General Surgery Coder",
"327"=>"ICD-10 Coder",
"320"=>"Internal Medicine Coder",
"308"=>"Neurology Coder",
"314"=>"Neurosurgery Coder",
"302"=>"Ob-Gyn Coder",
"309"=>"Oncology Coder",
"304"=>"Ophthalmology Coder",
"323"=>"Optometry Coder",
"307"=>"Orthopedic Coder",
"310"=>"Otolaryngology Coder",
"322"=>"Part B Coder",
"321"=>"Pathology Coder",
"306"=>"Pediatric Coder",
"325"=>"Physical Medicine Coder",
"313"=>"Podiatry Coder",
"316"=>"Pulmonology Coder",
"315"=>"Radiology Coder",
"311"=>"Urology Coder",
"121"=>"Cardiology Coder",
"173"=>"Dermatology Coder",
"175"=>"Emergency Medicine Coder",
"160"=>"Family Practice Coder",
"170"=>"Gastroenterology Coder",
"125"=>"General Surgery Coder",
"158"=>"ICD-10 Coder",
"177"=>"Internal Medicine Coder",
"130"=>"Neurology Coder",
"164"=>"Neurosurgery Coder",
"119"=>"Ob-Gyn Coder",
"132"=>"Oncology Coder",
"123"=>"Ophthalmology Coder",
"183"=>"Optometry Coder",
"128"=>"Orthopedic Coder",
"134"=>"Otolaryngology Coder",
"181"=>"Part B Coder",
"179"=>"Pathology Coder",
"127"=>"Pediatric Coder",
"187"=>"Physical Medicine Coder",
"162"=>"Podiatry Coder",
"168"=>"Pulmonology Coder",
"166"=>"Radiology Coder",
"136"=>"Urology Coder",
"53"=>"Anesthesia Coder (Trial)",
"55"=>"Physician Coder (Trial)",
"120"=>"Ob-Gyn Coder (Trial)",
"122"=>"Cardiology Coder (Trial)",
"124"=>"Ophthalmology Coder (Trial)",
"126"=>"General Surgery Coder (Trial)",
"129"=>"Orthopedic Coder (Trial)",
"131"=>"Neurology Coder (Trial)",
"133"=>"Oncology Coder (Trial)",
"135"=>"Otolaryngology Coder (Trial)",
"137"=>"Urology Coder (Trial)",
"159"=>"ICD-10 Coder (Trial)",
"161"=>"Family Practice Coder (Trial)",
"163"=>"Podiatry Coder (Trial)",
"165"=>"Neurosurgery Coder (Trial)",
"167"=>"Radiology Coder (Trial)",
"169"=>"Pulmonology Coder (Trial)",
"171"=>"Gastroenterology Coder (Trial)",
"174"=>"Dermatology Coder (Trial)",
"176"=>"Emergency Medicine Coder (Trial)",
"178"=>"Internal Medicine Coder (Trial)",
"180"=>"Pathology Coder (Trial)",
"182"=>"Part B Coder (Trial)",
"184"=>"Optometry Coder (Trial)",
"186"=>"Medical Office Biller Coder (Trial)",
"188"=>"Physical Medicine Coder (Trial)",
"190"=>"Pediatric Coder (Trial)"
/*"406"=>"Anesthesia Coder",
"410"=>"Cardiology Coder",
"425"=>"Dermatology Coder",
"407"=>"DRG Coder",
"426"=>"Emergency Medicine Coder",
"419"=>"Family Practice Coder",
"424"=>"Gastroenterology Coder",
"412"=>"General Surgery Coder",
"408"=>"ICD-10 Coder",
"427"=>"Internal Medicine Coder",
"415"=>"Neurology Coder",
"421"=>"Neurosurgery Coder",
"409"=>"Ob-Gyn Coder",
"416"=>"Oncology Coder",
"411"=>"Ophthalmology Coder",
"430"=>"Optometry Coder",
"414"=>"Orthopedic Coder",
"417"=>"Otolaryngology Coder",
"405"=>"Outpatient Facility Coder",
"429"=>"Part B Coder",
"428"=>"Pathology Coder",
"413"=>"Pediatric Coder",
"432"=>"Physical Medicine Coder",
"423"=>"Pulmonology Coder",
"422"=>"Radiology Coder",
"418"=>"Urology Coder"*/);
if(array_key_exists($product_id,$arr_coders))
		return true;
else
	return false;
}
function is_manager($user_id)
{
	$CI =& get_instance();
	$sql = "select id from subuser_product where user_id = ".$user_id." AND user_type='Subgroup' limit 1";
	$result = $CI->db->query($sql)->result_array();
	if(count($result)>0)
	{
		return true;
	}else
	{
		return false;
	}
}
function set_user_preference($userid, $pref_key, $pref_val)
{
	$CI =& get_instance();
	$sql = "select id from user_preference where user_id = ".$userid." AND user_pref ='".$pref_key."' limit 1";
	$result = $CI->db->query($sql)->result_array();
	if(count($result)>0)
	{
		$user_prefence_data = array(
		'user_pref_value' => $pref_val,
		);
		$CI->db->where('user_id',$userid);
		$CI->db->where('user_pref',$pref_key);
		$CI->db->update('user_preference',$user_prefence_data);
		//return true;
	}
	else
	{
		$user_prefence_data = array(
		'user_pref_value' => $pref_val,
		'user_id' => $userid,
		'user_pref' => $pref_key,
		);
		$CI->db->insert('user_preference',$user_prefence_data);
		//return false;
	}
}
function get_user_preference($userid, $pref_key)
{
$CI =& get_instance();
	$sql = "select user_pref_value from user_preference where user_id = ".$userid." AND user_pref ='".$pref_key."' limit 1";
	$result = $CI->db->query($sql)->result_array();
	if(count($result)>0)
	{
		return $result[0]['user_pref_value'];
	}
	else
	{
		return false;
	}
}
function update_userperms($products_id,$user_id)
	{
			$CI =& get_instance();
			$product_id_string='';
			$master_product_id='';
			$master_product_id_string='';
			$product_array=array();
			$user_product_perms=array();				
			
			$master_product_id_new='';			
			if(!empty($products_id) && !empty($user_id))
				{
					$sql="select master_product_ids from products where product_id=$products_id limit 1";
					$query=$CI->db->query($sql);
					$result=$query->result_array();	// Product Data	Array
					if($result[0]['master_product_ids'])
						{
							$master_product_id_new=$result[0]['master_product_ids'].',';
						}
					$sql_user="select * from user_perms where user_id=$user_id limit 1";
					$query=$CI->db->query($sql_user);
					$result_user=$query->result_array();	// Product Data	Array
					if(is_array($result_user) && $result_user[0]['user_perm_id'])
						{
							
							$data_perms=array();
							$product_id_string=$result_user[0]['product_ids'].','.$products_id;
							$master_product_id=$result_user[0]['master_product_ids'].','.$master_product_id_new;
							$unique=array();
							$unique_product=array();
							$master_array=array();
							if(!empty($master_product_id))
								{
									$master_array=explode(',',$master_product_id);
									$unique = array_unique($master_array);
									$master_product_id_string=implode(',',$unique);
									$data_perms['master_product_ids']=$master_product_id_string;
								}
							
							if(!empty($product_id_string))
								{
									$product_array=explode(',',$product_id_string);
									$unique_product = array_unique($product_array);
									$product_id_string=implode(',',$unique_product);
									$data_perms['product_ids']=$product_id_string;
								}
						
							$CI->db->where('user_id',$user_id);
							$CI->db->update('user_perms',$data_perms);
						
						}
				}
	
	}
	
function update_elearning_dbOLD($user_id,$courses)
{
	//echo "<pre>".$user_id."#####".$courses;
	$urltopost = "http://elearning.supercoder.com/lb_autoenroll.php";
	$token = base64_encode($user_id."&1");
	$datatopost = array (
	"token" => $token,
	"courses" => $courses
	);
	//print_r($datatopost);

	$ch = curl_init ($urltopost);
	curl_setopt ($ch, CURLOPT_POST, true);
	curl_setopt ($ch, CURLOPT_POSTFIELDS, $datatopost);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	$returndata = curl_exec($ch);
	//echo $data=trim($returndata);
	//die;
	return($data);
}				

function update_elearning_db($user,$courses,$order_data)
{
	//$user = get_userdata($user_order_data['user_id']);
	//print_r($user);die;
	if($user['last_name']=='')
		$user['last_name'] = '  ';
	$context_course_array = array(1=>15,
		4=>266,
		7=>304,
		10=>311,
		13=>316,
		16=>459,
		19=>494,
		22=>499,
		25=>528,
		28=>533,
		31=>538,
		37=>548,
		55=>611,
		62=>633,
		64=>698,
		73=>747,
		75=>795,
		);			
	$CI =& get_instance();
	$DB2 = $CI->load->database('elearning', TRUE);
	$sql = "select * from mdl_user where username='".$user['user_name']."'";
	$result = $DB2->query($sql)->result_array();
	if(count($result)>0)
	{
		//print_r($result);die;
		$pass=md5($user['password']);
		$uid = $result[0]['id'];
		$sqlupdatepassword="update mdl_user set password='".$pass."',email='".$user['email']."' where id='".$uid."'";
		$resupdate=$DB2->query($sqlupdatepassword);
		if($resupdate)
		{
			$enrol_array= explode(',',$courses);//array("1");//,"22","25","28","31","37","42");
			$enroll = array_unique($enrol_array);
			foreach($enroll as $val)
			{
				$checksql = "select * from mdl_user_enrolments where userid='".$uid."' and enrolid='".$val."'";
				$checkresult = $DB2->query($checksql)->result_array();
				if(count($checkresult)>0)
				{
				}else{
					//Course Enrollment
					$sql_user_enrol="insert into mdl_user_enrolments(enrolid,userid,timestart,timecreated,timemodified) values($val,$uid,unix_timestamp(),unix_timestamp(),unix_timestamp())";
					$resuserenrol=$DB2->query($sql_user_enrol);

					//Quiz Enrollment
					$sql_quiz_enrol="insert into mdl_role_assignments(`roleid`, `contextid`, `userid`, `timemodified`, `modifierid`, `component`, `itemid`, `sortorder`) values(5,$context_course_array[$val],$uid,unix_timestamp(),'2','','0','0')";
					$resquizenrol=$DB2->query($sql_quiz_enrol);
					
				}
			}						
		}						
	}
	else
	{
		$pass=md5($user['password']);
		$sqlinsert="insert into mdl_user(username,password,firstname,lastname,email,city,country,auth,confirmed,mnethostid) values('".$user['user_name']."','$pass','".$user['first_name']."','".$user['last_name']."','".$user['email']."','".$order_data['payment_city']."','".$order_data['payment_country']."','manual','1','1')";
		$resinsert=$DB2->query($sqlinsert);
		$uid=$DB2->insert_id();
		if($uid)
		{
			$enrol_array= explode(',',$courses);//array("1");//,"22","25","28","31","37","42");
			$enroll = array_unique($enrol_array);
			foreach($enroll as $val)
			{
				$checksql = "select * from mdl_user_enrolments where userid='".$uid."' and enrolid='".$val."'";
				$checkresult = $DB2->query($checksql)->result_array();
				if(count($checkresult)>0)
				{
				}else{
					//Course Enrollment
					$sql_user_enrol="insert into mdl_user_enrolments(enrolid,userid,timestart,timecreated,timemodified) values($val,$uid,unix_timestamp(),unix_timestamp(),unix_timestamp())";
					$resuserenrol=$DB2->query($sql_user_enrol);
					//Quiz Enrollment
					$sql_quiz_enrol="insert into mdl_role_assignments(`roleid`, `contextid`, `userid`, `timemodified`, `modifierid`, `component`, `itemid`, `sortorder`) values(5,$context_course_array[$val],$uid,unix_timestamp(),'2','','0','0')";
					$resquizenrol=$DB2->query($sql_quiz_enrol);
				}
			}						
		}
	}

}				


function get_elearning_enroll_ids($product_id)
{
$CI =& get_instance();
	$sql = "select courses_enrolls from products_elearning_courses where product_id = ".$product_id." limit 1";
	$result = $CI->db->query($sql)->result_array();
	if(count($result)>0)
	{
		return $result[0]['courses_enrolls'];
	}
	else
	{
		return false;
	}
}

function get_elearning_enroll_ids_multi($product_ids)
{
$CI =& get_instance();
	$sql = "select courses_enrolls from products_elearning_courses where product_id IN ( ".$product_ids.") limit 1";
	$result = $CI->db->query($sql)->result_array();
	if(count($result)>0)
	{
		return $result[0]['courses_enrolls'];
	}
	else
	{
		return false;
	}
}		

function update_elearning_login($user_id)
{
	//echo "<pre>".$user_id."#####".$courses;
	$urltopost = "http://elearning.supercoder.com/lb_autoupdate.php";
	$token = base64_encode($user_id."&1");
	$datatopost = array (
	"token" => $token
	);
	//print_r($datatopost);

	$ch = curl_init ($urltopost);
	curl_setopt ($ch, CURLOPT_POST, true);
	curl_setopt ($ch, CURLOPT_POSTFIELDS, $datatopost);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	$returndata = curl_exec($ch);
	//echo $data=trim($returndata);
	//die;
	return($data);
}

	
function get_users_bucks($user_id)
{
$CI =& get_instance();
	$sql = "select bucks from users_referals_bucks where user_id = ".$user_id." limit 1";
	$result = $CI->db->query($sql)->result_array();
	if(count($result)>0)
	{
		return $result[0]['bucks'];
	}
	else
	{
		return false;
	}
}	

	function reduce_user_bucks($user_id, $bucks)
	{
		$CI =& get_instance();
		$sql = "select * from users_referals_bucks where user_id = ".$user_id." limit 1";
		$result = $CI->db->query($sql)->result_array();
		if(count($result)>0)
		{
			$users_referals_data = array(
			'bucks' => $result[0]['bucks']-$bucks
			);
			$CI->db->where('user_id',$user_id);
			$CI->db->update('users_referals_bucks',$users_referals_data);
			return true;
		}
		return false; 
	}

	/*This function update supercoder bucks when receives and also when used*/
	function update_supercoder_bucks($user_id,$order_id,$net_amt,$bucks_used=0,$ref_user_id='')
	{
			$CI =& get_instance();
		if(!empty($user_id) && !empty($order_id) && !empty($net_amt) )
			{
					$amount=ceil($net_amt);
					$bucks = 0;
					$status = 1;
				//if($amount>=$rewards_array[0])
				//	{
						//$number=$amount/$rewards_array[0];
						//$total_reward_points=floor($number)*$rewards_array[1] ;
						if($bucks_used==0) /*when receives*/
							$bucks = $net_amt*(0.1);
							
						if($ref_user_id!='')
							$status = 0; /*when receives*/

							if($bucks>0 || $bucks_used>0)
							{
								  $add_date=date('Y-m-d h:i:s'); // Current Date
								$data_array=array('user_id'=>$user_id,'order_id'=>$order_id,'order_amount'=>$net_amt,'bucks_received'=>$bucks,'bucks_used'=>$bucks_used,'ref_user_id'=>$ref_user_id,'date_created'=>$add_date,'date_modified'=>$add_date,'status'=>$status);
								$CI->db->insert('users_referals_bucks',$data_array);
								return true;
							}
				//	}

			}
		return false;
		
	}	
	
	function get_supercoder_bucks($user_id)
	{
		$CI =& get_instance();
		$arr_user_prods ='';
		$sql = "select sum(bucks_received) as total_points,sum(bucks_used) as used_points from users_referals_bucks where user_id = ".$user_id." AND status=1";
		$result = $CI->db->query($sql)->result_array();
		//print_r($result);die;
		if(count($result)>0){
			$user_points = $result[0];
		}else
		{
			$user_points = false;
		}
		return $user_points;
	}	
	
	function get_supercoder_bucks_history($user_id)
	{
		$CI =& get_instance();
		$arr_user_prods ='';
		$sql = "select b.*,u.email from users_referals_bucks b
		join users u on b.ref_user_id=u.user_id
		where b.user_id = ".$user_id." AND b.status=1 order by b.date_created desc";
		$result = $CI->db->query($sql)->result_array();
		//print_r($result);die;
		if(count($result)>0){
			$user_points = $result;
		}else
		{
			$user_points = false;
		}
		return $user_points;
	}		

	function user_id_encode($user_id)
	{
		$char_array = array('0'=>'Z','1'=>'V','2'=>'T','3'=>'B','4'=>'F','5'=>'G','6'=>'X','7'=>'S','8'=>'P','9'=>'N');
		$num_array = array_flip($char_array);
		$nums = str_split($user_id);	
		$encript = '';
		if(is_array($nums))
		{
			foreach($nums as $num)
				$encript .=$char_array[$num];
		}
		return $encript;
	}

	function user_id_decode($encript)
	{
		$char_array = array('0'=>'Z','1'=>'V','2'=>'T','3'=>'B','4'=>'F','5'=>'G','6'=>'X','7'=>'S','8'=>'P','9'=>'N');
		$num_array = array_flip($char_array);
		$chars = str_split($encript);	
		$decript = '';
		if(is_array($chars))
		{
			foreach($chars as $char)
			{
				if(isset($num_array[$char]))
				$decript .=$num_array[$char];
			}
		}
		return $decript;
	}

	
	function is_referal_key_valid($encript)
	{
		$user_id = user_id_decode($encript);
		$logged_in_user = get_current_user_id();
		if(!empty($user_id))
		{
			$CI =& get_instance();
				$sql="select user_name 
				from users 
				where user_id=$user_id  
				";
			$query=$CI->db->query($sql);
			$result=$query->result_array();	
			if(count($result)>0)
			{
				if($logged_in_user)
				{
					if($logged_in_user!=$user_id)
						return true;
					else
						return false;
				}
			
				return true;
			}else
			{
				return false;
			}
		}
		return false;
	}	
	
			
function deactive_product_article($product_id,$user_id)
		{
			$purchased_product_name='';
			$trial_product_name='';
			$affected_rows=0;
		
			if(!empty($product_id) && !empty($user_id))
				{
					$CI =& get_instance();
					$sql_get_masterids="select group_concat(master_product_ids) as mids,group_concat(product_name) as pname from products where product_id in ($product_id)";
					$query_matser=$CI->db->query($sql_get_masterids);
					$results=$query_matser->result_array();	
					$checktrial="select * from order_masters where is_trial=1 and active=1 and user_id=$user_id";
					$query_checktrial=$CI->db->query($checktrial);
					$results_trial=$query_checktrial->result_array();	
					$trial=0;
					$trial_deactivate=0;
					if($results_trial && count($results_trial)>0 && isset($results_trial[0]['product_id']))
							{
								$trial=1;
								$trial_product_name=$results_trial[0]['product_name'];
							}
					if($results && $trial==1 && count($results)>0 && isset($results[0]['mids']) && !empty($results[0]['mids']))
						{
						
							$master_ids_array=array();
							$common_array=array();
							$master_ids_array=array_unique(explode(',',$results[0]['mids']));
							$specialty_array=array(29,32,33,34,35,37,62,38,63,39,40,41,42,43,45,44,46,47,49,50,51,36,30,48,31,54,53,52,69,71,70,72,73,74,75,76,77);
							$common_array=array_intersect($specialty_array,$master_ids_array);
							$purchased_product_name=$results[0]['pname'];
							
									
							if(count($common_array)>0)
								{
									$sqlOM="select om.product_id,p.master_product_ids,p.product_name from order_masters om join products p on p.product_id=om.product_id
									where om.is_trial=1 and om.active=1 and om.user_id=$user_id";
									$query_trial=$CI->db->query($sqlOM);
									$results_masters=$query_trial->result_array();	
									if(count($results_masters)>0)
										{
											foreach($results_masters as $res)
												{
													if(in_array($res['master_product_ids'],$common_array))
														{
															$sql="update order_masters set active=0,is_recurring=0 where user_id=$user_id and product_id =$res[product_id]
																and is_trial=1 and active=1";
															$query=$CI->db->query($sql);
															$affected_rows=$CI->db->affected_rows();
															if($affected_rows>0)
																{
																
																	$trial_deactivate=1;
																}
												
															$sql_update_access_history="update user_trial_access_history set is_active=0 where user_id=$user_id 
																and trial_product_id=$res[product_id] and is_active=1"; 
															$query_history=$CI->db->query($sql_update_access_history);
														}
												}
										}
								}
							
								$sql="update order_masters set active=0,is_recurring=0 where user_id=$user_id and product_id in ($product_id) and is_trial=1 and active=1";
								$querynew=$CI->db->query($sql);
								$affected_rows=$CI->db->affected_rows();
								if($affected_rows>0)
									{
										
										$trial_deactivate=1;
									}
								
								$sql_update_access_history="update user_trial_access_history set is_active=0 where user_id=$user_id and trial_product_id in ($product_id) and is_active=1"; 
								$query_history=$CI->db->query($sql_update_access_history);
								if($trial_deactivate==1)
									{
									
										return array($purchased_product_name,$trial_product_name);
									}
						}
					
				}
			return false;
		}
				
function deactive_product_article_test($product_id,$user_id)
	{
			$purchased_product_name='';
			$trial_product_name='';
			$affected_rows=0;
			if(!empty($product_id) && !empty($user_id))
				{
					$CI =& get_instance();
					$sql_get_masterids="select group_concat(master_product_ids) as mids,group_concat(product_name) as pname from products where product_id in ($product_id)";
					$query_matser=$CI->db->query($sql_get_masterids);
					$results=$query_matser->result_array();	
					$checktrial="select * from order_masters where is_trial=1 and active=1 and user_id=$user_id";
					$query_checktrial=$CI->db->query($checktrial);
					$results_trial=$query_checktrial->result_array();	
					$trial=0;
					$trial_deactivate=0;
					if($results_trial && count($results_trial)>0 && isset($results_trial[0]['product_id']))
							{
								$trial=1;
								$trial_product_name=$results_trial[0]['product_name'];
							}
					if($results && $trial==1 && count($results)>0 && isset($results[0]['mids']) && !empty($results[0]['mids']))
						{
						
							$master_ids_array=array();
							$common_array=array();
							$master_ids_array=array_unique(explode(',',$results[0]['mids']));
							$specialty_array=array(29,32,33,34,35,37,62,38,63,39,40,41,42,43,45,44,46,47,49,50,51,36,30,48,31,54,53,52,69,71,70,72,73,74,75,76,77);
							$common_array=array_intersect($specialty_array,$master_ids_array);
							$purchased_product_name=$results[0]['pname'];
							echo "Master Product IDS";
							echo "<br>";
							print_r($master_ids_array);
							echo "<br>";
							echo "Common Product IDS";
							echo "<br>";
							print_r($common_array);
									
							if(count($common_array)>0)
								{
									
									$sqlOM="select om.product_id,p.master_product_ids,p.product_name from order_masters om join products p on p.product_id=om.product_id
									where om.is_trial=1 and om.active=1 and om.user_id=$user_id";
									$query_trial=$CI->db->query($sqlOM);
									$results_masters=$query_trial->result_array();	
									if(count($results_masters)>0)
										{
											foreach($results_masters as $res)
												{
													if(in_array($res['master_product_ids'],$common_array))
														{
															$sql="update order_masters set active=0,is_recurring=0 where user_id=$user_id and product_id =$res[product_id]
																and is_trial=1 and active=1";
															$query=$CI->db->query($sql);
															$affected_rows=$CI->db->affected_rows();
															if($affected_rows>0)
																{
																
																	$trial_deactivate=1;
																}
												
															$sql_update_access_history="update user_trial_access_history set is_active=0 where user_id=$user_id 
																and trial_product_id=$res[product_id] and is_active=1"; 
															$query_history=$CI->db->query($sql_update_access_history);
														}
												}
										}
								}
							
								$sql="update order_masters set active=0,is_recurring=0 where user_id=$user_id and product_id in ($product_id) and is_trial=1 and active=1";
								$querynew=$CI->db->query($sql);
								$affected_rows=$CI->db->affected_rows();
								if($affected_rows>0)
									{
										
										$trial_deactivate=1;
									}
								
								$sql_update_access_history="update user_trial_access_history set is_active=0 where user_id=$user_id and trial_product_id in ($product_id) and is_active=1"; 
								$query_history=$CI->db->query($sql_update_access_history);
								if($trial_deactivate==1)
									{
									
										return array($purchased_product_name,$trial_product_name);
									}
								
						}
					
				}
			return false;
	}	
	function is_valid_forgot_password($access_code,$user_id)
	{
		if(!empty($user_id) && !empty($access_code))
		{
			$CI =& get_instance();
				$sql="select access_code 
				from users_forgot_password 
				where user_id=$user_id  
				";
			$query=$CI->db->query($sql);
			$result=$query->result_array();	
			//print_r($result);die;
			if(count($result)>0)
			{
				if(isset($result[0]['access_code']))
				{
					if($access_code==$result[0]['access_code'])
						return true;
					else
						return false;
				}
			
				return false;
			}else
			{
				return false;
			}
		}
		return false;
	}

	function is_valid_access_code($access_code,$user_id)
	{
		if(!empty($user_id) && !empty($access_code))
		{
			$CI =& get_instance();
				$sql="select access_code 
				from users_forgot_password 
				where user_id=$user_id  
				";
			$query=$CI->db->query($sql);
			$result=$query->result_array();	
			//print_r($result);die;
			if(count($result)>0)
			{
				if(isset($result[0]['access_code']))
				{
					if($access_code==$result[0]['access_code'])
						return true;
					else
						return false;
				}
			
				return false;
			}else
			{
				return false;
			}
		}
		return false;
	}
	
function update_user_order_perms($user_id,$order_id){	
	if(!empty($user_id) && !empty($order_id))
		{
			$CI =& get_instance();
			$sql="select group_concat(distinct p.product_id) as pids,group_concat(distinct p.master_product_ids) as mids from order_masters om join products p on om.product_id=p.product_id 
				  where om.order_id=$order_id and om.user_id=$user_id and om.resource_type='subs' group by order_id";
			$query=$CI->db->query($sql);
			$result=$query->result_array();	
			if($result && count($result)>0 && is_array($result[0]) && count($result[0])==2)
				{
					$product_id=$result[0]['pids'];
					$master_product_id=$result[0]['mids'];
					if(!empty($master_product_id))
						{
							$sql_user_perms="select * from user_perms where user_id=$user_id";
							$queryuser=$CI->db->query($sql_user_perms);
							$resultUser=$queryuser->result_array();	
							if($resultUser && count($resultUser)>0 && is_array($resultUser[0]))
								{
									$array_master=array();
									$array_products=array();
									$master_product_id=$master_product_id.','.$resultUser[0]['master_product_ids'];
									$product_id=$product_id.','.$resultUser[0]['product_ids'];
									$array_master=array_filter(array_unique(explode(',',$master_product_id)));
									$master_product_string=implode(',',$array_master);
									
									$array_products=array_filter(array_unique(explode(',',$product_id)));
									$product_string=implode(',',$array_products);
									$data_save=array();
									$data_save['product_ids']=$product_string;
									$data_save['master_product_ids']=$master_product_string;
									$CI->db->where('user_id',$user_id);
									$CI->db->update('user_perms',$data_save);
									
								}
							else
								{
									$array_master=array();
									$array_master=array_unique(explode(',',$master_product_id));
									$master_product_string=implode(',',$array_master);
									$data_save=array();
									$data_save['product_ids']=$product_id;
									$data_save['master_product_ids']=$master_product_string;
									$data_save['user_id']=$user_id;
									$CI->db->insert('user_perms',$data_save);									
								}
								update_user_subscription_status($user_id);
						}
				}
		}
}
function check_trial_coder_access()
	{
		if(isset($_SESSION['user']['user_id']) && !empty($_SESSION['user']['user_id']))
			{
				$uid='';
				$uid=$_SESSION['user']['user_id'];
				$unset_master_specialty_id='';
				if(!empty($uid))
					{
						$CI =& get_instance();
						$coder_specialty=$CI->config->item('coder_specialty');
						$sqltrial="select trial_product_id from user_trial_access_history where is_active=1 and user_id=$uid";
						$query=$CI->db->query($sqltrial);
						$result=$query->result_array();	
						//print_r($result);die;
						if(count($result)>0)
						{
							if(isset($result[0]['trial_product_id']))
							{
								$trial_id=$result[0]['trial_product_id'];
								if(!empty($trial_id) && array_key_exists($trial_id,$coder_specialty))
									{					
										$unset_master_specialty_id=$coder_specialty[$trial_id];
										return $unset_master_specialty_id;
									}
								else
									{
										return false;
									}
								
							}
							else
								{
									return false;
								}
						}
						else
						{
							return false;
						}
					}
			} else {
				return FALSE;
			}
	
		return FALSE;
	}	
function check_old_ondemand_user($user_id)
{
	$CI =& get_instance();
	$CI->db->select('*');
	$CI->db->from('user_expert_packs');
	$CI->db->where('user_id',$user_id);
	$CI->db->where('research_areas',NULL);
	$CI->db->where('specialties',NULL);
	$CI->db->where('word_count',NULL);
	$CI->db->where('remaining_question >',0);
	$query=$CI->db->get()->result_array();
	//echo $CI->db->last_query();
	if(is_array($query) && !empty($query))
		return true;
	else
		return false;
}

function user_tracking_page($title='',$url='',$type='',$pkey='',$extra='',$status=1,$user_id='') {
    //$CI =& get_instance();
    //$current_user = $CI->get_current_user();
	$CI =& get_instance();
	$fsource='';
	$source='';				
	$finalsource='';
	$finalsource2='';
	$rsource='';
	if($CI->session->userdata('fsource')){
		$fsource=$CI->session->userdata('fsource');
	}
	if($CI->session->userdata('source')){
		$source=$CI->session->userdata('source');
	}
	if($CI->session->userdata('rsource')){
		$rsource=$CI->session->userdata('rsource');
	}
	if(!empty($fsource) && $fsource=='PPC'){
		$finalsource='PPC';
		$finalsource2='PPC';
	}
	else if(!empty($fsource) && !empty($source)){
		$finalsource=$fsource;
		$finalsource2=$fsource.'-'.$source;
	}
	else if(!empty($fsource)){
		$finalsource=$fsource;
		$finalsource2=$fsource;
	}
	if($type=='article404'){
		$cutomer_ip='';
		$cutomer_ip=$CI->input->ip_address();
		//user_visit_tracking
		$data_array=array('user_id'=>'','user_keyword'=>addslashes('article404'),'user_url'=>$url,'ip'=>$cutomer_ip,'user_browser'=>$_SERVER['HTTP_USER_AGENT'],'page_type'=>$type,'pkey'=>$pkey,'category'=>$extra,'status'=>$status,'source'=>$finalsource2);
		if($cutomer_ip!='115.111.70.250'){
			$CI->db->insert('user_visit_tracking',$data_array);
		}
	
	}
	else if(isset($_SESSION['user']['user_id']) && !empty($_SESSION['user']['user_id'])){
		$cutomer_ip='';
		$cutomer_ip=$CI->input->ip_address();
		//user_visit_tracking
		$data_array=array('user_id'=>$_SESSION['user']['user_id'],'user_keyword'=>addslashes($title),'user_url'=>$url,'ip'=>$cutomer_ip,'user_browser'=>$_SERVER['HTTP_USER_AGENT'],'page_type'=>$type,'pkey'=>$pkey,'category'=>$extra,'status'=>$status,'source'=>$finalsource2);
		if($cutomer_ip!='115.111.70.250'){
			$CI->db->insert('user_visit_tracking',$data_array);
		}
	}
	else if(!empty($user_id)){
		$cutomer_ip='';
		$cutomer_ip=$CI->input->ip_address();
		//user_visit_tracking
		$data_array=array('user_id'=>$user_id,'user_keyword'=>addslashes($title),'user_url'=>$url,'ip'=>$cutomer_ip,'user_browser'=>$_SERVER['HTTP_USER_AGENT'],'page_type'=>$type,'pkey'=>$pkey,'category'=>$extra,'status'=>$status,'source'=>$finalsource2);
		if($cutomer_ip!='115.111.70.250'){
			$CI->db->insert('user_visit_tracking',$data_array);
		}	
	}
		
}
function get_search_history($limit=10) {
		if(isset($_SESSION['user']['user_id']) && !empty($_SESSION['user']['user_id']))
		{
			$CI =& get_instance();
			$sqlhistory="select user_keyword,user_url from user_visit_tracking where page_type='search' and user_id='".$_SESSION['user']['user_id']."' and status='1' group by user_keyword order by user_date desc limit ".$limit;
			$query=$CI->db->query($sqlhistory);
			$result=$query->result_array();	
			//print_r($result);die;
			if(count($result)>0)
			{
				return $result;
			}else{
				return false;
			}
		
		} else {
			return false;
		}
}
function get_news_posts($page,$limit=1) {
	$CI =& get_instance();
	$scc_news="select * from scc_news where news_page like '%cpt_asst%' and display_flag=1 order by sort_order desc limit $limit";
	$query=$CI->db->query($scc_news);
	$result=$query->result_array();	
	if(!empty($result) && is_array($result))
		return $result;
	else
		return false;
}
function get_news_posts_new($page='',$limit=1) {
	$CI =& get_instance();
	if($page!='')
		$scc_news="select * from scc_news where news_page like '%$page%' and display_flag=1 order by sort_order desc limit $limit";
	else
		$scc_news="select * from scc_news where display_flag=1 order by sort_order desc limit $limit";
	$query=$CI->db->query($scc_news);
	$result=$query->result_array();	
	if(!empty($result) && is_array($result))
		return $result;
	else
		return false;
}
function ordersort($orders) 
		{ 	
			$orders_active=array();
			$orders_cheque_pending=array();
			$orders_cheque_expired=array();
			$orders_cheque_rest=array();
			$final_array=array();
			if(is_array($orders) && count($orders)>0)
				{
					foreach($orders as $order)
						{
							if($order->active==1)
								{
									$orders_active[]=$order;
								}
							else if($order->active==3)
								{
									$orders_cheque_pending[]=$order;
								}
							else if($order->active==2)
								{
									$orders_cheque_expired[]=$order;
								}								
							else
								{
									$orders_cheque_rest[]=$order;
								}
						} 
					$final_array=array_merge($orders_active,$orders_cheque_pending,$orders_cheque_expired,$orders_cheque_rest);	
					return $final_array;
					exit;
				}
				
			return $orders;
            //return $retval;
		}
function randomPassword() {
		$pass=array();
		$alphabet = "abcdefghijklmnopqrstuwxyz0123456789";

		for ($i = 0; $i < 8; $i++) {
	
			$n = rand(0, strlen($alphabet)-1);
			//echo $n;
			$pass[$i] = $alphabet[$n];
		}
		return implode('',$pass);
	}		
	
	function get_product_search_categories()
	{
			$CI =& get_instance();
			$sql="select filter_id,filter_name from productfilters ";
			$query=$CI->db->query($sql);
			$result=$query->result_array();	
			if(count($result)>0)
			{
			$newresult = array();
				foreach($result as $r)
				{
					$newresult[$r['filter_id']] = $r['filter_name'];
				}
				return $newresult;
			}
			else
				return FALSE;

	}
function have_trial($user_id)
{
	$CI =& get_instance();
	$sql="select * from order_masters where is_trial=1 and user_id='$user_id' and active=1 and end_date>=curdate() limit 1";
	$query=$CI->db->query($sql);
	$result=$query->result_array();	
	$form_string="";
	if(isset($result) && !empty($result)) {
	$start = time();
	$end = strtotime($result[0]['end_date']);
	$days = ceil(abs($end - $start) / 86400);
		
		$form_string='<form name="trial_cart" method="post" action="/checkout/add_cart" id="trial_cart"><input type="hidden" name="product_price_id" value="'.$result[0]['product_id'].'_'.$result[0]['product_term'].'"><a href="javascript:void(0);" onClick="jQuery(\'#trial_cart\').submit();" class="trial_buy">Trial Days Remaining : <span>'.$days.'</span></a>
		</form>';
	}
	else {
		$form_string='';
	}
	return $form_string;
}	

function get_elerning_tests($product_id,$pre_post='pre')
{
$CI =& get_instance();
	$sql = "select ID from wp_quiz_quiz where source = ".$product_id." and quiz_pre_post='".$pre_post."' limit 1";
	$result = $CI->db->query($sql)->result_array();
	if(count($result)>0)
	{
		return $result[0]['ID'];
	}
	else
	{
		return false;
	}
}
function isset_user_homepage($user_id)
{
	$CI =& get_instance();
	$query=$CI->db->query("SELECT * FROM user_preference WHERE user_pref='customize_home_page' && user_id = $user_id");
	if(is_array($result) && isset($result[0]) && !empty($result[0]))
	{
		return 1;
	}
	else
	{
		return 0;
	}	
}
/*Notification count start for a particular users*/
function notificationCountforLoggedinUser()
{
	$CI =& get_instance();
    $current_user = get_current_user_id();
	$sq="select * from cr_notifications where user_id='$current_user'";
	$res=$CI->db->query($sq);
	if($res->num_rows())
	{
			$rows=$res->result();
			$i=0;
			foreach($rows as $row)
			{
				$noti_id=$row->noti_id;
				if(readedOrNot($noti_id,$current_user,$row->mod_date))
				{
					$i++;
				}
			}
			
			return $i;
	}
	else
	return false;

}
function readedOrNot($noti_id,$user_id,$mod_date)
{
	$CI =& get_instance();
	$where=array('noti_id'=>$noti_id,'user_id'=>$user_id);
	$CI->db->where($where);
	$CI->db->where('read_date >',$mod_date);
	$res=$CI->db->get('cr_read');
	if($res->num_rows()==0)
	{
		return true;
	}
	else
	 return false;
}
/*notification count end here*/
/**
 * Get's the name of user from table
 *
 * @author shankar kumar
 * @return string
 */
function get_user_name_by_user_id($user_id) {
	$CI =& get_instance();
	$sq="select first_name,last_name from users where user_id='$user_id'";
	$rs=$CI->db->query($sq);
	if($rs->num_rows())
	{
    $current_user=$rs->row_array();
	return $current_user['first_name'] .' '. $current_user['last_name'];
	}
	else
	return '';
}
function has_fast_coder_access()
{
	$user_data = get_current_user_data();
	if(empty($user_data)) 
	{ 
		return false;
	}
	else
	{
		$user_data['role_perms'] = array_filter($user_data['role_perms']);
		$product_id = $user_data['role_perms'][0]->product_ids;
		if($product_id != '254')
		{
			return false;
		}
		else
		{
			return true;
		}
	}
}
function insert_webinar_registrant($userid, $webid)
{
	$CI =& get_instance();
	$sq="select first_name,last_name, email, telephone,street, city, state, zip,country, company_name from users where user_id='$userid'";
	$rs=$CI->db->query($sq);
	$current_user=$rs->row_array();
	$web_sq = "insert into webinar_registrants(reg_fname,reg_lname,reg_email,reg_phone,reg_webinar_id)
	value('".$current_user['first_name']."','".$current_user['last_name']."','".$current_user['email']."','".$current_user['phone']."','".$webid."')";
	$query=$CI->db->query($web_sq);
	if($query)
		return true;
	else
		return false;
}
function check_webinar_registrant($userid, $webid)
{
	$CI =& get_instance();
	$sq="select email from users where user_id='$userid'";
	$rs=$CI->db->query($sq);
	$current_user=$rs->row_array();
	$web_sq = "select * from webinar_registrants where reg_email='".$current_user['email']."' and reg_webinar_id='".$webid."'";
	$query=$CI->db->query($web_sq)->row_array();
/*	print_r($query);
	die('fsdsf');*/
	if(!empty($query))
		return $query['reg_id'];
	else
		return false;
}
function check_user_status($user)
{
	$CI =& get_instance();
	$sq="select u.user_id, email from users u join user_perms up on u.user_id=up.user_id where up.master_product_ids='' and (email='".$user."' or user_name='".$user."')";
	$rs=$CI->db->query($sq);
	$user=$rs->row_array();
	if(!empty($user))
		return $user['user_id'];
	else
		return false;
}
function check_if_expired($userid)
{
	$CI =& get_instance();
	//$sq="select u.user_id, email from users u join user_perms up on u.user_id=up.user_id where up.master_product_ids='' and (email='".$user."' or user_name='".$user."')";
	$sq="select u.user_id, email from users u join user_perms up on u.user_id=up.user_id where up.master_product_ids='' and u.user_id=".$userid;
	$rs=$CI->db->query($sq);
	$user=$rs->row_array();
	if(!empty($user))
		return $user['user_id'];
	else
		return false;
}
function get_active_webinar()
{
	$CI =& get_instance();
	$sq="select webinar_product_id, webinar_name, webinar_date, webinar_time from webinars where webinar_status='3' and webinar_product_id is not null";
	$rs=$CI->db->query($sq);
	$webinar=$rs->row_array();
	if(!empty($webinar))
		return $webinar;
	else
		return false;
}
function check_if_expired_by_user($user)
{
	$CI =& get_instance();
	$sq="select u.user_id, email from users u join user_perms up on u.user_id=up.user_id where up.master_product_ids='' and (email='".$user."' or user_name='".$user."')";
	$rs=$CI->db->query($sq);
	$user=$rs->row_array();
	if(!empty($user))
		return $user['user_id'];
	else
		return false;
}
function insert_payment_transaction_log($userid, $transaction_data='',$payment_profile_id='')
{
	$CI =& get_instance();
	$web_sq = "insert into user_payment_transaction_log(user_id,transaction_details,payment_profile_id)
	value('".$user_id."','".addslashes($transaction_data)."','".$payment_profile_id."')";
	$query=$CI->db->query($web_sq);
	if($query)
		return true;
	else
		return false;
}