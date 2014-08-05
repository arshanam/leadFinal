<?

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * MY_Form_validation Class
 *
 * Extends Form_Validation library
 */
class MY_Form_validation extends CI_Form_validation {

    public $CI;

    function My_Form_validation($rules = array()) {
        parent::CI_Form_validation($rules);
        $this->CI = & get_instance();
    }

    /**
     * Run the Validator
     *
     * This function does all the work.
     *
     * Modified by Brett Millett:
     *  Provided option to remove the config or inline only restriction on
     *  rules. This version will process config rules first and then any
     *  inline rules that exist after. This has the benefit of allowing
     *  inline rules to overwite config rules by the same key.
     *
     * @access	public
     * @return	bool
     */
    function run($group = '', $combine_conf_inline = FALSE) {
        if ($combine_conf_inline) {
            //only perform if we have both field and config rules.
            if (count($this->_field_data) > 0 && count($this->_config_rules) > 0) {
                // Is there a validation rule for the particular URI being accessed?
                $uri = ($group == '') ? trim($this->CI->uri->ruri_string(), '/') : $group;

                if ($uri != '' AND isset($this->_config_rules[$uri])) {
                    $config_rules = $this->_config_rules[$uri];
                } else {
                    $config_rules = $this->_config_rules;
                }

                // only set the rule if it has not already been set inline.
                foreach ($config_rules as $row) {
                    if (!isset($this->_field_data[$row['field']]))
                        $this->set_rules($row['field'], $row['label'], $row['rules']);
                }
            }
        }
        //run parent version last, so field rules will  override config ones and update
        return parent::run($group);
    }

    // --------------------------------------------------------------------

    /**
     * Unique
     *
     * @access	public
     * @param	string
     * @param	field
     * @return	bool
     */
    function unique($str, $field) {
        list($table, $column) = explode('.', $field, 2);

        $this->CI->form_validation->set_message('unique', 'The %s that you requested is unavailable.');

        $query = $this->CI->db->query("SELECT COUNT(*) AS dupe FROM $table WHERE $column = '$str'");
        $row = $query->row();
        return ($row->dupe > 0) ? FALSE : TRUE;
    }

    /**
     * Checks for a valid date
     * @param string $str
     * @return bool
     */
    function date($str) {
        $this->CI->form_validation->set_message('date', 'The date you submitted is not a valid date.');
        $stamp = strtotime($str);
        if (!is_numeric($stamp)) {
            return FALSE;
        }
        $month = date('m', $stamp);
        $day = date('d', $stamp);
        $year = date('Y', $stamp);

        if (checkdate($month, $day, $year)) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Checks that a date submitted is in the past
     *
     * @param string $str
     * @return bool
     */
    function past_date($str) {
        $stamp = strtotime($str);
        if (!is_numeric($stamp)) {
            $this->CI->form_validation->set_message('past_date', 'The %s you submitted is not a valid date.');
            return FALSE;
        }
        if ($stamp >= time()) {
            $this->CI->form_validation->set_message('past_date', 'The %s must be in the past.');
            return FALSE;
        }

        return TRUE;
    }

	/**
     * Checks that a date submitted is in the future
     *
     * @param string $str
     * @return bool
     */
    function future_date($str) {
        $stamp = strtotime($str);
        if (!is_numeric($stamp)) {
            $this->CI->form_validation->set_message('future_date', 'The %s you submitted is not a valid date.');
            return FALSE;
        }
        if ($stamp <= time()) {
            $this->CI->form_validation->set_message('future_date', 'The %s must be in the future.');
            return FALSE;
        }

        return TRUE;
    }


    /**
     * Validates that the submitted gender is an acceptable input option
     *
     * @param string $str
     * @return bool
     */
    function gender($str) {
        if ($str != 'M' && $str != 'F') {
            $this->CI->form_validation->set_message('gender', 'The %s you submitted is not a valid gender.');
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Validates that the submitted credit card type is an acceptable input option
     *
     * @param string $str
     * @return bool
     */
    function credit_card_type($str) {
        if ($str != 'VISA' && $str != 'MASTERCARD' && $str != 'AMEX' && $str != 'DISCOVER') {
            $this->CI->form_validation->set_message('credit_card_type', 'The %s you submitted is not a valid credit card type.');
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Validates a card expiry date.  Finds the midnight on first day of the following
     * month and ensures that is greater than the current time (cards expire at the
     * end of the printed month).  Assumes basic sanity checks have already been performed
     * on month/year (i.e. length, numeric, etc).
     *
     * @param integer The expiry month shown on the card.
     * @param string The field name that contains the year.
     * @return boolean Returns true if the card is still valid, false if it has expired.
     */
    function credit_card_not_expired($month, $year_field_name) {
        $year = $this->CI->input->post($year_field_name);
        $expiry_date = mktime(0, 0, 0, ($month + 1), 1, $year);
        $return = ($expiry_date > time());
        if ($return == FALSE) {
            $this->CI->form_validation->set_message('credit_card_not_expired', 'The Expiration Date you submitted is in the past.');
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Strips all non-numerics from the card number.
     *
     * @param string The card number to clean up.
     * @return string The stripped down card number.
     */
    function credit_card_number_clean($number) {
        return preg_replace("#[^0-9]#", "", $number);
    }

    /**
     * Uses the Luhn algorithm (aka Mod10) <http://en.wikipedia.org/wiki/Luhn_algorithm>
     * to perform basic validation of a credit card number.
     *
     * @param string The card number to validate.
     * @return bool True if valid according to the Luhn algorith, false otherwise.
     */
    function credit_card_number($card_number) {
        $card_number = strrev($this->credit_card_number_clean($card_number));
        $sum = 0;

        for ($i = 0; $i < strlen($card_number); $i++) {
            $digit = substr($card_number, $i, 1);

            // Double every second digit
            if ($i % 2 == 1) {
                $digit *= 2;
            }

            // Add digits of 2-digit numbers together
            if ($digit > 9) {
                $digit = ($digit % 10) + floor($digit / 10);
            }

            $sum += $digit;
        }

        // If the total has no remainder it's OK
        $return = ($sum % 10 == 0);
        if ($return == FALSE) {
            $this->CI->form_validation->set_message('credit_card_number', 'The %s you submitted is not a valid card number.');
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Strips all non-numerics from the phone number.
     *
     * @param string The phone number to clean up.
     * @return string The stripped down phone number.
     */
    function phone_number_clean($number) {
        return preg_replace("#[^0-9]#", "", $number);
    }

    /**
     * Validates that the submitted phone type is an acceptable input option
     *
     * @param string $str
     * @return bool
     */
    function phone_type($str) {
        if ($str != 'WORK' && $str != 'HOME' && $str != 'MOBILE') {
            $this->CI->form_validation->set_message('phone_type', 'The %s you submitted is not a valid phone type.');
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Validates that the submitted country is an acceptable input option
     *
     * @param string $str Country iso3 abbreviation
     */
    function country($str) {
        $application_countries_model = new Countries();
        $country_found = $application_countries_model->get_country_by_abbreviation($str);
        if (!$country_found) {
            $this->CI->form_validation->set_message('country', 'The %s you submitted is not a valid country.');
            return FALSE;
        }

        return TRUE;
    }

	/**
     * Validates that the submitted string is an acceptable input option
     *
     * @param string $str
     * @return bool
     */
    function no_zero($str) {
        if ($str == 0) {
            $this->CI->form_validation->set_message('no_zero', '%s must be greater than 0.');
            return FALSE;
        }

        return TRUE;
    }

	/**
     * Validates that the submitted string is an acceptable input option
     *
     * @param string $str example: valid- test1 2, invalid - 6548222
     * @return bool
     */
	function alpha_numeric($str)
	{
		if(is_numeric($str)) {
			$this->CI->form_validation->set_message('alpha_numeric', '%s must be alphanumeric.');
            return FALSE;
		} else if(!preg_match('/^[a-zA-Z0-9 ]+$/',$str)) {
			$this->CI->form_validation->set_message('alpha_numeric', '%s must be alphanumeric.');
            return FALSE;
		}
		return TRUE;
	}


}