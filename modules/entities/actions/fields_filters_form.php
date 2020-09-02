<?php

$reports_info_query = db_query("select * from app_reports where id='" . db_input(filter_var($_GET['reports_id'],FILTER_SANITIZE_STRING)). "' and reports_type='fieldfilter" . filter_var($_GET['fields_id'],FILTER_SANITIZE_STRING) . "'");
if(!$reports_info = db_fetch_array($reports_info_query))
{
  echo TEXT_REPORT_NOT_FOUND;
  exit();
}

$obj = array();

if(isset($_GET['id']))
{
  $obj = db_find('app_reports_filters',filter_var($_GET['id'],FILTER_SANITIZE_STRING));  
}
else
{
  $obj = db_show_columns('app_reports_filters');
}