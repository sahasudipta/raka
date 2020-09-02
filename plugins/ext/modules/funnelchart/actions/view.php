<?php
//check if report exist
$reports_query = db_query("select * from app_ext_funnelchart where id='" . db_input(filter_var((int)$_GET['id'],FILTER_SANITIZE_STRING)) . "'");
if(!$reports = db_fetch_array($reports_query))
{
	redirect_to('dashboard/page_not_found');
}

if(!in_array(filter_var($app_user['group_id'],FILTER_SANITIZE_STRING),explode(',',filter_var($reports['users_groups'],FILTER_SANITIZE_STRING))) and filter_var($app_user['group_id'],FILTER_SANITIZE_STRING)>0)
{
	redirect_to('dashboard/access_forbidden');
}

$app_title = filter_var($reports['name'],FILTER_SANITIZE_STRING);


switch($app_module_action)
{
	case 'set_view_mode':
		$funnelchart_type[filter_var($reports['id'],FILTER_SANITIZE_STRING)] = filter_var($_GET['view_mode'],FILTER_SANITIZE_STRING);

		redirect_to('ext/funnelchart/view','id=' . filter_var(_get::int('id'),FILTER_SANITIZE_STRING). (isset($_GET['path']) ? '&path=' . $app_path:''));
		break;
}		