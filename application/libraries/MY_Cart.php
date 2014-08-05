<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


/**
 * Override Shopping Cart Class
 *
  * @author Sunil Punyani
 */
class MY_Cart extends CI_Cart
	{
	var $product_name_rules	= '\"\"\'\&\(\)\/\.\:\;\,\\+\(\)\-_ a-z0-9!'; // alpha-numeric, dashes, underscores, colons or periods
		function MY_Cart($params = array())
			{
				parent::CI_Cart($params);
				if ($this->CI->session->userdata('cart_contents') !== FALSE)
					{
						$this->_cart_contents = $this->CI->session->userdata('cart_contents');
					}
				else
					{
						// No cart exists so we'll set some base values
						$this->_cart_contents['cart_total_discounted'] = 0;
						$this->_cart_contents['total_discount'] = 0;
						$this->_cart_contents['coupon'] = '';
						$this->_cart_contents['error'] = '';
					}
				$this->CI = & get_instance();
			}

// --------------------------------------------------------------------

	/**
	 * Insert items into the cart and save it to the session table
	 *
	 * @access	public
	 * @param	array
	 * @return	bool
	 */
	function insert($items = array())
	{
		// Was any cart data passed? No? Bah...
		if ( ! is_array($items) OR count($items) == 0)
		{
			log_message('error', 'The insert method must be passed an array containing data.');
			return FALSE;
		}

		// You can either insert a single product using a one-dimensional array,
		// or multiple products using a multi-dimensional one. The way we
		// determine the array type is by looking for a required array key named "id"
		// at the top level. If it's not found, we will assume it's a multi-dimensional array.

		$save_cart = FALSE;

		if (isset($items['id']))
		{

			if ($this->_insert($items) == TRUE)
			{
				$save_cart = TRUE;
			}
		}
		else
		{
			foreach ($items as $val)
			{
				if (is_array($val) AND isset($val['id']))
				{
					if ($this->_insert($val) == TRUE)
					{
						$save_cart = TRUE;
					}
				}
			}
		}

		// Save the cart data if the insert was successful
		if ($save_cart == TRUE)
		{
			$this->_save_cart();
			if(isset($this->_cart_contents['coupon']) && is_array($this->_cart_contents['coupon']))
				{
					 $coupon=$this->_cart_contents['coupon']['coupon_code'];
					$this->discount($coupon);
				}
			else
				{
					$this->_cart_contents['total_discount']=0;
					$this->_cart_contents['error']='';
					$this->_cart_contents['cart_total_discounted']=$this->_cart_contents['cart_total'];
				}

			return TRUE;
		}

		return FALSE;
	}
	
	function discount_order($percentage)
		{
		
			if(empty($percentage))
			{
				 $this->_cart_contents['total_discount']=0;
				 $this->_cart_contents['cart_total_discounted']=$this->_cart_contents['cart_total'];
				 $this->_save_cart();
				 log_message('error', 'Invalid Discount');
				 return FALSE;
			}

			$total_discount=0;
			$discount_value=0;
			if($percentage)
				{
					$discount_value=$percentage;					
					$cart_data=$this->_cart_contents;
					$i=0;
					$check=0;
	
					foreach($cart_data as $key=>$items)
						{
							if(is_array($items) && !empty($items['rowid'])  && ($items['options']['is_trial']!='1') && $items['options']['renew']==0 && $items['options']['type']!='multispecialty')
								{
									$check=1;									
									$discount_product=$items['price']-(ceil(($items['price']*$discount_value)/100));
									$total_discount+=ceil(((($items['price']*$discount_value)/100))*$items['qty']);
									if($this->_cart_contents[$items['rowid']]['options']['discounted_price'])
										{
											$this->_cart_contents[$items['rowid']]['options']['discounted_price'] = $discount_product;
										}
									if($this->_cart_contents[$items['rowid']]['options']['type']=='package')
										{
											$discount_price_array=array();
											$addon_price_array=$this->_cart_contents[$items['rowid']]['options']['discounted_price_addone'];
											$base_product_price=$this->_cart_contents[$items['rowid']]['options']['base_price']; 
											$base_discounted_price=$base_product_price-(ceil(($base_product_price*$discount_value)/100));
											$this->_cart_contents[$items['rowid']]['options']['base_discounted_price']=$base_discounted_price;
											if(is_array($addon_price_array) && count($addon_price_array)>0)
												{
												
													foreach($addon_price_array as $key=>$val)
														{
															
															$discount_product=$val-(ceil(($val*$discount_value)/100));
															$discount_price_array[$key]=$discount_product;
														}
													if(count($discount_price_array)>0)
														{
															$this->_cart_contents[$items['rowid']]['options']['discounted_price_addone']=$discount_price_array;		
														}	
												}
											
										}
								}
						}
						//$this->_cart_contents['coupon']=array('coupon_code'=>$coupon,'coupon_discount'=>$discount);
						$this->_cart_contents['error']='';
				}
			else
				{
					$this->_cart_contents['error']='No Discount availavale for this order';
				}
	
			if($this->CI->session->userdata('coder_upgrade'))
				{
					$data_session=$this->CI->session->userdata('coder_upgrade');
					$syncprice=$data_session['syncprice'];					
					if(!empty($syncprice))
						{
							 $total_discount+=ceil(($syncprice*$discount_value)/100);
							
						}
				}

			 $this->_cart_contents['total_discount']=$total_discount;
			 $this->_cart_contents['cart_total_discounted']=$this->_cart_contents['cart_total']-$total_discount;			
			$this->_save_cart();

		}	
/**
	 * Save the cart array to the session DB
	 *
	 * @access	private
	 * @return	bool
	 */
	function _save_cart()
	{
		// Unset these so our total can be calculated correctly below
		unset($this->_cart_contents['total_items']);
		unset($this->_cart_contents['cart_total']);
		// Lets add up the individual prices and set the cart sub-total
		$total = 0;

		foreach ($this->_cart_contents as $key => $val)
		{
			// We make sure the array contains the proper indexes
			//if ( ! is_array($val) OR ! isset($val['price']) OR ! isset($val['qty']))
			if ( ! is_array($val) OR ! isset($val['qty']))
			{
				continue;
			}

			$total += ($val['price'] * $val['qty']);

			// Set the subtotal
			$this->_cart_contents[$key]['subtotal'] = ($this->_cart_contents[$key]['price'] * $this->_cart_contents[$key]['qty']);
		}

		// Set the cart total and total items.
		 $this->_cart_contents['total_items'] = count($this->_cart_contents);
		 $this->_cart_contents['cart_total'] = $total;
		 if($this->CI->session->userdata('coder_upgrade'))
				{
					$data_session=$this->CI->session->userdata('coder_upgrade');
					$total=$total+$data_session['syncprice'];
						
				}	
		if(isset($this->_cart_contents['coupon']) && is_array($this->_cart_contents['coupon']))
			{
				
			}
		else if(isset($this->_cart_contents['total_discount']) && !empty($this->_cart_contents['total_discount']) &&  $total>500)
			{
			
			}
		else
			{
				
				if(!$this->CI->session->userdata('user_referrer'))
				{
					$this->_cart_contents['total_discount']=0;
					$this->_cart_contents['cart_total_discounted']=$this->_cart_contents['cart_total'];
				}
				
			}
		// Is our cart empty?  If so we delete it from the session
		if (count($this->_cart_contents) <= 2)
		{
			$this->CI->session->unset_userdata('cart_contents');

			// Nothing more to do... coffee time!
			return FALSE;
		}

		// If we made it this far it means that our cart has data.
		// Let's pass it to the Session class so it can be stored
		$this->CI->session->set_userdata(array('cart_contents' => $this->_cart_contents));

		// Woot!
		return TRUE;
	}
	function refresh_cart_price()
		{
					// Unset these so our total can be calculated correctly below
				unset($this->_cart_contents['total_items']);
				unset($this->_cart_contents['cart_total']);
				// Lets add up the individual prices and set the cart sub-total
				$total = 0;
				$this->_cart_contents=$array;
			
				foreach ($this->_cart_contents as $key => $val)
				{
					// We make sure the array contains the proper indexes
					//if ( ! is_array($val) OR ! isset($val['price']) OR ! isset($val['qty']))
					if ( ! is_array($val) OR ! isset($val['qty']))
					{
						continue;
					}

					$total += ($val['price'] * $val['qty']);

					// Set the subtotal
					$this->_cart_contents[$key]['subtotal'] = ($this->_cart_contents[$key]['price'] * $this->_cart_contents[$key]['qty']);
				}

				// Set the cart total and total items.
				 $this->_cart_contents['total_items'] = count($this->_cart_contents);
				 $this->_cart_contents['cart_total'] = $total;
				  if($this->CI->session->userdata('coder_upgrade'))
						{
							$data_session=$this->CI->session->userdata('coder_upgrade');
							$total=$total+$data_session['syncprice'];					
						}	
				if(isset($this->_cart_contents['coupon']) && is_array($this->_cart_contents['coupon']))
					{
						
					}
				else if(isset($this->_cart_contents['total_discount']) && !empty($this->_cart_contents['total_discount']) &&  $total>500)
					{
					
					}
				else
					{
						
						if(!$this->CI->session->userdata('user_referrer'))
							{

								$this->_cart_contents['total_discount']=0;
									$this->_cart_contents['cart_total_discounted']=$this->_cart_contents['cart_total'];
							}
						
					}
				$this->_cart_contents['error']='';
				// Is our cart empty?  If so we delete it from the session
				if (count($this->_cart_contents) <= 2)
				{
					$this->CI->session->unset_userdata('cart_contents');

					// Nothing more to do... coffee time!
					return FALSE;
				}

				// If we made it this far it means that our cart has data.
				// Let's pass it to the Session class so it can be stored
				$this->CI->session->set_userdata(array('cart_contents' => $this->_cart_contents));

				// Woot!
				return TRUE;
		
			
		}
	
/**
	 * Save the cart array to the session DB
	 *
	 * @access	private
	 * @return	bool
	 */
	function refresh_cart($array)
	{
		// Unset these so our total can be calculated correctly below
		unset($this->_cart_contents['total_items']);
		unset($this->_cart_contents['cart_total']);
		// Lets add up the individual prices and set the cart sub-total
		$total = 0;
		$this->_cart_contents=$array;
	
		foreach ($this->_cart_contents as $key => $val)
		{
			// We make sure the array contains the proper indexes
			//if ( ! is_array($val) OR ! isset($val['price']) OR ! isset($val['qty']))
			if ( ! is_array($val) OR ! isset($val['qty']))
			{
				continue;
			}

			$total += ($val['price'] * $val['qty']);

			// Set the subtotal
			$this->_cart_contents[$key]['subtotal'] = ($this->_cart_contents[$key]['price'] * $this->_cart_contents[$key]['qty']);
		}

		// Set the cart total and total items.
		 $this->_cart_contents['total_items'] = count($this->_cart_contents);
		 $this->_cart_contents['cart_total'] = $total;
		  if($this->CI->session->userdata('coder_upgrade'))
				{
					$data_session=$this->CI->session->userdata('coder_upgrade');
					$total=$total+$data_session['syncprice'];					
				}	
		if(isset($this->_cart_contents['coupon']) && is_array($this->_cart_contents['coupon']))
			{
				
			}
		else if(isset($this->_cart_contents['total_discount']) && !empty($this->_cart_contents['total_discount']) &&  $total>500)
			{
			
			}
		else
			{
				
						$this->_cart_contents['total_discount']=0;
							$this->_cart_contents['cart_total_discounted']=$this->_cart_contents['cart_total'];
				
			}
		$this->_cart_contents['error']='';
		// Is our cart empty?  If so we delete it from the session
		if (count($this->_cart_contents) <= 2)
		{
			$this->CI->session->unset_userdata('cart_contents');

			// Nothing more to do... coffee time!
			return FALSE;
		}

		// If we made it this far it means that our cart has data.
		// Let's pass it to the Session class so it can be stored
		$this->CI->session->set_userdata(array('cart_contents' => $this->_cart_contents));

		// Woot!
		return TRUE;
	}

/**
	 * Save the cart order adjusted Price for upgraded order
	 *
	 * @access	private
	 * @return	bool
	 */
	function cart_adjusted_amount()
	{
			$cart_data=$this->_cart_contents;				
			$adjusted_amount=0;
			foreach($cart_data as $key=>$items)
				{
					if(is_array($items) && !empty($items['rowid']))
						{
							if(isset($items['options']['product_adjust_price']))
								{
									$adjusted_amount=$adjusted_amount+$items['options']['product_adjust_price'];
								}		
						}
				}
		return $adjusted_amount;
	}
	function cart_check_multispecialty()
	{
			$cart_data=$this->_cart_contents;				
			
			foreach($cart_data as $key=>$items)
				{
					if(is_array($items) && !empty($items['rowid']))
						{
							if(isset($items['options']['type']) && $items['options']['type']=='multispecialty')
								{
									return true;
								}		
						}
				}
		return false;
	}
	function cart_check_book()
	{
			$cart_data=$this->_cart_contents;				
			
			foreach($cart_data as $key=>$items)
				{
					if(is_array($items) && !empty($items['rowid']))
						{
							if(isset($items['options']['type']) && $items['options']['type']=='book')
								{
									return true;
								}		
						}
				}
		return false;
	}
	
	function cart_subs_items()
	{
			$cart_data=$this->_cart_contents;				
			$subs_items_id='';
			foreach($cart_data as $key=>$items)
				{
					if(is_array($items) && !empty($items['rowid']))
						{
							$subs_items_id.=$items['options']['subs_item_id'].',';								
						}
				}
		return rtrim($subs_items_id,',');
	}
	
	/**
	 * Applying Discount on Cart Items
	 *
	 */
	function discount($coupon)
		{
			if(strtoupper($coupon)=='THANKSGIVING')
				{
					$cart_amount_total=0;
					$cart_amount_total=$this->_cart_contents['cart_total'];
					if($cart_amount_total>=150)
						{
							$discount=0;
							if($cart_amount_total>=150 && $cart_amount_total<=250)
								{
									$discount=20;
								}
							else if($cart_amount_total>250)
								{
									$discount=50;
								}
							$total_discount=$discount;	
							$this->_cart_contents['coupon']=array('coupon_code'=>$coupon,'coupon_discount'=>$discount);
							$this->_cart_contents['total_discount']=$total_discount;
							$this->_cart_contents['cart_total_discounted']=$this->_cart_contents['cart_total']-$total_discount;

							$this->_save_cart();
							return;
						}
				}		
		
			
			$product_coupons = array('scr50','ob100','gs100','pb100','dc100','op100','icd100');
			if(empty($coupon))
			{
				log_message('error', 'Please provide a coupon code');
				return FALSE;
			}
			$this->CI->db->select('*')->from('members_coupon');
			$this->CI->db->where('code',$coupon);
			$this->CI->db->where('locked','0');
			$this->CI->db->where('begin_date <=',date('Y-m-d'));
			$this->CI->db->where('expire_date >=',date('Y-m-d'));
			$this->CI->db->limit('1');
			$query=$this->CI->db->get()->result_array();
			$total_discount=0;
			if($query && is_array($query[0]))
				{

					$discount=trim($query[0]['discount']);
					$discount_value=str_replace('%','',$discount);
					if(substr_count($discount,'%')==1)
						{
							$type=1;
						}
					else
						{
							$type=2;
						}
					if(!empty($query[0]['product_id']))
						{
							$products_id=explode(',',$query[0]['product_id']);
						}
					else
						{
							$products_id='';
						}

					$cart_data=$this->_cart_contents;
					$i=0;
					$check=0;

					foreach($cart_data as $key=>$items)
						{

							if(is_array($items) && !empty($items['rowid'])  && ($items['options']['type']!='article') && ($items['options']['is_trial']!='1'))
								{

									if(empty($products_id))
										{
											$check=1;
												//echo $items['id'];
											if($type==1)
												{
													$items['price'];
													$discount_product=$items['price']-(($items['price']*$discount_value)/100);
													$total_discount+=(($items['price']*$discount_value)/100)*$items['qty'];
												}
											else
												{
													$discount_product=$items['price']-$discount_value;
													 $total_discount+=($discount_value*$items['qty']);
												}
												if($this->_cart_contents[$items['rowid']]['options']['discounted_price'])
													{
														$this->_cart_contents[$items['rowid']]['options']['discounted_price'] = $discount_product;
													}
										}
									else if(is_array($products_id) && count($products_id)>0 && in_array($items['id'],$products_id))
											{
												$check=1;
												//echo $items['id'];
												if($type==1)
													{
														$items['price'];
														$discount_product=$items['price']-(($items['price']*$discount_value)/100);
														$total_discount+=(($items['price']*$discount_value)/100)*$items['qty'];
													}
												else
													{
													
														//if((strtolower($coupon)=='scr50' || strtolower($coupon)=='ob100' || strtolower($coupon)=='gs100' || strtolower($coupon)=='pb100' || strtolower($coupon)=='dc100' || strtolower($coupon)=='op100' || strtolower($coupon)=='icd100') && $items['options']['price_term']!='1 Month')
														if((in_array(strtolower($coupon), $product_coupons)) && $items['options']['price_term']!='1 Month')	
															{
																$discount_product=$items['price']-$discount_value;
																$total_discount+=($discount_value*$items['qty']);
															}
														else
															{
																//if(strtolower($coupon)!='scr50' && strtolower($coupon)!='ob100' && strtolower($coupon)!='dc100' && strtolower($coupon)!='op100' && strtolower($coupon)!='gs100' && strtolower($coupon)!='icd100')
																if(!in_array(strtolower($coupon), $product_coupons))
																	{
																		$discount_product=$items['price']-$discount_value;
																		$total_discount+=($discount_value*$items['qty']);
																	}
																else
																	{
																		$discount_product=0;
																		$check=0;
																		$this->_cart_contents['error']='Invalid coupon code';
																	}
															}
													}
													if($this->_cart_contents[$items['rowid']]['options']['discounted_price'])
														{
															$this->_cart_contents[$items['rowid']]['options']['discounted_price'] = $discount_product;
														}
											}
								}
						}


					if($check==0)
						{
							$this->_cart_contents['error']='Coupon is not valid for these products';
						}
					else
						{
							$this->_cart_contents['coupon']=array('coupon_code'=>$coupon,'coupon_discount'=>$discount);
							$this->_cart_contents['error']='';
						}


				}
			else
				{
					$this->_cart_contents['error']='Invalid coupon code';
				}

			$this->_cart_contents['total_discount']=$total_discount;
			$this->_cart_contents['cart_total_discounted']=$this->_cart_contents['cart_total']-$total_discount;

			$this->_save_cart();

		}
		// --------------------------------------------------------------------

	/**
	 * Update the cart
	 *
	 * This function permits the quantity of a given item to be changed.
	 * Typically it is called from the "view cart" page if a user makes
	 * changes to the quantity before checkout. That array must contain the
	 * product ID and quantity for each item.
	 *
	 * @access	public
	 * @param	array
	 * @param	string
	 * @return	bool
	 */
	function update($items = array())
	{

		// Was any cart data passed?
		if ( ! is_array($items) OR count($items) == 0)
		{
			return FALSE;
		}

		// You can either update a single product using a one-dimensional array,
		// or multiple products using a multi-dimensional one.  The way we
		// determine the array type is by looking for a required array key named "id".
		// If it's not found we assume it's a multi-dimensional array
		$save_cart = FALSE;
		if (isset($items['rowid']) AND isset($items['qty']))
		{
			if ($this->_update($items) == TRUE)
			{

				$save_cart = TRUE;
			}
		}
		else
		{
			foreach ($items as $val)
			{
				if (is_array($val) AND isset($val['rowid']) AND isset($val['qty']))
				{
					if ($this->_update($val) == TRUE)
					{

						$save_cart = TRUE;
					}
				}
			}
		}

		// Save the cart data if the insert was successful
		if ($save_cart == TRUE)
		{
			$this->_save_cart();
			if(isset($this->_cart_contents['coupon']) && is_array($this->_cart_contents['coupon']))
				{
					$coupon=$this->_cart_contents['coupon']['coupon_code'];
					$this->discount($coupon);
				}
			return TRUE;
		}

		return FALSE;
	}
// --------------------------------------------------------------------

	/**
	 * Update the cart
	 *
	 * This function permits the quantity of a given item to be changed.
	 * Typically it is called from the "view cart" page if a user makes
	 * changes to the quantity before checkout. That array must contain the
	 * product ID and quantity for each item.
	 *
	 * @access	private
	 * @param	array
	 * @return	bool
	 */
	function _update($items = array())
	{
		// Without these array indexes there is nothing we can do
		if ( ! isset($items['qty']) OR ! isset($items['rowid']) OR ! isset($this->_cart_contents[$items['rowid']]))
		{
			return FALSE;
		}

		// Prep the quantity
		$items['qty'] = preg_replace('/([^0-9])/i', '', $items['qty']);

		// Is the quantity a number?
		if ( ! is_numeric($items['qty']))
		{
			return FALSE;
		}

		// Is the new quantity different than what is already saved in the cart?
		// If it's the same there's nothing to do
		if ($this->_cart_contents[$items['rowid']]['qty'] == $items['qty'])
		{
			return FALSE;
		}

		// Is the quantity zero?  If so we will remove the item from the cart.
		// If the quantity is greater than zero we are updating
		if ($items['qty'] == 0)
		{	
	
			$data=$this->_cart_contents[$items['rowid']];
			$pid=$data['id'];
			if(isset($data['options']) && isset($data['options']['price_id']) && !empty($data['options']['price_id']))
				{
					if($this->CI->session->userdata('coder_terms'))
					{
						
						$price_id=$data['options']['price_id'];
						$key=$pid.'_'.$price_id;
						$data_arr=array();
						$data_arr=unserialize($this->CI->session->userdata('coder_terms'));
					
						if(array_key_exists($key,$data_arr) && count($data_arr)>0)
							{
							
								unset($data_arr[$key]);
								if(count($data_arr)>0)
									{
										$terms=serialize($data_arr);	
										$this->CI->session->set_userdata('coder_terms',$terms);
									}
								else
									{
										$this->CI->session->unset_userdata('coder_terms');
										if($this->CI->session->userdata('coder_upgrade'))
											{
												$this->CI->session->unset_userdata('coder_upgrade');
											}
										if($this->CI->session->userdata('coder_upgrade_discount'))
										{
											$this->CI->session->unset_userdata('coder_upgrade_discount');
										}	
								
										$coupon=$this->CI->cart->coupon_data();
										$total=$this->CI->cart->total();
									
										if(empty($coupon))
											{
												$this->CI->order_value_discount($total);

											}	
									}
									
								
					
							}
						else
							{
							
								$this->CI->session->unset_userdata('coder_terms');
								if($this->CI->session->userdata('coder_upgrade'))
									{
										$this->CI->session->unset_userdata('coder_upgrade');
									}
								if($this->CI->session->userdata('coder_upgrade_discount'))
								{
									$this->CI->session->unset_userdata('coder_upgrade_discount');
								}
									$coupon=$this->CI->cart->coupon_data();
										$total=$this->CI->cart->total();
										if(empty($coupon))
											{
												$this->CI->order_value_discount($total);

											}
							}
							
					}	
					
				}
		
			unset($this->_cart_contents[$items['rowid']]);
		}
		else
		{
	
			$this->_cart_contents[$items['rowid']]['qty'] = $items['qty'];
		
		}

		return TRUE;
	}
	
	function update_price($items = array())
	{

		// Was any cart data passed?
		if ( ! is_array($items) OR count($items) == 0)
		{
			return FALSE;
		}

		// You can either update a single product using a one-dimensional array,
		// or multiple products using a multi-dimensional one.  The way we
		// determine the array type is by looking for a required array key named "id".
		// If it's not found we assume it's a multi-dimensional array
		$save_cart = FALSE;
		if (isset($items['rowid']) AND isset($items['price']))
		{
			if ($this->_update_price($items) == TRUE)
			{

				$save_cart = TRUE;
			}
		}
		else
		{
			foreach ($items as $val)
			{
				if (is_array($val) AND isset($val['rowid']) AND isset($val['price']))
				{
					if ($this->_update_price($val) == TRUE)
					{

						$save_cart = TRUE;
					}
				}
			}
		}

		// Save the cart data if the insert was successful
		if ($save_cart == TRUE)
		{
			$this->_save_cart();
			if(isset($this->_cart_contents['coupon']) && is_array($this->_cart_contents['coupon']))
				{
					$coupon=$this->_cart_contents['coupon']['coupon_code'];
					$this->discount($coupon);
				}
			return TRUE;
		}

		return FALSE;
	}
/**
	 * Update the cart
	 *
	 * This function permits the quantity of a given item to be changed.
	 * Typically it is called from the "view cart" page if a user makes
	 * changes to the quantity before checkout. That array must contain the
	 * product ID and quantity for each item.
	 *
	 * @access	private
	 * @param	array
	 * @return	bool
	 */
	function _update_price($items = array())
	{
		// Without these array indexes there is nothing we can do
		if ( ! isset($items['price']) OR ! isset($items['rowid']) OR ! isset($this->_cart_contents[$items['rowid']]))
		{
			return FALSE;
		}

		// Prep the quantity
	

		// Is the quantity zero?  If so we will remove the item from the cart.
		// If the quantity is greater than zero we are updating
		if ($items['price'] != 0)
		{	
	
			$data=$this->_cart_contents[$items['rowid']];
			$pid=$data['id'];
			if(isset($data['options']) && isset($data['options']['price_id']) && !empty($data['options']['price_id']))
				{
					$this->_cart_contents[$items['rowid']]['price'] = $items['price'];	
					$this->_cart_contents[$items['rowid']]['options']['qty_upgrade'] = 1;	
					
				}
		
			
		}
		
		return TRUE;
	}
	/**
	 * Cart Total
	 *
	 * @access	public
	 * @return	float
	 */
	function total_discount()
	{
		
		return $this->_cart_contents['total_discount'];
	}
	/**
	 * Cart Coupon Data
	 *
	 * @access	public
	 * @return	array
	 */
	function coupon_data()
	{
		if(isset($this->_cart_contents['coupon']))
			{
				return $this->_cart_contents['coupon'];
			}
		else
			{
				return false;
			}
			
	}
	/**
	 * Cart Shippable Product
	 *
	 * @access	public
	 * @return	integer
	 */
	function check_shipping()
	{

		$cart = $this->contents();
		foreach($cart as $items)
			{
				if(isset($items['options']['ship']) && $items['options']['ship']=='1')
					{

						return true;
					}
			}
		return false;
	}
	/**
	 * Cart Discounted Amount or Net Amount
	 *
	 * @access	public
	 * @return	float
	 */
	function discount_amount()
	{
	
		return $this->_cart_contents['cart_total_discounted'];
	}
	/**
	 * Cart Error
	 *
	 * @access	public
	 * @return	integer
	 */
	function cart_error()
	{
		return $this->_cart_contents['error'];
	}

		/**
	 * Cart Contents
	 *
	 * Returns the entire cart array
	 *
	 * @access	public
	 * @return	array
	 */
	function contents()
	{
		$cart = $this->_cart_contents;

		// Remove these so they don't create a problem when showing the cart table
		unset($cart['coupon']);
		unset($cart['error']);
		unset($cart['cart_total_discounted']);
		unset($cart['total_items']);
		unset($cart['total_discount']);
		unset($cart['cart_total']);

		return $cart;
	}
	// --------------------------------------------------------------------

	/**
	 * Insert
	 *
	 * @access	private
	 * @param	array
	 * @return	bool
	 */
	function _insert($items = array())
	{
		// Was any cart data passed? No? Bah...
		if ( ! is_array($items) OR count($items) == 0)
		{
			log_message('error', 'The insert method must be passed an array containing data.');
			return FALSE;
		}

		// --------------------------------------------------------------------

		// Does the $items array contain an id, quantity, price, and name?  These are required
		//if ( ! isset($items['id']) OR ! isset($items['qty']) OR ! isset($items['price']) OR ! isset($items['name']))
		if ( ! isset($items['id']) OR ! isset($items['qty'])  OR ! isset($items['name']))
		{
			log_message('error', 'The cart array must contain a product ID, quantity, price, and name.');
			return FALSE;
		}

		// --------------------------------------------------------------------

		// Prep the quantity. It can only be a number.  Duh...
		$items['qty'] = trim(preg_replace('/([^0-9])/i', '', $items['qty']));
		// Trim any leading zeros
		$items['qty'] = trim(preg_replace('/(^[0]+)/i', '', $items['qty']));

		// If the quantity is zero or blank there's nothing for us to do
		if ( ! is_numeric($items['qty']) OR $items['qty'] == 0)
		{
			return FALSE;
		}

		// --------------------------------------------------------------------

		// Validate the product ID. It can only be alpha-numeric, dashes, underscores or periods
		// Not totally sure we should impose this rule, but it seems prudent to standardize IDs.
		// Note: These can be user-specified by setting the $this->product_id_rules variable.
		if ( ! preg_match("/^[".$this->product_id_rules."]+$/i", $items['id']))
		{
			log_message('error', 'Invalid product ID.  The product ID can only contain alpha-numeric characters, dashes, and underscores');
			return FALSE;
		}

		// --------------------------------------------------------------------

		// Validate the product name. It can only be alpha-numeric, dashes, underscores, colons or periods.
		// Note: These can be user-specified by setting the $this->product_name_rules variable.
		if ( ! preg_match("/^[".$this->product_name_rules."]+$/i", $items['name']))
		{
			log_message('error', 'An invalid name was submitted as the product name: '.$items['name'].' The name can only contain alpha-numeric characters, dashes, underscores, colons, and spaces');
			return FALSE;
		}
		if($items['options']['is_trial']==1)
			{
				$cart_array=array();
				$cart_array=$this->contents();
				if(is_array($cart_array) && count($cart_array)>0)
					{
						foreach($cart_array as $key=>$val)
							{
								if($val['options']['is_trial']==1)
									{
										$this->CI->session->set_userdata('error',"You have already one trial product in your cart");
										
										return false;
									}
							}
					}
			}
		// --------------------------------------------------------------------
		if($items['options']['is_trial']!=1 && $items['options']['is_trial']!=5)
			{
				// Prep the price.  Remove anything that isn't a number or decimal point.
				$items['price'] = trim(preg_replace('/([^0-9\.])/i', '', $items['price']));
				// Trim any leading zeros
				$items['price'] = trim(preg_replace('/(^[0]+)/i', '', $items['price']));
			}
		else
			{
				$items['price']=0;
			}
		// Is the price a valid number?
		if ( ! is_numeric($items['price']) && $items['options']['is_trial']!=1 && $items['options']['is_trial']!=5)
		{
			log_message('error', 'An invalid price was submitted for product ID: '.$items['id']);
			return FALSE;
		}

		// --------------------------------------------------------------------

		// We now need to create a unique identifier for the item being inserted into the cart.
		// Every time something is added to the cart it is stored in the master cart array.
		// Each row in the cart array, however, must have a unique index that identifies not only
		// a particular product, but makes it possible to store identical products with different options.
		// For example, what if someone buys two identical t-shirts (same product ID), but in
		// different sizes?  The product ID (and other attributes, like the name) will be identical for
		// both sizes because it's the same shirt. The only difference will be the size.
		// Internally, we need to treat identical submissions, but with different options, as a unique product.
		// Our solution is to convert the options array to a string and MD5 it along with the product ID.
		// This becomes the unique "row ID"
		if (isset($items['options']) AND count($items['options']) > 0)
		{
			$rowid = md5($items['id'].implode('', $items['options']));
		}
		else
		{
			// No options were submitted so we simply MD5 the product ID.
			// Technically, we don't need to MD5 the ID in this case, but it makes
			// sense to standardize the format of array indexes for both conditions
				$rowid = md5($items['id']);
		}

		// --------------------------------------------------------------------

		// Now that we have our unique "row ID", we'll add our cart items to the master array

		// let's unset this first, just to make sure our index contains only the data from this submission


		if(isset($this->_cart_contents[$rowid]) && $this->_cart_contents[$rowid]['options']['type']!='article' && $this->_cart_contents[$rowid]['options']['is_trial']!='1' && $this->_cart_contents[$rowid]['options']['product_upgrade']!='1' && $this->_cart_contents[$rowid]['options']['qty_upgrade']!='1')
			{

						$this->_cart_contents[$rowid]['qty']=$this->_cart_contents[$rowid]['qty']+$items['qty'];;
						if($this->_cart_contents[$rowid]['qty']>=999)
							{
								$this->_cart_contents[$rowid]['qty']=999;
							}
			}
			else
			{
				// Create a new index with our new row ID
					$this->_cart_contents[$rowid]['rowid'] = $rowid;

					// And add the new items to the cart array
					foreach ($items as $key => $val)
					{
						$this->_cart_contents[$rowid][$key] = $val;
					}
			}



		// Woot!
		return TRUE;
	}
	
	function cart_total_items()
		{
			$cart=$this->contents();
			$this->CI->session->set_userdata('cartcnt',count($cart));
			return count($cart);
		}
	
	function cart_product_array()
		{
				$cart_array=array();
				$cart_array=$this->contents();		
				$result_array=array();
				if(is_array($cart_array) && count($cart_array)>0)
				{
					foreach($cart_array as $key=>$val)
					{
								$result_array[]=$val['id'];							
					}
				}		
			return $result_array;
		}
		
	function cart_trial_product_array()
		{
				$cart_array=array();
				$cart_array=$this->contents();		
				$result_array=array();
				if(is_array($cart_array) && count($cart_array)>0)
				{
					foreach($cart_array as $key=>$val)
					{
						if($val['options']['is_trial']==1)
							{
								$result_array[]=$val['id'];
							}
					}
				}		
			return $result_array;
		}
	function cart_free_webinar_products()
	{
		$cart_array=array();
			$cart_array=$this->contents();		
			$result_array=array();
			if(is_array($cart_array) && count($cart_array)>0)
			{
				foreach($cart_array as $key=>$val)
				{
					if($val['options']['is_trial']==5)
						{
							$result_array[]=$val['id'];
						}
				}
			}		
		return $result_array;
	}
	function cart_custom_array($return_type='')
		{
				$cart_array=array();
				$cart_array=$this->contents();		
				$result_array=array();
				$prdocut_slug_array=array();
				$slug_array=array();
				$ariticle_name='';
				$prdocut_slug_array=$this->CI->config->item('master_article_slug_array');
				$pname='';
				$pnamewithprice='';
				if(is_array($cart_array) && count($cart_array)>0)
				{
					$i=0;
					foreach($cart_array as $key=>$val)
					{
						$ariticle_name='';
						$i++;
						if($val['options']['specialty']!=0)
							{
								if(is_array($prdocut_slug_array) && count($prdocut_slug_array)>0)
									{
										$sid=$val['options']['specialty'];
										$slug_array=array_flip($prdocut_slug_array);
										if(array_key_exists($sid,$slug_array))
											{
												$ariticle_name='Article '.strtoupper($slug_array[$sid]).' : ';
											}
									}	
							}
								$pname.=$ariticle_name.$val['name'].',';
								if($val['options']['is_trial']==1)
									{
										$pnamewithprice.=$ariticle_name.$val['name'].'-$'.$val['options']['discounted_price'].',';
									}
								else
									{
										if($val['options']['type']=='package')
											{
												$addon_name='';
												$addon_array=array();
												$addon_array=$val['options']['addon'];
												if(is_array($addon_array) && count($addon_array)>0)
													{
														$discounted_price_addon=array();													
														foreach($addon_array as $addon_item)
															{

																$addon_arr=explode('_',$addon_item);
																	if(is_array($addon_arr) && count($addon_arr)==4)
																		{
																			$pname=str_replace('-',' ',$addon_arr[2]);
																			$addon_name.=$pname.' ! ';																			
																		}

															}
															$pnamewithprice.=$ariticle_name.$val['name'].'('.rtrim($addon_name,' ! ').')-$'.$val['price'].',';
												
													}
												else
													{
														$pnamewithprice.=$ariticle_name.$val['name'].'-$'.$val['price'].',';
													}
												
											}
										else
											{
												$pnamewithprice.=$ariticle_name.$val['name'].'-$'.$val['price'].',';
											}
											
										
									}
								
								$result_array[$i]['pid']=$val['id'];
							if(isset($val['options']['spec_text']) && $val['options']['spec_text']!='' && $val['id']==1053)
							{
								$result_array[$i]['pname']=$ariticle_name.$val['name'].' '.$val['options']['spec_text'];
							}else{
								$result_array[$i]['pname']=$ariticle_name.$val['name'];
							}
								$result_array[$i]['pqty']=$val['qty'];
								$result_array[$i]['pprice']=$val['subtotal'];								
					}
				}	
				if($return_type=='pname')
					{
						return rtrim($pname,',');
					}
				else if($return_type=='pnamewithprice')
					{
						return rtrim($pnamewithprice,',');
					}	
				else
					{				
						return $result_array;
					}	
		}		
		
	function cart_has_only_trial()
		{
				$cart_array=array();
				$cart_array=$this->contents();		
				if(is_array($cart_array) && count($cart_array)==1)
				{
					foreach($cart_array as $key=>$val)
					{
						if($val['options']['is_trial']==1)
							{
							return true;
							}
					}
				}		
			return false;
		}
	function cart_has_only_singleoff()
		{
				$cart_array=array();
				$cart_array=$this->contents();		
				if(is_array($cart_array) && count($cart_array)>0)
				{

					foreach($cart_array as $key=>$val)
					{
				
						if(strtolower($val['options']['resource_type'])=='subs')
							{
							
								return false;
							}
					}
					return true;
				}
			return false;
		
		}
function cart_corporate()
		{
				$cart_array=array();
				$cart_array=$this->contents();		
				$array_items=array(258,652,259,260,257,256);
				if(is_array($cart_array) && count($cart_array)>0)
				{
					foreach($cart_array as $key=>$val)
					{
				
						if(strtolower($val['options']['resource_type'])=='subs' && !in_array($val['id'],$array_items))
							{
								return true;
							}
					}
					return false;
				}
			return false;
		}
		
function cart_expert_pack()
		{
				$cart_array=array();
				$cart_array=$this->contents();		
				$array_items=array(258,652,259,260,257,256);
				if(is_array($cart_array) && count($cart_array)>0)
				{
					foreach($cart_array as $key=>$val)
					{
				
						if(in_array($val['id'],$array_items))
							{
								return true;
							}
					}
					return false;
				}
			return false;
		}
		
function cart_mulitple_subs_qty()
		{
				$cart_array=array();
				$cart_array=$this->contents();		
				$array_items=array(258,652,259,260,257,256);
				if(is_array($cart_array) && count($cart_array)>0)
				{
					foreach($cart_array as $key=>$val)
					{
				
						if(strtolower($val['options']['resource_type'])=='subs' && !in_array($val['id'],$array_items) && $val['qty']>1)
							{
								return true;
							}
					}
					return false;
				}
			return false;
		}		
function cart_check_storeonly()
	{
			$cart_data=$this->_cart_contents;	
			foreach($cart_data as $key=>$items)
				{
					if(is_array($items) && !empty($items['rowid']))
						{
							if(isset($items['options']['type']) && ($items['options']['type']=='book' || $items['options']['type']=='audio'))
								{
									return true;
								}		
						}
				}
		return false;
	}
function cart_check_mixedshipping()
	{
			$cart_data=$this->_cart_contents;	
			foreach($cart_data as $key=>$items)
				{
					if(is_array($items) && !empty($items['rowid']))
						{
							if(($items['id']>=700 && $items['id']<=710) || $items['id']==653)
								{
									return true;
								}		
						}
				}
		return false;
	}	
	function destroy()
		{
			unset($this->_cart_contents);
			$this->_cart_contents['coupon'] = 0;
			$this->_cart_contents['cart_total'] = 0;
			$this->_cart_contents['total_items'] = 0;
			$this->_cart_contents['error'] = '';
			if($this->CI->session->userdata('coder_terms'))
				{
					$this->CI->session->unset_userdata('coder_terms');
				}	
				if($this->CI->session->userdata('coder_upgrade'))
				{
					$this->CI->session->unset_userdata('coder_upgrade');
				}				
			$this->CI->session->unset_userdata('cart_contents');
		}
	function christmas_coupon()
		{
			$cart_data=$this->_cart_contents;
			$cart_product_array=array();
			foreach($cart_data as $key=>$items)
				{
					if(is_array($items) && !empty($items['rowid']))
						{
							$cart_product_array[]=$items['id'];
						}
				}
			$array_comb1_coder=array(54,121,173,175,160,170,125,158,177,185,884,130,164,119,132,123,183,128,134,181,179,127,187,162,168,166,136);
			$array_comb1_books=array(972,973,974,975,976,977,978,979,980,981,982,983);
			$array_comb2=array(256,884);	
			if(is_array($cart_product_array) && count($cart_product_array)>0)
				{
					$comman_comb2=array_intersect($cart_product_array,$array_comb2);
					$comman_comb1_coder=array_intersect($cart_product_array,$array_comb1_coder);
					$comman_comb1_books=array_intersect($cart_product_array,$array_comb1_books);
					 if(is_array($comman_comb1_coder) && count($comman_comb1_coder)==1 && is_array($comman_comb1_books) && count($comman_comb1_books)==1)
						{
							$cart_amount_total=0;
							$cart_amount_total=$this->_cart_contents['cart_total'];
							if($cart_amount_total>=449)
								{
									$discount=139.95;
									$coupon='CHRIST1';
									$this->apply_custom_coupon($coupon,$discount);									
									return;
								}
							else
								{
									$this->apply_custom_coupon('',0);			
								}
						}
					else if(is_array($comman_comb2) && count($comman_comb2)==2)
						{
							$cart_amount_total=0;
							$cart_amount_total=$this->_cart_contents['cart_total'];
							if($cart_amount_total>=349)
								{
									$discount=150.95;
									$coupon='CHRIST2';
									$this->apply_custom_coupon($coupon,$discount);									
									return;
								}
							else
								{
									$this->apply_custom_coupon('',0);			
								}
						}
					else if(is_array($comman_comb1_books) && count($comman_comb1_books)==1 && in_array(254,$cart_product_array))
						{
							$cart_amount_total=0;
							$cart_amount_total=$this->_cart_contents['cart_total'];
							if($cart_amount_total>=199)
								{
									$discount=64.85;
									$coupon='CHRIST3';
									$this->apply_custom_coupon($coupon,$discount);									
									return;
								}
							else
								{
									$this->apply_custom_coupon('',0);			
								}
						}
					else if(in_array(992,$cart_product_array) && in_array(884,$cart_product_array))
						{
							$cart_amount_total=0;
							$cart_amount_total=$this->_cart_contents['cart_total'];
							if($cart_amount_total>=429)
								{
									$discount=139.95;
									$coupon='CHRIST4';
									$this->apply_custom_coupon($coupon,$discount);									
									return;
								}
							else
								{
									$this->apply_custom_coupon('',0);			
								}
						}
					else if(in_array(992,$cart_product_array) && in_array(843,$cart_product_array))
						{
							$cart_amount_total=0;
							$cart_amount_total=$this->_cart_contents['cart_total'];
							if($cart_amount_total>=308.95)
								{
									$discount=60;
									$coupon='CHRIST5';
									$this->apply_custom_coupon($coupon,$discount);									
									return;
								}
							else
								{
									$this->apply_custom_coupon('',0);			
								}
						}			
					else
						{
							$this->apply_custom_coupon('',0);			
							
						}
				}
			
		}
	function apply_custom_coupon($coupon,$discount)
		{
			if(!empty($coupon))
				{
					$total_discount=$discount;	
					$this->_cart_contents['coupon']=array('coupon_code'=>$coupon,'coupon_discount'=>$discount);
					$this->_cart_contents['total_discount']=$total_discount;
					$this->_cart_contents['cart_total_discounted']=$this->_cart_contents['cart_total']-$total_discount;
				}
			else
				{
					$total_discount=0;	
					$this->_cart_contents['coupon']=array('coupon_code'=>'','coupon_discount'=>'');
					$this->_cart_contents['total_discount']=$total_discount;
					$this->_cart_contents['cart_total_discounted']=$this->_cart_contents['cart_total']-$total_discount;
				}
			$this->_save_cart();
				
		}

}
// END Cart Class

/* End of file MY_Cart.php */
/* Location: ./application/libraries/MY_Cart.php */