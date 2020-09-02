<?php


switch($app_module_action)
{
	case 'save':
				
		$sql_data = array(
			'entities_id'=>filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING),
			'fields_id'=>filter_var($_POST['fields_id'],FILTER_SANITIZE_STRING),		
		);
	
		if(isset($_GET['id']))
		{
			$access_rules_fields_info = db_find('app_access_rules_fields',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
			if($access_rules_fields_info['fields_id']!=$_POST['fields_id'])
			{
				db_delete_row('app_access_rules',filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING),'entities_id');
			}
			
			db_perform('app_access_rules_fields',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
		}
		else
		{
			db_perform('app_access_rules_fields',$sql_data);			
		}
	
		redirect_to('access_rules/fields','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
		break;
	
	case 'delete':
		
		if(isset($_GET['id']))
		{
			db_delete_row('app_access_rules_fields',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
			db_delete_row('app_access_rules',filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING),'entities_id');			
		}
		
		redirect_to('access_rules/fields','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
		break;

}