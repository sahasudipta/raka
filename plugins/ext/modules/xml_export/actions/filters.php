<?php

$current_templates_info_query = db_query("select * from app_ext_xml_export_templates where id='" . db_input(filter_var($_GET['templates_id'],FILTER_SANITIZE_STRING)). "'");
if(!$current_templates_info = db_fetch_array($current_templates_info_query))
{
  $alerts->add(TEXT_RECORD_NOT_FOUND,'error');
  redirect_to('ext/xml_export/templates');
}


$current_reports_info_query = db_query("select * from app_reports where entities_id='" . $current_templates_info['entities_id'] . "' and reports_type='xml_export" . db_input($current_templates_info['id']). "'");
if(!$current_reports_info = db_fetch_array($current_reports_info_query))
{
   //atuo create report 
      $sql_reports_data = array(
      								 'name'=>'',
                       'entities_id'=>$current_templates_info['entities_id'],
                       'reports_type'=>'xml_export' . $current_templates_info['id'],                                              
                       'in_menu'=>0,
                       'in_dashboard'=>0,
                       'listing_order_fields'=>'',
                       'created_by'=>$app_logged_users_id,
                       );
                   
      db_perform('app_reports',$sql_reports_data);
      $reports_id = db_insert_id();
      
      reports::auto_create_parent_reports($reports_id);
      
      $current_reports_info = db_find('app_reports',$reports_id);
}

switch($app_module_action)
{
  case 'save':
    
    $values = '';
    
    if(isset($_POST['values']))
    {
      if(is_array($_POST['values']))
      {
        $values = implode(',',filter_var_array($_POST['values']));
      }
      else
      {
        $values = filter_var($_POST['values'],FILTER_SANITIZE_STRING);
      }
    }
    $sql_data = array('reports_id'=>(isset($_GET['parent_reports_id']) ? filter_var($_GET['parent_reports_id'],FILTER_SANITIZE_STRING):filter_var($_GET['reports_id'],FILTER_SANITIZE_STRING)),
                      'fields_id'=>filter_var($_POST['fields_id'],FILTER_SANITIZE_STRING),
                      'filters_condition'=>filter_var($_POST['filters_condition'],FILTER_SANITIZE_STRING),                                              
                      'filters_values'=>$values,
                      );
        
    if(isset($_GET['id']))
    {        
      db_perform('app_reports_filters',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");       
    }
    else
    {               
      db_perform('app_reports_filters',$sql_data);                  
    }
    
    redirect_to('ext/xml_export/filters','templates_id=' . filter_var($_GET['templates_id'],FILTER_SANITIZE_STRING));
        
          
  break;
  case 'delete':
      if(isset($_GET['id']))
      {      
        db_query("delete from app_reports_filters where id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
        
        redirect_to('ext/xml_export/filters','templates_id=' . filter_var($_GET['templates_id'],FILTER_SANITIZE_STRING));                  
      }
    break;   
}