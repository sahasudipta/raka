<?php

$app_title = app_set_title(TEXT_USERS_ALERTS);

switch($app_module_action)
{
  case 'save':
    $sql_data = array(
    	'is_active'	=> (isset($_POST['is_active']) ? 1:0),
    	'type'	=> filter_var($_POST['type'],FILTER_SANITIZE_STRING),
    	'title'	=> filter_var($_POST['title'],FILTER_SANITIZE_STRING),
    	'description'	=> filter_var($_POST['description'],FILTER_SANITIZE_STRING),
    	'location' => filter_var($_POST['location'],FILTER_SANITIZE_STRING),
    	'start_date' => (int)get_date_timestamp(filter_var($_POST['start_date'],FILTER_SANITIZE_STRING)),
    	'end_date' => (int)get_date_timestamp(filter_var($_POST['end_date'],FILTER_SANITIZE_STRING)),
    	'users_groups' => (isset($_POST['users_groups']) ? implode(',',filter_var_array($_POST['users_groups'])):''),
    	'assigned_to' => (isset($_POST['assigned_to']) ? implode(',',filter_var_array($_POST['assigned_to'])):''),
    	'created_by' => filter_var($app_user['id'],FILTER_SANITIZE_STRING),
    	
    );
        
    if(isset($_GET['id']))
    {                  
      db_perform('app_users_alerts',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");       
    }
    else
    {                     
      db_perform('app_users_alerts',$sql_data);                             
    }
        
    redirect_to('users_alerts/');      
  break;
  case 'delete':
      if(isset($_GET['id']))
      {  
        db_query("delete from app_users_alerts where id='" . _get::int('id') . "'");
        db_query("delete from app_users_alerts_viewed where alerts_id='" . _get::int('id') . "'");
                     
        redirect_to('users_alerts/');  
      }
    break; 

}