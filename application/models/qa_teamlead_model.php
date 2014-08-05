<?php

class Qa_teamlead_model extends MY_Model
{
	public $table = "users";
	private $CI;

	public function __construct()
	{
        $this->primary_keys = array('user_id');
        parent::__construct();
        $this->CI = & get_instance();
    }

	public function get_interns_count($tlid)
	{
		$query=$this->db->query("SELECT * FROM tl_interns WHERE tl_id='".$tlid."'");
		$count=$query->num_rows();
		return $count;
	}

	public function get_interns($tlid,$per_pg,$offset)
	{
		$query=$this->db->query("SELECT intern_id FROM tl_interns WHERE tl_id='".$tlid."'");
		$res_arr=$query->result_array();
		$show_arr=array_slice($res_arr,$offset,$per_pg);
			return $show_arr;
	}

	public function get_lists_count($tlid)
	{
		$query=$this->db->query("SELECT * FROM tl_lists WHERE tl_id='".$tlid."'");
		$count=$query->num_rows();
		return $count;
	}

	public function get_lists($tlid,$per_pg,$offset)
	{
		$q="select l.list_name,t.list_id, t.assigned_by, t.date_assigned, t.status from listmaster l, tl_lists t where l.list_id=t.list_id AND t.tl_id='".$tlid."' ";
		$query=$this->db->query($q);
		$res_arr=$query->result_array();
		$show_arr=array_slice($res_arr,$offset,$per_pg);
			return $show_arr;
	}
	public function insertstatus($id,$statusval)
	{
		$queryrun=$this->db->query("UPDATE tl_lists SET status='".$statusval."' WHERE list_id='".$id."'");
	}
	public function assignintern($vid,$listid,$intername,$startrec,$endrec,$tlid)
	{
		$internsql=$this->db->query("SELECT user_id FROM users WHERE user_name='".$intername."'");
		$tmpvar=$internsql->result_array();
		$internid=$tmpvar[0]['user_id'];

		$recordarr=$this->db->query("SELECT * from recordmaster WHERE list_id='".$listid."' AND vertical_id='".$vid."' order by record_id ASC");
		$tmpvar1=$recordarr->result_array();
		$x=0;
		foreach ($tmpvar1 as $value) {
			$arrayrecord[$x++]=$value['record_id'];
		}
		$qmkt=$this->db->query("SELECT * FROM mkt_lists WHERE list_id='".$listid."'");
		$mktrchr=$qmkt->result_array();
		$mkt_id=$mktrchr[0]['mkt_rch_id'];
		//Inserting Assigned Records into Record_Operations against intern Id...
		$startrec=(int)$startrec;
		$endrec=(int)$endrec;
		$date=date("Y-m-d H:i:s");
		$resqry=$this->db->query("select * from listmaster where list_id='".$listid."'");
		$resarr=$resqry->result_array();
		$list_name=$resarr[0]['list_name'];
		for ($i=$startrec; $i <= $endrec; $i++) {
			$y=$i;
			$qx="INSERT INTO record_operations(vertical_id, list_id, assigned_intern_id, record_id, uploaded_by_id, assigned_by_tl_id, record_date_assigned) VALUES ('$vid','$listid','$internid','$arrayrecord[$y]','$mkt_id','$tlid', '$date')";
			$sqlquery=$this->db->query($qx);
			}
		$qwe=$this->db->query("INSERT INTO intern_lists( list_id,list_name, intern_name, start_rec, end_rec, intern_id) VALUES('$listid','$list_name','$intername','$startrec','$endrec','$internid')");

	}
	public function updateassignment($newinternid, $listname, $tlid, $oldinternname)
	{
		//update intern_lists
		$queryzero=$this->db->query("SELECT DISTINCT user_name from users WHERE user_id='".$newinternid."'");
		$resz=$queryzero->result_array();
		$newintern=$resz[0]['user_name'];
		$queryone=$this->db->query("UPDATE intern_lists SET intern_name='".$newintern."', intern_id='".$newinternid."' WHERE intern_name='".$oldinternname."' AND list_name='".$listname."'");

		//update record operations
		$querytwo=$this->db->query("SELECT DISTINCT vertical_id from tl_lists WHERE tl_id='".$tlid."'");
		$restwo=$querytwo->result_array();
		$verticalid=$restwo[0]['vertical_id'];
		$queryfour=$this->db->query("SELECT DISTINCT list_id FROM listmaster WHERE list_name='".$listname."'");
		$resfour=$queryfour->result_array();
		$listid=$resfour[0]['list_id'];
		$querythree=$this->db->query("UPDATE record_operations SET assigned_intern_id='".$newinternid."' WHERE vertical_id='".$verticalid."' AND list_id='".$listid."' AND assigned_by_tl_id='".$tlid."'");

	}
}