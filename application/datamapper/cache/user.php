<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
$cache = array (
  'table' => 'users',
  'fields' => 
  array (
    0 => 'user_id',
    1 => 'first_name',
    2 => 'last_name',
    3 => 'user_name',
    4 => 'email',
    5 => 'date_added',
    6 => 'date_modified',
    7 => 'user_active',
    8 => 'password',
    9 => 'phone',
    10 => 'department_id',
    11 => 'is_superadmin',
    12 => 'is_admin',
  ),
  'validation' => 
  array (
    'id' => 
    array (
      'field' => 'id',
      'rules' => 
      array (
        0 => 'integer',
      ),
    ),
    'user_id' => 
    array (
      'field' => 'user_id',
      'rules' => 
      array (
      ),
    ),
    'first_name' => 
    array (
      'field' => 'first_name',
      'rules' => 
      array (
      ),
    ),
    'last_name' => 
    array (
      'field' => 'last_name',
      'rules' => 
      array (
      ),
    ),
    'user_name' => 
    array (
      'field' => 'user_name',
      'rules' => 
      array (
      ),
    ),
    'email' => 
    array (
      'field' => 'email',
      'rules' => 
      array (
      ),
    ),
    'date_added' => 
    array (
      'field' => 'date_added',
      'rules' => 
      array (
      ),
    ),
    'date_modified' => 
    array (
      'field' => 'date_modified',
      'rules' => 
      array (
      ),
    ),
    'user_active' => 
    array (
      'field' => 'user_active',
      'rules' => 
      array (
      ),
    ),
    'password' => 
    array (
      'field' => 'password',
      'rules' => 
      array (
      ),
    ),
    'phone' => 
    array (
      'field' => 'phone',
      'rules' => 
      array (
      ),
    ),
    'department_id' => 
    array (
      'field' => 'department_id',
      'rules' => 
      array (
      ),
    ),
    'is_superadmin' => 
    array (
      'field' => 'is_superadmin',
      'rules' => 
      array (
      ),
    ),
    'is_admin' => 
    array (
      'field' => 'is_admin',
      'rules' => 
      array (
      ),
    ),
  ),
  'has_one' => 
  array (
  ),
  'has_many' => 
  array (
  ),
  '_field_tracking' => 
  array (
    'get_rules' => 
    array (
    ),
    'matches' => 
    array (
    ),
    'intval' => 
    array (
      0 => 'id',
    ),
  ),
);