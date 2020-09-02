<?php

$obj = array();

if(isset($_GET['id']))
{
	$obj = db_find('app_help_pages',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
}
else
{
	$obj = db_show_columns('app_help_pages');
	
	$obj['type'] = filter_var($_GET['type'],FILTER_SANITIZE_STRING);
	$obj['is_active'] = 1;
}
