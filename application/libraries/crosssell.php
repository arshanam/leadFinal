<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * This class has function to show cross-sell product to details pages of supercoder product details pages.
 * 
 *
 * product id 
 * @author     Shankar Kumar
 */
class Crosssell
{

    private $CI;
	private $master_product;
	
    function __construct()
	{
        $this->CI = & get_instance();
		$this->master_product=$this->CI->config->item('master_product_array');
		$this->CI->load->helper('crossell');
		
    }
	
	/**
		This function give us crosssell product list for the code details page.
	**/	
	public function crosssell_products($product_id)
	{
				$where=array(
				'a.product_id'=>$product_id
				
				);
				$this->CI->db->where($where);
				$this->CI->db->select('a.related_product_ids,b.product_name');
				$this->CI->db->from('crosssell_products a');
				$this->CI->db->join('products b',"a.product_id=b.product_id");
				$res=$this->CI->db->get();
				if($res->num_rows()>0)
				{
					$data['mainproductdata']=$row=$res->row();
					$product_ids=explode(",",$row->related_product_ids);
					$where=array('p.status'=>'1','p.search_status'=>'1');
					$this->CI->db->where($where);
					$this->CI->db->where_in('p.product_id',$product_ids);
					$this->CI->db->select('p.image,p.product_id,p.product_name,p.product_url,p.product_user_url,p.product_type,pt.price');
					$this->CI->db->from('products p');
					$this->CI->db->join('price_terms pt',"pt.product_id=p.product_id and pt.term='1m' and pt.active='1'",'left');
					$rslt=$this->CI->db->get();
					if($rslt->num_rows()>0)
					{
						$html=$this->CI->load->view('modules/crosssell/crosssell_products',$data,true);
						return $html;
					}
					else
					return false;
				}
				else
				return false;
				
				//return $data;
			
			
			
	}
	
}