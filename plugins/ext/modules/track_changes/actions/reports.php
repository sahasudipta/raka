<?php

$app_title = app_set_title(TEXT_EXT_CHANGE_HISTORY);

switch($app_module_action)
{
	case 'save':
		$sql_data = array(
		        'is_active'=>(isset($_POST['is_active']) ? 1 : 0),
			'name'=>filter_var($_POST['name'],FILTER_SANITIZE_STRING),					
			'menu_icon'=>filter_var($_POST['menu_icon'],FILTER_SANITIZE_STRING),
			'position'=>(isset($_POST['position']) ? implode(',',filter_var_array($_POST['position'])) : ''),
			'users_groups'=>(isset($_POST['users_groups']) ? implode(',',filter_var_array($_POST['users_groups'])) : ''),
			'assigned_to'=>(isset($_POST['assigned_to']) ? implode(',',filter_var_array($_POST['assigned_to'])) : ''),
			'color_insert'=>filter_var($_POST['color_insert'],FILTER_SANITIZE_STRING),
			'color_comment'=>filter_var($_POST['color_comment'],FILTER_SANITIZE_STRING),
			'color_update'=>filter_var($_POST['color_update'],FILTER_SANITIZE_STRING),			
			'keep_history'=>filter_var((int)$_POST['keep_history'],FILTER_SANITIZE_STRING),
			'rows_per_page'=>filter_var((int)$_POST['rows_per_page'],FILTER_SANITIZE_STRING),
		);

		if(isset($_GET['id']))
		{			 
			db_perform('app_ext_track_changes',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
		}
		else
		{
			db_perform('app_ext_track_changes',$sql_data);

			$insert_id = db_insert_id();
		}

		redirect_to('ext/track_changes/reports');
		break;

	case 'delete':
		if(isset($_GET['id']))
		{
			$obj = db_find('app_ext_track_changes',filter_var($_GET['id'],FILTER_SANITIZE_STRING));

			db_query("delete from app_ext_track_changes where id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
			db_query("delete from app_ext_track_changes_entities where reports_id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
			db_query("delete from app_ext_track_changes_log where reports_id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
						 
			redirect_to('ext/track_changes/reports');
		}
		break;
}