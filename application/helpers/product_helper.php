<?php
/*
 * Provides helper functions dealing with products
 */

function get_product_price_terms($product_id) {
	 $CI =& get_instance();
	 $CI->load->model('Price_terms');
	 $price_term_obj = new Price_terms();
	 return $price_term_obj->get_product_price_terms('*',array('product_id'=>$product_id));
}

function get_term_price($term_price_id) {
	$price = 0;
	if($term_price_id > 0) {
		$CI =& get_instance();
		$CI->load->model('Price_terms');
		$price_term_obj = new Price_terms();
		$price_data = $price_term_obj->get_product_price_terms('*',array('price_id'=>$term_price_id));
		if($price_data) {
			$price = $price_data[0]->price;
		}
	}
	return $price;
}
 function get_product_price($product_id,$startterm='',$filed_order="term,'y','m'")
		{
			if(!empty($product_id))
				{
					$CI =& get_instance();
					$subqry='';
					if($startterm!='')
						{
							$subqry=" and term like '%$startterm%'";
						}
						$sql="select price_id,product_id,price,upgrade_price,term_description from price_terms where product_id='$product_id' and active=1 $subqry ORDER BY FIELD($filed_order)";
						$query=$CI->db->query($sql);
						if(count($query->result_array())>0)
							{
							
								return $query->result_array();
							}
				}
				return false;
		}
function get_product_by_url($url_tag)
		{
			if(!empty($url_tag))
				{
					$CI =& get_instance();
				
						//$sql="select A.product_id,A.product_name,A.price,A.date_available,B.description from products A,product_descriptions B where A.product_id=B.product_id and A.product_url='$url_tag'";
						$sql="select A.*,B.* from products A,product_descriptions B where A.product_id=B.product_id and A.product_url like '%$url_tag'";
						$query=$CI->db->query($sql);
						if(count($query->result_array())>0)
							{
							
								$result= $query->result_array();
								return $result[0];
							}
					
				}
			return false;
		}	
function get_product_url($product_id,$filed='')
		{
			if(empty($filed))
				{
					$filed='*';
				}
			if(!empty($product_id))
				{
					$CI =& get_instance();
					$subqry='';
					
						$sql="select ".$filed." from products where product_id='$product_id'";
						$query=$CI->db->query($sql);
						if(count($query->result_array())>0)
							{
							
								$result= $query->result_array();
								return $result[0];
							}
				}
				return false;
		}
function get_product_data_url($url,$filed='')
		{
			if(empty($filed))
				{
					$filed='*';
				}
			if(!empty($url))
				{
					$url=trim(strtolower($url));
					$CI =& get_instance();
					$subqry='';
					
						$sql="select ".$filed." from products where trim(lower(product_url))='$url'";
						$query=$CI->db->query($sql);
						if(count($query->result_array())>0)
							{
							
								$result= $query->result_array();
								return $result[0];
							}
				}
				return false;
		}
function get_masterids_name_string($ids)
{
	$string='';
	if(!empty($ids))
		{		
			$CI =& get_instance();			
			$sql="select group_concat(product_master_desc) as mname from master_products where master_product_id in ($ids)";
			$query=$CI->db->query($sql);
			if(count($query->result_array())>0)
				{
					$result=$query->result_array();
					$string=$result[0]['mname'];
				}
					
		}
		return $string;
}		
function get_article_name_string($ids,$type='')
		{
			$string='<ul>';
			$article_ids=array();
			$article_ids=explode(',',rtrim($ids,','));
			if(!empty($article_ids) && is_array($article_ids) && count($article_ids)>0)
				{
				
					$CI =& get_instance();
					foreach($article_ids as $article_id)
						{
							if(!empty($type) && $type=='archive')
								{
									$sql="select post_name,post_title from wp_posts_archive where id='$article_id' limit 1";
								}
							else
								{
									$sql="select post_name,post_title from wp_posts where id='$article_id' limit 1";
								}
							$query=$CI->db->query($sql);
							if(count($query->result_array())>0)
								{
									$result=$query->result_array();
									$string.='<li>'.$result[0]['post_title'].'</li>';
								}
						}	
				}
				return $string.'</ul>';
		}
 function get_article_link_string($ids)
		{
		
			
			$string='<ul>';
			$article_ids=array();
			$article_ids=explode(',',rtrim($ids,','));
			if(!empty($article_ids) && is_array($article_ids) && count($article_ids)>0)
				{
				
					$CI =& get_instance();
					$CI->load->helper('specialty_corner');
					foreach($article_ids as $article_id)
						{						
							$sql="select A.post_name,A.post_title,B.specialty from wp_posts A join articles_post_pdf B on A.pdf_id=B.id where A.id='$article_id' limit 1";
							$query=$CI->db->query($sql);
							if(count($query->result_array())>0)
								{
								
									$result=$query->result_array();
									$url=get_specialty_url_by_slug($result[0]['specialty']);
									
									$string.='<li><a href="'.current_base_url().'coding-newsletters/my-'.$url.'/'.$result[0]['post_name'].'-article">'.$result[0]['post_title'].'</a></li>';
								}
						}	
				}
				return $string.'</ul>';
		}
 function get_article_link_string_archive($ids)
		{
		
			
			$string='<ul>';
			$article_ids=array();
			$article_ids=explode(',',rtrim($ids,','));
			if(!empty($article_ids) && is_array($article_ids) && count($article_ids)>0)
				{
				
					$CI =& get_instance();
						$CI->load->helper('specialty_corner');
					foreach($article_ids as $article_id)
						{						
							$sql="select guid,post_title from wp_posts_archive where id='$article_id' limit 1";
							$query=$CI->db->query($sql);
							if(count($query->result_array())>0)
								{
								
									$result=$query->result_array();
							
									
									$string.='<li><a href="'.current_base_url().'archives'.$result[0]['guid'].'-article">'.$result[0]['post_title'].'</a></li>';
								}
						}	
				}
				return $string.'</ul>';
		}
function get_article_twolink_string($ids,$type='')
		{
		
			
			$string='<ul>';
			$article_ids=array();
			$article_ids=explode(',',rtrim($ids,','));
			if(!empty($article_ids) && is_array($article_ids) && count($article_ids)>0)
				{
				
					$CI =& get_instance();
						$CI->load->helper('specialty_corner');
					foreach($article_ids as $article_id)
						{						
							if(!empty($type) && $type=='archive')
								{
									$sql="select A.post_name,A.post_title,B.specialty,A.guid from wp_posts_archive A join articles_post_pdf_archive B on A.pdf_id=B.id where A.id='$article_id' limit 1";
								}
							else
								{
									$sql="select A.post_name,A.post_title,B.specialty from wp_posts A join articles_post_pdf B on A.pdf_id=B.id where A.id='$article_id' limit 1";
								}
								
							
							
							$query=$CI->db->query($sql);
							if(count($query->result_array())>0)
								{
								
									$result=$query->result_array();
									$url=get_specialty_url_by_slug($result[0]['specialty']);
									if(!empty($type) && $type=='archive')
										{
											$string.='<li style="float: left;margin-bottom: 5px;width: 100%;"><div style="clear:both;">'.$result[0]['post_title'].'</div><div style="clear:both;"><div style="float:left"><a href="'.current_base_url().'archives'.$result[0]['guid'].'-article">Click here</a> to read the article online or to download the PDF.</div></div></li>';
										}
									else
										{
											$string.='<li style="float: left;margin-bottom: 5px;width: 100%;"><div style="clear:both;">'.$result[0]['post_title'].'</div><div style="clear:both;"><div style="float:left"><a href="'.current_base_url().'coding-newsletters/my-'.$url.'/'.$result[0]['post_name'].'-article">Click here</a> to read the article online or to download the PDF.</div></div></li>';
										}
									
								}
						}	
				}
				return $string.'</ul>';
		}		

function get_articles_link_array($ids)
		{
		
			
			$final_array=array();
			$article_ids=array();
			$article_ids=explode(',',rtrim($ids,','));
			if(!empty($article_ids) && is_array($article_ids) && count($article_ids)>0)
				{
				
					$CI =& get_instance();
					$CI->load->helper('specialty_corner');
					foreach($article_ids as $article_id)
						{						
							$sql="select A.post_name,A.post_title,B.specialty from wp_posts A join articles_post_pdf B on A.pdf_id=B.id where A.id='$article_id' limit 1";
							$query=$CI->db->query($sql);
							if(count($query->result_array())>0)
								{
								
									$result=$query->result_array();
									$url=get_specialty_url_by_slug($result[0]['specialty']);
									$final_array[$article_id]='coding-newsletters/my-'.$url.'/'.$result[0]['post_name'].'-article';
									
								}
						}	
				}
				return $final_array;
		}
				
function calculate_coupon_discount($coupon_code='', $products=array()) {
	$product_discount = array();
	if(!empty($coupon_code) && !empty($products) && is_array($products)) {
		$CI = &get_instance();
		$CI->db->select('*')->from('members_coupon');
		$CI->db->where('code',$coupon);
		$CI->db->where('locked','0');
		$CI->db->where('begin_date <=',date('Y-m-d'));
		$CI->db->where('expire_date >=',date('Y-m-d'));
		$CI->db->limit('1');

		$query = $CI->db->get()->result_array();
		$total_discount=0;
		$products_id = array();
		if($query && is_array($query[0]))
		{
			$discount=trim($query[0]['discount']);
			$discount_value=str_replace('%','',$discount);
			if(substr_count($discount,'%')==1) {
				$type=1;
			} else {
				$type=2;
			}
			$products_id=explode(',',$query[0]['product_id']);
		}
		foreach($products as $product) {
			$discount = 0;
			if(in_array($product['id'], $products_id)) {
				if($type==1) {
					$discount = $product['price']-(($product['price']*$discount_value)/100);
				} else{
					$discount = $product['price']-$discount_value;
				}
			}
			$product_discount[$product['id']] = $discount;
		}
	}
	return $product_discount;
}

function get_store_product()
	{	
			$final_array=array();
			$CI =& get_instance();
			$sql="select * from products where product_type='onlinestore' order by sort_order";
			$query=$CI->db->query($sql);
			$results=$query->result_array();
			return $results;
	}
function get_storeproduct_data($product_url)
		{
			$final_array=array();
			if(!empty($product_url))
			{
				$CI =& get_instance();
				$sql="select * from products p left join product_descriptions pd on pd.product_id=p.product_id where p.product_type='onlinestore' and p.product_url ='$product_url' limit 1";
				$query=$CI->db->query($sql);
				$results=$query->result_array();
				if(is_array($results) && isset($results[0]) && count($results[0])>0)
						{
							$final_array=$results[0];
						}
			}
			return $final_array;
		}