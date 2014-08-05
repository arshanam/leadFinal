<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
$cache = array (
  'table' => 'audit_user_role',
  'fields' => 
  array (
    0 => 'role_id',
    1 => 'role_name',
    2 => 'module_read',
    3 => 'module_write',
    4 => 'extra_info',
    5 => 'active',
    6 => 'role_client_id',
    7 => 'is_deleted',
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
    'role_id' => 
    array (
      'field' => 'role_id',
      'rules' => 
      array (
      ),
    ),
    'role_name' => 
    array (
      'field' => 'role_name',
      'rules' => 
      array (
      ),
    ),
    'module_read' => 
    array (
      'field' => 'module_read',
      'rules' => 
      array (
      ),
    ),
    'module_write' => 
    array (
      'field' => 'module_write',
      'rules' => 
      array (
      ),
    ),
    'extra_info' => 
    array (
      'field' => 'extra_info',
      'rules' => 
      array (
      ),
    ),
    'active' => 
    array (
      'field' => 'active',
      'rules' => 
      array (
      ),
    ),
    'role_client_id' => 
    array (
      'field' => 'role_client_id',
      'rules' => 
      array (
      ),
    ),
    'is_deleted' => 
    array (
      'field' => 'is_deleted',
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