<?php
/**
* This helper helps to provide dropdown contents
*/
public function roles_drop()
{
	$q2="select role_name from roles";
	$CI = get_instance();
	$res2=$CI->db->query($q2);
	$row2=$res2->result_array();
	$j=0;
	for($i=0;$i<($res2->num_rows());$i++)
	{

	$arrval1[$j++]=$row2[$i]['role_name'];
	}
	return $arrval1;
}

public function departments_drop()
{
	$CI = get_instance();
	$q1="select department_name from department";
	$res1=$CI->db->query($q1);
	$row=$res1->result_array();
	$j=0;
	for($i=0;$i<($res1->num_rows());$i++)
	{

	$arrval[$j++]=$row[$i]['department_name'];
	}
	return $arrval;
}
public function users_drop()
{
	$CI = get_instance();
	$q1="select user_name from users";
	$res1=$CI->db->query($q1);
	$row=$res1->result_array();
	$j=0;
	for($i=0;$i<($res1->num_rows());$i++)
	{

	$arrval[$j++]=$row[$i]['user_name'];
	}
	return $arrval;

}
public function verticals_drop()
{
	$CI = get_instance();
	$q1="select vertical_name from verticals";
	$res1=$CI->db->query($q1);
	$row=$res1->result_array();
	$j=0;
	for($i=0;$i<($res1->num_rows());$i++)
	{

	$arrval[$j++]=$row[$i]['vertical_name'];
	}
	return $arrval;

}
public function permissions_drop()
{
	$CI = get_instance();
	$q1="select permission_name from permissions";
	$res1=$CI->db->query($q1);
	$row=$res1->result_array();
	$j=0;
	for($i=0;$i<($res1->num_rows());$i++)
	{

	$arrval[$j++]=$row[$i]['permission_name'];
	}
	return $arrval;
}

?>