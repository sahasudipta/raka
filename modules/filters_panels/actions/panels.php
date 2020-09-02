<?php


$entities_cfg = new entities_cfg(_get::int('entities_id'));

switch($app_module_action)
{
	case 'set_default_status':
		$entities_cfg->set('default_filter_panel_status',$_POST['status']);
		exit();
		break;
				
	case 'set_default_panel_access':
		$entities_cfg->set('default_filter_panel_access',(isset($_POST['users_groups']) ? implode(',', $_POST['users_groups']) : ''));
		redirect_to('filters_panels/panels','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
		break;
		
	case 'set_listing_config_access':
		$entities_cfg->set('listing_config_access',(isset($_POST['users_groups']) ? implode(',', $_POST['users_groups']) : ''));
		redirect_to('filters_panels/panels','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
		break;
				
	case 'save':		
		$sql_data = array(
		'position'=>filter_var($_POST['position'],FILTER_SANITIZE_STRING),	
		'type' =>'',
		'entities_id'=>filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING),
		'is_active'=>(isset($_POST['is_active']) ? 1:0),
		'is_active_filters'=>(isset($_POST['is_active_filters']) ? 1:0),		
		'users_groups'=>(isset($_POST['users_groups']) ? implode(',',filter_var_array($_POST['users_groups'])):''),
		'width'=>filter_var($_POST['width'],FILTER_SANITIZE_STRING),
		'sort_order'=>filter_var($_POST['sort_order'],FILTER_SANITIZE_STRING),		
		);
	
	
		if(isset($_GET['id']))
		{
			db_perform('app_filters_panels',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
		}
		else
		{
			db_perform('app_filters_panels',$sql_data);
		}
	
		redirect_to('filters_panels/panels','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
	
		break;
		
	case 'delete':
		
		db_delete_row('app_filters_panels',_get::int('id'));
		db_delete_row('app_filters_panels_fields',_get::int('id'),'panels_id');
	
		redirect_to('filters_panels/panels','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
		break;		
}
