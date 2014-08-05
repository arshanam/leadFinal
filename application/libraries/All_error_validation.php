<?

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * All_error_validation Class
 *
 * Extends Form_Validation library
 */
class All_error_validation extends CI_Form_validation {

     public function __construct() {

        parent::__construct();

    }

    /**
     * Return all validation errors
     *
     * @access  public
     * @return  array
     */
    function get_all_errors() {

        $error_array = array();

        if (count($this->_error_array) > 0) {

            foreach ($this->_error_array as $k => $v) {

                $error_array[$k] = $v;

            }

            return $error_array;

        }

        return false;

    }


}