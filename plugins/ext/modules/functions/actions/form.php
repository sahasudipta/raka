<?php

$obj = array();

if(isset($_GET['id']))
{
  $obj = db_find('app_ext_functions',filter_var($_GET['id'],FILTER_SANITIZE_STRING));  
}
else
{
  $obj = db_show_columns('app_ext_functions');
  
  if($functions_filter>0)
  {
  	$obj['entities_id'] = filter_var($functions_filter,FILTER_SANITIZE_STRING);
  }
}