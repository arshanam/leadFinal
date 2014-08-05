<?php
/*
 * Provides helper functions dealing with users
 */

/**
 * Checks if the current user is logged in
 *
 * @return bool
 */
function get_seo_data($page_url)
		{
			if(!empty($page_url))
				{

					$CI =& get_instance();
					$sql="select * from seo_pages where url='$page_url'";
					$query=$CI->db->query($sql);
					$result=$query->result_array();

					if(is_array($result) && count($result)>0 && isset($result[0]))
						{
								return $result[0];
						}
				}
			return false;
		}