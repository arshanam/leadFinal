<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Custom controller class that extends CI's controller class.
 *
 * MY_Controller is used for ‘on-every-page-load’ activities like:
 *  o calling models to retrieve database information,
 *  o setting / retrieving Session data,
 *  o applying an authentication layer - often tied in with Session data,
 *  o generating common view partials (headers, footers, menus).
 * @author Masa Gumiro
 * @author Kyle Gifford
 */
class MY_Controller extends Controller {

    /**
     * Contains properties related to the current user
     * @var array
     */
    protected $current_user = array();

    /**
     * Contains data that will be passed to the view layer
     * @var array
     */
    protected $data = array();

    /**
     * Contains permissions for accessing the action
     * @var array
     */
    protected $permissions = array();

    /**
     * If a permission is not met, then the permissions hook may over-write the method
     *  being called by overriding the user's action
     * @var string
     */
    private $overridden_user_action = '';

    /**
     * System notifications (success, notice, and error)
     * @var array
     */
    private $notifications;

    /**
     * The path to a file that should be used as the layout template
     * @var String
     */
    public $layout = '';

    /**
     * The currently loaded user storefront model
     * @var Marketplace_storefronts
     */
    protected $_currentUserStorefront = NULL;

    /**
     * The currently loaded shopping cart for the current user.
     * @var Marketplace_shopping_cart
     */
    protected $_currentUserShoppingCart = NULL;

    /**
     * Holds a cached copy of the application_enum_labels after retrieving them from memcache.
     * @var mixed NULL|array
     */
    protected $_applicationEnumLabels = NULL;
    protected $_applicationEnumLabelsByValue = NULL;

    /**
     * Class constructor
     */
    function __construct() {
        parent::__construct();

        $empty_notification_array = array(
            'success' => array(),
            'notice' => array(),
            'error' => array()
        );

        // Restore any notifications from flashdata (persistant messages)
        $this->notifications = $this->session->flashdata('user_notifications') ? $this->session->flashdata('user_notifications') : $empty_notification_array;

        // set the default layout
        $this->layout = 'layouts/one_column';

        // Load the current user settings
        $this->load_user_profile();

		if($this->current_user == false) {
			//$this->checkAndLoadSessionFromRememberMe();
		}

        // Load default view data
        $this->data['noindex'] = FALSE;
        $this->data['nofollow'] = FALSE;
        $this->data['css'] = array();
        $this->data['js'] = array();
        $this->data['meta_title'] = "";
        $this->data['meta_description'] = "";
        $this->data['meta_keywords'] = "";
        $this->data['login_message'] = "";
        $this->data['topnavcurve'] = 'bottom-curve';
        $this->data['meta'] = array(
            'robots' => array('name' => 'robots', 'content' => 'index,follow'),
            'description' => array('name' => 'description', 'content' => ''),
            'keywords' => array('name' => 'keywords', 'content' => ''),
            'rating' => array('name' => 'rating', 'content' => 'general'),
            'distribution' => array('name' => 'distribution', 'content' => 'global'),
            'resource-type' => array('name' => 'resource-type', 'content' => 'document'),
            'content-type' => array('name' => 'content-type', 'content' => 'text/html; charset=utf-8', 'type' => 'equiv'),
        );
        $this->data['title_tag'] = "";

        // shopping cart view data
        $this->data['shopping_cart_total'] = $this->session->userdata('shopping_cart_total') ? $this->session->userdata('shopping_cart_total') : 0.00;
        $this->data['shopping_cart_item_count'] = $this->session->userdata('shopping_cart_item_count') ? $this->session->userdata('shopping_cart_item_count') : 0;

        $this->output->enable_profiler($this->config->item('enable_profiler'));
    }

    /**
     * Used by CodeIgnitor to determine whether to remap the user's requested action
     * Checks to see if the user's action has been overridden by the permissions hook
     *
     * @param string $method The method name to run in place of the user's selected action
     */
    function _remap($method) {
        // if an overridden action has been specified, run that method instead
        if ($this->overridden_user_action) {
            $method = $this->overridden_user_action;
        }

        if (method_exists($this, $method)) {
            call_user_func_array(array($this, $method), array_slice($this->uri->rsegments, 2));
        } else {
            show_404();
        }
    }

    /**
     * Accessor for the current_user array
     *
     * @return array The information about the current user
     */
    public function get_current_user() {
        return $this->current_user;
    }

    public function set_cache_currentUserStorefront($storefront){
        $this->_currentUserStorefront = $storefront;
    }

    public function get_cache_currentUserStorefront(){
        return $this->_currentUserStorefront;
    }

    public function set_cache_currentUserShoppingCart($shoppingCart){
        $this->_currentUserShoppingCart = $shoppingCart;
    }

    public function get_cache_currentUserShoppingCart(){
        return $this->_currentUserShoppingCart;
    }

    public function set_cache_applicationEnumLabels($enum_labels){
        $this->_applicationEnumLabels = $enum_labels;
    }

    public function get_cache_applicationEnumLabels(){
        return $this->_applicationEnumLabels;
    }

    public function set_cache_applicationEnumLabelsByValue($applicationEnumLabelsByValue){
        $this->_applicationEnumLabelsByValue = $applicationEnumLabelsByValue;
    }

    public function get_cache_applicationEnumLabelsByValue(){
        return $this->_applicationEnumLabelsByValue;
    }

    /**
     * Accessor for the permissions array
     *
     * @return array The permissions for the current controller
     */
    public function get_permissions() {
		return $this->permissions;
    }

    /**
     *
     */
    public function load_user_profile() {
        $this->current_user = $this->user_profile->get_user_profile();
    }

    /**
     * Mutator to override the user's currently selected action
     *
     * @param array $method The method name that will be run
     */
    public function override_user_action($method) {
        $this->overridden_user_action = $method;
    }

    /**
     * Internal function for adding new notification, including managing persistance
     *
     * @param array $notifications The notifications to be added
     * @param string $type The type of notification (either success, notice, or error)
     * @param  $persist Whether to make the notification available after the next page load
     */
    private function add_notifications($notifications, $type, $persist = FALSE) {
        if (!is_array($notifications)) {
            $notifications = array((string) $notifications);
        }

        $this->notifications[$type] = array_merge($this->notifications[$type], $notifications);
        if ($persist) {
            $persistant_notifications = $this->session->flashdata('user_notifications');
            if(!is_array($persistant_notifications)) {
                $persistant_notifications = array(
                    'success' => array(),
                    'notice' => array(),
                    'error' => array()
                );
            }
            $persistant_notifications[$type] = array_merge($persistant_notifications[$type], $notifications);
            $this->session->set_flashdata('user_notifications', $persistant_notifications);
        }
    }

    /**
     * Adds success notifications
     * @param array $notifications The success notification to be passed to the user
     * @param bool $persist Whether to make the notification available after the next page load
     */
    public function add_success($notifications, $persist = FALSE) {
        $this->add_notifications($notifications, 'success', $persist);
    }

    /**
     * Adds notice notifications
     * @param array $notifications The notice notification to be passed to the user
     * @param bool $persist Whether to make the notification available after the next page load
     */
    public function add_notice($notifications, $persist = FALSE) {
        $this->add_notifications($notifications, 'notice', $persist);
    }

    /**
     * Adds error notifications
     * @param array $notifications The error notification to be passed to the user
     * @param bool $persist Whether to make the notification available after the next page load
     */
    public function add_error($notifications, $persist = FALSE) {
        $this->add_notifications($notifications, 'error', $persist);
    }

    /*
     * Wrapper for the load->view function, in order to pass the current_user array to the view data
     *
     * @param string $path The path to the view to be loaded
     * @param bool $return Whether to return the results of the view or not
     * @param array $data Unique data to pass to this view
     * @param bool $cache Whether to cache this view
     * @param string $cache_key If set not an empty string, will over-ride the default cache key
     * @param int $cache_interval The expiration time, in seconds, that this view will expire from cache
     * @param bool $force Flag to force the page to be loaded from the view and bypass the cache
     * @return mixed A rendered view
     */
    public function get_view($path, $return = FALSE, $data = array(), $cache = FALSE, $cache_key = '', $cache_interval = 86400, $force = FALSE) {
        // if the view is getting cached, retrieve it that way
        if($this->config->item('view_caching') && $cache) {
            // determine the cache key
            $cache_key = 'view_' . ($cache_key ? $cache_key : uri_string() . '_' . $path);

            // if the view already exists, retrieve it
            $view_data = $this->tools->shared_cache_retrieve($cache_key);
            if($force || $view_data === FALSE) {
                // look it up through CI and store it in cache
                $this->data['current_user'] = $this->current_user;
                $this->data['notifications'] = $this->notifications;

                $data = array_merge($this->data, $data);
                $view_data = $this->load->view($path, $data, TRUE);
                $this->tools->shared_cache_store($cache_key, $view_data, $cache_interval);
            }

            // either return the string, or output it the "CI way"
            if($return) {
                return $view_data;
            } else {
                // PHP 4 requires that we use a global
                global $OUT;
                $OUT->append_output($view_data);
            }
        } else {
            $this->data['current_user'] = $this->current_user;
            $this->data['notifications'] = $this->notifications;

            $data = array_merge($this->data, $data);
            return $this->load->view($path, $data, $return);
        }
    }

	/*
     * Wrapper for the load->view function, in order to pass the array to the view data
     *
     * @param string $path The path to the view to be loaded
     * @param array $data Unique data to pass to this view
     * @return mixed A rendered view
     */
    public function get_sub_view($path, $data = array(), $return=FALSE) {
		$this->data['notifications'] = $this->notifications;
        return $this->load->view($path, $data,$return);
    }

    /**
     * Checks if a cached view exists
     *
     * @param string $path
     * @param string $cache_key
     * @return bool
     */
    public function cached_view_exists($path, $cache_key = '') {
        // check if view caching is enabled
        if($this->config->item('view_caching')) {
            // determine the cache key
            $cache_key = 'view_' . ($cache_key ? $cache_key : uri_string() . '_' . $path);
            // if the view already exists, retrieve it
            return ($this->tools->shared_cache_retrieve($cache_key) !== FALSE);
        } else {
            return FALSE;
        }
    }

    /**
     * Simple controller action to display the user login form (usually set by the permissions hook)
     */
    protected function user_login_form() {
        $this->layout = 'layouts/one_column';
		$this->session->set_userdata('sess_redirect_url',current_app_url());

		redirect('/login/');
        //$this->render('modules/login/index');
    }

    /**
     * Simple controller action to display the admin login form (usually set by the permissions hook)
     */
    protected function admin_login_form() {
        // CSS
        $this->data['css'][] = 'admin/ui/ui.login.css';

        // JS
        $this->data['js'][] = 'admin/admintasia/ui/ui.tabs.js';

        $this->layout = 'layouts/admin_supercoder';

        $this->render('modules/admin/login/index');
    }

    /**
     * Helps to prepare the layout template for rendering.
     *
     * This method will retrieve the layout template, populate it with the
     * necessary sub-templates and then render the specified template within
     * the 'body' section of the layout.
     *
     * @param <string> $template path to a view used to populate the layout body
     */
    protected function render($template) {
        $this->get_view($this->layout, FALSE, array(
                'body'=>$this->get_view($template, TRUE)
            ));
    }

    /**
     * Appends the specified key => value to the data array that is eventually
     * passed to the view (because $this->data is protected).
     *
     * Warning!
     * If the given $key exists and is an array, this method will append $value
     * to the existing array (an attempt to support adding js includes on the fly).
     *
     * @param string $key
     * @param mixed $value
     */
    public function setData($key, $value) {
        if (isset($this->data[$key]) && is_array($this->data[$key])){
            if ($value != ''){
                $this->data[$key][basename($value)] = $value;
            }
        } else {
            $this->data[$key] = $value;
        }
    }

    /**
     * Gets the search results to be used in a jqGrid
     * @return string JSON array of results used for jqGrid
     */
    public function get_search_results() {
        // get data from POST
        $include_filters = (bool)$this->input->post('withFilters');
        $applied_filters = $this->input->post('filters') ? (array)$this->input->post('filters') : array();
        $search_id = $this->input->post('search_id');
        $term_match_mode = $this->input->post('term_match_mode') ? $this->input->post('term_match_mode') : 'all';

        // connect to Sphinx search
        $this->load->library('SphinxClient');
        $s = $this->sphinxclient;
        $s->setServer($this->config->item('sphinx_server'), $this->config->item('sphinx_server_port'));
        $s->setArrayResult(TRUE);

        // load the search library
        $this->load->library('search');

        try {
            // get available filters
            $available_filters = $this->search->get_available_search_filters($search_id);

            // get default applied search filters
            $default_applied_search_filters = $this->search->get_default_applied_search_filters($search_id);

            // get max search results
            $max_search_results = $this->search->get_maximum_search_results($search_id);
        } catch(Exception $e) {
            // @todo log the exception
            show_404();
            return;
        }

        // loop through any of the applied (POST) filters and convert them to an array
        foreach($applied_filters as $attribute => $values) {
            $applied_filters[$attribute] = explode('|', $values);
        }

        // merge the two together to get the list of all filters to be applied
        $applied_search_filters = array_merge_recursive($default_applied_search_filters, $applied_filters);

        // set the applied search filters in the session
        $search_fields_to_update = array(
            'applied_filters' => $applied_search_filters
        );

        // extract any search terms to be applied
        if($applied_search_filters && isset($applied_search_filters['term'])) {
            $term = implode(' ', $applied_search_filters['term']);
        } else {
            $term = '';
        }

        // if a search term was specified, set the match mode to ALL
        if($term) {
            if($term_match_mode && $term_match_mode == 'any') {
                $s->setMatchMode(SPH_MATCH_ANY);
            } else {
                $s->setMatchMode(SPH_MATCH_ALL);
            }

            // for numbers seperated by a forward slash, convert them to a hyphen for sphinx
            $term = preg_replace("/([0-9]+)\/([0-9]+)/", '$1-$2', $term);
            $term = str_replace("/", " ", $term);
        }

        // for all other filters besides the term, apply them to all queries as filters
        foreach($applied_search_filters as $attribute => $value) {
            if($attribute != 'term') {
                if(isset($value['type'])) {
                    switch ($value['type']) {
                        case 'Range':
                            $s->setFilterRange($attribute, $value['min'], $value['max']);
                            break;
                    }
                } elseif(isset($available_filters[$attribute]) && isset($available_filters[$attribute]['type'])) {
                    switch ($available_filters[$attribute]['type']) {
                        case 'Range':
                            $s->setFilterRange($attribute, $available_filters[$attribute]['min'], $available_filters[$attribute]['max']);
                            break;
                        case 'map':
                            if (is_array($available_filters[$attribute]['map'])){
                                foreach($value as $map_key){
                                    if (isset($available_filters[$attribute]['map'][$map_key])){
                                        $s->setFilter($available_filters[$attribute]['map'][$map_key][0], $available_filters[$attribute]['map'][$map_key][1]);
                                    }
                                }
                            }
                            break;
                    }
                } else {
                    $s->setFilter($attribute, $value);
                }

                // if there are additional attributes that should be applied when this attribute is in use, apply them
                if(isset($available_filters[$attribute]) && isset($available_filters[$attribute]['additional_filters_if_applied'])) {
                    foreach($available_filters[$attribute]['additional_filters_if_applied'] as $additional_attribute => $additional_value) {
                        $s->setFilter($additional_attribute, $additional_value);
                    }
                }
            }
        }

        // Apply sorting
        if($this->input->post('sidx') && $this->input->post('sord')) {
            $s->setSortMode(($this->input->post('sord') == 'desc' ? SPH_SORT_ATTR_DESC : SPH_SORT_ATTR_ASC), $this->input->post('sidx'));
        }

        // for each of the filters, apply a query
        foreach($available_filters as $filter_key => $filter) {
            if(isset($filter['select'])) {
                $s->setSelect($filter['select']);
            }

            // if the filter is a group by, execute that with a hard limit
            if(isset($filter['group_by'])) {
                foreach($filter['group_by'] as $group_by) {
                    if ( isset($group_by['groupsort']) ){
                        $s->setGroupBy($group_by['attribute'], SPH_GROUPBY_ATTR, $group_by['groupsort']);
                    } else {
                        // Apply sorting for the grouped result
                        $group_by_sort = '@group desc';
                        if($filter_key == 'term' && $this->input->post('sidx') && $this->input->post('sord')) {
                            $sort_order = $this->input->post('sord') == 'desc' ? 'desc' : 'asc';
                            $group_by_sort = $this->input->post('sidx') . ' ' . $sort_order;
                        }
                        $s->setGroupBy($group_by['attribute'], SPH_GROUPBY_ATTR, $group_by_sort);
                    }
                }
            }

            if (isset($filter['group_by']) && $filter_key != 'term'){
                $s->setLimits(0, 100, 1000);
            } else {
                // otherwise, set the limit to use the default for pagination
                $s->setLimits((int)($this->input->post('rows') * ($this->input->post('page') - 1)), (int)$this->input->post('rows'), $max_search_results);
            }

            if(isset($filter['group_distinct'])) {
                $s->setGroupDistinct($filter['group_distinct']);
            }

            // assuming index(es) are defined, apply them
            if(isset($filter['index'])) {
                $s->addQuery($term, implode(' ', $filter['index']));
                $s->setSelect('*');
                $s->resetGroupBy();
            }
        }

        // run all the queries and generate a result
        $result = $s->runQueries();
        //print_r($result[0]['matches'][0]);

        // check if we need to reload the filter information
        if($include_filters) {
            $index = 0;
            $facets = array();

            // if there is a post-processor for any of the results to generate the filter list, run that
            foreach($available_filters as $filter_key => $filter) {
                if(isset($filter['processor'])) {
                    $queried_results = array();

                    // if the filter has already been applied, just read in the applied result instead of the group by
                    if(in_array($filter_key, array_keys($applied_search_filters))) {
                        // format the result to a consistant format (in this case, to match Sphinx)
                        foreach($applied_search_filters[$filter_key] as $value) {
                            $queried_results['matches'][] = array(
                                'attrs' => array(
                                    '@groupby' => $value,
                                    '@count' => 0
                                )
                            );
                        }
                    // otherwise, pull the values from the search results
                    } elseif($filter_key != 'term' && isset($result[$index])) {
                        $queried_results = $result[$index];
                    }

                    // assuming results are returned, apply the post-processor function
                    if(!empty($queried_results)) {
                        switch($filter['processor']['type']) {
                            case 'model':
                                $model = new $filter['processor']['model']();
                                $facets[$filter_key] = $model->{$filter['processor']['method']}($queried_results);
                                break;
                            case 'library':
                                $this->load->library($filter['processor']['library']);
                                $facets[$filter_key] = $this->{strtolower($filter['processor']['library'])}->{$filter['processor']['method']}($queried_results);
                                break;
                        }
                    }
                }

                // all done, move on to the next filter
                $index++;
            }

            // set the facets
            $search_fields_to_update['facets'] = $facets;
        }

        // update any search fields that need updating
        $this->search->set_search_fields($search_id, $search_fields_to_update);

        // get the search result counts
        $search_result['page'] = $this->input->post('page');
        $search_result['records'] = isset($result[0]['total_found']) ? $result[0]['total_found'] : 0;
        $search_result['total'] = $this->input->post('rows') ? ceil(min($search_result['records'], $max_search_results) / $this->input->post('rows')) : min($search_result['records'], $max_search_results);

        // format the search results
        $table_schema = $this->search->get_search_table_schema($search_id);
        $rows = array();
        $key_alias = '';
        if(isset($result[0]['matches'])) {
            foreach($result[0]['matches'] as $id => $match) {
                $row_data = array('id' => $match['id']);
                foreach($table_schema as $column) {
                    $key_alias = (isset($column['key_alias'])) ? $column['key_alias'] : $column['name'];

                    if(isset($column['processor'])) {
                        switch($column['processor']['type']) {
                            case 'model':
                                $model = new $column['processor']['model']();
                                $row_data[$column['name']] = htmlentities($model->{$column['processor']['method']}($match), ENT_NOQUOTES);
                                break;
                            case 'library':
                                $this->load->library($column['processor']['library']);
                                $row_data[$column['name']] = htmlentities($this->{$column['processor']['library']}->{$column['processor']['method']}($match), ENT_NOQUOTES);
                                break;
                            case 'helper':
                                $this->load->helper($column['processor']['helper']);
                                $row_data[$column['name']] = htmlentities(call_user_func($column['processor']['method'], $match, $column['name']), ENT_NOQUOTES);
                                break;
                        }
                    } elseif(isset($match['attrs'][$key_alias])) {
                        $row_data[$column['name']] = $match['attrs'][$key_alias];
                    }
                }
                $rows[] = $row_data;
            }
        }

        $search_result['rows'] = $rows;
        echo html_entity_decode(JSON_encode($search_result));
        return;
    }

    /**
     * Gets the search filter HTML
     */
    public function get_search_filter_html() {
        // load the search_id from POST
        $search_id = $this->input->post('search_id');

        // load the search library
        $this->load->library('search');

        try {
            // get available filters
            $filters = $this->search->get_available_search_filters($search_id);

            // get applied filters
            $applied_filters = $this->search->get_applied_search_filters($search_id);

            // get the facets so we can convert them to HTML output
            $facets = $this->search->get_search_facets($search_id);

            $display_filters = array();
            foreach($filters as $filter_key => $filter) {
                if(isset($facets[$filter_key]) && $filter['show_in_filter_list'] && isset($filter['display_name']) && !empty($facets[$filter_key]) && !in_array($filter_key, array_keys($applied_filters))) {
                    $display_filters[$filter_key] = array(
                        'display_name' => $filter['display_name'],
                        'options' => $facets[$filter_key]
                    );
                }
            }

            $this->data['display_filters'] = $display_filters;
            $this->get_view('modules/search/filters');
        } catch(Exception $e) {
            // @todo log the exception
            echo 'An error occurred while loading the search filters from the session.';
        }
    }

    /**
     * Gets the applied filter HTML
     */
    public function get_applied_filter_html() {
        // load the search_id from POST
        $search_id = $this->input->post('search_id');

        // load the search library
        $this->load->library('search');

        try {
            // get default applied search filters
            $default_applied_search_filters = $this->search->get_default_applied_search_filters($search_id);

            // get available filters
            $filters = $this->search->get_available_search_filters($search_id);

            // get applied filters
            $applied_filters = $this->search->get_applied_search_filters($search_id);

            // get the facets so we can convert them to HTML output
            $facets = $this->search->get_search_facets($search_id);

            //
            $display_filters = array();
            foreach($filters as $filter_key => $filter) {
                if(in_array($filter_key, array_keys($applied_filters))) {
                    $display_filters[$filter_key] = array(
                        'display_name' => $filter['display_name'],
                        'options' => $facets[$filter_key],
                        'is_default' => FALSE
                    );
                    if(in_array($filter_key, array_keys($default_applied_search_filters))) {
                        $display_filters[$filter_key]['is_default'] = TRUE;
                        foreach($default_applied_search_filters[$filter_key] as $default_applied_search_filter) {
                            if(isset($display_filters[$filter_key]['options'][$default_applied_search_filter])) {
                                $display_filters[$filter_key]['options'][$default_applied_search_filter]['is_default'] = TRUE;
                            }
                        }
                    }
                }
            };

            $this->data['display_filters'] = $display_filters;
            $this->get_view('modules/search/applied_filters');
        } catch(Exception $e) {
            // @todo log the exception
            echo 'An error occurred while loading the search filters from the session.';
        }
    }

    /**
     * Generates the jqGrid Javascript for the search results page
     *
     * @param int $search_id The search ID that will be passed through POST
     * @param array $passed_grid_options Will be merged in to default grid options
     * @param array $passed_grid_events
     * @params bool $require_login If true, will set the URL to get_logged_in_search_results, which requires a logged in user to access
     * @return string Javascript to be rendered on the search results page
     */
    protected function build_search_results_table($search_id, $passed_grid_options = array(), $passed_grid_events = array(), $require_login = FALSE) {
        // Get the table schema
        $this->load->library('search');
        $table_schema = $this->search->get_search_table_schema($search_id);
        $default_grid_options = array(
            'mtype' => "POST",
            'rowNum' => 10,
            'rowList' => array(10,25,50,100),
            'toppager' => true,
            'viewrecords' => true,
            'gridview' => true,
            'autowidth' => true,
            'shrinkToFit' => true,
            'multiselect' => true,
            'height' => 'auto',
            'postData' => array('search_id' => $search_id),
            'jsonReader' => array('repeatitems' => false, 'id' => 'id'),
            'sortable' => false,
            'history' => array('hashPrefix' => 'sr')
        );

        // Build the grid
        $this->load->library('Jqgrid');
        $g = new jqGridRender();
        $g->dataType = 'local';
        $grid_url = (isset($passed_grid_options['url']) ? $passed_grid_options['url'] : ($require_login ? current_base_url() . 'services/get_logged_in_search_results' : current_base_url() . 'services/get_search_results'));
        $g->setURL($grid_url);
        // Let the grid create the model
        $g->setColModel($table_schema);
        $g->setGridOptions(array_merge($default_grid_options, $passed_grid_options));
        if ( is_array($passed_grid_events) && !empty($passed_grid_events) ){
            foreach($passed_grid_events as $name => $code){
                $g->setGridEvent($name, $code);
            }
        }
        $g->navigator = true;
        $g->history = true;
        $g->setNavOptions('navigator', array("cloneToTop"=>true,"excel"=>false,"add"=>false,"edit"=>false,"del"=>false,"view"=>false,"search"=>false));
        return $g->renderGrid('#search_results_table', '#search_results_table_actions', true, null, null, false, false, false);
    }

	public function checkAndLoadSessionFromRememberMe() {
		$browserToken = new Browsertoken();
		$browserToken->clear();

		$cookieHash = get_cookie($this->config->item('remember_me_cookie_hash'));

		if(isset($cookieHash) == false || $cookieHash == 0 || $cookieHash == "0") {
			return false;
		}

		$browserToken->where('cookie_hash', $cookieHash)->where('type', 'USER')->get();

		if($browserToken->type == 'USER' && $browserToken->type_id) {

			$browserToken->refresh_date = new DBCommand("NOW()");
			$browserToken->save();

			$user_id = $browserToken->type_id;
			$this->user_profile->refresh_user_session($browserToken->type_id);
			$this->load_user_profile();
		}
	}
}
