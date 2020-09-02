<?php

$obj = array();

if(isset($_GET['id']))
{
  $obj = db_find('app_global_lists_choices',filter_var($_GET['id'],FILTER_SANITIZE_STRING));  
}
else
{
  $obj = db_show_columns('app_global_lists_choices');
  $obj['is_active'] = 1;
}