<?php

$app_title = app_set_title(TEXT_USERS_ALERTS);

switch($app_module_action)
{
  case 'save':
    $sql_data = array(
    	'is_active'	=> (isset($_POST['is_active']) ? 1:0),
    	'entities_id' => filter_var(_get::int('entities_id'),FILTER_SANITIZE_STRING),
    	'type'	=> filter_var($_POST['type'],FILTER_SANITIZE_STRING),    	    
    	'color'	=> (isset($_POST['color']) ? filter_var($_POST['color'],FILTER_SANITIZE_STRING) : ''),
    	'position'	=> (isset($_POST['position']) ? filter_var($_POST['position'],FILTER_SANITIZE_STRING) : ''),
    	'start_date' => (isset($_POST['start_date']) ? (int)get_date_timestamp(filter_var($_POST['start_date'],FILTER_SANITIZE_STRING)):0),
    	'end_date' => (isset($_POST['end_date']) ? (int)get_date_timestamp(filter_var($_POST['end_date'],FILTER_SANITIZE_STRING)):0),
    	'name'	=> filter_var($_POST['name'],FILTER_SANITIZE_STRING),
    	'icon'	=> (isset($_POST['icon']) ? filter_var($_POST['icon'],FILTER_SANITIZE_STRING) : ''),
    	'description'	=> filter_var($_POST['description'],FILTER_SANITIZE_STRING),    	    	
    	'users_groups' => (isset($_POST['users_groups']) ? implode(',',filter_var_array($_POST['users_groups'])):''),    	
    	'created_by' => filter_var($app_user['id'],FILTER_SANITIZE_STRING),
    	'sort_order' => filter_var($_POST['sort_order'],FILTER_SANITIZE_STRING),
    	
    );
        
    if(isset($_GET['id']))
    {                  
      db_perform('app_help_pages',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");       
    }
    else
    {                     
      db_perform('app_help_pages',$sql_data);                             
    }
        
    redirect_to('help_pages/pages','entities_id=' .  _get::int('entities_id'));      
  break;
  case 'delete':
      if(isset($_GET['id']))
      {  
        db_query("delete from app_help_pages where id='" . _get::int('id') . "'");        
                     
        redirect_to('help_pages/pages','entities_id=' .  _get::int('entities_id'));  
      }
    break; 

}