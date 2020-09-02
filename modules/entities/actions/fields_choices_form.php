<?php

$obj = array();

if(isset($_GET['id']))
{
  $obj = db_find('app_fields_choices',filter_var($_GET['id'],FILTER_SANITIZE_STRING));  
}
else
{
  $obj = db_show_columns('app_fields_choices');
  $obj['is_active'] = 1;
}

$fields_info = db_find('app_fields',filter_var($_GET['fields_id'],FILTER_SANITIZE_STRING));