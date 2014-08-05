<?php
class Validation_module extends MY_Model
{
	public $table = "users";
	private $CI;

	public function __construct()
	{
        $this->primary_keys = array('user_id');
        parent::__construct();
        $this->CI = & get_instance();
    }
   public function uploadpdf($listid, $user_name, $user_id, $file_name)
   {
   	$qf="SELECT * FROM listmaster WHERE list_id='".$listid."'";
   	$resq=$this->db->query($qf);
   	$querylistname=$resq->result_array();
   	$list_name=$querylistname[0]['list_name'];

   	$qry=$this->db->query("INSERT INTO list_pdf(list_id,list_name,user_name, user_id,pdf_name) VALUES ('".$listid."','".$list_name."','".$user_name."','".$user_id."','".$file_name."')");

   }
    public function mailnotification($from_name,$to_id,$listid, $deadline)
    {
    	$med=$this->db->query("SELECT DISTINCT email, user_name FROM users WHERE user_id='".$to_id."'");
    	$qres=$med->result_array();
    	//$to=$qres[0]['email'];
    	$to="akumar@eliglobal.com";
    	$to_name=$qres[0]['user_name'];

    	$med=$this->db->query("SELECT DISTINCT list_name FROM listmaster WHERE list_id='".$listid."'");
    	$qres=$med->result_array();
    	$listname=$qres[0]['list_name'];

    	$subject = "New List Assigned";

    	$message ="Hello ".$to_name.",<br/> A new list ".$listname." is assigned to you by ".$from_name." with deadline ".$deadline;

    	// Always set content-type when sending HTML email
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

		// More headers
		$headers .= 'From: <'.$from_name.'>' . "\r\n";


		mail($to,$subject,$message,$headers);
    }
	//record storing in db
	public function record_in_db($fname,$fext,$lid)
	{
		$qry="INSERT INTO mkt_lists (mkt_rch_id,list_id) VALUES('".$_SESSION['ses']['user_id']."','$lid')";
		$this->db->query($qry);
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
			$fields=array_slice($arr,0,14);
			for($i=0;$i<=14;$i++)
			{
				$fields[$i]=strtolower($fields[$i]);
			}
			if(in_array("email",$fields) && in_array("firstname",$fields) && in_array("middlename",$fields) && in_array("lastname",$fields) && in_array("title",$fields) && in_array("company",$fields) && in_array("department",$fields) && in_array("address1",$fields) && in_array("address2",$fields) && in_array("city",$fields) && in_array("state",$fields) && in_array("zipcode",$fields) && in_array("phone",$fields) && in_array("fax",$fields))
			{
				for($j=14;$j<count($arr);$j=$j+15)
				{
					list($fname,$mname,$lname,$title,$company,$department,$addr1,$addr2,$city,$state,$zipcode,$phone,$fax,$email)=[$arr[$j+1],$arr[$j+2],$arr[$j+3],$arr[$j+4],$arr[$j+5],$arr[$j+6],$arr[$j+7],$arr[$j+8],$arr[$j+9],$arr[$j+10],$arr[$j+11],$arr[$j+12],$arr[$j+13],$arr[$j+14]];
					//slug left

					if(!(empty($email)))
					{
						$sqlquery=$this->db->query("SELECT * FROM recordmaster WHERE email='".$email."'");
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
							$q2="UPDATE recordmaster SET ".$str.",record_status='modified',vertical_id='".$_SESSION['ses_vid']."',list_id='".$lid."' where email='".$email."'";
						}
						else
						{
							$date=date('Y-m-d H:i:s');
							$q2="INSERT INTO recordmaster (firstname,middlename,lastname,title,company,department,address1,address2,city,state,zipcode,phone,fax,email,record_status,vertical_id,list_id,record_upload_date) VALUES('$fname','$mname','$lname','$title','$company','$department','$addr1','$addr2','$city','$state','$zipcode','$phone','$fax','$email','new','".$_SESSION['ses_vid']."','".$lid."','$date')";
						}
						$res2=$this->db->query($q2);
					}
				}
			}
			else return "Fields Not Matching";
		}
	}

	/*public function validate_permissions($value)
	{
		$arr=$value;
		//$query=$this->db->where('module_name', $Value['module_name']);
		$query=$this->db->query("select * from permissions");
		$row = $query->row();
		$val=$query->num_rows();

	}*/

	public function sanitizeuser($firstname=NULL,$lastname=NULL,$username=NULL,$email=NULL,$password=NULL)
	{
		$arr=array();
		//firstname Treatment
		if($firstname!=NULL)
		{
			$firstname = strval($firstname);
			$firstname = trim( htmlentities( strip_tags( $firstname,"," ) ) );
			if( get_magic_quotes_gpc() )
				$firstname = stripslashes( $firstname );
			$firstname = strip_tags($firstname);
			$firstname = preg_replace("/[^a-zA-Z0-9\s]/", "_", $firstname);
			$firstname = preg_replace("/[^a-z0-9]+/i", "_", $firstname);
			$firstname=strtolower(preg_replace('/\s\s+/','_', $firstname));
			preg_replace(' ','_',$firstname);
			$firstname = mysql_real_escape_string( $firstname );
			$firstname=rtrim($firstname,"_");
			$firstname=ltrim($firstname,"_");
			$arr['firstname']=$firstname;
		}

		//lastname Treatment
		if($lastname!=NULL)
		{
			$lastname = strval($lastname);
			$lastname = trim( htmlentities( strip_tags( $lastname,"," ) ) );
			if( get_magic_quotes_gpc() )
				$lastname = stripslashes( $lastname );
			$lastname = strip_tags($lastname);
			$lastname = preg_replace("/[^a-zA-Z0-9\s]/", "_", $lastname);
			$lastname = preg_replace("/[^a-z0-9]+/i", "_", $lastname);
			$lastname=strtolower(preg_replace('/\s\s+/','_', $lastname));
			preg_replace(' ','_',$lastname);
			$lastname = mysql_real_escape_string( $lastname );
			$lastname=rtrim($lastname,"_");
			$lastname=ltrim($lastname,"_");
			$arr['lastname']=$lastname;
		}

		//username Treatment
		if($username!=NULL)
		{
			$username = strval($username);
			$username = trim( htmlentities( strip_tags( $username,"," ) ) );
			if( get_magic_quotes_gpc() )
				$username = stripslashes( $username );
			$username = strip_tags($username);
			preg_replace('~[^\p{L}\p{N}]++~u','',$username);
			preg_replace('/\W*/', '', $username);
			$username = preg_replace("/[^a-zA-Z0-9\s]/", "_", $username);
			$username = preg_replace("/[^a-z0-9]+/i", "_", $username);
			$username=strtolower(preg_replace('/\s\s+/','_', $username));
			preg_replace(' ','_',$username);
			$username = mysql_real_escape_string( $username );
			$username=rtrim($username,"_");
			$username=ltrim($username,"_");
			$arr['username']=$username;
		}

		//Email Treatment
		if($email!=NULL)
		{
			$email = strval($email);
			$email=strtolower(preg_replace('/[^a-z0-9+_.@-]/i','',$email));
			$arr['email']=$email;
		}

		//password Treatment (No Spaces)
		if($password!=NULL)
		{
			$password = strval($password);
			$password = strip_tags($password);
			$password=strtolower(preg_replace('/\s\s+/','', $password));
			$arr['password']=$password;
		}

		return $arr;
	}
	public function sanitizename($name)
	{
		$name = strval($name);
		$name = trim( htmlentities( strip_tags( $name,"," ) ) );
    	if( get_magic_quotes_gpc() )
			$name = stripslashes( $name );

		$name = strip_tags($name);
		$name = preg_replace("/[^a-zA-Z0-9\s]/", "_", $name);
		$name = preg_replace("/[^a-z0-9]+/i", "_", $name);
		$name=strtolower(preg_replace('/\s\s+/','_', $name));
		preg_replace(' ','_',$name);
		$name = mysql_real_escape_string( $name );
		$name=rtrim($name,"_");
		$name=ltrim($name,"_");

		return $name;
	}
	public function assigntl($tlid, $verticalid, $listid, $deadline)
	{
		$asgnby=$_SESSION['ses']['user_name'];
		/*$qu="SELECT user_id FROM users WHERE user_name='".$tlname."'";
		$res=$this->db->query($qu);
		$var=$res->result_array();
		$tlid=$var[0]['user_id'];*/
		$query=$this->db->query("SELECT * from tl_lists WHERE list_id='".$listid."' AND vertical_id='".$verticalid."'");
		$row = $query->row();

		if($query->num_rows() == 1){
			$q="UPDATE tl_lists SET tl_id='".$tlid."', assigned_by='".$asgnby."', deadline='".$deadline."' WHERE list_id='".$listid."' and vertical_id='".$verticalid."'";
			$qx="UPDATE list_pdf SET tl_id='".$tlid."' WHERE list_id='".$listid."'";
		}
		else {
			$q="INSERT INTO tl_lists (tl_id, list_id, vertical_id, assigned_by, deadline) values ('".$tlid."', '".$listid."', '".$verticalid."', '".$asgnby."','".$deadline."' )";

		}
		$res=$this->db->query($q);

		$query1=$this->db->query("SELECT * FROM list_pdf where list_id='".$listid."'");
		$row1 = $query1->row();
		if($query1->num_rows() == 1){
			$qx="UPDATE list_pdf SET tl_id='".$tlid."' WHERE list_id='".$listid."'";
		}
	}
}