<?php

switch($app_module_action)
{
	case 'save':

		$values = '';

		if(isset($_POST['values']))
		{
			if(is_array($_POST['values']))
			{
				$values = implode(',',$_POST['values']);
			}
			else
			{
				$values = $_POST['values'];
			}
		}
		$sql_data = array('reports_id'=>filter_var($_GET['reports_id'],FILTER_SANITIZE_STRING),
				'fields_id'=>filter_var($_POST['fields_id'],FILTER_SANITIZE_STRING),
				'filters_condition'=>filter_var($_POST['filters_condition'],FILTER_SANITIZE_STRING),
				'filters_values'=>filter_var($values,FILTER_SANITIZE_STRING),
		);

		if(isset($_GET['id']))
		{
			db_perform('app_reports_filters',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
		}
		else
		{
			db_perform('app_reports_filters',$sql_data);
		}

		redirect_to('access_rules/fields','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
		break;
	case 'delete':
		if(isset($_GET['id']))
		{

			db_query("delete from app_reports_filters where id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");

			$alerts->add(TEXT_WARN_DELETE_FILTER_SUCCESS,'success');
			 

			redirect_to('access_rules/fields','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
		}
		break;
}