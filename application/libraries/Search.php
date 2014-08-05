<?php
//ini_set('display_errors', 0);
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Description of SphinxSearch
 *
 * @author Pawan
 */
class Search {

    /**
     * Instance of the CodeIgnitor object
     * @var object
     */
    private $CI;

    /**
     * Instance of the Tools library
     * @var object
     */
    private $tools_library;

    /**
     * The search expiration time
     * @var int Time in seconds
     */
    private $search_expiration = 3600;

    /**
     * The default maximum results for a search to return
     * @var int
     */
    private $maximum_results_default = 5000;

    public function __construct() {
        $this->CI = & get_instance();
        $this->tools_library = $this->CI->tools;
		$this->CI->load->library('SphinxClient');
		$this->CI->load->model('codeset_combined'); 
		$this->CI->load->helper('specialty_corner_helper'); 
    }
 
    /**
     * Performs a Sphinx search
     */
    public function search($filter,$q='',$searchtype='tabcode',$page=1,$search_and='',$negative_word='',$force_search_pref='',$parent='',$sort='',$timefilter='',$limit='',$start='',$end='') {
	
		$sphinx_conf['sphinx_host'] = $this->CI->config->item('sphinx_server');
		$sphinx_conf['sphinx_port'] = $this->CI->config->item('sphinx_server_port'); //this demo uses the SphinxAPI interface
		#can use 'excerpt' to highlight using the query, or 'asis' to show description as is.
		$sphinx_conf['body'] = $this->CI->config->item('sphinx_body');
		#the link for the title (only $id) placeholder supported
		//$sphinx_conf['link_format'] = "";
		#Change this to FALSE on a live site!
		$sphinx_conf['debug'] = $this->CI->config->item('sphinx_debug');
		#How many results per page
		$sphinx_conf['page_size'] = $this->CI->config->item('sphinx_page_size');
		#maximum number of results - should match sphinxes max_matches. default 1000
		$sphinx_conf['max_matches'] = $this->CI->config->item('sphinx_max_matches');
		$resultCount = 0;
		$count_array = array();
		$items = array();
		$result_string = '';
		$spell = '';
		//echo $searchtype;die;
		if($search_and)
		{
  	    	//$mode = SPH_MATCH_ALL;//3rd May
  	    	$mode = SPH_MATCH_EXTENDED2;
		}
		else
		{
			$mode = SPH_MATCH_ANY;
		}
		if (!empty($q)) 
		{
			//produce a version for display
			$qo = $q;
			
			//// spelling suggestions
			//$spell =  $this->spellcheck($q);
			////if no suggession for spelling pass an  array with first node 'none' 
			if(!is_array($spell)) $spell = array(0=>'none');
			///////////////////////////////////////////////////////////////////////			
			
			if($searchtype=='tabadvance')
			{	//echo $page;
				
				$sphinx_conf['sphinx_index'] = "combined_index_docs";
					//setup paging...
					if (!empty($page) || $limit=='') {
						$currentPage = intval($page);
						if (empty($currentPage) || $currentPage < 1) {$currentPage = 1;}
						
						$currentOffset = ($currentPage -1)* $sphinx_conf['page_size'];
						
						if ($currentOffset > ($sphinx_conf['max_matches']-$sphinx_conf['page_size']) ) {
							die("Only the first {$sphinx_conf['max_matches']} results accessible");
						}
					} else {
						$currentPage = 1;
						$currentOffset = 0;
					}
					$codeset_cats = array('7'=>'CPT',
						'8'=>'ICD-9-CM',
						'9'=>'HCPCS II',
						'10'=>'CCI Edit',
						'201'=>'DRG',
						'202'=>'ICD9-V3',
						'203'=>'ICD-10',
						'204'=>'APC',
						'205'=>'CPT Modifier',
						'206'=>'HCPCS Modifiers'
						);
					$cms_cats = array('20'=>'Transmittals',
						'21'=>'E&amp;M Guidelines',
						'22'=>'Claims Processing Manuals',
						'23'=>'CMS/MLN Specialty Book',
						'179'=>'Forms'
						);
					$articles_cats = array('165'=>'Anesthesia Coding Alert',
						'180'=>'Emergency Department Coding &amp; Reimbursement Alert',
						'181'=>'Family Practice Coding Alert',
						'182'=>'Gastroenterology Coding Alert',
						'183'=>'General Surgery Coding Alert',
						'184'=>'Medical Office Billing &amp; Collections Coding Alert',
						'186'=>'Neurology &amp; Pain Management Coding Alert',
						'187'=>'Neurosurgery Coding Alert',
						'188'=>'Ob-Gyn Coding Alert',
						'189'=>'Oncology &amp; Hematology Coding Alert',
						'190'=>'Ophthalmology Coding Alert',
						'191'=>'Optometry Coding &amp; Billing Alert',
						'192'=>'Orthopedic Coding Alert',
						'193'=>'Otolaryngology Coding Alert',
						'194'=>'Pathology/Lab Coding Alert',
						'195'=>'Part B Insider (Multispecialty) Coding Alert',
						'196'=>'Pediatric Coding Alert',
						'197'=>'Physical Medicine &amp; Rehab Coding Alert',
						'198'=>'Pulmonology Coding Alert',
						'199'=>'Radiology Coding Alert',
						'200'=>'Urology Coding Alert',
						'45991'=>'Internal Medicine Coding Alert',
						'45992'=>'Cardiology Coding Alert',
						'52728'=>'Podiatry Coding &amp; Billing Alert',
						'52729'=>'Dermatology Coding Alert',
						'57309'=>'ICD 10 Coding Alert',
						'57790'=>'Inpatient Facility Coding & Compliance Alert',
						'57791'=>'Outpatient Facility Coding Alert',
						'58172'=>'Psychiatry Coding &amp; Reimbursement Alert',
						'58290'=>'Home Care Week',
						'58291'=>'Health Information Compliance Alert',
						'58292'=>'Eli\'s Hospice Insider',
						'58293'=>'Home Health ICD-9 Alert',
						'58294'=>'Long-Term Care Survey Alert',
						'58295'=>'MDS Alert',
						'58296'=>'Medicare Compliance & Reimbursement',
						'58297'=>'OASIS Alert',
						'99371'=>'Behavioral Healthcare Alert',
						'99909'=>'E/M Coding Alert'
									);
					$guides_cats = array(
									'57318'=>'2012 ICD Specialty Guide',
									'57717'=>'2012 Pediatric Coding Survival Guide',
									'57779'=>'2012 Ob-Gyn Coding Survival Guide',
									'57785'=>'2012 Coding and Reimbursement Survival Guide',
									'57792'=>'2012 Part B Insider Survival Guide',
									'57794'=>'2012 Pathology Laboratory Survival Guide',
									'57810'=>'2012 Radiology Coding Survival Guide',
									'99637'=>'2013 Cardiology Survival Guide',
									'99662'=>'2013 EM Survival Guides',
									'99663'=>'2013 Physician Coding Update',
									'100324'=>'2013 Urology Survival Guide',
									'100325'=>'2013 Gastroenterology Survival Guide',
									'100326'=>'2013 General Surgery Survival Guide',
									'100327'=>'2013 Otolaryngology Survival Guide'
									);					
					$CatFullName=array('aca'=>'Anesthesia Coding Alert',
					'eca'=>'Emergency Department Coding & Reimbursement Alert',
					'fca'=>'Family Practice Coding Alert',
					'gac'=>'Gastroenterology Coding Alert',
					'gca'=>'General Surgery Coding Alert',
					'mob'=>'Medical Office Billing & Collections Alert',
					'nca'=>'Neurology & Pain Management Coding Alert',
					'nec'=>'Neurosurgery Coding Alert',
					'oca'=>'Ob-Gyn Coding Alert',
					'onc'=>'Oncology & Hematology Coding Alert',
					'opc'=>'Ophthalmology Coding Alert',
					'opt'=>'Optometry Coding & Billing Alert',
					'orc'=>'Orthopedic Coding Alert',
					'otc'=>'Otolaryngology Coding Alert',
					'pac'=>'Pathology/Lab Coding Alert',
					'pbi'=>'Part B Insider (Multispecialty)',
					'pca'=>'Pediatric Coding Alert',
					'pmc'=>'Physical Medicine & Rehab Coding Alert',
					'puc'=>'Pulmonology Coding Alert',
					'rca'=>'Radiology Coding Alert',
					'uca'=>'Urology Coding Alert',
					'ica'=>'Internal Medicine Coding Alert',
					'cca'=>'Cardiology Coding Alert',
					'pod'=>'Podiatry Coding & Billing Alert',
					'der'=>'Dermatology Coding Alert',
					'ict'=>'ICD 10 Coding Alert',
					'drg'=>'Inpatient Facility Coding & Compliance Alert',
					'ofc'=>'Outpatient Facility Coding Alert',
					'psy'=>'Psychiatry Coding & Reimbursement Alert',
					'hop'=>'Hospice Insider',
					'hcw'=>'Eli\'s Home Care Week',
					'icd'=>'Home Health ICD-9 Alert',
					'Lsa'=>'Long-Term Care Survey Alert',
					'mds'=>'MDS Alert',
					'osa'=>'Eli\'s OASIS Alert',
					'mlr'=>'Medicare Compliance and Reimbursement',
					'hica'=>'Health Information Compliance Alert',
					'bha'=>'Behavioral Healthcare Alert',
					'enm'=>'E/M Coding Alert');

					$specialty_master_array= array('165'=>'29','45992'=>'30','52729'=>'31','56739'=>'52','180'=>'32','181'=>'33','182'=>'34','183'=>'35','56738'=>'54','45991'=>'36','184'=>'37','186'=>'62','187'=>'38','188'=>'63','56740'=>'53','189'=>'39','190'=>'40','191'=>'41','192'=>'42','193'=>'43','194'=>'45','195'=>'44','196'=>'46','197'=>'47','52728'=>'48','58172'=>'69','198'=>'49','199'=>'50','200'=>'51','58292'=>'70','58290'=>'71','58293'=>'72','58294'=>'73','58295'=>'74','58297'=>'75','58296'=>'76','58291'=>'77','99371'=>'84','99909'=>'85');
					
					$user_id = '';
					$usersarticles = array();
					$user_data=$this->CI->session->userdata('user'); // User Session Data
					if(!empty($user_data['user_id'])) // If user ID is available
					{
						$user_id=$user_data['user_id'];	
						$usersarticles = get_user_article_access_data($user_id);
						//print_r($usersarticles);die;
					}
					
					//Connect to sphinx, and run the query
					$cl_count = new SphinxClient();
					$cl = new SphinxClient();
					$cl->SetServer($sphinx_conf['sphinx_host'], $sphinx_conf['sphinx_port']);
					$cl_count->SetServer($sphinx_conf['sphinx_host'], $sphinx_conf['sphinx_port']);
					if($limit==1)
					{
						$sort='date';
					}
					if($sort=='date')
					{
						$cl->SetSortMode(SPH_SORT_ATTR_DESC, "date_u" );
					}elseif($sort=='title')
					{
						$cl->SetSortMode(SPH_SORT_ATTR_ASC, "title" );
					}elseif($parent=='' && $filter=='' && ($sort=='' || $sort=='relevance'))
					{
						$cl->SetSortMode(SPH_SORT_EXPR, "@weight + IF(parent_id ==13, 500, 0)");
					}

					if($negative_word!='')
					{
						$cl_count->SetMatchMode ( SPH_MATCH_EXTENDED2 );
						$cl->SetMatchMode ( SPH_MATCH_EXTENDED2 );
						$q = $q." !".$negative_word;
					}else
					{
						$cl_count->SetMatchMode($mode);
						$cl->SetMatchMode($mode);
					}
					$cl_count->SetRankingMode ( SPH_RANK_SPH04 );
					$cl->SetRankingMode ( SPH_RANK_SPH04 );
					// Add filters
					// Add filters
					$startTime = mktime();     
					$endTime = mktime();    

					switch($timefilter)
					{
						case '0':
							
						break;
						case '1':
							$startTime = mktime() - 30*3600*24;
							$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
							$cl_count->SetFilterRange( "date_u", $startTime, $endTime, false );
						break;
						case '3':
							$startTime = mktime() - 3*30*3600*24;
							$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
							$cl_count->SetFilterRange( "date_u", $startTime, $endTime, false );
						break;
						case '12':
							$startTime = mktime() - 12*30*3600*24;
							$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
							$cl_count->SetFilterRange( "date_u", $startTime, $endTime, false );
						break;
						case '36':
							$startTime = mktime() - 36*30*3600*24;
							$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
							$cl_count->SetFilterRange( "date_u", $startTime, $endTime, false );
						break;
						case '60':
							$startTime = mktime() - 60*30*3600*24;
							$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
							$cl_count->SetFilterRange( "date_u", $startTime, $endTime, false );
						break;
						case 'range':
							$startTime = strtotime($start);
							$endTime = strtotime($end); 
							if($start!='' && $end!='')
							{
								$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
								$cl_count->SetFilterRange( "date_u", $startTime, $endTime, false );
							}
						break;						
					}	
					$cl_count->SetLimits(0,100);
					$cl_count->setGroupBy('doc_type',SPH_GROUPBY_ATTR, '@count desc' );	
					//$cl->setGroupDistinct('doc_type');
					$res_for_counts = $cl_count->Query($q, $sphinx_conf['sphinx_index']);
					//print_r($res_for_counts); die;
						if ( !empty($res_for_counts) && isset($res_for_counts["matches"]) && is_array($res_for_counts["matches"])) {
							//Build a list of IDs for use in the mysql Query and looping though the results
							//$items = $res_for_counts["matches"];
							$ids_array = $res_for_counts["matches"];
							
							$count_array['17'] = 0;
							$count_array['13'] = 0;
							$count_array['15'] = 0;
							$count_array['2'] = 0;
							$count_array['1'] = 0;
							$count_array['99999'] = 0;
							$count_array['5'] = 0;
							$count_array['50053']=0;
							$count_array['50055']=0;
							$count_array['50056']=0;
							$count_array['50058']=0;
							$count_array['50060']=0;
							$count_array['45516']=0;
							for($doc_id=1;$doc_id<=200;$doc_id++)
							{
								$doc_str = 50000 + $doc_id;
								$count_array[$doc_str]=0;
							}
							foreach($specialty_master_array as $pubid=>$pubmaster)
							{
								$count_array[$pubid] = 0;
							}
							foreach($guides_cats as $suvid=>$suvname)
							{
								$count_array[$suvid] = 0;
							}
							foreach($ids_array as $match)
							{	
								if(array_key_exists($match['attrs']['@groupby'],$cms_cats)) 
								{
									if(isset($count_array[$match['attrs']['@groupby']]))
									{
										$count_array[$match['attrs']['@groupby']]+= $match['attrs']['@count'];
									}else
									{
										$count_array[$match['attrs']['@groupby']] =$match['attrs']['@count'];
									}
									$count_array['17']+= $match['attrs']['@count'];
								}elseif(array_key_exists($match['attrs']['@groupby'],$articles_cats)) 
								{
									$count_array['13']+= $match['attrs']['@count'];
									$count_array[$match['attrs']['@groupby']]= $match['attrs']['@count'];
									if(in_array($match['attrs']['document_id'],$usersarticles))
									{
										$count_array['1']++;
									}
								}elseif(array_key_exists($match['attrs']['@groupby'],$guides_cats)) 
								{
									$count_array['15']+= $match['attrs']['@count'];
									$count_array[$match['attrs']['@groupby']]= $match['attrs']['@count'];
									if($match['attrs']['@groupby']==57785 || $match['attrs']['@groupby']==99662 || $match['attrs']['@groupby']==57318 || $match['attrs']['@groupby']==57792 || $match['attrs']['@groupby']==57717 || $match['attrs']['@groupby']==1672 || $match['attrs']['@groupby']==1653)
									{
										$count_array['99999']+= $match['attrs']['@count'];
									}
								}elseif($match['attrs']['@groupby']=='10001') 
								{//cpt assistant
									$count_array['2']+= $match['attrs']['@count'];
								}elseif($match['attrs']['@groupby']=='10002') 
								{//hcpcs code clinic
									$count_array['3']+= $match['attrs']['@count'];
								}elseif($match['attrs']['@groupby']=='10003') 
								{//icd code clinic
									$count_array['4']+= $match['attrs']['@count'];
								}elseif(($match['attrs']['@groupby']>'50000' && $match['attrs']['@groupby']<'52000') || $match['attrs']['@groupby']=='45516')
								{
									if(isset($count_array[$match['attrs']['@groupby']]))
									{
										$count_array[$match['attrs']['@groupby']]+= $match['attrs']['@count'];
									}else
									{
										$count_array[$match['attrs']['@groupby']] =$match['attrs']['@count'];
									}
									$count_array['5']++;
									if(in_array($match['attrs']['@groupby'], array('50035','50037','50036','50004','50007','45516')))
									{
										$count_array['17']+= $match['attrs']['@count'];
									}
									if(in_array($match['attrs']['@groupby'], array('50024','50016','50017','50020','50018','50040','50023','50025')))
									{
										$count_array['50060']+= $match['attrs']['@count'];
									}
									if(in_array($match['attrs']['@groupby'], array('50015','50019','50012','50022','50026','50011','50013')))
									{
										$count_array['50058']+= $match['attrs']['@count'];
									}
									if(in_array($match['attrs']['@groupby'], array('50032','50033','50034','50047')))
									{
										$count_array['50055']+= $match['attrs']['@count'];
									}
									if(in_array($match['attrs']['@groupby'], array('50111','50155','50110','50112','50113','50115','50156','50157')))
									{
										$count_array['50068']+= $match['attrs']['@count'];
										$count_array['50056']+= $match['attrs']['@count'];
									}
									if(in_array($match['attrs']['@groupby'], array('50039')))
									{
										$count_array['50056']+= $match['attrs']['@count'];
									}
								}
								if(is_logged_in())
								{
									if(array_key_exists($match['attrs']['@groupby'],$specialty_master_array) && has_access($specialty_master_array[$match['attrs']['@groupby']]))
									{
										$count_array['1']+= $match['attrs']['@count'];
									}
								}								
							}
							
						}
						
					//$cl->SetLimits($currentOffset,$sphinx_conf['page_size']); //current page and number of results
					if($limit==1)
						$cl->SetLimits($currentOffset,1); //current page and number of results
					else
						$cl->SetLimits($currentOffset,$sphinx_conf['page_size']); //current page and number of results
					$slug_arr = array();
					$parent_arr = array();
					$filter_string = "*, ";
					if($parent!='')
					{
						$parent_arr = explode(",",$parent);
						if(count($parent_arr)>0)
						{
							//$cl->SetFilter('parent_id',array_diff($parent_arr, array('1')));
							foreach($parent_arr as $p_id)
							{
								if($p_id!='1')//parent_id=1 is for my subscriptions
								{
									$filter_string .= " parent_id=".$p_id." OR ";
								}else{
									if(is_logged_in())
									{	
										//$this->data['css'][]='front/usersearchsettings.css';
										if(is_array($usersarticles) && count($usersarticles))
										{
											foreach($usersarticles as $document_id)
											$filter_string .= " post_id=".$document_id." OR ";
										}
									}
								}
							}
								
						}
					}
					if($filter!='')
					{
						$slug_arr = explode(",",$filter);
						if(count($slug_arr)>0)
						{
							//$cl->SetFilter('doc_type',$slug_arr);
							foreach($slug_arr as $c_id)
							$filter_string .= " doc_type=".$c_id." OR ";
						}
					}
					if(in_array(1,$parent_arr))//collecting doc_type for my subscrition
					{
						foreach($specialty_master_array as $spl_doc_type=>$spl_master_id)
						{
							if(has_access($spl_master_id))
							{
								$filter_string .= " doc_type=".$spl_doc_type." OR ";
							}
						}
					}
					if($filter_string!='*, ')
					{
						$filter_string = trim($filter_string,' OR');
						$filter_string .= '  AS mycond ';
						//echo $filter_string;die;
						$cl->SetSelect( $filter_string );
						$cl->SetFilter( "mycond", array(1) );
						//echo $filter_string;die;
					}
					$res = $cl->Query($q, $sphinx_conf['sphinx_index']);
					//echo "Hello";
					//print_r($res);die;
					$result_string = "";
					$resultCount = 0;
					if (empty($res) || !isset($res["matches"])) {
					} else {
						$resultCount = $res['total_found'];
						$numberOfPages = ceil($res['total']/$sphinx_conf['page_size']);
					}
					if ( !empty($res) && isset($res["matches"]) && is_array($res["matches"])) {
						$items = $res["matches"];
					}
				/////Start processing for advanced search.
					$master_array = array();
					$msg = "";
					//$items = $rows;
					if($limit==1)
						$totalResults = 1;
					else
						$totalResults = $resultCount;

					$startIndex = $page;
					
					$itemsPerPage = $this->CI->config->item('sphinx_page_size');
					if((int)$totalResults>=1000) $msg = 'The searched term returns large number of search results. Please add more search keywords to get specific results';
					
					
					///// totalResults - get total value returned by sphinx
					///// startindex - page page number as stated by sphinx
					///// itemsPerPage - number of items per page
					///// currentpage - current page number
					///// spelling - Spelling sugession 
					///// msg - any message to be sent along with data
					if($totalResults>0){
						$master_array = array(
									'totalResults'=>$totalResults,
									'startIndex'=>$startIndex,
									'itemsPerPage'=>$itemsPerPage,
									'currentpage'=>$page,
									'spelling'=>'',
									'result'=>$master_array,
									'msg'=>$msg,
									'countarray'=>$count_array);
					}else{

						$master_array = array(
									'totalResults'=>$totalResults,
									'startIndex'=>$startIndex,
									'itemsPerPage'=>$itemsPerPage,
									'currentpage'=>$page,
									'spelling'=>$spell,
									'result'=>$master_array,
									'msg'=>$msg,
									'countarray'=>$count_array);
					}
					
					///// traverse each item and copy to array
					//$counter = 0;
					//echo "##comming<pre>";print_r($items);echo "##";
					foreach($items as $id=>$match) {
						$item = $match['attrs'];
						//print_r($item);
						//$counter++;
						//$serial_number = ($page - 1)*$itemsPerPage + $counter;
						$doc_type = $item['doc_type'];
						$controller="";
						$postfix_url = '';
						$link = '';
						$pdflink = '';
						$titles = '';
						$type = '';
						$pre_title ='';
						
						if($doc_type>=1653 && $doc_type<=1690)
						{
							$type = 'Claim Processing Manual';
							$controller = 'exclusives/'.$cat_slug[$item['doc_type']].'/';
							$postfix_url = '';
							//$titles = $serial_number.". ".$pre_title.$this->mystriptag($item['name']);
							$titles = preg_replace("/[^a-zA-Z0-9\s]/", "", $item['title']);
							$link = current_base_url().$controller.$item['post_name'].$postfix_url;	
							$pdflink = current_base_url().$controller.$item['post_name'].$postfix_url;	
							$context = $item['name'];						
						}elseif(array_key_exists($doc_type,$cms_cats)) 
						{
							switch($item['doc_type'])
							{
								case 20: 
									//Extract tranmittal number from taxonomy
									preg_match('{(\d+)}', $item['taxonomy'], $m); 
									if(isset($m[1]))
									$pre_title = 'Transmittal No. '.$m[1].' '; 
									$type = 'Transmittal';								
									break;
								case 21: 
									$type = 'Evaluation Management';								
									break;
								case 22: 
									$type = 'Claim Processing Manual';								
									break;
								case 23: 
									$type = 'MLN Book';								
									break;
								case 179:
									$type = 'Form';								
									break;
							}
							$cat_slug = array('20'=>'transmittals',
											  '21'=>'evaluation-management',
											   '22'=>'claims-processing-manuals',
											   '23'=>'mln-specialty-book',
											   '179'=>'forms');
							$controller = 'exclusives/'.$cat_slug[$item['doc_type']].'/';
							$postfix_url = '';
							//$titles = $serial_number.". ".$pre_title.$this->mystriptag($item['name']);
							$titles = $this->mystriptag($item['title']);//preg_replace("/[^a-zA-Z0-9\s]/", "", $item['title']);
							$link = current_base_url().$controller.$item['post_name'].$postfix_url;	
							$pdflink = current_base_url().$controller.$item['post_name'].$postfix_url;	
							$context = $item['name'];
						}elseif(array_key_exists($doc_type,$articles_cats))
						{
							$speciality_url = get_specialty_url_by_slug($item['slug']);
							$controller = 'coding-newsletters/my-'.$speciality_url.'/';
							$postfix_url = '-article';
							$titles = $this->mystriptag($item['title']);
							$context = $CatFullName[$item['slug']]." - ".$item['pdf_name'];
							if(is_logged_in() && isset($specialty_master_array[$doc_type]) && has_access($specialty_master_array[$doc_type]) || in_array($item['document_id'],$usersarticles))
							{								
								$pdflink = current_base_url().'scc_articles/view_pdf/'.$item['pdf_year'].'-'.$item['specialty'].'-'.$item['pdf'];
							}
							$link = current_base_url().$controller.$item['post_name'].$postfix_url;							
							$type = 'Article';								
						}elseif(array_key_exists($doc_type,$guides_cats))
						{
							$controller = 'coding-references/'.$item['slug'].'/';
							$postfix_url = '';
							$titles = preg_replace("/[^a-zA-Z0-9\s]/", "", $item['title']);
							$context = preg_replace("/[^a-zA-Z0-9\s]/", "", $item['name']);
							$link = current_base_url().$controller.$item['post_name'].$postfix_url;							
							$type = 'Survival Guide';								
						}elseif($doc_type=='10001')
						{
							$controller = 'cpt_assistant/cpt_assistant_details/';
							$titles = $item['title'];//preg_replace("/[^a-zA-Z0-9\s]/", "", str_ireplace('CPT', 'CPT&reg;',$item['title']));
							if(!empty($item['pdf_year']))
							{
								$context = "CPT&reg; Assistant ".$item['pdf_name'];
							}else
							{
								$context = "CPT&reg; Assistant ".substr($item['post_date'],0,4).";";
							}
							$link = current_base_url().$controller.($id-200000000);							
						}elseif($doc_type=='10002')
						{
							$controller = 'hcpcs_coding_clinic/codingclinic_hcpcs_details/';
							$titles = $item['title'];
							if(!empty($item['pdf_name']))
							{
								$context = "AHA HCPCS Coding Clinic ".$item['pdf_name'];
							}else
							{
								$context = "AHA HCPCS Coding Clinic ".substr($item['publish_date'],0,4).";";
							}
							$link = current_base_url().$controller.($id-300000000);;							
						}elseif($doc_type=='10003')
						{
							$controller = 'icd_coding_clinic/codingclinic_icd_details/';
							$titles = $item['title'];
							if(!empty($item['pdf_name']))
							{
								$context = "AHA ICD-9-CM Coding Clinic ".$item['pdf_name'];
							}else
							{
								$context = "AHA ICD-9-CM Coding Clinic ".substr($item['publish_date'],0,4).";";
							}
							$link = current_base_url().$controller.($id-400000000);
						}elseif(($doc_type>'50000' && $doc_type<'52000') || $doc_type=='45516')
						{
							$titles = $item['title'];
							if(!empty($item['pdf_name']))
							{
								//$link = current_base_url()."webroot/upload/general_pages_docs/document/".$item['pdf_name'];
								$link = current_base_url()."webroot/document/".$item['pdf_name'];
							}else
							{
								$link = $item['pdf'];
							}
							$context = "Document ";
							if($item['post_date']!='0000-00-00 00:00:00' && $item['post_date']!=''){
								$context .=" posted on ".substr($item['post_date'],0,10);
							}
						}else
						{
							continue;
						}
						///// populate page title and links for advanced search
						$master_array['result'][] = array(
										'type'=>$type,
										'title'=>str_replace(array('\\','"'),array('\\\\','\"'),$titles),
										'link'=>$link,'linkpdf'=>$pdflink,'context'=>str_replace(array('\\','"'),array('\\\\','\"'),$context),
										);
							
					}
					///// convert the master array to json string
					$output = $this->array_to_json_string_advance($master_array);
				
					//// Output the json string
					return $output;
					
					/////////////////////
					//return $jsonresult;

			}
			elseif($searchtype=='tabcode')
			{
				$ignore_words = array('/cpt/','/hcpcs/','/icd/','/icd-9/','/icd-9-cm/','/icd9/','/icd-10/','/icd-10-cm/','/icd10/','/drg/','/apc/','/code/');
				$sphinx_conf['sphinx_index'] = "combined_index_codes";
				$sphinx_conf['sphinx_index1'] = "combined_index_codes1";
				$code_type_arr = array();
				$q = strtolower($q);
				$q= preg_replace($ignore_words,'',$q);
				// set filter attributes
				$IDs_combined = '';
				$index_ids_combined = '';
				if($filter!='')
				{
				
					$ids = array();
					$index_ids = array();
					$code_type_arr = explode(",",$filter);
					$count = count($code_type_arr);
					for($i=0;$i<$count;$i++)
					{
						if($code_type_arr[$i]=='204' && !has_access('7'))
						{
							unset($code_type_arr[$i]);
						}
						if($code_type_arr[$i]=='202' && !has_access('3'))
						{
							unset($code_type_arr[$i]);
						}
						if($code_type_arr[$i]=='201' && !has_access('6'))
						{
							unset($code_type_arr[$i]);
						}
					}
					
					if(count($code_type_arr)>0)
					foreach($code_type_arr as $option)
					{
						$filter_array = array($option);
						//echo $option;
						//print_r($filter_array);
						$sp = new SphinxClient();
						$sp->SetServer($sphinx_conf['sphinx_host'], $sphinx_conf['sphinx_port']);
						$results_index = '';
						$results = '';
						if($negative_word!='')
						{
							//$sp->SetMatchMode ( SPH_MATCH_EXTENDED2 );
							//$q = $q." !".$negative_word;
						}else
						{
							$sp->SetMatchMode ( SPH_MATCH_ALL );
							
							$sp->SetRankingMode ( SPH_RANK_SPH04 );
							$sp->SetArrayResult(true);
							$sp->SetLimits(0,10000);
							//$sp->setGroupBy('codeset_combined_id',SPH_GROUPBY_ATTR);
							$sp->SetFilter('code_type_int',$filter_array);
							$results_index = $sp->Query($q, $sphinx_conf['sphinx_index1']);
						}
//print_r($results_index);
						if(empty($results_index) || !isset($results_index['total_found']) || (isset($results_index['total_found']) && $results_index['total_found']==0))
						{
							$sp1 = new SphinxClient();
							$sp1->SetServer($sphinx_conf['sphinx_host'], $sphinx_conf['sphinx_port']);
							if($negative_word!='')
							{
								$sp1->SetMatchMode ( SPH_MATCH_EXTENDED2 );
								$q = $q." !".$negative_word;
							}else
							{
								$sp1->SetMatchMode ( SPH_MATCH_ALL );
							}
							$sp1->SetRankingMode ( SPH_RANK_SPH04 );
							$sp1->SetArrayResult(true);
							$sp1->SetLimits(0,3000);
							$sp1->SetFilter('code_type_int',$filter_array);
							$results = $sp1->Query($q, $sphinx_conf['sphinx_index']);
							//print_r($results);
							unset($sp1);
							if (isset($results['matches']) && is_array($results['matches'])  ) 
							{
								foreach($results['matches'] as $match1)
								{	
									if(isset($match1['id']))
										$ids[]= $match1["id"];	
								}
							}							
						}elseif (isset($results_index['matches']) && is_array($results_index['matches']) && $negative_word=='' ) 
						{
							foreach($results_index['matches'] as $match2)
							{	
								if(isset($match2['attrs']['codeset_combined_id']))
									$ids[]= $match2['attrs']['codeset_combined_id'] ;	
								if(isset($match2['id']))
										$index_ids[]= $match2["id"];									
							}
						}
						unset($sp);
					}
					$ids = array_unique($ids);
					$IDs_combined = implode(',',$ids);
					$index_ids = array_unique($index_ids);
					$index_ids_combined = implode(',',$index_ids);
				}
				//echo "##Coming##";
				//echo $IDs_combined."##########";
				//echo $index_ids_combined;die;
				if ((empty($IDs_combined) || $IDs_combined=='') && (empty($index_ids_combined) || $index_ids_combined=='')) 
				{
				//echo "comming1";die;
					return 	$output= 
								'{
									"spelling":'.$this->array_to_json_string_advance($spell).',
									"msg":"",
									"response":"",
									"type1":"",
									"treeview":""
								 }';
				} 
				else {
					//Build a list of IDs for use in the mysql Query and looping though the results
					$IDs_combined = trim($IDs_combined,",");
					$codeset_obj = new Codeset_combined();

					$rows  = $codeset_obj->search($IDs_combined,$index_ids_combined);
//print_r($rows);die;
					$msg = '';
					$response = '';
					$type1 = '';

					/////check if user logged in and has cpt codes access
					$login = is_logged_in();//is_user_logged_in();
					$user_search_pref = array();
					$current_user = $this->CI->session->userdata('user');
					if(isset($current_user['user_search_pref']) && $current_user['user_search_pref']!='')
					{
						$user_search_pref=unserialize($current_user['user_search_pref']);
					}
					$keyword = $q;
					$keyword = stripslashes(strtolower(str_replace(array('\'','-',' ',','),array('','-','+','+'),urldecode(trim($keyword))))); 
					$tmpkeyword_array = explode('+',$keyword);
					$tmpcount = count($tmpkeyword_array);
				
					$keyword = implode('+',$tmpkeyword_array);
					
					
					///// for codesearch enable no of records per lookup as 1000
					///// for advance search set 100 for each page
					
					///// use 'AND' as default word join, if not passed
					
					///// start** check if we can treat the entire passed keyword as codeset, useful for comma/space separated multiple code
					$iscodeset = true;
					$keyword_array = explode('+',$keyword);
					
					
					foreach ($keyword_array as $key_word){
						$key_word = trim($key_word);
						if($key_word!="") {
							$iscodeset = $this->check_if_codeset($key_word);
							if(!$iscodeset) break;
						}	
					}
					///// end** check for codeset
				
			
					$siteurl = current_base_url();
					
					$items = $rows;	
					$totalResults = count($rows);
					
					$master_array = array();
					$master_array['CPT'] = array();
					$master_array['ICD'] = array();
					$master_array['HCPCS'] = array();
					$master_array['DRG'] = array();
					$master_array['APC'] = array();
					$master_array['ICD10'] = array();
					$master_array['CPT-Modifier'] = array();
					$master_array['HCPCS-Modifier'] = array();
					$master_array['PCS'] = array();
					
					$morelinks['ICDVOL3'] = array();
					$morelinks['CPT-Modifier'] = array();
					$morelinks['HCPCS-Modifier'] = array();
					///// start** set codeset urls for redirection and links
					$morelinks['CPT']['detail'] = $siteurl."cpt-codes/%s";
					$morelinks['CPT']['listing'] = $siteurl."cpt-codes-range/%s/?code=%s";
					$morelinks['CPT']['range'] = $siteurl."cpt-codes-range/%s/";
					
					$morelinks['HCPCS']['detail'] = $siteurl."hcpcs-codes/%s";
					$morelinks['HCPCS']['listing'] = $siteurl."hcpcs-codes-range/%s/?code=%s";
					$morelinks['HCPCS']['range'] = $siteurl."hcpcs-codes-range/%s/";
					
					$morelinks['ICD']['detail'] = $siteurl."icd9-codes/%s";
					$morelinks['ICD']['listing'] = $siteurl."icd9-codes-range/%s/?code=%s";
					$morelinks['ICD']['range'] = $siteurl."icd9-codes-range/%s/";
					
					$morelinks['ICDVOL3']['detail'] = $siteurl."icd9-codes-vol3/%s";
					$morelinks['ICDVOL3']['listing'] = $siteurl."icd9-codes-vol3-range/%s/?code=%s";
					$morelinks['ICDVOL3']['range'] = $siteurl."icd9-codes-vol3-range/%s/";
					
					$morelinks['CPT-Modifier']['detail'] = $siteurl."cpt-modifiers/%s";
					$morelinks['CPT-Modifier']['listing'] = $siteurl."cpt-codes-range/%s/?modifier=%s";
					$morelinks['CPT-Modifier']['range'] = $siteurl."cpt-codes-range/%s/";
				
					$morelinks['HCPCS-Modifier']['detail'] = $siteurl."hcpcs-codes/%s";
					$morelinks['HCPCS-Modifier']['listing'] = $siteurl."hcpcs-codes-range/%s/?modifier=%s";
					$morelinks['HCPCS-Modifier']['range'] = $siteurl."hcpcs-codes-range/%s/";
					
					$morelinks['DRG']['detail'] = $siteurl."drg-codes/%s";				
					$morelinks['DRG']['listing'] = $siteurl."drg-codes-range/%s/?code=%s";
					$morelinks['DRG']['range'] = $siteurl."drg-codes-range/%s/";
		
					$morelinks['APC']['detail'] = $siteurl."apc-codes/%s";		
					$morelinks['APC']['listing'] = $siteurl."apc-codes-range/%s/?code=%s";
					$morelinks['APC']['range'] = $siteurl."apc-codes-range/%s/";

					$morelinks['ICD10']['detail'] = $siteurl."icd-10-codes/%s";		
					$morelinks['ICD10']['listing'] = $siteurl."icd-10-codes-range/%s/?code=%s";
					$morelinks['ICD10']['range'] = $siteurl."icd-10-codes-range/%s/";

					$morelinks['PCS']['detail'] = $siteurl."pcs-code/%s";		
					$morelinks['PCS']['listing'] = $siteurl."pcs-codes-list/%s/?code=%s";
					$morelinks['PCS']['range'] = $siteurl."pcs-codes/%s/";
					///// end** set codeset urls for redirection and links
			
					///// start manage redirection according to user option
					if($force_search_pref=='')
					{
						$uoption['CPT'] = isset($user_search_pref['cptoption'])?$user_search_pref['cptoption']:'detail';
						$uoption['ICD'] = isset($user_search_pref['icdoption'])?$user_search_pref['icdoption']:'detail';
						$uoption['HCPCS'] = isset($user_search_pref['hcpcsoption'])?$user_search_pref['hcpcsoption']:'detail';
						$uoption['ICDVOL3'] = isset($user_search_pref['icd3option'])?$user_search_pref['icd3option']:'detail';
						$uoption['CPT-Modifier'] = 'listing';
						$uoption['HCPCS-Modifier'] = 'listing';//'HCPCS-Modifier'
						$uoption['DRG'] = isset($user_search_pref['drgoption'])?$user_search_pref['drgoption']:'detail';
						$uoption['APC'] = 'detail';
						$uoption['ICD10'] = isset($user_search_pref['icd10option'])?$user_search_pref['icd10option']:'detail';
						$uoption['PCS'] = isset($user_search_pref['icd10pcs'])?$user_search_pref['icd10pcs']:'detail';
					}else
					{
						$uoption['CPT'] = $uoption['ICD'] = $uoption['HCPCS'] = $uoption['ICDVOL3'] = $uoption['CPT-Modifier'] = $uoption['HCPCS-Modifier'] = $uoption['HCPCS-Modifier'] = $uoption['DRG'] = $uoption['APC'] = $uoption['ICD10'] = $uoption['PCS'] = $force_search_pref;
					}				
					///// end manage redirection according to user option
										
					
					if(count($items)==1)
					{
						$single_row = array_values($items);
						$original_term=trim(strtoupper(trim($q,'*!')));
						if(is_array($single_row) && isset($single_row[0]['code_title']) && $single_row[0]['code_title']==$original_term)
						{
							$single_item = $single_row[0];
							if(isset($uoption[$single_item['code_type']]) && $uoption[$single_item['code_type']]=='listing') {
							
								$response = sprintf($morelinks[$single_item['code_type']][$uoption[$single_item['code_type']]],$single_item['range_id'],$single_item['code_title'],$single_item['code_title']);
								if($single_item['code_type']=='PCS')//PCS table links on codes having length less then 7
								{
									$response = sprintf($morelinks[$single_item['code_type']][$uoption[$single_item['code_type']]],$single_item['parent_code_title'],$single_item['code_title'],$single_item['code_title']);
								}
							}
							else
							{
								if(isset($uoption[$single_item['code_type']]))
								{
									$response = sprintf($morelinks[$single_item['code_type']][$uoption[$single_item['code_type']]],$single_item['code_title']);
								}
							}
							$output= 
								'{
									"spelling":"",
									"msg":"",
									"response":"'.$response.'",
									"type1":"code",
									"treeview":""
								 }';
							return $output;
						}
					}					
					$codesdata = array();
					$relevancy = array();
					$rel_data = array();
					$matchcounter = 0;
					$leaf_counter = array('CPT'=>0,'ICD'=>0,'HCPCS'=>0,'ICDVOL3'=>0,'CPT-Modifier'=>0,'HCPCS-Modifier'=>0,'DRG'=>0,'APC'=>0,'ICD10'=>0,'PCS'=>0);
					foreach ($items as $item) {

						$titles = $item['code_title'];
						
						$code_name = $item['code_title'];
						
						$code_type = $item['code_type'];
						$code_type_int = $item['code_type_int'];
						
						$code_range_id = $item['range_id'];

						$code_range = preg_replace("/[^a-zA-Z0-9.:;,-?()\s]/", "", $item['range_title']);//$this->mystriptag($item['range_title']);
						
						$code_desc = preg_replace("/[^a-zA-Z0-9.:;,-?()\s]/", "", $item['description']);//$this->mystriptag($item['description']);
						
						//$code_special_text = $this->mystriptag($item['special_text']);
						if( isset($item['index_description']) && trim($item['index_description'])!='')
						{
							$code_special_text = $this->mystriptag($item['index_description']);//preg_replace("/[^a-zA-Z0-9\s]/", " ", $item['index_description']);
						}else
						{
							$code_special_text = preg_replace("/[^a-zA-Z0-9\s]/", " ", $item['special_text']);//$this->mystriptag($item['special_text']);
						}
						
						$code_range_desc = preg_replace("/[^a-zA-Z0-9.:;,-?()\s]/", "", $item['range_description']);//$this->mystriptag($item['range_description']);
						
						$parent1_code = $item['parent_code_title'];
						
						$parent2_code = $item['super_parent_title'];
						
						$parent1_desc = $item['parent_code_description'];//preg_replace("/[^a-zA-Z0-9\s]/", " ", $item['parent_code_description']);//$this->mystriptag($item['parent_code_description']);
						
						$parent2_desc = $item['super_parent_description'];//preg_replace("/[^a-zA-Z0-9\s]/", " ", $item['super_parent_description']);//$this->mystriptag($item['super_parent_description']);

						$child_count = $item['child_count'];

						$parent_digit_image = '';
						$super_parent_digit_image = '';
						
						if($child_count>0)
						{
							$uoption[$code_type]='listing';
						}
						if($item['parent_have_child']==1) {
							$parent_digit_image = '<img src="'.static_files_url().'images/front/icd-04.gif" align="top" style="float:left;padding-left:5px;padding-right:5px;" />';
						}
						if($item['parent_have_child']==2) {
							$parent_digit_image = '<img src="'.static_files_url().'images/front/icd-05.gif" align="top" style="float:left;padding-left:5px;padding-right:5px;" />';
						}
						if($item['parent_have_child']==3) {
							$parent_digit_image = '<img src="'.static_files_url().'images/front/icd-06.gif" align="top" style="float:left;padding-left:5px;padding-right:5px;" />';
						}
						if($item['parent_have_child']==4 && $item['parent_xtension']=='0') {
							$parent_digit_image = '<img src="'.static_files_url().'images/front/icd-07.gif" align="top" style="float:left;padding-left:5px;padding-right:5px;" />';
						}
						if($item['parent_have_child']==4 && $item['parent_xtension']=='1') {
							$parent_digit_image = '<img src="'.static_files_url().'images/front/icd-07_blue.gif" align="top" style="float:left;padding-left:5px;padding-right:5px;" />';
						}
						if($item['super_parent_have_child']==1) {
							$super_parent_digit_image = '<img src="'.static_files_url().'images/front/icd-04.gif" align="top" style="float:left;padding-left:5px;padding-right:5px;" />';
						}
						if($item['super_parent_have_child']==2) {
							$super_parent_digit_image = '<img src="'.static_files_url().'images/front/icd-05.gif" align="top" style="float:left;padding-left:5px;padding-right:5px;" />';
						}
						if($item['super_parent_have_child']==3) {
							$super_parent_digit_image = '<img src="'.static_files_url().'images/front/icd-06.gif" align="top" style="float:left;padding-left:5px;padding-right:5px;" />';
						}
						switch($code_type)
						{
							case 'CPT';
								$leaf_counter[$code_type]++;
								break;
							case 'ICD';
								$leaf_counter[$code_type]++;
								break;
							case 'HCPCS';
								$leaf_counter[$code_type]++;
								break;
							case 'ICDVOL3';
								$leaf_counter[$code_type]++;
								break;
							case 'DRG';
								$leaf_counter[$code_type]++;
								break;
							case 'ICD10';
								$leaf_counter[$code_type]++;
								break;
							case 'APC';
								$code_range = 'APC';
								$code_range_desc = 'Codes';
								$leaf_counter[$code_type]++;
								break;
							case 'CPT-Modifier';
								$code_range = 'CPT-Modifier';
								$code_range_desc = 'Codes';
								$leaf_counter[$code_type]++;
								break;
							case 'HCPCS-Modifier';
								$code_range = 'HCPCS-Modifier';
								$code_range_desc = 'Codes';
								$leaf_counter[$code_type]++;
								break;
							case 'PCS';
								$leaf_counter[$code_type]++;
								break;
						}
						
						if((!$login || !has_access(5)) && ($code_type_int==7 || $code_type_int==205 || $code_type_int==203)) {
/*							if($code_type=='DRG') {
								$code_range_desc = 'To access the official DRG section, subsection to <a href="'.current_base_url().'coding-solutions/drg-coder" >DRG Coder</a>';
								$code_desc = 'Read the DRG definition by subscribing to <a href="'.current_base_url().'coding-solutions/drg-coder" >DRG Coder</a> right now!';
							}
							else
							{
*/
								$code_range_desc = '';
								$code_desc ='';
								$parent1_desc = '';
								$parent2_desc = '';
//							}
						}

						///// Check search settings and create urls for redirection and links
						$link=$parent1_code_link=$parent2_code_link=$range_link='';
						if(isset($uoption[$code_type]) && $uoption[$code_type]=='listing') {
							if($code_type=='PCS' && strlen($code_name)<7)//PCS table links on codes having length less then 7
							{
								$link = sprintf($morelinks[$code_type]['range'],$code_name,$code_name,$code_name);
							}elseif($code_type=='PCS')
							{
								//$link = sprintf($morelinks[$code_type][$uoption[$code_type]],$code_range_id,$code_name,$code_name);
								$link = sprintf($morelinks[$code_type][$uoption[$code_type]],$parent1_code,$code_name);
							}else{
								$link = sprintf($morelinks[$code_type][$uoption[$code_type]],$code_range_id,$code_name,$code_name);
							}
							//$parent1_code_link = empty($parent1_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$code_range_id,$parent1_code,$parent1_code);
							//$parent2_code_link = empty($parent2_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$code_range_id,$parent2_code,$parent2_code);
							$parent1_code_link = empty($parent1_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$parent1_code,$parent1_code);
							$parent2_code_link = empty($parent2_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$parent2_code,$parent2_code);
							if($code_type=='PCS'){
								$range_link = empty($code_range)?'':sprintf($morelinks[$code_type]['range'],trim($code_range));
							}else{
								$range_link = empty($code_range_id)?'':sprintf($morelinks[$code_type]['range'],$code_range_id);
							}
						}
						else
						{
							if(isset($uoption[$code_type]))
							{
								if($code_type=='PCS' && strlen($code_name)<7)//PCS table links on codes having length less then 7
								{
									$link = sprintf($morelinks[$code_type]['range'],$code_name);
									$range_link = empty($code_range)?'':sprintf($morelinks[$code_type]['range'],trim($code_range));
								}elseif($code_type=='PCS')
								{
									$link = sprintf($morelinks[$code_type][$uoption[$code_type]],$code_name);
									$range_link = empty($code_range)?'':sprintf($morelinks[$code_type]['range'],trim($code_range));
									$parent1_code_link = empty($parent1_code)?'':sprintf($morelinks[$code_type]['range'],$parent1_code);
									$parent2_code_link = empty($parent2_code)?'':sprintf($morelinks[$code_type]['range'],$parent2_code);
								}else
								{
									$link = sprintf($morelinks[$code_type][$uoption[$code_type]],$code_name);
									$parent1_code_link = empty($parent1_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$parent1_code);
									$parent2_code_link = empty($parent2_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$parent2_code);
									$range_link = empty($code_range_id)?'':sprintf($morelinks[$code_type]['range'],$code_range_id);
								}

							}
						}
						
						//$link_class = 'green-black';
						$range_link_class = '';
						$code_class = '';
						if($index_ids_combined!='')
						{
							//echo $index_ids_combined;die;
							$index_ids = explode(',',$index_ids_combined);
							if(in_array($item['index_unique_id'],$index_ids))
							{
								//$link_class = 'pink-green';
								$code_class = 'red-pink';
								$range_link_class = 'purple-pink';
							}else
							{
								$code_class = 'green-pink';
								$range_link_class = 'blue-pink';
							}
						}						
						///// Check login status, code discriptor for CPT & ICD-10 will only be displayed to users having access
						if((!$login || !has_access(5)) && ($code_type_int==7 || $code_type_int==205 || $code_type_int==203)) {										
							$leaf = '<a class="'.$code_class.'" href="javascript:void();" onclick="redir(\''.$link.'\');" title="Read the code description by subscribing to <a href=\''.current_base_url().'coding-tools/code-search\'>Coding Tools</a> or <a href=\''.current_base_url().'coding-solutions\'>Coding Solutions</a>" >'.$code_name.'</a>  ';//.$code_desc;
						}
						else {
							$leaf = '<a class="'.$code_class.'" href="javascript:void();" onclick="redir(\''.$link.'\');">'.$code_name.'</a>'.' - <a href="javascript:void();" onclick="redir(\''.$link.'\');">'.$code_desc.'</a>';
						}
						
						///// if the search item is codeset increase $matchcounter and set the last value to $ response and $type1 
						/*if($iscodeset)
						{
						
							if(in_array(strtolower($code_name),$keyword_array,true)) {
							
								//// redirection link
								if(has_access(5))
								{
									$response = $link;
									$type1 = 'code';
								}else
								{
									$response = '';
									$type1 = '';
								}
								//// specify that the searched keyword is code
								//$type1 = 'code';
								//// number of codes found
								$matchcounter++;
								///// get the relevancy of the string against the keyword
								if($sort=='number')
								{
									$rel_value = $code_name;
								}
								else
								{
									$rel_value = $this->string_relevancy($code_name,$code_desc." ".$code_special_text,$keyword_array);
								}
								///// store the values into an array
								$code_range_link = '<a class="'.$range_link_class.'" href="javascript:void();" onclick="redir(\''.$range_link.'\');">'.$code_range." - ".$code_range_desc.'</a>';
								$codesdata[]=array('relivancy'=>$rel_value,'data'=>array($code_type,$code_range_link,$leaf,$parent1_code,$parent1_desc,$parent1_code_link,$parent2_code,$parent2_desc,$parent2_code_link,$parent_digit_image,$super_parent_digit_image,$code_type_int));
								$relevancy[] = $rel_value;
								$rel_data[] = array($code_type,$code_range." - ".$code_range_desc,$leaf);	
							}
						}
						else
						{*/
							///// get the relevancy of the string against the keyword
							if($sort=='number')
							{
								$rel_value = $code_name;
							}
							else
							{
								//print_r($keyword_array);
								//echo "<br>".$code_name."##".$code_desc."##".$code_special_text."##".$keyword_array."##";
								$rel_value = $this->string_relevancy($code_name,$code_desc." ".$code_special_text,$keyword_array);
							}
							///// store the values into an array
							$code_range_link = '<a class="'.$range_link_class.'" href="javascript:void();" onclick="redir(\''.$range_link.'\');">'.trim($code_range)." - ".$code_range_desc.'</a>';
							
							/*if(!$login) {
								$codesdata[]=array('relivancy'=>$rel_value,'data'=>array($code_type,$code_range_link,$leaf,$parent1_code,"",$parent1_code_link,$parent2_code,"",$parent2_code_link,$parent_digit_image,$super_parent_digit_image,$code_type_int));
							}else{
							*/
							$codesdata[]=array('relivancy'=>$rel_value,'data'=>array($code_type,$code_range_link,$leaf,$parent1_code,$parent1_desc,$parent1_code_link,$parent2_code,$parent2_desc,$parent2_code_link,$parent_digit_image,$super_parent_digit_image,$code_type_int));
							//}
							$relevancy[] = $rel_value;
							$rel_data[] = array($code_type,$code_range." - ".$code_range_desc,$leaf);	
						//}
					}
					//echo "<pre>";
					//print_r($codesdata);die;
					//// sort the multidimentional codesdata array with respect to relevancy array
					if($sort=='number')
					{
						array_multisort($relevancy, SORT_ASC, $rel_data, $codesdata);
					}else{
						array_multisort($relevancy, SORT_DESC, $rel_data, $codesdata);
					}
					if(isset($codesdata[0]['data'][11]))
					$top_code_type_int = $codesdata[0]['data'][11];
					for($code_counter=0;($top_code_type_int!=7 && $top_code_type_int!=8 && $top_code_type_int!=9) && $code_counter<count($codesdata);$code_counter++)
					{
						if(isset($codesdata[$code_counter]['data'][11]))
						$top_code_type_int = $codesdata[$code_counter]['data'][11];
					}
					$top_open_set = 0;
					//print_r($leaf_counter);die;
					foreach($codesdata as $item1)
					{
						////// store the values into an array as parent child so that its easier to parse into json string 
						if(!empty($item1['data'][3]) && !empty($item1['data'][6])) {
							/*if(!$login) {
								$master_array[$item1['data'][0]][$item1['data'][1]]['<a class="meg-link" href="javascript:void();" onclick="redir(\''.$item1['data'][8].'\');" title="Read the code description by subscribing to <a href=\''.current_base_url().'coding-tools/code-search\'>Coding Tools</a> or <a href=\''.current_base_url().'coding-solutions\'>Coding Solutions</a>">'.$item1['data'][6].'</a>']['<a class="meg-link" href="javascript:void();" onclick="redir(\''.$item1['data'][5].'\');" title="Read the code description by subscribing to <a href=\''.current_base_url().'coding-tools/code-search\'>Coding Tools</a> or <a href=\''.current_base_url().'coding-solutions\'>Coding Solutions</a>">'.$item1['data'][3].'</a>'][$item1['data'][2]][] = 1;
							}
							else
							{*/
								$master_array[$item1['data'][0]][$item1['data'][1]]['<a class="meg-link" href="javascript:void();" onclick="redir(\''.$item1['data'][8].'\');">'.$item1['data'][6].'</a> '.$item1['data'][10].' - '.$item1['data'][7]]['<a class="meg-link" href="javascript:void();" onclick="redir(\''.$item1['data'][5].'\');">'.$item1['data'][3].'</a> '.$item1['data'][9].' - '.$item1['data'][4]][$item1['data'][2]][] = 1;
								if($top_code_type_int == $item1['data'][11] && $top_open_set==0)
								{
									$master_array[$item1['data'][0]][$item1['data'][1]]['<a class="meg-link" href="javascript:void();" onclick="redir(\''.$item1['data'][8].'\');">'.$item1['data'][6].'</a> '.$item1['data'][10].' - '.$item1['data'][7]]['expend'] = 1;
									$master_array[$item1['data'][0]][$item1['data'][1]]['<a class="meg-link" href="javascript:void();" onclick="redir(\''.$item1['data'][8].'\');">'.$item1['data'][6].'</a> '.$item1['data'][10].' - '.$item1['data'][7]]['<a class="meg-link" href="javascript:void();" onclick="redir(\''.$item1['data'][5].'\');">'.$item1['data'][3].'</a> '.$item1['data'][9].' - '.$item1['data'][4]]['expend'] = 1;
								}
							//}
						}
						else if(!empty($item1['data'][3]) && empty($item1['data'][6]))	{
							/*if(!$login) {
								$master_array[$item1['data'][0]][$item1['data'][1]]['<a class="meg-link" href="javascript:void();" onclick="redir(\''.$item1['data'][5].'\');" title="Read the code description by subscribing to <a href=\''.current_base_url().'coding-tools/code-search\'>Coding Tools</a> or <a href=\''.current_base_url().'coding-solutions\'>Coding Solutions</a>">'.$item1['data'][3].'</a> '.$item1['data'][9]][$item1['data'][2]][] = 1;
							}
							else
							{*/
								$master_array[$item1['data'][0]][$item1['data'][1]]['<a class="meg-link" href="javascript:void();" onclick="redir(\''.$item1['data'][5].'\');">'.$item1['data'][3].'</a> '.$item1['data'][9].' - '.$item1['data'][4]][$item1['data'][2]][] = 1;
								if($top_code_type_int == $item1['data'][11] && $top_open_set==0)
								{
									$master_array[$item1['data'][0]][$item1['data'][1]]['<a class="meg-link" href="javascript:void();" onclick="redir(\''.$item1['data'][5].'\');">'.$item1['data'][3].'</a> '.$item1['data'][9].' - '.$item1['data'][4]]['expend'] = 1;
								}
							//}
						}
						else
						{
							$master_array[$item1['data'][0]][$item1['data'][1]][$item1['data'][2]][] = 1;
						}
						if($top_code_type_int == $item1['data'][11] && $top_open_set==0){
							$master_array[$item1['data'][0]][$item1['data'][1]]['expend'] = 1;
							$top_open_set=1;
						}
					}
					$matchcounter = count($codesdata);
					if($iscodeset && $matchcounter>1) {
						$response = '';
						$type1 = '';
					}
					elseif($iscodeset && $matchcounter==0) { 
						$response = '';
						$type1 = 'code';
					}
					//echo $matchcounter;die;
					//echo $response."####".$type1;die;
					///// Remove any junk/incomplete vales if found, Inconsistent data can spoil tree 
					unset($master_array['']);
					
					$codestring ='';
					$codestring1 = '';
					
					///// Convert the array into json string
					$specific_msg_with_lable = '';
					//if(!$login) {
					//	$specific_msg_with_lable = "  <span class='blue'>Subscribers see the official code section or subsection</span>";
					//}
					if($top_code_type_int=='7')
					{
						if(count($master_array['CPT'])>0) {
							$codestring1 = '{"type":"Text", "label":"CPT&reg;'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['CPT']).',"count":"'.$leaf_counter['CPT'].'"},';
						}
					}else
					{
						if(count($master_array['CPT'])>0) {
							$codestring .= '{"type":"Text", "label":"CPT&reg;'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['CPT']).',"count":"'.$leaf_counter['CPT'].'"},';
						}
					}
						/////memory management
						unset($master_array['CPT']);
					if($top_code_type_int=='9')
					{
						if(count($master_array['HCPCS'])>0) {
							$codestring1 = '{"type":"Text", "label":"HCPCS'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['HCPCS']).',"count":"'.$leaf_counter['HCPCS'].'"},';
						}
					}else
					{
						if(count($master_array['HCPCS'])>0) {
							$codestring .= '{"type":"Text", "label":"HCPCS'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['HCPCS']).',"count":"'.$leaf_counter['HCPCS'].'"},';
						}
					}
					unset($master_array['HCPCS']);
					if($top_code_type_int=='8')
					{
						if(count($master_array['ICD'])>0) {
							$codestring1 = '{"type":"Text", "label":"ICD-9-CM'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['ICD']).',"count":"'.$leaf_counter['ICD'].'"},';
						}
					}else
					{
						if(count($master_array['ICD'])>0) {
							$codestring .= '{"type":"Text", "label":"ICD-9-CM'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['ICD']).',"count":"'.$leaf_counter['ICD'].'"},';
						}
					}
					/////memory management
					unset($master_array['ICD']);
					/*if($top_code_type_int=='202')
					{					
						if(isset($master_array['ICDVOL3']) && count($master_array['ICDVOL3'])>0) {
							$codestring1 = '{"type":"Text", "label":"ICD-9-CM VOL3'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['ICDVOL3']).'},';
						}
					}else
					{*/
						if(isset($master_array['ICDVOL3']) && count($master_array['ICDVOL3'])>0) {
							$codestring .= '{"type":"Text", "label":"ICD-9-CM Vol.3'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['ICDVOL3']).',"count":"'.$leaf_counter['ICDVOL3'].'"},';
						}
					//}
					unset($master_array['ICDVOL3']);

						if(isset($master_array['HCPCS-Modifier']) && count($master_array['HCPCS-Modifier'])>0) {
							$codestring .= '{"type":"Text", "label":"HCPCS-Modifiers'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['HCPCS-Modifier']).',"count":"'.$leaf_counter['HCPCS-Modifier'].'"},';
						}
					unset($master_array['HCPCS-Modifier']);

						if(isset($master_array['CPT-Modifier']) && count($master_array['CPT-Modifier'])>0) {
							$codestring .= '{"type":"Text", "label":"CPT&reg;-Modifiers'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['CPT-Modifier']).',"count":"'.$leaf_counter['CPT-Modifier'].'"},';
						}
					unset($master_array['CPT-Modifier']);

					/*if($top_code_type_int=='201')
					{										
						if(isset($master_array['DRG']) && count($master_array['DRG'])>0) {
							$codestring1 = '{"type":"Text", "label":"DRG","expanded": "true","children":'.$this->array_to_json_string($master_array['DRG']).'},';
						}
					}else{*/
						if(isset($master_array['DRG']) && count($master_array['DRG'])>0) {
							$codestring .= '{"type":"Text", "label":"DRG","expanded": "true","children":'.$this->array_to_json_string($master_array['DRG']).',"count":"'.$leaf_counter['DRG'].'"},';
						}
					//}
					
					/////memory management
					unset($master_array['DRG']);
					
					/*if($top_code_type_int=='204')
					{															
						if(count($master_array['APC'])>0) {
							$codestring1 = '{"type":"Text", "label":"APC","expanded": "true","children":'.$this->array_to_json_string($master_array['APC']).'},';
						}
					}else
					{*/
						if(count($master_array['APC'])>0) {
							$codestring .= '{"type":"Text", "label":"APC","expanded": "true","children":'.$this->array_to_json_string($master_array['APC']).',"count":"'.$leaf_counter['APC'].'"},';
						}
					//}
					
					/////memory management
					unset($master_array['APC']);
					/*if($top_code_type_int=='203')
					{															
						if(count($master_array['ICD10'])>0) {
							$codestring1 = '{"type":"Text", "label":"ICD-10","expanded": "true","children":'.$this->array_to_json_string($master_array['ICD10']).'},';
						}
					}else
					{*/
						if(count($master_array['ICD10'])>0) {
							$codestring .= '{"type":"Text", "label":"ICD-10-CM","expanded": "true","children":'.$this->array_to_json_string($master_array['ICD10']).',"count":"'.$leaf_counter['ICD10'].'"},';
						}
					//}
					/////memory management
					unset($master_array['ICD10']);

					if(count($master_array['PCS'])>0) {
							$codestring .= '{"type":"Text", "label":"PCS","expanded": "true","children":'.$this->array_to_json_string($master_array['PCS']).',"count":"'.$leaf_counter['PCS'].'"},';
						}
					unset($master_array['PCS']);
				
					$codestring = rtrim($codestring1.$codestring,',');
					//echo $codestring."###############";die;

					///// If record count is equal or greater  that 1000 show a warning message
					if($totalResults>=1000) $msg = 'The searched term returns large number of search results. Please add more search keywords to get specific results';
					
					///// spelling - carries all zoom sugession
					///// msg - any message to send along the data
					///// response - carries a link of the single code
					///// type1 - value "code" for codeset
					///// treeview - the tree structure as required by YUI
					//"spelling":'.$this->array_to_json_string_advance($spell).',
					$output= 
						'{
							"spelling":"",
							"msg":"'.$msg.'",
							"response":"'.$response.'",
							"type1":"'.$type1.'",
							"treeview":['.$codestring.']
						 }';		
//echo "comming##";die;						 
					return $output;
				}
			}
		}
		else
		{
			return 'No Query';
		}				  
   }


	public function codesearch($filter='',$q='',$force_search_pref='',$sort='')
	{	
		if($filter=='')
		{
			$filter = '7,8,9,201,202,203,204,205,206,207';
		}
		$filter_array = explode(",",$filter);
		$q = strtolower($q);
		$keyword_array = explode(' ',$q);
		$iscodeset = true;
		$resultCount = 0;
		$codesetcomb_obj = new Codeset_combined();
		$deletedcode  = $codesetcomb_obj->deleted_code($q);
		//// prepare url for deleted page
		$deletedlink['CPT'] = current_base_url()."code_lookup/deleted_cpt_codes/%s";
		$deletedlink['ICD'] = current_base_url()."code_lookup/deleted_icd_codes/%s";
		$deletedlink['HCPCS'] = current_base_url()."code_lookup/deleted_hcpcs_codes/%s";
		///// if deleted code then response = delsted url and type1=code
		if(count($deletedcode)) {
			$response = sprintf($deletedlink[$deletedcode[0]['level']],$deletedcode[0]['code'],$deletedcode[0]['code']);
			$type1 = 'code';
			
			return $output= 
						'{
							"spelling":"",
							"msg":"",
							"response":"'.$response.'",
							"type1":"'.$type1.'",
							"treeview":""
						 }';
		///// output string and exit only if deleted code found
		exit;	
		}

		$code_string = '';
		$icdcode_string = '';
		$keyword_part = '';
		if(is_array($keyword_array)){
			foreach ($keyword_array as $key_word)
			{
				$key_word = trim($key_word);
				if($key_word!="") 
				{
					if(strlen($key_word)==1)
					{
						if(in_array(201,$filter_array) ){ // only for DRG
						$code_string .= "'00".$key_word."',";//for database search title
						$keyword_part .= "00".$key_word.",";
						}
						$code_string .= "'".$key_word."',";//for database search title
						$keyword_part .= "".$key_word.",";
					}elseif(strlen($key_word)==2)
					{
						if(in_array(201,$filter_array) ){
						$code_string .= "'0".$key_word."',";					
						$keyword_part .= "0".$key_word.",";
						}
						$code_string .= "'".$key_word."',";					
						$keyword_part .= "".$key_word.",";
					}else
					{
						$code_string .= "'".$key_word."',";
						$keyword_part .= $key_word.",";
					}
					$icdcode_string.= "'".$key_word."',";
				}	
			}
			$code_string = trim($code_string,",");
			$keyword_part = trim($keyword_part,",");
			$icdcode_string = trim($icdcode_string,",");
		}else{
					if(strlen($key_word)==1)
					{
						if(in_array(201,$filter_array) ){
						$code_string = "'00".$q."'";	
						$keyword_part = "00".$q;
						}
						$code_string .= ",'".$q."'";	
						$keyword_part .= ",".$q;
					}elseif(strlen($key_word)==2)
					{
						if(in_array(201,$filter_array) ){
						$code_string = "'0".$q."'";	
						$keyword_part = "0".$q;
						}
						$code_string .= ",'".$q."'";	
						$keyword_part .= ",".$q;
					}else
					{
						$code_string = "'".$q."'";	
						$keyword_part = $q;
					}				
					$icdcode_string.= "'".$q."'";
		}
		$keyword_array = explode(",",$keyword_part);
		
		$codeset_obj = new Codeset_combined();
		$items  = $codeset_obj->search_only_codes($code_string,$filter,$q); 
		//print_r($items);die;
		////////////////////////////////
		$msg = '';
		$response = '';
		$type1 = '';
		$login = is_logged_in();
		$user_search_pref = array();
		$current_user = $this->CI->session->userdata('user');
		if(isset($current_user['user_search_pref']) && $current_user['user_search_pref']!='')
		{
			$user_search_pref=unserialize($current_user['user_search_pref']);
		}
		$iscodeset = true;
		$siteurl = current_base_url();
		
		$morelinks['ICDVOL3'] = array();
		$morelinks['CPT-Modifier'] = array();
		$morelinks['HCPCS-Modifier'] = array();
		$morelinks['PCS'] = array();
		///// start** set codeset urls for redirection and links
		$morelinks['CPT']['detail'] = $siteurl."cpt-codes/%s";
		$morelinks['CPT']['listing'] = $siteurl."cpt-codes-range/%s/?code=%s";
		$morelinks['CPT']['range'] = $siteurl."cpt-codes-range/%s/";
		
		$morelinks['HCPCS']['detail'] = $siteurl."hcpcs-codes/%s";
		$morelinks['HCPCS']['listing'] = $siteurl."hcpcs-codes-range/%s/?code=%s";
		$morelinks['HCPCS']['range'] = $siteurl."hcpcs-codes-range/%s/";
		
		$morelinks['ICD']['detail'] = $siteurl."icd9-codes/%s";
		$morelinks['ICD']['listing'] = $siteurl."icd9-codes-range/%s/?code=%s";
		$morelinks['ICD']['range'] = $siteurl."icd9-codes-range/%s/";
		
		$morelinks['ICDVOL3']['detail'] = $siteurl."icd9-codes-vol3/%s";
		$morelinks['ICDVOL3']['listing'] = $siteurl."icd9-codes-vol3-range/%s/?code=%s";
		$morelinks['ICDVOL3']['range'] = $siteurl."icd9-codes-vol3-range/%s/";
		
		$morelinks['CPT-Modifier']['detail'] = $siteurl."cpt-codes/%s";
		$morelinks['CPT-Modifier']['listing'] = $siteurl."cpt-codes-range/%s/?modifier=%s";
		$morelinks['CPT-Modifier']['range'] = $siteurl."cpt-codes-range/%s/";
	
		$morelinks['HCPCS-Modifier']['detail'] = $siteurl."hcpcs-codes/%s";
		$morelinks['HCPCS-Modifier']['listing'] = $siteurl."hcpcs-codes-range/%s/?modifier=%s";
		$morelinks['HCPCS-Modifier']['range'] = $siteurl."hcpcs-codes-range/%s/";
		
		$morelinks['DRG']['detail'] = $siteurl."drg-codes/%s";				
		$morelinks['DRG']['listing'] = $siteurl."drg-codes-range/%s/?code=%s";
		$morelinks['DRG']['range'] = $siteurl."drg-codes-range/%s/";

		$morelinks['APC']['detail'] = $siteurl."apc-codes/%s";		
		$morelinks['APC']['listing'] = $siteurl."apc-codes-range/%s/?code=%s";
		$morelinks['APC']['range'] = $siteurl."apc-codes-range/%s/";

		$morelinks['ICD10']['detail'] = $siteurl."icd-10-codes/%s";		
		$morelinks['ICD10']['listing'] = $siteurl."icd-10-codes-range/%s/?code=%s";
		$morelinks['ICD10']['range'] = $siteurl."icd-10-codes-range/%s/";

		$morelinks['PCS']['detail'] = $siteurl."pcs-code/%s";		
		$morelinks['PCS']['listing'] = $siteurl."pcs-codes-list/%s/?code=%s";
		$morelinks['PCS']['range'] = $siteurl."pcs-codes/%s/";
		
		///// end** set codeset urls for redirection and links
		
		///// start manage redirection according to user option
		if($force_search_pref=='')
		{
			$uoption['CPT'] = isset($user_search_pref['cptoption'])?$user_search_pref['cptoption']:'detail';
			$uoption['ICD'] = isset($user_search_pref['icdoption'])?$user_search_pref['icdoption']:'detail';
			$uoption['HCPCS'] = isset($user_search_pref['hcpcsoption'])?$user_search_pref['hcpcsoption']:'detail';
			$uoption['ICDVOL3'] = isset($user_search_pref['icd3option'])?$user_search_pref['icd3option']:'detail';
			$uoption['CPT-Modifier'] = 'listing';
			$uoption['HCPCS-Modifier'] = 'listing';//'HCPCS-Modifier'
			$uoption['DRG'] = isset($user_search_pref['drgoption'])?$user_search_pref['drgoption']:'detail';
			$uoption['APC'] = 'detail';
			$uoption['ICD10'] = isset($user_search_pref['icd10option'])?$user_search_pref['icd10option']:'detail';
			$uoption['PCS'] = isset($user_search_pref['icd10pcs'])?$user_search_pref['icd10pcs']:'detail';
		}else
		{
			$uoption['CPT'] = $uoption['ICD'] = $uoption['HCPCS'] = $uoption['ICDVOL3'] = $uoption['CPT-Modifier'] = $uoption['HCPCS-Modifier'] = $uoption['HCPCS-Modifier'] = $uoption['DRG'] = $uoption['APC'] = $uoption['ICD10'] = $uoption['PCS'] = $force_search_pref;
		}
		if(/*count($items)==1 COMMENTED FOR NOT REDIRECTING IF SINGLE CODE*/ FALSE)
		{
			$single_item = reset($items);
			
			if(isset($uoption[$single_item['code_type']]) && $uoption[$single_item['code_type']]=='listing') {
				$response = sprintf($morelinks[$single_item['code_type']][$uoption[$single_item['code_type']]],$single_item['range_id'],$single_item['code_title'],$single_item['code_title']);
			}
			else
			{
				if(isset($uoption[$single_item['code_type']]))
				{
					/*if(!$login) {
						$response = sprintf($morelinks[$single_item['code_type']][$uoption[$single_item['code_type']]],$single_item['code_title']);
					}
					else
					{*/
						$response = sprintf($morelinks[$single_item['code_type']][$uoption[$single_item['code_type']]],$single_item['code_title']);
					//}
				}
			}
			
			//$response=sprintf($morelinks[$single_item['code_type']][$uoption[$single_item['code_type']]],$single_item['code_title']);
			$output= 
				'{
					"spelling":"",
					"msg":"",
					"response":"'.$response.'",
					"type1":"code",
					"treeview":""
				 }';
			return $output;
		}
					if(count($items)==1)
					{
						$single_row = array_values($items);
						$original_term=trim(strtoupper(trim($q,'*!')));
						if(is_array($single_row) && isset($single_row[0]['code_title']) && $single_row[0]['code_title']==$original_term)
						{
							$single_item = $single_row[0];
							if(isset($uoption[$single_item['code_type']]) && $uoption[$single_item['code_type']]=='listing') {
							
								$response = sprintf($morelinks[$single_item['code_type']][$uoption[$single_item['code_type']]],$single_item['range_id'],$single_item['code_title'],$single_item['code_title']);
								if($single_item['code_type']=='PCS')//PCS table links on codes having length less then 7
								{
									$response = sprintf($morelinks[$single_item['code_type']][$uoption[$single_item['code_type']]],$single_item['parent_code_title'],$single_item['code_title'],$single_item['code_title']);
								}
							}
							else
							{
								if(isset($uoption[$single_item['code_type']]))
								{
									$response = sprintf($morelinks[$single_item['code_type']][$uoption[$single_item['code_type']]],$single_item['code_title']);
								}
							}
							$output= 
								'{
									"spelling":"",
									"msg":"",
									"response":"'.$response.'",
									"type1":"code",
									"treeview":""
								 }';
							return $output;
						}
					}		
		if(count($items)==0)
		{
			return false;
		}
		// If multiple codes then set tree values
		$master_array = array();
		$master_array['CPT'] = array();
		$master_array['ICD'] = array();
		$master_array['HCPCS'] = array();
		$master_array['DRG'] = array();
		$master_array['APC'] = array();
		$master_array['ICD10'] = array();
		$master_array['CPT-Modifier'] = array();
		$master_array['HCPCS-Modifier'] = array();
		$master_array['PCS'] = array();
		$codesdata = array();
		$matchcounter = 0;
		$leaf_counter = array('CPT'=>0,'ICD'=>0,'HCPCS'=>0,'ICDVOL3'=>0,'CPT-Modifier'=>0,'HCPCS-Modifier'=>0,'DRG'=>0,'APC'=>0,'ICD10'=>0,'PCS'=>0);
		//echo count($items);
		foreach ($items as $item) {
			$titles = $item['code_title'];			
			$code_name = $item['code_title'];			
			$code_type = $item['code_type'];			
			$code_range_id = $item['range_id'];
			$code_range = preg_replace("/[^a-zA-Z0-9.:;,-?()\s]/", "", $item['range_title']);//$item['range_title'];			
			$code_desc = preg_replace("/[^a-zA-Z0-9.:;,-?()\s]/", "", $item['description']);//			
			$code_special_text = preg_replace("/[^a-zA-Z0-9\s]/", "", $item['special_text']);//$this->mystriptag($item['special_text']);			
			$code_range_desc = preg_replace("/[^a-zA-Z0-9.:;,-?()\s]/", "", $item['range_description']);//$item['range_description'];			
			$parent1_code = $item['parent_code_title'];			
			$parent2_code = $item['super_parent_title'];			
			$parent1_desc = preg_replace("/[^a-zA-Z0-9\s]/", "", $item['parent_code_description']);//$this->mystriptag($item['parent_code_description']);			
			$parent2_desc = preg_replace("/[^a-zA-Z0-9\s]/", "", $item['super_parent_description']);//$this->mystriptag($item['super_parent_description']);
			$child_count = $item['child_count'];			
			$parent_digit_image = '';
			$super_parent_digit_image = '';
			if($child_count>0)
			{
				$uoption[$code_type]='listing';
			}
			if($item['parent_have_child']==1) {
				$parent_digit_image = '<img src="'.static_files_url().'images/front/icd-04.gif" align="top" style="float:left;padding-left:5px;padding-right:5px;" />';
			}
			if($item['parent_have_child']==2) {
				$parent_digit_image = '<img src="'.static_files_url().'images/front/icd-05.gif" align="top" style="float:left;padding-left:5px;padding-right:5px;" />';
			}
			if($item['super_parent_have_child']==1) {
				$super_parent_digit_image = '<img src="'.static_files_url().'images/front/icd-04.gif" align="top" style="float:left;padding-left:5px;padding-right:5px;" />';
			}
			if($item['super_parent_have_child']==2) {
				$super_parent_digit_image = '<img src="'.static_files_url().'images/front/icd-05.gif" align="top" style="float:left;padding-left:5px;padding-right:5px;" />';
			}
			if($item['super_parent_have_child']==3) {
				$super_parent_digit_image = '<img src="'.static_files_url().'images/front/icd-06.gif" align="top" style="float:left;padding-left:5px;padding-right:5px;" />';
			}
			if($item['parent_have_child']==3) {
				$parent_digit_image = '<img src="'.static_files_url().'images/front/icd-06.gif" align="top" style="float:left;padding-left:5px;padding-right:5px;" />';
			}
			if($item['parent_have_child']==4 && $item['parent_xtension']=='0') {
				$parent_digit_image = '<img src="'.static_files_url().'images/front/icd-07.gif" align="top" style="float:left;padding-left:5px;padding-right:5px;" />';
			}
			if($item['parent_have_child']==4 && $item['parent_xtension']=='1') {
				$parent_digit_image = '<img src="'.static_files_url().'images/front/icd-07_blue.gif" align="top" style="float:left;padding-left:5px;padding-right:5px;" />';
			}
			
			switch($code_type)
			{
				case 'CPT';
					$leaf_counter[$code_type]++;
					break;
				case 'ICD';
					$leaf_counter[$code_type]++;
					break;
				case 'HCPCS';
					$leaf_counter[$code_type]++;
					break;
				case 'ICDVOL3';
					$leaf_counter[$code_type]++;
					break;
				case 'DRG';
					$leaf_counter[$code_type]++;
					break;
				case 'ICD10';
					$leaf_counter[$code_type]++;
					break;
				case 'APC';
					$code_range = 'APC';
					$code_range_desc = 'Codes';
					$leaf_counter[$code_type]++;
					break;
				case 'CPT-Modifier';
					$code_range = 'CPT-Modifier';
					$code_range_desc = 'Codes';
					$leaf_counter[$code_type]++;
					break;
				case 'HCPCS-Modifier';
					$code_range = 'HCPCS-Modifier';
					$code_range_desc = 'Codes';
					$leaf_counter[$code_type]++;
					break;
				case 'PCS';
					$leaf_counter[$code_type]++;
					break;
			}			
			
			
			if((!$login || !has_access(5)) && ($code_type_int==7 || $code_type_int==205 || $code_type_int==203)) {
			/*	if($code_type=='DRG') {
					$code_range_desc = 'To see the official DRG section, subsection to <a href="'.current_base_url().'coding-solutions/drg-coder" >DRG Coder</a>';
					$code_desc = 'Read the DRG definition by subscribing to <a href="'.current_base_url().'coding-solutions/drg-coder" >DRG Coder</a> right now!';
				}
				else
				{
			*/
					$code_range_desc = '';
					$code_desc ='';
					$parent1_desc = '';
					$parent2_desc = '';
			//	}
			}

			///// Check search settings and create urls for redirection and links
			$link=$parent1_code_link=$parent2_code_link==$range_link='';
			if(isset($uoption[$code_type]) && $uoption[$code_type]=='listing') {
				if($code_type=='PCS' && strlen($code_name)<7)//PCS table links on codes having length less then 7
				{
					$link = sprintf($morelinks[$code_type]['range'],$code_name,$code_name,$code_name);
				}else
				{
					//$link = sprintf($morelinks[$code_type][$uoption[$code_type]],$code_range_id,$code_name,$code_name);
					$link = sprintf($morelinks[$code_type][$uoption[$code_type]],$parent1_code,$code_name);
				}
				//$link = sprintf($morelinks[$code_type][$uoption[$code_type]],$code_range_id,$code_name,$code_name);
				//$parent1_code_link = empty($parent1_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$code_range_id,$parent1_code,$parent1_code);
				//$parent2_code_link = empty($parent2_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$code_range_id,$parent2_code,$parent2_code);
				$parent1_code_link = empty($parent1_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$parent1_code,$parent1_code);
				$parent2_code_link = empty($parent2_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$parent2_code,$parent2_code);
				if($code_type=='PCS'){
					$range_link = empty($code_range)?'':sprintf($morelinks[$code_type]['range'],trim($code_range));
				}else{
					$range_link = empty($code_range_id)?'':sprintf($morelinks[$code_type]['range'],$code_range_id);
				}
			}
			else
			{
				if(isset($uoption[$code_type]))
				{
					/*if($code_type=='PCS' && strlen($code_name)<7)//PCS table links on codes having length less then 7
					{
						$link = sprintf($morelinks[$code_type]['range'],$code_name);
					}else
					{
						$link = sprintf($morelinks[$code_type][$uoption[$code_type]],$code_name);
					}
					//$link = sprintf($morelinks[$code_type][$uoption[$code_type]],$code_name);
					$parent1_code_link = empty($parent1_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$parent1_code);
					$parent2_code_link = empty($parent2_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$parent2_code);
					if($code_type=='PCS'){
						$range_link = empty($code_range)?'':sprintf($morelinks[$code_type]['range'],trim($code_range));
					}else{
						$range_link = empty($code_range_id)?'':sprintf($morelinks[$code_type]['range'],$code_range_id);
					}*/
					if($code_type=='PCS' && strlen($code_name)<7)//PCS table links on codes having length less then 7
					{
						$link = sprintf($morelinks[$code_type]['range'],$code_name);
						$range_link = empty($code_range)?'':sprintf($morelinks[$code_type]['range'],trim($code_range));
					}elseif($code_type=='PCS')
					{
						$link = sprintf($morelinks[$code_type][$uoption[$code_type]],$code_name);
						$range_link = empty($code_range)?'':sprintf($morelinks[$code_type]['range'],trim($code_range));
						$parent1_code_link = empty($parent1_code)?'':sprintf($morelinks[$code_type]['range'],$parent1_code);
						$parent2_code_link = empty($parent2_code)?'':sprintf($morelinks[$code_type]['range'],$parent2_code);
					}else
					{
						$link = sprintf($morelinks[$code_type][$uoption[$code_type]],$code_name);
						$parent1_code_link = empty($parent1_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$parent1_code);
						$parent2_code_link = empty($parent2_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$parent2_code);
						$range_link = empty($code_range_id)?'':sprintf($morelinks[$code_type]['range'],$code_range_id);
					}					
				}
			}
			///// Check login status, CPT codes will only be displayed to users having CPT access
			$code_class = 'red-pink';
			$range_link_class = 'purple-pink';
			if(!$login && ($code_type=='CPT' || $code_type=='CPT-Modifiers' || $code_type=='ICD10')) {										
				$leaf = '<a class="'.$code_class.'" href="javascript:void();" onclick="redir(\''.$link.'\');" title="Read the code description by subscribing to <a href=\''.current_base_url().'coding-tools/code-search\'>Coding Tools</a> or <a href=\''.current_base_url().'coding-solutions\'>Coding Solutions</a>" >'.$code_name.'</a>  ';//.$code_desc;
			}
			else {
				$leaf = '<a class="'.$code_class.'" href="javascript:void();" onclick="redir(\''.$link.'\');">'.$code_name.'</a>'.' - <a href="javascript:void();" onclick="redir(\''.$link.'\');">'.$code_desc.'</a>';
			}
			
			///// if the search item is codeset increase $matchcounter and set the last value to $ response and $type1 
			//if(in_array(strtolower($code_name),$keyword_array,true)) {   //Commented on 24Oct2013
				//// redirection link
				if(has_access(5)){
					$response = $link;
					$type1 = 'code';
				}else{
					$response = '';
					$type1 = '';
				}
				//// specify that the searched keyword is code
				
				//// number of codes found
				$matchcounter++;
				///// get the relevancy of the string against the keyword
				///// store the values into an array
				$code_range_link = '<a class="'.$range_link_class.'" href="javascript:void();" onclick="redir(\''.$range_link.'\');">'.trim($code_range)." - ".$code_range_desc.'</a>';
				$codesdata[]=array('data'=>array($code_type,$code_range_link,$leaf,$parent1_code,$parent1_desc,$parent1_code_link,$parent2_code,$parent2_desc,$parent2_code_link,$parent_digit_image,$super_parent_digit_image));
			//}
		}
		foreach($codesdata as $item1)
		{
			////// store the values into an array as parent child so that its easier to parse into json string 
			if(!empty($item1['data'][3]) && !empty($item1['data'][6])) {
				if(!$login) {
					$master_array[$item1['data'][0]][$item1['data'][1]]['<a class="meg-link" href="javascript:void();" onclick="redir(\''.$item1['data'][8].'\');" title="Read the code description by subscribing to <a href=\''.current_base_url().'coding-tools/code-search\'>Coding Tools</a> or <a href=\''.current_base_url().'coding-solutions\'>Coding Solutions</a>">'.$item1['data'][6].'</a>']['<a class="meg-link" href="javascript:void();" onclick="redir(\''.$item1['data'][5].'\');" title="Read the code description by subscribing to <a href=\''.current_base_url().'coding-tools/code-search\'>Coding Tools</a> or <a href=\''.current_base_url().'coding-solutions\'>Coding Solutions</a>">'.$item1['data'][3].'</a>'][$item1['data'][2]][] = 1;
				}
				else
				{
					$master_array[$item1['data'][0]][$item1['data'][1]]['<a class="meg-link" href="javascript:void();" onclick="redir(\''.$item1['data'][8].'\');">'.$item1['data'][6].'</a> '.$item1['data'][10].' - '.$item1['data'][7]]['<a class="meg-link" href="javascript:void();" onclick="redir(\''.$item1['data'][5].'\');">'.$item1['data'][3].'</a> '.$item1['data'][9].' - '.$item1['data'][4]][$item1['data'][2]][] = 1;
				}
			}
			else if(!empty($item1['data'][3]) && empty($item1['data'][6]))	{
				if(!$login) {
					$master_array[$item1['data'][0]][$item1['data'][1]]['<a class="meg-link" href="javascript:void();" onclick="redir(\''.$item1['data'][5].'\');" title="Read the code description by subscribing to <a href=\''.current_base_url().'coding-tools/code-search\'>Coding Tools</a> or <a href=\''.current_base_url().'coding-solutions\'>Coding Solutions</a>">'.$item1['data'][3].'</a> '.$item1['data'][9]][$item1['data'][2]][] = 1;
				}
				else
				{
					$master_array[$item1['data'][0]][$item1['data'][1]]['<a class="meg-link" href="javascript:void();" onclick="redir(\''.$item1['data'][5].'\');">'.$item1['data'][3].'</a> '.$item1['data'][9].' - '.$item1['data'][4]][$item1['data'][2]][] = 1;
				}
			}
			else
			{
				$master_array[$item1['data'][0]][$item1['data'][1]][$item1['data'][2]][] = 1;
			}
		}
		
		if($matchcounter>1) {
			$response = '';
			$type1 = '';
		}
		elseif($matchcounter==0) { 
			$response = '';
			$type1 = 'code';
		}
		///// Remove any junk/incomplete vales if found, Inconsistent data can spoil tree 
		unset($master_array['']);
		
		$codestring ='';
		
		
		///// Convert the array into json string
		$specific_msg_with_lable = '';
		if(!$login) {
			$specific_msg_with_lable = "  <span class='blue'>Subscribers see the official code section or subsection</span>";
		}
		if(count($master_array['CPT'])>0) {
			$codestring .= '{"type":"Text", "label":"CPT&reg;'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['CPT']).',"count":"'.$leaf_counter['CPT'].'"},';
		}
		/////memory management
		unset($master_array['CPT']);
		
		if(count($master_array['HCPCS'])>0) {
			$codestring .= '{"type":"Text", "label":"HCPCS'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['HCPCS']).',"count":"'.$leaf_counter['HCPCS'].'"},';
		}
		unset($master_array['HCPCS']);
		
		if(count($master_array['ICD'])>0) {
			$codestring .= '{"type":"Text", "label":"ICD-9-CM'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['ICD']).',"count":"'.$leaf_counter['ICD'].'"},';
		}
		
		/////memory management
		unset($master_array['ICD']);
		
		if(isset($master_array['ICDVOL3']) && count($master_array['ICDVOL3'])>0) {
			$codestring .= '{"type":"Text", "label":"ICD-9-CM Vol.3'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['ICDVOL3']).',"count":"'.$leaf_counter['ICDVOL3'].'"},';
		}
		unset($master_array['ICDVOL3']);
		if(isset($master_array['HCPCS-Modifier']) && count($master_array['HCPCS-Modifier'])>0) {
			$codestring .= '{"type":"Text", "label":"HCPCS-Modifiers'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['HCPCS-Modifier']).',"count":"'.$leaf_counter['HCPCS-Modifier'].'"},';
		}
		unset($master_array['HCPCS-Modifier']);
		
		if(isset($master_array['CPT-Modifier']) && count($master_array['CPT-Modifier'])>0) {
			$codestring .= '{"type":"Text", "label":"CPT&reg;-Modifiers'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['CPT-Modifier']).',"count":"'.$leaf_counter['CPT-Modifier'].'"},';
		}
		unset($master_array['CPT-Modifier']);

		if(isset($master_array['DRG']) && count($master_array['DRG'])>0) {
			$codestring .= '{"type":"Text", "label":"DRG","expanded": "true","children":'.$this->array_to_json_string($master_array['DRG']).',"count":"'.$leaf_counter['DRG'].'"},';
		}
		
		/////memory management
		unset($master_array['DRG']);
		
		if(count($master_array['APC'])>0) {
			$codestring .= '{"type":"Text", "label":"APC","expanded": "true","children":'.$this->array_to_json_string($master_array['APC']).',"count":"'.$leaf_counter['APC'].'"},';
		}
		
		/////memory management
		unset($master_array['APC']);

		if(count($master_array['ICD10'])>0) {
			$codestring .= '{"type":"Text", "label":"ICD-10-CM","expanded": "true","children":'.$this->array_to_json_string($master_array['ICD10']).',"count":"'.$leaf_counter['ICD10'].'"},';
		}
		/////memory management
		unset($master_array['ICD10']);

		if(count($master_array['PCS'])>0) {
			$codestring .= '{"type":"Text", "label":"PCS","expanded": "true","children":'.$this->array_to_json_string($master_array['PCS']).',"count":"'.$leaf_counter['PCS'].'"},';
		}
		
		/////memory management
		unset($master_array['PCS']);

		$codestring = rtrim($codestring,',');
		//echo "<pre>".$type1;
		//echo $response;die;
		$output= 
			'{
				"spelling":"",
				"msg":"'.$msg.'",
				"response":"'.$response.'",
				"type1":"'.$type1.'",
				"treeview":['.$codestring.']
			 }';
		return $output;
	}
	
    /**
     * Performs a Sphinx search
     */
    public function product_search($filter,$q='',$searchtype='tabcode',$page=1,$search_and='',$negative_word='',$sort='') {
		$sphinx_conf['sphinx_host'] = $this->CI->config->item('sphinx_server');
		$sphinx_conf['sphinx_port'] = $this->CI->config->item('sphinx_server_port'); //this demo uses the SphinxAPI interface
		#can use 'excerpt' to highlight using the query, or 'asis' to show description as is.
		$sphinx_conf['body'] = $this->CI->config->item('sphinx_body');
		#the link for the title (only $id) placeholder supported
		//$sphinx_conf['link_format'] = "";
		#Change this to FALSE on a live site!
		$sphinx_conf['debug'] = $this->CI->config->item('sphinx_debug');
		#How many results per page
		$sphinx_conf['page_size'] = $this->CI->config->item('sphinx_page_size');
		#maximum number of results - should match sphinxes max_matches. default 1000
		$sphinx_conf['max_matches'] = $this->CI->config->item('sphinx_max_matches');
		$product_categories = get_product_search_categories();
		$resultCount = 0;
		$count_array = array();
		$result_string = '';
		$spell = '';
		//echo $searchtype;die;
		/*if($search_and)
		{
  	    	$mode = SPH_MATCH_EXTENDED2;
		}
		else
		{
			$mode = SPH_MATCH_ANY;
		}*/
		$mode = SPH_MATCH_ANY;
		//if (!empty($q)) 
		//{
			//produce a version for display
			$qo = $q;
			///////////////////////////////////////////////////////////////////////			
				
				$sphinx_conf['sphinx_index'] = "combined_products";
					//setup paging...
					if (!empty($page)) {
						$currentPage = intval($page);
						if (empty($currentPage) || $currentPage < 1) {$currentPage = 1;}
						
						$currentOffset = ($currentPage -1)* $sphinx_conf['page_size'];
						
						if ($currentOffset > ($sphinx_conf['max_matches']-$sphinx_conf['page_size']) ) {
							die("Only the first {$sphinx_conf['max_matches']} results accessible");
						}
					} else {
						$currentPage = 1;
						$currentOffset = 0;
					}

					
					//Connect to sphinx, and run the query
					$cl = new SphinxClient();
					//$cl_count = new SphinxClient();
					$cl->SetServer($sphinx_conf['sphinx_host'], $sphinx_conf['sphinx_port']);
					//$cl_count->SetServer($sphinx_conf['sphinx_host'], $sphinx_conf['sphinx_port']);
					$cl->SetMatchMode($mode);
					//$cl_count->SetMatchMode($mode);
					$cl->SetRankingMode ( SPH_RANK_SPH04 );
					//$cl_count->SetRankingMode ( SPH_RANK_SPH04 );
					if($sort=='title')
					{
						$cl->SetSortMode ( SPH_SORT_ATTR_ASC, "product_name" );
					}elseif($sort=='priceasc')
					{
						$cl->SetSortMode ( SPH_SORT_ATTR_ASC, "price_u" );
					}elseif($sort=='pricedesc')
					{
						$cl->SetSortMode ( SPH_SORT_ATTR_DESC, "price_u" );
					}else
					{//product_name
						$cl->SetFieldWeights(array('product_name' => 10000, 'filter_id' => 500, 'product_type' => 100));		
						//$cl->SetSortMode ( SPH_SORT_ATTR_ASC, "product_type" );
						$cl->setSelect("*,IN(filter_id,67) + IN(filter_id,71) + IN(filter_id,72) + IN(filter_id,79) + IN(filter_id,77) + IN(filter_id,76) + IN(filter_id,78) + IN(filter_id,80) AS matchtags");
						//$cl->SetSortMode(SPH_SORT_EXTENDED, 'matchtags DESC');
						$cl->SetSortMode ( SPH_SORT_EXTENDED  , "@weight DESC" );
					}
					
					$cl->SetLimits($currentOffset,$sphinx_conf['page_size']); //current page and number of results
					$filter_arr = array();
					$price_filter_arr = array();
					if(trim($q)=='')
						$q='product';
					$filter_string = "*, ";
					if($filter!='')
					{
						$filter_arr = explode(",",$filter);
						if(count($filter_arr)>0)
						{
							$cl->SetFilter('filter_id',$filter_arr);
							/*foreach($filter_arr as $p_id)
							{
									$filter_string .= " filter_id=".$p_id." OR ";
							}*/
								
						}
					}
					if($filter_string!='*, ')
					{
						$filter_string = trim($filter_string,' OR');
						$filter_string .= '  AS mycond ';
						//echo $filter_string;die;
						$cl->SetSelect( $filter_string );
						$cl->SetFilter( "mycond", array(1) );
					}
					/*
					if($price_filter!='')
					{
						$price_filter_arr = explode(",",$price_filter);
						if(count($price_filter_arr)>0)
						{
							$price_arr = array();
							foreach($price_filter_arr as $price_range)
							{
								$price_arr = explode("_",$price_range);
								if(is_array($price_arr) && count($price_arr)>1)
								{
									$lower = (int)$price_arr[0]; 
									$heigher = (int)$price_arr[1]; 
									$cl->SetFilterRange( "price_u", $lower, $heigher, false );
								}
							}
						}
					}*/				
					/*$cl_count->SetLimits(0,200);
					$cl_count->SetSelect("@groupby,@count");
					$cl_count->setGroupBy('filter_id',SPH_GROUPBY_ATTR, '@count desc' );	
					$res_for_counts = $cl_count->Query($q, $sphinx_conf['sphinx_index']);
					if ( !empty($res_for_counts) && isset($res_for_counts["matches"]) && is_array($res_for_counts["matches"])) {
						$ids_array = $res_for_counts["matches"];
						//print_r($ids_array);die;
						foreach($ids_array as $match)
						{	
							$count_array[$match['attrs']['@groupby']]= $match['attrs']['@count'];
						}
						
					}*/					
					$res = $cl->Query($q, $sphinx_conf['sphinx_index']);
					if(	$res['total_found'] == 0 && $q!='product')
					{
						$res = $cl->Query("code search, fast coder", $sphinx_conf['sphinx_index']);
					}					
					//echo "Hello";
					//print_r($res);die;
					$result_string = "";
					if (empty($res) || !isset($res["matches"])) {
						//echo "comming";die;
						$result_string .= "Query failed: -- please try again later.\n";
						if ($sphinx_conf['debug'] && $cl->GetLastError())
							$result_string .= "<br/>Error: ".$cl->GetLastError()."\n\n";
						return;
					} else {
						
						$resultCount = $res['total_found'];
						$numberOfPages = ceil($res['total']/$sphinx_conf['page_size']);
					}
					//$ids = '';
					//$ids_array = array();
					if ( !empty($res) && isset($res["matches"]) && is_array($res["matches"])) {
						//Build a list of IDs for use in the mysql Query and looping though the results
						$items = $res["matches"];
					} else {
						return;
					}
				/////Start processing for advanced search.
					$master_array = array();
					$msg = "";
					//$items = $rows;
					$totalResults = $resultCount;
					$startIndex = $page;
					
					$itemsPerPage = $this->CI->config->item('sphinx_page_size');
					if((int)$totalResults>=1000) $msg = 'The searched term returns large number of search results. Please add more search keywords to get specific results';
					
					
					///// totalResults - get total value returned by sphinx
					///// startindex - page page number as stated by sphinx
					///// itemsPerPage - number of items per page
					///// currentpage - current page number
					///// spelling - Spelling sugession 
					///// msg - any message to be sent along with data
					if($totalResults>0){
						$master_array = array(
									'totalResults'=>$totalResults,
									'startIndex'=>$startIndex,
									'itemsPerPage'=>$itemsPerPage,
									'currentpage'=>$page,
									'spelling'=>'',
									'result'=>$master_array,
									'msg'=>$msg,
									'countarray'=>$count_array);
					}else{

						$master_array = array(
									'totalResults'=>$totalResults,
									'startIndex'=>$startIndex,
									'itemsPerPage'=>$itemsPerPage,
									'currentpage'=>$page,
									'spelling'=>$spell,
									'result'=>$master_array,
									'msg'=>$msg,
									'countarray'=>$count_array);
					}
					
					///// traverse each item and copy to array
					//$counter = 0;
					//print_r($items);die;
					foreach ($items as $id=>$match) {
						$item = $match['attrs'];
						$doc_type = $item['doc_type'];
						$controller="";
						$postfix_url = '';
						$link = '';
						$imagepath = '';
						$titles = '';
						$type = '';
						$pre_title ='';
						$context='';

						$titles = $item['product_name'];
						if($item['product_user_url'])
						{
							$link = current_base_url().str_replace('my-','',$item['product_user_url']);							
						}elseif($item['product_type']=='audio')
						{
							$link = current_base_url().'coding-education/'.$item['product_url'];
						}elseif($item['product_type']=='onlinestore')
						{
							$link = current_base_url().$item['product_url'];
						}elseif($item['product_type']=='elearning')
						{
							$link = current_base_url().'icd10/'.$item['product_url'];
						}elseif($item['product_type']=='book')
						{
							$link = current_base_url().'coding-education/books/'.$item['product_url'];
						}else
						{
							$link = current_base_url().str_replace('/my-','/',$item['product_url']);
						}
						if($item['image'])
						{
							switch($item['product_type'])
							{
								case 'book':
								$imagepath = static_files_url().'images/front/books/'.$item['image'];
								break;
								case 'onlinestore':
								$imagepath = static_files_url().'images/store/'.$item['image'];
								break;
								case 'specialty':
								$imagepath = static_files_url().'images/front/'.$item['image'];
								break;
							}
						}
						///// populate page title and links for advanced search
						if($item['price']==0)
						{
							$master_array['result'][] = array(
										'type'=>$item['product_type'],
										'title'=>str_replace(array('\\','"'),array('\\\\','\"'),$titles),
										'link'=>$link,'image'=>$imagepath,'context'=>$item['search_desc'].'<br/>'.str_replace(array('\\','"'),array('\\\\','\"'),'<span style="color:#cc0000">Call for Pricing</san>'),
										);
						}else{
							$master_array['result'][] = array(
										'type'=>$item['product_type'],
										'title'=>str_replace(array('\\','"'),array('\\\\','\"'),$titles),
										'link'=>$link,'image'=>$imagepath,'context'=>$item['search_desc'].'<br/>'.str_replace(array('\\','"'),array('\\\\','\"'),'$'.number_format($item['price'],2,'.','')),
										);
						}
							
					}
					///// convert the master array to json string
					$output = $this->array_to_json_string_advance($master_array);
					//$output = $this->offersToJSON($master_array);
				
					//// Output the json string
					return $output;
		//}
	}
	

    /**
     * Performs a Sphinx search for guidelines, leyterms, history, usernotes etc.
     */
    public function tools_search($filter,$q='',$searchtype='tabcode',$page=1,$iscodeset=0,$iscpthcpcs=0,$sort='',$guideline='',$negative_word='') {
	
		$sphinx_conf['sphinx_host'] = $this->CI->config->item('sphinx_server');
		$sphinx_conf['sphinx_port'] = $this->CI->config->item('sphinx_server_port'); //this demo uses the SphinxAPI interface
		#can use 'excerpt' to highlight using the query, or 'asis' to show description as is.
		$sphinx_conf['body'] = $this->CI->config->item('sphinx_body');
		#the link for the title (only $id) placeholder supported
		//$sphinx_conf['link_format'] = "";
		#Change this to FALSE on a live site!
		$sphinx_conf['debug'] = $this->CI->config->item('sphinx_debug');
		#How many results per page
		$sphinx_conf['page_size'] = $this->CI->config->item('sphinx_page_size');
		#maximum number of results - should match sphinxes max_matches. default 1000
		$sphinx_conf['max_matches'] = $this->CI->config->item('sphinx_max_matches');
		$resultCount = 0;
		$count_array = array();
		$result_string = '';
		$spell = '';
		$login = is_logged_in();
		$mode = SPH_MATCH_EXTENDED2;
		if($iscpthcpcs)
		{
			$mode = SPH_MATCH_ANY;
		}
		//echo $searchtype;die;
		if (!empty($q)) 
		{
			//produce a version for display
			/*
			$ignore_words = array('/cpt/','/hcpcs/','/icd/','/icd-9/','/icd-9-cm/','/icd9/','/icd-10/','/icd-10-cm/','/icd10/','/drg/','/apc/','/code/');
			$q = strtolower($q);
			$q= preg_replace($ignore_words,'',$q);
			*/
			$qo = $q;
			$q_array = explode(' ',$q);
			///////////////////////////////////////////////////////////////////////			
			switch($filter)
			{
				case 2:
					//if($iscodeset)
					//{
					//	$mode = SPH_MATCH_ANY;
					//}
					$sphinx_conf['sphinx_index'] = "index_guidelines";
				break;
				case 3:
					$sphinx_conf['sphinx_index'] = "index_layterms";
				break;
				case 9:
					$sphinx_conf['sphinx_index'] = "index_history";
				break;
			}
				
					//setup paging...
					if (!empty($page)) {
						$currentPage = intval($page);
						if (empty($currentPage) || $currentPage < 1) {$currentPage = 1;}
						
						$currentOffset = ($currentPage -1)* $sphinx_conf['page_size'];
						
						if ($currentOffset > ($sphinx_conf['max_matches']-$sphinx_conf['page_size']) ) {
							die("Only the first {$sphinx_conf['max_matches']} results accessible");
						}
					} else {
						$currentPage = 1;
						$currentOffset = 0;
					}

					
					//Connect to sphinx, and run the query
					$cl = new SphinxClient();
					$cl->SetServer($sphinx_conf['sphinx_host'], $sphinx_conf['sphinx_port']);
					if($negative_word!='')
					{
						$cl->SetMatchMode ( SPH_MATCH_EXTENDED2 );
						$q = $q." !".$negative_word;
					}else
					{
						$cl->SetMatchMode($mode);
					}
					$cl->SetRankingMode ( SPH_RANK_SPH04 );
					$cl->SetLimits($currentOffset,$sphinx_conf['page_size']); //current page and number of results
					/*sorting rule */
					$sorting_exp = '';
					/*foreach($q_array as $term)
					{
						$code_term = crc32(trim($term));
						$sorting_exp .= " IF(code_title_int=".$code_term.",10,0) +";
					}
					if($sorting_exp!='')
					{
						$sorting_exp  = trim($sorting_exp,' +')." as codepriority ";
					}					
					$cl->SetSelect("*, ".$sorting_exp);					
					$cl->SetSortMode(SPH_SORT_EXTENDED, "codepriority DESC, @relevance DESC");
					*/
					/*sorting rule */	
					$cl->setGroupBy('code_title',SPH_GROUPBY_ATTR);	

					$guideline_arr = array();
					if($guideline!='')
					{
						//echo $guideline;die;
						$guideline_arr = explode(",",$guideline);
						if(count($guideline_arr)>0)
						{
							$cl->SetFilter('code_type_int',$guideline_arr);
						}
					}
					$res = $cl->Query($q, $sphinx_conf['sphinx_index']);
					//print_r($res);die;
					$result_string = "";
					if (empty($res) || !isset($res["matches"])) {
						//echo "comming";die;
						$result_string .= "Query failed: -- please try again later.\n";
						if ($sphinx_conf['debug'] && $cl->GetLastError())
							$result_string .= "<br/>Error: ".$cl->GetLastError()."\n\n";
						return;
					} else {
						
						$resultCount = $res['total_found'];
						$numberOfPages = ceil($res['total']/$sphinx_conf['page_size']);
					}
					//$ids = '';
					//$ids_array = array();
					if ( !empty($res) && isset($res["matches"]) && is_array($res["matches"])) {
						//Build a list of IDs for use in the mysql Query and looping though the results
						$items = $res["matches"];
					} else {
						return;
					}
				/////Start processing for advanced search.
					$master_array = array();
					$msg = "";
					//$items = $rows;
					$totalResults = $resultCount;
					$startIndex = $page;
					
					$itemsPerPage = $this->CI->config->item('sphinx_page_size');
					if((int)$totalResults>=1000) $msg = 'The searched term returns large number of search results. Please add more search keywords to get specific results';
					$specialmsg = '';//msg for logged out or unsubscribed users.
					if(!$login || !has_access(1) || !has_access(4))
					{
						$specialmsg = "<div style='padding:10px; background:#f5f444;'><strong>This is a sample page showing only the result  headline(s). To see the information under the blue headline(s), login or  subscribe to <a href='".current_base_url()."coding-tools/code-search'>Code  Search</a>, <a href='".current_base_url()."coding-tools/fast-coder'>Fast  Coder</a> or <a href='".current_base_url()."/coding-solutions'>Coding  Solutions</a>.</strong> <strong><br /><br />A subscriber will see under:</strong><br /><strong>Guidelines - </strong>All AMA, AHA, or CMS official guidelines including the <strong>code(s) and/or keyword(s) entered</strong>.<strong></strong> <br /><br /><strong>Lay Term - </strong>All Coding Institute written plain English  explanations Lay Terms containing the <strong>CPT&reg; or HCPCS code(s) and/or keyword(s) entered</strong>.<strong></strong><strong><br /><br />Personal Notes -  </strong>All personal notes for the code entered and all personal notes containing the <strong>code(s) or keyword(s)</strong> entered. This option is <em><strong>visible only to logged in subscribers</strong></em> after they have saved personal notes under a code's hierarchy or details page.<strong><br /><br />Upcoming and Historical Info - </strong>All CPT&reg; code new and past changes including the <strong>code(s)  or keyword(s) entered</strong>.<strong></strong></div>";
					}
					
					///// totalResults - get total value returned by sphinx
					///// startindex - page page number as stated by sphinx
					///// itemsPerPage - number of items per page
					///// currentpage - current page number
					///// spelling - Spelling sugession 
					///// msg - any message to be sent along with data
					if($totalResults>0){
						$master_array = array(
									'totalResults'=>$totalResults,
									'startIndex'=>$startIndex,
									'itemsPerPage'=>$itemsPerPage,
									'currentpage'=>$page,
									'spelling'=>'',
									'result'=>$master_array,
									'msg'=>$msg,
									'countarray'=>$count_array,
									'specialmsg'=>$specialmsg);
					}else{

						$master_array = array(
									'totalResults'=>$totalResults,
									'startIndex'=>$startIndex,
									'itemsPerPage'=>$itemsPerPage,
									'currentpage'=>$page,
									'spelling'=>$spell,
									'result'=>$master_array,
									'msg'=>$msg,
									'countarray'=>$count_array,
									'specialmsg'=>$specialmsg);
					}
					
					///// traverse each item and copy to array
					//$counter = 0;
					$new_line = array('02:','03:','04:','05:','06:','07:','08:','09:','10:','11:','12:','13:','14:','15:','16:','17:','18:','19:','20:','21:','22:','23:','24:','25:','26:','27:','28:','29:','30:','31:','32:','33:','34:','35:','36:','37:','38:','39:','40:','41:','42:','43:','44:','45:','46:','47:','48:','49:','50:');
					$relevancy = array();
					$data_array = array();
					foreach ($items as $id=>$match) {
						$item = $match['attrs'];
						$doc_type = $item['doc_type'];
						$controller="";
						$postfix_url = '';
						$link = '';
						$pdflink = '';
						$titles = '';
						$type = '';
						$pre_title ='';
						$context = '';
						$lt_general_arr='';
						$lt_physician_arr='';
						$lt_terminology_arr='';
						$lt_tips_arr='';
						$lt_additional_arr='';

						if($item['code_type']=='CPT')
						{
							switch($item['toolsfilter'])
							{
								case 2: 
									$link = current_base_url().'cpt-codes/'.$item['code_title']."#guidelines";
									$titles = "CPT Guidelines : ".$item['code_title'];
									$context = '';
									if($login && has_access(1))
									{								
										if(!empty($item['code_guideline']) && trim($item['code_guideline'])!='')
												{
													$context .= '<strong style="text-decoration:underline;">Code Specific Guideline</strong><br>'.substr(str_replace($new_line,"<br />",str_replace('01:','',$this->mystriptag($item['code_guideline']))),0,200)."...".'<br />';
												}
												
										 if(!empty($item['range_guideline']) && trim($item['range_guideline'])!='')
												{ 
													$context .= '<strong style="text-decoration:underline;">Range Specific Guideline</strong><br>'.substr(str_replace($new_line,"<br />",str_replace('01:','',$this->mystriptag($item['range_guideline']))),0,200)."...".'<br />';
												}
										if(!empty($item['section_guideline']) && trim($item['section_guideline'])!='')
												{
													$context .= '<strong style="text-decoration:underline;">Section Specific Guideline</strong><br>'.substr(str_replace($new_line,"<br/>",str_replace('01:','',$this->mystriptag($item['section_guideline']))),0,200)."...".'';
												}									
										$context=preg_replace("/[^a-zA-Z0-9<>=;:\,\-\/\'\"\s]/", "", $context);
										//$context=substr($context,0,400)."...";
									}else{
										//$context='Abbreviated results are shown. Subscribers to Code Search, Fast Coder, or Coding Solutions';
										$context='';
									}
								break;
								case 3: 
									$link = current_base_url().'cpt-codes/'.$item['code_title']."#layterms";
									$titles = "Lay Term : ".$item['code_title'];	
									//if(!empty($item['meta_value']))
									//	{
										if($item['additional']!='')// Meta Key Type For CPT Layterms
											{
												$lt_additional_arr = $this->mystriptag($item['additional']);
											}
										if($item['summary']!='')// Meta Key Type For CPT Layterms
											{
											$lt_general_arr = $this->mystriptag($item['summary']);
											}
										if($item['clinic_resp']!='')// Meta Key Type For CPT Layterms
											{
											$lt_physician_arr = $this->mystriptag($item['clinic_resp']);
											}
										if($item['terminology_alert']!='')// Meta Key Type For CPT Layterms
											{
											$lt_terminology_arr = $this->mystriptag($item['terminology_alert']);
											}
										if($item['coding_tips']!='')// Meta Key Type For CPT Layterms
											{
											$lt_tips_arr = $this->mystriptag($item['coding_tips']);
											}
										if($lt_general_arr && $lt_general_arr!='')		
											$context .= $lt_general_arr;
										if($lt_physician_arr && $lt_physician_arr!='')		
											$context .= "<strong>Physician Responsibility</strong><br>".$lt_physician_arr;
										if($lt_terminology_arr && $lt_terminology_arr!='')	
											$context .= "<strong>Terminology</strong><br>".$lt_terminology_arr;
										if($lt_tips_arr && $lt_tips_arr!='')		
											$context .= "<strong>Tips</strong><br>".$lt_tips_arr;
										if($lt_additional_arr && $lt_additional_arr!='')	
											$context .= "<strong>Additional Info</strong><br>".$lt_additional_arr;
									//}
									if($login && has_access(1))
									{								
										$context=preg_replace("/[^a-zA-Z0-9<>=;:\,\-\/\'\"\s]/", "", $context);
										//$context=substr($context,0,500)."...";
										$context="<div style='text-align:left !important;'>".substr($context,0,500)."... </div>";

									}else{
										//$context='Abbreviated results are shown. Subscribers to Code Search, Fast Coder, or Coding Solutions';
										$context='';
									}
									break;
								case 9: 
									$titles = "Upcoming and Historical Information : ".$item['code_title'];	
									$link_ext = '';
									if($item['category']!='')
									{
										$history_cat = explode(',',$item['category']);
										if(is_array($history_cat) && count($history_cat)>0)
										$link_ext = "/code-category/".$history_cat[0];
									}
									$link = current_base_url().'cpt-code-changes-2013'.$link_ext.'#'.$item['code_title'];
									$context=$item['change_year'].' ';
									if($item['change_type']=='DELETED')	
									{
										$context .= "Code Deleted"; 
										$deleted = 1;
									}
									if($item['change_type']=='CHANGED') 
									{
										$context .=  "Change in long description"; 
									}
									if($item['change_type']=='ADDED')
									{
										if($deleted==1)
										{
											$context .=  "Code Reinstated"; 
										}
										else 
										{
											$context .=  "Code Added"; 
										}
									}									
								if($login && has_access(1))
								{								
									$context .= '<br />'.$item['current_value'];
									$context=substr($context,0,300)."...";
								}else{
									//$context='Abbreviated results are shown. Subscribers to Code Search, Fast Coder, or Coding Solutions';
									$context='';
								}
								
								break;
							}
						}elseif($item['code_type']=='HCPCS')
						{
							switch($item['toolsfilter'])
							{
								case 2: 
									$link = current_base_url().'hcpcs-codes/'.$item['code_title']."#guidelines";
									$titles = "HCPCS Guidelines : ".$item['code_title'];	
									if($login && has_access(4))
									{								
										$context=$this->mystriptag($item['code_guideline']);
										$context=substr($context,0,500)."...";
									}else{
										//$context='Abbreviated results are shown. Subscribers to Code Search, Fast Coder, or Coding Solutions';
										$context='';
									}
								break;
								case 3: 
									$link = current_base_url().'hcpcs-codes/'.$item['code_title']."#layterms";
									$titles = "Lay Term : ".$item['code_title'];	
									//$context=$this->mystriptag($item['description']);
									if(!empty($item['meta_value']))
										{
										/* Get HCPCS Layterms Meta*/		
										if($item['meta_key']=='HCPCS_LT_additional')// Meta Key Type For HCPCS Layterms
											{
												$lt_additional_arr = $this->mystriptag($item['meta_value']);
											}
										if($item['meta_key']=='HCPCS_LT_general')// Meta Key Type For HCPCS Layterms
											{
											$lt_general_arr = $this->mystriptag($item['meta_value']);
											}
										if($item['meta_key']=='HCPCS_LT_physician_responsibility')// Meta Key Type For HCPCS Layterms
											{
											$lt_physician_arr = $this->mystriptag($item['meta_value']);
											}
										if($item['meta_key']=='HCPCS_LT_terminology')// Meta Key Type For HCPCS Layterms
											{
											$lt_terminology_arr = $this->mystriptag($item['meta_value']);
											}
										if($item['meta_key']=='HCPCS_LT_tips')// Meta Key Type For HCPCS Layterms
											{
											$lt_tips_arr = $this->mystriptag($item['meta_value']);
											}
										if($lt_general_arr && $lt_general_arr!='')		
											$context .= $lt_general_arr;
										if($lt_physician_arr && $lt_physician_arr!='')		
											$context .= "<strong>Physician Responsibility</strong><br>".$lt_physician_arr;
										if($lt_terminology_arr && $lt_terminology_arr!='')	
											$context .= "<strong>Terminology</strong><br>".$lt_terminology_arr;
										if($lt_tips_arr && $lt_tips_arr!='')		
											$context .= "<strong>Tips</strong><br>".$lt_tips_arr;
										if($lt_additional_arr && $lt_additional_arr!='')	
											$context .= "<strong>Additional Info</strong><br>".$lt_additional_arr;
									}
									if($login && has_access(4))
									{								
										$context=preg_replace("/[^a-zA-Z0-9<>=;:\,\-\/\'\"\s]/", "", $context);
										$context="<div style='text-align:left !important;'>".substr($context,0,500)."... </div>";
									}else{
										//$context='Abbreviated results are shown. Subscribers to Code Search, Fast Coder, or Coding Solutions';
										$context='';
									}
								break;
								case 9: 
									$titles = "Upcoming and Historical Information : ".$item['code_title']." ".$item['cange_type'];	
									$link_ext = '';
									if($item['category']!='')
									{
										$history_cat = explode(',',$item['category']);
										if(is_array($history_cat) && count($history_cat)>0)
										$link_ext = "/code-changes/".$history_cat[0];
									}
									$link = current_base_url().'hcpcs-code-changes-2013'.$link_ext.'#'.$item['code_title'];
									if($login && has_access(4))
									{								
										$context=$item['change_year'].' ';
										if($item['change_type']=='DELETED')	
										{
											$context .= "Code Deleted"; 
											$deleted = 1;
										}
										if($item['change_type']=='CHANGED') 
										{
											$context .=  "Change in long description"; 
										}
										if($item['change_type']=='ADDED')
										{
											if($deleted==1)
											{
												$context .=  "Code Reinstated"; 
											}
											else 
											{
												$context .=  "Code Added"; 
											}
										}									
										$context .= '<br />'.$item['current_value'];
										$context=substr($context,0,300)."...";
									}else{
										//$context='Abbreviated results are shown. Subscribers to Code Search, Fast Coder, or Coding Solutions';
										$context='';
									}
								break;
							}
						}elseif($item['code_type']=='ICD9')
						{
							if($item['toolsfilter']==2){
								$link = current_base_url().'icd9-codes/'.$item['code_title']."#guidelines";
								$titles = "ICD-9 Guidelines : ".$item['code_title'];	
								if($login && has_access(2))
								{								
									$context = stripslashes(utf8_encode(html_entity_decode(str_replace('&nbsp;'," ",$item['chepter_guideline']))));
									//$context=str_replace('&nbsp;'," ",$context);
									$context=str_replace($new_line,"<br />",strip_tags($this->mystriptag($context)));
									$context='<strong style="text-decoration:underline;">Chapter Specific Guideline</strong><br>'.substr($context,0,500)."...";
								}else{
									//$context='Abbreviated results are shown. Subscribers to Code Search, Fast Coder, or Coding Solutions';
									$context='';
								}
							}
						}elseif($item['code_type']=='ICD9Vol3')
						{
							if($item['toolsfilter']==2){
								$link = current_base_url().'icd9-codes-vol3/'.$item['code_title']."#guidelines";
								$titles = "ICD-9 Vol3 Guidelines: ".$item['code_title'];	
								if($login && has_access(3))
								{								
									$context = stripslashes(utf8_encode(html_entity_decode($item['code_guideline'])));
									$context = stripslashes(utf8_encode(html_entity_decode($context)));
									$context=str_replace($new_line,"<br />",$this->mystriptag($context));
									$context='<strong style="text-decoration:underline;">Code Specific Guideline</strong><br>'.substr($context,0,500)."...";
								}else{
									//$context='Abbreviated results are shown. Subscribers to Code Search, Fast Coder, or Coding Solutions';
									$context='';
								}
							}
						}elseif($item['code_type']=='ICD10')
						{
							if($item['toolsfilter']==2){
								$link = current_base_url().'icd-10-codes/'.$item['code_title']."#guidelines";
								$titles = "ICD-10 Guidelines : ".$item['code_title'];	
								if($login && has_access(64))
								{								
									$context = stripslashes(utf8_encode(html_entity_decode($item['chepter_guideline'])));
									$context=str_replace($new_line,"<br />",$this->mystriptag($context));
									$context='<strong style="text-decoration:underline;">Chapter Specific Guideline</strong><br>'.substr($context,0,500)."...";
								}else{
									//$context='Abbreviated results are shown. Subscribers to Code Search, Fast Coder, or Coding Solutions';
									$context='';
								}
							}
						}
						///// populate page title and links for advanced search
						$data_array[] = array(
										'type'=>$item['product_type'],
										'title'=>str_replace(array('\\','"'),array('\\\\','\"'),$titles),
										'link'=>$link,'linkpdf'=>$pdflink,'context'=>str_replace(array('\\','"'),array('\\\\','\"'),$context),
										);
						$sort_value = 0;
						foreach($q_array as $term)
						{
							if($item['code_title']==$term)
									$sort_value = 10;
						}
						$relevancy[] = $sort_value;
					}
					//print_r($relevancy);
					//print_r($data_array);
					array_multisort($relevancy, SORT_DESC, $data_array);
					//print_r($data_array);
					$master_array['result'] = $data_array;
					///// convert the master array to json string
					$output = $this->array_to_json_string_advance($master_array);
					//$output = $this->offersToJSON($master_array);
				
					//// Output the json string
					return $output;
		}
	}

    /**
     * Performs a Sphinx search for personal notes and shared notes etc.
     */
    public function notes_search($filter,$q='',$searchtype='tabcode',$page=1,$search_and='',$negative_word='',$sort='') {
	
		$sphinx_conf['sphinx_host'] = $this->CI->config->item('sphinx_server');
		$sphinx_conf['sphinx_port'] = $this->CI->config->item('sphinx_server_port'); //this demo uses the SphinxAPI interface
		#can use 'excerpt' to highlight using the query, or 'asis' to show description as is.
		$sphinx_conf['body'] = $this->CI->config->item('sphinx_body');
		#the link for the title (only $id) placeholder supported
		//$sphinx_conf['link_format'] = "";
		#Change this to FALSE on a live site!
		$sphinx_conf['debug'] = $this->CI->config->item('sphinx_debug');
		#How many results per page
		$sphinx_conf['page_size'] = $this->CI->config->item('sphinx_page_size');
		#maximum number of results - should match sphinxes max_matches. default 1000
		$sphinx_conf['max_matches'] = $this->CI->config->item('sphinx_max_matches');
		$resultCount = 0;
		$count_array = array();
		$result_string = '';
		$spell = '';
		$login = is_logged_in();
		$user_id = '';
		$current_user = $this->CI->session->userdata('user');
		//print_r($current_user);die;
		if(isset($current_user['user_id']) && $current_user['user_id']!='')
		{
			$user_id=$current_user['user_id'];
		}
		if($search_and)
		{
  	    	$mode = SPH_MATCH_EXTENDED2;
		}
		else
		{
			$mode = SPH_MATCH_ANY;
		}
		if (!empty($q)) 
		{
			//produce a version for display
			$qo = $q;
			///////////////////////////////////////////////////////////////////////			
				
				$sphinx_conf['sphinx_index'] = "users_notes";
					//setup paging...
					if (!empty($page)) {
						$currentPage = intval($page);
						if (empty($currentPage) || $currentPage < 1) {$currentPage = 1;}
						
						$currentOffset = ($currentPage -1)* $sphinx_conf['page_size'];
						
						if ($currentOffset > ($sphinx_conf['max_matches']-$sphinx_conf['page_size']) ) {
							die("Only the first {$sphinx_conf['max_matches']} results accessible");
						}
					} else {
						$currentPage = 1;
						$currentOffset = 0;
					}

					
					//Connect to sphinx, and run the query
					$cl = new SphinxClient();
					$cl->SetServer($sphinx_conf['sphinx_host'], $sphinx_conf['sphinx_port']);
					if($negative_word!='')
					{
						$cl->SetMatchMode ( SPH_MATCH_EXTENDED2 );
						$q = $q." !".$negative_word;
					}else
					{
						$cl->SetMatchMode($mode);
					}
					$cl->SetRankingMode ( SPH_RANK_SPH04 );
					$cl->SetLimits($currentOffset,$sphinx_conf['page_size']); //current page and number of results
					//$cl->setGroupBy('code_title',SPH_GROUPBY_ATTR);	

					
					$filter_arr = array();
					if($filter!='')
					{
						$filter_arr = array($user_id);
						//print_r($filter_arr);
						if(count($filter_arr)>0)
						{
							$cl->SetFilter('user_id',$filter_arr);
						}
					}
					$res = $cl->Query($q, $sphinx_conf['sphinx_index']);
					//print_r($res);die;
					$result_string = "";
					if (empty($res) || !isset($res["matches"])) {
						//echo "comming";die;
						$result_string .= "Query failed: -- please try again later.\n";
						if ($sphinx_conf['debug'] && $cl->GetLastError())
							$result_string .= "<br/>Error: ".$cl->GetLastError()."\n\n";
						return;
					} else {
						
						$resultCount = $res['total_found'];
						$numberOfPages = ceil($res['total']/$sphinx_conf['page_size']);
					}
					//$ids = '';
					//$ids_array = array();
					if ( !empty($res) && isset($res["matches"]) && is_array($res["matches"])) {
						//Build a list of IDs for use in the mysql Query and looping though the results
						$ids_array = array_keys($res["matches"]);
						foreach($ids_array as $match)
						{	
							$ids .= $match.",";		
						}	
						$ids = trim($ids,",");
						//$items = $res["matches"];
						$items=$this->CI->db->query("select * from  user_personal_notes where notes_id in ($ids)")->result_array();						
					} else {
						return;
					}
				/////Start processing for advanced search.
					$master_array = array();
					$msg = "";
					//$items = $rows;
					$totalResults = $resultCount;
					$startIndex = $page;
					
					$itemsPerPage = $this->CI->config->item('sphinx_page_size');
					if((int)$totalResults>=1000) $msg = 'The searched term returns large number of search results. Please add more search keywords to get specific results';
					
					
					///// totalResults - get total value returned by sphinx
					///// startindex - page page number as stated by sphinx
					///// itemsPerPage - number of items per page
					///// currentpage - current page number
					///// spelling - Spelling sugession 
					///// msg - any message to be sent along with data
					if($totalResults>0){
						$master_array = array(
									'totalResults'=>$totalResults,
									'startIndex'=>$startIndex,
									'itemsPerPage'=>$itemsPerPage,
									'currentpage'=>$page,
									'spelling'=>'',
									'result'=>$master_array,
									'msg'=>$msg,
									'countarray'=>$count_array);
					}else{

						$master_array = array(
									'totalResults'=>$totalResults,
									'startIndex'=>$startIndex,
									'itemsPerPage'=>$itemsPerPage,
									'currentpage'=>$page,
									'spelling'=>$spell,
									'result'=>$master_array,
									'msg'=>$msg,
									'countarray'=>$count_array);
					}
					
					///// traverse each item and copy to array
					//$counter = 0;
					foreach ($items as $item) {
					//foreach ($items as $id=>$match) {
						//$item = $match['attrs'];
						$doc_type = $item['doc_type'];
						$link = '';
						$titles = '';
						$type = '';
						$context = '';
						switch($item['code_type'])
						{
							case 'CPT':
								$link = current_base_url().'cpt-codes/'.$item['code_title']."#personal_notes";
								$titles = "CPT Personal Notes : ".$item['code_title'];	
								$context=$this->mystriptag($item['user_notes']);
							break;
							case 'HCPCS':
								$link = current_base_url().'hcpcs-codes/'.$item['code_title']."#personal_notes";
								$titles = "HCPCS Personal Notes : ".$item['code_title'];	
								$context=$this->mystriptag($item['user_notes']);
							break;
							case 'APC':
								$link = current_base_url().'apc-codes/'.$item['code_title']."#personal_notes";
								$titles = "APC Personal Notes : ".$item['code_title'];	
								$context=$this->mystriptag($item['user_notes']);
							break;
							case 'DRG':
								$link = current_base_url().'drg-codes/'.$item['code_title']."#personal_notes";
								$titles = "DRG Personal Notes : ".$item['code_title'];	
								$context=$this->mystriptag($item['user_notes']);
							break;
							case 'ICDVol1':
								$link = current_base_url().'icd9-codes/'.$item['code_title']."#personal_notes";
								$titles = "ICD-9 CM Personal Notes : ".$item['code_title'];	
								$context=$this->mystriptag($item['user_notes']);
							break;
							case 'ICDVol3':
								$link = current_base_url().'icd9-codes-vol3/'.$item['code_title']."#personal_notes";
								$titles = "ICD-9 Vol.3 Personal Notes : ".$item['code_title'];	
								$context=$this->mystriptag($item['user_notes']);
							break;
							case 'ICD10':
								$link = current_base_url().'icd-10-codes/'.$item['code_title']."#personal_notes";
								$titles = "ICD-10 Personal Notes : ".$item['code_title'];	
								$context=$this->mystriptag($item['user_notes']);
							break;
						}
						///// populate page title and links for advanced search
						$master_array['result'][] = array(
										'type'=>$item['product_type'],
										'title'=>str_replace(array('\\','"'),array('\\\\','\"'),$titles),
										'link'=>$link,'context'=>str_replace(array('\\','"'),array('\\\\','\"'),$context),
										);
							
					}
					///// convert the master array to json string
					$output = $this->array_to_json_string_advance($master_array);
					//$output = $this->offersToJSON($master_array);
				
					//// Output the json string
					return $output;
		}
	}	

	function linktoself($params,$selflink= '') {
		$a = array();
		$b = explode('?',$_SERVER['REQUEST_URI']);
		if (isset($b[1])) 
			parse_str($b[1],$a);
	
		if (isset($params['value']) && isset($a[$params['name']])) {
			if ($params['value'] == 'null') {
				unset($a[$params['name']]);
			} else {
				$a[$params['name']] = $params['value'];
			}
	
		} else {
			foreach ($params as $key => $value)
				$a[$key] = $value;
		}
	
		if (!empty($params['delete'])) {
			if (is_array($params['delete'])) {
				foreach ($params['delete'] as $del) {
					unset($a[$del]);
				}
			} else {
				unset($a[$params['delete']]);
			}
			unset($a['delete']);
		} 
		if (empty($selflink)) {
			$selflink = $_SERVER['SCRIPT_NAME'];
		} 
		if ($selflink == '/index.php') {
			$selflink = '/';
		}
	
		return htmlentities($selflink.(count($a)?("?".http_build_query($a,'','/')):''));
	}
	

	function string_relevancy($code,$code_desc,$keyword_array)
	{
                                $value = 0;
                                $keyword = trim(implode(' ',$keyword_array));
                                $length_relevancy = (200-strlen($code_desc))/400;
                                
                                $level_relevancy = 0;
                                
                                if(preg_match('/[a-z]{1}[0-9]{2,3}|[a-z]{1}[0-9]{2,3}\.[0-9]{1,2}|[0-9]{3}\.[0-9]{1,2}|[0-9]{3}/isU',$code,$r)) {
                                                if(strlen($code)==3)
                                                {
                                                                $level_relevancy = 0.05;
                                                }
                                                else if(strlen($code)==5)
                                                {
                                                                $level_relevancy = 0.03;
                                                }
                                                else
                                                {
                                                                $level_relevancy = 0.01;
                                                }
                                }
                                $occurrance_relevancy = 0;
                                $position_relevancy = 0;
                                foreach($keyword_array as $keyword) {
                                                $occurrance = @substr_count(strtolower($code_desc),strtolower($keyword));
                                                if($occurrance){
                                                                $occurrance_relevancy += 0.05 + ($occurrance*0.05);
                                                                $position = stripos($code_desc,$keyword)===false?0:stripos($code_desc,$keyword);
                                                                $position_relevancy += (200-$position)/400;
                                                }
                                }
                                //echo $position_relevancy.$code_desc."<br/>";
                                $occurrance_relevancy = $occurrance_relevancy/count($keyword_array);
                                $position_relevancy = $position_relevancy/count($keyword_array);
                                $group_word_relevency = 0;
								/*
                                if($occurrance_relevancy){
                                                if(stripos($code_desc,'other specified')!==false){
                                                                $group_word_relevency += 0.5;
                                                }
                                                else if(stripos($code_desc,'unspecified')!==false){
                                                                $group_word_relevency += 0.4;
                                                }
                                                else if(stripos($code_desc,'NOS')!==false){
                                                                $group_word_relevency += 0.3;
                                                }
                                                else if(stripos($code_desc,'NEC')!==false){
                                                                $group_word_relevency += 0.3;
                                                }
                                                else if(stripos($code_desc,$keyword.' with')!==false){
                                                                $group_word_relevency += 0.2;
                                                }
                                                else if(stripos($code_desc,$keyword.' due to')!==false){
                                                                $group_word_relevency += 0.2;
                                                }
                                }*/
                                
                                $minus_relevancy = 0;
                                if(strtolower(substr($code,0,1))=='e') {
                                                $minus_relevancy = -5;
                                }
                                if(strtolower(substr($code,0,1))=='v') {
                                                $minus_relevancy = -7;
                                }
                                if(strtolower(substr($code,-1,1))=='f') {
                                                $minus_relevancy = -7;
                                }                                
                                if(strtolower(substr($code,-1,1))=='t') {
                                                $minus_relevancy = -9;
                                }                                
                                
                                
                                //$group_word_relevency = 0;
                                //echo $group_word_relevency.$code_desc."<br/>";
                                //$occurrance_relevancy = 0;
                                
                                $value = $length_relevancy + $level_relevancy + $occurrance_relevancy + $position_relevancy + $group_word_relevency + $minus_relevancy;
                                return $value;
                }

	/*
	function check_if_codeset checks if a passed string is a keywords
	Use Regex to identify the codes/keywords
	Parameters string $key_word
	ReturnType Boolean
	*/
	function check_if_codeset($key_word) {
		$iscodeset = false;
		if(trim($key_word)){
			if (is_numeric($key_word)|| preg_match('/^[0-9]{4}[a-zA-Z]{1}/i',$key_word,$res) || preg_match("/^[a-zA-Z]{1}+([\.0-9])+$/i",trim($key_word),$all_codes) || preg_match("/^[a-zA-Z]{1}+([0-9]){4}$/i",trim($key_word),$all_codes) || preg_match("/^[a-zA-Z]{1}+[0-9]{2}+([\.0-9a-zA-Z]){5}+$/i",trim($key_word),$all_codes)) {
				$iscodeset = true;
			}
		}
		return $iscodeset;
	}	
 

	
		/*
	function array_to_json_string converts an array to json string compatible to YUI Tree view
	reccursive funtion to drilldown on multidimention array
	
	Parameter array $arraydata
	ReturnType string 
	*/
	function array_to_json_string($arraydata) {
		$output = "";
		$output .= "[";
		//ksort($arraydata);
		
		foreach($arraydata as $key=>$val){
			$default_expend_clause = '';
			if(isset($val['expend']) && $val['expend']==1 )
				$default_expend_clause = ',"expanded": "true"';
				
			if (is_array($val) && count($val)>0) {
				$output .= '{"type":"Text", "label":"'.str_replace(array('\\','"'),array('\\\\','\"'),$key).'"'.$default_expend_clause.',"children":'.$this->array_to_json_string($val).'},';
			} else {
				if($val!='1' ){
				$output .= '{"type":"HTML", "html":"'.str_replace(array('\\','"'),array('\\\\','\"'),$key).'"},';
				}
			}
		}
		$output = rtrim($output,',');
		$output .= ']';
		return $output;
	}

	/*
	function array_to_json_string_advance converts an array to json string
	reccursive funtion to drilldown on multidimention array
	
	Parameter array $arraydata
	ReturnType string 
	*/
	function array_to_json_string_advance($arraydata) {
		$output = "";
		$output .= "{";
		if(is_array($arraydata)){
			foreach($arraydata as $key=>$val){
				if (is_array($val)) {
					$output .= '"'.$key.'" : ['.$this->array_to_json_string_advance($val).'],';
				} else {
					$output .= '"'.$key.'" : "'.$val.'",';
				}
			}
		}
		$output = rtrim($output,',');

		$output .= '}';
		return $output;
	}
	
	/*
	function to remove speical symbols from string;
	*/
	public function mystriptag($text,$ignore = null){
		$text = str_replace("\t", " ", $text);		
		$text = str_replace("\n", " ", $text);		
		$text = str_replace("\r", " ", $text);		
		$text = str_replace("\0", " ", $text);		
		$text = str_replace("\x0B", " ", $text);
		$text = preg_replace('|<p([^>]*)>\s*</p>|siU', "", $text);		//	Used to remove blank p ie, <p></p>
		$text = preg_replace('/[\r\n\t]+/', "", $text);
		$text = preg_replace('/(style|class)="([^"]*)"/siU', "", $text);
		$text = str_replace(array('&lt;!--','--&gt;','“','”','’','…','•','—'),array('<!--','-->','\'','\'','\'','...','&bull;','&mdash;'),$text);
		$text = strip_tags($text,'<strong><b><em><i><p>');
		$remove = array("\n", "\r\n", "\r", "<p>", "</p>", "<h1>", "</h1>");
		$text = str_replace($remove, ' ', $text);
		$text = str_replace(chr(10), " ", $text);		
		$text = str_replace(chr(13), " ", $text);		
		$text = str_replace(chr(9), " ", $text);		
		
		// To remove junk chars
		$text = str_replace("? ", " ", $text);		
		$text = str_replace(";", " ", $text);		
		$text=str_replace("€˜","'",$text);
		$text=str_replace("€™","'",$text);
		$text=str_replace("€œ","\"",$text);
		$text=str_replace("€","\"",$text);

		$text=str_replace("¡","&#161;",$text);
		$text=str_replace("¢","&#162;",$text);
		$text=str_replace("£","&#163;",$text);
		$text=str_replace("¤","&#164;",$text);
		$text=str_replace("¥","&#165;",$text);
		$text=str_replace("®","&#174;",$text);	
		$text=str_replace("¦","&#166;",$text);
		$text=str_replace("§","&#167;",$text);
		$text=str_replace("¨","&#168;",$text);
		$text=str_replace("©","&#169;",$text);
		$text=str_replace("ª","&#170;",$text);
		$text=str_replace("«","&#171;",$text);
		$text=str_replace("¬","&#172;",$text);
		$text=str_replace("¯","&#175;",$text);
		$text=str_replace("°","&#176;",$text);
		$text=str_replace("±","&#177;",$text);
		$text=str_replace("²","&#178;",$text);
		$text=str_replace("³","&#179;",$text);
		$text=str_replace("´","&#180;",$text);
		$text=str_replace("»","&#187;",$text);
		$text=str_replace("¼","&#188;",$text);
		$text=str_replace("½","&#189;",$text);
		$text=str_replace("¾","&#190;",$text);
		$text=str_replace("™","&#8482;",$text);
		$text=str_replace("€","&#8364;",$text);
		$text=str_replace("‰","&#8240;",$text);
		$text=str_replace("…","&#8230;",$text);
		$text=str_replace("•","&#8226;",$text);
		$text=str_replace("–","&#8211;",$text);
		$text=str_replace("—","&#8212;",$text);
		$text=str_replace("‘","&#8216;",$text);
		$text=str_replace("’","&#8217;",$text);
		$text=str_replace("‚","&#8218;",$text);
		$text=str_replace("“","&#8220;",$text);
		$text=str_replace("”","&#8221;",$text);
		$text=str_replace("„","&#8222;",$text);
		$text=str_replace("†","&#8224;",$text);
		$text=str_replace("‡","&#8225;",$text);

		$text=str_replace("ã","",$text);
		$text=str_replace("Ã","",$text);
		$text=str_replace("á","",$text);
		$text=str_replace("Á","",$text);
		$text=str_replace("à","",$text);
		$text=str_replace("À","",$text);
		$text=str_replace("â","",$text);
		$text=str_replace("Â","",$text);
		$text=str_replace("ç","",$text);
		$text=str_replace("Ç","",$text);
		$text=str_replace("é","",$text);
		$text=str_replace("É","",$text);
		$text=str_replace("è","",$text);
		$text=str_replace("È","",$text);
		$text=str_replace("ê","",$text);
		$text=str_replace("Ê","",$text);
		$text=str_replace("ï","",$text);
		$text=str_replace("Ï","",$text);
		$text=str_replace("í","",$text);
		$text=str_replace("Í","",$text);
		$text=str_replace("ó","",$text);
		$text=str_replace("Ó","",$text);
		$text=str_replace("õ","",$text);
		$text=str_replace("Õ","",$text);
		$text=str_replace("ô","",$text);
		$text=str_replace("Ô","",$text);
		$text=str_replace("ñ","",$text);
		$text=str_replace("Ñ","",$text);
		$text=str_replace("ú","",$text);
		$text=str_replace("Ú","",$text);
		$text=str_replace("º","",$text);	
	return nl2br($text);
	}	
	
	public function offersToJSON($s){
$s=str_replace("\n","",$s);
$s=str_replace("\t","",$s);
while(strpos($s,"  ")>0){$s=str_replace("  "," ",$s);}
$s=str_replace("> <","><",$s);
$s=preg_replace("/\<\?xml[\d\D]*\?\>/","",$s);
$s=str_replace("<Offer>","{",$s);
$s=str_replace("</Offer>","},",$s);
$s=str_replace("<Offers>","[",$s);
$s=str_replace("</Offers>","]",$s);
$s=str_replace("</Point>","},",$s);
$s=str_replace("</Address>","},",$s);
$s=str_replace("</Location>","},",$s);
$s=str_replace("</Merchant>","},",$s);
$s=str_replace("</Content>","},",$s);
$s=str_replace("</Media>","},",$s);
$s=str_replace("</ValidityPeriod>","},",$s);
$s=preg_replace("/<\/([\w|\s]*)>/","\",",$s);
$s=preg_replace("/\<([\w|\s]*)\>/","\"\\1\":\"",$s);

$s=str_replace("\":\"\"","\":{\"",$s);
$s=str_replace("}{","},{",$s);
$s=str_replace(",}","}",$s);
$s=str_replace("{,","{",$s);
$s=str_replace("[,","[",$s);
$s=str_replace(",]","]",$s);
$s=str_replace("},}","}}",$s);

$s=str_replace("}}}{","}}},{",$s);
$s=str_replace("/","\/",$s);
$s=str_replace("—","\\u2014",$s);
$s=str_replace("–","\\u2013",$s);	
$s=str_replace("–","\\u2013",$s);	
$s=utf8_encode($s);
$s=str_replace("—","\\u2014",$s);
$s=str_replace("–","\\u2013",$s);
$s=str_replace("ã","\\u00e3",$s);
$s=str_replace("Ã","\\u00c3",$s);
$s=str_replace("á","\\u00e1",$s);
$s=str_replace("Á","\\u00c1",$s);
$s=str_replace("à","\\u00e0",$s);
$s=str_replace("À","\\u00c0",$s);
$s=str_replace("â","\\u00e2",$s);
$s=str_replace("Â","\\u00c2",$s);
$s=str_replace("ç","\\u00e7",$s);
$s=str_replace("Ç","\\u00c7",$s);
$s=str_replace("é","\\u00e9",$s);
$s=str_replace("É","\\u00c9",$s);
$s=str_replace("è","\\u00e8",$s);
$s=str_replace("È","\\u00c8",$s);
$s=str_replace("ê","\\u00ea",$s);
$s=str_replace("Ê","\\u00ca",$s);
$s=str_replace("ï","\\u00ef",$s);
$s=str_replace("Ï","\\u00cf",$s);
$s=str_replace("í","\\u00ed",$s);
$s=str_replace("Í","\\u00cd",$s);
$s=str_replace("ó","\\u00f3",$s);
$s=str_replace("Ó","\\u00d3",$s);
$s=str_replace("õ","\\u00f5",$s);
$s=str_replace("Õ","\\u00d5",$s);
$s=str_replace("ô","\\u00f4",$s);
$s=str_replace("Ô","\\u00d4",$s);
$s=str_replace("ñ","\\u00f1",$s);
$s=str_replace("Ñ","\\u00d1",$s);
$s=str_replace("ú","\\u00fa",$s);
$s=str_replace("Ú","\\u00da",$s);
$s=str_replace("ª","\\u00aa",$s);
$s=str_replace("º","\\u00ba",$s);
$s=str_replace("<br>","\\n",$s);
$s=str_replace("<br/>","\\n",$s);
$s=str_replace("<br />","\\n",$s);
$s=str_replace("</p><p>","\\n\\n",$s);
$s=str_replace("<p>","",$s);
$s=str_replace("</p>","\\n",$s);
$s=str_replace("<","&lt;",$s);
$s=str_replace(">","&gt;",$s);
$s=str_replace("(","",$s);
$s=str_replace(")","",$s);
$s=str_replace("â","",$s);
$s=str_replace("€","",$s);
$s=str_replace("â€'","",$s);
$s=str_replace("®","&#174;",$s);
$s=str_replace("
"," ",$s);
$s=utf8_decode($s);
$s=str_replace("Â'","'",$s);
$s=str_replace("\n","",$s);
return $s;
}

	/* function for spelling suggestions */
	public function spellcheck( $string ) {
		$codeset_obj = new Codeset_combined();
		$items  = $codeset_obj->search_soundex($string);	
		return $items;
	}
	public function spellcheck_old( $string ) {
	$res = '';
	$res2 = '';
	$n='';
	   $words = explode(' ',$string);//explode
	   $misspelled = $return = array();
	   pspell_config_create("en",PSPELL_NORMAL);
	   $int = pspell_new('en');
	   $poss =array();
	   foreach ($words as $value) {
		   $check = preg_split('/[\W]+?/',$value);
		   if (( !empty($check) && is_array($check) && isset($check[1]) && $check[1] != '') and (strpos("'",$value) > 0) ) {$check[0] = $value;}
		   if ( !empty($check) && is_array($check) && isset($check[0]) && ($check[0] + 1 == 1) and (!pspell_check($int, $check[0]) )) {
		   		$poss[1]='';
			   //$res  .=  $value.' ' ;
			   $poss = pspell_suggest($int,$value);
			   $orig = metaphone($value);
			   foreach ($poss as $suggested)
			   {
					 $ranked[metaphone($suggested)] = $suggested;
			   }
			   if (isset($orig) && !empty($ranked) && is_array($ranked) && isset($ranked[$orig]) && $ranked[$orig] <> '') {$poss[1] = $ranked[$orig];}
			   if(isset($poss[1]))
			   {
			   	$res  .=  $poss[1];
				}
			   if(isset($poss[2]))
			   {
			   	$res2 .=  $poss[2];
				}
		   } else { 
		   $res .=''; 
		   $res2 .= '';
		   }
	   }
	
		if($res!=$res2)
		{
		   $n[1] = $res;
		   $n[2] = $res2;
		}
	   return $n;
	}

	    /**
     * Performs a Sphinx search for articles,CMS and CPT assistant on code detail pages
     */
    public function articlesearch($parent,$q='',$searchtype='tabadvance',$page=1,$search_and='',$priority='') {
		$sphinx_conf['sphinx_host'] = $this->CI->config->item('sphinx_server');
		$sphinx_conf['sphinx_port'] = $this->CI->config->item('sphinx_server_port'); //this demo uses the SphinxAPI interface
		#can use 'excerpt' to highlight using the query, or 'asis' to show description as is.
		$sphinx_conf['body'] = $this->CI->config->item('sphinx_body');
		#the link for the title (only $id) placeholder supported
		//$sphinx_conf['link_format'] = "";
		#Change this to FALSE on a live site!
		$sphinx_conf['debug'] = $this->CI->config->item('sphinx_debug');
		#How many results per page
		$sphinx_conf['page_size'] = 5;//$this->CI->config->item('sphinx_page_size');
		#maximum number of results - should match sphinxes max_matches. default 1000
		$sphinx_conf['max_matches'] = $this->CI->config->item('sphinx_max_matches');
		$resultCount = 0;
		$mode = SPH_MATCH_ANY;
		$result_string = '';
		$spell = '';
			$cms_cats = array('20'=>'Transmittals',
				'21'=>'E&amp;M Guidelines',
				'22'=>'Claims Processing Manuals',
				'23'=>'Guides',
				'179'=>'Forms'
				);

		$articles_cats = array('165'=>'Anesthesia Coding Alert',
		'180'=>'Emergency Department Coding &amp; Reimbursement Alert',
		'181'=>'Family Practice Coding Alert',
		'182'=>'Gastroenterology Coding Alert',
		'183'=>'General Surgery Coding Alert',
		'186'=>'Neurology &amp; Pain Management Coding Alert',
		'187'=>'Neurosurgery Coding Alert',
		'188'=>'Ob-Gyn Coding Alert',
		'189'=>'Oncology &amp; Hematology Coding Alert',
		'190'=>'Ophthalmology Coding Alert',
		'191'=>'Optometry Coding &amp; Billing Alert',
		'192'=>'Orthopedic Coding Alert',
		'193'=>'Otolaryngology Coding Alert',
		'194'=>'Pathology/Lab Coding Alert',
		'195'=>'Part B Insider (Multispecialty) Coding Alert',
		'196'=>'Pediatric Coding Alert',
		'197'=>'Physical Medicine &amp; Rehab Coding Alert',
		'198'=>'Pulmonology Coding Alert',
		'199'=>'Radiology Coding Alert',
		'200'=>'Urology Coding Alert',
		'45991'=>'Internal Medicine Coding Alert',
		'45992'=>'Cardiology Coding Alert',
		'52728'=>'Podiatry Coding &amp; Billing Alert',
		'52729'=>'Dermatology Coding Alert',
		'57309'=>'ICD 10 Coding Alert',
		'57790'=>'Inpatient Facility Coding & Compliance Alert',
		'57791'=>'Outpatient Facility Coding Alert',
		'58172'=>'Psychiatry Coding &amp; Reimbursement Alert',
		'99909'=>'E/M Coding Alert',
		'184'=>'Practice Management Alert',
		'58290'=>'Home Care Week',
		'58291'=>'Health Information Compliance Alert',
		'58292'=>'Eli\'s Hospice Insider',
		'58293'=>'Home Health ICD-9 Alert',
		'58294'=>'Long-Term Care Survey Alert',
		'58295'=>'MDS Alert',
		'58296'=>'Medicare Compliance & Reimbursement',
		'58297'=>'OASIS Alert',
		'99371'=>'Behavioral Healthcare Alert',
					);
		//User specialty section
		$specialty_master_array= array('165'=>'29','45992'=>'30','52729'=>'31','56739'=>'52','180'=>'32','181'=>'33','182'=>'34','183'=>'35','56738'=>'54','45991'=>'36','186'=>'62','187'=>'38','188'=>'63','56740'=>'53','189'=>'39','190'=>'40','191'=>'41','192'=>'42','193'=>'43','194'=>'45','195'=>'44','196'=>'46','197'=>'47','52728'=>'48','58172'=>'69','198'=>'49','199'=>'50','200'=>'51','57790'=>'52','57309'=>'54','99909'=>'85','184'=>'37','58292'=>'70','58290'=>'71','58293'=>'72','58294'=>'73','58295'=>'74','58297'=>'75','58296'=>'76','58291'=>'77','99371'=>'84');
		if($search_and)
		{
  	    	$mode = SPH_MATCH_EXTENDED2;
		}
		if (!empty($q)) 
		{
			//produce a version for display
			$qo = $q;
			////if no suggession for spelling pass an  array with first node 'none' 
			if(!is_array($spell)) $spell = array(0=>'none');
			///////////////////////////////////////////////////////////////////////			
			
				$sphinx_conf['sphinx_index'] = "combined_index_docs";
			//setup paging...
			if (!empty($page)) {
				$currentPage = intval($page);
				if (empty($currentPage) || $currentPage < 1) {$currentPage = 1;}
				
				$currentOffset = ($currentPage -1)* $sphinx_conf['page_size'];
				
				if ($currentOffset > ($sphinx_conf['max_matches']-$sphinx_conf['page_size']) ) {
					die("Only the first {$sphinx_conf['max_matches']} results accessible");
				}
			} else {
				$currentPage = 1;
				$currentOffset = 0;
			}

			//Connect to sphinx, and run the query
			$cl = new SphinxClient();
			$cl->SetServer($sphinx_conf['sphinx_host'], $sphinx_conf['sphinx_port']);
						
			if(is_logged_in())
			{
				$sorting_exp = '';
				$slug_arr = array();
				if($parent==13 && has_specialty_access())
				{
					$user_id = '';
					$usersarticles = array();
					$user_data=$this->CI->session->userdata('user'); // User Session Data
					if(!empty($user_data['user_id'])) // If user ID is available
					{
						$user_id=$user_data['user_id'];	
						$usersarticles = get_user_article_access_data($user_id);
					}				
					if(is_numeric($q))
					{
						if($priority!='')
						{
							$cl->SetSortMode(SPH_SORT_EXPR, "@weight + IF(doc_type=".$priority.",1,0)*400");
						}elseif($q>=00100 && $q<=01999) //00100-01999  Anersthesia (ACA) 165
						{
							$cl->SetSortMode(SPH_SORT_EXPR, "@weight + IF(doc_type=165,1,0)*300");
						}
						elseif($q>=10021 && $q<=19499) //10021 - 19499  Dermatology (DER) 52729 Emergency Dept (ED)180  Podiatry (POD)52728
						{
							$cl->SetSortMode(SPH_SORT_EXPR, "@weight + IF(doc_type=52729,1,0)*300 + IF(doc_type=180,1,0)*200 + IF(doc_type=52728,1,0)*100");
						} 
						elseif($q>=20000 && $q<=29999) //20000-29999 Orthopedics (ORC)192 Part B Insider (PBI)195 Emergency Dept (ED)180
						{
							$cl->SetSortMode(SPH_SORT_EXPR, "@weight + IF(doc_type=192,1,0)*300 + IF(doc_type=195,1,0)*200 + IF(doc_type=180,1,0)*100");
						}
						elseif($q>=30000 && $q<=32999) // 30000-32999 ulmonology (PUC)198 Pediatric (PCA)196
						{
							$cl->SetSortMode(SPH_SORT_EXPR, "@weight + IF(doc_type=198,1,0)*300 + IF(doc_type=196,1,0)*200");
						}
						elseif($q>=33010 && $q<=37799) // 33010-37799 Cardiology (CCA)45992 Part B Insider (PBI)195 Pediatric (PCA)196
						{
							$cl->SetSortMode(SPH_SORT_EXPR, "@weight + IF(doc_type=45992,1,0)*300 + IF(doc_type=195,1,0)*200 + IF(doc_type=196,1,0)*100");
						}
						elseif($q>=38100 && $q<=39599) // 38100-39599 Oncology & Hematology (ONC)189 Cardiology (CCA)45992 General Surgery (GCA)183
						{
							$cl->SetSortMode(SPH_SORT_EXPR, "@weight + IF(doc_type=189,1,0)*300 + IF(doc_type=45992,1,0)*200 + IF(doc_type=183,1,0)*100");
						}
						elseif($q>=40490 && $q<=49999) // 40490-49999 Gastroenterology (GAC)182 General Surgery (GCA)183 Part B Insider (PBI)195
						{
							$cl->SetSortMode(SPH_SORT_EXPR, "@weight + IF(doc_type=182,1,0)*300 + IF(doc_type=183,1,0)*200 + IF(doc_type=195,1,0)*100");
						}
						elseif($q>=50010 && $q<=53899) // 50010-53899 Urology (UCA)200 Family Practice (FCA)181 General Surgery (GCA)183
						{
							$cl->SetSortMode(SPH_SORT_EXPR, "@weight + IF(doc_type=200,1,0)*300 + IF(doc_type=181,1,0)*200 + IF(doc_type=183,1,0)*100");
						}
						elseif($q>=54000 && $q<=55899) // 54000-55899 Urology (UCA)200 General Surgery (GCA)183
						{
							$cl->SetSortMode(SPH_SORT_EXPR, "@weight + IF(doc_type=200,1,0)*300 + IF(doc_type=183,1,0)*200");
						}
						elseif($q>=56405 && $q<=59899) // 56405-59899 Ob-Gyn (OCA)188
						{
							$cl->SetSortMode(SPH_SORT_EXPR, "@weight + IF(doc_type=188,1,0)*300");
						}
						elseif($q>=60000 && $q<=60699) // 60000-60699 General Surgery (GCA)183 Family Practice (FCA)181 Part B Insider (PBI)195
						{
							$cl->SetSortMode(SPH_SORT_EXPR, "@weight + IF(doc_type=183,1,0)*300 + IF(doc_type=181,1,0)*200 + IF(doc_type=195,1,0)*100");
						}
						elseif($q>=61000 && $q<=64999) // 61000-64999 Neurology & Pain Management (NCA)186 Neurosurgery (NEC)187
						{
							$cl->SetSortMode(SPH_SORT_EXPR, "@weight + IF(doc_type=186,1,0)*300 + IF(doc_type=187,1,0)*200");
						}
						elseif($q>=65091 && $q<=68899) // 65091-68899 Ophthalmology (OPC)190 Optometry (OPT)191
						{
							$cl->SetSortMode(SPH_SORT_EXPR, "@weight + IF(doc_type=190,1,0)*300 + IF(doc_type=191,1,0)*200");
						}
						elseif($q>=69000 && $q<=69979) // 69000-69979 Otolaryngology (OTC)193
						{
							$cl->SetSortMode(SPH_SORT_EXPR, "@weight + IF(doc_type=193,1,0)*300");
						}
						elseif($q>=70010 && $q<=79999) // 70010-79999 Radiology (RCA)199 Part B Insider (PBI)195
						{
							$cl->SetSortMode(SPH_SORT_EXPR, "@weight + IF(doc_type=199,1,0)*300 + IF(doc_type=195,1,0)*200");
						}
						elseif($q>=80047 && $q<=89398) // 80047-89398 Pathology/Lab (PAC)194
						{
							$cl->SetSortMode(SPH_SORT_EXPR, "@weight + IF(doc_type=194,1,0)*300");
						}
						elseif($q>=90201 && $q<=90800) //90201-99607 Emergency Dept (ED)180 Physical Medicine & Rehabilitation 197 Part B Insider (PBI)195
						{
							$cl->SetSortMode(SPH_SORT_EXPR, "@weight + IF(doc_type=180,1,0)*300 + IF(doc_type=197,1,0)*200 + IF(doc_type=195,1,0)*100");
						}
						elseif($q>=90801 && $q<=90899) //Psychiatry 58172
						{
							$cl->SetSortMode(SPH_SORT_EXPR, "@weight + IF(doc_type=58172,1,0)*300");
						}
						elseif($q>=90900 && $q<=99607) //90201-99607 Emergency Dept (ED)180 Physical Medicine & Rehabilitation 197 Part B Insider (PBI)195
						{
							$cl->SetSortMode(SPH_SORT_EXPR, "@weight + IF(doc_type=180,1,0)*300 + IF(doc_type=197,1,0)*200 + IF(doc_type=195,1,0)*100");
						}
						else
						{
							$cl->SetSortMode(SPH_SORT_EXTENDED, "@relevance DESC");
						}
					}
					/*****************************/
					$slug_arr = array();
					$parent_arr = array();
					$filter_string = "*, (year=2013 OR year=2012 OR year=2011  OR year=2010  OR year=2009 ) AND ( ";
					if($parent!='')
					{
						$parent_arr = explode(",",$parent);
						if(count($parent_arr)>0)
						{
							foreach($parent_arr as $p_id)
							{
								if(is_logged_in())
								{	
									if(is_array($usersarticles) && count($usersarticles))
									{
										foreach($usersarticles as $document_id)
										$filter_string .= " document_id=".$document_id." OR ";
									}
								}
							}
						}
					}
					foreach($specialty_master_array as $spl_doc_type=>$spl_master_id)
					{
						if(has_access($spl_master_id) || has_access(86))
						{
							$filter_string .= " doc_type=".$spl_doc_type." OR ";
						}
					}
					//echo $filter_string; 
					if($filter_string!='*, ')
					{
						$filter_string = trim($filter_string,' OR');
						$filter_string .= ')  AS mycond ';
						//echo $filter_string;die; 
						$cl->SetSelect( $filter_string );
						$cl->SetFilter( "mycond", array(1) );
						//echo $filter_string;
					}
					/*****************************/						
				}else
				{
					$slug_arr = explode(",",$parent);
					if(count($slug_arr)>0)
						$cl->SetFilter('parent_id',$slug_arr);				
				}
			}
			else
			{
				$slug_arr = array();
				if($parent!='')
				{
					$slug_arr = explode(",",$parent);
					if(count($slug_arr)>0)
						$cl->SetFilter('parent_id',$slug_arr);
				}
				$cl->SetFieldWeights(array('post_title' => 1000, 'short_content' => 500, 'post_content' => 100));		
				$cl->SetSortMode(SPH_SORT_EXTENDED, "parent_id ASC, year DESC, @relevance DESC");
			}
			
			$cl->SetMatchMode($mode);
			$cl->SetRankingMode ( SPH_RANK_SPH04 );
			$cl->SetLimits($currentOffset,$sphinx_conf['page_size']); //current page and number of results
			// Add filters
								
			$res = $cl->Query($q, $sphinx_conf['sphinx_index']);
			//print_r($res);die;
			$result_string = "";
			if (empty($res) || !isset($res["matches"])) {
				//echo "comming";die;
				$result_string .= "Query failed: -- please try again later.\n";
				if ($sphinx_conf['debug'] && $cl->GetLastError())
					$result_string .= "<br/>Error: ".$cl->GetLastError()."\n\n";
				return;
			} else {
			
				//We have results to display!
				if ($sphinx_conf['debug'] && $cl->GetLastWarning())
					$result_string .= "<br/>WARNING: ".$cl->GetLastWarning()."\n\n";
				$query_info = "Query '".htmlentities($qo)."' retrieved ".count($res['matches'])." of $res[total_found] matches in $res[time] sec.\n";
				
				$resultCount = $res['total_found'];
				$numberOfPages = ceil($res['total']/$sphinx_conf['page_size']);
				if($resultCount>200)
				{
					$resultCount =200;
					$numberOfPages = ceil($resultCount/$sphinx_conf['page_size']);
				}
			}
			$ids = '';
			$ids_array = array();

			if ( !empty($res) && isset($res["matches"]) && is_array($res["matches"])) {
				$items = $res["matches"];
			}
		/////Start processing for advanced search.
			$master_array = array();
			$msg = "";
			//$items = $rows;
			$totalResults = $resultCount;
			$startIndex = $page;
			
			$itemsPerPage = $this->CI->config->item('sphinx_page_size');
			if((int)$totalResults>=1000) $msg = 'The searched term returns large number of search results. Please add more search keywords to get specific results';
			
			
			///// totalResults - get total value returned by sphinx
			///// startindex - page page number as stated by sphinx
			///// itemsPerPage - number of items per page
			///// currentpage - current page number
			///// spelling - Spelling sugession 
			///// msg - any message to be sent along with data
			if($totalResults>0){
				$master_array = array(
							'totalResults'=>$totalResults,
							'startIndex'=>$startIndex,
							'itemsPerPage'=>$itemsPerPage,
							'currentpage'=>$page,
							'spelling'=>'',
							'result'=>$master_array,
							'msg'=>$msg,
							'countarray'=>$count_array);
			}else{

				$master_array = array(
							'totalResults'=>$totalResults,
							'startIndex'=>$startIndex,
							'itemsPerPage'=>$itemsPerPage,
							'currentpage'=>$page,
							'spelling'=>$spell,
							'result'=>$master_array,
							'msg'=>$msg,
							'countarray'=>$count_array);
			}
			
			///// traverse each item and copy to array
			//$counter = 0;
			foreach ($items as $id=>$match) {
				$item = $match['attrs'];
				$doc_type = $item['doc_type'];
				$controller="";
				$postfix_url = '';
				$link = '';
				$pdflink = '';
				$titles = '';
				$type = '';
				$pre_title ='';
				
				if(array_key_exists($doc_type,$cms_cats)) 
				{
					switch($item['doc_type'])
					{
						case 20: 
							//Extract tranmittal number from taxonomy
							preg_match('{(\d+)}', $item['taxonomy'], $m); 
							if(isset($m[1]))
							$pre_title = 'Transmittal No. '.$m[1].' '; 
							$type = 'Transmittal';								
							break;
						case 21: 
							$type = 'Evaluation Management';								
							break;
						case 22: 
							$type = 'Claim Processing Manual';								
							break;
						case 23: 
							$type = 'MLN Book';								
							break;
						case 179:
							$type = 'Form';								
							break;
					}
					$cat_slug = array('20'=>'transmittals',
									  '21'=>'evaluation-management',
									   '22'=>'claims-processing-manuals',
									   '23'=>'mln-specialty-book',
									   '179'=>'forms');
					$controller = 'exclusives/'.$cat_slug[$item['doc_type']].'/';
					$postfix_url = '';
					$titles = $this->mystriptag($item['title']);
					$link = current_base_url().$controller.$item['post_name'].$postfix_url;	
					$context = $item['name'];
				}elseif(array_key_exists($doc_type,$articles_cats))
				{
					$speciality_url = get_specialty_url_by_slug($item['slug']);
					//$controller = 'coding-newsletters/my-'.$speciality_url.'/';
					$controller = $speciality_url.'/';
					$postfix_url = '-article';
					$titles = $this->mystriptag($item['title']);
					$context = $CatFullName[$item['slug']]." - ".$item['pdf_name'];
					$link = current_base_url().$controller.$item['post_name'].$postfix_url;
					$type = 'Article';								
				}elseif($doc_type=='10001')
				{
					$controller = 'cpt_assistant/cpt_assistant_details/';
					$titles = $item['title'];//preg_replace("/[^a-zA-Z0-9\s]/", "", str_ireplace('CPT', 'CPT&reg;',$item['title']));
					if(!empty($item['pdf_year']))
					{
						$context = "CPT&reg; Assistant ".$item['pdf_name'];
					}else
					{
						$context = "CPT&reg; Assistant ".substr($item['post_date'],0,4).";";
					}
					$link = '';
					if(has_access(17))
					{
						$link = current_base_url().$controller.($id-200000000);							
					}
				}else
				{
					continue;
				}
				///// populate page title and links for advanced search
				$master_array['result'][] = array(
								'type'=>$type,
								'title'=>str_replace(array('\\','"'),array('\\\\','\"'),$titles),
								'link'=>$link,'linkpdf'=>$pdflink,'context'=>str_replace(array('\\','"'),array('\\\\','\"'),$context),
								);
					
			}
			///// convert the master array to json string
			$output = $this->array_to_json_string_advance($master_array);
			return $output;
		}
	}
	
	
    /**
     * Performs a Sphinx search for hcpcs_clinic_search
     */
    public function hcpcs_clinic_search($q='',$page=1,$sort_order='',$filter='',$limit='',$start,$end) {
		$sphinx_conf['sphinx_host'] = $this->CI->config->item('sphinx_server');
		$sphinx_conf['sphinx_port'] = $this->CI->config->item('sphinx_server_port'); //this demo uses the SphinxAPI interface
		#can use 'excerpt' to highlight using the query, or 'asis' to show description as is.
		$sphinx_conf['body'] = $this->CI->config->item('sphinx_body');
		#the link for the title (only $id) placeholder supported
		#Change this to FALSE on a live site!
		$sphinx_conf['debug'] = $this->CI->config->item('sphinx_debug');
		#How many results per page
		$sphinx_conf['page_size'] = $this->CI->config->item('sphinx_page_size');
		#maximum number of results - should match sphinxes max_matches. default 1000
		$sphinx_conf['max_matches'] = $this->CI->config->item('sphinx_max_matches');
		$resultCount = 0;
		$resultCount_without_filter = 0;
		$result_string = '';
		$spell = '';
  	    $mode = SPH_MATCH_EXTENDED2;
		if (!empty($q)) 
		{
			//produce a version for display
			$qo = $q;
			
			//// spelling suggestions
			$spell =  $this->spellcheck($q);
			////if no suggession for spelling pass an  array with first node 'none' 
			if(!is_array($spell)) $spell = array(0=>'none');
			///////////////////////////////////////////////////////////////////////			
				$sphinx_conf['sphinx_index'] = "combined_index_docs";
			//setup paging...
			if (!empty($page) || $limit=='') {
				$currentPage = intval($page);
				if (empty($currentPage) || $currentPage < 1) {$currentPage = 1;}
				
				$currentOffset = ($currentPage -1)* $sphinx_conf['page_size'];
				
				if ($currentOffset > ($sphinx_conf['max_matches']-$sphinx_conf['page_size']) ) {
					die("Only the first {$sphinx_conf['max_matches']} results accessible");
				}
			} else {
				$currentPage = 1;
				$currentOffset = 0;
			}
					//Connect to sphinx, and run the query
					$cl = new SphinxClient();
					$cl->SetFilter('doc_type',array(10002));
					$cl->SetServer($sphinx_conf['sphinx_host'], $sphinx_conf['sphinx_port']);
					//if($sort_order=='date')
					//{
						//$cl->SetSortMode ( SPH_SORT_ATTR_DESC, "date_u" );
						$cl->SetSortMode ( SPH_SORT_ATTR_DESC, "pdf_year" );
					//}
					$cl->SetMatchMode($mode);
					$cl->SetRankingMode ( SPH_RANK_SPH04 );
					$res_without_filter = $cl->Query($q, $sphinx_conf['sphinx_index']);
					if($limit==1)
						$cl->SetLimits($currentOffset,1); //current page and number of results
					else
						$cl->SetLimits($currentOffset,$sphinx_conf['page_size']); //current page and number of results
					// Add filters
					$startTime = mktime();     
					$endTime = mktime();    

					switch($filter)
					{
						case '0':
							
						break;
						case '1':
							$startTime = mktime() - 30*3600*24;
							$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
						break;
						case '3':
							$startTime = mktime() - 3*30*3600*24;
							$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
						break;
						case '12':
							$startTime = mktime() - 12*30*3600*24;
							$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
						break;
						case '36':
							$startTime = mktime() - 36*30*3600*24;
							$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
						break;
						case '60':
							$startTime = mktime() - 60*30*3600*24;
							$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
						break;
						case 'range':
							$startTime = strtotime($start);
							$endTime = strtotime($end);     
							$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
							//echo strtotime($start);
							//echo "##########";
							//echo strtotime($end);die;							
						break;						
					}
					$res = $cl->Query($q, $sphinx_conf['sphinx_index']);
					
					
					//print_r($res);die;
					$result_string = "";
					if (empty($res_without_filter) || !isset($res_without_filter["matches"])) {

					} else {
						$resultCount_without_filter = $res_without_filter['total_found'];
					}					
					if (empty($res) || !isset($res["matches"])) {
						//echo "comming";die;
						/*
						$result_string .= "Query failed: -- please try again later.\n";
						if ($sphinx_conf['debug'] && $cl->GetLastError())
							$result_string .= "<br/>Error: ".$cl->GetLastError()."\n\n";
						return;
						*/
					} else {
						$resultCount = $res['total_found'];
						$numberOfPages = ceil($res['total']/$sphinx_conf['page_size']);
					}
					if ( !empty($res) && isset($res["matches"]) && is_array($res["matches"])) {
						//Build a list of IDs for use in the mysql Query and looping though the results
						$items = $res["matches"];
					}/* else {
						return;
					}*/
					
					$master_array = array();
					$msg = "";
					if($limit==1)
						$totalResults = 1;
					else
						$totalResults = $resultCount;
						
					$startIndex = $page;
					
					$itemsPerPage = $this->CI->config->item('sphinx_page_size');
					if((int)$totalResults>=1000) $msg = 'The searched term returns large number of search results. Please add more search keywords to get specific results';
					
					
					///// totalResults - get total value returned by sphinx
					///// startindex - page page number as stated by sphinx
					///// itemsPerPage - number of items per page
					///// currentpage - current page number
					///// spelling - Spelling sugession 
					///// msg - any message to be sent along with data
					if($totalResults>0){
						$master_array = array(
									'totalResults'=>($totalResults>1000?1000:$totalResults),
									'startIndex'=>$startIndex,
									'itemsPerPage'=>$itemsPerPage,
									'currentpage'=>$page,
									'result'=>$master_array,
									'msg'=>$msg);
					}else{

						$master_array = array(
									'totalResults'=>($totalResults>1000?1000:$totalResults),
									'startIndex'=>$startIndex,
									'itemsPerPage'=>$itemsPerPage,
									'currentpage'=>$page,
									'result'=>$master_array,
									'msg'=>$msg);
					}
					
					///// traverse each item and copy to array
					//$counter = 0;
					//print_r($items);die;
					if(!empty($items))
					foreach ($items as $id=>$match) {
						$item = $match['attrs'];
						//$counter++;
						//$serial_number = ($page - 1)*$itemsPerPage + $counter;

						$controller="";
						$link = '';
						$context = '';
						$type = '';
						
						$speciality_url = get_specialty_url_by_slug($item['slug']);
						$controller = 'hcpcs_coding_clinic/codingclinic_hcpcs_details/';
						$titles = preg_replace("/[^a-zA-Z0-9\s]/", "", str_ireplace('CPT', 'CPT&reg;',$item['title']));
						if(!empty($item['pdf_name']))
						{
							$context = "AHA HCPCS Coding Clinic ".$item['pdf_name'];
						}else
						{
							$context = "AHA HCPCS Coding Clinic ".substr($item['publish_date'],0,4).";";
						}
						$link = current_base_url().$controller.($id-300000000);;							
							
						$master_array['result'][] = array(
										'type'=>$type,
										'title'=>str_replace(array('\\','"'),array('\\\\','\"'),$titles),
										'link'=>$link,'context'=>str_replace(array('\\','"'),array('\\\\','\"'),$context)
										
										);
					}
					$master_array['resultwithoutfilter']=$resultCount_without_filter;
					$output = $this->array_to_json_string_advance($master_array);
					return $output;
		}
	}	
	
    /**
     * Performs a Sphinx search for icd_clinic_search
     */
    public function icd_clinic_search($q='',$page=1,$sort_order='',$filter='',$limit='',$start,$end) {
		$sphinx_conf['sphinx_host'] = $this->CI->config->item('sphinx_server');
		$sphinx_conf['sphinx_port'] = $this->CI->config->item('sphinx_server_port'); //this demo uses the SphinxAPI interface
		#can use 'excerpt' to highlight using the query, or 'asis' to show description as is.
		$sphinx_conf['body'] = $this->CI->config->item('sphinx_body');
		#the link for the title (only $id) placeholder supported
		#Change this to FALSE on a live site!
		$sphinx_conf['debug'] = $this->CI->config->item('sphinx_debug');
		#How many results per page
		$sphinx_conf['page_size'] = $this->CI->config->item('sphinx_page_size');
		#maximum number of results - should match sphinxes max_matches. default 1000
		$sphinx_conf['max_matches'] = $this->CI->config->item('sphinx_max_matches');
		$resultCount = 0;
		$resultCount_without_filter = 0;
		$result_string = '';
		$spell = '';
  	    $mode = SPH_MATCH_EXTENDED2;
		if (!empty($q)) 
		{
			//produce a version for display
			$qo = $q;
			
			//// spelling suggestions
			$spell =  $this->spellcheck($q);
			////if no suggession for spelling pass an  array with first node 'none' 
			if(!is_array($spell)) $spell = array(0=>'none');
			///////////////////////////////////////////////////////////////////////			
				$sphinx_conf['sphinx_index'] = "combined_index_docs";
			//setup paging...
			if (!empty($page) || $limit=='') {
				$currentPage = intval($page);
				if (empty($currentPage) || $currentPage < 1) {$currentPage = 1;}
				
				$currentOffset = ($currentPage -1)* $sphinx_conf['page_size'];
				
				if ($currentOffset > ($sphinx_conf['max_matches']-$sphinx_conf['page_size']) ) {
					die("Only the first {$sphinx_conf['max_matches']} results accessible");
				}
			} else {
				$currentPage = 1;
				$currentOffset = 0;
			}
					//Connect to sphinx, and run the query
					$cl = new SphinxClient();
					$cl->SetFilter('doc_type',array(10003));
					$cl->SetServer($sphinx_conf['sphinx_host'], $sphinx_conf['sphinx_port']);
					//if($sort_order=='date')
					//{
						//$cl->SetSortMode ( SPH_SORT_ATTR_DESC, "date_u" );
						$cl->SetSortMode ( SPH_SORT_ATTR_DESC, "pdf_year" );
					//}
					$cl->SetMatchMode($mode);
					$cl->SetRankingMode ( SPH_RANK_SPH04 );
					$res_without_filter = $cl->Query($q, $sphinx_conf['sphinx_index']);
					if($limit==1)
						$cl->SetLimits($currentOffset,1); //current page and number of results
					else
						$cl->SetLimits($currentOffset,$sphinx_conf['page_size']); //current page and number of results
					// Add filters
					$startTime = mktime();     
					$endTime = mktime();    

					switch($filter)
					{
						case '0':
							
						break;
						case '1':
							$startTime = mktime() - 30*3600*24;
							$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
						break;
						case '3':
							$startTime = mktime() - 3*30*3600*24;
							$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
						break;
						case '12':
							$startTime = mktime() - 12*30*3600*24;
							$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
						break;
						case '36':
							$startTime = mktime() - 36*30*3600*24;
							$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
						break;
						case '60':
							$startTime = mktime() - 60*30*3600*24;
							$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
						break;
						case 'range':
							$startTime = strtotime($start);
							$endTime = strtotime($end);     
							$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
							//echo strtotime($start);
							//echo "##########";
							//echo strtotime($end);die;							
						break;						
					}
					$res = $cl->Query($q, $sphinx_conf['sphinx_index']);
					
					
					//print_r($res);die;
					$result_string = "";
					if (empty($res_without_filter) || !isset($res_without_filter["matches"])) {

					} else {
						$resultCount_without_filter = $res_without_filter['total_found'];
					}					
					if (empty($res) || !isset($res["matches"])) {
						//echo "comming";die;
						/*
						$result_string .= "Query failed: -- please try again later.\n";
						if ($sphinx_conf['debug'] && $cl->GetLastError())
							$result_string .= "<br/>Error: ".$cl->GetLastError()."\n\n";
						return;
						*/
					} else {
						$resultCount = $res['total_found'];
						$numberOfPages = ceil($res['total']/$sphinx_conf['page_size']);
					}
					if ( !empty($res) && isset($res["matches"]) && is_array($res["matches"])) {
						//Build a list of IDs for use in the mysql Query and looping though the results
						$items = $res["matches"];
					}/* else {
						return;
					}*/
					
					$master_array = array();
					$msg = "";
					if($limit==1)
						$totalResults = 1;
					else
						$totalResults = $resultCount;
						
					$startIndex = $page;
					
					$itemsPerPage = $this->CI->config->item('sphinx_page_size');
					if((int)$totalResults>=1000) $msg = 'The searched term returns large number of search results. Please add more search keywords to get specific results';
					
					
					///// totalResults - get total value returned by sphinx
					///// startindex - page page number as stated by sphinx
					///// itemsPerPage - number of items per page
					///// currentpage - current page number
					///// spelling - Spelling sugession 
					///// msg - any message to be sent along with data
					if($totalResults>0){
						$master_array = array(
									'totalResults'=>($totalResults>1000?1000:$totalResults),
									'startIndex'=>$startIndex,
									'itemsPerPage'=>$itemsPerPage,
									'currentpage'=>$page,
									'result'=>$master_array,
									'msg'=>$msg);
					}else{

						$master_array = array(
									'totalResults'=>($totalResults>1000?1000:$totalResults),
									'startIndex'=>$startIndex,
									'itemsPerPage'=>$itemsPerPage,
									'currentpage'=>$page,
									'result'=>$master_array,
									'msg'=>$msg);
					}
					
					///// traverse each item and copy to array
					//$counter = 0;
					//print_r($items);die;
					if(!empty($items))
					foreach ($items as $id=>$match) {
						$item = $match['attrs'];
						//$counter++;
						//$serial_number = ($page - 1)*$itemsPerPage + $counter;

						$controller="";
						$link = '';
						$context = '';
						$type = '';
						
						$speciality_url = get_specialty_url_by_slug($item['slug']);
						$controller = 'icd_coding_clinic/codingclinic_icd_details/';
						$titles = preg_replace("/[^a-zA-Z0-9\s]/", "", str_ireplace('CPT', 'CPT&reg;',$item['title']));
						if(!empty($item['pdf_name']))
						{
							$context = "AHA ICD-9-CM Coding Clinic ".$item['pdf_name'];
						}else
						{
							$context = "AHA ICD-9-CM Coding Clinic ".substr($item['publish_date'],0,4).";";
						}
						$link = current_base_url().$controller.($id-400000000);;							
							
						$master_array['result'][] = array(
										'type'=>$type,
										'title'=>str_replace(array('\\','"'),array('\\\\','\"'),$titles),
										'link'=>$link,'context'=>str_replace(array('\\','"'),array('\\\\','\"'),$context)
										
										);
					}
					$master_array['resultwithoutfilter']=$resultCount_without_filter;
					$output = $this->array_to_json_string_advance($master_array);
					return $output;
		}
	}	


    /**
     * Performs a Sphinx search for cpt_assistant
     */
    public function cpt_assistant_search($q='',$page=1,$sort_order='',$filter='',$limit='',$start,$end) {
		$sphinx_conf['sphinx_host'] = $this->CI->config->item('sphinx_server');
		$sphinx_conf['sphinx_port'] = $this->CI->config->item('sphinx_server_port'); //this demo uses the SphinxAPI interface
		#can use 'excerpt' to highlight using the query, or 'asis' to show description as is.
		$sphinx_conf['body'] = $this->CI->config->item('sphinx_body');
		#the link for the title (only $id) placeholder supported
		#Change this to FALSE on a live site!
		$sphinx_conf['debug'] = $this->CI->config->item('sphinx_debug');
		#How many results per page
		$sphinx_conf['page_size'] = $this->CI->config->item('sphinx_page_size');
		#maximum number of results - should match sphinxes max_matches. default 1000
		$sphinx_conf['max_matches'] = $this->CI->config->item('sphinx_max_matches');
		$resultCount = 0;
		$resultCount_without_filter = 0;
		$result_string = '';
		$spell = '';
  	    $mode = SPH_MATCH_EXTENDED2;
		if (!empty($q)) 
		{
			//produce a version for display
			$qo = $q;
			
			//// spelling suggestions
			$spell =  $this->spellcheck($q);
			////if no suggession for spelling pass an  array with first node 'none' 
			if(!is_array($spell)) $spell = array(0=>'none');
			///////////////////////////////////////////////////////////////////////			
				$sphinx_conf['sphinx_index'] = "combined_index_docs";
			//setup paging...
			if (!empty($page) || $limit=='') {
				$currentPage = intval($page);
				if (empty($currentPage) || $currentPage < 1) {$currentPage = 1;}
				
				$currentOffset = ($currentPage -1)* $sphinx_conf['page_size'];
				
				if ($currentOffset > ($sphinx_conf['max_matches']-$sphinx_conf['page_size']) ) {
					die("Only the first {$sphinx_conf['max_matches']} results accessible");
				}
			} else {
				$currentPage = 1;
				$currentOffset = 0;
			}
					//Connect to sphinx, and run the query
					$cl = new SphinxClient();
					$cl->SetFilter('doc_type',array(10001));
					$cl->SetServer($sphinx_conf['sphinx_host'], $sphinx_conf['sphinx_port']);
					//if($sort_order=='date')
					//{
						//$cl->SetSortMode ( SPH_SORT_ATTR_DESC, "date_u" );
						$cl->SetSortMode ( SPH_SORT_ATTR_DESC, "pdf_year" );
					//}
					$cl->SetMatchMode($mode);
					$cl->SetRankingMode ( SPH_RANK_SPH04 );
					$res_without_filter = $cl->Query($q, $sphinx_conf['sphinx_index']);
					if($limit==1)
						$cl->SetLimits($currentOffset,1); //current page and number of results
					else
						$cl->SetLimits($currentOffset,$sphinx_conf['page_size']); //current page and number of results
					// Add filters
					$startTime = mktime();     
					$endTime = mktime();    

					switch($filter)
					{
						case '0':
							
						break;
						case '1':
							$startTime = mktime() - 30*3600*24;
							$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
						break;
						case '3':
							$startTime = mktime() - 3*30*3600*24;
							$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
						break;
						case '12':
							$startTime = mktime() - 12*30*3600*24;
							$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
						break;
						case '36':
							$startTime = mktime() - 36*30*3600*24;
							$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
						break;
						case '60':
							$startTime = mktime() - 60*30*3600*24;
							$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
						break;
						case 'range':
							$startTime = strtotime($start);
							$endTime = strtotime($end);     
							$cl->SetFilterRange( "date_u", $startTime, $endTime, false );
							//echo strtotime($start);
							//echo "##########";
							//echo strtotime($end);die;							
						break;						
					}
					$res = $cl->Query($q, $sphinx_conf['sphinx_index']);
					
					
					//print_r($res);die;
					$result_string = "";
					if (empty($res_without_filter) || !isset($res_without_filter["matches"])) {

					} else {
						$resultCount_without_filter = $res_without_filter['total_found'];
					}					
					if (empty($res) || !isset($res["matches"])) {
						//echo "comming";die;
						/*
						$result_string .= "Query failed: -- please try again later.\n";
						if ($sphinx_conf['debug'] && $cl->GetLastError())
							$result_string .= "<br/>Error: ".$cl->GetLastError()."\n\n";
						return;
						*/
					} else {
						$resultCount = $res['total_found'];
						$numberOfPages = ceil($res['total']/$sphinx_conf['page_size']);
					}
					if ( !empty($res) && isset($res["matches"]) && is_array($res["matches"])) {
						//Build a list of IDs for use in the mysql Query and looping though the results
						$items = $res["matches"];
					}/* else {
						return;
					}*/
					
					$master_array = array();
					$msg = "";
					if($limit==1)
						$totalResults = 1;
					else
						$totalResults = $resultCount;
						
					$startIndex = $page;
					
					$itemsPerPage = $this->CI->config->item('sphinx_page_size');
					if((int)$totalResults>=1000) $msg = 'The searched term returns large number of search results. Please add more search keywords to get specific results';
					
					
					///// totalResults - get total value returned by sphinx
					///// startindex - page page number as stated by sphinx
					///// itemsPerPage - number of items per page
					///// currentpage - current page number
					///// spelling - Spelling sugession 
					///// msg - any message to be sent along with data
					if($totalResults>0){
						$master_array = array(
									'totalResults'=>($totalResults>1000?1000:$totalResults),
									'startIndex'=>$startIndex,
									'itemsPerPage'=>$itemsPerPage,
									'currentpage'=>$page,
									'result'=>$master_array,
									'msg'=>$msg);
					}else{

						$master_array = array(
									'totalResults'=>($totalResults>1000?1000:$totalResults),
									'startIndex'=>$startIndex,
									'itemsPerPage'=>$itemsPerPage,
									'currentpage'=>$page,
									'result'=>$master_array,
									'msg'=>$msg);
					}
					
					///// traverse each item and copy to array
					//$counter = 0;
					//print_r($items);die;
					if(!empty($items))
					foreach ($items as $id=>$match) {
						$item = $match['attrs'];
						//$counter++;
						//$serial_number = ($page - 1)*$itemsPerPage + $counter;

						$controller="";
						$link = '';
						$context = '';
						$type = '';
						
						$speciality_url = get_specialty_url_by_slug($item['slug']);
						$controller = 'cpt_assistant/cpt_assistant_details/';
						$titles = preg_replace("/[^a-zA-Z0-9\s]/", "", str_ireplace('CPT', 'CPT&reg;',$item['title']));
						/*if(!empty($item['publish_year']))
						{
							$context = "CPT&reg; Assistant ".$item['publish_year']."; Volume ".$item['publish_volume'].", Issue ".$item['publish_issue'];
						}else
						{
							$context = "CPT&reg; Assistant ".substr($item['publish_date'],0,4).";";
						}*/
						if(!empty($item['pdf_year']))
						{
							$context = "CPT&reg; Assistant ".$item['pdf_name'];
						}else
						{
							$context = "CPT&reg; Assistant ".substr($item['post_date'],0,4).";";
						}
						
						$link = current_base_url().$controller.($id-200000000);;							
							
						$master_array['result'][] = array(
										'type'=>$type,
										'title'=>str_replace(array('\\','"'),array('\\\\','\"'),$titles),
										'link'=>$link,'context'=>str_replace(array('\\','"'),array('\\\\','\"'),$context)
										
										);
					}
					$master_array['resultwithoutfilter']=$resultCount_without_filter;
					$output = $this->array_to_json_string_advance($master_array);
					return $output;
		}
	}	

	
}