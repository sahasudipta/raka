<?php

$obj = array();

if(isset($_GET['id']))
{
  $obj = db_find('app_ext_processes',filter_var($_GET['id'],FILTER_SANITIZE_STRING));  
}
else
{
  $obj = db_show_columns('app_ext_processes');
  
  $obj['entities_id'] = $processes_filter;
  $obj['is_active'] = true;
}