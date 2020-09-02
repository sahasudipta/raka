<?php 

$app_title = app_set_title(TEXT_SECTIONS);

switch($app_module_action)
{
  case 'save':
    $sql_data = array(    
    	'name'	=> filter_var($_POST['name'],FILTER_SANITIZE_STRING),    	
    	'grid'	=> filter_var($_POST['grid'],FILTER_SANITIZE_STRING),
    	'sort_order' => filter_var($_POST['sort_order'],FILTER_SANITIZE_STRING),
    	
    );
        
    if(isset($_GET['id']))
    {                  
      db_perform('app_dashboard_pages_sections',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");       
    }
    else
    {                     
      db_perform('app_dashboard_pages_sections',$sql_data);                             
    }
        
    redirect_to('dashboard_configure/sections');      
  break;
  case 'delete':
      if(isset($_GET['id']))
      {  
        db_query("delete from app_dashboard_pages_sections where id='" . _get::int('id') . "'");
        db_query("update app_dashboard_pages set sections_id=0 where sections_id='" . _get::int('id') . "'");
                     
        redirect_to('dashboard_configure/sections');  
      }
    break; 

}