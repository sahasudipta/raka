<?php

$app_reports_groups_id = (isset($_GET['id']) ? filter_var(_get::int('id'),FILTER_SANITIZE_STRING) : 0);

if($app_reports_groups_id>0)
{	
	$reports_groups_info_query = db_query("select * from app_reports_groups where ((created_by = '" . db_input(filter_var($app_user['id'],FILTER_SANITIZE_STRING)) . "' and is_common=0) or (" . (filter_var($app_user['group_id'],FILTER_SANITIZE_STRING)>0 ? "find_in_set(" . filter_var($app_user['group_id'],FILTER_SANITIZE_STRING) . ",users_groups) and ":"") . " is_common=1)) and id='" . db_input(filter_var($app_reports_groups_id,FILTER_SANITIZE_STRING)) . "'");
	if(!$reports_groups_info = db_fetch_array($reports_groups_info_query))
	{
		redirect_to('dashboard/access_forbidden');
	}
}

switch($app_module_action)
{	
	case 'save':
		redirect_to('dashboard/reports','id=' . $app_reports_groups_id);
		break;
	case 'sort_reports':	
		if(isset($_POST['reports_on_dashboard']))
		{
			$sql_data = array('reports_list'=>str_replace('report_','',$_POST['reports_on_dashboard']));
			db_perform('app_reports_groups',$sql_data,'update',"id='" .  $app_reports_groups_id . "'" );		
		}
		
		exit();
		break;
	
	case 'sort_reports_counter':			
		if(isset($_POST['reports_counter_on_dashboard']))
		{
			$sql_data = array('counters_list'=>str_replace('report_','',$_POST['reports_counter_on_dashboard']));
			db_perform('app_reports_groups',$sql_data,'update',"id='" .  $app_reports_groups_id . "'" );
		}
			
		exit();
		break;
		
	//handle sections
	case 'add_section':
		$sql_data = array('reports_groups_id'=>$app_reports_groups_id,'created_by'=>$app_user['id']);
		db_perform('app_reports_sections',$sql_data);
		 
		$sections = new reports_sections($app_reports_groups_id, $reports_groups_info['is_common']);		
		echo $sections->render();
		 
		exit();
		break;
	case 'get_sections':
		$sections = new reports_sections($app_reports_groups_id, $reports_groups_info['is_common']);
		echo $sections->render();
		exit();
		break;
	case 'delete_section':
		if(isset($_POST['section_id']))
		{
			db_delete_row('app_reports_sections', filter_var($_POST['section_id'],FILTER_SANITIZE_STRING));
		}
		exit();
		break;
	case 'edit_section':
		if(isset($_POST['section_id']))
		{
			$value = filter_var($_POST['value'],FILTER_SANITIZE_STRING);
			
			$check_query = db_query("select id from app_reports_sections where ((report_left='{$value}' and length(report_left)>0) or (report_right='{$value}' and length(report_right)>0)) and reports_groups_id={$app_reports_groups_id} and created_by='{$app_user['id']}'");
			if(!$check = db_fetch_array($check_query))
			{
				$sql_data = array(filter_var($_POST['type'],FILTER_SANITIZE_STRING)=>$value);
				db_perform('app_reports_sections',$sql_data,'update',"id='" .  db_input(filter_var($_POST['section_id'],FILTER_SANITIZE_STRING)) . "'" );
			}
			else
			{
				echo TEXT_REPORT_ALREADY_ASSIGNED;
			}
		}
		exit();
		break;
	case 'sort_sections':
		if(isset($_POST['section_panel']))
		{
			$sort_order = 0;
			foreach(explode(',',filter_var($_POST['section_panel'],FILTER_SANITIZE_STRING)) as $v)
			{
				$sql_data = array('sort_order'=>$sort_order);
				db_perform('app_reports_sections',$sql_data,'update',"id='" . db_input(str_replace('section_panel_','',$v)) . "'");
				$sort_order++;
			}
		}
		exit();
		break;
}