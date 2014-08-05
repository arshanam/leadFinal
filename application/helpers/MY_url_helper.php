<?php

/**
 * Current Secure URL
 *
 * Returns the full URL (including segments) of the page where this
 * function is placed... this value is encrypted before being returned
 *
 * @access	public
 * @return	string
 */
function current_secure_url() {
    $CI = & get_instance();
    return $CI->config->item('redirect_base_url') . uri_string();

    $secure_url = $CI->tools->simple_encrypt_string($CI->config->item('redirect_base_url') . uri_string());
    return $secure_url;
}

function current_app_url() {
	$CI = & get_instance();
    $url = current_base_url();
	$url .= $CI->uri->rsegments[1] . '/' . $CI->uri->rsegments[2];

	return $url;
}

function current_base_url(){
    /*$url = is_ssl() ? 'https://' : 'http://';
    $url .= get_instance()->input->server('HTTP_HOST');
    return $url.'/';*/
	$CI = & get_instance();
    return $CI->config->slash_item('base_url');
}

function forum_url(){
	$CI = & get_instance();
    return $CI->config->slash_item('forum_url');
}

function secure_base_url() {
    $CI = & get_instance();
    return $CI->config->slash_item('secure_base_url');
}

function admin_base_url() {
    $CI = & get_instance();
    return $CI->config->slash_item('admin_base_url');
}

function secure_admin_base_url() {
    $CI = & get_instance();
    return $CI->config->slash_item('secure_admin_base_url');
}

function current_protocol() {
    $CI = & get_instance();
    return $CI->config->item('current_protocol');
}

function openx_url() {
    $CI = & get_instance();
    return $CI->config->slash_item('openx_url');
}

function images_url() {
    $CI = & get_instance();
    return $CI->config->slash_item('images_url');
}

function static_files_url() {
    $CI = & get_instance();
    return $CI->config->slash_item('static_files_url');
}

function static_cdn_url() {
    $CI = & get_instance();
    return $CI->config->slash_item('static_cdn_url');
}

/**
 * Performs a header redirect to the given location
 * This method was overridden to deal with the multiple base_urls used by our application.
 *
 * @param string $uri the URL
 * @param string $method (location or redirect)
 * @param int $http_response_code
 */
function redirect($uri = '', $method = 'location', $http_response_code = 302) {
    if (strpos($uri, '://') === FALSE) {
        $url = is_ssl() ? 'https://' : 'http://';
        $url .= get_instance()->input->server('HTTP_HOST');
        $uri = $url . $uri;
    }

    $ci =& get_instance();
    if($ci->config->item('enable_redirects')) {
        switch ($method) {
            case 'refresh':
                header("Refresh:0;url=" . $uri);
                break;
            default:
                header("Location: " . $uri, TRUE, $http_response_code);
                break;
        }
        exit;
    } else {
        echo 'redirect disabled - destination would have been <a href="'.$uri.'">'.$uri.'</a>';
    }
}

/**
 * Helps to determine if the current request was made over SSL.
 *
 * @return boolean
 */
function is_ssl() {
    return (isset($_SERVER['HTTP_OFFLOADSSL']) && $_SERVER['HTTP_OFFLOADSSL']) ? TRUE : FALSE;
}

/* temporarly enable global $_GET array*/

function enable_global_get() {
	parse_str(substr(strrchr($_SERVER['REQUEST_URI'],'?'),1),$_GET);
	return FALSE;
}