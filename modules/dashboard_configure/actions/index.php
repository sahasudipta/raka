<?php

$app_title = app_set_title(TEXT_USERS_ALERTS);

switch($app_module_action)
{
  case 'save':
    $sql_data = array(
    	'is_active'	=> (isset($_POST['is_active']) ? 1:0),
    	'type'	=> filter_var($_POST['type'],FILTER_SANITIZE_STRING),
    	'sections_id'	=> (isset($_POST['sections_id']) ? filter_var($_POST['sections_id'],FILTER_SANITIZE_STRING):0),    	
    	'color'	=> filter_var($_POST['color'],FILTER_SANITIZE_STRING),
    	'name'	=> filter_var($_POST['name'],FILTER_SANITIZE_STRING),
    	'icon'	=> filter_var($_POST['icon'],FILTER_SANITIZE_STRING),
    	'description'	=> filter_var($_POST['description'],FILTER_SANITIZE_STRING),    	    	
    	'users_groups' => (isset($_POST['users_groups']) ? implode(',',filter_var_array($_POST['users_groups'])):''),
    	'users_fields' => (isset($_POST['users_fields'])? implode(',',filter_var_array($_POST['users_fields'])):''),
    	'created_by' => filter_var($app_user['id'],FILTER_SANITIZE_STRING),
    	'sort_order' => filter_var($_POST['sort_order'],FILTER_SANITIZE_STRING),
    	
    );
        
    if(isset($_GET['id']))
    {                  
      db_perform('app_dashboard_pages',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");       
    }
    else
    {                     
      db_perform('app_dashboard_pages',$sql_data);                             
    }
        
    redirect_to('dashboard_configure/index');      
  break;
  case 'delete':
      if(isset($_GET['id']))
      {  
        db_query("delete from app_dashboard_pages where id='" . db_input(filter_var(_get::int('id'),FILTER_SANITIZE_STRING)) . "'");        
                     
        redirect_to('dashboard_configure/index');  
      }
    break; 

}