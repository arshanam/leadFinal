<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * This class is built to get lcd lookup beta policy data after click on find button.
 * Used cookies to get those data.
 * Following cookies has been used for custom_print_get_lcd_data_only_content
 * $_COOKIE['custom_print_codename'] 
 * $_COOKIE['custom_print_stateid']
 * $_COOKIE['custom_print_contractor_type_id']
 * $_COOKIE['custom_print_contractor_id']
 * $_COOKIE['custom_print_lcdid']
 * @author     Shankar Kumar
 */
class Custom_print
{

    private $CI;
	private $master_product;
	
    function __construct()
	{
        $this->CI = & get_instance();
		$this->CI->load->model('lcds_beta');	
		$this->CI->load->helper('codesets_helper');
		$this->master_product=$this->CI->config->item('master_product_array');
		
    }
	
	/**
		This function gives you State name and Contarctor name for Selected area for selected CPT/HCPCS Code
	**/	
	public function custom_print_get_lcd_data_only_content($codename,$stateid,$contractor_type_id,$contractor_id,$lcdid,$custom_title,$related_article=false,$title=false)
	{
						
				$custom_print_lcd_beta = new Lcds_beta();	
				
				
				$lcd_support_data_new=array();
				$lcd_icd_data_new=array();
				$final_array=array();
				$lcdbetakey_data='lcd_data_'.$codename.'_'.$stateid.'_'.$contractor_type_id.'_'.$contractor_id.'_'.$lcdid;			
				
					$lcd_support_data=$custom_print_lcd_beta->get_lcd_support_group($lcdid);
					/*Fetch Related LCD Article Data - Start */
						$data['lcd_article_data']=$custom_print_lcd_beta->related_article_exists($lcdid,$state_id,$contractor_id,$codename,$title);
					//print_r($lcd_article_data);
					/*Fetch Related LCD Article Data - End */
					/*Fetch Related NCD Data - Start */
					$data['ncd_data']=$custom_print_lcd_beta->related_ncd($lcdid);
					//print_r($lcd_article_data);
					/*Fetch Related NCD Data - End */
					if(is_array($lcd_support_data) && count($lcd_support_data)>0)
					{
						foreach($lcd_support_data as $betakey=>$val)
						{
							$lcd_icd_data[$val['icd9_support_group']]=$custom_print_lcd_beta->get_lcd_icd_data($lcdid,$val['icd9_support_group']); // Give ICD Code Realted to LCD ID				
						}
					}
					$final_array=array($lcd_support_data,$lcd_icd_data);
					
					
					$lcd_hcpc_support_data = $custom_print_lcd_beta->get_hcpc_code_group($lcdid);
					if(is_array($lcd_hcpc_support_data) && count($lcd_hcpc_support_data)>0)
					{
						foreach($lcd_hcpc_support_data as $key=>$val)
						{
							$lcd_hcpc_data[$val['hcpc_code_group']]=$custom_print_lcd_beta->get_lcd_hcpc_data($lcdid,$val['hcpc_code_group']); // Give HCPCS/CPT Code Realted to LCD ID				
						}
					}
					$final_hcpc_array=array($lcd_hcpc_support_data,$lcd_hcpc_data);
					
					/* ICD-10 Related Data - Start */
					$lcd_icd10_data=array();
					$lcd_icd10_support_data = $custom_print_lcd_beta->get_lcd_icd10_support_group($lcdid);
					if(is_array($lcd_icd10_support_data) && count($lcd_icd10_support_data)>0)
					{
						foreach($lcd_icd10_support_data as $key=>$val)
						{
							$lcd_icd10_data[$val['icd10_support_group']]=$custom_print_lcd_beta->get_lcd_icd10_data($lcdid,$val['icd10_support_group']); // Give HCPCS/CPT Code Realted to LCD ID				
						}
					}
					$final_icd10_array=array($lcd_icd10_support_data,$lcd_icd10_data);
					/* ICD-10 Related Data - End */
					
				
				$link_data = $custom_print_lcd_beta->get_public_version_link_data($lcdid, $contractor_id);
				$state_desc=$custom_print_lcd_beta->get_state_info($stateid);
				
				//echo $assoc_data;
				$data['lcdid'] = $lcdid;
				$data['lcd_support_data']=$lcd_support_data;
				$data['lcd_hcpc_support_data']=$lcd_hcpc_support_data;
				$data['lcd_icd_data']=$lcd_icd_data;
				$data['lcd_hcpc_data']=$lcd_hcpc_data;
				$data['policy_title']=$custom_title;
				$data['related_article']=$related_article;
				$data['lcd_icd10_support_data']=$lcd_icd10_support_data;
				$data['lcd_icd10_data']=$lcd_icd10_data;
				$html=$this->CI->load->view('modules/lcd_beta/lcd_icd_data_content',$data,true);
				return $html;
				//return $data;
			
			
			
	}
	
    
}