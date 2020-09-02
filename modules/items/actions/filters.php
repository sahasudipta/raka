<?php

$reports_info_query = db_query("select * from app_reports where id='" . db_input(filter_var($_GET['reports_id'],FILTER_SANITIZE_STRING)). "'");
if(!$reports_info = db_fetch_array($reports_info_query))
{
  $alerts->add(TEXT_REPORT_NOT_FOUND,'error');
  redirect_to('items/','path=' . filter_var($_GET['path'],FILTER_SANITIZE_STRING));
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
    $sql_data = array('reports_id'=>filter_var($_GET['reports_id'],FILTER_SANITIZE_STRING),
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
        
    redirect_to('items/filters','reports_id=' . filter_var($_GET['reports_id'],FILTER_SANITIZE_STRING) . '&path=' . filter_var($_GET['path'],FILTER_SANITIZE_STRING));      
  break;
  case 'delete':
      if(isset($_GET['id']))
      {      

        db_query("delete from app_reports_filters where id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
                            
        $alerts->add(TEXT_WARN_DELETE_FILTER_SUCCESS,'success');
     
                
        redirect_to('items/filters','reports_id=' . filter_var($_GET['reports_id'],FILTER_SANITIZE_STRING) . '&path=' . filter_var($_GET['path'],FILTER_SANITIZE_STRING));  
      }
    break;   
}

$entity_info = db_find('app_entities',$current_entity_id);
$entity_cfg = entities::get_cfg($current_entity_id);

$entity_listing_heading = (strlen($entity_cfg['listing_heading'])>0 ? $entity_cfg['listing_heading'] : $entity_info['name']);