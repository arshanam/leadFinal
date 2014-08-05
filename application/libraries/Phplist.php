<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);

/**
 * PHPList Class
 *
 * Provides most functions to manage and update PHPList
 *
 * @author  	Ben Marshall <bmarshall@beckett.com>
 *
 * @method phplist save_user(array $params) Saves/Updates user
 * @method phplist assigned_lists(int $user_id) Returns an array of lists the specified user is assigned to
 * @method phplist user(string $user_id) Returns an array of the specified user's information
 * @method phplist user_attributes(int $user_id) Returns an array of the specified user's attributes
 * @method phplist lists() Returns an array of active mailing lists
 * @method phplist remove_user_list(string $user_id,int $list_id) Removes a specified user from the specified list
 * @method phplist convert_user_id() Returns the PHPList user id based off the Beckett.com user id that's specified
 */
class Phplist {

    private $db_name = "phplist"; // Database name
    private $db_host = "mysqldev01.beckett.com"; // Database host
    private $db_user = "incarnate"; // Database user
    private $db_pass = "aKaRxPZ9Cn2cL7sp"; // Database password
    private $connect;
    private $tbl_user = "phplist_user_user";
    private $tbl_attributes = "phplist_user_attribute";
    private $tbl_user_attributes = "phplist_user_user_attribute";
    private $tbl_lists = "list";
    private $tbl_user_lists = "listuser";

    public function __construct() {
        switch ($_SERVER['APP_MODE']) {
            case 'prod':
                $this->db_host = '172.18.5.12';
                break;
            case 'test':
                $this->db_host = 'mysqltest01.beckett.com';
                break;
            case 'dev':
            case 'local':
            default:
                $this->db_host = 'mysqldev01.beckett.com';
                break;
        }
        $this->connect = mysql_connect($this->db_host, $this->db_user, $this->db_pass);
        mysql_select_db($this->db_name, $this->connect);
    }

    public function __destruct() {
        mysql_close($this->connect);
    }

    /**
     * Removes a specified user from the specified list
     *
     * @param int $user_id, PHPList user id
     * @param int $list_id, PHPList list id
     *
     * @return boolean
     */
    public function remove_user_list($user_id, $list_id) {
        if (is_numeric($user_id) && is_numeric($list_id)) {
            $sql = $this->sql("DELETE FROM " . $this->tbl_user_lists . " WHERE userid = " . $user_id . " AND listid = " . $list_id . "");
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the PHPList user id based off the Beckett.com user id that's specified
     *
     * @param int $user_id, Beckett user id
     *
     * @return int
     */
    public function convert_user_id($user_id) {
        if (is_numeric($user_id)) {
            $sql = $this->sql("SELECT user_attrs.userid FROM " . $this->tbl_attributes . " AS attrs, " . $this->tbl_user_attributes . " AS user_attrs WHERE attrs.name = 'user_id' AND attrs.id = user_attrs.attributeid AND user_attrs.value = '" . $this->sql_safe($user_id) . "' LIMIT 1");
            if (mysql_num_rows($sql) == 1) {
                $row = mysql_fetch_array($sql);
                return $row['userid'];
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    /**
     * Adds/Updates user
     * Includes assigning user attributes and mailing lists
     *
     * @param array $params ex.	array(
     * 								'user'=>array('email'=>'yourname@email.com','confirmed'=>1,'blacklisted'=>1,etc.),
     * 								'attributes'=>array('first_name'=>'John','last_name'=>'Doe','username'=>'username',etc.),
     * 								'lists'=>array('list1','list2')
     *
     * @return bool
     */
    public function save_user($params) {
        if ($params['attributes']['state_id']) {
            $states_model = new Application_states();
            $params['attributes']['state'] = $states_model->get_state_abbrev_by_id($params['attributes']['state_id']);
            unset($params['attributes']['state_id']);
        }

        if ($params['user']['user_id'] > 0) {
            $column = "id";
            $value = $params['user']['user_id'];
        } else {
            $column = "email";
            $value = $params['user']['email'];
        }

        // Check if user already exists
        $check = mysql_fetch_array($this->sql("SELECT COUNT(id) AS num FROM " . $this->tbl_user . " WHERE " . $column . " = '" . $this->sql_safe($value) . "'"));
        if ($check['num'] == 0) {
            if ($params['user']['email']) {
                // Add user to users table
                $columns = "";
                $values = "";
                if(isset($params['user']['user_id'])) {
                    unset($params['user']['user_id']);
                }
                foreach ($params['user'] as $key => $value) {
                    if ($columns)
                        $columns .= ", ";
                    if ($values)
                        $values .= ", ";
                    $columns .= $key;
                    $values .= "'" . $this->sql_safe($value) . "'";
                }
                $uniqid = md5(uniqid(mt_rand(0, 1000) . $params['user']['email']));
                $add = $this->sql("INSERT INTO " . $this->tbl_user . " (" . $columns . ",uniqid,entered) VALUES (" . $values . ",'" . $this->sql_safe($uniqid) . "',NOW())");
                $user_id = mysql_insert_id();

                // Get attributes
                $sql = $this->sql("SELECT id, name FROM " . $this->tbl_attributes . "");
                if (mysql_num_rows($sql) > 0) {
                    $attributes = array();
                    while ($row = mysql_fetch_array($sql)) {
                        $attributes[] = array('attribute_id' => $row['id'], 'name' => $row['name']);
                    }
                    foreach ($attributes as $key => $value) {
                        // Check it attribute is defined
                        if ($params['attributes'][$value['name']]) {
                            $attr_value = $params['attributes'][$value['name']];
                        } else {
                            $attr_value = "";
                        }
                        // Add attribute to table
                        $add = $this->sql("INSERT INTO " . $this->tbl_user_attributes . " (attributeid,userid,value) VALUES (" . $value['attribute_id'] . "," . $user_id . ",'" . $this->sql_safe($attr_value) . "')");
                    }
                }

                // Assign to lists
                if ($params['lists']) {
                    foreach ($params['lists'] as $key => $value) {
                        // Get list id
                        if (is_numeric($value)) {
                            $list_id = $value;
                        } else {
                            $sql = $this->sql("SELECT id FROM " . $this->tbl_lists . " WHERE name = '" . $this->sql_safe($value) . "' LIMIT 1");
                            if (mysql_num_rows($sql) == 1) {
                                $list = mysql_fetch_array($sql);
                                $list_id = $list['id'];
                            }
                        }
                        if (is_numeric($list_id)) {
                            // Add to users list table
                            $sql = $this->sql("INSERT INTO " . $this->tbl_user_lists . " (userid,listid) VALUES (" . $user_id . "," . $list_id . ")");
                        }
                    }
                }
                return true;
            } else {
                return false;
            }
        } else {
            // Get user id
            $sql = $this->sql("SELECT id FROM " . $this->tbl_user . " WHERE " . $column . " = '" . $this->sql_safe($value) . "' LIMIT 1");
            if (mysql_num_rows($sql) == 1) {
                $user = mysql_fetch_array($sql);
                $user_id = $user['id'];

                // Update users table
                $update = "";
                foreach ($params['user'] as $key => $value) {
                    if ($key != 'user_id') {
                        if ($update)
                            $update .= ", ";
                        $update .= $key . " = '" . $this->sql_safe($value) . "'";
                    }
                }
                if ($update) {
                    $sql = $this->sql("UPDATE " . $this->tbl_user . " SET " . $update . " WHERE id = " . $user_id);
                }

                // Update user sttributes table
                if ($params['attributes']) {
                    foreach ($params['attributes'] as $key => $value) {
                        // Get attribute ID
                        $sql = $this->sql("SELECT id FROM " . $this->tbl_attributes . " WHERE name = '" . $this->sql_safe($key) . "' LIMIT 1");
                        if (mysql_num_rows($sql) == 1) {
                            // Update attribute
                            $id = mysql_fetch_array($sql);
                            $update = $this->sql("UPDATE " . $this->tbl_user_attributes . " SET value = '" . $this->sql_safe($value) . "' WHERE attributeid = " . $id['id'] . " AND userid = " . $user_id);
                        }
                    }
                }

                // Assign to lists
                if ($params['lists']) {
                    foreach ($params['lists'] as $key => $value) {
                        // Get list id
                        if (is_numeric($value)) {
                            $list_id = $value;
                        } else {
                            $sql = $this->sql("SELECT id FROM " . $this->tbl_lists . " WHERE name = '" . $this->sql_safe($value) . "' LIMIT 1");
                            if (mysql_num_rows($sql) == 1) {
                                $list = mysql_fetch_array($sql);
                                $list_id = $list['id'];
                            }
                        }
                        if (is_numeric($list_id) && $list_id) {
                            // Check if already assigned
                            $check = mysql_fetch_array($this->sql("SELECT COUNT(userid) AS num FROM " . $this->tbl_user_lists . " WHERE userid = " . $user_id . " AND listid = " . $list_id));
                            if ($check['num'] == 0) {
                                // Assign user to the list
                                $sql = $this->sql("INSERT INTO " . $this->tbl_user_lists . " (userid,listid) VALUES (" . $user_id . "," . $list_id . ")");
                            }
                        }
                    }
                }
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Gets an array of all the lists the specified user is currently assigned to
     *
     * @param int $user_id
     *
     * @return array
     */
    public function assigned_lists($user_id) {
        $sql = $this->sql("SELECT list.* FROM " . $this->tbl_lists . " AS list, " . $this->tbl_user_lists . " AS user_list WHERE list.id = user_list.listid AND user_list.userid = " . $user_id . "");
        if (mysql_num_rows($sql) > 0) {
            $list = array();
            while ($row = mysql_fetch_array($sql)) {
                $list[$row['id']] = array(
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'entered' => $row['entered'],
                    'listorder' => $row['listorder'],
                    'prefix' => $row['prefix'],
                    'rssfeed' => $row['rssfeed'],
                    'modified' => $row['modified'],
                    'active' => $row['active'],
                    'owner' => $row['owner']
                );
            }
            return $list;
        } else {
            return array();
        }
    }

    /**
     * Gets an array of the specified users information
     *
     * @param string $user_id
     *
     * @return array
     */
    public function user($user_id) {
        if (is_numeric($user_id)) {
            $column = "id";
        } else {
            $column = "email";
        }
        // Get user from users table
        $sql = $this->sql("SELECT * FROM " . $this->tbl_user . " WHERE " . $column . " = '" . $this->sql_safe($user_id) . "' LIMIT 1");
        if (mysql_num_rows($sql) == 1) {
            $user = array();
            while ($row = mysql_fetch_array($sql)) {
                $user[] = array(
                    'user_id' => $row['user_id'],
                    'email' => $row['email'],
                    'confirmed' => $row['confirmed'],
                    'blacklisted' => $row['blacklisted'],
                    'bouncecount' => $row['bouncecount'],
                    'entered' => $row['entered'],
                    'uniqid' => $row['uniqid'],
                    'htmlemail' => $row['htmlemail'],
                    'subscribepage' => $row['subscribepage'],
                    'rssfrequency' => $row['rssfrequency'],
                    'password' => $row['password'],
                    'passwordchanged' => $row['passwordchanged'],
                    'disabled' => $row['disabled'],
                    'extradata' => $row['extradata'],
                    'foreignkey' => $row['foreignkey']
                );
                return $user;
            }
        } else {
            return array();
        }
    }

    /**
     * Gets an array of the specified user's attributes
     *
     * @param int $user_id
     *
     * @return array
     */
    public function user_attributes($user_id) {
        $sql = $this->sql("SELECT attribute.name, user_attr.value FROM " . $this->tbl_attributes . " AS attribute, " . $this->tbl_user_attributes . " AS user_attr WHERE user_attr.userid = " . $user_id . " AND user_attr.attributeid = attribute.id");
        if (mysql_num_rows($sql) > 0) {
            $attributes = array();
            while ($row = mysql_fetch_array($sql)) {
                $attributes[] = array(
                    'name' => $row['name'],
                    'value' => $row['value']
                );
            }
            return $attributes;
        } else {
            return array();
        }
    }

    /**
     * Gets an array of active mailing lists
     *
     * @return array
     */
    public function lists() {
        $sql = $this->sql("SELECT * FROM " . $this->tbl_lists . " WHERE active = 1 ORDER BY listorder ASC");
        if (mysql_num_rows($sql) > 0) {
            $lists = array();
            while ($row = mysql_fetch_array($sql)) {
                $lists[] = array(
                    'list_id' => $row['id'],
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'entered' => $row['entered'],
                    'listorder' => $row['listorder'],
                    'prefix' => $row['prefix'],
                    'rssfeed' => $row['rssfeed'],
                    'modified' => $row['modified'],
                    'owner' => $row['owner']
                );
            }
            return $lists;
        } else {
            return array();
        }
    }

    private function sql($sql) {
        $rs = mysql_query($sql, $this->connect);
        if(!$rs) {
            $log = new Application_log();
            $log->description = 'phplist error occurred: ' . mysql_error() . '-' . $rs;
            $log->save_as_new();
        }
        return $rs;
    }

    private function sql_safe($string) {
        return mysql_escape_string($string);
    }

}

?>