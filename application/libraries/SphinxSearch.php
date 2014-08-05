<?php
//ini_set('display_errors', 0);
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Description of SphinxSearch
 *
 * @author Pawan
 */
class SphinxSearch {

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
		$this->CI->load->model('posts_combined'); 
		$this->CI->load->model('codeset_combined'); 
		$this->CI->load->model('categories'); 
		$this->CI->load->helper('specialty_corner_helper'); 
    }


    /**
     * Performs a Sphinx search for cpt assistant
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
				$sphinx_conf['sphinx_index'] = "combined_cpt_assistant";
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
					$cl->SetServer($sphinx_conf['sphinx_host'], $sphinx_conf['sphinx_port']);
					if($sort_order=='date')
					{
						$cl->SetSortMode ( SPH_SORT_ATTR_DESC, "date_u" );
					}
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
					foreach ($items as $cptfileid=>$match) {
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
						if(!empty($item['publish_year']))
						{
							$context = "CPT&reg; Assistant ".$item['publish_year']."; Volume ".$item['publish_volume'].", Issue ".$item['publish_issue'];
						}else
						{
							$context = "CPT&reg; Assistant ".substr($item['publish_date'],0,4).";";
						}
						$link = current_base_url().$controller.$cptfileid;							
							
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
     * Performs a Sphinx search for articles and CMS on code detail pages
     */
    public function articlesearch($filter,$q='',$searchtype='tabadvance',$page=1,$search_and='',$priority='') {
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
		$mode = SPH_MATCH_ANY;
		$result_string = '';
		$spell = '';
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
			
			$slug_arr = array();
			if($filter!='')
			{
				if($priority!='' && $priority=='57790'){
					$cl->SetFilter('doc_type',array('57790'));
				}else
				{
					$slug_arr = explode(",",$filter);
					if(count($slug_arr)>0)
						$cl->SetFilter('doc_type',$slug_arr);
				}
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
				if(!in_array(20,$slug_arr) && !in_array(21,$slug_arr) && !in_array(22,$slug_arr) && !in_array(23,$slug_arr) && !in_array(179,$slug_arr))//not applied on cms search on codedetail page
				{
					$cl->SetSelect("*, IF(parent_id=13 OR (parent_id=15 AND year=2012), 1, 0) as validfilter");
					$cl->SetFilter('validfilter', array(1));
				}
			}
			else
			{
				if(is_logged_in())
				{
					//User specialty section
					$specialty_master_array= array('165'=>'29','45992'=>'30','52729'=>'31','56739'=>'52','180'=>'32','181'=>'33','182'=>'34','183'=>'35','56738'=>'54','45991'=>'36','184'=>'37','186'=>'62','187'=>'38','188'=>'63','56740'=>'53','189'=>'39','190'=>'40','191'=>'41','192'=>'42','193'=>'43','194'=>'45','195'=>'44','196'=>'46','197'=>'47','52728'=>'48','58172'=>'69','198'=>'49','199'=>'50','200'=>'51','58292'=>'70','58290'=>'71','58293'=>'72','58294'=>'73','58295'=>'74','58297'=>'75','58296'=>'76','58291'=>'77','57790'=>'52','57309'=>'54');							//$userspeciality = "180";
					$sorting_exp = '';
					foreach($specialty_master_array as $specialty=>$masterid)
					{
						if(has_access($masterid))
						{
							$sorting_exp .=  ' IF(doc_type='.$specialty.' AND year=2012,5,0) + IF(doc_type='.$specialty.' AND year=2011,4,0) + IF(doc_type='.$specialty.' AND year=2010,3,0) + IF(doc_type='.$specialty.' AND year=2009,2,0) + IF(doc_type='.$specialty.' AND year=2008,1,0) +';

						}
					}
					if($sorting_exp!='')
					{
						$sorting_exp  = trim($sorting_exp,' +')." as userspeciality ";
					}	
					$cl->SetSelect("*, ".$sorting_exp);
					$cl->SetFieldWeights(array('post_title' => 1000, 'short_content' => 500, 'post_content' => 100));		
					$cl->SetSortMode(SPH_SORT_EXTENDED, "userspeciality DESC, @relevance DESC, parent_id ASC");
					//User specialty section
				}
				else
				{
					$cl->SetFieldWeights(array('post_title' => 1000, 'short_content' => 500, 'post_content' => 100));		
					$cl->SetSortMode(SPH_SORT_EXTENDED, "parent_id ASC, year DESC, @relevance DESC");
				}
			}
			
			$cl->SetMatchMode($mode);
			$cl->SetRankingMode ( SPH_RANK_SPH04 );
			$cl->SetLimits($currentOffset,$sphinx_conf['page_size']); //current page and number of results
			// Add filters
								
			$res = $cl->Query($q, $sphinx_conf['sphinx_index']);
			//print_r($res);die;
			$result_string = "";
					if (empty($res) || !isset($res["matches"])) {
					} else {
						$resultCount = $res['total_found'];
						$numberOfPages = ceil($res['total']/$sphinx_conf['page_size']);
					}
					if ( !empty($res) && isset($res["matches"]) && is_array($res["matches"])) {
						$items = $res["matches"];
					}
			/*if (empty($res) || !isset($res["matches"])) {
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
				//Build a list of IDs for use in the mysql Query and looping though the results
				
				$ids_array = array_keys($res["matches"]);
				foreach($ids_array as $match)
				{	
					$ids .= $match.",";		
				}	
				$ids = trim($ids,",");
				
			} else {
				return;
			}
	
			$posts_obj = new Posts_combined();
			$rows  = $posts_obj->search($ids);
			*/
			$cms_cats = array('20'=>'Transmittals',
				'21'=>'E&amp;M Guidelines',
				'22'=>'Claims Processing Manuals',
				'23'=>'Guides',
				'179'=>'Forms'
				);
				//'Medical Office Billing &amp; Collections Coding Alert'
			$articles_cats = array('165'=>'Anesthesia Coding Alert',
				'180'=>'Emergency Department Coding &amp; Reimbursement Alert',
				'181'=>'Family Practice Coding Alert',
				'182'=>'Gastroenterology Coding Alert',
				'183'=>'General Surgery Coding Alert',
				'184'=>'Practice Management Alert',
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
				'99371'=>'Behavioral Healthcare Alert'

							);

			$master_array = array();
			$msg = "";
			//$items = $rows;
			$totalResults = $resultCount;
			$startIndex = $page;
			
			$itemsPerPage = 5;//$this->CI->config->item('sphinx_page_size');
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
							'spelling'=>'',
							'result'=>$master_array,
							'msg'=>$msg);
			}else{

				$master_array = array(
							'totalResults'=>($totalResults>1000?1000:$totalResults),
							'startIndex'=>$startIndex,
							'itemsPerPage'=>$itemsPerPage,
							'currentpage'=>$page,
							'spelling'=>$spell,
							'result'=>$master_array,
							'msg'=>$msg);
			}
			
			///// traverse each item and copy to array
			foreach ($items as $id=>$match) {
					$item = $match['attrs'];
					$doc_type = $item['doc_type'];
					$controller="";
					$postfix_url = '';
					$link = '';
					$titles = '';
					
					if(array_key_exists($doc_type,$cms_cats)) 
					{
						$cat_slug = array('20'=>'transmittals',
										  '21'=>'evaluation-management',
										   '22'=>'claims-processing-manuals',
										   '23'=>'mln-specialty-book');
						$controller = 'exclusives/'.$cat_slug[$item['doc_type']].'/';
						$postfix_url = '';
						$titles = $this->mystriptag($item['name']);
						$link = current_base_url().$controller.$item['post_name'].$postfix_url;							
					}elseif(array_key_exists($doc_type,$articles_cats))
					{
						$speciality_url = get_specialty_url_by_slug($item['slug']);
						$controller = 'coding-newsletters/my-'.$speciality_url.'/';
						$postfix_url = '-article';
						//$titles = $this->mystriptag($item['name'])." | ".$this->mystriptag($item['title']);
						$titles = preg_replace("/[^a-zA-Z0-9\s]/", "", $item['name'])." | ".preg_replace("/[^a-zA-Z0-9\s]/", "", $item['title']);
						$link = current_base_url().$controller.$item['post_name'].$postfix_url;							
					}
					
					$context = '';
					///// populate page title and links for advanced search
					$master_array['result'][] = array(
									'title'=>str_replace(array('\\','"'),array('\\\\','\"'),$titles),
									'link'=>$link,'context'=>str_replace(array('\\','"'),array('\\\\','\"'),$context)
									);
			}
			///// convert the master array to json string
			$output = $this->array_to_json_string_advance($master_array);
			//// Output the json string
			return $output;
		}
	}

	public function codesearch($filter='',$q='',$force_search_pref='')
	{	
		if($filter=='')
		{
			$filter = '7,8,9,201,202,203,204,205,206';
		}
		$filter_array = explode(",",$filter);
		$q = strtolower($q);
		$keyword_array = explode(' ',$q);
		$iscodeset = true;
		$resultCount = 0;
		$codesetcomb_obj = new Codeset_combined();
		$deletedcode  = $codesetcomb_obj->deleted_code($q);
		//// prepare url for deleted page
		$deletedlink['CPT'] = current_base_url()."code_lookup/deleted_cpt_codes/%s#%s";
		$deletedlink['ICD'] = current_base_url()."code_lookup/deleted_icd_codes/%s#%s";
		$deletedlink['HCPCS'] = current_base_url()."code_lookup/deleted_hcpcs_codes/%s#%s";
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
		$items  = $codeset_obj->search_only_codes($code_string,$filter);
		////////////////////////////////
		/*echo "<pre>";
		print_r($items);
		die;*/
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
		///////////////////////////////////////////
				/*$keyword = stripslashes(strtolower(str_replace(array('\'','-',' ',','),array('','-','+','+'),urldecode(trim($q))))); 
				$tmpkeyword_array = explode('+',$keyword);
				$tmpcount = count($tmpkeyword_array);
			
				$keyword = implode('+',$tmpkeyword_array);
				
				
				///// start** check if we can treat the entire passed keyword as codeset, useful for comma/space separated multiple code
				*/
				$iscodeset = true;
				//$keyword_array = explode('+',$keyword);
		///////////////////////////////////////////
		$siteurl = current_base_url();
		
		$morelinks['ICDVOL3'] = array();
		$morelinks['CPT-Modifier'] = array();
		$morelinks['HCPCS-Modifier'] = array();
		///// start** set codeset urls for redirection and links
		$morelinks['CPT']['detail'] = $siteurl."cpt-codes/%s";
		$morelinks['CPT']['listing'] = $siteurl."cpt-codes-range/%s/?code=%s#%s";
		$morelinks['CPT']['range'] = $siteurl."cpt-codes-range/%s/";
		
		$morelinks['HCPCS']['detail'] = $siteurl."hcpcs-codes/%s";
		$morelinks['HCPCS']['listing'] = $siteurl."hcpcs-codes-range/%s/?code=%s#%s";
		$morelinks['HCPCS']['range'] = $siteurl."hcpcs-codes-range/%s/";
		
		$morelinks['ICD']['detail'] = $siteurl."icd9-codes/%s";
		$morelinks['ICD']['listing'] = $siteurl."icd9-codes-range/%s/?code=%s#%s";
		$morelinks['ICD']['range'] = $siteurl."icd9-codes-range/%s/";
		
		$morelinks['ICDVOL3']['detail'] = $siteurl."icd9-codes-vol3/%s";
		$morelinks['ICDVOL3']['listing'] = $siteurl."icd9-codes-vol3-range/%s/?code=%s#%s";
		$morelinks['ICDVOL3']['range'] = $siteurl."icd9-codes-vol3-range/%s/";
		
		$morelinks['CPT-Modifier']['detail'] = $siteurl."cpt-codes/%s";
		$morelinks['CPT-Modifier']['listing'] = $siteurl."cpt-codes-range/%s/?modifier=%s#%s";
		$morelinks['CPT-Modifier']['range'] = $siteurl."cpt-codes-range/%s/";
	
		$morelinks['HCPCS-Modifier']['detail'] = $siteurl."hcpcs-codes/%s";
		$morelinks['HCPCS-Modifier']['listing'] = $siteurl."hcpcs-codes-range/%s/?modifier=%s#%s";
		$morelinks['HCPCS-Modifier']['range'] = $siteurl."hcpcs-codes-range/%s/";
		
		$morelinks['DRG']['detail'] = $siteurl."drg-codes/%s";				
		$morelinks['DRG']['listing'] = $siteurl."drg-codes-range/%s/?code=%s#%s";
		$morelinks['DRG']['range'] = $siteurl."drg-codes-range/%s/";

		$morelinks['APC']['detail'] = $siteurl."apc-codes/%s";		
		$morelinks['APC']['listing'] = $siteurl."apc-codes-range/%s/?code=%s#%s";
		$morelinks['APC']['range'] = $siteurl."apc-codes-range/%s/";

		$morelinks['ICD10']['detail'] = $siteurl."icd-10-codes/%s";		
		$morelinks['ICD10']['listing'] = $siteurl."icd-10-codes-range/%s/?code=%s#%s";
		$morelinks['ICD10']['range'] = $siteurl."icd-10-codes-range/%s/";

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
		}else
		{
			$uoption['CPT'] = $uoption['ICD'] = $uoption['HCPCS'] = $uoption['ICDVOL3'] = $uoption['CPT-Modifier'] = $uoption['HCPCS-Modifier'] = $uoption['HCPCS-Modifier'] = $uoption['DRG'] = $uoption['APC'] = $uoption['ICD10'] = $force_search_pref;
		}
		if(count($items)==1)
		{
			$single_item = reset($items);
			
			if(isset($uoption[$single_item['code_type']]) && $uoption[$single_item['code_type']]=='listing') {
				if(!$login) {
					$response = sprintf($morelinks[$single_item['code_type']][$uoption[$single_item['code_type']]],$single_item['range_id'],$single_item['code_title'],$single_item['code_title']);
				}
				else
				{
					$response = sprintf($morelinks[$single_item['code_type']][$uoption[$single_item['code_type']]],$single_item['range_id'],$single_item['code_title'],$single_item['code_title']);
				}
			}
			else
			{
				if(isset($uoption[$single_item['code_type']]))
				{
					if(!$login) {
						$response = sprintf($morelinks[$single_item['code_type']][$uoption[$single_item['code_type']]],$single_item['code_title']);
					}
					else
					{
						$response = sprintf($morelinks[$single_item['code_type']][$uoption[$single_item['code_type']]],$single_item['code_title']);
					}
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
		$codesdata = array();
		$matchcounter = 0;
		foreach ($items as $item) {
			$titles = $item['code_title'];			
			$code_name = $item['code_title'];			
			$code_type = $item['code_type'];			
			$code_range_id = $item['range_id'];
			$code_range = $item['range_title'];			
			$code_desc = preg_replace("/[^a-zA-Z0-9\s]/", "", $item['description']);//			
			$code_special_text = preg_replace("/[^a-zA-Z0-9\s]/", "", $item['special_text']);//$this->mystriptag($item['special_text']);			
			$code_range_desc = $item['range_description'];			
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
			if($code_type=='APC') {
				$code_range = 'APC';
				$code_range_desc = 'Codes';
			}
			if($code_type=='CPT-Modifier') {
				$code_range = 'CPT-Modifier';
				$code_range_desc = 'Codes';
			}
			if($code_type=='HCPCS-Modifier') {
				$code_range = 'HCPCS-Modifier';
				$code_range_desc = 'Codes';
			}
			if(!$login) {
				if($code_type=='DRG') {
					$code_range_desc = 'To see the official DRG section, subsection to <a href="'.current_base_url().'coding-solutions/drg-coder" >DRG Coder</a>';
					$code_desc = 'Read the DRG definition by subscribing to <a href="'.current_base_url().'coding-solutions/drg-coder" >DRG Coder</a> right now!';
				}
				else
				{
					$code_range_desc = '';
					$code_desc ='';
				}
			}

			///// Check search settings and create urls for redirection and links
			$link=$parent1_code_link=$parent2_code_link==$range_link='';
			if(isset($uoption[$code_type]) && $uoption[$code_type]=='listing') {
				$link = sprintf($morelinks[$code_type][$uoption[$code_type]],$code_range_id,$code_name,$code_name);
				$parent1_code_link = empty($parent1_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$code_range_id,$parent1_code,$parent1_code);
				$parent2_code_link = empty($parent2_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$code_range_id,$parent2_code,$parent2_code);
				$range_link = empty($code_range_id)?'':sprintf($morelinks[$code_type]['range'],$code_range_id);
			}
			else
			{
				if(isset($uoption[$code_type]))
				{
					$link = sprintf($morelinks[$code_type][$uoption[$code_type]],$code_name);
					$parent1_code_link = empty($parent1_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$parent1_code);
					$parent2_code_link = empty($parent2_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$parent2_code);
					$range_link = empty($code_range_id)?'':sprintf($morelinks[$code_type]['range'],$code_range_id);
				}
			}

			///// Check login status, CPT codes will only be displayed to users having CPT access
			$code_class = 'red-pink';
			$range_link_class = 'purple-pink';
			if(!$login) {										
				$leaf = '<a class="'.$code_class.'" href="javascript:void();" onclick="redir(\''.$link.'\');" title="Read the code description by subscribing to <a href=\''.current_base_url().'coding-tools/code-search\'>Coding Tools</a> or <a href=\''.current_base_url().'coding-solutions\'>Coding Solutions</a>" >'.$code_name.'</a>  '.$code_desc;
			}
			else {
				$leaf = '<a class="'.$code_class.'" href="javascript:void();" onclick="redir(\''.$link.'\');">'.$code_name.'</a>'.' - <a href="javascript:void();" onclick="redir(\''.$link.'\');">'.$code_desc.'</a>';
			}
			
			///// if the search item is codeset increase $matchcounter and set the last value to $ response and $type1 
			if(in_array(strtolower($code_name),$keyword_array,true)) {
				//// redirection link
				$response = $link;
				//// specify that the searched keyword is code
				$type1 = 'code';
				//// number of codes found
				$matchcounter++;
				///// get the relevancy of the string against the keyword
				///// store the values into an array
				$code_range_link = '<a class="'.$range_link_class.'" href="javascript:void();" onclick="redir(\''.$range_link.'\');">'.$code_range." - ".$code_range_desc.'</a>';
				$codesdata[]=array('data'=>array($code_type,$code_range_link,$leaf,$parent1_code,$parent1_desc,$parent1_code_link,$parent2_code,$parent2_desc,$parent2_code_link,$parent_digit_image,$super_parent_digit_image));
			}
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
			$codestring .= '{"type":"Text", "label":"CPT&reg;'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['CPT']).'},';
		}
		/////memory management
		unset($master_array['CPT']);
		
		if(count($master_array['HCPCS'])>0) {
			$codestring .= '{"type":"Text", "label":"HCPCS'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['HCPCS']).'},';
		}
		unset($master_array['HCPCS']);
		
		if(count($master_array['ICD'])>0) {
			$codestring .= '{"type":"Text", "label":"ICD-9-CM'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['ICD']).'},';
		}
		
		/////memory management
		unset($master_array['ICD']);
		
		if(isset($master_array['ICDVOL3']) && count($master_array['ICDVOL3'])>0) {
			$codestring .= '{"type":"Text", "label":"ICD-9-CM VOL3'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['ICDVOL3']).'},';
		}
		unset($master_array['ICDVOL3']);
		if(isset($master_array['HCPCS-Modifier']) && count($master_array['HCPCS-Modifier'])>0) {
			$codestring .= '{"type":"Text", "label":"HCPCS-Modifiers'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['HCPCS-Modifier']).'},';
		}
		unset($master_array['HCPCS-Modifier']);
		
		if(isset($master_array['CPT-Modifier']) && count($master_array['CPT-Modifier'])>0) {
			$codestring .= '{"type":"Text", "label":"CPT&reg;-Modifiers'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['CPT-Modifier']).'},';
		}
		unset($master_array['CPT-Modifier']);

		if(isset($master_array['DRG']) && count($master_array['DRG'])>0) {
			$codestring .= '{"type":"Text", "label":"DRG","expanded": "true","children":'.$this->array_to_json_string($master_array['DRG']).'},';
		}
		
		/////memory management
		unset($master_array['DRG']);
		
		if(count($master_array['APC'])>0) {
			$codestring .= '{"type":"Text", "label":"APC","expanded": "true","children":'.$this->array_to_json_string($master_array['APC']).'},';
		}
		
		/////memory management
		unset($master_array['APC']);

		if(count($master_array['ICD10'])>0) {
			$codestring .= '{"type":"Text", "label":"ICD-10","expanded": "true","children":'.$this->array_to_json_string($master_array['ICD10']).'},';
		}
		
		/////memory management
		unset($master_array['ICD10']);
		$codestring = rtrim($codestring,',');
		$output= 
			'{
				"spelling":"",
				"msg":"'.$msg.'",
				"response":"'.$response.'",
				"type1":"'.$type1.'",
				"treeview":['.$codestring.']
			 }';
			 echo $output;die;
		return $output;
	}
	
    /**
     * Performs a Sphinx search
     */
    public function search($filter,$q='',$searchtype='tabadvance',$page=1,$search_and='',$negative_word='',$force_search_pref='') {
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
					$slug_arr = array();
					if($filter!='')
					{
						$slug_arr = explode(",",$filter);
						if(count($slug_arr)>0)
							$cl->SetFilter('doc_type',$slug_arr);
					}
					
						if(is_logged_in())
						{
							//User specialty section
							$specialty_master_array= array('165'=>'29','45992'=>'30','52729'=>'31','56739'=>'52','180'=>'32','181'=>'33','182'=>'34','183'=>'35','56738'=>'54','45991'=>'36','184'=>'37','186'=>'62','187'=>'38','188'=>'63','56740'=>'53','189'=>'39','190'=>'40','191'=>'41','192'=>'42','193'=>'43','194'=>'45','195'=>'44','196'=>'46','197'=>'47','52728'=>'48','58172'=>'69','198'=>'49','199'=>'50','200'=>'51','58292'=>'70','58290'=>'71','58293'=>'72','58294'=>'73','58295'=>'74','58297'=>'75','58296'=>'76','58291'=>'77');							//$userspeciality = "180";
							$sorting_exp = '';
							foreach($specialty_master_array as $specialty=>$masterid)
							{
								if(has_access($masterid))
								{
									$sorting_exp .=  ' IF(doc_type='.$specialty.' AND year=2013,5,0) + IF(doc_type='.$specialty.' AND year=2012,4,0) + IF(doc_type='.$specialty.' AND year=2011,3,0) + IF(doc_type='.$specialty.' AND year=2010,2,0) + IF(doc_type='.$specialty.' AND year=2009,1,0) +';

								}
							}
							if($sorting_exp!='')
							{
								$sorting_exp  = trim($sorting_exp,' +')." as userspeciality ";
							}	
							//echo $sorting_exp;die;
							$cl->SetSelect("*, ".$sorting_exp);
							$cl->SetFieldWeights(array('post_title' => 1000, 'short_content' => 500, 'post_content' => 100));		
							$cl->SetSortMode(SPH_SORT_EXTENDED, "userspeciality DESC, @relevance DESC, parent_id ASC");
							//User specialty section
							
						}
						else
						{
							$cl->SetFieldWeights(array('post_title' => 1000, 'short_content' => 500, 'post_content' => 100));		
							$cl->SetSortMode(SPH_SORT_EXTENDED, "parent_id ASC, year DESC, @relevance DESC");
						}

					$cl->SetMatchMode($mode);
					$cl->SetRankingMode ( SPH_RANK_SPH04 );
					$cl->SetLimits($currentOffset,$sphinx_conf['page_size']); //current page and number of results
					// Add filters
										
					$res = $cl->Query($q, $sphinx_conf['sphinx_index']);
					//echo "<pre>";
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
						//if ($sphinx_conf['debug'] && $cl->GetLastWarning())
						//	$result_string .= "<br/>WARNING: ".$cl->GetLastWarning()."\n\n";
						//$query_info = "Query '".htmlentities($qo)."' retrieved ".count($res['matches'])." of $res[total_found] matches in $res[time] sec.\n";
						
						$resultCount = $res['total_found'];
						$numberOfPages = ceil($res['total']/$sphinx_conf['page_size']);
					}
					//$ids = '';
					//$ids_array = array();
					if ( !empty($res) && isset($res["matches"]) && is_array($res["matches"])) {
						//Build a list of IDs for use in the mysql Query and looping though the results
						$items = $res["matches"];
						/*$ids_array = array_keys($res["matches"]);
						foreach($ids_array as $match)
						{	
							$ids .= $match.",";		
						}	
						$ids = trim($ids,",");
						*/
					} else {
						return;
					}
					//$posts_obj = new Posts_combined();
					//$rows  = $posts_obj->search($ids);

				/////Start processing for advanced search.
					$category_obj = new Categories();
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
						'23'=>'Guides',
						'179'=>'Forms'
						);
					$articles_cats = array('165'=>'Anesthesia Coding Alert',
						'180'=>'Emergency Department Coding &amp; Reimbursement Alert',
						'181'=>'Family Practice Coding Alert',
						'182'=>'Gastroenterology Coding Alert',
						'183'=>'General Surgery Coding Alert',
						'184'=>'Practice Management Alert',
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
						'99371'=>'Behavioral Healthcare Alert'

									);
					$guides_cats = array('171'=>'2009-General Surgery Coders Survival Guide',
									'172'=>'2010 Pediatrics Survival Guide',
									'174'=>'Modifier Coding Survival Guide-2009',
									'175'=>'2009 Radiology Coders Survival Guide',
									'176'=>'2009-Otolaryngology Coders Survival Guide',
									'177'=>'2009-Cardiology Coders Survival Guide',
									'178'=>'2009-Part B Coder\'s Rule Book',
									'50018'=>'2009-Urology Coders Survival Guide',
									'50019'=>'Orthopedics Survival guide-2009',
									'50007'=>'ICD-9 UPDATE GUIDE',
									'50011'=>'CPT Implementation',
									'50016'=>'Ob Gyn Coders Survival Guide',
									'50017'=>'Pathology/Lab Survival Guide',
									'50020'=>'2009-Gastroenterology Survival Guide',
									'50022'=>'2009-EM Survival Guide',
									'50023'=>'Procedural Coding Implementation Guide',
									'53578'=>'2010 General Surgery Survival Guide',
									'53579'=>'2010 Urology Survival Guide',
									'53580'=>'2010 Cardiology Survival Guide',
									'53592'=>'2010 Radiology Survival Guide',
									'53618'=>'Obstetrics and Gynecology (Ob Gyn)',
									'53666'=>'Part B Coders Rule Book-2010',
									'53668'=>'2010 CPT Survival Guide',
									'53759'=>'2010 ICD9 Survival Guide',
									'53855'=>'2010 Procedure Survival Guide',
									'53899'=>'Otolaryngology Coders Survival Guide',
									'53900'=>'EM Survival Guide',
									'53901'=>'Gastroenterology Survival Guide',
									'54157'=>'2010 Coding &amp; Reimbursement',
									'54158'=>'2010 Orthopedics Survival Guide',
									'54159'=>'2010 Modifier Survival Guide',
									'54255'=>'2010 Pathology / Laboratory Survival Guide',
									'55368'=>'2011 Urology Survival Guide',
									'55405'=>'2011 Part B Insider Survival Guide',
									'55511'=>'2011 Gastroenterology Survival Guide',
									'55512'=>'2011 Otolaryngology Survival Guide',
									'55561'=>'2011 Physician Coding Update',
									'55562'=>'2011 Cardiology Survival Guide',
									'55770'=>'2011 EM Survival Guide',
									'55773'=>'2011 ICD 9 Specialty Guide',
									'55776'=>'2011 Coding and Reimbursement Survival Guide',
									'55858'=>'2011 Radiology Coding Survival Guide',
									'55859'=>'2011 Pediatric Coding Survival Guide',
									'55957'=>'2011 Pathology Laboratory Survival Guide',
									'55992'=>'2011 Obstetrics &amp; Gynecology Coding Survival Guide',
									'56035'=>'2011 General Surgery Survival Guide',
									'57318'=>'2012 ICD Specialty Guide',
									'57612'=>'2012 Urology Survival Guide',
									'57636'=>'2012 Cardiology Survival Guide',
									'57637'=>'2012 Gastroenterology Survival Guide',
									'57678'=>'2012 Evaluation and Management Survival Guide',
									'57717'=>'2012 Pediatric Coding Survival Guide',
									'57779'=>'2012 Ob-Gyn Coding Survival Guide',
									'57784'=>'2012 Physician Coding Update',
									'57785'=>'2012 Coding and Reimbursement Survival Guide',
									'57789'=>'2012 Otolaryngology Survival Guide',
									'57792'=>'2012 Part B Insider Survival Guide',
									'57794'=>'2012 Pathology Laboratory Survival Guide',
									'57810'=>'2012 Radiology Coding Survival Guide',
									'57932'=>'2012 General Surgery Survival Guide',
									'99637'=>'2013 Cardiology Survival Guide',
									'99662'=>'2013 EM Survival Guides',
									'99663'=>'2013 Physician Coding Update'
									);					
					$CatFullName=array('aca'=>'Anesthesia Coding Alert',
					'eca'=>'Emergency Department Coding & Reimbursement Alert',
					'fca'=>'Family Practice Coding Alert',
					'gac'=>'Gastroenterology Coding Alert',
					'gca'=>'General Surgery Coding Alert',
					'mob'=>'Practice Management Alert',
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
					'bha'=>'Behavioral Healthcare Alert');
					
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
									'totalResults'=>($totalResults>1000?1000:$totalResults),
									'startIndex'=>$startIndex,
									'itemsPerPage'=>$itemsPerPage,
									'currentpage'=>$page,
									'spelling'=>'',
									'result'=>$master_array,
									'msg'=>$msg);
					}else{

						$master_array = array(
									'totalResults'=>($totalResults>1000?1000:$totalResults),
									'startIndex'=>$startIndex,
									'itemsPerPage'=>$itemsPerPage,
									'currentpage'=>$page,
									'spelling'=>$spell,
									'result'=>$master_array,
									'msg'=>$msg);
					}
					
					///// traverse each item and copy to array
					$counter = 0;
					//print_r($items);die;
					foreach ($items as $match) {
						$item = $match['attrs'];
						$counter++;
						$serial_number = ($page - 1)*$itemsPerPage + $counter;
						$doc_type = $item['doc_type'];
						
						if(array_key_exists($doc_type,$codeset_cats)) 
						{
							$controller = '';
							switch($doc_type)
							{
								case 7:
								case 205:
									$controller = 'cpt-codes';
									break;
								case 8:
									$controller = 'icd9-codes';
									break;
								case 9:
								case 206:
									$controller = 'hcpcs-codes';
									break;
								case 201:
									$controller = 'drg-codes';
									break;
								case 202:
									$controller = 'icd9-codes-vol3';
									break;
								case 203:
									$controller = 'icd-10-codes';
									break;
								case 204:
									$controller = 'apc-codes';
									break;							
							}
								
							if(trim($item['description'])!='')
							{																		
								$titles = $codeset_cats[$doc_type]." | ".$this->mystriptag($item['description'])." @ Supercoder ";
								//$titles = $codeset_cats[$doc_type]." | ".preg_replace("/[^a-zA-Z0-9\s]/", "", $item['description'])." @ Supercoder ";
							}
							else
							{
								//$titles = $codeset_cats[$doc_type]." | ".$this->mystriptag($item['title'])." @ Supercoder ";
								$titles = $codeset_cats[$doc_type]." | ".preg_replace("/[^a-zA-Z0-9\s]/", "", $item['title'])." @ Supercoder ";
							}
							$link = current_base_url().$controller.'/'.$item['name'];
							if(trim($item['title'])!='')
							{							
								//$context = "...".$item['name']." ".$this->mystriptag($item['title'])."...";
								$context = "...".$item['name']." ".preg_replace("/[^a-zA-Z0-9\s]/", "", $item['title'])."...";
							}
							else
							{
								$context = "...".$this->mystriptag($item['description'])."...";
								//$context = "...".preg_replace("/[^a-zA-Z0-9\s]/", "", $item['description'])."...";
							}							
							$context = strip_tags(str_replace(
								array('&#60;','&#62;'),								
								array('<','>'),
								$context));
							///// populate page title and links for advanced search
							$master_array['result'][] = array(
											'type'=>'Code Detail',
											'title'=>$serial_number.'. '.str_replace(array('\\','"'),array('\\\\','\"'),$titles),
											'link'=>$link,'context'=>str_replace(array('\\','"'),array('\\\\','\"'),$context)
											);
						}
						else {

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
								//$titles = $serial_number.". ".$pre_title.$this->mystriptag($item['name']);
								$titles = $serial_number.". ".$pre_title;
								$link = current_base_url().$controller.$item['post_name'].$postfix_url;	
								$pdflink = current_base_url().$controller.$item['post_name'].$postfix_url;	

							}elseif(array_key_exists($doc_type,$articles_cats))
							{
								$speciality_url = get_specialty_url_by_slug($item['slug']);
								$controller = 'coding-newsletters/my-'.$speciality_url.'/';
								$postfix_url = '-article';
								//$titles = $this->mystriptag($item['name'])." | ".$this->mystriptag($item['title']);
								$titles = $serial_number.". ".$CatFullName[$item['slug']]." - ".$item['pdf_name'];
								if(is_logged_in() && isset($specialty_master_array[$doc_type]) && has_access($specialty_master_array[$doc_type]))
								{								
									$pdflink = current_base_url().'scc_articles/view_pdf/'.$item['pdf_year'].'-'.$item['specialty'].'-'.$item['pdf'];
								}
								$link = current_base_url().$controller.$item['post_name'].$postfix_url;							
								$type = 'Article';								
							}elseif(array_key_exists($doc_type,$guides_cats))
							{
								$controller = 'coding-references/'.$item['slug'].'/';
								$postfix_url = '';
								//$titles = $serial_number.". ".$this->mystriptag($item['name']);
								$titles = $serial_number.". ".preg_replace("/[^a-zA-Z0-9\s]/", "", $item['name']);
								$link = current_base_url().$controller.$item['post_name'].$postfix_url;							
								$type = 'Survival Guide';								
							}
							//$context = $this->mystriptag($item['title']);
							$context = preg_replace("/[^a-zA-Z0-9\s]/", "", $item['title']);
							/*$context = $this->mystriptag($item['body']);
							$context = strip_tags(str_replace(
								array('&#60;','&#62;'),								
								array('<','>'),
								$context));
							*/
							///// populate page title and links for advanced search
							$master_array['result'][] = array(
											'type'=>$type,
											'title'=>str_replace(array('\\','"'),array('\\\\','\"'),$titles),
											'link'=>$link,'linkpdf'=>$pdflink,'context'=>str_replace(array('\\','"'),array('\\\\','\"'),$context)
											);
						}	
					}
					///// convert the master array to json string
					$output = $this->array_to_json_string_advance($master_array);
					//$output = $this->offersToJSON($master_array);
				
					//// Output the json string
					return $output;
					
					/////////////////////
					//return $jsonresult;

			}
			elseif($searchtype=='tabcode')
			{
				$sphinx_conf['sphinx_index'] = "combined_index_codes";
				$sphinx_conf['sphinx_index1'] = "combined_index_codes1";
				$code_type_arr = array();

				$q = strtolower($q);
				// set filter attributes
				$IDs_combined = '';
				$index_ids_combined = '';
				if($filter!='')
				{
					$ids = array();
					$index_ids = array();
					$code_type_arr = explode(",",$filter);
					//print_r($code_type_arr);
					if(count($code_type_arr)>0)
					foreach($code_type_arr as $option)
					{
						$filter_array = array($option);
						$results = '';
						//echo $option;
						//print_r($filter_array);
						$sp = new SphinxClient();
						$sp->SetServer($sphinx_conf['sphinx_host'], $sphinx_conf['sphinx_port']);
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
							$results = $sp->Query($q, $sphinx_conf['sphinx_index1']);							
						}

						if(empty($results) || !isset($results['total_found']) || (isset($results['total_found']) && $results['total_found']==0) )
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
								foreach($results['matches'] as $match)
								{	
									if(isset($match['id']))
										$ids[]= $match["id"];	
								}
							}							
						}elseif (isset($results['matches']) && is_array($results['matches']) && $negative_word=='') 
						{
							foreach($results['matches'] as $match)
							{	
								if(isset($match['attrs']['codeset_combined_id']))
									$ids[]= $match['attrs']['codeset_combined_id'] ;	
								if(isset($match['id']))
										$index_ids[]= $match["id"];									
							}
						}
						unset($sp);
					}
					$ids = array_unique($ids);
					$IDs_combined = implode(',',$ids);
					$index_ids = array_unique($index_ids);
					$index_ids_combined = implode(',',$index_ids);
				}
				//echo $IDs_combined;
				//echo "###";
				//echo $index_ids_combined;die;
				if ((empty($IDs_combined) || $IDs_combined=='') && (empty($index_ids_combined) || $index_ids_combined=='')) 
				{
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
					
					$morelinks['ICDVOL3'] = array();
					$morelinks['CPT-Modifier'] = array();
					$morelinks['HCPCS-Modifier'] = array();
					///// start** set codeset urls for redirection and links
					$morelinks['CPT']['detail'] = $siteurl."cpt-codes/%s";
					$morelinks['CPT']['listing'] = $siteurl."cpt-codes-range/%s/?code=%s#%s";
					$morelinks['CPT']['range'] = $siteurl."cpt-codes-range/%s/";
					
					$morelinks['HCPCS']['detail'] = $siteurl."hcpcs-codes/%s";
					$morelinks['HCPCS']['listing'] = $siteurl."hcpcs-codes-range/%s/?code=%s#%s";
					$morelinks['HCPCS']['range'] = $siteurl."hcpcs-codes-range/%s/";
					
					$morelinks['ICD']['detail'] = $siteurl."icd9-codes/%s";
					$morelinks['ICD']['listing'] = $siteurl."icd9-codes-range/%s/?code=%s#%s";
					$morelinks['ICD']['range'] = $siteurl."icd9-codes-range/%s/";
					
					$morelinks['ICDVOL3']['detail'] = $siteurl."icd9-codes-vol3/%s";
					$morelinks['ICDVOL3']['listing'] = $siteurl."icd9-codes-vol3-range/%s/?code=%s#%s";
					$morelinks['ICDVOL3']['range'] = $siteurl."icd9-codes-vol3-range/%s/";
					
					$morelinks['CPT-Modifier']['detail'] = $siteurl."cpt-codes/%s";
					$morelinks['CPT-Modifier']['listing'] = $siteurl."cpt-codes-range/%s/?modifier=%s#%s";
					$morelinks['CPT-Modifier']['range'] = $siteurl."cpt-codes-range/%s/";
				
					$morelinks['HCPCS-Modifier']['detail'] = $siteurl."hcpcs-codes/%s";
					$morelinks['HCPCS-Modifier']['listing'] = $siteurl."hcpcs-codes-range/%s/?modifier=%s#%s";
					$morelinks['HCPCS-Modifier']['range'] = $siteurl."hcpcs-codes-range/%s/";
					
					$morelinks['DRG']['detail'] = $siteurl."drg-codes/%s";				
					$morelinks['DRG']['listing'] = $siteurl."drg-codes-range/%s/?code=%s#%s";
					$morelinks['DRG']['range'] = $siteurl."drg-codes-range/%s/";
		
					$morelinks['APC']['detail'] = $siteurl."apc-codes/%s";		
					$morelinks['APC']['listing'] = $siteurl."apc-codes-range/%s/?code=%s#%s";
					$morelinks['APC']['range'] = $siteurl."apc-codes-range/%s/";

					$morelinks['ICD10']['detail'] = $siteurl."icd-10-codes/%s";		
					$morelinks['ICD10']['listing'] = $siteurl."icd-10-codes-range/%s/?code=%s#%s";
					$morelinks['ICD10']['range'] = $siteurl."icd-10-codes-range/%s/";

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
					}else
					{
						$uoption['CPT'] = $uoption['ICD'] = $uoption['HCPCS'] = $uoption['ICDVOL3'] = $uoption['CPT-Modifier'] = $uoption['HCPCS-Modifier'] = $uoption['HCPCS-Modifier'] = $uoption['DRG'] = $uoption['APC'] = $uoption['ICD10'] = $force_search_pref;
					}				
					///// end manage redirection according to user option
					
					$codesdata = array();
					$relevancy = array();
					$rel_data = array();
					$matchcounter = 0;
					//print_r($items);die;
					foreach ($items as $item) {

						$titles = $item['code_title'];
						
						$code_name = $item['code_title'];
						
						$code_type = $item['code_type'];
						$code_type_int = $item['code_type_int'];
						
						$code_range_id = $item['range_id'];

						$code_range = $item['range_title'];
						
						$code_desc = preg_replace("/[^a-zA-Z0-9\s]/", "", $item['description']);//$this->mystriptag($item['description']);
						
						//$code_special_text = $this->mystriptag($item['special_text']);
						if( isset($item['index_description']) && trim($item['index_description'])!='')
						{
							$code_special_text = $this->mystriptag($item['index_description']);//preg_replace("/[^a-zA-Z0-9\s]/", " ", $item['index_description']);
						}else
						{
							$code_special_text = preg_replace("/[^a-zA-Z0-9\s]/", " ", $item['special_text']);//$this->mystriptag($item['special_text']);
						}
						
						$code_range_desc = $item['range_description'];
						
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
						if($item['super_parent_have_child']==1) {
							$super_parent_digit_image = '<img src="'.static_files_url().'images/front/icd-04.gif" align="top" style="float:left;padding-left:5px;padding-right:5px;" />';
						}
						if($item['super_parent_have_child']==2) {
							$super_parent_digit_image = '<img src="'.static_files_url().'images/front/icd-05.gif" align="top" style="float:left;padding-left:5px;padding-right:5px;" />';
						}
						if($code_type=='APC') {
							$code_range = 'APC';
							$code_range_desc = 'Codes';
						}
						if($code_type=='CPT-Modifier') {
							$code_range = 'CPT-Modifier';
							$code_range_desc = 'Codes';
						}
						if($code_type=='HCPCS-Modifier') {
							$code_range = 'HCPCS-Modifier';
							$code_range_desc = 'Codes';
						}
						if(!$login) {
							if($code_type=='DRG') {
								$code_range_desc = 'To see the official DRG section, subsection to <a href="'.current_base_url().'coding-solutions/drg-coder" >DRG Coder</a>';
								$code_desc = 'Read the DRG definition by subscribing to <a href="'.current_base_url().'coding-solutions/drg-coder" >DRG Coder</a> right now!';
							}
							else
							{
								$code_range_desc = '';
								$code_desc ='';
							}
						}

						///// Check search settings and create urls for redirection and links
						$link=$parent1_code_link=$parent2_code_link=$range_link='';
						if(isset($uoption[$code_type]) && $uoption[$code_type]=='listing') {
							$link = sprintf($morelinks[$code_type][$uoption[$code_type]],$code_range_id,$code_name,$code_name);
							$parent1_code_link = empty($parent1_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$code_range_id,$parent1_code,$parent1_code);
							$parent2_code_link = empty($parent2_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$code_range_id,$parent2_code,$parent2_code);
							$range_link = empty($code_range_id)?'':sprintf($morelinks[$code_type]['range'],$code_range_id);
						}
						else
						{
							if(isset($uoption[$code_type]))
							{
								$link = sprintf($morelinks[$code_type][$uoption[$code_type]],$code_name);
								$parent1_code_link = empty($parent1_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$parent1_code);
								$parent2_code_link = empty($parent2_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$parent2_code);
								$range_link = empty($code_range_id)?'':sprintf($morelinks[$code_type]['range'],$code_range_id);
							}
						}
						
						///// Check login status, CPT codes will only be displayed to users having CPT access
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
						if(!$login) {										
							$leaf = '<a class="'.$code_class.'" href="javascript:void();" onclick="redir(\''.$link.'\');" title="Read the code description by subscribing to <a href=\''.current_base_url().'coding-tools/code-search\'>Coding Tools</a> or <a href=\''.current_base_url().'coding-solutions\'>Coding Solutions</a>" >'.$code_name.'</a>  '.$code_desc;
						}
						else {
							$leaf = '<a class="'.$code_class.'" href="javascript:void();" onclick="redir(\''.$link.'\');">'.$code_name.'</a>'.' - <a href="javascript:void();" onclick="redir(\''.$link.'\');">'.$code_desc.'</a>';
						}
						
						///// if the search item is codeset increase $matchcounter and set the last value to $ response and $type1 
						if($iscodeset)
						{
						
							if(in_array(strtolower($code_name),$keyword_array,true)) {
							
								//// redirection link
								$response = $link;
								//// specify that the searched keyword is code
								$type1 = 'code';
								//// number of codes found
								$matchcounter++;
								///// get the relevancy of the string against the keyword

								$rel_value = $this->string_relevancy($code_name,$code_desc." ".$code_special_text,$keyword_array);
								///// store the values into an array
								$code_range_link = '<a class="'.$range_link_class.'" href="javascript:void();" onclick="redir(\''.$range_link.'\');">'.$code_range." - ".$code_range_desc.'</a>';
								$codesdata[]=array('relivancy'=>$rel_value,'data'=>array($code_type,$code_range_link,$leaf,$parent1_code,$parent1_desc,$parent1_code_link,$parent2_code,$parent2_desc,$parent2_code_link,$parent_digit_image,$super_parent_digit_image,$code_type_int));
								$relevancy[] = $rel_value;
								$rel_data[] = array($code_type,$code_range." - ".$code_range_desc,$leaf);	
							}
						}
						else
						{
							///// get the relevancy of the string against the keyword
							$rel_value = $this->string_relevancy($code_name,$code_desc." ".$code_special_text,$keyword_array);
							///// store the values into an array
							$code_range_link = '<a class="'.$range_link_class.'" href="javascript:void();" onclick="redir(\''.$range_link.'\');">'.$code_range." - ".$code_range_desc.'</a>';
							
							if(!$login) {
								$codesdata[]=array('relivancy'=>$rel_value,'data'=>array($code_type,$code_range_link,$leaf,$parent1_code,"",$parent1_code_link,$parent2_code,"",$parent2_code_link,$parent_digit_image,$super_parent_digit_image,$code_type_int));
							}else{
								$codesdata[]=array('relivancy'=>$rel_value,'data'=>array($code_type,$code_range_link,$leaf,$parent1_code,$parent1_desc,$parent1_code_link,$parent2_code,$parent2_desc,$parent2_code_link,$parent_digit_image,$super_parent_digit_image,$code_type_int));
							}
							$relevancy[] = $rel_value;
							$rel_data[] = array($code_type,$code_range." - ".$code_range_desc,$leaf);	
						}
					}
					//print_r($codesdata);die;
					//// sort the multidimentional codesdata array with respect to relevancy array
					array_multisort($relevancy, SORT_DESC, $rel_data, $codesdata);
					if(isset($codesdata[0]['data'][11]))
					$top_code_type_int = $codesdata[0]['data'][11];
					for($code_counter=0;($top_code_type_int!=7 && $top_code_type_int!=8 && $top_code_type_int!=9) && $code_counter<count($codesdata);$code_counter++)
					{
						if(isset($codesdata[$code_counter]['data'][11]))
						$top_code_type_int = $codesdata[$code_counter]['data'][11];
					}
					$top_open_set = 0;
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
								if($top_code_type_int == $item1['data'][11] && $top_open_set==0)
								{
									$master_array[$item1['data'][0]][$item1['data'][1]]['<a class="meg-link" href="javascript:void();" onclick="redir(\''.$item1['data'][8].'\');">'.$item1['data'][6].'</a> '.$item1['data'][10].' - '.$item1['data'][7]]['expend'] = 1;
									$master_array[$item1['data'][0]][$item1['data'][1]]['<a class="meg-link" href="javascript:void();" onclick="redir(\''.$item1['data'][8].'\');">'.$item1['data'][6].'</a> '.$item1['data'][10].' - '.$item1['data'][7]]['<a class="meg-link" href="javascript:void();" onclick="redir(\''.$item1['data'][5].'\');">'.$item1['data'][3].'</a> '.$item1['data'][9].' - '.$item1['data'][4]]['expend'] = 1;
								}
							}
						}
						else if(!empty($item1['data'][3]) && empty($item1['data'][6]))	{
							if(!$login) {
								$master_array[$item1['data'][0]][$item1['data'][1]]['<a class="meg-link" href="javascript:void();" onclick="redir(\''.$item1['data'][5].'\');" title="Read the code description by subscribing to <a href=\''.current_base_url().'coding-tools/code-search\'>Coding Tools</a> or <a href=\''.current_base_url().'coding-solutions\'>Coding Solutions</a>">'.$item1['data'][3].'</a> '.$item1['data'][9]][$item1['data'][2]][] = 1;
							}
							else
							{
								$master_array[$item1['data'][0]][$item1['data'][1]]['<a class="meg-link" href="javascript:void();" onclick="redir(\''.$item1['data'][5].'\');">'.$item1['data'][3].'</a> '.$item1['data'][9].' - '.$item1['data'][4]][$item1['data'][2]][] = 1;
								if($top_code_type_int == $item1['data'][11] && $top_open_set==0)
								{
									$master_array[$item1['data'][0]][$item1['data'][1]]['<a class="meg-link" href="javascript:void();" onclick="redir(\''.$item1['data'][5].'\');">'.$item1['data'][3].'</a> '.$item1['data'][9].' - '.$item1['data'][4]]['expend'] = 1;
								}
							}
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
					if(!$login) {
						$specific_msg_with_lable = "  <span class='blue'>Subscribers see the official code section or subsection</span>";
					}
					if($top_code_type_int=='7')
					{
						if(count($master_array['CPT'])>0) {
							$codestring1 = '{"type":"Text", "label":"CPT&reg;'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['CPT']).'},';
						}
					}else
					{
						if(count($master_array['CPT'])>0) {
							$codestring .= '{"type":"Text", "label":"CPT&reg;'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['CPT']).'},';
						}
					}
						/////memory management
						unset($master_array['CPT']);
					if($top_code_type_int=='9')
					{
						if(count($master_array['HCPCS'])>0) {
							$codestring1 = '{"type":"Text", "label":"HCPCS'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['HCPCS']).'},';
						}
					}else
					{
						if(count($master_array['HCPCS'])>0) {
							$codestring .= '{"type":"Text", "label":"HCPCS'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['HCPCS']).'},';
						}
					}
					unset($master_array['HCPCS']);
					if($top_code_type_int=='8')
					{
						if(count($master_array['ICD'])>0) {
							$codestring1 = '{"type":"Text", "label":"ICD-9-CM'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['ICD']).'},';
						}
					}else
					{
						if(count($master_array['ICD'])>0) {
							$codestring .= '{"type":"Text", "label":"ICD-9-CM'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['ICD']).'},';
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
							$codestring .= '{"type":"Text", "label":"ICD-9-CM VOL3'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['ICDVOL3']).'},';
						}
					//}
					unset($master_array['ICDVOL3']);

						if(isset($master_array['HCPCS-Modifier']) && count($master_array['HCPCS-Modifier'])>0) {
							$codestring .= '{"type":"Text", "label":"HCPCS-Modifiers'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['HCPCS-Modifier']).'},';
						}
					unset($master_array['HCPCS-Modifier']);

						if(isset($master_array['CPT-Modifier']) && count($master_array['CPT-Modifier'])>0) {
							$codestring .= '{"type":"Text", "label":"CPT&reg;-Modifiers'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['CPT-Modifier']).'},';
						}
					unset($master_array['CPT-Modifier']);

					/*if($top_code_type_int=='201')
					{										
						if(isset($master_array['DRG']) && count($master_array['DRG'])>0) {
							$codestring1 = '{"type":"Text", "label":"DRG","expanded": "true","children":'.$this->array_to_json_string($master_array['DRG']).'},';
						}
					}else{*/
						if(isset($master_array['DRG']) && count($master_array['DRG'])>0) {
							$codestring .= '{"type":"Text", "label":"DRG","expanded": "true","children":'.$this->array_to_json_string($master_array['DRG']).'},';
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
							$codestring .= '{"type":"Text", "label":"APC","expanded": "true","children":'.$this->array_to_json_string($master_array['APC']).'},';
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
							$codestring .= '{"type":"Text", "label":"ICD-10","expanded": "true","children":'.$this->array_to_json_string($master_array['ICD10']).'},';
						}
					//}
					/////memory management
					unset($master_array['ICD10']);
				
					$codestring = rtrim($codestring1.$codestring,',');
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
					//$jsonresult = $this->set_result($q,$rows,$searchtype,$resultCount,'',$spell,'',$force_search_pref);
					//exit;
					return $output;
				}
			}
		}
		else
		{
			return 'No Query';
		}				  
   }


	/*
	this function sets the result in tree structure or listing structure as specified
	*/
	function set_result($keyword,$data,$searchtype = 'tabcode',$resultCount=0,$page='',$spell='',$top_code_type_int='',$force_search_pref=''){
			$output ='';
			//// get total results
			$totalResults = $resultCount;//$doc->getElementsByTagName('totalResults');
			
			/////start processing the xml for codeset search.
			if($searchtype == 'tabcode') {
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
					
					//print_r($data);die;

					$items = $data;	
					
					
					$master_array = array();
					$master_array['CPT'] = array();
					$master_array['ICD'] = array();
					$master_array['HCPCS'] = array();
					$master_array['DRG'] = array();
					$master_array['APC'] = array();
					$master_array['ICD10'] = array();
					$master_array['CPT-Modifier'] = array();
					$master_array['HCPCS-Modifier'] = array();
					
					$morelinks['ICDVOL3'] = array();
					$morelinks['CPT-Modifier'] = array();
					$morelinks['HCPCS-Modifier'] = array();
					///// start** set codeset urls for redirection and links
					$morelinks['CPT']['detail'] = $siteurl."cpt-codes/%s";
					$morelinks['CPT']['listing'] = $siteurl."cpt-codes-range/%s/?code=%s#%s";
					$morelinks['CPT']['range'] = $siteurl."cpt-codes-range/%s/";
					
					$morelinks['HCPCS']['detail'] = $siteurl."hcpcs-codes/%s";
					$morelinks['HCPCS']['listing'] = $siteurl."hcpcs-codes-range/%s/?code=%s#%s";
					$morelinks['HCPCS']['range'] = $siteurl."hcpcs-codes-range/%s/";
					
					$morelinks['ICD']['detail'] = $siteurl."icd9-codes/%s";
					$morelinks['ICD']['listing'] = $siteurl."icd9-codes-range/%s/?code=%s#%s";
					$morelinks['ICD']['range'] = $siteurl."icd9-codes-range/%s/";
					
					$morelinks['ICDVOL3']['detail'] = $siteurl."icd9-codes-vol3/%s";
					$morelinks['ICDVOL3']['listing'] = $siteurl."icd9-codes-vol3-range/%s/?code=%s#%s";
					$morelinks['ICDVOL3']['range'] = $siteurl."icd9-codes-vol3-range/%s/";
					
					$morelinks['CPT-Modifier']['detail'] = $siteurl."cpt-codes/%s";
					$morelinks['CPT-Modifier']['listing'] = $siteurl."cpt-codes-range/%s/?modifier=%s#%s";
					$morelinks['CPT-Modifier']['range'] = $siteurl."cpt-codes-range/%s/";
				
					$morelinks['HCPCS-Modifier']['detail'] = $siteurl."hcpcs-codes/%s";
					$morelinks['HCPCS-Modifier']['listing'] = $siteurl."hcpcs-codes-range/%s/?modifier=%s#%s";
					$morelinks['HCPCS-Modifier']['range'] = $siteurl."hcpcs-codes-range/%s/";
					
					$morelinks['DRG']['detail'] = $siteurl."drg-codes/%s";				
					$morelinks['DRG']['listing'] = $siteurl."drg-codes-range/%s/?code=%s#%s";
					$morelinks['DRG']['range'] = $siteurl."drg-codes-range/%s/";
		
					$morelinks['APC']['detail'] = $siteurl."apc-codes/%s";		
					$morelinks['APC']['listing'] = $siteurl."apc-codes-range/%s/?code=%s#%s";
					$morelinks['APC']['range'] = $siteurl."apc-codes-range/%s/";

					$morelinks['ICD10']['detail'] = $siteurl."icd-10-codes/%s";		
					$morelinks['ICD10']['listing'] = $siteurl."icd-10-codes-range/%s/?code=%s#%s";
					$morelinks['ICD10']['range'] = $siteurl."icd-10-codes-range/%s/";

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
					}else
					{
						$uoption['CPT'] = $uoption['ICD'] = $uoption['HCPCS'] = $uoption['ICDVOL3'] = $uoption['CPT-Modifier'] = $uoption['HCPCS-Modifier'] = $uoption['HCPCS-Modifier'] = $uoption['DRG'] = $uoption['APC'] = $uoption['ICD10'] = $force_search_pref;
					}				
					///// end manage redirection according to user option
					
					$codesdata = array();
					$relevancy = array();
					$rel_data = array();
					$matchcounter = 0;
					//print_r($items);die;
					foreach ($items as $item) {

						$titles = $item['code_title'];
						
						$code_name = $item['code_title'];
						
						$code_type = $item['code_type'];
						$code_type_int = $item['code_type_int'];
						
						$code_range_id = $item['range_id'];

						$code_range = $item['range_title'];
						
						$code_desc = preg_replace("/[^a-zA-Z0-9\s]/", "", $item['description']);//$this->mystriptag($item['description']);
						
						//$code_special_text = $this->mystriptag($item['special_text']);
						if( isset($item['index_description']) && trim($item['index_description'])!='')
						{
							$code_special_text = $this->mystriptag($item['index_description']);//preg_replace("/[^a-zA-Z0-9\s]/", " ", $item['index_description']);
						}else
						{
							$code_special_text = preg_replace("/[^a-zA-Z0-9\s]/", " ", $item['special_text']);//$this->mystriptag($item['special_text']);
						}
						
						$code_range_desc = $item['range_description'];
						
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
						if($item['super_parent_have_child']==1) {
							$super_parent_digit_image = '<img src="'.static_files_url().'images/front/icd-04.gif" align="top" style="float:left;padding-left:5px;padding-right:5px;" />';
						}
						if($item['super_parent_have_child']==2) {
							$super_parent_digit_image = '<img src="'.static_files_url().'images/front/icd-05.gif" align="top" style="float:left;padding-left:5px;padding-right:5px;" />';
						}
						if($code_type=='APC') {
							$code_range = 'APC';
							$code_range_desc = 'Codes';
						}
						if($code_type=='CPT-Modifier') {
							$code_range = 'CPT-Modifier';
							$code_range_desc = 'Codes';
						}
						if($code_type=='HCPCS-Modifier') {
							$code_range = 'HCPCS-Modifier';
							$code_range_desc = 'Codes';
						}
						if(!$login) {
							if($code_type=='DRG') {
								$code_range_desc = 'To see the official DRG section, subsection to <a href="'.current_base_url().'coding-solutions/drg-coder" >DRG Coder</a>';
								$code_desc = 'Read the DRG definition by subscribing to <a href="'.current_base_url().'coding-solutions/drg-coder" >DRG Coder</a> right now!';
							}
							else
							{
								$code_range_desc = '';
								$code_desc ='';
							}
						}

						///// Check search settings and create urls for redirection and links
						$link=$parent1_code_link=$parent2_code_link=$range_link='';
						if(isset($uoption[$code_type]) && $uoption[$code_type]=='listing') {
							$link = sprintf($morelinks[$code_type][$uoption[$code_type]],$code_range_id,$code_name,$code_name);
							$parent1_code_link = empty($parent1_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$code_range_id,$parent1_code,$parent1_code);
							$parent2_code_link = empty($parent2_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$code_range_id,$parent2_code,$parent2_code);
							$range_link = empty($code_range_id)?'':sprintf($morelinks[$code_type]['range'],$code_range_id);
						}
						else
						{
							if(isset($uoption[$code_type]))
							{
								$link = sprintf($morelinks[$code_type][$uoption[$code_type]],$code_name);
								$parent1_code_link = empty($parent1_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$parent1_code);
								$parent2_code_link = empty($parent2_code)?'':sprintf($morelinks[$code_type][$uoption[$code_type]],$parent2_code);
								$range_link = empty($code_range_id)?'':sprintf($morelinks[$code_type]['range'],$code_range_id);
							}
						}
						
						///// Check login status, CPT codes will only be displayed to users having CPT access
						if(!$login) {										
							$leaf = '<a class="blue-link" href="javascript:void();" onclick="redir(\''.$link.'\');" title="Read the code description by subscribing to <a href=\''.current_base_url().'coding-tools/code-search\'>Coding Tools</a> or <a href=\''.current_base_url().'coding-solutions\'>Coding Solutions</a>" >'.$code_name.'</a>  '.$code_desc;
						}
						else {
							$leaf = '<a class="blue-link" href="javascript:void();" onclick="redir(\''.$link.'\');">'.$code_name.'</a>'.' - <a href="javascript:void();" onclick="redir(\''.$link.'\');">'.$code_desc.'</a>';
						}
						
						///// if the search item is codeset increase $matchcounter and set the last value to $ response and $type1 
						if($iscodeset)
						{
						
							if(in_array(strtolower($code_name),$keyword_array,true)) {
							
								//// redirection link
								$response = $link;
								//// specify that the searched keyword is code
								$type1 = 'code';
								//// number of codes found
								$matchcounter++;
								///// get the relevancy of the string against the keyword

								$rel_value = $this->string_relevancy($code_name,$code_desc." ".$code_special_text,$keyword_array);
								///// store the values into an array
								$code_range_link = '<a class="meg-link" href="javascript:void();" onclick="redir(\''.$range_link.'\');">'.$code_range." - ".$code_range_desc.'</a>';
								$codesdata[]=array('relivancy'=>$rel_value,'data'=>array($code_type,$code_range_link,$leaf,$parent1_code,$parent1_desc,$parent1_code_link,$parent2_code,$parent2_desc,$parent2_code_link,$parent_digit_image,$super_parent_digit_image,$code_type_int));
								$relevancy[] = $rel_value;
								$rel_data[] = array($code_type,$code_range." - ".$code_range_desc,$leaf);	
							}
						}
						else
						{
							///// get the relevancy of the string against the keyword
							$rel_value = $this->string_relevancy($code_name,$code_desc." ".$code_special_text,$keyword_array);
							///// store the values into an array
							$code_range_link = '<a class="meg-link" href="javascript:void();" onclick="redir(\''.$range_link.'\');">'.$code_range." - ".$code_range_desc.'</a>';
							if(!$login) {
								$codesdata[]=array('relivancy'=>$rel_value,'data'=>array($code_type,$code_range_link,$leaf,$parent1_code,"",$parent1_code_link,$parent2_code,"",$parent2_code_link,$parent_digit_image,$super_parent_digit_image,$code_type_int));
							}else{
								$codesdata[]=array('relivancy'=>$rel_value,'data'=>array($code_type,$code_range_link,$leaf,$parent1_code,$parent1_desc,$parent1_code_link,$parent2_code,$parent2_desc,$parent2_code_link,$parent_digit_image,$super_parent_digit_image,$code_type_int));
							}
							$relevancy[] = $rel_value;
							$rel_data[] = array($code_type,$code_range." - ".$code_range_desc,$leaf);	
						}
					}
					//print_r($codesdata);die;
					//// sort the multidimentional codesdata array with respect to relevancy array
					array_multisort($relevancy, SORT_DESC, $rel_data, $codesdata);
					if(isset($codesdata[0]['data'][11]))
					$top_code_type_int = $codesdata[0]['data'][11];
					for($code_counter=0;($top_code_type_int!=7 && $top_code_type_int!=8 && $top_code_type_int!=9);$code_counter++)
					{
						$top_code_type_int = $codesdata[$code_counter]['data'][11];
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
					if(!$login) {
						$specific_msg_with_lable = "  <span class='blue'>Subscribers see the official code section or subsection</span>";
					}
					if($top_code_type_int=='7')
					{
						if(count($master_array['CPT'])>0) {
							$codestring1 = '{"type":"Text", "label":"CPT&reg;'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['CPT']).'},';
						}
					}else
					{
						if(count($master_array['CPT'])>0) {
							$codestring .= '{"type":"Text", "label":"CPT&reg;'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['CPT']).'},';
						}
					}
						/////memory management
						unset($master_array['CPT']);
					if($top_code_type_int=='9')
					{
						if(count($master_array['HCPCS'])>0) {
							$codestring1 = '{"type":"Text", "label":"HCPCS'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['HCPCS']).'},';
						}
					}else
					{
						if(count($master_array['HCPCS'])>0) {
							$codestring .= '{"type":"Text", "label":"HCPCS'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['HCPCS']).'},';
						}
					}
					unset($master_array['HCPCS']);
					if($top_code_type_int=='8')
					{
						if(count($master_array['ICD'])>0) {
							$codestring1 = '{"type":"Text", "label":"ICD-9-CM'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['ICD']).'},';
						}
					}else
					{
						if(count($master_array['ICD'])>0) {
							$codestring .= '{"type":"Text", "label":"ICD-9-CM'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['ICD']).'},';
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
							$codestring .= '{"type":"Text", "label":"ICD-9-CM VOL3'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['ICDVOL3']).'},';
						}
					//}
					unset($master_array['ICDVOL3']);

						if(isset($master_array['HCPCS-Modifier']) && count($master_array['HCPCS-Modifier'])>0) {
							$codestring .= '{"type":"Text", "label":"HCPCS-Modifiers'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['HCPCS-Modifier']).'},';
						}
					unset($master_array['HCPCS-Modifier']);

						if(isset($master_array['CPT-Modifier']) && count($master_array['CPT-Modifier'])>0) {
							$codestring .= '{"type":"Text", "label":"CPT&reg;-Modifiers'.$specific_msg_with_lable.'","expanded": "true","children":'.$this->array_to_json_string($master_array['CPT-Modifier']).'},';
						}
					unset($master_array['CPT-Modifier']);

					/*if($top_code_type_int=='201')
					{										
						if(isset($master_array['DRG']) && count($master_array['DRG'])>0) {
							$codestring1 = '{"type":"Text", "label":"DRG","expanded": "true","children":'.$this->array_to_json_string($master_array['DRG']).'},';
						}
					}else{*/
						if(isset($master_array['DRG']) && count($master_array['DRG'])>0) {
							$codestring .= '{"type":"Text", "label":"DRG","expanded": "true","children":'.$this->array_to_json_string($master_array['DRG']).'},';
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
							$codestring .= '{"type":"Text", "label":"APC","expanded": "true","children":'.$this->array_to_json_string($master_array['APC']).'},';
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
							$codestring .= '{"type":"Text", "label":"ICD-10","expanded": "true","children":'.$this->array_to_json_string($master_array['ICD10']).'},';
						}
					//}
					/////memory management
					unset($master_array['ICD10']);
				
					$codestring = rtrim($codestring1.$codestring,',');
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
			} /////end processing the xml for codeset search.
			elseif($searchtype == 'tabadvance')
			{	
				/////Start processing for advanced search.
					//$category_obj = new Categories();
					//$codeset_cats = array();
					//$cms_cats = array();
					//$articles_cats = array();
					//$guides_cats = array();
					//$codeset_cats  = $category_obj->get_categories_codeset();
					//$cms_cats  = $category_obj->get_categories_cms();
					//$articles_cats  = $category_obj->get_categories_articles();
					//$guides_cats  = $category_obj->get_categories_guides();
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
						'23'=>'Guides',
						'179'=>'Forms'
						); 
					$articles_cats = array('165'=>'Anesthesia Coding Alert',
						'180'=>'Emergency Department Coding &amp; Reimbursement Alert',
						'181'=>'Family Practice Coding Alert',
						'182'=>'Gastroenterology Coding Alert',
						'183'=>'General Surgery Coding Alert',
						'184'=>'Practice Management Alert',
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
						'99371'=>'Behavioral Healthcare Alert'

									);
					$guides_cats = array('171'=>'2009-General Surgery Coders Survival Guide',
									'172'=>'2010 Pediatrics Survival Guide',
									'174'=>'Modifier Coding Survival Guide-2009',
									'175'=>'2009 Radiology Coders Survival Guide',
									'176'=>'2009-Otolaryngology Coders Survival Guide',
									'177'=>'2009-Cardiology Coders Survival Guide',
									'178'=>'2009-Part B Coder\'s Rule Book',
									'50018'=>'2009-Urology Coders Survival Guide',
									'50019'=>'Orthopedics Survival guide-2009',
									'50007'=>'ICD-9 UPDATE GUIDE',
									'50011'=>'CPT Implementation',
									'50016'=>'Ob Gyn Coders Survival Guide',
									'50017'=>'Pathology/Lab Survival Guide',
									'50020'=>'2009-Gastroenterology Survival Guide',
									'50022'=>'2009-EM Survival Guide',
									'50023'=>'Procedural Coding Implementation Guide',
									'53578'=>'2010 General Surgery Survival Guide',
									'53579'=>'2010 Urology Survival Guide',
									'53580'=>'2010 Cardiology Survival Guide',
									'53592'=>'2010 Radiology Survival Guide',
									'53618'=>'Obstetrics and Gynecology (Ob Gyn)',
									'53666'=>'Part B Coders Rule Book-2010',
									'53668'=>'2010 CPT Survival Guide',
									'53759'=>'2010 ICD9 Survival Guide',
									'53855'=>'2010 Procedure Survival Guide',
									'53899'=>'Otolaryngology Coders Survival Guide',
									'53900'=>'EM Survival Guide',
									'53901'=>'Gastroenterology Survival Guide',
									'54157'=>'2010 Coding &amp; Reimbursement',
									'54158'=>'2010 Orthopedics Survival Guide',
									'54159'=>'2010 Modifier Survival Guide',
									'54255'=>'2010 Pathology / Laboratory Survival Guide',
									'55368'=>'2011 Urology Survival Guide',
									'55405'=>'2011 Part B Insider Survival Guide',
									'55511'=>'2011 Gastroenterology Survival Guide',
									'55512'=>'2011 Otolaryngology Survival Guide',
									'55561'=>'2011 Physician Coding Update',
									'55562'=>'2011 Cardiology Survival Guide',
									'55770'=>'2011 EM Survival Guide',
									'55773'=>'2011 ICD 9 Specialty Guide',
									'55776'=>'2011 Coding and Reimbursement Survival Guide',
									'55858'=>'2011 Radiology Coding Survival Guide',
									'55859'=>'2011 Pediatric Coding Survival Guide',
									'55957'=>'2011 Pathology Laboratory Survival Guide',
									'55992'=>'2011 Obstetrics &amp; Gynecology Coding Survival Guide',
									'56035'=>'2011 General Surgery Survival Guide',
									'57318'=>'2012 ICD Specialty Guide',
									'57612'=>'2012 Urology Survival Guide',
									'57636'=>'2012 Cardiology Survival Guide',
									'57637'=>'2012 Gastroenterology Survival Guide',
									'57678'=>'2012 Evaluation and Management Survival Guide',
									'57717'=>'2012 Pediatric Coding Survival Guide',
									'57779'=>'2012 Ob-Gyn Coding Survival Guide',
									'57784'=>'2012 Physician Coding Update',
									'57785'=>'2012 Coding and Reimbursement Survival Guide',
									'57789'=>'2012 Otolaryngology Survival Guide',
									'57792'=>'2012 Part B Insider Survival Guide',
									'57794'=>'2012 Pathology Laboratory Survival Guide',
									'57810'=>'2012 Radiology Coding Survival Guide',
									'57932'=>'2012 General Surgery Survival Guide',
									'99637'=>'2013 Cardiology Survival Guide',
									'99662'=>'2013 EM Survival Guides',
									'99663'=>'2013 Physician Coding Update'
									);				
					$master_array = array();
					$msg = "";
					$items = $data;
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
									'spelling'=>'',
									'result'=>$master_array,
									'msg'=>$msg);
					}else{

						$master_array = array(
									'totalResults'=>($totalResults>1000?1000:$totalResults),
									'startIndex'=>$startIndex,
									'itemsPerPage'=>$itemsPerPage,
									'currentpage'=>$page,
									'spelling'=>$spell,
									'result'=>$master_array,
									'msg'=>$msg);
					}
					
					///// traverse each item and copy to array
					foreach ($items as $item) {
						
						$doc_type = $item['doc_type'];
						
						if(array_key_exists($doc_type,$codeset_cats)) 
						{
							$controller = '';
							switch($doc_type)
							{
								case 7:
								case 205:
									$controller = 'cpt-codes';
									break;
								case 8:
									$controller = 'icd9-codes';
									break;
								case 9:
								case 206:
									$controller = 'hcpcs-codes';
									break;
								case 201:
									$controller = 'drg-codes';
									break;
								case 202:
									$controller = 'icd9-codes-vol3';
									break;
								case 203:
									$controller = 'icd-10-codes';
									break;
								case 204:
									$controller = 'apc-codes';
									break;							
							}
								
							if(trim($item['description'])!='')
							{																		
								$titles = $codeset_cats[$doc_type]." | ".$this->mystriptag($item['description'])." @ Supercoder ";
								//$titles = $codeset_cats[$doc_type]." | ".preg_replace("/[^a-zA-Z0-9\s]/", "", $item['description'])." @ Supercoder ";
							}
							else
							{
								//$titles = $codeset_cats[$doc_type]." | ".$this->mystriptag($item['title'])." @ Supercoder ";
								$titles = $codeset_cats[$doc_type]." | ".preg_replace("/[^a-zA-Z0-9\s]/", "", $item['title'])." @ Supercoder ";
							}
							$link = current_base_url().$controller.'/'.$item['name'];
							if(trim($item['title'])!='')
							{							
								//$context = "...".$item['name']." ".$this->mystriptag($item['title'])."...";
								$context = "...".$item['name']." ".preg_replace("/[^a-zA-Z0-9\s]/", "", $item['title'])."...";
							}
							else
							{
								$context = "...".$this->mystriptag($item['description'])."...";
								//$context = "...".preg_replace("/[^a-zA-Z0-9\s]/", "", $item['description'])."...";
							}							
							$context = strip_tags(str_replace(
								array('&#60;','&#62;'),								
								array('<','>'),
								$context));
							///// populate page title and links for advanced search
							$master_array['result'][] = array(
											'title'=>str_replace(array('\\','"'),array('\\\\','\"'),$titles),
											'link'=>$link,'context'=>str_replace(array('\\','"'),array('\\\\','\"'),$context)
											);
						}
						else {

							$controller="";
							$postfix_url = '';
							$link = '';
							$titles = '';
							if(array_key_exists($doc_type,$cms_cats)) 
							{
								$cat_slug = array('20'=>'transmittals',
                                                  '21'=>'evaluation-management',
                                                   '22'=>'claims-processing-manuals',
                                                   '23'=>'mln-specialty-book');
								$controller = 'exclusives/'.$cat_slug[$item['doc_type']].'/';
								$postfix_url = '';
								//$titles = $this->mystriptag($item['name']);
								$titles = preg_replace("/[^a-zA-Z0-9\s]/", "", $item['name']);
								$link = current_base_url().$controller.$item['post_name'].$postfix_url;							
							}elseif(array_key_exists($doc_type,$articles_cats))
							{
								$speciality_url = get_specialty_url_by_slug($item['slug']);
								$controller = 'coding-newsletters/my-'.$speciality_url.'/';
								$postfix_url = '-article';
								//$titles = $this->mystriptag($item['name'])." | ".$this->mystriptag($item['title']);
								$titles = preg_replace("/[^a-zA-Z0-9\s]/", "", $item['name'])." | ".preg_replace("/[^a-zA-Z0-9\s]/", "", $item['title']);
								$link = current_base_url().$controller.$item['post_name'].$postfix_url;							
							}elseif(array_key_exists($doc_type,$guides_cats))
							{
								$controller = 'coding-references/'.$item['slug'].'/';
								$postfix_url = '';
								//$titles = $this->mystriptag($item['name'])." | ".$this->mystriptag($item['title']);
								$titles = preg_replace("/[^a-zA-Z0-9\s]/", "", $item['name'])." | ".preg_replace("/[^a-zA-Z0-9\s]/", "", $item['title']);
								$link = current_base_url().$controller.$item['post_name'].$postfix_url;							
							}
							
							//$titles = $item['name']." | ".$item['description']." : ".$this->mystriptag($item['title']);
							//$context = $this->mystriptag($item['body']);
							$context = preg_replace("/[^a-zA-Z0-9\s]/", "", $item['body']);
							$context = strip_tags(str_replace(
								array('&#60;','&#62;'),								
								array('<','>'),
								$context));
							///// populate page title and links for advanced search
							$master_array['result'][] = array(
											'title'=>str_replace(array('\\','"'),array('\\\\','\"'),$titles),
											'link'=>$link,'context'=>str_replace(array('\\','"'),array('\\\\','\"'),$context)
											);
						}	
					}
					///// convert the master array to json string
					$output = $this->array_to_json_string_advance($master_array);
					//$output = $this->offersToJSON($master_array);
			}	
			//// Output the json string
			return $output;
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
	
	
	function pagesString($currentUrl,$currentPage,$numberOfPages,$postfix = '',$extrahtml ='') {
		static $r;
		if (!empty($r))
			return($r);
	
		if ($currentPage > 1) 
			$r .= "<a href=\"".($currentPage-1)."$postfix\"$extrahtml>&lt; &lt; prev</a> ";
		$start = max(1,$currentPage-5);
		$endr = min($numberOfPages+1,$currentPage+8);
	
		if ($start > 1)
			$r .= "<a href=\""."1"."$postfix\"$extrahtml>1</a> ... ";
	
		for($index = $start;$index<$endr;$index++) {
			if ($index == $currentPage) 
				$r .= "<b>$index</b> "; 
			else
				$r .= "<a href=\"".$index."$postfix\"$extrahtml>$index</a> ";
		}
		if ($endr < $numberOfPages+1) 
			$r .= "... ";
	
		if ($numberOfPages > $currentPage) 
			$r .= "<a href=\"".($currentPage+1)."$postfix\"$extrahtml>next &gt;&gt;</a> ";
	
		return $r;
	}
	
	//output a multi-dimensional array as a nested UL
	function print_tree_from_array($array){
		$tree_str ='';
		//start the UL
		$tree_str .= "<ul>\n";
		   //loop through the array
		foreach($array as $key => $member){
			//check for value member
			if(isset($member['txt']) && $key!='txt' )
			{
				//if value is present, echo it in an li
				$tree_str .=  "<li>$key - {$member['txt']}\n";
			}
			elseif(!isset($member['txt']) && $key!='txt' )
			{
				$tree_str .=  "<li class='expanded'>$key";
			}
			if(is_array($member) && count($member)>0){
				//if the member is another array, start a fresh li
				//echo "<li>\n";
				//and pass the member back to this function to start a new ul
				$tree_str .= print_tree_from_array($member);
				//then close the li
				$tree_str .=  "</li>\n";
			}
		}
		//finally close the ul
		$tree_str .=  "</ul>\n";
		return $tree_str;
	}
	
	
	function create_rcrsv_array($icd)
	{
	//echo "<pre>";
		$icd_tree=array();
		if(count($icd)>0)
		{
			foreach($icd as $icd_data)
			{
				$icd_data = trim($icd_data,"##");
				$icd_data_array = explode("##",$icd_data);
				//print_r($icd_data_array);
					$level0key = '';
					$level1key = '';
					$level2key = '';
					$level3key = '';
					$level4key = '';
					$level5key = '';
				for($i=0;$i<count($icd_data_array);$i++)
				{ 
					if($i==0)
					{
							$level0key = $icd_data_array[$i];
					}
					if($i==1)
					{
						if(is_numeric(strpos($icd_data_array[$i]," - ")))
						{
							$parts = explode(" - ",$icd_data_array[$i]);
							$level1key = $parts[0];
							$icd_tree[$level0key][$level1key]['txt'] = $parts[1]; 
						}
						else
						{
							$icd_tree[$level0key]['txt'] = $icd_data_array[$i]; 
						}
					}
					if($i==2)
					{
						if(is_numeric(strpos($icd_data_array[$i]," - ")))
						{
							$parts = explode(" - ",$icd_data_array[$i]);
							$level2key = $parts[0];
							$icd_tree[$level0key][$level1key][$level2key]['txt'] = $parts[1]; 
						}
						else
						{
							//echo $level0key.">".$level1key;die;
							$icd_tree[$level0key][$level1key]['txt'] = $icd_data_array[$i]; 
						}
					}
					if($i==3)
					{
						if(is_numeric(strpos($icd_data_array[$i]," - ")))
						{
							$parts = explode(" - ",$icd_data_array[$i]);
							$level3key = $parts[0];
							$icd_tree[$level0key][$level1key][$level2key][$level3key]['txt'] = $parts[1]; 
						}
						else
						{
							$icd_tree[$level0key][$level1key][$leve21key]['txt'] = $icd_data_array[$i]; 
						}
					}	
					if($i==4)
					{
						if(is_numeric(strpos($icd_data_array[$i]," - ")))
						{
							$parts = explode(" - ",$icd_data_array[$i]);
							$level4key = $parts[0];
							$icd_tree[$level0key][$level1key][$level2key][$level3key][$level4key]['txt'] = $parts[1]; 
						}
						else
						{
							$icd_tree[$level0key][$level1key][$leve21key][$level3key]['txt'] = $icd_data_array[$i]; 
						}
					}
					if($i==5)
					{
						if(is_numeric(strpos($icd_data_array[$i]," - ")))
						{
							$parts = explode(" - ",$icd_data_array[$i]);
							$level5key = $parts[0];
							$icd_tree[$level0key][$level1key][$level2key][$level3key][$level4key][$level5key]['txt'] = $parts[1]; 
						}
						else
						{
							$icd_tree[$level0key][$level1key][$leve21key][$level3key][$level4key]['txt'] = $icd_data_array[$i]; 
						}
					}
				}
			}
		}
		//echo "<pre>";
		//print_r($icd_tree);die;
		return $icd_tree;
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
			if (is_numeric($key_word)|| preg_match('/^[0-9]{4}[a-zA-Z]{1}/i',$key_word,$res) || preg_match("/^[a-zA-Z]{1}+([\.0-9])+$/i",trim($key_word),$all_codes) || preg_match("/^[a-zA-Z]{1}+([0-9]){4}$/i",trim($key_word),$all_codes)) {
				$iscodeset = true;
			}
		}
		return $iscodeset;
	}	
 
 	/*
	function draw_search_interface draw search interface for different pages as given parameter
	category_types_to_show_options  =  blank/codeset/library/specialities/survivalguide/cmsinfocenter
	searchin_category  =  all/specificcode/post/codeset/library/specialities/survivalguide/cmsinfocenter
	output  =  tree / rows
	numberofresult = 0(all), n ( number of rows )
	*/
 	function draw_search_interface($options='blank',$searchin='all',$output='rows',$numberofrows=0)
	{
		$html = '';
		$cat_obj = new Categories();
		$allcats = $cat_obj->get_all_searchable_categories();
		
		$html .= '<form action="'.base_url().'sphinx_search1/search" method="post" id="search">
									Enter Search Term
							  <input type="text" class="input" size="50" name="txtadvancesearch" value="" style="height:18px;width:190px" />
							  <input type="hidden" value="'.$output.'" name="output">
							  <input type="hidden" value="'.$searchin.'" name="searchin">
							  <input type="hidden" value="'.$numberofrows.'" name="numberofrows">
								<input name="" type="submit" class="search-btn" value="Search" id="btntabadvance" />';

		switch($options)
		{
			case 'blank':
				$html .= '';
				break;
			case 'codeset':
					$codesets = array("1"=>"CPT<sup>&reg;</sup>","2"=>"DRG","3"=>"HCPCS II","4"=>"ICD-9-CM","5"=>"ICD-9-CM Vol.3","6"=>"APC","7"=>"Modifiers");
					foreach($codesets as $codeint=>$codetype)
					{
						$checkstr = '';
/*						if(isset($codefilter) && is_array($codefilter))
						{
							foreach($codefilter as $check)
							{
								if($check == 'all')
									$checkstr = 'checked="checked"';
								if($check == $codeint)
									$checkstr = 'checked="checked"';
							}
								
						}*/
						$html .= '<input type="checkbox" value="'.$codeint.'" onclick="document.getElementById(\'checkboxcodec1\').checked=false;" name="codefilter_cat[]" id="checkboxcodec2" class="code" '.$checkstr.' >&nbsp;&nbsp;'.$codetype.'&nbsp;&nbsp;';
					}
				$html .= '';
				break;
			case 'specialities':
					$specialities = $cat_obj->get_specialities();
					foreach($specialities as $codeint=>$codetype)
					{
						$checkstr = '';
/*						if(isset($filter) && is_array($filter))
						{
							foreach($filter as $check)
							{
								if($check == 'all')
									$checkstr = 'checked="checked"';
								if($check == $codeint)
									$checkstr = 'checked="checked"';
							}
								
						}
*/						$html .= '<br><input type="checkbox" value="'.$codeint.'" onclick="document.getElementById(\'checkboxcodec1\').checked=false;" name="filter_cat[]" id="checkboxcodec2" class="code" '.$checkstr.' >&nbsp;&nbsp;'.$codetype.'&nbsp;&nbsp;';
					
					
					}
				$html .= '';
				break;
			case 'survivalguide':
					$survivalguides = $cat_obj->get_survivalguides();
					foreach($survivalguides as $codeint=>$codetype)
					{
						$checkstr = '';
/*						if(isset($filter) && is_array($filter))
						{
							foreach($filter as $check)
							{
								if($check == 'all')
									$checkstr = 'checked="checked"';
								if($check == $codeint)
									$checkstr = 'checked="checked"';
							}
								
						}*/
						$html .= '<br><input type="checkbox" value="'.$codeint.'" onclick="document.getElementById(\'checkboxcodec1\').checked=false;" name="filter_cat[]" id="checkboxcodec2" class="code" '.$checkstr.' >&nbsp;&nbsp;'.$codetype.'&nbsp;&nbsp;';
					
					
					}			
				$html .= '';
				break;
			case 'cmsinfocenter':
					$cms = $cat_obj->get_allcms();
					foreach($cms as $codeint=>$codetype)
					{
						$checkstr = '';
/*						if(isset($filter) && is_array($filter))
						{
							foreach($filter as $check)
							{
								if($check == 'all')
									$checkstr = 'checked="checked"';
								if($check == $codeint)
									$checkstr = 'checked="checked"';
							}
								
						}*/
						$html .='<br><input type="checkbox" value="'.$codeint.'" onclick="document.getElementById(\'checkboxcodec1\').checked=false;" name="filter_cat[]" id="checkboxcodec2" class="code" '.$checkstr.' >&nbsp;&nbsp;'.$codetype.'&nbsp;&nbsp;';
					
					
					}			
				$html .= '';
				break;
		
			default:
			
				$html .= '</form>';
		
		}
	
		return $html;
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


}