<?php

class MY_DB_mysql_driver extends CI_DB_mysql_driver {

    // determines if we should SELECT SQL_CALC_FOUND_ROWS
    protected $ar_sql_calc_found_rows = FALSE;

    // determines if we should SELECT SQL_NO_CACHE
    protected $ar_sql_no_cache = FALSE;

    function __construct($params){
        parent::__construct($params);
    }

    /**
     * Allows the user to enable/disable the use of SQL_CALC_FOUND_ROWS
     */
    public function sql_calc_found_rows($flag = TRUE){
        $this->ar_sql_calc_found_rows = $flag;
    }

    /**
     * Allows the user to enable/disable the use of SQL_NO_CACHE
     */
    public function sql_no_cache($flag = TRUE){
        $this->ar_sql_no_cache = $flag;
    }

    /**
     * Compile the SELECT statement
     *
     * Generates a query string based on which functions were used.
     * Should not be called directly.  The get() function calls it.
     *
     * @overridden This method was overridden in order to support SQL_CALC_FOUND_ROWS
     *
     * @access public
     * @return string
     */
    public function _compile_select($select_override = FALSE)
    {
        $select = '';

        // Write the "select" portion of the query
        if ($select_override !== FALSE)
        {
            $select = $select_override;
        }
        else
        {
            $select = 'SELECT ';

            if ( $this->ar_sql_calc_found_rows )
                $select .= 'SQL_CALC_FOUND_ROWS ';

            if ( $this->ar_sql_no_cache )
                $select .= 'SQL_NO_CACHE ';

            if ( $this->ar_distinct )
                $select .= 'DISTINCT ';

            if (count($this->ar_select) == 0)
            {
                $select .= '*';
            }
            else
            {
                // Cycle through the "select" portion of the query and prep each column name.
                // The reason we protect identifiers here rather then in the select() function
                // is because until the user calls the from() function we don't know if there are aliases
                foreach ($this->ar_select as $key => $val)
                {
                    $this->ar_select[$key] = $this->_protect_identifiers($val);
                }

                $select .= implode(', ', $this->ar_select);
            }
        }

        return parent::_compile_select($select);
    }

    /**
     * The "set" function.  Allows key/value pairs to be set for inserting or updating
     *
     * @overridden to add support for the DBCommand helper class.
     * @access public
     * @param mixed
     * @param string
     * @param boolean
     * @return object
     */
    public function set($key, $value = '', $escape = TRUE){
        $key = $this->_object_to_array($key);

        if (!is_array($key)){
            $key = array($key => $value);
        }

        foreach ($key as $k => $v){
            if ($escape === FALSE || $v instanceof DBCommand){
                $this->ar_set[$this->_protect_identifiers($k)] = $v;
            } else {
                $this->ar_set[$this->_protect_identifiers($k)] = $this->escape($v);
            }
        }

        return $this;
    }

    /**
     * Execute the query
     *
     * Accepts an SQL string as input and returns a result object upon
     * successful execution of a "read" type query.  Returns boolean TRUE
     * upon successful execution of a "write" type query. Returns boolean
     * FALSE upon failure, and if the $db_debug variable is set to TRUE
     * will raise an error.
     *
     * @overridden added functionality to pass the query type to the simple_query() method 
     * @access public
     * @param string An SQL query string
     * @param array An array of binding data
     * @return mixed
     */
    function query($sql, $binds = FALSE, $return_object = TRUE){
        if ($sql == ''){
            if ($this->db_debug){
                log_message('error', 'Invalid query: '.$sql);
                return $this->display_error('db_invalid_query');
            }
            return FALSE;
        }

        // Verify table prefix and replace if necessary
        if ( ($this->dbprefix != '' AND $this->swap_pre != '') AND ($this->dbprefix != $this->swap_pre) ){
            $sql = preg_replace("/(\W)".$this->swap_pre."(\S+?)/", "\\1".$this->dbprefix."\\2", $sql);
        }

        // Is query caching enabled?  If the query is a "read type"
        // we will load the caching class and return the previously
        // cached query if it exists
        if ($this->cache_on == TRUE AND stristr($sql, 'SELECT')){
            if ($this->_cache_init()){
                $this->load_rdriver();
                if (FALSE !== ($cache = $this->CACHE->read($sql))){
                    return $cache;
                }
            }
        }

        // Compile binds if needed
        if ($binds !== FALSE){
            $sql = $this->compile_binds($sql, $binds);
        }

        // Save the  query for debugging
        if ($this->save_queries == TRUE){
            $this->queries[] = $sql;
        }

        // Start the Query Timer
        $time_start = list($sm, $ss) = explode(' ', microtime());

        // Run the Query
        if (FALSE === ($this->result_id = $this->simple_query($sql, stristr($sql, 'SELECT')))){
            if ($this->save_queries == TRUE){
                $this->query_times[] = 0;
            }

            // This will trigger a rollback if transactions are being used
            $this->_trans_status = FALSE;

            if ($this->db_debug){
                // grab the error number and message now, as we might run some
                // additional queries before displaying the error
                $error_no = $this->_error_number();
                $error_msg = $this->_error_message();

                // We call this function in order to roll-back queries
                // if transactions are enabled.  If we don't call this here
                // the error message will trigger an exit, causing the
                // transactions to remain in limbo.
                $this->trans_complete();

                // Log and display errors
                log_message('error', 'Query error: '.$error_msg);
                return $this->display_error(
                    array(
                        'Error Number: '.$error_no,
                        $error_msg,
                        $sql
                    )
                );
            }

            return FALSE;
        }

        // Stop and aggregate the query time results
        $time_end = list($em, $es) = explode(' ', microtime());
        $this->benchmark += ($em + $es) - ($sm + $ss);

        if ($this->save_queries == TRUE){
            $this->query_times[] = ($em + $es) - ($sm + $ss);
        }

        // Increment the query counter
        $this->query_count++;

        // Was the query a "write" type?
        // If so we'll simply return true
        if ($this->is_write_type($sql) === TRUE){
            // If caching is enabled we'll auto-cleanup any
            // existing files related to this particular URI
            if ($this->cache_on == TRUE AND $this->cache_autodel == TRUE AND $this->_cache_init()){
                $this->CACHE->delete();
            }

            return TRUE;
        }

        // Return TRUE if we don't need to create a result object
        // Currently only the Oracle driver uses this when stored
        // procedures are used
        if ($return_object !== TRUE){
            return TRUE;
        }

        // Load and instantiate the result driver

        $driver = $this->load_rdriver();
        $RES = new $driver();
        $RES->conn_id = $this->conn_id;
        $RES->result_id = $this->result_id;

        if ($this->dbdriver == 'oci8'){
            $RES->stmt_id = $this->stmt_id;
            $RES->curs_id = NULL;
            $RES->limit_used = $this->limit_used;
            $this->stmt_id = FALSE;
        }

        // oci8 vars must be set before calling this
        $RES->num_rows = $RES->num_rows();

        // Is query caching enabled?  If so, we'll serialize the
        // result object and save it to a cache file.
        if ($this->cache_on == TRUE AND $this->_cache_init()){
            // We'll create a new instance of the result object
            // only without the platform specific driver since
            // we can't use it with cached data (the query result
            // resource ID won't be any good once we've cached the
            // result object, so we'll have to compile the data
            // and save it)
            $CR = new CI_DB_result();
            $CR->num_rows = $RES->num_rows();
            $CR->result_object = $RES->result_object();
            $CR->result_array = $RES->result_array();

            // Reset these since cached objects can not utilize resource IDs.
            $CR->conn_id = NULL;
            $CR->result_id = NULL;

            $this->CACHE->write($sql, $CR);
        }

        return $RES;
    }

    /**
     * Simple Query
     * This is a simplified version of the query() function.  Internally
     * we only use it when running transaction commands since they do
     * not require all the features of the main query() function.
     *
     * @overridden added functionality to send read queries to a different database 
     * @access public
     * @param string the sql query
     * @return mixed
     */
    function simple_query($sql, $is_read_query = FALSE){
        if (isset($this->read_splitting) && $this->read_splitting && isset($this->read_db) && $is_read_query){
            if ( ! $this->read_db->conn_id)
            {
                $this->read_db->initialize();
            }

            return $this->read_db->_execute($sql);
        }

        if ( ! $this->conn_id)
        {
            $this->initialize();
        }

        return $this->_execute($sql);
    }
}