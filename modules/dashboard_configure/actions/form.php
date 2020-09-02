<?php

$obj = array();

if(isset($_GET['id']))
{
	$obj = db_find('app_dashboard_pages',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
}
else
{
	$obj = db_show_columns('app_dashboard_pages');
	
	$obj['type'] = $_GET['type'];
	$obj['is_active'] = 1;
}
