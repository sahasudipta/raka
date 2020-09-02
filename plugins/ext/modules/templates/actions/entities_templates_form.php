<?php

$obj = array();

if(isset($_GET['id']))
{
  $obj = db_find('app_ext_entities_templates',filter_var($_GET['id'],FILTER_SANITIZE_STRING));  
}
else
{
  $obj = db_show_columns('app_ext_entities_templates');
  
  if($entities_templates_filter>0)
  {
    $obj['entities_id'] = $entities_templates_filter;
  }
  
  $obj['is_active'] = 1;
}