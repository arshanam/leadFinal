<?php
/*
 * Provides helper functions dealing with data
 */

/**
 * replace the matching content of data from an array   
 *
 * @return data
 */
function replace_content($replaced_array,$data)
		{
			if(!empty($replaced_array)){
				
				$data	=	str_replace($replaced_array, "", $data);
			}
			
			//echo "<pre>";print_r($data);die;
			
			return $data;
		}
		
		

/**
 * replace the matching content of data from an array   
 *
 * @return data
 */
 
 function xml_from_array($range_meta,$xmlObj){
 	
	
		if($range_meta) {
		//start of range meta branch
			$xmlObj->startBranch("range_meta");
			
				foreach ($range_meta as $metaKey=>$rangeMeta) {
					
					$xmlObj->addNode($metaKey, $rangeMeta);
				}			
			//end  of range meta branch
			$xmlObj->endBranch();
			
		}	
 }
 
 
 /**
 * replace the matching content of data from an array   
 *
 * @return data
 */
function codeset_meta_xml($meta,$xml){
 	
	$range_meta	= array();
		foreach ($meta as $singleMeta) {
												
				if($singleMeta->meta_flag!='r'){
					//start of parent codeset meta	
					$xml->startBranch("codeset_meta");
					$xml->addNode($singleMeta->meta_key, $singleMeta->meta_value);	
					$xml->endBranch();
										
					} 
					else{
											
						//set the range meta
					$range_meta[$singleMeta->id][$singleMeta->meta_key]	=	$singleMeta->meta_value;
					}
														
														
			}
		
		return $range_meta;
 }


/**
 * grouping value according to alphabets  (like indexing)  for codeset_hcpcs_index
 *
 * @return data
 */
  
 function group_by_alphabet($parentIndex){
 	
		if($parentIndex){
			
			$index=array();
			foreach ($parentIndex as $key => $value) {
					
					$letter	=	strtoupper(substr($value->title,0,1));
					$index[$letter][$key]=$value;	
			}
			return $index;
		} 
		return false;
 }
 
 
 
 
/**
 * removes codes from hcpcs codeset index title  
 *
 * @return data
 */ 
 
 function get_main_term($letterArray){
 	
		return trim(str_replace($letterArray->code, "", $letterArray->title));
	
 }
 
 /**
 * get child codeset_index xml  
 *
 * @return data
 */ 
 
 function get_child_codeset_xml($childCodesets,$xml){
 	
			
		$totalCodeset	=	count($childCodesets);
		$i=1;		
		foreach ($childCodesets as $singleCodeset) {
			
			$xml->addNode("child_title", get_main_term($singleCodeset));
			$xml->writeRaw("nspace");
			$xml->addNode("code",  $singleCodeset->code);
			
			if($totalCodeset>$i)
			$xml->writeRaw("\n");
			
			$i++;
		} 
		
 }
 
 
  function get_child_codeset_cpt($childCodesets,$xml,$db_object,$i=1){
 	
			
		foreach ($childCodesets as $key=>$singleCodeset) {
			
			$xml->writeRaw("\n");
			
			
			$xml->startBranch('child_term_'.$singleCodeset->header);
			
			if($key==0 && $singleCodeset->header=='H1') {
				
				$xml->addNode("child_title_top", get_main_term($singleCodeset));
				
			} else {
					
				$xml->addNode("child_title_".$singleCodeset->header, get_main_term($singleCodeset));	
			}
			
			
			if($singleCodeset->code) {
				
				$xml->writeRaw("shifttab");
				$xml->addNode("code",  $singleCodeset->code);
			
			}
			
			if($child_codesets=$db_object->get_child_codeset($singleCodeset->id)){
				$i++;	
				get_child_codeset_cpt($child_codesets,$xml,$db_object,$i);
		}
			
			
			$xml->endBranch();
			//$xml->writeRaw("\n");
		} 
		
 }
 
 
 
 
 /**
 * get hcpcs_symbol xml  
 *
 * @return data
 */ 
 
 
 function get_hcpcs_symbol($symbol,$xml){
 	
		foreach($symbol[0] as $key => $value) {
				
			if(($value) && $key!='hcpcs_code'){
				
				if($key=='pqrs_dmepos'){
					
					$xml->startBranch($key);
						$xml->addNode("image","" ,array('href'=>'file://icons/symbol-'.$value.'.eps'));
						$xml->writeRaw(" ");
					$xml->endBranch();
					
				} elseif ($key=='gender'){
					
					if($value=='M'){
						
						$gender='male';
					} elseif($value=='F'){
						
						$gender	=	'female';
					} else {
							
							$gender='';	
					}
					
					if($gender!=''){
							
						$xml->startBranch($key);
						$xml->addNode("image","" ,array('href'=>'file://icons/'.$gender.'_sign.eps'));
						$xml->endBranch();
						$xml->writeRaw("nspace");
					}
						
				} else {
						
					if($key=='price_cd1' || $key=="dmepos_mod" || $key=='ASC_SI') {
						
						if($key=='price_cd1')
						$xml->writeRaw("\n");
							
					} else {
					
					
					if($key=='age') {
						
						$xml->addNode("age","" ,array('href'=>'file://icons/A.eps'));
						$xml->writeRaw("nspace");
					
					}else{
						
						$xml->addNode($key, $value);
						$xml->writeRaw("nspace");	
						
					}
						
					}	
					
					
				}
				
				
					
			}
			
		}
	
 }
 
 
 /**
 * get icd10 range meta xml  
 *
 * @return data
 */ 
 
 
 	function get_icd10_meta_xml($range_meta,$xmlObj){
 	
		if($range_meta) {
		//start of range meta branch
		
			$xmlObj->startBranch("meta_values");
			$CI = get_instance();
			$CI->load->helper('codesets');
			
			
				foreach ($range_meta as $rangeMeta) {
					
				 //if($rangeMeta->meta_key!='inclusionTerm'){
				 	$meta_array = array('inclusionTerm','sevenChrNote','useAdditionalCode','codeAlso','codeFirst');
				 	if(!in_array($rangeMeta->meta_key, $meta_array)){	
				 	
						
					$xmlObj->writeRaw("\n");
					
					if($rangeMeta->meta_key=='sevenChrDef') {
					
						$xmlObj->addNode($rangeMeta->meta_key, str_replace(array("<br/>","<br>","&lt;br/&gt;"), "shiftenter", format_sevenChrMeta($rangeMeta->meta_value)));
					}
					else {
						
						$xmlObj->addNode(str_replace(" ", "", $rangeMeta->meta_key)."_head", strtoupper($rangeMeta->meta_key));
						$xmlObj->writeRaw("\t");$xmlObj->writeRaw("indenttohere");
						$xmlObj->addNode(str_replace(" ", "", $rangeMeta->meta_key), str_replace("&lt;br/&gt;", "shiftenter", $rangeMeta->meta_value));
					}	
					
				} else {
					
					
					
					switch ($rangeMeta->meta_key) {
						case 'codeAlso':
							$rangeMeta->meta_value	=	"Code also ".$rangeMeta->meta_value;		
							break;
						case 'codeFirst':
							$rangeMeta->meta_value	=	"Code first ".$rangeMeta->meta_value;		
							break;
						case 'useAdditionalCode':
							$rangeMeta->meta_value	=	"Use additional ".$rangeMeta->meta_value;		
							break;		
						
						default:
							$rangeMeta->meta_value=$rangeMeta->meta_value;		
							break;

							}
					
					$xmlObj->writeRaw("\n");
					$xmlObj->writeRaw("indenttohere");
					$xmlObj->addNode(str_replace(" ", "", $rangeMeta->meta_key), str_replace("&lt;br/&gt;", "shiftenter", $rangeMeta->meta_value));
				}
					
				}			
			//end of range meta branch
			$xmlObj->endBranch();
			
			
		}	
	
 }

 /**
 * get icd10 range codeset  
 *
 * @return data
 */ 	
	
function get_icd10_codeset_xml($codeset,$xml, $parent_range_id){
			
		$level=0;
		
		foreach ($codeset as $single_code) {
		
			
			$description	=	get_colored_icd10_description($single_code->description);
			
			$xml->writeRaw("\n");
			$xml->startBranch('parent_codeset');
			
			
			//section for codeset image
			if($codeset_image	=	get_codeset_image($single_code->code_title)){
					
				$xml->addNode('image',"",array('href'=>'file://images/'.$codeset_image[0]->code_image));
				$xml->writeRaw("\n");
				$xml->addNode('caption',$codeset_image[0]->code_caption);
				$xml->writeRaw("\n");
				
			}
			
			
        	
			
			if($single_code->have_child){
				
				$xml->addNode('have_child',"",array('href'=>'file://icons/icd-0'.get_digit_from_havechild($single_code->have_child).'.eps'));
				$xml->writeRaw("nspace");
			}
			
			$xml->addNode('code_title', $single_code->code_title);
			$xml->writeRaw("nspace");
			$xml->writeRaw("indenttohere");
			
			if(get_manifestataion_symbol($single_code->code_title)){
				
				$xml->startBranch('blue');
        		$xml->addNode('code_description', $description);
				$xml->endBranch();
				
			}
			else{
				$xml->addNode('code_description', $description);
				
			}
			
		
				if($codeset_meta	=	get_codeset_meta($single_code->id)){
					
					get_icd10_meta_xml($codeset_meta,$xml);
				}
				
				//section for code symbols
				get_icd10_codeset_symbol_xml($single_code->code_title,$xml);
				
				if($child_codeset	=	get_icd10_child_codeset($single_code->id)){
						
						get_icd10_child_codeset_xml($child_codeset,$xml,$level,$parent_range_id);					
				}
				
			//end of codeset branch
        	$xml->endBranch();
		}
		
}




/**
 * get icd10 child codeset  
 *
 * @return data
*/ 

function get_icd10_child_codeset($id){
	
		 $CI = get_instance();
		 $CI->load->model('codeset_icd10_tables');
		 return $CI->codeset_icd10_tables->get_codesets_codeset($id);
		 
}

function get_manifestataion_symbol($code_title){
	
	$CI = get_instance();
	$CI->db->select('*')->from("codeset_icd10_symbols");
	$CI->db->where('code_title',$code_title);
	$result =	$CI->db->get()->result();
	
	if($result)
	return true;
	else
	return false; 
}

function get_codeset_meta($id){
	
		 $CI = get_instance();
		 $CI->load->model('codeset_icd10_tables');
		 return $CI->codeset_icd10_tables->get_icd10_meta($id, $meta_flag='c' );
		 
}
/**
 * get icd10 codeset image  
 *
 * @return data
*/ 
function get_codeset_image($code_title){
	
		 $CI = get_instance();
		 $CI->load->model('codeset_icd10_tables');
		 return $CI->codeset_icd10_tables->get_icd10_image($code_title);
}

/**
 * get icd10 child codeset xml  
 *
 * @return data
*/
 
function get_icd10_child_codeset_xml($child_codeset,$xml,$level,$parent_range_id){
	
	$level++;	
	
	foreach ($child_codeset as $single_code) {
		
		//skip the 7th digit character code	
		if(strlen($single_code->code_title) < 8) {	
			
			$xml->writeRaw("\n");
			for ($i=0; $i < $level ; $i++) {
				 
				$xml->writeRaw("\t");
			}	
			
			
			$xml->startBranch('child_codeset_level_'.$level);
			
			
			//section for codeset image
			if($codeset_image	=	get_codeset_image($single_code->code_title)){
					
				$xml->addNode('image',"",array('href'=>'file://images/'.$codeset_image[0]->code_image));
				$xml->writeRaw("\n");
				$xml->addNode('caption',$codeset_image[0]->code_caption);
				$xml->writeRaw("\n");
				
			}
			
			//section for have child image
			if($single_code->have_child){
					
					$have_child	=	get_digit_from_havechild($single_code->have_child);
				
				if($have_child=='7' && $single_code->xtension=='1')
					$xml->addNode('have_child',"",array('href'=>'file://icons/icd-0'.$have_child.'_blue.eps'));
				else
					$xml->addNode('have_child',"",array('href'=>'file://icons/icd-0'.$have_child.'.eps'));
				
				$xml->writeRaw("nspace");
				
			} else{
				
				$xml->writeRaw("\t");
			}
			
			
			$description	=	get_colored_icd10_description($single_code->description);
			
        	$xml->addNode('code_title', $single_code->code_title);
			$xml->writeRaw("nspace");
			$xml->writeRaw("indenttohere");
			
			
			if(get_manifestataion_symbol($single_code->code_title)){
				
				$xml->startBranch('blue');
        		$xml->addNode('code_description', $description);
				$xml->endBranch();
				
				
			} else{
				
				$xml->addNode('code_description', $description);	
			}
        	
			
			//commenting code status suggested by rajender sir(coder)
			//$xml->addNode('code_status', $single_code->code_status);
			//$xml->writeRaw("\n");
				
				if($codeset_meta	=	get_codeset_meta($single_code->id)){
					
					get_icd10_meta_xml($codeset_meta,$xml);
				}
				
				
				//section for code symbols
			if($parent_range_id=='298')	
				get_icd10_codeset_symbol_xml_z($single_code->code_title,$xml);
			else 
				get_icd10_codeset_symbol_xml($single_code->code_title,$xml);
				
				
				if($child_codeset	=	get_icd10_child_codeset($single_code->id)){
						
						get_icd10_child_codeset_xml($child_codeset,$xml,$level,$parent_range_id);		
				}
				
			
			//end of codeset branch
        	$xml->endBranch();
			
			} //if condition ends here	
			
	}
	
}	


function get_icd10_codeset_symbol_xml($code_title,$xml){
	
	$CI = get_instance();
	$CI->db->select('*')->from("codeset_icd10_symbols");
	$CI->db->where('code_title',$code_title);
	$CI->db->where('status','1');
	$result =	$CI->db->get()->result();
	
	if($result){
		
		$xml->writeRaw("shifttab");
		$xml->startBranch('Symbols');
		
		foreach ($result as $symbol) {
			
			if($symbol->code_symbol!='manifestation')	{
				$xml->addNode($symbol->code_symbol,"",array('href'=>'file://icons/'.$symbol->code_symbol.'.eps'));
				$xml->writeRaw("nspace");
			}
		}
		
		$xml->endBranch();
	}
	
}




function get_icd10_codeset_symbol_xml_z($code_title,$xml){
	
	$CI = get_instance();
	$CI->db->select('*')->from("codeset_icd10_symbols");
	$CI->db->where('code_title',$code_title);
	$CI->db->where('status','1');
	$result =	$CI->db->get()->result();
	
	if($result){
		
		$xml->writeRaw("shifttab");
		$xml->startBranch('Symbols');
		
		foreach ($result as $symbol) {
			
			if($symbol->code_symbol!='manifestation')	{
			$xml->addNode($symbol->code_symbol,"",array('href'=>'file://icons/'.$symbol->code_symbol.'.eps'));
			$xml->writeRaw("nspace");
			$symbol_array[]	=$symbol->code_symbol;	
			}
		}
		
		get_parent_symbols_z($code_title,$xml,$symbol_array);
		
		$xml->endBranch();
	}
	
}


function get_parent_symbols_z($code_title,$xml,$symbol_array){
	
		$CI = get_instance();
		//get the id of current codeset
		$CI->db->select('*')->from("codeset_icd10");
		$CI->db->where('code_title',$code_title);
		$result =	$CI->db->get()->result();
		
		//get parent of current codeset
		$CI->db->select('*')->from("codeset_icd10");
		$CI->db->where('id',$result[0]->parent);
		$parent_result =	$CI->db->get()->result();
		
		//get symbols of parent codeset
		$CI->db->select('*')->from("codeset_icd10_symbols");
		$CI->db->where('code_title',$parent_result[0]->code_title);
		
		if($symbol_array){
			
			$CI->db->where_not_in('code_symbol',$symbol_array);
			
		}
		
		$symbols_result =	$CI->db->get()->result();
		
		/*if($code_title=='Z52.810'){
			
			echo $CI->db->last_query();die;
		}*/
		
		foreach ($symbols_result as $symbol) {
			
			if($symbol->code_symbol!='manifestation')	{
			$xml->addNode($symbol->code_symbol,"",array('href'=>'file://icons/'.$symbol->code_symbol.'.eps'));
			$xml->writeRaw("nspace");
			
			$symbol_array[]	=	$symbol->code_symbol;
			
			}
		}
		
		if($parent_result[0]->parent>0){
			
			get_parent_symbols_z($parent_result[0]->code_title,$xml,$symbol_array);
		}		
}


function get_colored_icd10_description($description){
	
	$icd10_text_array  			= array('right','left','bilateral','first trimester','second trimester','third trimester');
	$icd10_colortext_array  	= array('<green>right</green>','<green>left</green>','<green>bilateral</green>','<green>first trimester</green>','<green>second trimester</green>','<green>third trimester</green>');
	$icd10_yellow_background	= array('unspecified');
	$icd10_grey_background		= array('other');
		
	$new_description	= str_replace($icd10_text_array, $icd10_colortext_array, $description);
	
	if(strpos($new_description, 'unspecified')){
		
		$new_description	=	"<yellow>".$new_description."</yellow>";
	}
	
	if(strstr($new_description, 'Unspecified')){
		
		$new_description	=	"<yellow>".$new_description."</yellow>";
	}
	
	if(strpos($new_description, 'other')){
		
		$new_description	=	"<grey>".$new_description."</grey>";
	}
	
	if(strstr($new_description, 'Other')){
		
		$new_description	=	"<grey>".$new_description."</grey>";
	}	
	
	return $new_description;
}



function get_digit_from_havechild($havechild,$type='icd10'){
	if($type=='icd10') {	
		switch ($havechild) {
			
			case '1':
				return 4;
				break;
			case '2':
				return 5;
				break;
			case '3':
				return 6;
				break;
			case '4':
				return 7;
				break;				
			default:
				 return false;
				break;		
		}		
	}
	if($type=='icd9v1')	
	{
		switch ($havechild) {
			
			case '1':
				return 4;
				break;
			case '2':
				return 5;
				break;
			default:
				 return false;
				break;		
		}		
	}
}

function get_codeset_icd9v1_meta($id)
{
	$CI = get_instance();
	$CI->load->model('codeset_icd9_tables');
	return $CI->codeset_icd9_tables->get_icd9v1_meta($id, $meta_flag='c' );
}
function get_icd9v1_child_codeset($id)
{
	$CI = get_instance();
	$CI->load->model('codeset_icd9_tables');
	return $CI->codeset_icd9_tables->get_codesets_codeset($id);
}
function get_icd9v1_meta_xml($range_meta,$xmlObj)
{
	if($range_meta) 
	{
		//start of range meta branch
		//$xmlObj->writeRaw("\n");
		$xmlObj->startBranch("meta_values");
		$CI = get_instance();
		$CI->load->helper('codesets');
		foreach ($range_meta as $rangeMeta) 
		{
		 //if($rangeMeta->meta_key!='inclusionTerm'){
		 	//$meta_array = array('inclusionTerm','sevenChrNote','useAdditionalCode','codeAlso','codeFirst');
		 	$meta_array = array('three_digit_header','fourth_digit_header','fifth_digit_header','ICD9_section_header','ICD9_chapter_header');
		 	if(in_array($rangeMeta->meta_key, $meta_array))
			{	
				$xmlObj->addNode(str_replace(" ", "", $rangeMeta->meta_key), replace_icd9_meta_spaces(stripslashes(utf8_encode(html_entity_decode($rangeMeta->meta_value)))));
			} 
		}			
			//end of range meta branch
			$xmlObj->endBranch();
	}	
}
function check_icd9v1_bracket_content($code)
{
	$CI = get_instance();
	$bracket_val = $CI->db->query("select code_title, meta_key, meta_value from codeset_icd_meta m join codeset_icdvol1 i on i.id=m.code_id where meta_value like '%4digitsBracket%' and code_title='".$code."'")->result_array();
	if(is_array($bracket_val) && !empty($bracket_val))
	{	
		//print_r($bracket_val);
		return true;
	}
	else
		return false;
}
function get_icd9v1_codeset_xml($codeset,$xml, $parent_range_id)
{
	$level=0;
	foreach ($codeset as $single_code) 
	{
		/*if(!check_icd9v1_bracket_content($single_code->code_title))
		{*/
			$description	=	my_strip_tags($single_code->description);
			$xml->writeRaw("\n");
			$xml->startBranch('parent_codeset');
			//section for codeset image

			if($single_code->have_child)
			{
				$xml->addNode('have_child',"",array('href'=>'file://icons/icd-0'.get_digit_from_havechild($single_code->have_child).'.eps','icd9v1'));
				$xml->writeRaw("nspace");
			}
			$xml->addNode('code_title', $single_code->code_title);
			$xml->writeRaw("nspace");
			$xml->writeRaw("indenttohere");
			if(strlen($single_code->code_title)==3)
			{
				if(get_digit_from_havechild($single_code->have_child)==4)
					$desc_node = 'code_description_level_1_5';
				else
					$desc_node = 'code_description';
			}
			if(strlen($single_code->code_title)==5)
			{
				if(get_digit_from_havechild($single_code->have_child)==5)
					$desc_node = 'code_description_level_1_5_1';
				else
					$desc_node = 'code_description_level_1';
			}
			if(strlen($single_code->code_title)==6)
				$desc_node = 'code_description_level_2';
			$xml->addNode($desc_node, $description);
			get_icd9v1_codeset_symbol_xml($single_code->code_title, $single_code->description, $xml);
			if($codeset_meta	=	get_codeset_icd9v1_meta($single_code->id))
			{
				get_icd9v1_meta_xml($codeset_meta,$xml);
			}
			get_icd9_coding_clinic($single_code->code_title,$xml);
			
			//section for code symbols
			
			if($child_codeset	=	get_icd9v1_child_codeset($single_code->id))
			{
				if(!check_icd9v1_bracket_content($single_code->code_title))
				{
					get_icd9v1_child_codeset_xml($child_codeset,$xml,$level,$parent_range_id);					
				}
			}
			//end of codeset branch
			$xml->endBranch();
		// }
	}
}

function get_icd9v1_child_codeset_xml($child_codeset,$xml,$level,$parent_range_id)
{
	$level++;	
	foreach ($child_codeset as $single_code) 
	{
		/*if(!check_icd9v1_bracket_content($single_code->code_title))
		{ */
			$xml->writeRaw("\n");
			for ($i=0; $i < $level ; $i++) 
			{
				$xml->writeRaw("\t");
			}	
			$xml->startBranch('child_codeset_level_'.$level);
			//section for codeset image

			//section for have child image
			if($single_code->have_child)
			{
				$have_child	=	get_digit_from_havechild($single_code->have_child,'icd9v1');
				$xml->addNode('have_child',"",array('href'=>'file://icons/icd-0'.$have_child.'.eps'));
				$xml->writeRaw("nspace");
			} 
			else
			{
				//$xml->writeRaw("\t");
			}
			$description	=	my_strip_tags($single_code->description);
			$xml->addNode('code_title', $single_code->code_title);
			$xml->writeRaw("nspace");
			$xml->writeRaw("indenttohere");
			//$xml->addNode('code_description', $description);	
			if(strlen($single_code->code_title)==3)
			{
				if(get_digit_from_havechild($single_code->have_child)==4)
					$desc_node = 'code_description_level_1_5';
				else
					$desc_node = 'code_description';
			}
			if(strlen($single_code->code_title)==5)
			{
				if(get_digit_from_havechild($single_code->have_child)==5)
					$desc_node = 'code_description_level_1_5_1';
				else
					$desc_node = 'code_description_level_1';
			}
			if(strlen($single_code->code_title)==6)
				$desc_node = 'code_description_level_2';
			$xml->addNode($desc_node, $description);
			get_icd9v1_codeset_symbol_xml($single_code->code_title,$single_code->description,$xml);
			if($codeset_meta	=	get_codeset_icd9v1_meta($single_code->id))
			{
				get_icd9v1_meta_xml($codeset_meta,$xml);
			}
			get_icd9_coding_clinic($single_code->code_title,$xml);
			
			//section for code symbols
			
			if($child_codeset	=	get_icd9v1_child_codeset($single_code->id))
			{
				if(!check_icd9v1_bracket_content($single_code->code_title))
				{
					get_icd9v1_child_codeset_xml($child_codeset,$xml,$level,$parent_range_id);		
				}
			}
			//end of codeset branch
			$xml->endBranch();
		//  }
	}
}	
function get_icd9v1_codeset_symbol_xml($code_title,$desc, $xml)
{
	$CI = get_instance();
	$CI->load->model('codeset_icd9_tables');
	
	$pdx_symbol =  $CI->codeset_icd9_tables->symbol_pdx_code($code_title);
	
	$cc_mcc_symbol = $CI->codeset_icd9_tables->symbol_cc_mcc($code_title);
	
	$cc_exclusion_symbol = $CI->codeset_icd9_tables->symbol_cc_exclusion($code_title);
	
	$gender = $CI->db->query("SELECT * FROM codeset_icd_gender WHERE code_title='".$code_title."'")->result_array();
	
	//$other_symbols = $CI->db->query("SELECT * FROM codeset_icdvol1_symbols WHERE code_title='".$code_title."'")->result_array();
	
	$poa = $CI->db->query("SELECT * FROM codeset_icdvol1 i join codeset_icd_meta m on i.id=m.code_id WHERE i.code_title='".$code_title."' and m.meta_key='POA_Exempt'")->result_array();
	
	$CI->db->select('*')->from("codeset_icdvol1_symbols_for_books");
	$CI->db->where('code_title',$code_title);
	$CI->db->where('status','1');
	$other_symbols = $CI->db->get()->result();

	if((isset($pdx_symbol) && $pdx_symbol!='') || (isset($cc_mcc_symbol) && $cc_mcc_symbol!='') || (isset($cc_exclusion_symbol) && $cc_exclusion_symbol!='') || (is_array($gender) && !empty($gender)) || (is_array($poa) && !empty($poa)) || (is_array($other_symbols) && !empty($other_symbols))) {
		$xml->writeRaw("shifttab");
		$xml->startBranch('Symbols');
		if(isset($pdx_symbol) && $pdx_symbol!='')	
		{
			$xml->addNode("pdx_symbol","",array('href'=>'file://icons/pdx.eps'));
			$xml->writeRaw("nspace");
		}
		if(isset($cc_mcc_symbol) && $cc_mcc_symbol!='') 
		{ 
			if($cc_mcc_symbol=='CC')
			{
				$xml->addNode("cc_symbol","",array('href'=>'file://icons/cc.eps'));
			}
			if($cc_mcc_symbol=='MCC')
			{
				$xml->addNode("mcc_symbol","",array('href'=>'file://icons/mcc.eps'));
			}
			$xml->writeRaw("nspace");	
		}
		if(isset($cc_exclusion_symbol) && $cc_exclusion_symbol!='')	
		{
			$xml->addNode("cc_exclusion_symbol","",array('href'=>'file://icons/cc_exclusion.eps'));
			$xml->writeRaw("nspace");
		}
		if(is_array($gender) && !empty($gender)){
			if($gender[0]['newborn']=='1') { 
				$xml->addNode("newborn_symbol","",array('href'=>'file://icons/newborn.eps'));
				$xml->writeRaw("nspace");
			}
			if($gender[0]['pediatric']=='1') { 
				$xml->addNode("pediatric_symbol","",array('href'=>'file://icons/pediatric.eps'));
				$xml->writeRaw("nspace");
			}
			if($gender[0]['maternity']=='1') { 
				$xml->addNode("maternity_symbol","",array('href'=>'file://icons/maternity.eps'));
				$xml->writeRaw("nspace");
			}
			if($gender[0]['adult']=='1') {
				$xml->addNode("adult_symbol","",array('href'=>'file://icons/adult.eps'));
				$xml->writeRaw("nspace");
			}
			if($gender[0]['female']=='1') {  
				$xml->addNode("female_symbol","",array('href'=>'file://icons/female.eps'));
				$xml->writeRaw("nspace");
			}
			if($gender[0]['male']=='1'){ 
				$xml->addNode("male_symbol","",array('href'=>'file://icons/male.eps'));
				$xml->writeRaw("nspace");
			}
        } 
		if(is_array($poa) && !empty($poa))
		{
			$xml->addNode("poa_symbol","",array('href'=>'file://icons/poa.eps'));
			$xml->writeRaw("nspace");
		}
		if(strpos(strtolower($desc), 'unspecified') && strlen($code_title)>3)
		{
			$xml->addNode("unspecified","",array('href'=>'file://icons/unspecified.eps'));
			$xml->writeRaw("nspace");
		}
		if(strpos(strtolower($desc), 'other') && strlen($code_title)>3)
		{
			$xml->addNode("other_specified","",array('href'=>'file://icons/other_specified.eps'));
			$xml->writeRaw("nspace");
		}
		if($other_symbols){
			foreach ($other_symbols as $symbol) {
				$xml->addNode($symbol->code_symbol,"",array('href'=>'file://icons/'.$symbol->code_symbol.'.eps'));
				$xml->writeRaw("nspace");
			}
		}		
	$xml->endBranch();
	 }
}
function get_icd9_coding_clinic($code_title,$xml)
{
	$CI = get_instance();
	$coding_clinic = $CI->db->query("SELECT distinct concat(published_qtr,', ',published_year) publish_qtr FROM codingclinic_icd i join codingclinic_icdcodes c on c.codingclinic_id=i.detail_id WHERE c.code='".$code_title."' order by published_year, published_qtr")->result_array();
	if($coding_clinic){
		$xml->writeRaw("\n");
		$pub_qtr='<aha>AHA:</aha> ';
		foreach($coding_clinic as $clinic)
		{
			$pub_qtr .= $clinic['publish_qtr']."; ";
		}
		$pub_qtr = rtrim($pub_qtr,"; ");
		if(strlen($code_title)==3)
			$clinic_node = 'icd_coding_clinic_1';
		if(strlen($code_title)==5)
			$clinic_node = 'icd_coding_clinic_2';
		if(strlen($code_title)==6)
			$clinic_node = 'icd_coding_clinic_3';
		$xml->addNode($clinic_node, $pub_qtr);
		
	}
}
function get_child_codeset_icd9v1($childCodesets,$xml,$db_object,$i=1)
{
	foreach ($childCodesets as $key=>$singleCodeset) 
	{
		$xml->writeRaw("\n");
		$xml->startBranch('child_term_'.$singleCodeset->header);
		if($key==0 && $singleCodeset->header=='H1') 
		{
			//$xml->addNode("child_title_top", get_main_term($singleCodeset));
			create_index_term($xml, $singleCodeset, 'child_title_top');
		} 
		else 
		{
			//$xml->addNode("child_title_".$singleCodeset->header, get_main_term($singleCodeset));	
			create_index_term($xml, $singleCodeset, 'child_title_'.$singleCodeset->header);
		}
		/*if($singleCodeset->code) 
		{
			// Create Code Part along with corresponding digit-required symbol - start 
			create_index_code_node($singleCodeset->code,$xml,'code');
			// Create Code Part along with corresponding digit-required symbol - end 
		}*/
		if($child_codesets=$db_object->get_child_codeset($singleCodeset->id))
		{
			$i++;	
			get_child_codeset_icd9v1($child_codesets,$xml,$db_object,$i);
		}
		$xml->endBranch();
		//$xml->writeRaw("\n");
	} 
 }
function check_have_child($table, $code)
{
	$CI = get_instance();
	//echo "select have_child from $table where code_title='$code' and have_child>0";
	$code_have_child = $CI->db->query("select have_child from $table where code_title='$code' and have_child>0")->result_array();
	if(isset($code_have_child) && !empty($code_have_child))
	{
		return $code_have_child[0]['have_child'];
	}	
}
function create_index_term($xml, $indexArr, $code_tag_name)
{
	$icdcode = $indexArr->code;
	$index_term = '';
	if(isset($icdcode) && $icdcode!='') 
	{	
		if(strpos($icdcode,","))
		{
			//echo "==>".$icdcode."<==<br>";
			$codes = explode(",",$icdcode);
			foreach($codes as $c)
			{
				if(check_valid_code($c,'codeset_icdvol1'))
				{
					$have_child_digit = check_have_child('codeset_icdvol1',$c);
					if($have_child_digit)
					{
						//$xml->addNode('have_child',"",array('href'=>'file://icons/icd-0'.get_digit_from_havechild($have_child_digit,'icd9v1').'.eps'));
						$index_code .= $c.'nspace<have_child href="file://icons/icd-0'.get_digit_from_havechild($have_child_digit,'icd9v1').'.eps" />,';	
						//$xml->addNode($code_tag_name, $index_term1);
					}
					else
					{
						$index_code .= $c.",";
						//$index_term = $indexArr->title;
					}
				}
			}
			$index_code = rtrim($index_code,",");
			$index_term = explode(",",$index_code);
			$xml->addNode($code_tag_name, str_replace($codes,$index_term,$indexArr->title));
		}
		else
		{
			if(check_valid_code($icdcode,'codeset_icdvol1'))
			{
				$have_child_digit = check_have_child('codeset_icdvol1',$icdcode);
				if($have_child_digit)
				{
					//$xml->addNode('have_child',"",array('href'=>'file://icons/icd-0'.get_digit_from_havechild($have_child_digit,'icd9v1').'.eps'));
					$index_code = $icdcode.'nspace<have_child href="file://icons/icd-0'.get_digit_from_havechild($have_child_digit,'icd9v1').'.eps" />';	
					$index_term = str_replace($icdcode, $index_code, $indexArr->title);
					//$index_term1 = $indexArr->title;
				}
				else
				{
					$index_term = $indexArr->title;
				}
			}
			$xml->addNode($code_tag_name, $index_term);
		}
	}
	else
	{
	$xml->addNode($code_tag_name, $indexArr->title);
	}
}
function create_index_code_node($icdcode,$xml,$code_tag_name)	
{
	$xml->startBranch('icd9_code');
	if(strpos($icdcode,","))
	{
		$codes = explode(",",$icdcode);
		foreach($codes as $c)
		{
			if(check_valid_code($c,'codeset_icdvol1'))
			{
				$xml->addNode($code_tag_name, $c);
				$have_child_digit = check_have_child('codeset_icdvol1',$c);
				if($have_child_digit){
					$xml->addNode('have_child',"",array('href'=>'file://icons/icd-0'.get_digit_from_havechild($have_child_digit,'icd9v1').'.eps'));
				}
			}
		}
	}
	else
	{
		if(check_valid_code($icdcode,'codeset_icdvol1'))
			{
			$xml->addNode($code_tag_name, $icdcode);
			$have_child_digit = check_have_child('codeset_icdvol1',$icdcode);
			if($have_child_digit){
				$xml->addNode('have_child',"",array('href'=>'file://icons/icd-0'.get_digit_from_havechild($have_child_digit,'icd9v1').'.eps'));
			}
		}
	}
	$xml->endBranch();
}
function check_valid_code($code, $table)
{
	$CI = get_instance();
	$valid_codeset = $CI->db->query("select code_title from $table where code_title='".$code."'")->result_array();
	if(is_array($valid_codeset) && !empty($valid_codeset))
		return true;
	else
		return false;
}
function my_strip_tags($txt)
{
	/*$find = array('À','Á','Â','Ã','Ä','Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ð','Ñ','Ò','Ó','Ô','Õ','Ö','×','Ø','Ù','Ú','Û','Ü','Ý','Þ','ß','à','á','â','ã','ä','å','æ','ç','è','é','ê','ë','ì','í','î','ï','ð','ñ','ò','ó','ô','õ','ö','÷','ø','ù','ú','û','ü','ý','þ','ÿ');

	$replace = array('&#192;','&#193;','&#194;','&#195;','&#196;','&#197;','&#198;','&#199;','&#200;','&#201;','&#202;','&#203;','&#204;','&#205;','&#206;','&#207;','&#208;','&#209;','&#210;','&#211;','&#212;','&#213;','&#214;','&#215;','&#216;','&#217;','&#218;','&#219;','&#220;','&#221;','&#222;','&#223;','&#224;','&#225;','&#226;','&#227;','&#228;','&#229;','&#230;','&#231;','&#232;','&#233;','&#234;','&#235;','&#236;','&#237;','&#238;','&#239;','&#240;','&#241;','&#242;','&#243;','&#244;','&#245;','&#246;','&#247;','&#248;','&#249;','&#250;','&#251;','&#252;','&#253;','&#254;','&#255;');*/
	return strip_tags($txt);
}
function replace_icd9_meta_spaces($text)
{
		$val = trim(preg_replace(array('/\r/','/&nbsp;/','/\n/'),'',$text));
		$find = array('<b>',
					'</b>',
					'Use additional',
					'Code first',
					'3digitsAdditionalhead',
					'3digitsAdditionalline',
					'3digitsAdditionallist1',
					'3digitsAdditionallist2',
					'3digitsBracket',
					'3digitsCFST',
					'3digitsCFSTlist1',
					'3digitsCFSTlist2',
					'3digitsExcludeshead',
					'3digitsExcludesline',
					'3digitsExcludeslist1',
					'3digitsExcludeslist2',
					'3digitsExcludeslist3',
					'3digitsIncludeshead',
					'3digitsIncludesline',
					'3digitsIncludeslist1',
					'3digitsIncludeslist2',
					'3digitsIncludeslist3',
					'3digitsline',
					'3digitslist1',
					'3digitslist2',
					'3digitsNoteblock',
					'3digitsNotehead',
					'3digitsNoteline',
					'3digitsNotelist1',
					'3digitsNotelist2',
					'3tablelist1',
					'3tablelist2',
					'3tablelist3',
					'3table',
					'4digitsAdditionalhead',
					'4digitsAdditionalline',
					'4digitsAdditionallist1',
					'4digitsAdditionallist2',
					'4digitsBracket',
					'4digitsCFST',
					'4digitsCFSTlist1',
					'4digitsCFSTlist2',
					'4digitsExcludeshead',
					'4digitsExcludesline',
					'4digitsExcludeslist1',
					'4digitsExcludeslist2',
					'4digitsExcludeslist3',
					'4digitsIncludeshead',
					'4digitsIncludesline',
					'4digitsIncludeslist1',
					'4digitsIncludeslist2',
					'4digitsIncludeslist3',
					'4digitsline',
					'4digitslist1',
					'4digitslist2',
					'4digitsNoteblock',
					'4digitsNotehead',
					'4digitsNoteline',
					'4digitsNotelist1',
					'4digitsNotelist2',
					'4tablelist1',
					'4tablelist2',
					'4tablelist3',
					'4table',
					'5digitsAdditionalhead',
					'5digitsAdditionalline',
					'5digitsAdditionallist1',
					'5digitsAdditionallist2',
					'5digitsBracket',
					'5digitsCFST',
					'5digitsCFSTlist1',
					'5digitsCFSTlist2',
					'5digitsExcludeshead',
					'5digitsExcludesline',
					'5digitsExcludeslist1',
					'5digitsExcludeslist2',
					'5digitsExcludeslist3',
					'5digitsIncludeshead',
					'5digitsIncludesline',
					'5digitsIncludeslist1',
					'5digitsIncludeslist2',
					'5digitsIncludeslist3',
					'5digitsline',
					'5digitslist1',
					'5digitslist2',
					'5digitsNoteblock',
					'5digitsNotehead',
					'5digitsNoteline',
					'5digitsNotelist1',
					'5digitsNotelist2',
					'5tablelist1',
					'5tablelist2',
					'5tablelist3',
					'5table',
					'list1>0 ',		
					'list1>1 ',		
					'list1>2 ',		
					'list1>3 ',		
					'list1>4 ',		
					'list1>5 ',		
					'list1>6 ',		
					'list1>7 ',		
					'list1>8 ',		
					'list1>9 ',		
					);
		$replace = array('',
						'',
						'<use_additional>Use additional</use_additional> ',
						'<code_first>Code first</code_first> ',
						'"3digitsAdditionalhead"',
						'"3digitsAdditionalline"',
						'"3digitsAdditionallist1"',
						'"3digitsAdditionallist2"',
						'"3digitsBracket"',
						'"3digitsCFST"',
						'"3digitsCFSTlist1"',
						'"3digitsCFSTlist2"',
						'"3digitsExcludeshead"',
						'"3digitsExcludesline"',
						'"3digitsExcludeslist1"',
						'"3digitsExcludeslist2"',
						'"3digitsExcludeslist3"',
						'"3digitsIncludeshead"',
						'"3digitsIncludesline"',
						'"3digitsIncludeslist1"',
						'"3digitsIncludeslist2"',
						'"3digitsIncludeslist3"',
						'"3digitsline"',
						'"3digitslist1"',
						'"3digitslist2"',
						'"3digitsNoteblock"',
						'"3digitsNotehead"',
						'"3digitsNoteline"',
						'"3digitsNotelist1"',
						'"3digitsNotelist2"',
						'"3tablelist1"',
						'"3tablelist2"',
						'"3tablelist3"',
						'"3table"',
						'"4digitsAdditionalhead"',
						'"4digitsAdditionalline"',
						'"4digitsAdditionallist1"',
						'"4digitsAdditionallist2"',
						'"4digitsBracket"',
						'"4digitsCFST"',
						'"4digitsCFSTlist1"',
						'"4digitsCFSTlist2"',
						'"4digitsExcludeshead"',
						'"4digitsExcludesline"',
						'"4digitsExcludeslist1"',
						'"4digitsExcludeslist2"',
						'"4digitsExcludeslist3"',
						'"4digitsIncludeshead"',
						'"4digitsIncludesline"',
						'"4digitsIncludeslist1"',
						'"4digitsIncludeslist2"',
						'"4digitsIncludeslist3"',
						'"4digitsline"',
						'"4digitslist1"',
						'"4digitslist2"',
						'"4digitsNoteblock"',
						'"4digitsNotehead"',
						'"4digitsNoteline"',
						'"4digitsNotelist1"',
						'"4digitsNotelist2"',
						'"4tablelist1"',
						'"4tablelist2"',
						'"4tablelist3"',
						'"4table"',
						'"5digitsAdditionalhead"',
						'"5digitsAdditionalline"',
						'"5digitsAdditionallist1"',
						'"5digitsAdditionallist2"',
						'"5digitsBracket"',
						'"5digitsCFST"',
						'"5digitsCFSTlist1"',
						'"5digitsCFSTlist2"',
						'"5digitsExcludeshead"',
						'"5digitsExcludesline"',
						'"5digitsExcludeslist1"',
						'"5digitsExcludeslist2"',
						'"5digitsExcludeslist3"',
						'"5digitsIncludeshead"',
						'"5digitsIncludesline"',
						'"5digitsIncludeslist1"',
						'"5digitsIncludeslist2"',
						'"5digitsIncludeslist3"',
						'"5digitsline"',
						'"5digitslist1"',
						'"5digitslist2"',
						'"5digitsNoteblock"',
						'"5digitsNotehead"',
						'"5digitsNoteline"',
						'"5digitsNotelist1"',
						'"5digitsNotelist2"',
						'"5tablelist1"',
						'"5tablelist2"',
						'"5tablelist3"',
						'"5table"',
						'list1">0indenttoherenspace',		
						'list1">1indenttoherenspace',		
						'list1">2indenttoherenspace',		
						'list1">3indenttoherenspace',		
						'list1">4indenttoherenspace',		
						'list1">5indenttoherenspace',		
						'list1">6indenttoherenspace',		
						'list1">7indenttoherenspace',		
						'list1">8indenttoherenspace',		
						'list1">9indenttoherenspace',		
						);
		/*$val=str_replace('&lt;b&gt;','',$val);
		$val=str_replace('&lt;/b&gt;','',$val);
		$val=str_replace('</p><p','</p> newlinechar <p',$val);
		$val = strip_tags($val);
		$val=str_replace('Use additional','<use_additional>Use additional</use_additional> ',$val);	
		$val=str_replace('Code first','<code_first>Code first</code_first> ',$val);*/
		$val=str_replace($find, $replace, $val);
		if(substr_count($text,'Excludes: '))
		{
			$val=str_replace('Excludes: ','<exclude_tag>Excludes:</exclude_tag>nspace',$val);
		}
		else
		{
			$val=str_replace('Excludes:','<exclude_tag>Excludes:</exclude_tag>nspace',$val);
		}
		if(substr_count($text,'Note: '))
		{
			$val=str_replace('Note: ','<note_tag>Note:</note_tag>nspace',$val);
		}
		else
		{
			$val=str_replace('Note:','<note_tag>Note:</note_tag>nspace',$val);
		}
		
		if(substr_count($text,'Includes: '))
		{
			$val=str_replace('Includes: ','<include_tag>Includes:</include_tag>nspace',$val);
		}
		else
		{
			$val=str_replace('Includes:','<include_tag>Includes:</include_tag>nspace',$val);
		}
		if(substr_count($text,'<p class=5table>'))
		{
			$val=str_replace('<p class=5table>','<table_5><tr><td><p class=5table>',$val);
			$val = $val.'</td></tr></table_5>';
		}
		if(substr_count($text,'<p class=4table>'))
		{
			$val=str_replace('<p class=4table>','<table_4><tr><td><p class=4table>',$val);
			$val = $val.'</td></tr></table_4>';
		}
		if(substr_count($text,'<p class=3table>'))
		{
			$val=str_replace('<p class=3table>','<table_3><tr><td><p class=3table>',$val);
			$val = $val.'</td></tr></table_3>';
		}
		return $val;
}
?>
