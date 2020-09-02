<?php
//check access
if($app_user['group_id']>0)
{
	exit();
}

$obj = array();

if(isset($_GET['id']))
{
	$obj = db_find('app_ext_funnelchart',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
}
else
{
	$obj = db_show_columns('app_ext_funnelchart');
}