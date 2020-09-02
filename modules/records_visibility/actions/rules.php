<?php

switch($app_module_action)
{
	case 'save':
		$sql_data = array(
			'entities_id'=>filter_var(_get::int('entities_id'),FILTER_SANITIZE_STRING),
			'is_active' => (isset($_POST['is_active']) ? 1:0),
			'users_groups'=>implode(',',filter_var_array($_POST['users_groups'])),
			'merged_fields'=>(isset($_POST['merged_fields']) ? implode(',',filter_var_array($_POST['merged_fields'])):''),
			'merged_fields_empty_values'=>(isset($_POST['merged_fields_empty_values']) ? implode(',',filter_var_array($_POST['merged_fields_empty_values'])):''),
			'notes'=>filter_var($_POST['notes'],FILTER_SANITIZE_STRING),
		);

		if(isset($_GET['id']))
		{
			db_perform('app_records_visibility_rules',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
		}
		else
		{
			db_perform('app_records_visibility_rules',$sql_data);
		}

		redirect_to('records_visibility/rules','entities_id=' . _get::int('entities_id'));
		break;
	case 'delete':
		if(isset($_GET['id']))
		{			
				db_delete_row('app_records_visibility_rules',_get::int('id'));	
				
				$report_info_query = db_query("select * from app_reports where reports_type='records_visibility" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)). "'");
				if($report_info = db_fetch_array($report_info_query))
				{
					reports::delete_reports_by_id($report_info['id']);
				}

				$alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS,''),'success');
		}	

		redirect_to('records_visibility/rules','entities_id=' . _get::int('entities_id'));
		
		break;
}