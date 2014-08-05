<?php
/*
 * Provides helper functions dealing with the application layout
 */

/**
 * Provides functionality to populate the HTML title tag from within a view.
 *
 * @param string $title
 */
function title($title = '') {
    $current_controller =& get_instance();
    $current_controller->setData('title_tag', $title);
}

/**
 * Provides functionality to retrieve a view from within a template
 *
 * @param string $view The view to retrieve
 * @param array $data
 * @param bool $cache
 * @param string $cache_key
 * @param int $cache_interval
 * @param bool $force Flag to force the page to be loaded from the view and bypass the cache
 * @return string
 */
function get_view($view = '', $data = array(), $cache = FALSE, $cache_key = '', $cache_interval = 86400, $force = FALSE) {
    $result = '';

    if ( $view != '' ) {
        $current_controller =& get_instance();
        $result = $current_controller->get_view(trim($view), TRUE, $data, $cache, $cache_key, $cache_interval, $force);
    }

    return $result;
}

/**
 * Provides functionality to retrieve a view from within a template
 *
 * @param string $view The view to retrieve
 * @param array $data
 * @return string
 */
function get_sub_view($view = '', $data = array()) {
    $result = '';

    if ( $view != '' ) {
        $current_controller =& get_instance();
        $result = $current_controller->get_sub_view(trim($view), $data);
    }

    return $result;
}


/**
 * Generate breadcrumbs
 *
 * @param array $path Breadcrumb path, in the form of array('label'=>'', 'url'=>'')
 * @return string
 */
function breadcrumbs($path, $cache_string = '') {
    if(count($path)) {
		$count = 0;
		foreach($path as $key=>$value) {
			if(strlen($value['title']) > 60) {
				$path[$count]['title'] = substr($value['title'],0,60)."&hellip;";
			}
			$count++;
		}
        return get_view('modules/global/breadcrumbs', array('breadcrumbs' => $path), TRUE, 'breadcrumb_' . md5(serialize($path)));
    }
}

/**
 * Generates a user badge
 *
 * @param array $user_data
 * @return string
 */
function badge($user_data) {
    $CI =& get_instance();

    if(!is_array($user_data)) {
        $CI->load->library('User_profile');
        $user_data = $CI->user_profile->build_user_profile($user_data);
    }

    $CI->load->helper('Image');
    return get_view('modules/users/badge', array('user_data' => $user_data), TRUE, 'user_badge_'.$user_data['user_id']);
}