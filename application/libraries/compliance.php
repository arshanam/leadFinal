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
class Compliance
{

    private $CI;
	private $master_product;
	
    function __construct()
	{
        $this->CI = & get_instance();
		$this->master_product=$this->CI->config->item('master_product_array');
		$this->CI->load->helper('crossell');
		$this->CI->load->model('document_categories');
		
    }
	
	/**
		This function give us crosssell product list for the code details page.
	**/	
	public function compliance_nav()
	{
		$a_categories=$this->CI->document_categories->getNestedCategories();
		$categories=array_shift($a_categories);
		$cmscategory=$this->CI->document_categories->getCMSCategories();
		$cnt=count($cmscategory);
		$cmscategory[$cnt+1]['name']='Transmittals';
		$cmscategory[$cnt+1]['url']='/exclusives/cms-center/transmittals';
		$cmscategory[$cnt+2]['name']='Claims Processing Manuals';
		$cmscategory[$cnt+2]['url']='/exclusives/cms-center/claims-processing-manuals';
		$cmscategory[$cnt+3]['name']='CMS/MLN Specialty Book';
		$cmscategory[$cnt+3]['url']='/exclusives/cms-center/mln-specialty-book';
		$compl_arr['categories']	=	$a_categories;
		$compl_arr['cmscategory']	=	$cmscategory;
		return $compl_arr;
			
	}
	
}