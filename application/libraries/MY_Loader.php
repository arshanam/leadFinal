<?php

class MY_Loader extends CI_Loader {
    /**
     * Database Loader
     *
     * @overridden This method was overridden to allow us to override the DB driver class.
     *
     * @access    public
     * @param    string    the DB credentials
     * @param    bool    whether to return the DB object
     * @param    bool    whether to enable active record (this allows us to override the config setting)
     * @return    object
     */
    function database($params = '', $return = FALSE, $active_record = FALSE)
    {
        // Grab the super object
        $CI =& get_instance();

        // Do we even need to load the database class?
        if (class_exists('CI_DB') AND $return == FALSE AND $active_record == FALSE AND isset($CI->db) AND is_object($CI->db)){
            return FALSE;
        }

        require_once(BASEPATH.'database/DB'.EXT);

        // Load the DB class
        $db =& DB($params, $active_record);

        $my_driver = config_item('subclass_prefix').'DB_'.$db->dbdriver.'_driver';
        $my_driver_file = APPPATH.'libraries/'.$my_driver.EXT;

        if (file_exists($my_driver_file)){
            require_once($my_driver_file);
            $db = new $my_driver(get_object_vars($db));

            if (isset($db->read_splitting) && $db->read_splitting && isset($db->read_db) && !empty($db->read_db)){
                $read_db =& DB($db->read_db, $active_record);
                $db->read_db = $read_db;
            }
        }

        if ($return === TRUE){
            return $db;
        }

        // Initialize the db variable.  Needed to prevent
        // reference errors with some configurations
        $CI->db = '';
        $CI->db = $db;

        // Assign the DB object to any existing models
        $this->_ci_assign_to_models();
    }
}