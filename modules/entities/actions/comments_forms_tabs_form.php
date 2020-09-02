<?php

$obj = array();

if(isset($_GET['id']))
{
	$obj = db_find('app_comments_forms_tabs',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
}
else
{
	$obj = db_show_columns('app_comments_forms_tabs');
}