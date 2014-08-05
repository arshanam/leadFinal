<?php
/**
* 	Intern Model
*/
class Intern_model extends MY_Model
{

	public $table = "users";
	private $CI;

	public function __construct()
	{
		$this->primary_keys = array('user_id');
		parent::__construct();
      $this->CI = & get_instance();
   }
    public function interndashboard($intern_id,$intern_name)
   {
    	$querystring="SELECT * FROM intern_lists WHERE intern_id='".$intern_id."'";
		$query=$this->db->query($querystring);
		$resarr=$query->result_array();
		return $resarr;
   }
   public function downloadrecord($internid,$listname)
   {
   	$fquery=$this->db->query("SELECT list_id FROM listmaster where list_name='".$listname."'");
   	$fqueryres=$fquery->result_array();
   	$listid=$fqueryres[0]['list_id'];
    $fq=$this->db->query("SELECT min(record_id) FROM record_operations WHERE assigned_intern_id='".$internid."'");

   	$eq=$this->db->query("SELECT max(record_id) FROM record_operations WHERE assigned_intern_id='".$internid."'");

   	$frec=$fq->result_array();
   	$erec=$eq->result_array();
   	$start=(int)$frec[0]['record_id'];
   	$end=(int)$erec[0]['record_id'];
	$queryxyz="SELECT DISTINCT rm.email, rm.firstname, rm.middlename, rm.lastname, rm.title, rm.company, rm.department, rm.address1, rm.address2, rm.city, rm.state, rm.zipcode, rm.phone, rm.fax  FROM recordmaster rm, record_operations ro WHERE ro.assigned_intern_id='".$internid."' AND ro.list_id='".$listid."' AND ro.list_id=rm.list_id AND rm.record_id IN (SELECT record_id FROM record_operations WHERE assigned_intern_id='".$internid."')";
	$qfire=$this->db->query($queryxyz);
	$resquery=$qfire->result_array();
	$count=count($resquery);
	for($i=0;$i<$count;$i++)
	{
		$resquery[$i]['status']=0;
	}
	return $resquery;
}
	public function download_send_headers($filename) {
    // disable caching
    $now = gmdate("D, d M Y H:i:s");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");

    // force download
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");

    // disposition / encoding on response body
    header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary");
	}
	public function array2csv(array &$array)
	{
   	if (count($array) == 0) {
     		return null;
   	}
   	ob_start();
   	$df = fopen("php://output", 'w');
   	fputcsv($df, array_keys(reset($array)));
   	foreach ($array as $row) {
      	fputcsv($df, $row);
   	}
   	fclose($df);
   	// tell the browser it's going to be a csv file
    	//header('Content-Type: application/csv');
    	// tell the browser we want to save it instead of displaying it
    	//header('Content-Disposition: attachement; filename="'.$fname.'";');
    	// make php send the generated csv lines to the browser
    	//fpassthru($df);
   	return ob_get_clean();
	}

	//record storing in db
	public function record_in_db($fname,$fext,$lid)
	{
		$f=fopen("./webroot/uploads/".$fname,"r") or die("Unable to open file!");
		if($fext==".csv")
		{
			$str=file_get_contents("./webroot/uploads/".$fname);
			if(!$str)
			{
				return;
			}
			$a1=[",,","\n","\r\n"];
			$a2=[",bl,",",br,",",br,"];
			$strr=str_replace($a1,$a2,$str);
			$arr=explode(",",$strr);
			foreach($arr as $key => $val)
			{
				if($val=="bl")
				{
					$arr[$key]=" ";
				}
			}
			//fields checking
			$fields=array("email","firstname","middlename","lastname","title","company","department","address1","address2","city","state","zipcode","phone","fax","status");
			$subarr=array_slice($arr,0,15);
			$flag=0;
			foreach(array_combine($fields,$subarr) as $key => $value)
			{
				if($key==$value)
					$flag=1;
			}
			if($flag==1 && $arr[15]=="br")
			{
				//loop for records accessing
				for($j=15;$j<count($arr);$j=$j+16)
				{
					list($em,$fname,$mname,$lname,$title,$company,$department,$addr1,$addr2,$city,$state,$zipcode,$phone,$fax,$status)=[$arr[$j+1],$arr[$j+2],$arr[$j+3],$arr[$j+4],$arr[$j+5],$arr[$j+6],$arr[$j+7],$arr[$j+8],$arr[$j+9],$arr[$j+10],$arr[$j+11],$arr[$j+12],$arr[$j+13],$arr[$j+14],$arr[$j+15]];
					//slug left
					$email=str_replace('"',"",$em);
					if(!empty($email) && $status==1)
					{
						$q="SELECT * FROM recordmaster WHERE email='".$email."'";
						$sqlquery=$this->db->query($q);
						$res=$sqlquery->result_array();
						$numrows=$sqlquery->num_rows();
						if($numrows>0)
						{
							if(!(empty($fname)))
								$updates[]="firstname='".$fname."'";
							if(!(empty($mname)))
								$updates[]="middlename='".$mname."'";
							if(!(empty($lname)))
								$updates[]="lastname='".$lname."'";
							if(!(empty($title)))
								$updates[]="title='".$title."'";
							if(!(empty($company)))
								$updates[]="company='".$company."'";
							if(!(empty($department)))
								$updates[]="department='".$department."'";
							if(!(empty($addr1)))
								$updates[]="address1='".$addr1."'";
							if(!(empty($addr2)))
								$updates[]="address2='".$addr2."'";
							if(!(empty($city)))
								$updates[]="city='".$city."'";
							if(!(empty($state)))
								$updates[]="state='".$state."'";
							if(!(empty($zipcode)))
								$updates[]="zipcode='".$zipcode."'";
							if(!(empty($phone)))
								$updates[]="phone='".$phone."'";
							if(!(empty($fax)))
								$updates[]="fax='".$fax."'";
							$str=implode(",",$updates);

							$q2="UPDATE recordmaster SET ".$str.",record_status='modified_by_intern' where email='".$email."' AND list_id='".$lid."'";
							$res2=$this->db->query($q2);
							//intern status in record_operations
							$rid=$res[0]['record_id'];
							$q3="UPDATE record_operations SET intern_status='1' where record_id='".$rid."' AND list_id='".$lid."'";
							$res3=$this->db->query($q3);
						}
					}
				}
				return "Success";
			}
			else
				return "Error!!! Upload Again";
		}
	}
	public function intern_history($intern_id)
	{
		$fetchhistory=$this->db->query("SELECT * FROM intern_history WHERE intern_id='".$intern_id."'");
		$history=$fetchhistory->result_array();
		return $history;
	}
}
