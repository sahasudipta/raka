<?php

if (!app_session_is_registered('holidays_filter'))
{
	$holidays_filter = date('Y');
	app_session_register('holidays_filter');
}

switch($app_module_action)
{
	case 'set_holidays_filter':
		$holidays_filter = filter_var($_POST['holidays_filter'],FILTER_SANITIZE_STRING);
	
		redirect_to('holidays/holidays');
		break;
	case 'save':
		$sql_data = array(
			'name'=>filter_var($_POST['name'],FILTER_SANITIZE_STRING),
			'start_date'=>filter_var($_POST['start_date'],FILTER_SANITIZE_STRING),
			'end_date'=>filter_var($_POST['end_date'],FILTER_SANITIZE_STRING),
		);

		if(isset($_GET['id']))
		{
			db_perform('app_holidays',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
		}
		else
		{
			db_perform('app_holidays',$sql_data);
		}

		redirect_to('holidays/holidays');
		break;
	case 'delete':
		if(isset($_GET['id']))
		{
			db_delete_row('app_holidays', filter_var(_get::int('id'),FILTER_SANITIZE_STRING));
			
			redirect_to('holidays/holidays');
		}
		break;
}