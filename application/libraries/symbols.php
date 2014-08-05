<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * This class is built to get symbols for icd9 and icd10.
 * 
 * @author     Shankar Kumar
 */
class Symbols
{

    private $CI;
	private $master_product;
	
    function __construct()
	{
        $this->CI = & get_instance();
		$this->CI->load->model('Icd_codes');
		$this->CI->load->model('icd_ten_codes');	
		$this->CI->load->helper('codesets_helper');
		$this->master_product=$this->CI->config->item('master_product_array');
		
    }
	
	/**
		This function gives you State name and Contarctor name for Selected area for selected CPT/HCPCS Code
	**/	
	public function getsymbolsforcode($cpt_code,$table)
	{
						
				$icd_codes = new Icd_codes();	
					
				$row=$this->get_icd_range_code($cpt_code,$table);
				$data['gender'] = getICDGenderResult($cpt_code);
				$data['code_status'] = $row->code_status;
				$data['pdx_code'] = $icd_codes->check_pdx_code($cpt_code);
				$data['cc_mcc'] = $icd_codes->check_cc_mcc($cpt_code);
				$data['cc_exclusion'] = $icd_codes->get_cc_exclusion($cpt_code);
				$data['no_poa'] = $icd_codes->get_icd_meta($row->id,'POA_Exempt');
				$html=$this->CI->load->view('modules/symbols/symbols',$data,true);
				return $html;
				//return $data;
			
			
			
	}
	
	public function getsymbolsforcodeICD10($cpt_code,$table)
	{
						
				$icd_ten_codes = new Icd_ten_codes();	
				$data['code_symbols'] =	$icd_ten_codes->get_icd_symbols($cpt_code);
				$html=$this->CI->load->view('modules/symbols/icd_ten_symbols',$data,true);
				return $html;
				//return $data;
			
			
			
	}
	
	public function get_icd_range_code($code_title,$table)
		{
				$this->CI->db->select('*')->from($table);
				$this->CI->db->where('code_title',$code_title);
				$this->CI->db->where('parent','0');
				$this->CI->db->order_by('code_title');	
				
					
				$query=$this->CI->db->get()->row();						
				return $query;
		}
	
    
}