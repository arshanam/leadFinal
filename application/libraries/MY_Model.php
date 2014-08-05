<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Custom model class that extends the DataMapper ORM
 *
 * @author Kyle Gifford
 */
class MY_Model extends DataMapper {

    /**
     * Array holding the primary keys for this model
     * @var array
     */
    protected $primary_keys = array();

    /**
     * Internal array designed to keep track of which relations have been added
     * @var array
     */
    private $joined_relations = array();

    /**
     * When using $this->all, this defines the key that will be used to index
     * @var string
     */
    protected $all_index = NULL;

    /**
     * Holds a collection of related models that will be deleted (cascade) upon the deletion of their parent record
     *
     * Example:
     * array(array('Marketplace_inventory_items', 'merchant_id', 'country'));
     *
     * Produces:
     * DELETE FROM `Marketplace`.`inventory_items` WHERE `merchant_id` = 'US'
     *
     * @var array
     */
    public $cascade_delete_models = array();

    /**
     * MY_Model constructor
     */
    function __construct() {
        if (!$this->primary_keys_defined()) {
            throw new Exception("Primary key not set");
        }

        // specify that this is a new record (because it's not yet populated)
        $this->setIsNewRecord(true);

        parent::__construct();

        // The datamapper construct blindly adds the id column to the validation
        // rules array, so we will remove it if it doesn't exist. :(
        if (!in_array('id', $this->fields)) {
            unset($this->validation['id']);
        }
    }

    /**
     * Over-riding the clear method to also clear out the joined relations array
     */
    public function clear() {
        $this->joined_relations = array();
        return parent::clear();
    }

    /**
     * Over-riding the clear_after_query() method to also clear out the joined relations array
     */
    protected function _clear_after_query() {
        $this->joined_relations = array();
        return parent::_clear_after_query();
    }

    /**
     * Fix for like to fix incorrect quoting
     * _Like
     *
     * Private function to do actual work.
     * NOTE: this does NOT use the built-in ActiveRecord LIKE function.
     *
     * @ignore
     * @param	mixed $field A field or array of fields to check.
     * @param	mixed $match For a single field, the value to compare to.
     * @param	string $type The type of connection (AND or OR)
     * @param	string $side One of 'both', 'before', or 'after'
     * @param	string $not 'NOT' or ''
     * @param	bool $no_case If TRUE, configure to ignore case.
     * @return	DataMapper Returns self for method chaining.
     */
    protected function _like($field, $match = '', $type = 'AND ', $side = 'both', $not = '', $no_case = FALSE) {
        if (!is_array($field)) {
            $field = array($field => $match);
        }

        foreach ($field as $k => $v) {
            $new_k = $this->add_table_name($k);
            if ($new_k != $k) {
                $field[$new_k] = $v;
                unset($field[$k]);
            }
        }

        // Taken from CodeIgniter's Active Record because (for some reason)
        // it is stored separately that normal where statements.

        $ci =& get_instance();
        foreach ($field as $k => $v) {
            if ($no_case) {
                $k = 'UPPER(' . $this->db->protect_identifiers($k) . ')';
                $v = strtoupper($v);
            }
            $f = "$k $not LIKE ";
            $v = $ci->db->escape_str($v);

            if ($side == 'before') {
                $m = "'%{$v}'";
            } elseif ($side == 'after') {
                $m = "'{$v}%'";
            } else {
                $m = "'%{$v}%'";
            }

            $this->_where($f, $m, $type, FALSE);
        }

        // For method chaining
        return $this;
    }

    /**
     * Deletes a row from a model, if a value is set for each primary key
     *
     * @return bool
     */
    public function delete() {
        if ($this->primary_keys_populated()) {
            foreach ($this->primary_keys as $pk) {
                $this->db->where($pk, $this->$pk);
            }
            $result = $this->db->delete($this->table);
            if ($result && !empty($this->cascade_delete_models)) {
                // cascade delete
                foreach ($this->cascade_delete_models as $dependent) {
                    if (isset($dependent[0])) {
                        $_dependent_model = new $dependent[0];

                        // if both keys are specified, it's easy
                        if (isset($dependent[2]) && isset($dependent[1])) {
                            // if the parents value was not set, we should not execute the delete(),
                            // but instead continue on to the next cascade_on_delete model
                            if (!isset($this->{$dependent[2]})) {
                                continue;
                            }

                            $_dependent_model->db->where($dependent[1], $this->{$dependent[2]});
                            // else, there better be only one PK
                        } elseif (count($this->primary_keys) == 1) {
                            $_local_key = reset($this->primary_keys);
                            // if the parents value was not set, we should not execute the delete(),
                            // but instead continue on to the next cascade_on_delete model
                            if (!isset($this->{$_local_key})) {
                                continue;
                            }

                            if (isset($dependent[1]) && in_array($dependent[1], $_dependent_model->fields)) {
                                $_dependent_model->db->where($dependent[1], $this->{$_local_key});
                            } elseif (in_array($_local_key, $_dependent_model->fields)) {
                                $_dependent_model->db->where($_local_key, $this->{$_local_key});
                            } else {
                                continue;
                            }
                        } else {
                            continue;
                        }

                        $_dependent_model->db->delete($_dependent_model->table);
                    }
                }
            }

            return $result;
        }
        return false;
    }

    /**
     * Deletes all rows from a model, if a value is set for each primary key
     *
     * @return bool
     */
    public function delete_all() {
        $success = TRUE;

        foreach ($this as $item) {
            $success_temp = $item->delete();
            $success = $success && $success_temp;
        }

        // clear this object
        $this->clear();

        return $success;
    }

    /**
     * Wrapper for the join method
     *
     * @param string $table The table to join
     * @param string $on What to join on
     * @param string $type Optional, the type of join to perform
     * @return object
     */
    public function join($table, $on, $type = '') {
        $this->db->join($table, $on, $type);

        return $this;
    }

    /**
     * Generates the search results to return to jqGrid
     *
     * @param bool $split_count_query Uses two separate queries instead of
     *  SQL_CALC_FOUND_ROWS to get the total number of results
     * @param array $fulltext_matches If any filters match the key, add a fulltext search on the value
     * @return array jqGrid results
     */
    public function jqgrid_search($split_count_query = FALSE, $fulltext_matches = array()) {
        $CI = & get_instance();
        $params = array(
            'sort_by' => $CI->input->post('sidx'),
            'sort_direction' => $CI->input->post('sord'),
            'page' => $CI->input->post('page'),
            'num_rows' => $CI->input->post('rows'),
            'search' => $CI->input->post('_search'),
            'search_field' => $CI->input->post('searchField'),
            'search_oper' => $CI->input->post('searchOper'),
            'search_string' => $CI->input->post('searchString'),
            'filters' => $CI->input->post('filters')
        );

        if ((($params['num_rows'] * $params['page']) >= 0 && $params['num_rows'] > 0)) {
            if ($params['search'] == 'true') {
                $ops = array(
                    'eq' => '=',
                    'ne' => '<>',
                    'lt' => '<',
                    'le' => '<=',
                    'gt' => '>',
                    'ge' => '>='
                );
                $filter_array = $params['filters'] ? json_decode($params['filters'], TRUE) : array(
                    'groupOp' => 'AND',
                    'rules' => array(
                        0 => array(
                            'op' => $params['search_oper'],
                            'field' => $params['search_field'],
                            'data' => $params['search_string']
                        )
                    )
                );
                if (is_array($filter_array)) {
                    $op_prefix = ($filter_array['groupOp'] == "OR") ? "or_" : "";
                    $fulltext_op_prefix = ($filter_array['groupOp'] == "OR") ? '' : '+';
                    if (is_array($filter_array['rules'])) {
                        $this->group_start();
                        $fulltext_filters = array();
                        foreach ($filter_array['rules'] as $value) {
                            if(in_array($value['field'], array_keys($fulltext_matches))) {
                                $terms = explode(' ', $value['data']);
                                foreach($terms as $term) {
                                    $term = str_replace("#", "", $term);
                                    switch($value['op']) {
                                        case 'eq':  // equal
                                        case 'cn':
                                            $fulltext_filters[$value['field']][] = $fulltext_op_prefix . $CI->db->escape_str($term);
                                            break;
                                        case 'ne':  // not equal
                                        case 'nc':
                                            $fulltext_filters[$value['field']][] = '-' . $CI->db->escape_str($term);
                                            break;
                                        case 'bw':  // begins with
                                            $fulltext_filters[$value['field']][] = $fulltext_op_prefix . $CI->db->escape_str($term) . '*';
                                            break;
                                        case 'bn':  // does not begin with
                                            $fulltext_filters[$value['field']][] = '-' . $CI->db->escape_str($term) . '*';
                                            break;
                                    }
                                }
                            } else {
                                switch ($value['op']) {
                                    case 'bw':
                                        $this->{$op_prefix . 'like'}($value['field'], $value['data'], 'after');
                                        break;
                                    case 'bn':
                                        $this->{$op_prefix . 'not_like'}($value['field'], $value['data'], 'after');
                                        break;
                                    case 'in':
                                        $this->{$op_prefix . 'where_in'}($value['field'], $value['data']);
                                        break;
                                    case 'ni':
                                        $this->{$op_prefix . 'where_not_in'}($value['field'], $value['data']);
                                        break;
                                    case 'ew':
                                        $this->{$op_prefix . 'like'}($value['field'], $value['data'], 'before');
                                        break;
                                    case 'en':
                                        $this->{$op_prefix . 'not_like'}($value['field'], $value['data'], 'before');
                                        break;
                                    case 'cn':
                                        $this->{$op_prefix . 'like'}($value['field'], $value['data'], 'both');
                                        break;
                                    case 'nc':
                                        $this->{$op_prefix . 'not_like'}($value['field'], $value['data'], 'both');
                                        break;
                                    default:
                                        $this->{$op_prefix . 'where'}($value['field'] . " " . $ops[$value['op']], $value['data']);
                                        break;
                                }
                            }
                        }
                        foreach($fulltext_filters as $field => $filters) {
                            $this->{$op_prefix . 'where'}('MATCH (' . $fulltext_matches[$field] . ') AGAINST (\''. implode(' ', $filters) .'\' IN BOOLEAN MODE)', NULL, FALSE);
                        }
                        $this->group_end();
                    }
                }
            }

            if(!$split_count_query) {
                // we need to get the total records - so we'll do it with SQL_CALC_FOUND_ROWS
                $this->sql_calc_found_rows();
            } else {
                $count_query = $this->get_copy();
            }

            if ($params['sort_by']) {
                $this->order_by($params['sort_by'], $params['sort_direction']);
            }

            if ($params['page'] != 'all') {
                $this->limit($params['num_rows'], $params['num_rows'] * ($params['page'] - 1));
            }
        } else {
            if(!$split_count_query) {
                // we need to get the total records - so we'll do it with SQL_CALC_FOUND_ROWS
                $this->sql_calc_found_rows();
            } else {
                $count_query = $this->get_copy();
            }
        }

        // return the results
        $query = $this->get_raw();
        $result['rows'] = $query->result_array();
        $result['page'] = $params['page'];
        if($split_count_query) {
            $result['records'] = $count_query->count();
        } else {
            $result['records'] = $this->query('SELECT FOUND_ROWS() AS total_records')->total_records;
        }
        $result['total'] = ($params['num_rows']) ? ceil($result['records'] / $params['num_rows']) : $result['records'];
        if ($result['total'] == 0)
            $result['total'] = 1;

        return $result;
    }

    /**
     * Checks if primary keys are defined for a given model
     *
     * @return bool
     */
    private function primary_keys_defined() {
        return empty($this->primary_keys) ? FALSE : TRUE;
    }

    /**
     * Checks if there are current values set each primary key value
     *
     * @return bool
     */
    public function primary_keys_populated() {
        foreach ($this->primary_keys as $pk) {
            if (!isset($this->$pk)) {
                return false;
            }
        }
        return true;
    }

    public function with($requested_relations = '', $merge_codes = array()) {
        if (is_string($requested_relations) && $requested_relations != '') {
            // retrieve this models relations
            $model_relations = $this->relations();

            // determine which relations we are wanting to use
            $requested_relations = explode(',', $requested_relations);
            foreach ($requested_relations as $requested_relation) {
                $requested_relation = trim($requested_relation);

                if (isset($model_relations[$requested_relation])) {
                    if (!in_array($requested_relation, $this->joined_relations)) {
                        $this->join(
                                $model_relations[$requested_relation][0],
                                strtr($model_relations[$requested_relation][1], $merge_codes),
                                ($model_relations[$requested_relation][2]) ? $model_relations[$requested_relation][2] : ''
                        );
                        $this->joined_relations[] = $requested_relation;
                    }
                } else {
                    throw new Exception("The relationship {$requested_relation} does not exist for the current model.");
                }
            }
        }

        return $this;
    }

    /**
     * Defines a models relations, which can be specified (by name) using with()
     *
     * @see MY_Model::with()
     * @return array
     */
    public function relations() {
        return array();
    }

    /**
     * Defines which model attributes are safe/allowed to be populated from post automatically.
     *
     * @see MY_Model::safelyPopulateFromPost()
     * @return array
     */
    public function safeAttributes() {
        return array();
    }

    /**
     * Populates the current model attributes defined by safeAttributes(), using
     * data from the codeigniter post input filter.
     *
     * @see MY_Model::safeAttributes()
     * @returns $this for method chaining
     */
    public function safelyPopulateFromPost() {
        $CI = & get_instance();
        $safeAttributes = (array) $this->safeAttributes();

        foreach ($safeAttributes as $safeAttribute) {
            $this->{$safeAttribute} = $CI->input->post($safeAttribute);
        }

        return $this;
    }

    /**
     * Populates the current model attributes defined by safeAttributes(), using
     * data from the array given.
     *
     * @see MY_Model::safeAttributes()
     * @param array $data
     */
    public function safelyPopulate($data = array()) {
        $safeAttributes = (array) $this->safeAttributes();

        foreach ($safeAttributes as $safeAttribute) {
            if (isset($data[$safeAttribute]))
                $this->{$safeAttribute} = $data[$safeAttribute];
        }

        return $this;
    }

    /**
     * Helper to determines if this is a new record.
     *
     * @return boolean
     */
    public function isNewRecord() {
        return $this->_force_save_as_new;
    }

    /**
     * Sets wether or not this is a new record.
     *
     * @return boolean
     */
    public function setIsNewRecord($flag) {
        $this->_force_save_as_new = $flag;
    }

    /**
     * Gets objects from the database.
     *
     * Wraps Datamapper::get() in an attempt to automatically determine if this
     * model is a new record.
     *
     * @see DataMapper::get()
     * @param	integer|NULL $limit Limit the number of results.
     * @param	integer|NULL $offset Offset the results when limiting.
     * @return	DataMapper Returns self for method chaining.
     */
    public function get($limit = NULL, $offset = NULL) {
        $result = parent::get($limit, $offset);

        if ($result->result_count() > 0) {
            // if there is one result, $this->all might not be used... so update $this, too.
            if ($result->result_count() == 1) {
                $this->setIsNewRecord(false);
            }

            foreach (array_keys($result->all) as $all_id) {
                $result->all[$all_id]->setIsNewRecord(false);
            }
        }

        return $result;
    }

    /**
     * Validates and saves the current model.
     *
     * @return boolean Success or Failure of the validation and save.
     */
    public function save() {
        $result = false;

        // validate the current model
        $this->validate();

        if ($this->valid && $this->beforeSave()) {
            // Check if object has an 'updated' field
            if (in_array($this->updated_field, $this->fields)) {
                // Update updated datetime
                $this->{$this->updated_field} = new DBCommand('NOW()');
            }

            $data = $this->_to_array(TRUE);

            // determine if we should do an update or an insert
            if (!$this->isNewRecord() && $this->primary_keys_populated()) {
                // Prepare data to send only changed fields
                foreach ($data as $field => $value) {
                    // Unset field from data if it hasn't been changed
                    if ($this->{$field} === $this->stored->{$field}) {
                        unset($data[$field]);
                    }
                }

                // Check if only the 'updated' field has changed, and if so, revert it
                if (count($data) == 1 && isset($data[$this->updated_field])) {
                    // Revert updated
                    $this->{$this->updated_field} = $this->stored->{$this->updated_field};

                    // Unset it
                    unset($data[$this->updated_field]);
                }

                // Only go ahead with save if there is still data
                if (!empty($data)) {
                    // add necessary criteria so that we only update this model
                    foreach ($this->primary_keys as $pk) {
                        if ($this->stored->{$pk} && $this->{$pk} !== $this->stored->{$pk}) {
                            $this->where($pk, $this->stored->{$pk});
                        } else {
                            $this->where($pk, $this->$pk);
                        }
                    }

                    $result = $this->update($data);
                } else {
                    $result = TRUE;
                }
            } else {
                $result = $this->db->insert($this->table, $data);

                // populate the insert id, if available
                $insert_id = $this->db->insert_id();
                if ($insert_id) {
                    $pk = reset($this->primary_keys);
                    $this->{$pk} = $insert_id;
                }
            }
        }

        if ($result === TRUE)
            $this->afterSave();

        // reset valdiates
        $this->_validated = FALSE;

        return $result;
    }

    /**
     * Converts this objects current record into an array for database queries.
     * If $only_set_values is TRUE, empty objects will be left out.
     *
     * Exposes DataMapper::_to_array() to the public.
     *
     * @param boolean $only_set_values
     * @return array
     */
    public function _toArray($only_set_values = TRUE) {
        return parent::_to_array($only_set_values);
    }

    /**
     * Count
     *
     * Returns the total count of the object records from the database.
     * If on a related object, returns the total count of related objects records.
     *
     * @Overridden to prevent fatal errors when there is a db error ($query->result_count() being called on non-object)
     * @param	array $exclude_ids A list of ids to exlcude from the count
     * @return	int Number of rows in query.
     */
    public function count($exclude_ids = NULL, $column = NULL, $related_id = NULL) {
        // Check if related object
        if (!empty($this->parent)) {
            // Prepare model
            $related_field = $this->parent['model'];
            $related_properties = $this->_get_related_properties($related_field);
            $class = $related_properties['class'];
            $other_model = $related_properties['join_other_as'];
            $this_model = $related_properties['join_self_as'];
            $object = new $class();

            // To ensure result integrity, group all previous queries
            if (!empty($this->db->ar_where)) {
                array_unshift($this->db->ar_where, '( ');
                $this->db->ar_where[] = ' )';
            }

            // Determine relationship table name
            $relationship_table = $this->_get_relationship_table($object, $related_field);

            // We have to query special for in-table foreign keys that
            // are pointing at this object
            if ($relationship_table == $object->table && // ITFK
                    // NOT ITFKs that point at the other object
                    !($object->table == $this->table && // self-referencing has_one join
                    in_array($other_model . '_id', $this->fields)) // where the ITFK is for the other object
            ) {
                // ITFK on the other object's table
                $this->db->where('id', $this->parent['id'])->where($this_model . '_id IS NOT NULL');
            } else {
                // All other cases
                $this->db->where($other_model . '_id', $this->parent['id']);
            }
            if (!empty($exclude_ids)) {
                $this->db->where_not_in($this_model . '_id', $exclude_ids);
            }
            if ($column == 'id') {
                $column = $relationship_table . '.' . $this_model . '_id';
            }
            if (!empty($related_id)) {
                $this->db->where($this_model . '_id', $related_id);
            }
            $this->db->from($relationship_table);
        } else {
            $this->db->from($this->table);
            if (!empty($exclude_ids)) {
                $this->db->where_not_in('id', $exclude_ids);
            }
            if (!empty($related_id)) {
                $this->db->where('id', $related_id);
            }
            $column = $this->add_table_name($column);
        }

        // Manually overridden to allow for COUNT(DISTINCT COLUMN)
        $select = $this->db->_count_string;
        if (!empty($column)) {
            // COUNT DISTINCT
            $select = 'SELECT COUNT(DISTINCT ' . $this->db->_protect_identifiers($column) . ') AS ';
        }
        $sql = $this->db->_compile_select($select . $this->db->_protect_identifiers('numrows'));

        $query = $this->db->query($sql);
        $this->db->_reset_select();

        if (!$query || $query->num_rows() == 0) {
            return 0;
        }

        $row = $query->row();
        return intval($row->numrows);
    }

    /**
     * Helps generate the select portion of a query when there are a large number
     * of columns being selected across multiple joined tables.
     *
     * For special circumstances, such as method calls, you should key your select
     * criteria with the special keyword of '_special'
     *
     * Example:
     *
     * $model->select_array(
     *     array(
     *         'Marketplace.inventory_items' => 'inventory_item_id',
     *         'A' => 'column_1, column_2',
     *         '_special' =>
     *             'IFNULL(Marketplace.inventory_items.quantity_total, 0) AS quantity_total, ' .
     *             'IFNULL(Marketplace.inventory_items.weight, 0.03) AS weight',
     *     )
     * )->get();
     *
     * The above example will produce the following select criteria:
     *
     * SELECT
     *     IFNULL(Marketplace.inventory_items.quantity_total, 0) AS quantity_total,
     *     IFNULL(Marketplace.inventory_items.weight, 0.03) AS weight,
     *     Marketplace.inventory_items.inventory_item_id, A.column_1, A.column_2
     *
     * @param array $select_array An array of columns(CSV) to select, keyed by their table name/alias.
     * @return MY_Model $this
     */
    public function select_array(array $select_array) {
        $final_columns = array();

        if (isset($select_array['_special'])) {
            $final_columns[] = $select_array['_special'];
            unset($select_array['_special']);
        }

        foreach ($select_array as $table => $columns) {
            $tmp_columns = explode(',', $columns);
            foreach ($tmp_columns as $column) {
                $final_columns[] = $table . '.' . trim($column);
            }
        }

        $this->select(implode(', ', $final_columns), FALSE);
        return $this;
    }

    /**
     * Select_prepend
     *
     * Generates the SELECT portion of the query, but prepends the contents to the beginning of the SELECT statement
     * Useful for SQL_CALC_FOUND_ROWS
     *
     * @access	public
     * @param	string
     * @return	object
     */
    public function select_prepend($select = '*', $escape = NULL) {

        // Set the global value if this was sepecified
        if (is_bool($escape)) {
            $this->db->_protect_identifiers = $escape;
        }

        if (is_string($select)) {
            $select = explode(',', $select);
        }

        foreach ($select as $val) {
            $val = trim($val);

            if ($val != '') {
                array_unshift($this->db->ar_select, $val);
                if ($this->db->ar_caching === TRUE) {
                    $this->db->ar_cache_select[] = $val;
                    $this->db->ar_cache_exists[] = 'select';
                }
            }
        }

        return $this;
    }

    /**
     * Allows the user to enable/disable the use of SQL_CALC_FOUND_ROWS
     *
     * @return $this for chain-ability
     */
    public function sql_calc_found_rows($enabled = TRUE) {
        $this->db->sql_calc_found_rows($enabled);

        return $this;
    }

    /**
     * Allows the user to enable/disable the use of SQL_NO_CACHE
     *
     * @return $this for chain-ability
     */
    public function sql_no_cache($enabled = TRUE) {
        $this->db->sql_no_cache($enabled);

        return $this;
    }

    /**
     * Allows the user to skip model validation when saving
     * @return MY_Model $this
     */
    public function skip_validation() {
        $this->_validated = TRUE;
        $this->valid = TRUE;
        return $this;
    }

    /**
     * This method will perform any actions that need to be run before save().
     *
     * @return boolean wether the record should still be saved. Defaults to TRUE
     */
    protected function beforeSave() {
        return TRUE;
    }

    /**
     * This method will perform any actions that need to be run after a successful
     * save().
     */
    protected function afterSave() {
        $this->setIsNewRecord(false);

        $this->_refresh_stored_values();
    }

    /**
     * Process Query
     *
     * Converts a query result into an array of objects.
     * Also updates this object
     *
     * @overridden to support indexing the $model->all array with a specified column name
     * @ignore
     * @param	CI_DB_result $query
     */
    protected function _process_query($query) {
        if ($query->num_rows() > 0) {
            // Populate all with records as objects
            $this->all = array();

            $this->_to_object($this, $query->row());

            // don't bother recreating the first item.
            $index = ($this->all_index && isset($this->{$this->all_index})) ? $this->{$this->all_index} : 0;
            $this->all[$index] = $this->get_clone();

            if ($query->num_rows() > 1) {
                $model = get_class($this);

                $first = TRUE;

                foreach ($query->result() as $row) {
                    if ($first) {
                        $first = FALSE;
                        continue;
                    }

                    $item = new $model();

                    $this->_to_object($item, $row);

                    if ($this->all_index && isset($item->{$this->all_index})) {
                        $this->all[$item->{$this->all_index}] = $item;
                    } else {
                        $this->all[] = $item;
                    }
                }
            }

            // remove instantiations
            $this->_instantiations = NULL;

            // free large queries
            if ($query->num_rows() > $this->free_result_threshold) {
                $query->free_result();
            }
        } else {
            // Refresh stored values is called by _to_object normally
            $this->_refresh_stored_values();
        }
    }

    /**
     * Allows the ability to index a models collection array by the specified
     * columns value.
     *
     * @param string $column_name The name of the column
     */
    public function all_index($column_name) {
        $this->all_index = $column_name;

        return $this;
    }

    /**
     * Magic Call
     *
     * Calls special methods, or extension methods.
     *
     * @overridden to allow us to override the _get_by() private method.
     * @ignore
     * @param	string $method Method name
     * @param	array $arguments Arguments to method
     * @return	mixed
     */
    public function __call($method, $arguments) {

        // if this is a get_by_* magic method, we want to override the parent version
        if (strpos($method, 'get_by_') === 0) {
            return $this->_get_by(str_replace('get_by_', '', $method), $arguments);
        } else {
            parent::__call($method, $arguments);
        }
    }

    /**
     * Get By
     *
     * Gets objects by specified field name and value.
     *
     * @overridden to prevent the where condition from getting dropped with the value is NULL
     * @ignore
     * @param	string $field Field to look at.
     * @param	array $value Arguments to this method.
     * @return	DataMapper Returns self for method chaining.
     */
    private function _get_by($field, $value = array()) {
        if (isset($value[0])) {
            $this->where($field, $value[0]);
        } else {
            $this->where($field);
        }

        return $this->get();
    }

}

/**
 * This class can be used to bypass the escaping of values being stored in the
 * database, when using active record.
 *
 * @example $model->created = new DBCommand('NOW()');
 */
class DBCommand {

    protected $string;

    function __construct($string = '') {
        $this->string = $string;
    }

    function __toString() {
        return $this->string;
    }

}
