<?php

//check access
if($app_user['group_id']>0)
{
	redirect_to('dashboard/access_forbidden');
}

switch($app_module_action)
{
	case 'save':

		$sql_data = array('name'=>db_prepare_input(filter_var($_POST['name'],FILTER_SANITIZE_STRING)),
				'entities_id'=>filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING),
				'cfg_numer_format'=>filter_var($_POST['cfg_numer_format'],FILTER_SANITIZE_STRING),				
				'allowed_groups'=>(isset($_POST['allowed_groups']) ? implode(',',filter_var_array($_POST['allowed_groups'])):''),
				'allow_edit'=>(isset($_POST['allow_edit']) ? filter_var($_POST['allow_edit'],FILTER_SANITIZE_STRING):0),
				'sort_order'=>filter_var($_POST['sort_order'],FILTER_SANITIZE_STRING),				
		);


		if(isset($_GET['id']))
		{
			//check if entity changed
			$pivotreports = db_find('app_ext_pivotreports',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
			if($pivotreports['entities_id']!=$_POST['entities_id'])
			{
				db_delete_row('app_ext_pivotreports_fields',filter_var($_GET['id'],FILTER_SANITIZE_STRING),'pivotreports_id');
				$sql_data['reports_settings'] = '';
			}
			
			db_perform('app_ext_pivotreports',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
		}
		else
		{
			db_perform('app_ext_pivotreports',$sql_data);
		}

		redirect_to('ext/pivotreports/reports');

		break;
	case 'delete':
		$obj = db_find('app_ext_pivotreports',filter_var($_GET['id'],FILTER_SANITIZE_STRING));

		db_delete_row('app_ext_pivotreports',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
		db_delete_row('app_ext_pivotreports_fields',filter_var($_GET['id'],FILTER_SANITIZE_STRING),'pivotreports_id');
		db_delete_row('app_ext_pivotreports_settings',filter_var($_GET['id'],FILTER_SANITIZE_STRING),'reports_id');

		$report_info_query = db_query("select * from app_reports where reports_type='pivotreports" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)). "'");
		if($report_info = db_fetch_array($report_info_query))
		{
			reports::delete_reports_by_id($report_info['id']);
		}

		$alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS,$obj['name']),'success');

		redirect_to('ext/pivotreports/reports');
		break;
}
