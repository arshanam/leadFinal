<?

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * This class retrives information from the Google Analytics API using the Google Analytics library. It also includes methods to render data using Google Chart Tools
 *
 * @author     Ben Marshall
 */
class Gapi {

    private $CI;
    // Account information for the Google Analytics account
    public $gapi_config = array(
        'email' => 'bmarshall.0511@gmail.com',
        'passwd' => '215475id'
    );
    public $profile = 'ga:10513548';

    function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->library('GoogleAnalytics', $this->gapi_config);
        $this->CI->googleanalytics->setProfile($this->profile);
    }

    function get_site_visits($start_date, $end_date) {
        $this->CI->googleanalytics->setDateRange($start_date, $end_date);
        $site_visits = $this->CI->googleanalytics->getReport(
                        array('dimensions' => urlencode('ga:date'),
                            'metrics' => urlencode('ga:visits'),
                            'sort' => '-ga:date'
                        )
        );
        return $site_visits;
    }

}
