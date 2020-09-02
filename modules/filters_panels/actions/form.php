<?php

$obj = array();

if(isset($_GET['id']))
{
	$obj = db_find('app_filters_panels',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
}
else
{
	$obj = db_show_columns('app_filters_panels');
	$obj['is_active_filters']=1;
}