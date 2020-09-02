<?php

if(!users::has_reports_access())
{
	redirect_to('dashboard/access_forbidden');
}

$app_title = app_set_title(TEXT_REPORTS_GROUPS);

switch($app_module_action)
{
	case 'save':
		$sql_data = array(
			'name'=>db_prepare_input(filter_var($_POST['name'],FILTER_SANITIZE_STRING)),		
			'menu_icon'=>filter_var($_POST['menu_icon'],FILTER_SANITIZE_STRING),
			'in_menu'=>(isset($_POST['in_menu']) ? filter_var($_POST['in_menu'],FILTER_SANITIZE_STRING):0),
			'sort_order'=>filter_var($_POST['sort_order'],FILTER_SANITIZE_STRING),	
			'created_by' => filter_var($app_user['id'],FILTER_SANITIZE_STRING),
			'is_common' =>0,
		);

		if(isset($_GET['id']))
		{			
			db_perform('app_reports_groups',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
		}
		else
		{
			db_perform('app_reports_groups',$sql_data);					
		}

		redirect_to('reports_groups/reports');
		break;
	case 'delete':
		if(isset($_GET['id']))
		{
			db_delete_row('app_reports_groups',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
			
			redirect_to('reports_groups/reports');
		}
		break;
}		
