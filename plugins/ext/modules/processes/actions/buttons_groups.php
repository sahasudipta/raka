<?php

if (!app_session_is_registered('processes_filter'))
{
	$processes_filter = 0;
	app_session_register('processes_filter');
}

$app_title = app_set_title(TEXT_EXT_BUTTONS_GROUPS);

switch($app_module_action)
{
	case 'set_processes_filter':
		$processes_filter = $_POST['processes_filter'];
	
		redirect_to('ext/processes/buttons_groups');
		break;
  case 'save':
    $sql_data = array('name'=>filter_var($_POST['name'],FILTER_SANITIZE_STRING),
                      'entities_id'=>filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING),                        
                      'button_color'=>filter_var($_POST['button_color'],FILTER_SANITIZE_STRING),
                      'button_icon'=>filter_var($_POST['button_icon'],FILTER_SANITIZE_STRING),
                      'button_position'=>(isset($_POST['button_position']) ? implode(',',filter_var_array($_POST['button_position'])) : ''),
                      'sort_order'=>filter_var($_POST['sort_order'],FILTER_SANITIZE_STRING),
                      );
        
    if(isset($_GET['id']))
    {               	
    	    	
      db_perform('app_ext_processes_buttons_groups',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");            
    }
    else
    {              
      db_perform('app_ext_processes_buttons_groups',$sql_data);   
      
      $insert_id = db_insert_id();                           
    }
        
    redirect_to('ext/processes/buttons_groups');      
  break;
  
  case 'delete':
      if(isset($_GET['id']))
      {      
        $obj = db_find('app_ext_processes_buttons_groups',filter_var($_GET['id'],FILTER_SANITIZE_STRING));  
        
        db_query("delete from app_ext_processes_buttons_groups where id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
        
        $alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS,$obj['name']),'success');
                                                                      
        redirect_to('ext/processes/buttons_groups');  
      }
    break; 
}
