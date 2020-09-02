<?php

if (!app_session_is_registered('common_filters_filter'))
{
	$common_filters_filter = 0;
	app_session_register('common_filters_filter');
}

$app_title = app_set_title(TEXT_HEADING_REPORTS);

switch($app_module_action)
{
	case 'copy':
		$reports_id = _get::int('reports_id');
		reports::copy($reports_id);
		redirect_to('ext/common_filters/reports');
		break;
	case 'set_reports_filter':
		$common_filters_filter = $_POST['reports_filter'];
	
		redirect_to('ext/common_filters/reports');
		break;
  case 'save':
    $sql_data = array('name'=>filter_var($_POST['name'],FILTER_SANITIZE_STRING),
                      'entities_id'=>filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING),
                      'menu_icon'=>filter_var($_POST['menu_icon'],FILTER_SANITIZE_STRING),
                      'dashboard_sort_order'=>filter_var($_POST['sort_order'],FILTER_SANITIZE_STRING),
                      'in_dashboard_counter'=>(isset($_POST['in_dashboard_counter']) ? filter_var($_POST['in_dashboard_counter'],FILTER_SANITIZE_STRING):0),
                      'in_dashboard_icon'=>1,
                      'in_dashboard_counter_color'=>filter_var($_POST['in_dashboard_counter_color'],FILTER_SANITIZE_STRING),
                      'in_dashboard_counter_fields'=>(isset($_POST['in_dashboard_counter_fields']) ? implode(',',filter_var_array($_POST['in_dashboard_counter_fields'])):''),
                      'dashboard_counter_sum_by_field'=>filter_var($_POST['dashboard_counter_sum_by_field'],FILTER_SANITIZE_STRING),
                      'dashboard_counter_hide_count'=>(isset($_POST['dashboard_counter_hide_count']) ? 1:0),
                      'users_groups' => (isset($_POST['users_groups']) ? implode(',',filter_var_array($_POST['users_groups'])):''),
                      'reports_type'=>'common_filters',                                              
                      'in_menu'=>(isset($_POST['in_menu']) ? filter_var($_POST['in_menu'],FILTER_SANITIZE_STRING):0),
                      'in_dashboard'=>(isset($_POST['in_dashboard']) ? filter_var($_POST['in_dashboard'],FILTER_SANITIZE_STRING):0),
                      'in_header'=>(isset($_POST['in_header']) ? filter_var($_POST['in_header'],FILTER_SANITIZE_STRING):0),
                      'in_header_autoupdate'=>(isset($_POST['in_header_autoupdate']) ? filter_var($_POST['in_header_autoupdate'],FILTER_SANITIZE_STRING):0),
                      'displays_assigned_only'=>(isset($_POST['displays_assigned_only']) ? filter_var($_POST['displays_assigned_only'],FILTER_SANITIZE_STRING):0),                      
                      'created_by'=>filter_var($app_logged_users_id,FILTER_SANITIZE_STRING),
                      'notification_days'=>(isset($_POST['notification_days']) ? implode(',',filter_var_array($_POST['notification_days'])):''),
                      'notification_time'=>(isset($_POST['notification_time']) ? implode(',',filter_var_array($_POST['notification_time'])):''),
                      'fields_in_listing' =>(isset($_POST['fields_in_listing']) ? implode(',',filter_var_array($_POST['fields_in_listing'])):''),
                      'listing_type' => (isset($_POST['listing_type']) ? filter_var($_POST['listing_type'],FILTER_SANITIZE_STRING):''),
                      );
            
    if(isset($_GET['id']))
    {        
      
      $report_info = db_find('app_reports',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
      
      //check reprot entity and if it's changed remove report filters and parent reports
      if($report_info['entities_id']!=$_POST['entities_id'])
      {
        db_query("delete from app_reports_filters where reports_id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
        
        //delete paretn reports
        reports::delete_parent_reports(filter_var($_GET['id'],FILTER_SANITIZE_STRING));
        $sql_data['parent_id']=0;
      }
      
      db_perform('app_reports',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");       
    }
    else
    {                     
      db_perform('app_reports',$sql_data);   
      
      $insert_id = db_insert_id();
      
     //reports::auto_create_parent_reports($insert_id);               
    }
        
    redirect_to('ext/common_filters/reports');      
  break;
  case 'delete':
      if(isset($_GET['id']))
      {      
      
        //delete paretn reports
        reports::delete_parent_reports($_GET['id']);

        db_query("delete from app_reports where id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
        db_query("delete from app_reports_filters where reports_id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
                            
        $alerts->add(TEXT_WARN_DELETE_REPORT_SUCCESS,'success');
     
                
        redirect_to('ext/common_filters/reports');  
      }
    break;   
}