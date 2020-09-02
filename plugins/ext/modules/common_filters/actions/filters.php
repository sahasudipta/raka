<?php

$current_reports_info_query = db_query("select * from app_reports where id='" . db_input(filter_var($_GET['reports_id'],FILTER_SANITIZE_STRING)). "'");
if(!$current_reports_info = db_fetch_array($current_reports_info_query))
{
  $alerts->add(TEXT_REPORT_NOT_FOUND,'error');
  redirect_to('ext/common_filters/reports');
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
    
    redirect_to('ext/common_filters/filters','reports_id=' . filter_var($_GET['reports_id'],FILTER_SANITIZE_STRING));
        
          
  break;
  case 'delete':
      if(isset($_GET['id']))
      {      
        db_query("delete from app_reports_filters where id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
        
        redirect_to('ext/common_filters/filters','reports_id=' . filter_var($_GET['reports_id'],FILTER_SANITIZE_STRING));                  
      }
    break;   
}