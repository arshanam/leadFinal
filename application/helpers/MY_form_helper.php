<?php

/**
 * Creates the opening portion of the form tag.
 *
 * @param string $action the URI segments of the form destination
 * @param array $attributes a key/value pair of attributes
 * @param array $hidden a key/value pair of hidden data
 * @return string
 */
function form_open($action = '', $attributes = '', $hidden = array()) {
    if ($attributes == '') {
        $attributes = 'method="post"';
    }

    if ( strpos($action, '://') === FALSE ) {
        $url = is_ssl() ? 'https://' : 'http://';
        $url .= get_instance()->input->server('HTTP_HOST');
        $action = $url . $action;
    }

    $form = '<form action="'.$action.'"';

    $form .= _attributes_to_string($attributes, TRUE);

    $form .= '>';

    if (is_array($hidden) AND count($hidden) > 0) {
        $form .= form_hidden($hidden);
    }

    return $form;
}

/**
 * Creates an array of options (name-value pair) that can be used to populate the
 * options of a select box, from the specified $list.
 *
 * @see form_dropdown()
 * @param array $list
 * @param string $key the attribute name to use for the option values
 * @param string $label the attribute name to use for the option labels
 * @return array
 */
function toOptions($list, $key, $label, $additional = array(),$default_label_text='') {
    if($default_label_text != '') {
		$new_options = array(''=>$default_label_text);
	} else {
		$new_options = array(''=>'- Select -');
	}
	if(!empty($additional) && is_array($additional)) {
		$new_options = array(''=>'- Select -', '-1' => 'All Service Types');
	}
    $first_option = current($list);

    if ( is_object($first_option) || $list instanceof MY_Model ){
        foreach($list as $option){
            $new_options[$option->$key] = $option->$label;
        }
    } elseif( is_array($first_option) ) {
        foreach($list as $option){
            $new_options[$option[$key]] = $option[$label];
        }
    }

    return $new_options;
}


/**
 * Creates an array of options (name-value pair) that can be used to populate the
 * array, from the specified $list.
 * @param array $list
 * @param string $key the array index
 * @param string $label the array index value
 * @return array
 */
function toArray($list, $key, $label) {
    $new_options = array();

    $first_option = current($list);

    if ( is_object($first_option) || $list instanceof MY_Model ){
        foreach($list as $option){
            $new_options[$option->$key] = $option->$label;
        }
    } elseif( is_array($first_option) ) {
        foreach($list as $option){
            $new_options[$option[$key]] = $option[$label];
        }
    }

    return $new_options;
}


/**
 * Form Error
 *
 * Returns the error for a specific form field.  This is a helper for the
 * form validation class.
 *
 * @access	public
 * @param	string
 * @param	string
 * @param	string
 * @return	string
 */
function form_error($field = '', $model = NULL, $prefix = '<div class="gen_err">', $suffix = '</div>'){
    $error_string = '';

    if (($OBJ =& _get_validation_object()) !== FALSE){
        $error_string .= $OBJ->error($field, $prefix, $suffix);

        // so ugly :(
        if($prefix == '')
            $prefix = $OBJ->_error_prefix;

        if($suffix == '')
            $suffix = $OBJ->_error_suffix;
    }

    if ($model !== NULL && $model instanceof MY_Model && isset($model->error->{$field})){
        $error_string .= $prefix.$model->error->{$field}.$suffix;
    }

    return $error_string;
}

/**
 * Generate Textarea with custom rows and columns
 * Textarea field
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */
	function form_textarea($data = '', $value = '', $cols = '90', $rows = '12' ,$extra = '')
	{
		$defaults = array('name' => (( ! is_array($data)) ? $data : ''), 'cols' => $cols, 'rows' => $rows);

		if ( ! is_array($data) OR ! isset($data['value']))
		{
			$val = $value;
		}
		else
		{
			$val = $data['value'];
			unset($data['value']); // textareas don't use the value attribute
		}

		$name = (is_array($data)) ? $data['name'] : $data;
		return "<textarea "._parse_form_attributes($data, $defaults).$extra.">".form_prep($val, $name)."</textarea>";
	}


// ------------------------------------------------------------------------


/**
 * This method will return checked="checked" if $value is true.
 *
 * @param bool $value
 * @return string
 */
function set_checked($value){

    if ($value){
        return ' checked="checked" ';
    }
}

/**
 * Set DataMapper Checkbox
 *
 * Let's you set the selected value of a checkbox via the value in the POST array.
 * This method is different from set_checkbox because it bypasses the form_validation class.
 *
 * This method should be used if you are using datamappers model validation instead
 * of codeigniters form_validation.
 *
 * @access	public
 * @param	string
 * @param	string
 * @param	bool
 * @return	string
 */
function set_dm_checkbox($field = '', $value = '', $default = FALSE){

    if (!isset($_POST[$field])){
        if (count($_POST) === 0 AND $default == TRUE){
            return ' checked="checked"';
        }

        return '';
    }

    $field = $_POST[$field];

    if (is_array($field)){
        if (!in_array($value, $field)){
            return '';
        }
    } else {
        if (($field == '' OR $value == '') OR ($field != $value)){
            return '';
        }
    }

    return ' checked="checked"';
}